<?php

namespace App\Repositories;

use App\Models\Claim;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ClaimRepository extends BaseRepository
{
    protected function model(): string
    {
        return Claim::class;
    }

    protected function tableName(): string
    {
        return 'claims';
    }

    public function forUser(int $userId)
    {
        $all = $this->all();

        return $all->where('user_id', $userId)->sortByDesc('created_at')->values();
    }

    public function forFoundItem(int $foundItemId)
    {
        return $this->all()
            ->where('found_item_id', $foundItemId)
            ->sortByDesc('created_at')
            ->values();
    }

    public function paginateWithRelations(int $perPage = 15): LengthAwarePaginator
    {
        return $this->paginate($perPage);
    }

    public function pendingCount(): int
    {
        $all = $this->all();

        return $all->where('status', 'pending')->count();
    }

    public function hasActiveClaim(int $userId, int $foundItemId): bool
    {
        return $this->all()
            ->where('user_id', $userId)
            ->where('found_item_id', $foundItemId)
            ->whereIn('status', ['pending', 'approved'])
            ->isNotEmpty();
    }

    public function pendingForFoundItemExcept(int $foundItemId, int $claimId)
    {
        return $this->all()
            ->where('found_item_id', $foundItemId)
            ->where('status', 'pending')
            ->reject(fn ($claim) => (int) $claim->id === $claimId)
            ->values();
    }
}
