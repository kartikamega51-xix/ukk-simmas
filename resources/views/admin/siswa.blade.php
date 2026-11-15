<x-layouts.app title="Master Data Siswa" subtitle="Kelola data siswa peserta PKL, plotting guru pembimbing, dan tempat magang">

    @php
        $createdAccount = session('created_account');
    @endphp

    <div x-data="{
        editSiswa: { id: '', nama: '', nis: '', kelas: '' },
        statusSiswa: { id: '', nama: '', is_active: 1 },
        plotSiswa: { id: '', nama: '', nis: '', kelas: '', guru_id: '', tempat_magang_id: '' },
        deleteSiswa: { id: '', nama: '', nis: '' },
        accountModalOpen: {{ $createdAccount ? 'true' : 'false' }},
        copiedText: '',
        openEdit(s) {
            this.editSiswa = s;
            this.$dispatch('open-modal', 'edit-siswa-modal');
        },
        openStatus(s) {
            this.statusSiswa = s;
            this.$dispatch('open-modal', 'status-siswa-modal');
        },
        openPlot(s) {
            this.plotSiswa = s;
            this.$dispatch('open-modal', 'plot-siswa-modal');
        },
        openDelete(s) {
            this.deleteSiswa = s;
            this.$dispatch('open-modal', 'delete-siswa-modal');
        },
        copyText(val, label) {
            navigator.clipboard.writeText(val);
            this.copiedText = label;
            setTimeout(() => this.copiedText = '', 2000);
        }
    }" class="space-y-6">

        <!-- 4 Stat Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <x-stat-card label="Total Siswa" :value="$totalSiswa" subtitle="Seluruh siswa terdaftar" icon="users" color="blue" />
            <x-stat-card label="Sedang Magang" :value="$sedangMagang" subtitle="Aktif melaksanakan PKL" icon="briefcase" color="emerald" />
            <x-stat-card label="Belum Magang" :value="$belumMagang" subtitle="Belum ditempatkan / pengajuan" icon="clock" color="amber" />
            <x-stat-card label="Lulus Magang" :value="$lulusMagang" subtitle="Telah menyelesaikan PKL" icon="graduation-cap" color="violet" />
        </div>

        <!-- Filter & Table Card -->
        <div class="simmas-card overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex flex-col xl:flex-row xl:items-center justify-between gap-4">
                <form method="GET" action="{{ route('admin.siswa') }}" class="flex flex-1 flex-wrap items-center gap-3">
                    <div class="relative flex-1 min-w-[200px] max-w-xs">
                        <input
                            type="text"
                            name="q"
                            value="{{ $search }}"
                            placeholder="Cari nama, NIS, atau kelas..."
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

                    <select name="industri" onchange="this.form.submit()" class="simmas-input w-auto min-w-[160px]">
                        <option value="">Semua Industri</option>
                        @foreach($allDudi as $d)
                            <option value="{{ $d->id }}" @selected($industriFilter === $d->id)>{{ $d->nama_perusahaan }}</option>
                        @endforeach
                    </select>

                    <select name="status" onchange="this.form.submit()" class="simmas-input w-auto min-w-[150px]">
                        <option value="">Semua Status</option>
                        <option value="sedang_magang" @selected($statusFilter === 'sedang_magang')>Sedang Magang</option>
                        <option value="pengajuan" @selected($statusFilter === 'pengajuan')>Pengajuan</option>
                        <option value="belum_magang" @selected($statusFilter === 'belum_magang')>Belum Magang</option>
                        <option value="lulus" @selected($statusFilter === 'lulus')>Lulus Magang</option>
                    </select>

                    <button type="submit" class="simmas-btn-secondary">Filter</button>
                </form>

                <button
                    type="button"
                    @click="$dispatch('open-modal', 'tambah-siswa-modal')"
                    class="simmas-btn-primary shrink-0"
                >
                    <i data-lucide="plus" class="h-4 w-4"></i>
                    <span>+ Tambah Siswa</span>
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr>
                            <th class="simmas-table-th">NIS</th>
                            <th class="simmas-table-th">Nama & Email</th>
                            <th class="simmas-table-th">Kelas</th>
                            <th class="simmas-table-th">Status Magang</th>
                            <th class="simmas-table-th">Industri</th>
                            <th class="simmas-table-th">Guru Pembimbing</th>
                            <th class="simmas-table-th text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($siswas as $siswa)
                            @php
                                $penempatan = $siswa->penempatanAktif;
                                $statusVariant = match($siswa->status_magang) {
                                    'sedang_magang' => 'emerald',
                                    'pengajuan' => 'amber',
                                    'lulus' => 'violet',
                                    default => 'slate',
                                };
                                $statusLabel = match($siswa->status_magang) {
                                    'sedang_magang' => 'Sedang Magang',
                                    'pengajuan' => 'Pengajuan',
                                    'lulus' => 'Lulus Magang',
                                    default => 'Belum Magang',
                                };
                            @endphp
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="simmas-table-td font-mono text-xs font-semibold text-slate-600">
                                    {{ $siswa->nis }}
                                </td>
                                <td class="simmas-table-td">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600 text-xs font-bold">
                                            {{ $siswa->profile?->initials ?? 'SW' }}
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-1.5">
                                                <p class="font-bold text-slate-900">{{ $siswa->profile?->nama }}</p>
                                                @if(!$siswa->is_active)
                                                    <span class="rounded bg-rose-50 px-1.5 py-0.5 text-[10px] font-bold text-rose-600">Nonaktif</span>
                                                @endif
                                            </div>
                                            <p class="text-xs text-slate-400">{{ $siswa->profile?->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="simmas-table-td">
                                    <x-badge variant="mono">{{ $siswa->kelas }}</x-badge>
                                </td>
                                <td class="simmas-table-td">
                                    <x-badge :variant="$statusVariant" :dot="true">{{ $statusLabel }}</x-badge>
                                </td>
                                <td class="simmas-table-td text-xs font-semibold text-slate-700">
                                    {{ $penempatan?->tempatMagang?->nama_perusahaan ?? '-' }}
                                </td>
                                <td class="simmas-table-td text-xs text-slate-600">
                                    {{ $penempatan?->guru?->profile?->nama ?? '-' }}
                                </td>
                                <td class="simmas-table-td text-right">
                                    <div class="inline-flex items-center gap-1.5">
                                        <button
                                            type="button"
                                            title="Plotting Guru & DUDI"
                                            @click="openPlot(@js([
                                                'id' => $siswa->id,
                                                'nama' => $siswa->profile?->nama,
                                                'nis' => $siswa->nis,
                                                'kelas' => $siswa->kelas,
                                                'guru_id' => $penempatan?->guru_id ?? '',
                                                'tempat_magang_id' => $penempatan?->tempat_magang_id ?? '',
                                            ]))"
                                            class="rounded-lg border border-blue-200 bg-blue-50 px-2.5 py-1.5 text-xs font-bold text-blue-600 hover:bg-blue-100 cursor-pointer inline-flex items-center gap-1"
                                        >
                                            <i data-lucide="git-branch" class="h-3.5 w-3.5"></i>
                                            <span>Plot</span>
                                        </button>
                                        <button
                                            type="button"
                                            title="Edit Siswa"
                                            @click="openEdit(@js([
                                                'id' => $siswa->id,
                                                'nama' => $siswa->profile?->nama,
                                                'nis' => $siswa->nis,
                                                'kelas' => $siswa->kelas,
                                            ]))"
                                            class="rounded-lg border border-slate-200 p-1.5 text-slate-600 hover:bg-slate-100 cursor-pointer"
                                        >
                                            <i data-lucide="pencil" class="h-3.5 w-3.5"></i>
                                        </button>
                                        <button
                                            type="button"
                                            title="Ubah Status Akun"
                                            @click="openStatus(@js([
                                                'id' => $siswa->id,
                                                'nama' => $siswa->profile?->nama,
                                                'is_active' => $siswa->is_active ? 1 : 0,
                                            ]))"
                                            class="rounded-lg border border-slate-200 p-1.5 text-amber-600 hover:bg-amber-50 cursor-pointer"
                                        >
                                            <i data-lucide="power" class="h-3.5 w-3.5"></i>
                                        </button>
                                        <button
                                            type="button"
                                            title="Hapus Siswa"
                                            @click="openDelete(@js([
                                                'id' => $siswa->id,
                                                'nama' => $siswa->profile?->nama,
                                                'nis' => $siswa->nis,
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
                                    <x-empty-state title="Data siswa tidak ditemukan" description="Tidak ada siswa yang sesuai dengan kriteria pencarian Anda." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-pagination-bar :paginator="$siswas" />
        </div>

        <!-- Modal Tambah Siswa -->
        <x-modal name="tambah-siswa-modal" title="Tambah Data Siswa" subtitle="Akun login otomatis berformat NIS@siswa.smk.sch.id" icon="user-plus">
            <form method="POST" action="{{ route('admin.siswa.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Lengkap Siswa <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama" required placeholder="Contoh: Budi Pratama" class="simmas-input">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Nomor Induk Siswa (NIS) <span class="text-rose-500">*</span></label>
                    <input type="text" name="nis" required pattern="\d+" placeholder="Contoh: 20261008" class="simmas-input font-mono">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Kelas <span class="text-rose-500">*</span></label>
                    <select name="kelas" required class="simmas-input">
                        <option value="">-- Pilih Kelas --</option>
                        <option value="XII RPL A">XII RPL A</option>
                        <option value="XII RPL B">XII RPL B</option>
                        <option value="XII TKJ A">XII TKJ A</option>
                        <option value="XII TKJ B">XII TKJ B</option>
                        <option value="XII DKV A">XII DKV A</option>
                    </select>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100">
                    <button type="button" @click="$dispatch('close-modal', 'tambah-siswa-modal')" class="simmas-btn-secondary">Batal</button>
                    <button type="submit" class="simmas-btn-primary">Simpan & Buat Akun</button>
                </div>
            </form>
        </x-modal>

        <!-- Modal Edit Siswa -->
        <x-modal name="edit-siswa-modal" title="Edit Data Siswa" subtitle="Perbarui nama lengkap dan kelas siswa" icon="pencil">
            <form method="POST" :action="'{{ url('/admin/siswa') }}/' + editSiswa.id" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">NIS (Read-only)</label>
                    <input type="text" :value="editSiswa.nis" readonly class="simmas-input font-mono">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Lengkap <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama" x-model="editSiswa.nama" required class="simmas-input">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Kelas <span class="text-rose-500">*</span></label>
                    <select name="kelas" x-model="editSiswa.kelas" required class="simmas-input">
                        <option value="XII RPL A">XII RPL A</option>
                        <option value="XII RPL B">XII RPL B</option>
                        <option value="XII TKJ A">XII TKJ A</option>
                        <option value="XII TKJ B">XII TKJ B</option>
                        <option value="XII DKV A">XII DKV A</option>
                    </select>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100">
                    <button type="button" @click="$dispatch('close-modal', 'edit-siswa-modal')" class="simmas-btn-secondary">Batal</button>
                    <button type="submit" class="simmas-btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </x-modal>

        <!-- Modal Plotting Guru Pembimbing & DUDI -->
        <x-modal name="plot-siswa-modal" title="Plotting Guru Pembimbing & DUDI" subtitle="Tempatkan siswa ke mitra industri terverifikasi yang kuotanya masih tersedia" icon="git-branch">
            <form method="POST" :action="'{{ url('/admin/siswa') }}/' + plotSiswa.id + '/plot'" class="space-y-4">
                @csrf
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3.5 text-xs">
                    <p class="font-bold text-slate-900" x-text="plotSiswa.nama"></p>
                    <p class="text-slate-500 font-mono mt-0.5">NIS: <span x-text="plotSiswa.nis"></span> • Kelas: <span x-text="plotSiswa.kelas"></span></p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Pilih Guru Pembimbing <span class="text-rose-500">*</span></label>
                    <select name="guru_id" x-model="plotSiswa.guru_id" required class="simmas-input">
                        <option value="">-- Pilih Guru Pembimbing --</option>
                        @foreach($activeGurus as $g)
                            <option value="{{ $g->id }}">{{ $g->profile?->nama }} (NIP: {{ $g->nip }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Pilih Tempat Magang (Mitra DUDI) <span class="text-rose-500">*</span></label>
                    <select name="tempat_magang_id" x-model="plotSiswa.tempat_magang_id" required class="simmas-input">
                        <option value="">-- Pilih DUDI Terverifikasi --</option>
                        @foreach($verifiedAvailableDudi as $d)
                            <option value="{{ $d->id }}">{{ $d->nama_perusahaan }} — Sisa Kuota: {{ $d->sisa_kuota }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-[11px] text-slate-400">Hanya menampilkan Mitra DUDI terverifikasi yang masih memiliki sisa kuota.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" value="{{ now()->toDateString() }}" class="simmas-input">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai" value="{{ now()->addMonths(3)->toDateString() }}" class="simmas-input">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100">
                    <button type="button" @click="$dispatch('close-modal', 'plot-siswa-modal')" class="simmas-btn-secondary">Batal</button>
                    <button type="submit" class="simmas-btn-primary">Simpan Plotting</button>
                </div>
            </form>
        </x-modal>

        <!-- Modal Ubah Status Siswa -->
        <x-modal name="status-siswa-modal" title="Ubah Status Akun Siswa" subtitle="Nonaktifkan akun bila siswa sudah tidak aktif" icon="power" iconColor="amber" maxWidth="md">
            <form method="POST" :action="'{{ url('/admin/siswa') }}/' + statusSiswa.id + '/status'" class="space-y-4">
                @csrf
                @method('PATCH')
                <p class="text-xs text-slate-600">Ubah status akses untuk <strong class="text-slate-900" x-text="statusSiswa.nama"></strong>:</p>
                <div class="space-y-2">
                    <label class="flex items-center gap-3 rounded-xl border border-slate-200 p-3.5 cursor-pointer hover:bg-slate-50">
                        <input type="radio" name="is_active" value="1" :checked="statusSiswa.is_active == 1" class="text-blue-600">
                        <div>
                            <p class="text-xs font-bold text-slate-900">Aktif</p>
                            <p class="text-[11px] text-slate-500">Siswa dapat login dan mengisi absensi serta jurnal</p>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 rounded-xl border border-slate-200 p-3.5 cursor-pointer hover:bg-slate-50">
                        <input type="radio" name="is_active" value="0" :checked="statusSiswa.is_active == 0" class="text-rose-600">
                        <div>
                            <p class="text-xs font-bold text-rose-700">Nonaktif</p>
                            <p class="text-[11px] text-slate-500">Siswa tidak dapat login ke portal</p>
                        </div>
                    </label>
                </div>
                <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100">
                    <button type="button" @click="$dispatch('close-modal', 'status-siswa-modal')" class="simmas-btn-secondary">Batal</button>
                    <button type="submit" class="simmas-btn-primary">Simpan Status</button>
                </div>
            </form>
        </x-modal>

        <!-- Alert Hapus Siswa -->
        <x-modal name="delete-siswa-modal" title="Konfirmasi Hapus Siswa" subtitle="Perhatian: Penghapusan bersifat cascade dan permanen" icon="alert-triangle" iconColor="rose" maxWidth="md">
            <div class="space-y-4">
                <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-xs text-rose-700 leading-relaxed">
                    Menghapus siswa <strong x-text="deleteSiswa.nama"></strong> (<span class="font-mono" x-text="deleteSiswa.nis"></span>) akan menghapus akun login beserta seluruh <strong>riwayat presensi, jurnal kegiatan harian, pengajuan, dan penempatan magang</strong> secara otomatis (cascade).
                </div>

                <form method="POST" :action="'{{ url('/admin/siswa') }}/' + deleteSiswa.id" class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
                    @csrf
                    @method('DELETE')
                    <button type="button" @click="$dispatch('close-modal', 'delete-siswa-modal')" class="simmas-btn-secondary">Batal</button>
                    <button type="submit" class="simmas-btn-danger">Ya, Hapus Permanen</button>
                </form>
            </div>
        </x-modal>

        <!-- Modal Kredensial Akun Siswa Berhasil Dibuat -->
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
                                <p class="text-[10px] font-bold uppercase text-slate-400">Email Akun Siswa</p>
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

