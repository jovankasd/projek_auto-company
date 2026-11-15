<x-dashboard-layout title="Rincian Servis {{ $service->ticket_number }}" breadcrumb="Detail Servis">
    <div class="space-y-8" x-data="{ itemModalOpen: false }">
        <!-- Top Action Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <a href="{{ auth()->user()->isCustomer() ? route('services.my') : route('dashboard') }}" class="text-xs font-bold text-slate-600 hover:text-slate-900 inline-flex items-center gap-1.5 mb-2 min-h-[44px] sm:min-h-0 py-1.5 transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>Kembali</span>
                </a>
                <div class="flex flex-wrap items-center gap-3">
                    <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 font-mono">{{ $service->ticket_number }}</h1>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider border {{ $service->status->color() }}">
                        <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                        {{ $service->status->label() }}
                    </span>
                    <a href="{{ route('services.track', $service) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 min-h-[44px] rounded-xl bg-slate-900 text-white font-semibold text-xs hover:bg-blue-600 active:scale-[0.98] transition shadow-sm">
                        <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        <span>Pelacakan Real-time</span>
                    </a>
                </div>
            </div>

            <!-- Quick Action for Mechanic / Admin / Customer Cancel -->
            <div class="flex flex-wrap items-center gap-2">
                @can('updateStatus', $service)
                    @if($service->status->value === 'pending')
                        <form method="POST" action="{{ route('services.update-status', $service) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="confirmed">
                            <input type="hidden" name="notes" value="Booking servis dikonfirmasi oleh petugas bengkel.">
                            <button type="submit" class="inline-flex items-center justify-center min-h-[44px] px-4 py-2 rounded-xl bg-slate-900 text-white font-bold text-xs hover:bg-slate-800 active:scale-[0.98] transition shadow-sm">
                                Konfirmasi Booking
                            </button>
                        </form>
                    @endif

                    @if($service->status->value === 'confirmed')
                        <form method="POST" action="{{ route('services.update-status', $service) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="in_progress">
                            <input type="hidden" name="notes" value="Mulai pengerjaan unit servis.">
                            <button type="submit" class="inline-flex items-center justify-center min-h-[44px] px-4 py-2 rounded-xl bg-blue-600 text-white font-bold text-xs hover:bg-blue-700 active:scale-[0.98] transition shadow-sm">
                                Mulai Pengerjaan
                            </button>
                        </form>
                    @endif
                @endcan

                @can('addItem', $service)
                    <button type="button" @click="itemModalOpen = true" class="inline-flex items-center justify-center min-h-[44px] px-4 py-2 rounded-xl bg-slate-900 text-white font-bold text-xs hover:bg-slate-800 active:scale-[0.98] transition shadow-sm">
                        + Tambah Onderdil / Jasa
                    </button>
                @endcan

                @can('updateStatus', $service)
                    @if($service->status->value === 'in_progress')
                        <form method="POST" action="{{ route('services.update-status', $service) }}" onsubmit="return confirm('Konfirmasi pengerjaan telah selesai?')">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="completed">
                            <input type="hidden" name="notes" value="Pengerjaan selesai oleh mekanik.">
                            <button type="submit" class="inline-flex items-center justify-center min-h-[44px] px-4 py-2 rounded-xl bg-emerald-600 text-white font-bold text-xs hover:bg-emerald-700 active:scale-[0.98] transition shadow-sm">
                                Selesaikan Servis
                            </button>
                        </form>
                    @endif
                @endcan

                @can('cancel', $service)
                    <form method="POST" action="{{ route('services.cancel', $service) }}" onsubmit="return confirm('Batalkan booking servis ini?')">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="inline-flex items-center justify-center min-h-[44px] px-4 py-2 rounded-xl bg-red-50 text-red-700 border border-red-200 font-bold text-xs hover:bg-red-100 active:scale-[0.98] transition">
                            Batalkan
                        </button>
                    </form>
                @endcan
            </div>
        </div>

        <!-- Vehicle & Service Meta Card -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-8">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-600 mb-6">Informasi Servis & Kendaraan</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div>
                    <span class="text-xs uppercase text-slate-600 font-semibold">Kendaraan</span>
                    <p class="font-extrabold text-base text-slate-900 mt-1">{{ $service->vehicle_model }} ({{ $service->vehicle_year }})</p>
                    <p class="text-xs font-mono text-slate-600">Plat: {{ $service->vehicle_plate_number }}</p>
                    <p class="text-xs text-slate-600 mt-0.5 tabular-nums">Odometer: {{ number_format($service->odometer_km, 0, ',', '.') }} KM</p>
                </div>

                <div>
                    <span class="text-xs uppercase text-slate-600 font-semibold">Pemilik / Pelanggan</span>
                    <p class="font-extrabold text-base text-slate-900 mt-1">{{ $service->customer?->name ?? '-' }}</p>
                    <p class="text-xs text-slate-600">{{ $service->customer?->email ?? '-' }}</p>
                    <p class="text-xs text-slate-600 font-mono">{{ $service->customer?->phone_number ?? '-' }}</p>
                </div>

                <div>
                    <span class="text-xs uppercase text-slate-600 font-semibold">Jadwal Kedatangan</span>
                    <p class="font-extrabold text-base text-slate-900 mt-1">{{ $service->service_date?->translatedFormat('l, d F Y') ?? '-' }}</p>
                    <p class="text-xs text-slate-600 tabular-nums">Pukul {{ sprintf('%02d:00', $service->slot_hour) }} WIB</p>
                    <p class="text-xs text-slate-600 capitalize">Paket: {{ $service->service_type }}</p>
                </div>

                <div>
                    <span class="text-xs uppercase text-slate-600 font-semibold">Mekanik Bertugas</span>
                    <p class="font-extrabold text-base text-slate-900 mt-1">
                        {{ $service->mechanic ? $service->mechanic->name : 'Belum Ditugaskan' }}
                    </p>
                    <p class="text-xs text-slate-600">Bengkel Resmi Dealer Pro</p>
                    @can('assignMechanic', $service)
                        @if(isset($mechanics) && $mechanics->isNotEmpty())
                            <form method="POST" action="{{ route('services.assign', $service) }}" class="mt-3 flex flex-col gap-2">
                                @csrf
                                @method('PATCH')
                                <label for="assign_mechanic_id" class="sr-only">Pilih Mekanik</label>
                                <select id="assign_mechanic_id" name="assigned_mechanic_id" required class="w-full min-h-[44px] px-3 py-2 rounded-xl border border-slate-300 text-xs font-medium text-slate-800 bg-white focus:ring-2 focus:ring-blue-600 focus:outline-none">
                                    <option value="">-- Tugaskan Mekanik --</option>
                                    @foreach($mechanics as $mechanic)
                                        <option value="{{ $mechanic->id }}" @selected($service->mechanic_id === $mechanic->id)>
                                            {{ $mechanic->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="submit" class="inline-flex items-center justify-center min-h-[44px] px-3 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 transition">
                                    Tugaskan Mekanik
                                </button>
                            </form>
                        @endif
                    @endcan
                </div>
            </div>

            <!-- Complaint Notes -->
            <div class="mt-6 pt-6 border-t border-slate-100">
                <span class="text-xs uppercase text-slate-600 font-semibold">Keluhan Pelanggan</span>
                <p class="text-sm text-slate-800 mt-1 bg-slate-50 p-4 rounded-2xl border border-slate-200/60">
                    {{ $service->complaint_notes }}
                </p>
            </div>
        </div>

        <!-- Service Items (Work Order Onderdil & Jasa) -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h3 class="font-black text-lg text-slate-900">Rincian Onderdil & Biaya Jasa</h3>
                    <p class="text-xs text-slate-600 mt-0.5">Komponen suku cadang resmi dan pekerjaan mekanik.</p>
                </div>
                <div class="sm:text-right">
                    <span class="text-xs text-slate-600 uppercase font-semibold">Total Biaya:</span>
                    <p class="font-mono text-xl sm:text-2xl font-black text-slate-900 tabular-nums">
                        Rp{{ number_format($service->total_cost, 0, ',', '.') }}
                    </p>
                </div>
            </div>

            @if($service->items->isEmpty())
                <div class="py-12 px-6 text-center text-slate-600 text-sm">
                    Belum ada onderdil atau jasa yang dimasukkan ke tiket ini.
                </div>
            @else
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-[11px] uppercase tracking-wider text-slate-600 font-bold">
                                <th class="py-3 px-6">Tipe</th>
                                <th class="py-3 px-6">Deskripsi / No. Part</th>
                                <th class="py-3 px-6 text-center">Jumlah</th>
                                <th class="py-3 px-6 text-right">Harga Satuan</th>
                                <th class="py-3 px-6 text-right">Subtotal</th>
                                @can('removeItem', $service)
                                    <th class="py-3 px-6 text-right">Aksi</th>
                                @endcan
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @foreach($service->items as $item)
                                <tr>
                                    <td class="py-3.5 px-6">
                                        <span class="inline-block px-2.5 py-0.5 rounded-full text-[11px] font-semibold uppercase tracking-wider {{ $item->item_type->value === 'part' ? 'bg-blue-50 text-blue-700 border border-blue-200' : ($item->item_type->value === 'labor' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-800 border border-amber-200') }}">
                                            {{ $item->item_type->label() }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-6">
                                        <p class="font-bold text-slate-900">{{ $item->name }}</p>
                                        @if($item->part_number)
                                            <p class="text-xs font-mono text-slate-600">{{ $item->part_number }}</p>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-6 text-center font-mono font-semibold text-slate-700 tabular-nums">
                                        {{ $item->quantity }}
                                    </td>
                                    <td class="py-3.5 px-6 text-right font-mono text-slate-700 tabular-nums">
                                        Rp{{ number_format($item->unit_price, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3.5 px-6 text-right font-mono font-bold text-slate-900 tabular-nums">
                                        Rp{{ number_format($item->subtotal, 0, ',', '.') }}
                                    </td>
                                    @can('removeItem', $service)
                                        <td class="py-3.5 px-6 text-right">
                                            <form method="POST" action="{{ route('services.remove-item', ['service' => $service, 'item' => $item]) }}" onsubmit="return confirm('Hapus item ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center min-h-[44px] px-3 text-xs font-bold text-red-700 hover:underline">Hapus</button>
                                            </form>
                                        </td>
                                    @endcan
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-slate-50 font-bold text-sm">
                                <td colspan="4" class="py-4 px-6 text-right uppercase tracking-wider text-slate-600">Total Keseluruhan</td>
                                <td class="py-4 px-6 text-right font-mono text-base text-slate-900 tabular-nums">Rp{{ number_format($service->total_cost, 0, ',', '.') }}</td>
                                @can('removeItem', $service)
                                    <td></td>
                                @endcan
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Mobile Item Cards -->
                <div class="md:hidden divide-y divide-slate-100">
                    @foreach($service->items as $item)
                        <div class="p-5 space-y-2">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[11px] font-semibold uppercase tracking-wider {{ $item->item_type->value === 'part' ? 'bg-blue-50 text-blue-700 border border-blue-200' : ($item->item_type->value === 'labor' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-800 border border-amber-200') }}">
                                        {{ $item->item_type->label() }}
                                    </span>
                                    <p class="font-bold text-slate-900 mt-1.5">{{ $item->name }}</p>
                                    @if($item->part_number)
                                        <p class="text-xs font-mono text-slate-600">{{ $item->part_number }}</p>
                                    @endif
                                </div>
                                <span class="font-mono font-bold text-sm text-slate-900 tabular-nums">Rp{{ number_format($item->subtotal, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex items-center justify-between text-xs text-slate-600 pt-1">
                                <span class="font-mono tabular-nums">{{ $item->quantity }} x Rp{{ number_format($item->unit_price, 0, ',', '.') }}</span>
                                @can('removeItem', $service)
                                    <form method="POST" action="{{ route('services.remove-item', ['service' => $service, 'item' => $item]) }}" onsubmit="return confirm('Hapus item ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center min-h-[44px] px-3 rounded-xl text-xs font-bold text-red-700 hover:bg-red-50 transition">Hapus</button>
                                    </form>
                                @endcan
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Status History Timeline -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-8">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-600 mb-6">Riwayat Log Status Tiket</h3>
            <div class="space-y-4">
                @foreach($service->statusLogs as $log)
                    <div class="flex items-start gap-4">
                        <div class="w-8 h-8 rounded-full bg-slate-900 text-white flex items-center justify-center shrink-0 mt-0.5">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div class="flex-1 bg-slate-50 p-4 rounded-2xl border border-slate-200/60">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <span class="font-bold text-xs uppercase tracking-wider text-slate-800">
                                    {{ $log->to_status ? \App\Enums\ServiceStatus::tryFrom($log->to_status)?->label() ?? $log->to_status : 'Dibuat' }}
                                </span>
                                <span class="text-xs text-slate-600 font-mono">{{ $log->created_at?->translatedFormat('d M Y, H:i') }} WIB</span>
                            </div>
                            <p class="text-xs text-slate-600 mt-1">{{ $log->notes ?? '-' }}</p>
                            @if($log->changedBy)
                                <p class="text-[11px] text-slate-600 mt-1">Oleh: {{ $log->changedBy->name }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Modal Tambah Onderdil (untuk Mekanik/Admin) -->
    @can('addItem', $service)
        <div x-show="itemModalOpen"
             x-cloak
             role="dialog"
             aria-modal="true"
             aria-labelledby="add-service-item-title"
             class="fixed inset-0 z-50 flex items-center justify-center p-4"
             @keydown.escape.window="itemModalOpen = false">
            <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="itemModalOpen = false"></div>
            <div class="relative w-full max-w-lg rounded-3xl bg-white border border-slate-200/80 shadow-2xl p-6 sm:p-8 overflow-hidden"
                 x-transition:enter="transition ease-out duration-300 transform"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <h3 id="add-service-item-title" class="font-black text-lg text-slate-900">Tambah Item ke Tiket Servis</h3>
                    <button type="button" @click="itemModalOpen = false" aria-label="Tutup modal" class="inline-flex items-center justify-center min-h-[44px] min-w-[44px] rounded-xl text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form action="{{ route('services.add-item', $service) }}" method="POST" class="mt-6 space-y-4">
                    @csrf
                    <div>
                        <label for="service_item_type" class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Tipe Item</label>
                        <select id="service_item_type" name="item_type" required class="w-full min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                            <option value="part">Suku Cadang / Onderdil (Part)</option>
                            <option value="labor">Jasa Mekanik (Labor)</option>
                            <option value="consumable">Bahan Habis Pakai / Cairan (Consumable)</option>
                        </select>
                    </div>

                    <div>
                        <label for="service_item_name" class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Nama Onderdil / Jasa</label>
                        <input id="service_item_name" type="text" name="name" required placeholder="Contoh: Kanvas Rem Depan"
                               class="w-full min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                    </div>

                    <div>
                        <label for="service_item_part_number" class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Nomor Part (Opsional)</label>
                        <input id="service_item_part_number" type="text" name="part_number" placeholder="Contoh: BP-OEM-2024"
                               class="w-full min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none font-mono">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="service_item_quantity" class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Jumlah</label>
                            <input id="service_item_quantity" type="number" step="0.01" min="0.01" name="quantity" value="1" required
                                   class="w-full min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none tabular-nums">
                        </div>
                        <div>
                            <label for="service_item_unit_price" class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Harga Satuan (Rp)</label>
                            <input id="service_item_unit_price" type="number" step="1000" min="0" name="unit_price" required placeholder="0"
                                   class="w-full min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none font-mono tabular-nums">
                        </div>
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                        <button type="button" @click="itemModalOpen = false" class="inline-flex items-center justify-center min-h-[44px] px-5 py-2.5 rounded-xl font-bold text-sm text-slate-600 hover:bg-slate-100 transition">
                            Batal
                        </button>
                        <button type="submit" class="inline-flex items-center justify-center min-h-[44px] px-5 py-2.5 rounded-xl font-bold text-sm bg-slate-900 text-white hover:bg-slate-800 shadow-sm transition">
                            Simpan Item
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endcan
</x-dashboard-layout>
