<?php

namespace App\Repositories;

use App\Models\AuditLog;

class AuditLogRepository extends BaseRepository
{
    protected function model(): string
    {
        return AuditLog::class;
    }

    protected function tableName(): string
    {
        return 'audit_logs';
    }

    public function recent(int $limit = 50)
    {
        $all = $this->all();
        return $all->sortByDesc('created_at')->take($limit)->values();
    }
}
