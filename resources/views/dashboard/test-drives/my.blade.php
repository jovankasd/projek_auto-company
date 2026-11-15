<x-dashboard-layout title="Test Drive Saya" breadcrumb="Test Drive">
    <div class="space-y-8">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900">Jadwal & Riwayat Test Drive</h1>
                <p class="text-slate-600 text-sm mt-1">Daftar permohonan uji kemudi mobil impian Anda bersama perwakilan sales Dealer Pro.</p>
            </div>
            <a href="{{ route('home') }}#showroom" class="inline-flex items-center justify-center min-h-[44px] gap-2 bg-slate-900 text-white px-5 py-2.5 rounded-xl font-bold text-sm hover:bg-slate-800 active:scale-[0.98] transition shadow-sm">
                + Ajukan Test Drive Baru
            </a>
        </div>

        <!-- Table / Card Stack -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
            @if($testDrives->isEmpty())
                <div class="py-16 px-6 text-center">
                    <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-600 mx-auto flex items-center justify-center mb-4">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-900">Belum Ada Pengajuan Test Drive</h3>
                    <p class="text-sm text-slate-600 mt-1">Rasakan sensasi berkendara lini mobil premium Dealer Pro langsung di jalan raya.</p>
                    <a href="{{ route('home') }}#showroom" class="mt-4 inline-flex items-center gap-1.5 min-h-[44px] px-4 font-bold text-sm text-slate-900 hover:underline">
                        <span>Pilih Mobil di Showroom</span>
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </a>
                </div>
            @else
                <!-- Desktop Table -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-[11px] uppercase tracking-wider text-slate-600 font-bold">
                                <th class="py-3.5 px-6">Mobil Pilihan</th>
                                <th class="py-3.5 px-6">Jadwal Uji Kemudi</th>
                                <th class="py-3.5 px-6">Lokasi Penjemputan</th>
                                <th class="py-3.5 px-6">Perwakilan Sales</th>
                                <th class="py-3.5 px-6 text-right">Status & Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @foreach($testDrives as $td)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-4 px-6">
                                        <p class="font-bold text-slate-900">
                                            {{ $td->car ? ($td->car->brand . ' ' . $td->car->model_name) : 'Unit Showroom' }}
                                        </p>
                                        <p class="text-xs text-slate-600 font-mono">
                                            @if($td->car)
                                                {{ $td->car->year }} &bull; {{ $td->car->price_formatted }}
                                            @else
                                                Data unit tidak tersedia
                                            @endif
                                        </p>
                                    </td>
                                    <td class="py-4 px-6">
                                        <p class="font-semibold text-slate-900">{{ $td->preferred_date?->translatedFormat('l, d F Y') ?? '-' }}</p>
                                        <p class="text-xs text-slate-600 font-mono tabular-nums">Pukul {{ $td->preferred_date?->format('H:i') ?? '--:--' }} WIB</p>
                                    </td>
                                    <td class="py-4 px-6 text-xs text-slate-700">
                                        {{ $td->pickup_location ?? 'Showroom Pusat Dealer Pro' }}
                                    </td>
                                    <td class="py-4 px-6">
                                        @if($td->sales)
                                            <p class="font-bold text-slate-900 text-xs">{{ $td->sales->name }}</p>
                                            <p class="text-xs text-slate-600 font-mono">{{ $td->sales->phone_number ?? '-' }}</p>
                                        @else
                                            <span class="text-xs text-slate-600 italic">Menunggu penetapan sales</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-6 text-right">
                                        <div class="inline-flex flex-col items-end gap-2">
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider border {{ $td->status->color() }}">
                                                <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                                {{ $td->status->label() }}
                                            </span>
                                            @if($td->status->value === 'rejected' && $td->rejection_reason)
                                                <p class="text-xs text-red-700 max-w-xs italic text-right">{{ $td->rejection_reason }}</p>
                                            @endif
                                            @if(auth()->user()->can('cancel', $td) && $td->status->canTransitionTo(\App\Enums\TestDriveStatus::Cancelled))
                                                <form method="POST" action="{{ route('test-drives.cancel', $td) }}" onsubmit="return confirm('Batalkan pengajuan test drive ini?')">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="inline-flex items-center justify-center min-h-[44px] px-3 py-1.5 rounded-xl bg-red-50 text-red-700 border border-red-200 text-xs font-bold hover:bg-red-100 transition">
                                                        Batalkan
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Card Stack -->
                <div class="md:hidden divide-y divide-slate-100">
                    @foreach($testDrives as $td)
                        <div class="p-5 space-y-2.5">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <span class="font-extrabold text-base text-slate-900 block">
                                        {{ $td->car ? ($td->car->brand . ' ' . $td->car->model_name) : 'Unit Showroom' }}
                                    </span>
                                    @if($td->car)
                                        <span class="text-xs text-slate-600 font-mono">{{ $td->car->year }} &bull; {{ $td->car->price_formatted }}</span>
                                    @endif
                                </div>
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider border shrink-0 {{ $td->status->color() }}">
                                    {{ $td->status->label() }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-600 tabular-nums">Jadwal: {{ $td->preferred_date?->translatedFormat('d M Y, H:i') ?? '-' }} WIB</p>
                            <p class="text-xs text-slate-600">Lokasi: {{ $td->pickup_location ?? 'Showroom Pusat Dealer Pro' }}</p>
                            @if($td->sales)
                                <p class="text-xs text-slate-600">Sales: <span class="font-bold text-slate-900">{{ $td->sales->name }}</span> ({{ $td->sales->phone_number ?? '-' }})</p>
                            @endif
                            @if($td->status->value === 'rejected' && $td->rejection_reason)
                                <p class="text-xs text-red-700 bg-red-50/70 p-2.5 rounded-xl border border-red-200/70 italic">Alasan penolakan: {{ $td->rejection_reason }}</p>
                            @endif
                            @if(auth()->user()->can('cancel', $td) && $td->status->canTransitionTo(\App\Enums\TestDriveStatus::Cancelled))
                                <div class="pt-1">
                                    <form method="POST" action="{{ route('test-drives.cancel', $td) }}" onsubmit="return confirm('Batalkan pengajuan test drive ini?')">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="w-full inline-flex items-center justify-center min-h-[44px] px-4 py-2 rounded-xl bg-red-50 text-red-700 border border-red-200 text-xs font-bold hover:bg-red-100 transition">
                                            Batalkan Pengajuan
                                        </button>
                                    </form>
                                </div>
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
