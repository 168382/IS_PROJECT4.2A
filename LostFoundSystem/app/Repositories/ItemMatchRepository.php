<?php

namespace App\Repositories;

use App\Models\ItemMatch;
use Illuminate\Database\Eloquent\Collection;

class ItemMatchRepository extends BaseRepository
{
    protected function model(): string
    {
        return ItemMatch::class;
    }

    protected function tableName(): string
    {
        return 'item_matches';
    }

    public function forLostItem(int $lostItemId): Collection
    {
        $all = $this->all();
        return $all->where('lost_item_id', $lostItemId)
            ->sortByDesc('similarity_score')
            ->values();
    }

    public function forFoundItem(int $foundItemId): Collection
    {
        $all = $this->all();
        return $all->where('found_item_id', $foundItemId)
            ->sortByDesc('similarity_score')
            ->values();
    }

    public function averageSimilarity(): float
    {
        $all = $this->all();
        if ($all->isEmpty()) {
            return 0.0;
        }
        return (float) $all->avg('similarity_score');
    }
}
