<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\ActivityLog;
use App\Models\Guru;
use App\Models\JurnalHarian;
use App\Models\PenempatanMagang;
use App\Models\PengajuanMagang;
use App\Models\Profile;
use App\Models\Setting;
use App\Models\Siswa;
use App\Models\TempatMagang;
use App\Services\ActivityLogger;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function dashboard()
    {
        $totalSiswa = Siswa::count();
        $totalGuru = Guru::count();
        $totalDudi = TempatMagang::count();
        $menungguValidasi = PengajuanMagang::where('status', 'menunggu')->count();

        // Status Pengajuan breakdown
        $totalPengajuan = PengajuanMagang::count();
        $disetujuiCount = PengajuanMagang::where('status', 'disetujui')->count();
        $menungguCount = $menungguValidasi;
        $ditolakCount = PengajuanMagang::where('status', 'ditolak')->count();

        $pctDisetujui = $totalPengajuan > 0 ? round(($disetujuiCount / $totalPengajuan) * 100) : 0;
        $pctMenunggu = $totalPengajuan > 0 ? round(($menungguCount / $totalPengajuan) * 100) : 0;
        $pctDitolak = $totalPengajuan > 0 ? round(($ditolakCount / $totalPengajuan) * 100) : 0;

        // Tren Pengajuan 6 bulan terakhir
        $months = [];
        $counts = [];
        $monthNames = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
            7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
        ];
        for ($i = 5; $i >= 0; $i--) {
            $dt = Carbon::now()->subMonths($i);
            $months[] = $monthNames[$dt->month] . ' ' . $dt->format('y');
            $c = PengajuanMagang::whereYear('tanggal_pengajuan', $dt->year)
                ->whereMonth('tanggal_pengajuan', $dt->month)
                ->count();
            $counts[] = $c;
        }

        // Distribusi DUDI
        $dudiList = TempatMagang::withCount([
            'penempatan as siswa_aktif_count' => function ($q) {
                $q->whereIn('status_pengesahan', ['belum_disahkan', 'disahkan']);
            }
        ])->orderByDesc('kuota')->get();

        // 5 log terbaru
        $recentLogs = ActivityLog::orderByDesc('timestamp')->limit(5)->get();

        // Daftar Permohonan Magang Siswa (Tabel Mockup Referensi)
        $daftarPermohonan = PengajuanMagang::with(['siswa.profile', 'tempatMagang'])
            ->orderByDesc('tanggal_pengajuan')
            ->limit(10)
            ->get();

        // Pengajuan menunggu untuk ditinjau cepat
        $pendingPengajuan = PengajuanMagang::with(['siswa.profile', 'tempatMagang'])
            ->where('status', 'menunggu')
            ->orderByDesc('tanggal_pengajuan')
            ->get();

        $activeGurus = Guru::with('profile')->where('is_active', true)->get();

        return view('admin.dashboard', compact(
            'totalSiswa',
            'totalGuru',
            'totalDudi',
            'menungguValidasi',
            'totalPengajuan',
            'disetujuiCount',
            'menungguCount',
            'ditolakCount',
            'pctDisetujui',
            'pctMenunggu',
            'pctDitolak',
            'months',
            'counts',
            'dudiList',
            'recentLogs',
            'daftarPermohonan',
            'pendingPengajuan',
            'activeGurus'
        ));
    }

    public function monitoring(Request $request)
    {
        $search = trim((string) $request->query('q', ''));
        $kelasFilter = $request->query('kelas', '');
        $perPage = (int) $request->query('per_page', 10);

        $allSiswaAktif = Siswa::where('is_active', true)->count();
        $totalHadir = Absensi::where('status', 'hadir')->count();
        $totalAbsensi = Absensi::count();
        $tingkatKehadiran = $totalAbsensi > 0 ? round(($totalHadir / $totalAbsensi) * 100, 1) : 100;
        $jurnalTerkumpul = JurnalHarian::count();

        // Hitung perlu perhatian (siswa dengan alfa > 0 atau jurnal revisi atau belum ditempatkan)
        $perluPerhatianCount = Siswa::whereHas('absensi', fn ($q) => $q->where('status', 'alfa'))
            ->orWhereHas('jurnal', fn ($q) => $q->where('status_verifikasi', 'revisi'))
            ->count();

        $kelasList = Siswa::select('kelas')->distinct()->orderBy('kelas')->pluck('kelas');

        $query = Siswa::with([
            'profile',
            'penempatanAktif.tempatMagang',
            'penempatanAktif.guru.profile',
            'absensi',
            'jurnal' => fn ($q) => $q->orderByDesc('tanggal'),
        ]);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nis', 'like', "%{$search}%")
                  ->orWhereHas('profile', fn ($p) => $p->where('nama', 'like', "%{$search}%"));
            });
        }

        if ($kelasFilter !== '') {
            $query->where('kelas', $kelasFilter);
        }

        $siswas = $query->orderBy('nis')->paginate($perPage)->withQueryString();

        return view('admin.monitoring', compact(
            'siswas',
            'allSiswaAktif',
            'tingkatKehadiran',
            'jurnalTerkumpul',
            'perluPerhatianCount',
            'kelasList',
            'search',
            'kelasFilter'
        ));
    }

    // ==========================================
    // MASTER DATA GURU
    // ==========================================
    public function guruIndex(Request $request)
    {
        $search = trim((string) $request->query('q', ''));
        $jurusanFilter = $request->query('jurusan', '');
        $statusFilter = $request->query('status', '');
        $perPage = (int) $request->query('per_page', 10);

        $totalGuru = Guru::count();
        $guruAktif = Guru::where('is_active', true)->count();
        $totalPenempatanAktif = PenempatanMagang::whereIn('status_pengesahan', ['belum_disahkan', 'disahkan'])->count();
        $rataRataBimbingan = $guruAktif > 0 ? round($totalPenempatanAktif / $guruAktif, 1) : 0;

        $jurusanList = Guru::select('jurusan')->distinct()->orderBy('jurusan')->pluck('jurusan');

        $query = Guru::with('profile')->withCount([
            'penempatan as bimbingan_aktif_count' => fn ($q) => $q->whereIn('status_pengesahan', ['belum_disahkan', 'disahkan'])
        ]);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nip', 'like', "%{$search}%")
                  ->orWhereHas('profile', fn ($p) => $p->where('nama', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
            });
        }

        if ($jurusanFilter !== '') {
            $query->where('jurusan', $jurusanFilter);
        }

        if ($statusFilter !== '') {
            $query->where('is_active', $statusFilter === 'aktif');
        }

        $gurus = $query->orderBy('created_at', 'desc')->paginate($perPage)->withQueryString();

        return view('admin.guru', compact(
            'gurus',
            'totalGuru',
            'guruAktif',
            'rataRataBimbingan',
            'jurusanList',
            'search',
            'jurusanFilter',
            'statusFilter'
        ));
    }

    public function guruStore(Request $request)
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:150'],
            'nip' => ['required', 'digits:18', 'unique:guru,nip'],
            'jurusan' => ['required', 'string', 'max:100'],
        ], [
            'nama.required' => 'Nama lengkap beserta gelar wajib diisi.',
            'nip.required' => 'NIP wajib diisi.',
            'nip.digits' => 'NIP harus terdiri dari tepat 18 digit angka.',
            'nip.unique' => 'NIP sudah terdaftar pada sistem.',
            'jurusan.required' => 'Kompetensi keahlian/jurusan wajib dipilih.',
        ]);

        // Buat email otomatis dari nama (contoh ahmad.fauzi@smk.sch.id)
        $cleanName = preg_replace('/[^a-zA-Z\s]/', '', explode(',', $validated['nama'])[0]);
        $words = array_values(array_filter(explode(' ', strtolower(trim($cleanName)))));
        $slug = implode('.', array_slice($words, 0, 2));
        if ($slug === '') {
            $slug = 'guru.' . substr($validated['nip'], -4);
        }
        $baseEmail = $slug . '@smk.sch.id';
        $email = $baseEmail;
        $counter = 1;
        while (Profile::where('email', $email)->exists()) {
            $email = $slug . $counter . '@smk.sch.id';
            $counter++;
        }

        $tempPassword = 'Guru#' . strtoupper(Str::random(6));

        DB::transaction(function () use ($validated, $email, $tempPassword) {
            $profile = Profile::create([
                'nama' => $validated['nama'],
                'email' => $email,
                'password' => Hash::make($tempPassword),
                'role' => 'guru',
                'is_active' => true,
            ]);

            Guru::create([
                'user_id' => $profile->id,
                'nip' => $validated['nip'],
                'jurusan' => $validated['jurusan'],
                'is_active' => true,
            ]);
        });

        ActivityLogger::log(
            actionType: 'GURU_CREATED',
            target: $validated['nama'] . ' (' . $validated['nip'] . ')',
            metadata: ['email' => $email, 'jurusan' => $validated['jurusan']]
        );

        return redirect()->route('admin.guru')
            ->with('toast_success', 'Data Guru Pembimbing dan akun login berhasil dibuat.')
            ->with('created_account', [
                'title' => 'Akun Guru Berhasil Dibuat',
                'nama' => $validated['nama'],
                'email' => $email,
                'password' => $tempPassword,
            ]);
    }

    public function guruUpdate(Request $request, Guru $guru)
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:150'],
            'jurusan' => ['required', 'string', 'max:100'],
        ], [
            'nama.required' => 'Nama lengkap guru wajib diisi.',
            'jurusan.required' => 'Kompetensi keahlian/jurusan wajib dipilih.',
        ]);

        DB::transaction(function () use ($guru, $validated) {
            $guru->update(['jurusan' => $validated['jurusan']]);
            $guru->profile?->update(['nama' => $validated['nama']]);
        });

        ActivityLogger::log(
            actionType: 'GURU_UPDATED',
            target: $validated['nama'] . ' (' . $guru->nip . ')',
            metadata: ['jurusan' => $validated['jurusan']]
        );

        return redirect()->route('admin.guru')->with('toast_success', 'Data Guru Pembimbing berhasil diperbarui.');
    }

    public function guruToggleStatus(Request $request, Guru $guru)
    {
        $validated = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        $newStatus = (bool) $validated['is_active'];

        DB::transaction(function () use ($guru, $newStatus) {
            $guru->update(['is_active' => $newStatus]);
            $guru->profile?->update(['is_active' => $newStatus]);
        });

        ActivityLogger::log(
            actionType: 'GURU_STATUS_CHANGED',
            target: ($guru->profile?->nama ?? $guru->nip),
            metadata: ['is_active' => $newStatus]
        );

        return redirect()->route('admin.guru')->with('toast_success', 'Status aktif guru berhasil diperbarui.');
    }

    public function guruDestroy(Guru $guru)
    {
        $activeCount = $guru->penempatan()->whereIn('status_pengesahan', ['belum_disahkan', 'disahkan'])->count();
        if ($activeCount > 0) {
            return redirect()->route('admin.guru')->with('toast_error', "Penghapusan ditolak! Guru ini masih membimbing {$activeCount} siswa aktif.");
        }

        $nama = $guru->profile?->nama ?? $guru->nip;
        $nip = $guru->nip;

        DB::transaction(function () use ($guru) {
            $profile = $guru->profile;
            $guru->delete();
            $profile?->delete();
        });

        ActivityLogger::log(
            actionType: 'GURU_DELETED',
            target: "{$nama} ({$nip})",
            metadata: ['nip' => $nip],
            level: 'WARN'
        );

        return redirect()->route('admin.guru')->with('toast_success', 'Data Guru beserta akun login berhasil dihapus.');
    }

    // ==========================================
    // MASTER DATA SISWA
    // ==========================================
    public function siswaIndex(Request $request)
    {
        $search = trim((string) $request->query('q', ''));
        $kelasFilter = $request->query('kelas', '');
        $industriFilter = $request->query('industri', '');
        $statusFilter = $request->query('status', '');
        $perPage = (int) $request->query('per_page', 10);

        $totalSiswa = Siswa::count();
        $sedangMagang = Siswa::where('status_magang', 'sedang_magang')->count();
        $belumMagang = Siswa::whereIn('status_magang', ['belum_magang', 'pengajuan'])->count();
        $lulusMagang = Siswa::where('status_magang', 'lulus')->count();

        $kelasList = Siswa::select('kelas')->distinct()->orderBy('kelas')->pluck('kelas');
        $allDudi = TempatMagang::withCount([
            'penempatan as siswa_aktif_count' => fn ($q) => $q->whereIn('status_pengesahan', ['belum_disahkan', 'disahkan'])
        ])->orderBy('nama_perusahaan')->get();

        $verifiedAvailableDudi = $allDudi->filter(fn ($d) => $d->is_verified && $d->sisa_kuota > 0);
        $activeGurus = Guru::with('profile')->where('is_active', true)->orderBy('nip')->get();

        $query = Siswa::with(['profile', 'penempatanAktif.tempatMagang', 'penempatanAktif.guru.profile']);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nis', 'like', "%{$search}%")
                  ->orWhere('kelas', 'like', "%{$search}%")
                  ->orWhereHas('profile', fn ($p) => $p->where('nama', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
            });
        }

        if ($kelasFilter !== '') {
            $query->where('kelas', $kelasFilter);
        }

        if ($statusFilter !== '') {
            $query->where('status_magang', $statusFilter);
        }

        if ($industriFilter !== '') {
            $query->whereHas('penempatan', fn ($q) => $q->where('tempat_magang_id', $industriFilter));
        }

        $siswas = $query->orderBy('nis')->paginate($perPage)->withQueryString();

        return view('admin.siswa', compact(
            'siswas',
            'totalSiswa',
            'sedangMagang',
            'belumMagang',
            'lulusMagang',
            'kelasList',
            'allDudi',
            'verifiedAvailableDudi',
            'activeGurus',
            'search',
            'kelasFilter',
            'industriFilter',
            'statusFilter'
        ));
    }

    public function siswaStore(Request $request)
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:150'],
            'nis' => ['required', 'numeric', 'unique:siswa,nis'],
            'kelas' => ['required', 'string', 'max:50'],
        ], [
            'nama.required' => 'Nama lengkap siswa wajib diisi.',
            'nis.required' => 'NIS wajib diisi.',
            'nis.numeric' => 'NIS harus berupa angka numerik.',
            'nis.unique' => 'NIS sudah terdaftar.',
            'kelas.required' => 'Kelas wajib dipilih.',
        ]);

        $email = $validated['nis'] . '@siswa.smk.sch.id';
        if (Profile::where('email', $email)->exists()) {
            $email = $validated['nis'] . '.' . strtolower(Str::random(3)) . '@siswa.smk.sch.id';
        }
        $tempPassword = 'Siswa#' . strtoupper(Str::random(6));

        DB::transaction(function () use ($validated, $email, $tempPassword) {
            $profile = Profile::create([
                'nama' => $validated['nama'],
                'email' => $email,
                'password' => Hash::make($tempPassword),
                'role' => 'siswa',
                'is_active' => true,
            ]);

            Siswa::create([
                'user_id' => $profile->id,
                'nis' => $validated['nis'],
                'kelas' => $validated['kelas'],
                'status_magang' => 'belum_magang',
                'is_active' => true,
            ]);
        });

        ActivityLogger::log(
            actionType: 'SISWA_CREATED',
            target: $validated['nama'] . ' (' . $validated['nis'] . ')',
            metadata: ['email' => $email, 'kelas' => $validated['kelas']]
        );

        return redirect()->route('admin.siswa')
            ->with('toast_success', 'Data Siswa dan akun login berhasil dibuat.')
            ->with('created_account', [
                'title' => 'Akun Siswa Berhasil Dibuat',
                'nama' => $validated['nama'],
                'email' => $email,
                'password' => $tempPassword,
            ]);
    }

    public function siswaUpdate(Request $request, Siswa $siswa)
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:150'],
            'kelas' => ['required', 'string', 'max:50'],
        ], [
            'nama.required' => 'Nama lengkap siswa wajib diisi.',
            'kelas.required' => 'Kelas wajib dipilih.',
        ]);

        DB::transaction(function () use ($siswa, $validated) {
            $siswa->update(['kelas' => $validated['kelas']]);
            $siswa->profile?->update(['nama' => $validated['nama']]);
        });

        ActivityLogger::log(
            actionType: 'SISWA_UPDATED',
            target: $validated['nama'] . ' (' . $siswa->nis . ')',
            metadata: ['kelas' => $validated['kelas']]
        );

        return redirect()->route('admin.siswa')->with('toast_success', 'Data Siswa berhasil diperbarui.');
    }

    public function siswaToggleStatus(Request $request, Siswa $siswa)
    {
        $validated = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        $newStatus = (bool) $validated['is_active'];

        DB::transaction(function () use ($siswa, $newStatus) {
            $siswa->update(['is_active' => $newStatus]);
            $siswa->profile?->update(['is_active' => $newStatus]);
        });

        ActivityLogger::log(
            actionType: 'SISWA_STATUS_CHANGED',
            target: ($siswa->profile?->nama ?? $siswa->nis),
            metadata: ['is_active' => $newStatus]
        );

        return redirect()->route('admin.siswa')->with('toast_success', 'Status aktif akun siswa berhasil diperbarui.');
    }

    public function siswaPlot(Request $request, Siswa $siswa)
    {
        $validated = $request->validate([
            'guru_id' => ['required', 'uuid', 'exists:guru,id'],
            'tempat_magang_id' => ['required', 'uuid', 'exists:tempat_magang,id'],
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after:tanggal_mulai'],
        ], [
            'guru_id.required' => 'Guru pembimbing wajib dipilih.',
            'tempat_magang_id.required' => 'Tempat magang (DUDI) wajib dipilih.',
            'tanggal_selesai.after' => 'Tanggal selesai harus setelah tanggal mulai.',
        ]);

        $dudi = TempatMagang::findOrFail($validated['tempat_magang_id']);
        if (!$dudi->is_verified) {
            return back()->with('toast_error', 'Mitra DUDI yang dipilih belum terverifikasi.');
        }

        if ($dudi->sisa_kuota <= 0) {
            return back()->with('toast_error', 'Kuota pada mitra DUDI ' . $dudi->nama_perusahaan . ' sudah penuh.');
        }

        $mulai = $validated['tanggal_mulai'] ?? now()->toDateString();
        $selesai = $validated['tanggal_selesai'] ?? now()->addMonths(3)->toDateString();

        DB::transaction(function () use ($siswa, $validated, $mulai, $selesai) {
            PenempatanMagang::updateOrCreate(
                ['siswa_id' => $siswa->id],
                [
                    'tempat_magang_id' => $validated['tempat_magang_id'],
                    'guru_id' => $validated['guru_id'],
                    'status_pengesahan' => 'disahkan',
                    'tanggal_mulai' => $mulai,
                    'tanggal_selesai' => $selesai,
                    'catatan_admin' => 'Plotting langsung melalui Master Data Siswa',
                ]
            );

            $siswa->update(['status_magang' => 'sedang_magang']);
        });

        ActivityLogger::log(
            actionType: 'SISWA_PLOTTED',
            target: ($siswa->profile?->nama ?? $siswa->nis) . ' -> ' . $dudi->nama_perusahaan,
            metadata: ['siswa_id' => $siswa->id, 'tempat_magang_id' => $dudi->id, 'guru_id' => $validated['guru_id']]
        );

        return redirect()->route('admin.siswa')->with('toast_success', 'Plotting Guru Pembimbing & Mitra DUDI berhasil disimpan.');
    }

    public function siswaDestroy(Siswa $siswa)
    {
        $nama = $siswa->profile?->nama ?? $siswa->nis;
        $nis = $siswa->nis;

        DB::transaction(function () use ($siswa) {
            $profile = $siswa->profile;
            $siswa->delete();
            $profile?->delete();
        });

        ActivityLogger::log(
            actionType: 'SISWA_DELETED',
            target: "{$nama} ({$nis})",
            metadata: ['nis' => $nis, 'cascade' => true],
            level: 'WARN'
        );

        return redirect()->route('admin.siswa')->with('toast_success', 'Data Siswa beserta seluruh riwayat penempatan, presensi, dan jurnal berhasil dihapus.');
    }

    // ==========================================
    // MASTER DATA MITRA DUDI
    // ==========================================
    public function dudiIndex(Request $request)
    {
        $search = trim((string) $request->query('q', ''));
        $statusFilter = $request->query('status', '');
        $perPage = (int) $request->query('per_page', 10);

        $totalDudi = TempatMagang::count();
        $terverifikasi = TempatMagang::where('is_verified', true)->count();
        $menungguValidasi = TempatMagang::where('is_verified', false)->count();
        $siswaDitempatkan = PenempatanMagang::whereIn('status_pengesahan', ['belum_disahkan', 'disahkan'])->count();

        $query = TempatMagang::withCount([
            'penempatan as siswa_aktif_count' => fn ($q) => $q->whereIn('status_pengesahan', ['belum_disahkan', 'disahkan'])
        ]);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nama_perusahaan', 'like', "%{$search}%")
                  ->orWhere('bidang_usaha', 'like', "%{$search}%")
                  ->orWhere('penanggung_jawab', 'like', "%{$search}%")
                  ->orWhere('alamat', 'like', "%{$search}%");
            });
        }

        if ($statusFilter !== '') {
            $query->where('is_verified', $statusFilter === 'verified');
        }

        $dudis = $query->orderByDesc('created_at')->paginate($perPage)->withQueryString();

        return view('admin.dudi', compact(
            'dudis',
            'totalDudi',
            'terverifikasi',
            'menungguValidasi',
            'siswaDitempatkan',
            'search',
            'statusFilter'
        ));
    }

    public function dudiStore(Request $request)
    {
        $validated = $request->validate([
            'nama_perusahaan' => ['required', 'string', 'max:150'],
            'bidang_usaha' => ['required', 'string', 'max:150'],
            'penanggung_jawab' => ['required', 'string', 'max:150'],
            'kontak_pic' => ['required', 'string', 'max:50'],
            'kuota' => ['required', 'integer', 'min:1', 'max:500'],
            'is_verified' => ['required', 'boolean'],
            'alamat' => ['required', 'string'],
        ], [
            'nama_perusahaan.required' => 'Nama perusahaan wajib diisi.',
            'bidang_usaha.required' => 'Bidang usaha wajib diisi.',
            'penanggung_jawab.required' => 'Nama PIC wajib diisi.',
            'kontak_pic.required' => 'Kontak PIC (WA/Telp) wajib diisi.',
            'kuota.required' => 'Kuota magang wajib diisi.',
            'kuota.min' => 'Kuota minimal 1 siswa.',
            'alamat.required' => 'Alamat lengkap perusahaan wajib diisi.',
        ]);

        $dudi = TempatMagang::create($validated);

        ActivityLogger::log(
            actionType: 'DUDI_CREATED',
            target: $dudi->nama_perusahaan,
            metadata: ['kuota' => $dudi->kuota, 'is_verified' => $dudi->is_verified]
        );

        return redirect()->route('admin.dudi')->with('toast_success', 'Data Mitra DUDI berhasil ditambahkan.');
    }

    public function dudiUpdate(Request $request, TempatMagang $dudi)
    {
        $validated = $request->validate([
            'nama_perusahaan' => ['required', 'string', 'max:150'],
            'bidang_usaha' => ['required', 'string', 'max:150'],
            'penanggung_jawab' => ['required', 'string', 'max:150'],
            'kontak_pic' => ['required', 'string', 'max:50'],
            'kuota' => ['required', 'integer', 'min:0', 'max:500'],
            'is_verified' => ['required', 'boolean'],
            'alamat' => ['required', 'string'],
        ]);

        $dudi->update($validated);

        ActivityLogger::log(
            actionType: 'DUDI_UPDATED',
            target: $dudi->nama_perusahaan,
            metadata: ['kuota' => $dudi->kuota, 'is_verified' => $dudi->is_verified]
        );

        return redirect()->route('admin.dudi')->with('toast_success', 'Data Mitra DUDI berhasil diperbarui.');
    }

    public function dudiVerify(Request $request, TempatMagang $dudi)
    {
        $validated = $request->validate([
            'is_verified' => ['required', 'boolean'],
        ]);

        $dudi->update(['is_verified' => (bool) $validated['is_verified']]);

        ActivityLogger::log(
            actionType: 'DUDI_VERIFICATION_CHANGED',
            target: $dudi->nama_perusahaan,
            metadata: ['is_verified' => $dudi->is_verified]
        );

        return redirect()->route('admin.dudi')->with('toast_success', 'Status verifikasi Mitra DUDI berhasil diperbarui.');
    }

    public function dudiDestroy(TempatMagang $dudi)
    {
        $activeSiswa = $dudi->penempatan()->whereIn('status_pengesahan', ['belum_disahkan', 'disahkan'])->count();
        if ($activeSiswa > 0) {
            return redirect()->route('admin.dudi')->with('toast_error', "Penghapusan ditolak! Masih terdapat {$activeSiswa} siswa aktif magang di {$dudi->nama_perusahaan}.");
        }

        $nama = $dudi->nama_perusahaan;
        $dudi->delete();

        ActivityLogger::log(
            actionType: 'DUDI_DELETED',
            target: $nama,
            metadata: [],
            level: 'WARN'
        );

        return redirect()->route('admin.dudi')->with('toast_success', 'Data Mitra DUDI berhasil dihapus.');
    }

    // ==========================================
    // MANAJEMEN PENEMPATAN & PENGAJUAN
    // ==========================================
    public function penempatanIndex(Request $request)
    {
        $search = trim((string) $request->query('q', ''));
        $kelasFilter = $request->query('kelas', '');
        $dudiFilter = $request->query('dudi', '');
        $statusFilter = $request->query('status', '');
        $perPage = (int) $request->query('per_page', 10);

        $totalPenempatan = PenempatanMagang::count();
        $sedangBerlangsung = PenempatanMagang::where('status_pengesahan', 'disahkan')->count();
        $selesaiMagang = PenempatanMagang::where('status_pengesahan', 'lulus_magang')->count();
        $dudiTerlibat = PenempatanMagang::distinct('tempat_magang_id')->count('tempat_magang_id');

        $kelasList = Siswa::select('kelas')->distinct()->orderBy('kelas')->pluck('kelas');
        $dudiList = TempatMagang::withCount([
            'penempatan as siswa_aktif_count' => fn ($q) => $q->whereIn('status_pengesahan', ['belum_disahkan', 'disahkan'])
        ])->orderBy('nama_perusahaan')->get();

        $availableSiswas = Siswa::with('profile')->where('is_active', true)->orderBy('nis')->get();
        $activeGurus = Guru::with('profile')->where('is_active', true)->orderBy('nip')->get();

        $query = PenempatanMagang::with(['siswa.profile', 'tempatMagang', 'guru.profile']);

        if ($search !== '') {
            $query->whereHas('siswa', function ($q) use ($search) {
                $q->where('nis', 'like', "%{$search}%")
                  ->orWhereHas('profile', fn ($p) => $p->where('nama', 'like', "%{$search}%"));
            });
        }

        if ($kelasFilter !== '') {
            $query->whereHas('siswa', fn ($q) => $q->where('kelas', $kelasFilter));
        }

        if ($dudiFilter !== '') {
            $query->where('tempat_magang_id', $dudiFilter);
        }

        if ($statusFilter !== '') {
            $query->where('status_pengesahan', $statusFilter);
        }

        $penempatans = $query->orderByDesc('created_at')->paginate($perPage)->withQueryString();

        // Daftar pengajuan siswa (menunggu / riwayat)
        $pengajuans = PengajuanMagang::with(['siswa.profile', 'tempatMagang'])
            ->orderByRaw("CASE WHEN status = 'menunggu' THEN 0 ELSE 1 END")
            ->orderByDesc('tanggal_pengajuan')
            ->get();

        return view('admin.penempatan', compact(
            'penempatans',
            'pengajuans',
            'totalPenempatan',
            'sedangBerlangsung',
            'selesaiMagang',
            'dudiTerlibat',
            'kelasList',
            'dudiList',
            'availableSiswas',
            'activeGurus',
            'search',
            'kelasFilter',
            'dudiFilter',
            'statusFilter'
        ));
    }

    public function penempatanStore(Request $request)
    {
        $validated = $request->validate([
            'siswa_id' => ['required', 'uuid', 'exists:siswa,id'],
            'tempat_magang_id' => ['required', 'uuid', 'exists:tempat_magang,id'],
            'guru_id' => ['required', 'uuid', 'exists:guru,id'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after:tanggal_mulai'],
            'catatan_admin' => ['nullable', 'string'],
        ], [
            'siswa_id.required' => 'Siswa wajib dipilih.',
            'tempat_magang_id.required' => 'Tempat magang wajib dipilih.',
            'guru_id.required' => 'Guru pembimbing wajib dipilih.',
            'tanggal_mulai.required' => 'Tanggal mulai wajib diisi.',
            'tanggal_selesai.required' => 'Tanggal selesai wajib diisi.',
            'tanggal_selesai.after' => 'Tanggal selesai harus lebih besar dari tanggal mulai.',
        ]);

        $dudi = TempatMagang::findOrFail($validated['tempat_magang_id']);
        if (!$dudi->is_verified) {
            return back()->with('toast_error', 'Mitra DUDI belum diverifikasi.');
        }

        if ($dudi->sisa_kuota <= 0) {
            return back()->with('toast_error', "Penempatan ditolak! Kuota Mitra DUDI {$dudi->nama_perusahaan} sudah penuh.");
        }

        $siswa = Siswa::with('profile')->findOrFail($validated['siswa_id']);

        DB::transaction(function () use ($validated, $siswa) {
            PenempatanMagang::create([
                'siswa_id' => $validated['siswa_id'],
                'tempat_magang_id' => $validated['tempat_magang_id'],
                'guru_id' => $validated['guru_id'],
                'status_pengesahan' => 'disahkan',
                'tanggal_mulai' => $validated['tanggal_mulai'],
                'tanggal_selesai' => $validated['tanggal_selesai'],
                'catatan_admin' => $validated['catatan_admin'] ?? null,
            ]);

            $siswa->update(['status_magang' => 'sedang_magang']);
        });

        ActivityLogger::log(
            actionType: 'PENEMPATAN_CREATED',
            target: ($siswa->profile?->nama ?? $siswa->nis) . ' -> ' . $dudi->nama_perusahaan,
            metadata: $validated
        );

        return redirect()->route('admin.penempatan')->with('toast_success', 'Data penempatan magang berhasil ditambahkan.');
    }

    public function penempatanUpdate(Request $request, PenempatanMagang $penempatan)
    {
        $validated = $request->validate([
            'tempat_magang_id' => ['required', 'uuid', 'exists:tempat_magang,id'],
            'guru_id' => ['required', 'uuid', 'exists:guru,id'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after:tanggal_mulai'],
            'catatan_admin' => ['nullable', 'string'],
        ], [
            'tanggal_selesai.after' => 'Tanggal selesai harus setelah tanggal mulai.',
        ]);

        if ($validated['tempat_magang_id'] !== $penempatan->tempat_magang_id) {
            $newDudi = TempatMagang::findOrFail($validated['tempat_magang_id']);
            if ($newDudi->sisa_kuota <= 0) {
                return back()->with('toast_error', "Kuota pada {$newDudi->nama_perusahaan} sudah penuh.");
            }
        }

        $penempatan->update($validated);

        ActivityLogger::log(
            actionType: 'PENEMPATAN_UPDATED',
            target: ($penempatan->siswa?->profile?->nama ?? 'Siswa'),
            metadata: $validated
        );

        return redirect()->route('admin.penempatan')->with('toast_success', 'Data penempatan magang berhasil diperbarui.');
    }

    public function penempatanSahkan(Request $request, PenempatanMagang $penempatan)
    {
        $validated = $request->validate([
            'status_pengesahan' => ['required', Rule::in(['belum_disahkan', 'disahkan', 'lulus_magang'])],
            'catatan_admin' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($penempatan, $validated) {
            $penempatan->update([
                'status_pengesahan' => $validated['status_pengesahan'],
                'catatan_admin' => $validated['catatan_admin'] ?? $penempatan->catatan_admin,
            ]);

            $siswaStatus = match ($validated['status_pengesahan']) {
                'disahkan' => 'sedang_magang',
                'lulus_magang' => 'lulus',
                default => 'pengajuan',
            };

            $penempatan->siswa?->update(['status_magang' => $siswaStatus]);
        });

        ActivityLogger::log(
            actionType: 'PENEMPATAN_STATUS_CHANGED',
            target: ($penempatan->siswa?->profile?->nama ?? 'Siswa'),
            metadata: ['status_pengesahan' => $validated['status_pengesahan']]
        );

        return redirect()->route('admin.penempatan')->with('toast_success', 'Status pengesahan penempatan dan status magang siswa berhasil disinkronkan.');
    }

    public function penempatanDestroy(PenempatanMagang $penempatan)
    {
        $siswa = $penempatan->siswa;
        $targetName = ($siswa?->profile?->nama ?? 'Siswa') . ' - ' . ($penempatan->tempatMagang?->nama_perusahaan ?? 'DUDI');

        DB::transaction(function () use ($penempatan, $siswa) {
            $penempatan->delete();
            $siswa?->update(['status_magang' => 'belum_magang']);
        });

        ActivityLogger::log(
            actionType: 'PENEMPATAN_CANCELLED',
            target: $targetName,
            metadata: ['siswa_status' => 'belum_magang'],
            level: 'WARN'
        );

        return redirect()->route('admin.penempatan')->with('toast_success', 'Penempatan magang dibatalkan dan status siswa dikembalikan ke Belum Magang.');
    }

    public function pengajuanIndex(Request $request)
    {
        $search = trim((string) $request->query('q', ''));
        $statusFilter = $request->query('status', '');
        $dudiFilter = $request->query('dudi', '');
        $perPage = (int) $request->query('per_page', 10);

        $totalPengajuan = PengajuanMagang::count();
        $menungguCount = PengajuanMagang::where('status', 'menunggu')->count();
        $disetujuiCount = PengajuanMagang::where('status', 'disetujui')->count();
        $ditolakCount = PengajuanMagang::where('status', 'ditolak')->count();

        $query = PengajuanMagang::with(['siswa.profile', 'tempatMagang']);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->whereHas('siswa', function ($s) use ($search) {
                    $s->where('nis', 'like', "%{$search}%")
                      ->orWhereHas('profile', fn ($p) => $p->where('nama', 'like', "%{$search}%"));
                })->orWhereHas('tempatMagang', fn ($d) => $d->where('nama_perusahaan', 'like', "%{$search}%"));
            });
        }

        if ($statusFilter !== '') {
            $query->where('status', $statusFilter);
        }

        if ($dudiFilter !== '') {
            $query->where('tempat_magang_id', $dudiFilter);
        }

        $pengajuans = $query->orderByRaw("CASE WHEN status = 'menunggu' THEN 0 ELSE 1 END")
            ->orderByDesc('tanggal_pengajuan')
            ->paginate($perPage)
            ->withQueryString();

        $dudiList = TempatMagang::where('is_verified', true)->orderBy('nama_perusahaan')->get();
        $activeGurus = Guru::with('profile')->where('is_active', true)->get();

        return view('admin.pengajuan', compact(
            'pengajuans',
            'totalPengajuan',
            'menungguCount',
            'disetujuiCount',
            'ditolakCount',
            'dudiList',
            'activeGurus',
            'search',
            'statusFilter',
            'dudiFilter'
        ));
    }

    public function pengajuanReview(Request $request, PengajuanMagang $pengajuan)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['disetujui', 'ditolak'])],
            'guru_id' => ['required_if:status,disetujui', 'nullable', 'uuid', 'exists:guru,id'],
            'catatan_penolakan' => ['required_if:status,ditolak', 'nullable', 'string'],
        ], [
            'guru_id.required_if' => 'Pilih Guru Pembimbing untuk menyetujui pengajuan magang.',
            'catatan_penolakan.required_if' => 'Alasan penolakan wajib diisi saat menolak pengajuan.',
        ]);

        $siswa = $pengajuan->siswa;
        $dudi = $pengajuan->tempatMagang;

        if ($validated['status'] === 'disetujui') {
            if ($dudi && $dudi->sisa_kuota <= 0) {
                return back()->with('toast_error', "Gagal menyetujui! Kuota {$dudi->nama_perusahaan} sudah penuh.");
            }

            DB::transaction(function () use ($pengajuan, $validated, $siswa) {
                $pengajuan->update([
                    'status' => 'disetujui',
                    'catatan_penolakan' => null,
                ]);

                PenempatanMagang::updateOrCreate(
                    ['siswa_id' => $pengajuan->siswa_id],
                    [
                        'tempat_magang_id' => $pengajuan->tempat_magang_id,
                        'guru_id' => $validated['guru_id'],
                        'status_pengesahan' => 'disahkan',
                        'tanggal_mulai' => $pengajuan->tanggal_mulai,
                        'tanggal_selesai' => $pengajuan->tanggal_selesai,
                        'catatan_admin' => 'Disetujui dari pengajuan mandiri siswa (' . $pengajuan->posisi . ')',
                    ]
                );

                $siswa?->update(['status_magang' => 'sedang_magang']);
            });

            ActivityLogger::log(
                actionType: 'PENGAJUAN_APPROVED',
                target: ($siswa?->profile?->nama ?? 'Siswa') . ' -> ' . ($dudi?->nama_perusahaan ?? 'DUDI'),
                metadata: ['pengajuan_id' => $pengajuan->id, 'guru_id' => $validated['guru_id']]
            );

            return back()->with('toast_success', 'Pengajuan magang disetujui dan penempatan siswa telah diaktifkan.');
        }

        DB::transaction(function () use ($pengajuan, $validated, $siswa) {
            $pengajuan->update([
                'status' => 'ditolak',
                'catatan_penolakan' => $validated['catatan_penolakan'],
            ]);

            $siswa?->update(['status_magang' => 'belum_magang']);
        });

        ActivityLogger::log(
            actionType: 'PENGAJUAN_REJECTED',
            target: ($siswa?->profile?->nama ?? 'Siswa') . ' -> ' . ($dudi?->nama_perusahaan ?? 'DUDI'),
            metadata: ['catatan_penolakan' => $validated['catatan_penolakan']],
            level: 'WARN'
        );

        return back()->with('toast_success', 'Pengajuan magang telah ditolak beserta catatan evaluasi.');
    }

    // ==========================================
    // PENGATURAN SISTEM (3 TAB)
    // ==========================================
    public function settingsIndex()
    {
        $settings = Setting::allKeyValue();
        return view('admin.settings', compact('settings'));
    }

    public function settingsUpdate(Request $request)
    {
        $validated = $request->validate([
            'app_name' => ['required', 'string', 'max:50'],
            'app_fullname' => ['required', 'string', 'max:150'],
            'app_description' => ['required', 'string', 'max:500'],
            'hero_title' => ['required', 'string', 'max:150'],
            'hero_description' => ['required', 'string', 'max:500'],
            'school_name' => ['required', 'string', 'max:150'],
            'school_website' => ['required', 'string', 'max:150'],
            'principal_name' => ['required', 'string', 'max:150'],
            'principal_nip' => ['required', 'string', 'max:30'],
            'school_address' => ['required', 'string', 'max:500'],
            'school_phone' => ['required', 'string', 'max:50'],
        ], [
            'app_name.required' => 'Nama aplikasi wajib diisi.',
            'hero_title.required' => 'Judul utama hero wajib diisi.',
            'school_name.required' => 'Nama sekolah wajib diisi.',
        ]);

        foreach ($validated as $key => $val) {
            Setting::updateOrCreate(['key' => $key], ['value' => $val]);
        }

        Cache::forget('simmas_settings');

        ActivityLogger::log(
            actionType: 'SETTINGS_UPDATED',
            target: 'Pengaturan Sistem',
            metadata: ['app_name' => $validated['app_name'], 'school_name' => $validated['school_name']]
        );

        return redirect()->route('admin.settings')->with('toast_success', 'Pengaturan sistem berhasil disimpan dan diterapkan ke seluruh antarmuka.');
    }

    // ==========================================
    // LOG AKTIVITAS & AUDIT
    // ==========================================
    public function logsIndex(Request $request)
    {
        $search = trim((string) $request->query('q', ''));
        $levelFilter = $request->query('level', '');
        $perPage = (int) $request->query('per_page', 10);

        $query = ActivityLog::query();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('action_type', 'like', "%{$search}%")
                  ->orWhere('actor_email', 'like', "%{$search}%")
                  ->orWhere('target', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        if ($levelFilter !== '') {
            $query->where('level', strtoupper($levelFilter));
        }

        $logs = $query->orderByDesc('timestamp')->paginate($perPage)->withQueryString();

        return view('admin.logs', compact('logs', 'search', 'levelFilter'));
    }

    public function logsClear()
    {
        $count = ActivityLog::count();
        ActivityLog::query()->delete();

        ActivityLogger::log(
            actionType: 'LOGS_CLEARED',
            target: 'Activity Logs',
            metadata: ['cleared_records' => $count],
            level: 'WARN'
        );

        return redirect()->route('admin.logs')->with('toast_success', 'Seluruh riwayat log aktivitas berhasil dibersihkan.');
    }
}

