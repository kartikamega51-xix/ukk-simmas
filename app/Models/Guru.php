<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Guru extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'guru';

    protected $fillable = [
        'user_id',
        'nip',
        'jurusan',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class, 'user_id');
    }

    public function penempatan(): HasMany
    {
        return $this->hasMany(PenempatanMagang::class, 'guru_id');
    }

    public function kunjungan(): HasMany
    {
        return $this->hasMany(Kunjungan::class, 'guru_id');
    }

    public function activePenempatanCount(): int
    {
        return $this->penempatan()->whereIn('status_pengesahan', ['belum_disahkan', 'disahkan'])->count();
    }
}

