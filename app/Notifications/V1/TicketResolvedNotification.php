<?php

namespace App\Notifications\V1;

class TicketResolvedNotification extends TicketNotification
{
    public function __construct(object $ticket, protected string $resolvedBy, array $channels = self::ALL_CHANNELS)
    {
        parent::__construct($ticket, $channels);
    }

    protected function event(): string
    {
        return 'resolved';
    }

    protected function message(): string
    {
        return "Your ticket {$this->ticketNumber} was resolved by {$this->resolvedBy}. Reply if the problem is not fixed.";
    }

    protected function mailSubject(): string
    {
        return 'Your ticket was resolved';
    }
}
