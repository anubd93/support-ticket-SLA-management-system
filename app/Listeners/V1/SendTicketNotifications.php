<?php

namespace App\Listeners\V1;

use App\Enums\V1\TicketStatus;
use App\Events\V1\SlaApproaching;
use App\Events\V1\SlaBreached;
use App\Events\V1\TicketAssigned;
use App\Events\V1\TicketCreated;
use App\Events\V1\TicketReplied;
use App\Events\V1\TicketStatusChanged;
use App\Notifications\V1\SlaApproachingNotification;
use App\Notifications\V1\SlaBreachedNotification;
use App\Notifications\V1\TicketAssignedNotification;
use App\Notifications\V1\TicketCreatedNotification;
use App\Notifications\V1\TicketRepliedNotification;
use App\Notifications\V1\TicketResolvedNotification;
use App\Repositories\V1\TicketRepositoryV1;
use App\Repositories\V1\UserRepositoryV1;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Notification;

class SendTicketNotifications
{
    public function __construct(private readonly TicketRepositoryV1 $ticketRepositoryV1,
                                private readonly UserRepositoryV1 $userRepositoryV1
    ) {}

    public function handleTicketCreated(TicketCreated $event): void
    {
        $ticket = $this->ticketRepositoryV1->setId($event->ticketId)->findById();

        if (! $ticket) {
            return;
        }

        Notification::send(
            $this->userRepositoryV1->getNotifiableByIds([$ticket->created_by]),
            new TicketCreatedNotification($ticket, forCustomer: true)
        );

        Notification::send(
            $this->userRepositoryV1->getNotifiableAdmins(),
            new TicketCreatedNotification($ticket, forCustomer: false, channels: ['database'])
        );
    }

    public function handleTicketAssigned(TicketAssigned $event): void
    {
        if ($event->agentId === $event->actorId) {
            return;
        }

        $ticket = $this->ticketRepositoryV1->setId($event->ticketId)->findById();

        if (! $ticket) {
            return;
        }

        $assignedBy = $event->actorId ? $this->userRepositoryV1->setId($event->actorId)->getNameById() : null;

        Notification::send(
            $this->userRepositoryV1->getNotifiableByIds([$event->agentId]),
            new TicketAssignedNotification($ticket, $assignedBy)
        );
    }
    
    public function handleTicketReplied(TicketReplied $event): void
    {
        $ticket = $this->ticketRepositoryV1->setId($event->ticketId)->findById();

        if (! $ticket) {
            return;
        }

        $author = $this->userRepositoryV1->setId($event->actorId)->getNameById() ?? 'Someone';
        $assignedTo = $ticket->assigned_to ? (int) $ticket->assigned_to : null;

        $byCustomer = $event->actorId === (int) $ticket->created_by;

        if ($event->isInternal) {
            Notification::send(
                $this->recipients([$assignedTo], $event->actorId),
                new TicketRepliedNotification($ticket, $author, isInternal: true, channels: ['database'])
            );

            return;
        }

        if (! $byCustomer) {
            Notification::send(
                $this->recipients([$ticket->created_by], $event->actorId),
                new TicketRepliedNotification($ticket, $author, isInternal: false)
            );

            return;
        }

        if ($assignedTo) {
            Notification::send(
                $this->recipients([$assignedTo], $event->actorId),
                new TicketRepliedNotification($ticket, $author, isInternal: false)
            );

            return;
        }

        Notification::send(
            $this->userRepositoryV1->getNotifiableAdmins(),
            new TicketRepliedNotification($ticket, $author, isInternal: false, channels: ['database'])
        );
    }
    
    public function handleTicketStatusChanged(TicketStatusChanged $event): void
    {
        if ($event->to !== TicketStatus::Resolved->value) {
            return;
        }

        $ticket = $this->ticketRepositoryV1->setId($event->ticketId)->findById();

        if (! $ticket) {
            return;
        }

        $resolvedBy = $this->userRepositoryV1->setId($event->actorId)->getNameById() ?? 'our team';

        Notification::send(
            $this->recipients([$ticket->created_by], $event->actorId),
            new TicketResolvedNotification($ticket, $resolvedBy)
        );
    }
    
    public function handleSlaApproaching(SlaApproaching $event): void
    {
        $ticket = $this->ticketRepositoryV1->setId($event->ticketId)->findById();

        if (! $ticket) {
            return;
        }

        // Staff only: the assigned agent, or the admins while nobody owns the ticket.
        $recipients = $ticket->assigned_to
            ? $this->userRepositoryV1->getNotifiableByIds([$ticket->assigned_to])
            : $this->userRepositoryV1->getNotifiableAdmins();

        Notification::send($recipients, new SlaApproachingNotification($ticket));
    }

    public function handleSlaBreached(SlaBreached $event): void
    {
        $ticket = $this->ticketRepositoryV1->setId($event->ticketId)->findById();

        if (! $ticket) {
            return;
        }

        // Staff only: a breach goes to the assigned agent AND all admins.
        $recipients = $this->userRepositoryV1->getNotifiableAdmins()
            ->merge($this->userRepositoryV1->getNotifiableByIds([$ticket->assigned_to]))
            ->unique('id');

        Notification::send($recipients, new SlaBreachedNotification($ticket));
    }

    private function recipients(array $ids, ?int $exceptId): Collection
    {
        return $this->userRepositoryV1
            ->getNotifiableByIds($ids)
            ->reject(fn ($user) => $user->id === $exceptId);
    }
}
