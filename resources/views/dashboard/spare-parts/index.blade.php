<x-dashboard-layout title="Suku Cadang (Sparepart)" breadcrumb="Suku Cadang">
    <div class="space-y-8">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900">Manajemen Suku Cadang (Sparepart)</h1>
                <p class="text-slate-500 text-sm mt-1">Kelola inventaris suku cadang, pantau stok minimum, kompatibilitas kendaraan, dan harga suku cadang.</p>
            </div>
            @can('create', App\Models\SparePart::class)
                <a href="{{ route('spare-parts.create') }}" class="inline-flex items-center gap-2 bg-slate-900 text-white px-5 py-2.5 rounded-xl font-bold text-sm hover:bg-slate-800 transition shadow-sm">
                    + Tambah Suku Cadang Baru
                </a>
            @endcan
        </div>

        <!-- Filter & Search Bar -->
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <form method="GET" action="{{ route('spare-parts.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                <!-- Search -->
                <div class="lg:col-span-2">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, SKU/nomor part, atau merk..." 
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
                    <a href="{{ route('spare-parts.index') }}" class="px-3 py-2 bg-slate-100 text-slate-600 rounded-xl text-xs font-bold hover:bg-slate-200 transition">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
            @if($spareParts->isEmpty())
                <div class="py-16 text-center text-slate-400">
                    <p class="text-base font-bold text-slate-700">Tidak ada suku cadang ditemukan.</p>
                    <p class="text-sm mt-1">Coba sesuaikan kata kunci atau filter pencarian Anda.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 text-xs font-bold uppercase tracking-wider">
                                <th class="p-4 sm:p-5">Nama Suku Cadang</th>
                                <th class="p-4 sm:p-5">Part Number</th>
                                <th class="p-4 sm:p-5">Kategori</th>
                                <th class="p-4 sm:p-5">Harga Satuan</th>
                                <th class="p-4 sm:p-5">Stok</th>
                                <th class="p-4 sm:p-5">Status</th>
                                <th class="p-4 sm:p-5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @foreach($spareParts as $part)
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="p-4 sm:p-5">
                                        <div class="flex items-center gap-3">
                                            <img src="{{ $part->imageUrl }}" alt="{{ $part->name }}" class="w-10 h-10 object-contain rounded-lg border border-slate-200 bg-white p-0.5">
                                            <div>
                                                <p class="font-bold text-slate-900">{{ $part->name }}</p>
                                                <p class="text-xs text-slate-500">{{ $part->brand }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-4 sm:p-5 font-mono text-xs font-bold text-slate-700">
                                        {{ $part->part_number }}
                                    </td>
                                    <td class="p-4 sm:p-5">
                                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold border {{ $part->category->color() }}">
                                            {{ $part->category->label() }}
                                        </span>
                                    </td>
                                    <td class="p-4 sm:p-5 font-mono font-bold text-slate-900">
                                        Rp {{ number_format($part->price, 0, ',', '.') }}
                                    </td>
                                    <td class="p-4 sm:p-5">
                                        <div class="flex items-center gap-1.5 font-bold">
                                            <span class="{{ $part->isLowStock() ? 'text-red-600' : 'text-slate-900' }}">
                                                {{ $part->stock_quantity }}
                                            </span>
                                            @if($part->isLowStock())
                                                <span class="px-1.5 py-0.5 rounded text-[10px] bg-red-100 text-red-700 font-bold" title="Stok Minimum: {{ $part->min_stock_alert }}">
                                                    Low
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="p-4 sm:p-5">
                                        @if($part->is_available)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Nonaktif
                                            </span>
                                        @endif
                                    </td>
                                    <td class="p-4 sm:p-5 text-right space-x-2">
                                        <a href="{{ route('spare-parts.show', $part) }}" class="text-xs font-bold text-slate-600 hover:text-slate-900">
                                            Lihat
                                        </a>
                                        @can('update', $part)
                                            <a href="{{ route('spare-parts.edit', $part) }}" class="text-xs font-bold text-blue-600 hover:text-blue-800">
                                                Edit
                                            </a>
                                        @endcan
                                        @can('delete', $part)
                                            <form action="{{ route('spare-parts.destroy', $part) }}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus suku cadang ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-xs font-bold text-red-600 hover:text-red-800">
                                                    Hapus
                                                </button>
                                            </form>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($spareParts->hasPages())
                    <div class="p-4 sm:p-6 border-t border-slate-100">
                        {{ $spareParts->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</x-dashboard-layout>
