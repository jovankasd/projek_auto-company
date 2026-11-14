<x-dashboard-layout title="Pengaturan Sistem" breadcrumb="Pengaturan">
    <div class="max-w-4xl mx-auto space-y-8" x-data="{ tab: 'booking' }">
        <!-- Header -->
        <div>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900">Pengaturan Sistem & Operasional</h1>
            <p class="text-slate-500 text-sm mt-1">Konfigurasi kuota booking per jam, jam operasional dealer, metadata SEO, dan kontak WhatsApp.</p>
        </div>

        @if ($errors->any())
            <div class="p-4 rounded-2xl bg-red-50 border border-red-200 text-red-700 text-sm">
                <p class="font-bold mb-1">Terjadi kesalahan pada input pengaturan:</p>
                <ul class="list-disc list-inside space-y-0.5 text-xs">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Tab Nav -->
        <div class="flex items-center gap-2 border-b border-slate-200">
            <button type="button" @click="tab = 'booking'" 
                    :class="tab === 'booking' ? 'border-slate-900 text-slate-900 font-extrabold' : 'border-transparent text-slate-500 hover:text-slate-700'"
                    class="py-3 px-4 border-b-2 text-sm font-semibold transition">
                Operasional & Booking
            </button>
            <button type="button" @click="tab = 'contact'" 
                    :class="tab === 'contact' ? 'border-slate-900 text-slate-900 font-extrabold' : 'border-transparent text-slate-500 hover:text-slate-700'"
                    class="py-3 px-4 border-b-2 text-sm font-semibold transition">
                Kontak & Alamat
            </button>
            <button type="button" @click="tab = 'seo'" 
                    :class="tab === 'seo' ? 'border-slate-900 text-slate-900 font-extrabold' : 'border-transparent text-slate-500 hover:text-slate-700'"
                    class="py-3 px-4 border-b-2 text-sm font-semibold transition">
                SEO & Meta Showroom
            </button>
        </div>

        <form action="{{ route('settings.update') }}" method="POST" class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-6">
            @csrf
            @method('PATCH')

            <!-- Tab 1: Booking & Operasional -->
            <div x-show="tab === 'booking'" class="space-y-6">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Jam Buka Bengkel (WIB)</label>
                        <input type="number" name="booking_open_hour" min="0" max="23" 
                               value="{{ old('booking_open_hour', \App\Models\Setting::get('booking.open_hour', 8)) }}" required 
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm font-mono focus:ring-2 focus:ring-slate-900 focus:outline-none">
                        <p class="text-[11px] text-slate-400 mt-1">Pukul operasional dimulai.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Jam Tutup Bengkel (WIB)</label>
                        <input type="number" name="booking_close_hour" min="0" max="23" 
                               value="{{ old('booking_close_hour', \App\Models\Setting::get('booking.close_hour', 17)) }}" required 
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm font-mono focus:ring-2 focus:ring-slate-900 focus:outline-none">
                        <p class="text-[11px] text-slate-400 mt-1">Batas akhir penerimaan unit.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Maks. Kuota per Slot Jam (BR-02)</label>
                        <input type="number" name="booking_max_per_slot" min="1" max="50" 
                               value="{{ old('booking_max_per_slot', \App\Models\Setting::get('booking.max_per_slot', 3)) }}" required 
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm font-mono focus:ring-2 focus:ring-slate-900 focus:outline-none">
                        <p class="text-[11px] text-slate-400 mt-1">Batas kapasitas stall bengkel per jam (Default 3).</p>
                    </div>
                </div>
            </div>

            <!-- Tab 2: Kontak -->
            <div x-show="tab === 'contact'" class="space-y-6" x-cloak>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Nomor WhatsApp Resmi Dealer</label>
                        <input type="text" name="contact_whatsapp" 
                               value="{{ old('contact_whatsapp', \App\Models\Setting::get('contact.whatsapp', '6281234567890')) }}" required 
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm font-mono focus:ring-2 focus:ring-slate-900 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Email Layanan Pelanggan</label>
                        <input type="email" name="contact_email" 
                               value="{{ old('contact_email', \App\Models\Setting::get('contact.email', 'info@xyzmotors.id')) }}" required 
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-slate-900 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Alamat Showroom Utama</label>
                    <textarea name="contact_address" rows="2" 
                              class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-slate-900 focus:outline-none">{{ old('contact_address', \App\Models\Setting::get('contact.address', 'Jl. Sudirman No. 88, Jakarta Selatan 12190')) }}</textarea>
                </div>
            </div>

            <!-- Tab 3: SEO -->
            <div x-show="tab === 'seo'" class="space-y-6" x-cloak>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Nama Situs (Site Name)</label>
                        <input type="text" name="seo_site_name" 
                               value="{{ old('seo_site_name', \App\Models\Setting::get('seo.site_name', 'Dealer Pro')) }}" required 
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-slate-900 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Tagline Brand</label>
                        <input type="text" name="seo_tagline" 
                               value="{{ old('seo_tagline', \App\Models\Setting::get('seo.tagline', 'Drive Your Dream')) }}" required 
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-slate-900 focus:outline-none">
                    </div>
                </div>
            </div>

            <div class="pt-4 flex items-center justify-between border-t border-slate-100">
                <span class="text-xs text-slate-500 font-medium">Perubahan pengaturan langsung berlaku pada operasional sistem dan tampilan publik.</span>
                <button type="submit" class="px-6 py-2.5 rounded-xl font-bold text-sm bg-slate-900 text-white hover:bg-slate-800 shadow-sm transition">
                    Simpan Pengaturan
                </button>
            </div>
        </form>
    </div>
</x-dashboard-layout>
