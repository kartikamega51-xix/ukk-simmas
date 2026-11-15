<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'activity_logs';

    public $timestamps = false;

    protected $fillable = [
        'timestamp',
        'level',
        'action_type',
        'actor_email',
        'actor_role',
        'target',
        'ip_address',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'timestamp' => 'datetime',
            'metadata' => 'array',
        ];
    }
}

