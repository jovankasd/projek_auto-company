<x-dashboard-layout title="Buat Promo Baru" breadcrumb="Buat Promo">
    <div class="max-w-2xl mx-auto space-y-8">
        <div>
            <a href="{{ route('promos.index') }}" class="text-xs font-bold text-slate-600 hover:text-slate-900 inline-flex items-center gap-1.5 mb-2 min-h-[44px] sm:min-h-0 py-1.5 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Kembali ke Daftar Promo</span>
            </a>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900">Buat Program Promo Baru</h1>
            <p class="text-slate-600 text-sm mt-1">Tentukan kode voucher unik, periode berlaku, dan besaran potongan harga.</p>
        </div>

        <form action="{{ route('promos.store') }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-8 space-y-6">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label for="promo_code" class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Kode Promo <span class="text-red-600">*</span></label>
                    <input id="promo_code" type="text" name="code" value="{{ old('code') }}" required placeholder="Contoh: MERDEKA2024"
                           class="w-full min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-300 text-sm font-mono uppercase focus:ring-2 focus:ring-blue-600 focus:outline-none">
                    @error('code') <p class="text-xs text-red-700 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="promo_title" class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Judul Promo <span class="text-red-600">*</span></label>
                    <input id="promo_title" type="text" name="title" value="{{ old('title') }}" required placeholder="Contoh: Diskon Servis Spesial Kemerdekaan"
                           class="w-full min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                    @error('title') <p class="text-xs text-red-700 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label for="promo_description" class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Deskripsi &amp; Syarat Ketentuan</label>
                <textarea id="promo_description" name="description" rows="3" placeholder="Jelaskan ketentuan penggunaan promo ini..."
                          class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">{{ old('description') }}</textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label for="promo_discount_type" class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Tipe Diskon <span class="text-red-600">*</span></label>
                    <select id="promo_discount_type" name="discount_type" required class="w-full min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                        @foreach($discountTypes as $dt)
                            <option value="{{ $dt->value }}" {{ old('discount_type') === $dt->value ? 'selected' : '' }}>
                                {{ $dt->label() }}
                            </option>
                        @endforeach
                    </select>
                    @error('discount_type') <p class="text-xs text-red-700 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="promo_discount_value" class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Nilai Potongan <span class="text-red-600">*</span></label>
                    <input id="promo_discount_value" type="number" step="0.01" min="0" name="discount_value" value="{{ old('discount_value') }}" required placeholder="20 (% atau Rp)"
                           class="w-full min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-300 text-sm font-mono tabular-nums focus:ring-2 focus:ring-blue-600 focus:outline-none">
                    <p class="text-xs text-slate-600 mt-1">Jika tipe persentase isi angka 1-100, jika fixed isi nominal rupiah.</p>
                    @error('discount_value') <p class="text-xs text-red-700 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label for="promo_car_id" class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Target Mobil Tertentu (Opsional)</label>
                    <select id="promo_car_id" name="car_id" class="w-full min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                        <option value="">Semua Mobil / Layanan Umum</option>
                        @foreach($cars as $car)
                            <option value="{{ $car->id }}" {{ old('car_id') == $car->id ? 'selected' : '' }}>
                                {{ $car->brand }} {{ $car->model_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="promo_max_usage" class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Maksimal Pemakaian (Kuota)</label>
                    <input id="promo_max_usage" type="number" min="1" name="max_usage" value="{{ old('max_usage') }}" placeholder="Contoh: 100 (Kosongkan jika tak terbatas)"
                           class="w-full min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-300 text-sm font-mono tabular-nums focus:ring-2 focus:ring-blue-600 focus:outline-none">
                    @error('max_usage') <p class="text-xs text-red-700 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label for="promo_starts_at" class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Tanggal Mulai <span class="text-red-600">*</span></label>
                    <input id="promo_starts_at" type="datetime-local" name="starts_at" value="{{ old('starts_at', now()->format('Y-m-d\TH:i')) }}" required
                           class="w-full min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none font-mono">
                    @error('starts_at') <p class="text-xs text-red-700 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="promo_ends_at" class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Tanggal Berakhir <span class="text-red-600">*</span></label>
                    <input id="promo_ends_at" type="datetime-local" name="ends_at" value="{{ old('ends_at', now()->addDays(30)->format('Y-m-d\TH:i')) }}" required
                           class="w-full min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none font-mono">
                    @error('ends_at') <p class="text-xs text-red-700 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label for="promo_banner_image" class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Banner Gambar Promo (Opsional)</label>
                <input id="promo_banner_image" type="file" name="banner_image" accept="image/png,image/webp,image/jpeg"
                       class="w-full text-sm text-slate-600 file:mr-4 file:min-h-[44px] file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-900 file:text-white hover:file:bg-slate-800 cursor-pointer">
                @error('banner_image') <p class="text-xs text-red-700 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="pt-2">
                <label class="inline-flex items-center gap-2.5 min-h-[44px] cursor-pointer">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" @checked(filter_var(old('is_active', true), FILTER_VALIDATE_BOOLEAN))
                           class="rounded border-slate-300 text-slate-900 focus:ring-blue-600 w-5 h-5">
                    <span class="text-sm font-bold text-slate-800">Status Promo Aktif</span>
                </label>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('promos.index') }}" class="inline-flex items-center justify-center min-h-[44px] px-5 py-2.5 rounded-xl font-bold text-sm text-slate-600 hover:bg-slate-100 transition">
                    Batal
                </a>
                <button type="submit" class="inline-flex items-center justify-center min-h-[44px] px-6 py-2.5 rounded-xl font-bold text-sm bg-slate-900 text-white hover:bg-slate-800 active:scale-[0.98] shadow-sm transition">
                    Simpan Promo
                </button>
            </div>
        </form>
    </div>
</x-dashboard-layout>
