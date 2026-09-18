<?php

namespace App\Http\Controllers;

use App\Exports\AdminReportExport;
use App\Models\User;
use App\Repositories\AuditLogRepository;
use App\Repositories\ClaimRepository;
use App\Repositories\FoundItemRepository;
use App\Repositories\ItemMatchRepository;
use App\Repositories\LostItemRepository;
use App\Repositories\NotificationRepository;
use App\Services\AuthService;
use App\Services\ClaimService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class AdminController extends Controller
{
    public function __construct(
        protected AuthService $auth,
        protected LostItemRepository $lostItemRepo,
        protected FoundItemRepository $foundItemRepo,
        protected ClaimRepository $claimRepo,
        protected ItemMatchRepository $matchRepo,
        protected ClaimService $claimService,
        protected AuditLogRepository $auditRepo,
        protected NotificationRepository $notifRepo,
    ) {}

    // ─── Guard: admin/staff only ──────────────────────────────────
    private function guard(Request $request): ?array
    {
        $user = $this->auth->user($request);
        if (! $user || ! in_array($user['role'] ?? '', ['admin', 'staff'])) {
            return null;
        }

        return $user;
    }

    /** Administrative tasks that change platform-wide data are admin-only. */
    private function adminGuard(Request $request): ?array
    {
        $user = $this->auth->user($request);
        if (! $user || ($user['role'] ?? '') !== 'admin') {
            return null;
        }

        return $user;
    }

    // ─── Dashboard Overview ───────────────────────────────────────
    public function index(Request $request)
    {
        $userData = $this->adminGuard($request);
        if (! $userData) {
            return redirect('/dashboard')->with('error', 'Access denied. Administrative privileges required.');
        }

        $allUsers = $this->auth->allUsers();
        $lostItems = $this->lostItemRepo->all();
        $foundItems = $this->foundItemRepo->all();
        $claims = $this->claimRepo->all();
        $pendingClaims = $this->claimRepo->pendingCount();
        $recentAudits = $this->auditRepo->recent(20);

        // Stats for charts
        $approvedClaims = $claims->where('status', 'approved')->count();
        $rejectedClaims = $claims->where('status', 'rejected')->count();
        $resolvedItems = $lostItems->where('status', 'retrieved')->count();

        return view('admin.index', compact(
            'userData', 'allUsers', 'lostItems', 'foundItems', 'claims',
            'pendingClaims', 'recentAudits', 'approvedClaims', 'rejectedClaims', 'resolvedItems'
        ));
    }

    // ─── Claim Management ────────────────────────────────────────
    public function claims(Request $request)
    {
        $userData = $this->guard($request);
        if (! $userData) {
            return redirect('/dashboard')->with('error', 'Access denied.');
        }

        $claims = $this->claimRepo->all()->sortByDesc('created_at')->values();

        return view('admin.claims', compact('claims', 'userData'));
    }

    public function approveClaim(Request $request, int $id)
    {
        $userData = $this->guard($request);
        if (! $userData) {
            return back()->with('error', 'Access denied.');
        }

        $claim = $this->claimRepo->find($id);
        if (! $claim) {
            return back()->with('error', 'Claim not found.');
        }

        if ($claim->status !== 'pending') {
            return back()->with('error', 'This claim has already been processed.');
        }

        $staff = new User($userData);
        $staff->id = (int) $userData['id'];
        $staff->exists = true;

        $this->claimService->approve($claim, $staff);

        return back()->with('success', 'Claim #CLM-'.$id.' has been approved. User notified automatically.');
    }

    public function rejectClaim(Request $request, int $id)
    {
        $userData = $this->guard($request);
        if (! $userData) {
            return back()->with('error', 'Access denied.');
        }

        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $claim = $this->claimRepo->find($id);
        if (! $claim) {
            return back()->with('error', 'Claim not found.');
        }

        if ($claim->status !== 'pending') {
            return back()->with('error', 'This claim has already been processed.');
        }

        $staff = new User($userData);
        $staff->id = (int) $userData['id'];
        $staff->exists = true;

        $reason = $validated['reason'] ?? 'Verification details did not match.';
        $this->claimService->reject($claim, $staff, $reason);

        return back()->with('success', 'Claim #CLM-'.$id.' has been rejected. User notified automatically.');
    }

    // ─── Item Management ─────────────────────────────────────────
    public function items(Request $request)
    {
        $userData = $this->adminGuard($request);
        if (! $userData) {
            return redirect('/dashboard')->with('error', 'Access denied.');
        }

        $lostItems = $this->lostItemRepo->all()->sortByDesc('created_at')->values();
        $foundItems = $this->foundItemRepo->all()->sortByDesc('created_at')->values();

        return view('admin.items', compact('lostItems', 'foundItems', 'userData'));
    }

    public function deleteLostItem(Request $request, int $id)
    {
        $userData = $this->adminGuard($request);
        if (! $userData) {
            return back()->with('error', 'Access denied.');
        }

        if ($this->matchRepo->forLostItem($id)->isNotEmpty()) {
            return back()->with('error', 'This lost item has related matches and cannot be removed. Resolve its matching records first.');
        }

        if (! $this->lostItemRepo->deleteById($id)) {
            return back()->with('error', 'Lost item not found or has already been removed.');
        }

        return back()->with('success', 'Lost item removed from the system.');
    }

    public function deleteFoundItem(Request $request, int $id)
    {
        $userData = $this->adminGuard($request);
        if (! $userData) {
            return back()->with('error', 'Access denied.');
        }

        if ($this->claimRepo->forFoundItem($id)->isNotEmpty() || $this->matchRepo->forFoundItem($id)->isNotEmpty()) {
            return back()->with('error', 'This found item has related claims or matches and cannot be removed.');
        }

        if (! $this->foundItemRepo->deleteById($id)) {
            return back()->with('error', 'Found item not found or has already been removed.');
        }

        return back()->with('success', 'Found item removed from the system.');
    }

    // ─── User Management ─────────────────────────────────────────
    public function users(Request $request)
    {
        $userData = $this->adminGuard($request);
        if (! $userData) {
            return redirect('/dashboard')->with('error', 'Access denied.');
        }

        $allUsers = $this->auth->allUsers();

        return view('admin.users', compact('allUsers', 'userData'));
    }

    public function updateUserRole(Request $request, int $id)
    {
        $userData = $this->adminGuard($request);
        if (! $userData) {
            return back()->with('error', 'Access denied.');
        }

        if ((int) $userData['id'] === $id) {
            return back()->with('error', 'You cannot change your own role.');
        }

        $validated = $request->validate([
            'role' => 'required|in:admin,staff,student',
        ]);

        $targetUser = $this->auth->findUser($id);
        if (! $targetUser) {
            return back()->with('error', 'User not found.');
        }

        $adminCount = collect($this->auth->allUsers())->where('role', 'admin')->count();
        if (($targetUser['role'] ?? '') === 'admin' && $validated['role'] !== 'admin' && $adminCount <= 1) {
            return back()->with('error', 'At least one administrator must remain on the system.');
        }

        if (! $this->auth->updateUserRole($id, $validated['role'])) {
            return back()->with('error', 'User not found.');
        }

        return back()->with('success', 'User role updated successfully.');
    }

    // ─── Reporting (admin only) ─────────────────────────────────
    public function downloadPdfReport(Request $request)
    {
        $userData = $this->adminGuard($request);
        if (! $userData) {
            return redirect('/dashboard')->with('error', 'Access denied. Administrative privileges required.');
        }

        $report = $this->reportData();

        return Pdf::loadView('admin.reports.pdf', compact('report', 'userData'))
            ->setPaper('a4', 'landscape')
            ->download('lost-found-report-'.now()->format('Y-m-d').'.pdf');
    }

    public function downloadExcelReport(Request $request)
    {
        $userData = $this->adminGuard($request);
        if (! $userData) {
            return redirect('/dashboard')->with('error', 'Access denied. Administrative privileges required.');
        }

        return Excel::download(
            new AdminReportExport($this->reportData()),
            'lost-found-report-'.now()->format('Y-m-d').'.xlsx'
        );
    }

    private function reportData(): array
    {
        $users = collect($this->auth->allUsers())->sortBy('name')->values();
        $lostItems = $this->lostItemRepo->all()->sortByDesc('created_at')->values();
        $foundItems = $this->foundItemRepo->all()->sortByDesc('created_at')->values();
        $claims = $this->claimRepo->all()->sortByDesc('created_at')->values();

        return [
            'generatedAt' => now()->format('F j, Y g:i A'),
            'summary' => [
                'Registered users' => $users->count(),
                'Lost items' => $lostItems->count(),
                'Found items' => $foundItems->count(),
                'Pending claims' => $claims->where('status', 'pending')->count(),
                'Approved claims' => $claims->where('status', 'approved')->count(),
                'Retrieved lost items' => $lostItems->where('status', 'retrieved')->count(),
            ],
            'users' => $users,
            'lostItems' => $lostItems,
            'foundItems' => $foundItems,
            'claims' => $claims,
        ];
    }
}
