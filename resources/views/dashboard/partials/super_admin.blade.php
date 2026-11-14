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
            <div class="flex items-center gap-2 mb-1">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wide bg-slate-900 text-white">
                    Super Admin
                </span>
                <span class="text-xs text-slate-500 font-medium">Pusat Integrasi Sales, Admin Mekanik, dan Customer</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900">Executive Control Panel</h1>
            <p class="text-slate-500 text-sm mt-1">Kelola rincian operasional seluruh divisi, selesaikan kendala, dan kirimkan laporan terpadu ke Owner.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('reports.index') }}" class="px-4 py-2 rounded-lg bg-slate-900 text-white text-xs font-semibold hover:bg-slate-800 transition shadow-sm">
                Laporan Keuangan & Operasional
            </a>
            <a href="{{ route('audit.index') }}" class="px-4 py-2 rounded-lg bg-white border border-slate-300 text-slate-700 text-xs font-semibold hover:bg-slate-50 transition">
                Audit Log
            </a>
            <a href="{{ route('settings.edit') }}" class="p-2 rounded-lg bg-white border border-slate-300 text-slate-600 hover:text-slate-900 transition" title="Pengaturan">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </a>
        </div>
    </div>

    <!-- PUSAT PENGIRIMAN LAPORAN RESMI KE OWNER -->
    <div class="bg-slate-900 text-white rounded-2xl p-6 sm:p-7 shadow-sm border border-slate-800">
        <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-6">
            <div class="max-w-xl">
                <div class="flex items-center gap-2 mb-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wide bg-slate-800 text-slate-200 border border-slate-700">
                        Pelaporan Eksekutif
                    </span>
                    @if($lastReportSubmittedAt)
                        <span class="text-xs text-emerald-400 font-semibold flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                            </svg>
                            Terkirim: {{ \Carbon\Carbon::parse($lastReportSubmittedAt)->translatedFormat('d M Y, H:i') }} WIB
                        </span>
                    @else
                        <span class="text-xs text-amber-300 font-medium">Belum ada pengiriman periode ini</span>
                    @endif
                </div>
                <h2 class="text-xl sm:text-2xl font-black text-white">Kirim Laporan Resmi Operasional & Finansial ke Owner</h2>
                <p class="text-slate-300 text-xs sm:text-sm mt-1">
                    Super Admin bertugas memeriksa rincian operasional pada bagian <strong>Sales</strong>, <strong>Admin Mekanik</strong>, dan <strong>Customer</strong>. Tekan tombol untuk memverifikasi dan mengirimkan hasil keseluruhan ke Owner.
                </p>
                @if($lastReportNote)
                    <p class="text-xs text-slate-300 mt-2.5 italic bg-slate-800/80 px-3.5 py-2 rounded-lg border border-slate-700">
                        Catatan Pengiriman Terakhir: &ldquo;{{ $lastReportNote }}&rdquo;
                    </p>
                @endif
            </div>

            <!-- Form Kirim Laporan -->
            <form method="POST" action="{{ route('reports.submit-to-owner') }}" class="w-full xl:w-auto flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                @csrf
                <input type="text" 
                       name="note" 
                       placeholder="Catatan ke Owner (opsional)..." 
                       value="Laporan 3 Divisi (Sales, Admin Mekanik, Customer) telah diverifikasi lengkap."
                       class="px-4 py-2.5 rounded-lg bg-slate-800 border border-slate-700 text-white placeholder-slate-400 text-xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none flex-1 sm:min-w-[240px]">
                
                <button type="submit" 
                        class="px-5 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs transition duration-200 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 flex items-center justify-center gap-2 whitespace-nowrap shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                    <span>Verifikasi & Kirim ke Owner</span>
                </button>
            </form>
        </div>
    </div>

    <!-- PUSAT KENDALI OPERASIONAL 3 DIVISI: SALES, ADMIN MEKANIK, CUSTOMER -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-black text-slate-900">Rincian Operasional 3 Divisi</h2>
            <span class="text-xs text-slate-500 font-semibold">Tanggung Jawab Super Admin</span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- DIVISI 1: SALES & SHOWROOM -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-2.5">
                            <span class="w-8 h-8 rounded-lg bg-slate-100 text-slate-800 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                                </svg>
                            </span>
                            <h3 class="font-black text-slate-900 text-sm">Divisi Sales & Showroom</h3>
                        </div>
                        <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200">{{ $kpis['active_units'] }} Unit Aktif</span>
                    </div>

                    <div class="mt-4 space-y-3">
                        <div class="p-3 bg-slate-50 rounded-xl flex items-center justify-between text-xs">
                            <span class="text-slate-600 font-medium">Permohonan Test Drive Pending:</span>
                            <span class="font-mono font-bold text-amber-700">{{ $pendingTestDrives->count() }} Permintaan</span>
                        </div>

                        <!-- Daftar Lead Menunggu -->
                        <div class="space-y-2">
                            @forelse($pendingTestDrives->take(3) as $ptd)
                                <div class="p-2.5 rounded-lg border border-slate-100 text-xs flex items-center justify-between">
                                    <div class="truncate">
                                        <p class="font-bold text-slate-800 truncate">{{ $ptd->customer?->name ?? 'Customer' }}</p>
                                        <p class="text-[11px] text-slate-500">{{ $ptd->car?->model_name }}</p>
                                    </div>
                                    <form method="POST" action="{{ route('test-drives.approve', $ptd) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="px-2.5 py-1 bg-emerald-600 text-white font-semibold rounded-lg text-[10px] hover:bg-emerald-700 transition">
                                            Setujui
                                        </button>
                                    </form>
                                </div>
                            @empty
                                <p class="text-xs text-slate-500 py-2 text-center">Semua permohonan test drive telah ditangani.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="pt-4 mt-4 border-t border-slate-100 flex items-center gap-2">
                    <a href="{{ route('cars.create') }}" class="flex-1 text-center py-2 bg-slate-900 text-white rounded-lg text-xs font-semibold hover:bg-slate-800 transition">
                        + Unit Mobil
                    </a>
                    <a href="{{ route('promos.index') }}" class="px-3 py-2 border border-slate-300 text-slate-700 rounded-lg text-xs font-semibold hover:bg-slate-50 transition">
                        Promo
                    </a>
                </div>
            </div>

            <!-- DIVISI 2: ADMIN MEKANIK & BENGKEL -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-2.5">
                            <span class="w-8 h-8 rounded-lg bg-slate-100 text-slate-800 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </span>
                            <h3 class="font-black text-slate-900 text-sm">Divisi Admin Mekanik</h3>
                        </div>
                        <span class="text-xs font-bold text-amber-700 bg-amber-50 px-2.5 py-0.5 rounded-full border border-amber-200">{{ $mechanics->count() }} Mekanik</span>
                    </div>

                    <div class="mt-4 space-y-3">
                        <div class="p-3 bg-slate-50 rounded-xl flex items-center justify-between text-xs">
                            <span class="text-slate-600 font-medium">Servis Butuh Penugasan Mekanik:</span>
                            <span class="font-mono font-bold text-blue-700">{{ $unassignedServices->count() }} Unit</span>
                        </div>

                        <!-- Daftar Servis Menunggu -->
                        <div class="space-y-2">
                            @forelse($unassignedServices->take(3) as $us)
                                <div class="p-2.5 rounded-lg border border-slate-100 text-xs flex items-center justify-between">
                                    <div class="truncate">
                                        <p class="font-mono font-bold text-slate-800">{{ $us->ticket_number }}</p>
                                        <p class="text-[11px] text-slate-500">{{ $us->car_model }} ({{ $us->license_plate }})</p>
                                    </div>
                                    <button type="button" 
                                            @click="openAssign({{ $us->toJson() }})"
                                            class="px-2.5 py-1 bg-blue-600 text-white font-semibold rounded-lg text-[10px] hover:bg-blue-700 transition">
                                        Tugaskan
                                    </button>
                                </div>
                            @empty
                                <p class="text-xs text-slate-500 py-2 text-center">Semua unit servis telah memiliki mekanik.</p>
                            @endforelse
                        </div>

                        <!-- Peringatan Suku Cadang -->
                        @if($lowStockParts->count() > 0)
                            <div class="p-2.5 bg-red-50 border border-red-200 rounded-xl text-xs text-red-700 flex items-start gap-2">
                                <span class="font-bold shrink-0">Peringatan:</span>
                                <span class="text-[11px] block">Perlu restock part: {{ $lowStockParts->pluck('name')->take(2)->implode(', ') }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="pt-4 mt-4 border-t border-slate-100 flex items-center gap-2">
                    <a href="{{ route('spare-parts.create') }}" class="flex-1 text-center py-2 bg-slate-900 text-white rounded-lg text-xs font-semibold hover:bg-slate-800 transition">
                        + Tambah Suku Cadang
                    </a>
                    <a href="{{ route('spare-parts.index') }}" class="px-3 py-2 border border-slate-300 text-slate-700 rounded-lg text-xs font-semibold hover:bg-slate-50 transition">
                        Gudang
                    </a>
                </div>
            </div>

            <!-- DIVISI 3: CUSTOMER & PELANGGAN -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-2.5">
                            <span class="w-8 h-8 rounded-lg bg-slate-100 text-slate-800 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </span>
                            <h3 class="font-black text-slate-900 text-sm">Divisi Customer & Pelanggan</h3>
                        </div>
                        <span class="text-xs font-bold text-blue-700 bg-blue-50 px-2.5 py-0.5 rounded-full border border-blue-200">{{ $totalCustomers }} Pelanggan</span>
                    </div>

                    <div class="mt-4 space-y-3">
                        <div class="p-3 bg-slate-50 rounded-xl flex items-center justify-between text-xs">
                            <span class="text-slate-600 font-medium">Pelanggan Baru Bulan Ini:</span>
                            <span class="font-mono font-bold text-emerald-700">+{{ $newCustomersMonth }} Orang</span>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-xl flex items-center justify-between text-xs">
                            <span class="text-slate-600 font-medium">Total Booking Servis Diterima:</span>
                            <span class="font-mono font-bold text-slate-800">{{ $kpis['total_bookings_month'] }} Kali</span>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-xs text-slate-700">
                            <p class="font-bold">Prioritas Pelayanan:</p>
                            <p class="text-[11px] text-slate-600 mt-0.5">Seluruh pemesanan servis dan pengajuan test drive terdata secara transparan.</p>
                        </div>
                    </div>
                </div>

                <div class="pt-4 mt-4 border-t border-slate-100 flex items-center gap-2">
                    <a href="{{ route('users.index') }}" class="w-full text-center py-2 bg-slate-900 text-white rounded-lg text-xs font-semibold hover:bg-slate-800 transition">
                        Kelola Akun & Pelanggan
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 4 KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6">
        <!-- KPI 1: Total Booking Servis -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-xs uppercase font-semibold text-slate-500 tracking-wider">Booking Servis Bulan Ini</span>
                <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
            </div>
            <p class="font-mono text-3xl font-semibold text-slate-900 mt-4">{{ $kpis['total_bookings_month'] }}</p>
            <div class="flex items-center gap-1.5 mt-2 text-xs text-slate-500 font-medium">
                <span class="text-emerald-700 font-semibold">&bull; Terjadwal</span>
                <span>di bengkel resmi</span>
            </div>
        </div>

        <!-- KPI 2: Unit Aktif di Katalog -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-xs uppercase font-semibold text-slate-500 tracking-wider">Unit Mobil Aktif</span>
                <div class="w-10 h-10 rounded-lg bg-slate-100 text-slate-800 flex items-center justify-center font-bold">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                    </svg>
                </div>
            </div>
            <p class="font-mono text-3xl font-semibold text-slate-900 mt-4">{{ $kpis['active_units'] }}</p>
            <div class="flex items-center gap-1.5 mt-2 text-xs text-slate-500 font-medium">
                <a href="{{ route('cars.index') }}" class="text-blue-600 hover:text-blue-700 hover:underline font-semibold">Kelola Unit Katalog &rarr;</a>
            </div>
        </div>

        <!-- KPI 3: Lead Baru Test Drive -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-xs uppercase font-semibold text-slate-500 tracking-wider">Lead Test Drive Bulan Ini</span>
                <div class="w-10 h-10 rounded-lg bg-amber-50 text-amber-700 flex items-center justify-center font-bold">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>
            <p class="font-mono text-3xl font-semibold text-slate-900 mt-4">{{ $kpis['new_leads_month'] }}</p>
            <div class="flex items-center gap-1.5 mt-2 text-xs text-slate-500 font-medium">
                <span class="text-amber-700 font-semibold">&bull; Peminat mobil</span>
                <span>tercatat</span>
            </div>
        </div>

        <!-- KPI 4: Pendapatan Servis -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-xs uppercase font-semibold text-slate-500 tracking-wider">Pendapatan Servis Bulan Ini</span>
                <div class="w-10 h-10 rounded-lg bg-green-50 text-green-700 flex items-center justify-center font-bold">
                    <span class="text-sm font-bold">Rp</span>
                </div>
            </div>
            <p class="font-mono text-2xl sm:text-3xl font-semibold text-slate-900 mt-4 truncate" title="Rp{{ number_format($kpis['service_revenue_month'], 0, ',', '.') }}">
                Rp{{ number_format($kpis['service_revenue_month'], 0, ',', '.') }}
            </p>
            <div class="flex items-center gap-1.5 mt-2 text-xs text-slate-500 font-medium">
                <span class="text-green-700 font-semibold">&bull; Dari unit selesai</span>
            </div>
        </div>
    </div>

    <!-- 30-Day Service Trend Visual Chart -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8 overflow-hidden">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 border-b border-slate-100 gap-2">
            <div>
                <h2 class="text-lg font-black text-slate-900">Tren Pemesanan Servis 30 Hari Terakhir</h2>
                <p class="text-xs text-slate-500 mt-0.5">Grafik volume kendaraan yang masuk ke bengkel resmi harian.</p>
            </div>
            <span class="text-xs font-mono font-bold text-slate-700 bg-slate-100 px-3 py-1 rounded-lg">
                Total: {{ array_sum($chartCounts) }} Kendaraan
            </span>
        </div>

        @php
            $maxCount = max($chartCounts) > 0 ? max($chartCounts) : 1;
        @endphp

        <div class="mt-8">
            <div class="h-48 flex items-end gap-1 sm:gap-2 pt-6">
                @foreach($chartCounts as $i => $val)
                    @php
                        $heightPercent = max(8, round(($val / $maxCount) * 100));
                        $dateLabel = $chartDates[$i];
                    @endphp
                    <div class="flex-1 flex flex-col items-center gap-1 group relative">
                        <!-- Tooltip on hover -->
                        <div class="opacity-0 group-hover:opacity-100 pointer-events-none absolute -top-8 bg-slate-900 text-white text-[10px] font-mono px-2 py-0.5 rounded shadow transition duration-200 z-10 whitespace-nowrap">
                            {{ $dateLabel }}: {{ $val }} unit
                        </div>
                        <div class="w-full bg-slate-100 group-hover:bg-slate-900 rounded-t transition-colors duration-200 {{ $val > 0 ? 'bg-slate-800' : 'bg-slate-200' }}"
                             style="height: {{ $heightPercent }}%">
                        </div>
                        <span class="text-[9px] text-slate-500 font-mono hidden md:block rotate-45 origin-left truncate mt-1">
                            {{ $i % 3 === 0 ? $dateLabel : '' }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- 2 Column Section: 10 Servis Terbaru & 10 Test Drive Terbaru -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- 10 Servis Terbaru -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <h3 class="font-black text-lg text-slate-900">10 Servis Terbaru</h3>
                <span class="text-xs text-slate-500 font-semibold uppercase tracking-wider">Aktivitas Bengkel</span>
            </div>

            <div class="divide-y divide-slate-100 mt-2">
                @forelse($recentServices as $srv)
                    <div class="py-3.5 flex items-center justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <span class="font-mono font-bold text-xs text-slate-900">{{ $srv->ticket_number }}</span>
                                <span class="text-xs font-mono text-slate-500">({{ $srv->license_plate }})</span>
                            </div>
                            <p class="text-xs font-semibold text-slate-700 truncate mt-0.5">{{ $srv->car_model }} &bull; {{ $srv->customer->name ?? 'Pelanggan' }}</p>
                            <p class="text-[11px] text-slate-500 mt-0.5">
                                Mekanik: <span class="font-semibold text-slate-700">{{ $srv->mechanic ? $srv->mechanic->name : 'Belum Ditugaskan' }}</span>
                            </p>
                        </div>
                        <div class="text-right shrink-0">
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold uppercase tracking-wider border {{ $srv->status->color() }}">
                                {{ $srv->status->label() }}
                            </span>
                            @if(! $srv->assigned_mechanic_id || $srv->status->value === 'pending')
                                <button type="button" 
                                        @click="openAssign({{ $srv->toJson() }})"
                                        class="block mt-1 text-[11px] font-bold text-blue-600 hover:text-blue-700 hover:underline">
                                    Tugaskan Mekanik
                                </button>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="py-8 text-center text-xs text-slate-500">Belum ada pemesanan servis.</p>
                @endforelse
            </div>
        </div>

        <!-- 10 Test Drive Terbaru -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <h3 class="font-black text-lg text-slate-900">10 Test Drive Terbaru</h3>
                <span class="text-xs text-slate-500 font-semibold uppercase tracking-wider">Aktivitas Sales</span>
            </div>

            <div class="divide-y divide-slate-100 mt-2">
                @forelse($recentTestDrives as $td)
                    <div class="py-3.5 flex items-center justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <p class="font-bold text-xs text-slate-900">{{ $td->customer->name ?? 'Customer' }}</p>
                            <p class="text-xs text-slate-600 truncate mt-0.5">{{ $td->car->brand ?? '' }} {{ $td->car->model_name ?? '' }}</p>
                            <p class="text-[11px] text-slate-500 mt-0.5">
                                Jadwal: {{ $td->preferred_date ? $td->preferred_date->translatedFormat('d M Y, H:i') : '-' }} WIB
                            </p>
                        </div>
                        <div class="text-right shrink-0">
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold uppercase tracking-wider border {{ $td->status->color() }}">
                                {{ $td->status->label() }}
                            </span>
                            <span class="block text-[11px] text-slate-500 mt-0.5 font-mono">
                                Sales: {{ $td->sales ? $td->sales->name : '-' }}
                            </span>
                        </div>
                    </div>
                @empty
                    <p class="py-8 text-center text-xs text-slate-500">Belum ada pengajuan test drive.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Modal Assign Mekanik -->
    <div x-show="assignModalOpen" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         @keydown.escape.window="assignModalOpen = false">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="assignModalOpen = false"></div>
        <div class="relative w-full max-w-md rounded-2xl bg-white border border-slate-200 shadow-2xl p-6 sm:p-8 overflow-hidden"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            <h3 class="font-black text-lg text-slate-900">Tugaskan Mekanik</h3>
            <p class="text-xs text-slate-500 mt-1" x-text="selectedService ? 'Tiket: ' + selectedService.ticket_number + ' (' + selectedService.car_model + ')' : ''"></p>

            <form :action="selectedService ? '/dashboard/services/' + selectedService.id + '/assign' : '#'" method="POST" class="mt-5 space-y-4">
                @csrf
                @method('PATCH')
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">Pilih Mekanik</label>
                    <select name="assigned_mechanic_id" required class="w-full px-4 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-black focus:border-black focus:outline-none">
                        <option value="">-- Pilih Mekanik Tersedia --</option>
                        @foreach($mechanics as $mech)
                            <option value="{{ $mech->id }}">{{ $mech->name }} ({{ $mech->email }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="assignModalOpen = false" class="px-4 py-2 rounded-lg text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-lg text-xs font-semibold bg-black text-white hover:bg-slate-800 transition">
                        Tugaskan & Konfirmasi Servis
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
