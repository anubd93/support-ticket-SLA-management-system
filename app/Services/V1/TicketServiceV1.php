<?php

namespace App\Services\V1;

use App\Enums\V1\ActivityType;
use App\Enums\V1\SlaStatus;
use App\Enums\V1\TicketPriority;
use App\Enums\V1\TicketSource;
use App\Enums\V1\TicketStatus;
use Exception;
use App\Models\User;
use App\Models\V1\Ticket;
use App\Repositories\V1\CategoryRepositoryV1;
use App\Repositories\V1\TicketActivityRepositoryV1;
use App\Repositories\V1\TicketRepositoryV1;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TicketServiceV1
{
    public function __construct(private readonly TicketRepositoryV1 $ticketRepositoryV1,
                                private readonly CategoryRepositoryV1 $categoryRepositoryV1,
                                private readonly TicketActivityRepositoryV1 $ticketActivityRepositoryV1,
                                private readonly TicketNumberServiceV1 $ticketNumberServiceV1,
                                private readonly SlaCalculatorServiceV1 $slaCalculatorServiceV1,
                                private readonly TicketAssignmentServiceV1 $ticketAssignmentServiceV1
    ) {}

    public function create(array $data, int $userId): Ticket
    {
        return DB::transaction(function () use ($data, $userId) {
            $priority = TicketPriority::from($data['priority']);
            $deadlines = $this->slaCalculatorServiceV1->deadlines($priority, now());

            $ticket = $this->ticketRepositoryV1
                ->setTicketNumber($this->ticketNumberServiceV1->generate())
                ->setSubject($data['subject'])
                ->setDescription($data['description'])
                ->setCategoryId($data['category_id'])
                ->setPriority($priority->value)
                ->setStatus(TicketStatus::Open->value)
                ->setSource(TicketSource::Web->value)
                ->setCreatedBy($userId)
                ->setSlaDueAt($deadlines['due_at'])
                ->setSlaWarningAt($deadlines['warning_at'])
                ->store();

            $this->logActivity($ticket->id, $userId, ActivityType::Created, null, [
                'ticket_number' => $ticket->ticket_number,
                'priority' => $priority->value,
            ]);

            $this->ticketAssignmentServiceV1->autoAssign($ticket);

            return $ticket;
        });
    }

    public function update(int $id, array $data, int $userId): Ticket
    {
        return DB::transaction(function () use ($id, $data, $userId) {
            $ticket = $this->ticketRepositoryV1->setId($id)->findForUpdate();

            if ($ticket->status === TicketStatus::Closed) {
                throw new Exception('Closed tickets cannot be edited.');
            }

            $oldPriority = $ticket->priority;
            $newPriority = TicketPriority::from($data['priority']);
            $oldCategoryId = (int) $ticket->category_id;
            $newCategoryId = (int) $data['category_id'];
            $changedFields = array_keys(array_filter([
                'subject' => $ticket->subject !== $data['subject'],
                'description' => $ticket->description !== $data['description'],
            ]));
            
            $deadlines = $newPriority === $oldPriority
                ? ['due_at' => $ticket->sla_due_at, 'warning_at' => $ticket->sla_warning_at]
                : $this->slaCalculatorServiceV1->deadlines($newPriority, $ticket->created_at, (int) $ticket->sla_paused_minutes);

            $this->ticketRepositoryV1
                ->setSubject($data['subject'])
                ->setDescription($data['description'])
                ->setCategoryId($newCategoryId)
                ->setPriority($newPriority->value)
                ->setSlaDueAt($deadlines['due_at'])
                ->setSlaWarningAt($deadlines['warning_at'])
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
                throw new Exception("A ticket that is {$from->label()} cannot be changed to {$to->label()}.");
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
            
            $pausedAt = $ticket->sla_paused_at;
            $pausedMinutes = (int) $ticket->sla_paused_minutes;
            $deadlines = ['due_at' => $ticket->sla_due_at, 'warning_at' => $ticket->sla_warning_at];

            if ($to->pausesSla() && ! $from->pausesSla()) {
                $pausedAt = now();
            } elseif ($from->pausesSla() && ! $to->pausesSla()) {
                if ($pausedAt) {
                    $pausedMinutes += $this->slaCalculatorServiceV1->businessMinutesBetween($pausedAt, now());
                }
                $pausedAt = null;
                $deadlines = $this->slaCalculatorServiceV1->deadlines($ticket->priority, $ticket->created_at, $pausedMinutes);
            }

            $this->ticketRepositoryV1
                ->setStatus($to->value)
                ->setResolvedAt($resolvedAt)
                ->setClosedAt($closedAt)
                ->setSlaPausedAt($pausedAt)
                ->setSlaPausedMinutes($pausedMinutes)
                ->setSlaDueAt($deadlines['due_at'])
                ->setSlaWarningAt($deadlines['warning_at'])
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

    public function getActiveTickets(User $user): LengthAwarePaginator
    {
        return $this->scopedTo($user)->getActive(config('tickets.per_page'));
    }

    public function getFinishedTickets(User $user): LengthAwarePaginator
    {
        return $this->scopedTo($user)->getFinished(config('tickets.per_page'));
    }

    public function getUnassignedTickets(User $user): LengthAwarePaginator
    {
        return $this->scopedTo($user)->getUnassigned(config('tickets.per_page'));
    }

    public function getMyQueue(User $user): LengthAwarePaginator
    {
        return $this->scopedTo($user, $user->id)->getActive(config('tickets.per_page'));
    }

    public function getTabCounts(User $user): array
    {
        return $this->scopedTo($user)->countByGroup();
    }

    private function scopedTo(User $user, ?int $assignedTo = null): TicketRepositoryV1
    {
        return $this->ticketRepositoryV1
            ->setOnlyCreatedBy($user->hasPermission('ticket.view_all') ? null : $user->id)
            ->setOnlyAssignedTo($assignedTo);
    }

    public function findTicket(int $id): ?object
    {
        return $this->ticketRepositoryV1->setId($id)->findById();
    }

    public function getActivities(int $ticketId, User $user)
    {
        return $this->ticketActivityRepositoryV1
            ->setTicketId($ticketId)
            ->setIncludeInternal($user->hasPermission('ticket.internal_note'))
            ->getByTicketId();
    }

    public function getSlaSummary(object $ticket): array
    {
        $status = TicketStatus::from($ticket->status);
        $policy = $this->slaCalculatorServiceV1->policyFor(TicketPriority::from($ticket->priority));
        $dueAt = $ticket->sla_due_at ? Carbon::parse($ticket->sla_due_at) : null;
        $warningAt = $ticket->sla_warning_at ? Carbon::parse($ticket->sla_warning_at) : null;
        $pausedAt = $ticket->sla_paused_at ? Carbon::parse($ticket->sla_paused_at) : null;
        $totalMinutes = $policy ? (int) $policy->resolution_minutes : null;

        if (! $dueAt) {
            return ['state' => null, 'policy_minutes' => $totalMinutes];
        }

        if (! $status->isActive()) {
            $finishedAt = Carbon::parse($ticket->resolved_at ?? $ticket->closed_at);

            return [
                'state' => $finishedAt->lte($dueAt) ? 'met' : 'missed',
                'policy_minutes' => $totalMinutes,
                'due_at' => $dueAt,
                'paused_minutes' => (int) $ticket->sla_paused_minutes,
            ];
        }
        
        $countFrom = $pausedAt ?? now();
        $remaining = $this->slaCalculatorServiceV1->businessMinutesBetween($countFrom, $dueAt);
        $overdue = $dueAt->lt($countFrom) ? $this->slaCalculatorServiceV1->businessMinutesBetween($dueAt, $countFrom) : 0;

        $state = match (true) {
            $status->pausesSla() => SlaStatus::Paused,
            $dueAt->lt($countFrom) => SlaStatus::Breached,
            $warningAt && $warningAt->lte(now()) => SlaStatus::Approaching,
            default => SlaStatus::Within,
        };

        return [
            'state' => $state,
            'policy_minutes' => $totalMinutes,
            'due_at' => $dueAt,
            'remaining_minutes' => $remaining,
            'overdue_minutes' => $overdue,
            'used_percent' => $totalMinutes ? min(100, (int) round(($totalMinutes - $remaining) / $totalMinutes * 100)) : null,
            'paused_minutes' => (int) $ticket->sla_paused_minutes,
        ];
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
