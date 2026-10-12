<?php

namespace App\Services\V1;

use App\Enums\V1\ActivityType;
use App\Enums\V1\TicketStatus;
use App\Models\User;
use App\Models\V1\Ticket;
use App\Repositories\V1\TicketActivityRepositoryV1;
use App\Repositories\V1\TicketReplyRepositoryV1;
use App\Repositories\V1\TicketRepositoryV1;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TicketReplyServiceV1
{
    public function __construct(private readonly TicketReplyRepositoryV1 $ticketReplyRepositoryV1,
                                private readonly TicketRepositoryV1 $ticketRepositoryV1,
                                private readonly TicketActivityRepositoryV1 $ticketActivityRepositoryV1,
                                private readonly TicketServiceV1 $ticketServiceV1
    ) {}

    public function create(int $ticketId, array $data, User $user): array
    {
        return DB::transaction(function () use ($ticketId, $data, $user) {
            $ticket = $this->ticketRepositoryV1->setId($ticketId)->findForUpdate();

            if ($ticket->status === TicketStatus::Closed) {
                throw new Exception('Closed tickets cannot receive replies.');
            }

            $isInternal = (bool) ($data['is_internal'] ?? false);

            $reply = $this->ticketReplyRepositoryV1
                ->setTicketId($ticketId)
                ->setUserId($user->id)
                ->setBody($data['body'])
                ->setIsInternal($isInternal)
                ->store();

            $this->logActivity($ticketId, $user->id, $isInternal ? ActivityType::InternalNote : ActivityType::Replied, [
                'reply_id' => $reply->id,
            ]);

            // Internal notes are staff-only work: no first response, no status or SLA change.
            $statusChanged = false;

            if (! $isInternal) {
                $statusChanged = $user->isCustomer()
                    ? $this->afterCustomerReply($ticket, $user)
                    : $this->afterStaffReply($ticket, $user);
            }

            return [
                'reply' => $this->ticketReplyRepositoryV1->setId($reply->id)->findById(),
                'status_changed' => $statusChanged,
            ];
        });
    }

    public function getThread(int $ticketId, User $user): Collection
    {
        return $this->ticketReplyRepositoryV1
            ->setTicketId($ticketId)
            ->setIncludeInternal($user->hasPermission('ticket.internal_note'))
            ->getByTicketId();
    }

    public function findTicket(int $id): ?object
    {
        return $this->ticketRepositoryV1->setId($id)->findById();
    }

    private function afterStaffReply(Ticket $ticket, User $user): bool
    {
        if (! $ticket->first_responded_at) {
            $this->ticketRepositoryV1->setFirstRespondedAt(now())->markFirstResponse($ticket);
        }

        if ($ticket->status !== TicketStatus::Open) {
            return false;
        }

        $this->ticketServiceV1->changeStatus($ticket->id, TicketStatus::InProgress->value, $user->id);

        return true;
    }

    private function afterCustomerReply(Ticket $ticket, User $user): bool
    {
        if (! in_array($ticket->status, [TicketStatus::WaitingForCustomer, TicketStatus::Resolved], true)) {
            return false;
        }

        $this->ticketServiceV1->changeStatus($ticket->id, TicketStatus::InProgress->value, $user->id);

        return true;
    }

    private function logActivity(int $ticketId, ?int $userId, ActivityType $type, ?array $new): void
    {
        $this->ticketActivityRepositoryV1
            ->setTicketId($ticketId)
            ->setUserId($userId)
            ->setType($type)
            ->setOldValues(null)
            ->setNewValues($new)
            ->store();
    }
}
