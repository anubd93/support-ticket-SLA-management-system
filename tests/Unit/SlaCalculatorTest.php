<?php

namespace Tests\Unit;

use App\Enums\V1\TicketPriority;
use App\Models\V1\BusinessHour;
use App\Models\V1\Holiday;
use App\Services\V1\SlaCalculatorServiceV1;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Group;
use RuntimeException;
use Tests\TestCase;

/**
 * SLA deadlines are counted in business time.
 * Seeded calendar: Sunday-Thursday 09:00-18:00 (Friday and Saturday closed).
 * Seeded policies: Critical 2h / 25% warning, Medium 24h / 20% warning.
 * 2026-10-15 is a Thursday.
 */
#[Group('sla')]
class SlaCalculatorTest extends TestCase
{
    private function calculator(): SlaCalculatorServiceV1
    {
        return app(SlaCalculatorServiceV1::class);
    }

    public function test_the_pdf_example_critical_ticket_created_thursday_5pm_is_due_sunday_10am(): void
    {
        // 1h left on Thursday (17:00-18:00), Friday + Saturday closed, 1h on Sunday (09:00-10:00).
        $deadlines = $this->calculator()->deadlines(TicketPriority::Critical, Carbon::parse('2026-10-15 17:00'));

        $this->assertSame('2026-10-18 10:00:00', $deadlines['due_at']->toDateTimeString());
    }

    public function test_warning_point_is_the_threshold_before_the_deadline(): void
    {
        // Critical: 120 min, warn at 25% left = after 90 business minutes (60 Thursday + 30 Sunday).
        $deadlines = $this->calculator()->deadlines(TicketPriority::Critical, Carbon::parse('2026-10-15 17:00'));

        $this->assertSame('2026-10-18 09:30:00', $deadlines['warning_at']->toDateTimeString());
    }

    public function test_a_ticket_created_after_hours_starts_counting_next_business_morning(): void
    {
        // Wednesday 20:00 -> clock starts Thursday 09:00 -> +2h = 11:00.
        $deadlines = $this->calculator()->deadlines(TicketPriority::Critical, Carbon::parse('2026-10-14 20:00'));

        $this->assertSame('2026-10-15 11:00:00', $deadlines['due_at']->toDateTimeString());
    }

    public function test_a_long_sla_spans_several_business_days(): void
    {
        // Medium: 24 business hours = 1440 min. Sunday 540 + Monday 540 + Tuesday 360 -> Tuesday 15:00.
        $deadlines = $this->calculator()->deadlines(TicketPriority::Medium, Carbon::parse('2026-10-18 09:00'));

        $this->assertSame('2026-10-20 15:00:00', $deadlines['due_at']->toDateTimeString());
    }

    public function test_holidays_are_not_counted(): void
    {
        // Sunday is a holiday, so the remaining hour moves to Monday 09:00-10:00.
        Holiday::create(['date' => '2026-10-18', 'name' => 'Test holiday']);

        $deadlines = $this->calculator()->deadlines(TicketPriority::Critical, Carbon::parse('2026-10-15 17:00'));

        $this->assertSame('2026-10-19 10:00:00', $deadlines['due_at']->toDateTimeString());
    }

    public function test_paused_minutes_push_the_deadline_later(): void
    {
        // 60 minutes spent in "Waiting for Customer" are added: 120 + 60 = 180 business minutes.
        $deadlines = $this->calculator()->deadlines(TicketPriority::Critical, Carbon::parse('2026-10-15 17:00'), pausedMinutes: 60);

        $this->assertSame('2026-10-18 11:00:00', $deadlines['due_at']->toDateTimeString());
    }

    public function test_business_minutes_between_skips_closed_days(): void
    {
        // Thursday 17:00 -> Sunday 10:00 is three calendar days but only 120 business minutes.
        $minutes = $this->calculator()->businessMinutesBetween(Carbon::parse('2026-10-15 17:00'), Carbon::parse('2026-10-18 10:00'));

        $this->assertSame(120, $minutes);
    }

    public function test_it_fails_clearly_when_no_business_hours_are_configured(): void
    {
        BusinessHour::query()->update(['is_working_day' => false]);

        $this->expectException(RuntimeException::class);

        $this->calculator()->addBusinessMinutes(Carbon::parse('2026-10-15 17:00'), 60);
    }
}
