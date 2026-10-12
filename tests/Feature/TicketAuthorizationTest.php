<?php

namespace Tests\Feature;

use App\Models\V1\Ticket;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

/**
 * customers only reach their own tickets, every role only does what it may,
 * and all of it is enforced on the server (direct requests, not just hidden buttons).
 */
#[Group('ticket')]
class TicketAuthorizationTest extends TestCase
{
    public function test_guests_are_sent_to_the_login_page(): void
    {
        $this->get(route('tickets.index'))->assertRedirect(route('login'));
    }

    public function test_a_customer_can_open_their_own_ticket(): void
    {
        $customer = $this->customer();
        $ticket = Ticket::factory()->create(['created_by' => $customer->id]);

        $this->actingAs($customer)
            ->get(route('tickets.show', $ticket->id))
            ->assertOk()
            ->assertSee($ticket->subject);
    }

    public function test_a_customer_gets_404_for_someone_elses_ticket(): void
    {
        $ticket = Ticket::factory()->create();

        // 404 rather than 403, so customers can't even learn which ticket ids exist.
        $this->actingAs($this->customer())
            ->get(route('tickets.show', $ticket->id))
            ->assertNotFound();
    }

    public function test_a_customer_only_sees_their_own_tickets_in_the_list(): void
    {
        $customer = $this->customer();
        $own = Ticket::factory()->create(['created_by' => $customer->id, 'subject' => 'My printer is broken']);
        $other = Ticket::factory()->create(['subject' => 'Someone else needs a refund']);

        $this->actingAs($customer)
            ->get(route('tickets.index'))
            ->assertOk()
            ->assertSee($own->subject)
            ->assertDontSee($other->subject);
    }

    public function test_a_customer_cannot_edit_their_ticket(): void
    {
        $customer = $this->customer();
        $ticket = Ticket::factory()->create(['created_by' => $customer->id]);

        $this->actingAs($customer)
            ->putJson(route('tickets.update', $ticket->id), [
                'subject' => 'Changed by the customer',
                'description' => 'Trying to change it anyway.',
                'category_id' => $this->category('billing')->id,
                'priority' => 'critical',
            ])
            ->assertForbidden();

        $this->assertNotSame('Changed by the customer', $ticket->fresh()->subject);
    }

    public function test_a_customer_cannot_change_the_status(): void
    {
        $customer = $this->customer();
        $ticket = Ticket::factory()->create(['created_by' => $customer->id]);

        $this->actingAs($customer)
            ->patchJson(route('tickets.status', $ticket->id), ['status' => 'resolved'])
            ->assertForbidden();
    }

    public function test_a_customer_cannot_add_an_internal_note(): void
    {
        $customer = $this->customer();
        $ticket = Ticket::factory()->create(['created_by' => $customer->id]);

        $this->actingAs($customer)
            ->postJson(route('tickets.replies.store', $ticket->id), ['body' => 'Sneaky note', 'is_internal' => 1])
            ->assertForbidden();

        $this->assertDatabaseMissing('ticket_replies', ['ticket_id' => $ticket->id]);
    }

    public function test_an_agent_can_open_any_ticket(): void
    {
        $ticket = Ticket::factory()->create();

        $this->actingAs($this->agent())
            ->get(route('tickets.show', $ticket->id))
            ->assertOk();
    }

    public function test_only_customers_can_create_tickets(): void
    {
        $this->actingAs($this->customer())->get(route('tickets.create'))->assertOk();
        $this->actingAs($this->agent())->get(route('tickets.create'))->assertForbidden();
        $this->actingAs($this->admin())->get(route('tickets.create'))->assertForbidden();
    }

    public function test_admin_pages_are_closed_to_agents_and_customers(): void
    {
        foreach ([$this->agent(), $this->customer()] as $user) {
            $this->actingAs($user)->get(route('users.index'))->assertForbidden();
            $this->actingAs($user)->get(route('imports.index'))->assertForbidden();
        }

        $admin = $this->admin();
        $this->actingAs($admin)->get(route('users.index'))->assertOk();
        $this->actingAs($admin)->get(route('imports.index'))->assertOk();
    }
}
