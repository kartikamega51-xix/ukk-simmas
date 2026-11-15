<x-layouts.app title="Jurnal Kegiatan" subtitle="Dokumentasikan rincian kegiatan harian, kendala teknis, dan solusi selama magang">

    <div x-data="{
        editJurnal: { id: '', tanggal: '', kegiatan: '', kendala: '', solusi: '' },
        deleteJurnal: { id: '', tanggal: '', status_verifikasi: '' },
        photoPreviewUrl: '',
        openEdit(j) {
            this.editJurnal = j;
            this.$dispatch('open-modal', 'edit-jurnal-modal');
        },
        openDelete(j) {
            this.deleteJurnal = j;
            this.$dispatch('open-modal', 'delete-jurnal-modal');
        },
        openPhoto(url) {
            this.photoPreviewUrl = url;
            this.$dispatch('open-modal', 'photo-jurnal-modal');
        }
    }" class="space-y-6">

        <div class="simmas-card overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <form method="GET" action="{{ route('siswa.jurnal') }}" class="flex flex-1 items-center gap-3 max-w-md">
                    <div class="relative flex-1">
                        <input
                            type="text"
                            name="q"
                            value="{{ $search }}"
                            placeholder="Cari rincian kegiatan atau kendala..."
                            class="simmas-input pl-10"
                        >
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                            <i data-lucide="search" class="h-4 w-4"></i>
                        </span>
                    </div>
                    <button type="submit" class="simmas-btn-secondary">Cari</button>
                </form>

                <button
                    type="button"
                    @click="$dispatch('open-modal', 'tulis-jurnal-modal')"
                    class="simmas-btn-primary shrink-0"
                >
                    <i data-lucide="plus" class="h-4 w-4"></i>
                    <span>+ Tulis Jurnal</span>
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr>
                            <th class="simmas-table-th">Tanggal</th>
                            <th class="simmas-table-th">Kegiatan</th>
                            <th class="simmas-table-th text-center">Foto</th>
                            <th class="simmas-table-th">Status</th>
                            <th class="simmas-table-th">Catatan Guru</th>
                            <th class="simmas-table-th text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($jurnals as $j)
                            @php
                                $stVar = match($j->status_verifikasi) {
                                    'disetujui' => 'emerald',
                                    'revisi' => 'rose',
                                    default => 'amber',
                                };
                                $stLabel = match($j->status_verifikasi) {
                                    'disetujui' => 'Disetujui',
                                    'revisi' => 'Perlu Revisi',
                                    default => 'Menunggu',
                                };
                            @endphp
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="simmas-table-td font-mono text-xs font-semibold text-blue-600 whitespace-nowrap">
                                    {{ $j->tanggal->format('d M Y') }}
                                </td>
                                <td class="simmas-table-td max-w-md">
                                    <p class="text-xs font-medium text-slate-800 leading-relaxed">{{ $j->kegiatan }}</p>
                                    @if($j->kendala)
                                        <p class="text-[11px] text-slate-500 mt-1"><strong>Kendala:</strong> {{ $j->kendala }}</p>
                                    @endif
                                    @if($j->solusi)
                                        <p class="text-[11px] text-slate-500 mt-0.5"><strong>Solusi:</strong> {{ $j->solusi }}</p>
                                    @endif
                                </td>
                                <td class="simmas-table-td text-center">
                                    @if($j->photo_url)
                                        <button type="button" @click="openPhoto(@js($j->photo_url))" class="simmas-btn-secondary py-1 px-2.5 text-xs">
                                            <i data-lucide="image" class="h-3.5 w-3.5"></i>
                                            <span>Foto</span>
                                        </button>
                                    @else
                                        <span class="text-xs text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="simmas-table-td">
                                    <x-badge :variant="$stVar" :dot="true">{{ $stLabel }}</x-badge>
                                </td>
                                <td class="simmas-table-td text-xs text-slate-600 max-w-xs">
                                    {{ $j->catatan_guru ?? '-' }}
                                </td>
                                <td class="simmas-table-td text-right">
                                    @if($j->status_verifikasi !== 'disetujui')
                                        <div class="inline-flex items-center gap-1.5">
                                            <button
                                                type="button"
                                                title="Edit Jurnal"
                                                @click="openEdit(@js([
                                                    'id' => $j->id,
                                                    'tanggal' => $j->tanggal->format('Y-m-d'),
                                                    'kegiatan' => $j->kegiatan,
                                                    'kendala' => $j->kendala ?? '',
                                                    'solusi' => $j->solusi ?? '',
                                                ]))"
                                                class="rounded-lg border border-slate-200 p-1.5 text-slate-600 hover:bg-slate-100 cursor-pointer"
                                            >
                                                <i data-lucide="pencil" class="h-3.5 w-3.5"></i>
                                            </button>
                                            <button
                                                type="button"
                                                title="Hapus Jurnal"
                                                @click="openDelete(@js([
                                                    'id' => $j->id,
                                                    'tanggal' => $j->tanggal->format('d M Y'),
                                                    'status_verifikasi' => $j->status_verifikasi,
                                                ]))"
                                                class="rounded-lg border border-slate-200 p-1.5 text-rose-600 hover:bg-rose-50 cursor-pointer"
                                            >
                                                <i data-lucide="trash-2" class="h-3.5 w-3.5"></i>
                                            </button>
                                        </div>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-600">
                                            <i data-lucide="lock" class="h-3.5 w-3.5"></i>
                                            <span>Terkunci</span>
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <x-empty-state icon="book-open" title="Belum ada jurnal kegiatan." description="Klik tombol '+ Tulis Jurnal' untuk mencatat laporan kegiatan harian pertama Anda." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal Tulis Jurnal -->
        <x-modal name="tulis-jurnal-modal" title="Tulis Jurnal Kegiatan Harian" subtitle="Tuliskan rincian aktivitas magang minimal 15 karakter" icon="pen-line" maxWidth="lg">
            <form method="POST" action="{{ route('siswa.jurnal.store') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Tanggal Pelaksanaan <span class="text-rose-500">*</span></label>
                    <input type="date" name="tanggal" required max="{{ now()->toDateString() }}" value="{{ now()->toDateString() }}" class="simmas-input font-mono">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Rincian Kegiatan <span class="text-rose-500">*</span></label>
                    <textarea
                        name="kegiatan"
                        rows="4"
                        required
                        minlength="15"
                        placeholder="Jelaskan pekerjaan yang Anda lakukan hari ini secara rinci (minimal 15 karakter)..."
                        class="simmas-input"
                    ></textarea>
                    <p class="mt-1 text-[11px] text-slate-400">Wajib diisi, minimal 15 karakter.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Kendala (Opsional)</label>
                        <textarea name="kendala" rows="2" placeholder="Kendala teknis yang ditemui..." class="simmas-input"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Solusi (Opsional)</label>
                        <textarea name="solusi" rows="2" placeholder="Langkah penyelesaian..." class="simmas-input"></textarea>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Foto Bukti Kegiatan (Opsional)</label>
                    <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" class="simmas-input text-xs">
                    <p class="mt-1 text-[11px] text-slate-400">Format: JPG/PNG/WEBP, maksimal 2 MB.</p>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100">
                    <button type="button" @click="$dispatch('close-modal', 'tulis-jurnal-modal')" class="simmas-btn-secondary">Batal</button>
                    <button type="submit" class="simmas-btn-primary">
                        <i data-lucide="send" class="h-4 w-4"></i>
                        <span>Kirim Jurnal</span>
                    </button>
                </div>
            </form>
        </x-modal>

        <!-- Modal Edit Jurnal -->
        <x-modal name="edit-jurnal-modal" title="Edit Jurnal Kegiatan" subtitle="Perbaiki laporan jurnal harian Anda" icon="pencil" maxWidth="lg">
            <form method="POST" :action="'{{ url('/siswa/jurnal') }}/' + editJurnal.id" enctype="multipart/form-data" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Tanggal Pelaksanaan <span class="text-rose-500">*</span></label>
                    <input type="date" name="tanggal" x-model="editJurnal.tanggal" required max="{{ now()->toDateString() }}" class="simmas-input font-mono">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Rincian Kegiatan <span class="text-rose-500">*</span></label>
                    <textarea name="kegiatan" rows="4" x-model="editJurnal.kegiatan" required minlength="15" class="simmas-input"></textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Kendala (Opsional)</label>
                        <textarea name="kendala" rows="2" x-model="editJurnal.kendala" class="simmas-input"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Solusi (Opsional)</label>
                        <textarea name="solusi" rows="2" x-model="editJurnal.solusi" class="simmas-input"></textarea>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Ganti Foto Bukti (Opsional)</label>
                    <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" class="simmas-input text-xs">
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100">
                    <button type="button" @click="$dispatch('close-modal', 'edit-jurnal-modal')" class="simmas-btn-secondary">Batal</button>
                    <button type="submit" class="simmas-btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </x-modal>

        <!-- Alert Hapus Laporan Jurnal -->
        <x-modal name="delete-jurnal-modal" title="Hapus Laporan Jurnal" subtitle="Hanya jurnal berstatus menunggu/revisi yang dapat dihapus" icon="trash-2" iconColor="rose" maxWidth="md">
            <div class="space-y-4">
                <p class="text-xs text-slate-600 leading-relaxed">
                    Apakah Anda yakin ingin menghapus laporan jurnal kegiatan tanggal <strong class="font-mono text-slate-900" x-text="deleteJurnal.tanggal"></strong>?
                </p>

                <form method="POST" :action="'{{ url('/siswa/jurnal') }}/' + deleteJurnal.id" class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
                    @csrf
                    @method('DELETE')
                    <button type="button" @click="$dispatch('close-modal', 'delete-jurnal-modal')" class="simmas-btn-secondary">Batal</button>
                    <button type="submit" class="simmas-btn-danger">Ya, Hapus Jurnal</button>
                </form>
            </div>
        </x-modal>

        <!-- Modal Preview Foto Jurnal -->
        <x-modal name="photo-jurnal-modal" title="Foto Bukti Kegiatan Jurnal" icon="image" maxWidth="md">
            <div class="space-y-4 text-center">
                <img :src="photoPreviewUrl" alt="Foto Jurnal" class="mx-auto max-h-96 rounded-xl border border-slate-200 object-contain">
                <button type="button" @click="$dispatch('close-modal', 'photo-jurnal-modal')" class="simmas-btn-primary w-full">Tutup</button>
            </div>
        </x-modal>

    </div>

</x-layouts.app>

