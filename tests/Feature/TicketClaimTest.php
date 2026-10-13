<?php

namespace Tests\Feature;

use App\Enums\V1\ActivityType;
use App\Enums\V1\TicketStatus;
use App\Models\V1\Ticket;
use App\Repositories\V1\TicketRepositoryV1;
use App\Services\V1\TicketAssignmentServiceV1;
use Exception;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

/**
 * two agents accept the same unassigned ticket at about the same time;
 * it must never end up assigned to both. The claim is one atomic
 * UPDATE ... WHERE assigned_to IS NULL, so only the first agent's update changes a row.
 */
#[Group('ticket')]
class TicketClaimTest extends TestCase
{
    public function test_an_agent_can_accept_an_unassigned_ticket(): void
    {
        $agent = $this->agent();
        $ticket = Ticket::factory()->create();

        $this->actingAs($agent)
            ->postJson(route('tickets.claim', $ticket->id))
            ->assertOk();

        $ticket->refresh();
        $this->assertEquals($agent->id, $ticket->assigned_to);
        $this->assertSame(TicketStatus::InProgress, $ticket->status);
        $this->assertTrue($ticket->activities()->where('type', ActivityType::Assigned)->exists());
    }

    public function test_a_second_agent_cannot_accept_a_ticket_that_is_already_taken(): void
    {
        $first = $this->agent();
        $second = $this->agent();
        $ticket = Ticket::factory()->create();

        $this->actingAs($first)->postJson(route('tickets.claim', $ticket->id))->assertOk();

        $this->actingAs($second)
            ->postJson(route('tickets.claim', $ticket->id))
            ->assertForbidden()
            ->assertJsonFragment(['message' => "Already taken by {$first->name}."]);

        $this->assertEquals($first->id, $ticket->fresh()->assigned_to);
    }

    /**
     * The real race: both agents passed the page's checks while the ticket was still free,
     * then the first one's claim landed. The atomic update must reject the second.
     */
    public function test_the_atomic_claim_rejects_the_loser_of_a_race(): void
    {
        $winner = $this->agent();
        $loser = $this->agent();
        $ticket = Ticket::factory()->assignedTo($winner)->create();

        try {
            app(TicketAssignmentServiceV1::class)->claim($ticket->id, $loser->id);
            $this->fail('The second claim should have been rejected.');
        } catch (Exception $exception) {
            $this->assertSame("Already taken by {$winner->name}.", $exception->getMessage());
        }

        $this->assertEquals($winner->id, $ticket->fresh()->assigned_to);
    }

    public function test_the_conditional_update_changes_no_row_once_the_ticket_is_assigned(): void
    {
        $winner = $this->agent();
        $loser = $this->agent();
        $ticket = Ticket::factory()->assignedTo($winner)->create();

        $affected = app(TicketRepositoryV1::class)
            ->setId($ticket->id)
            ->setAssignedTo($loser->id)
            ->setAssignedAt(now())
            ->claimIfUnassigned();

        $this->assertSame(0, $affected);
    }

    public function test_customers_cannot_accept_tickets(): void
    {
        $customer = $this->customer();
        $ticket = Ticket::factory()->create(['created_by' => $customer->id]);

        $this->actingAs($customer)
            ->postJson(route('tickets.claim', $ticket->id))
            ->assertForbidden();

        $this->assertNull($ticket->fresh()->assigned_to);
    }
}
