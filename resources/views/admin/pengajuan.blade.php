<x-layouts.app title="Permohonan Magang" subtitle="Tinjau dan verifikasi permohonan tempat magang mandiri yang diajukan oleh siswa">

    <div x-data="{
        reviewPengajuan: { id: '', siswa_nama: '', dudi_nama: '', posisi: '', status: 'disetujui', guru_id: '', catatan_penolakan: '' },
        openReview(pg) {
            this.reviewPengajuan = pg;
            this.$dispatch('open-modal', 'review-pengajuan-modal');
        }
    }" class="space-y-6">

        <!-- 4 Stat Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <x-stat-card label="Total Permohonan" :value="$totalPengajuan" subtitle="Seluruh pengajuan siswa" icon="file-text" color="blue" />
            <x-stat-card label="Menunggu Review" :value="$menungguCount" subtitle="Perlu tindakan sekolah" icon="clock" color="amber" :badge="$menungguCount > 0 ? 'Perlu Tindakan' : null" />
            <x-stat-card label="Disetujui" :value="$disetujuiCount" subtitle="Telah diplot pembimbing" icon="check-circle-2" color="emerald" />
            <x-stat-card label="Ditolak" :value="$ditolakCount" subtitle="Permohonan tidak disetujui" icon="x-circle" color="rose" />
        </div>

        <!-- Tabel Permohonan Magang -->
        <div class="simmas-card overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex flex-col xl:flex-row xl:items-center justify-between gap-4">
                <form method="GET" action="{{ route('admin.pengajuan') }}" class="flex flex-1 flex-wrap items-center gap-3">
                    <div class="relative flex-1 min-w-[200px] max-w-xs">
                        <input
                            type="text"
                            name="q"
                            value="{{ $search }}"
                            placeholder="Cari siswa, NIS, atau DUDI..."
                            class="simmas-input pl-10"
                        >
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                            <i data-lucide="search" class="h-4 w-4"></i>
                        </span>
                    </div>

                    <select name="status" onchange="this.form.submit()" class="simmas-input w-auto min-w-[150px]">
                        <option value="">Semua Status</option>
                        <option value="menunggu" @selected($statusFilter === 'menunggu')>Menunggu</option>
                        <option value="disetujui" @selected($statusFilter === 'disetujui')>Disetujui</option>
                        <option value="ditolak" @selected($statusFilter === 'ditolak')>Ditolak</option>
                    </select>

                    <select name="dudi" onchange="this.form.submit()" class="simmas-input w-auto min-w-[160px]">
                        <option value="">Semua Mitra DUDI</option>
                        @foreach($dudiList as $d)
                            <option value="{{ $d->id }}" @selected($dudiFilter === $d->id)>{{ $d->nama_perusahaan }}</option>
                        @endforeach
                    </select>

                    <button type="submit" class="simmas-btn-secondary">Filter</button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr>
                            <th class="simmas-table-th">Tanggal</th>
                            <th class="simmas-table-th">Siswa</th>
                            <th class="simmas-table-th">Mitra DUDI & Posisi</th>
                            <th class="simmas-table-th">Rencana Periode</th>
                            <th class="simmas-table-th">Status</th>
                            <th class="simmas-table-th text-right">Keputusan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pengajuans as $pg)
                            @php
                                $pgVariant = match($pg->status) {
                                    'disetujui' => 'emerald',
                                    'ditolak' => 'rose',
                                    default => 'amber',
                                };
                            @endphp
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="simmas-table-td text-xs text-slate-500 whitespace-nowrap">
                                    {{ $pg->tanggal_pengajuan?->format('d M Y H:i') }}
                                </td>
                                <td class="simmas-table-td">
                                    <p class="font-bold text-slate-900">{{ $pg->siswa?->profile?->nama }}</p>
                                    <p class="font-mono text-xs text-slate-400">{{ $pg->siswa?->nis }} • {{ $pg->siswa?->kelas }}</p>
                                </td>
                                <td class="simmas-table-td">
                                    <p class="font-bold text-slate-800 text-xs">{{ $pg->tempatMagang?->nama_perusahaan }}</p>
                                    <p class="text-xs text-blue-600 font-medium">{{ $pg->posisi }}</p>
                                </td>
                                <td class="simmas-table-td text-xs text-slate-600">
                                    {{ $pg->tanggal_mulai?->format('d M Y') }} s/d {{ $pg->tanggal_selesai?->format('d M Y') }}
                                </td>
                                <td class="simmas-table-td">
                                    <x-badge :variant="$pgVariant" :dot="true">{{ ucfirst($pg->status) }}</x-badge>
                                    @if($pg->status === 'ditolak' && $pg->catatan_penolakan)
                                        <p class="text-[11px] text-rose-500 mt-1 max-w-xs truncate" title="{{ $pg->catatan_penolakan }}">
                                            Alasan: {{ $pg->catatan_penolakan }}
                                        </p>
                                    @endif
                                </td>
                                <td class="simmas-table-td text-right">
                                    @if($pg->status === 'menunggu')
                                        <button
                                            type="button"
                                            @click="openReview(@js([
                                                'id' => $pg->id,
                                                'siswa_nama' => $pg->siswa?->profile?->nama,
                                                'dudi_nama' => $pg->tempatMagang?->nama_perusahaan,
                                                'posisi' => $pg->posisi,
                                                'status' => 'disetujui',
                                                'guru_id' => '',
                                                'catatan_penolakan' => '',
                                            ]))"
                                            class="simmas-btn-primary py-1.5 px-3 text-xs"
                                        >
                                            <span>Tinjau</span>
                                            <i data-lucide="chevron-right" class="h-3.5 w-3.5"></i>
                                        </button>
                                    @else
                                        <span class="text-xs font-semibold text-slate-400">Selesai</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <x-empty-state title="Belum ada permohonan magang" description="Daftar pengajuan permohonan magang mandiri dari siswa akan muncul di sini." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-pagination-bar :paginator="$pengajuans" />
        </div>

        <!-- Modal Tinjau / Keputusan Pengajuan Magang -->
        <x-modal name="review-pengajuan-modal" title="Tinjau Permohonan Magang" subtitle="Tetapkan keputusan penerimaan dan plotting guru pembimbing" icon="check-square" maxWidth="md">
            <form method="POST" :action="'{{ url('/admin/pengajuan') }}/' + reviewPengajuan.id + '/review'" class="space-y-4">
                @csrf
                @method('PATCH')

                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 space-y-1.5 text-xs">
                    <p class="text-[10px] font-bold uppercase text-slate-400">Permohonan Siswa</p>
                    <p class="font-extrabold text-slate-900 text-sm" x-text="reviewPengajuan.siswa_nama"></p>
                    <p class="text-slate-600"><span class="font-semibold text-slate-800" x-text="reviewPengajuan.dudi_nama"></span> • Posisi: <span class="font-medium text-blue-600" x-text="reviewPengajuan.posisi"></span></p>
                </div>

                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-700">Keputusan Sekolah <span class="text-rose-500">*</span></label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="flex items-center gap-2 rounded-xl border border-slate-200 p-3 cursor-pointer hover:bg-slate-50">
                            <input type="radio" name="status" value="disetujui" x-model="reviewPengajuan.status" class="text-blue-600">
                            <span class="text-xs font-bold text-emerald-700">Setujui</span>
                        </label>
                        <label class="flex items-center gap-2 rounded-xl border border-slate-200 p-3 cursor-pointer hover:bg-slate-50">
                            <input type="radio" name="status" value="ditolak" x-model="reviewPengajuan.status" class="text-rose-600">
                            <span class="text-xs font-bold text-rose-700">Tolak</span>
                        </label>
                    </div>
                </div>

                <!-- Input jika Disetujui: Pilih Guru Pembimbing -->
                <div x-show="reviewPengajuan.status === 'disetujui'" class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-700">Plotting Guru Pembimbing Lapangan <span class="text-rose-500">*</span></label>
                    <select name="guru_id" class="simmas-input">
                        <option value="">-- Pilih Guru Pembimbing --</option>
                        @foreach($activeGurus as $g)
                            <option value="{{ $g->id }}">{{ $g->profile?->nama }} ({{ $g->jurusan }})</option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-slate-400">Siswa otomatis ditempatkan di DUDI terkait dan dibimbing guru ini.</p>
                </div>

                <!-- Input jika Ditolak: Alasan Penolakan -->
                <div x-show="reviewPengajuan.status === 'ditolak'" x-cloak class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-700">Alasan Penolakan <span class="text-rose-500">*</span></label>
                    <textarea name="catatan_penolakan" rows="3" placeholder="Tuliskan alasan penolakan agar siswa dapat memilih DUDI lain..." class="simmas-input"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100">
                    <button type="button" @click="$dispatch('close-modal', 'review-pengajuan-modal')" class="simmas-btn-secondary">Batal</button>
                    <button type="submit" class="simmas-btn-primary">Simpan Keputusan</button>
                </div>
            </form>
        </x-modal>

    </div>

</x-layouts.app>

