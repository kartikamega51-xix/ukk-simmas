@php
    $appName = $settings['app_name'] ?? 'SIMMAS';
    $appFullname = $settings['app_fullname'] ?? 'Sistem Informasi Manajemen Magang Siswa';
    $heroTitle = $settings['hero_title'] ?? 'Magang lebih teratur.';
    $heroDesc = $settings['hero_description'] ?? 'Platform manajemen magang siswa SMK yang menghubungkan sekolah, guru pembimbing, dan dunia usaha dalam satu sistem terpadu.';
@endphp
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#2563eb">
    <title>{{ $appName }} — {{ $appFullname }}</title>

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
    <style>
        html { scroll-behavior: smooth; font-family: 'Plus Jakarta Sans', sans-serif; }
        .simmas-card {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            box-shadow: 0 1px 3px 0 rgba(15, 23, 42, 0.03), 0 1px 2px -1px rgba(15, 23, 42, 0.03);
        }
        .simmas-hero-gradient {
            background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 50%, #3b82f6 100%);
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
        [x-cloak] { display: none !important; }
    </style>
    <!-- Alpine.js CDN -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-white text-slate-900 antialiased selection:bg-blue-600 selection:text-white relative overflow-x-hidden">

    <!-- Vibrant Blue Organic Background Shape on the Right (Matches Reference Screenshot) -->
    <div class="pointer-events-none absolute top-0 right-0 w-[42%] xl:w-[45%] h-[820px] bg-blue-600 rounded-bl-[190px] hidden lg:block z-0 overflow-hidden">
        <!-- Soft diagonal highlight inside the blue shape -->
        <div class="absolute -top-24 -right-24 w-[480px] h-[480px] rounded-full bg-blue-500/25 blur-3xl"></div>
        <div class="absolute bottom-10 left-10 w-52 h-52 rounded-full bg-blue-700/40 blur-2xl"></div>
    </div>

    <!-- Floating Pill Header -->
    <header class="fixed top-0 inset-x-0 z-50 px-4 sm:px-6 lg:px-8 pt-4">
        <div class="mx-auto max-w-7xl rounded-full border border-slate-200/80 bg-white/95 px-6 sm:px-8 h-16 flex items-center justify-between shadow-md backdrop-blur-md">
            <a href="{{ url('/') }}" class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm shadow-blue-600/30">
                    <i data-lucide="graduation-cap" class="h-5 w-5"></i>
                </div>
                <span class="text-base font-extrabold tracking-tight text-slate-900">jagang company</span>
            </a>

            <nav class="hidden md:flex items-center gap-8 text-sm font-semibold text-slate-600">
                <a href="#features" class="hover:text-blue-600 transition-colors">Fitur</a>
                <a href="#panduan" class="hover:text-blue-600 transition-colors">Panduan</a>
            </nav>

            <div class="flex items-center gap-3">
                @auth
                    <a href="{{ route(auth()->user()->role . '.dashboard') }}" class="rounded-full bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs sm:text-sm px-5 py-2.5 shadow-sm transition-all flex items-center gap-1.5">
                        <span>Dashboard</span>
                        <i data-lucide="arrow-right" class="h-4 w-4"></i>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="rounded-full bg-blue-50 hover:bg-blue-100 text-blue-600 font-semibold text-xs sm:text-sm px-5 py-2 transition-colors">
                        Masuk
                    </a>
                    <a href="{{ route('login') }}" class="rounded-full bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs sm:text-sm px-5 py-2 shadow-sm transition-all flex items-center gap-1.5">
                        <span>Mulai Sekarang</span>
                        <i data-lucide="arrow-right" class="h-4 w-4"></i>
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Hero Section (Pixel-Perfect with Reference) -->
    <section class="relative pt-32 pb-20 lg:pt-36 lg:pb-28">
        <!-- Soft Circular Backdrop Glow on the Left -->
        <div class="pointer-events-none absolute top-40 left-10 w-96 h-96 rounded-full bg-blue-100/50 blur-3xl -z-10"></div>

        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 items-center">

                <!-- Left Column -->
                <div class="lg:col-span-6 space-y-6 z-10">
                    <!-- Overline: Clean uppercase text with letter-spacing (no badge box) -->
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">
                        SISTEM INFORMASI MANAJEMEN MAGANG SISWA
                    </p>

                    <!-- Big 3-line Headline -->
                    <h1 class="text-5xl sm:text-6xl lg:text-[72px] font-black tracking-tight text-slate-900 leading-[1.04]">
                        Magang<br>lebih<br>teratur.
                    </h1>

                    <!-- Description Paragraph -->
                    <p class="text-base text-slate-500 leading-relaxed max-w-md">
                        Platform manajemen magang siswa SMK yang menghubungkan sekolah, guru pembimbing, dan dunia usaha dalam satu sistem terpadu.
                    </p>

                    <!-- 3 Checklists with blue circular check icons -->
                    <ul class="space-y-3 pt-1">
                        <li class="flex items-center gap-3 text-sm font-semibold text-slate-700">
                            <span class="flex h-5 w-5 shrink-0 items-center justify-center text-blue-600">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"/>
                                    <path d="m9 12 2 2 4-4"/>
                                </svg>
                            </span>
                            <span>Penempatan magang terpusat & transparan</span>
                        </li>
                        <li class="flex items-center gap-3 text-sm font-semibold text-slate-700">
                            <span class="flex h-5 w-5 shrink-0 items-center justify-center text-blue-600">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"/>
                                    <path d="m9 12 2 2 4-4"/>
                                </svg>
                            </span>
                            <span>Monitoring kehadiran & jurnal real-time</span>
                        </li>
                        <li class="flex items-center gap-3 text-sm font-semibold text-slate-700">
                            <span class="flex h-5 w-5 shrink-0 items-center justify-center text-blue-600">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"/>
                                    <path d="m9 12 2 2 4-4"/>
                                </svg>
                            </span>
                            <span>Koordinasi sekolah, guru, dan industri</span>
                        </li>
                    </ul>

                    <!-- Buttons -->
                    <div class="flex flex-wrap items-center gap-4 pt-3">
                        <a href="{{ route('login') }}" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white px-7 py-3.5 text-sm font-bold shadow-md shadow-blue-600/25 transition-all">
                            <span>Mulai Sekarang</span>
                            <i data-lucide="arrow-right" class="h-4 w-4"></i>
                        </a>
                        <a href="#features" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-800 px-7 py-3.5 text-sm font-bold shadow-xs transition-all">
                            <span>Lihat Fitur</span>
                        </a>
                    </div>
                </div>

                <!-- Right Column: Interactive Dashboard Mockup + Floating LIVE Badge + PWA Install Card -->
                <div class="lg:col-span-6 relative z-10 flex justify-center lg:justify-end">

                    <!-- Floating LIVE 324 Siswa Badge (Top Right of Mockup) -->
                    <div class="absolute -top-4 right-4 sm:right-6 z-20 rounded-2xl bg-[#1d4ed8] text-white px-5 py-2.5 shadow-xl border border-white/20 text-center">
                        <div class="flex items-center justify-center gap-1.5 text-[9px] font-bold text-blue-200 uppercase tracking-wider">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-400"></span>
                            </span>
                            <span>LIVE</span>
                        </div>
                        <p class="text-xs font-black text-white mt-0.5 tracking-wide">324 Siswa</p>
                    </div>

                    <!-- Mockup Window -->
                    <div class="relative w-full max-w-[540px] rounded-2xl border border-slate-200 bg-white shadow-2xl overflow-hidden">
                        <!-- Window Header with 3 colored dots -->
                        <div class="flex items-center gap-1.5 border-b border-slate-100 bg-white px-4 py-3">
                            <span class="h-2.5 w-2.5 rounded-full bg-rose-400"></span>
                            <span class="h-2.5 w-2.5 rounded-full bg-amber-400"></span>
                            <span class="h-2.5 w-2.5 rounded-full bg-emerald-400"></span>
                        </div>

                        <!-- Window Content: Mini Sidebar + Mini Content Area -->
                        <div class="grid grid-cols-12 min-h-[390px] bg-white">
                            <!-- Mini Left Sidebar -->
                            <div class="col-span-4 border-r border-slate-100 bg-white p-3 space-y-4">
                                <div class="flex items-center gap-2 px-1">
                                    <div class="flex h-6 w-6 items-center justify-center rounded-lg bg-blue-600 text-white text-[11px]">
                                        <i data-lucide="graduation-cap" class="h-3.5 w-3.5"></i>
                                    </div>
                                    <span class="font-black text-xs text-slate-800 tracking-tight">SIMMAS</span>
                                </div>

                                <div class="space-y-1 text-[10px]">
                                    <div class="flex items-center gap-2 rounded-lg bg-blue-50 px-2 py-1.5 font-bold text-blue-600">
                                        <i data-lucide="layout-dashboard" class="h-3 w-3"></i>
                                        <span>Beranda</span>
                                    </div>
                                    <div class="flex items-center gap-2 px-2 py-1 font-medium text-slate-500 hover:text-slate-800">
                                        <i data-lucide="users" class="h-3 w-3"></i>
                                        <span>Data Siswa</span>
                                    </div>
                                    <div class="flex items-center gap-2 px-2 py-1 font-medium text-slate-500 hover:text-slate-800">
                                        <i data-lucide="user-check" class="h-3 w-3"></i>
                                        <span>Data Guru</span>
                                    </div>
                                    <div class="flex items-center gap-2 px-2 py-1 font-medium text-slate-500 hover:text-slate-800">
                                        <i data-lucide="building-2" class="h-3 w-3"></i>
                                        <span>Mitra DUDI</span>
                                    </div>
                                    <div class="flex items-center gap-2 px-2 py-1 font-medium text-slate-500 hover:text-slate-800">
                                        <i data-lucide="file-text" class="h-3 w-3"></i>
                                        <span>Permohonan Magang</span>
                                    </div>
                                    <div class="flex items-center gap-2 px-2 py-1 font-medium text-slate-500 hover:text-slate-800">
                                        <i data-lucide="briefcase" class="h-3 w-3"></i>
                                        <span>Penempatan</span>
                                    </div>
                                    <div class="flex items-center gap-2 px-2 py-1 font-medium text-slate-500 hover:text-slate-800">
                                        <i data-lucide="clipboard-list" class="h-3 w-3"></i>
                                        <span>Laporan</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Mini Right Dashboard Area -->
                            <div class="col-span-8 p-3.5 space-y-3">
                                <!-- Top Bar -->
                                <div class="flex items-center justify-between pb-1.5 border-b border-slate-100">
                                    <span class="text-xs font-bold text-slate-800">Dashboard</span>
                                    <div class="flex items-center gap-1.5 text-[10px] text-slate-500">
                                        <span>Profile</span>
                                        <i data-lucide="chevron-down" class="h-2.5 w-2.5 text-slate-400"></i>
                                    </div>
                                </div>

                                <!-- 3 Stat Cards in Mockup -->
                                <div class="grid grid-cols-3 gap-2">
                                    <div class="rounded-xl border border-slate-200/80 bg-white p-2 text-left">
                                        <p class="text-[9px] font-semibold text-slate-400">Total Siswa</p>
                                        <p class="text-sm font-black text-slate-900 mt-0.5">324</p>
                                    </div>
                                    <div class="rounded-xl border border-slate-200/80 bg-white p-2 text-left">
                                        <p class="text-[9px] font-semibold text-slate-400">Total Guru</p>
                                        <p class="text-sm font-black text-slate-900 mt-0.5">45</p>
                                    </div>
                                    <div class="rounded-xl border border-slate-200/80 bg-white p-2 text-left">
                                        <p class="text-[9px] font-semibold text-slate-400">Mitra DUDI</p>
                                        <p class="text-sm font-black text-slate-900 mt-0.5">112</p>
                                    </div>
                                </div>

                                <!-- Mini Table: Daftar Permohonan Magang Siswa -->
                                <div class="space-y-1.5 pt-1">
                                    <p class="text-[10px] font-bold text-slate-700">Daftar Permohonan Magang Siswa</p>
                                    <div class="rounded-xl border border-slate-200/80 bg-white overflow-hidden text-[9px]">
                                        <div class="grid grid-cols-12 bg-slate-50 font-bold text-slate-400 uppercase py-1 px-2 border-b border-slate-100 text-[8px]">
                                            <span class="col-span-4">Nama Siswa</span>
                                            <span class="col-span-2 text-center">Kelas</span>
                                            <span class="col-span-4">Perusahaan Mitra</span>
                                            <span class="col-span-2 text-right">Status</span>
                                        </div>
                                        <div class="divide-y divide-slate-100">
                                            <div class="grid grid-cols-12 py-1 px-2 items-center text-slate-700">
                                                <span class="col-span-4 font-semibold text-slate-900 truncate">Nama Siswa</span>
                                                <span class="col-span-2 text-center font-mono">AK1</span>
                                                <span class="col-span-4 text-slate-500 truncate">Perusahaan Mitra</span>
                                                <span class="col-span-2 text-right">
                                                    <span class="inline-block rounded bg-emerald-50 px-1 py-0.5 text-[8px] font-bold text-emerald-700">Disetujui</span>
                                                </span>
                                            </div>
                                            <div class="grid grid-cols-12 py-1 px-2 items-center text-slate-700">
                                                <span class="col-span-4 font-semibold text-slate-900 truncate">Nama Dompet</span>
                                                <span class="col-span-2 text-center font-mono">AK2</span>
                                                <span class="col-span-4 text-slate-500 truncate">Perusahaan Mitra</span>
                                                <span class="col-span-2 text-right">
                                                    <span class="inline-block rounded bg-amber-50 px-1 py-0.5 text-[8px] font-bold text-amber-700">Menunggu</span>
                                                </span>
                                            </div>
                                            <div class="grid grid-cols-12 py-1 px-2 items-center text-slate-700">
                                                <span class="col-span-4 font-semibold text-slate-900 truncate">Aurah Fajar</span>
                                                <span class="col-span-2 text-center font-mono">AK3</span>
                                                <span class="col-span-4 text-slate-500 truncate">Perusahaan Mitra</span>
                                                <span class="col-span-2 text-right">
                                                    <span class="inline-block rounded bg-amber-50 px-1 py-0.5 text-[8px] font-bold text-amber-700">Menunggu</span>
                                                </span>
                                            </div>
                                            <div class="grid grid-cols-12 py-1 px-2 items-center text-slate-700">
                                                <span class="col-span-4 font-semibold text-slate-900 truncate">Nama Sektyar</span>
                                                <span class="col-span-2 text-center font-mono">AK4</span>
                                                <span class="col-span-4 text-slate-500 truncate">Perusahaan Mitra</span>
                                                <span class="col-span-2 text-right">
                                                    <span class="inline-block rounded bg-amber-50 px-1 py-0.5 text-[8px] font-bold text-amber-700">Menunggu</span>
                                                </span>
                                            </div>
                                            <div class="grid grid-cols-12 py-1 px-2 items-center text-slate-700">
                                                <span class="col-span-4 font-semibold text-slate-900 truncate">Ryan Giro</span>
                                                <span class="col-span-2 text-center font-mono">AK1</span>
                                                <span class="col-span-4 text-slate-500 truncate">Kakar Mitra</span>
                                                <span class="col-span-2 text-right">
                                                    <span class="inline-block rounded bg-emerald-50 px-1 py-0.5 text-[8px] font-bold text-emerald-700">Disetujui</span>
                                                </span>
                                            </div>
                                            <div class="grid grid-cols-12 py-1 px-2 items-center text-slate-700">
                                                <span class="col-span-4 font-semibold text-slate-900 truncate">Moch Aurelio</span>
                                                <span class="col-span-2 text-center font-mono">AK4</span>
                                                <span class="col-span-4 text-slate-500 truncate">Perusahaan Mitra</span>
                                                <span class="col-span-2 text-right">
                                                    <span class="inline-block rounded bg-emerald-50 px-1 py-0.5 text-[8px] font-bold text-emerald-700">Disetujui</span>
                                                </span>
                                            </div>
                                            <div class="grid grid-cols-12 py-1 px-2 items-center text-slate-700">
                                                <span class="col-span-4 font-semibold text-slate-900 truncate">Nadia Hinggarani</span>
                                                <span class="col-span-2 text-center font-mono">AK2</span>
                                                <span class="col-span-4 text-slate-500 truncate">Perusahaan Mitra</span>
                                                <span class="col-span-2 text-right">
                                                    <span class="inline-block rounded bg-emerald-50 px-1 py-0.5 text-[8px] font-bold text-emerald-700">Disetujui</span>
                                                </span>
                                            </div>
                                            <div class="grid grid-cols-12 py-1 px-2 items-center text-slate-700">
                                                <span class="col-span-4 font-semibold text-slate-900 truncate">Nurul Bustia</span>
                                                <span class="col-span-2 text-center font-mono">AK3</span>
                                                <span class="col-span-4 text-slate-500 truncate">Perusahaan Mitra</span>
                                                <span class="col-span-2 text-right">
                                                    <span class="inline-block rounded bg-amber-50 px-1 py-0.5 text-[8px] font-bold text-amber-700">Menunggu</span>
                                                </span>
                                            </div>
                                            <div class="grid grid-cols-12 py-1 px-2 items-center text-slate-700">
                                                <span class="col-span-4 font-semibold text-slate-900 truncate">Nana Devaya</span>
                                                <span class="col-span-2 text-center font-mono">AK1</span>
                                                <span class="col-span-4 text-slate-500 truncate">Perusahaan Mitra</span>
                                                <span class="col-span-2 text-right">
                                                    <span class="inline-block rounded bg-amber-50 px-1 py-0.5 text-[8px] font-bold text-amber-700">Menunggu</span>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Floating "Instal Aplikasi SIMMAS" Card (Matches Screenshot Bottom Right) -->
                    <div x-data="{ showPrompt: true }" x-show="showPrompt" x-cloak class="absolute -bottom-8 -right-2 sm:-right-6 z-30 w-80 sm:w-88 rounded-2xl bg-white shadow-2xl border border-slate-200/90 p-4 transition-all duration-300">
                        <button type="button" @click="showPrompt = false" class="absolute top-2.5 right-2.5 text-slate-400 hover:text-slate-600 p-1 cursor-pointer">
                            <i data-lucide="x" class="h-3.5 w-3.5"></i>
                        </button>

                        <div class="flex items-start gap-3">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm shadow-blue-600/30">
                                <i data-lucide="graduation-cap" class="h-6 w-6"></i>
                            </div>
                            <div class="min-w-0 pr-4">
                                <h4 class="text-xs font-bold text-slate-900">Instal Aplikasi SIMMAS</h4>
                                <p class="text-[11px] text-slate-500 leading-snug mt-0.5">
                                    Pasang di Desktop Anda untuk akses instan, pengalaman layar penuh, dan performa lebih lancar.
                                </p>
                            </div>
                        </div>

                        <div class="mt-3.5 flex items-center justify-end gap-2">
                            <button type="button" @click="alert('PWA Installer: Aplikasi SIMMAS siap dipasang.')" class="rounded-lg bg-blue-600 hover:bg-blue-700 px-3.5 py-1.5 text-[11px] font-bold text-white transition-colors flex items-center gap-1.5 shadow-xs cursor-pointer">
                                <i data-lucide="download" class="h-3 w-3"></i>
                                <span>Instal Sekarang</span>
                            </button>
                            <button type="button" @click="showPrompt = false" class="rounded-lg border border-transparent hover:bg-slate-100 px-3 py-1.5 text-[11px] font-semibold text-slate-500 transition-colors cursor-pointer">
                                Nanti Saja
                            </button>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </section>

                </div>

            </div>
        </div>
    </section>

    <!-- Section Fitur Platform: "Solusi untuk setiap peran." -->
    <section id="features" class="py-20 bg-slate-50/70 border-y border-slate-100">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto space-y-3">
                <span class="inline-flex items-center rounded-full bg-blue-50 border border-blue-200 px-3 py-1 text-xs font-bold uppercase tracking-wider text-blue-600">
                    FITUR PLATFORM
                </span>
                <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900">
                    Solusi untuk setiap peran.
                </h2>
                <p class="text-sm sm:text-base text-slate-600">
                    Dirancang spesifik untuk kebutuhan siswa peserta PKL, guru pembimbing lapangan, dan koordinator hubin sekolah.
                </p>
            </div>

            <div class="mt-12 grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Card 1: Siswa -->
                <div class="simmas-card p-7 space-y-4 hover:border-blue-300 transition-all">
                    <div class="flex items-center justify-between">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-50 text-blue-600 ring-1 ring-blue-100">
                            <i data-lucide="user-round" class="h-6 w-6"></i>
                        </div>
                        <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700">Siswa</span>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-blue-600">Jurnal & Kehadiran</p>
                        <h3 class="mt-1 text-xl font-bold text-slate-900">Pelaporan Harian Praktis</h3>
                        <p class="mt-2 text-sm text-slate-600 leading-relaxed">
                            Pengajuan tempat magang mandiri, presensi masuk & pulang dengan bukti foto selfie, serta pencatatan jurnal kegiatan harian secara terstruktur.
                        </p>
                    </div>
                </div>

                <!-- Card 2: Guru Pembimbing -->
                <div class="simmas-card p-7 space-y-4 hover:border-blue-300 transition-all">
                    <div class="flex items-center justify-between">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 ring-1 ring-emerald-100">
                            <i data-lucide="clipboard-check" class="h-6 w-6"></i>
                        </div>
                        <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">Guru Pembimbing</span>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-emerald-600">Monitoring Terpadu</p>
                        <h3 class="mt-1 text-xl font-bold text-slate-900">Evaluasi & Validasi Cepat</h3>
                        <p class="mt-2 text-sm text-slate-600 leading-relaxed">
                            Pantau kehadiran siswa bimbingan, validasi jurnal harian maupun surat izin/sakit, catat kunjungan lapangan DUDI, dan input nilai akhir magang.
                        </p>
                    </div>
                </div>

                <!-- Card 3: Admin Sekolah -->
                <div class="simmas-card p-7 space-y-4 hover:border-blue-300 transition-all">
                    <div class="flex items-center justify-between">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-violet-50 text-violet-600 ring-1 ring-violet-100">
                            <i data-lucide="building-2" class="h-6 w-6"></i>
                        </div>
                        <span class="rounded-full bg-violet-50 px-3 py-1 text-xs font-bold text-violet-700">Admin Sekolah</span>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-violet-600">Manajemen Penempatan</p>
                        <h3 class="mt-1 text-xl font-bold text-slate-900">Kendali Penuh & Audit Log</h3>
                        <p class="mt-2 text-sm text-slate-600 leading-relaxed">
                            Kelola data guru, siswa, kuota mitra DUDI, plotting penempatan otomatis, pengesahan magang, serta pemantauan statistik dan log aktivitas.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Section: "Akurasi data, setiap hari." + Ringkasan Mingguan -->
    <section class="py-20 bg-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                <div class="lg:col-span-6 space-y-5">
                    <span class="inline-flex items-center rounded-full bg-emerald-50 border border-emerald-200 px-3 py-1 text-xs font-bold uppercase tracking-wider text-emerald-700">
                        TRANSPARANSI REAL-TIME
                    </span>
                    <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900">
                        Akurasi data, setiap hari.
                    </h2>
                    <p class="text-base text-slate-600 leading-relaxed">
                        Setiap presensi masuk, pengumpulan laporan kegiatan, dan evaluasi pembimbing tercatat langsung ke basis data sekolah tanpa rekap manual.
                    </p>

                    <div class="space-y-4 pt-2">
                        <div class="flex items-start gap-3.5">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                                <i data-lucide="camera" class="h-5 w-5"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-slate-900">Verifikasi Bukti Foto Presensi</h4>
                                <p class="text-xs text-slate-500 mt-0.5">Memastikan kehadiran siswa di lokasi mitra industri maupun keabsahan surat keterangan sakit/izin.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3.5">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                                <i data-lucide="file-check-2" class="h-5 w-5"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-slate-900">Umpan Balik Jurnal Berjenjang</h4>
                                <p class="text-xs text-slate-500 mt-0.5">Guru pembimbing dapat menyetujui atau meminta revisi dengan catatan evaluasi yang jelas.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3.5">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-violet-50 text-violet-600">
                                <i data-lucide="shield-check" class="h-5 w-5"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-slate-900">Kontrol Kuota Mitra DUDI Otomatis</h4>
                                <p class="text-xs text-slate-500 mt-0.5">Mencegah penumpukan penempatan melebihi daya tampung perusahaan mitra.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kartu Ringkasan Mingguan (Database Real Metrics) -->
                <div class="lg:col-span-6">
                    <div class="simmas-card p-6 sm:p-8 bg-gradient-to-br from-slate-900 to-slate-800 text-white border-slate-800 shadow-xl">
                        <div class="flex items-center justify-between pb-6 border-b border-slate-700/80">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wider text-blue-400">METRIK SISTEM LANGSUNG</p>
                                <h3 class="text-xl font-extrabold mt-1">Ringkasan Mingguan</h3>
                            </div>
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/20 border border-emerald-400/30 px-3 py-1 text-xs font-semibold text-emerald-300">
                                <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                                Sinkronisasi Database
                            </span>
                        </div>

                        <div class="grid grid-cols-2 gap-4 mt-6">
                            <div class="rounded-2xl bg-slate-800/90 border border-slate-700 p-5">
                                <div class="flex items-center justify-between text-slate-400">
                                    <span class="text-xs font-semibold">Jurnal Disetujui</span>
                                    <i data-lucide="check-circle-2" class="h-4 w-4 text-emerald-400"></i>
                                </div>
                                <p class="text-3xl font-extrabold text-white mt-2">{{ $jurnalDisetujui }}</p>
                                <p class="text-[11px] text-slate-400 mt-1">Laporan kegiatan terverifikasi</p>
                            </div>

                            <div class="rounded-2xl bg-slate-800/90 border border-slate-700 p-5">
                                <div class="flex items-center justify-between text-slate-400">
                                    <span class="text-xs font-semibold">Presensi Tercatat</span>
                                    <i data-lucide="clock" class="h-4 w-4 text-blue-400"></i>
                                </div>
                                <p class="text-3xl font-extrabold text-white mt-2">{{ $presensiTercatat }}</p>
                                <p class="text-[11px] text-slate-400 mt-1">Absensi harian terkumpul</p>
                            </div>

                            <div class="rounded-2xl bg-slate-800/90 border border-slate-700 p-5">
                                <div class="flex items-center justify-between text-slate-400">
                                    <span class="text-xs font-semibold">Siswa Aktif</span>
                                    <i data-lucide="users" class="h-4 w-4 text-amber-400"></i>
                                </div>
                                <p class="text-3xl font-extrabold text-white mt-2">{{ $siswaAktif }}</p>
                                <p class="text-[11px] text-slate-400 mt-1">Terdaftar dalam program</p>
                            </div>

                            <div class="rounded-2xl bg-slate-800/90 border border-slate-700 p-5">
                                <div class="flex items-center justify-between text-slate-400">
                                    <span class="text-xs font-semibold">Total Mitra DUDI</span>
                                    <i data-lucide="building-2" class="h-4 w-4 text-violet-400"></i>
                                </div>
                                <p class="text-3xl font-extrabold text-white mt-2">{{ $totalMitraDudi }}</p>
                                <p class="text-[11px] text-slate-400 mt-1">Perusahaan mitra terverifikasi</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Section Cara Kerja: "Empat langkah, satu sistem." -->
    <section id="panduan" class="py-20 bg-slate-50/70 border-t border-slate-100">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto space-y-3">
                <span class="inline-flex items-center rounded-full bg-blue-50 border border-blue-200 px-3 py-1 text-xs font-bold uppercase tracking-wider text-blue-600">
                    ALUR PELAKSANAAN
                </span>
                <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900">
                    Empat langkah, satu sistem.
                </h2>
                <p class="text-sm sm:text-base text-slate-600">
                    Mulai dari pendataan perusahaan mitra hingga penilaian kelulusan magang siswa.
                </p>
            </div>

            <div class="mt-12 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="simmas-card p-6 relative">
                    <span class="font-mono text-2xl font-extrabold text-blue-600">01</span>
                    <h3 class="mt-3 text-base font-bold text-slate-900">Registrasi DUDI</h3>
                    <p class="mt-1.5 text-xs text-slate-600 leading-relaxed">
                        Admin mendata dan memverifikasi perusahaan mitra beserta kuota penerimaan siswa magang.
                    </p>
                </div>
                <div class="simmas-card p-6 relative">
                    <span class="font-mono text-2xl font-extrabold text-blue-600">02</span>
                    <h3 class="mt-3 text-base font-bold text-slate-900">Pengajuan Siswa</h3>
                    <p class="mt-1.5 text-xs text-slate-600 leading-relaxed">
                        Siswa mengajukan permohonan magang pada mitra DUDI terverifikasi atau di-plotting langsung oleh sekolah.
                    </p>
                </div>
                <div class="simmas-card p-6 relative">
                    <span class="font-mono text-2xl font-extrabold text-blue-600">03</span>
                    <h3 class="mt-3 text-base font-bold text-slate-900">Persetujuan & Surat</h3>
                    <p class="mt-1.5 text-xs text-slate-600 leading-relaxed">
                        Sekolah menetapkan guru pembimbing dan mengesahkan penempatan magang siswa.
                    </p>
                </div>
                <div class="simmas-card p-6 relative">
                    <span class="font-mono text-2xl font-extrabold text-blue-600">04</span>
                    <h3 class="mt-3 text-base font-bold text-slate-900">Monitoring</h3>
                    <p class="mt-1.5 text-xs text-slate-600 leading-relaxed">
                        Pelaksanaan presensi harian, pengisian jurnal, kunjungan industri, dan penilaian akhir.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Banner -->
    <section class="py-16 bg-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-3xl simmas-hero-gradient px-6 py-12 sm:px-12 sm:py-16 text-center text-white shadow-xl">
                <h2 class="text-2xl sm:text-4xl font-extrabold tracking-tight">
                    Siap untuk digitalisasi magang?
                </h2>
                <p class="mt-3 max-w-xl mx-auto text-sm sm:text-base text-blue-100">
                    Gunakan akun sekolah Anda untuk mulai mengelola penempatan, kehadiran, dan jurnal kegiatan siswa secara terpadu.
                </p>
                <div class="mt-8">
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-2 rounded-xl bg-white px-6 py-3.5 text-sm font-bold text-blue-600 shadow-lg hover:bg-blue-50 transition-all">
                        <span>Masuk ke Dashboard</span>
                        <i data-lucide="arrow-right" class="h-4 w-4"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="border-t border-slate-200 bg-slate-50 pt-14 pb-10 text-sm text-slate-600">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-8 pb-10 border-b border-slate-200">
                <div class="md:col-span-2 space-y-3">
                    <div class="flex items-center gap-2.5">
                        <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-blue-600 text-white">
                            <i data-lucide="graduation-cap" class="h-4 w-4"></i>
                        </div>
                        <span class="text-base font-extrabold text-slate-900">{{ $appName }}</span>
                    </div>
                    <p class="text-xs text-slate-500 max-w-sm leading-relaxed">
                        {{ $settings['app_description'] ?? $heroDesc }}
                    </p>
                    <p class="text-xs text-slate-400">
                        {{ $settings['school_name'] ?? 'SMK Negeri 1 Rekayasa' }}
                    </p>
                </div>

                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-900">Produk</h4>
                    <ul class="mt-3 space-y-2 text-xs">
                        <li><a href="#features" class="hover:text-blue-600">Fitur</a></li>
                        <li><a href="#panduan" class="hover:text-blue-600">Panduan</a></li>
                        <li><a href="{{ route('login') }}" class="hover:text-blue-600">Keamanan</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-900">Akses</h4>
                    <ul class="mt-3 space-y-2 text-xs">
                        <li><a href="{{ route('login') }}" class="hover:text-blue-600">Siswa</a></li>
                        <li><a href="{{ route('login') }}" class="hover:text-blue-600">Guru Pembimbing</a></li>
                        <li><a href="{{ route('login') }}" class="hover:text-blue-600">Administrator</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-900">Legal</h4>
                    <ul class="mt-3 space-y-2 text-xs">
                        <li><a href="#" class="hover:text-blue-600">Kebijakan Privasi</a></li>
                        <li><a href="#" class="hover:text-blue-600">Syarat & Ketentuan</a></li>
                    </ul>
                </div>
            </div>

            <div class="pt-6 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-400">
                <p>&copy; {{ date('Y') }} {{ $appName }} — {{ $appFullname }}. Hak cipta dilindungi.</p>
                <span class="font-mono text-[11px] bg-slate-200/70 text-slate-600 px-2.5 py-0.5 rounded-md">v2.4.0-ukk</span>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) {
                lucide.createIcons();
            }
        });
    </script>
</body>
</html>

