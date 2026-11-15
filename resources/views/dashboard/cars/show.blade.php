<x-dashboard-layout>
    <div class="max-w-5xl mx-auto space-y-8">
        <!-- Breadcrumb & Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <a href="{{ route('cars.index') }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900 flex items-center gap-1.5 mb-2 min-h-[32px]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Kembali ke Katalog Mobil
                </a>
                <h1 class="font-display text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                    {{ $car->brand }} {{ $car->model_name }} ({{ $car->year }})
                </h1>
                <div class="flex flex-wrap items-center gap-2 mt-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $car->category->color() }}">
                        {{ $car->category->label() }}
                    </span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $car->is_available ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-100 text-slate-700 border-slate-200' }}">
                        {{ $car->is_available ? 'Tersedia di Showroom' : 'Tidak Aktif (Arsip)' }}
                    </span>
                    @if($car->is_featured)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                            Unit Unggulan
                        </span>
                    @endif
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                @can('update', $car)
                    <a href="{{ route('cars.edit', $car) }}" class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-slate-900 text-white text-xs sm:text-sm font-semibold hover:bg-blue-600 shadow-sm hover:scale-[1.02] active:scale-[0.98] transition-all duration-200 min-h-[44px]">
                        Edit Unit
                    </a>
                @endcan
                <a href="{{ route('cars.public.show', $car) }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-white border border-slate-200/80 text-slate-800 text-xs sm:text-sm font-semibold hover:bg-slate-50 shadow-sm transition min-h-[44px]">
                    Lihat di Showroom Publik
                </a>
            </div>
        </div>

        <!-- Visual Showcase & Main Info -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 sm:gap-8">
            <div class="lg:col-span-6 space-y-4">
                <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-8 flex items-center justify-center aspect-video shadow-sm overflow-hidden relative">
                    <img src="{{ $car->hero_image_url }}"
                         alt="{{ $car->hero_image_alt ?: $car->brand . ' ' . $car->model_name }}"
                         class="w-full h-full object-contain hover:scale-105 transition-transform duration-500">
                </div>

                @if(!empty($car->gallery_images) && count($car->gallery_images) > 0)
                    <div class="grid grid-cols-3 gap-3">
                        @foreach($car->gallery_images as $img)
                            @php
                                $imgSrc = str_starts_with((string) $img, 'http') ? $img : asset($img);
                            @endphp
                            <div class="bg-white rounded-2xl border border-slate-200/80 aspect-video overflow-hidden shadow-sm">
                                <img src="{{ $imgSrc }}"
                                     alt="Galeri {{ $car->brand }} {{ $car->model_name }}"
                                     class="w-full h-full object-cover">
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="lg:col-span-6 space-y-6">
                <!-- Harga & Deskripsi -->
                <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-8 shadow-sm space-y-4">
                    <div>
                        <span class="text-xs font-semibold uppercase text-slate-600 tracking-wider">Harga OTR Showroom</span>
                        <p class="text-3xl font-extrabold text-slate-900 mt-1 font-mono tabular-nums">
                            {{ $car->price_formatted }}
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-3 pt-3 border-t border-slate-100 text-xs">
                        <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200/70">
                            <span class="text-slate-600 block">Warna Eksterior</span>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="w-3.5 h-3.5 rounded-full border border-slate-300 shrink-0" style="background-color: {{ $car->color_hex }}"></span>
                                <span class="font-semibold text-slate-900">{{ $car->color_name }}</span>
                            </div>
                        </div>
                        <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200/70">
                            <span class="text-slate-600 block">Ketersediaan Stok</span>
                            <span class="font-bold text-slate-900 font-mono tabular-nums mt-1 block">{{ $car->stock_quantity }} Unit</span>
                        </div>
                    </div>

                    @if($car->description)
                        <div class="pt-4 border-t border-slate-100">
                            <span class="text-xs font-bold uppercase text-slate-600 tracking-wider block mb-1.5">Deskripsi Kendaraan</span>
                            <p class="text-sm text-slate-700 leading-relaxed">{{ $car->description }}</p>
                        </div>
                    @endif
                </div>

                <!-- Spesifikasi Teknis (Engine Specs HUD) -->
                <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-8 shadow-sm space-y-4">
                    <h2 class="text-xs font-bold uppercase text-slate-600 tracking-wider">Spesifikasi Teknis &amp; Performa</h2>
                    @php
                        $specs = is_array($car->engine_specs) ? $car->engine_specs : [];
                    @endphp
                    <dl class="divide-y divide-slate-100 text-sm">
                        <div class="py-2.5 flex justify-between gap-4">
                            <dt class="text-slate-600">Tenaga Maksimum</dt>
                            <dd class="font-bold text-slate-900 font-mono tabular-nums">{{ $specs['power'] ?? '-' }}</dd>
                        </div>
                        <div class="py-2.5 flex justify-between gap-4">
                            <dt class="text-slate-600">Torsi Puncak</dt>
                            <dd class="font-bold text-slate-900 font-mono tabular-nums">{{ $specs['torque'] ?? '-' }}</dd>
                        </div>
                        <div class="py-2.5 flex justify-between gap-4">
                            <dt class="text-slate-600">Akselerasi 0-100 km/j</dt>
                            <dd class="font-bold text-slate-900 font-mono tabular-nums">{{ $specs['accel'] ?? '-' }}</dd>
                        </div>
                        <div class="py-2.5 flex justify-between gap-4">
                            <dt class="text-slate-600">Transmisi</dt>
                            <dd class="font-bold text-slate-900 font-mono">{{ $specs['trans'] ?? '-' }}</dd>
                        </div>
                        <div class="py-2.5 flex justify-between gap-4">
                            <dt class="text-slate-600">Kunjungan Showroom</dt>
                            <dd class="font-bold text-slate-900 font-mono tabular-nums">{{ number_format((int) $car->view_count, 0, ',', '.') }} Tayangan</dd>
                        </div>
                    </dl>
                </div>

                <!-- Features Tags -->
                @if(!empty($car->features) && count($car->features) > 0)
                    <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-8 shadow-sm">
                        <h2 class="text-xs font-bold uppercase text-slate-600 tracking-wider mb-3">Fitur &amp; Perlengkapan Unggulan</h2>
                        <div class="flex flex-wrap gap-2">
                            @foreach($car->features as $feature)
                                <span class="px-3 py-1.5 rounded-full bg-slate-100 text-slate-800 text-xs font-semibold border border-slate-200/80">
                                    {{ $feature }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-dashboard-layout>
