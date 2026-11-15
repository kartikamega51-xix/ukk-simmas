<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    public static function log(
        string $actionType,
        ?string $target = null,
        array $metadata = [],
        string $level = 'INFO',
        ?string $actorEmail = null,
        ?string $actorRole = null
    ): void {
        try {
            $user = Auth::user();
            ActivityLog::create([
                'timestamp' => now(),
                'level' => strtoupper($level),
                'action_type' => $actionType,
                'actor_email' => $actorEmail ?? ($user ? $user->email : 'guest'),
                'actor_role' => $actorRole ?? ($user ? $user->role : 'guest'),
                'target' => $target ?? '-',
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'metadata' => $metadata,
            ]);
        } catch (\Throwable $e) {
            // Jangan gagalkan transaksi utama bila gagal mencatat log
        }
    }
}

