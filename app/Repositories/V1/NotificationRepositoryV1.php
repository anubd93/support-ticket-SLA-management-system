<?php

namespace App\Repositories\V1;

use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class NotificationRepositoryV1
{
    private $id, $notifiable_id;

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function setNotifiableId($notifiable_id)
    {
        $this->notifiable_id = $notifiable_id;
        return $this;
    }

    public function getLatest(int $limit): Collection
    {
        return $this->baseQuery()
            ->select('id', 'data', 'read_at', 'created_at')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public function countUnread(): int
    {
        return $this->baseQuery()->whereNull('read_at')->count();
    }

    public function findForUser(): ?object
    {
        return $this->baseQuery()->where('id', $this->id)->select('id', 'data', 'read_at')->first();
    }

    public function markAsRead(): int
    {
        return $this->eloquentQuery()->whereKey($this->id)->whereNull('read_at')->update(['read_at' => now()]);
    }

    public function markAllAsRead(): int
    {
        return $this->eloquentQuery()->whereNull('read_at')->update(['read_at' => now()]);
    }

    private function baseQuery(): Builder
    {
        return DB::table('notifications')
            ->where('notifiable_type', (new User)->getMorphClass())
            ->where('notifiable_id', $this->notifiable_id)
            ->whereNull('deleted_at');
    }

    private function eloquentQuery()
    {
        return DatabaseNotification::query()
            ->where('notifiable_type', (new User)->getMorphClass())
            ->where('notifiable_id', $this->notifiable_id);
    }
}
