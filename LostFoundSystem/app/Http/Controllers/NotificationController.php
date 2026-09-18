<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(
        protected AuthService $auth,
        protected NotificationService $notifications,
    ) {}

    public function markRead(Request $request, int $id)
    {
        $user = $this->auth->user($request);

        if (! $user || ! $this->notifications->markAsRead($id, (int) $user['id'])) {
            return back()->with('error', 'That notification could not be updated.');
        }

        return back();
    }

    public function markAllRead(Request $request)
    {
        $user = $this->auth->user($request);

        if ($user) {
            $this->notifications->markAllRead((int) $user['id']);
        }

        return back()->with('success', 'All notifications have been marked as read.');
    }

    public function open(Request $request, int $id)
    {
        $user = $this->auth->user($request);

        if (! $user || ! $this->notifications->markAsRead($id, (int) $user['id'])) {
            return redirect()->route('dashboard')->with('error', 'That notification is unavailable.');
        }

        return redirect()->route('dashboard');
    }
}
