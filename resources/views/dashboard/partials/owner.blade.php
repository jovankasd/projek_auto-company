<div class="space-y-8" x-data="{ activeTab: 'summary' }">
    <!-- Header Owner -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-gradient-to-br from-slate-900 via-slate-900 to-blue-950 text-white p-6 sm:p-8 rounded-3xl shadow-lg border border-slate-800">
        <div>
            <div class="flex flex-wrap items-center gap-2 mb-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-blue-500/20 text-blue-200 border border-blue-400/30">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    <span>Owner Executive View</span>
                </span>
                <span class="text-xs text-slate-300 font-medium">Khusus Pemilik Dealer</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">Laporan Eksekutif & Hasil Usaha</h1>
            <p class="text-slate-300 text-sm mt-1 max-w-2xl">
                Pantau hasil akhir finansial, performa penjualan Sales, aktivitas bengkel Admin Mekanik, dan perkembangan Customer secara ringkas dan terpadu.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3 shrink-0">
            <a href="{{ route('reports.index') }}" class="min-h-[44px] px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold transition inline-flex items-center gap-2 shadow-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <span>Buka Laporan Detail</span>
            </a>
            <button type="button" onclick="window.print()" class="min-h-[44px] px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 text-white text-xs font-bold transition inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span>Cetak / PDF Laporan</span>
            </button>
        </div>
    </div>

    <!-- BANNER STATUS PENGIRIMAN LAPORAN DARI SUPER ADMIN -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-5 sm:p-6 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-700 border border-blue-100 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <h2 class="font-extrabold text-slate-900 text-base">Status Laporan Terverifikasi dari Super Admin</h2>
                    @if($lastReportSubmittedAt)
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase tracking-wider bg-emerald-50 text-emerald-800 border border-emerald-200">
                            Resmi Terkirim
                        </span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase tracking-wider bg-amber-50 text-amber-800 border border-amber-200">
                            Sinkronisasi Otomatis
                        </span>
                    @endif
                </div>
                @if($lastReportSubmittedAt)
                    <p class="text-xs text-slate-600 mt-1">
                        Diverifikasi & dikirimkan oleh <strong>{{ $lastReportSubmittedBy }}</strong> pada {{ \Carbon\Carbon::parse($lastReportSubmittedAt)->translatedFormat('d F Y, H:i') }} WIB.
                    </p>
                    @if($lastReportNote)
                        <p class="text-xs text-blue-950 bg-blue-50 px-3 py-2 rounded-xl border border-blue-100 mt-2">
                            Catatan Super Admin: &ldquo;{{ $lastReportNote }}&rdquo;
                        </p>
                    @endif
                @else
                    <p class="text-xs text-slate-600 mt-1">
                        Data real-time 3 divisi (Sales, Admin Mekanik, Customer) otomatis dihimpun di bawah ini. Super Admin dapat mengirimkan notifikasi rekap resmi sewaktu-waktu.
                    </p>
                @endif
            </div>
        </div>

        <!-- Mode Navigasi Tab Tampilan Owner -->
        <div class="inline-flex p-1 rounded-2xl bg-slate-100 border border-slate-200/80 shrink-0 text-xs self-start lg:self-center" role="tablist" aria-label="Mode tampilan laporan owner">
            <button type="button" 
                    role="tab"
                    :aria-selected="(activeTab === 'summary').toString()"
                    @click="activeTab = 'summary'" 
                    :class="activeTab === 'summary' ? 'bg-white text-slate-900 font-bold shadow-sm' : 'text-slate-600 font-semibold hover:text-slate-900'"
                    class="min-h-[44px] px-4 py-2 rounded-xl transition inline-flex items-center gap-1.5">
                <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                </svg>
                <span>Ringkasan Hasil</span>
            </button>
            <button type="button" 
                    role="tab"
                    :aria-selected="(activeTab === 'detail').toString()"
                    @click="activeTab = 'detail'" 
                    :class="activeTab === 'detail' ? 'bg-white text-slate-900 font-bold shadow-sm' : 'text-slate-600 font-semibold hover:text-slate-900'"
                    class="min-h-[44px] px-4 py-2 rounded-xl transition inline-flex items-center gap-1.5">
                <svg class="w-4 h-4 text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                </svg>
                <span>Laporan 3 Divisi</span>
            </button>
        </div>
    </div>

    <!-- TAB 1: RINGKASAN HASIL (EXECUTIVE SUMMARY) -->
    <div x-show="activeTab === 'summary'" class="space-y-8">
        <!-- 4 Kartu KPI Hasil Usaha Utama -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <!-- KPI 1: Pemasukan Bulan Ini -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 hover:shadow-md transition">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs uppercase font-bold text-slate-600 tracking-wider">Hasil Pemasukan Bengkel</span>
                    <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold text-sm shrink-0">
                        Rp
                    </div>
                </div>
                <p class="font-mono tabular-nums text-2xl font-black text-slate-900 mt-4 truncate" title="Rp {{ number_format($revenueThisMonth, 0, ',', '.') }}">
                    Rp {{ number_format($revenueThisMonth, 0, ',', '.') }}
                </p>
                <div class="flex items-center gap-1.5 mt-2 text-xs text-slate-600 font-medium">
                    <span class="text-emerald-700 font-bold">{{ $totalServicesCompletedThisMonth }} unit selesai</span>
                    <span>bulan ini</span>
                </div>
            </div>

            <!-- KPI 2: Akumulasi Pemasukan Tahunan -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 hover:shadow-md transition">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs uppercase font-bold text-slate-600 tracking-wider">Pemasukan Tahun {{ now()->year }}</span>
                    <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-700 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                </div>
                <p class="font-mono tabular-nums text-2xl font-black text-blue-700 mt-4 truncate" title="Rp {{ number_format($revenueThisYear, 0, ',', '.') }}">
                    Rp {{ number_format($revenueThisYear, 0, ',', '.') }}
                </p>
                <div class="flex items-center gap-1.5 mt-2 text-xs text-slate-600 font-medium">
                    <span class="text-blue-700 font-bold">Akumulasi bersih</span>
                    <span>tahun berjalan</span>
                </div>
            </div>

            <!-- KPI 3: Nilai Stok Mobil Showroom (Sales) -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 hover:shadow-md transition">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs uppercase font-bold text-slate-600 tracking-wider">Nilai Aset Mobil Showroom</span>
                    <div class="w-10 h-10 rounded-2xl bg-slate-100 text-slate-800 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                </div>
                <p class="font-mono tabular-nums text-2xl font-black text-slate-900 mt-4 truncate" title="Rp {{ number_format($carInventoryValue, 0, ',', '.') }}">
                    Rp {{ number_format($carInventoryValue, 0, ',', '.') }}
                </p>
                <div class="flex items-center gap-1.5 mt-2 text-xs text-slate-600 font-medium">
                    <span class="font-bold text-slate-900">{{ $totalCarStockQty }} unit tersedia</span>
                    <span>({{ $activeCarsForSale }} tipe mobil)</span>
                </div>
            </div>

            <!-- KPI 4: Nilai Aset Stok Sparepart (Admin Mekanik) -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 hover:shadow-md transition">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs uppercase font-bold text-slate-600 tracking-wider">Nilai Aset Suku Cadang</span>
                    <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-700 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        </svg>
                    </div>
                </div>
                <p class="font-mono tabular-nums text-2xl font-black text-amber-700 mt-4 truncate" title="Rp {{ number_format($sparePartInventoryValue, 0, ',', '.') }}">
                    Rp {{ number_format($sparePartInventoryValue, 0, ',', '.') }}
                </p>
                <div class="flex items-center gap-1.5 mt-2 text-xs text-slate-600 font-medium">
                    <span class="font-bold text-slate-900">{{ $totalSparePartsCount }} SKU siap pakai</span>
                    <span>({{ $lowStockSpareParts }} perlu restock)</span>
                </div>
            </div>
        </div>

        <!-- Grafik Pemasukan 6 Bulan & Komposisi Pendapatan -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Bar Chart Tren Pemasukan 6 Bulan -->
            <div class="lg:col-span-2 bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-8">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 border-b border-slate-100 gap-2">
                    <div>
                        <h2 class="text-lg font-black text-slate-900">Tren Pemasukan Bersih (6 Bulan Terakhir)</h2>
                        <p class="text-xs text-slate-600 mt-0.5">Ringkasan hasil usaha dealer & bengkel Dealer Pro per bulan.</p>
                    </div>
                    <div class="text-xs font-mono tabular-nums font-bold text-slate-800 bg-slate-100 px-3 py-1.5 rounded-xl self-start sm:self-center">
                        Total All-Time: Rp {{ number_format($totalAllTimeRevenue, 0, ',', '.') }}
                    </div>
                </div>

                @php
                    $maxRev = 0;
                    foreach ($monthlyRevenueChart as $m) {
                        if ($m['revenue'] > $maxRev) $maxRev = $m['revenue'];
                    }
                    if ($maxRev <= 0) $maxRev = 1;
                @endphp

                <div class="mt-6">
                    <div class="h-56 flex items-end gap-2 sm:gap-5 pt-6">
                        @foreach($monthlyRevenueChart as $mItem)
                            @php
                                $hPct = max(10, round(($mItem['revenue'] / $maxRev) * 100));
                                $shortJt = $mItem['revenue'] >= 1000000
                                    ? number_format($mItem['revenue'] / 1000000, 1, ',', '.') . ' Jt'
                                    : ($mItem['revenue'] > 0 ? number_format($mItem['revenue'] / 1000, 0, ',', '.') . ' Rb' : '0');
                            @endphp
                            <div class="flex-1 flex flex-col items-center gap-1.5 group relative">
                                <span class="text-[11px] font-mono tabular-nums font-bold text-slate-700 text-center">
                                    {{ $shortJt }}
                                </span>
                                <!-- Tooltip desktop -->
                                <div class="opacity-0 group-hover:opacity-100 pointer-events-none hidden sm:block absolute -top-12 bg-slate-900 text-white text-[11px] font-mono tabular-nums px-2.5 py-1.5 rounded-xl shadow-lg transition duration-200 z-10 whitespace-nowrap text-center">
                                    <div class="font-bold">Rp {{ number_format($mItem['revenue'], 0, ',', '.') }}</div>
                                    <div class="text-slate-300 text-[10px]">{{ $mItem['count'] }} Unit Servis</div>
                                </div>

                                <div class="w-full bg-slate-100 rounded-2xl transition-all duration-300 relative overflow-hidden"
                                     style="height: {{ $hPct }}%">
                                    <div class="absolute inset-0 bg-gradient-to-t from-blue-700 to-blue-500 group-hover:from-blue-800 group-hover:to-blue-600 rounded-2xl transition"></div>
                                </div>

                                <span class="text-[11px] font-bold text-slate-700 mt-1 truncate max-w-full">
                                    {{ $mItem['month_label'] }}
                                </span>
                                <span class="text-[10px] text-slate-600 sm:hidden tabular-nums">{{ $mItem['count'] }} srv</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Komposisi Pemasukan Servis -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-8 flex flex-col justify-between">
                <div>
                    <h2 class="text-lg font-black text-slate-900 pb-1">Komposisi Pemasukan</h2>
                    <p class="text-xs text-slate-600 mb-6">Sumber pendapatan bengkel bulan ini.</p>

                    @php
                        $totalMonthPartsLabor = ($revenueParts + $revenueLabor + $revenueConsumable);
                        $partsPct = $totalMonthPartsLabor > 0 ? round(($revenueParts / $totalMonthPartsLabor) * 100) : 0;
                        $laborPct = $totalMonthPartsLabor > 0 ? round(($revenueLabor / $totalMonthPartsLabor) * 100) : 0;
                        $consumablePct = $totalMonthPartsLabor > 0 ? round(($revenueConsumable / $totalMonthPartsLabor) * 100) : 0;
                    @endphp

                    <div class="space-y-4">
                        <!-- Sparepart -->
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80">
                            <div class="flex items-center justify-between text-xs font-bold text-slate-800 mb-1">
                                <span>Suku Cadang (Sparepart)</span>
                                <span class="font-mono tabular-nums text-slate-900">{{ $partsPct }}%</span>
                            </div>
                            <p class="font-mono tabular-nums text-lg font-black text-slate-900">
                                Rp {{ number_format($revenueParts, 0, ',', '.') }}
                            </p>
                        </div>

                        <!-- Jasa Mekanik -->
                        <div class="p-4 rounded-2xl bg-blue-50/70 border border-blue-100">
                            <div class="flex items-center justify-between text-xs font-bold text-blue-950 mb-1">
                                <span>Jasa Mekanik (Labor)</span>
                                <span class="font-mono tabular-nums text-blue-950">{{ $laborPct }}%</span>
                            </div>
                            <p class="font-mono tabular-nums text-lg font-black text-blue-950">
                                Rp {{ number_format($revenueLabor, 0, ',', '.') }}
                            </p>
                        </div>

                        <!-- Bahan & Oli -->
                        <div class="p-4 rounded-2xl bg-amber-50/70 border border-amber-100">
                            <div class="flex items-center justify-between text-xs font-bold text-amber-950 mb-1">
                                <span>Bahan & Pelumas (Consumable)</span>
                                <span class="font-mono tabular-nums text-amber-950">{{ $consumablePct }}%</span>
                            </div>
                            <p class="font-mono tabular-nums text-lg font-black text-amber-950">
                                Rp {{ number_format($revenueConsumable, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Mini Summary Customer -->
                <div class="mt-6 pt-5 border-t border-slate-100 flex items-center justify-between text-xs text-slate-600">
                    <div>
                        <span class="text-slate-600 block text-[11px]">Total Basis Customer</span>
                        <strong class="font-bold text-slate-900 text-sm tabular-nums">{{ $totalCustomers }} Pelanggan</strong>
                    </div>
                    <div class="text-right">
                        <span class="text-slate-600 block text-[11px]">Pertumbuhan Bulan Ini</span>
                        <strong class="font-bold text-emerald-700 text-sm tabular-nums">+{{ $newCustomersMonth }} Akun Baru</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 2: LAPORAN KESELURUHAN 3 DIVISI (CONSOLIDATED MASTER REPORT) -->
    <div x-show="activeTab === 'detail'" class="space-y-8">
        <!-- 3 Kartu Rangkuman Terpadu Divisi -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- 1. LAPORAN DIVISI SALES -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <span class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                            </svg>
                        </span>
                        <h3 class="font-black text-slate-900 text-sm">Laporan Divisi Sales</h3>
                    </div>
                    <span class="text-xs font-bold text-emerald-700">Showroom Mobil</span>
                </div>

                <div class="space-y-2.5 text-xs">
                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-2xl">
                        <span class="text-slate-600">Total Tipe Mobil Dipajang:</span>
                        <strong class="font-mono tabular-nums font-bold text-slate-900">{{ $activeCarsForSale }} Model</strong>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-2xl">
                        <span class="text-slate-600">Total Stok Fisik Unit:</span>
                        <strong class="font-mono tabular-nums font-bold text-slate-900">{{ $totalCarStockQty }} Unit</strong>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-2xl">
                        <span class="text-slate-600">Estimasi Nilai Total Aset Mobil:</span>
                        <strong class="font-mono tabular-nums font-bold text-slate-900">Rp {{ number_format($carInventoryValue, 0, ',', '.') }}</strong>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-2xl">
                        <span class="text-slate-600">Prospek Lead Test Drive:</span>
                        <strong class="font-mono tabular-nums font-bold text-blue-700">{{ $testDriveLeadsThisMonth }} Calon Pembeli</strong>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-2xl">
                        <span class="text-slate-600">Test Drive Selesai Dilayani:</span>
                        <strong class="font-mono tabular-nums font-bold text-emerald-700">{{ $testDriveCompletedThisMonth }} Kali</strong>
                    </div>
                </div>
            </div>

            <!-- 2. LAPORAN DIVISI ADMIN MEKANIK -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <span class="w-9 h-9 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </span>
                        <h3 class="font-black text-slate-900 text-sm">Laporan Divisi Admin Mekanik</h3>
                    </div>
                    <span class="text-xs font-bold text-amber-700">Workshop & Onderdil</span>
                </div>

                <div class="space-y-2.5 text-xs">
                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-2xl">
                        <span class="text-slate-600">Mobil Selesai Diservis Bulan Ini:</span>
                        <strong class="font-mono tabular-nums font-bold text-emerald-700">{{ $totalServicesCompletedThisMonth }} Unit</strong>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-2xl">
                        <span class="text-slate-600">Total Pemasukan Jasa Mekanik:</span>
                        <strong class="font-mono tabular-nums font-bold text-blue-700">Rp {{ number_format($revenueLabor, 0, ',', '.') }}</strong>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-2xl">
                        <span class="text-slate-600">Total Suku Cadang Terpasang:</span>
                        <strong class="font-mono tabular-nums font-bold text-slate-900">Rp {{ number_format($revenueParts, 0, ',', '.') }}</strong>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-2xl">
                        <span class="text-slate-600">Total SKU Suku Cadang Bengkel:</span>
                        <strong class="font-mono tabular-nums font-bold text-slate-900">{{ $totalSparePartsCount }} Jenis Part</strong>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-2xl">
                        <span class="text-slate-600">Suku Cadang Perlu Restock:</span>
                        <strong class="font-mono tabular-nums font-bold text-red-700">{{ $lowStockSpareParts }} Item</strong>
                    </div>
                </div>
            </div>

            <!-- 3. LAPORAN DIVISI CUSTOMER -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <span class="w-9 h-9 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </span>
                        <h3 class="font-black text-slate-900 text-sm">Laporan Divisi Customer</h3>
                    </div>
                    <span class="text-xs font-bold text-blue-700">Basis Pelanggan</span>
                </div>

                <div class="space-y-2.5 text-xs">
                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-2xl">
                        <span class="text-slate-600">Total Basis Pelanggan Terdaftar:</span>
                        <strong class="font-mono tabular-nums font-bold text-slate-900">{{ $totalCustomers }} Akun</strong>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-2xl">
                        <span class="text-slate-600">Pelanggan Baru Bulan Ini:</span>
                        <strong class="font-mono tabular-nums font-bold text-emerald-700">+{{ $newCustomersMonth }} Orang</strong>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-2xl">
                        <span class="text-slate-600">Rata-rata Biaya Servis / Unit:</span>
                        @php
                            $avgTicket = $totalServicesCompletedThisMonth > 0 ? round($revenueThisMonth / $totalServicesCompletedThisMonth) : 0;
                        @endphp
                        <strong class="font-mono tabular-nums font-bold text-slate-900">Rp {{ number_format($avgTicket, 0, ',', '.') }}</strong>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-2xl">
                        <span class="text-slate-600">Status Retensi & Kepuasan:</span>
                        <strong class="font-bold text-emerald-700">Tinggi (Repeat Service)</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Suku Cadang yang Paling Sering Digunakan untuk Perbaikan Mobil -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-8">
            <div class="flex flex-wrap items-center justify-between gap-2 pb-4 border-b border-slate-100">
                <div>
                    <h3 class="font-black text-lg text-slate-900">Suku Cadang Paling Sering Digunakan untuk Perbaikan Unit</h3>
                    <p class="text-xs text-slate-600 mt-0.5">Daftar onderdil mobil terlaris yang dipasang oleh Admin Mekanik bulan ini.</p>
                </div>
                <span class="text-xs font-bold text-blue-700 bg-blue-50 px-3 py-1 rounded-full border border-blue-200">
                    Rotasi Suku Cadang
                </span>
            </div>

            <!-- Mobile Cards -->
            <div class="mt-4 space-y-3 md:hidden">
                @forelse($topUsedSpareParts as $tsp)
                    <div class="p-4 rounded-2xl border border-slate-200/80 bg-slate-50/70 flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-bold text-slate-900 text-sm truncate">{{ $tsp->name }}</p>
                            <p class="text-xs font-mono tabular-nums text-slate-600 mt-0.5">{{ $tsp->total_qty }} unit terpasang</p>
                        </div>
                        <span class="font-mono tabular-nums font-bold text-emerald-700 text-sm shrink-0">
                            Rp {{ number_format($tsp->total_revenue, 0, ',', '.') }}
                        </span>
                    </div>
                @empty
                    <p class="py-8 text-center text-xs text-slate-600">
                        Belum ada data pemakaian sparepart yang tercatat pada tiket servis selesai.
                    </p>
                @endforelse
            </div>

            <!-- Desktop Table -->
            <div class="hidden md:block mt-4 overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-700">
                    <thead class="bg-slate-50 text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                        <tr>
                            <th class="py-3 px-4 rounded-l-xl">Nama Komponen / Sparepart</th>
                            <th class="py-3 px-4 text-center">Jumlah Dipasang (Qty)</th>
                            <th class="py-3 px-4 text-right rounded-r-xl">Total Nilai Pemasukan Part</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($topUsedSpareParts as $tsp)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3.5 px-4 font-bold text-slate-900">
                                    {{ $tsp->name }}
                                </td>
                                <td class="py-3.5 px-4 text-center font-mono tabular-nums font-bold text-slate-800">
                                    {{ $tsp->total_qty }} unit
                                </td>
                                <td class="py-3.5 px-4 text-right font-mono tabular-nums font-bold text-emerald-700">
                                    Rp {{ number_format($tsp->total_revenue, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-8 text-center text-xs text-slate-600">
                                    Belum ada data pemakaian sparepart yang tercatat pada tiket servis selesai.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TABEL SEMUA TRANSAKSI SELESAI -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-8">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 border-b border-slate-100 gap-2">
            <div>
                <h2 class="text-lg font-black text-slate-900">Pemasukan Transaksi Selesai Terkini</h2>
                <p class="text-xs text-slate-600 mt-0.5">Daftar transaksi servis dan pembelian suku cadang yang telah lunas dan diselesaikan.</p>
            </div>
            <span class="text-xs font-semibold px-3 py-1 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200 self-start sm:self-center">
                Pemasukan Resmi Masuk Kas
            </span>
        </div>

        @if($topRecentTransactions->count() > 0)
            <!-- Mobile Cards -->
            <div class="mt-4 space-y-3 md:hidden">
                @foreach($topRecentTransactions as $srv)
                    <a href="{{ route('services.show', $srv) }}" class="block p-4 rounded-2xl border border-slate-200/80 bg-slate-50/60 hover:bg-slate-50 transition space-y-2">
                        <div class="flex items-center justify-between gap-2">
                            <span class="font-mono font-bold text-sm text-blue-700">{{ $srv->ticket_number }}</span>
                            <span class="font-mono tabular-nums font-black text-emerald-700 text-sm">
                                Rp {{ number_format($srv->total_cost, 0, ',', '.') }}
                            </span>
                        </div>
                        <div>
                            <p class="font-bold text-slate-900 text-sm">{{ $srv->customer?->name ?? 'Pelanggan Umum' }}</p>
                            <p class="text-xs text-slate-600 font-mono">{{ $srv->car_model }} ({{ $srv->license_plate }})</p>
                        </div>
                        <div class="flex items-center justify-between text-xs text-slate-600 pt-2 border-t border-slate-200/70">
                            <span>{{ $srv->mechanic?->name ?? 'Admin Mekanik' }}</span>
                            <span>{{ $srv->completed_at ? $srv->completed_at->translatedFormat('d M Y, H:i') : 'Belum selesai' }}</span>
                        </div>
                    </a>
                @endforeach
            </div>

            <!-- Desktop Table -->
            <div class="hidden md:block mt-4 overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-700">
                    <thead class="bg-slate-50 text-[11px] font-bold text-slate-600 uppercase tracking-wider rounded-xl">
                        <tr>
                            <th class="py-3 px-4 rounded-l-xl">No. Tiket</th>
                            <th class="py-3 px-4">Pelanggan & Kendaraan</th>
                            <th class="py-3 px-4">Tanggal Selesai</th>
                            <th class="py-3 px-4">Mekanik</th>
                            <th class="py-3 px-4">Rincian Item</th>
                            <th class="py-3 px-4 rounded-r-xl text-right">Total Biaya (Pemasukan)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($topRecentTransactions as $srv)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-4 px-4 font-mono font-bold text-slate-900">
                                    <a href="{{ route('services.show', $srv) }}" class="text-blue-700 hover:underline">{{ $srv->ticket_number }}</a>
                                </td>
                                <td class="py-4 px-4">
                                    <div class="font-bold text-slate-900">{{ $srv->customer?->name ?? 'Pelanggan Umum' }}</div>
                                    <div class="text-xs text-slate-600 font-mono">{{ $srv->car_model }} ({{ $srv->license_plate }})</div>
                                </td>
                                <td class="py-4 px-4 text-xs text-slate-700">
                                    {{ $srv->completed_at ? $srv->completed_at->translatedFormat('d M Y, H:i') : 'Belum selesai' }}
                                </td>
                                <td class="py-4 px-4 text-xs font-medium text-slate-700">
                                    {{ $srv->mechanic?->name ?? 'Admin Mekanik' }}
                                </td>
                                <td class="py-4 px-4 text-xs text-slate-600">
                                    {{ $srv->items->count() }} item (part & labor)
                                </td>
                                <td class="py-4 px-4 font-mono tabular-nums font-black text-right text-emerald-700 text-base">
                                    Rp {{ number_format($srv->total_cost, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="py-12 text-center">
                <p class="text-sm text-slate-600">Belum ada transaksi servis yang berstatus selesai.</p>
            </div>
        @endif
    </div>
</div>
