<?php

namespace App\Notifications\V1;

use Illuminate\Support\Carbon;

class SlaBreachedNotification extends TicketNotification
{
    protected string $dueAt;

    public function __construct(object $ticket, array $channels = self::ALL_CHANNELS)
    {
        parent::__construct($ticket, $channels);

        $this->dueAt = Carbon::parse($ticket->sla_due_at)->format('d M Y, H:i');
    }

    protected function event(): string
    {
        return 'sla_breached';
    }

    protected function message(): string
    {
        return "SLA breached on {$this->ticketNumber}: it was due {$this->dueAt} and has been escalated.";
    }

    protected function mailSubject(): string
    {
        return 'SLA breached, ticket escalated';
    }
}
