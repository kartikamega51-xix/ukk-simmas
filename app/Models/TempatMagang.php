<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TempatMagang extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'tempat_magang';

    public $timestamps = false;

    protected $fillable = [
        'nama_perusahaan',
        'alamat',
        'bidang_usaha',
        'kuota',
        'penanggung_jawab',
        'kontak_pic',
        'is_verified',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'kuota' => 'integer',
            'is_verified' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function penempatan(): HasMany
    {
        return $this->hasMany(PenempatanMagang::class, 'tempat_magang_id');
    }

    public function pengajuan(): HasMany
    {
        return $this->hasMany(PengajuanMagang::class, 'tempat_magang_id');
    }

    public function kunjungan(): HasMany
    {
        return $this->hasMany(Kunjungan::class, 'tempat_magang_id');
    }

    public function getSiswaAktifCountAttribute(): int
    {
        return $this->penempatan()->whereIn('status_pengesahan', ['belum_disahkan', 'disahkan'])->count();
    }

    public function getSisaKuotaAttribute(): int
    {
        return max(0, $this->kuota - $this->siswa_aktif_count);
    }
}

