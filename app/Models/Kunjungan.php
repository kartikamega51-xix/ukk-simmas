<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Kunjungan extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'kunjungan';

    public $timestamps = false;

    protected $fillable = [
        'guru_id',
        'tempat_magang_id',
        'tanggal',
        'catatan',
        'photo_url',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'created_at' => 'datetime',
        ];
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class, 'guru_id');
    }

    public function tempatMagang(): BelongsTo
    {
        return $this->belongsTo(TempatMagang::class, 'tempat_magang_id');
    }
}

