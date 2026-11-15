<x-layouts.app title="Siswa Bimbingan" subtitle="Daftar siswa dalam bimbingan Anda dan penilaian akhir pelaksanaan magang">

    <div x-data="{
        nilaiModal: { id: '', siswa_nama: '', siswa_nis: '', dudi_nama: '', jurnal_disetujui: 0, pct_kehadiran: 100, nilai_akhir: '' },
        openNilai(data) {
            this.nilaiModal = data;
            this.$dispatch('open-modal', 'penilaian-akhir-modal');
        }
    }" class="space-y-6">

        <div class="simmas-card overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Daftar Siswa Bimbingan & Penilaian</h3>
                    <p class="text-xs text-slate-500">Hanya menampilkan siswa yang ditempatkan di bawah bimbingan Anda</p>
                </div>
                <x-badge variant="blue">{{ $penempatans->count() }} Siswa</x-badge>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr>
                            <th class="simmas-table-th">Siswa & NIS</th>
                            <th class="simmas-table-th">Kelas</th>
                            <th class="simmas-table-th">Tempat Magang</th>
                            <th class="simmas-table-th">Status</th>
                            <th class="simmas-table-th text-center">Nilai Akhir</th>
                            <th class="simmas-table-th text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($penempatans as $p)
                            @php
                                $siswa = $p->siswa;
                                $totalAbsen = $siswa?->absensi?->count() ?? 0;
                                $hadirCount = $siswa?->absensi?->where('status', 'hadir')->count() ?? 0;
                                $pctHadir = $totalAbsen > 0 ? round(($hadirCount / $totalAbsen) * 100) : 100;
                                $jurnalValid = $siswa?->jurnal?->where('status_verifikasi', 'disetujui')->count() ?? 0;

                                $stVariant = match($p->status_pengesahan) {
                                    'disahkan' => 'emerald',
                                    'lulus_magang' => 'violet',
                                    default => 'amber',
                                };
                                $stLabel = match($p->status_pengesahan) {
                                    'disahkan' => 'Sedang Magang',
                                    'lulus_magang' => 'Lulus Magang',
                                    default => 'Belum Disahkan',
                                };
                            @endphp
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="simmas-table-td">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600 text-xs font-bold">
                                            {{ $siswa?->profile?->initials ?? 'SW' }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-900">{{ $siswa?->profile?->nama }}</p>
                                            <p class="font-mono text-xs text-slate-400">{{ $siswa?->nis }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="simmas-table-td">
                                    <x-badge variant="mono">{{ $siswa?->kelas }}</x-badge>
                                </td>
                                <td class="simmas-table-td">
                                    <p class="font-bold text-slate-800 text-xs">{{ $p->tempatMagang?->nama_perusahaan }}</p>
                                    <p class="text-xs text-slate-400 max-w-xs truncate">{{ $p->tempatMagang?->alamat }}</p>
                                </td>
                                <td class="simmas-table-td">
                                    <x-badge :variant="$stVariant" :dot="true">{{ $stLabel }}</x-badge>
                                </td>
                                <td class="simmas-table-td text-center">
                                    @if($p->nilai_akhir !== null)
                                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-emerald-50 border border-emerald-200 font-mono text-xs font-extrabold text-emerald-700">
                                            {{ round($p->nilai_akhir) }}
                                        </span>
                                    @else
                                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 border border-slate-200 font-mono text-xs font-semibold text-slate-400">
                                            -
                                        </span>
                                    @endif
                                </td>
                                <td class="simmas-table-td text-right">
                                    <button
                                        type="button"
                                        @click="openNilai(@js([
                                            'id' => $p->id,
                                            'siswa_nama' => $siswa?->profile?->nama,
                                            'siswa_nis' => $siswa?->nis,
                                            'dudi_nama' => $p->tempatMagang?->nama_perusahaan,
                                            'jurnal_disetujui' => $jurnalValid,
                                            'pct_kehadiran' => $pctHadir,
                                            'nilai_akhir' => $p->nilai_akhir ?? '',
                                        ]))"
                                        class="{{ $p->nilai_akhir !== null ? 'simmas-btn-secondary' : 'simmas-btn-primary' }} py-1.5 px-3 text-xs"
                                    >
                                        <i data-lucide="award" class="h-3.5 w-3.5"></i>
                                        <span>{{ $p->nilai_akhir !== null ? 'Revisi Nilai' : 'Beri Nilai' }}</span>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <x-empty-state title="Belum ada siswa bimbingan" description="Daftar siswa bimbingan Anda akan muncul setelah di-plotting oleh Administrator." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal Penilaian Akhir Magang -->
        <x-modal name="penilaian-akhir-modal" title="Penilaian Akhir Magang" subtitle="Berikan nilai evaluasi akhir pelaksanaan PKL siswa (rentang 0 - 100)" icon="award" maxWidth="md">
            <form method="POST" :action="'{{ url('/guru/siswa') }}/' + nilaiModal.id + '/nilai'" class="space-y-4">
                @csrf
                @method('PATCH')

                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 space-y-3 text-xs">
                    <div>
                        <p class="text-sm font-extrabold text-slate-900" x-text="nilaiModal.siswa_nama"></p>
                        <p class="font-mono text-slate-500">NIS: <span x-text="nilaiModal.siswa_nis"></span></p>
                    </div>

                    <div class="grid grid-cols-3 gap-2 pt-2.5 border-t border-slate-200/80">
                        <div>
                            <p class="text-[10px] font-bold uppercase text-slate-400">Tempat Magang</p>
                            <p class="font-bold text-slate-800 mt-0.5 truncate" x-text="nilaiModal.dudi_nama"></p>
                        </div>
                        <div class="text-center">
                            <p class="text-[10px] font-bold uppercase text-slate-400">Jurnal Disetujui</p>
                            <p class="font-extrabold text-blue-600 mt-0.5" x-text="nilaiModal.jurnal_disetujui + ' Jurnal'"></p>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] font-bold uppercase text-slate-400">Kehadiran</p>
                            <p class="font-extrabold text-emerald-600 mt-0.5" x-text="nilaiModal.pct_kehadiran + '%'"></p>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Input Nilai Akhir (0 - 100) <span class="text-rose-500">*</span></label>
                    <input
                        type="number"
                        name="nilai_akhir"
                        x-model="nilaiModal.nilai_akhir"
                        required
                        min="0"
                        max="100"
                        step="0.01"
                        placeholder="Contoh: 88"
                        class="simmas-input font-mono text-base font-bold"
                    >
                    <p class="mt-1 text-[11px] text-slate-400">Masukkan angka antara 0 hingga 100.</p>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100">
                    <button type="button" @click="$dispatch('close-modal', 'penilaian-akhir-modal')" class="simmas-btn-secondary">Batal</button>
                    <button type="submit" class="simmas-btn-primary">Simpan Nilai</button>
                </div>
            </form>
        </x-modal>

    </div>

</x-layouts.app>

