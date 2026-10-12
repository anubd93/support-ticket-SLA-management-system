<?php

namespace App\Repositories\V1;

use App\Models\V1\TicketReply;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TicketReplyRepositoryV1
{
    private $id, $ticket_id, $user_id, $body, $is_internal, $created_at;

    // false = customers: internal notes are filtered out in SQL, never loaded at all.
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

    public function setBody($body)
    {
        $this->body = $body;
        return $this;
    }

    public function setIsInternal($is_internal)
    {
        $this->is_internal = $is_internal;
        return $this;
    }

    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
        return $this;
    }

    public function store(): TicketReply
    {
        return TicketReply::create([
            'ticket_id' => $this->ticket_id,
            'user_id' => $this->user_id,
            'body' => $this->body,
            'is_internal' => $this->is_internal,
        ]);
    }

    public function findById(): ?object
    {
        return $this->baseQuery()->where('r.id', $this->id)->first();
    }

    /**
     * Oldest first, with the author's name and role for the badge.
     */
    public function getByTicketId(): Collection
    {
        return $this->baseQuery()
            ->where('r.ticket_id', $this->ticket_id)
            ->when(! $this->include_internal, fn ($query) => $query->where('r.is_internal', false))
            ->orderBy('r.created_at')
            ->orderBy('r.id')
            ->get();
    }

    private function baseQuery(): Builder
    {
        return DB::table('ticket_replies as r')
            ->join('users as u', 'u.id', '=', 'r.user_id')
            ->leftJoin('roles as ro', 'ro.id', '=', 'u.role_id')
            ->whereNull('r.deleted_at')
            ->select(
                'r.id',
                'r.ticket_id',
                'r.body',
                'r.is_internal',
                'r.created_at',
                'u.name as user_name',
                'ro.name as user_role',
            );
    }
}
