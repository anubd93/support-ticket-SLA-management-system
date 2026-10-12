<?php

namespace App\Services\V1;

use App\Models\User;
use App\Repositories\V1\NotificationRepositoryV1;

class NotificationServiceV1
{
    private const BELL_LIMIT = 8;

    public function __construct(private readonly NotificationRepositoryV1 $notificationRepositoryV1
    ) {}
    
    public function forBell(User $user): array
    {
        $this->notificationRepositoryV1->setNotifiableId($user->id);

        return [
            'bellNotifications' => $this->notificationRepositoryV1->getLatest(self::BELL_LIMIT)->map(function ($notification) {
                $notification->data = json_decode($notification->data, true) ?? [];

                return $notification;
            }),
            'bellUnreadCount' => $this->notificationRepositoryV1->countUnread(),
        ];
    }
    
    public function open(User $user, string $id): ?int
    {
        $notification = $this->notificationRepositoryV1->setNotifiableId($user->id)->setId($id)->findForUser();

        if (! $notification) {
            return null;
        }

        $this->notificationRepositoryV1->markAsRead();

        return json_decode($notification->data, true)['ticket_id'] ?? null;
    }

    public function markAllAsRead(User $user): int
    {
        return $this->notificationRepositoryV1->setNotifiableId($user->id)->markAllAsRead();
    }
}
