<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\JurnalHarian;
use App\Models\PengajuanMagang;
use App\Models\TempatMagang;
use App\Services\ActivityLogger;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SiswaController extends Controller
{
    private function currentSiswa()
    {
        return Auth::user()?->siswa;
    }

    public function dashboard()
    {
        $siswa = $this->currentSiswa();
        $siswa?->load(['profile', 'penempatanAktif.tempatMagang', 'penempatanAktif.guru.profile']);

        $penempatan = $siswa?->penempatanAktif;

        // Hitung progres hari ke-n dari N hari
        $hariKe = 0;
        $totalHari = 0;
        $progressPct = 0;

        if ($penempatan && $penempatan->tanggal_mulai && $penempatan->tanggal_selesai) {
            $start = $penempatan->tanggal_mulai->copy()->startOfDay();
            $end = $penempatan->tanggal_selesai->copy()->startOfDay();
            $today = Carbon::today();

            $totalHari = max(1, $start->diffInDays($end) + 1);
            if ($today->lt($start)) {
                $hariKe = 0;
            } elseif ($today->gt($end)) {
                $hariKe = $totalHari;
            } else {
                $hariKe = $start->diffInDays($today) + 1;
            }
            $progressPct = min(100, max(0, round(($hariKe / $totalHari) * 100)));
        }

        $totalKehadiran = Absensi::where('siswa_id', $siswa?->id)->where('status', 'hadir')->count();
        $jurnalDitulis = JurnalHarian::where('siswa_id', $siswa?->id)->count();

        $absensiHariIni = Absensi::where('siswa_id', $siswa?->id)
            ->where('tanggal', Carbon::today()->toDateString())
            ->first();

        return view('siswa.dashboard', compact(
            'siswa',
            'penempatan',
            'hariKe',
            'totalHari',
            'progressPct',
            'totalKehadiran',
            'jurnalDitulis',
            'absensiHariIni'
        ));
    }

    public function pengajuanIndex()
    {
        $siswa = $this->currentSiswa();
        $siswa?->load(['penempatanAktif.tempatMagang']);

        $pengajuans = PengajuanMagang::with('tempatMagang')
            ->where('siswa_id', $siswa?->id)
            ->orderByDesc('tanggal_pengajuan')
            ->get();

        $latestPengajuan = $pengajuans->first();
        $penempatanAktif = $siswa?->penempatanAktif;

        $hasActiveOrPending = ($penempatanAktif && in_array($penempatanAktif->status_pengesahan, ['belum_disahkan', 'disahkan'], true))
            || $pengajuans->whereIn('status', ['menunggu', 'disetujui'])->isNotEmpty();

        // DUDI terverifikasi yang kuotanya masih tersedia
        $availableDudis = TempatMagang::withCount([
            'penempatan as siswa_aktif_count' => fn ($q) => $q->whereIn('status_pengesahan', ['belum_disahkan', 'disahkan'])
        ])
            ->where('is_verified', true)
            ->orderBy('nama_perusahaan')
            ->get()
            ->filter(fn ($d) => $d->sisa_kuota > 0);

        return view('siswa.pengajuan', compact(
            'siswa',
            'pengajuans',
            'latestPengajuan',
            'penempatanAktif',
            'hasActiveOrPending',
            'availableDudis'
        ));
    }

    public function pengajuanStore(Request $request)
    {
        $siswa = $this->currentSiswa();
        if (!$siswa) {
            abort(403);
        }

        // Cek apakah sudah punya pengajuan menunggu/disetujui atau penempatan aktif
        $hasPending = PengajuanMagang::where('siswa_id', $siswa->id)
            ->whereIn('status', ['menunggu', 'disetujui'])
            ->exists();
        $hasActivePlacement = $siswa->penempatan()
            ->whereIn('status_pengesahan', ['belum_disahkan', 'disahkan'])
            ->exists();

        if ($hasPending || $hasActivePlacement) {
            return back()->with('toast_error', 'Anda tidak dapat mengajukan permohonan baru karena masih memiliki pengajuan yang diproses atau penempatan magang aktif.');
        }

        $validated = $request->validate([
            'tempat_magang_id' => ['required', 'uuid', 'exists:tempat_magang,id'],
            'posisi' => ['required', 'string', 'max:150'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after:tanggal_mulai'],
        ], [
            'tempat_magang_id.required' => 'Perusahaan mitra DUDI wajib dipilih.',
            'posisi.required' => 'Posisi atau divisi yang diminati wajib diisi.',
            'tanggal_mulai.required' => 'Tanggal mulai magang wajib diisi.',
            'tanggal_selesai.required' => 'Tanggal selesai magang wajib diisi.',
            'tanggal_selesai.after' => 'Tanggal selesai harus lebih besar dari tanggal mulai.',
        ]);

        $dudi = TempatMagang::findOrFail($validated['tempat_magang_id']);
        if (!$dudi->is_verified) {
            return back()->with('toast_error', 'Perusahaan mitra yang dipilih belum terverifikasi.');
        }

        if ($dudi->sisa_kuota <= 0) {
            return back()->with('toast_error', "Kuota magang di {$dudi->nama_perusahaan} sudah penuh.");
        }

        DB::transaction(function () use ($siswa, $validated) {
            PengajuanMagang::create([
                'siswa_id' => $siswa->id,
                'tempat_magang_id' => $validated['tempat_magang_id'],
                'posisi' => $validated['posisi'],
                'status' => 'menunggu',
                'tanggal_mulai' => $validated['tanggal_mulai'],
                'tanggal_selesai' => $validated['tanggal_selesai'],
                'tanggal_pengajuan' => now(),
            ]);

            $siswa->update(['status_magang' => 'pengajuan']);
        });

        ActivityLogger::log(
            actionType: 'PENGAJUAN_CREATED',
            target: $dudi->nama_perusahaan . ' (' . $validated['posisi'] . ')',
            metadata: $validated
        );

        return redirect()->route('siswa.pengajuan')->with('toast_success', 'Pengajuan tempat magang berhasil dikirim dan sedang menunggu tinjauan sekolah.');
    }

    public function absensiIndex()
    {
        $siswa = $this->currentSiswa();
        $siswa?->load(['penempatanAktif.tempatMagang']);
        $penempatanAktif = $siswa?->penempatanAktif;

        $todayStr = Carbon::today()->toDateString();
        $absensiHariIni = Absensi::where('siswa_id', $siswa?->id)
            ->where('tanggal', $todayStr)
            ->first();

        $riwayatBulanIni = Absensi::where('siswa_id', $siswa?->id)
            ->orderByDesc('tanggal')
            ->get();

        return view('siswa.absensi', compact(
            'siswa',
            'penempatanAktif',
            'absensiHariIni',
            'riwayatBulanIni'
        ));
    }

    public function absensiStore(Request $request)
    {
        $siswa = $this->currentSiswa();
        if (!$siswa) {
            abort(403);
        }

        $hasActivePlacement = $siswa->penempatan()
            ->whereIn('status_pengesahan', ['disahkan'])
            ->exists();

        if (!$hasActivePlacement) {
            return back()->with('toast_error', 'Anda harus memiliki penempatan magang aktif yang sudah disahkan untuk melakukan presensi.');
        }

        $validated = $request->validate([
            'status' => ['required', Rule::in(['hadir', 'sakit', 'izin'])],
            'tanggal' => ['required', 'date', 'before_or_equal:today'],
            'jam_masuk' => ['nullable', 'string'],
            'keterangan' => ['nullable', 'string', 'max:500'],
            'photo_masuk' => [
                Rule::requiredIf(fn () => $request->input('status') === 'hadir'),
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ], [
            'status.required' => 'Status kehadiran wajib dipilih.',
            'tanggal.required' => 'Tanggal presensi wajib diisi.',
            'tanggal.before_or_equal' => 'Tidak diperbolehkan melakukan absensi untuk tanggal di masa depan.',
            'photo_masuk.required' => 'Foto bukti kehadiran (selfie lokasi) wajib diunggah untuk status Hadir.',
            'photo_masuk.image' => 'Bukti presensi harus berupa file gambar.',
            'photo_masuk.mimes' => 'Format file gambar harus JPG, PNG, atau WEBP.',
            'photo_masuk.max' => 'Ukuran file foto maksimal 2 MB.',
        ]);

        // Cek maksimal 1 absensi per hari
        $exists = Absensi::where('siswa_id', $siswa->id)
            ->where('tanggal', $validated['tanggal'])
            ->exists();

        if ($exists) {
            return back()->with('toast_error', 'Anda sudah melakukan presensi pada tanggal tersebut (maksimal 1 absensi per hari).');
        }

        $photoUrl = null;
        if ($request->hasFile('photo_masuk')) {
            $path = $request->file('photo_masuk')->store('absensi', 'public');
            $photoUrl = Storage::url($path);
        }

        // Status Sakit/Izin masuk antrean validasi guru (menunggu), Hadir langsung disetujui
        $statusValidasi = $validated['status'] === 'hadir' ? 'disetujui' : 'menunggu';
        $jamMasuk = $validated['jam_masuk'] ?: now()->format('H:i:s');

        Absensi::create([
            'siswa_id' => $siswa->id,
            'tanggal' => $validated['tanggal'],
            'jam_masuk' => $jamMasuk,
            'status' => $validated['status'],
            'photo_masuk_url' => $photoUrl,
            'keterangan' => $validated['keterangan'] ?? null,
            'status_validasi' => $statusValidasi,
        ]);

        ActivityLogger::log(
            actionType: 'ABSENSI_CLOCK_IN',
            target: 'Presensi ' . ucfirst($validated['status']) . ' (' . $validated['tanggal'] . ')',
            metadata: ['status' => $validated['status'], 'status_validasi' => $statusValidasi]
        );

        $msg = $validated['status'] === 'hadir'
            ? 'Clock In berhasil tercatat!'
            : 'Keterangan ' . strtoupper($validated['status']) . ' berhasil dikirim dan menunggu validasi Guru Pembimbing.';

        return redirect()->route('siswa.absensi')->with('toast_success', $msg);
    }

    public function absensiClockOut(Request $request)
    {
        $siswa = $this->currentSiswa();
        if (!$siswa) {
            abort(403);
        }

        $todayStr = Carbon::today()->toDateString();
        $absensi = Absensi::where('siswa_id', $siswa->id)
            ->where('tanggal', $todayStr)
            ->first();

        if (!$absensi) {
            return back()->with('toast_error', 'Anda belum melakukan Clock In hari ini.');
        }

        if ($absensi->jam_pulang) {
            return back()->with('toast_error', 'Anda sudah melakukan Clock Out hari ini.');
        }

        $request->validate([
            'photo_pulang' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'photo_pulang.image' => 'Foto pulang harus berupa file gambar.',
            'photo_pulang.mimes' => 'Format foto harus JPG, PNG, atau WEBP.',
            'photo_pulang.max' => 'Ukuran foto maksimal 2 MB.',
        ]);

        $photoPulangUrl = $absensi->photo_pulang_url;
        if ($request->hasFile('photo_pulang')) {
            $path = $request->file('photo_pulang')->store('absensi', 'public');
            $photoPulangUrl = Storage::url($path);
        }

        $absensi->update([
            'jam_pulang' => now()->format('H:i:s'),
            'photo_pulang_url' => $photoPulangUrl,
        ]);

        ActivityLogger::log(
            actionType: 'ABSENSI_CLOCK_OUT',
            target: 'Clock Out (' . $todayStr . ')',
            metadata: ['jam_pulang' => $absensi->jam_pulang]
        );

        return redirect()->route('siswa.absensi')->with('toast_success', 'Clock Out berhasil dicatat. Terima kasih atas kerja keras Anda hari ini!');
    }

    public function jurnalIndex(Request $request)
    {
        $siswa = $this->currentSiswa();
        $search = trim((string) $request->query('q', ''));

        $query = JurnalHarian::where('siswa_id', $siswa?->id);
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('kegiatan', 'like', "%{$search}%")
                  ->orWhere('kendala', 'like', "%{$search}%")
                  ->orWhere('solusi', 'like', "%{$search}%");
            });
        }

        $jurnals = $query->orderByDesc('tanggal')->orderByDesc('created_at')->get();

        return view('siswa.jurnal', compact('siswa', 'jurnals', 'search'));
    }

    public function jurnalStore(Request $request)
    {
        $siswa = $this->currentSiswa();
        if (!$siswa) {
            abort(403);
        }

        $validated = $request->validate([
            'tanggal' => ['required', 'date', 'before_or_equal:today'],
            'kegiatan' => ['required', 'string', 'min:15'],
            'kendala' => ['nullable', 'string'],
            'solusi' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'tanggal.required' => 'Tanggal pelaksanaan wajib diisi.',
            'tanggal.before_or_equal' => 'Tanggal jurnal tidak boleh di masa depan.',
            'kegiatan.required' => 'Rincian kegiatan wajib diisi.',
            'kegiatan.min' => 'Rincian kegiatan minimal 15 karakter agar jelas dan informatif.',
            'photo.image' => 'Foto bukti kegiatan harus berupa file gambar.',
            'photo.mimes' => 'Format foto harus JPG, PNG, atau WEBP.',
            'photo.max' => 'Ukuran foto maksimal 2 MB.',
        ]);

        $photoUrl = null;
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('jurnal', 'public');
            $photoUrl = Storage::url($path);
        }

        $jurnal = JurnalHarian::create([
            'siswa_id' => $siswa->id,
            'tanggal' => $validated['tanggal'],
            'kegiatan' => $validated['kegiatan'],
            'kendala' => $validated['kendala'] ?? null,
            'solusi' => $validated['solusi'] ?? null,
            'photo_url' => $photoUrl,
            'status_verifikasi' => 'menunggu',
        ]);

        ActivityLogger::log(
            actionType: 'JURNAL_CREATED',
            target: 'Jurnal Tanggal ' . $validated['tanggal'],
            metadata: ['jurnal_id' => $jurnal->id]
        );

        return redirect()->route('siswa.jurnal')->with('toast_success', 'Jurnal kegiatan harian berhasil dikirim dan menunggu evaluasi Guru Pembimbing.');
    }

    public function jurnalUpdate(Request $request, JurnalHarian $jurnal)
    {
        $siswa = $this->currentSiswa();
        if (!$siswa || $jurnal->siswa_id !== $siswa->id) {
            abort(403);
        }

        if ($jurnal->status_verifikasi === 'disetujui') {
            return back()->with('toast_error', 'Jurnal yang sudah disetujui oleh Guru Pembimbing tidak dapat diubah.');
        }

        $validated = $request->validate([
            'tanggal' => ['required', 'date', 'before_or_equal:today'],
            'kegiatan' => ['required', 'string', 'min:15'],
            'kendala' => ['nullable', 'string'],
            'solusi' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'kegiatan.min' => 'Rincian kegiatan minimal 15 karakter.',
        ]);

        $data = [
            'tanggal' => $validated['tanggal'],
            'kegiatan' => $validated['kegiatan'],
            'kendala' => $validated['kendala'] ?? null,
            'solusi' => $validated['solusi'] ?? null,
            'status_verifikasi' => 'menunggu',
        ];

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('jurnal', 'public');
            $data['photo_url'] = Storage::url($path);
        }

        $jurnal->update($data);

        ActivityLogger::log(
            actionType: 'JURNAL_UPDATED',
            target: 'Jurnal Tanggal ' . $validated['tanggal'],
            metadata: ['jurnal_id' => $jurnal->id]
        );

        return redirect()->route('siswa.jurnal')->with('toast_success', 'Jurnal kegiatan berhasil diperbarui dan dikirim ulang untuk verifikasi.');
    }

    public function jurnalDestroy(JurnalHarian $jurnal)
    {
        $siswa = $this->currentSiswa();
        if (!$siswa || $jurnal->siswa_id !== $siswa->id) {
            abort(403);
        }

        if ($jurnal->status_verifikasi === 'disetujui') {
            return redirect()->route('siswa.jurnal')->with('toast_error', 'Penghapusan ditolak! Jurnal yang sudah disetujui Guru Pembimbing tidak dapat dihapus.');
        }

        $tgl = $jurnal->tanggal->format('Y-m-d');
        $jurnal->delete();

        ActivityLogger::log(
            actionType: 'JURNAL_DELETED',
            target: 'Jurnal Tanggal ' . $tgl,
            metadata: [],
            level: 'WARN'
        );

        return redirect()->route('siswa.jurnal')->with('toast_success', 'Laporan jurnal berhasil dihapus.');
    }
}

