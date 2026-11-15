<x-dashboard-layout title="Pengaturan Sistem" breadcrumb="Pengaturan">
    <div class="max-w-4xl mx-auto space-y-8" x-data="{ tab: @js($errors->hasAny(['contact_whatsapp', 'contact_email', 'contact_address']) ? 'contact' : ($errors->hasAny(['seo_site_name', 'seo_tagline']) ? 'seo' : 'booking')) }">
        <!-- Header -->
        <div>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900">Pengaturan Sistem &amp; Operasional</h1>
            <p class="text-slate-600 text-sm mt-1">Konfigurasi kuota booking per jam, jam operasional dealer, metadata SEO, dan kontak WhatsApp.</p>
        </div>

        <!-- Tab Nav -->
        <div class="flex flex-wrap items-center gap-2 border-b border-slate-200">
            <button type="button" @click="tab = 'booking'"
                    :class="tab === 'booking' ? 'border-slate-900 text-slate-900 font-extrabold' : 'border-transparent text-slate-600 hover:text-slate-900'"
                    class="min-h-[44px] py-3 px-4 border-b-2 text-sm font-semibold transition">
                Operasional &amp; Booking
            </button>
            <button type="button" @click="tab = 'contact'"
                    :class="tab === 'contact' ? 'border-slate-900 text-slate-900 font-extrabold' : 'border-transparent text-slate-600 hover:text-slate-900'"
                    class="min-h-[44px] py-3 px-4 border-b-2 text-sm font-semibold transition">
                Kontak &amp; Alamat
            </button>
            <button type="button" @click="tab = 'seo'"
                    :class="tab === 'seo' ? 'border-slate-900 text-slate-900 font-extrabold' : 'border-transparent text-slate-600 hover:text-slate-900'"
                    class="min-h-[44px] py-3 px-4 border-b-2 text-sm font-semibold transition">
                SEO &amp; Meta Showroom
            </button>
        </div>

        <form action="{{ route('settings.update') }}" method="POST" class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-8 space-y-6">
            @csrf
            @method('PATCH')

            <!-- Tab 1: Booking & Operasional -->
            <div x-show="tab === 'booking'" class="space-y-6">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                    <div>
                        <label for="setting_booking_open_hour" class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Jam Buka Bengkel (WIB)</label>
                        <input id="setting_booking_open_hour" type="number" name="booking_open_hour" min="0" max="23"
                               value="{{ old('booking_open_hour', \App\Models\Setting::get('booking.open_hour', 8)) }}" required
                               class="w-full min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-300 text-sm font-mono tabular-nums focus:ring-2 focus:ring-blue-600 focus:outline-none">
                        <p class="text-xs text-slate-600 mt-1">Pukul operasional dimulai.</p>
                        @error('booking_open_hour') <p class="text-xs text-red-700 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="setting_booking_close_hour" class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Jam Tutup Bengkel (WIB)</label>
                        <input id="setting_booking_close_hour" type="number" name="booking_close_hour" min="0" max="23"
                               value="{{ old('booking_close_hour', \App\Models\Setting::get('booking.close_hour', 17)) }}" required
                               class="w-full min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-300 text-sm font-mono tabular-nums focus:ring-2 focus:ring-blue-600 focus:outline-none">
                        <p class="text-xs text-slate-600 mt-1">Batas akhir penerimaan unit.</p>
                        @error('booking_close_hour') <p class="text-xs text-red-700 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="setting_booking_max_per_slot" class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Maks. Kuota per Slot Jam (BR-02)</label>
                        <input id="setting_booking_max_per_slot" type="number" name="booking_max_per_slot" min="1" max="10"
                               value="{{ old('booking_max_per_slot', \App\Models\Setting::get('booking.max_per_slot', 3)) }}" required
                               class="w-full min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-300 text-sm font-mono tabular-nums focus:ring-2 focus:ring-blue-600 focus:outline-none">
                        <p class="text-xs text-slate-600 mt-1">Batas kapasitas stall bengkel per jam (Default 3).</p>
                        @error('booking_max_per_slot') <p class="text-xs text-red-700 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <!-- Tab 2: Kontak -->
            <div x-show="tab === 'contact'" class="space-y-6" x-cloak>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label for="setting_contact_whatsapp" class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Nomor WhatsApp Resmi Dealer</label>
                        <input id="setting_contact_whatsapp" type="text" name="contact_whatsapp"
                               value="{{ old('contact_whatsapp', \App\Models\Setting::get('contact.whatsapp', '6281234567890')) }}" required
                               class="w-full min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-300 text-sm font-mono focus:ring-2 focus:ring-blue-600 focus:outline-none">
                        @error('contact_whatsapp') <p class="text-xs text-red-700 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="setting_contact_email" class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Email Layanan Pelanggan</label>
                        <input id="setting_contact_email" type="email" name="contact_email"
                               value="{{ old('contact_email', \App\Models\Setting::get('contact.email', 'info@xyzmotors.id')) }}" required
                               class="w-full min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                        @error('contact_email') <p class="text-xs text-red-700 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label for="setting_contact_address" class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Alamat Showroom Utama</label>
                    <textarea id="setting_contact_address" name="contact_address" rows="2"
                              class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">{{ old('contact_address', \App\Models\Setting::get('contact.address', 'Jl. Sudirman No. 1, Jakarta Selatan')) }}</textarea>
                    @error('contact_address') <p class="text-xs text-red-700 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Tab 3: SEO -->
            <div x-show="tab === 'seo'" class="space-y-6" x-cloak>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label for="setting_seo_site_name" class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Nama Situs (Site Name)</label>
                        <input id="setting_seo_site_name" type="text" name="seo_site_name"
                               value="{{ old('seo_site_name', \App\Models\Setting::get('seo.site_name', 'Dealer Pro')) }}" required
                               class="w-full min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                        @error('seo_site_name') <p class="text-xs text-red-700 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="setting_seo_tagline" class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Tagline Brand</label>
                        <input id="setting_seo_tagline" type="text" name="seo_tagline"
                               value="{{ old('seo_tagline', \App\Models\Setting::get('seo.tagline', 'Drive Your Dream')) }}" required
                               class="w-full min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                        @error('seo_tagline') <p class="text-xs text-red-700 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="pt-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-t border-slate-100">
                <span class="text-xs text-slate-600 font-medium">Perubahan langsung ter-cache dan ter-invalidasi otomatis.</span>
                <button type="submit" class="inline-flex items-center justify-center min-h-[44px] px-6 py-2.5 rounded-xl font-bold text-sm bg-slate-900 text-white hover:bg-slate-800 active:scale-[0.98] shadow-sm transition">
                    Simpan Pengaturan
                </button>
            </div>
        </form>
    </div>
</x-dashboard-layout>
