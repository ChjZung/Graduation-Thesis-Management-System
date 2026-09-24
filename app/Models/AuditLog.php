<?php

namespace App\Models;

use Illuminate\Support\Facades\Log;

class AuditLog
{
    /**
     * Log system actions or import events safely to Laravel log.
     */
    public static function log(string $action, string $model, $id = null, array $details = []): void
    {
        Log::info("AuditLog [{$action}] on [{$model}] ID [{$id}]: " . json_encode($details, JSON_UNESCAPED_UNICODE));
    }
}
