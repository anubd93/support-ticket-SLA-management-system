<?php

namespace App\Repositories\V1;

use App\Enums\V1\TicketPriority;
use App\Enums\V1\TicketStatus;
use App\Models\V1\Ticket;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TicketRepositoryV1
{
    private $id, $ticket_number, $subject, $description, $priority, $status, $source,
        $external_reference, $category_id, $created_by, $assigned_to, $assigned_at,
        $sla_due_at, $sla_warning_at, $sla_paused_at, $sla_paused_minutes, $first_responded_at,
        $sla_warning_sent_at, $sla_breached_at, $escalated_at, $escalation_level,
        $resolved_at, $closed_at, $lock_version, $created_at;

    private ?int $only_created_by = null;

    public function setOnlyCreatedBy(?int $only_created_by)
    {
        $this->only_created_by = $only_created_by;
        return $this;
    }
    
    private ?int $only_assigned_to = null;

    // Validated search/filter values (TicketFilterRequestV1), applied to every list query.
    private array $filters = [];

    public function setFilters(array $filters)
    {
        $this->filters = array_filter($filters, fn ($value) => $value !== null && $value !== '');
        return $this;
    }

    public function setOnlyAssignedTo(?int $only_assigned_to)
    {
        $this->only_assigned_to = $only_assigned_to;
        return $this;
    }

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
            'external_reference' => $this->external_reference,
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

    public function assign(Ticket $ticket): Ticket
    {
        $ticket->update([
            'assigned_to' => $this->assigned_to,
            'assigned_at' => $this->assigned_at,
        ]);

        return $ticket;
    }

    public function claimIfUnassigned(): int
    {
        return Ticket::whereKey($this->id)
            ->whereNull('assigned_to')
            ->whereIn('status', TicketStatus::active())
            ->update([
                'assigned_to' => $this->assigned_to,
                'assigned_at' => $this->assigned_at,
            ]);
    }

    public function markFirstResponse(Ticket $ticket): Ticket
    {
        $ticket->update(['first_responded_at' => $this->first_responded_at]);

        return $ticket;
    }

    public function markInProgress(Ticket $ticket): Ticket
    {
        $ticket->update(['status' => TicketStatus::InProgress]);

        return $ticket;
    }

    public function markBreached(): int
    {
        return Ticket::whereKey($this->id)
            ->whereNull('sla_breached_at')
            ->update([
                'sla_breached_at' => $this->sla_breached_at,
                'sla_warning_sent_at' => DB::raw('COALESCE(sla_warning_sent_at, sla_breached_at)'),
                'escalated_at' => $this->escalated_at,
                'escalation_level' => $this->escalation_level,
            ]);
    }

    public function markWarningSent(): int
    {
        return Ticket::whereKey($this->id)
            ->whereNull('sla_warning_sent_at')
            ->whereNull('sla_breached_at')
            ->update([
                'sla_warning_sent_at' => $this->sla_warning_sent_at,
                'escalated_at' => $this->escalated_at,
                'escalation_level' => $this->escalation_level,
            ]);
    }

    public function updateEscalation(Ticket $ticket): Ticket
    {
        $ticket->update([
            'sla_warning_sent_at' => $this->sla_warning_sent_at,
            'sla_breached_at' => $this->sla_breached_at,
            'escalation_level' => $this->escalation_level,
        ]);

        return $ticket;
    }

    public function chunkDueForBreach(int $size, callable $callback): void
    {
        $this->escalationQuery()
            ->where('sla_due_at', '<', now())
            ->whereNull('sla_breached_at')
            ->chunkById($size, $callback);
    }

    public function chunkDueForWarning(int $size, callable $callback): void
    {
        $this->escalationQuery()
            ->where('sla_warning_at', '<=', now())
            ->whereNull('sla_warning_sent_at')
            ->whereNull('sla_breached_at')
            ->chunkById($size, $callback);
    }

    private function escalationQuery(): Builder
    {
        return DB::table('tickets')
            ->whereIn('status', [TicketStatus::Open, TicketStatus::InProgress])
            ->whereNull('deleted_at')
            ->select('id', 'sla_due_at', 'sla_warning_at');
    }
    
    public function getExistingExternalReferences(array $references): array
    {
        if (! $references) {
            return [];
        }

        return DB::table('tickets')
            ->whereIn('external_reference', $references)
            ->pluck('ticket_number', 'external_reference')
            ->all();
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
            ->paginate($perPage)
            ->withQueryString();
    }

    public function getFinished(int $perPage): LengthAwarePaginator
    {
        return $this->baseQuery()
            ->whereIn('t.status', TicketStatus::finished())
            ->orderByRaw('COALESCE(t.closed_at, t.resolved_at) DESC')
            ->orderByDesc('t.id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function getUnassigned(int $perPage): LengthAwarePaginator
    {
        return $this->baseQuery()
            ->whereNull('t.assigned_to')
            ->whereIn('t.status', TicketStatus::active())
            ->orderBy('t.sla_due_at')
            ->orderBy('t.id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function countByGroup(): array
    {
        $active = TicketStatus::active();
        $placeholders = implode(',', array_fill(0, count($active), '?'));

        $row = DB::table('tickets')
            ->whereNull('deleted_at')
            ->when($this->only_created_by, fn ($query, $userId) => $query->where('created_by', $userId))
            ->selectRaw(
                "SUM(CASE WHEN status IN ($placeholders) THEN 1 ELSE 0 END) AS active_count,
                 SUM(CASE WHEN status IN ($placeholders) AND assigned_to IS NULL THEN 1 ELSE 0 END) AS unassigned_count,
                 COUNT(*) AS total_count",
                array_merge($active, $active)
            )
            ->first();

        $activeCount = (int) $row->active_count;

        return [
            'active' => $activeCount,
            'unassigned' => (int) $row->unassigned_count,
            'finished' => (int) $row->total_count - $activeCount,
        ];
    }

    public function getDashboardCounts(?int $agentId, Carbon $since): object
    {
        $now = now();
        $active = TicketStatus::active();
        $running = [TicketStatus::Open, TicketStatus::InProgress];
        $activeIn = implode(',', array_fill(0, count($active), '?'));
        $runningIn = implode(',', array_fill(0, count($running), '?'));
        $finishedAt = 'COALESCE(resolved_at, closed_at)';

        return DB::table('tickets')
            ->whereNull('deleted_at')
            ->when($this->only_created_by, fn ($query, $userId) => $query->where('created_by', $userId))
            ->selectRaw("SUM(CASE WHEN status IN ($activeIn) THEN 1 ELSE 0 END) AS open_total", $active)
            ->selectRaw("SUM(CASE WHEN status IN ($activeIn) AND assigned_to IS NULL THEN 1 ELSE 0 END) AS unassigned", $active)
            ->selectRaw("SUM(CASE WHEN status IN ($activeIn) AND priority = ? THEN 1 ELSE 0 END) AS critical", [...$active, TicketPriority::Critical])
            ->selectRaw("SUM(CASE WHEN status IN ($runningIn) AND sla_due_at < ? THEN 1 ELSE 0 END) AS breached", [...$running, $now])
            ->selectRaw("SUM(CASE WHEN status IN ($runningIn) AND sla_warning_at <= ? AND sla_due_at >= ? THEN 1 ELSE 0 END) AS approaching", [...$running, $now, $now])
            ->selectRaw("SUM(CASE WHEN status IN ($activeIn) AND escalation_level > 0 THEN 1 ELSE 0 END) AS escalated", $active)
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) AS waiting', [TicketStatus::WaitingForCustomer])
            ->selectRaw("SUM(CASE WHEN $finishedAt >= ? THEN 1 ELSE 0 END) AS finished_recent", [$since])
            ->selectRaw("SUM(CASE WHEN $finishedAt >= ? AND $finishedAt <= sla_due_at THEN 1 ELSE 0 END) AS met_recent", [$since])
            ->selectRaw("SUM(CASE WHEN status IN ($activeIn) AND assigned_to = ? THEN 1 ELSE 0 END) AS mine", [...$active, $agentId ?? 0])
            ->first();
    }

    public function countActiveByPriority(): array
    {
        return DB::table('tickets')
            ->whereNull('deleted_at')
            ->whereIn('status', TicketStatus::active())
            ->when($this->only_created_by, fn ($query, $userId) => $query->where('created_by', $userId))
            ->groupBy('priority')
            ->selectRaw('priority, COUNT(*) AS total')
            ->pluck('total', 'priority')
            ->map(fn ($total) => (int) $total)
            ->all();
    }

    public function getNeedsAttention(int $limit): Collection
    {
        return $this->baseQuery()
            ->whereIn('t.status', [TicketStatus::Open, TicketStatus::InProgress])
            ->where('t.sla_warning_at', '<=', now())
            ->orderBy('t.sla_due_at')
            ->limit($limit)
            ->get();
    }

    public function getRecentlyUpdated(int $limit): Collection
    {
        return $this->baseQuery()
            ->orderByDesc('t.updated_at')
            ->orderByDesc('t.id')
            ->limit($limit)
            ->get();
    }

    private function baseQuery(): Builder
    {
        return DB::table('tickets as t')
            ->join('categories as c', 'c.id', '=', 't.category_id')
            ->join('users as u', 'u.id', '=', 't.created_by')
            ->leftJoin('users as a', 'a.id', '=', 't.assigned_to')
            ->whereNull('t.deleted_at')
            ->when($this->only_created_by, fn ($query, $userId) => $query->where('t.created_by', $userId))
            ->when($this->only_assigned_to, fn ($query, $agentId) => $query->where('t.assigned_to', $agentId))
            ->tap(fn ($query) => $this->applyFilters($query))
            ->select(
                't.id',
                't.created_by',
                't.ticket_number',
                't.subject',
                't.priority',
                't.status',
                't.assigned_to',
                't.sla_due_at',
                't.sla_warning_at',
                't.sla_paused_at',
                't.sla_paused_minutes',
                't.escalation_level',
                't.escalated_at',
                't.resolved_at',
                't.closed_at',
                't.created_at',
                'c.name as category_name',
                'u.name as creator_name',
                'u.email as creator_email',
                'a.name as assignee_name',
            );
    }

    private function applyFilters(Builder $query): void
    {
        $filters = $this->filters;

        if (isset($filters['keyword'])) {
            $this->applyKeyword($query, $filters['keyword']);
        }

        $query
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('t.status', $status))
            ->when($filters['priority'] ?? null, fn ($query, $priority) => $query->where('t.priority', $priority))
            ->when($filters['category_id'] ?? null, fn ($query, $categoryId) => $query->where('t.category_id', $categoryId))
            ->when($filters['agent_id'] ?? null, fn ($query, $agentId) => $agentId === 'none'
                ? $query->whereNull('t.assigned_to')
                : $query->where('t.assigned_to', $agentId))
            ->when($filters['customer'] ?? null, function ($query, $customer) {
                $prefix = $this->escapeLike($customer) . '%';
                $query->where(fn ($query) => $query->where('u.name', 'like', $prefix)->orWhere('u.email', 'like', $prefix));
            })
            ->when($filters['from'] ?? null, fn ($query, $from) => $query->where('t.created_at', '>=', $from . ' 00:00:00'))
            ->when($filters['to'] ?? null, fn ($query, $to) => $query->where('t.created_at', '<', date('Y-m-d', strtotime($to . ' +1 day'))));

        if (isset($filters['sla'])) {
            $this->applySla($query, $filters['sla']);
        }
    }

    private function applyKeyword(Builder $query, string $term): void
    {
        $term = trim($term);

        if (preg_match('/^([A-Za-z]+)-(\d{4})-(\d+)$/', $term, $parts)) {
            $query->where('t.ticket_number', strtoupper($parts[1]) . '-' . $parts[2] . '-' . $this->padNumber($parts[3]));

            return;
        }

        if (ctype_digit($term)) {
            $query->where('t.ticket_number', 'like', '%-' . $this->padNumber($term));

            return;
        }

        $words = preg_split('/\s+/', preg_replace('/[+\-<>()~*"@]+/', ' ', $term), -1, PREG_SPLIT_NO_EMPTY);
        
        $indexable = array_filter($words, fn ($word) => mb_strlen($word) >= 3);

        if (! $indexable) {
            $query->where('t.subject', 'like', $this->escapeLike($term) . '%');

            return;
        }
        
        $query->whereFullText(
            ['t.subject', 't.description'],
            implode(' ', array_map(fn ($word) => '+' . $word . '*', $indexable)),
            ['mode' => 'boolean']
        );
    }

    private function applySla(Builder $query, string $sla): void
    {
        $now = now();
        $running = [TicketStatus::Open, TicketStatus::InProgress];

        match ($sla) {
            'breached' => $query->whereIn('t.status', $running)->where('t.sla_due_at', '<', $now),
            'approaching' => $query->whereIn('t.status', $running)
                ->where('t.sla_warning_at', '<=', $now)
                ->where('t.sla_due_at', '>=', $now),
            'within' => $query->whereIn('t.status', $running)
                ->where('t.sla_due_at', '>=', $now)
                ->where(fn ($query) => $query->whereNull('t.sla_warning_at')->orWhere('t.sla_warning_at', '>', $now)),
            'paused' => $query->where('t.status', TicketStatus::WaitingForCustomer),
            'escalated' => $query->where('t.escalation_level', '>', 0),
            'met' => $query->whereRaw('COALESCE(t.resolved_at, t.closed_at) <= t.sla_due_at'),
            'missed' => $query->whereRaw('COALESCE(t.resolved_at, t.closed_at) > t.sla_due_at'),
            default => null,
        };
    }

    private function padNumber(string $digits): string
    {
        return str_pad(ltrim($digits, '0') ?: '0', config('tickets.number_padding'), '0', STR_PAD_LEFT);
    }

    private function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }
}
