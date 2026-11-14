<x-dashboard-layout title="Pelacakan Servis {{ $service->ticket_number }}" breadcrumb="Pelacakan Servis">
    {{-- 
        Live Service Status Tracker (PRD §7 & Prompt 4)
        Auto-refresh polling 30 detik via Alpine.js
        Upgrade path:
        Echo.private('services.{{ $service->id }}')
            .listen('ServiceStatusChanged', (e) => {
                // Reactive instant update
            });
    --}}
    <div x-data="serviceTracker({{ $service->id }}, {{ $service->progressPercent() }}, '{{ $service->status->value }}')" 
         x-init="initTracker()"
         class="space-y-8 max-w-5xl mx-auto">

        <!-- Top Header & Back -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <a href="{{ route('services.my') }}" class="text-xs font-bold text-slate-500 hover:text-slate-900 inline-flex items-center gap-1.5 mb-2 transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>Kembali ke Daftar Servis</span>
                </a>
                <div class="flex flex-wrap items-center gap-3">
                    <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 font-mono">{{ $service->ticket_number }}</h1>
                    <span :class="currentStatusBadgeClass" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider border {{ $service->status->color() }}">
                        <span class="w-1.5 h-1.5 rounded-full bg-current animate-pulse"></span>
                        <span x-text="currentStatusLabel">{{ $service->status->label() }}</span>
                    </span>
                </div>
            </div>

            <!-- Live Polling Status Indicator -->
            <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-white border border-slate-200/80 shadow-xs self-start sm:self-auto">
                <span class="relative flex h-2.5 w-2.5">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                </span>
                <span class="text-[11px] font-semibold text-slate-600">Pelacakan Langsung Aktif</span>
                <span class="text-[10px] text-slate-400 font-mono" x-show="lastChecked">· <span x-text="lastChecked"></span></span>
            </div>
        </div>

        <!-- Upper Progress Stepper Bar -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-xs p-6 sm:p-8">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400">Tahapan Pengerjaan Servis</h2>
                <span class="text-xs font-mono font-bold text-slate-700"><span x-text="progress"></span>% Selesai</span>
            </div>

            <!-- Progress Bar Line -->
            <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden mb-8">
                <div class="bg-slate-900 h-2.5 rounded-full transition-all duration-700 ease-out" 
                     :style="'width: ' + progress + '%'"></div>
            </div>

            <!-- 4 Visual Stepper Nodes -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <!-- Step 1: Pending -->
                <div class="flex flex-col items-center text-center p-3 rounded-2xl transition"
                     :class="progress >= 25 ? 'bg-slate-50 border border-slate-200/60' : 'opacity-40'">
                    <div class="w-9 h-9 rounded-full flex items-center justify-center font-bold text-xs mb-2 transition"
                         :class="progress >= 25 ? 'bg-slate-900 text-white' : 'bg-slate-200 text-slate-500'">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <span class="text-xs font-bold text-slate-900">1. Booking Diterima</span>
                    <span class="text-[10px] text-slate-500 mt-0.5">Tiket diterbitkan</span>
                </div>

                <!-- Step 2: Confirmed -->
                <div class="flex flex-col items-center text-center p-3 rounded-2xl transition"
                     :class="progress >= 50 ? 'bg-slate-50 border border-slate-200/60' : 'opacity-40'">
                    <div class="w-9 h-9 rounded-full flex items-center justify-center font-bold text-xs mb-2 transition"
                         :class="progress >= 50 ? 'bg-slate-900 text-white' : 'bg-slate-200 text-slate-500'">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <span class="text-xs font-bold text-slate-900">2. Dikonfirmasi</span>
                    <span class="text-[10px] text-slate-500 mt-0.5">Mekanik ditugaskan</span>
                </div>

                <!-- Step 3: In Progress -->
                <div class="flex flex-col items-center text-center p-3 rounded-2xl transition"
                     :class="progress >= 75 ? 'bg-slate-50 border border-slate-200/60' : 'opacity-40'">
                    <div class="w-9 h-9 rounded-full flex items-center justify-center font-bold text-xs mb-2 transition"
                         :class="progress >= 75 ? 'bg-slate-900 text-white' : 'bg-slate-200 text-slate-500'">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                        </svg>
                    </div>
                    <span class="text-xs font-bold text-slate-900">3. Pengerjaan Unit</span>
                    <span class="text-[10px] text-slate-500 mt-0.5">Inspeksi & servis</span>
                </div>

                <!-- Step 4: Completed -->
                <div class="flex flex-col items-center text-center p-3 rounded-2xl transition"
                     :class="progress >= 100 ? 'bg-emerald-50 border border-emerald-200/60' : 'opacity-40'">
                    <div class="w-9 h-9 rounded-full flex items-center justify-center font-bold text-xs mb-2 transition"
                         :class="progress >= 100 ? 'bg-emerald-600 text-white' : 'bg-slate-200 text-slate-500'">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <span class="text-xs font-bold text-slate-900">4. Servis Selesai</span>
                    <span class="text-[10px] text-slate-500 mt-0.5">Siap serah terima</span>
                </div>
            </div>
        </div>

        <!-- Vehicle & Assignment Information -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white rounded-3xl border border-slate-200 shadow-xs p-6">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Unit Kendaraan</span>
                <p class="font-extrabold text-lg text-slate-900 mt-1">{{ $service->vehicle_model }}</p>
                <p class="text-xs font-mono text-slate-600 mt-0.5">Plat: {{ $service->vehicle_plate_number }}</p>
                <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                    <span>Tahun: {{ $service->vehicle_year }}</span>
                    <span>{{ number_format($service->odometer_km, 0, ',', '.') }} KM</span>
                </div>
            </div>

            <div class="bg-white rounded-3xl border border-slate-200 shadow-xs p-6">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Jadwal Kedatangan</span>
                <p class="font-extrabold text-lg text-slate-900 mt-1">{{ \App\Support\Timezone::format($service->service_date, 'd F Y', false) }}</p>
                <p class="text-xs text-slate-600 mt-0.5">Pukul {{ sprintf('%02d:00', $service->slot_hour) }} WIB</p>
                <div class="mt-4 pt-4 border-t border-slate-100 text-xs text-slate-500">
                    <span class="capitalize">Paket: {{ $service->service_type }}</span>
                </div>
            </div>

            <div class="bg-white rounded-3xl border border-slate-200 shadow-xs p-6">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Mekanik Bertugas</span>
                <p class="font-extrabold text-lg text-slate-900 mt-1" x-text="mechanicName">
                    {{ $service->mechanic ? $service->mechanic->name : 'Belum Ditugaskan' }}
                </p>
                <p class="text-xs text-slate-500 mt-0.5">Bengkel Resmi Dealer Pro</p>
                <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="text-slate-400">Total Biaya Saat Ini:</span>
                    <span class="font-mono font-extrabold text-slate-900" x-text="totalCostFormatted">
                        Rp{{ number_format($service->total_cost, 0, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Visual Timeline of Status Logs -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-xs p-6 sm:p-8">
            <div class="flex items-center justify-between mb-8 pb-4 border-b border-slate-100">
                <div>
                    <h2 class="text-lg font-black text-slate-900">Riwayat Perjalanan Servis</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Catatan aktivitas pengerjaan unit secara kronologis dari awal hingga selesai.</p>
                </div>
                <button @click="fetchStatus()" title="Muat ulang sekarang" class="p-2 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-xl transition">
                    <svg class="w-4 h-4" :class="loading ? 'animate-spin' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                </button>
            </div>

            <!-- Timeline List -->
            <div class="relative pl-6 sm:pl-8 space-y-8 before:absolute before:left-3 sm:before:left-4 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200">
                <template x-for="(log, index) in logs" :key="log.id">
                    <div class="relative flex items-start gap-4 group">
                        <!-- Node Icon Indicator -->
                        <div class="absolute -left-6 sm:-left-8 w-6 h-6 rounded-full bg-slate-900 ring-4 ring-white flex items-center justify-center text-white shrink-0 mt-0.5">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>

                        <!-- Card Content -->
                        <div class="flex-1 bg-slate-50/70 group-hover:bg-slate-50 p-4 sm:p-5 rounded-2xl border border-slate-200/60 transition">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 mb-2">
                                <span class="font-extrabold text-sm text-slate-900 tracking-tight" x-text="log.to_status_label"></span>
                                <div class="flex items-center gap-2 text-xs text-slate-400 font-mono">
                                    <span x-text="log.created_at_human"></span>
                                    <span>·</span>
                                    <span class="text-slate-500 font-sans" x-text="log.time_ago"></span>
                                </div>
                            </div>

                            <p class="text-xs sm:text-sm text-slate-700 leading-relaxed" x-text="log.notes || 'Pembaruan status servis.'"></p>

                            <div class="mt-3 pt-3 border-t border-slate-200/50 flex items-center justify-between text-[11px] text-slate-400">
                                <span>Diperbarui oleh: <strong class="text-slate-600 font-semibold" x-text="log.changed_by"></strong></span>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Initial Server-side Render Fallback -->
                @if($service->statusLogs->isEmpty())
                    <p class="text-xs text-slate-400 italic">Belum ada riwayat aktivitas yang tercatat.</p>
                @endif
            </div>
        </div>

        <!-- Work Order Items Breakdown (Read-Only for Customer) -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-xs p-6 sm:p-8">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-4">Rincian Onderdil & Jasa Resmi</h2>
            @if($service->items->isEmpty())
                <p class="text-xs text-slate-500">Mekanik belum menambahkan rincian suku cadang atau biaya jasa.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-slate-200 text-[11px] uppercase tracking-wider text-slate-400 font-bold">
                                <th class="py-2.5">Onderdil / Jasa</th>
                                <th class="py-2.5 text-center">Jumlah</th>
                                <th class="py-2.5 text-right">Harga Satuan</th>
                                <th class="py-2.5 text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($service->items as $item)
                                <tr>
                                    <td class="py-3 font-semibold text-slate-800">
                                        {{ $item->name }}
                                        @if($item->part_number)
                                            <span class="block text-[10px] text-slate-400 font-mono">{{ $item->part_number }}</span>
                                        @endif
                                    </td>
                                    <td class="py-3 text-center font-mono">{{ $item->quantity }}</td>
                                    <td class="py-3 text-right font-mono">Rp{{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                    <td class="py-3 text-right font-mono font-bold text-slate-900">Rp{{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="font-bold border-t border-slate-200">
                                <td colspan="3" class="py-3 text-right uppercase tracking-wider text-slate-500">Total Keseluruhan:</td>
                                <td class="py-3 text-right font-mono text-sm text-slate-900">Rp{{ number_format($service->total_cost, 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <!-- Alpine.js Live Polling Script -->
    <script>
        function serviceTracker(serviceId, initialProgress, initialStatus) {
            return {
                serviceId: serviceId,
                progress: initialProgress,
                status: initialStatus,
                currentStatusLabel: '{{ $service->status->label() }}',
                currentStatusBadgeClass: '{{ $service->status->color() }}',
                mechanicName: '{{ $service->mechanic ? $service->mechanic->name : "Belum Ditugaskan" }}',
                totalCostFormatted: 'Rp{{ number_format($service->total_cost, 0, ",", ".") }}',
                loading: false,
                lastChecked: '',
                pollingInterval: null,
                logs: @json($statusLogsData ?? []),

                initTracker() {
                    this.updateTimestamp();
                    // Polling setiap 30 detik (PRD §7)
                    this.pollingInterval = setInterval(() => {
                        this.fetchStatus();
                    }, 30000);
                },

                updateTimestamp() {
                    const now = new Date();
                    this.lastChecked = now.getHours().toString().padStart(2, '0') + ':' + 
                                       now.getMinutes().toString().padStart(2, '0') + ':' + 
                                       now.getSeconds().toString().padStart(2, '0');
                },

                async fetchStatus() {
                    this.loading = true;
                    try {
                        const response = await fetch(`/dashboard/services/${this.serviceId}/status-stream`, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });

                        if (response.ok) {
                            const data = await response.json();
                            this.progress = data.progress_percent;
                            this.status = data.status;
                            this.currentStatusLabel = data.status_label;
                            this.currentStatusBadgeClass = data.status_color;
                            this.mechanicName = data.mechanic_name;
                            this.totalCostFormatted = data.total_cost_formatted;
                            this.logs = data.logs;
                            this.updateTimestamp();
                        }
                    } catch (error) {
                        console.error('[ServiceTracker Polling Error]', error);
                    } finally {
                        this.loading = false;
                    }
                }
            };
        }
    </script>
</x-dashboard-layout>
