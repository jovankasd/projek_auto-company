<x-dashboard-layout title="Katalog Mobil" breadcrumb="Katalog Mobil">
    <div class="space-y-8">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900">Manajemen Katalog Mobil</h1>
                <p class="text-slate-500 text-sm mt-1">Kelola data unit kendaraan, spesifikasi mesin, galeri foto WebP, dan urutan tampilan showroom.</p>
            </div>
            <a href="{{ route('cars.create') }}" class="inline-flex items-center gap-2 bg-slate-900 text-white px-5 py-2.5 rounded-xl font-bold text-sm hover:bg-slate-800 transition shadow-sm">
                + Tambah Unit Mobil Baru
            </a>
        </div>

        <!-- Filter & Search Bar -->
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <form method="GET" action="{{ route('cars.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                <!-- Search -->
                <div class="lg:col-span-2">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari merk, model, atau warna..." 
                           class="w-full px-4 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-slate-900 focus:outline-none">
                </div>

                <!-- Brand Filter -->
                <div>
                    <select name="brand" class="w-full px-4 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-slate-900 focus:outline-none">
                        <option value="">Semua Brand</option>
                        @foreach($brands as $b)
                            <option value="{{ $b }}" {{ request('brand') === $b ? 'selected' : '' }}>{{ $b }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Category Filter -->
                <div>
                    <select name="category" class="w-full px-4 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-slate-900 focus:outline-none">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->value }}" {{ request('category') === $cat->value ? 'selected' : '' }}>{{ $cat->label() }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Submit & Clear -->
                <div class="flex items-center gap-2">
                    <button type="submit" class="flex-1 py-2 bg-slate-900 text-white rounded-xl text-xs font-bold hover:bg-slate-800 transition">
                        Filter
                    </button>
                    <a href="{{ route('cars.index') }}" class="px-3 py-2 bg-slate-100 text-slate-600 rounded-xl text-xs font-bold hover:bg-slate-200 transition">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
            @if($cars->isEmpty())
                <div class="py-16 text-center text-slate-400">
                    <p class="text-base font-bold text-slate-700">Tidak ada mobil ditemukan.</p>
                    <p class="text-sm mt-1">Coba sesuaikan kata kunci atau filter pencarian Anda.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-[11px] uppercase tracking-wider text-slate-500 font-bold">
                                <th class="py-3.5 px-6">Urut</th>
                                <th class="py-3.5 px-6">Unit Mobil</th>
                                <th class="py-3.5 px-6">Kategori</th>
                                <th class="py-3.5 px-6">Warna & Hex</th>
                                <th class="py-3.5 px-6">Harga OTR</th>
                                <th class="py-3.5 px-6">Stok & Status</th>
                                <th class="py-3.5 px-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @foreach($cars as $car)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-4 px-6 font-mono font-bold text-slate-400">
                                        #{{ $car->sort_order }}
                                    </td>
                                    <td class="py-4 px-6">
                                        <div class="flex items-center gap-3">
                                            <div class="w-12 h-10 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center shrink-0 overflow-hidden">
                                                @if($car->hero_image_transparent)
                                                    <img src="{{ asset($car->hero_image_transparent) }}" alt="{{ $car->model_name }}" class="w-full h-full object-contain p-1">
                                                @else
                                                    <span class="text-[10px] font-bold text-slate-400">IMG</span>
                                                @endif
                                            </div>
                                            <div>
                                                <p class="font-extrabold text-slate-900 leading-snug">{{ $car->brand }} {{ $car->model_name }}</p>
                                                <p class="text-xs text-slate-400 font-mono">Tahun {{ $car->year }} &bull; Slug: {{ $car->slug }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $car->category->color() }}">
                                            {{ $car->category->label() }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6">
                                        <div class="flex items-center gap-2">
                                            <span class="w-3.5 h-3.5 rounded-full border border-slate-300 shadow-sm" style="background-color: {{ $car->color_hex }}"></span>
                                            <span class="text-xs text-slate-700 font-medium">{{ $car->color_name }}</span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6 font-mono font-bold text-slate-900">
                                        {{ $car->price_formatted }}
                                    </td>
                                    <td class="py-4 px-6">
                                        <div class="space-y-1">
                                            <span class="inline-flex items-center gap-1 text-xs font-semibold {{ $car->is_available ? 'text-emerald-700' : 'text-slate-400' }}">
                                                <span class="w-1.5 h-1.5 rounded-full {{ $car->is_available ? 'bg-emerald-500' : 'bg-slate-300' }}"></span>
                                                {{ $car->is_available ? 'Tersedia' : 'Arsip' }}
                                            </span>
                                            <span class="text-[11px] text-slate-500 block">Stok: {{ $car->stock_quantity }} unit</span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6 text-right space-x-2">
                                        <a href="{{ route('cars.edit', $car) }}" class="inline-flex items-center px-2.5 py-1.5 rounded-lg bg-slate-100 text-slate-800 text-xs font-bold hover:bg-slate-200 transition">
                                            Edit
                                        </a>

                                        @if($car->trashed())
                                            <form method="POST" action="{{ route('cars.restore', $car->slug) }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="inline-flex items-center px-2.5 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 text-xs font-bold hover:bg-emerald-100 transition">
                                                    Pulihkan
                                                </button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('cars.destroy', $car) }}" class="inline" onsubmit="return confirm('Yakin ingin menghapus mobil ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center px-2.5 py-1.5 rounded-lg bg-red-50 text-red-600 text-xs font-bold hover:bg-red-100 transition">
                                                    Hapus
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-6 border-t border-slate-100">
                    {{ $cars->links() }}
                </div>
            @endif
        </div>
    </div>
</x-dashboard-layout>
