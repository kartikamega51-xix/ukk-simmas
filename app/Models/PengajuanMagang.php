<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengajuanMagang extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'pengajuan_magang';

    public $timestamps = false;

    protected $fillable = [
        'siswa_id',
        'tempat_magang_id',
        'posisi',
        'status',
        'catatan_penolakan',
        'tanggal_mulai',
        'tanggal_selesai',
        'tanggal_pengajuan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            'tanggal_pengajuan' => 'datetime',
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
}

