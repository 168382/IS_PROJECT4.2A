<?php

namespace App\Services;

use App\Models\FoundItem;
use App\Models\LostItem;
use App\Repositories\FoundItemRepository;
use App\Repositories\ItemMatchRepository;
use App\Repositories\LostItemRepository;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NlpMatchingService
{
    public function __construct(
        protected LostItemRepository $lostItems,
        protected FoundItemRepository $foundItems,
        protected ItemMatchRepository $matches,
        protected NotificationService $notifications,
    ) {}

    public function matchLostItem(LostItem $lostItem): array
    {
        $candidates = $this->foundItems->activeForMatching();
        $results = $this->callNlpEngine($this->formatItem($lostItem, 'lost'), $candidates, 'found');

        foreach ($results as $match) {
            $found = $candidates->firstWhere('id', $match['item_id']);
            if (! $found) {
                continue;
            }

            $this->matches->create([
                'lost_item_id' => $lostItem->id,
                'found_item_id' => $found->id,
                'similarity_score' => $match['similarity_score'],
                'status' => 'pending',
            ]);

            if ($match['similarity_score'] >= 50 && $lostItem->user) {
                $this->notifications->notify(
                    $lostItem->user,
                    'Potential Match Found',
                    "A found item \"{$found->item_name}\" may match your lost \"{$lostItem->item_name}\" ({$match['similarity_score']}%).",
                    url('/dashboard')
                );
            }
        }

        return $results;
    }

    public function matchFoundItem(FoundItem $foundItem): array
    {
        $candidates = $this->lostItems->activeForMatching();
        $results = $this->callNlpEngine($this->formatItem($foundItem, 'found'), $candidates, 'lost');

        foreach ($results as $match) {
            $lost = $candidates->firstWhere('id', $match['item_id']);
            if (! $lost) {
                continue;
            }

            $this->matches->create([
                'lost_item_id' => $lost->id,
                'found_item_id' => $foundItem->id,
                'similarity_score' => $match['similarity_score'],
                'status' => 'pending',
            ]);

            if ($match['similarity_score'] >= 50 && $lost->user) {
                $this->notifications->notify(
                    $lost->user,
                    'Potential Match Found',
                    "Your lost item \"{$lost->item_name}\" may match a found \"{$foundItem->item_name}\" ({$match['similarity_score']}%).",
                    url('/dashboard')
                );
            }
        }

        return $results;
    }

    protected function formatItem(LostItem|FoundItem $item, string $type): array
    {
        return [
            'id' => $item->id,
            'item_name' => $item->item_name,
            'description' => $item->description,
            'category_name' => $item->category?->name ?? '',
            'color' => $item->color,
            'brand' => $item->brand,
            'location' => $type === 'lost' ? $item->location_lost : $item->location_found,
            'date' => ($type === 'lost' ? $item->date_lost : $item->date_found)?->format('Y-m-d'),
        ];
    }

    protected function callNlpEngine(array $target, $dataset, string $type): array
    {
        if ($dataset->isEmpty()) {
            return [];
        }

        $rows = $dataset->map(function ($item) use ($type) {
            return [
                'id' => $item->id,
                'item_name' => $item->item_name,
                'description' => $item->description,
                'category_name' => $item->category?->name ?? '',
                'color' => $item->color,
                'brand' => $item->brand,
                'location' => $type === 'found' ? $item->location_found : $item->location_lost,
                'date' => ($type === 'found' ? $item->date_found : $item->date_lost)?->format('Y-m-d'),
            ];
        })->values()->all();

        $url = config('services.nlp.url', 'http://127.0.0.1:5000/match');

        try {
            $response = Http::timeout(15)->post($url, [
                'item' => $target,
                'dataset' => $rows,
            ]);

            if ($response->successful()) {
                return $response->json('matches', []);
            }
        } catch (\Throwable $e) {
            Log::warning('NLP service unavailable, using fallback matcher.', ['error' => $e->getMessage()]);
        }

        return $this->fallbackMatch($target, $rows);
    }

    protected function fallbackMatch(array $target, array $dataset): array
    {
        $targetText = strtolower(implode(' ', array_filter([
            $target['item_name'] ?? '',
            $target['description'] ?? '',
            $target['category_name'] ?? '',
            $target['color'] ?? '',
            $target['brand'] ?? '',
            $target['location'] ?? '',
        ])));

        $scores = [];
        foreach ($dataset as $row) {
            $rowText = strtolower(implode(' ', array_filter([
                $row['item_name'] ?? '',
                $row['description'] ?? '',
                $row['category_name'] ?? '',
                $row['color'] ?? '',
                $row['brand'] ?? '',
                $row['location'] ?? '',
            ])));

            similar_text($targetText, $rowText, $percent);
            if ($percent > 0) {
                $scores[] = [
                    'item_id' => $row['id'],
                    'item_name' => $row['item_name'],
                    'similarity_score' => round($percent, 2),
                ];
            }
        }

        usort($scores, fn ($a, $b) => $b['similarity_score'] <=> $a['similarity_score']);

        return array_slice($scores, 0, 5);
    }
}
