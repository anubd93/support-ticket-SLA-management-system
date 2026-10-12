<?php

namespace Tests\Feature;

use App\Models\V1\Attachment;
use App\Models\V1\Ticket;
use App\Notifications\V1\TicketRepliedNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

/**
 * "Internal notes must never be visible to customers."
 * Checked in every place a customer could see one: the conversation, the activity log,
 * a file attached to the note, and notifications.
 */
#[Group('ticket')]
class InternalNotesVisibilityTest extends TestCase
{
    private const NOTE = 'Refund approved internally, do not tell the customer yet';

    public function test_a_customer_never_sees_an_internal_note_but_staff_do(): void
    {
        $customer = $this->customer();
        $agent = $this->agent();
        $ticket = Ticket::factory()->create(['created_by' => $customer->id]);

        $this->actingAs($agent)
            ->postJson(route('tickets.replies.store', $ticket->id), ['body' => self::NOTE, 'is_internal' => 1])
            ->assertCreated();

        // Not in the conversation and not in the activity log.
        $this->actingAs($customer)
            ->get(route('tickets.show', $ticket->id))
            ->assertOk()
            ->assertDontSee(self::NOTE)
            ->assertDontSee('Internal note added');

        $this->actingAs($agent)
            ->get(route('tickets.show', $ticket->id))
            ->assertSee(self::NOTE);
    }

    public function test_a_public_reply_is_visible_to_the_customer(): void
    {
        $customer = $this->customer();
        $ticket = Ticket::factory()->create(['created_by' => $customer->id]);

        $this->actingAs($this->agent())
            ->postJson(route('tickets.replies.store', $ticket->id), ['body' => 'We are looking into it.'])
            ->assertCreated();

        $this->actingAs($customer)
            ->get(route('tickets.show', $ticket->id))
            ->assertSee('We are looking into it.');
    }

    public function test_a_customer_cannot_download_a_file_attached_to_an_internal_note(): void
    {
        Storage::fake('local');

        $customer = $this->customer();
        $agent = $this->agent();
        $ticket = Ticket::factory()->create(['created_by' => $customer->id]);

        $this->actingAs($agent)
            ->withHeader('Accept', 'application/json')
            ->post(route('tickets.replies.store', $ticket->id), [
                'body' => self::NOTE,
                'is_internal' => 1,
                'attachments' => [UploadedFile::fake()->create('investigation.pdf', 20, 'application/pdf')],
            ])
            ->assertCreated();

        $attachment = Attachment::where('ticket_id', $ticket->id)->firstOrFail();

        // 404, same as a ticket the customer can't see: no hint that the file exists.
        $this->actingAs($customer)->get(route('attachments.download', $attachment->id))->assertNotFound();
        $this->actingAs($agent)->get(route('attachments.download', $attachment->id))->assertOk();
    }

    public function test_the_customer_is_not_notified_about_an_internal_note(): void
    {
        $customer = $this->customer();
        $ticket = Ticket::factory()->create(['created_by' => $customer->id]);

        $this->actingAs($this->agent())
            ->postJson(route('tickets.replies.store', $ticket->id), ['body' => self::NOTE, 'is_internal' => 1])
            ->assertCreated();

        Notification::assertNotSentTo($customer, TicketRepliedNotification::class);
    }
}
