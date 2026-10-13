<?php

namespace App\Repositories\V1;

use App\Enums\V1\TicketStatus;
use App\Enums\V1\UserRole;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class UserRepositoryV1
{
    private $id, $role_id, $name, $email, $email_verified_at, $password,
        $is_active, $is_available, $last_assigned_at, $created_at, $category_id;

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function setRoleId($role_id)
    {
        $this->role_id = $role_id;
        return $this;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    public function setEmailVerifiedAt($email_verified_at)
    {
        $this->email_verified_at = $email_verified_at;
        return $this;
    }

    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    public function setIsActive($is_active)
    {
        $this->is_active = $is_active;
        return $this;
    }

    public function setIsAvailable($is_available)
    {
        $this->is_available = $is_available;
        return $this;
    }

    public function setLastAssignedAt($last_assigned_at)
    {
        $this->last_assigned_at = $last_assigned_at;
        return $this;
    }

    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
        return $this;
    }

    private array $filters = [];

    public function setFilters(array $filters)
    {
        $this->filters = array_filter($filters, fn ($value) => $value !== null && $value !== '');
        return $this;
    }

    public function setCategoryId($category_id)
    {
        $this->category_id = $category_id;
        return $this;
    }

    public function updateLastAssignedAt(): int
    {
        return User::whereKey($this->id)->update(['last_assigned_at' => $this->last_assigned_at]);
    }

    public function getNotifiableByIds(array $ids): EloquentCollection
    {
        return User::query()
            ->whereIn('id', array_unique(array_filter($ids)))
            ->where('is_active', true)
            ->get();
    }

    public function getNotifiableAdmins(): EloquentCollection
    {
        return User::query()
            ->whereIn('role_id', DB::table('roles')->where('name', UserRole::Admin)->select('id'))
            ->where('is_active', true)
            ->get();
    }
    
    public function getActiveIdsByEmail(array $emails, UserRole $role): array
    {
        $emails = array_values(array_unique(array_filter(array_map('strtolower', $emails))));

        if (! $emails) {
            return [];
        }

        return DB::table('users as u')
            ->join('roles as r', 'r.id', '=', 'u.role_id')
            ->where('r.name', $role)
            ->where('u.is_active', true)
            ->whereNull('u.deleted_at')
            ->whereIn('u.email', $emails)
            ->pluck('u.id', 'u.email')
            ->mapWithKeys(fn ($id, $email) => [strtolower($email) => (int) $id])
            ->all();
    }

    public function getNameById(): ?string
    {
        return DB::table('users')->where('id', $this->id)->value('name');
    }

    public function findActiveAgent(): ?object
    {
        return $this->agentQuery()
            ->where('u.id', $this->id)
            ->select('u.id', 'u.name')
            ->first();
    }

    public function getAgents(): Collection
    {
        return $this->agentQuery()
            ->select('u.id', 'u.name', 'u.is_available')
            ->selectRaw('COUNT(t.id) AS open_count')
            ->groupBy('u.id', 'u.name', 'u.is_available')
            ->orderBy('u.name')
            ->get();
    }

    public function getAgentWorkload(Carbon $since): Collection
    {
        $now = now();
        $active = TicketStatus::active();
        $running = [TicketStatus::Open, TicketStatus::InProgress];
        $activeIn = implode(',', array_fill(0, count($active), '?'));
        $runningIn = implode(',', array_fill(0, count($running), '?'));

        return DB::table('users as u')
            ->join('roles as r', 'r.id', '=', 'u.role_id')
            ->leftJoin('tickets as t', function ($join) use ($active, $since) {
                $join->on('t.assigned_to', '=', 'u.id')
                    ->whereNull('t.deleted_at')
                    ->where(fn ($query) => $query->whereIn('t.status', $active)->orWhere('t.resolved_at', '>=', $since));
            })
            ->where('r.name', UserRole::Agent)
            ->where('u.is_active', true)
            ->whereNull('u.deleted_at')
            ->select('u.id', 'u.name', 'u.is_available')
            ->selectRaw("SUM(CASE WHEN t.status IN ($activeIn) THEN 1 ELSE 0 END) AS open_total", $active)
            ->selectRaw("SUM(CASE WHEN t.status IN ($runningIn) AND t.sla_due_at < ? THEN 1 ELSE 0 END) AS breached", [...$running, $now])
            ->selectRaw('SUM(CASE WHEN t.resolved_at >= ? THEN 1 ELSE 0 END) AS resolved_recent', [$since])
            ->groupBy('u.id', 'u.name', 'u.is_available')
            ->orderByDesc('open_total')
            ->orderBy('u.name')
            ->get();
    }

    public function getLeastLoadedAgentId(): ?int
    {
        $agentId = $this->agentQuery()
            ->join('category_user as cu', 'cu.user_id', '=', 'u.id')
            ->where('cu.category_id', $this->category_id)
            ->whereNull('cu.deleted_at')
            ->where('u.is_available', true)
            ->groupBy('u.id', 'u.last_assigned_at')
            ->orderByRaw('COUNT(t.id)')
            ->orderBy('u.last_assigned_at')
            ->orderBy('u.id')
            ->value('u.id');

        return $agentId ? (int) $agentId : null;
    }

    private function agentQuery(): Builder
    {
        return DB::table('users as u')
            ->join('roles as r', 'r.id', '=', 'u.role_id')
            ->leftJoin('tickets as t', function ($join) {
                $join->on('t.assigned_to', '=', 'u.id')
                    ->whereIn('t.status', TicketStatus::active())
                    ->whereNull('t.deleted_at');
            })
            ->where('r.name', UserRole::Agent)
            ->where('u.is_active', true)
            ->whereNull('u.deleted_at');
    }
    
    public function paginateUsers(int $perPage): LengthAwarePaginator
    {
        $filters = $this->filters;

        $openTickets = DB::table('tickets')
            ->selectRaw('COUNT(*)')
            ->whereColumn('tickets.assigned_to', 'u.id')
            ->whereIn('tickets.status', TicketStatus::active())
            ->whereNull('tickets.deleted_at');

        return DB::table('users as u')
            ->join('roles as r', 'r.id', '=', 'u.role_id')
            ->whereNull('u.deleted_at')
            ->when($filters['keyword'] ?? null, function ($query, $keyword) {
                $prefix = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $keyword) . '%';
                $query->where(fn ($query) => $query->where('u.name', 'like', $prefix)->orWhere('u.email', 'like', $prefix));
            })
            ->when($filters['role'] ?? null, fn ($query, $role) => $query->where('r.name', $role))
            ->select('u.id', 'u.name', 'u.email', 'u.is_active', 'u.is_available', 'u.created_at', 'r.name as role', 'r.display_name as role_name')
            ->selectSub($openTickets, 'open_tickets')
            ->orderBy('u.name')
            ->orderBy('u.id')
            ->paginate($perPage)
            ->withQueryString();
    }
    
    public function getCategoryNamesByUserIds(array $userIds): array
    {
        if (! $userIds) {
            return [];
        }

        return DB::table('category_user as cu')
            ->join('categories as c', 'c.id', '=', 'cu.category_id')
            ->whereIn('cu.user_id', $userIds)
            ->whereNull('cu.deleted_at')
            ->orderBy('c.name')
            ->get(['cu.user_id', 'c.name'])
            ->groupBy('user_id')
            ->map(fn ($rows) => $rows->pluck('name')->all())
            ->all();
    }

    public function findById(): ?object
    {
        return DB::table('users as u')
            ->join('roles as r', 'r.id', '=', 'u.role_id')
            ->where('u.id', $this->id)
            ->whereNull('u.deleted_at')
            ->select('u.id', 'u.role_id', 'u.name', 'u.email', 'u.is_active', 'u.is_available', 'r.name as role')
            ->first();
    }

    public function getCategoryIds(): array
    {
        return DB::table('category_user')
            ->where('user_id', $this->id)
            ->whereNull('deleted_at')
            ->pluck('category_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    public function store(): User
    {
        return User::create([
            'role_id' => $this->role_id,
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
            'is_active' => true,
            'is_available' => true,
        ]);
    }

    public function findModel(): User
    {
        return User::findOrFail($this->id);
    }
    
    public function update(User $user): User
    {
        $user->fill([
            'role_id' => $this->role_id,
            'name' => $this->name,
            'email' => $this->email,
        ]);

        if ($this->password) {
            $user->password = $this->password;
        }

        $user->save();

        return $user;
    }

    public function syncCategories(User $user, array $categoryIds): void
    {
        $user->categories()->sync($categoryIds);
    }
}
