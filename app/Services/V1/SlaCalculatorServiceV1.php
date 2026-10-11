<?php

namespace App\Services\V1;

use App\Enums\V1\TicketPriority;
use App\Repositories\V1\BusinessHourRepositoryV1;
use App\Repositories\V1\HolidayRepositoryV1;
use App\Repositories\V1\SlaPolicyRepositoryV1;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

class SlaCalculatorServiceV1
{
    public function __construct(private readonly SlaPolicyRepositoryV1 $slaPolicyRepositoryV1,
                                private readonly BusinessHourRepositoryV1 $businessHourRepositoryV1,
                                private readonly HolidayRepositoryV1 $holidayRepositoryV1
    ) {}

    /**
     * Resolution deadline for a priority, counted from $from.
     * Returns null when the priority has no active SLA policy.
     *
     * For now this adds clock time. The SLA module replaces it with
     * business hours (working days, opening hours and holidays).
     */
    public function dueAt(TicketPriority $priority, CarbonInterface $from): ?Carbon
    {
        $minutes = $this->slaPolicyRepositoryV1->setPriority($priority->value)->getResolutionMinutesByPriority();

        if ($minutes === null) {
            return null;
        }

        return Carbon::instance($from)->copy()->addMinutes($minutes);
    }

    /**
     * @return array<string, int> priority => resolution hours, for forms
     */
    public function resolutionHours(): array
    {
        return $this->slaPolicyRepositoryV1->getActiveResolutionMinutes()
            ->map(fn ($minutes) => intdiv((int) $minutes, 60))
            ->all();
    }
}
