@php
    $appName = $settings['app_name'] ?? 'SIMMAS';
    $appFullname = $settings['app_fullname'] ?? 'Sistem Informasi Manajemen Magang Siswa';
@endphp
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#2563eb">
    <title>Masuk — {{ $appName }}</title>

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
        html { font-family: 'Plus Jakarta Sans', sans-serif; }
        [x-cloak] { display: none !important; }
        .simmas-hero-gradient {
            background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 50%, #3b82f6 100%);
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
    </style>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
</head>
<body class="h-full bg-white text-slate-900 antialiased" x-data="{
    email: @js(old('email', '')),
    password: '',
    showPassword: false,
    isSubmitting: false,
    clientErrors: {},
    fillDemo(roleEmail) {
        this.email = roleEmail;
        this.password = 'password';
        this.clientErrors = {};
    },
    validateAndSubmit(e) {
        this.clientErrors = {};
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!this.email || !this.email.trim()) {
            this.clientErrors.email = 'Alamat email sekolah wajib diisi.';
        } else if (!emailRegex.test(this.email)) {
            this.clientErrors.email = 'Format alamat email tidak valid.';
        }
        if (!this.password) {
            this.clientErrors.password = 'Kata sandi wajib diisi.';
        } else if (this.password.length < 6) {
            this.clientErrors.password = 'Kata sandi minimal 6 karakter.';
        }
        if (Object.keys(this.clientErrors).length > 0) {
            e.preventDefault();
            return;
        }
        this.isSubmitting = true;
    }
}">

    <x-toast />

    <!-- Transition Loading Screen on Login Submit -->
    <div
        x-show="isSubmitting"
        x-transition.opacity
        x-cloak
        class="fixed inset-0 z-50 flex flex-col items-center justify-center bg-slate-900/75 backdrop-blur-sm text-white"
    >
        <div class="h-12 w-12 rounded-full border-4 border-blue-400 border-t-transparent animate-spin"></div>
        <p class="mt-4 text-sm font-bold tracking-wide">Memverifikasi kredensial & menyiapkan sesi...</p>
        <p class="mt-1 text-xs text-blue-200">Mohon tunggu sebentar</p>
    </div>

    <div class="min-h-screen grid grid-cols-1 lg:grid-cols-12">
        <!-- Left Panel: Blue Branding -->
        <div class="hidden lg:flex lg:col-span-6 relative overflow-hidden simmas-hero-gradient p-12 text-white flex-col justify-between">
            <!-- Decorative circles -->
            <div class="pointer-events-none absolute -top-24 -left-24 h-96 w-96 rounded-full bg-white/10 blur-2xl"></div>
            <div class="pointer-events-none absolute -bottom-32 -right-24 h-[420px] w-[420px] rounded-full bg-blue-400/20 blur-2xl"></div>
            <div class="pointer-events-none absolute top-1/3 right-12 h-40 w-40 rounded-full border border-white/15"></div>

            <!-- Top Logo -->
            <div class="relative z-10 flex items-center justify-between">
                <a href="{{ url('/') }}" class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white text-blue-600 shadow-md">
                        <i data-lucide="graduation-cap" class="h-6 w-6"></i>
                    </div>
                    <div>
                        <span class="text-lg font-extrabold tracking-tight">{{ $appName }}</span>
                        <span class="block text-[11px] text-blue-100">{{ $appFullname }}</span>
                    </div>
                </a>
            </div>

            <!-- Center Content -->
            <div class="relative z-10 space-y-6 my-auto py-12">
                <span class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3.5 py-1 text-xs font-bold uppercase tracking-wider text-blue-50 backdrop-blur-xs">
                    SISTEM MANAJEMEN PKL TERPADU
                </span>
                <h1 class="text-4xl xl:text-5xl font-extrabold tracking-tight leading-tight">
                    Magang lebih teratur.
                </h1>
                <p class="text-base text-blue-100 leading-relaxed max-w-lg">
                    Kelola penempatan mitra industri, presensi foto harian, buku jurnal kegiatan, serta evaluasi guru pembimbing dalam satu portal terintegrasi.
                </p>

                <ul class="space-y-3 pt-2 text-sm text-blue-50">
                    <li class="flex items-center gap-3">
                        <span class="flex h-5 w-5 items-center justify-center rounded-full bg-white/20">
                            <i data-lucide="check" class="h-3.5 w-3.5"></i>
                        </span>
                        <span>Penempatan magang terpusat & transparan</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="flex h-5 w-5 items-center justify-center rounded-full bg-white/20">
                            <i data-lucide="check" class="h-3.5 w-3.5"></i>
                        </span>
                        <span>Monitoring kehadiran & jurnal real-time</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="flex h-5 w-5 items-center justify-center rounded-full bg-white/20">
                            <i data-lucide="check" class="h-3.5 w-3.5"></i>
                        </span>
                        <span>Koordinasi sekolah, guru, dan industri</span>
                    </li>
                </ul>
            </div>

            <!-- Bottom Stats -->
            <div class="relative z-10 grid grid-cols-3 gap-4 border-t border-white/20 pt-8">
                <div>
                    <p class="text-2xl font-extrabold">50+</p>
                    <p class="text-xs text-blue-100 mt-0.5">SMK Aktif</p>
                </div>
                <div>
                    <p class="text-2xl font-extrabold">10k+</p>
                    <p class="text-xs text-blue-100 mt-0.5">Siswa Terdaftar</p>
                </div>
                <div>
                    <p class="text-2xl font-extrabold">200+</p>
                    <p class="text-xs text-blue-100 mt-0.5">Mitra DUDI</p>
                </div>
            </div>
        </div>

        <!-- Right Panel: Login Form -->
        <div class="lg:col-span-6 flex flex-col justify-center px-6 py-12 sm:px-12 lg:px-16 xl:px-24 bg-white">
            <div class="mx-auto w-full max-w-md space-y-7">
                <!-- Mobile Logo -->
                <div class="flex items-center justify-between lg:hidden pb-2">
                    <a href="{{ url('/') }}" class="flex items-center gap-2.5">
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-600 text-white">
                            <i data-lucide="graduation-cap" class="h-5 w-5"></i>
                        </div>
                        <span class="text-lg font-extrabold text-slate-900">{{ $appName }}</span>
                    </a>
                    <a href="{{ url('/') }}" class="text-xs font-semibold text-slate-500 hover:text-blue-600">← Beranda</a>
                </div>

                <div>
                    <span class="text-xs font-bold uppercase tracking-widest text-blue-600">PORTAL MASUK</span>
                    <h2 class="mt-1.5 text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">
                        Masuk ke akun Anda.
                    </h2>
                    <p class="mt-1.5 text-sm text-slate-500">
                        Gunakan email sekolah yang terdaftar untuk melanjutkan ke dashboard {{ $appName }}.
                    </p>
                </div>

                @if($errors->any())
                    <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-xs text-rose-700 flex items-start gap-3">
                        <i data-lucide="alert-circle" class="h-4 w-4 text-rose-600 shrink-0 mt-0.5"></i>
                        <div class="space-y-1">
                            @foreach($errors->all() as $err)
                                <p class="font-semibold">{{ $err }}</p>
                            @endforeach
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('login.submit') }}" @submit="validateAndSubmit" novalidate class="space-y-5">
                    @csrf

                    <!-- Email Sekolah -->
                    <div>
                        <label for="email" class="block text-xs font-bold text-slate-700 mb-1.5">Email Sekolah</label>
                        <div class="relative">
                            <input
                                id="email"
                                name="email"
                                type="email"
                                x-model="email"
                                required
                                autocomplete="email"
                                placeholder="nama@simmas.sch.id"
                                class="simmas-input pl-10"
                                :class="(clientErrors.email || {{ $errors->has('email') ? 'true' : 'false' }}) ? 'border-rose-400 focus:border-rose-500' : ''"
                            >
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                <i data-lucide="mail" class="h-4 w-4"></i>
                            </span>
                        </div>
                        <p x-show="clientErrors.email" x-text="clientErrors.email" x-cloak class="mt-1.5 text-xs font-medium text-rose-600"></p>
                        @error('email')
                            <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="password" class="block text-xs font-bold text-slate-700">Password</label>
                            <a href="#demo-accounts" @click.prevent="$dispatch('notify', { type: 'info', title: 'Lupa Password?', message: 'Silakan hubungi Administrator Sekolah untuk mereset password akun Anda.' })" class="text-xs font-semibold text-blue-600 hover:text-blue-700">
                                Lupa?
                            </a>
                        </div>
                        <div class="relative">
                            <input
                                id="password"
                                name="password"
                                :type="showPassword ? 'text' : 'password'"
                                x-model="password"
                                required
                                minlength="6"
                                autocomplete="current-password"
                                placeholder="Minimal 6 karakter"
                                class="simmas-input pl-10 pr-10"
                                :class="(clientErrors.password || {{ $errors->has('password') ? 'true' : 'false' }}) ? 'border-rose-400 focus:border-rose-500' : ''"
                            >
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                <i data-lucide="lock" class="h-4 w-4"></i>
                            </span>
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-slate-600 cursor-pointer"
                            >
                                <span x-show="!showPassword"><i data-lucide="eye" class="h-4 w-4"></i></span>
                                <span x-show="showPassword" x-cloak><i data-lucide="eye-off" class="h-4 w-4"></i></span>
                            </button>
                        </div>
                        <p x-show="clientErrors.password" x-text="clientErrors.password" x-cloak class="mt-1.5 text-xs font-medium text-rose-600"></p>
                        @error('password')
                            <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="simmas-btn-primary w-full py-3 text-sm">
                        <span>Masuk Dashboard</span>
                        <i data-lucide="arrow-right" class="h-4 w-4"></i>
                    </button>
                </form>

                <!-- Seksi AKUN DEMO -->
                <div id="demo-accounts" class="pt-4 border-t border-slate-200 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">AKUN DEMO</span>
                        <span class="text-[11px] text-slate-400">Klik untuk isi otomatis</span>
                    </div>

                    <div class="grid grid-cols-1 gap-2">
                        <button
                            type="button"
                            @click="fillDemo('admin@simmas.sch.id')"
                            class="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50/70 px-3.5 py-2.5 text-left hover:border-blue-300 hover:bg-blue-50/40 transition-all cursor-pointer"
                        >
                            <div class="flex items-center gap-2.5">
                                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-violet-100 text-violet-700 text-xs font-bold">A</span>
                                <span class="font-mono text-xs font-medium text-slate-700">admin@simmas.sch.id</span>
                            </div>
                            <span class="rounded-md bg-violet-100 px-2 py-0.5 text-[10px] font-extrabold uppercase tracking-wider text-violet-700">ADMIN</span>
                        </button>

                        <button
                            type="button"
                            @click="fillDemo('guru@simmas.sch.id')"
                            class="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50/70 px-3.5 py-2.5 text-left hover:border-blue-300 hover:bg-blue-50/40 transition-all cursor-pointer"
                        >
                            <div class="flex items-center gap-2.5">
                                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700 text-xs font-bold">G</span>
                                <span class="font-mono text-xs font-medium text-slate-700">guru@simmas.sch.id</span>
                            </div>
                            <span class="rounded-md bg-emerald-100 px-2 py-0.5 text-[10px] font-extrabold uppercase tracking-wider text-emerald-700">GURU</span>
                        </button>

                        <button
                            type="button"
                            @click="fillDemo('siswa@simmas.sch.id')"
                            class="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50/70 px-3.5 py-2.5 text-left hover:border-blue-300 hover:bg-blue-50/40 transition-all cursor-pointer"
                        >
                            <div class="flex items-center gap-2.5">
                                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-100 text-blue-700 text-xs font-bold">S</span>
                                <span class="font-mono text-xs font-medium text-slate-700">siswa@simmas.sch.id</span>
                            </div>
                            <span class="rounded-md bg-blue-100 px-2 py-0.5 text-[10px] font-extrabold uppercase tracking-wider text-blue-700">SISWA</span>
                        </button>
                    </div>
                </div>
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
</body>
</html>

