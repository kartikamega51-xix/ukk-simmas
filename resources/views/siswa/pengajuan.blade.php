<x-layouts.app title="Pengajuan Magang" subtitle="Ajukan permohonan tempat magang mandiri dan pantau status verifikasi sekolah">

    @php
        $currentStatus = $latestPengajuan?->status ?? ($penempatanAktif ? 'disetujui' : 'belum');
        $step1Done = $currentStatus !== 'belum';
        $step2Done = in_array($currentStatus, ['menunggu', 'disetujui', 'ditolak']);
        $step3Done = $currentStatus === 'disetujui';
    @endphp

    <div class="space-y-6">

        <!-- Header + Stepper 3 Langkah -->
        <div class="simmas-card p-6 sm:p-8 space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-slate-100">
                <div>
                    <div class="flex items-center gap-2.5">
                        <h2 class="text-lg font-extrabold text-slate-900">Alur Pengajuan Tempat Magang</h2>
                        @if($currentStatus === 'disetujui')
                            <x-badge variant="emerald" :dot="true">Disetujui</x-badge>
                        @elseif($currentStatus === 'menunggu')
                            <x-badge variant="amber" :dot="true">Ditinjau Sekolah</x-badge>
                        @elseif($currentStatus === 'ditolak')
                            <x-badge variant="rose" :dot="true">Ditolak</x-badge>
                        @else
                            <x-badge variant="slate" :dot="true">Belum Mengajukan</x-badge>
                        @endif
                    </div>
                    <p class="text-xs text-slate-500 mt-1">Tahapan verifikasi pengajuan tempat magang siswa oleh pihak sekolah</p>
                </div>

                @if(!$hasActiveOrPending)
                    <button
                        type="button"
                        @click="$dispatch('open-modal', 'form-pengajuan-modal')"
                        class="simmas-btn-primary shrink-0"
                    >
                        <i data-lucide="send" class="h-4 w-4"></i>
                        <span>Ajukan Tempat Magang</span>
                    </button>
                @endif
            </div>

            <!-- Stepper 3 Langkah: Ajukan -> Ditinjau Sekolah -> Disetujui -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Step 1: Ajukan -->
                <div class="flex items-center gap-3.5 rounded-2xl border p-4 {{ $step1Done ? 'border-blue-200 bg-blue-50/60' : 'border-slate-200 bg-slate-50/50' }}">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl font-mono text-sm font-extrabold {{ $step1Done ? 'bg-blue-600 text-white' : 'bg-slate-200 text-slate-600' }}">
                        01
                    </div>
                    <div>
                        <p class="text-xs font-extrabold text-slate-900">Ajukan</p>
                        <p class="text-[11px] text-slate-500">Pilih mitra DUDI & posisi</p>
                    </div>
                </div>

                <!-- Step 2: Ditinjau Sekolah -->
                <div class="flex items-center gap-3.5 rounded-2xl border p-4 {{ $currentStatus === 'menunggu' ? 'border-amber-300 bg-amber-50/60' : ($step3Done ? 'border-blue-200 bg-blue-50/60' : 'border-slate-200 bg-slate-50/50') }}">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl font-mono text-sm font-extrabold {{ $currentStatus === 'menunggu' ? 'bg-amber-500 text-white' : ($step3Done ? 'bg-blue-600 text-white' : 'bg-slate-200 text-slate-600') }}">
                        02
                    </div>
                    <div>
                        <p class="text-xs font-extrabold text-slate-900">Ditinjau Sekolah</p>
                        <p class="text-[11px] text-slate-500">Verifikasi kuota & pembimbing</p>
                    </div>
                </div>

                <!-- Step 3: Disetujui -->
                <div class="flex items-center gap-3.5 rounded-2xl border p-4 {{ $step3Done ? 'border-emerald-200 bg-emerald-50/60' : ($currentStatus === 'ditolak' ? 'border-rose-200 bg-rose-50/60' : 'border-slate-200 bg-slate-50/50') }}">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl font-mono text-sm font-extrabold {{ $step3Done ? 'bg-emerald-600 text-white' : ($currentStatus === 'ditolak' ? 'bg-rose-600 text-white' : 'bg-slate-200 text-slate-600') }}">
                        03
                    </div>
                    <div>
                        <p class="text-xs font-extrabold text-slate-900">{{ $currentStatus === 'ditolak' ? 'Ditolak' : 'Disetujui' }}</p>
                        <p class="text-[11px] text-slate-500">Penempatan resmi aktif</p>
                    </div>
                </div>
            </div>
        </div>

        @if($latestPengajuan)
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <!-- Kartu Detail Pengajuan Magang -->
                <div class="lg:col-span-7 simmas-card p-6 space-y-4">
                    <h3 class="text-base font-bold text-slate-900 pb-3 border-b border-slate-100">Detail Pengajuan Magang</h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                            <p class="text-[10px] font-bold uppercase text-slate-400">Tempat Magang (Mitra DUDI)</p>
                            <p class="text-sm font-extrabold text-slate-900 mt-1">{{ $latestPengajuan->tempatMagang?->nama_perusahaan }}</p>
                            <p class="text-slate-500 mt-0.5">{{ $latestPengajuan->tempatMagang?->alamat }}</p>
                        </div>

                        <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                            <p class="text-[10px] font-bold uppercase text-slate-400">Posisi yang Diajukan</p>
                            <p class="text-sm font-extrabold text-blue-600 mt-1">{{ $latestPengajuan->posisi }}</p>
                            <p class="text-slate-500 mt-0.5">Bidang: {{ $latestPengajuan->tempatMagang?->bidang_usaha }}</p>
                        </div>

                        <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                            <p class="text-[10px] font-bold uppercase text-slate-400">Tanggal Pengajuan</p>
                            <p class="font-mono font-bold text-slate-800 mt-1">{{ $latestPengajuan->tanggal_pengajuan?->format('d M Y H:i') }}</p>
                        </div>

                        <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                            <p class="text-[10px] font-bold uppercase text-slate-400">Rencana Periode</p>
                            <p class="font-mono font-bold text-slate-800 mt-1">
                                {{ $latestPengajuan->tanggal_mulai?->format('d M Y') }} - {{ $latestPengajuan->tanggal_selesai?->format('d M Y') }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Kartu Status -->
                <div class="lg:col-span-5 simmas-card p-6 flex flex-col justify-between">
                    @if($latestPengajuan->status === 'disetujui')
                        <div class="space-y-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 ring-1 ring-emerald-100">
                                    <i data-lucide="check-circle-2" class="h-6 w-6"></i>
                                </div>
                                <div>
                                    <h3 class="text-base font-extrabold text-emerald-700">Pengajuan Magang Aktif</h3>
                                    <p class="text-xs text-slate-500">Permohonan Anda telah disetujui sekolah</p>
                                </div>
                            </div>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                Selamat! Pengajuan magang Anda di <strong>{{ $latestPengajuan->tempatMagang?->nama_perusahaan }}</strong> telah disetujui. Silakan laksanakan kegiatan magang dan isi absensi serta jurnal harian secara rutin.
                            </p>
                        </div>
                        <div class="pt-4">
                            <a href="{{ route('siswa.dashboard') }}" class="simmas-btn-primary w-full">Lihat Informasi Penempatan</a>
                        </div>
                    @elseif($latestPengajuan->status === 'ditolak')
                        <div class="space-y-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-rose-50 text-rose-600 ring-1 ring-rose-100">
                                    <i data-lucide="x-circle" class="h-6 w-6"></i>
                                </div>
                                <div>
                                    <h3 class="text-base font-extrabold text-rose-700">Pengajuan Ditolak</h3>
                                    <p class="text-xs text-slate-500">Silakan ajukan ke mitra DUDI atau posisi lain</p>
                                </div>
                            </div>
                            <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-xs text-rose-800">
                                <p class="font-bold uppercase text-[10px] text-rose-600">Catatan Alasan Penolakan:</p>
                                <p class="mt-1 leading-relaxed">{{ $latestPengajuan->catatan_penolakan ?? 'Tidak memenuhi kuota pada periode yang dipilih.' }}</p>
                            </div>
                        </div>
                        <div class="pt-4">
                            <button type="button" @click="$dispatch('open-modal', 'form-pengajuan-modal')" class="simmas-btn-primary w-full">
                                Ajukan Kembali
                            </button>
                        </div>
                    @else
                        <div class="space-y-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-50 text-amber-600 ring-1 ring-amber-100">
                                    <i data-lucide="clock" class="h-6 w-6"></i>
                                </div>
                                <div>
                                    <h3 class="text-base font-extrabold text-amber-700">Menunggu Tinjauan Sekolah</h3>
                                    <p class="text-xs text-slate-500">Sedang diverifikasi oleh Administrator Hubin</p>
                                </div>
                            </div>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                Pengajuan Anda telah masuk ke antrean verifikasi. Pihak sekolah akan memeriksa ketersediaan kuota dan menetapkan Guru Pembimbing untuk Anda.
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        @else
            <div class="simmas-card p-8">
                <x-empty-state
                    icon="send"
                    title="Belum Ada Pengajuan Magang"
                    description="Klik tombol 'Ajukan Tempat Magang' untuk memilih perusahaan mitra DUDI dan posisi yang Anda minati."
                >
                    @if(!$hasActiveOrPending)
                        <button type="button" @click="$dispatch('open-modal', 'form-pengajuan-modal')" class="simmas-btn-primary">
                            <i data-lucide="send" class="h-4 w-4"></i>
                            <span>Ajukan Tempat Magang</span>
                        </button>
                    @endif
                </x-empty-state>
            </div>
        @endif

        <!-- Modal Form Pengajuan Tempat Magang -->
        <x-modal name="form-pengajuan-modal" title="Form Pengajuan Tempat Magang" subtitle="Pilih perusahaan mitra DUDI terverifikasi yang kuotanya masih tersedia" icon="send">
            <form method="POST" action="{{ route('siswa.pengajuan.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Pilih Perusahaan Mitra (DUDI) <span class="text-rose-500">*</span></label>
                    <select name="tempat_magang_id" required class="simmas-input">
                        <option value="">-- Pilih Perusahaan Mitra --</option>
                        @foreach($availableDudis as $d)
                            <option value="{{ $d->id }}">
                                {{ $d->nama_perusahaan }} ({{ $d->bidang_usaha }}) — Sisa Kuota: {{ $d->sisa_kuota }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Posisi / Divisi yang Diminati <span class="text-rose-500">*</span></label>
                    <input type="text" name="posisi" required placeholder="Contoh: Fullstack Web Developer Intern" class="simmas-input">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Tanggal Mulai <span class="text-rose-500">*</span></label>
                        <input type="date" name="tanggal_mulai" required value="{{ now()->addDays(7)->toDateString() }}" class="simmas-input">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Tanggal Selesai <span class="text-rose-500">*</span></label>
                        <input type="date" name="tanggal_selesai" required value="{{ now()->addMonths(3)->addDays(7)->toDateString() }}" class="simmas-input">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100">
                    <button type="button" @click="$dispatch('close-modal', 'form-pengajuan-modal')" class="simmas-btn-secondary">Batal</button>
                    <button type="submit" class="simmas-btn-primary">
                        <i data-lucide="send" class="h-4 w-4"></i>
                        <span>Kirim Pengajuan</span>
                    </button>
                </div>
            </form>
        </x-modal>

    </div>

</x-layouts.app>

