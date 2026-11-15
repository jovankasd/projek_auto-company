<x-dashboard-layout title="Laporan & Analitik" breadcrumb="Laporan">
    <div class="space-y-8">
        <!-- Header & Date Filter -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900">Laporan Operasional & Keuangan</h1>
                <p class="text-slate-600 text-sm mt-1">Analisis pendapatan servis, beban kerja mekanik, dan konversi minat test drive.</p>
            </div>

            <!-- Date Filter Form -->
            <form method="GET" action="{{ route('reports.index') }}" class="flex flex-wrap items-center gap-2 bg-white p-2 rounded-2xl border border-slate-200/80 shadow-sm">
                <label for="report_start_date" class="sr-only">Tanggal Mulai</label>
                <input id="report_start_date" type="date" name="start_date" value="{{ $startDate }}" 
                       class="min-h-[44px] px-3 py-2 rounded-xl border border-slate-300 text-xs font-mono text-slate-900 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                <span class="text-xs text-slate-600 font-bold">s.d.</span>
                <label for="report_end_date" class="sr-only">Tanggal Selesai</label>
                <input id="report_end_date" type="date" name="end_date" value="{{ $endDate }}" 
                       class="min-h-[44px] px-3 py-2 rounded-xl border border-slate-300 text-xs font-mono text-slate-900 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                <button type="submit" class="min-h-[44px] px-5 py-2 bg-slate-900 text-white rounded-xl text-xs font-bold hover:bg-blue-600 transition">
                    Terapkan
                </button>
            </form>
        </div>

        <!-- Metric Overview -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6">
                <span class="text-xs uppercase font-bold text-slate-600 tracking-wider">Total Pendapatan Servis</span>
                <p class="font-mono tabular-nums text-2xl sm:text-3xl font-black text-emerald-700 mt-2 truncate" title="Rp{{ number_format($totalRevenue, 0, ',', '.') }}">
                    Rp{{ number_format($totalRevenue, 0, ',', '.') }}
                </p>
                <span class="text-xs text-slate-600 mt-1 block">Dari unit yang terselesaikan</span>
            </div>

            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6">
                <span class="text-xs uppercase font-bold text-slate-600 tracking-wider">Total Unit Servis Masuk</span>
                <p class="font-mono tabular-nums text-3xl font-black text-slate-900 mt-2">{{ $totalServices }}</p>
                <span class="text-xs text-slate-600 mt-1 block">Terselesaikan: {{ $completedServices }} unit</span>
            </div>

            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6">
                <span class="text-xs uppercase font-bold text-slate-600 tracking-wider">Permohonan Test Drive</span>
                <p class="font-mono tabular-nums text-3xl font-black text-blue-700 mt-2">{{ $totalTestDrives }}</p>
                <span class="text-xs text-slate-600 mt-1 block">Permintaan calon pembeli</span>
            </div>

            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6">
                <span class="text-xs uppercase font-bold text-slate-600 tracking-wider">Tingkat Penyelesaian Servis</span>
                @php
                    $rate = $totalServices > 0 ? round(($completedServices / $totalServices) * 100) : 0;
                @endphp
                <p class="font-mono tabular-nums text-3xl font-black text-slate-900 mt-2">{{ $rate }}%</p>
                <span class="text-xs text-slate-600 mt-1 block">Efisiensi operasional bengkel</span>
            </div>
        </div>

        <!-- 2 Column Breakdown -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Breakdown Jenis Servis -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-8">
                <h2 class="font-black text-lg text-slate-900 pb-4 border-b border-slate-100">Distribusi Paket Servis</h2>
                <div class="space-y-4 mt-6">
                    @forelse($servicesByType as $type => $count)
                        <div>
                            <div class="flex items-center justify-between text-xs font-bold text-slate-800 mb-1.5">
                                <span class="capitalize">{{ $type }} Service</span>
                                <span class="font-mono tabular-nums">{{ $count }} Unit ({{ $totalServices > 0 ? round(($count / $totalServices) * 100) : 0 }}%)</span>
                            </div>
                            <div class="w-full h-2.5 rounded-full bg-slate-100 overflow-hidden">
                                <div class="h-full bg-blue-600 rounded-full" style="width: {{ $totalServices > 0 ? round(($count / $totalServices) * 100) : 0 }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="py-6 text-center text-xs text-slate-600">Tidak ada data transaksi servis pada rentang tanggal ini.</p>
                    @endforelse
                </div>
            </div>

            <!-- Performa Mekanik -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-8">
                <h2 class="font-black text-lg text-slate-900 pb-4 border-b border-slate-100">Beban Kerja & Performa Mekanik</h2>

                <!-- Mobile Cards -->
                <div class="mt-4 space-y-3 md:hidden">
                    @forelse($mechanics as $mech)
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/70 flex items-center justify-between gap-3">
                            <div>
                                <p class="font-bold text-slate-900 text-sm">{{ $mech['name'] }}</p>
                                <p class="text-xs font-mono text-slate-600 mt-0.5">{{ $mech['assigned'] }} tiket ditugaskan</p>
                            </div>
                            <span class="font-mono tabular-nums font-bold text-emerald-700 text-sm">{{ $mech['completed'] }} selesai</span>
                        </div>
                    @empty
                        <p class="py-6 text-center text-xs text-slate-600">Belum ada mekanik terdaftar.</p>
                    @endforelse
                </div>

                <!-- Desktop Table -->
                <div class="hidden md:block overflow-x-auto mt-4">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="text-[11px] uppercase tracking-wider text-slate-600 font-bold border-b border-slate-200">
                                <th class="pb-3">Nama Mekanik</th>
                                <th class="pb-3 text-center">Ditugaskan</th>
                                <th class="pb-3 text-right">Diselesaikan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($mechanics as $mech)
                                <tr>
                                    <td class="py-3 font-bold text-slate-900">{{ $mech['name'] }}</td>
                                    <td class="py-3 text-center font-mono tabular-nums text-slate-700">{{ $mech['assigned'] }} tiket</td>
                                    <td class="py-3 text-right font-mono tabular-nums font-bold text-emerald-700">{{ $mech['completed'] }} unit</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-6 text-center text-xs text-slate-600">Belum ada mekanik terdaftar.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Popular Cars Interest -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-8">
            <h2 class="font-black text-lg text-slate-900 pb-4 border-b border-slate-100">Mobil Paling Diminati untuk Test Drive</h2>
            @if($popularCars->isEmpty())
                <p class="py-8 text-center text-xs text-slate-600">Belum ada data permohonan test drive untuk periode ini.</p>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mt-6">
                    @foreach($popularCars as $idx => $pCar)
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/70 space-y-1">
                            <span class="text-[11px] font-bold uppercase text-slate-600">Peringkat #{{ $idx + 1 }}</span>
                            <h3 class="font-black text-slate-900 text-sm leading-tight">{{ $pCar->brand }} {{ $pCar->model_name }}</h3>
                            <p class="font-mono tabular-nums text-xs font-bold text-blue-700 pt-1">{{ $pCar->test_drives_count }} Calon Pembeli</p>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-dashboard-layout>
