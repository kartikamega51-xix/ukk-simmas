<x-layouts.app title="Monitoring Global" subtitle="Pemantauan real-time kehadiran, jurnal kegiatan, dan status magang seluruh siswa">

    <div x-data="{
        selectedSiswa: null,
        openDetail(data) {
            this.selectedSiswa = data;
            this.$dispatch('open-modal', 'detail-monitoring-modal');
        }
    }" class="space-y-6">

        <!-- 4 Stat Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <x-stat-card
                label="Total Siswa Aktif"
                :value="$allSiswaAktif"
                subtitle="Peserta program magang"
                icon="users"
                color="blue"
            />
            <x-stat-card
                label="Tingkat Kehadiran"
                :value="$tingkatKehadiran . '%'"
                subtitle="Rata-rata kehadiran global"
                icon="check-circle-2"
                color="emerald"
            />
            <x-stat-card
                label="Jurnal Terkumpul"
                :value="$jurnalTerkumpul"
                subtitle="Total laporan harian siswa"
                icon="book-open"
                color="violet"
            />
            <x-stat-card
                label="Perlu Perhatian"
                :value="$perluPerhatianCount"
                subtitle="Terdapat alfa atau revisi"
                icon="alert-triangle"
                color="amber"
            />
        </div>

        <!-- Search + Filter Bar + Table -->
        <div class="simmas-card overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <form method="GET" action="{{ route('admin.monitoring') }}" class="flex flex-1 flex-col sm:flex-row items-stretch sm:items-center gap-3">
                    <div class="relative flex-1 max-w-md">
                        <input
                            type="text"
                            name="q"
                            value="{{ $search }}"
                            placeholder="Cari nama siswa atau NIS..."
                            class="simmas-input pl-10"
                        >
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                            <i data-lucide="search" class="h-4 w-4"></i>
                        </span>
                    </div>

                    <select name="kelas" onchange="this.form.submit()" class="simmas-input sm:w-48">
                        <option value="">Semua Kelas</option>
                        @foreach($kelasList as $kls)
                            <option value="{{ $kls }}" @selected($kelasFilter === $kls)>{{ $kls }}</option>
                        @endforeach
                    </select>

                    <button type="submit" class="simmas-btn-secondary">Filter</button>
                    @if($search !== '' || $kelasFilter !== '')
                        <a href="{{ route('admin.monitoring') }}" class="simmas-btn-secondary text-rose-600">Reset</a>
                    @endif
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr>
                            <th class="simmas-table-th">Siswa & Kelas</th>
                            <th class="simmas-table-th">DUDI & Pembimbing</th>
                            <th class="simmas-table-th text-center">Kehadiran (H/S/I/A)</th>
                            <th class="simmas-table-th text-center">Jurnal</th>
                            <th class="simmas-table-th">Status</th>
                            <th class="simmas-table-th text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($siswas as $siswa)
                            @php
                                $hadir = $siswa->absensi->where('status', 'hadir')->count();
                                $sakit = $siswa->absensi->where('status', 'sakit')->count();
                                $izin = $siswa->absensi->where('status', 'izin')->count();
                                $alfa = $siswa->absensi->where('status', 'alfa')->count();
                                $jurnalCount = $siswa->jurnal->count();
                                $jurnalApproved = $siswa->jurnal->where('status_verifikasi', 'disetujui')->count();
                                $lastJurnal = $siswa->jurnal->first();

                                $penempatan = $siswa->penempatanAktif;
                                $dudiNama = $penempatan?->tempatMagang?->nama_perusahaan ?? 'Belum Ditempatkan';
                                $guruNama = $penempatan?->guru?->profile?->nama ?? '-';

                                $monitoringStatus = 'Aktif';
                                $monitoringVariant = 'emerald';
                                if ($alfa >= 2) {
                                    $monitoringStatus = 'Bermasalah';
                                    $monitoringVariant = 'rose';
                                } elseif ($alfa === 1 || $siswa->jurnal->where('status_verifikasi', 'revisi')->count() > 0 || !$penempatan) {
                                    $monitoringStatus = 'Perlu Perhatian';
                                    $monitoringVariant = 'amber';
                                }

                                $lastActiveText = $lastJurnal
                                    ? 'Terakhir aktif ' . $lastJurnal->tanggal->diffForHumans()
                                    : 'Belum ada aktivitas jurnal';

                                $detailPayload = [
                                    'nama' => $siswa->profile?->nama ?? '-',
                                    'nis' => $siswa->nis,
                                    'kelas' => $siswa->kelas,
                                    'dudi' => $dudiNama,
                                    'guru' => $guruNama,
                                    'hadir' => $hadir,
                                    'sakit' => $sakit,
                                    'izin' => $izin,
                                    'alfa' => $alfa,
                                    'jurnal_terakhir' => $lastJurnal ? [
                                        'tanggal' => $lastJurnal->tanggal->format('d M Y'),
                                        'kegiatan' => $lastJurnal->kegiatan,
                                        'status' => strtoupper($lastJurnal->status_verifikasi),
                                    ] : null,
                                ];
                            @endphp
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="simmas-table-td">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600 text-xs font-bold">
                                            {{ $siswa->profile?->initials ?? 'SM' }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-900">{{ $siswa->profile?->nama }}</p>
                                            <div class="flex items-center gap-2 mt-0.5">
                                                <span class="font-mono text-xs text-slate-500">{{ $siswa->nis }}</span>
                                                <span class="text-slate-300">•</span>
                                                <span class="rounded bg-slate-100 px-1.5 py-0.5 text-[11px] font-semibold text-slate-700">{{ $siswa->kelas }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="simmas-table-td">
                                    <p class="font-semibold text-slate-800">{{ $dudiNama }}</p>
                                    <p class="text-xs text-slate-500 mt-0.5">Pembimbing: {{ $guruNama }}</p>
                                </td>
                                <td class="simmas-table-td text-center">
                                    <div class="inline-flex items-center gap-1.5">
                                        <span title="Hadir" class="inline-flex h-6 min-w-6 items-center justify-center rounded-md bg-emerald-50 border border-emerald-200 px-1.5 text-xs font-bold text-emerald-700">{{ $hadir }}</span>
                                        <span title="Sakit" class="inline-flex h-6 min-w-6 items-center justify-center rounded-md bg-amber-50 border border-amber-200 px-1.5 text-xs font-bold text-amber-700">{{ $sakit }}</span>
                                        <span title="Izin" class="inline-flex h-6 min-w-6 items-center justify-center rounded-md bg-blue-50 border border-blue-200 px-1.5 text-xs font-bold text-blue-700">{{ $izin }}</span>
                                        <span title="Alfa" class="inline-flex h-6 min-w-6 items-center justify-center rounded-md bg-rose-50 border border-rose-200 px-1.5 text-xs font-bold text-rose-700">{{ $alfa }}</span>
                                    </div>
                                </td>
                                <td class="simmas-table-td text-center">
                                    <span class="font-bold text-slate-900">{{ $jurnalApproved }}</span>
                                    <span class="text-xs text-slate-400">/ {{ $jurnalCount }}</span>
                                </td>
                                <td class="simmas-table-td">
                                    <div>
                                        <x-badge :variant="$monitoringVariant" :dot="true">{{ $monitoringStatus }}</x-badge>
                                        <p class="text-[11px] text-slate-400 mt-1">{{ $lastActiveText }}</p>
                                    </div>
                                </td>
                                <td class="simmas-table-td text-right">
                                    <button
                                        type="button"
                                        @click="openDetail(@js($detailPayload))"
                                        class="simmas-btn-secondary py-1.5 px-3 text-xs"
                                    >
                                        <i data-lucide="eye" class="h-3.5 w-3.5"></i>
                                        <span>Detail</span>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <x-empty-state title="Data monitoring tidak ditemukan" description="Tidak ada siswa yang sesuai dengan kriteria pencarian atau filter kelas." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-pagination-bar :paginator="$siswas" />
        </div>

        <!-- Modal Detail Monitoring Magang -->
        <x-modal name="detail-monitoring-modal" title="Detail Monitoring Magang" subtitle="Ringkasan kehadiran dan aktivitas jurnal terbaru siswa" icon="activity" maxWidth="lg">
            <template x-if="selectedSiswa">
                <div class="space-y-5">
                    <!-- Info Siswa & Tempat Magang -->
                    <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4 space-y-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-base font-extrabold text-slate-900" x-text="selectedSiswa.nama"></p>
                                <p class="text-xs text-slate-500 font-mono mt-0.5">
                                    NIS: <span x-text="selectedSiswa.nis"></span> • Kelas: <span x-text="selectedSiswa.kelas"></span>
                                </p>
                            </div>
                            <span class="rounded-xl bg-blue-600 px-3 py-1 text-xs font-bold text-white" x-text="selectedSiswa.kelas"></span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-3 border-t border-slate-200/80 text-xs">
                            <div>
                                <p class="text-slate-400 font-semibold uppercase text-[10px]">Tempat Magang (DUDI)</p>
                                <p class="font-bold text-slate-800 mt-0.5" x-text="selectedSiswa.dudi"></p>
                            </div>
                            <div>
                                <p class="text-slate-400 font-semibold uppercase text-[10px]">Guru Pembimbing</p>
                                <p class="font-bold text-slate-800 mt-0.5" x-text="selectedSiswa.guru"></p>
                            </div>
                        </div>
                    </div>

                    <!-- 4 Kartu Rekap Kehadiran -->
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2.5">Rekap Kehadiran</p>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            <div class="rounded-xl border border-emerald-200 bg-emerald-50/70 p-3.5 text-center">
                                <p class="text-2xl font-extrabold text-emerald-700" x-text="selectedSiswa.hadir"></p>
                                <p class="text-xs font-bold text-emerald-600 mt-0.5">Hadir</p>
                            </div>
                            <div class="rounded-xl border border-amber-200 bg-amber-50/70 p-3.5 text-center">
                                <p class="text-2xl font-extrabold text-amber-700" x-text="selectedSiswa.sakit"></p>
                                <p class="text-xs font-bold text-amber-600 mt-0.5">Sakit</p>
                            </div>
                            <div class="rounded-xl border border-blue-200 bg-blue-50/70 p-3.5 text-center">
                                <p class="text-2xl font-extrabold text-blue-700" x-text="selectedSiswa.izin"></p>
                                <p class="text-xs font-bold text-blue-600 mt-0.5">Izin</p>
                            </div>
                            <div class="rounded-xl border border-rose-200 bg-rose-50/70 p-3.5 text-center">
                                <p class="text-2xl font-extrabold text-rose-700" x-text="selectedSiswa.alfa"></p>
                                <p class="text-xs font-bold text-rose-600 mt-0.5">Alfa</p>
                            </div>
                        </div>
                    </div>

                    <!-- Jurnal Terakhir -->
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Jurnal Terakhir</p>
                        <template x-if="selectedSiswa.jurnal_terakhir">
                            <div class="rounded-xl border border-slate-200 p-4 space-y-2">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="font-bold text-slate-700" x-text="selectedSiswa.jurnal_terakhir.tanggal"></span>
                                    <span class="rounded-full bg-blue-50 px-2.5 py-0.5 text-[11px] font-bold text-blue-700" x-text="selectedSiswa.jurnal_terakhir.status"></span>
                                </div>
                                <p class="text-xs text-slate-600 leading-relaxed" x-text="selectedSiswa.jurnal_terakhir.kegiatan"></p>
                            </div>
                        </template>
                        <template x-if="!selectedSiswa.jurnal_terakhir">
                            <div class="rounded-xl border border-dashed border-slate-200 p-4 text-center text-xs text-slate-400">
                                Siswa belum menulis laporan jurnal harian.
                            </div>
                        </template>
                    </div>

                    <div class="flex justify-end pt-3 border-t border-slate-100">
                        <button type="button" @click="$dispatch('close-modal', 'detail-monitoring-modal')" class="simmas-btn-primary">
                            Tutup Detail
                        </button>
                    </div>
                </div>
            </template>
        </x-modal>

    </div>

</x-layouts.app>

