<?php

namespace App\Repositories;

use App\Models\FoundItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class FoundItemRepository extends BaseRepository
{
    protected function model(): string
    {
        return FoundItem::class;
    }

    protected function tableName(): string
    {
        return 'found_items';
    }

    public function forUser(int $userId): Collection
    {
        $all = $this->all();
        return $all->where('user_id', $userId)->sortByDesc('created_at')->values();
    }

    public function search(array $filters): LengthAwarePaginator
    {
        $records = $this->jsonDb->all($this->tableName());

        $filtered = array_filter($records, function ($item) use ($filters) {
            if (!empty($filters['keyword'])) {
                $kw = strtolower($filters['keyword']);
                $name = strtolower($item['item_name'] ?? '');
                $desc = strtolower($item['description'] ?? '');
                $brand = strtolower($item['brand'] ?? '');
                if (!str_contains($name, $kw) && !str_contains($desc, $kw) && !str_contains($brand, $kw)) {
                    return false;
                }
            }

            if (!empty($filters['category_id'])) {
                if ((int)($item['category_id'] ?? 0) !== (int)$filters['category_id']) {
                    return false;
                }
            }

            if (!empty($filters['color'])) {
                if (!str_contains(strtolower($item['color'] ?? ''), strtolower($filters['color']))) {
                    return false;
                }
            }

            if (!empty($filters['location'])) {
                if (!str_contains(strtolower($item['location_found'] ?? ''), strtolower($filters['location']))) {
                    return false;
                }
            }

            if (!empty($filters['date'])) {
                if (($item['date_found'] ?? '') !== $filters['date']) {
                    return false;
                }
            }

            if (!empty($filters['status'])) {
                if (($item['status'] ?? '') !== $filters['status']) {
                    return false;
                }
            }

            return true;
        });

        // Reverse for latest first
        usort($filtered, fn($a, $b) => ($b['id'] ?? 0) <=> ($a['id'] ?? 0));

        $models = array_map(fn($r) => $this->makeModel($r), array_values($filtered));

        return $this->paginateArray($models, 10);
    }

    public function activeForMatching(): Collection
    {
        $all = $this->all();
        return $all->where('status', 'found')->values();
    }
}
