<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Repositories\ClaimRepository;
use App\Repositories\FoundItemRepository;
use App\Services\AuthService;
use App\Services\ClaimService;
use Illuminate\Http\Request;

class ClaimController extends Controller
{
    public function __construct(
        protected ClaimService $claimService,
        protected FoundItemRepository $foundItemRepo,
        protected ClaimRepository $claimRepo,
        protected AuthService $auth,
    ) {}

    public function submit(Request $request)
    {
        $validated = $request->validate([
            'found_item_id' => 'required|integer',
            'verification_info' => 'required|string|min:20|max:2000',
            'proof_image' => 'nullable|image|max:5120',
        ]);

        $userData = $this->auth->user($request);
        if (! $userData) {
            return redirect('/login')->with('error', 'Please log in to submit a claim.');
        }

        $foundItem = $this->foundItemRepo->find((int) $validated['found_item_id']);
        if (! $foundItem) {
            return back()->with('error', 'Found item not found.');
        }

        if ($foundItem->status !== 'found') {
            return back()->with('error', 'This item is no longer available to claim.');
        }

        $user = new User($userData);
        $user->id = (int) $userData['id'];
        $user->exists = true;

        if ((int) $foundItem->user_id === (int) $user->id) {
            return back()->with('error', 'You cannot submit a claim for an item you reported.');
        }

        if ($this->claimRepo->hasActiveClaim($user->id, $foundItem->id)) {
            return back()->with('error', 'You already have an active claim for this item.');
        }

        $proofImage = $request->file('proof_image');

        $validated['proof_description'] = $validated['verification_info'];
        unset($validated['verification_info']);

        $this->claimService->submit($user, $foundItem, $validated, $proofImage);

        return redirect('/dashboard')->with('success', 'Claim submitted successfully! Administration will review your claim.');
    }
}
