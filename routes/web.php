<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Guru\GuruController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\Siswa\SiswaController;
use Illuminate\Support\Facades\Route;

// Halaman Publik
Route::get('/', [LandingController::class, 'index'])->name('landing');

// Autentikasi
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Modul Admin
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'role:admin'])
    ->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/monitoring', [AdminController::class, 'monitoring'])->name('monitoring');

        // Master Data Guru
        Route::get('/guru', [AdminController::class, 'guruIndex'])->name('guru');
        Route::post('/guru', [AdminController::class, 'guruStore'])->name('guru.store');
        Route::put('/guru/{guru}', [AdminController::class, 'guruUpdate'])->name('guru.update');
        Route::patch('/guru/{guru}/status', [AdminController::class, 'guruToggleStatus'])->name('guru.status');
        Route::delete('/guru/{guru}', [AdminController::class, 'guruDestroy'])->name('guru.destroy');

        // Master Data Siswa
        Route::get('/siswa', [AdminController::class, 'siswaIndex'])->name('siswa');
        Route::post('/siswa', [AdminController::class, 'siswaStore'])->name('siswa.store');
        Route::put('/siswa/{siswa}', [AdminController::class, 'siswaUpdate'])->name('siswa.update');
        Route::patch('/siswa/{siswa}/status', [AdminController::class, 'siswaToggleStatus'])->name('siswa.status');
        Route::post('/siswa/{siswa}/plot', [AdminController::class, 'siswaPlot'])->name('siswa.plot');
        Route::delete('/siswa/{siswa}', [AdminController::class, 'siswaDestroy'])->name('siswa.destroy');

        // Master Data Mitra DUDI
        Route::get('/dudi', [AdminController::class, 'dudiIndex'])->name('dudi');
        Route::post('/dudi', [AdminController::class, 'dudiStore'])->name('dudi.store');
        Route::put('/dudi/{dudi}', [AdminController::class, 'dudiUpdate'])->name('dudi.update');
        Route::patch('/dudi/{dudi}/verify', [AdminController::class, 'dudiVerify'])->name('dudi.verify');
        Route::delete('/dudi/{dudi}', [AdminController::class, 'dudiDestroy'])->name('dudi.destroy');

        // Permohonan Magang Siswa
        Route::get('/pengajuan', [AdminController::class, 'pengajuanIndex'])->name('pengajuan');
        Route::patch('/pengajuan/{pengajuan}/review', [AdminController::class, 'pengajuanReview'])->name('pengajuan.review');

        // Manajemen Penempatan Magang
        Route::get('/penempatan', [AdminController::class, 'penempatanIndex'])->name('penempatan');
        Route::post('/penempatan', [AdminController::class, 'penempatanStore'])->name('penempatan.store');
        Route::put('/penempatan/{penempatan}', [AdminController::class, 'penempatanUpdate'])->name('penempatan.update');
        Route::patch('/penempatan/{penempatan}/sahkan', [AdminController::class, 'penempatanSahkan'])->name('penempatan.sahkan');
        Route::delete('/penempatan/{penempatan}', [AdminController::class, 'penempatanDestroy'])->name('penempatan.destroy');

        // Pengaturan Sistem (3 Tab)
        Route::get('/settings', [AdminController::class, 'settingsIndex'])->name('settings');
        Route::put('/settings', [AdminController::class, 'settingsUpdate'])->name('settings.update');

        // Log Aktivitas & Audit
        Route::get('/logs', [AdminController::class, 'logsIndex'])->name('logs');
        Route::delete('/logs', [AdminController::class, 'logsClear'])->name('logs.clear');
    });

// Modul Guru Pembimbing
Route::prefix('guru')
    ->name('guru.')
    ->middleware(['auth', 'role:guru'])
    ->group(function () {
        Route::get('/dashboard', [GuruController::class, 'dashboard'])->name('dashboard');
        Route::get('/siswa', [GuruController::class, 'siswaIndex'])->name('siswa');
        Route::patch('/siswa/{penempatan}/nilai', [GuruController::class, 'siswaNilai'])->name('siswa.nilai');

        Route::get('/jurnal', [GuruController::class, 'jurnalIndex'])->name('jurnal');
        Route::patch('/jurnal/{jurnal}/validasi', [GuruController::class, 'jurnalValidasi'])->name('jurnal.validasi');
        Route::patch('/absensi/{absensi}/validasi', [GuruController::class, 'absensiValidasi'])->name('absensi.validasi');

        Route::get('/kunjungan', [GuruController::class, 'kunjunganIndex'])->name('kunjungan');
        Route::post('/kunjungan', [GuruController::class, 'kunjunganStore'])->name('kunjungan.store');
        Route::put('/kunjungan/{kunjungan}', [GuruController::class, 'kunjunganUpdate'])->name('kunjungan.update');
        Route::delete('/kunjungan/{kunjungan}', [GuruController::class, 'kunjunganDestroy'])->name('kunjungan.destroy');
    });

// Modul Siswa Magang
Route::prefix('siswa')
    ->name('siswa.')
    ->middleware(['auth', 'role:siswa'])
    ->group(function () {
        Route::get('/dashboard', [SiswaController::class, 'dashboard'])->name('dashboard');

        Route::get('/pengajuan', [SiswaController::class, 'pengajuanIndex'])->name('pengajuan');
        Route::post('/pengajuan', [SiswaController::class, 'pengajuanStore'])->name('pengajuan.store');

        Route::get('/absensi', [SiswaController::class, 'absensiIndex'])->name('absensi');
        Route::post('/absensi', [SiswaController::class, 'absensiStore'])->name('absensi.store');
        Route::post('/absensi/clock-out', [SiswaController::class, 'absensiClockOut'])->name('absensi.clockout');

        Route::get('/jurnal', [SiswaController::class, 'jurnalIndex'])->name('jurnal');
        Route::post('/jurnal', [SiswaController::class, 'jurnalStore'])->name('jurnal.store');
        Route::put('/jurnal/{jurnal}', [SiswaController::class, 'jurnalUpdate'])->name('jurnal.update');
        Route::delete('/jurnal/{jurnal}', [SiswaController::class, 'jurnalDestroy'])->name('jurnal.destroy');
    });
