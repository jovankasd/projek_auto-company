<div class="space-y-8" x-data="{ 
    assignModalOpen: false, 
    selectedService: null,
    openAssign(service) {
        this.selectedService = service;
        this.assignModalOpen = true;
    }
}">
    <!-- Top Welcome & Quick Links -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex flex-wrap items-center gap-2 mb-1.5">
                <span class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider bg-slate-900 text-white">
                    <svg class="w-3.5 h-3.5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    <span>Pusat Kendali Super Admin</span>
                </span>
                <span class="text-xs text-slate-600 font-medium">Pusat Integrasi Sales &bull; Admin Mekanik &bull; Customer</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900">Executive Control Panel</h1>
            <p class="text-slate-600 text-sm mt-1">Kelola rincian operasional seluruh divisi, selesaikan kendala, dan kirimkan laporan terpadu ke Owner.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('reports.index') }}" class="min-h-[44px] px-4 py-2.5 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 transition shadow-sm inline-flex items-center">
                Laporan Keuangan & Operasional
            </a>
            <a href="{{ route('audit.index') }}" class="min-h-[44px] px-4 py-2.5 rounded-xl bg-white border border-slate-200/80 text-slate-700 text-xs font-bold hover:bg-slate-50 transition inline-flex items-center">
                Audit Log
            </a>
            <a href="{{ route('settings.edit') }}" aria-label="Pengaturan Sistem" title="Pengaturan" class="min-h-[44px] min-w-[44px] rounded-xl bg-white border border-slate-200/80 text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition inline-flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </a>
        </div>
    </div>

    <!-- PUSAT PENGIRIMAN LAPORAN RESMI KE OWNER -->
    <div class="bg-gradient-to-br from-slate-900 via-slate-900 to-blue-950 text-white rounded-3xl p-6 sm:p-7 shadow-lg border border-slate-800">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div class="max-w-xl">
                <div class="flex flex-wrap items-center gap-2 mb-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full text-[11px] font-bold uppercase tracking-wider bg-blue-500/20 text-blue-200 border border-blue-400/30">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                        <span>Terhubung Langsung ke Owner</span>
                    </span>
                    @if($lastReportSubmittedAt)
                        <span class="text-xs text-emerald-300 font-semibold flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                            </svg>
                            Terkirim: {{ \Carbon\Carbon::parse($lastReportSubmittedAt)->translatedFormat('d M Y, H:i') }}
                        </span>
                    @else
                        <span class="text-xs text-amber-300 font-medium">Belum ada pengiriman periode ini</span>
                    @endif
                </div>
                <h2 class="text-xl sm:text-2xl font-black text-white">Kirim Laporan Resmi Operasional & Finansial ke Owner</h2>
                <p class="text-slate-300 text-xs sm:text-sm mt-1">
                    Super Admin bertugas memeriksa rincian permasalahan pada bagian <strong>Sales</strong>, <strong>Admin Mekanik</strong>, dan <strong>Customer</strong>. Klik tombol untuk memverifikasi dan mengirimkan hasil keseluruhan ke Owner.
                </p>
                @if($lastReportNote)
                    <p class="text-xs text-blue-100 mt-2 bg-white/10 px-3 py-2 rounded-xl border border-white/15">
                        Catatan Pengiriman Terakhir: &ldquo;{{ $lastReportNote }}&rdquo;
                    </p>
                @endif
            </div>

            <!-- Form Kirim Laporan -->
            <form method="POST" action="{{ route('reports.submit-to-owner') }}" class="shrink-0 flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                @csrf
                <label for="owner_report_note" class="sr-only">Catatan laporan ke Owner</label>
                <input id="owner_report_note"
                       type="text" 
                       name="note" 
                       placeholder="Catatan ke Owner (opsional)..." 
                       value="Laporan 3 Divisi (Sales, Admin Mekanik, Customer) telah diverifikasi lengkap."
                       class="min-h-[44px] px-4 py-2.5 rounded-xl bg-white/10 border border-white/25 text-white placeholder-slate-300 text-xs focus:ring-2 focus:ring-blue-400 focus:outline-none sm:min-w-[260px]">
                
                <button type="submit" 
                        class="min-h-[44px] px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs transition duration-200 shadow-lg flex items-center justify-center gap-2 whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                    <span>Verifikasi & Kirim ke Owner</span>
                </button>
            </form>
        </div>
    </div>

    <!-- PUSAT KENDALI OPERASIONAL 3 DIVISI: SALES, ADMIN MEKANIK, CUSTOMER -->
    <div class="space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h2 class="text-lg font-black text-slate-900">Pusat Rincian & Permasalahan 3 Divisi Operasional</h2>
            <span class="text-xs text-slate-600 font-semibold">Tanggung Jawab Super Admin</span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- DIVISI 1: SALES & SHOWROOM -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-2.5">
                            <span class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                                </svg>
                            </span>
                            <h3 class="font-black text-slate-900 text-sm">Divisi Sales & Showroom</h3>
                        </div>
                        <span class="text-xs font-bold text-emerald-700">{{ $kpis['active_units'] }} Unit Aktif</span>
                    </div>

                    <div class="mt-4 space-y-3">
                        <div class="p-3 bg-slate-50 rounded-2xl flex items-center justify-between text-xs">
                            <span class="text-slate-600 font-medium">Permohonan Test Drive Pending:</span>
                            <span class="font-mono tabular-nums font-bold text-amber-800">{{ $pendingTestDrives->count() }} Permintaan</span>
                        </div>

                        <!-- Daftar Lead Menunggu -->
                        <div class="space-y-2">
                            @forelse($pendingTestDrives->take(3) as $ptd)
                                <div class="p-3 rounded-xl border border-slate-200/70 text-xs flex items-center justify-between gap-2">
                                    <div class="truncate">
                                        <p class="font-bold text-slate-900 truncate">{{ $ptd->customer?->name ?? 'Customer' }}</p>
                                        <p class="text-[11px] text-slate-600">{{ $ptd->car?->model_name ?? 'Unit Showroom' }}</p>
                                    </div>
                                    <form method="POST" action="{{ route('test-drives.approve', $ptd) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="min-h-[44px] px-3.5 py-2 bg-emerald-700 text-white font-bold rounded-xl text-xs hover:bg-emerald-800 transition">
                                            Setujui
                                        </button>
                                    </form>
                                </div>
                            @empty
                                <p class="text-xs text-slate-600 py-3 text-center">Semua permohonan test drive telah ditangani.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="pt-4 mt-4 border-t border-slate-100 flex items-center gap-2">
                    <a href="{{ route('cars.create') }}" class="flex-1 min-h-[44px] inline-flex items-center justify-center bg-slate-900 text-white rounded-xl text-xs font-bold hover:bg-slate-800 transition">
                        + Unit Mobil
                    </a>
                    <a href="{{ route('promos.index') }}" class="min-h-[44px] px-4 inline-flex items-center justify-center border border-slate-200 text-slate-700 rounded-xl text-xs font-bold hover:bg-slate-50 transition">
                        Promo
                    </a>
                </div>
            </div>

            <!-- DIVISI 2: ADMIN MEKANIK & BENGKEL -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-2.5">
                            <span class="w-9 h-9 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </span>
                            <h3 class="font-black text-slate-900 text-sm">Divisi Admin Mekanik</h3>
                        </div>
                        <span class="text-xs font-bold text-amber-800">{{ $mechanics->count() }} Mekanik</span>
                    </div>

                    <div class="mt-4 space-y-3">
                        <div class="p-3 bg-slate-50 rounded-2xl flex items-center justify-between text-xs">
                            <span class="text-slate-600 font-medium">Servis Butuh Penugasan Mekanik:</span>
                            <span class="font-mono tabular-nums font-bold text-blue-700">{{ $unassignedServices->count() }} Unit</span>
                        </div>

                        <!-- Daftar Servis Menunggu -->
                        <div class="space-y-2">
                            @forelse($unassignedServices->take(3) as $us)
                                <div class="p-3 rounded-xl border border-slate-200/70 text-xs flex items-center justify-between gap-2">
                                    <div class="truncate">
                                        <p class="font-mono font-bold text-slate-900">{{ $us->ticket_number }}</p>
                                        <p class="text-[11px] text-slate-600">{{ $us->car_model }} ({{ $us->license_plate }})</p>
                                    </div>
                                    <button type="button" 
                                            @click="openAssign({{ \Illuminate\Support\Js::from(['id' => $us->id, 'ticket_number' => $us->ticket_number, 'car_model' => $us->car_model, 'assign_url' => route('services.assign', $us)]) }})"
                                            class="min-h-[44px] px-3.5 py-2 bg-blue-600 text-white font-bold rounded-xl text-xs hover:bg-blue-700 transition shrink-0">
                                        Tugaskan
                                    </button>
                                </div>
                            @empty
                                <p class="text-xs text-slate-600 py-3 text-center">Semua unit servis telah memiliki mekanik.</p>
                            @endforelse
                        </div>

                        <!-- Peringatan Suku Cadang -->
                        @if($lowStockParts->count() > 0)
                            <div class="p-3 bg-red-50 border border-red-200 rounded-xl text-xs text-red-900">
                                <span class="font-bold block">Perlu Penambahan Suku Cadang:</span>
                                <span class="text-[11px] block mt-0.5">{{ $lowStockParts->pluck('name')->take(2)->implode(', ') }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="pt-4 mt-4 border-t border-slate-100 flex items-center gap-2">
                    <a href="{{ route('spare-parts.create') }}" class="flex-1 min-h-[44px] inline-flex items-center justify-center bg-amber-500 text-slate-950 rounded-xl text-xs font-bold hover:bg-amber-400 transition">
                        + Tambah Suku Cadang
                    </a>
                    <a href="{{ route('spare-parts.index') }}" class="min-h-[44px] px-4 inline-flex items-center justify-center border border-slate-200 text-slate-700 rounded-xl text-xs font-bold hover:bg-slate-50 transition">
                        Gudang
                    </a>
                </div>
            </div>

            <!-- DIVISI 3: CUSTOMER & PELANGGAN -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-2.5">
                            <span class="w-9 h-9 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </span>
                            <h3 class="font-black text-slate-900 text-sm">Divisi Customer & Pengguna</h3>
                        </div>
                        <span class="text-xs font-bold text-blue-700">{{ $totalCustomers }} Pelanggan</span>
                    </div>

                    <div class="mt-4 space-y-3">
                        <div class="p-3 bg-slate-50 rounded-2xl flex items-center justify-between text-xs">
                            <span class="text-slate-600 font-medium">Pelanggan Baru Bulan Ini:</span>
                            <span class="font-mono tabular-nums font-bold text-emerald-700">+{{ $newCustomersMonth }} Orang</span>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-2xl flex items-center justify-between text-xs">
                            <span class="text-slate-600 font-medium">Total Booking Servis Diterima:</span>
                            <span class="font-mono tabular-nums font-bold text-slate-900">{{ $kpis['total_bookings_month'] }} Kali</span>
                        </div>
                        <div class="p-3 bg-emerald-50 rounded-2xl border border-emerald-200/80 text-xs text-emerald-900">
                            <p class="font-bold">Prioritas Pelayanan:</p>
                            <p class="text-[11px] text-emerald-800 mt-0.5">Seluruh pemesanan servis & test drive terdata rapi dan transparan.</p>
                        </div>
                    </div>
                </div>

                <div class="pt-4 mt-4 border-t border-slate-100 flex items-center gap-2">
                    <a href="{{ route('users.index') }}" class="w-full min-h-[44px] inline-flex items-center justify-center bg-slate-900 text-white rounded-xl text-xs font-bold hover:bg-slate-800 transition">
                        Kelola Akun & Pelanggan
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 4 KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6">
        <!-- KPI 1: Total Booking Servis -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6">
            <div class="flex items-center justify-between">
                <span class="text-xs uppercase font-bold text-slate-600 tracking-wider">Booking Servis Bulan Ini</span>
                <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-700 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
            </div>
            <p class="font-mono tabular-nums text-4xl font-black text-slate-900 mt-4">{{ $kpis['total_bookings_month'] }}</p>
            <div class="flex items-center gap-1.5 mt-2 text-xs text-slate-600 font-medium">
                <span class="text-emerald-700 font-bold">Terjadwal</span>
                <span>di bengkel resmi</span>
            </div>
        </div>

        <!-- KPI 2: Unit Aktif di Katalog -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6">
            <div class="flex items-center justify-between">
                <span class="text-xs uppercase font-bold text-slate-600 tracking-wider">Unit Mobil Aktif</span>
                <div class="w-10 h-10 rounded-2xl bg-slate-100 text-slate-800 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                    </svg>
                </div>
            </div>
            <p class="font-mono tabular-nums text-4xl font-black text-slate-900 mt-4">{{ $kpis['active_units'] }}</p>
            <div class="flex items-center gap-1.5 mt-2 text-xs text-slate-600 font-medium">
                <a href="{{ route('cars.index') }}" class="text-blue-700 hover:underline font-bold">Kelola Unit Katalog</a>
            </div>
        </div>

        <!-- KPI 3: Lead Baru Test Drive -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6">
            <div class="flex items-center justify-between">
                <span class="text-xs uppercase font-bold text-slate-600 tracking-wider">Lead Test Drive Bulan Ini</span>
                <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-700 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>
            <p class="font-mono tabular-nums text-4xl font-black text-slate-900 mt-4">{{ $kpis['new_leads_month'] }}</p>
            <div class="flex items-center gap-1.5 mt-2 text-xs text-slate-600 font-medium">
                <span class="text-amber-800 font-bold">Peminat mobil</span>
                <span>tercatat</span>
            </div>
        </div>

        <!-- KPI 4: Pendapatan Servis -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6">
            <div class="flex items-center justify-between">
                <span class="text-xs uppercase font-bold text-slate-600 tracking-wider">Pendapatan Servis Bulan Ini</span>
                <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold">
                    <span class="text-base font-extrabold">Rp</span>
                </div>
            </div>
            <p class="font-mono tabular-nums text-2xl sm:text-3xl font-black text-emerald-700 mt-4 truncate" title="Rp {{ number_format($kpis['service_revenue_month'], 0, ',', '.') }}">
                Rp {{ number_format($kpis['service_revenue_month'], 0, ',', '.') }}
            </p>
            <div class="flex items-center gap-1.5 mt-2 text-xs text-slate-600 font-medium">
                <span class="text-emerald-700 font-bold">Dari unit selesai</span>
            </div>
        </div>
    </div>

    <!-- 30-Day Service Trend & Workshop Slot Heatmap -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-8 flex flex-col justify-between">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-5 border-b border-slate-100 gap-2">
                <div>
                    <h2 class="text-lg font-black text-slate-900">Tren Pemesanan Servis 30 Hari Terakhir</h2>
                    <p class="text-xs text-slate-600 mt-0.5">Grafik kurva harian volume kendaraan yang masuk ke bengkel resmi.</p>
                </div>
                <div class="flex flex-wrap items-center gap-2 self-start sm:self-center">
                    <span class="text-xs font-mono tabular-nums font-bold text-amber-800 bg-amber-50 border border-amber-200 px-3 py-1.5 rounded-xl">
                        Avg: {{ number_format($avgDailyBookings ?? 0, 1, ',', '.') }}/hari
                    </span>
                    <span class="text-xs font-mono tabular-nums font-bold text-slate-700 bg-slate-100 px-3 py-1.5 rounded-xl">
                        Total: {{ array_sum($chartCounts) }} Kendaraan
                    </span>
                </div>
            </div>

            @php
                $saChartPayload = [
                    'type' => 'superadmin-area',
                    'labels' => array_values($chartDates),
                    'counts' => array_map('intval', array_values($chartCounts)),
                    'avg' => (float) ($avgDailyBookings ?? 0),
                ];
            @endphp

            <div class="mt-5">
                <div role="img"
                     aria-label="Grafik Tren Pemesanan Servis 30 Hari Terakhir"
                     class="relative h-72 w-full"
                     data-echarts-config='@json($saChartPayload)'></div>
            </div>
        </div>

        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-8 flex flex-col justify-between">
            <div class="flex items-start justify-between pb-4 border-b border-slate-100 gap-2">
                <div>
                    <h2 class="text-lg font-black text-slate-900">Kepadatan Slot Bengkel</h2>
                    <p class="text-xs text-slate-600 mt-0.5">Distribusi jam booking 7 hari terakhir.</p>
                </div>
                <span class="text-xs font-mono tabular-nums font-bold text-blue-800 bg-blue-50 border border-blue-200 px-2.5 py-1 rounded-xl shrink-0">
                    {{ $slotHeatmap['total7d'] ?? 0 }} Slot
                </span>
            </div>

            @php
                $saHeatmapPayload = [
                    'type' => 'superadmin-heatmap',
                    'days' => $slotHeatmap['days'] ?? [],
                    'slots' => $slotHeatmap['slots'] ?? [],
                    'points' => $slotHeatmap['points'] ?? [],
                    'maxCount' => (int) ($slotHeatmap['maxCount'] ?? 1),
                ];
            @endphp

            <div class="my-4">
                <div role="img"
                     aria-label="Heatmap Kepadatan Slot Bengkel 7 Hari Terakhir"
                     class="relative h-60 w-full"
                     data-echarts-config='@json($saHeatmapPayload)'></div>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-600">
                <span>Intensitas Antrean:</span>
                <div class="flex items-center gap-1.5 font-medium">
                    <span>Sepi</span>
                    <span class="w-3 h-3 rounded bg-slate-100 border border-slate-200"></span>
                    <span class="w-3 h-3 rounded bg-blue-100"></span>
                    <span class="w-3 h-3 rounded bg-blue-300"></span>
                    <span class="w-3 h-3 rounded bg-blue-600"></span>
                    <span class="w-3 h-3 rounded bg-blue-900"></span>
                    <span>Padat</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 2 Column Section: 10 Servis Terbaru & 10 Test Drive Terbaru -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- 10 Servis Terbaru -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-8">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <h3 class="font-black text-lg text-slate-900">10 Servis Terbaru</h3>
                <span class="text-xs text-slate-600 font-bold uppercase tracking-wider">Real-time</span>
            </div>

            <div class="divide-y divide-slate-100 mt-2">
                @forelse($recentServices as $srv)
                    <div class="py-3.5 flex items-center justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('services.show', $srv) }}" class="font-mono font-bold text-xs text-blue-700 hover:underline">{{ $srv->ticket_number }}</a>
                                <span class="text-xs font-mono text-slate-600">({{ $srv->license_plate }})</span>
                            </div>
                            <p class="text-xs font-semibold text-slate-800 truncate mt-0.5">{{ $srv->car_model }} &bull; {{ $srv->customer?->name ?? 'Pelanggan' }}</p>
                            <p class="text-[11px] text-slate-600 mt-0.5">
                                Mekanik: <span class="font-bold text-slate-800">{{ $srv->mechanic ? $srv->mechanic->name : 'Belum Ditugaskan' }}</span>
                            </p>
                        </div>
                        <div class="text-right shrink-0 flex flex-col items-end gap-1.5">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase tracking-wider border {{ $srv->status->color() }}">
                                {{ $srv->status->label() }}
                            </span>
                            @if(! $srv->assigned_mechanic_id || $srv->status->value === 'pending')
                                <button type="button" 
                                        @click="openAssign({{ \Illuminate\Support\Js::from(['id' => $srv->id, 'ticket_number' => $srv->ticket_number, 'car_model' => $srv->car_model, 'assign_url' => route('services.assign', $srv)]) }})"
                                        class="min-h-[44px] px-3 inline-flex items-center rounded-xl text-xs font-bold text-blue-700 hover:bg-blue-50 transition">
                                    Tugaskan Mekanik
                                </button>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="py-8 text-center text-xs text-slate-600">Belum ada pemesanan servis.</p>
                @endforelse
            </div>
        </div>

        <!-- 10 Test Drive Terbaru -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-8">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <h3 class="font-black text-lg text-slate-900">10 Test Drive Terbaru</h3>
                <span class="text-xs text-slate-600 font-bold uppercase tracking-wider">Aktivitas Sales</span>
            </div>

            <div class="divide-y divide-slate-100 mt-2">
                @forelse($recentTestDrives as $td)
                    <div class="py-3.5 flex items-center justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <p class="font-bold text-xs text-slate-900">{{ $td->customer?->name ?? 'Customer' }}</p>
                            <p class="text-xs text-slate-600 truncate mt-0.5">{{ $td->car?->brand ?? '' }} {{ $td->car?->model_name ?? '' }}</p>
                            <p class="text-[11px] text-slate-600 mt-0.5">
                                Jadwal: {{ $td->preferred_date ? $td->preferred_date->translatedFormat('d M Y, H:i') : '-' }} WIB
                            </p>
                        </div>
                        <div class="text-right shrink-0">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase tracking-wider border {{ $td->status->color() }}">
                                {{ $td->status->label() }}
                            </span>
                            <span class="block text-[11px] text-slate-600 mt-1 font-mono">
                                Sales: {{ $td->sales ? $td->sales->name : '-' }}
                            </span>
                        </div>
                    </div>
                @empty
                    <p class="py-8 text-center text-xs text-slate-600">Belum ada pengajuan test drive.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Modal Assign Mekanik -->
    <div x-show="assignModalOpen" 
         x-cloak
         role="dialog"
         aria-modal="true"
         aria-labelledby="assign-mechanic-title"
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         @keydown.escape.window="assignModalOpen = false">
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" @click="assignModalOpen = false"></div>
        <div class="relative w-full max-w-md rounded-3xl bg-white border border-slate-200/80 shadow-2xl p-6 sm:p-8 overflow-hidden"
             x-transition:enter="transition ease-out duration-200 transform"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h3 id="assign-mechanic-title" class="font-black text-lg text-slate-900">Tugaskan Mekanik</h3>
                    <p class="text-xs text-slate-600 mt-1" x-text="selectedService ? 'Tiket: ' + selectedService.ticket_number + ' (' + selectedService.car_model + ')' : ''"></p>
                </div>
                <button type="button" @click="assignModalOpen = false" aria-label="Tutup modal penugasan" class="min-h-[44px] min-w-[44px] inline-flex items-center justify-center rounded-xl text-slate-600 hover:bg-slate-100 transition -mt-1 -mr-1">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form :action="selectedService ? selectedService.assign_url : '#'" method="POST" class="mt-5 space-y-4">
                @csrf
                @method('PATCH')
                <div>
                    <label for="assign_mechanic_select" class="block text-xs font-bold uppercase text-slate-700 mb-1.5">Pilih Mekanik</label>
                    <select id="assign_mechanic_select" name="assigned_mechanic_id" required class="w-full min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-300 text-sm text-slate-900 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                        <option value="">-- Pilih Mekanik Tersedia --</option>
                        @foreach($mechanics as $mech)
                            <option value="{{ $mech->id }}">{{ $mech->name }} ({{ $mech->email }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" @click="assignModalOpen = false" class="min-h-[44px] px-4 py-2 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-100 transition">
                        Batal
                    </button>
                    <button type="submit" class="min-h-[44px] px-5 py-2 rounded-xl text-xs font-bold bg-slate-900 text-white hover:bg-slate-800 transition shadow-sm">
                        Tugaskan & Konfirmasi Servis
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
