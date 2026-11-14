<div class="space-y-8">
    <!-- Welcome Header Banner -->
    <div class="rounded-3xl bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 text-white p-6 sm:p-8 relative overflow-hidden shadow-xl border border-slate-800">
        <div class="relative z-10 max-w-2xl">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wider bg-white/10 text-white border border-white/20 mb-3">
                Portal Pelanggan Dealer Pro
            </span>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight">Selamat Datang, {{ auth()->user()->name }}!</h1>
            <p class="text-slate-300 text-sm sm:text-base mt-2">
                Pantau proses perawatan berkala kendaraan Anda secara real-time dan kelola jadwal test drive mobil impian Anda di satu tempat.
            </p>
            <div class="flex flex-wrap items-center gap-3 mt-6">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2 bg-white text-slate-900 px-5 py-2.5 rounded-xl font-bold text-sm hover:bg-slate-100 transition shadow-sm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Booking Servis / Test Drive
                </a>
                <a href="{{ route('services.my') }}" class="inline-flex items-center gap-2 bg-slate-800 text-white border border-slate-700 px-5 py-2.5 rounded-xl font-semibold text-sm hover:bg-slate-700 transition">
                    Lihat Semua Servis Saya
                </a>
            </div>
        </div>
        <div class="absolute right-0 bottom-0 opacity-10 pointer-events-none translate-x-12 translate-y-12">
            <svg class="w-96 h-96 text-white" fill="currentColor" viewBox="0 0 24 24">
                <path d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11c-.66 0-1.21.42-1.42 1.01L3 12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-8l-2.08-5.99zM6.5 16c-.83 0-1.5-.67-1.5-1.5S5.67 13 6.5 13s1.5.67 1.5 1.5S7.33 16 6.5 16zm11 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zM5 11l1.5-4.5h11L19 11H5z"/>
            </svg>
        </div>
    </div>

    <!-- Active Service Section with Visual Timeline -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 border-b border-slate-100 gap-4">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Status Pengerjaan Saat Ini</span>
                <h2 class="text-xl font-black text-slate-900 mt-1">Servis Aktif</h2>
            </div>
            @if($activeService)
                <div class="flex items-center gap-3">
                    <span class="font-mono text-xs font-bold text-slate-600 bg-slate-100 px-3 py-1.5 rounded-lg border border-slate-200">
                        {{ $activeService->ticket_number }}
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider border {{ $activeService->status->color() }}">
                        <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                        {{ $activeService->status->label() }}
                    </span>
                </div>
            @endif
        </div>

        @if($activeService)
            <!-- Service Info Grid -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 py-6 border-b border-slate-100">
                <div>
                    <span class="text-xs uppercase text-slate-400 font-semibold">Kendaraan</span>
                    <p class="font-bold text-base text-slate-900 mt-0.5">{{ $activeService->vehicle_model }}</p>
                    <p class="text-xs font-mono text-slate-500">{{ $activeService->vehicle_plate_number }} ({{ $activeService->vehicle_year }})</p>
                </div>
                <div>
                    <span class="text-xs uppercase text-slate-400 font-semibold">Jenis Servis</span>
                    <p class="font-bold text-base text-slate-900 mt-0.5 capitalize">{{ $activeService->service_type }} Service</p>
                    <p class="text-xs text-slate-500">Odometer: {{ number_format($activeService->odometer_km, 0, ',', '.') }} KM</p>
                </div>
                <div>
                    <span class="text-xs uppercase text-slate-400 font-semibold">Waktu Booking</span>
                    <p class="font-bold text-base text-slate-900 mt-0.5">{{ $activeService->service_date->translatedFormat('d M Y') }}</p>
                    <p class="text-xs text-slate-500">Pukul {{ sprintf('%02d:00', $activeService->slot_hour) }} WIB</p>
                </div>
                <div>
                    <span class="text-xs uppercase text-slate-400 font-semibold">Mekanik Penanggung Jawab</span>
                    <p class="font-bold text-base text-slate-900 mt-0.5">
                        {{ $activeService->mechanic ? $activeService->mechanic->name : 'Menunggu Penugasan' }}
                    </p>
                    <p class="text-xs text-slate-500">Bengkel Resmi Dealer Pro</p>
                </div>
            </div>

            <!-- Visual Progress Timeline -->
            <div class="py-8">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-6">Timeline Progres Servis</h3>
                
                @php
                    $steps = [
                        ['key' => 'pending', 'label' => 'Booking Dibuat', 'desc' => 'Menunggu verifikasi'],
                        ['key' => 'confirmed', 'label' => 'Dikonfirmasi', 'desc' => 'Mekanik ditugaskan'],
                        ['key' => 'in_progress', 'label' => 'Sedang Dikerjakan', 'desc' => 'Unit di stall bengkel'],
                        ['key' => 'completed', 'label' => 'Selesai', 'desc' => 'Siap diserahterimakan'],
                    ];
                    $currentIdx = match($activeService->status->value) {
                        'pending' => 0,
                        'confirmed' => 1,
                        'in_progress' => 2,
                        'completed' => 3,
                        'cancelled' => -1,
                        default => 0,
                    };
                @endphp

                @if($activeService->status->value === 'cancelled')
                    <div class="p-4 rounded-2xl bg-red-50 border border-red-200 text-red-700 text-sm font-semibold">
                        Servis ini telah dibatalkan. Alasan: {{ $activeService->cancellation_reason ?? 'Tidak disebutkan' }}
                    </div>
                @else
                    <div class="relative">
                        <!-- Progress Bar Line -->
                        <div class="hidden sm:block absolute top-5 left-8 right-8 h-1 bg-slate-200 -z-0">
                            <div class="h-full bg-slate-900 transition-all duration-500" style="width: {{ ($currentIdx / 3) * 100 }}%"></div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-6 relative z-10">
                            @foreach($steps as $idx => $step)
                                @php
                                    $isPassed = $idx <= $currentIdx;
                                    $isCurrent = $idx === $currentIdx;
                                @endphp
                                <div class="flex sm:flex-col items-center sm:items-center gap-4 sm:gap-2 text-left sm:text-center">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm border-2 transition duration-300 {{ $isPassed ? 'bg-slate-900 text-white border-slate-900 shadow-md' : 'bg-white text-slate-400 border-slate-200' }}">
                                        @if($isPassed && $idx < $currentIdx)
                                            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                            </svg>
                                        @else
                                            {{ $idx + 1 }}
                                        @endif
                                    </div>
                                    <div>
                                        <p class="font-bold text-sm {{ $isCurrent ? 'text-slate-900' : ($isPassed ? 'text-slate-700' : 'text-slate-400') }}">
                                            {{ $step['label'] }}
                                        </p>
                                        <p class="text-[11px] text-slate-500 mt-0.5">{{ $step['desc'] }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- Activity Logs / History -->
            @if($activeService->statusLogs->isNotEmpty())
                <div class="mt-4 pt-6 border-t border-slate-100">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Catatan Riwayat Servis</h4>
                    <div class="space-y-2.5">
                        @foreach($activeService->statusLogs as $log)
                            <div class="flex items-start gap-3 text-xs bg-slate-50 p-3 rounded-xl border border-slate-100">
                                <span class="w-2 h-2 rounded-full bg-slate-400 mt-1.5 shrink-0"></span>
                                <div class="flex-1">
                                    <p class="font-semibold text-slate-800">{{ $log->notes ?? 'Status diperbarui' }}</p>
                                    <p class="text-[10px] text-slate-400 mt-0.5">
                                        {{ $log->created_at->translatedFormat('d M Y, H:i') }} WIB 
                                        @if($log->changedBy) &bull; Oleh {{ $log->changedBy->name }} @endif
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @else
            <!-- Empty State for Active Service -->
            <div class="py-12 text-center">
                <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-4">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-800">Belum Ada Jadwal Servis Aktif</h3>
                <p class="text-sm text-slate-500 mt-1 max-w-sm mx-auto">
                    Kendaraan Anda dalam kondisi prima. Jadwalkan perawatan rutin untuk menjaga performa optimal.
                </p>
                <div class="mt-5">
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-2 bg-slate-900 text-white px-5 py-2.5 rounded-xl font-bold text-sm hover:bg-slate-800 transition">
                        Booking Servis Sekarang
                    </a>
                </div>
            </div>
        @endif
    </div>

    <!-- Upcoming Test Drive Section -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8">
        <div class="flex items-center justify-between pb-6 border-b border-slate-100">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Pengalaman Berkendara</span>
                <h2 class="text-xl font-black text-slate-900 mt-1">Jadwal Test Drive</h2>
            </div>
            <a href="{{ route('test-drives.my') }}" class="text-xs font-bold text-slate-700 hover:text-slate-900 transition">
                Lihat Semua &rarr;
            </a>
        </div>

        @if($upcomingTestDrive)
            <div class="mt-6 flex flex-col md:flex-row items-center gap-6 bg-slate-50 p-6 rounded-2xl border border-slate-100">
                <div class="w-full md:w-48 h-32 rounded-xl bg-slate-200 overflow-hidden flex items-center justify-center shrink-0">
                    @if($upcomingTestDrive->car->hero_image_transparent)
                        <img src="{{ asset($upcomingTestDrive->car->hero_image_transparent) }}" alt="{{ $upcomingTestDrive->car->model_name }}" class="w-full h-full object-contain p-2">
                    @else
                        <span class="font-bold text-xs uppercase text-slate-500">{{ $upcomingTestDrive->car->brand }}</span>
                    @endif
                </div>
                <div class="flex-1 w-full space-y-2">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-black text-slate-900">{{ $upcomingTestDrive->car->brand }} {{ $upcomingTestDrive->car->model_name }}</h3>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider border {{ $upcomingTestDrive->status->color() }}">
                            {{ $upcomingTestDrive->status->label() }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-600">
                        <span class="font-semibold text-slate-900">Jadwal:</span> {{ $upcomingTestDrive->preferred_date->translatedFormat('l, d F Y, H:i') }} WIB
                    </p>
                    <p class="text-xs text-slate-600">
                        <span class="font-semibold text-slate-900">Lokasi:</span> {{ $upcomingTestDrive->pickup_location ?? 'Showroom Pusat Dealer Pro' }}
                    </p>
                    @if($upcomingTestDrive->sales)
                        <p class="text-xs text-slate-600">
                            <span class="font-semibold text-slate-900">Sales Pendamping:</span> {{ $upcomingTestDrive->sales->name }} ({{ $upcomingTestDrive->sales->phone_number }})
                        </p>
                    @endif
                    <div class="pt-2">
                        <a href="{{ route('test-drives.show', $upcomingTestDrive) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-900 text-white font-bold text-xs hover:bg-slate-800 transition">
                            Lihat Rincian &rarr;
                        </a>
                    </div>
                </div>
            </div>
        @else
            <div class="py-8 text-center text-sm text-slate-500">
                <p>Belum ada pengajuan test drive aktif.</p>
                <a href="{{ route('home') }}" class="mt-3 inline-block font-bold text-slate-900 hover:underline">
                    Jelajahi Showroom & Ajukan Test Drive
                </a>
            </div>
        @endif
    </div>
</div>
