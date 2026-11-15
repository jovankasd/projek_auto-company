<x-dashboard-layout title="Katalog Mobil" breadcrumb="Katalog Mobil">
    <div class="space-y-8">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900">Manajemen Katalog Mobil</h1>
                <p class="text-slate-600 text-sm mt-1">Kelola data unit kendaraan, spesifikasi mesin, galeri foto WebP, dan urutan tampilan showroom.</p>
            </div>
            @can('create', \App\Models\Car::class)
                <a href="{{ route('cars.create') }}" class="inline-flex items-center justify-center min-h-[44px] gap-2 bg-slate-900 text-white px-5 py-2.5 rounded-xl font-bold text-sm hover:bg-slate-800 active:scale-[0.98] transition shadow-sm">
                    + Tambah Unit Mobil Baru
                </a>
            @endcan
        </div>

        <!-- Filter & Search Bar -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm space-y-3">
            <form method="GET" action="{{ route('cars.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                @if(request()->boolean('trashed'))
                    <input type="hidden" name="trashed" value="1">
                @endif
                <!-- Search -->
                <div class="lg:col-span-2">
                    <label for="car_filter_search" class="sr-only">Cari mobil</label>
                    <input id="car_filter_search" type="text" name="search" value="{{ request('search') }}" placeholder="Cari merk, model, atau warna..."
                           class="w-full min-h-[44px] px-4 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                </div>

                <!-- Brand Filter -->
                <div>
                    <label for="car_filter_brand" class="sr-only">Filter brand</label>
                    <select id="car_filter_brand" name="brand" class="w-full min-h-[44px] px-4 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                        <option value="">Semua Brand</option>
                        @foreach($brands as $b)
                            <option value="{{ $b }}" {{ request('brand') === $b ? 'selected' : '' }}>{{ $b }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Category Filter -->
                <div>
                    <label for="car_filter_category" class="sr-only">Filter kategori</label>
                    <select id="car_filter_category" name="category" class="w-full min-h-[44px] px-4 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->value }}" {{ request('category') === $cat->value ? 'selected' : '' }}>{{ $cat->label() }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Submit & Clear -->
                <div class="flex items-center gap-2">
                    <button type="submit" class="flex-1 min-h-[44px] py-2 px-4 bg-slate-900 text-white rounded-xl text-xs font-bold hover:bg-slate-800 transition">
                        Filter
                    </button>
                    <a href="{{ route('cars.index') }}" class="inline-flex items-center justify-center min-h-[44px] px-3.5 py-2 bg-slate-100 text-slate-700 rounded-xl text-xs font-bold hover:bg-slate-200 transition">
                        Reset
                    </a>
                </div>
            </form>

            <div class="flex flex-wrap items-center gap-2 pt-2 border-t border-slate-100">
                <a href="{{ route('cars.index', request()->except(['trashed', 'page'])) }}"
                   class="inline-flex items-center min-h-[44px] px-3.5 py-1.5 rounded-xl text-xs font-bold transition {{ !request()->boolean('trashed') ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                    Unit Aktif
                </a>
                <a href="{{ route('cars.index', array_merge(request()->except('page'), ['trashed' => 1])) }}"
                   class="inline-flex items-center min-h-[44px] px-3.5 py-1.5 rounded-xl text-xs font-bold transition {{ request()->boolean('trashed') ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                    Arsip Terhapus
                </a>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
            @if($cars->isEmpty())
                <div class="py-16 px-6 text-center text-slate-600">
                    <p class="text-base font-bold text-slate-800">Tidak ada mobil ditemukan.</p>
                    <p class="text-sm mt-1">Coba sesuaikan kata kunci atau filter pencarian Anda.</p>
                </div>
            @else
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-[11px] uppercase tracking-wider text-slate-600 font-bold">
                                <th class="py-3.5 px-6">Urut</th>
                                <th class="py-3.5 px-6">Unit Mobil</th>
                                <th class="py-3.5 px-6">Kategori</th>
                                <th class="py-3.5 px-6">Warna &amp; Hex</th>
                                <th class="py-3.5 px-6">Harga OTR</th>
                                <th class="py-3.5 px-6">Stok &amp; Status</th>
                                <th class="py-3.5 px-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @foreach($cars as $car)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-4 px-6 font-mono font-bold text-slate-600 tabular-nums">
                                        #{{ $car->sort_order }}
                                    </td>
                                    <td class="py-4 px-6">
                                        <div class="flex items-center gap-3">
                                            <div class="w-12 h-10 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center shrink-0 overflow-hidden">
                                                @if($car->hero_image_transparent)
                                                    <img src="{{ asset($car->hero_image_transparent) }}" alt="{{ $car->model_name }}" class="w-full h-full object-contain p-1">
                                                @else
                                                    <span class="text-[10px] font-bold text-slate-600">IMG</span>
                                                @endif
                                            </div>
                                            <div>
                                                <p class="font-extrabold text-slate-900 leading-snug">{{ $car->brand }} {{ $car->model_name }}</p>
                                                <p class="text-xs text-slate-600 font-mono">Tahun {{ $car->year }} &bull; Slug: {{ $car->slug }}</p>
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
                                            <span class="w-3.5 h-3.5 rounded-full border border-slate-300 shadow-sm shrink-0" style="background-color: {{ $car->color_hex }}"></span>
                                            <span class="text-xs text-slate-700 font-medium">{{ $car->color_name }}</span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6 font-mono font-bold text-slate-900 tabular-nums">
                                        {{ $car->price_formatted }}
                                    </td>
                                    <td class="py-4 px-6">
                                        <div class="space-y-1">
                                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold {{ $car->is_available ? 'text-emerald-700' : 'text-slate-600' }}">
                                                <span class="w-1.5 h-1.5 rounded-full {{ $car->is_available ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                                {{ $car->is_available ? 'Tersedia' : 'Arsip' }}
                                            </span>
                                            <span class="text-[11px] text-slate-600 block tabular-nums">Stok: {{ $car->stock_quantity }} unit</span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6 text-right">
                                        <div class="inline-flex items-center justify-end gap-2">
                                            @if(!$car->trashed())
                                                <a href="{{ route('cars.show', $car) }}" class="inline-flex items-center justify-center min-h-[44px] px-3 py-1.5 rounded-xl bg-slate-100 text-slate-800 text-xs font-bold hover:bg-slate-200 transition">
                                                    Detail
                                                </a>
                                                @can('update', $car)
                                                    <a href="{{ route('cars.edit', $car) }}" class="inline-flex items-center justify-center min-h-[44px] px-3 py-1.5 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 transition">
                                                        Edit
                                                    </a>
                                                @endcan
                                                @can('delete', $car)
                                                    <form method="POST" action="{{ route('cars.destroy', $car) }}" class="inline" onsubmit="return confirm('Yakin ingin menghapus mobil ini?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="inline-flex items-center justify-center min-h-[44px] px-3 py-1.5 rounded-xl bg-red-50 text-red-700 text-xs font-bold hover:bg-red-100 transition">
                                                            Hapus
                                                        </button>
                                                    </form>
                                                @endcan
                                            @else
                                                @can('restore', $car)
                                                    <form method="POST" action="{{ route('cars.restore', $car->slug) }}" class="inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="inline-flex items-center justify-center min-h-[44px] px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 text-xs font-bold hover:bg-emerald-100 transition">
                                                            Pulihkan
                                                        </button>
                                                    </form>
                                                @endcan
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
                    @foreach($cars as $car)
                        <div class="p-5 space-y-3">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-14 h-11 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center shrink-0 overflow-hidden">
                                        @if($car->hero_image_transparent)
                                            <img src="{{ asset($car->hero_image_transparent) }}" alt="{{ $car->model_name }}" class="w-full h-full object-contain p-1">
                                        @else
                                            <span class="text-[10px] font-bold text-slate-600">IMG</span>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-extrabold text-sm text-slate-900 truncate">{{ $car->brand }} {{ $car->model_name }}</p>
                                        <p class="text-xs text-slate-600 font-mono">Tahun {{ $car->year }} &bull; #{{ $car->sort_order }}</p>
                                    </div>
                                </div>
                                <span class="inline-block px-2.5 py-0.5 rounded-full text-[11px] font-bold border shrink-0 {{ $car->category->color() }}">
                                    {{ $car->category->label() }}
                                </span>
                            </div>

                            <div class="flex flex-wrap items-center justify-between gap-2 text-xs">
                                <span class="font-mono font-bold text-sm text-slate-900 tabular-nums">{{ $car->price_formatted }}</span>
                                <span class="font-semibold {{ $car->is_available ? 'text-emerald-700' : 'text-slate-600' }} tabular-nums">
                                    {{ $car->is_available ? 'Tersedia' : 'Arsip' }} &middot; Stok {{ $car->stock_quantity }} unit
                                </span>
                            </div>

                            <div class="flex flex-wrap items-center gap-2 pt-1">
                                @if(!$car->trashed())
                                    <a href="{{ route('cars.show', $car) }}" class="flex-1 inline-flex items-center justify-center min-h-[44px] px-3 py-2 rounded-xl bg-slate-100 text-slate-800 text-xs font-bold hover:bg-slate-200 transition">
                                        Detail
                                    </a>
                                    @can('update', $car)
                                        <a href="{{ route('cars.edit', $car) }}" class="flex-1 inline-flex items-center justify-center min-h-[44px] px-3 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 transition">
                                            Edit
                                        </a>
                                    @endcan
                                    @can('delete', $car)
                                        <form method="POST" action="{{ route('cars.destroy', $car) }}" class="flex-1" onsubmit="return confirm('Yakin ingin menghapus mobil ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-full inline-flex items-center justify-center min-h-[44px] px-3 py-2 rounded-xl bg-red-50 text-red-700 text-xs font-bold hover:bg-red-100 transition">
                                                Hapus
                                            </button>
                                        </form>
                                    @endcan
                                @else
                                    @can('restore', $car)
                                        <form method="POST" action="{{ route('cars.restore', $car->slug) }}" class="w-full">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="w-full inline-flex items-center justify-center min-h-[44px] px-3 py-2 rounded-xl bg-emerald-50 text-emerald-700 text-xs font-bold hover:bg-emerald-100 transition">
                                                Pulihkan Unit
                                            </button>
                                        </form>
                                    @endcan
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="p-6 border-t border-slate-100">
                    {{ $cars->links() }}
                </div>
            @endif
        </div>
    </div>
</x-dashboard-layout>
