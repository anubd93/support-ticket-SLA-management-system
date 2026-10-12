<?php

namespace App\Repositories\V1;

use App\Enums\V1\TicketStatus;
use App\Enums\V1\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Query\Builder;
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
}
