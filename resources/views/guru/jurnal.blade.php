<x-layouts.app title="Validasi Jurnal & Absensi" subtitle="Verifikasi laporan jurnal kegiatan harian serta surat keterangan sakit/izin siswa bimbingan">

    <div x-data="{
        activeTab: @js($tab === 'absensi' ? 'absensi' : 'jurnal'),
        jurnalModal: { id: '', siswa_nama: '', dudi_nama: '', tanggal: '', kegiatan: '', kendala: '', solusi: '', photo_url: '', status_verifikasi: 'disetujui', catatan_guru: '' },
        absensiModal: { id: '', siswa_nama: '', status_diajukan: '', tanggal: '', photo_url: '', status_validasi: 'disetujui', catatan_guru: '' },
        photoPreviewUrl: '',
        openJurnalModal(j) {
            this.jurnalModal = j;
            this.$dispatch('open-modal', 'validasi-jurnal-modal');
        },
        openAbsensiModal(a) {
            this.absensiModal = a;
            this.$dispatch('open-modal', 'validasi-absensi-modal');
        },
        openPhoto(url) {
            this.photoPreviewUrl = url;
            this.$dispatch('open-modal', 'photo-preview-modal');
        }
    }" class="space-y-6">

        <!-- 2 Tab Bar -->
        <div class="simmas-card p-2 inline-flex flex-wrap items-center gap-1.5 bg-slate-100/80 border-slate-200">
            <button
                type="button"
                @click="activeTab = 'jurnal'"
                :class="activeTab === 'jurnal' ? 'bg-white text-blue-600 shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-xs font-bold transition-all cursor-pointer"
            >
                <i data-lucide="book-open" class="h-4 w-4"></i>
                <span>Jurnal Kegiatan</span>
                @if($jurnalMenunggu > 0)
                    <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-extrabold text-amber-700">{{ $jurnalMenunggu }}</span>
                @endif
            </button>

            <button
                type="button"
                @click="activeTab = 'absensi'"
                :class="activeTab === 'absensi' ? 'bg-white text-blue-600 shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-xs font-bold transition-all cursor-pointer"
            >
                <i data-lucide="clock" class="h-4 w-4"></i>
                <span>Absensi Siswa</span>
                @if($absensiMenunggu > 0)
                    <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-extrabold text-amber-700">{{ $absensiMenunggu }}</span>
                @endif
            </button>
        </div>

        <!-- TAB 1: JURNAL KEGIATAN -->
        <div x-show="activeTab === 'jurnal'" class="space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <x-stat-card label="Menunggu Validasi" :value="$jurnalMenunggu" subtitle="Jurnal belum diperiksa" icon="clock" color="amber" />
                <x-stat-card label="Disetujui" :value="$jurnalDisetujui" subtitle="Jurnal telah disahkan" icon="check-circle-2" color="emerald" />
                <x-stat-card label="Perlu Revisi" :value="$jurnalRevisi" subtitle="Dikembalikan untuk diperbaiki" icon="edit-3" color="rose" />
            </div>

            <div class="simmas-card overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100">
                    <h3 class="text-base font-bold text-slate-900">Daftar Jurnal Kegiatan Siswa Bimbingan</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr>
                                <th class="simmas-table-th">Tanggal & Siswa</th>
                                <th class="simmas-table-th">Kegiatan</th>
                                <th class="simmas-table-th text-center">Foto</th>
                                <th class="simmas-table-th">Status</th>
                                <th class="simmas-table-th text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($jurnals as $j)
                                @php
                                    $jVariant = match($j->status_verifikasi) {
                                        'disetujui' => 'emerald',
                                        'revisi' => 'rose',
                                        default => 'amber',
                                    };
                                    $jLabel = match($j->status_verifikasi) {
                                        'disetujui' => 'Disetujui',
                                        'revisi' => 'Perlu Revisi',
                                        default => 'Menunggu',
                                    };
                                @endphp
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <td class="simmas-table-td">
                                        <p class="font-mono text-xs font-semibold text-blue-600">{{ $j->tanggal->format('d M Y') }}</p>
                                        <p class="font-bold text-slate-900 mt-0.5">{{ $j->siswa?->profile?->nama }}</p>
                                        <p class="text-[11px] text-slate-400">{{ $j->siswa?->penempatanAktif?->tempatMagang?->nama_perusahaan ?? '-' }}</p>
                                    </td>
                                    <td class="simmas-table-td max-w-md">
                                        <p class="text-xs text-slate-700 leading-relaxed">{{ $j->kegiatan }}</p>
                                        @if($j->kendala)
                                            <p class="text-[11px] text-slate-400 mt-1"><strong>Kendala:</strong> {{ $j->kendala }}</p>
                                        @endif
                                        @if($j->catatan_guru)
                                            <p class="text-[11px] text-blue-600 mt-1"><strong>Catatan Guru:</strong> {{ $j->catatan_guru }}</p>
                                        @endif
                                    </td>
                                    <td class="simmas-table-td text-center">
                                        @if($j->photo_url)
                                            <button type="button" @click="openPhoto(@js($j->photo_url))" class="simmas-btn-secondary py-1 px-2.5 text-xs">
                                                <i data-lucide="image" class="h-3.5 w-3.5"></i>
                                                <span>Lihat</span>
                                            </button>
                                        @else
                                            <span class="text-xs text-slate-400">Tidak ada</span>
                                        @endif
                                    </td>
                                    <td class="simmas-table-td">
                                        <x-badge :variant="$jVariant" :dot="true">{{ $jLabel }}</x-badge>
                                    </td>
                                    <td class="simmas-table-td text-right">
                                        <button
                                            type="button"
                                            @click="openJurnalModal(@js([
                                                'id' => $j->id,
                                                'siswa_nama' => $j->siswa?->profile?->nama,
                                                'dudi_nama' => $j->siswa?->penempatanAktif?->tempatMagang?->nama_perusahaan ?? '-',
                                                'tanggal' => $j->tanggal->format('d M Y'),
                                                'kegiatan' => $j->kegiatan,
                                                'kendala' => $j->kendala ?? 'Tidak ada kendala',
                                                'solusi' => $j->solusi ?? '-',
                                                'photo_url' => $j->photo_url,
                                                'status_verifikasi' => $j->status_verifikasi === 'revisi' ? 'revisi' : 'disetujui',
                                                'catatan_guru' => $j->catatan_guru ?? '',
                                            ]))"
                                            class="{{ $j->status_verifikasi === 'menunggu' ? 'simmas-btn-primary' : 'simmas-btn-secondary' }} py-1.5 px-3 text-xs"
                                        >
                                            <span>{{ $j->status_verifikasi === 'menunggu' ? 'Validasi' : 'Lihat Detail' }}</span>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">
                                        <x-empty-state title="Belum ada jurnal kegiatan" description="Laporan jurnal dari siswa bimbingan akan tampil di sini." />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TAB 2: ABSENSI SISWA -->
        <div x-show="activeTab === 'absensi'" x-cloak class="space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <x-stat-card label="Menunggu Validasi" :value="$absensiMenunggu" subtitle="Keterangan Sakit/Izin baru" icon="clock" color="amber" />
                <x-stat-card label="Disetujui" :value="$absensiDisetujui" subtitle="Presensi sah tercatat" icon="check-circle-2" color="emerald" />
                <x-stat-card label="Ditolak" :value="$absensiDitolak" subtitle="Presensi tidak disetujui" icon="x-circle" color="rose" />
            </div>

            <div class="simmas-card overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100">
                    <h3 class="text-base font-bold text-slate-900">Daftar Presensi & Pengajuan Sakit/Izin Siswa</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr>
                                <th class="simmas-table-th">Tanggal & Siswa</th>
                                <th class="simmas-table-th">Status Kehadiran</th>
                                <th class="simmas-table-th text-center">Foto Bukti</th>
                                <th class="simmas-table-th">Status Validasi</th>
                                <th class="simmas-table-th text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($absensis as $ab)
                                @php
                                    $abColor = match($ab->status) {
                                        'hadir' => 'emerald',
                                        'sakit' => 'amber',
                                        'izin' => 'blue',
                                        default => 'rose',
                                    };
                                    $valColor = match($ab->status_validasi) {
                                        'disetujui' => 'emerald',
                                        'ditolak' => 'rose',
                                        default => 'amber',
                                    };
                                @endphp
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <td class="simmas-table-td">
                                        <p class="font-mono text-xs font-semibold text-blue-600">{{ $ab->tanggal->format('d M Y') }}</p>
                                        <p class="font-bold text-slate-900 mt-0.5">{{ $ab->siswa?->profile?->nama }}</p>
                                        <p class="text-[11px] text-slate-400">Jam Masuk: {{ $ab->jam_masuk ?? '-' }} • Pulang: {{ $ab->jam_pulang ?? '-' }}</p>
                                    </td>
                                    <td class="simmas-table-td">
                                        <x-badge :variant="$abColor">{{ ucfirst($ab->status) }}</x-badge>
                                        @if($ab->keterangan)
                                            <p class="text-xs text-slate-500 mt-1">{{ $ab->keterangan }}</p>
                                        @endif
                                    </td>
                                    <td class="simmas-table-td text-center">
                                        @if($ab->photo_masuk_url)
                                            <button type="button" @click="openPhoto(@js($ab->photo_masuk_url))" class="simmas-btn-secondary py-1 px-2.5 text-xs">
                                                <i data-lucide="camera" class="h-3.5 w-3.5"></i>
                                                <span>Bukti</span>
                                            </button>
                                        @else
                                            <span class="text-xs text-slate-400">-</span>
                                        @endif
                                    </td>
                                    <td class="simmas-table-td">
                                        <x-badge :variant="$valColor" :dot="true">{{ ucfirst($ab->status_validasi) }}</x-badge>
                                        @if($ab->catatan_guru)
                                            <p class="text-[11px] text-slate-500 mt-1">Catatan: {{ $ab->catatan_guru }}</p>
                                        @endif
                                    </td>
                                    <td class="simmas-table-td text-right">
                                        @if(in_array($ab->status, ['sakit', 'izin']) || $ab->status_validasi === 'menunggu')
                                            <button
                                                type="button"
                                                @click="openAbsensiModal(@js([
                                                    'id' => $ab->id,
                                                    'siswa_nama' => $ab->siswa?->profile?->nama,
                                                    'status_diajukan' => ucfirst($ab->status) . ($ab->keterangan ? ' (' . $ab->keterangan . ')' : ''),
                                                    'tanggal' => $ab->tanggal->format('d M Y'),
                                                    'photo_url' => $ab->photo_masuk_url,
                                                    'status_validasi' => $ab->status_validasi === 'ditolak' ? 'ditolak' : 'disetujui',
                                                    'catatan_guru' => $ab->catatan_guru ?? '',
                                                ]))"
                                                class="simmas-btn-primary py-1.5 px-3 text-xs"
                                            >
                                                Validasi Presensi
                                            </button>
                                        @else
                                            <span class="text-xs text-slate-400">Otomatis Valid</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">
                                        <x-empty-state title="Belum ada data absensi" description="Riwayat presensi siswa bimbingan akan tampil di sini." />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal Validasi Jurnal -->
        <x-modal name="validasi-jurnal-modal" title="Validasi Jurnal Kegiatan" subtitle="Periksa rincian kegiatan harian dan berikan evaluasi kepada siswa" icon="check-square" maxWidth="lg">
            <form method="POST" :action="'{{ url('/guru/jurnal') }}/' + jurnalModal.id + '/validasi'" class="space-y-4">
                @csrf
                @method('PATCH')

                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 space-y-2.5 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="font-extrabold text-slate-900 text-sm" x-text="jurnalModal.siswa_nama"></span>
                        <span class="font-mono text-blue-600 font-semibold" x-text="jurnalModal.tanggal"></span>
                    </div>
                    <p class="text-slate-500">Tempat Magang: <strong class="text-slate-800" x-text="jurnalModal.dudi_nama"></strong></p>

                    <div class="pt-2 border-t border-slate-200 space-y-1.5">
                        <p class="text-[10px] font-bold uppercase text-slate-400">Rincian Kegiatan</p>
                        <p class="text-slate-800 leading-relaxed" x-text="jurnalModal.kegiatan"></p>
                    </div>
                    <div class="pt-2 border-t border-slate-200 space-y-1">
                        <p class="text-[10px] font-bold uppercase text-slate-400">Kendala</p>
                        <p class="text-slate-600" x-text="jurnalModal.kendala"></p>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-2">Keputusan Evaluasi <span class="text-rose-500">*</span></label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="flex items-center gap-2.5 rounded-xl border border-slate-200 p-3.5 cursor-pointer hover:bg-emerald-50/50">
                            <input type="radio" name="status_verifikasi" value="disetujui" x-model="jurnalModal.status_verifikasi" class="text-emerald-600">
                            <span class="text-xs font-bold text-emerald-700">Setujui Jurnal</span>
                        </label>
                        <label class="flex items-center gap-2.5 rounded-xl border border-slate-200 p-3.5 cursor-pointer hover:bg-rose-50/50">
                            <input type="radio" name="status_verifikasi" value="revisi" x-model="jurnalModal.status_verifikasi" class="text-rose-600">
                            <span class="text-xs font-bold text-rose-700">Minta Revisi</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Catatan untuk Siswa (Opsional)</label>
                    <textarea name="catatan_guru" rows="3" x-model="jurnalModal.catatan_guru" placeholder="Berikan masukan atau arahan perbaikan jurnal..." class="simmas-input"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100">
                    <button type="button" @click="$dispatch('close-modal', 'validasi-jurnal-modal')" class="simmas-btn-secondary">Batal</button>
                    <button type="submit" class="simmas-btn-primary">Simpan Validasi</button>
                </div>
            </form>
        </x-modal>

        <!-- Modal Validasi Presensi (Sakit/Izin) -->
        <x-modal name="validasi-absensi-modal" title="Validasi Presensi Siswa" subtitle="Konfirmasi pengajuan keterangan Sakit atau Izin siswa" icon="clock" maxWidth="md">
            <form method="POST" :action="'{{ url('/guru/absensi') }}/' + absensiModal.id + '/validasi'" class="space-y-4">
                @csrf
                @method('PATCH')

                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 space-y-2 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-slate-900" x-text="absensiModal.siswa_nama"></span>
                        <span class="font-mono text-slate-500" x-text="absensiModal.tanggal"></span>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold uppercase text-slate-400">Status Diajukan</p>
                        <p class="font-bold text-amber-700 mt-0.5" x-text="absensiModal.status_diajukan"></p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <label class="flex items-center gap-2.5 rounded-xl border border-slate-200 p-3.5 cursor-pointer hover:bg-emerald-50/50">
                        <input type="radio" name="status_validasi" value="disetujui" x-model="absensiModal.status_validasi" class="text-emerald-600">
                        <span class="text-xs font-bold text-emerald-700">Setujui Absensi</span>
                    </label>
                    <label class="flex items-center gap-2.5 rounded-xl border border-slate-200 p-3.5 cursor-pointer hover:bg-rose-50/50">
                        <input type="radio" name="status_validasi" value="ditolak" x-model="absensiModal.status_validasi" class="text-rose-600">
                        <span class="text-xs font-bold text-rose-700">Tolak Presensi</span>
                    </label>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Catatan Pembimbing (Opsional)</label>
                    <textarea name="catatan_guru" rows="2" x-model="absensiModal.catatan_guru" placeholder="Catatan terkait surat izin/sakit..." class="simmas-input"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100">
                    <button type="button" @click="$dispatch('close-modal', 'validasi-absensi-modal')" class="simmas-btn-secondary">Batal</button>
                    <button type="submit" class="simmas-btn-primary">Simpan Keputusan</button>
                </div>
            </form>
        </x-modal>

        <!-- Modal Foto Bukti -->
        <x-modal name="photo-preview-modal" title="Dokumentasi Bukti Foto" icon="image" maxWidth="md">
            <div class="space-y-4 text-center">
                <img :src="photoPreviewUrl" alt="Bukti Foto" class="mx-auto max-h-96 rounded-xl border border-slate-200 object-contain">
                <button type="button" @click="$dispatch('close-modal', 'photo-preview-modal')" class="simmas-btn-primary w-full">Tutup</button>
            </div>
        </x-modal>

    </div>

</x-layouts.app>

