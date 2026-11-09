<x-dashboard-layout title="Tambah Suku Cadang Baru" breadcrumb="Tambah Suku Cadang">
    <div class="max-w-4xl mx-auto space-y-8" x-data="{
        brands: ['Toyota', 'Ford', 'BMW', 'Tesla', 'Honda', 'Mercedes-Benz'],
        newBrand: '',
        addBrand() {
            if (this.newBrand.trim() !== '') {
                this.brands.push(this.newBrand.trim());
                this.newBrand = '';
            }
        },
        removeBrand(index) {
            this.brands.splice(index, 1);
        }
    }">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('spare-parts.index') }}" class="text-xs font-bold text-slate-500 hover:text-slate-900 inline-flex items-center gap-1 mb-2">
                    &larr; Kembali ke Daftar Suku Cadang
                </a>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900">Tambah Suku Cadang Baru</h1>
                <p class="text-slate-500 text-sm mt-1">Lengkapi spesifikasi suku cadang, nomor part pabrikan, kuantitas stok, dan harga satuan.</p>
            </div>
        </div>

        <form action="{{ route('spare-parts.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
            @csrf

            <!-- 1. Data Pokok -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-6">
                <h2 class="text-base font-black text-slate-900 border-b border-slate-100 pb-3">1. Informasi Utama</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Nama Suku Cadang <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" required placeholder="Contoh: Brake Pad Set Depan" 
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-slate-900 focus:outline-none">
                        @error('name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Part Number / SKU <span class="text-red-500">*</span></label>
                        <input type="text" name="part_number" value="{{ old('part_number') }}" required placeholder="Contoh: BRK-FRD-001" 
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-slate-900 focus:outline-none font-mono">
                        @error('part_number') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Merk / Brand Produsen <span class="text-red-500">*</span></label>
                        <input type="text" name="brand" value="{{ old('brand') }}" required placeholder="Contoh: Brembo, Bosch, Toyota Genuine" 
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-slate-900 focus:outline-none">
                        @error('brand') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Kategori <span class="text-red-500">*</span></label>
                        <select name="category" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-slate-900 focus:outline-none">
                            <option value="">Pilih Kategori...</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->value }}" {{ old('category') === $cat->value ? 'selected' : '' }}>
                                    {{ $cat->label() }}
                                </option>
                            @endforeach
                        </select>
                        @error('category') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Deskripsi & Spesifikasi Singkat</label>
                    <textarea name="description" rows="3" placeholder="Jelaskan spesifikasi material, fungsi suku cadang, dan garansi..." 
                              class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-slate-900 focus:outline-none">{{ old('description') }}</textarea>
                    @error('description') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- 2. Harga & Inventaris Stok -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-6">
                <h2 class="text-base font-black text-slate-900 border-b border-slate-100 pb-3">2. Harga & Manajemen Stok</h2>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Harga Satuan (Rp) <span class="text-red-500">*</span></label>
                        <input type="number" step="1000" min="0" name="price" value="{{ old('price', 0) }}" required placeholder="Contoh: 1500000" 
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-slate-900 focus:outline-none font-mono">
                        @error('price') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Stok Tersedia <span class="text-red-500">*</span></label>
                        <input type="number" min="0" name="stock_quantity" value="{{ old('stock_quantity', 10) }}" required 
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-slate-900 focus:outline-none font-mono">
                        @error('stock_quantity') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Peringatan Stok Minimum <span class="text-red-500">*</span></label>
                        <input type="number" min="1" name="min_stock_alert" value="{{ old('min_stock_alert', 5) }}" required 
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-slate-900 focus:outline-none font-mono">
                        @error('min_stock_alert') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex items-center gap-6 pt-2">
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_available" value="1" {{ old('is_available', true) ? 'checked' : '' }} class="rounded border-slate-300 text-slate-900 focus:ring-slate-900">
                        <span class="text-sm font-bold text-slate-700">Tersedia untuk Dipasang / Dijual</span>
                    </label>

                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }} class="rounded border-slate-300 text-slate-900 focus:ring-slate-900">
                        <span class="text-sm font-bold text-slate-700">Produk Unggulan (Featured)</span>
                    </label>
                </div>
            </div>

            <!-- 3. Foto & Upload -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-6">
                <h2 class="text-base font-black text-slate-900 border-b border-slate-100 pb-3">3. Foto Suku Cadang</h2>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Unggah Foto Produk</label>
                    <input type="file" name="image" accept="image/png,image/jpeg,image/webp" 
                           class="w-full px-4 py-2 rounded-xl border border-slate-300 text-sm text-slate-600 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-900 file:text-white hover:file:bg-slate-800">
                    <p class="text-xs text-slate-400 mt-1">Format: JPG, PNG, atau WebP (Maks. 2MB). Jika dikosongkan, sistem akan menggunakan gambar representatif kategori.</p>
                    @error('image') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4">
                <a href="{{ route('spare-parts.index') }}" class="px-6 py-3 rounded-xl font-bold text-sm text-slate-600 hover:bg-slate-100 transition">
                    Batal
                </a>
                <button type="submit" class="px-8 py-3 rounded-xl bg-slate-900 text-white font-bold text-sm hover:bg-slate-800 transition shadow-md">
                    Simpan Suku Cadang
                </button>
            </div>
        </form>
    </div>
</x-dashboard-layout>
