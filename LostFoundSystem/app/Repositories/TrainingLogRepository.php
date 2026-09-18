<?php

namespace App\Repositories;

use App\Models\TrainingLog;

class TrainingLogRepository extends BaseRepository
{
    protected function model(): string
    {
        return TrainingLog::class;
    }

    public function latestTraining(): ?TrainingLog
    {
        return $this->query()->latest()->first();
    }
}
