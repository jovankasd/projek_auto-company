<x-dashboard-layout title="Edit Promo {{ $promo->code }}" breadcrumb="Edit Promo">
    <div class="max-w-2xl mx-auto space-y-8">
        <div>
            <a href="{{ route('promos.index') }}" class="text-xs font-bold text-slate-600 hover:text-slate-900 inline-flex items-center gap-1.5 mb-2 min-h-[44px] sm:min-h-0 py-1.5 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Kembali ke Daftar Promo</span>
            </a>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900">Edit Program Promo: {{ $promo->code }}</h1>
            <p class="text-slate-600 text-sm mt-1">Perbarui rincian potongan diskon, periode berlaku, atau status kuota.</p>
        </div>

        <form action="{{ route('promos.update', $promo) }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-8 space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label for="edit_promo_code" class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Kode Promo <span class="text-red-600">*</span></label>
                    <input id="edit_promo_code" type="text" name="code" value="{{ old('code', $promo->code) }}" required
                           class="w-full min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-300 text-sm font-mono uppercase focus:ring-2 focus:ring-blue-600 focus:outline-none">
                    @error('code') <p class="text-xs text-red-700 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="edit_promo_title" class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Judul Promo <span class="text-red-600">*</span></label>
                    <input id="edit_promo_title" type="text" name="title" value="{{ old('title', $promo->title) }}" required
                           class="w-full min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                    @error('title') <p class="text-xs text-red-700 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label for="edit_promo_description" class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Deskripsi &amp; Syarat Ketentuan</label>
                <textarea id="edit_promo_description" name="description" rows="3"
                          class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">{{ old('description', $promo->description) }}</textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label for="edit_promo_discount_type" class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Tipe Diskon <span class="text-red-600">*</span></label>
                    <select id="edit_promo_discount_type" name="discount_type" required class="w-full min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                        @foreach($discountTypes as $dt)
                            <option value="{{ $dt->value }}" {{ old('discount_type', $promo->discount_type->value ?? $promo->discount_type) === $dt->value ? 'selected' : '' }}>
                                {{ $dt->label() }}
                            </option>
                        @endforeach
                    </select>
                    @error('discount_type') <p class="text-xs text-red-700 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="edit_promo_discount_value" class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Nilai Potongan <span class="text-red-600">*</span></label>
                    <input id="edit_promo_discount_value" type="number" step="0.01" min="0" name="discount_value" value="{{ old('discount_value', $promo->discount_value) }}" required
                           class="w-full min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-300 text-sm font-mono tabular-nums focus:ring-2 focus:ring-blue-600 focus:outline-none">
                    @error('discount_value') <p class="text-xs text-red-700 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label for="edit_promo_car_id" class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Target Mobil Tertentu</label>
                    <select id="edit_promo_car_id" name="car_id" class="w-full min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                        <option value="">Semua Mobil / Layanan Umum</option>
                        @foreach($cars as $car)
                            <option value="{{ $car->id }}" {{ old('car_id', $promo->car_id) == $car->id ? 'selected' : '' }}>
                                {{ $car->brand }} {{ $car->model_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="edit_promo_max_usage" class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Maksimal Pemakaian (Kuota)</label>
                    <input id="edit_promo_max_usage" type="number" min="1" name="max_usage" value="{{ old('max_usage', $promo->max_usage) }}"
                           class="w-full min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-300 text-sm font-mono tabular-nums focus:ring-2 focus:ring-blue-600 focus:outline-none">
                    @error('max_usage') <p class="text-xs text-red-700 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label for="edit_promo_starts_at" class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Tanggal Mulai <span class="text-red-600">*</span></label>
                    <input id="edit_promo_starts_at" type="datetime-local" name="starts_at" value="{{ old('starts_at', $promo->starts_at ? $promo->starts_at->format('Y-m-d\TH:i') : '') }}" required
                           class="w-full min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none font-mono">
                    @error('starts_at') <p class="text-xs text-red-700 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="edit_promo_ends_at" class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Tanggal Berakhir <span class="text-red-600">*</span></label>
                    <input id="edit_promo_ends_at" type="datetime-local" name="ends_at" value="{{ old('ends_at', $promo->ends_at ? $promo->ends_at->format('Y-m-d\TH:i') : '') }}" required
                           class="w-full min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none font-mono">
                    @error('ends_at') <p class="text-xs text-red-700 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label for="edit_promo_banner_image" class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Perbarui Banner (Opsional)</label>
                @if($promo->banner_image)
                    <div class="mb-3 w-48 h-24 rounded-xl overflow-hidden border border-slate-200 bg-slate-100">
                        <img src="{{ asset($promo->banner_image) }}" alt="{{ $promo->title }}" class="w-full h-full object-cover">
                    </div>
                @endif
                <input id="edit_promo_banner_image" type="file" name="banner_image" accept="image/png,image/webp,image/jpeg"
                       class="w-full text-sm text-slate-600 file:mr-4 file:min-h-[44px] file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-900 file:text-white hover:file:bg-slate-800 cursor-pointer">
                @error('banner_image') <p class="text-xs text-red-700 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="pt-2">
                <label class="inline-flex items-center gap-2.5 min-h-[44px] cursor-pointer">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" @checked(filter_var(old('is_active', $promo->is_active), FILTER_VALIDATE_BOOLEAN))
                           class="rounded border-slate-300 text-slate-900 focus:ring-blue-600 w-5 h-5">
                    <span class="text-sm font-bold text-slate-800">Status Promo Aktif</span>
                </label>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('promos.index') }}" class="inline-flex items-center justify-center min-h-[44px] px-5 py-2.5 rounded-xl font-bold text-sm text-slate-600 hover:bg-slate-100 transition">
                    Batal
                </a>
                <button type="submit" class="inline-flex items-center justify-center min-h-[44px] px-6 py-2.5 rounded-xl font-bold text-sm bg-slate-900 text-white hover:bg-slate-800 active:scale-[0.98] shadow-sm transition">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</x-dashboard-layout>
