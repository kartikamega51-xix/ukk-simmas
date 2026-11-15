@props([
    'name',
    'title' => 'Konfirmasi Tindakan',
    'description' => 'Apakah Anda yakin ingin melanjutkan tindakan ini?',
    'actionUrl' => '#',
    'method' => 'DELETE',
    'confirmText' => 'Ya, Hapus',
    'cancelText' => 'Batal',
    'variant' => 'danger', // danger (red) or warning (amber/orange)
    'icon' => 'alert-triangle',
])

@php
    $iconColors = $variant === 'warning'
        ? 'bg-amber-50 text-amber-600 ring-amber-100'
        : 'bg-rose-50 text-rose-600 ring-rose-100';
    $btnColors = $variant === 'warning'
        ? 'bg-amber-600 hover:bg-amber-700 text-white'
        : 'bg-rose-600 hover:bg-rose-700 text-white';
@endphp

<div
    x-data="{ open: false }"
    x-on:open-modal.window="if ($event.detail === '{{ $name }}') open = true"
    x-on:close-modal.window="if (!$event.detail || $event.detail === '{{ $name }}') open = false"
    x-on:keydown.escape.window="open = false"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
    role="alertdialog"
    aria-modal="true"
>
    <div
        x-show="open"
        x-transition.opacity
        @click="open = false"
        class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs"
    ></div>

    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl border border-slate-200 text-center"
    >
        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl ring-1 {{ $iconColors }}">
            <i data-lucide="{{ $icon }}" class="h-7 w-7"></i>
        </div>

        <h3 class="mt-4 text-lg font-bold text-slate-900">{{ $title }}</h3>
        <p class="mt-2 text-sm text-slate-500 leading-relaxed">{{ $description }}</p>

        {{ $slot }}

        <form method="POST" action="{{ $actionUrl }}" class="mt-6 flex items-center justify-center gap-3">
            @csrf
            @if(strtoupper($method) !== 'POST')
                @method($method)
            @endif
            <button
                type="button"
                @click="open = false"
                class="simmas-btn-secondary flex-1"
            >
                {{ $cancelText }}
            </button>
            <button
                type="submit"
                class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold transition-all cursor-pointer {{ $btnColors }}"
            >
                {{ $confirmText }}
            </button>
        </form>
    </div>
</div>

