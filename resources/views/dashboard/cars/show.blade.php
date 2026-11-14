<x-dashboard-layout title="Detail Mobil {{ $car->brand }} {{ $car->model_name }}" breadcrumb="Detail Mobil">
    <div class="max-w-5xl mx-auto space-y-8">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <a href="{{ route('cars.index') }}" class="text-xs font-bold text-slate-500 hover:text-slate-900 inline-flex items-center gap-1 mb-2">
                    &larr; Kembali ke Katalog Mobil
                </a>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900">{{ $car->brand }} {{ $car->model_name }} ({{ $car->year }})</h1>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $car->is_available ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-600 border border-slate-200' }}">
                        {{ $car->is_available ? 'Katalog Aktif' : 'Non-Aktif' }}
                    </span>
                    @if($car->is_featured)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                            ★ Unggulan
                        </span>
                    @endif
                </div>
                <p class="text-slate-500 text-sm mt-1">Kode Slug: <span class="font-mono text-slate-700">{{ $car->slug }}</span> &bull; Kategori: <span class="font-bold text-slate-700">{{ $car->category->label() }}</span></p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('cars.edit', $car) }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl font-bold text-sm shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                    </svg>
                    Edit Mobil
                </a>
            </div>
        </div>

        <!-- Main Card: Hero & Key Specs -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            <!-- Left: Hero Image Preview & Gallery -->
            <div class="lg:col-span-7 space-y-6">
                <div class="bg-gradient-to-b from-slate-100 to-slate-200 rounded-3xl p-8 flex items-center justify-center border border-slate-200 relative overflow-hidden min-h-[320px]">
                    @if($car->hero_image_transparent)
                        <img src="{{ asset($car->hero_image_transparent) }}" alt="{{ $car->hero_image_alt }}" class="max-h-72 object-contain drop-shadow-2xl">
                    @else
                        <div class="text-center text-slate-400">
                            <svg class="w-16 h-16 mx-auto mb-2 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span class="text-xs font-bold uppercase tracking-wider">Belum ada foto hero transparan</span>
                        </div>
                    @endif
                </div>

                @if(!empty($car->gallery_images) && count($car->gallery_images) > 0)
                    <div class="bg-white rounded-3xl border border-slate-200 p-6">
                        <h3 class="text-xs font-black uppercase text-slate-400 tracking-wider mb-4">Galeri Eksterior & Interior ({{ count($car->gallery_images) }} Foto)</h3>
                        <div class="grid grid-cols-3 sm:grid-cols-4 gap-3">
                            @foreach($car->gallery_images as $img)
                                <div class="aspect-video bg-slate-100 rounded-xl overflow-hidden border border-slate-200 group">
                                    <img src="{{ asset($img) }}" alt="Galeri {{ $car->brand }} {{ $car->model_name }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- Right: Details & Specs -->
            <div class="lg:col-span-5 space-y-6">
                <!-- Price Card -->
                <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm">
                    <p class="text-xs font-bold uppercase text-slate-400 tracking-wider">Harga Resmi OTR</p>
                    <p class="text-3xl font-black text-slate-900 mt-1 font-mono">Rp {{ number_format((float) $car->price, 0, ',', '.') }}</p>
                    <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between text-xs">
                        <span class="text-slate-500 font-medium">Warna & Kode Cat</span>
                        <span class="font-bold text-slate-900 inline-flex items-center gap-1.5">
                            @if($car->color_hex)
                                <span class="w-3.5 h-3.5 rounded-full border border-slate-300" style="background-color: {{ $car->color_hex }}"></span>
                            @endif
                            {{ $car->color_name }}
                        </span>
                    </div>
                </div>

                <!-- Engine Specs -->
                <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm space-y-4">
                    <h3 class="text-xs font-black uppercase text-slate-400 tracking-wider">Spesifikasi Mesin & Performa</h3>
                    <dl class="divide-y divide-slate-100 text-sm">
                        <div class="py-2.5 flex justify-between">
                            <dt class="text-slate-500">Transmisi</dt>
                            <dd class="font-bold text-slate-900">{{ $car->engine_specs['trans'] ?? ($car->transmission ?? '-') }}</dd>
                        </div>
                        <div class="py-2.5 flex justify-between">
                            <dt class="text-slate-500">Bahan Bakar</dt>
                            <dd class="font-bold text-slate-900">{{ $car->engine_specs['fuel_type'] ?? ($car->fuel_type ?? '-') }}</dd>
                        </div>
                        <div class="py-2.5 flex justify-between">
                            <dt class="text-slate-500">Tenaga Maksimum</dt>
                            <dd class="font-bold text-slate-900 font-mono">{{ $car->engine_specs['power'] ?? ($car->power_hp ? $car->power_hp . ' HP' : '-') }}</dd>
                        </div>
                        <div class="py-2.5 flex justify-between">
                            <dt class="text-slate-500">Torsi Maksimum</dt>
                            <dd class="font-bold text-slate-900 font-mono">{{ $car->engine_specs['torque'] ?? ($car->torque_nm ? $car->torque_nm . ' Nm' : '-') }}</dd>
                        </div>
                        <div class="py-2.5 flex justify-between">
                            <dt class="text-slate-500">Akselerasi (0-100 km/h)</dt>
                            <dd class="font-bold text-slate-900 font-mono">{{ $car->engine_specs['accel'] ?? '-' }}</dd>
                        </div>
                        <div class="py-2.5 flex justify-between">
                            <dt class="text-slate-500">Kapasitas Tempat Duduk</dt>
                            <dd class="font-bold text-slate-900 font-mono">{{ $car->dimensions['seats'] ?? ($car->seating_capacity ? $car->seating_capacity . ' Kursi' : '-') }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Features Tags -->
                @if(!empty($car->features) && count($car->features) > 0)
                    <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm">
                        <h3 class="text-xs font-black uppercase text-slate-400 tracking-wider mb-3">Fitur & Perlengkapan Unggulan</h3>
                        <div class="flex flex-wrap gap-2">
                            @foreach($car->features as $feature)
                                <span class="px-3 py-1 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold border border-slate-200">
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
