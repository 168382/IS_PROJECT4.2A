<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use App\Repositories\LostItemRepository;
use App\Repositories\FoundItemRepository;
use App\Repositories\ClaimRepository;
use App\Repositories\ItemMatchRepository;
use App\Repositories\NotificationRepository;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        protected AuthService $auth,
        protected LostItemRepository $lostItemRepo,
        protected FoundItemRepository $foundItemRepo,
        protected ClaimRepository $claimRepo,
        protected ItemMatchRepository $matchRepo,
        protected NotificationRepository $notificationRepo,
    ) {}

    public function index(Request $request)
    {
        $user = $this->auth->user($request);
        if (!$user) {
            return redirect('/login');
        }

        $userId = (int)$user['id'];

        $lostItems = $this->lostItemRepo->forUser($userId);
        $foundItems = $this->foundItemRepo->forUser($userId);
        $claims = $this->claimRepo->forUser($userId);
        $notifications = $this->notificationRepo->forUser($userId, 10);
        $unreadNotificationCount = $this->notificationRepo->unreadCount($userId);

        // Gather matches for user's lost items
        $matches = [];
        foreach ($lostItems as $lostItem) {
            $itemMatches = $this->matchRepo->forLostItem($lostItem->id);
            foreach ($itemMatches as $m) {
                $matches[] = $m;
            }
        }

        return view('dashboard', compact(
            'user',
            'lostItems',
            'foundItems',
            'claims',
            'notifications',
            'unreadNotificationCount',
            'matches'
        ));
    }
}
