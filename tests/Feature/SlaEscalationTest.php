<?php

namespace Tests\Feature;

use App\Enums\V1\ActivityType;
use App\Enums\V1\TicketStatus;
use App\Models\V1\Ticket;
use App\Notifications\V1\SlaApproachingNotification;
use App\Notifications\V1\SlaBreachedNotification;
use App\Services\V1\EscalationServiceV1;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

/**
 * tickets approaching or past their SLA are found and escalated automatically
 * (tickets:check-sla, scheduled every minute). Level 1 = approaching, level 2 = breached,
 * each stage exactly once; paused and finished tickets are never escalated.
 */
#[Group('sla')]
class SlaEscalationTest extends TestCase
{
    private function escalate(): array
    {
        return app(EscalationServiceV1::class)->run();
    }

    public function test_a_ticket_past_its_warning_point_is_escalated_to_level_1(): void
    {
        $agent = $this->agent();
        $ticket = Ticket::factory()->assignedTo($agent)->create([
            'sla_warning_at' => now()->subMinute(),
            'sla_due_at' => now()->addHour(),
        ]);

        $this->assertSame(['breached' => 0, 'approaching' => 1], $this->escalate());

        $ticket->refresh();
        $this->assertSame(1, $ticket->escalation_level);
        $this->assertNotNull($ticket->sla_warning_sent_at);
        $this->assertTrue($ticket->activities()->where('type', ActivityType::SlaWarning)->exists());
        Notification::assertSentTo($agent, SlaApproachingNotification::class);
    }

    public function test_a_ticket_past_its_deadline_is_escalated_to_level_2_and_admins_are_told(): void
    {
        $admin = $this->admin();
        $agent = $this->agent();
        $ticket = Ticket::factory()->assignedTo($agent)->create([
            'sla_warning_at' => now()->subHours(2),
            'sla_due_at' => now()->subMinute(),
        ]);

        $this->assertSame(['breached' => 1, 'approaching' => 0], $this->escalate());

        $ticket->refresh();
        $this->assertSame(2, $ticket->escalation_level);
        $this->assertNotNull($ticket->sla_breached_at);
        // Already late when found: one breach escalation, no late "approaching" one.
        $this->assertFalse($ticket->activities()->where('type', ActivityType::SlaWarning)->exists());
        Notification::assertSentTo([$agent, $admin], SlaBreachedNotification::class);
        Notification::assertNotSentTo($ticket->creator, SlaBreachedNotification::class);
    }

    public function test_running_the_check_again_escalates_nothing_twice(): void
    {
        Ticket::factory()->create(['sla_warning_at' => now()->subHours(2), 'sla_due_at' => now()->subMinute()]);

        $this->escalate();

        $this->assertSame(['breached' => 0, 'approaching' => 0], $this->escalate());
    }

    public function test_tickets_waiting_for_the_customer_are_not_escalated(): void
    {
        $ticket = Ticket::factory()->status(TicketStatus::WaitingForCustomer)->create([
            'sla_warning_at' => now()->subHours(2),
            'sla_due_at' => now()->subMinute(),
        ]);

        $this->assertSame(['breached' => 0, 'approaching' => 0], $this->escalate());
        $this->assertSame(0, $ticket->fresh()->escalation_level);
    }

    public function test_resolved_tickets_are_not_escalated(): void
    {
        Ticket::factory()->status(TicketStatus::Resolved)->create([
            'sla_warning_at' => now()->subHours(2),
            'sla_due_at' => now()->subMinute(),
        ]);

        $this->assertSame(['breached' => 0, 'approaching' => 0], $this->escalate());
    }

    public function test_the_scheduled_command_runs_the_check(): void
    {
        Ticket::factory()->create(['sla_warning_at' => now()->subHours(2), 'sla_due_at' => now()->subMinute()]);

        $this->artisan('tickets:check-sla')
            ->expectsOutputToContain('1 breached')
            ->assertSuccessful();
    }
}
