<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PenempatanMagang extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'penempatan_magang';

    public $timestamps = false;

    protected $fillable = [
        'siswa_id',
        'tempat_magang_id',
        'guru_id',
        'status_pengesahan',
        'tanggal_mulai',
        'tanggal_selesai',
        'catatan_admin',
        'nilai_akhir',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            'nilai_akhir' => 'float',
            'created_at' => 'datetime',
        ];
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function tempatMagang(): BelongsTo
    {
        return $this->belongsTo(TempatMagang::class, 'tempat_magang_id');
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class, 'guru_id');
    }
}

