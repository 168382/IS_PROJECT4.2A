<?php

namespace App\Repositories;

use App\Models\Notification;
use Illuminate\Database\Eloquent\Collection;

class NotificationRepository extends BaseRepository
{
    protected function model(): string
    {
        return Notification::class;
    }

    protected function tableName(): string
    {
        return 'notifications';
    }

    public function unreadForUser(int $userId): Collection
    {
        $all = $this->all();

        return $all->where('user_id', $userId)
            ->where('is_read', false)
            ->sortByDesc('created_at')
            ->values();
    }

    public function forUser(int $userId, int $limit = 20)
    {
        $all = $this->all();

        return $all->where('user_id', $userId)
            ->sortByDesc('created_at')
            ->take($limit)
            ->values();
    }

    public function unreadCount(int $userId): int
    {
        $all = $this->all();

        return $all->where('user_id', $userId)
            ->where('is_read', false)
            ->count();
    }

    public function markAllReadForUser(int $userId): void
    {
        foreach ($this->unreadForUser($userId) as $notification) {
            $this->update($notification, ['is_read' => true]);
        }
    }
}
