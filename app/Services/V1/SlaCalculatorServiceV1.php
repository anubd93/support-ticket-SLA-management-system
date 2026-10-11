<?php

namespace App\Services\V1;

use App\Enums\V1\TicketPriority;
use App\Repositories\V1\BusinessHourRepositoryV1;
use App\Repositories\V1\HolidayRepositoryV1;
use App\Repositories\V1\SlaPolicyRepositoryV1;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use RuntimeException;

class SlaCalculatorServiceV1
{
    private const MAX_DAYS_TO_SCAN = 366;

    private ?array $schedule = null;
    private ?array $holidays = null;

    public function __construct(private readonly SlaPolicyRepositoryV1 $slaPolicyRepositoryV1,
                                private readonly BusinessHourRepositoryV1 $businessHourRepositoryV1,
                                private readonly HolidayRepositoryV1 $holidayRepositoryV1
    ) {}

    public function deadlines(TicketPriority $priority, CarbonInterface $createdAt, int $pausedMinutes = 0): array
    {
        $policy = $this->policyFor($priority);

        if (! $policy) {
            return ['due_at' => null, 'warning_at' => null];
        }

        $total = (int) $policy->resolution_minutes;
        $warnBefore = (int) round($total * $policy->warning_threshold_percent / 100);

        return [
            'due_at' => $this->addBusinessMinutes($createdAt, $total + $pausedMinutes),
            'warning_at' => $this->addBusinessMinutes($createdAt, $total - $warnBefore + $pausedMinutes),
        ];
    }

    public function policyFor(TicketPriority $priority): ?object
    {
        return $this->slaPolicyRepositoryV1->setPriority($priority->value)->getPolicyByPriority();
    }

    public function resolutionHours(): array
    {
        return $this->slaPolicyRepositoryV1->getActiveResolutionMinutes()
            ->map(fn ($minutes) => intdiv((int) $minutes, 60))
            ->all();
    }

    public function addBusinessMinutes(CarbonInterface $start, int $minutes): Carbon
    {
        $moment = Carbon::instance($start)->copy();
        $remaining = max(0, $minutes);

        for ($day = 0; $day <= self::MAX_DAYS_TO_SCAN; $day++) {
            $opening = $this->openingTime($moment);
            $closing = $this->closingTime($moment);

            if (! $opening || ! $closing) {
                $moment = $moment->copy()->addDay()->startOfDay();
                continue;
            }

            if ($moment->lt($opening)) {
                $moment = $opening;
            }
            
            $available = $moment->lt($closing) ? (int) floor($moment->diffInMinutes($closing, true)) : 0;

            if ($available > 0 && $remaining <= $available) {
                return $moment->copy()->addMinutes($remaining);
            }

            $remaining -= $available;
            $moment = $moment->copy()->addDay()->startOfDay();
        }

        throw new RuntimeException('Could not calculate the SLA deadline: no business hours are configured.');
    }

    public function businessMinutesBetween(CarbonInterface $from, CarbonInterface $to): int
    {
        $moment = Carbon::instance($from)->copy();
        $end = Carbon::instance($to)->copy();
        $total = 0;

        while ($moment->lt($end)) {
            $opening = $this->openingTime($moment);
            $closing = $this->closingTime($moment);

            if ($opening && $closing) {
                $start = $moment->max($opening);
                $stop = $end->min($closing);

                if ($stop->gt($start)) {
                    $total += (int) floor($start->diffInMinutes($stop, true));
                }
            }

            $moment = $moment->copy()->addDay()->startOfDay();
        }

        return $total;
    }

    private function openingTime(Carbon $day): ?Carbon
    {
        $hours = $this->hoursFor($day);

        return $hours ? $day->copy()->setTimeFromTimeString($hours->start_time) : null;
    }

    private function closingTime(Carbon $day): ?Carbon
    {
        $hours = $this->hoursFor($day);

        return $hours ? $day->copy()->setTimeFromTimeString($hours->end_time) : null;
    }

    private function hoursFor(Carbon $day): ?object
    {
        $this->loadCalendar();

        $hours = $this->schedule[$day->dayOfWeek] ?? null;

        if (! $hours || ! $hours->is_working_day || ! $hours->start_time || ! $hours->end_time) {
            return null;
        }

        if (isset($this->holidays[$day->toDateString()])) {
            return null;
        }

        return $hours;
    }

    private function loadCalendar(): void
    {
        if ($this->schedule === null) {
            $this->schedule = $this->businessHourRepositoryV1->getWeeklySchedule()->all();
            $this->holidays = $this->holidayRepositoryV1->getDates();
        }
    }
}
