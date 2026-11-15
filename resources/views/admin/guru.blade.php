<x-layouts.app title="Master Data Guru" subtitle="Kelola data guru pembimbing magang dan kredensial akun akses">

    @php
        $createdAccount = session('created_account');
    @endphp

    <div x-data="{
        editGuru: { id: '', nama: '', nip: '', jurusan: '' },
        statusGuru: { id: '', nama: '', is_active: 1 },
        deleteGuru: { id: '', nama: '', nip: '', bimbingan_count: 0 },
        accountModalOpen: {{ $createdAccount ? 'true' : 'false' }},
        copiedText: '',
        openEdit(g) {
            this.editGuru = g;
            this.$dispatch('open-modal', 'edit-guru-modal');
        },
        openStatus(g) {
            this.statusGuru = g;
            this.$dispatch('open-modal', 'status-guru-modal');
        },
        openDelete(g) {
            this.deleteGuru = g;
            this.$dispatch('open-modal', 'delete-guru-modal');
        },
        copyText(val, label) {
            navigator.clipboard.writeText(val);
            this.copiedText = label;
            setTimeout(() => this.copiedText = '', 2000);
        }
    }" class="space-y-6">

        <!-- 3 Stat Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            <x-stat-card
                label="Total Guru Pembimbing"
                :value="$totalGuru"
                subtitle="Terdaftar pada sistem"
                icon="users"
                color="blue"
            />
            <x-stat-card
                label="Guru Aktif"
                :value="$guruAktif"
                subtitle="Siap membimbing siswa PKL"
                icon="user-check"
                color="emerald"
            />
            <x-stat-card
                label="Rata-rata Bimbingan"
                :value="$rataRataBimbingan . ' Siswa'"
                subtitle="Beban bimbingan per guru aktif"
                icon="award"
                color="violet"
            />
        </div>

        <!-- Filter & Table Card -->
        <div class="simmas-card overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <form method="GET" action="{{ route('admin.guru') }}" class="flex flex-1 flex-wrap items-center gap-3">
                    <div class="relative flex-1 min-w-[220px] max-w-sm">
                        <input
                            type="text"
                            name="q"
                            value="{{ $search }}"
                            placeholder="Cari nama guru atau NIP..."
                            class="simmas-input pl-10"
                        >
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                            <i data-lucide="search" class="h-4 w-4"></i>
                        </span>
                    </div>

                    <select name="jurusan" onchange="this.form.submit()" class="simmas-input w-auto min-w-[180px]">
                        <option value="">Semua Jurusan</option>
                        @foreach($jurusanList as $jrs)
                            <option value="{{ $jrs }}" @selected($jurusanFilter === $jrs)>{{ $jrs }}</option>
                        @endforeach
                    </select>

                    <select name="status" onchange="this.form.submit()" class="simmas-input w-auto min-w-[140px]">
                        <option value="">Semua Status</option>
                        <option value="aktif" @selected($statusFilter === 'aktif')>Aktif</option>
                        <option value="nonaktif" @selected($statusFilter === 'nonaktif')>Nonaktif</option>
                    </select>

                    <button type="submit" class="simmas-btn-secondary">Cari</button>
                </form>

                <button
                    type="button"
                    @click="$dispatch('open-modal', 'tambah-guru-modal')"
                    class="simmas-btn-primary shrink-0"
                >
                    <i data-lucide="plus" class="h-4 w-4"></i>
                    <span>+ Tambah Guru</span>
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr>
                            <th class="simmas-table-th">NIP</th>
                            <th class="simmas-table-th">Nama Lengkap</th>
                            <th class="simmas-table-th">Jurusan</th>
                            <th class="simmas-table-th text-center">Bimbingan</th>
                            <th class="simmas-table-th">Status</th>
                            <th class="simmas-table-th text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($gurus as $guru)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="simmas-table-td font-mono text-xs font-semibold text-slate-600">
                                    {{ $guru->nip }}
                                </td>
                                <td class="simmas-table-td">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600 text-xs font-bold">
                                            {{ $guru->profile?->initials ?? 'GR' }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-900">{{ $guru->profile?->nama }}</p>
                                            <p class="text-xs text-slate-400">{{ $guru->profile?->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="simmas-table-td text-xs font-medium text-slate-700">
                                    {{ $guru->jurusan }}
                                </td>
                                <td class="simmas-table-td text-center">
                                    <x-badge variant="blue">{{ $guru->bimbingan_aktif_count }} Siswa Aktif</x-badge>
                                </td>
                                <td class="simmas-table-td">
                                    @if($guru->is_active)
                                        <x-badge variant="emerald" :dot="true">Aktif</x-badge>
                                    @else
                                        <x-badge variant="rose" :dot="true">Nonaktif</x-badge>
                                    @endif
                                </td>
                                <td class="simmas-table-td text-right">
                                    <div class="inline-flex items-center gap-1.5">
                                        <button
                                            type="button"
                                            title="Edit Guru"
                                            @click="openEdit(@js([
                                                'id' => $guru->id,
                                                'nama' => $guru->profile?->nama,
                                                'nip' => $guru->nip,
                                                'jurusan' => $guru->jurusan,
                                            ]))"
                                            class="rounded-lg border border-slate-200 p-1.5 text-slate-600 hover:bg-slate-100 cursor-pointer"
                                        >
                                            <i data-lucide="pencil" class="h-3.5 w-3.5"></i>
                                        </button>
                                        <button
                                            type="button"
                                            title="Ubah Status"
                                            @click="openStatus(@js([
                                                'id' => $guru->id,
                                                'nama' => $guru->profile?->nama,
                                                'is_active' => $guru->is_active ? 1 : 0,
                                            ]))"
                                            class="rounded-lg border border-slate-200 p-1.5 text-amber-600 hover:bg-amber-50 cursor-pointer"
                                        >
                                            <i data-lucide="power" class="h-3.5 w-3.5"></i>
                                        </button>
                                        <button
                                            type="button"
                                            title="Hapus Guru"
                                            @click="openDelete(@js([
                                                'id' => $guru->id,
                                                'nama' => $guru->profile?->nama,
                                                'nip' => $guru->nip,
                                                'bimbingan_count' => $guru->bimbingan_aktif_count,
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
                                <td colspan="6">
                                    <x-empty-state title="Data guru tidak ditemukan" description="Belum ada data guru atau tidak ada yang cocok dengan filter." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-pagination-bar :paginator="$gurus" />
        </div>

        <!-- Modal Tambah Guru -->
        <x-modal name="tambah-guru-modal" title="Tambah Guru Pembimbing" subtitle="Akun login akan dibuat otomatis dari nama lengkap guru" icon="user-plus">
            <form method="POST" action="{{ route('admin.guru.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Lengkap beserta Gelar <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama" required placeholder="Contoh: Ahmad Fauzi, S.Kom." class="simmas-input">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">NIP (18 Digit Numerik) <span class="text-rose-500">*</span></label>
                    <input type="text" name="nip" required pattern="\d{18}" maxlength="18" placeholder="Contoh: 198504122010011009" class="simmas-input font-mono">
                    <p class="mt-1 text-[11px] text-slate-400">Wajib tepat 18 digit angka tanpa spasi.</p>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Kompetensi Keahlian / Jurusan <span class="text-rose-500">*</span></label>
                    <select name="jurusan" required class="simmas-input">
                        <option value="">-- Pilih Jurusan --</option>
                        <option value="Rekayasa Perangkat Lunak">Rekayasa Perangkat Lunak</option>
                        <option value="Teknik Komputer dan Jaringan">Teknik Komputer dan Jaringan</option>
                        <option value="Desain Komunikasi Visual">Desain Komunikasi Visual</option>
                        <option value="Sistem Informasi Jaringan dan Aplikasi">Sistem Informasi Jaringan dan Aplikasi</option>
                    </select>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100">
                    <button type="button" @click="$dispatch('close-modal', 'tambah-guru-modal')" class="simmas-btn-secondary">Batal</button>
                    <button type="submit" class="simmas-btn-primary">Simpan & Buat Akun</button>
                </div>
            </form>
        </x-modal>

        <!-- Modal Edit Guru -->
        <x-modal name="edit-guru-modal" title="Edit Data Guru" subtitle="Perbarui nama atau kompetensi keahlian guru pembimbing" icon="pencil">
            <form method="POST" :action="'{{ url('/admin/guru') }}/' + editGuru.id" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">NIP (Read-only)</label>
                    <input type="text" :value="editGuru.nip" readonly class="simmas-input font-mono">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Lengkap beserta Gelar <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama" x-model="editGuru.nama" required class="simmas-input">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Kompetensi Keahlian / Jurusan <span class="text-rose-500">*</span></label>
                    <select name="jurusan" x-model="editGuru.jurusan" required class="simmas-input">
                        <option value="Rekayasa Perangkat Lunak">Rekayasa Perangkat Lunak</option>
                        <option value="Teknik Komputer dan Jaringan">Teknik Komputer dan Jaringan</option>
                        <option value="Desain Komunikasi Visual">Desain Komunikasi Visual</option>
                        <option value="Sistem Informasi Jaringan dan Aplikasi">Sistem Informasi Jaringan dan Aplikasi</option>
                    </select>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100">
                    <button type="button" @click="$dispatch('close-modal', 'edit-guru-modal')" class="simmas-btn-secondary">Batal</button>
                    <button type="submit" class="simmas-btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </x-modal>

        <!-- Modal Ubah Status Guru -->
        <x-modal name="status-guru-modal" title="Ubah Status Guru" subtitle="Guru dengan status Nonaktif tidak dapat masuk ke sistem" icon="power" iconColor="amber" maxWidth="md">
            <form method="POST" :action="'{{ url('/admin/guru') }}/' + statusGuru.id + '/status'" class="space-y-4">
                @csrf
                @method('PATCH')
                <p class="text-xs text-slate-600">
                    Ubah status akses akun untuk <strong class="text-slate-900" x-text="statusGuru.nama"></strong>:
                </p>
                <div class="space-y-2">
                    <label class="flex items-center gap-3 rounded-xl border border-slate-200 p-3.5 cursor-pointer hover:bg-slate-50">
                        <input type="radio" name="is_active" value="1" :checked="statusGuru.is_active == 1" class="text-blue-600">
                        <div>
                            <p class="text-xs font-bold text-slate-900">Aktif</p>
                            <p class="text-[11px] text-slate-500">Dapat login dan membimbing siswa magang</p>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 rounded-xl border border-slate-200 p-3.5 cursor-pointer hover:bg-slate-50">
                        <input type="radio" name="is_active" value="0" :checked="statusGuru.is_active == 0" class="text-rose-600">
                        <div>
                            <p class="text-xs font-bold text-rose-700">Nonaktif</p>
                            <p class="text-[11px] text-slate-500">Akses login ke portal diblokir sementara</p>
                        </div>
                    </label>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100">
                    <button type="button" @click="$dispatch('close-modal', 'status-guru-modal')" class="simmas-btn-secondary">Batal</button>
                    <button type="submit" class="simmas-btn-primary">Simpan Status</button>
                </div>
            </form>
        </x-modal>

        <!-- Alert Hapus Guru -->
        <x-modal name="delete-guru-modal" title="Hapus Data Guru" subtitle="Tindakan ini akan menghapus data guru beserta akun login" icon="trash-2" iconColor="rose" maxWidth="md">
            <div class="space-y-4">
                <template x-if="deleteGuru.bimbingan_count > 0">
                    <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-xs text-rose-700">
                        <p class="font-bold">Tidak Dapat Menghapus Guru!</p>
                        <p class="mt-1">Guru <strong x-text="deleteGuru.nama"></strong> masih membimbing <strong x-text="deleteGuru.bimbingan_count"></strong> siswa aktif. Silakan alihkan pembimbing siswa terlebih dahulu.</p>
                    </div>
                </template>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Apakah Anda yakin ingin menghapus data guru <strong class="text-slate-900" x-text="deleteGuru.nama"></strong> (NIP: <span class="font-mono" x-text="deleteGuru.nip"></span>)? Akun login terkait juga akan dihapus secara permanen.
                </p>

                <form method="POST" :action="'{{ url('/admin/guru') }}/' + deleteGuru.id" class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
                    @csrf
                    @method('DELETE')
                    <button type="button" @click="$dispatch('close-modal', 'delete-guru-modal')" class="simmas-btn-secondary">Batal</button>
                    <button type="submit" class="simmas-btn-danger">Ya, Hapus Guru</button>
                </form>
            </div>
        </x-modal>

        <!-- Modal Akun Guru Berhasil Dibuat -->
        @if($createdAccount)
            <div x-show="accountModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div @click="accountModalOpen = false" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs"></div>
                <div class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl border border-slate-200 space-y-5">
                    <div class="flex items-center gap-3.5">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 ring-1 ring-emerald-100">
                            <i data-lucide="check-circle-2" class="h-6 w-6"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-extrabold text-slate-900">{{ $createdAccount['title'] }}</h3>
                            <p class="text-xs text-slate-500">Salin kredensial sementara untuk {{ $createdAccount['nama'] }}</p>
                        </div>
                    </div>

                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 space-y-3 text-xs">
                        <div class="flex items-center justify-between gap-2">
                            <div>
                                <p class="text-[10px] font-bold uppercase text-slate-400">Email Akun</p>
                                <p class="font-mono font-bold text-slate-900 mt-0.5">{{ $createdAccount['email'] }}</p>
                            </div>
                            <button type="button" @click="copyText(@js($createdAccount['email']), 'email')" class="simmas-btn-secondary py-1.5 px-2.5 text-xs">
                                <span x-text="copiedText === 'email' ? 'Tersalin!' : 'Salin'"></span>
                            </button>
                        </div>
                        <div class="flex items-center justify-between gap-2 pt-2.5 border-t border-slate-200">
                            <div>
                                <p class="text-[10px] font-bold uppercase text-slate-400">Password Sementara</p>
                                <p class="font-mono font-bold text-blue-600 mt-0.5">{{ $createdAccount['password'] }}</p>
                            </div>
                            <button type="button" @click="copyText(@js($createdAccount['password']), 'password')" class="simmas-btn-secondary py-1.5 px-2.5 text-xs">
                                <span x-text="copiedText === 'password' ? 'Tersalin!' : 'Salin'"></span>
                            </button>
                        </div>
                    </div>

                    <button type="button" @click="accountModalOpen = false" class="simmas-btn-primary w-full">
                        Selesai
                    </button>
                </div>
            </div>
        @endif

    </div>

</x-layouts.app>

