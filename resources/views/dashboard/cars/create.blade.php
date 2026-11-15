<x-dashboard-layout title="Tambah Mobil Baru" breadcrumb="Tambah Mobil">
    <div class="max-w-4xl mx-auto space-y-8" x-data="{
        features: @js(old('features', ['Apple CarPlay / Android Auto', 'Panoramic Sunroof', 'Keyless Smart Entry'])),
        newFeature: '',
        colorHex: @js(old('color_hex', '#1E293B')),
        addFeature() {
            if (this.newFeature.trim() !== '') {
                this.features.push(this.newFeature.trim());
                this.newFeature = '';
            }
        },
        removeFeature(index) {
            this.features.splice(index, 1);
        }
    }">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('cars.index') }}" class="text-xs font-bold text-slate-600 hover:text-slate-900 inline-flex items-center gap-1.5 mb-2 min-h-[44px] sm:min-h-0 py-1.5 transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>Kembali ke Katalog</span>
                </a>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900">Tambah Unit Mobil Baru</h1>
                <p class="text-slate-600 text-sm mt-1">Masukkan spesifikasi lengkap unit kendaraan untuk ditampilkan di katalog showroom.</p>
            </div>
        </div>

        <form action="{{ route('cars.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
            @csrf

            <!-- 1. Data Utama -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-8 space-y-6">
                <h2 class="text-base font-black text-slate-900 border-b border-slate-100 pb-3">1. Informasi Pokok Kendaraan</h2>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                    <div>
                        <label for="car_brand" class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Merk / Brand <span class="text-red-600">*</span></label>
                        <input id="car_brand" type="text" name="brand" value="{{ old('brand') }}" required placeholder="Contoh: Ford, BMW, Toyota"
                               class="w-full min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                        @error('brand') <p class="text-xs text-red-700 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="car_model_name" class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Model / Tipe <span class="text-red-600">*</span></label>
                        <input id="car_model_name" type="text" name="model_name" value="{{ old('model_name') }}" required placeholder="Contoh: Mustang GT / M4 Competition"
                               class="w-full min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                        @error('model_name') <p class="text-xs text-red-700 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="car_year" class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Tahun Perakitan <span class="text-red-600">*</span></label>
                        <input id="car_year" type="number" name="year" value="{{ old('year', date('Y')) }}" min="2000" max="{{ date('Y') + 2 }}" required
                               class="w-full min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none font-mono tabular-nums">
                        @error('year') <p class="text-xs text-red-700 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                    <div>
                        <label for="car_category" class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Kategori Bodi <span class="text-red-600">*</span></label>
                        <select id="car_category" name="category" required class="w-full min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                            @foreach($categories as $cat)
                                <option value="{{ $cat->value }}" {{ old('category') === $cat->value ? 'selected' : '' }}>{{ $cat->label() }}</option>
                            @endforeach
                        </select>
                        @error('category') <p class="text-xs text-red-700 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="car_color_name" class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Nama Warna <span class="text-red-600">*</span></label>
                        <input id="car_color_name" type="text" name="color_name" value="{{ old('color_name') }}" required placeholder="Contoh: Race Red / Brooklyn Grey"
                               class="w-full min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                        @error('color_name') <p class="text-xs text-red-700 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="car_color_hex" class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Kode Warna (Hex) <span class="text-red-600">*</span></label>
                        <div class="flex items-center gap-2">
                            <input id="car_color_hex" type="color" name="color_hex" x-model="colorHex"
                                   class="w-11 h-11 rounded-xl border border-slate-300 p-1 cursor-pointer shrink-0">
                            <input type="text" aria-label="Nilai hex warna" x-model="colorHex"
                                   class="flex-1 min-h-[44px] px-4 py-2 rounded-xl border border-slate-300 text-sm font-mono bg-slate-50 focus:ring-2 focus:ring-blue-600 focus:outline-none" readonly>
                        </div>
                        @error('color_hex') <p class="text-xs text-red-700 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                    <div>
                        <label for="car_price" class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Harga OTR (Rp) <span class="text-red-600">*</span></label>
                        <input id="car_price" type="number" step="100000" name="price" value="{{ old('price') }}" required placeholder="Contoh: 980000000"
                               class="w-full min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none font-mono tabular-nums">
                        @error('price') <p class="text-xs text-red-700 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="car_stock_quantity" class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Jumlah Stok Unit <span class="text-red-600">*</span></label>
                        <input id="car_stock_quantity" type="number" name="stock_quantity" value="{{ old('stock_quantity', 1) }}" min="0" required
                               class="w-full min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none font-mono tabular-nums">
                        @error('stock_quantity') <p class="text-xs text-red-700 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="car_sort_order" class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Urutan Prioritas (Sort Order)</label>
                        <input id="car_sort_order" type="number" name="sort_order" value="{{ old('sort_order', 1) }}" min="0"
                               class="w-full min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none font-mono tabular-nums">
                    </div>
                </div>

                <div>
                    <label for="car_description" class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Deskripsi Lengkap</label>
                    <textarea id="car_description" name="description" rows="3" placeholder="Tuliskan ulasan mendalam mengenai performa, kenyamanan, dan keunggulan mobil ini..."
                              class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">{{ old('description') }}</textarea>
                </div>

                <div class="flex flex-wrap items-center gap-6 pt-2">
                    <label class="inline-flex items-center gap-2.5 min-h-[44px] cursor-pointer">
                        <input type="hidden" name="is_available" value="0">
                        <input type="checkbox" name="is_available" value="1" @checked(filter_var(old('is_available', true), FILTER_VALIDATE_BOOLEAN))
                               class="rounded border-slate-300 text-slate-900 focus:ring-blue-600 w-5 h-5">
                        <span class="text-sm font-bold text-slate-800">Tersedia untuk Booking Showroom</span>
                    </label>

                    <label class="inline-flex items-center gap-2.5 min-h-[44px] cursor-pointer">
                        <input type="hidden" name="is_featured" value="0">
                        <input type="checkbox" name="is_featured" value="1" @checked(filter_var(old('is_featured', false), FILTER_VALIDATE_BOOLEAN))
                               class="rounded border-slate-300 text-slate-900 focus:ring-blue-600 w-5 h-5">
                        <span class="text-sm font-bold text-slate-800">Tampilkan sebagai Highlight (Featured)</span>
                    </label>
                </div>
            </div>

            <!-- 2. Spesifikasi Mesin (Engine Specs) -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-8 space-y-6">
                <h2 class="text-base font-black text-slate-900 border-b border-slate-100 pb-3">2. Spesifikasi Mesin &amp; Performa (HUD Showroom)</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div>
                        <label for="car_spec_power" class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Tenaga Mesin</label>
                        <input id="car_spec_power" type="text" name="engine_specs[power]" value="{{ old('engine_specs.power') }}" placeholder="Contoh: 450 HP"
                               class="w-full min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none font-mono">
                    </div>

                    <div>
                        <label for="car_spec_torque" class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Torsi Maksimum</label>
                        <input id="car_spec_torque" type="text" name="engine_specs[torque]" value="{{ old('engine_specs.torque') }}" placeholder="Contoh: 529 Nm"
                               class="w-full min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none font-mono">
                    </div>

                    <div>
                        <label for="car_spec_accel" class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Akselerasi 0-100</label>
                        <input id="car_spec_accel" type="text" name="engine_specs[accel]" value="{{ old('engine_specs.accel') }}" placeholder="Contoh: 4.3s"
                               class="w-full min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none font-mono">
                    </div>

                    <div>
                        <label for="car_spec_trans" class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Transmisi</label>
                        <input id="car_spec_trans" type="text" name="engine_specs[trans]" value="{{ old('engine_specs.trans') }}" placeholder="Contoh: 8AT"
                               class="w-full min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none font-mono">
                    </div>
                </div>
            </div>

            <!-- 3. Fitur Unggulan (Tags Input) -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-8 space-y-6">
                <h2 class="text-base font-black text-slate-900 border-b border-slate-100 pb-3">3. Fitur &amp; Kelengkapan Unggulan</h2>

                <div class="flex flex-col sm:flex-row gap-2">
                    <label for="car_new_feature" class="sr-only">Tambah fitur baru</label>
                    <input id="car_new_feature" type="text" x-model="newFeature" @keydown.enter.prevent="addFeature()" placeholder="Ketik nama fitur lalu tekan Tambah..."
                           class="flex-1 min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                    <button type="button" @click="addFeature()" class="min-h-[44px] px-5 py-2.5 bg-slate-900 text-white rounded-xl text-xs font-bold hover:bg-slate-800 transition shrink-0">
                        Tambah Fitur
                    </button>
                </div>

                <div class="flex flex-wrap gap-2 pt-2">
                    <template x-for="(feat, idx) in features" :key="idx">
                        <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-100 text-slate-800 text-xs font-bold border border-slate-200">
                            <span x-text="feat"></span>
                            <input type="hidden" name="features[]" :value="feat">
                            <button type="button" @click="removeFeature(idx)" aria-label="Hapus fitur" class="inline-flex items-center justify-center w-6 h-6 rounded-lg text-slate-600 hover:text-red-700 hover:bg-red-50 transition">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </span>
                    </template>
                </div>
            </div>

            <!-- 4. Media & Galeri Gambar (WebP Auto Pipeline) -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-8 space-y-6">
                <h2 class="text-base font-black text-slate-900 border-b border-slate-100 pb-3">4. Media Foto Kendaraan (Auto-WebP Optimizer)</h2>

                <div>
                    <label for="car_hero_image" class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Foto Hero Transparan Showroom (PNG/WebP max 2MB)</label>
                    <input id="car_hero_image" type="file" name="hero_image" accept="image/png,image/webp,image/jpeg"
                           class="w-full text-sm text-slate-600 file:mr-4 file:min-h-[44px] file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-900 file:text-white hover:file:bg-slate-800 cursor-pointer">
                    <p class="text-xs text-slate-600 mt-1.5">Sistem otomatis mengonversi ke WebP Q85 dan menyesuaikan dimensi.</p>
                    @error('hero_image') <p class="text-xs text-red-700 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="car_gallery_images" class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Galeri Foto Tambahan (Maksimal 10 gambar)</label>
                    <input id="car_gallery_images" type="file" name="gallery_images[]" multiple accept="image/png,image/webp,image/jpeg"
                           class="w-full text-sm text-slate-600 file:mr-4 file:min-h-[44px] file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-100 file:text-slate-800 hover:file:bg-slate-200 cursor-pointer">
                    @error('gallery_images.*') <p class="text-xs text-red-700 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- 5. Submit Button -->
            <div class="flex items-center justify-end gap-4">
                <a href="{{ route('cars.index') }}" class="inline-flex items-center justify-center min-h-[44px] px-6 py-3 rounded-xl font-bold text-sm text-slate-600 hover:bg-slate-100 transition">
                    Batal
                </a>
                <button type="submit" class="inline-flex items-center justify-center min-h-[44px] px-8 py-3 rounded-xl font-bold text-sm bg-slate-900 text-white hover:bg-slate-800 active:scale-[0.98] shadow-md transition">
                    Simpan Unit Mobil ke Katalog
                </button>
            </div>
        </form>
    </div>
</x-dashboard-layout>
