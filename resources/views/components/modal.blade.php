@props([
    'name',
    'title' => '',
    'subtitle' => null,
    'icon' => 'layers',
    'iconColor' => 'blue', // blue, emerald, amber, rose, violet
    'maxWidth' => 'lg', // sm, md, lg, xl, 2xl
])

@php
    $widths = [
        'sm' => 'max-w-sm',
        'md' => 'max-w-md',
        'lg' => 'max-w-lg',
        'xl' => 'max-w-xl',
        '2xl' => 'max-w-2xl',
    ];
    $icons = [
        'blue' => 'bg-blue-50 text-blue-600 ring-blue-100',
        'emerald' => 'bg-emerald-50 text-emerald-600 ring-emerald-100',
        'amber' => 'bg-amber-50 text-amber-600 ring-amber-100',
        'rose' => 'bg-rose-50 text-rose-600 ring-rose-100',
        'violet' => 'bg-violet-50 text-violet-600 ring-violet-100',
    ];
    $maxWidthClass = $widths[$maxWidth] ?? $widths['lg'];
    $iconClass = $icons[$iconColor] ?? $icons['blue'];
@endphp

<div
    x-data="{ open: false }"
    x-on:open-modal.window="if ($event.detail === '{{ $name }}' || $event.detail?.name === '{{ $name }}') open = true"
    x-on:close-modal.window="if (!$event.detail || $event.detail === '{{ $name }}') open = false"
    x-on:keydown.escape.window="open = false"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto"
    role="dialog"
    aria-modal="true"
>
    <!-- Backdrop -->
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="open = false"
        class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs"
    ></div>

    <!-- Modal Panel -->
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95 translate-y-2"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 translate-y-2"
        class="relative w-full {{ $maxWidthClass }} rounded-2xl bg-white shadow-2xl border border-slate-200 overflow-hidden my-8"
    >
        @if($title)
            <div class="flex items-start justify-between gap-4 border-b border-slate-100 px-6 py-4 bg-slate-50/60">
                <div class="flex items-center gap-3.5">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl ring-1 {{ $iconClass }}">
                        <i data-lucide="{{ $icon }}" class="h-5 w-5"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">{{ $title }}</h3>
                        @if($subtitle)
                            <p class="text-xs text-slate-500 mt-0.5">{{ $subtitle }}</p>
                        @endif
                    </div>
                </div>
                <button
                    type="button"
                    @click="open = false"
                    class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors cursor-pointer"
                >
                    <i data-lucide="x" class="h-4 w-4"></i>
                </button>
            </div>
        @endif

        <div class="p-6 max-h-[80vh] overflow-y-auto">
            {{ $slot }}
        </div>
    </div>
</div>

