<x-layouts.app title="Log Aktivitas & Audit" subtitle="Catatan append-only untuk seluruh aktivitas autentikasi dan perubahan data penting">

    <div x-data="{
        selectedLog: null,
        openMetadata(log) {
            this.selectedLog = log;
            this.$dispatch('open-modal', 'metadata-log-modal');
        }
    }" class="space-y-6">

        <div class="simmas-card overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <form method="GET" action="{{ route('admin.logs') }}" class="flex flex-1 flex-wrap items-center gap-3">
                    <div class="relative flex-1 min-w-[220px] max-w-md">
                        <input
                            type="text"
                            name="q"
                            value="{{ $search }}"
                            placeholder="Cari tipe aksi, aktor, target, atau IP..."
                            class="simmas-input pl-10"
                        >
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                            <i data-lucide="search" class="h-4 w-4"></i>
                        </span>
                    </div>

                    <select name="level" onchange="this.form.submit()" class="simmas-input w-auto min-w-[140px]">
                        <option value="">Semua Level</option>
                        <option value="INFO" @selected($levelFilter === 'INFO')>INFO</option>
                        <option value="WARN" @selected($levelFilter === 'WARN')>WARN</option>
                        <option value="ERROR" @selected($levelFilter === 'ERROR')>ERROR</option>
                    </select>

                    <button type="submit" class="simmas-btn-secondary">Filter</button>
                </form>

                <button
                    type="button"
                    @click="$dispatch('open-modal', 'clear-logs-modal')"
                    class="simmas-btn-danger shrink-0"
                >
                    <i data-lucide="trash-2" class="h-4 w-4"></i>
                    <span>Bersihkan Log</span>
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr>
                            <th class="simmas-table-th">Waktu</th>
                            <th class="simmas-table-th">Level</th>
                            <th class="simmas-table-th">Aksi</th>
                            <th class="simmas-table-th">Aktor</th>
                            <th class="simmas-table-th">IP Address</th>
                            <th class="simmas-table-th text-right">Metadata</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            @php
                                $lvlVariant = match($log->level) {
                                    'WARN' => 'amber',
                                    'ERROR' => 'rose',
                                    default => 'blue',
                                };
                            @endphp
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="simmas-table-td font-mono text-xs text-slate-500 whitespace-nowrap">
                                    {{ $log->timestamp?->format('d M Y H:i:s') }}
                                </td>
                                <td class="simmas-table-td">
                                    <x-badge :variant="$lvlVariant">{{ $log->level }}</x-badge>
                                </td>
                                <td class="simmas-table-td">
                                    <p class="font-mono text-xs font-bold text-slate-900">{{ $log->action_type }}</p>
                                    <p class="text-xs text-slate-500 mt-0.5">Target: {{ $log->target }}</p>
                                </td>
                                <td class="simmas-table-td">
                                    <p class="text-xs font-semibold text-slate-800">{{ $log->actor_email }}</p>
                                    <span class="rounded bg-slate-100 px-1.5 py-0.5 text-[10px] font-bold uppercase text-slate-600">{{ $log->actor_role }}</span>
                                </td>
                                <td class="simmas-table-td font-mono text-xs text-slate-500">
                                    {{ $log->ip_address }}
                                </td>
                                <td class="simmas-table-td text-right">
                                    <button
                                        type="button"
                                        @click="openMetadata(@js([
                                            'action_type' => $log->action_type,
                                            'timestamp' => $log->timestamp?->format('d M Y H:i:s'),
                                            'actor' => $log->actor_email . ' (' . $log->actor_role . ')',
                                            'ip' => $log->ip_address,
                                            'metadata' => json_encode($log->metadata ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
                                        ]))"
                                        class="simmas-btn-secondary py-1.5 px-3 text-xs font-mono"
                                    >
                                        <i data-lucide="code-2" class="h-3.5 w-3.5"></i>
                                        <span>Metadata</span>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <x-empty-state icon="shield-alert" title="Log aktivitas kosong" description="Belum ada catatan audit atau filter tidak menemukan hasil." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-pagination-bar :paginator="$logs" />
        </div>

        <!-- Modal Metadata JSON -->
        <x-modal name="metadata-log-modal" title="Detail Metadata Log Aktivitas" subtitle="Payload JSON lengkap dari peristiwa sistem" icon="code-2" maxWidth="lg">
            <template x-if="selectedLog">
                <div class="space-y-4">
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3.5 text-xs space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="font-mono font-bold text-blue-600" x-text="selectedLog.action_type"></span>
                            <span class="font-mono text-slate-400" x-text="selectedLog.timestamp"></span>
                        </div>
                        <p class="text-slate-600">Aktor: <strong x-text="selectedLog.actor"></strong> • IP: <span class="font-mono" x-text="selectedLog.ip"></span></p>
                    </div>

                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">JSON Payload</p>
                        <pre class="rounded-xl bg-slate-900 text-emerald-300 p-4 font-mono text-xs overflow-x-auto leading-relaxed" x-text="selectedLog.metadata"></pre>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="button" @click="$dispatch('close-modal', 'metadata-log-modal')" class="simmas-btn-primary">
                            Tutup
                        </button>
                    </div>
                </div>
            </template>
        </x-modal>

        <!-- Alert Konfirmasi Bersihkan Log -->
        <x-alert-dialog
            name="clear-logs-modal"
            title="Bersihkan Seluruh Log Aktivitas?"
            description="Tindakan ini akan menghapus seluruh riwayat catatan audit yang tersimpan saat ini dan mencatat aksi pembersihan baru."
            :actionUrl="route('admin.logs.clear')"
            method="DELETE"
            confirmText="Ya, Bersihkan Log"
            variant="danger"
        />

    </div>

</x-layouts.app>

