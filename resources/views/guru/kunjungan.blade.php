<x-layouts.app title="Kunjungan Lapangan" subtitle="Catatan monitoring dan dokumentasi kunjungan ke perusahaan mitra DUDI">

    <div x-data="{
        editKunjungan: { id: '', tempat_magang_id: '', tanggal: '', catatan: '' },
        deleteKunjungan: { id: '', dudi_nama: '', tanggal: '' },
        photoPreviewUrl: '',
        openEdit(k) {
            this.editKunjungan = k;
            this.$dispatch('open-modal', 'edit-kunjungan-modal');
        },
        openDelete(k) {
            this.deleteKunjungan = k;
            this.$dispatch('open-modal', 'delete-kunjungan-modal');
        },
        openPhoto(url) {
            this.photoPreviewUrl = url;
            this.$dispatch('open-modal', 'photo-kunjungan-modal');
        }
    }" class="space-y-6">

        <!-- Header Action + 3 Stat Cards -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-extrabold text-slate-900">Riwayat Kunjungan Industri</h2>
                <p class="text-xs text-slate-500">Catat hasil koordinasi dan evaluasi lapangan bersama PIC mitra DUDI</p>
            </div>
            <button
                type="button"
                @click="$dispatch('open-modal', 'tambah-kunjungan-modal')"
                class="simmas-btn-primary shrink-0"
            >
                <i data-lucide="plus" class="h-4 w-4"></i>
                <span>+ Tambah Kunjungan</span>
            </button>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            <x-stat-card label="Total Kunjungan" :value="$totalKunjungan" subtitle="Seluruh riwayat kunjungan" icon="map-pin" color="blue" />
            <x-stat-card label="Bulan Ini" :value="$bulanIni" subtitle="Kunjungan terlaksana bulan ini" icon="calendar" color="emerald" />
            <x-stat-card label="DUDI Dikunjungi" :value="$dudiDikunjungi" subtitle="Mitra industri dimonitoring" icon="building-2" color="violet" />
        </div>

        <!-- Timeline Vertikal Kronologis -->
        <div class="simmas-card p-6 sm:p-8">
            <h3 class="text-base font-bold text-slate-900 mb-6">Timeline Monitoring Lapangan</h3>

            @if($kunjungans->isNotEmpty())
                <div class="relative border-l-2 border-blue-200 ml-4 space-y-8">
                    @foreach($kunjungans as $k)
                        <div class="relative pl-7">
                            <!-- Titik Indikator -->
                            <div class="absolute -left-[11px] top-1 flex h-5 w-5 items-center justify-center rounded-full bg-blue-600 text-white ring-4 ring-blue-50">
                                <span class="h-2 w-2 rounded-full bg-white"></span>
                            </div>

                            <div class="rounded-2xl border border-slate-200 bg-slate-50/60 p-5 hover:border-blue-200 transition-all">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-slate-200/70">
                                    <div>
                                        <div class="flex items-center gap-2.5">
                                            <h4 class="text-sm font-extrabold text-slate-900">{{ $k->tempatMagang?->nama_perusahaan }}</h4>
                                            <span class="rounded-lg bg-blue-50 px-2.5 py-0.5 font-mono text-xs font-semibold text-blue-700">
                                                {{ $k->tanggal->format('d M Y') }}
                                            </span>
                                        </div>
                                        <p class="text-xs text-slate-400 mt-0.5">{{ $k->tempatMagang?->alamat }}</p>
                                    </div>

                                    <div class="flex items-center gap-1.5 shrink-0">
                                        @if($k->photo_url)
                                            <button
                                                type="button"
                                                @click="openPhoto(@js($k->photo_url))"
                                                class="simmas-btn-secondary py-1.5 px-3 text-xs"
                                            >
                                                <i data-lucide="camera" class="h-3.5 w-3.5 text-blue-600"></i>
                                                <span>Dokumentasi Kunjungan</span>
                                            </button>
                                        @endif
                                        <button
                                            type="button"
                                            title="Edit Catatan"
                                            @click="openEdit(@js([
                                                'id' => $k->id,
                                                'tempat_magang_id' => $k->tempat_magang_id,
                                                'tanggal' => $k->tanggal->format('Y-m-d'),
                                                'catatan' => $k->catatan,
                                            ]))"
                                            class="rounded-lg border border-slate-200 bg-white p-1.5 text-slate-600 hover:bg-slate-100 cursor-pointer"
                                        >
                                            <i data-lucide="pencil" class="h-3.5 w-3.5"></i>
                                        </button>
                                        <button
                                            type="button"
                                            title="Hapus Catatan"
                                            @click="openDelete(@js([
                                                'id' => $k->id,
                                                'dudi_nama' => $k->tempatMagang?->nama_perusahaan,
                                                'tanggal' => $k->tanggal->format('d M Y'),
                                            ]))"
                                            class="rounded-lg border border-slate-200 bg-white p-1.5 text-rose-600 hover:bg-rose-50 cursor-pointer"
                                        >
                                            <i data-lucide="trash-2" class="h-3.5 w-3.5"></i>
                                        </button>
                                    </div>
                                </div>

                                <p class="mt-3 text-xs sm:text-sm text-slate-700 leading-relaxed">{{ $k->catatan }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <x-empty-state icon="map-pin" title="Belum ada catatan kunjungan" description="Klik tombol '+ Tambah Kunjungan' untuk mencatat hasil kunjungan monitoring industri Anda." />
            @endif
        </div>

        <!-- Modal Catat Kunjungan Industri -->
        <x-modal name="tambah-kunjungan-modal" title="Catat Kunjungan Industri" subtitle="Dokumentasikan hasil evaluasi dan monitoring siswa di lokasi mitra DUDI" icon="map-pin">
            <form method="POST" action="{{ route('guru.kunjungan.store') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Pilih Mitra DUDI <span class="text-rose-500">*</span></label>
                    <select name="tempat_magang_id" required class="simmas-input">
                        <option value="">-- Pilih Perusahaan Mitra --</option>
                        @foreach($dudiList as $d)
                            <option value="{{ $d->id }}">{{ $d->nama_perusahaan }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Tanggal Kunjungan <span class="text-rose-500">*</span></label>
                    <input type="date" name="tanggal" required max="{{ now()->toDateString() }}" value="{{ now()->toDateString() }}" class="simmas-input">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Catatan Evaluasi / Hasil Monitoring <span class="text-rose-500">*</span></label>
                    <textarea name="catatan" rows="4" required minlength="10" placeholder="Tuliskan hasil koordinasi dengan PIC industri dan perkembangan siswa..." class="simmas-input"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Upload Foto Dokumentasi (Opsional)</label>
                    <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" class="simmas-input text-xs">
                    <p class="mt-1 text-[11px] text-slate-400">Format: JPG, PNG, WEBP. Maksimal 2 MB.</p>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100">
                    <button type="button" @click="$dispatch('close-modal', 'tambah-kunjungan-modal')" class="simmas-btn-secondary">Batal</button>
                    <button type="submit" class="simmas-btn-primary">Simpan Kunjungan</button>
                </div>
            </form>
        </x-modal>

        <!-- Modal Edit Catatan Kunjungan -->
        <x-modal name="edit-kunjungan-modal" title="Edit Catatan Kunjungan" subtitle="Perbarui rincian evaluasi atau unggah ulang foto dokumentasi" icon="pencil">
            <form method="POST" :action="'{{ url('/guru/kunjungan') }}/' + editKunjungan.id" enctype="multipart/form-data" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Mitra DUDI <span class="text-rose-500">*</span></label>
                    <select name="tempat_magang_id" x-model="editKunjungan.tempat_magang_id" required class="simmas-input">
                        @foreach($dudiList as $d)
                            <option value="{{ $d->id }}">{{ $d->nama_perusahaan }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Tanggal Kunjungan <span class="text-rose-500">*</span></label>
                    <input type="date" name="tanggal" x-model="editKunjungan.tanggal" required max="{{ now()->toDateString() }}" class="simmas-input">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Catatan Evaluasi <span class="text-rose-500">*</span></label>
                    <textarea name="catatan" rows="4" x-model="editKunjungan.catatan" required minlength="10" class="simmas-input"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Ganti Foto Dokumentasi (Opsional)</label>
                    <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" class="simmas-input text-xs">
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100">
                    <button type="button" @click="$dispatch('close-modal', 'edit-kunjungan-modal')" class="simmas-btn-secondary">Batal</button>
                    <button type="submit" class="simmas-btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </x-modal>

        <!-- Alert Hapus Catatan Kunjungan -->
        <x-modal name="delete-kunjungan-modal" title="Hapus Catatan Kunjungan" subtitle="Konfirmasi penghapusan catatan monitoring lapangan" icon="trash-2" iconColor="rose" maxWidth="md">
            <div class="space-y-4">
                <p class="text-xs text-slate-600 leading-relaxed">
                    Apakah Anda yakin ingin menghapus catatan kunjungan ke <strong class="text-slate-900" x-text="deleteKunjungan.dudi_nama"></strong> pada tanggal <span class="font-mono" x-text="deleteKunjungan.tanggal"></span>?
                </p>

                <form method="POST" :action="'{{ url('/guru/kunjungan') }}/' + deleteKunjungan.id" class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
                    @csrf
                    @method('DELETE')
                    <button type="button" @click="$dispatch('close-modal', 'delete-kunjungan-modal')" class="simmas-btn-secondary">Batal</button>
                    <button type="submit" class="simmas-btn-danger">Ya, Hapus Catatan</button>
                </form>
            </div>
        </x-modal>

        <!-- Modal Dokumentasi Kunjungan -->
        <x-modal name="photo-kunjungan-modal" title="Dokumentasi Kunjungan Lapangan" icon="camera" maxWidth="md">
            <div class="space-y-4 text-center">
                <img :src="photoPreviewUrl" alt="Foto Kunjungan" class="mx-auto max-h-96 rounded-xl border border-slate-200 object-contain">
                <button type="button" @click="$dispatch('close-modal', 'photo-kunjungan-modal')" class="simmas-btn-primary w-full">Tutup</button>
            </div>
        </x-modal>

    </div>

</x-layouts.app>

