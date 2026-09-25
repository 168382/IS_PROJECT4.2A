<?php

namespace App\Services;

use App\Models\Claim;
use App\Models\FoundItem;
use App\Models\User;
use App\Repositories\ClaimRepository;
use App\Repositories\FoundItemRepository;
use App\Repositories\LostItemRepository;
use Illuminate\Http\UploadedFile;

class ClaimService
{
    public function __construct(
        protected ClaimRepository $repository,
        protected FoundItemRepository $foundItemRepo,
        protected LostItemRepository $lostItemRepo,
        protected NotificationService $notifications,
        protected AuditLogService $audit,
    ) {}

    public function submit(User $user, FoundItem $foundItem, array $data, ?UploadedFile $proofImage = null): Claim
    {
        if ($proofImage) {
            $data['proof_image_path'] = $proofImage->store('claims', 'public');
        }

        $data['user_id'] = $user->id;
        $data['found_item_id'] = $foundItem->id;
        $data['status'] = 'pending';

        /** @var Claim $claim */
        $claim = $this->repository->create($data);

        if ($foundItem->user) {
            $this->notifications->notify(
                $foundItem->user,
                'New Claim Submitted',
                "{$user->name} submitted a claim for \"{$foundItem->item_name}\".",
                url('/admin/claims')
            );
        }

        $this->audit->log($user->id, 'submit_claim', Claim::class, $claim->id, $foundItem->item_name, request());

        return $claim;
    }

    public function approve(Claim $claim, User $staff): Claim
    {
        $claim = $this->repository->update($claim, ['status' => 'approved']);
        if ($claim->foundItem) {
            $this->foundItemRepo->update($claim->foundItem, ['status' => 'claimed']);

            foreach ($this->repository->pendingForFoundItemExcept($claim->foundItem->id, $claim->id) as $otherClaim) {
                $this->repository->update($otherClaim, ['status' => 'rejected']);

                if ($otherClaim->user) {
                    $this->notifications->notify(
                        $otherClaim->user,
                        'Claim Closed',
                        "Another claim for \"{$claim->foundItem->item_name}\" was approved, so your pending claim has been closed.",
                        url('/dashboard')
                    );
                }
            }
        }

        if ($claim->user) {
            $itemName = $claim->foundItem ? $claim->foundItem->item_name : 'item';
            $this->notifications->notify(
                $claim->user,
                'Claim Approved',
                "Your claim for \"{$itemName}\" has been approved. Please collect your item.",
                url('/dashboard')
            );
        }

        $this->audit->log($staff->id, 'approve_claim', Claim::class, $claim->id, null, request());

        return $claim;
    }

    public function reject(Claim $claim, User $staff, ?string $reason = null): Claim
    {
        $claim = $this->repository->update($claim, ['status' => 'rejected']);

        if ($claim->user) {
            $itemName = $claim->foundItem ? $claim->foundItem->item_name : 'item';
            $this->notifications->notify(
                $claim->user,
                'Claim Rejected',
                "Your claim for \"{$itemName}\" was rejected.".($reason ? " Reason: {$reason}" : ''),
                url('/dashboard')
            );
        }

        $this->audit->log($staff->id, 'reject_claim', Claim::class, $claim->id, $reason, request());

        return $claim;
    }

    public function markRetrieved(Claim $claim, User $staff, array $collectionDetails): Claim
    {
        $claim = $this->repository->update($claim, [
            'collection_verification_details' => $collectionDetails['collection_verification_details'],
            'collection_notes' => $collectionDetails['collection_notes'] ?? null,
            'collected_at' => now()->toDateTimeString(),
            'collected_by_user_id' => $staff->id,
        ]);

        if ($claim->foundItem) {
            $this->foundItemRepo->update($claim->foundItem, ['status' => 'claimed']);
        }

        $userLostItems = $this->lostItemRepo->forUser($claim->user_id);
        $itemName = $claim->foundItem ? $claim->foundItem->item_name : '';
        foreach ($userLostItems as $lostItem) {
            if ($itemName && str_contains(strtolower($lostItem->item_name), strtolower($itemName))) {
                $this->lostItemRepo->update($lostItem, ['status' => 'retrieved']);
                break;
            }
        }

        if ($claim->user) {
            $this->notifications->notify(
                $claim->user,
                'Item Retrieved',
                "Your item \"{$itemName}\" has been marked as retrieved.",
                url('/dashboard')
            );
        }

        $this->audit->log(
            $staff->id,
            'confirm_item_collection',
            Claim::class,
            $claim->id,
            'Collection confirmed after owner identity verification.',
            request()
        );

        return $claim;
    }
}
