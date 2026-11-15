<x-layouts.app title="Dashboard Siswa" subtitle="Status penempatan magang, ringkasan kehadiran, dan aktivitas jurnal harian Anda">

    @php
        $namaSiswa = $siswa?->profile?->nama ?? auth()->user()->nama;
        $dudiNama = $penempatan?->tempatMagang?->nama_perusahaan ?? 'Belum Ditempatkan di Mitra DUDI';
    @endphp

    <!-- Hero Banner Biru Gradasi -->
    <div class="rounded-2xl simmas-hero-gradient p-6 sm:p-8 text-white shadow-lg relative overflow-hidden">
        <div class="pointer-events-none absolute -right-12 -top-12 h-56 w-56 rounded-full bg-white/10 blur-2xl"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-2">
                <span class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1 text-xs font-semibold text-blue-50">
                    <i data-lucide="building-2" class="h-3.5 w-3.5"></i>
                    <span>{{ $dudiNama }}</span>
                </span>
                <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                    Semangat magang, {{ $namaSiswa }}!
                </h2>
                <p class="text-sm text-blue-100 max-w-xl">
                    @if($absensiHariIni)
                        Presensi Anda hari ini telah tercatat sebagai <strong class="font-bold text-white uppercase">{{ $absensiHariIni->status }}</strong> pada pukul {{ $absensiHariIni->jam_masuk }}. Jangan lupa mengisi jurnal kegiatan harian!
                    @else
                        Jangan lupa melakukan presensi kehadiran hari ini dan mendokumentasikan kegiatan magang Anda di jurnal harian.
                    @endif
                </p>
            </div>
            <div class="shrink-0">
                <a href="{{ route('siswa.absensi') }}" class="inline-flex items-center gap-2 rounded-xl bg-white px-5 py-3 text-sm font-bold text-blue-600 shadow-md hover:bg-blue-50 transition-all">
                    <i data-lucide="clock" class="h-4 w-4"></i>
                    <span>Isi Absensi</span>
                </a>
            </div>
        </div>
    </div>

    <!-- 3 Stat Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <!-- Progres Magang dengan Progress Bar -->
        <x-stat-card
            label="Progres Magang"
            :value="$penempatan ? 'Hari ke-' . $hariKe . ' dari ' . $totalHari . ' hari' : 'Belum Aktif'"
            :subtitle="$penempatan ? ($penempatan->tanggal_mulai?->format('d M Y') . ' sampai ' . $penempatan->tanggal_selesai?->format('d M Y')) : 'Menunggu penempatan magang'"
            icon="calendar"
            color="blue"
        >
            <div class="space-y-1.5">
                <div class="flex items-center justify-between text-[11px] font-semibold text-slate-500">
                    <span>Capaian Periode</span>
                    <span class="font-mono font-bold text-blue-600">{{ $progressPct }}%</span>
                </div>
                <div class="h-2 w-full rounded-full bg-slate-100 overflow-hidden">
                    <div class="h-full rounded-full bg-blue-600 transition-all" style="width: {{ $progressPct }}%"></div>
                </div>
            </div>
        </x-stat-card>

        <x-stat-card
            label="Total Kehadiran"
            :value="$totalKehadiran . ' Hari'"
            subtitle="Akumulasi status hadir"
            icon="check-circle-2"
            color="emerald"
        />

        <x-stat-card
            label="Jurnal Ditulis"
            :value="$jurnalDitulis . ' Laporan'"
            subtitle="Total jurnal kegiatan harian"
            icon="book-open"
            color="violet"
        />
    </div>

    @if($penempatan)
        <!-- Kartu Informasi Magang + Kartu Aksi Cepat -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Kartu Informasi Magang -->
            <div class="lg:col-span-7 simmas-card p-6 space-y-5">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Informasi Magang</h3>
                        <p class="text-xs text-slate-500">Detail penempatan industri dan guru pembimbing lapangan</p>
                    </div>
                    @php
                        $stVariant = match($penempatan->status_pengesahan) {
                            'disahkan' => 'emerald',
                            'lulus_magang' => 'violet',
                            default => 'amber',
                        };
                        $stLabel = match($penempatan->status_pengesahan) {
                            'disahkan' => 'Penempatan Aktif',
                            'lulus_magang' => 'Lulus Magang',
                            default => 'Menunggu Pengesahan',
                        };
                    @endphp
                    <x-badge :variant="$stVariant" :dot="true">{{ $stLabel }}</x-badge>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4 space-y-2">
                        <div class="flex items-center gap-2 text-blue-600">
                            <i data-lucide="building-2" class="h-4 w-4"></i>
                            <span class="text-[11px] font-bold uppercase tracking-wider">Tempat Magang DUDI</span>
                        </div>
                        <p class="text-sm font-extrabold text-slate-900">{{ $penempatan->tempatMagang?->nama_perusahaan }}</p>
                        <p class="text-xs text-slate-500 leading-relaxed">{{ $penempatan->tempatMagang?->alamat }}</p>
                        <p class="text-[11px] text-slate-400 pt-1">PIC: {{ $penempatan->tempatMagang?->penanggung_jawab }} ({{ $penempatan->tempatMagang?->kontak_pic }})</p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4 space-y-2">
                        <div class="flex items-center gap-2 text-emerald-600">
                            <i data-lucide="user-check" class="h-4 w-4"></i>
                            <span class="text-[11px] font-bold uppercase tracking-wider">Guru Pembimbing</span>
                        </div>
                        <p class="text-sm font-extrabold text-slate-900">{{ $penempatan->guru?->profile?->nama }}</p>
                        <p class="font-mono text-xs text-slate-500">NIP: {{ $penempatan->guru?->nip }}</p>
                        <p class="text-[11px] text-slate-400 pt-1">Jurusan: {{ $penempatan->guru?->jurusan }}</p>
                    </div>
                </div>

                @if($penempatan->nilai_akhir !== null)
                    <div class="rounded-2xl border border-emerald-200 bg-emerald-50/70 p-4 flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-emerald-700">Nilai Akhir Magang</p>
                            <p class="text-xs text-emerald-600 mt-0.5">Nilai evaluasi akhir dari Guru Pembimbing Anda</p>
                        </div>
                        <span class="text-2xl font-extrabold font-mono text-emerald-700">{{ round($penempatan->nilai_akhir) }}</span>
                    </div>
                @endif
            </div>

            <!-- Kartu Biru Absensi Hari Ini + Kartu Jurnal Kegiatan -->
            <div class="lg:col-span-5 space-y-5">
                <!-- Kartu Biru Absensi Hari Ini -->
                <div class="rounded-2xl bg-blue-600 p-6 text-white shadow-md space-y-4">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-blue-200">PRESENSI HARIAN</p>
                            <h3 class="text-lg font-extrabold mt-0.5">Absensi Hari Ini</h3>
                        </div>
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/15">
                            <i data-lucide="clock" class="h-5 w-5"></i>
                        </div>
                    </div>

                    <p class="text-xs text-blue-100 leading-relaxed">
                        @if($absensiHariIni)
                            Status: <strong>{{ ucfirst($absensiHariIni->status) }}</strong> • Clock In: {{ $absensiHariIni->jam_masuk ?? '-' }} • Clock Out: {{ $absensiHariIni->jam_pulang ?? 'Belum Clock Out' }}
                        @else
                            Anda belum mengisi daftar hadir untuk hari ini ({{ now()->format('d M Y') }}).
                        @endif
                    </p>

                    <a href="{{ route('siswa.absensi') }}" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-white py-2.5 px-4 text-xs font-bold text-blue-600 hover:bg-blue-50 transition-all">
                        <span>Isi Absensi</span>
                        <i data-lucide="arrow-right" class="h-4 w-4"></i>
                    </a>
                </div>

                <!-- Kartu Jurnal Kegiatan -->
                <div class="simmas-card p-6 space-y-4">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-violet-600">LOGBOOK PKL</p>
                            <h3 class="text-lg font-extrabold text-slate-900 mt-0.5">Jurnal Kegiatan</h3>
                        </div>
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-50 text-violet-600">
                            <i data-lucide="book-open" class="h-5 w-5"></i>
                        </div>
                    </div>

                    <p class="text-xs text-slate-500 leading-relaxed">
                        Catat aktivitas pekerjaan harian, kendala yang dihadapi, serta solusi teknis selama magang untuk dievaluasi pembimbing.
                    </p>

                    <a href="{{ route('siswa.jurnal') }}" class="simmas-btn-primary w-full">
                        <i data-lucide="pen-line" class="h-4 w-4"></i>
                        <span>Tulis Jurnal</span>
                    </a>
                </div>
            </div>
        </div>
    @else
        <!-- Empty State bila belum ditempatkan -->
        <div class="simmas-card p-8">
            <x-empty-state
                icon="send"
                title="Anda Belum Memiliki Penempatan Magang Aktif"
                description="Silakan ajukan permohonan tempat magang pada mitra DUDI terverifikasi atau tunggu proses plotting dari Administrator Sekolah."
            >
                <a href="{{ route('siswa.pengajuan') }}" class="simmas-btn-primary">
                    <i data-lucide="send" class="h-4 w-4"></i>
                    <span>Buka Pengajuan Magang</span>
                </a>
            </x-empty-state>
        </div>
    @endif

</x-layouts.app>

