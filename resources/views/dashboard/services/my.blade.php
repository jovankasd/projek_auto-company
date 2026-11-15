<x-dashboard-layout title="Servis Berkala Saya" breadcrumb="Servis Saya">
    <div class="space-y-8">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900">Riwayat Servis Berkala</h1>
                <p class="text-slate-600 text-sm mt-1">Daftar lengkap jadwal servis, status pengerjaan, dan riwayat perawatan kendaraan Anda.</p>
            </div>
            <a href="{{ route('home') }}" class="min-h-[44px] inline-flex items-center justify-center gap-2 bg-slate-900 text-white px-5 py-2.5 rounded-xl font-bold text-sm hover:bg-blue-600 transition shadow-sm">
                <span>+ Booking Servis Baru</span>
            </a>
        </div>

        <!-- Table / Card Stack -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
            @if($services->isEmpty())
                <div class="py-16 text-center px-4">
                    <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-600 mx-auto flex items-center justify-center mb-4">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                    <h2 class="text-base font-bold text-slate-900">Belum Ada Riwayat Servis</h2>
                    <p class="text-sm text-slate-600 mt-1">Yuk, booking servis pertama Anda untuk menjaga performa mobil selalu prima.</p>
                    <a href="{{ route('home') }}" class="mt-4 min-h-[44px] inline-flex items-center gap-1.5 font-bold text-sm text-blue-700 hover:underline">
                        <span>Booking Servis Sekarang</span>
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
            @else
                <!-- Desktop Table -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-[11px] uppercase tracking-wider text-slate-600 font-bold">
                                <th class="py-3.5 px-6">Tiket & Tanggal</th>
                                <th class="py-3.5 px-6">Kendaraan</th>
                                <th class="py-3.5 px-6">Jenis Servis</th>
                                <th class="py-3.5 px-6">Biaya Total</th>
                                <th class="py-3.5 px-6">Status</th>
                                <th class="py-3.5 px-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @foreach($services as $srv)
                                @php
                                    $slotStr = sprintf('%02d:00', (int) $srv->slot_hour);
                                @endphp
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-4 px-6">
                                        <span class="font-mono font-bold text-slate-900 block">{{ $srv->ticket_number }}</span>
                                        <span class="text-xs text-slate-600">{{ $srv->service_date->translatedFormat('d F Y') }} ({{ $slotStr }} WIB)</span>
                                    </td>
                                    <td class="py-4 px-6">
                                        <p class="font-bold text-slate-900">{{ $srv->vehicle_model }}</p>
                                        <p class="text-xs font-mono text-slate-600">{{ $srv->vehicle_plate_number }}</p>
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="font-semibold capitalize text-slate-900">{{ $srv->service_type }}</span>
                                        <p class="text-xs text-slate-600">Odo: {{ number_format($srv->odometer_km, 0, ',', '.') }} KM</p>
                                    </td>
                                    <td class="py-4 px-6 font-mono tabular-nums font-bold text-slate-900">
                                        Rp{{ number_format($srv->total_cost, 0, ',', '.') }}
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider border {{ $srv->status->color() }}">
                                            {{ $srv->status->label() }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-right whitespace-nowrap space-x-1.5">
                                        <a href="{{ route('services.track', $srv) }}" class="min-h-[44px] inline-flex items-center gap-1 px-3 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-blue-600 transition">
                                            Lacak
                                        </a>
                                        <a href="{{ route('services.show', $srv) }}" class="min-h-[44px] inline-flex items-center gap-1 px-3 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-800 hover:bg-slate-50 transition">
                                            Detail
                                        </a>
                                        @can('cancel', $srv)
                                            <form method="POST" action="{{ route('services.cancel', $srv) }}" class="inline" onsubmit="return confirm('Batalkan jadwal servis ini?')">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="min-h-[44px] inline-flex items-center px-3 py-2 rounded-xl bg-red-50 text-red-700 border border-red-200 text-xs font-bold hover:bg-red-100 transition">
                                                    Batalkan
                                                </button>
                                            </form>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Card Stack -->
                <div class="md:hidden divide-y divide-slate-100">
                    @foreach($services as $srv)
                        @php
                            $slotStr = sprintf('%02d:00', (int) $srv->slot_hour);
                        @endphp
                        <div class="p-5 space-y-3">
                            <div class="flex items-center justify-between gap-2">
                                <span class="font-mono font-bold text-slate-900 text-xs">{{ $srv->ticket_number }}</span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider border {{ $srv->status->color() }}">
                                    {{ $srv->status->label() }}
                                </span>
                            </div>
                            <h3 class="font-bold text-base text-slate-900">{{ $srv->vehicle_model }} ({{ $srv->vehicle_plate_number }})</h3>
                            <div class="flex items-center justify-between text-xs text-slate-600">
                                <span>{{ $srv->service_date->translatedFormat('d M Y') }}, {{ $slotStr }} WIB</span>
                                <span class="font-mono tabular-nums font-bold text-slate-900">Rp{{ number_format($srv->total_cost, 0, ',', '.') }}</span>
                            </div>
                            <div class="pt-2 flex flex-wrap gap-2">
                                <a href="{{ route('services.track', $srv) }}" class="flex-1 min-h-[44px] inline-flex items-center justify-center rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-blue-600 transition">
                                    Lacak Servis
                                </a>
                                <a href="{{ route('services.show', $srv) }}" class="flex-1 min-h-[44px] inline-flex items-center justify-center rounded-xl border border-slate-200 text-xs font-bold text-slate-800 hover:bg-slate-50 transition">
                                    Lihat Rincian
                                </a>
                                @can('cancel', $srv)
                                    <form method="POST" action="{{ route('services.cancel', $srv) }}" class="w-full" onsubmit="return confirm('Batalkan jadwal servis ini?')">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="w-full min-h-[44px] rounded-xl bg-red-50 text-red-700 border border-red-200 text-xs font-bold hover:bg-red-100 transition">
                                            Batalkan Jadwal Servis
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="p-6 border-t border-slate-100">
                    {{ $services->links() }}
                </div>
            @endif
        </div>
    </div>
</x-dashboard-layout>
