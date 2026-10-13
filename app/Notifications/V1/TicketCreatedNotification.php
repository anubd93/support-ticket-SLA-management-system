<?php

namespace App\Notifications\V1;

class TicketCreatedNotification extends TicketNotification
{
    protected string $customerName;

    public function __construct(object $ticket, protected bool $forCustomer, array $channels = self::ALL_CHANNELS)
    {
        parent::__construct($ticket, $channels);

        $this->customerName = $ticket->creator_name;
    }

    protected function event(): string
    {
        return 'created';
    }

    protected function message(): string
    {
        return $this->forCustomer
            ? "We received your ticket {$this->ticketNumber}. Our team will get back to you soon."
            : "New ticket {$this->ticketNumber} from {$this->customerName}.";
    }

    protected function mailSubject(): string
    {
        return $this->forCustomer ? 'We received your ticket' : 'New ticket';
    }
}
