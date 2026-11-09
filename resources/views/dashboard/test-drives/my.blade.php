<x-dashboard-layout title="Test Drive Saya" breadcrumb="Test Drive">
    <div class="space-y-8">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900">Jadwal & Riwayat Test Drive</h1>
                <p class="text-slate-500 text-sm mt-1">Daftar permohonan uji kemudi mobil impian Anda bersama perwakilan sales Dealer Pro.</p>
            </div>
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2 bg-slate-900 text-white px-5 py-2.5 rounded-xl font-bold text-sm hover:bg-slate-800 transition shadow-sm">
                + Ajukan Test Drive Baru
            </a>
        </div>

        <!-- Table / Card Stack -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
            @if($testDrives->isEmpty())
                <div class="py-16 text-center">
                    <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-4">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-800">Belum Ada Pengajuan Test Drive</h3>
                    <p class="text-sm text-slate-500 mt-1">Rasakan sensasi berkendara lini mobil premium Dealer Pro langsung di jalan raya.</p>
                    <a href="{{ route('home') }}" class="mt-4 inline-block font-bold text-sm text-slate-900 hover:underline">
                        Pilih Mobil di Showroom &rarr;
                    </a>
                </div>
            @else
                <!-- Desktop Table -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-[11px] uppercase tracking-wider text-slate-500 font-bold">
                                <th class="py-3.5 px-6">Mobil Pilihan</th>
                                <th class="py-3.5 px-6">Jadwal Uji Kemudi</th>
                                <th class="py-3.5 px-6">Lokasi Penjemputan</th>
                                <th class="py-3.5 px-6">Perwakilan Sales</th>
                                <th class="py-3.5 px-6 text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @foreach($testDrives as $td)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-4 px-6">
                                        <p class="font-bold text-slate-900">{{ $td->car->brand }} {{ $td->car->model_name }}</p>
                                        <p class="text-xs text-slate-500 font-mono">{{ $td->car->year }} &bull; {{ $td->car->price_formatted }}</p>
                                    </td>
                                    <td class="py-4 px-6">
                                        <p class="font-semibold text-slate-900">{{ $td->preferred_date->translatedFormat('l, d F Y') }}</p>
                                        <p class="text-xs text-slate-500 font-mono">Pukul {{ $td->preferred_date->format('H:i') }} WIB</p>
                                    </td>
                                    <td class="py-4 px-6 text-xs text-slate-700">
                                        {{ $td->pickup_location ?? 'Showroom Pusat Dealer Pro' }}
                                    </td>
                                    <td class="py-4 px-6">
                                        @if($td->sales)
                                            <p class="font-bold text-slate-900 text-xs">{{ $td->sales->name }}</p>
                                            <p class="text-[11px] text-slate-500 font-mono">{{ $td->sales->phone_number }}</p>
                                        @else
                                            <span class="text-xs text-slate-400 italic">Menunggu penetapan sales</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-6 text-right">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider border {{ $td->status->color() }}">
                                            <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                            {{ $td->status->label() }}
                                        </span>
                                        @if($td->status->value === 'rejected' && $td->rejection_reason)
                                            <p class="text-[11px] text-red-600 mt-1 max-w-xs italic">{{ $td->rejection_reason }}</p>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Card Stack -->
                <div class="md:hidden divide-y divide-slate-100">
                    @foreach($testDrives as $td)
                        <div class="p-5 space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="font-extrabold text-base text-slate-900">{{ $td->car->brand }} {{ $td->car->model_name }}</span>
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider border {{ $td->status->color() }}">
                                    {{ $td->status->label() }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-600">Jadwal: {{ $td->preferred_date->translatedFormat('d M Y, H:i') }} WIB</p>
                            <p class="text-xs text-slate-500">Lokasi: {{ $td->pickup_location ?? 'Showroom Pusat' }}</p>
                            @if($td->sales)
                                <p class="text-xs text-slate-600">Sales: <span class="font-bold">{{ $td->sales->name }}</span> ({{ $td->sales->phone_number }})</p>
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="p-6 border-t border-slate-100">
                    {{ $testDrives->links() }}
                </div>
            @endif
        </div>
    </div>
</x-dashboard-layout>
