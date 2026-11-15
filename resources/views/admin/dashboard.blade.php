<x-layouts.app title="Dashboard" subtitle="Ringkasan pemantauan dan data magang siswa SMK">

    <div x-data="{
        reviewPengajuan: { id: '', siswa_nama: '', dudi_nama: '', posisi: '', status: 'disetujui', guru_id: '', catatan_penolakan: '' },
        openReview(pg) {
            this.reviewPengajuan = pg;
            this.$dispatch('open-modal', 'review-pengajuan-modal');
        }
    }" class="space-y-6">

        <!-- 3 Stat Cards (Sama Persis Plek Ketiplek dengan Mockup Referensi) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Total Siswa -->
            <div class="simmas-card p-6 bg-white border border-slate-200 rounded-2xl shadow-xs transition-all hover:shadow-md">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Total Siswa</p>
                <div class="flex items-baseline justify-between">
                    <h3 class="text-4xl font-extrabold text-slate-900 tracking-tight">{{ $totalSiswa }}</h3>
                    <span class="inline-flex items-center gap-1 text-xs font-bold text-blue-600 bg-blue-50 px-2.5 py-1 rounded-full">
                        <i data-lucide="users" class="h-3.5 w-3.5"></i>
                        <span>Siswa</span>
                    </span>
                </div>
            </div>

            <!-- Total Guru -->
            <div class="simmas-card p-6 bg-white border border-slate-200 rounded-2xl shadow-xs transition-all hover:shadow-md">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Total Guru</p>
                <div class="flex items-baseline justify-between">
                    <h3 class="text-4xl font-extrabold text-slate-900 tracking-tight">{{ $totalGuru }}</h3>
                    <span class="inline-flex items-center gap-1 text-xs font-bold text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-full">
                        <i data-lucide="user-check" class="h-3.5 w-3.5"></i>
                        <span>Pembimbing</span>
                    </span>
                </div>
            </div>

            <!-- Mitra DUDI -->
            <div class="simmas-card p-6 bg-white border border-slate-200 rounded-2xl shadow-xs transition-all hover:shadow-md">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Mitra DUDI</p>
                <div class="flex items-baseline justify-between">
                    <h3 class="text-4xl font-extrabold text-slate-900 tracking-tight">{{ $totalDudi }}</h3>
                    <span class="inline-flex items-center gap-1 text-xs font-bold text-purple-600 bg-purple-50 px-2.5 py-1 rounded-full">
                        <i data-lucide="building-2" class="h-3.5 w-3.5"></i>
                        <span>Industri</span>
                    </span>
                </div>
            </div>
        </div>

        <!-- Tabel Daftar Permohonan Magang Siswa (Mockup Referensi Utama) -->
        <div class="simmas-card bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-xs">
            <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Daftar Permohonan Magang Siswa</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Daftar pengajuan tempat magang terbaru oleh siswa</p>
                </div>
                <div class="flex items-center gap-3">
                    @if($menungguValidasi > 0)
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700 border border-amber-200/60">
                            <span class="h-2 w-2 rounded-full bg-amber-500 animate-pulse"></span>
                            {{ $menungguValidasi }} Menunggu
                        </span>
                    @endif
                    <a href="{{ route('admin.pengajuan') }}" class="text-xs font-bold text-blue-600 hover:text-blue-700 flex items-center gap-1">
                        <span>Kelola Semua</span>
                        <i data-lucide="arrow-right" class="h-3.5 w-3.5"></i>
                    </a>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50/80 text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-100">
                        <tr>
                            <th class="py-3.5 px-6">Nama Siswa</th>
                            <th class="py-3.5 px-6">Kelas</th>
                            <th class="py-3.5 px-6">Perusahaan Mitra</th>
                            <th class="py-3.5 px-6">Status</th>
                            <th class="py-3.5 px-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @forelse($daftarPermohonan as $pengajuan)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="py-3.5 px-6">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-blue-100 text-xs font-bold text-blue-700">
                                            {{ $pengajuan->siswa?->profile?->initials ?? 'SW' }}
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold text-slate-900">{{ $pengajuan->siswa?->profile?->nama ?? 'Siswa' }}</p>
                                            <p class="text-[11px] text-slate-400 font-mono">{{ $pengajuan->siswa?->nis ?? '-' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-6 text-xs text-slate-600">
                                    {{ $pengajuan->siswa?->kelas ?? 'RPL' }}
                                </td>
                                <td class="py-3.5 px-6 text-xs font-semibold text-slate-800">
                                    {{ $pengajuan->tempatMagang?->nama_perusahaan ?? 'Perusahaan Mitra' }}
                                </td>
                                <td class="py-3.5 px-6">
                                    @if($pengajuan->status === 'disetujui')
                                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700 border border-emerald-200/60">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                            Disetujui
                                        </span>
                                    @elseif($pengajuan->status === 'ditolak')
                                        <span class="inline-flex items-center gap-1 rounded-full bg-rose-50 px-2.5 py-1 text-xs font-bold text-rose-700 border border-rose-200/60">
                                            <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>
                                            Ditolak
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-700 border border-amber-200/60">
                                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                            Menunggu
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-6 text-right">
                                    @if($pengajuan->status === 'menunggu')
                                        <button
                                            type="button"
                                            @click="openReview({
                                                id: '{{ $pengajuan->id }}',
                                                siswa_nama: '{{ addslashes($pengajuan->siswa?->profile?->nama ?? '') }}',
                                                dudi_nama: '{{ addslashes($pengajuan->tempatMagang?->nama_perusahaan ?? '') }}',
                                                posisi: '{{ addslashes($pengajuan->posisi_tujuan ?? '') }}',
                                                status: 'disetujui',
                                                guru_id: '',
                                                catatan_penolakan: ''
                                            })"
                                            class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 text-xs font-bold shadow-xs transition-all"
                                        >
                                            <i data-lucide="check-circle" class="h-3.5 w-3.5"></i>
                                            <span>Tinjau</span>
                                        </button>
                                    @else
                                        <a href="{{ route('admin.pengajuan') }}" class="text-xs font-semibold text-slate-400 hover:text-slate-600">
                                            Detail
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="h-12 w-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-3">
                                            <i data-lucide="inbox" class="h-6 w-6"></i>
                                        </div>
                                        <p class="text-sm font-bold text-slate-700">Belum Ada Permohonan Magang</p>
                                        <p class="text-xs text-slate-400 mt-1">Data permohonan yang diajukan oleh siswa akan langsung muncul di sini.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Section Analitik: Grafik Tren & Log Aktivitas -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Tren Pengajuan -->
            <div class="lg:col-span-8 simmas-card bg-white border border-slate-200 rounded-2xl p-6 shadow-xs">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Tren Permohonan Magang</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Statistik kurva pengajuan tempat magang 6 bulan terakhir</p>
                    </div>
                    <span class="rounded-lg bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-600">6 Bulan Terakhir</span>
                </div>
                <div class="h-64 w-full">
                    <canvas id="trenPengajuanChart"></canvas>
                </div>
            </div>

            <!-- Log Aktivitas Terakhir -->
            <div class="lg:col-span-4 simmas-card bg-white border border-slate-200 rounded-2xl p-6 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-base font-bold text-slate-900">Aktivitas Terkini</h3>
                        <a href="{{ route('admin.logs') }}" class="text-xs font-bold text-blue-600 hover:text-blue-700">Lihat Semua</a>
                    </div>
                    <div class="space-y-3">
                        @forelse($recentLogs as $log)
                            <div class="flex items-start gap-3 rounded-xl border border-slate-100 bg-slate-50/60 p-3">
                                <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg {{ $log->level === 'WARN' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700' }}">
                                    <i data-lucide="{{ $log->level === 'WARN' ? 'alert-triangle' : 'activity' }}" class="h-3.5 w-3.5"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-bold text-slate-900 truncate">{{ $log->action_type }}</p>
                                    <p class="text-[11px] text-slate-500 truncate">{{ $log->target ?? '-' }}</p>
                                    <p class="text-[10px] text-slate-400 mt-0.5">{{ $log->timestamp?->diffForHumans() }}</p>
                                </div>
                            </div>
                        @empty
                            <div class="py-8 text-center text-xs text-slate-400">
                                Belum ada catatan aktivitas sistem.
                            </div>
                        @endforelse
                    </div>
                </div>
                <div class="pt-4 border-t border-slate-100 mt-4 flex items-center justify-between text-xs text-slate-500">
                    <span>Audit Trail Aman</span>
                    <span class="inline-flex items-center gap-1 font-semibold text-emerald-600">
                        <i data-lucide="check-circle" class="h-3.5 w-3.5"></i>
                        Aktif
                    </span>
                </div>
            </div>
        </div>

        <!-- Modal Review Pengajuan -->
        <x-modal name="review-pengajuan-modal" maxWidth="lg">
            <form :action="'{{ url('/admin/pengajuan') }}/' + reviewPengajuan.id + '/review'" method="POST" class="p-6 space-y-5">
                @csrf
                @method('PATCH')

                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Tinjau Permohonan Magang</h3>
                        <p class="text-xs text-slate-500 mt-0.5" x-text="reviewPengajuan.siswa_nama + ' — ' + reviewPengajuan.dudi_nama"></p>
                    </div>
                    <button type="button" @click="$dispatch('close-modal', 'review-pengajuan-modal')" class="text-slate-400 hover:text-slate-600">
                        <i data-lucide="x" class="h-5 w-5"></i>
                    </button>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Keputusan Review</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="flex items-center justify-center gap-2 rounded-xl border p-3 cursor-pointer text-xs font-bold transition-all"
                                   :class="reviewPengajuan.status === 'disetujui' ? 'border-emerald-600 bg-emerald-50 text-emerald-700 ring-2 ring-emerald-600/20' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50'">
                                <input type="radio" name="status" value="disetujui" x-model="reviewPengajuan.status" class="sr-only">
                                <i data-lucide="check-circle" class="h-4 w-4 text-emerald-600"></i>
                                <span>Setujui Permohonan</span>
                            </label>
                            <label class="flex items-center justify-center gap-2 rounded-xl border p-3 cursor-pointer text-xs font-bold transition-all"
                                   :class="reviewPengajuan.status === 'ditolak' ? 'border-rose-600 bg-rose-50 text-rose-700 ring-2 ring-rose-600/20' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50'">
                                <input type="radio" name="status" value="ditolak" x-model="reviewPengajuan.status" class="sr-only">
                                <i data-lucide="x-circle" class="h-4 w-4 text-rose-600"></i>
                                <span>Tolak Permohonan</span>
                            </label>
                        </div>
                    </div>

                    <!-- Pilih Guru Pembimbing jika Disetujui -->
                    <div x-show="reviewPengajuan.status === 'disetujui'" class="space-y-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                            Pilih Guru Pembimbing <span class="text-rose-500">*</span>
                        </label>
                        <select name="guru_id" x-model="reviewPengajuan.guru_id" class="simmas-input" :required="reviewPengajuan.status === 'disetujui'">
                            <option value="">-- Pilih Guru Pembimbing --</option>
                            @foreach($activeGurus as $g)
                                <option value="{{ $g->id }}">{{ $g->profile?->nama }} (NIP: {{ $g->nip }})</option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-slate-400">Guru yang dipilih akan bertanggung jawab memvalidasi jurnal & absensi siswa ini.</p>
                    </div>

                    <!-- Alasan Penolakan jika Ditolak -->
                    <div x-show="reviewPengajuan.status === 'ditolak'" class="space-y-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                            Alasan Penolakan <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="catatan_penolakan" x-model="reviewPengajuan.catatan_penolakan" rows="3" class="simmas-input" placeholder="Tuliskan alasan penolakan permohonan magang..." :required="reviewPengajuan.status === 'ditolak'"></textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="$dispatch('close-modal', 'review-pengajuan-modal')" class="simmas-btn-secondary text-xs">
                        Batal
                    </button>
                    <button type="submit" class="simmas-btn-primary text-xs">
                        Simpan Keputusan
                    </button>
                </div>
            </form>
        </x-modal>

    </div>

    <!-- Script Chart.js Tren Pengajuan -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('trenPengajuanChart');
            if (!ctx) return;

            const months = @json($months ?? ['Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov']);
            const counts = @json($counts ?? [0, 0, 0, 0, 0, 0]);

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: months,
                    datasets: [{
                        label: 'Permohonan Magang',
                        data: counts,
                        borderColor: '#2563eb',
                        backgroundColor: 'rgba(37, 99, 235, 0.08)',
                        borderWidth: 2.5,
                        fill: true,
                        tension: 0.35,
                        pointBackgroundColor: '#2563eb',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            titleFont: { size: 12, weight: 'bold' },
                            bodyFont: { size: 12 },
                            padding: 10,
                            cornerRadius: 8,
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0, font: { size: 11 }, color: '#94a3b8' },
                            grid: { color: '#f1f5f9' },
                            border: { dash: [4, 4] }
                        },
                        x: {
                            ticks: { font: { size: 11 }, color: '#94a3b8' },
                            grid: { display: false }
                        }
                    }
                }
            });
        });
    </script>
</x-layouts.app>
