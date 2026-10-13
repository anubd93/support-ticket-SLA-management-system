<?php

namespace App\Repositories\V1;

use App\Enums\V1\ActivityType;
use App\Models\V1\TicketActivity;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TicketActivityRepositoryV1
{
    private $id, $ticket_id, $user_id, $type, $old_values, $new_values, $created_at;

    // false = customers: "Internal note added" entries are left out.
    private bool $include_internal = true;

    public function setIncludeInternal(bool $include_internal)
    {
        $this->include_internal = $include_internal;
        return $this;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function setTicketId($ticket_id)
    {
        $this->ticket_id = $ticket_id;
        return $this;
    }

    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
        return $this;
    }

    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    public function setOldValues($old_values)
    {
        $this->old_values = $old_values;
        return $this;
    }

    public function setNewValues($new_values)
    {
        $this->new_values = $new_values;
        return $this;
    }

    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
        return $this;
    }

    public function store(): TicketActivity
    {
        return TicketActivity::create([
            'ticket_id' => $this->ticket_id,
            'user_id' => $this->user_id,
            'type' => $this->type,
            'old_values' => $this->old_values,
            'new_values' => $this->new_values,
        ]);
    }

    /**
     * Newest first, with the actor's name (null user = system).
     */
    public function getByTicketId(): Collection
    {
        return DB::table('ticket_activities')
            ->leftJoin('users', 'users.id', '=', 'ticket_activities.user_id')
            ->where('ticket_activities.ticket_id', $this->ticket_id)
            ->whereNull('ticket_activities.deleted_at')
            ->when(! $this->include_internal, fn ($query) => $query->where('ticket_activities.type', '!=', ActivityType::InternalNote))
            ->select(
                'ticket_activities.id',
                'ticket_activities.type',
                'ticket_activities.old_values',
                'ticket_activities.new_values',
                'ticket_activities.created_at',
                'users.name as user_name',
            )
            ->orderByDesc('ticket_activities.created_at')
            ->orderByDesc('ticket_activities.id')
            ->get();
    }
}
