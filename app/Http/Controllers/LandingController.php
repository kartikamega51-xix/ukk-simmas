<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\JurnalHarian;
use App\Models\Setting;
use App\Models\Siswa;
use App\Models\TempatMagang;

class LandingController extends Controller
{
    public function index()
    {
        $settings = Setting::allKeyValue();

        try {
            $jurnalDisetujui = JurnalHarian::where('status_verifikasi', 'disetujui')->count();
            $presensiTercatat = Absensi::count();
            $siswaAktif = Siswa::where('is_active', true)->count();
            $totalMitraDudi = TempatMagang::where('is_verified', true)->count();
        } catch (\Throwable $e) {
            $jurnalDisetujui = 0;
            $presensiTercatat = 0;
            $siswaAktif = 0;
            $totalMitraDudi = 0;
        }

        return view('landing', compact(
            'settings',
            'jurnalDisetujui',
            'presensiTercatat',
            'siswaAktif',
            'totalMitraDudi'
        ));
    }
}

