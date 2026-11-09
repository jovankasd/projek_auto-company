<x-dashboard-layout title="Program Promo & Diskon" breadcrumb="Promo">
    <div class="space-y-8">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900">Program Promo & Voucher</h1>
                <p class="text-slate-500 text-sm mt-1">Kelola kode diskon servis berkala, cashback unit kendaraan, dan batas periode promo.</p>
            </div>
            <a href="{{ route('promos.create') }}" class="inline-flex items-center gap-2 bg-slate-900 text-white px-5 py-2.5 rounded-xl font-bold text-sm hover:bg-slate-800 transition shadow-sm">
                + Buat Kode Promo Baru
            </a>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
            @if($promos->isEmpty())
                <div class="py-16 text-center text-slate-400">
                    <p class="text-base font-bold text-slate-700">Belum ada promo yang terdaftar.</p>
                    <p class="text-sm mt-1">Buat program diskon menarik untuk meningkatkan penjualan dan booking servis.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-[11px] uppercase tracking-wider text-slate-500 font-bold">
                                <th class="py-3.5 px-6">Kode & Judul</th>
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
                                        <span class="text-xs uppercase font-bold text-slate-600 bg-slate-100 px-2.5 py-1 rounded-md">
                                            {{ $promo->discount_type->label() }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 font-mono font-extrabold text-slate-900">
                                        @if($promo->discount_type->value === 'percentage')
                                            {{ (int)$promo->discount_value }}%
                                        @else
                                            Rp{{ number_format($promo->discount_value, 0, ',', '.') }}
                                        @endif
                                    </td>
                                    <td class="py-4 px-6 text-xs text-slate-600">
                                        {{ $promo->car ? $promo->car->brand . ' ' . $promo->car->model_name : 'Semua Unit / Servis' }}
                                    </td>
                                    <td class="py-4 px-6 text-xs text-slate-600">
                                        <span>{{ $promo->starts_at->translatedFormat('d M Y') }}</span>
                                        <span class="text-slate-400 block">s/d {{ $promo->ends_at->translatedFormat('d M Y') }}</span>
                                    </td>
                                    <td class="py-4 px-6 text-xs font-mono text-slate-600">
                                        {{ $promo->used_count }} / {{ $promo->max_usage ?? '∞' }}
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider {{ $promo->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $promo->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                            {{ $promo->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-right space-x-2">
                                        <a href="{{ route('promos.edit', $promo) }}" class="inline-flex items-center px-2.5 py-1.5 rounded-lg bg-slate-100 text-slate-800 text-xs font-bold hover:bg-slate-200 transition">
                                            Edit
                                        </a>
                                        <form method="POST" action="{{ route('promos.destroy', $promo) }}" class="inline" onsubmit="return confirm('Hapus promo ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center px-2.5 py-1.5 rounded-lg bg-red-50 text-red-600 text-xs font-bold hover:bg-red-100 transition">
                                                Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-6 border-t border-slate-100">
                    {{ $promos->links() }}
                </div>
            @endif
        </div>
    </div>
</x-dashboard-layout>
