<?php

namespace App\Notifications\V1;

class TicketRepliedNotification extends TicketNotification
{
    public function __construct(object $ticket, protected string $authorName, protected bool $isInternal, array $channels = self::ALL_CHANNELS)
    {
        parent::__construct($ticket, $channels);
    }

    protected function event(): string
    {
        return $this->isInternal ? 'internal_note' : 'replied';
    }

    protected function message(): string
    {
        return $this->isInternal
            ? "{$this->authorName} added an internal note on {$this->ticketNumber}."
            : "{$this->authorName} replied on {$this->ticketNumber}.";
    }

    protected function mailSubject(): string
    {
        return $this->isInternal ? "Internal note from {$this->authorName}" : "New reply from {$this->authorName}";
    }
}
