<x-layouts.app title="Pengaturan Sistem" subtitle="Konfigurasi identitas aplikasi, konten halaman depan, dan data sekolah">

    <div x-data="{ activeTab: 'identitas' }" class="space-y-6">

        <!-- 3 Tab Navigation Bar -->
        <div class="simmas-card p-2 inline-flex flex-wrap items-center gap-1.5 bg-slate-100/80 border-slate-200">
            <button
                type="button"
                @click="activeTab = 'identitas'"
                :class="activeTab === 'identitas' ? 'bg-white text-blue-600 shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-xs font-bold transition-all cursor-pointer"
            >
                <i data-lucide="sliders" class="h-4 w-4"></i>
                <span>Identitas Aplikasi</span>
            </button>

            <button
                type="button"
                @click="activeTab = 'hero'"
                :class="activeTab === 'hero' ? 'bg-white text-blue-600 shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-xs font-bold transition-all cursor-pointer"
            >
                <i data-lucide="layout-template" class="h-4 w-4"></i>
                <span>Halaman Depan</span>
            </button>

            <button
                type="button"
                @click="activeTab = 'sekolah'"
                :class="activeTab === 'sekolah' ? 'bg-white text-blue-600 shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-xs font-bold transition-all cursor-pointer"
            >
                <i data-lucide="school" class="h-4 w-4"></i>
                <span>Data Sekolah</span>
            </button>
        </div>

        <form method="POST" action="{{ route('admin.settings.update') }}" class="simmas-card p-6 sm:p-8 space-y-6">
            @csrf
            @method('PUT')

            <!-- Tab 1: Identitas Aplikasi -->
            <div x-show="activeTab === 'identitas'" class="space-y-5">
                <div class="border-b border-slate-100 pb-4">
                    <h3 class="text-base font-bold text-slate-900">Identitas Aplikasi</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Nama aplikasi akan diterapkan secara dinamis pada sidebar, header, dan judul halaman.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Aplikasi (Singkatan) <span class="text-rose-500">*</span></label>
                        <input type="text" name="app_name" value="{{ old('app_name', $settings['app_name'] ?? 'SIMMAS') }}" required class="simmas-input">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Kepanjangan Aplikasi <span class="text-rose-500">*</span></label>
                        <input type="text" name="app_fullname" value="{{ old('app_fullname', $settings['app_fullname'] ?? 'Sistem Informasi Manajemen Magang Siswa') }}" required class="simmas-input">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Deskripsi Singkat Aplikasi <span class="text-rose-500">*</span></label>
                    <textarea name="app_description" rows="3" required class="simmas-input">{{ old('app_description', $settings['app_description'] ?? '') }}</textarea>
                </div>
            </div>

            <!-- Tab 2: Halaman Depan -->
            <div x-show="activeTab === 'hero'" x-cloak class="space-y-5">
                <div class="border-b border-slate-100 pb-4">
                    <h3 class="text-base font-bold text-slate-900">Konten Halaman Depan (Landing Page)</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Atur judul utama hero dan paragraf deskripsi yang tampil pada halaman publik `/`.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Judul Utama Hero <span class="text-rose-500">*</span></label>
                    <input type="text" name="hero_title" value="{{ old('hero_title', $settings['hero_title'] ?? 'Magang lebih teratur.') }}" required class="simmas-input">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Deskripsi Hero <span class="text-rose-500">*</span></label>
                    <textarea name="hero_description" rows="3" required class="simmas-input">{{ old('hero_description', $settings['hero_description'] ?? '') }}</textarea>
                </div>
            </div>

            <!-- Tab 3: Data Sekolah -->
            <div x-show="activeTab === 'sekolah'" x-cloak class="space-y-5">
                <div class="border-b border-slate-100 pb-4">
                    <h3 class="text-base font-bold text-slate-900">Data Institusi Sekolah</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Informasi resmi sekolah penyelenggara program Praktik Kerja Lapangan (PKL).</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Sekolah <span class="text-rose-500">*</span></label>
                        <input type="text" name="school_name" value="{{ old('school_name', $settings['school_name'] ?? '') }}" required class="simmas-input">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Website Resmi <span class="text-rose-500">*</span></label>
                        <input type="text" name="school_website" value="{{ old('school_website', $settings['school_website'] ?? '') }}" required class="simmas-input">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Kepala Sekolah <span class="text-rose-500">*</span></label>
                        <input type="text" name="principal_name" value="{{ old('principal_name', $settings['principal_name'] ?? '') }}" required class="simmas-input">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">NIP Kepala Sekolah <span class="text-rose-500">*</span></label>
                        <input type="text" name="principal_nip" value="{{ old('principal_nip', $settings['principal_nip'] ?? '') }}" required class="simmas-input font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Nomor Telepon Sekolah <span class="text-rose-500">*</span></label>
                        <input type="text" name="school_phone" value="{{ old('school_phone', $settings['school_phone'] ?? '') }}" required class="simmas-input font-mono">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Alamat Lengkap Sekolah <span class="text-rose-500">*</span></label>
                    <textarea name="school_address" rows="3" required class="simmas-input">{{ old('school_address', $settings['school_address'] ?? '') }}</textarea>
                </div>
            </div>

            <div class="flex items-center justify-end pt-4 border-t border-slate-100">
                <button type="submit" class="simmas-btn-primary px-6 py-3">
                    <i data-lucide="save" class="h-4 w-4"></i>
                    <span>Simpan Pengaturan</span>
                </button>
            </div>
        </form>

    </div>

</x-layouts.app>

