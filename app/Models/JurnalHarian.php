<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JurnalHarian extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'jurnal_harian';

    public $timestamps = false;

    protected $fillable = [
        'siswa_id',
        'tanggal',
        'kegiatan',
        'kendala',
        'solusi',
        'photo_url',
        'status_verifikasi',
        'catatan_guru',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'created_at' => 'datetime',
        ];
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }
}

