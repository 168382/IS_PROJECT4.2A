<?php

namespace App\Services;

use App\Repositories\AuditLogRepository;
use Illuminate\Http\Request;

class AuditLogService
{
    public function __construct(protected AuditLogRepository $repository) {}

    public function log(?int $userId, string $action, ?string $entityType = null, ?int $entityId = null, ?string $details = null, ?Request $request = null): void
    {
        $this->repository->create([
            'user_id' => $userId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'details' => $details,
            'ip_address' => $request?->ip(),
        ]);
    }
}
