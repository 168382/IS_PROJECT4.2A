<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\NotificationRepository;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    public function __construct(protected NotificationRepository $repository) {}

    public function notify(User $user, string $title, string $message, ?string $link = null): void
    {
        $this->repository->create([
            'user_id' => $user->id,
            'title' => $title,
            'message' => $message,
            'related_link' => $link,
            'is_read' => false,
        ]);

        try {
            Mail::raw($message, function ($mail) use ($user, $title) {
                $mail->to($user->email)->subject($title);
            });
        } catch (\Throwable) {
            // Email failures should not block in-app notifications.
        }
    }

    public function markAsRead(int $notificationId, int $userId): bool
    {
        $notification = $this->repository->find($notificationId);

        if (! $notification || (int)$notification->user_id !== $userId) {
            return false;
        }

        $this->repository->update($notification, ['is_read' => true]);

        return true;
    }

    public function markAllRead(int $userId): void
    {
        $this->repository->markAllReadForUser($userId);
    }
}
