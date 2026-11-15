<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\JurnalHarian;
use App\Models\Kunjungan;
use App\Models\PenempatanMagang;
use App\Models\TempatMagang;
use App\Services\ActivityLogger;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class GuruController extends Controller
{
    private function currentGuru()
    {
        $user = Auth::user();
        return $user?->guru;
    }

    public function dashboard()
    {
        $guru = $this->currentGuru();
        $guruId = $guru?->id;

        $penempatans = PenempatanMagang::with(['siswa.profile', 'tempatMagang'])
            ->where('guru_id', $guruId)
            ->orderByDesc('created_at')
            ->get();

        $siswaIds = $penempatans->pluck('siswa_id')->unique();
        $totalSiswaBimbingan = $siswaIds->count();

        $jurnalPerluEvaluasi = JurnalHarian::with(['siswa.profile', 'siswa.penempatanAktif.tempatMagang'])
            ->whereIn('siswa_id', $siswaIds)
            ->where('status_verifikasi', 'menunggu')
            ->orderByDesc('tanggal')
            ->get();

        $jurnalBelumDinilaiCount = $jurnalPerluEvaluasi->count();

        $todayStr = Carbon::today()->toDateString();
        $absensiHariIni = Absensi::whereIn('siswa_id', $siswaIds)
            ->where('tanggal', $todayStr)
            ->get()
            ->keyBy('siswa_id');

        $hadirHariIniCount = $absensiHariIni->where('status', 'hadir')->count();

        return view('guru.dashboard', compact(
            'guru',
            'penempatans',
            'totalSiswaBimbingan',
            'jurnalPerluEvaluasi',
            'jurnalBelumDinilaiCount',
            'absensiHariIni',
            'hadirHariIniCount'
        ));
    }

    public function siswaIndex()
    {
        $guru = $this->currentGuru();
        $penempatans = PenempatanMagang::with([
            'siswa.profile',
            'siswa.absensi',
            'siswa.jurnal',
            'tempatMagang',
        ])
            ->where('guru_id', $guru?->id)
            ->orderByDesc('created_at')
            ->get();

        return view('guru.siswa', compact('guru', 'penempatans'));
    }

    public function siswaNilai(Request $request, PenempatanMagang $penempatan)
    {
        $guru = $this->currentGuru();
        if (!$guru || $penempatan->guru_id !== $guru->id) {
            abort(403, 'Anda hanya dapat memberi nilai kepada siswa bimbingan Anda sendiri.');
        }

        $validated = $request->validate([
            'nilai_akhir' => ['required', 'numeric', 'min:0', 'max:100'],
        ], [
            'nilai_akhir.required' => 'Nilai akhir wajib diisi.',
            'nilai_akhir.numeric' => 'Nilai akhir harus berupa angka.',
            'nilai_akhir.min' => 'Nilai akhir minimal 0.',
            'nilai_akhir.max' => 'Nilai akhir maksimal 100.',
        ]);

        $penempatan->update([
            'nilai_akhir' => $validated['nilai_akhir'],
            'status_pengesahan' => 'lulus_magang',
        ]);

        $penempatan->siswa?->update([
            'status_magang' => 'lulus',
        ]);

        ActivityLogger::log(
            actionType: 'PENILAIAN_UPDATED',
            target: ($penempatan->siswa?->profile?->nama ?? 'Siswa'),
            metadata: ['nilai_akhir' => $validated['nilai_akhir'], 'penempatan_id' => $penempatan->id]
        );

        return redirect()->route('guru.siswa')->with('toast_success', 'Nilai akhir magang siswa berhasil disimpan.');
    }

    public function jurnalIndex(Request $request)
    {
        $guru = $this->currentGuru();
        $tab = $request->query('tab', 'jurnal');

        $siswaIds = PenempatanMagang::where('guru_id', $guru?->id)->pluck('siswa_id')->unique();

        // Tab 1: Jurnal Kegiatan
        $jurnals = JurnalHarian::with(['siswa.profile', 'siswa.penempatanAktif.tempatMagang'])
            ->whereIn('siswa_id', $siswaIds)
            ->orderByRaw("CASE WHEN status_verifikasi = 'menunggu' THEN 0 ELSE 1 END")
            ->orderByDesc('tanggal')
            ->get();

        $jurnalMenunggu = $jurnals->where('status_verifikasi', 'menunggu')->count();
        $jurnalDisetujui = $jurnals->where('status_verifikasi', 'disetujui')->count();
        $jurnalRevisi = $jurnals->where('status_verifikasi', 'revisi')->count();

        // Tab 2: Absensi Siswa
        $absensis = Absensi::with(['siswa.profile', 'siswa.penempatanAktif.tempatMagang'])
            ->whereIn('siswa_id', $siswaIds)
            ->orderByRaw("CASE WHEN status_validasi = 'menunggu' THEN 0 ELSE 1 END")
            ->orderByDesc('tanggal')
            ->get();

        $absensiMenunggu = $absensis->where('status_validasi', 'menunggu')->count();
        $absensiDisetujui = $absensis->where('status_validasi', 'disetujui')->count();
        $absensiDitolak = $absensis->where('status_validasi', 'ditolak')->count();

        return view('guru.jurnal', compact(
            'tab',
            'jurnals',
            'jurnalMenunggu',
            'jurnalDisetujui',
            'jurnalRevisi',
            'absensis',
            'absensiMenunggu',
            'absensiDisetujui',
            'absensiDitolak'
        ));
    }

    public function jurnalValidasi(Request $request, JurnalHarian $jurnal)
    {
        $guru = $this->currentGuru();
        $isBimbingan = PenempatanMagang::where('guru_id', $guru?->id)
            ->where('siswa_id', $jurnal->siswa_id)
            ->exists();

        if (!$isBimbingan) {
            abort(403, 'Anda tidak berhak memvalidasi jurnal siswa di luar bimbingan Anda.');
        }

        $validated = $request->validate([
            'status_verifikasi' => ['required', Rule::in(['disetujui', 'revisi'])],
            'catatan_guru' => ['nullable', 'string', 'max:500'],
        ], [
            'status_verifikasi.required' => 'Keputusan validasi jurnal wajib dipilih.',
        ]);

        $jurnal->update([
            'status_verifikasi' => $validated['status_verifikasi'],
            'catatan_guru' => $validated['catatan_guru'] ?? null,
        ]);

        ActivityLogger::log(
            actionType: 'JURNAL_VALIDATED',
            target: 'Jurnal: ' . ($jurnal->siswa?->profile?->nama ?? 'Siswa'),
            metadata: ['jurnal_id' => $jurnal->id, 'status_verifikasi' => $validated['status_verifikasi']]
        );

        return redirect()->route('guru.jurnal', ['tab' => 'jurnal'])->with('toast_success', 'Validasi jurnal kegiatan siswa berhasil disimpan.');
    }

    public function absensiValidasi(Request $request, Absensi $absensi)
    {
        $guru = $this->currentGuru();
        $isBimbingan = PenempatanMagang::where('guru_id', $guru?->id)
            ->where('siswa_id', $absensi->siswa_id)
            ->exists();

        if (!$isBimbingan) {
            abort(403, 'Anda tidak berhak memvalidasi presensi siswa di luar bimbingan Anda.');
        }

        $validated = $request->validate([
            'status_validasi' => ['required', Rule::in(['disetujui', 'ditolak'])],
            'catatan_guru' => ['nullable', 'string', 'max:500'],
        ], [
            'status_validasi.required' => 'Keputusan validasi presensi wajib dipilih.',
        ]);

        $absensi->update([
            'status_validasi' => $validated['status_validasi'],
            'catatan_guru' => $validated['catatan_guru'] ?? null,
        ]);

        ActivityLogger::log(
            actionType: 'ABSENSI_VALIDATED',
            target: 'Absensi: ' . ($absensi->siswa?->profile?->nama ?? 'Siswa'),
            metadata: ['absensi_id' => $absensi->id, 'status_validasi' => $validated['status_validasi']]
        );

        return redirect()->route('guru.jurnal', ['tab' => 'absensi'])->with('toast_success', 'Keputusan validasi presensi siswa berhasil disimpan.');
    }

    public function kunjunganIndex()
    {
        $guru = $this->currentGuru();
        $kunjungans = Kunjungan::with('tempatMagang')
            ->where('guru_id', $guru?->id)
            ->orderByDesc('tanggal')
            ->orderByDesc('created_at')
            ->get();

        $totalKunjungan = $kunjungans->count();
        $bulanIni = $kunjungans->filter(fn ($k) => $k->tanggal->isCurrentMonth())->count();
        $dudiDikunjungi = $kunjungans->pluck('tempat_magang_id')->unique()->count();

        // Pilihan DUDI tempat siswa bimbingan atau seluruh DUDI terverifikasi
        $dudiList = TempatMagang::where('is_verified', true)->orderBy('nama_perusahaan')->get();

        return view('guru.kunjungan', compact(
            'guru',
            'kunjungans',
            'totalKunjungan',
            'bulanIni',
            'dudiDikunjungi',
            'dudiList'
        ));
    }

    public function kunjunganStore(Request $request)
    {
        $guru = $this->currentGuru();
        if (!$guru) {
            abort(403);
        }

        $validated = $request->validate([
            'tempat_magang_id' => ['required', 'uuid', 'exists:tempat_magang,id'],
            'tanggal' => ['required', 'date', 'before_or_equal:today'],
            'catatan' => ['required', 'string', 'min:10'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'tempat_magang_id.required' => 'Mitra DUDI wajib dipilih.',
            'tanggal.required' => 'Tanggal kunjungan wajib diisi.',
            'tanggal.before_or_equal' => 'Tanggal kunjungan tidak boleh di masa depan.',
            'catatan.required' => 'Catatan evaluasi kunjungan wajib diisi.',
            'catatan.min' => 'Catatan evaluasi minimal 10 karakter.',
            'photo.image' => 'File dokumentasi harus berupa gambar.',
            'photo.mimes' => 'Format foto harus JPG, PNG, atau WEBP.',
            'photo.max' => 'Ukuran foto maksimal 2 MB.',
        ]);

        $photoUrl = null;
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('kunjungan', 'public');
            $photoUrl = Storage::url($path);
        }

        $kunjungan = Kunjungan::create([
            'guru_id' => $guru->id,
            'tempat_magang_id' => $validated['tempat_magang_id'],
            'tanggal' => $validated['tanggal'],
            'catatan' => $validated['catatan'],
            'photo_url' => $photoUrl,
        ]);

        ActivityLogger::log(
            actionType: 'KUNJUNGAN_CREATED',
            target: $kunjungan->tempatMagang?->nama_perusahaan ?? 'DUDI',
            metadata: ['tanggal' => $validated['tanggal']]
        );

        return redirect()->route('guru.kunjungan')->with('toast_success', 'Catatan kunjungan lapangan berhasil disimpan.');
    }

    public function kunjunganUpdate(Request $request, Kunjungan $kunjungan)
    {
        $guru = $this->currentGuru();
        if (!$guru || $kunjungan->guru_id !== $guru->id) {
            abort(403);
        }

        $validated = $request->validate([
            'tempat_magang_id' => ['required', 'uuid', 'exists:tempat_magang,id'],
            'tanggal' => ['required', 'date', 'before_or_equal:today'],
            'catatan' => ['required', 'string', 'min:10'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $data = [
            'tempat_magang_id' => $validated['tempat_magang_id'],
            'tanggal' => $validated['tanggal'],
            'catatan' => $validated['catatan'],
        ];

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('kunjungan', 'public');
            $data['photo_url'] = Storage::url($path);
        }

        $kunjungan->update($data);

        ActivityLogger::log(
            actionType: 'KUNJUNGAN_UPDATED',
            target: $kunjungan->tempatMagang?->nama_perusahaan ?? 'DUDI',
            metadata: ['kunjungan_id' => $kunjungan->id]
        );

        return redirect()->route('guru.kunjungan')->with('toast_success', 'Catatan kunjungan lapangan berhasil diperbarui.');
    }

    public function kunjunganDestroy(Kunjungan $kunjungan)
    {
        $guru = $this->currentGuru();
        if (!$guru || $kunjungan->guru_id !== $guru->id) {
            abort(403);
        }

        $target = $kunjungan->tempatMagang?->nama_perusahaan ?? 'DUDI';
        $kunjungan->delete();

        ActivityLogger::log(
            actionType: 'KUNJUNGAN_DELETED',
            target: $target,
            metadata: [],
            level: 'WARN'
        );

        return redirect()->route('guru.kunjungan')->with('toast_success', 'Catatan kunjungan lapangan berhasil dihapus.');
    }
}

