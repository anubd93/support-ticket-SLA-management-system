<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\V1\Ticket;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

/**
 * a new ticket goes to an active, available agent with the ticket's category skill,
 * the one with the fewest open tickets; ties go to the agent who waited longest (round-robin).
 * No eligible agent = the ticket stays unassigned.
 */
#[Group('ticket')]
class AutoAssignmentTest extends TestCase
{
    private function createBillingTicket(User $customer, string $subject = 'Charged twice'): Ticket
    {
        $this->actingAs($customer)->postJson(route('tickets.store'), [
            'subject' => $subject,
            'description' => 'Please check my invoice.',
            'category_id' => $this->category('billing')->id,
            'priority' => 'medium',
        ])->assertCreated();

        return Ticket::where('subject', $subject)->firstOrFail();
    }

    public function test_the_agent_with_the_fewest_open_tickets_gets_the_new_ticket(): void
    {
        $busy = $this->agent(['billing']);
        $free = $this->agent(['billing']);
        Ticket::factory()->count(2)->assignedTo($busy)->create();

        $ticket = $this->createBillingTicket($this->customer());

        $this->assertEquals($free->id, $ticket->assigned_to);
    }

    public function test_on_a_tie_the_agent_assigned_longest_ago_wins(): void
    {
        $recent = $this->agent(['billing']);
        $waiting = $this->agent(['billing']);
        User::whereKey($recent->id)->update(['last_assigned_at' => now()->subMinutes(5)]);
        User::whereKey($waiting->id)->update(['last_assigned_at' => now()->subDays(2)]);

        $ticket = $this->createBillingTicket($this->customer());

        $this->assertEquals($waiting->id, $ticket->assigned_to);
    }

    public function test_tickets_rotate_between_equally_loaded_agents(): void
    {
        $first = $this->agent(['billing']);
        $second = $this->agent(['billing']);
        $customer = $this->customer();

        $one = $this->createBillingTicket($customer, 'Problem one');
        $two = $this->createBillingTicket($customer, 'Problem two');

        $this->assertEqualsCanonicalizing([$first->id, $second->id], [$one->assigned_to, $two->assigned_to]);
    }

    public function test_agents_without_the_category_skill_are_skipped(): void
    {
        $this->agent(['technical']);

        $ticket = $this->createBillingTicket($this->customer());

        $this->assertNull($ticket->assigned_to);
    }

    public function test_unavailable_agents_get_no_new_tickets(): void
    {
        $away = User::factory()->agent()->unavailable()->create();
        $away->categories()->sync([$this->category('billing')->id]);

        $ticket = $this->createBillingTicket($this->customer());

        $this->assertNull($ticket->assigned_to);
    }

    public function test_inactive_agents_get_no_new_tickets(): void
    {
        $inactive = $this->agent(['billing']);
        User::whereKey($inactive->id)->update(['is_active' => false]);

        $ticket = $this->createBillingTicket($this->customer());

        $this->assertNull($ticket->assigned_to);
    }

    public function test_the_ticket_stays_unassigned_when_no_agent_exists(): void
    {
        $ticket = $this->createBillingTicket($this->customer());

        $this->assertNull($ticket->assigned_to);
        $this->assertTrue($ticket->activities()->exists());
    }
}
