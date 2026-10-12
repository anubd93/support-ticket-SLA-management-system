<?php

namespace App\Notifications\V1;

use Illuminate\Support\Carbon;

class SlaApproachingNotification extends TicketNotification
{
    protected string $dueAt;

    public function __construct(object $ticket, array $channels = self::ALL_CHANNELS)
    {
        parent::__construct($ticket, $channels);

        $this->dueAt = Carbon::parse($ticket->sla_due_at)->format('d M Y, H:i');
    }

    protected function event(): string
    {
        return 'sla_warning';
    }

    protected function message(): string
    {
        return "SLA approaching on {$this->ticketNumber}: due {$this->dueAt}.";
    }

    protected function mailSubject(): string
    {
        return 'SLA approaching';
    }
}
