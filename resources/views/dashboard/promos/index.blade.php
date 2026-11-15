<x-dashboard-layout title="Program Promo & Diskon" breadcrumb="Promo">
    <div class="space-y-8">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900">Program Promo &amp; Voucher</h1>
                <p class="text-slate-600 text-sm mt-1">Kelola kode diskon servis berkala, cashback unit kendaraan, dan batas periode promo.</p>
            </div>
            <a href="{{ route('promos.create') }}" class="inline-flex items-center justify-center min-h-[44px] gap-2 bg-slate-900 text-white px-5 py-2.5 rounded-xl font-bold text-sm hover:bg-slate-800 active:scale-[0.98] transition shadow-sm">
                + Buat Kode Promo Baru
            </a>
        </div>

        <!-- Filter & Search Bar -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm space-y-3">
            <form method="GET" action="{{ route('promos.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                @if(request()->boolean('trashed'))
                    <input type="hidden" name="trashed" value="1">
                @endif
                <div class="lg:col-span-2">
                    <label for="promo_filter_search" class="sr-only">Cari promo</label>
                    <input id="promo_filter_search" type="text" name="search" value="{{ request('search') }}" placeholder="Cari kode atau judul promo..."
                           class="w-full min-h-[44px] px-4 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                </div>

                <div>
                    <label for="promo_filter_active" class="sr-only">Filter status promo</label>
                    <select id="promo_filter_active" name="active" class="w-full min-h-[44px] px-4 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                        <option value="">Semua Status</option>
                        <option value="1" {{ request('active') === '1' ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ request('active') === '0' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="flex-1 min-h-[44px] py-2 px-4 bg-slate-900 text-white rounded-xl text-xs font-bold hover:bg-slate-800 transition">
                        Filter
                    </button>
                    <a href="{{ route('promos.index') }}" class="inline-flex items-center justify-center min-h-[44px] px-3.5 py-2 bg-slate-100 text-slate-700 rounded-xl text-xs font-bold hover:bg-slate-200 transition">
                        Reset
                    </a>
                </div>
            </form>

            <div class="flex flex-wrap items-center gap-2 pt-2 border-t border-slate-100">
                <a href="{{ route('promos.index', request()->except(['trashed', 'page'])) }}"
                   class="inline-flex items-center min-h-[44px] px-3.5 py-1.5 rounded-xl text-xs font-bold transition {{ !request()->boolean('trashed') ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                    Promo Tersimpan
                </a>
                <a href="{{ route('promos.index', array_merge(request()->except('page'), ['trashed' => 1])) }}"
                   class="inline-flex items-center min-h-[44px] px-3.5 py-1.5 rounded-xl text-xs font-bold transition {{ request()->boolean('trashed') ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                    Arsip Terhapus
                </a>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
            @if($promos->isEmpty())
                <div class="py-16 px-6 text-center text-slate-600">
                    <p class="text-base font-bold text-slate-800">Belum ada promo yang terdaftar.</p>
                    <p class="text-sm mt-1">Buat program diskon menarik untuk meningkatkan penjualan dan booking servis.</p>
                </div>
            @else
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-[11px] uppercase tracking-wider text-slate-600 font-bold">
                                <th class="py-3.5 px-6">Kode &amp; Judul</th>
                                <th class="py-3.5 px-6">Tipe Diskon</th>
                                <th class="py-3.5 px-6">Nilai Potongan</th>
                                <th class="py-3.5 px-6">Target Unit</th>
                                <th class="py-3.5 px-6">Periode Berlaku</th>
                                <th class="py-3.5 px-6">Penggunaan</th>
                                <th class="py-3.5 px-6">Status</th>
                                <th class="py-3.5 px-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @foreach($promos as $promo)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-4 px-6">
                                        <span class="inline-block px-2.5 py-0.5 rounded-lg bg-slate-900 text-white font-mono font-bold text-xs tracking-wider">
                                            {{ $promo->code }}
                                        </span>
                                        <p class="font-bold text-slate-900 mt-1">{{ $promo->title }}</p>
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="text-xs uppercase font-bold text-slate-700 bg-slate-100 px-2.5 py-1 rounded-md">
                                            {{ $promo->discount_type->label() }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 font-mono font-extrabold text-slate-900 tabular-nums">
                                        @if($promo->discount_type->value === 'percentage')
                                            {{ (int)$promo->discount_value }}%
                                        @else
                                            Rp{{ number_format($promo->discount_value, 0, ',', '.') }}
                                        @endif
                                    </td>
                                    <td class="py-4 px-6 text-xs text-slate-600">
                                        {{ $promo->car ? $promo->car->brand . ' ' . $promo->car->model_name : 'Semua Unit / Servis' }}
                                    </td>
                                    <td class="py-4 px-6 text-xs text-slate-600 tabular-nums">
                                        <span>{{ $promo->starts_at?->translatedFormat('d M Y') ?? '-' }}</span>
                                        <span class="text-slate-600 block">s/d {{ $promo->ends_at?->translatedFormat('d M Y') ?? '-' }}</span>
                                    </td>
                                    <td class="py-4 px-6 text-xs font-mono text-slate-600 tabular-nums">
                                        {{ $promo->used_count }} / {{ $promo->max_usage ?? 'Tanpa batas' }}
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider {{ $promo->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700' }}">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $promo->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                            {{ $promo->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-right">
                                        <div class="inline-flex items-center justify-end gap-2">
                                            @if($promo->trashed())
                                                <form method="POST" action="{{ route('promos.restore', $promo->id) }}" class="inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="inline-flex items-center justify-center min-h-[44px] px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 text-xs font-bold hover:bg-emerald-100 transition">
                                                        Pulihkan
                                                    </button>
                                                </form>
                                            @else
                                                <a href="{{ route('promos.edit', $promo) }}" class="inline-flex items-center justify-center min-h-[44px] px-3 py-1.5 rounded-xl bg-slate-100 text-slate-800 text-xs font-bold hover:bg-slate-200 transition">
                                                    Edit
                                                </a>
                                                <form method="POST" action="{{ route('promos.destroy', $promo) }}" class="inline" onsubmit="return confirm('Hapus promo ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="inline-flex items-center justify-center min-h-[44px] px-3 py-1.5 rounded-xl bg-red-50 text-red-700 text-xs font-bold hover:bg-red-100 transition">
                                                        Hapus
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
                    @foreach($promos as $promo)
                        <div class="p-5 space-y-3">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <span class="inline-block px-2.5 py-0.5 rounded-lg bg-slate-900 text-white font-mono font-bold text-xs tracking-wider">
                                        {{ $promo->code }}
                                    </span>
                                    <p class="font-bold text-sm text-slate-900 mt-1">{{ $promo->title }}</p>
                                </div>
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase tracking-wider shrink-0 {{ $promo->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700' }}">
                                    {{ $promo->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </div>

                            <div class="grid grid-cols-2 gap-2 text-xs text-slate-600 tabular-nums">
                                <div>
                                    <span class="font-semibold text-slate-700">Potongan:</span>
                                    <span class="font-mono font-bold text-slate-900">
                                        @if($promo->discount_type->value === 'percentage')
                                            {{ (int)$promo->discount_value }}%
                                        @else
                                            Rp{{ number_format($promo->discount_value, 0, ',', '.') }}
                                        @endif
                                    </span>
                                </div>
                                <div>
                                    <span class="font-semibold text-slate-700">Kuota:</span>
                                    <span class="font-mono">{{ $promo->used_count }} / {{ $promo->max_usage ?? 'Tanpa batas' }}</span>
                                </div>
                                <div class="col-span-2">
                                    Periode: {{ $promo->starts_at?->translatedFormat('d M Y') ?? '-' }} s/d {{ $promo->ends_at?->translatedFormat('d M Y') ?? '-' }}
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center gap-2 pt-1">
                                @if($promo->trashed())
                                    <form method="POST" action="{{ route('promos.restore', $promo->id) }}" class="w-full">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="w-full inline-flex items-center justify-center min-h-[44px] px-4 py-2 rounded-xl bg-emerald-50 text-emerald-700 text-xs font-bold hover:bg-emerald-100 transition">
                                            Pulihkan Promo
                                        </button>
                                    </form>
                                @else
                                    <a href="{{ route('promos.edit', $promo) }}" class="flex-1 inline-flex items-center justify-center min-h-[44px] px-4 py-2 rounded-xl bg-slate-100 text-slate-800 text-xs font-bold hover:bg-slate-200 transition">
                                        Edit
                                    </a>
                                    <form method="POST" action="{{ route('promos.destroy', $promo) }}" class="flex-1" onsubmit="return confirm('Hapus promo ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-full inline-flex items-center justify-center min-h-[44px] px-4 py-2 rounded-xl bg-red-50 text-red-700 text-xs font-bold hover:bg-red-100 transition">
                                            Hapus
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="p-6 border-t border-slate-100">
                    {{ $promos->links() }}
                </div>
            @endif
        </div>
    </div>
</x-dashboard-layout>
