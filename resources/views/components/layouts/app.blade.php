
@props([
    'title' => 'Dashboard',
    'subtitle' => null,
])

@php
    $user = auth()->user();
    $role = $user?->role ?? 'siswa';
    $appName = \App\Models\Setting::getValue('app_name', 'SIMMAS');
    $appFullname = \App\Models\Setting::getValue('app_fullname', 'Sistem Informasi Manajemen Magang Siswa');

    $roleLabels = [
        'admin' => 'Administrator',
        'guru' => 'Guru Pembimbing',
        'siswa' => 'Siswa Magang',
    ];
    $roleLabel = $roleLabels[$role] ?? 'Pengguna';

    // Guru dynamic badges
    $guruPendingValidasiCount = 0;
    $guruSiswaCount = 0;
    if ($role === 'guru' && $user?->guru) {
        $guruId = $user->guru->id;
        $siswaIds = \App\Models\PenempatanMagang::where('guru_id', $guruId)->pluck('siswa_id');
        $guruSiswaCount = $siswaIds->unique()->count();
        $pendingJurnal = \App\Models\JurnalHarian::whereIn('siswa_id', $siswaIds)->where('status_verifikasi', 'menunggu')->count();
        $pendingAbsensi = \App\Models\Absensi::whereIn('siswa_id', $siswaIds)->whereIn('status', ['sakit', 'izin'])->where('status_validasi', 'menunggu')->count();
        $guruPendingValidasiCount = $pendingJurnal + $pendingAbsensi;
    }
@endphp

<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#2563eb">
    <title>{{ $title }} — {{ $appName }}</title>

    <!-- Google Fonts: Plus Jakarta Sans & JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'Inter', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    },
                    colors: {
                        brand: {
                            50: '#eff6ff', 100: '#dbeafe', 200: '#bfdbfe', 300: '#93c5fd',
                            400: '#60a5fa', 500: '#3b82f6', 600: '#2563eb', 700: '#1d4ed8',
                            800: '#1e40af', 900: '#1e3a8a',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Custom SIMMAS Styles -->
    <style>
        html { scroll-behavior: smooth; font-family: 'Plus Jakarta Sans', sans-serif; }
        [x-cloak] { display: none !important; }
        .simmas-card {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            box-shadow: 0 1px 3px 0 rgba(15, 23, 42, 0.03), 0 1px 2px -1px rgba(15, 23, 42, 0.03);
        }
        .simmas-hero-gradient {
            background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 50%, #3b82f6 100%);
        }
        .simmas-table-th {
            padding: 0.875rem 1.25rem;
            text-align: left;
            font-size: 0.6875rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #64748b;
            background-color: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
        }
        .simmas-table-td {
            padding: 1rem 1.25rem;
            font-size: 0.875rem;
            color: #334155;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        .simmas-input {
            width: 100%;
            border-radius: 0.75rem;
            border: 1px solid #cbd5e1;
            background-color: #ffffff;
            padding: 0.625rem 0.875rem;
            font-size: 0.875rem;
            color: #0f172a;
            transition: all 0.15s ease;
        }
        .simmas-input:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        }
        .simmas-input:disabled, .simmas-input[readonly] {
            background-color: #f1f5f9;
            color: #64748b;
            cursor: not-allowed;
        }
        .simmas-btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            border-radius: 0.75rem;
            background-color: #2563eb;
            padding: 0.625rem 1.125rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: #ffffff;
            transition: all 0.15s ease;
            box-shadow: 0 1px 2px 0 rgba(37, 99, 235, 0.25);
            cursor: pointer;
        }
        .simmas-btn-primary:hover { background-color: #1d4ed8; }
        .simmas-btn-secondary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            border-radius: 0.75rem;
            border: 1px solid #cbd5e1;
            background-color: #ffffff;
            padding: 0.625rem 1.125rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: #334155;
            transition: all 0.15s ease;
            cursor: pointer;
        }
        .simmas-btn-secondary:hover { background-color: #f8fafc; border-color: #94a3b8; }
        .simmas-btn-danger {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            border-radius: 0.75rem;
            background-color: #dc2626;
            padding: 0.625rem 1.125rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: #ffffff;
            transition: all 0.15s ease;
            cursor: pointer;
        }
        .simmas-btn-danger:hover { background-color: #b91c1c; }
    </style>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
</head>
<body class="h-full bg-[#f8fafc] text-slate-900 antialiased" x-data="{ sidebarOpen: false, searchModalOpen: false }" @keydown.window.ctrl.k.prevent="searchModalOpen = true" @keydown.window.meta.k.prevent="searchModalOpen = true">

    <x-toast />

    <div class="min-h-screen flex">
        <!-- Mobile Sidebar Backdrop -->
        <div
            x-show="sidebarOpen"
            x-transition.opacity
            @click="sidebarOpen = false"
            x-cloak
            class="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-xs lg:hidden"
        ></div>

        <!-- Sidebar Left (260px fixed) -->
        <aside
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
            class="fixed inset-y-0 left-0 z-40 flex w-[264px] flex-col border-r border-slate-200 bg-white transition-transform duration-200 ease-in-out lg:static lg:inset-auto lg:translate-x-0 shrink-0"
        >
            <!-- Brand Header -->
            <div class="flex h-16 items-center justify-between px-5 border-b border-slate-100">
                <a href="{{ url('/') }}" class="flex items-center gap-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm shadow-blue-600/30">
                        <i data-lucide="graduation-cap" class="h-5 w-5"></i>
                    </div>
                    <div class="leading-tight">
                        <span class="block text-base font-extrabold tracking-tight text-slate-900">{{ $appName }}</span>
                        <span class="block text-[11px] font-semibold text-blue-600">{{ $roleLabel }}</span>
                    </div>
                </a>
                <button @click="sidebarOpen = false" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 lg:hidden">
                    <i data-lucide="x" class="h-4 w-4"></i>
                </button>
            </div>

            <!-- Navigation Menu -->
            <nav class="flex-1 overflow-y-auto px-3.5 py-4 space-y-4">
                @if($role === 'admin')
                    <div class="space-y-1">
                        <a href="{{ route('admin.dashboard') }}"
                           class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-semibold' }}">
                            <i data-lucide="layout-dashboard" class="h-4 w-4 shrink-0"></i>
                            <span>Beranda</span>
                        </a>
                        <a href="{{ route('admin.siswa') }}"
                           class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm transition-all {{ request()->routeIs('admin.siswa*') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-semibold' }}">
                            <i data-lucide="users" class="h-4 w-4 shrink-0"></i>
                            <span>Data Siswa</span>
                        </a>
                        <a href="{{ route('admin.guru') }}"
                           class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm transition-all {{ request()->routeIs('admin.guru*') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-semibold' }}">
                            <i data-lucide="user-check" class="h-4 w-4 shrink-0"></i>
                            <span>Data Guru</span>
                        </a>
                        <a href="{{ route('admin.dudi') }}"
                           class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm transition-all {{ request()->routeIs('admin.dudi*') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-semibold' }}">
                            <i data-lucide="building-2" class="h-4 w-4 shrink-0"></i>
                            <span>Mitra DUDI</span>
                        </a>
                        <a href="{{ route('admin.pengajuan') }}"
                           class="flex items-center justify-between gap-3 rounded-xl px-3.5 py-2.5 text-sm transition-all {{ request()->routeIs('admin.pengajuan*') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-semibold' }}">
                            <span class="flex items-center gap-3">
                                <i data-lucide="file-text" class="h-4 w-4 shrink-0"></i>
                                <span>Permohonan Magang</span>
                            </span>
                            @php
                                $pendingCount = \App\Models\PengajuanMagang::where('status', 'menunggu')->count();
                            @endphp
                            @if($pendingCount > 0)
                                <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-extrabold text-amber-700">{{ $pendingCount }}</span>
                            @endif
                        </a>
                        <a href="{{ route('admin.penempatan') }}"
                           class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm transition-all {{ request()->routeIs('admin.penempatan*') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-semibold' }}">
                            <i data-lucide="briefcase" class="h-4 w-4 shrink-0"></i>
                            <span>Penempatan</span>
                        </a>
                        <a href="{{ route('admin.monitoring') }}"
                           class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm transition-all {{ request()->routeIs('admin.monitoring*') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-semibold' }}">
                            <i data-lucide="clipboard-list" class="h-4 w-4 shrink-0"></i>
                            <span>Laporan</span>
                        </a>
                    </div>

                    <div class="pt-3 border-t border-slate-100 space-y-1">
                        <a href="{{ route('admin.settings') }}"
                           class="flex items-center gap-3 rounded-xl px-3.5 py-2 text-xs transition-all {{ request()->routeIs('admin.settings*') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900 font-medium' }}">
                            <i data-lucide="settings" class="h-3.5 w-3.5 shrink-0"></i>
                            <span>Pengaturan</span>
                        </a>
                        <a href="{{ route('admin.logs') }}"
                           class="flex items-center gap-3 rounded-xl px-3.5 py-2 text-xs transition-all {{ request()->routeIs('admin.logs*') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900 font-medium' }}">
                            <i data-lucide="shield-alert" class="h-3.5 w-3.5 shrink-0"></i>
                            <span>Log Aktivitas</span>
                        </a>
                    </div>

                @elseif($role === 'guru')
                    <div class="space-y-1">
                        <a href="{{ route('guru.dashboard') }}"
                           class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm transition-all {{ request()->routeIs('guru.dashboard') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-semibold' }}">
                            <i data-lucide="layout-dashboard" class="h-4 w-4 shrink-0"></i>
                            <span>Beranda</span>
                        </a>
                        <a href="{{ route('guru.siswa') }}"
                           class="flex items-center justify-between gap-2 rounded-xl px-3.5 py-2.5 text-sm transition-all {{ request()->routeIs('guru.siswa*') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-semibold' }}">
                            <span class="flex items-center gap-3">
                                <i data-lucide="users" class="h-4 w-4 shrink-0"></i>
                                <span>Siswa Bimbingan</span>
                            </span>
                            <span class="inline-flex h-5 min-w-5 items-center justify-center rounded-full px-1.5 text-[11px] font-bold {{ request()->routeIs('guru.siswa*') ? 'bg-blue-600 text-white' : 'bg-blue-50 text-blue-600' }}">
                                {{ $guruSiswaCount }}
                            </span>
                        </a>
                        <a href="{{ route('guru.jurnal') }}"
                           class="flex items-center justify-between gap-2 rounded-xl px-3.5 py-2.5 text-sm transition-all {{ request()->routeIs('guru.jurnal*') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-semibold' }}">
                            <span class="flex items-center gap-3 truncate">
                                <i data-lucide="check-square" class="h-4 w-4 shrink-0"></i>
                                <span class="truncate">Validasi Jurnal & Absensi</span>
                            </span>
                            @if($guruPendingValidasiCount > 0)
                                <span class="inline-flex h-5 min-w-5 items-center justify-center rounded-full px-1.5 text-[11px] font-bold {{ request()->routeIs('guru.jurnal*') ? 'bg-blue-600 text-white' : 'bg-amber-100 text-amber-700' }}">
                                    {{ $guruPendingValidasiCount }}
                                </span>
                            @endif
                        </a>
                        <a href="{{ route('guru.kunjungan') }}"
                           class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm transition-all {{ request()->routeIs('guru.kunjungan*') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-semibold' }}">
                            <i data-lucide="map-pin" class="h-4 w-4 shrink-0"></i>
                            <span>Kunjungan Lapangan</span>
                        </a>
                    </div>

                @elseif($role === 'siswa')
                    <div class="space-y-1">
                        <a href="{{ route('siswa.dashboard') }}"
                           class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm transition-all {{ request()->routeIs('siswa.dashboard') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-semibold' }}">
                            <i data-lucide="layout-dashboard" class="h-4 w-4 shrink-0"></i>
                            <span>Beranda</span>
                        </a>
                        <a href="{{ route('siswa.pengajuan') }}"
                           class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm transition-all {{ request()->routeIs('siswa.pengajuan*') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-semibold' }}">
                            <i data-lucide="file-text" class="h-4 w-4 shrink-0"></i>
                            <span>Permohonan Magang</span>
                        </a>
                        <a href="{{ route('siswa.absensi') }}"
                           class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm transition-all {{ request()->routeIs('siswa.absensi*') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-semibold' }}">
                            <i data-lucide="clock" class="h-4 w-4 shrink-0"></i>
                            <span>Absensi Harian</span>
                        </a>
                        <a href="{{ route('siswa.jurnal') }}"
                           class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm transition-all {{ request()->routeIs('siswa.jurnal*') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-semibold' }}">
                            <i data-lucide="book-open" class="h-4 w-4 shrink-0"></i>
                            <span>Jurnal Kegiatan</span>
                        </a>
                    </div>
                @endif
            </nav>

            <!-- Sidebar Bottom User Summary -->
            <div class="border-t border-slate-100 p-3.5">
                <div class="flex items-center justify-between gap-2.5 rounded-xl bg-slate-50 p-2.5 border border-slate-200/60">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-xs font-bold text-white">
                            {{ $user?->initials ?? 'US' }}
                        </div>
                        <div class="min-w-0">
                            <p class="truncate text-xs font-bold text-slate-900">{{ $user?->nama ?? 'Pengguna' }}</p>
                            <p class="truncate text-[11px] text-slate-500">{{ $user?->email }}</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" title="Keluar" class="rounded-lg p-1.5 text-slate-400 hover:bg-rose-50 hover:text-rose-600 transition-colors cursor-pointer">
                            <i data-lucide="log-out" class="h-4 w-4"></i>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex flex-1 flex-col min-w-0">
            <!-- Top Header (64px) -->
            <header class="sticky top-0 z-30 flex h-16 items-center justify-between gap-4 border-b border-slate-200 bg-white/90 px-4 sm:px-6 lg:px-8 backdrop-blur-md">
                <div class="flex items-center gap-3 min-w-0">
                    <button
                        type="button"
                        @click="sidebarOpen = true"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 lg:hidden"
                    >
                        <i data-lucide="menu" class="h-5 w-5"></i>
                    </button>
                    <div class="min-w-0">
                        <h1 class="truncate text-base sm:text-lg font-bold text-slate-900">{{ $title }}</h1>
                        @if($subtitle)
                            <p class="hidden sm:block truncate text-xs text-slate-500">{{ $subtitle }}</p>
                        @endif
                    </div>
                </div>

                <!-- Right Header Controls -->
                <div class="flex items-center gap-3">
                    <!-- Quick Search Button (Cari... ⌘K) -->
                    <button
                        type="button"
                        @click="searchModalOpen = true"
                        class="hidden md:flex items-center justify-between gap-6 rounded-xl border border-slate-200 bg-slate-50/80 px-3.5 py-1.5 text-xs text-slate-400 hover:border-slate-300 hover:bg-slate-100/70 transition-all cursor-pointer"
                    >
                        <span class="flex items-center gap-2">
                            <i data-lucide="search" class="h-3.5 w-3.5 text-slate-400"></i>
                            <span>Cari...</span>
                        </span>
                        <kbd class="rounded border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[10px] font-semibold text-slate-500">⌘K</kbd>
                    </button>

                    <!-- Notification Dropdown -->
                    <div x-data="{ notifOpen: false }" class="relative">
                        <button
                            type="button"
                            @click="notifOpen = !notifOpen"
                            class="relative flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 transition-colors cursor-pointer"
                        >
                            <i data-lucide="bell" class="h-4 w-4"></i>
                            <span class="absolute top-1.5 right-1.5 h-2 w-2 rounded-full bg-blue-600 ring-2 ring-white"></span>
                        </button>
                        <div
                            x-show="notifOpen"
                            @click.outside="notifOpen = false"
                            x-transition
                            x-cloak
                            class="absolute right-0 mt-2 w-80 rounded-2xl border border-slate-200 bg-white p-4 shadow-xl z-50"
                        >
                            <div class="flex items-center justify-between pb-2.5 border-b border-slate-100">
                                <span class="text-xs font-bold text-slate-900">Notifikasi Sistem</span>
                                <span class="rounded-full bg-blue-50 px-2 py-0.5 text-[10px] font-semibold text-blue-600">Terbaru</span>
                            </div>
                            <div class="py-3 space-y-2.5 text-xs text-slate-600">
                                <div class="flex items-start gap-2.5">
                                    <div class="h-2 w-2 rounded-full bg-blue-600 mt-1.5 shrink-0"></div>
                                    <div>
                                        <p class="font-semibold text-slate-800">Sistem {{ $appName }} Aktif</p>
                                        <p class="text-[11px] text-slate-500">Seluruh layanan pemantauan magang berjalan normal.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- User Avatar + Name + Dropdown Logout -->
                    <div x-data="{ userMenuOpen: false }" class="relative">
                        <button
                            type="button"
                            @click="userMenuOpen = !userMenuOpen"
                            class="flex items-center gap-2.5 rounded-xl border border-slate-200 bg-white p-1.5 pr-3 hover:bg-slate-50 transition-colors cursor-pointer"
                        >
                            <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-600 text-xs font-bold text-white">
                                {{ $user?->initials ?? 'US' }}
                            </div>
                            <div class="hidden sm:block text-left leading-tight">
                                <p class="text-xs font-bold text-slate-800 max-w-[130px] truncate">{{ $user?->nama }}</p>
                                <p class="text-[10px] font-medium text-slate-400">{{ $roleLabel }}</p>
                            </div>
                            <i data-lucide="chevron-down" class="h-3.5 w-3.5 text-slate-400"></i>
                        </button>

                        <div
                            x-show="userMenuOpen"
                            @click.outside="userMenuOpen = false"
                            x-transition
                            x-cloak
                            class="absolute right-0 mt-2 w-56 rounded-2xl border border-slate-200 bg-white p-2 shadow-xl z-50"
                        >
                            <div class="px-3 py-2 border-b border-slate-100">
                                <p class="text-xs font-bold text-slate-900 truncate">{{ $user?->nama }}</p>
                                <p class="text-[11px] text-slate-500 truncate">{{ $user?->email }}</p>
                            </div>
                            <div class="mt-1">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button
                                        type="submit"
                                        class=" flex w-full items-center gap-2.5 rounded-xl px-3 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer"
                                    >
                                        <i data-lucide="log-out" class="h-4 w-4"></i>
                                        <span>Keluar dari Akun</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Page Content -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8 space-y-6 max-w-[1440px] w-full mx-auto">
                {{ $slot }}
            </main>
        </div>
    </div>

    <!-- Command K Quick Navigation Modal -->
    <div
        x-show="searchModalOpen"
        x-cloak
        @keydown.escape.window="searchModalOpen = false"
        class="fixed inset-0 z-50 flex items-start justify-center pt-20 p-4"
    >
        <div @click="searchModalOpen = false" class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs"></div>
        <div class="relative w-full max-w-lg rounded-2xl bg-white shadow-2xl border border-slate-200 overflow-hidden" x-data="{ q: '' }">
            <div class="flex items-center gap-3 px-4 py-3.5 border-b border-slate-100">
                <i data-lucide="search" class="h-4 w-4 text-slate-400"></i>
                <input
                    type="text"
                    x-model="q"
                    placeholder="Ketik halaman atau menu untuk navigasi cepat..."
                    class="w-full text-sm text-slate-800 placeholder-slate-400 focus:outline-none"
                >
                <button @click="searchModalOpen = false" class="text-xs text-slate-400 hover:text-slate-600">ESC</button>
            </div>
            <div class="p-3 max-h-72 overflow-y-auto space-y-1 text-xs">
                <p class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">Navigasi Cepat</p>
                @if($role === 'admin')
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center justify-between rounded-xl px-3 py-2 text-slate-700 hover:bg-blue-50 hover:text-blue-600 font-medium">
                        <span>Dashboard Statistik</span>
                        <span class="text-[11px] text-slate-400">/admin/dashboard</span>
                    </a>
                    <a href="{{ route('admin.monitoring') }}" class="flex items-center justify-between rounded-xl px-3 py-2 text-slate-700 hover:bg-blue-50 hover:text-blue-600 font-medium">
                        <span>Monitoring Global</span>
                        <span class="text-[11px] text-slate-400">/admin/monitoring</span>
                    </a>
                    <a href="{{ route('admin.guru') }}" class="flex items-center justify-between rounded-xl px-3 py-2 text-slate-700 hover:bg-blue-50 hover:text-blue-600 font-medium">
                        <span>Master Data Guru</span>
                        <span class="text-[11px] text-slate-400">/admin/guru</span>
                    </a>
                    <a href="{{ route('admin.siswa') }}" class="flex items-center justify-between rounded-xl px-3 py-2 text-slate-700 hover:bg-blue-50 hover:text-blue-600 font-medium">
                        <span>Master Data Siswa</span>
                        <span class="text-[11px] text-slate-400">/admin/siswa</span>
                    </a>
                    <a href="{{ route('admin.dudi') }}" class="flex items-center justify-between rounded-xl px-3 py-2 text-slate-700 hover:bg-blue-50 hover:text-blue-600 font-medium">
                        <span>Master Data Mitra DUDI</span>
                        <span class="text-[11px] text-slate-400">/admin/dudi</span>
                    </a>
                    <a href="{{ route('admin.penempatan') }}" class="flex items-center justify-between rounded-xl px-3 py-2 text-slate-700 hover:bg-blue-50 hover:text-blue-600 font-medium">
                        <span>Manajemen Penempatan</span>
                        <span class="text-[11px] text-slate-400">/admin/penempatan</span>
                    </a>
                    <a href="{{ route('admin.settings') }}" class="flex items-center justify-between rounded-xl px-3 py-2 text-slate-700 hover:bg-blue-50 hover:text-blue-600 font-medium">
                        <span>Pengaturan Sistem</span>
                        <span class="text-[11px] text-slate-400">/admin/settings</span>
                    </a>
                    <a href="{{ route('admin.logs') }}" class="flex items-center justify-between rounded-xl px-3 py-2 text-slate-700 hover:bg-blue-50 hover:text-blue-600 font-medium">
                        <span>Log Aktivitas & Audit</span>
                        <span class="text-[11px] text-slate-400">/admin/logs</span>
                    </a>
                @elseif($role === 'guru')
                    <a href="{{ route('guru.dashboard') }}" class="flex items-center justify-between rounded-xl px-3 py-2 text-slate-700 hover:bg-blue-50 hover:text-blue-600 font-medium">
                        <span>Dashboard Pembimbing</span>
                        <span class="text-[11px] text-slate-400">/guru/dashboard</span>
                    </a>
                    <a href="{{ route('guru.jurnal') }}" class="flex items-center justify-between rounded-xl px-3 py-2 text-slate-700 hover:bg-blue-50 hover:text-blue-600 font-medium">
                        <span>Validasi Jurnal & Absensi</span>
                        <span class="text-[11px] text-slate-400">/guru/jurnal</span>
                    </a>
                    <a href="{{ route('guru.siswa') }}" class="flex items-center justify-between rounded-xl px-3 py-2 text-slate-700 hover:bg-blue-50 hover:text-blue-600 font-medium">
                        <span>Siswa Bimbingan</span>
                        <span class="text-[11px] text-slate-400">/guru/siswa</span>
                    </a>
                    <a href="{{ route('guru.kunjungan') }}" class="flex items-center justify-between rounded-xl px-3 py-2 text-slate-700 hover:bg-blue-50 hover:text-blue-600 font-medium">
                        <span>Kunjungan Lapangan</span>
                        <span class="text-[11px] text-slate-400">/guru/kunjungan</span>
                    </a>
                @else
                    <a href="{{ route('siswa.dashboard') }}" class="flex items-center justify-between rounded-xl px-3 py-2 text-slate-700 hover:bg-blue-50 hover:text-blue-600 font-medium">
                        <span>Dashboard Siswa</span>
                        <span class="text-[11px] text-slate-400">/siswa/dashboard</span>
                    </a>
                    <a href="{{ route('siswa.pengajuan') }}" class="flex items-center justify-between rounded-xl px-3 py-2 text-slate-700 hover:bg-blue-50 hover:text-blue-600 font-medium">
                        <span>Pengajuan Magang</span>
                        <span class="text-[11px] text-slate-400">/siswa/pengajuan</span>
                    </a>
                    <a href="{{ route('siswa.absensi') }}" class="flex items-center justify-between rounded-xl px-3 py-2 text-slate-700 hover:bg-blue-50 hover:text-blue-600 font-medium">
                        <span>Absensi Harian</span>
                        <span class="text-[11px] text-slate-400">/siswa/absensi</span>
                    </a>
                    <a href="{{ route('siswa.jurnal') }}" class="flex items-center justify-between rounded-xl px-3 py-2 text-slate-700 hover:bg-blue-50 hover:text-blue-600 font-medium">
                        <span>Jurnal Kegiatan</span>
                        <span class="text-[11px] text-slate-400">/siswa/jurnal</span>
                    </a>
                @endif
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) {
                lucide.createIcons();
            }
        });
    </script>
    @stack('scripts')
</body>
</html>

