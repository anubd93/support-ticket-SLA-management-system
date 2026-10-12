<?php

namespace App\Notifications\V1;

class TicketAssignedNotification extends TicketNotification
{
    public function __construct(object $ticket, protected ?string $assignedBy, array $channels = self::ALL_CHANNELS)
    {
        parent::__construct($ticket, $channels);
    }

    protected function event(): string
    {
        return 'assigned';
    }

    protected function message(): string
    {
        return $this->assignedBy
            ? "{$this->assignedBy} assigned ticket {$this->ticketNumber} to you."
            : "Ticket {$this->ticketNumber} was automatically assigned to you.";
    }

    protected function mailSubject(): string
    {
        return 'Ticket assigned to you';
    }
}
