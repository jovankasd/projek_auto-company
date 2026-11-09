<x-dashboard-layout title="Buat Promo Baru" breadcrumb="Buat Promo">
    <div class="max-w-2xl mx-auto space-y-8">
        <div>
            <a href="{{ route('promos.index') }}" class="text-xs font-bold text-slate-500 hover:text-slate-900 inline-flex items-center gap-1 mb-2">
                &larr; Kembali ke Daftar Promo
            </a>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900">Buat Program Promo Baru</h1>
            <p class="text-slate-500 text-sm mt-1">Tentukan kode voucher unik, periode berlaku, dan besaran potongan harga.</p>
        </div>

        <form action="{{ route('promos.store') }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-6">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Kode Promo <span class="text-red-500">*</span></label>
                    <input type="text" name="code" value="{{ old('code') }}" required placeholder="Contoh: MERDEKA2024" 
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm font-mono uppercase focus:ring-2 focus:ring-slate-900 focus:outline-none">
                    @error('code') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Judul Promo <span class="text-red-500">*</span></label>
                    <input type="text" name="title" value="{{ old('title') }}" required placeholder="Contoh: Diskon Servis Spesial Kemerdekaan" 
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-slate-900 focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Deskripsi & Syarat Ketentuan</label>
                <textarea name="description" rows="3" placeholder="Jelaskan ketentuan penggunaan promo ini..." 
                          class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-slate-900 focus:outline-none">{{ old('description') }}</textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Tipe Diskon <span class="text-red-500">*</span></label>
                    <select name="discount_type" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-slate-900 focus:outline-none">
                        @foreach($discountTypes as $dt)
                            <option value="{{ $dt->value }}" {{ old('discount_type') === $dt->value ? 'selected' : '' }}>
                                {{ $dt->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Nilai Potongan <span class="text-red-500">*</span></label>
                    <input type="number" step="0.01" min="0" name="discount_value" value="{{ old('discount_value') }}" required placeholder="20 (% atau Rp)" 
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm font-mono focus:ring-2 focus:ring-slate-900 focus:outline-none">
                    <p class="text-[11px] text-slate-400 mt-1">Jika tipe persentase isi angka 1-100, jika fixed isi nominal rupiah.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Target Mobil Tertentu (Opsional)</label>
                    <select name="car_id" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-slate-900 focus:outline-none">
                        <option value="">Semua Mobil / Layanan Umum</option>
                        @foreach($cars as $car)
                            <option value="{{ $car->id }}" {{ old('car_id') == $car->id ? 'selected' : '' }}>
                                {{ $car->brand }} {{ $car->model_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Maksimal Pemakaian (Kuota)</label>
                    <input type="number" min="1" name="max_usage" value="{{ old('max_usage') }}" placeholder="Contoh: 100 (Kosongkan jika tak terbatas)" 
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm font-mono focus:ring-2 focus:ring-slate-900 focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Tanggal Mulai <span class="text-red-500">*</span></label>
                    <input type="datetime-local" name="starts_at" value="{{ old('starts_at', now()->format('Y-m-d\TH:i')) }}" required 
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-slate-900 focus:outline-none font-mono">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Tanggal Berakhir <span class="text-red-500">*</span></label>
                    <input type="datetime-local" name="ends_at" value="{{ old('ends_at', now()->addDays(30)->format('Y-m-d\TH:i')) }}" required 
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-slate-900 focus:outline-none font-mono">
                    @error('ends_at') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Banner Gambar Promo (Opsional)</label>
                <input type="file" name="banner_image" accept="image/png,image/webp,image/jpeg" 
                       class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-900 file:text-white hover:file:bg-slate-800 cursor-pointer">
            </div>

            <div class="pt-2">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }} 
                           class="rounded border-slate-300 text-slate-900 focus:ring-slate-900 w-4 h-4">
                    <span class="text-sm font-bold text-slate-800">Status Promo Aktif</span>
                </label>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('promos.index') }}" class="px-5 py-2.5 rounded-xl font-bold text-sm text-slate-600 hover:bg-slate-100">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl font-bold text-sm bg-slate-900 text-white hover:bg-slate-800 shadow-sm">
                    Simpan Promo
                </button>
            </div>
        </form>
    </div>
</x-dashboard-layout>
