<?php

namespace App\Repositories;

use App\Models\NlpDataset;

class NlpDatasetRepository extends BaseRepository
{
    protected function model(): string
    {
        return NlpDataset::class;
    }

    public function activeRecords()
    {
        return $this->query()->where('is_active', true)->get();
    }
}
