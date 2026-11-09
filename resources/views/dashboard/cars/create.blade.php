<x-dashboard-layout title="Tambah Mobil Baru" breadcrumb="Tambah Mobil">
    <div class="max-w-4xl mx-auto space-y-8" x-data="{
        features: ['Apple CarPlay / Android Auto', 'Panoramic Sunroof', 'Keyless Smart Entry'],
        newFeature: '',
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
                <a href="{{ route('cars.index') }}" class="text-xs font-bold text-slate-500 hover:text-slate-900 inline-flex items-center gap-1 mb-2">
                    &larr; Kembali ke Katalog
                </a>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900">Tambah Unit Mobil Baru</h1>
                <p class="text-slate-500 text-sm mt-1">Masukkan spesifikasi lengkap unit kendaraan untuk ditampilkan di katalog showroom.</p>
            </div>
        </div>

        <form action="{{ route('cars.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
            @csrf

            <!-- 1. Data Utama -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-6">
                <h2 class="text-base font-black text-slate-900 border-b border-slate-100 pb-3">1. Informasi Pokok Kendaraan</h2>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Merk / Brand <span class="text-red-500">*</span></label>
                        <input type="text" name="brand" value="{{ old('brand') }}" required placeholder="Contoh: Ford, BMW, Toyota" 
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-slate-900 focus:outline-none">
                        @error('brand') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Model / Tipe <span class="text-red-500">*</span></label>
                        <input type="text" name="model_name" value="{{ old('model_name') }}" required placeholder="Contoh: Mustang GT / M4 Competition" 
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-slate-900 focus:outline-none">
                        @error('model_name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Tahun Perakitan <span class="text-red-500">*</span></label>
                        <input type="number" name="year" value="{{ old('year', date('Y')) }}" min="2000" max="{{ date('Y') + 2 }}" required 
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-slate-900 focus:outline-none font-mono">
                        @error('year') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Kategori Bodi <span class="text-red-500">*</span></label>
                        <select name="category" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-slate-900 focus:outline-none">
                            @foreach($categories as $cat)
                                <option value="{{ $cat->value }}" {{ old('category') === $cat->value ? 'selected' : '' }}>{{ $cat->label() }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Nama Warna <span class="text-red-500">*</span></label>
                        <input type="text" name="color_name" value="{{ old('color_name') }}" required placeholder="Contoh: Race Red / Brooklyn Grey" 
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-slate-900 focus:outline-none">
                        @error('color_name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Kode Warna (Hex) <span class="text-red-500">*</span></label>
                        <div class="flex items-center gap-2">
                            <input type="color" name="color_hex" value="{{ old('color_hex', '#1E293B') }}" 
                                   class="w-10 h-10 rounded-xl border border-slate-300 p-1 cursor-pointer">
                            <input type="text" name="color_hex_text" x-model="$el.previousElementSibling.value" 
                                   class="flex-1 px-4 py-2 rounded-xl border border-slate-300 text-sm font-mono focus:ring-2 focus:ring-slate-900 focus:outline-none" readonly>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Harga OTR (Rp) <span class="text-red-500">*</span></label>
                        <input type="number" step="100000" name="price" value="{{ old('price') }}" required placeholder="Contoh: 980000000" 
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-slate-900 focus:outline-none font-mono">
                        @error('price') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Jumlah Stok Unit <span class="text-red-500">*</span></label>
                        <input type="number" name="stock_quantity" value="{{ old('stock_quantity', 1) }}" min="0" required 
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-slate-900 focus:outline-none font-mono">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Urutan Prioritas (Sort Order)</label>
                        <input type="number" name="sort_order" value="{{ old('sort_order', 1) }}" min="0" 
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-slate-900 focus:outline-none font-mono">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Deskripsi Lengkap</label>
                    <textarea name="description" rows="3" placeholder="Tuliskan ulasan mendalam mengenai performa, kenyamanan, dan keunggulan mobil ini..." 
                              class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-slate-900 focus:outline-none">{{ old('description') }}</textarea>
                </div>

                <div class="flex items-center gap-6 pt-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_available" value="1" {{ old('is_available', '1') ? 'checked' : '' }} 
                               class="rounded border-slate-300 text-slate-900 focus:ring-slate-900 w-4 h-4">
                        <span class="text-sm font-bold text-slate-800">Tersedia untuk Booking Showroom</span>
                    </label>

                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }} 
                               class="rounded border-slate-300 text-slate-900 focus:ring-slate-900 w-4 h-4">
                        <span class="text-sm font-bold text-slate-800">Tampilkan sebagai Highlight (Featured)</span>
                    </label>
                </div>
            </div>

            <!-- 2. Spesifikasi Mesin (Engine Specs) -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-6">
                <h2 class="text-base font-black text-slate-900 border-b border-slate-100 pb-3">2. Spesifikasi Mesin & Performa (HUD Showroom)</h2>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-6">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Tenaga Mesin</label>
                        <input type="text" name="engine_specs[power]" value="{{ old('engine_specs.power') }}" placeholder="Contoh: 450 HP" 
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-slate-900 focus:outline-none font-mono">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Torsi Maksimum</label>
                        <input type="text" name="engine_specs[torque]" value="{{ old('engine_specs.torque') }}" placeholder="Contoh: 529 Nm" 
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-slate-900 focus:outline-none font-mono">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Akselerasi 0-100</label>
                        <input type="text" name="engine_specs[accel]" value="{{ old('engine_specs.accel') }}" placeholder="Contoh: 4.3s" 
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-slate-900 focus:outline-none font-mono">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Transmisi</label>
                        <input type="text" name="engine_specs[trans]" value="{{ old('engine_specs.trans') }}" placeholder="Contoh: 8AT" 
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-slate-900 focus:outline-none font-mono">
                    </div>
                </div>
            </div>

            <!-- 3. Fitur Unggulan (Tags Input) -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-6">
                <h2 class="text-base font-black text-slate-900 border-b border-slate-100 pb-3">3. Fitur & Kelengkapan Unggulan</h2>

                <div class="flex gap-2">
                    <input type="text" x-model="newFeature" @keydown.enter.prevent="addFeature()" placeholder="Ketik nama fitur lalu tekan Tambah..." 
                           class="flex-1 px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-slate-900 focus:outline-none">
                    <button type="button" @click="addFeature()" class="px-5 py-2.5 bg-slate-900 text-white rounded-xl text-xs font-bold hover:bg-slate-800 transition">
                        Tambah Fitur
                    </button>
                </div>

                <div class="flex flex-wrap gap-2 pt-2">
                    <template x-for="(feat, idx) in features" :key="idx">
                        <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-100 text-slate-800 text-xs font-bold border border-slate-200">
                            <span x-text="feat"></span>
                            <input type="hidden" name="features[]" :value="feat">
                            <button type="button" @click="removeFeature(idx)" class="text-slate-400 hover:text-red-600 font-black">&times;</button>
                        </span>
                    </template>
                </div>
            </div>

            <!-- 4. Media & Galeri Gambar (WebP Auto Pipeline) -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-6">
                <h2 class="text-base font-black text-slate-900 border-b border-slate-100 pb-3">4. Media Foto Kendaraan (Auto-WebP Optimizer)</h2>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Foto Hero Transparan Showroom (PNG/WebP max 2MB)</label>
                    <input type="file" name="hero_image" accept="image/png,image/webp,image/jpeg" 
                           class="w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-900 file:text-white hover:file:bg-slate-800 cursor-pointer">
                    <p class="text-[11px] text-slate-400 mt-1">Sistem otomatis mengonversi ke WebP Q85 dan menyesuaikan dimensi.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Galeri Foto Tambahan (Maksimal 10 gambar)</label>
                    <input type="file" name="gallery_images[]" multiple accept="image/png,image/webp,image/jpeg" 
                           class="w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-100 file:text-slate-800 hover:file:bg-slate-200 cursor-pointer">
                </div>
            </div>

            <!-- 5. Submit Button -->
            <div class="flex items-center justify-end gap-4">
                <a href="{{ route('cars.index') }}" class="px-6 py-3 rounded-xl font-bold text-sm text-slate-600 hover:bg-slate-100 transition">
                    Batal
                </a>
                <button type="submit" class="px-8 py-3 rounded-xl font-bold text-sm bg-slate-900 text-white hover:bg-slate-800 shadow-md transition">
                    Simpan Unit Mobil ke Katalog
                </button>
            </div>
        </form>
    </div>
</x-dashboard-layout>
