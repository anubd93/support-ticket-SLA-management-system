<?php

namespace Tests\Feature;

use App\Models\V1\Ticket;
use App\Services\V1\TicketNumberServiceV1;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

/**
 * unique ticket numbers like TKT-2026-000001, from a per-year counter.
 */
#[Group('ticket')]
class TicketNumberTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_new_tickets_get_sequential_numbers_in_the_expected_format(): void
    {
        $customer = $this->customer();
        $year = now()->year;

        foreach (['First problem', 'Second problem'] as $subject) {
            $this->actingAs($customer)->postJson(route('tickets.store'), [
                'subject' => $subject,
                'description' => 'Details of the problem.',
                'category_id' => $this->category('billing')->id,
                'priority' => 'medium',
            ])->assertCreated();
        }

        $this->assertSame("TKT-{$year}-000001", Ticket::where('subject', 'First problem')->value('ticket_number'));
        $this->assertSame("TKT-{$year}-000002", Ticket::where('subject', 'Second problem')->value('ticket_number'));
    }

    public function test_the_counter_starts_again_each_year(): void
    {
        $numbers = app(TicketNumberServiceV1::class);

        Carbon::setTestNow('2026-12-31 10:00');
        $this->assertSame('TKT-2026-000001', $numbers->generate());
        $this->assertSame('TKT-2026-000002', $numbers->generate());

        Carbon::setTestNow('2027-01-01 10:00');
        $this->assertSame('TKT-2027-000001', $numbers->generate());
    }

    public function test_numbers_are_never_repeated(): void
    {
        $numbers = app(TicketNumberServiceV1::class);

        $generated = collect(range(1, 50))->map(fn () => $numbers->generate());

        $this->assertCount(50, $generated->unique());
    }
}
