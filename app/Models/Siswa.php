<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Siswa extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'siswa';

    protected $fillable = [
        'user_id',
        'nis',
        'kelas',
        'status_magang',
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
        return $this->hasMany(PenempatanMagang::class, 'siswa_id');
    }

    public function penempatanAktif(): HasOne
    {
        return $this->hasOne(PenempatanMagang::class, 'siswa_id')->orderByDesc('created_at');
    }

    public function pengajuan(): HasMany
    {
        return $this->hasMany(PengajuanMagang::class, 'siswa_id');
    }

    public function absensi(): HasMany
    {
        return $this->hasMany(Absensi::class, 'siswa_id');
    }

    public function jurnal(): HasMany
    {
        return $this->hasMany(JurnalHarian::class, 'siswa_id');
    }
}

