<?php

namespace App\Services\V1;

use App\Enums\V1\ActivityType;
use App\Enums\V1\TicketStatus;
use App\Events\V1\TicketAssigned;
use Exception;
use App\Models\V1\Ticket;
use App\Repositories\V1\TicketActivityRepositoryV1;
use App\Repositories\V1\TicketRepositoryV1;
use App\Repositories\V1\UserRepositoryV1;
use App\Services\V1\Assignment\AutoAssignmentStrategy;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TicketAssignmentServiceV1
{
    public function __construct(private readonly TicketRepositoryV1 $ticketRepositoryV1,
                                private readonly UserRepositoryV1 $userRepositoryV1,
                                private readonly TicketActivityRepositoryV1 $ticketActivityRepositoryV1,
                                private readonly AutoAssignmentStrategy $autoAssignmentStrategy
    ) {}
    
    public function autoAssign(Ticket $ticket, bool $notify = true): void
    {
        $agentId = $this->autoAssignmentStrategy->pickAgent((int) $ticket->category_id);

        if (! $agentId) {
            return;
        }

        $this->ticketRepositoryV1
            ->setAssignedTo($agentId)
            ->setAssignedAt(now())
            ->assign($ticket);

        $this->touchAgent($agentId);

        $this->logActivity($ticket->id, null, ActivityType::Assigned, null, [
            'agent_id' => $agentId,
            'agent' => $this->userRepositoryV1->setId($agentId)->getNameById(),
            'auto' => true,
        ]);

        if ($notify) {
            TicketAssigned::dispatch($ticket->id, $agentId, null);
        }
    }

    public function assignImported(Ticket $ticket, int $agentId, int $importerId): void
    {
        $this->ticketRepositoryV1
            ->setAssignedTo($agentId)
            ->setAssignedAt(now())
            ->assign($ticket);

        $this->touchAgent($agentId);

        $this->logActivity($ticket->id, $importerId, ActivityType::Assigned, null, [
            'agent_id' => $agentId,
            'agent' => $this->userRepositoryV1->setId($agentId)->getNameById(),
        ]);
    }

    public function assign(int $id, ?int $agentId, int $userId): Ticket
    {
        return DB::transaction(function () use ($id, $agentId, $userId) {
            $ticket = $this->ticketRepositoryV1->setId($id)->findForUpdate();

            if (! $ticket->status->isActive()) {
                throw new Exception('Resolved or closed tickets cannot be reassigned.');
            }

            $oldAgentId = $ticket->assigned_to ? (int) $ticket->assigned_to : null;

            if ($oldAgentId === $agentId) {
                throw new Exception($agentId ? 'The ticket is already assigned to this agent.' : 'The ticket is already unassigned.');
            }

            $agent = null;

            if ($agentId) {
                $agent = $this->userRepositoryV1->setId($agentId)->findActiveAgent();

                if (! $agent) {
                    throw new Exception('The selected user is not an active support agent.');
                }
            }

            $this->ticketRepositoryV1
                ->setAssignedTo($agentId)
                ->setAssignedAt($agentId ? now() : null)
                ->assign($ticket);

            $old = $oldAgentId
                ? ['agent_id' => $oldAgentId, 'agent' => $this->userRepositoryV1->setId($oldAgentId)->getNameById()]
                : null;

            if ($agent) {
                $this->touchAgent($agent->id);
                $this->logActivity($ticket->id, $userId, ActivityType::Assigned, $old, ['agent_id' => $agent->id, 'agent' => $agent->name]);
                TicketAssigned::dispatch($ticket->id, (int) $agent->id, $userId);
            } else {
                $this->logActivity($ticket->id, $userId, ActivityType::Unassigned, $old, null);
            }

            return $ticket;
        });
    }

    public function claim(int $id, int $agentId): Ticket
    {
        return DB::transaction(function () use ($id, $agentId) {
            $affected = $this->ticketRepositoryV1
                ->setId($id)
                ->setAssignedTo($agentId)
                ->setAssignedAt(now())
                ->claimIfUnassigned();

            if ($affected === 0) {
                $current = $this->ticketRepositoryV1->setId($id)->findById();

                throw new Exception($current?->assignee_name
                    ? "Already taken by {$current->assignee_name}."
                    : 'This ticket can no longer be accepted.');
            }
            
            $ticket = $this->ticketRepositoryV1->setId($id)->findForUpdate();

            $this->touchAgent($agentId);

            $this->logActivity($id, $agentId, ActivityType::Assigned, null, [
                'agent_id' => $agentId,
                'agent' => $this->userRepositoryV1->setId($agentId)->getNameById(),
                'claimed' => true,
            ]);
            
            TicketAssigned::dispatch($id, $agentId, $agentId);

            if ($ticket->status === TicketStatus::Open) {
                $this->ticketRepositoryV1->markInProgress($ticket);

                $this->logActivity($id, $agentId, ActivityType::StatusChanged,
                    ['status' => TicketStatus::Open->value], ['status' => TicketStatus::InProgress->value]);
            }

            return $ticket;
        });
    }

    public function findTicket(int $id): ?object
    {
        return $this->ticketRepositoryV1->setId($id)->findById();
    }

    public function getAgentOptions(): Collection
    {
        return $this->userRepositoryV1->getAgents();
    }

    private function touchAgent(int $agentId): void
    {
        $this->userRepositoryV1
            ->setId($agentId)
            ->setLastAssignedAt(now())
            ->updateLastAssignedAt();
    }

    private function logActivity(int $ticketId, ?int $userId, ActivityType $type, ?array $old, ?array $new): void
    {
        $this->ticketActivityRepositoryV1
            ->setTicketId($ticketId)
            ->setUserId($userId)
            ->setType($type)
            ->setOldValues($old)
            ->setNewValues($new)
            ->store();
    }
}
