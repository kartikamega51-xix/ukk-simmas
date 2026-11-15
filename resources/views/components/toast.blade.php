@php
    $toastSuccess = session('toast_success') ?? session('success');
    $toastError = session('toast_error') ?? session('error');
    $validationError = $errors->any() ? $errors->first() : null;
@endphp

<div
    x-data="{
        toasts: [],
        add(type, title, message) {
            const id = Date.now() + Math.random();
            this.toasts.push({ id, type, title, message });
            setTimeout(() => this.remove(id), 4500);
        },
        remove(id) {
            this.toasts = this.toasts.filter(t => t.id !== id);
        }
    }"
    x-init="
        @if($toastSuccess)
            add('success', 'Berhasil', @js($toastSuccess));
        @endif
        @if($toastError)
            add('error', 'Gagal', @js($toastError));
        @elseif($validationError)
            add('error', 'Validasi Gagal', @js($validationError));
        @endif
    "
    @notify.window="add($event.detail.type || 'success', $event.detail.title || 'Informasi', $event.detail.message || '')"
    class="fixed top-5 right-5 z-50 flex flex-col gap-2.5 w-full max-w-sm pointer-events-none"
>
    <template x-for="toast in toasts" :key="toast.id">
        <div
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-x-6"
            x-transition:enter-end="opacity-100 translate-x-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-x-0"
            x-transition:leave-end="opacity-0 translate-x-6"
            class="pointer-events-auto flex items-start gap-3 rounded-2xl bg-white p-4 shadow-xl border border-slate-200"
        >
            <div
                :class="toast.type === 'error' ? 'bg-rose-50 text-rose-600 ring-rose-100' : 'bg-emerald-50 text-emerald-600 ring-emerald-100'"
                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl ring-1"
            >
                <svg x-show="toast.type !== 'error'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                <svg x-show="toast.type === 'error'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M12 3a9 9 0 100 18 9 9 0 000-18z"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-bold text-slate-900" x-text="toast.title"></p>
                <p class="mt-0.5 text-xs text-slate-600 leading-relaxed" x-text="toast.message"></p>
            </div>
            <button @click="remove(toast.id)" class="text-slate-400 hover:text-slate-600 p-1 cursor-pointer">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </template>
</div>

