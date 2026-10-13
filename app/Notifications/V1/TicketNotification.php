<?php

namespace App\Notifications\V1;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

abstract class TicketNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public const ALL_CHANNELS = ['mail', 'database'];

    public int $tries = 3;

    public array $backoff = [10, 60];

    protected int $ticketId;

    protected string $ticketNumber;

    protected string $ticketSubject;

    public function __construct(object $ticket, protected array $channels = self::ALL_CHANNELS)
    {
        $this->ticketId = (int) $ticket->id;
        $this->ticketNumber = $ticket->ticket_number;
        $this->ticketSubject = $ticket->subject;

        $this->afterCommit();
    }
    
    abstract protected function event(): string;
    
    abstract protected function message(): string;

    abstract protected function mailSubject(): string;

    public function via(object $notifiable): array
    {
        return $this->channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("[{$this->ticketNumber}] {$this->mailSubject()}")
            ->greeting("Hello {$notifiable->name},")
            ->line($this->message())
            ->line("Subject: {$this->ticketSubject}")
            ->action('View ticket', route('tickets.show', $this->ticketId));
    }
    
    public function toArray(object $notifiable): array
    {
        return [
            'ticket_id' => $this->ticketId,
            'ticket_number' => $this->ticketNumber,
            'event' => $this->event(),
            'message' => $this->message(),
        ];
    }
}
