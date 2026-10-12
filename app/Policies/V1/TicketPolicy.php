<?php

namespace App\Policies\V1;

use App\Enums\V1\TicketStatus;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TicketPolicy
{
    public function view(User $user, object $ticket): Response
    {
        return $this->canSee($user, $ticket)
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    public function update(User $user, object $ticket): Response
    {
        if (! $this->canSee($user, $ticket)) {
            return Response::denyAsNotFound();
        }

        if (! $user->hasPermission('ticket.update')) {
            return Response::deny('You are not allowed to edit tickets.');
        }

        return $this->status($ticket) === TicketStatus::Closed
            ? Response::deny('Closed tickets cannot be edited.')
            : Response::allow();
    }

    public function changeStatus(User $user, object $ticket): Response
    {
        if (! $this->canSee($user, $ticket)) {
            return Response::denyAsNotFound();
        }

        return $user->hasPermission('ticket.change_status')
            ? Response::allow()
            : Response::deny('You are not allowed to change the ticket status.');
    }

    public function assign(User $user, object $ticket): Response
    {
        if (! $this->canSee($user, $ticket)) {
            return Response::denyAsNotFound();
        }

        if (! $user->hasPermission('ticket.assign')) {
            return Response::deny('You are not allowed to assign tickets.');
        }

        return $this->status($ticket)->isActive()
            ? Response::allow()
            : Response::deny('Resolved or closed tickets cannot be reassigned.');
    }

    /**
     * Pre-check for the Accept button. The real race is settled by the atomic update in the service.
     */
    public function claim(User $user, object $ticket): Response
    {
        if (! $this->canSee($user, $ticket)) {
            return Response::denyAsNotFound();
        }

        if (! $user->hasPermission('ticket.claim')) {
            return Response::deny('You are not allowed to accept tickets.');
        }

        if ($ticket->assigned_to) {
            return Response::deny('Already taken by ' . ($ticket->assignee_name ?? 'another agent') . '.');
        }

        return $this->status($ticket)->isActive()
            ? Response::allow()
            : Response::deny('Resolved or closed tickets cannot be accepted.');
    }

    private function canSee(User $user, object $ticket): bool
    {
        if ($user->hasPermission('ticket.view_all')) {
            return true;
        }

        return $user->hasPermission('ticket.view_own') && (int) $ticket->created_by === (int) $user->id;
    }

    private function status(object $ticket): TicketStatus
    {
        return $ticket->status instanceof TicketStatus ? $ticket->status : TicketStatus::from($ticket->status);
    }
}
