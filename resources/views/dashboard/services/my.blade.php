<x-dashboard-layout title="Servis Berkala Saya" breadcrumb="Servis Saya">
    <div class="space-y-8">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900">Riwayat Servis Berkala</h1>
                <p class="text-slate-500 text-sm mt-1">Daftar lengkap jadwal servis, status pengerjaan, dan riwayat perawatan kendaraan Anda.</p>
            </div>
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2 bg-slate-900 text-white px-5 py-2.5 rounded-xl font-bold text-sm hover:bg-slate-800 transition shadow-sm">
                + Booking Servis Baru
            </a>
        </div>

        <!-- Table / Card Stack -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
            @if($services->isEmpty())
                <div class="py-16 text-center">
                    <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-4">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-800">Belum Ada Riwayat Servis</h3>
                    <p class="text-sm text-slate-500 mt-1">Yuk, booking servis pertama Anda untuk menjaga performa mobil selalu prima.</p>
                    <a href="{{ route('home') }}" class="mt-4 inline-block font-bold text-sm text-slate-900 hover:underline">
                        Booking Servis Sekarang &rarr;
                    </a>
                </div>
            @else
                <!-- Desktop Table -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-[11px] uppercase tracking-wider text-slate-500 font-bold">
                                <th class="py-3.5 px-6">Tiket & Tanggal</th>
                                <th class="py-3.5 px-6">Kendaraan</th>
                                <th class="py-3.5 px-6">Jenis Servis</th>
                                <th class="py-3.5 px-6">Biaya Total</th>
                                <th class="py-3.5 px-6">Status</th>
                                <th class="py-3.5 px-6 text-right">Rincian</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @foreach($services as $srv)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-4 px-6">
                                        <span class="font-mono font-bold text-slate-900 block">{{ $srv->ticket_number }}</span>
                                        <span class="text-xs text-slate-500">{{ \App\Support\Timezone::format($srv->service_date, 'd F Y', false) }} ({{ sprintf('%02d:00', $srv->slot_hour) }} WIB)</span>
                                    </td>
                                    <td class="py-4 px-6">
                                        <p class="font-bold text-slate-900">{{ $srv->vehicle_model }}</p>
                                        <p class="text-xs font-mono text-slate-500">{{ $srv->vehicle_plate_number }}</p>
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="font-semibold capitalize text-slate-800">{{ $srv->service_type }}</span>
                                        <p class="text-xs text-slate-400">Odo: {{ number_format($srv->odometer_km, 0, ',', '.') }} KM</p>
                                    </td>
                                    <td class="py-4 px-6 font-mono font-bold text-slate-900">
                                        Rp{{ number_format($srv->total_cost, 0, ',', '.') }}
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider border {{ $srv->status->color() }}">
                                            <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                            {{ $srv->status->label() }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-right">
                                        <a href="{{ route('services.show', $srv) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-bold text-slate-700 hover:bg-slate-50 transition">
                                            Detail &rarr;
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Card Stack -->
                <div class="md:hidden divide-y divide-slate-100">
                    @foreach($services as $srv)
                        <div class="p-5 space-y-2.5">
                            <div class="flex items-center justify-between">
                                <span class="font-mono font-bold text-slate-900 text-xs">{{ $srv->ticket_number }}</span>
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider border {{ $srv->status->color() }}">
                                    {{ $srv->status->label() }}
                                </span>
                            </div>
                            <h4 class="font-bold text-base text-slate-900">{{ $srv->vehicle_model }} ({{ $srv->vehicle_plate_number }})</h4>
                            <div class="flex items-center justify-between text-xs text-slate-500">
                                <span>{{ \App\Support\Timezone::format($srv->service_date, 'd M Y, H:i') }}</span>
                                <span class="font-mono font-bold text-slate-900">Rp{{ number_format($srv->total_cost, 0, ',', '.') }}</span>
                            </div>
                            <div class="pt-2">
                                <a href="{{ route('services.show', $srv) }}" class="block w-full py-2 rounded-xl border border-slate-200 text-center text-xs font-bold text-slate-700 hover:bg-slate-50">
                                    Lihat Rincian Servis
                                </a>
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
