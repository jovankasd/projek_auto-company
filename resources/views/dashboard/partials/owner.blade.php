<div class="space-y-8" x-data="{ activeTab: 'summary' }">
    <!-- Header Owner -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-900 text-white p-6 sm:p-8 rounded-2xl shadow-sm border border-slate-800 relative overflow-hidden">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wide bg-slate-800 text-slate-200 border border-slate-700">
                    Laporan Eksekutif Pemilik
                </span>
                <span class="text-xs text-slate-400 font-medium">Khusus Pemilik Dealer</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">Laporan Eksekutif & Hasil Usaha</h1>
            <p class="text-slate-300 text-sm mt-1 max-w-2xl">
                Pantau hasil akhir finansial, performa penjualan Sales, aktivitas bengkel Admin Mekanik, dan perkembangan Customer secara ringkas dan terpadu.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <button type="button" onclick="window.print()" class="px-4 py-2.5 rounded-lg bg-slate-800 hover:bg-slate-700 border border-slate-700 text-white text-xs font-semibold transition flex items-center gap-2 shadow-sm focus:outline-none focus:ring-2 focus:ring-white">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span>Cetak / PDF Laporan</span>
            </button>
        </div>
    </div>

    <!-- BANNER STATUS PENGIRIMAN LAPORAN DARI SUPER ADMIN -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 sm:p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-xl bg-slate-100 text-slate-800 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h3 class="font-extrabold text-slate-900 text-base">Status Laporan Terverifikasi dari Super Admin</h3>
                    @if($lastReportSubmittedAt)
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-200">
                            Resmi Terkirim
                        </span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-amber-100 text-amber-800 border border-amber-200">
                            Sinkronisasi Otomatis
                        </span>
                    @endif
                </div>
                @if($lastReportSubmittedAt)
                    <p class="text-xs text-slate-600 mt-1">
                        Diverifikasi & dikirimkan oleh <strong>{{ $lastReportSubmittedBy }}</strong> pada {{ \Carbon\Carbon::parse($lastReportSubmittedAt)->translatedFormat('d F Y, H:i') }} WIB.
                    </p>
                    @if($lastReportNote)
                        <p class="text-xs text-slate-700 bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-200 mt-2 italic">
                            Catatan Super Admin: &ldquo;{{ $lastReportNote }}&rdquo;
                        </p>
                    @endif
                @else
                    <p class="text-xs text-slate-500 mt-1">
                        Data real-time 3 divisi (Sales, Admin Mekanik, Customer) otomatis dihimpun di bawah ini. Super Admin dapat mengirimkan notifikasi rekap resmi sewaktu-waktu.
                    </p>
                @endif
            </div>
        </div>

        <!-- Mode Navigasi Tab Tampilan Owner -->
        <div class="inline-flex p-1 rounded-xl bg-slate-100 border border-slate-200 shrink-0 text-xs">
            <button type="button" 
                    @click="activeTab = 'summary'" 
                    :class="activeTab === 'summary' ? 'bg-white text-slate-900 font-extrabold shadow-sm' : 'text-slate-600 font-semibold'"
                    class="px-4 py-2 rounded-lg transition">
                Ringkasan Eksekutif
            </button>
            <button type="button" 
                    @click="activeTab = 'detail'" 
                    :class="activeTab === 'detail' ? 'bg-white text-slate-900 font-extrabold shadow-sm' : 'text-slate-600 font-semibold'"
                    class="px-4 py-2 rounded-lg transition">
                Rincian 3 Divisi
            </button>
        </div>
    </div>

    <!-- TAB 1: LIHAT HASILNYA SAJA (EXECUTIVE SUMMARY) -->
    <div x-show="activeTab === 'summary'" class="space-y-8">
        <!-- 4 Kartu KPI Hasil Usaha Utama -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <!-- KPI 1: Pemasukan Bulan Ini -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <span class="text-xs uppercase font-semibold text-slate-500 tracking-wider">Hasil Pemasukan Bengkel</span>
                    <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold">
                        <span class="text-sm font-bold">Rp</span>
                    </div>
                </div>
                <p class="font-mono text-2xl xl:text-3xl font-semibold text-slate-900 mt-4 truncate" title="Rp {{ number_format($revenueThisMonth, 0, ',', '.') }}">
                    Rp {{ number_format($revenueThisMonth, 0, ',', '.') }}
                </p>
                <div class="flex items-center gap-1.5 mt-2 text-xs text-slate-500 font-medium">
                    <span class="text-emerald-700 font-semibold">&bull; {{ $totalServicesCompletedThisMonth }} unit selesai</span>
                    <span>bulan ini</span>
                </div>
            </div>

            <!-- KPI 2: Akumulasi Pemasukan Tahunan -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <span class="text-xs uppercase font-semibold text-slate-500 tracking-wider">Pemasukan Tahun {{ now()->year }}</span>
                    <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-700 flex items-center justify-center font-bold">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                </div>
                <p class="font-mono text-2xl xl:text-3xl font-semibold text-slate-900 mt-4 truncate" title="Rp {{ number_format($revenueThisYear, 0, ',', '.') }}">
                    Rp {{ number_format($revenueThisYear, 0, ',', '.') }}
                </p>
                <div class="flex items-center gap-1.5 mt-2 text-xs text-slate-500 font-medium">
                    <span class="text-blue-700 font-semibold">&bull; Akumulasi bersih</span>
                    <span>tahun berjalan</span>
                </div>
            </div>

            <!-- KPI 3: Nilai Stok Mobil Showroom (Sales) -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <span class="text-xs uppercase font-semibold text-slate-500 tracking-wider">Nilai Aset Mobil Showroom</span>
                    <div class="w-10 h-10 rounded-lg bg-slate-100 text-slate-800 flex items-center justify-center font-bold">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                </div>
                <p class="font-mono text-2xl xl:text-3xl font-semibold text-slate-900 mt-4 truncate" title="Rp {{ number_format($carInventoryValue, 0, ',', '.') }}">
                    Rp {{ number_format($carInventoryValue, 0, ',', '.') }}
                </p>
                <div class="flex items-center gap-1.5 mt-2 text-xs text-slate-500 font-medium">
                    <span class="font-bold text-slate-800">{{ $totalCarStockQty }} unit tersedia</span>
                    <span>({{ $activeCarsForSale }} tipe mobil)</span>
                </div>
            </div>

            <!-- KPI 4: Nilai Aset Stok Sparepart (Admin Mekanik) -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <span class="text-xs uppercase font-semibold text-slate-500 tracking-wider">Nilai Aset Suku Cadang</span>
                    <div class="w-10 h-10 rounded-lg bg-amber-50 text-amber-700 flex items-center justify-center font-bold">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        </svg>
                    </div>
                </div>
                <p class="font-mono text-2xl xl:text-3xl font-semibold text-slate-900 mt-4 truncate" title="Rp {{ number_format($sparePartInventoryValue, 0, ',', '.') }}">
                    Rp {{ number_format($sparePartInventoryValue, 0, ',', '.') }}
                </p>
                <div class="flex items-center gap-1.5 mt-2 text-xs text-slate-500 font-medium">
                    <span class="font-bold text-slate-800">{{ $totalSparePartsCount }} SKU siap pakai</span>
                    <span>({{ $lowStockSpareParts }} perlu restock)</span>
                </div>
            </div>
        </div>

        <!-- Grafik Pemasukan 6 Bulan & Komposisi Pendapatan -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Bar Chart Tren Pemasukan 6 Bulan -->
            <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8 overflow-hidden">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 border-b border-slate-100 gap-2">
                    <div>
                        <h2 class="text-lg font-black text-slate-900">Tren Pemasukan Bersih (6 Bulan Terakhir)</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Ringkasan hasil usaha Dealer Pro per bulan.</p>
                    </div>
                    <div class="text-xs font-mono font-bold text-slate-700 bg-slate-100 px-3 py-1.5 rounded-lg">
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

                <div class="mt-8">
                    <div class="h-52 flex items-end gap-3 sm:gap-6 pt-6">
                        @foreach($monthlyRevenueChart as $mItem)
                            @php
                                $hPct = max(10, round(($mItem['revenue'] / $maxRev) * 100));
                            @endphp
                            <div class="flex-1 flex flex-col items-center gap-2 group relative">
                                <!-- Tooltip -->
                                <div class="opacity-0 group-hover:opacity-100 pointer-events-none absolute -top-12 bg-slate-900 text-white text-[10px] font-mono p-2 rounded-lg shadow-lg transition duration-200 z-10 whitespace-nowrap text-center">
                                    <div class="font-bold">Rp {{ number_format($mItem['revenue'], 0, ',', '.') }}</div>
                                    <div class="text-slate-400 text-[9px]">{{ $mItem['count'] }} Unit Servis</div>
                                </div>

                                <div class="w-full bg-slate-100 rounded-lg transition-all duration-300 relative overflow-hidden h-full flex items-end">
                                    <div class="w-full bg-slate-800 group-hover:bg-blue-600 transition-colors duration-200 rounded-t"
                                         style="height: {{ $hPct }}%"></div>
                                </div>

                                <span class="text-[11px] font-semibold text-slate-600 mt-1 truncate">
                                    {{ $mItem['month_label'] }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Komposisi Pemasukan Servis -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8 flex flex-col justify-between">
                <div>
                    <h2 class="text-lg font-black text-slate-900 pb-2">Komposisi Pemasukan</h2>
                    <p class="text-xs text-slate-500 mb-6">Sumber pendapatan bengkel bulan ini.</p>

                    @php
                        $totalMonthPartsLabor = ($revenueParts + $revenueLabor + $revenueConsumable);
                        $partsPct = $totalMonthPartsLabor > 0 ? round(($revenueParts / $totalMonthPartsLabor) * 100) : 0;
                        $laborPct = $totalMonthPartsLabor > 0 ? round(($revenueLabor / $totalMonthPartsLabor) * 100) : 0;
                        $consumablePct = $totalMonthPartsLabor > 0 ? round(($revenueConsumable / $totalMonthPartsLabor) * 100) : 0;
                    @endphp

                    <div class="space-y-4">
                        <!-- Sparepart -->
                        <div class="p-4 rounded-xl bg-blue-50/80 border border-blue-200">
                            <div class="flex items-center justify-between text-xs font-semibold text-slate-700 mb-1">
                                <span class="flex items-center gap-1.5 text-blue-900">
                                    <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                                    Suku Cadang (Sparepart)
                                </span>
                                <span class="font-mono font-bold text-blue-900">{{ $partsPct }}%</span>
                            </div>
                            <p class="font-mono text-lg font-bold text-blue-900">
                                Rp {{ number_format($revenueParts, 0, ',', '.') }}
                            </p>
                        </div>

                        <!-- Jasa Mekanik -->
                        <div class="p-4 rounded-xl bg-slate-100/80 border border-slate-200">
                            <div class="flex items-center justify-between text-xs font-semibold text-slate-700 mb-1">
                                <span class="flex items-center gap-1.5 text-slate-900">
                                    <span class="w-2.5 h-2.5 rounded-full bg-slate-800"></span>
                                    Jasa Mekanik (Labor)
                                </span>
                                <span class="font-mono font-bold text-slate-900">{{ $laborPct }}%</span>
                            </div>
                            <p class="font-mono text-lg font-bold text-slate-900">
                                Rp {{ number_format($revenueLabor, 0, ',', '.') }}
                            </p>
                        </div>

                        <!-- Bahan & Oli -->
                        <div class="p-4 rounded-xl bg-amber-50/80 border border-amber-200">
                            <div class="flex items-center justify-between text-xs font-semibold text-slate-700 mb-1">
                                <span class="flex items-center gap-1.5 text-amber-900">
                                    <span class="w-2.5 h-2.5 rounded-full bg-amber-600"></span>
                                    Bahan & Pelumas (Consumable)
                                </span>
                                <span class="font-mono font-bold text-amber-900">{{ $consumablePct }}%</span>
                            </div>
                            <p class="font-mono text-lg font-bold text-amber-900">
                                Rp {{ number_format($revenueConsumable, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Mini Summary Customer -->
                <div class="mt-6 pt-5 border-t border-slate-100 flex items-center justify-between text-xs text-slate-600">
                    <div>
                        <span class="text-slate-500 block text-[11px]">Basis Pelanggan</span>
                        <strong class="font-bold text-slate-900 text-sm">{{ $totalCustomers }} Pelanggan</strong>
                    </div>
                    <div class="text-right">
                        <span class="text-slate-500 block text-[11px]">Pertumbuhan Bulan Ini</span>
                        <strong class="font-bold text-emerald-700 text-sm">+{{ $newCustomersMonth }} Akun Baru</strong>
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
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-lg bg-slate-100 text-slate-800 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                            </svg>
                        </span>
                        <h3 class="font-black text-slate-900 text-sm">Laporan Divisi Sales</h3>
                    </div>
                    <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200">Showroom Mobil</span>
                </div>

                <div class="space-y-2.5 text-xs">
                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl">
                        <span class="text-slate-600">Total Tipe Mobil Dipajang:</span>
                        <strong class="font-mono font-bold text-slate-900">{{ $activeCarsForSale }} Model</strong>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl">
                        <span class="text-slate-600">Total Stok Fisik Unit:</span>
                        <strong class="font-mono font-bold text-slate-900">{{ $totalCarStockQty }} Unit</strong>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl">
                        <span class="text-slate-600">Estimasi Nilai Total Aset Mobil:</span>
                        <strong class="font-mono font-bold text-slate-900">Rp {{ number_format($carInventoryValue, 0, ',', '.') }}</strong>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl">
                        <span class="text-slate-600">Prospek Lead Test Drive:</span>
                        <strong class="font-mono font-bold text-blue-700">{{ $testDriveLeadsThisMonth }} Calon Pembeli</strong>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl">
                        <span class="text-slate-600">Test Drive Selesai Dilayani:</span>
                        <strong class="font-mono font-bold text-emerald-700">{{ $testDriveCompletedThisMonth }} Kali</strong>
                    </div>
                </div>
            </div>

            <!-- 2. LAPORAN DIVISI ADMIN MEKANIK -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-lg bg-slate-100 text-slate-800 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </span>
                        <h3 class="font-black text-slate-900 text-sm">Laporan Divisi Admin Mekanik</h3>
                    </div>
                    <span class="text-xs font-bold text-amber-700 bg-amber-50 px-2.5 py-0.5 rounded-full border border-amber-200">Bengkel & Suku Cadang</span>
                </div>

                <div class="space-y-2.5 text-xs">
                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl">
                        <span class="text-slate-600">Mobil Selesai Diservis Bulan Ini:</span>
                        <strong class="font-mono font-bold text-emerald-700">{{ $totalServicesCompletedThisMonth }} Unit</strong>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl">
                        <span class="text-slate-600">Total Pemasukan Jasa Mekanik:</span>
                        <strong class="font-mono font-bold text-slate-900">Rp {{ number_format($revenueLabor, 0, ',', '.') }}</strong>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl">
                        <span class="text-slate-600">Total Suku Cadang Terpasang:</span>
                        <strong class="font-mono font-bold text-slate-900">Rp {{ number_format($revenueParts, 0, ',', '.') }}</strong>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl">
                        <span class="text-slate-600">Total SKU Suku Cadang Bengkel:</span>
                        <strong class="font-mono font-bold text-slate-900">{{ $totalSparePartsCount }} Jenis Part</strong>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl">
                        <span class="text-slate-600">Suku Cadang Perlu Restock:</span>
                        <strong class="font-mono font-bold text-red-600">{{ $lowStockSpareParts }} Item</strong>
                    </div>
                </div>
            </div>

            <!-- 3. LAPORAN DIVISI CUSTOMER -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-lg bg-slate-100 text-slate-800 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </span>
                        <h3 class="font-black text-slate-900 text-sm">Laporan Divisi Customer</h3>
                    </div>
                    <span class="text-xs font-bold text-blue-700 bg-blue-50 px-2.5 py-0.5 rounded-full border border-blue-200">Basis Pelanggan</span>
                </div>

                <div class="space-y-2.5 text-xs">
                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl">
                        <span class="text-slate-600">Total Basis Pelanggan Terdaftar:</span>
                        <strong class="font-mono font-bold text-slate-900">{{ $totalCustomers }} Akun</strong>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl">
                        <span class="text-slate-600">Pelanggan Baru Bulan Ini:</span>
                        <strong class="font-mono font-bold text-emerald-700">+{{ $newCustomersMonth }} Orang</strong>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl">
                        <span class="text-slate-600">Rata-rata Biaya Servis / Unit:</span>
                        @php
                            $avgTicket = $totalServicesCompletedThisMonth > 0 ? round($revenueThisMonth / $totalServicesCompletedThisMonth) : 0;
                        @endphp
                        <strong class="font-mono font-bold text-slate-900">Rp {{ number_format($avgTicket, 0, ',', '.') }}</strong>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl">
                        <span class="text-slate-600">Status Retensi & Kepuasan:</span>
                        <strong class="font-bold text-emerald-700">Tinggi (Repeat Service)</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Suku Cadang yang Paling Sering Digunakan untuk Perbaikan Mobil -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div>
                    <h3 class="font-black text-lg text-slate-900">Suku Cadang Paling Sering Digunakan</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Daftar suku cadang terlaris yang dipasang oleh Admin Mekanik bulan ini.</p>
                </div>
                <span class="text-xs font-semibold text-slate-700 bg-slate-100 px-3 py-1 rounded-full border border-slate-200">
                    Rotasi Suku Cadang
                </span>
            </div>

            <div class="mt-4 overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-700">
                    <thead class="bg-slate-50 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">
                        <tr>
                            <th class="py-3 px-4 rounded-l-lg">Nama Suku Cadang</th>
                            <th class="py-3 px-4 text-center">Jumlah Terpasang (Qty)</th>
                            <th class="py-3 px-4 text-right rounded-r-lg">Total Nilai Pemasukan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($topUsedSpareParts as $tsp)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3.5 px-4 font-bold text-slate-900">
                                    {{ $tsp->name }}
                                </td>
                                <td class="py-3.5 px-4 text-center font-mono font-semibold text-slate-800">
                                    {{ $tsp->total_qty }} unit
                                </td>
                                <td class="py-3.5 px-4 text-right font-mono font-bold text-emerald-700">
                                    Rp {{ number_format($tsp->total_revenue, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-8 text-center text-xs text-slate-500">
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
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 border-b border-slate-100 gap-2">
            <div>
                <h2 class="text-lg font-black text-slate-900">Pemasukan Transaksi Selesai Terkini</h2>
                <p class="text-xs text-slate-500 mt-0.5">Daftar transaksi servis dan suku cadang yang telah selesai dan tercatat lunas.</p>
            </div>
            <span class="text-xs font-semibold px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                Pemasukan Resmi Masuk Kas
            </span>
        </div>

        @if($topRecentTransactions->count() > 0)
            <div class="mt-4 overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 text-[11px] font-semibold text-slate-500 uppercase tracking-wider rounded-lg">
                        <tr>
                            <th class="py-3 px-4 rounded-l-lg">No. Tiket</th>
                            <th class="py-3 px-4">Pelanggan & Kendaraan</th>
                            <th class="py-3 px-4">Tanggal Selesai</th>
                            <th class="py-3 px-4">Mekanik</th>
                            <th class="py-3 px-4">Rincian Item</th>
                            <th class="py-3 px-4 rounded-r-lg text-right">Total Biaya (Pemasukan)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($topRecentTransactions as $srv)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-4 px-4 font-mono font-bold text-slate-900">
                                    {{ $srv->ticket_number }}
                                </td>
                                <td class="py-4 px-4">
                                    <div class="font-bold text-slate-900">{{ $srv->customer->name ?? 'Pelanggan Umum' }}</div>
                                    <div class="text-xs text-slate-500 font-mono">{{ $srv->car_model }} ({{ $srv->license_plate }})</div>
                                </td>
                                <td class="py-4 px-4 text-xs text-slate-700">
                                    {{ $srv->completed_at ? $srv->completed_at->translatedFormat('d M Y, H:i') : '-' }} WIB
                                </td>
                                <td class="py-4 px-4 text-xs font-medium text-slate-700">
                                    {{ $srv->mechanic->name ?? 'Admin Mekanik' }}
                                </td>
                                <td class="py-4 px-4 text-xs text-slate-500">
                                    {{ $srv->items->count() }} item (part & labor)
                                </td>
                                <td class="py-4 px-4 font-mono font-bold text-right text-emerald-700 text-base">
                                    Rp {{ number_format($srv->total_cost, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="py-12 text-center">
                <p class="text-sm text-slate-500">Belum ada transaksi servis yang berstatus selesai.</p>
            </div>
        @endif
    </div>
</div>
