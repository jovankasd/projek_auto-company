<x-dashboard-layout title="Tambah Suku Cadang Baru" breadcrumb="Tambah Suku Cadang">
    <div class="max-w-4xl mx-auto space-y-8" x-data="{
        brands: @js(old('car_brand_compatibility', ['Toyota', 'Ford', 'BMW', 'Tesla', 'Honda', 'Mercedes-Benz'])),
        newBrand: '',
        models: @js(old('car_model_compatibility', [])),
        newModel: '',
        addBrand() {
            const val = this.newBrand.trim();
            if (val !== '' && !this.brands.includes(val)) {
                this.brands.push(val);
            }
            this.newBrand = '';
        },
        removeBrand(index) {
            this.brands.splice(index, 1);
        },
        addModel() {
            const val = this.newModel.trim();
            if (val !== '' && !this.models.includes(val)) {
                this.models.push(val);
            }
            this.newModel = '';
        },
        removeModel(index) {
            this.models.splice(index, 1);
        }
    }">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('spare-parts.index') }}" class="text-xs font-bold text-slate-600 hover:text-slate-900 inline-flex items-center gap-1.5 mb-2 min-h-[44px] sm:min-h-0 py-1.5 transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>Kembali ke Daftar Suku Cadang</span>
                </a>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900">Tambah Suku Cadang Baru</h1>
                <p class="text-slate-600 text-sm mt-1">Lengkapi spesifikasi suku cadang, nomor part pabrikan, kuantitas stok, dan harga satuan.</p>
            </div>
        </div>

        <form action="{{ route('spare-parts.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
            @csrf

            <!-- 1. Data Pokok -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-8 space-y-6">
                <h2 class="text-base font-black text-slate-900 border-b border-slate-100 pb-3">1. Informasi Utama</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label for="sp_name" class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Nama Suku Cadang <span class="text-red-600">*</span></label>
                        <input id="sp_name" type="text" name="name" value="{{ old('name') }}" required placeholder="Contoh: Brake Pad Set Depan"
                               class="w-full min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                        @error('name') <p class="text-xs text-red-700 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="sp_part_number" class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Part Number / SKU <span class="text-red-600">*</span></label>
                        <input id="sp_part_number" type="text" name="part_number" value="{{ old('part_number') }}" required placeholder="Contoh: BRK-FRD-001"
                               class="w-full min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none font-mono">
                        @error('part_number') <p class="text-xs text-red-700 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="sp_brand" class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Merk / Brand Produsen <span class="text-red-600">*</span></label>
                        <input id="sp_brand" type="text" name="brand" value="{{ old('brand') }}" required placeholder="Contoh: Brembo, Bosch, Toyota Genuine"
                               class="w-full min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                        @error('brand') <p class="text-xs text-red-700 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="sp_category" class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Kategori <span class="text-red-600">*</span></label>
                        <select id="sp_category" name="category" required class="w-full min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                            <option value="">Pilih Kategori...</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->value }}" {{ old('category') === $cat->value ? 'selected' : '' }}>
                                    {{ $cat->label() }}
                                </option>
                            @endforeach
                        </select>
                        @error('category') <p class="text-xs text-red-700 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label for="sp_description" class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Deskripsi &amp; Spesifikasi Singkat</label>
                    <textarea id="sp_description" name="description" rows="3" placeholder="Jelaskan spesifikasi material, fungsi suku cadang, dan garansi..."
                              class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">{{ old('description') }}</textarea>
                    @error('description') <p class="text-xs text-red-700 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- 2. Harga & Inventaris Stok -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-8 space-y-6">
                <h2 class="text-base font-black text-slate-900 border-b border-slate-100 pb-3">2. Harga &amp; Manajemen Stok</h2>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                    <div>
                        <label for="sp_price" class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Harga Satuan (Rp) <span class="text-red-600">*</span></label>
                        <input id="sp_price" type="number" step="1000" min="0" name="price" value="{{ old('price', 0) }}" required placeholder="Contoh: 1500000"
                               class="w-full min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none font-mono tabular-nums">
                        @error('price') <p class="text-xs text-red-700 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="sp_stock_quantity" class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Stok Tersedia <span class="text-red-600">*</span></label>
                        <input id="sp_stock_quantity" type="number" min="0" name="stock_quantity" value="{{ old('stock_quantity', 10) }}" required
                               class="w-full min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none font-mono tabular-nums">
                        @error('stock_quantity') <p class="text-xs text-red-700 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="sp_min_stock_alert" class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Peringatan Stok Minimum <span class="text-red-600">*</span></label>
                        <input id="sp_min_stock_alert" type="number" min="1" name="min_stock_alert" value="{{ old('min_stock_alert', 5) }}" required
                               class="w-full min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none font-mono tabular-nums">
                        @error('min_stock_alert') <p class="text-xs text-red-700 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-6 pt-2">
                    <label class="inline-flex items-center gap-2.5 min-h-[44px] cursor-pointer">
                        <input type="hidden" name="is_available" value="0">
                        <input type="checkbox" name="is_available" value="1" @checked(filter_var(old('is_available', true), FILTER_VALIDATE_BOOLEAN)) class="rounded border-slate-300 text-slate-900 focus:ring-blue-600 w-5 h-5">
                        <span class="text-sm font-bold text-slate-800">Tersedia untuk Dipasang / Dijual</span>
                    </label>

                    <label class="inline-flex items-center gap-2.5 min-h-[44px] cursor-pointer">
                        <input type="hidden" name="is_featured" value="0">
                        <input type="checkbox" name="is_featured" value="1" @checked(filter_var(old('is_featured', false), FILTER_VALIDATE_BOOLEAN)) class="rounded border-slate-300 text-slate-900 focus:ring-blue-600 w-5 h-5">
                        <span class="text-sm font-bold text-slate-800">Produk Unggulan (Featured)</span>
                    </label>
                </div>
            </div>

            <!-- 3. Kompatibilitas Kendaraan -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-8 space-y-6">
                <h2 class="text-base font-black text-slate-900 border-b border-slate-100 pb-3">3. Kompatibilitas Kendaraan</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-3">
                        <label for="sp_new_brand_compat" class="block text-xs font-bold uppercase text-slate-600">Kompatibilitas Merk Mobil</label>
                        <div class="flex gap-2">
                            <input id="sp_new_brand_compat" type="text" x-model="newBrand" @keydown.enter.prevent="addBrand()" placeholder="Contoh: Toyota, Ford"
                                   class="flex-1 min-h-[44px] px-4 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                            <button type="button" @click="addBrand()" class="min-h-[44px] px-4 py-2 bg-slate-900 text-white rounded-xl text-xs font-bold hover:bg-slate-800 transition shrink-0">
                                Tambah
                            </button>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <template x-for="(b, idx) in brands" :key="idx">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 text-slate-800 text-xs font-bold border border-slate-200">
                                    <span x-text="b"></span>
                                    <input type="hidden" name="car_brand_compatibility[]" :value="b">
                                    <button type="button" @click="removeBrand(idx)" aria-label="Hapus merk kompatibel" class="inline-flex items-center justify-center w-5 h-5 rounded-md text-slate-600 hover:text-red-700 hover:bg-red-50 transition">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </span>
                            </template>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <label for="sp_new_model_compat" class="block text-xs font-bold uppercase text-slate-600">Kompatibilitas Model Mobil</label>
                        <div class="flex gap-2">
                            <input id="sp_new_model_compat" type="text" x-model="newModel" @keydown.enter.prevent="addModel()" placeholder="Contoh: Mustang GT, Camry"
                                   class="flex-1 min-h-[44px] px-4 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                            <button type="button" @click="addModel()" class="min-h-[44px] px-4 py-2 bg-slate-900 text-white rounded-xl text-xs font-bold hover:bg-slate-800 transition shrink-0">
                                Tambah
                            </button>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <template x-for="(m, idx) in models" :key="idx">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 text-slate-800 text-xs font-bold border border-slate-200">
                                    <span x-text="m"></span>
                                    <input type="hidden" name="car_model_compatibility[]" :value="m">
                                    <button type="button" @click="removeModel(idx)" aria-label="Hapus model kompatibel" class="inline-flex items-center justify-center w-5 h-5 rounded-md text-slate-600 hover:text-red-700 hover:bg-red-50 transition">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </span>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. Foto & Upload -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-8 space-y-6">
                <h2 class="text-base font-black text-slate-900 border-b border-slate-100 pb-3">4. Foto Suku Cadang</h2>

                <div>
                    <label for="sp_image" class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Unggah Foto Produk</label>
                    <input id="sp_image" type="file" name="image" accept="image/png,image/jpeg,image/webp"
                           class="w-full px-4 py-2 rounded-xl border border-slate-300 text-sm text-slate-600 file:mr-4 file:min-h-[44px] file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-900 file:text-white hover:file:bg-slate-800 cursor-pointer">
                    <p class="text-xs text-slate-600 mt-1.5">Format: JPG, PNG, atau WebP (Maks. 2MB). Jika dikosongkan, sistem akan menggunakan gambar representatif kategori.</p>
                    @error('image') <p class="text-xs text-red-700 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4">
                <a href="{{ route('spare-parts.index') }}" class="inline-flex items-center justify-center min-h-[44px] px-6 py-3 rounded-xl font-bold text-sm text-slate-600 hover:bg-slate-100 transition">
                    Batal
                </a>
                <button type="submit" class="inline-flex items-center justify-center min-h-[44px] px-8 py-3 rounded-xl bg-slate-900 text-white font-bold text-sm hover:bg-slate-800 active:scale-[0.98] transition shadow-md">
                    Simpan Suku Cadang
                </button>
            </div>
        </form>
    </div>
</x-dashboard-layout>
