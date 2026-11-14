<x-dashboard-layout title="Audit Trail & Log Aktivitas" breadcrumb="Audit Log">
    <div class="space-y-8" x-data="{ 
        detailModalOpen: false, 
        selectedLog: null,
        openDetail(log) {
            this.selectedLog = log;
            this.detailModalOpen = true;
        }
    }">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900">Audit Trail Sistem</h1>
                <p class="text-slate-500 text-sm mt-1">Rekaman jejak aktivitas perubahan data kritis (Create, Update, Delete) oleh seluruh pengguna.</p>
            </div>
        </div>

        <!-- Filter -->
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <form method="GET" action="{{ route('audit.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <div class="lg:col-span-2">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari entitas model, IP, atau nama user..." 
                           class="w-full px-4 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-slate-900 focus:outline-none">
                </div>

                <div>
                    <select name="event" class="w-full px-4 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-slate-900 focus:outline-none">
                        <option value="">Semua Peristiwa (Event)</option>
                        @foreach($events as $ev)
                            <option value="{{ $ev }}" {{ request('event') === $ev ? 'selected' : '' }}>
                                {{ ucfirst($ev) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="flex-1 py-2 bg-slate-900 text-white rounded-xl text-xs font-bold hover:bg-slate-800 transition">
                        Filter
                    </button>
                    <a href="{{ route('audit.index') }}" class="px-3 py-2 bg-slate-100 text-slate-600 rounded-xl text-xs font-bold hover:bg-slate-200 transition">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
            @if($auditLogs->isEmpty())
                <div class="py-16 text-center text-slate-400">
                    <p class="text-base font-bold text-slate-700">Tidak ada log aktivitas tercatat.</p>
                    <p class="text-sm mt-1">Aktivitas penambahan, perubahan, dan penghapusan data akan muncul di sini secara otomatis.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-[11px] uppercase tracking-wider text-slate-500 font-bold">
                                <th class="py-3.5 px-6">Waktu Kejadian</th>
                                <th class="py-3.5 px-6">Aktor (Pengguna)</th>
                                <th class="py-3.5 px-6">Peristiwa</th>
                                <th class="py-3.5 px-6">Entitas Target (Model)</th>
                                <th class="py-3.5 px-6 font-mono">IP Address</th>
                                <th class="py-3.5 px-6 text-right">Rincian Perubahan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @foreach($auditLogs as $log)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-4 px-6 font-mono text-xs text-slate-600 whitespace-nowrap">
                                        {{ \App\Support\Timezone::format($log->created_at, 'd M Y, H:i:s') }}
                                    </td>
                                    <td class="py-4 px-6">
                                        @if($log->user)
                                            <p class="font-bold text-slate-900 text-xs">{{ $log->user->name }}</p>
                                            <p class="text-[11px] text-slate-400 font-mono">{{ $log->user->email }}</p>
                                        @else
                                            <span class="text-xs text-slate-400 italic">Sistem / Tamu</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="inline-block px-2.5 py-0.5 rounded text-[11px] font-bold uppercase tracking-wider {{ $log->event === 'created' ? 'bg-emerald-100 text-emerald-800' : ($log->event === 'updated' ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800') }}">
                                            {{ $log->event }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-xs font-mono text-slate-700">
                                        <span class="font-bold text-slate-900">{{ class_basename($log->auditable_type) }}</span>
                                        <span class="text-slate-400">#{{ $log->auditable_id }}</span>
                                    </td>
                                    <td class="py-4 px-6 font-mono text-xs text-slate-500">
                                        {{ $log->ip_address ?? '127.0.0.1' }}
                                    </td>
                                    <td class="py-4 px-6 text-right">
                                        <button type="button" 
                                                @click="openDetail({{ $log->toJson() }})"
                                                class="inline-flex items-center px-3 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold transition">
                                            Lihat Diff JSON
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-6 border-t border-slate-100">
                    {{ $auditLogs->links() }}
                </div>
            @endif
        </div>

        <!-- Diff JSON Detail Modal -->
        <div x-show="detailModalOpen" 
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4"
             @keydown.escape.window="detailModalOpen = false">
            <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="detailModalOpen = false"></div>
            <div class="relative w-full max-w-3xl rounded-3xl bg-white border border-slate-200 shadow-2xl p-6 sm:p-8 overflow-hidden max-h-[85vh] flex flex-col"
                 x-transition:enter="transition ease-out duration-300 transform"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div>
                        <h3 class="font-black text-lg text-slate-900">Perbedaan Nilai Data (Audit Diff)</h3>
                        <p class="text-xs text-slate-500 font-mono mt-0.5" 
                           x-text="selectedLog ? selectedLog.event.toUpperCase() + ' ' + selectedLog.auditable_type + ' (ID: ' + selectedLog.auditable_id + ')' : ''"></p>
                    </div>
                    <button @click="detailModalOpen = false" class="p-2 text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
                </div>

                <div class="flex-1 overflow-y-auto py-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Old Values -->
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-red-600 block mb-2">&minus; Nilai Sebelumnya (Old)</span>
                        <pre class="bg-red-50/60 border border-red-200 text-red-950 p-4 rounded-2xl text-xs font-mono overflow-x-auto whitespace-pre-wrap max-h-96" 
                             x-text="selectedLog && selectedLog.old_values ? JSON.stringify(selectedLog.old_values, null, 2) : 'Tidak ada nilai sebelumnya (Baru dibuat)'"></pre>
                    </div>

                    <!-- New Values -->
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-emerald-600 block mb-2">&plus; Nilai Baru (New)</span>
                        <pre class="bg-emerald-50/60 border border-emerald-200 text-emerald-950 p-4 rounded-2xl text-xs font-mono overflow-x-auto whitespace-pre-wrap max-h-96" 
                             x-text="selectedLog && selectedLog.new_values ? JSON.stringify(selectedLog.new_values, null, 2) : 'Tidak ada nilai baru (Dihapus)'"></pre>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end">
                    <button type="button" @click="detailModalOpen = false" class="px-5 py-2.5 rounded-xl font-bold text-sm bg-slate-900 text-white hover:bg-slate-800">
                        Tutup Rincian
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-dashboard-layout>
