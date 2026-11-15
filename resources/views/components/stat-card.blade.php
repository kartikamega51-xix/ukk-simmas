@props([
    'label' => '',
    'value' => '0',
    'subtitle' => null,
    'icon' => 'bar-chart-3',
    'color' => 'blue', // blue, emerald, amber, rose, violet, slate
    'badge' => null,
])

@php
    $colorMap = [
        'blue' => [
            'bg' => 'bg-blue-50 text-blue-600 ring-blue-100',
            'badge' => 'bg-blue-50 text-blue-700 border-blue-200',
        ],
        'emerald' => [
            'bg' => 'bg-emerald-50 text-emerald-600 ring-emerald-100',
            'badge' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        ],
        'amber' => [
            'bg' => 'bg-amber-50 text-amber-600 ring-amber-100',
            'badge' => 'bg-amber-50 text-amber-700 border-amber-200',
        ],
        'rose' => [
            'bg' => 'bg-rose-50 text-rose-600 ring-rose-100',
            'badge' => 'bg-rose-50 text-rose-700 border-rose-200',
        ],
        'violet' => [
            'bg' => 'bg-violet-50 text-violet-600 ring-violet-100',
            'badge' => 'bg-violet-50 text-violet-700 border-violet-200',
        ],
        'slate' => [
            'bg' => 'bg-slate-100 text-slate-600 ring-slate-200',
            'badge' => 'bg-slate-100 text-slate-700 border-slate-200',
        ],
    ];
    $style = $colorMap[$color] ?? $colorMap['blue'];
@endphp

<div {{ $attributes->merge(['class' => 'simmas-card p-5 transition-all duration-200 hover:shadow-md']) }}>
    <div class="flex items-start justify-between gap-4">
        <div class="space-y-1.5 min-w-0 flex-1">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ $label }}</p>
            <div class="flex items-baseline gap-2.5">
                <h3 class="text-2xl font-bold tracking-tight text-slate-900">{{ $value }}</h3>
                @if($badge)
                    <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold {{ $style['badge'] }}">
                        {{ $badge }}
                    </span>
                @endif
            </div>
            @if($subtitle)
                <p class="text-xs text-slate-500 truncate">{{ $subtitle }}</p>
            @endif
        </div>
        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl ring-1 {{ $style['bg'] }}">
            <i data-lucide="{{ $icon }}" class="h-5 w-5"></i>
        </div>
    </div>
    @if(!$slot->isEmpty())
        <div class="mt-3 pt-3 border-t border-slate-100">
            {{ $slot }}
        </div>
    @endif
</div>

