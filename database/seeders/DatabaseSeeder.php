<?php

namespace Database\Seeders;

use App\Models\Guru;
use App\Models\Profile;
use App\Models\Setting;
use App\Models\Siswa;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with only initial required accounts.
     * All dummy data removed; live data will be populated via user CRUD to Supabase.
     */
    public function run(): void
    {
        $password = Hash::make('password');

        // 1. Initial Default Settings
        $defaultSettings = Setting::allKeyValue();
        foreach ($defaultSettings as $key => $val) {
            Setting::updateOrCreate(['key' => $key], ['value' => $val]);
        }

        // 2. Initial Admin Account
        Profile::updateOrCreate(
            ['email' => 'admin@simmas.sch.id'],
            [
                'nama' => 'Administrator',
                'password' => $password,
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        // 3. Initial Guru Account
        $guruProfile = Profile::updateOrCreate(
            ['email' => 'guru@simmas.sch.id'],
            [
                'nama' => 'Guru Pembimbing',
                'password' => $password,
                'role' => 'guru',
                'is_active' => true,
            ]
        );

        Guru::updateOrCreate(
            ['user_id' => $guruProfile->id],
            [
                'nip' => '198501012010011001',
                'jurusan' => 'Rekayasa Perangkat Lunak',
                'is_active' => true,
            ]
        );

        // 4. Initial Siswa Account
        $siswaProfile = Profile::updateOrCreate(
            ['email' => 'siswa@simmas.sch.id'],
            [
                'nama' => 'Siswa Magang',
                'password' => $password,
                'role' => 'siswa',
                'is_active' => true,
            ]
        );

        Siswa::updateOrCreate(
            ['user_id' => $siswaProfile->id],
            [
                'nis' => '20261001',
                'kelas' => 'XII RPL 1',
                'status_magang' => 'belum_magang',
                'is_active' => true,
            ]
        );
    }
}
