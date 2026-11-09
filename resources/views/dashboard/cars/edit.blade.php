<x-dashboard-layout title="Edit Mobil {{ $car->brand }} {{ $car->model_name }}" breadcrumb="Edit Mobil">
    <div class="max-w-4xl mx-auto space-y-8" x-data="{
        features: {{ json_encode($car->features ?? []) }},
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
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900">Edit Mobil: {{ $car->brand }} {{ $car->model_name }}</h1>
                <p class="text-slate-500 text-sm mt-1">Perbarui data spesifikasi, harga, status ketersediaan, atau galeri foto kendaraan.</p>
            </div>
        </div>

        <form action="{{ route('cars.update', $car) }}" method="POST" enctype="multipart/form-data" class="space-y-8">
            @csrf
            @method('PUT')

            <!-- 1. Data Utama -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-6">
                <h2 class="text-base font-black text-slate-900 border-b border-slate-100 pb-3">1. Informasi Pokok Kendaraan</h2>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Merk / Brand <span class="text-red-500">*</span></label>
                        <input type="text" name="brand" value="{{ old('brand', $car->brand) }}" required 
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-slate-900 focus:outline-none">
                        @error('brand') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Model / Tipe <span class="text-red-500">*</span></label>
                        <input type="text" name="model_name" value="{{ old('model_name', $car->model_name) }}" required 
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-slate-900 focus:outline-none">
                        @error('model_name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Tahun Perakitan <span class="text-red-500">*</span></label>
                        <input type="number" name="year" value="{{ old('year', $car->year) }}" min="2000" max="{{ date('Y') + 2 }}" required 
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-slate-900 focus:outline-none font-mono">
                        @error('year') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Kategori Bodi <span class="text-red-500">*</span></label>
                        <select name="category" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-slate-900 focus:outline-none">
                            @foreach($categories as $cat)
                                <option value="{{ $cat->value }}" {{ (old('category', $car->category->value ?? $car->category) === $cat->value) ? 'selected' : '' }}>
                                    {{ $cat->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Nama Warna <span class="text-red-500">*</span></label>
                        <input type="text" name="color_name" value="{{ old('color_name', $car->color_name) }}" required 
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-slate-900 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Kode Warna (Hex) <span class="text-red-500">*</span></label>
                        <div class="flex items-center gap-2">
                            <input type="color" name="color_hex" value="{{ old('color_hex', $car->color_hex) }}" 
                                   class="w-10 h-10 rounded-xl border border-slate-300 p-1 cursor-pointer">
                            <input type="text" name="color_hex_text" x-model="$el.previousElementSibling.value" 
                                   class="flex-1 px-4 py-2 rounded-xl border border-slate-300 text-sm font-mono focus:ring-2 focus:ring-slate-900 focus:outline-none" readonly>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Harga OTR (Rp) <span class="text-red-500">*</span></label>
                        <input type="number" step="100000" name="price" value="{{ old('price', (int)$car->price) }}" required 
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-slate-900 focus:outline-none font-mono">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Jumlah Stok Unit <span class="text-red-500">*</span></label>
                        <input type="number" name="stock_quantity" value="{{ old('stock_quantity', $car->stock_quantity) }}" min="0" required 
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-slate-900 focus:outline-none font-mono">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Urutan Tampil (Sort Order)</label>
                        <input type="number" name="sort_order" value="{{ old('sort_order', $car->sort_order) }}" min="0" 
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-slate-900 focus:outline-none font-mono">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Deskripsi Kendaraan</label>
                    <textarea name="description" rows="3" 
                              class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-slate-900 focus:outline-none">{{ old('description', $car->description) }}</textarea>
                </div>

                <div class="flex items-center gap-6 pt-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_available" value="1" {{ old('is_available', $car->is_available) ? 'checked' : '' }} 
                               class="rounded border-slate-300 text-slate-900 focus:ring-slate-900 w-4 h-4">
                        <span class="text-sm font-bold text-slate-800">Tersedia untuk Booking Showroom</span>
                    </label>

                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $car->is_featured) ? 'checked' : '' }} 
                               class="rounded border-slate-300 text-slate-900 focus:ring-slate-900 w-4 h-4">
                        <span class="text-sm font-bold text-slate-800">Tampilkan sebagai Highlight (Featured)</span>
                    </label>
                </div>
            </div>

            <!-- 2. Engine Specs -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-6">
                <h2 class="text-base font-black text-slate-900 border-b border-slate-100 pb-3">2. Spesifikasi Mesin (HUD)</h2>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-6">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Tenaga</label>
                        <input type="text" name="engine_specs[power]" value="{{ old('engine_specs.power', $car->engine_specs['power'] ?? '') }}" 
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm font-mono focus:ring-2 focus:ring-slate-900 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Torsi</label>
                        <input type="text" name="engine_specs[torque]" value="{{ old('engine_specs.torque', $car->engine_specs['torque'] ?? '') }}" 
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm font-mono focus:ring-2 focus:ring-slate-900 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Akselerasi</label>
                        <input type="text" name="engine_specs[accel]" value="{{ old('engine_specs.accel', $car->engine_specs['accel'] ?? '') }}" 
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm font-mono focus:ring-2 focus:ring-slate-900 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Transmisi</label>
                        <input type="text" name="engine_specs[trans]" value="{{ old('engine_specs.trans', $car->engine_specs['trans'] ?? '') }}" 
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm font-mono focus:ring-2 focus:ring-slate-900 focus:outline-none">
                    </div>
                </div>
            </div>

            <!-- 3. Fitur Unggulan -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-6">
                <h2 class="text-base font-black text-slate-900 border-b border-slate-100 pb-3">3. Fitur & Kelengkapan</h2>

                <div class="flex gap-2">
                    <input type="text" x-model="newFeature" @keydown.enter.prevent="addFeature()" placeholder="Ketik nama fitur..." 
                           class="flex-1 px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-slate-900 focus:outline-none">
                    <button type="button" @click="addFeature()" class="px-5 py-2.5 bg-slate-900 text-white rounded-xl text-xs font-bold hover:bg-slate-800 transition">
                        Tambah
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

            <!-- 4. Media Gambar -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-6">
                <h2 class="text-base font-black text-slate-900 border-b border-slate-100 pb-3">4. Media Foto Kendaraan</h2>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 mb-2">Foto Hero Transparan Showroom Saat Ini</label>
                    @if($car->hero_image_transparent)
                        <div class="w-48 h-28 rounded-xl bg-slate-100 border border-slate-200 p-2 flex items-center justify-center mb-3">
                            <img src="{{ asset($car->hero_image_transparent) }}" alt="{{ $car->model_name }}" class="max-h-full object-contain">
                        </div>
                    @endif
                    <input type="file" name="hero_image" accept="image/png,image/webp,image/jpeg" 
                           class="w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-900 file:text-white hover:file:bg-slate-800 cursor-pointer">
                    <p class="text-[11px] text-slate-400 mt-1">Unggah file baru jika ingin mengganti foto hero transparan.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Unggah Galeri Tambahan (Maks. 10 gambar)</label>
                    <input type="file" name="gallery_images[]" multiple accept="image/png,image/webp,image/jpeg" 
                           class="w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-100 file:text-slate-800 hover:file:bg-slate-200 cursor-pointer">
                </div>
            </div>

            <!-- 5. Submit -->
            <div class="flex items-center justify-end gap-4">
                <a href="{{ route('cars.index') }}" class="px-6 py-3 rounded-xl font-bold text-sm text-slate-600 hover:bg-slate-100 transition">
                    Batal
                </a>
                <button type="submit" class="px-8 py-3 rounded-xl font-bold text-sm bg-slate-900 text-white hover:bg-slate-800 shadow-md transition">
                    Simpan Perubahan Mobil
                </button>
            </div>
        </form>
    </div>
</x-dashboard-layout>
