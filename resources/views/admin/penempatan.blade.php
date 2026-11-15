<x-layouts.app title="Manajemen Penempatan" subtitle="Kelola penempatan magang siswa, pengesahan status, dan tinjauan pengajuan mandiri">

    <div x-data="{
        editPenempatan: { id: '', siswa_nama: '', siswa_nis: '', tempat_magang_id: '', guru_id: '', tanggal_mulai: '', tanggal_selesai: '', catatan_admin: '' },
        sahkanPenempatan: { id: '', siswa_nama: '', status_pengesahan: 'disahkan', catatan_admin: '' },
        cancelPenempatan: { id: '', siswa_nama: '', dudi_nama: '' },
        reviewPengajuan: { id: '', siswa_nama: '', dudi_nama: '', posisi: '', status: 'disetujui', guru_id: '', catatan_penolakan: '' },
        openEdit(p) {
            this.editPenempatan = p;
            this.$dispatch('open-modal', 'edit-penempatan-modal');
        },
        openSahkan(p) {
            this.sahkanPenempatan = p;
            this.$dispatch('open-modal', 'sahkan-penempatan-modal');
        },
        openCancel(p) {
            this.cancelPenempatan = p;
            this.$dispatch('open-modal', 'cancel-penempatan-modal');
        },
        openReview(pg) {
            this.reviewPengajuan = pg;
            this.$dispatch('open-modal', 'review-pengajuan-modal');
        }
    }" class="space-y-6">

        <!-- 4 Stat Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <x-stat-card label="Total Penempatan" :value="$totalPenempatan" subtitle="Seluruh data penempatan" icon="briefcase" color="blue" />
            <x-stat-card label="Sedang Berlangsung" :value="$sedangBerlangsung" subtitle="Penempatan telah disahkan" icon="play-circle" color="emerald" />
            <x-stat-card label="Selesai Magang" :value="$selesaiMagang" subtitle="Siswa telah lulus magang" icon="check-circle-2" color="violet" />
            <x-stat-card label="DUDI Terlibat" :value="$dudiTerlibat" subtitle="Mitra industri aktif" icon="building-2" color="amber" />
        </div>

        <!-- Tabel Utama Penempatan Magang -->
        <div class="simmas-card overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex flex-col xl:flex-row xl:items-center justify-between gap-4">
                <form method="GET" action="{{ route('admin.penempatan') }}" class="flex flex-1 flex-wrap items-center gap-3">
                    <div class="relative flex-1 min-w-[200px] max-w-xs">
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

                    <select name="kelas" onchange="this.form.submit()" class="simmas-input w-auto min-w-[140px]">
                        <option value="">Semua Kelas</option>
                        @foreach($kelasList as $kls)
                            <option value="{{ $kls }}" @selected($kelasFilter === $kls)>{{ $kls }}</option>
                        @endforeach
                    </select>

                    <select name="dudi" onchange="this.form.submit()" class="simmas-input w-auto min-w-[160px]">
                        <option value="">Semua DUDI</option>
                        @foreach($dudiList as $d)
                            <option value="{{ $d->id }}" @selected($dudiFilter === $d->id)>{{ $d->nama_perusahaan }}</option>
                        @endforeach
                    </select>

                    <select name="status" onchange="this.form.submit()" class="simmas-input w-auto min-w-[150px]">
                        <option value="">Semua Status</option>
                        <option value="disahkan" @selected($statusFilter === 'disahkan')>Disahkan</option>
                        <option value="belum_disahkan" @selected($statusFilter === 'belum_disahkan')>Belum Disahkan</option>
                        <option value="lulus_magang" @selected($statusFilter === 'lulus_magang')>Lulus Magang</option>
                    </select>

                    <button type="submit" class="simmas-btn-secondary">Filter</button>
                </form>

                <button
                    type="button"
                    @click="$dispatch('open-modal', 'tambah-penempatan-modal')"
                    class="simmas-btn-primary shrink-0"
                >
                    <i data-lucide="plus" class="h-4 w-4"></i>
                    <span>+ Tambah Penempatan</span>
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr>
                            <th class="simmas-table-th">Siswa & NIS</th>
                            <th class="simmas-table-th">Kelas</th>
                            <th class="simmas-table-th">Tempat Magang</th>
                            <th class="simmas-table-th">Guru Pembimbing</th>
                            <th class="simmas-table-th">Periode</th>
                            <th class="simmas-table-th text-center">Nilai Akhir</th>
                            <th class="simmas-table-th">Status Pengesahan</th>
                            <th class="simmas-table-th text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($penempatans as $p)
                            @php
                                $stVariant = match($p->status_pengesahan) {
                                    'disahkan' => 'emerald',
                                    'lulus_magang' => 'violet',
                                    default => 'amber',
                                };
                                $stLabel = match($p->status_pengesahan) {
                                    'disahkan' => 'Disahkan',
                                    'lulus_magang' => 'Lulus Magang',
                                    default => 'Belum Disahkan',
                                };
                            @endphp
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="simmas-table-td">
                                    <p class="font-bold text-slate-900">{{ $p->siswa?->profile?->nama }}</p>
                                    <p class="font-mono text-xs text-slate-400">{{ $p->siswa?->nis }}</p>
                                </td>
                                <td class="simmas-table-td">
                                    <x-badge variant="mono">{{ $p->siswa?->kelas }}</x-badge>
                                </td>
                                <td class="simmas-table-td font-semibold text-slate-800 text-xs">
                                    {{ $p->tempatMagang?->nama_perusahaan }}
                                </td>
                                <td class="simmas-table-td text-xs text-slate-600">
                                    {{ $p->guru?->profile?->nama }}
                                </td>
                                <td class="simmas-table-td text-xs text-slate-600">
                                    {{ $p->tanggal_mulai?->format('d M Y') }} <span class="text-slate-400">s.d.</span> {{ $p->tanggal_selesai?->format('d M Y') }}
                                </td>
                                <td class="simmas-table-td text-center">
                                    @if($p->nilai_akhir !== null)
                                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-emerald-50 border border-emerald-200 font-mono text-xs font-extrabold text-emerald-700">
                                            {{ round($p->nilai_akhir) }}
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="simmas-table-td">
                                    <x-badge :variant="$stVariant" :dot="true">{{ $stLabel }}</x-badge>
                                </td>
                                <td class="simmas-table-td text-right">
                                    <div class="inline-flex items-center gap-1.5">
                                        <button
                                            type="button"
                                            title="Sahkan / Ubah Status"
                                            @click="openSahkan(@js([
                                                'id' => $p->id,
                                                'siswa_nama' => $p->siswa?->profile?->nama,
                                                'status_pengesahan' => $p->status_pengesahan,
                                                'catatan_admin' => $p->catatan_admin ?? '',
                                            ]))"
                                            class="rounded-lg border border-emerald-200 bg-emerald-50 px-2.5 py-1.5 text-xs font-bold text-emerald-700 hover:bg-emerald-100 cursor-pointer"
                                        >
                                            Sahkan
                                        </button>
                                        <button
                                            type="button"
                                            title="Edit Penempatan"
                                            @click="openEdit(@js([
                                                'id' => $p->id,
                                                'siswa_nama' => $p->siswa?->profile?->nama,
                                                'siswa_nis' => $p->siswa?->nis,
                                                'tempat_magang_id' => $p->tempat_magang_id,
                                                'guru_id' => $p->guru_id,
                                                'tanggal_mulai' => $p->tanggal_mulai?->format('Y-m-d'),
                                                'tanggal_selesai' => $p->tanggal_selesai?->format('Y-m-d'),
                                                'catatan_admin' => $p->catatan_admin ?? '',
                                            ]))"
                                            class="rounded-lg border border-slate-200 p-1.5 text-slate-600 hover:bg-slate-100 cursor-pointer"
                                        >
                                            <i data-lucide="pencil" class="h-3.5 w-3.5"></i>
                                        </button>
                                        <button
                                            type="button"
                                            title="Batalkan Penempatan"
                                            @click="openCancel(@js([
                                                'id' => $p->id,
                                                'siswa_nama' => $p->siswa?->profile?->nama,
                                                'dudi_nama' => $p->tempatMagang?->nama_perusahaan,
                                            ]))"
                                            class="rounded-lg border border-amber-200 bg-amber-50 p-1.5 text-amber-600 hover:bg-amber-100 cursor-pointer"
                                        >
                                            <i data-lucide="x-circle" class="h-3.5 w-3.5"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">
                                    <x-empty-state title="Belum ada data penempatan" description="Tambahkan penempatan magang baru atau setujui pengajuan siswa." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-pagination-bar :paginator="$penempatans" />
        </div>

        <!-- Seksi Tinjauan Pengajuan Magang Mandiri Siswa -->
        <div id="pengajuan-section" class="simmas-card overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Daftar Pengajuan Tempat Magang Siswa</h3>
                    <p class="text-xs text-slate-500">Tinjau permohonan tempat magang mandiri yang diajukan oleh siswa</p>
                </div>
                <x-badge variant="amber">{{ $pengajuans->where('status', 'menunggu')->count() }} Menunggu</x-badge>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr>
                            <th class="simmas-table-th">Tanggal Pengajuan</th>
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
                                <td class="simmas-table-td text-xs text-slate-500">
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
                                    {{ $pg->tanggal_mulai?->format('d M Y') }} - {{ $pg->tanggal_selesai?->format('d M Y') }}
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
                                            Tinjau Pengajuan
                                        </button>
                                    @else
                                        <span class="text-xs text-slate-400">Telah Ditinjau</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <x-empty-state title="Belum ada pengajuan" description="Daftar pengajuan mandiri dari siswa akan tampil di sini." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal Tambah Penempatan -->
        <x-modal name="tambah-penempatan-modal" title="Tambah Penempatan Magang" subtitle="Validasi kuota mitra DUDI dan periode pelaksanaan secara otomatis" icon="briefcase">
            <form method="POST" action="{{ route('admin.penempatan.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Pilih Siswa <span class="text-rose-500">*</span></label>
                    <select name="siswa_id" required class="simmas-input">
                        <option value="">-- Pilih Siswa --</option>
                        @foreach($availableSiswas as $s)
                            <option value="{{ $s->id }}">{{ $s->profile?->nama }} ({{ $s->nis }} — {{ $s->kelas }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Tempat Magang (Mitra DUDI) <span class="text-rose-500">*</span></label>
                    <select name="tempat_magang_id" required class="simmas-input">
                        <option value="">-- Pilih Tempat Magang --</option>
                        @foreach($dudiList->where('is_verified', true) as $d)
                            <option value="{{ $d->id }}" @disabled($d->sisa_kuota <= 0)>
                                {{ $d->nama_perusahaan }} (Sisa Kuota: {{ $d->sisa_kuota }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Guru Pembimbing <span class="text-rose-500">*</span></label>
                    <select name="guru_id" required class="simmas-input">
                        <option value="">-- Pilih Guru Pembimbing --</option>
                        @foreach($activeGurus as $g)
                            <option value="{{ $g->id }}">{{ $g->profile?->nama }} (NIP: {{ $g->nip }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Tanggal Mulai <span class="text-rose-500">*</span></label>
                        <input type="date" name="tanggal_mulai" required value="{{ now()->toDateString() }}" class="simmas-input">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Tanggal Selesai <span class="text-rose-500">*</span></label>
                        <input type="date" name="tanggal_selesai" required value="{{ now()->addMonths(3)->toDateString() }}" class="simmas-input">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Catatan Admin (Opsional)</label>
                    <textarea name="catatan_admin" rows="2" placeholder="Catatan penempatan..." class="simmas-input"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100">
                    <button type="button" @click="$dispatch('close-modal', 'tambah-penempatan-modal')" class="simmas-btn-secondary">Batal</button>
                    <button type="submit" class="simmas-btn-primary">Simpan Penempatan</button>
                </div>
            </form>
        </x-modal>

        <!-- Modal Edit Penempatan (Siswa Read-only) -->
        <x-modal name="edit-penempatan-modal" title="Edit Penempatan Magang" subtitle="Data siswa bersifat read-only pada perubahan penempatan" icon="pencil">
            <form method="POST" :action="'{{ url('/admin/penempatan') }}/' + editPenempatan.id" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Siswa (Read-only)</label>
                    <input type="text" :value="editPenempatan.siswa_nama + ' (' + editPenempatan.siswa_nis + ')'" readonly class="simmas-input">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Tempat Magang <span class="text-rose-500">*</span></label>
                    <select name="tempat_magang_id" x-model="editPenempatan.tempat_magang_id" required class="simmas-input">
                        @foreach($dudiList as $d)
                            <option value="{{ $d->id }}">{{ $d->nama_perusahaan }} (Sisa Kuota: {{ $d->sisa_kuota }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Guru Pembimbing <span class="text-rose-500">*</span></label>
                    <select name="guru_id" x-model="editPenempatan.guru_id" required class="simmas-input">
                        @foreach($activeGurus as $g)
                            <option value="{{ $g->id }}">{{ $g->profile?->nama }} (NIP: {{ $g->nip }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Tanggal Mulai <span class="text-rose-500">*</span></label>
                        <input type="date" name="tanggal_mulai" x-model="editPenempatan.tanggal_mulai" required class="simmas-input">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Tanggal Selesai <span class="text-rose-500">*</span></label>
                        <input type="date" name="tanggal_selesai" x-model="editPenempatan.tanggal_selesai" required class="simmas-input">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Catatan Admin</label>
                    <textarea name="catatan_admin" rows="2" x-model="editPenempatan.catatan_admin" class="simmas-input"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100">
                    <button type="button" @click="$dispatch('close-modal', 'edit-penempatan-modal')" class="simmas-btn-secondary">Batal</button>
                    <button type="submit" class="simmas-btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </x-modal>

        <!-- Modal Sahkan Penempatan -->
        <x-modal name="sahkan-penempatan-modal" title="Sahkan Status Penempatan" subtitle="Status magang siswa akan disinkronkan secara otomatis" icon="check-circle-2" iconColor="emerald" maxWidth="md">
            <form method="POST" :action="'{{ url('/admin/penempatan') }}/' + sahkanPenempatan.id + '/sahkan'" class="space-y-4">
                @csrf
                @method('PATCH')
                <p class="text-xs text-slate-600">Pilih status pengesahan penempatan untuk <strong class="text-slate-900" x-text="sahkanPenempatan.siswa_nama"></strong>:</p>

                <div class="space-y-2">
                    <label class="flex items-center gap-3 rounded-xl border border-slate-200 p-3.5 cursor-pointer hover:bg-slate-50">
                        <input type="radio" name="status_pengesahan" value="disahkan" x-model="sahkanPenempatan.status_pengesahan" class="text-emerald-600">
                        <div>
                            <p class="text-xs font-bold text-emerald-700">Disahkan (Sedang Magang)</p>
                            <p class="text-[11px] text-slate-500">Siswa resmi aktif melaksanakan PKL dan mengisi absensi</p>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 rounded-xl border border-slate-200 p-3.5 cursor-pointer hover:bg-slate-50">
                        <input type="radio" name="status_pengesahan" value="lulus_magang" x-model="sahkanPenempatan.status_pengesahan" class="text-violet-600">
                        <div>
                            <p class="text-xs font-bold text-violet-700">Lulus Magang</p>
                            <p class="text-[11px] text-slate-500">Siswa telah menyelesaikan periode magang dengan tuntas</p>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 rounded-xl border border-slate-200 p-3.5 cursor-pointer hover:bg-slate-50">
                        <input type="radio" name="status_pengesahan" value="belum_disahkan" x-model="sahkanPenempatan.status_pengesahan" class="text-amber-600">
                        <div>
                            <p class="text-xs font-bold text-amber-700">Belum Disahkan</p>
                            <p class="text-[11px] text-slate-500">Penempatan masih menunggu verifikasi surat tugas</p>
                        </div>
                    </label>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100">
                    <button type="button" @click="$dispatch('close-modal', 'sahkan-penempatan-modal')" class="simmas-btn-secondary">Batal</button>
                    <button type="submit" class="simmas-btn-primary">Simpan Pengesahan</button>
                </div>
            </form>
        </x-modal>

        <!-- Alert Batalkan Penempatan (Warna Oranye) -->
        <x-modal name="cancel-penempatan-modal" title="Batalkan Penempatan Magang" subtitle="Konfirmasi pembatalan penempatan siswa" icon="alert-triangle" iconColor="amber" maxWidth="md">
            <div class="space-y-4">
                <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-xs text-amber-800 leading-relaxed">
                    Apakah Anda yakin ingin membatalkan penempatan <strong x-text="cancelPenempatan.siswa_nama"></strong> di <strong x-text="cancelPenempatan.dudi_nama"></strong>? Status magang siswa akan dikembalikan menjadi <strong>"Belum Magang"</strong>.
                </div>

                <form method="POST" :action="'{{ url('/admin/penempatan') }}/' + cancelPenempatan.id" class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
                    @csrf
                    @method('DELETE')
                    <button type="button" @click="$dispatch('close-modal', 'cancel-penempatan-modal')" class="simmas-btn-secondary">Batal</button>
                    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-amber-600 hover:bg-amber-700 px-4 py-2.5 text-xs font-bold text-white cursor-pointer">
                        Ya, Batalkan Penempatan
                    </button>
                </form>
            </div>
        </x-modal>

        <!-- Modal Review Pengajuan Siswa -->
        <x-modal name="review-pengajuan-modal" title="Tinjau Pengajuan Magang Siswa" subtitle="Setujui sekaligus tetapkan Guru Pembimbing, atau tolak dengan alasan" icon="file-check-2">
            <form method="POST" :action="'{{ url('/admin/pengajuan') }}/' + reviewPengajuan.id + '/review'" class="space-y-4">
                @csrf
                @method('PATCH')
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3.5 text-xs space-y-1">
                    <p class="font-bold text-slate-900" x-text="reviewPengajuan.siswa_nama"></p>
                    <p class="text-slate-600">Mitra DUDI: <strong x-text="reviewPengajuan.dudi_nama"></strong></p>
                    <p class="text-blue-600 font-semibold">Posisi: <span x-text="reviewPengajuan.posisi"></span></p>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <label class="flex items-center gap-2.5 rounded-xl border border-slate-200 p-3 cursor-pointer hover:bg-emerald-50/40">
                        <input type="radio" name="status" value="disetujui" x-model="reviewPengajuan.status" class="text-emerald-600">
                        <span class="text-xs font-bold text-emerald-700">Setujui Pengajuan</span>
                    </label>
                    <label class="flex items-center gap-2.5 rounded-xl border border-slate-200 p-3 cursor-pointer hover:bg-rose-50/40">
                        <input type="radio" name="status" value="ditolak" x-model="reviewPengajuan.status" class="text-rose-600">
                        <span class="text-xs font-bold text-rose-700">Tolak Pengajuan</span>
                    </label>
                </div>

                <div x-show="reviewPengajuan.status === 'disetujui'">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Tetapkan Guru Pembimbing <span class="text-rose-500">*</span></label>
                    <select name="guru_id" x-model="reviewPengajuan.guru_id" :required="reviewPengajuan.status === 'disetujui'" class="simmas-input">
                        <option value="">-- Pilih Guru Pembimbing --</option>
                        @foreach($activeGurus as $g)
                            <option value="{{ $g->id }}">{{ $g->profile?->nama }} (NIP: {{ $g->nip }})</option>
                        @endforeach
                    </select>
                </div>

                <div x-show="reviewPengajuan.status === 'ditolak'" x-cloak>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Catatan Alasan Penolakan <span class="text-rose-500">*</span></label>
                    <textarea name="catatan_penolakan" rows="3" x-model="reviewPengajuan.catatan_penolakan" :required="reviewPengajuan.status === 'ditolak'" placeholder="Jelaskan alasan penolakan agar siswa dapat mengajukan kembali..." class="simmas-input"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100">
                    <button type="button" @click="$dispatch('close-modal', 'review-pengajuan-modal')" class="simmas-btn-secondary">Batal</button>
                    <button type="submit" class="simmas-btn-primary">Simpan Keputusan</button>
                </div>
            </form>
        </x-modal>

    </div>

</x-layouts.app>

