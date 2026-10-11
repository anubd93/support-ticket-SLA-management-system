<?php

namespace App\Services\V1;

use App\Enums\V1\ActivityType;
use App\Enums\V1\TicketPriority;
use App\Enums\V1\TicketSource;
use App\Enums\V1\TicketStatus;
use App\Exceptions\V1\TicketException;
use App\Models\V1\Ticket;
use App\Repositories\V1\CategoryRepositoryV1;
use App\Repositories\V1\TicketActivityRepositoryV1;
use App\Repositories\V1\TicketRepositoryV1;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class TicketServiceV1
{
    public function __construct(private readonly TicketRepositoryV1 $ticketRepositoryV1,
                                private readonly CategoryRepositoryV1 $categoryRepositoryV1,
                                private readonly TicketActivityRepositoryV1 $ticketActivityRepositoryV1,
                                private readonly TicketNumberServiceV1 $ticketNumberServiceV1,
                                private readonly SlaCalculatorServiceV1 $slaCalculatorServiceV1
    ) {}

    public function create(array $data, int $userId): Ticket
    {
        return DB::transaction(function () use ($data, $userId) {
            $priority = TicketPriority::from($data['priority']);

            $ticket = $this->ticketRepositoryV1
                ->setTicketNumber($this->ticketNumberServiceV1->generate())
                ->setSubject($data['subject'])
                ->setDescription($data['description'])
                ->setCategoryId($data['category_id'])
                ->setPriority($priority->value)
                ->setStatus(TicketStatus::Open->value)
                ->setSource(TicketSource::Web->value)
                ->setCreatedBy($userId)
                ->setSlaDueAt($this->slaCalculatorServiceV1->dueAt($priority, now()))
                ->store();

            $this->logActivity($ticket->id, $userId, ActivityType::Created, null, [
                'ticket_number' => $ticket->ticket_number,
                'priority' => $priority->value,
            ]);

            return $ticket;
        });
    }

    public function update(int $id, array $data, int $userId): Ticket
    {
        return DB::transaction(function () use ($id, $data, $userId) {
            $ticket = $this->ticketRepositoryV1->setId($id)->findForUpdate();

            if ($ticket->status === TicketStatus::Closed) {
                throw new TicketException('Closed tickets cannot be edited.');
            }

            $oldPriority = $ticket->priority;
            $newPriority = TicketPriority::from($data['priority']);
            $oldCategoryId = (int) $ticket->category_id;
            $newCategoryId = (int) $data['category_id'];
            $changedFields = array_keys(array_filter([
                'subject' => $ticket->subject !== $data['subject'],
                'description' => $ticket->description !== $data['description'],
            ]));
            
            $slaDueAt = $newPriority === $oldPriority
                ? $ticket->sla_due_at
                : $this->slaCalculatorServiceV1->dueAt($newPriority, $ticket->created_at);

            $this->ticketRepositoryV1
                ->setSubject($data['subject'])
                ->setDescription($data['description'])
                ->setCategoryId($newCategoryId)
                ->setPriority($newPriority->value)
                ->setSlaDueAt($slaDueAt)
                ->updateDetails($ticket);

            if ($newPriority !== $oldPriority) {
                $this->logActivity($ticket->id, $userId, ActivityType::PriorityChanged,
                    ['priority' => $oldPriority->value], ['priority' => $newPriority->value]);
            }

            if ($newCategoryId !== $oldCategoryId) {
                $this->logActivity($ticket->id, $userId, ActivityType::CategoryChanged,
                    ['category' => $this->categoryRepositoryV1->setId($oldCategoryId)->getNameById()],
                    ['category' => $this->categoryRepositoryV1->setId($newCategoryId)->getNameById()]);
            }

            if ($changedFields) {
                $this->logActivity($ticket->id, $userId, ActivityType::Updated, null, ['fields' => $changedFields]);
            }

            return $ticket;
        });
    }

    public function changeStatus(int $id, string $status, int $userId): Ticket
    {
        return DB::transaction(function () use ($id, $status, $userId) {
            $ticket = $this->ticketRepositoryV1->setId($id)->findForUpdate();
            $from = $ticket->status;
            $to = TicketStatus::from($status);

            if (! $from->canTransitionTo($to)) {
                throw new TicketException("A ticket that is {$from->label()} cannot be changed to {$to->label()}.");
            }

            $resolvedAt = $ticket->resolved_at;
            $closedAt = $ticket->closed_at;

            if ($to === TicketStatus::Resolved) {
                $resolvedAt = now();
            } elseif ($to === TicketStatus::Closed) {
                $closedAt = now();
            } elseif ($from === TicketStatus::Resolved) {
                $resolvedAt = null;
            }

            $this->ticketRepositoryV1
                ->setStatus($to->value)
                ->setResolvedAt($resolvedAt)
                ->setClosedAt($closedAt)
                ->updateStatus($ticket);

            $type = match (true) {
                $to === TicketStatus::Resolved => ActivityType::Resolved,
                $to === TicketStatus::Closed => ActivityType::Closed,
                $from === TicketStatus::Resolved => ActivityType::Reopened,
                default => ActivityType::StatusChanged,
            };

            $this->logActivity($ticket->id, $userId, $type, ['status' => $from->value], ['status' => $to->value]);

            return $ticket;
        });
    }

    public function getActiveTickets(): LengthAwarePaginator
    {
        // dd($this->ticketRepositoryV1->getActive(config('tickets.per_page')));
        return $this->ticketRepositoryV1->getActive(config('tickets.per_page'));
    }

    public function getFinishedTickets(): LengthAwarePaginator
    {
        return $this->ticketRepositoryV1->getFinished(config('tickets.per_page'));
    }

    public function getTabCounts(): array
    {
        return $this->ticketRepositoryV1->countByGroup();
    }

    public function findTicket(int $id): ?object
    {
        return $this->ticketRepositoryV1->setId($id)->findById();
    }

    public function getActivities(int $ticketId)
    {
        return $this->ticketActivityRepositoryV1->setTicketId($ticketId)->getByTicketId();
    }

    public function getFormOptions(): array
    {
        return [
            'categories' => $this->categoryRepositoryV1->getActive(),
            'slaHours' => $this->slaCalculatorServiceV1->resolutionHours(),
        ];
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
