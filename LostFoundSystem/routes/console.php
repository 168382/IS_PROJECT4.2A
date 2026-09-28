<?php

use App\Repositories\FoundItemRepository;
use App\Repositories\LostItemRepository;
use App\Services\ItemImage;
use App\Services\JsonDatabase;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

Artisan::command('items:import-json', function (
    JsonDatabase $json,
    LostItemRepository $lost,
    FoundItemRepository $found,
    ItemImage $images
) {
    foreach (['lost_items' => $lost, 'found_items' => $found] as $table => $repository) {
        foreach ($json->all($table) as $record) {
            $existing = $repository->find((int) $record['id']);
            if ($existing) {
                if ($existing->item_name !== $record['item_name']
                    || (int) $existing->user_id !== (int) $record['user_id']
                    || (string) $existing->created_at !== (string) ($record['created_at'] ?? '')) {
                    throw new RuntimeException("Conflicting {$table} ID {$record['id']} in SQLite.");
                }
                continue;
            }

            if (! empty($record['image_path'])) {
                if (Storage::disk('public')->exists($record['image_path'])) {
                    $record = array_merge(
                        $record,
                        $images->fromBytes(Storage::disk('public')->get($record['image_path'])),
                        ['image_path' => 'database']
                    );
                } else {
                    $this->warn("Photo missing for {$table} ID {$record['id']}: {$record['image_path']}");
                    $record['image_path'] = null;
                }
            }

            $repository->import($record);
        }
        $this->info("Imported {$table} records into SQLite.");
    }
})->purpose('Copy JSON item records and existing photos into SQLite');

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
