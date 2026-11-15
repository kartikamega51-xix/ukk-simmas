<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $table = 'settings';

    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'key',
        'value',
    ];

    public static function getValue(string $key, ?string $default = null): ?string
    {
        try {
            $all = Cache::remember('simmas_settings', 300, function () {
                return static::query()->pluck('value', 'key')->toArray();
            });
            return $all[$key] ?? $default;
        } catch (\Throwable $e) {
            return $default;
        }
    }

    public static function allKeyValue(): array
    {
        $defaults = [
            'app_name' => 'SIMMAS',
            'app_fullname' => 'Sistem Informasi Manajemen Magang Siswa',
            'app_description' => 'Platform manajemen magang siswa SMK yang menghubungkan sekolah, guru pembimbing, dan dunia usaha dalam satu sistem terpadu.',
            'hero_title' => 'Magang lebih teratur.',
            'hero_description' => 'Platform manajemen magang siswa SMK yang menghubungkan sekolah, guru pembimbing, dan dunia usaha dalam satu sistem terpadu.',
            'school_name' => 'SMK Negeri 1 Rekayasa',
            'school_website' => 'https://smkn1rekayasa.sch.id',
            'principal_name' => 'Drs. H. Bambang Sudibyo, M.Pd.',
            'principal_nip' => '196805141994031005',
            'school_address' => 'Jl. Pendidikan No. 45, Kec. Sidoarjo, Kabupaten Sidoarjo, Jawa Timur 61215',
            'school_phone' => '(031) 8941234',
        ];

        try {
            $stored = static::query()->pluck('value', 'key')->toArray();
            return array_merge($defaults, $stored);
        } catch (\Throwable $e) {
            return $defaults;
        }
    }
}

