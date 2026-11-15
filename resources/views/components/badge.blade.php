@props([
    'variant' => 'slate', // blue, emerald, amber, rose, violet, slate, mono
    'dot' => false,
])

@php
    $variants = [
        'blue' => 'bg-blue-50 text-blue-700 border-blue-200',
        'emerald' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'amber' => 'bg-amber-50 text-amber-700 border-amber-200',
        'rose' => 'bg-rose-50 text-rose-700 border-rose-200',
        'violet' => 'bg-violet-50 text-violet-700 border-violet-200',
        'slate' => 'bg-slate-100 text-slate-700 border-slate-200',
        'mono' => 'bg-slate-100 text-slate-700 border-slate-200 font-mono',
    ];
    $dots = [
        'blue' => 'bg-blue-500',
        'emerald' => 'bg-emerald-500',
        'amber' => 'bg-amber-500',
        'rose' => 'bg-rose-500',
        'violet' => 'bg-violet-500',
        'slate' => 'bg-slate-400',
        'mono' => 'bg-slate-500',
    ];
    $cls = $variants[$variant] ?? $variants['slate'];
    $dotCls = $dots[$variant] ?? $dots['slate'];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-xs font-semibold {$cls}"]) }}>
    @if($dot)
        <span class="h-1.5 w-1.5 rounded-full {{ $dotCls }}"></span>
    @endif
    {{ $slot }}
</span>

