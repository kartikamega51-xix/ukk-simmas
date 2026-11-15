<x-layouts.app title="Master Data Mitra DUDI" subtitle="Kelola perusahaan mitra Dunia Usaha dan Dunia Industri beserta kuota magang">

    <div x-data="{
        editDudi: { id: '', nama_perusahaan: '', bidang_usaha: '', penanggung_jawab: '', kontak_pic: '', kuota: 5, is_verified: 1, alamat: '' },
        verifyDudi: { id: '', nama_perusahaan: '', is_verified: 1 },
        deleteDudi: { id: '', nama_perusahaan: '', siswa_aktif_count: 0 },
        openEdit(d) {
            this.editDudi = d;
            this.$dispatch('open-modal', 'edit-dudi-modal');
        },
        openVerify(d) {
            this.verifyDudi = d;
            this.$dispatch('open-modal', 'verify-dudi-modal');
        },
        openDelete(d) {
            this.deleteDudi = d;
            this.$dispatch('open-modal', 'delete-dudi-modal');
        }
    }" class="space-y-6">

        <!-- 4 Stat Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <x-stat-card label="Total Mitra DUDI" :value="$totalDudi" subtitle="Perusahaan terdaftar" icon="building-2" color="blue" />
            <x-stat-card label="Terverifikasi" :value="$terverifikasi" subtitle="Siap menerima penempatan" icon="shield-check" color="emerald" />
            <x-stat-card label="Menunggu Validasi" :value="$menungguValidasi" subtitle="Belum diverifikasi sekolah" icon="clock" color="amber" />
            <x-stat-card label="Siswa Ditempatkan" :value="$siswaDitempatkan" subtitle="Total siswa aktif di DUDI" icon="users" color="violet" />
        </div>

        <!-- Search + Filter + Table -->
        <div class="simmas-card overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <form method="GET" action="{{ route('admin.dudi') }}" class="flex flex-1 flex-wrap items-center gap-3">
                    <div class="relative flex-1 min-w-[220px] max-w-md">
                        <input
                            type="text"
                            name="q"
                            value="{{ $search }}"
                            placeholder="Cari nama perusahaan, bidang usaha, atau PIC..."
                            class="simmas-input pl-10"
                        >
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                            <i data-lucide="search" class="h-4 w-4"></i>
                        </span>
                    </div>

                    <select name="status" onchange="this.form.submit()" class="simmas-input w-auto min-w-[160px]">
                        <option value="">Semua Status</option>
                        <option value="verified" @selected($statusFilter === 'verified')>Terverifikasi</option>
                        <option value="unverified" @selected($statusFilter === 'unverified')>Belum Diverifikasi</option>
                    </select>

                    <button type="submit" class="simmas-btn-secondary">Filter</button>
                </form>

                <button
                    type="button"
                    @click="$dispatch('open-modal', 'tambah-dudi-modal')"
                    class="simmas-btn-primary shrink-0"
                >
                    <i data-lucide="plus" class="h-4 w-4"></i>
                    <span>+ Tambah DUDI</span>
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr>
                            <th class="simmas-table-th">Perusahaan</th>
                            <th class="simmas-table-th">PIC & Kontak</th>
                            <th class="simmas-table-th">Bidang Usaha</th>
                            <th class="simmas-table-th text-center">Kuota</th>
                            <th class="simmas-table-th text-center">Siswa Aktif</th>
                            <th class="simmas-table-th">Status Verifikasi</th>
                            <th class="simmas-table-th text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dudis as $dudi)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="simmas-table-td">
                                    <div class="flex items-start gap-3">
                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-violet-50 text-violet-600">
                                            <i data-lucide="building-2" class="h-4 w-4"></i>
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-900">{{ $dudi->nama_perusahaan }}</p>
                                            <p class="text-xs text-slate-400 max-w-xs truncate">{{ $dudi->alamat }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="simmas-table-td">
                                    <p class="font-semibold text-slate-800 text-xs">{{ $dudi->penanggung_jawab }}</p>
                                    <p class="font-mono text-xs text-slate-500 mt-0.5">{{ $dudi->kontak_pic }}</p>
                                </td>
                                <td class="simmas-table-td text-xs text-slate-600">
                                    {{ $dudi->bidang_usaha }}
                                </td>
                                <td class="simmas-table-td text-center font-mono font-bold text-slate-800">
                                    {{ $dudi->kuota }}
                                </td>
                                <td class="simmas-table-td text-center">
                                    <x-badge variant="blue">{{ $dudi->siswa_aktif_count }} / {{ $dudi->kuota }}</x-badge>
                                </td>
                                <td class="simmas-table-td">
                                    @if($dudi->is_verified)
                                        <x-badge variant="emerald" :dot="true">Terverifikasi</x-badge>
                                    @else
                                        <x-badge variant="amber" :dot="true">Belum Diverifikasi</x-badge>
                                    @endif
                                </td>
                                <td class="simmas-table-td text-right">
                                    <div class="inline-flex items-center gap-1.5">
                                        <button
                                            type="button"
                                            title="Edit DUDI"
                                            @click="openEdit(@js([
                                                'id' => $dudi->id,
                                                'nama_perusahaan' => $dudi->nama_perusahaan,
                                                'bidang_usaha' => $dudi->bidang_usaha,
                                                'penanggung_jawab' => $dudi->penanggung_jawab,
                                                'kontak_pic' => $dudi->kontak_pic,
                                                'kuota' => $dudi->kuota,
                                                'is_verified' => $dudi->is_verified ? 1 : 0,
                                                'alamat' => $dudi->alamat,
                                            ]))"
                                            class="rounded-lg border border-slate-200 p-1.5 text-slate-600 hover:bg-slate-100 cursor-pointer"
                                        >
                                            <i data-lucide="pencil" class="h-3.5 w-3.5"></i>
                                        </button>
                                        <button
                                            type="button"
                                            title="Ubah Status Verifikasi"
                                            @click="openVerify(@js([
                                                'id' => $dudi->id,
                                                'nama_perusahaan' => $dudi->nama_perusahaan,
                                                'is_verified' => $dudi->is_verified ? 1 : 0,
                                            ]))"
                                            class="rounded-lg border border-slate-200 p-1.5 text-emerald-600 hover:bg-emerald-50 cursor-pointer"
                                        >
                                            <i data-lucide="shield-check" class="h-3.5 w-3.5"></i>
                                        </button>
                                        <button
                                            type="button"
                                            title="Hapus DUDI"
                                            @click="openDelete(@js([
                                                'id' => $dudi->id,
                                                'nama_perusahaan' => $dudi->nama_perusahaan,
                                                'siswa_aktif_count' => $dudi->siswa_aktif_count,
                                            ]))"
                                            class="rounded-lg border border-slate-200 p-1.5 text-rose-600 hover:bg-rose-50 cursor-pointer"
                                        >
                                            <i data-lucide="trash-2" class="h-3.5 w-3.5"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <x-empty-state title="Mitra DUDI tidak ditemukan" description="Belum ada perusahaan mitra yang sesuai dengan pencarian." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-pagination-bar :paginator="$dudis" />
        </div>

        <!-- Modal Tambah DUDI -->
        <x-modal name="tambah-dudi-modal" title="Tambah Mitra DUDI" subtitle="Daftarkan perusahaan mitra baru beserta daya tampung magang" icon="building-2" maxWidth="xl">
            <form method="POST" action="{{ route('admin.dudi.store') }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Perusahaan <span class="text-rose-500">*</span></label>
                        <input type="text" name="nama_perusahaan" required placeholder="Contoh: PT. Nusantara Digital" class="simmas-input">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Bidang Usaha <span class="text-rose-500">*</span></label>
                        <input type="text" name="bidang_usaha" required placeholder="Contoh: Software House & Cloud" class="simmas-input">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama PIC (Penanggung Jawab) <span class="text-rose-500">*</span></label>
                        <input type="text" name="penanggung_jawab" required placeholder="Contoh: Hendra Wijaya" class="simmas-input">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Kontak PIC (WA/Telp) <span class="text-rose-500">*</span></label>
                        <input type="text" name="kontak_pic" required placeholder="Contoh: 081234567890" class="simmas-input font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Kuota Magang <span class="text-rose-500">*</span></label>
                        <input type="number" name="kuota" required min="1" max="500" value="5" class="simmas-input font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Status Verifikasi <span class="text-rose-500">*</span></label>
                        <select name="is_verified" required class="simmas-input">
                            <option value="1">Terverifikasi</option>
                            <option value="0">Belum Diverifikasi</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Alamat Lengkap <span class="text-rose-500">*</span></label>
                    <textarea name="alamat" rows="3" required placeholder="Masukkan alamat lengkap kantor/industri..." class="simmas-input"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100">
                    <button type="button" @click="$dispatch('close-modal', 'tambah-dudi-modal')" class="simmas-btn-secondary">Batal</button>
                    <button type="submit" class="simmas-btn-primary">Simpan Mitra DUDI</button>
                </div>
            </form>
        </x-modal>

        <!-- Modal Edit DUDI -->
        <x-modal name="edit-dudi-modal" title="Edit Mitra DUDI" subtitle="Perbarui informasi perusahaan mitra dan kuota penerimaan" icon="pencil" maxWidth="xl">
            <form method="POST" :action="'{{ url('/admin/dudi') }}/' + editDudi.id" class="space-y-4">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Perusahaan <span class="text-rose-500">*</span></label>
                        <input type="text" name="nama_perusahaan" x-model="editDudi.nama_perusahaan" required class="simmas-input">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Bidang Usaha <span class="text-rose-500">*</span></label>
                        <input type="text" name="bidang_usaha" x-model="editDudi.bidang_usaha" required class="simmas-input">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama PIC <span class="text-rose-500">*</span></label>
                        <input type="text" name="penanggung_jawab" x-model="editDudi.penanggung_jawab" required class="simmas-input">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Kontak PIC (WA/Telp) <span class="text-rose-500">*</span></label>
                        <input type="text" name="kontak_pic" x-model="editDudi.kontak_pic" required class="simmas-input font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Kuota Magang <span class="text-rose-500">*</span></label>
                        <input type="number" name="kuota" x-model="editDudi.kuota" required min="0" max="500" class="simmas-input font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Status Verifikasi <span class="text-rose-500">*</span></label>
                        <select name="is_verified" x-model="editDudi.is_verified" required class="simmas-input">
                            <option value="1">Terverifikasi</option>
                            <option value="0">Belum Diverifikasi</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Alamat Lengkap <span class="text-rose-500">*</span></label>
                    <textarea name="alamat" rows="3" x-model="editDudi.alamat" required class="simmas-input"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100">
                    <button type="button" @click="$dispatch('close-modal', 'edit-dudi-modal')" class="simmas-btn-secondary">Batal</button>
                    <button type="submit" class="simmas-btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </x-modal>

        <!-- Modal Ubah Status Verifikasi DUDI -->
        <x-modal name="verify-dudi-modal" title="Ubah Status Verifikasi DUDI" subtitle="Hanya DUDI Terverifikasi yang dapat dipilih siswa saat pengajuan" icon="shield-check" iconColor="emerald" maxWidth="md">
            <form method="POST" :action="'{{ url('/admin/dudi') }}/' + verifyDudi.id + '/verify'" class="space-y-4">
                @csrf
                @method('PATCH')
                <p class="text-xs text-slate-600">Pilih status verifikasi untuk <strong class="text-slate-900" x-text="verifyDudi.nama_perusahaan"></strong>:</p>
                <div class="space-y-2">
                    <label class="flex items-center gap-3 rounded-xl border border-slate-200 p-3.5 cursor-pointer hover:bg-slate-50">
                        <input type="radio" name="is_verified" value="1" :checked="verifyDudi.is_verified == 1" class="text-emerald-600">
                        <div>
                            <p class="text-xs font-bold text-emerald-700">Terverifikasi</p>
                            <p class="text-[11px] text-slate-500">Perusahaan telah divalidasi dan terbuka untuk penempatan siswa</p>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 rounded-xl border border-slate-200 p-3.5 cursor-pointer hover:bg-slate-50">
                        <input type="radio" name="is_verified" value="0" :checked="verifyDudi.is_verified == 0" class="text-amber-600">
                        <div>
                            <p class="text-xs font-bold text-amber-700">Belum Diverifikasi</p>
                            <p class="text-[11px] text-slate-500">Sembunyikan sementara dari daftar pengajuan siswa</p>
                        </div>
                    </label>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100">
                    <button type="button" @click="$dispatch('close-modal', 'verify-dudi-modal')" class="simmas-btn-secondary">Batal</button>
                    <button type="submit" class="simmas-btn-primary">Simpan Status</button>
                </div>
            </form>
        </x-modal>

        <!-- Alert Hapus DUDI -->
        <x-modal name="delete-dudi-modal" title="Hapus Mitra DUDI" subtitle="Konfirmasi penghapusan perusahaan mitra" icon="trash-2" iconColor="rose" maxWidth="md">
            <div class="space-y-4">
                <template x-if="deleteDudi.siswa_aktif_count > 0">
                    <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-xs text-rose-700">
                        <p class="font-bold">Penghapusan Diblokir!</p>
                        <p class="mt-1">Masih terdapat <strong x-text="deleteDudi.siswa_aktif_count"></strong> siswa aktif magang di <strong x-text="deleteDudi.nama_perusahaan"></strong>.</p>
                    </div>
                </template>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Apakah Anda yakin ingin menghapus data perusahaan mitra <strong class="text-slate-900" x-text="deleteDudi.nama_perusahaan"></strong>?
                </p>

                <form method="POST" :action="'{{ url('/admin/dudi') }}/' + deleteDudi.id" class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
                    @csrf
                    @method('DELETE')
                    <button type="button" @click="$dispatch('close-modal', 'delete-dudi-modal')" class="simmas-btn-secondary">Batal</button>
                    <button type="submit" class="simmas-btn-danger">Ya, Hapus DUDI</button>
                </form>
            </div>
        </x-modal>

    </div>

</x-layouts.app>

