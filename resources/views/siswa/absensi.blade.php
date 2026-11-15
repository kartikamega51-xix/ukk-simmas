<x-layouts.app title="Absensi Harian" subtitle="Catat kehadiran harian (Clock In & Clock Out) beserta bukti foto lokasi atau surat izin">

    @php
        $clockInDone = $absensiHariIni !== null;
        $clockOutDone = $absensiHariIni && $absensiHariIni->jam_pulang !== null;
    @endphp

    <div x-data="{
        statusPilihan: 'hadir',
        photoPreviewUrl: '',
        photoTitle: 'Foto Bukti Presensi',
        openPhoto(url, title) {
            this.photoPreviewUrl = url;
            this.photoTitle = title || 'Foto Bukti Presensi';
            this.$dispatch('open-modal', 'preview-foto-absensi-modal');
        }
    }" class="space-y-6">

        <!-- Kartu Status Kehadiran Hari Ini -->
        <div class="simmas-card p-6 sm:p-8">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                <div class="space-y-2">
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-bold uppercase tracking-wider text-blue-600">STATUS KEHADIRAN HARI INI</span>
                        <span class="font-mono text-xs text-slate-400">• {{ now()->format('d M Y') }}</span>
                    </div>

                    @if($absensiHariIni)
                        <div class="flex items-center gap-3">
                            <h2 class="text-2xl font-extrabold text-slate-900">
                                Tercatat: {{ ucfirst($absensiHariIni->status) }}
                            </h2>
                            @php
                                $vColor = match($absensiHariIni->status_validasi) {
                                    'disetujui' => 'emerald',
                                    'ditolak' => 'rose',
                                    default => 'amber',
                                };
                            @endphp
                            <x-badge :variant="$vColor" :dot="true">{{ ucfirst($absensiHariIni->status_validasi) }}</x-badge>
                        </div>
                        <p class="text-xs text-slate-500">
                            Jam Masuk: <strong class="font-mono text-slate-800">{{ $absensiHariIni->jam_masuk ?? '-' }}</strong>
                            • Jam Pulang: <strong class="font-mono text-slate-800">{{ $absensiHariIni->jam_pulang ?? 'Belum Clock Out' }}</strong>
                        </p>
                    @else
                        <h2 class="text-2xl font-extrabold text-slate-900">Belum Melakukan Presensi</h2>
                        <p class="text-xs text-slate-500">
                            Silakan klik tombol <strong>Clock In</strong> untuk mencatat kehadiran masuk atau mengajukan keterangan Sakit/Izin.
                        </p>
                    @endif
                </div>

                <!-- Tombol Clock In dan Clock Out -->
                <div class="flex flex-wrap items-center gap-3 shrink-0">
                    <button
                        type="button"
                        @click="$dispatch('open-modal', 'form-presensi-modal')"
                        @disabled($clockInDone)
                        class="{{ $clockInDone ? 'bg-slate-100 text-slate-400 border border-slate-200 cursor-not-allowed' : 'simmas-btn-primary' }} inline-flex items-center gap-2 rounded-xl px-5 py-3 text-sm font-bold transition-all"
                    >
                        <i data-lucide="log-in" class="h-4 w-4"></i>
                        <span>{{ $clockInDone ? 'Sudah Clock In' : 'Clock In' }}</span>
                    </button>

                    <!-- Clock Out nonaktif sebelum Clock In -->
                    <button
                        type="button"
                        @click="$dispatch('open-modal', 'form-clockout-modal')"
                        @disabled(!$clockInDone || $clockOutDone || $absensiHariIni?->status !== 'hadir')
                        class="{{ (!$clockInDone || $clockOutDone || $absensiHariIni?->status !== 'hadir') ? 'bg-slate-100 text-slate-400 border border-slate-200 cursor-not-allowed' : 'bg-emerald-600 hover:bg-emerald-700 text-white cursor-pointer shadow-sm' }} inline-flex items-center gap-2 rounded-xl px-5 py-3 text-sm font-bold transition-all"
                    >
                        <i data-lucide="log-out" class="h-4 w-4"></i>
                        <span>{{ $clockOutDone ? 'Sudah Clock Out' : 'Clock Out' }}</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Tabel Riwayat Bulan Ini -->
        <div class="simmas-card overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Riwayat Bulan Ini</h3>
                    <p class="text-xs text-slate-500">Daftar kehadiran, jam presensi, dan status validasi guru pembimbing</p>
                </div>
                <x-badge variant="blue">{{ $riwayatBulanIni->count() }} Hari Tercatat</x-badge>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr>
                            <th class="simmas-table-th">Tanggal</th>
                            <th class="simmas-table-th">Status</th>
                            <th class="simmas-table-th">Masuk</th>
                            <th class="simmas-table-th">Pulang</th>
                            <th class="simmas-table-th text-center">Foto</th>
                            <th class="simmas-table-th">Status Validasi Guru</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($riwayatBulanIni as $ab)
                            @php
                                $stVar = match($ab->status) {
                                    'hadir' => 'emerald',
                                    'sakit' => 'amber',
                                    'izin' => 'blue',
                                    default => 'rose',
                                };
                                $valVar = match($ab->status_validasi) {
                                    'disetujui' => 'emerald',
                                    'ditolak' => 'rose',
                                    default => 'amber',
                                };
                            @endphp
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="simmas-table-td font-mono text-xs font-semibold text-slate-800">
                                    {{ $ab->tanggal->format('d M Y') }}
                                </td>
                                <td class="simmas-table-td">
                                    <x-badge :variant="$stVar">{{ ucfirst($ab->status) }}</x-badge>
                                    @if($ab->keterangan)
                                        <p class="text-[11px] text-slate-400 mt-1 max-w-xs truncate">{{ $ab->keterangan }}</p>
                                    @endif
                                </td>
                                <td class="simmas-table-td font-mono text-xs text-slate-700">
                                    {{ $ab->jam_masuk ?? '-' }}
                                </td>
                                <td class="simmas-table-td font-mono text-xs text-slate-700">
                                    {{ $ab->jam_pulang ?? '-' }}
                                </td>
                                <td class="simmas-table-td text-center">
                                    <div class="inline-flex items-center gap-1.5">
                                        @if($ab->photo_masuk_url)
                                            <button
                                                type="button"
                                                @click="openPhoto(@js($ab->photo_masuk_url), 'Foto Masuk (' . @js($ab->tanggal->format('d M Y')) . ')')"
                                                class="simmas-btn-secondary py-1 px-2.5 text-xs"
                                            >
                                                <i data-lucide="camera" class="h-3.5 w-3.5 text-blue-600"></i>
                                                <span>Foto Masuk</span>
                                            </button>
                                        @endif
                                        @if($ab->photo_pulang_url)
                                            <button
                                                type="button"
                                                @click="openPhoto(@js($ab->photo_pulang_url), 'Foto Pulang (' . @js($ab->tanggal->format('d M Y')) . ')')"
                                                class="simmas-btn-secondary py-1 px-2.5 text-xs"
                                            >
                                                <i data-lucide="camera" class="h-3.5 w-3.5 text-emerald-600"></i>
                                                <span>Foto Pulang</span>
                                            </button>
                                        @endif
                                        @if(!$ab->photo_masuk_url && !$ab->photo_pulang_url)
                                            <span class="text-xs text-slate-400">-</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="simmas-table-td">
                                    <x-badge :variant="$valVar" :dot="true">{{ ucfirst($ab->status_validasi) }}</x-badge>
                                    @if($ab->catatan_guru)
                                        <p class="text-[11px] text-slate-500 mt-1">Catatan: {{ $ab->catatan_guru }}</p>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <x-empty-state icon="clock" title="Belum ada riwayat presensi" description="Lakukan Clock In harian untuk mencatat kehadiran magang Anda." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal Form Presensi Harian (Clock In) -->
        <x-modal name="form-presensi-modal" title="Form Presensi Harian" subtitle="Pilih status kehadiran dan unggah foto bukti lokasi atau surat keterangan" icon="camera">
            <form method="POST" action="{{ route('siswa.absensi.store') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf

                <!-- Segmented Button: Hadir / Sakit / Izin -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Pilihan Status Kehadiran <span class="text-rose-500">*</span></label>
                    <input type="hidden" name="status" :value="statusPilihan">
                    <div class="grid grid-cols-3 gap-2 rounded-xl bg-slate-100 p-1.5 border border-slate-200">
                        <button
                            type="button"
                            @click="statusPilihan = 'hadir'"
                            :class="statusPilihan === 'hadir' ? 'bg-emerald-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                            class="rounded-lg py-2 text-xs font-bold transition-all cursor-pointer"
                        >
                            Hadir
                        </button>
                        <button
                            type="button"
                            @click="statusPilihan = 'sakit'"
                            :class="statusPilihan === 'sakit' ? 'bg-amber-500 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                            class="rounded-lg py-2 text-xs font-bold transition-all cursor-pointer"
                        >
                            Sakit
                        </button>
                        <button
                            type="button"
                            @click="statusPilihan = 'izin'"
                            :class="statusPilihan === 'izin' ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                            class="rounded-lg py-2 text-xs font-bold transition-all cursor-pointer"
                        >
                            Izin
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Tanggal Presensi <span class="text-rose-500">*</span></label>
                        <input type="date" name="tanggal" required max="{{ now()->toDateString() }}" value="{{ now()->toDateString() }}" class="simmas-input font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Jam Presensi</label>
                        <input type="time" name="jam_masuk" value="{{ now()->format('H:i') }}" class="simmas-input font-mono">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        <span x-text="statusPilihan === 'hadir' ? 'Upload Foto Bukti Kehadiran (Selfie Lokasi)' : 'Upload Foto Surat Dokter / Bukti Izin'"></span>
                        <span x-show="statusPilihan === 'hadir'" class="text-rose-500">*</span>
                    </label>
                    <input
                        type="file"
                        name="photo_masuk"
                        accept="image/jpeg,image/png,image/webp"
                        capture="environment"
                        :required="statusPilihan === 'hadir'"
                        class="simmas-input text-xs"
                    >
                    <p class="mt-1 text-[11px] text-slate-400">Wajib untuk status Hadir. Format: JPG/PNG/WEBP, maksimal 2 MB.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Keterangan Tambahan (Opsional)</label>
                    <textarea name="keterangan" rows="2" placeholder="Contoh: Hadir di divisi engineering / Surat keterangan dokter terlampir..." class="simmas-input"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100">
                    <button type="button" @click="$dispatch('close-modal', 'form-presensi-modal')" class="simmas-btn-secondary">Batal</button>
                    <button type="submit" class="simmas-btn-primary">Simpan Presensi</button>
                </div>
            </form>
        </x-modal>

        <!-- Modal Clock Out -->
        <x-modal name="form-clockout-modal" title="Konfirmasi Clock Out (Presensi Pulang)" subtitle="Catat waktu selesai kerja hari ini beserta foto bukti pulang opsional" icon="log-out" iconColor="emerald" maxWidth="md">
            <form method="POST" action="{{ route('siswa.absensi.clockout') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3.5 text-xs">
                    <p class="font-bold text-slate-800">Waktu Clock Out Saat Ini: <span class="font-mono text-emerald-600">{{ now()->format('H:i') }} WIB</span></p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Upload Foto Pulang (Opsional)</label>
                    <input type="file" name="photo_pulang" accept="image/jpeg,image/png,image/webp" capture="environment" class="simmas-input text-xs">
                    <p class="mt-1 text-[11px] text-slate-400">Format: JPG/PNG/WEBP, maksimal 2 MB.</p>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100">
                    <button type="button" @click="$dispatch('close-modal', 'form-clockout-modal')" class="simmas-btn-secondary">Batal</button>
                    <button type="submit" class="simmas-btn-primary">Simpan Clock Out</button>
                </div>
            </form>
        </x-modal>

        <!-- Modal Preview Foto -->
        <x-modal name="preview-foto-absensi-modal" title="Bukti Foto Presensi" icon="camera" maxWidth="md">
            <div class="space-y-4 text-center">
                <p class="text-xs font-bold text-slate-700" x-text="photoTitle"></p>
                <img :src="photoPreviewUrl" alt="Foto Presensi" class="mx-auto max-h-96 rounded-xl border border-slate-200 object-contain">
                <button type="button" @click="$dispatch('close-modal', 'preview-foto-absensi-modal')" class="simmas-btn-primary w-full">Tutup</button>
            </div>
        </x-modal>

    </div>

</x-layouts.app>

