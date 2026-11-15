<x-layouts.app title="Dashboard Pembimbing" subtitle="Ringkasan pemantauan siswa bimbingan, evaluasi jurnal, dan kehadiran harian">

    <!-- Hero Biru Gradasi -->
    <div class="rounded-2xl simmas-hero-gradient p-6 sm:p-8 text-white shadow-lg relative overflow-hidden">
        <div class="pointer-events-none absolute -right-12 -top-12 h-56 w-56 rounded-full bg-white/10 blur-2xl"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-2">
                <span class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1 text-xs font-semibold text-blue-50">
                    <i data-lucide="user-check" class="h-3.5 w-3.5"></i>
                    <span>NIP: {{ $guru?->nip ?? '-' }} • {{ $guru?->jurusan ?? 'Pembimbing Lapangan' }}</span>
                </span>
                <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                    Selamat bertugas, {{ auth()->user()->nama }}!
                </h2>
                <p class="text-sm text-blue-100 max-w-xl">
                    @if($jurnalBelumDinilaiCount > 0)
                        Terdapat <strong class="font-bold text-white underline">{{ $jurnalBelumDinilaiCount }} jurnal kegiatan siswa</strong> bimbingan Anda yang sedang menunggu evaluasi.
                    @else
                        Seluruh laporan jurnal harian siswa bimbingan Anda telah dievaluasi.
                    @endif
                </p>
            </div>
            <div class="shrink-0">
                <a href="{{ route('guru.jurnal') }}" class="inline-flex items-center gap-2 rounded-xl bg-white px-5 py-3 text-sm font-bold text-blue-600 shadow-md hover:bg-blue-50 transition-all">
                    <span>Lihat Jurnal</span>
                    <i data-lucide="arrow-right" class="h-4 w-4"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- 3 Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <x-stat-card
            label="Siswa Bimbingan"
            :value="$totalSiswaBimbingan"
            subtitle="Siswa dalam bimbingan Anda"
            icon="users"
            color="blue"
        />
        <x-stat-card
            label="Jurnal Belum Dinilai"
            :value="$jurnalBelumDinilaiCount"
            subtitle="Menunggu verifikasi pembimbing"
            icon="book-open"
            color="amber"
            :badge="$jurnalBelumDinilaiCount > 0 ? 'Perlu Evaluasi' : null"
        />
        <x-stat-card
            label="Kehadiran Hari Ini"
            :value="$hadirHariIniCount . '/' . $totalSiswaBimbingan"
            subtitle="Siswa bimbingan hadir hari ini"
            icon="check-circle-2"
            color="emerald"
        />
    </div>

    <!-- 2 Widgets: Jurnal Perlu Evaluasi & Daftar Siswa Bimbingan -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Widget Jurnal Perlu Evaluasi -->
        <div class="lg:col-span-6 simmas-card p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Jurnal Perlu Evaluasi</h3>
                    <p class="text-xs text-slate-500">Laporan kegiatan harian yang menunggu persetujuan</p>
                </div>
                <a href="{{ route('guru.jurnal') }}" class="text-xs font-bold text-blue-600 hover:text-blue-700">Lihat Semua</a>
            </div>

            <div class="space-y-3">
                @forelse($jurnalPerluEvaluasi as $jurnal)
                    <div class="flex items-start justify-between gap-4 rounded-xl border border-slate-100 bg-slate-50/70 p-4">
                        <div class="space-y-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <p class="text-xs font-bold text-slate-900">{{ $jurnal->siswa?->profile?->nama }}</p>
                                <span class="text-[11px] font-mono text-slate-400">• {{ $jurnal->tanggal->format('d M Y') }}</span>
                            </div>
                            <p class="text-xs text-slate-600 line-clamp-2">{{ $jurnal->kegiatan }}</p>
                            <p class="text-[11px] text-blue-600 font-medium">{{ $jurnal->siswa?->penempatanAktif?->tempatMagang?->nama_perusahaan ?? '-' }}</p>
                        </div>
                        <a href="{{ route('guru.jurnal') }}" class="simmas-btn-primary py-1.5 px-3 text-xs shrink-0">
                            Review
                        </a>
                    </div>
                @empty
                    <x-empty-state icon="check-circle-2" title="Semua jurnal telah dinilai" description="Tidak ada antrean jurnal kegiatan yang menunggu evaluasi." />
                @endforelse
            </div>
        </div>

        <!-- Widget Daftar Siswa Bimbingan (Status Hari Ini) -->
        <div class="lg:col-span-6 simmas-card p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Daftar Siswa Bimbingan</h3>
                    <p class="text-xs text-slate-500">Status kehadiran siswa bimbingan hari ini</p>
                </div>
                <a href="{{ route('guru.siswa') }}" class="text-xs font-bold text-blue-600 hover:text-blue-700">Lihat Semua</a>
            </div>

            <div class="space-y-3">
                @forelse($penempatans as $p)
                    @php
                        $absen = $absensiHariIni->get($p->siswa_id);
                        $stLabel = 'Belum Absen';
                        $stVariant = 'slate';
                        if ($absen) {
                            $stLabel = ucfirst($absen->status);
                            $stVariant = match($absen->status) {
                                'hadir' => 'emerald',
                                'sakit' => 'amber',
                                'izin' => 'blue',
                                'alfa' => 'rose',
                                default => 'slate',
                            };
                        }
                    @endphp
                    <div class="flex items-center justify-between gap-4 rounded-xl border border-slate-100 p-3.5 hover:bg-slate-50/70 transition-colors">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600 text-xs font-bold">
                                {{ $p->siswa?->profile?->initials ?? 'SW' }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-slate-900 truncate">{{ $p->siswa?->profile?->nama }}</p>
                                <p class="text-[11px] text-slate-500 truncate">{{ $p->tempatMagang?->nama_perusahaan }} • {{ $p->siswa?->kelas }}</p>
                            </div>
                        </div>
                        <x-badge :variant="$stVariant" :dot="true">{{ $stLabel }}</x-badge>
                    </div>
                @empty
                    <x-empty-state icon="users" title="Belum ada siswa bimbingan" description="Siswa yang di-plotting ke Anda akan tampil di sini." />
                @endforelse
            </div>
        </div>
    </div>

</x-layouts.app>

