@props([
    'icon' => 'inbox',
    'title' => 'Tidak ada data ditemukan',
    'description' => 'Belum ada data yang tercatat atau sesuai dengan filter pencarian Anda.',
])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center py-12 px-4 text-center']) }}>
    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 ring-1 ring-slate-200/60">
        <i data-lucide="{{ $icon }}" class="h-7 w-7"></i>
    </div>
    <h4 class="mt-4 text-sm font-bold text-slate-800">{{ $title }}</h4>
    <p class="mt-1 max-w-sm text-xs text-slate-500 leading-relaxed">{{ $description }}</p>
    @if(!$slot->isEmpty())
        <div class="mt-4">
            {{ $slot }}
        </div>
    @endif
</div>

