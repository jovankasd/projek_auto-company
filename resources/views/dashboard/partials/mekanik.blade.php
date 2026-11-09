<div class="space-y-8" x-data="{ 
    itemModalOpen: false, 
    selectedService: null,
    statusModalOpen: false,
    statusAction: '',
    statusService: null,
    notes: '',
    // Data untuk modal pemasangan suku cadang
    partModalOpen: false,
    selectedPart: null,
    targetServiceId: '{{ $activeServicesList->first()->id ?? '' }}',
    partQty: 1,

    openAddItem(service) {
        this.selectedService = service;
        this.itemModalOpen = true;
    },
    openStatusModal(service, action) {
        this.statusService = service;
        this.statusAction = action;
        this.statusModalOpen = true;
    },
    installPart(part) {
        this.selectedPart = part;
        this.partQty = 1;
        this.partModalOpen = true;
    }
}">
    <!-- Header Admin Mekanik -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-gradient-to-r from-slate-900 via-amber-950/70 to-slate-900 text-white p-6 sm:p-8 rounded-3xl shadow-xl relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-amber-500/10 rounded-full blur-2xl pointer-events-none"></div>
        <div class="relative z-10">
            <div class="flex items-center gap-2 mb-2">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider bg-amber-500/20 text-amber-300 border border-amber-500/30">
                    🔧 Workshop Control
                </span>
                <span class="text-xs text-slate-400 font-medium">Divisi Admin Mekanik Bengkel</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">Dashboard Admin Mekanik & Suku Cadang</h1>
            <p class="text-slate-300 text-sm mt-1 max-w-2xl">
                Pantau proses perbaikan unit kendaraan, gunakan suku cadang/onderdil fisik untuk perbaikan mobil, dan kirimkan data pekerjaan ke Super Admin.
            </p>
        </div>

        <div class="relative z-10 flex flex-wrap items-center gap-3">
            <a href="{{ route('spare-parts.index') }}" class="px-4 py-2.5 rounded-xl bg-amber-500 text-slate-950 font-bold text-xs hover:bg-amber-400 transition shadow-md flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                </svg>
                <span>Lihat Katalog Suku Cadang</span>
            </a>
        </div>
    </div>

    <!-- Quick Operational Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-5 hover:shadow-md transition">
            <span class="text-xs uppercase font-extrabold text-slate-400 tracking-wider">Sedang Dikerjakan</span>
            <p class="font-mono text-3xl font-black text-blue-600 mt-2">{{ $stats['active_jobs'] }}</p>
            <span class="text-[11px] text-slate-500 mt-1 block">Unit di stall bengkel</span>
        </div>
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-5 hover:shadow-md transition">
            <span class="text-xs uppercase font-extrabold text-slate-400 tracking-wider">Siap Dikerjakan</span>
            <p class="font-mono text-3xl font-black text-amber-600 mt-2">{{ $stats['pending_confirmed'] }}</p>
            <span class="text-[11px] text-slate-500 mt-1 block">Menunggu giliran mekanik</span>
        </div>
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-5 hover:shadow-md transition">
            <span class="text-xs uppercase font-extrabold text-slate-400 tracking-wider">Selesai Hari Ini</span>
            <p class="font-mono text-3xl font-black text-emerald-600 mt-2">{{ $stats['completed_today'] }}</p>
            <span class="text-[11px] text-slate-500 mt-1 block">Siap serah terima customer</span>
        </div>
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-5 hover:shadow-md transition">
            <span class="text-xs uppercase font-extrabold text-slate-400 tracking-wider">Total Suku Cadang</span>
            <p class="font-mono text-3xl font-black text-slate-900 mt-2">{{ $stats['total_spare_parts'] ?? count($spareParts) }}</p>
            <span class="text-[11px] text-slate-500 mt-1 block">Komponen perbaikan siap pakai</span>
        </div>
    </div>

    <!-- SEKSI 1: GALERI GAMBAR SUKU CADANG / ONDERDIL MOBIL UNTUK PERBAIKAN -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6 sm:p-8 space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
            <div>
                <div class="flex items-center gap-2">
                    <span class="p-2 rounded-xl bg-amber-50 text-amber-700">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                    </span>
                    <h2 class="text-xl font-black text-slate-900 tracking-tight">Katalog Suku Cadang & Komponen Perbaikan Mobil</h2>
                </div>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">
                    Gunakan suku cadang fisik di bawah ini untuk dipasangkan langsung ke tiket servis mobil pelanggan.
                </p>
            </div>

            <a href="{{ route('spare-parts.index') }}" class="text-xs font-bold text-amber-700 hover:text-amber-800 flex items-center gap-1 group">
                <span>Lihat Semua Suku Cadang</span>
                <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
            </a>
        </div>

        <!-- Grid Kartu Foto Suku Cadang -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
            @forelse($spareParts as $part)
                <div class="group bg-slate-50/50 hover:bg-white rounded-2xl border border-slate-200/80 p-4 transition duration-200 hover:shadow-lg flex flex-col justify-between relative overflow-hidden">
                    <div>
                        <!-- Gambar Suku Cadang Mobil -->
                        <div class="w-full h-44 rounded-xl bg-white border border-slate-100 flex items-center justify-center p-3 overflow-hidden relative shadow-inner">
                            <img src="{{ $part->imageUrl }}" 
                                 alt="{{ $part->name }}" 
                                 class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-300 drop-shadow-md"
                                 loading="lazy" />
                            
                            <!-- Badges -->
                            <div class="absolute top-2.5 left-2.5 flex flex-col gap-1">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-slate-900/80 backdrop-blur-md text-white shadow-xs">
                                    {{ $part->brand }}
                                </span>
                            </div>

                            <div class="absolute top-2.5 right-2.5">
                                @if($part->stock_quantity <= ($part->min_stock_alert ?? 5))
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-red-100 text-red-700 border border-red-200">
                                        Stok Menipis ({{ $part->stock_quantity }})
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        Tersedia: {{ $part->stock_quantity }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Info Detail Part -->
                        <div class="mt-3.5 space-y-1">
                            <div class="flex items-center justify-between text-[11px] text-slate-400 font-mono">
                                <span>No. Part: {{ $part->part_number ?? '-' }}</span>
                                <span class="capitalize text-slate-500 font-semibold">{{ $part->category?->label() ?? 'Umum' }}</span>
                            </div>
                            <h3 class="font-extrabold text-sm sm:text-base text-slate-900 group-hover:text-amber-800 transition line-clamp-1">
                                {{ $part->name }}
                            </h3>

                            <!-- Kompatibilitas Mobil -->
                            @if(!empty($part->car_brand_compatibility))
                                <div class="flex items-center gap-1 flex-wrap pt-1">
                                    <span class="text-[10px] font-bold text-slate-400">Kompatibel:</span>
                                    @foreach(array_slice($part->car_brand_compatibility, 0, 3) as $cb)
                                        <span class="px-1.5 py-0.5 rounded-md text-[10px] bg-slate-200/70 text-slate-700 font-semibold">
                                            {{ $cb }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif

                            <p class="font-mono text-base font-black text-amber-700 pt-1">
                                {{ $part->price_formatted }}
                            </p>
                        </div>
                    </div>

                    <!-- Tombol Aksi Pasang ke Unit Servis -->
                    <div class="pt-3 mt-3 border-t border-slate-100">
                        <button type="button" 
                                @click="installPart({{ json_encode([
                                    'id' => $part->id,
                                    'name' => $part->name,
                                    'part_number' => $part->part_number,
                                    'price' => (float) $part->price,
                                    'price_formatted' => $part->price_formatted,
                                    'brand' => $part->brand,
                                    'stock' => $part->stock_quantity,
                                    'image_url' => $part->imageUrl,
                                ]) }})"
                                class="w-full py-2 px-3 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 text-xs font-extrabold transition shadow-xs flex items-center justify-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                            </svg>
                            <span>Pasang ke Mobil Servis</span>
                        </button>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-12 text-center text-slate-400 text-sm">
                    Belum ada data suku cadang yang dimuat. Silakan periksa inventaris bengkel.
                </div>
            @endforelse
        </div>
    </div>

    <!-- SEKSI 2: ANTREAN PERINTAH KERJA (WORK ORDERS) BENGKEL -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="font-black text-lg text-slate-900">Antrean Work Order Bengkel</h2>
                <p class="text-xs text-slate-500 mt-0.5">Semua data perbaikan dan penggantian suku cadang otomatis terhubung ke laporan Super Admin.</p>
            </div>
            
            <!-- Filter Tabs -->
            <div class="inline-flex p-1 rounded-2xl bg-slate-100 border border-slate-200 text-xs">
                <a href="{{ route('dashboard', ['filter' => 'today']) }}" 
                   class="px-3.5 py-1.5 rounded-xl font-bold transition {{ $filter === 'today' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                    Hari Ini
                </a>
                <a href="{{ route('dashboard', ['filter' => 'week']) }}" 
                   class="px-3.5 py-1.5 rounded-xl font-bold transition {{ $filter === 'week' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                    Minggu Ini
                </a>
                <a href="{{ route('dashboard', ['filter' => 'all']) }}" 
                   class="px-3.5 py-1.5 rounded-xl font-bold transition {{ $filter === 'all' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                    Semua
                </a>
            </div>
        </div>

        @if($workOrders->isEmpty())
            <div class="py-16 text-center">
                <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-4">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-800">Tidak Ada Antrean Perbaikan</h3>
                <p class="text-sm text-slate-500 mt-1">Seluruh unit pada filter ini telah terselesaikan atau belum ada tiket servis baru.</p>
            </div>
        @else
            <!-- Desktop Table View -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/75 border-b border-slate-200 text-[11px] uppercase tracking-wider text-slate-500 font-bold">
                            <th class="py-3.5 px-6">Tiket & Plat Nomor</th>
                            <th class="py-3.5 px-6">Mobil & Pelanggan</th>
                            <th class="py-3.5 px-6">Waktu & Tipe</th>
                            <th class="py-3.5 px-6">Keluhan Servis</th>
                            <th class="py-3.5 px-6">Status Pengerjaan</th>
                            <th class="py-3.5 px-6 text-right">Aksi Mekanik</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @foreach($workOrders as $service)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-4 px-6">
                                    <span class="font-mono font-bold text-xs text-slate-900 block">{{ $service->ticket_number }}</span>
                                    <span class="font-mono text-xs px-2 py-0.5 rounded bg-slate-200/80 font-bold text-slate-800 mt-1 inline-block">
                                        {{ $service->vehicle_plate_number }}
                                    </span>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="font-extrabold text-slate-900 block">{{ $service->vehicle_model }} ({{ $service->vehicle_year }})</span>
                                    <span class="text-xs text-slate-500">{{ $service->customer?->name ?? 'Pelanggan Umum' }}</span>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="text-xs font-semibold block text-slate-800">
                                        {{ \Carbon\Carbon::parse($service->service_date)->translatedFormat('d M Y') }}
                                    </span>
                                    <span class="text-[11px] text-slate-500 capitalize">{{ is_object($service->service_type) && method_exists($service->service_type, 'label') ? $service->service_type->label() : $service->service_type }}</span>
                                </td>
                                <td class="py-4 px-6 max-w-xs">
                                    <p class="text-xs text-slate-600 truncate" title="{{ $service->complaint_notes }}">
                                        {{ $service->complaint_notes ?: 'Servis berkala rutin' }}
                                    </p>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-extrabold border {{ is_object($service->status) && method_exists($service->status, 'color') ? $service->status->color() : 'bg-slate-100 text-slate-800' }}">
                                        {{ is_object($service->status) && method_exists($service->status, 'label') ? $service->status->label() : $service->status }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-right space-x-1.5 whitespace-nowrap">
                                    @can('updateStatus', $service)
                                        @if(in_array($service->status?->value ?? (string)$service->status, ['pending', 'confirmed']))
                                            <form method="POST" action="{{ route('services.update-status', $service) }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="in_progress">
                                                <button type="submit" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs transition">
                                                    Mulai Servis
                                                </button>
                                            </form>
                                        @endif
                                    @endcan

                                    @if(($service->status?->value ?? (string)$service->status) === 'in_progress')
                                        @can('addItem', $service)
                                            <button type="button" 
                                                    @click="openAddItem({{ $service->toJson() }})"
                                                    class="px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-slate-950 text-xs font-extrabold rounded-xl shadow-xs transition">
                                                + Tambah Item
                                            </button>
                                        @endcan
                                        @can('updateStatus', $service)
                                            <form method="POST" action="{{ route('services.update-status', $service) }}" class="inline" onsubmit="return confirm('Apakah servis unit ini telah selesai?')">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="completed">
                                                <button type="submit" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition">
                                                    Selesai
                                                </button>
                                            </form>
                                        @endcan
                                    @endif

                                    <a href="{{ route('services.show', $service) }}" class="px-3 py-1.5 border border-slate-300 hover:bg-slate-100 text-slate-700 text-xs font-bold rounded-xl transition">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="p-6 border-t border-slate-100">
                {{ $workOrders->links() }}
            </div>
        @endif
    </div>

    <!-- MODAL 1: PASANG SUKU CADANG LANGSUNG KE UNIT MOBIL -->
    <div x-show="partModalOpen" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         @keydown.escape.window="partModalOpen = false">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="partModalOpen = false"></div>
        <div class="relative w-full max-w-lg rounded-3xl bg-white border border-slate-200 shadow-2xl p-6 sm:p-8 overflow-hidden"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-800 uppercase">Gudang Suku Cadang</span>
                    <h3 class="font-black text-lg text-slate-900 mt-1">Pasang Komponen ke Unit Mobil</h3>
                </div>
                <button @click="partModalOpen = false" class="p-2 text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>

            <template x-if="selectedPart">
                <form :action="'/dashboard/services/' + targetServiceId + '/items'" method="POST" class="mt-5 space-y-4">
                    @csrf
                    <input type="hidden" name="item_type" value="part">
                    <input type="hidden" name="name" :value="selectedPart.name">
                    <input type="hidden" name="part_number" :value="selectedPart.part_number">
                    <input type="hidden" name="unit_price" :value="selectedPart.price">

                    <!-- Info Komponen -->
                    <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-200/80 flex items-center gap-3">
                        <img :src="selectedPart.image_url" :alt="selectedPart.name" class="w-14 h-14 object-contain rounded-xl bg-white border border-slate-200 p-1 shrink-0">
                        <div class="flex-1 min-w-0">
                            <span class="text-xs text-slate-400 font-mono" x-text="'Brand: ' + selectedPart.brand"></span>
                            <h4 class="font-extrabold text-sm text-slate-900 truncate" x-text="selectedPart.name"></h4>
                            <span class="text-xs text-slate-500 font-mono" x-text="'No. Part: ' + (selectedPart.part_number || '-')"></span>
                        </div>
                        <div class="text-right shrink-0">
                            <span class="text-xs text-slate-400 block">Harga Satuan</span>
                            <span class="font-mono text-sm font-extrabold text-amber-700" x-text="selectedPart.price_formatted"></span>
                        </div>
                    </div>

                    <!-- Pilih Unit Mobil yang Sedang Dikerjakan -->
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1.5">Pilih Unit Kendaraan Pelanggan</label>
                        @if($activeServicesList->isEmpty())
                            <div class="p-3 bg-red-50 text-red-700 rounded-xl text-xs font-semibold">
                                Belum ada unit servis yang berstatus Dikonfirmasi / Sedang Dikerjakan. Mulai servis pada tabel di atas terlebih dahulu.
                            </div>
                        @else
                            <select x-model="targetServiceId" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-slate-900 focus:outline-none">
                                @foreach($activeServicesList as $activeSvc)
                                    <option value="{{ $activeSvc->id }}">
                                        {{ $activeSvc->ticket_number }} - {{ $activeSvc->vehicle_model }} ({{ $activeSvc->vehicle_plate_number }}) - {{ $activeSvc->customer?->name ?? 'Pelanggan' }}
                                    </option>
                                @endforeach
                            </select>
                        @endif
                    </div>

                    <!-- Jumlah -->
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Jumlah Pemakaian (Qty)</label>
                        <input type="number" min="1" :max="selectedPart.stock || 99" name="quantity" x-model="partQty" required 
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-slate-900 focus:outline-none font-mono">
                        <span class="text-[11px] text-slate-400 mt-1 block" x-text="'Stok tersedia di gudang: ' + selectedPart.stock"></span>
                    </div>

                    <!-- Total Subtotal -->
                    <div class="p-3 bg-amber-50 rounded-xl flex items-center justify-between text-xs font-bold text-amber-950">
                        <span>Estimasi Biaya Ditambahkan:</span>
                        <span class="font-mono text-sm" x-text="'Rp ' + Number(selectedPart.price * partQty).toLocaleString('id-ID')"></span>
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                        <button type="button" @click="partModalOpen = false" class="px-5 py-2.5 rounded-xl font-bold text-sm text-slate-600 hover:bg-slate-100">
                            Batal
                        </button>
                        <button type="submit" 
                                :disabled="{{ $activeServicesList->isEmpty() ? 'true' : 'false' }}"
                                class="px-5 py-2.5 rounded-xl font-bold text-sm bg-slate-900 text-white hover:bg-slate-800 shadow-sm disabled:opacity-50">
                            Konfirmasi & Pasang ke Mobil
                        </button>
                    </div>
                </form>
            </template>
        </div>
    </div>

    <!-- MODAL 2: TAMBAH ITEM MANUAL / JASA -->
    <div x-show="itemModalOpen" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         @keydown.escape.window="itemModalOpen = false">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="itemModalOpen = false"></div>
        <div class="relative w-full max-w-lg rounded-3xl bg-white border border-slate-200 shadow-2xl p-6 sm:p-8 overflow-hidden"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div>
                    <h3 class="font-black text-lg text-slate-900">Tambah Item Pekerjaan / Jasa Servis</h3>
                    <p class="text-xs text-slate-500 mt-0.5" x-text="selectedService ? 'Tiket: ' + selectedService.ticket_number : ''"></p>
                </div>
                <button @click="itemModalOpen = false" class="p-2 text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>

            <form :action="selectedService ? '/dashboard/services/' + selectedService.id + '/items' : '#'" method="POST" class="mt-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Tipe Item</label>
                    <select name="item_type" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-slate-900 focus:outline-none">
                        <option value="part">Suku Cadang / Onderdil (Part)</option>
                        <option value="labor" selected>Jasa Pengerjaan Mekanik (Labor)</option>
                        <option value="consumable">Bahan Habis Pakai / Pelumas (Consumable)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Deskripsi Item / Jasa</label>
                    <input type="text" name="name" required placeholder="Contoh: Jasa Tune Up Mesin / Penggantian Kampas Rem" 
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-slate-900 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Nomor Part / Ref (Opsional)</label>
                    <input type="text" name="part_number" placeholder="Contoh: REF-SRV-01" 
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-slate-900 focus:outline-none font-mono">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Jumlah (Qty)</label>
                        <input type="number" step="0.01" min="0.01" name="quantity" value="1" required 
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-slate-900 focus:outline-none font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Biaya Satuan (Rp)</label>
                        <input type="number" step="1000" min="0" name="unit_price" required placeholder="0" 
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-slate-900 focus:outline-none font-mono">
                    </div>
                </div>

                <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                    <button type="button" @click="itemModalOpen = false" class="px-5 py-2.5 rounded-xl font-bold text-sm text-slate-600 hover:bg-slate-100">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl font-bold text-sm bg-slate-900 text-white hover:bg-slate-800 shadow-sm">
                        Simpan ke Tiket Servis
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
