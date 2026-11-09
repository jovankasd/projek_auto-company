<x-dashboard-layout title="Detail Suku Cadang" breadcrumb="Detail Suku Cadang">
    <div class="max-w-4xl mx-auto space-y-8">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('spare-parts.index') }}" class="text-xs font-bold text-slate-500 hover:text-slate-900 inline-flex items-center gap-1 mb-2">
                    &larr; Kembali ke Daftar Suku Cadang
                </a>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900">{{ $sparePart->name }}</h1>
                <p class="text-slate-500 text-sm mt-1">Part Number: <span class="font-mono font-bold text-slate-800">{{ $sparePart->part_number }}</span></p>
            </div>
            @can('update', $sparePart)
                <a href="{{ route('spare-parts.edit', $sparePart) }}" class="inline-flex items-center gap-2 bg-slate-900 text-white px-5 py-2.5 rounded-xl font-bold text-sm hover:bg-slate-800 transition shadow-sm">
                    Edit Suku Cadang
                </a>
            @endcan
        </div>

        <!-- Detail Card -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden p-6 sm:p-8 space-y-8">
            <div class="flex flex-col sm:flex-row gap-6 items-start">
                <img src="{{ $sparePart->imageUrl }}" alt="{{ $sparePart->name }}" class="w-full sm:w-48 h-48 object-contain rounded-2xl border border-slate-200 bg-white p-2">
                <div class="flex-1 space-y-4">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold border {{ $sparePart->category->color() }}">
                            {{ $sparePart->category->label() }}
                        </span>
                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                            Merk: {{ $sparePart->brand }}
                        </span>
                        @if($sparePart->is_available)
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                Aktif
                            </span>
                        @else
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                                Nonaktif
                            </span>
                        @endif
                    </div>

                    <div class="text-3xl font-black text-slate-900 font-mono">
                        {{ $sparePart->price_formatted }}
                    </div>

                    <p class="text-slate-600 text-sm leading-relaxed">
                        {{ $sparePart->description ?? 'Tidak ada deskripsi spesifikasi untuk suku cadang ini.' }}
                    </p>
                </div>
            </div>

            <!-- Grid Info Stok & Kompatibilitas -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-6 border-t border-slate-100">
                <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100">
                    <p class="text-xs font-bold uppercase text-slate-500">Stok Gudang Saat Ini</p>
                    <p class="text-2xl font-black text-slate-900 mt-1 {{ $sparePart->isLowStock() ? 'text-red-600' : '' }}">
                        {{ $sparePart->stock_quantity }} unit
                    </p>
                    <p class="text-xs text-slate-500 mt-0.5">Batas Minimum: {{ $sparePart->min_stock_alert }} unit</p>
                </div>

                <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100">
                    <p class="text-xs font-bold uppercase text-slate-500">Kompatibilitas Merk Mobil</p>
                    <div class="flex flex-wrap gap-1 mt-2">
                        @if(!empty($sparePart->car_brand_compatibility))
                            @foreach($sparePart->car_brand_compatibility as $cb)
                                <span class="px-2 py-0.5 rounded bg-white border border-slate-200 text-xs font-bold text-slate-700">
                                    {{ $cb }}
                                </span>
                            @endforeach
                        @else
                            <span class="text-xs text-slate-400">Universal / Semua Merk</span>
                        @endif
                    </div>
                </div>

                <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100">
                    <p class="text-xs font-bold uppercase text-slate-500">Kompatibilitas Model Mobil</p>
                    <div class="flex flex-wrap gap-1 mt-2">
                        @if(!empty($sparePart->car_model_compatibility))
                            @foreach($sparePart->car_model_compatibility as $cm)
                                <span class="px-2 py-0.5 rounded bg-white border border-slate-200 text-xs font-bold text-slate-700">
                                    {{ $cm }}
                                </span>
                            @endforeach
                        @else
                            <span class="text-xs text-slate-400">Semua Model</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-dashboard-layout>
