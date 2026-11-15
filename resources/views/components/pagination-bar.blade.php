@props([
    'paginator' => null,
    'total' => 0,
    'from' => 1,
    'to' => 1,
])

@if($paginator && method_exists($paginator, 'total'))
    <div class="flex flex-col sm:flex-row items-center justify-between gap-3 border-t border-slate-100 px-5 py-3.5 bg-slate-50/40 text-xs text-slate-500">
        <div class="flex items-center gap-4">
            <span>
                Menampilkan <strong class="font-semibold text-slate-700">{{ $paginator->firstItem() ?? 0 }}-{{ $paginator->lastItem() ?? 0 }}</strong> dari <strong class="font-semibold text-slate-700">{{ $paginator->total() }}</strong> data
            </span>
            <form method="GET" class="hidden sm:flex items-center gap-1.5">
                @foreach(request()->except(['per_page', 'page']) as $k => $v)
                    @if(is_scalar($v))
                        <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                    @endif
                @endforeach
                <span class="text-slate-400">|</span>
                <label for="per_page_select" class="text-slate-500">Baris per halaman:</label>
                <select
                    id="per_page_select"
                    name="per_page"
                    onchange="this.form.submit()"
                    class="rounded-lg border border-slate-200 bg-white px-2 py-1 text-xs font-semibold text-slate-700 focus:border-blue-600 focus:outline-none"
                >
                    @foreach([5, 10, 25, 50] as $size)
                        <option value="{{ $size }}" @selected((int) request('per_page', $paginator->perPage()) === $size)>{{ $size }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        @if($paginator->hasPages())
            <div class="flex items-center gap-1">
                @if($paginator->onFirstPage())
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-slate-100 text-slate-300 cursor-not-allowed">
                        <i data-lucide="chevron-left" class="h-4 w-4"></i>
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-50">
                        <i data-lucide="chevron-left" class="h-4 w-4"></i>
                    </a>
                @endif

                @foreach($paginator->getUrlRange(max(1, $paginator->currentPage() - 2), min($paginator->lastPage(), $paginator->currentPage() + 2)) as $page => $url)
                    @if($page == $paginator->currentPage())
                        <span class="inline-flex h-8 min-w-8 px-2 items-center justify-center rounded-lg bg-blue-600 text-white font-semibold">
                            {{ $page }}
                        </span>
                    @else
                        <a href="{{ $url }}" class="inline-flex h-8 min-w-8 px-2 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 font-medium">
                            {{ $page }}
                        </a>
                    @endif
                @endforeach

                @if($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-50">
                        <i data-lucide="chevron-right" class="h-4 w-4"></i>
                    </a>
                @else
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-slate-100 text-slate-300 cursor-not-allowed">
                        <i data-lucide="chevron-right" class="h-4 w-4"></i>
                    </span>
                @endif
            </div>
        @endif
    </div>
@else
    <div class="flex flex-col sm:flex-row items-center justify-between gap-3 border-t border-slate-100 px-5 py-3.5 bg-slate-50/40 text-xs text-slate-500">
        <span>
            Menampilkan <strong class="font-semibold text-slate-700">{{ $total > 0 ? $from : 0 }}-{{ $total > 0 ? $to : 0 }}</strong> dari <strong class="font-semibold text-slate-700">{{ $total }}</strong> data
        </span>
        <div class="flex items-center gap-2">
            <span>Baris per halaman: <strong class="text-slate-700">10</strong></span>
        </div>
    </div>
@endif

