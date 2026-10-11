<?php

namespace App\Repositories\V1;

use App\Enums\V1\TicketStatus;
use App\Models\V1\Ticket;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class TicketRepositoryV1
{
    private $id, $ticket_number, $subject, $description, $priority, $status, $source,
        $external_reference, $category_id, $created_by, $assigned_to, $assigned_at,
        $sla_due_at, $sla_warning_at, $sla_paused_at, $sla_paused_minutes, $first_responded_at,
        $sla_warning_sent_at, $sla_breached_at, $escalated_at, $escalation_level,
        $resolved_at, $closed_at, $lock_version, $created_at;

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function setTicketNumber($ticket_number)
    {
        $this->ticket_number = $ticket_number;
        return $this;
    }

    public function setSubject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    public function setPriority($priority)
    {
        $this->priority = $priority;
        return $this;
    }

    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    public function setSource($source)
    {
        $this->source = $source;
        return $this;
    }

    public function setExternalReference($external_reference)
    {
        $this->external_reference = $external_reference;
        return $this;
    }

    public function setCategoryId($category_id)
    {
        $this->category_id = $category_id;
        return $this;
    }

    public function setCreatedBy($created_by)
    {
        $this->created_by = $created_by;
        return $this;
    }

    public function setAssignedTo($assigned_to)
    {
        $this->assigned_to = $assigned_to;
        return $this;
    }

    public function setAssignedAt($assigned_at)
    {
        $this->assigned_at = $assigned_at;
        return $this;
    }

    public function setSlaDueAt($sla_due_at)
    {
        $this->sla_due_at = $sla_due_at;
        return $this;
    }

    public function setSlaWarningAt($sla_warning_at)
    {
        $this->sla_warning_at = $sla_warning_at;
        return $this;
    }

    public function setSlaPausedAt($sla_paused_at)
    {
        $this->sla_paused_at = $sla_paused_at;
        return $this;
    }

    public function setSlaPausedMinutes($sla_paused_minutes)
    {
        $this->sla_paused_minutes = $sla_paused_minutes;
        return $this;
    }

    public function setFirstRespondedAt($first_responded_at)
    {
        $this->first_responded_at = $first_responded_at;
        return $this;
    }

    public function setSlaWarningSentAt($sla_warning_sent_at)
    {
        $this->sla_warning_sent_at = $sla_warning_sent_at;
        return $this;
    }

    public function setSlaBreachedAt($sla_breached_at)
    {
        $this->sla_breached_at = $sla_breached_at;
        return $this;
    }

    public function setEscalatedAt($escalated_at)
    {
        $this->escalated_at = $escalated_at;
        return $this;
    }

    public function setEscalationLevel($escalation_level)
    {
        $this->escalation_level = $escalation_level;
        return $this;
    }

    public function setResolvedAt($resolved_at)
    {
        $this->resolved_at = $resolved_at;
        return $this;
    }

    public function setClosedAt($closed_at)
    {
        $this->closed_at = $closed_at;
        return $this;
    }

    public function setLockVersion($lock_version)
    {
        $this->lock_version = $lock_version;
        return $this;
    }

    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
        return $this;
    }

    public function store(): Ticket
    {
        return Ticket::create([
            'ticket_number' => $this->ticket_number,
            'subject' => $this->subject,
            'description' => $this->description,
            'priority' => $this->priority,
            'status' => $this->status,
            'source' => $this->source,
            'category_id' => $this->category_id,
            'created_by' => $this->created_by,
            'sla_due_at' => $this->sla_due_at,
            'sla_warning_at' => $this->sla_warning_at,
        ]);
    }

    public function findForUpdate(): Ticket
    {
        return Ticket::lockForUpdate()->findOrFail($this->id);
    }

    public function updateDetails(Ticket $ticket): Ticket
    {
        $ticket->update([
            'subject' => $this->subject,
            'description' => $this->description,
            'category_id' => $this->category_id,
            'priority' => $this->priority,
            'sla_due_at' => $this->sla_due_at,
            'sla_warning_at' => $this->sla_warning_at,
        ]);

        return $ticket;
    }

    public function updateStatus(Ticket $ticket): Ticket
    {
        $ticket->update([
            'status' => $this->status,
            'resolved_at' => $this->resolved_at,
            'closed_at' => $this->closed_at,
            'sla_due_at' => $this->sla_due_at,
            'sla_warning_at' => $this->sla_warning_at,
            'sla_paused_at' => $this->sla_paused_at,
            'sla_paused_minutes' => $this->sla_paused_minutes,
        ]);

        return $ticket;
    }

    public function findById(): ?object
    {
        return $this->baseQuery()
            ->addSelect('t.description', 't.category_id', 't.source')
            ->where('t.id', $this->id)
            ->first();
    }

    public function getActive(int $perPage): LengthAwarePaginator
    {
        return $this->baseQuery()
            ->whereIn('t.status', TicketStatus::active())
            ->orderByDesc('t.created_at')
            ->orderByDesc('t.id')
            ->paginate($perPage);
    }

    public function getFinished(int $perPage): LengthAwarePaginator
    {
        return $this->baseQuery()
            ->whereIn('t.status', TicketStatus::finished())
            ->orderByRaw('COALESCE(t.closed_at, t.resolved_at) DESC')
            ->orderByDesc('t.id')
            ->paginate($perPage);
    }

    public function countByGroup(): array
    {
        $active = TicketStatus::active();

        $row = DB::table('tickets')
            ->whereNull('deleted_at')
            ->selectRaw(
                'SUM(CASE WHEN status IN (' . implode(',', array_fill(0, count($active), '?')) . ') THEN 1 ELSE 0 END) AS active_count, COUNT(*) AS total_count',
                $active
            )
            ->first();

        $activeCount = (int) $row->active_count;

        return ['active' => $activeCount, 'finished' => (int) $row->total_count - $activeCount];
    }

    private function baseQuery(): Builder
    {
        return DB::table('tickets as t')
            ->join('categories as c', 'c.id', '=', 't.category_id')
            ->join('users as u', 'u.id', '=', 't.created_by')
            ->leftJoin('users as a', 'a.id', '=', 't.assigned_to')
            ->whereNull('t.deleted_at')
            ->select(
                't.id',
                't.ticket_number',
                't.subject',
                't.priority',
                't.status',
                't.assigned_to',
                't.sla_due_at',
                't.sla_warning_at',
                't.sla_paused_at',
                't.sla_paused_minutes',
                't.resolved_at',
                't.closed_at',
                't.created_at',
                'c.name as category_name',
                'u.name as creator_name',
                'u.email as creator_email',
                'a.name as assignee_name',
            );
    }
}
