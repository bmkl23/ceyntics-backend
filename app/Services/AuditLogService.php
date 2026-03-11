<?php

namespace App\Services;

use App\Models\ActivityLog;

class AuditLogService
{
    public static function log(
        string $action,
        string $entityType,
        int $entityId,
        array $oldValues = [],
        array $newValues = [],
        string $description = ''
    ): void {
        ActivityLog::create([
            'user_id'     => auth()->id(),
            'action'      => $action,
            'entity_type' => $entityType,
            'entity_id'   => $entityId,
            'old_values'  => empty($oldValues) ? null : $oldValues,
            'new_values'  => empty($newValues) ? null : $newValues,
            'description' => $description,
            'ip_address'  => request()->ip(),
            'created_at'  => now(),
        ]);
    }
}