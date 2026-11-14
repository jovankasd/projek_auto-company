<!DOCTYPE html>
<html lang="id" class="h-full bg-gray-50 text-slate-900 scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Hubungi Tim Konsultan Showroom &amp; Bengkel | Dealer Pro XYZ Motors</title>
    <meta name="description" content="Saluran kontak resmi Dealer Pro XYZ Motors. Hubungi kami untuk konsultasi unit kendaraan baru, ketersediaan suku cadang OEM, jadwal servis bengkel resmi, atau kunjungan langsung.">
    <link rel="canonical" href="{{ url()->current() }}">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="Hubungi Tim Konsultan Showroom &amp; Bengkel | Dealer Pro">
    <meta property="og:description" content="Saluran komunikasi langsung dengan konsultan penjualan dan tim teknis bengkel resmi Dealer Pro XYZ Motors.">
    <meta property="og:image" content="{{ asset('images/logo.jpg') }}">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="Hubungi Dealer Pro XYZ Motors">
    <meta name="twitter:description" content="Saluran komunikasi langsung dengan konsultan penjualan dan teknisi bengkel resmi Dealer Pro.">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full font-sans antialiased overflow-x-hidden selection:bg-black selection:text-white"
      x-data="contactPageApp()">

    <!-- Accessibility: Skip to Content -->
    <a href="#main-content"
       class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-70 focus:px-4 focus:py-2 focus:bg-black focus:text-white focus:rounded-lg shadow-lg">
        Lewati ke konten utama
    </a>

    <!-- Sticky Architectural Header -->
    <header class="fixed top-0 inset-x-0 w-full z-30 backdrop-blur-md bg-white/95 border-b border-slate-200 transition-colors duration-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 sm:h-20 flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-3 group focus-visible:ring-2 focus-visible:ring-black rounded-sm py-1">
                <span class="h-9 flex items-center justify-center overflow-hidden">
                    <img src="{{ asset('images/logo.jpg') }}" alt="Dealer Pro" class="h-full w-auto object-contain">
                </span>
                <div class="flex flex-col">
                    <span class="font-extrabold text-base sm:text-lg tracking-tight text-slate-900 uppercase leading-none">
                        Dealer Pro
                    </span>
                    <span class="font-mono text-[10px] tracking-widest text-slate-500 uppercase mt-0.5">
                        XYZ Motors Indonesia
                    </span>
                </div>
            </a>

            <!-- Desktop Nav -->
            <nav class="hidden lg:flex items-center gap-7 text-xs font-semibold uppercase tracking-wider text-slate-600" aria-label="Navigasi Publik">
                <a href="{{ route('home') }}" class="hover:text-slate-900 transition py-1">Beranda Showroom</a>
                <a href="{{ route('home') }}#katalog-mobil" class="hover:text-slate-900 transition py-1">Katalog Mobil</a>
                <a href="{{ route('home') }}#katalog-suku-cadang" class="hover:text-slate-900 transition py-1">Suku Cadang OEM</a>
                <a href="{{ route('home') }}#layanan-bengkel" class="hover:text-slate-900 transition py-1">Bengkel Resmi</a>
                <a href="{{ route('contact.index') }}" class="text-slate-900 hover:text-blue-600 transition py-1">Hubungi Kami</a>
            </nav>

            <div class="flex items-center gap-3">
                @auth
                    <a href="{{ route('dashboard') }}"
                       class="text-xs font-bold uppercase tracking-wider text-blue-600 hover:text-slate-900 transition px-2 py-2">
                        Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}"
                       class="text-xs font-bold uppercase tracking-wider text-slate-700 hover:text-slate-900 transition px-2 py-2">
                        Masuk
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <main id="main-content" class="pt-24 sm:pt-28 pb-20">
        <!-- Breadcrumb -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-8">
            <nav class="flex items-center gap-2 text-xs font-medium text-slate-500" aria-label="Breadcrumb">
                <a href="{{ route('home') }}" class="hover:text-slate-900 transition">Beranda</a>
                <span class="text-slate-300">/</span>
                <span class="font-bold text-slate-900" aria-current="page">Hubungi Kami</span>
            </nav>
        </div>

        <!-- Contact Section -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header Title -->
            <div class="border-b border-slate-300 pb-8 mb-10">
                <span class="font-mono text-xs font-bold uppercase tracking-widest text-blue-600">Layanan Pelanggan &amp; Konsultasi Prospek</span>
                <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900 mt-2">
                    Hubungi Tim Konsultan Dealer Pro
                </h1>
                <p class="text-sm sm:text-base text-slate-600 max-w-3xl mt-3 leading-relaxed">
                    Sampaikan pertanyaan seputar pemesanan unit kendaraan baru, cek stok suku cadang orisinal, atau kebutuhan perawatan berkala di bengkel kami. Tim sales dan customer support kami akan menanggapi pesan Anda dalam waktu operasional.
                </p>
            </div>

            <!-- Main Content Columns -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-start">
                <!-- Left: Official Contact Directory (5 Cols) -->
                <div class="lg:col-span-5 space-y-8">
                    <!-- Office & Workshop Location -->
                    <div class="bg-white border border-slate-300 p-6 sm:p-8 space-y-4">
                        <div class="flex items-center gap-3">
                            <span class="p-2.5 bg-slate-900 text-white">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </span>
                            <div>
                                <h2 class="text-base font-bold text-slate-900">Showroom &amp; Workshop Resmi</h2>
                                <p class="text-xs text-slate-500">Kantor Pusat XYZ Motors Indonesia</p>
                            </div>
                        </div>

                        <p class="text-sm text-slate-700 leading-relaxed font-sans">
                            Jl. Sudirman No. 88, Karet Semanggi, Setiabudi,<br>
                            Jakarta Selatan, DKI Jakarta 12190
                        </p>

                        <div class="pt-4 border-t border-slate-200 space-y-2 text-xs">
                            <div class="flex items-center justify-between">
                                <span class="font-semibold text-slate-500 uppercase tracking-wider">Senin - Jumat:</span>
                                <span class="font-mono font-bold text-slate-900">08:00 - 18:00 WIB</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="font-semibold text-slate-500 uppercase tracking-wider">Sabtu:</span>
                                <span class="font-mono font-bold text-slate-900">08:00 - 15:00 WIB</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="font-semibold text-slate-500 uppercase tracking-wider">Minggu / Libur:</span>
                                <span class="font-bold text-amber-700">Janji Temu Khusus</span>
                            </div>
                        </div>
                    </div>

                    <!-- Direct Communication Channels -->
                    <div class="bg-white border border-slate-300 p-6 sm:p-8 space-y-4">
                        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200 pb-2">
                            Saluran Komunikasi Langsung
                        </h2>

                        <div class="space-y-4 text-sm">
                            <div class="flex items-start gap-3">
                                <span class="text-blue-600 mt-0.5">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                </span>
                                <div>
                                    <span class="block text-xs font-semibold text-slate-500">Telepon Showroom:</span>
                                    <span class="font-mono font-bold text-slate-900 text-base">+62-21-555-0199</span>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <span class="text-emerald-600 mt-0.5">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.312.045-.694.077-2.027-.47-1.525-.626-2.525-2.183-2.603-2.288-.077-.104-.626-.833-.626-1.589 0-.756.398-1.127.54-1.28.143-.153.311-.191.415-.191.104 0 .208.002.299.006.096.004.225-.036.35.265.13.312.446 1.085.485 1.164.039.078.065.17.013.273-.052.104-.078.169-.156.26-.078.091-.163.203-.233.273-.078.077-.16.162-.069.317.091.156.403.666.866 1.077.595.528 1.097.691 1.253.769.156.078.247.065.338-.039.091-.104.39-.455.494-.611.104-.156.208-.13.35-.078.144.052.91.43 1.066.508.156.078.26.117.299.182.039.065.039.377-.105.782z"/></svg>
                                </span>
                                <div>
                                    <span class="block text-xs font-semibold text-slate-500">WhatsApp Sales &amp; Konsultasi:</span>
                                    <a href="https://wa.me/6281234567890" target="_blank" rel="noopener noreferrer" class="font-mono font-bold text-emerald-800 hover:underline text-base">
                                        +62-812-3456-7890
                                    </a>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <span class="text-slate-700 mt-0.5">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                </span>
                                <div>
                                    <span class="block text-xs font-semibold text-slate-500">Email Resmi:</span>
                                    <span class="font-mono font-bold text-slate-900 text-sm">sales@dealerpro.xyzmotors.co.id</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: Verified Contact Inquiry Form (7 Cols) -->
                <div class="lg:col-span-7 bg-white border border-slate-300 p-6 sm:p-10 shadow-sm">
                    <div class="border-b border-slate-200 pb-4 mb-6">
                        <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Formulir Pesan Konsultasi</h2>
                        <p class="text-xs text-slate-500 mt-1">
                            Isi detail kebutuhan Anda di bawah ini. Lead akan tercatat langsung ke sistem CRM Dealer Pro.
                        </p>
                    </div>

                    <!-- Flash Success Alert (Non-JS / standard POST support) -->
                    @if(session('contact_success'))
                        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-300 text-emerald-900 text-sm flex items-start gap-3" role="alert">
                            <svg class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <div>
                                <p class="font-bold">Pesan Berhasil Terkirim</p>
                                <p class="text-xs text-emerald-800 mt-0.5">{{ session('contact_success') }}</p>
                            </div>
                        </div>
                    @endif

                    <!-- Validation Errors -->
                    @if($errors->any())
                        <div class="mb-6 p-4 bg-red-50 border border-red-300 text-red-900 text-sm" role="alert">
                            <p class="font-bold text-xs uppercase tracking-wider mb-1">Periksa kembali data yang dimasukkan:</p>
                            <ul class="list-disc list-inside text-xs space-y-1">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('contact.store') }}"
                          method="POST"
                          @submit.prevent="submitContact"
                          class="space-y-5">
                        @csrf

                        <!-- Honeypot Field (Anti-bot) -->
                        <div class="hidden" aria-hidden="true" style="display:none !important;">
                            <label for="contact_website_url">Website URL (Jangan diisi)</label>
                            <input type="text"
                                   id="contact_website_url"
                                   name="website_url"
                                   value=""
                                   tabindex="-1"
                                   autocomplete="off">
                        </div>

                        <!-- Name -->
                        <div>
                            <label for="contact_name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                                Nama Lengkap <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                   id="contact_name"
                                   name="name"
                                   x-model="formData.name"
                                   value="{{ old('name', auth()->user()?->name ?? '') }}"
                                   placeholder="Contoh: Budi Santoso"
                                   required
                                   maxlength="100"
                                   class="w-full text-sm border-slate-300 focus:border-blue-600 focus:ring-blue-600 rounded-none bg-white">
                        </div>

                        <!-- Email & Phone Grid -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <label for="contact_email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                                    Alamat Email <span class="text-red-500">*</span>
                                </label>
                                <input type="email"
                                       id="contact_email"
                                       name="email"
                                       x-model="formData.email"
                                       value="{{ old('email', auth()->user()?->email ?? '') }}"
                                       placeholder="nama@domain.com"
                                       required
                                       maxlength="150"
                                       class="w-full text-sm border-slate-300 focus:border-blue-600 focus:ring-blue-600 rounded-none bg-white">
                            </div>

                            <div>
                                <label for="contact_phone" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                                    Nomor Telepon / WhatsApp <span class="text-red-500">*</span>
                                </label>
                                <input type="tel"
                                       id="contact_phone"
                                       name="phone"
                                       x-model="formData.phone"
                                       value="{{ old('phone', auth()->user()?->phone_number ?? '') }}"
                                       placeholder="081234567890"
                                       required
                                       minlength="9"
                                       maxlength="30"
                                       class="w-full text-sm border-slate-300 focus:border-blue-600 focus:ring-blue-600 rounded-none bg-white">
                            </div>
                        </div>

                        <!-- Subject -->
                        <div>
                            <label for="contact_subject" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                                Subjek / Topik Konsultasi <span class="text-red-500">*</span>
                            </label>
                            <select id="contact_subject"
                                    name="subject"
                                    x-model="formData.subject"
                                    required
                                    class="w-full text-sm border-slate-300 focus:border-blue-600 focus:ring-blue-600 rounded-none bg-white">
                                <option value="">-- Pilih Topik Pembahasan --</option>
                                <option value="Pembelian &amp; Konsultasi Unit Mobil Baru" {{ old('subject') === 'Pembelian & Konsultasi Unit Mobil Baru' ? 'selected' : '' }}>Pembelian &amp; Konsultasi Unit Mobil Baru</option>
                                <option value="Informasi Suku Cadang &amp; Komponen OEM" {{ old('subject') === 'Informasi Suku Cadang & Komponen OEM' ? 'selected' : '' }}>Informasi Suku Cadang &amp; Komponen OEM</option>
                                <option value="Reservasi &amp; Penjadwalan Servis Bengkel" {{ old('subject') === 'Reservasi & Penjadwalan Servis Bengkel' ? 'selected' : '' }}>Reservasi &amp; Penjadwalan Servis Bengkel</option>
                                <option value="Klaim Garansi &amp; Layanan Purna Jual" {{ old('subject') === 'Klaim Garansi & Layanan Purna Jual' ? 'selected' : '' }}>Klaim Garansi &amp; Layanan Purna Jual</option>
                                <option value="Kerjasama Bisnis &amp; Pertanyaan Umum" {{ old('subject') === 'Kerjasama Bisnis & Pertanyaan Umum' ? 'selected' : '' }}>Kerjasama Bisnis &amp; Pertanyaan Umum</option>
                            </select>
                        </div>

                        <!-- Message -->
                        <div>
                            <label for="contact_message" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                                Pesan / Kebutuhan Lengkap <span class="text-red-500">*</span>
                            </label>
                            <textarea id="contact_message"
                                      name="message"
                                      x-model="formData.message"
                                      rows="5"
                                      placeholder="Tuliskan pertanyaan atau kebutuhan spesifik Anda secara rinci (minimal 10 karakter)..."
                                      required
                                      minlength="10"
                                      maxlength="2000"
                                      class="w-full text-sm border-slate-300 focus:border-blue-600 focus:ring-blue-600 rounded-none bg-white">{{ old('message') }}</textarea>
                            <span class="block text-[11px] text-slate-500 mt-1">Minimal 10 karakter, maksimal 2.000 karakter.</span>
                        </div>

                        <!-- Submit Button -->
                        <div class="pt-2">
                            <button type="submit"
                                    :disabled="submitting"
                                    class="w-full py-4 px-6 bg-slate-900 hover:bg-blue-600 text-white text-xs font-bold uppercase tracking-widest transition text-center disabled:opacity-50 shadow-md">
                                <span x-show="!submitting">Kirimkan Pesan Konsultasi</span>
                                <span x-show="submitting" x-cloak>Mengirimkan Pesan...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="bg-slate-50 border-t border-slate-300 text-slate-600 py-14 sm:py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-10 pb-12 border-b border-slate-300">
                <div class="md:col-span-5 space-y-3">
                    <div class="flex items-center gap-3">
                        <img src="{{ asset('images/logo.jpg') }}" alt="Dealer Pro" class="h-8 w-auto object-contain">
                        <span class="font-extrabold text-lg tracking-tight text-slate-900 uppercase">Dealer Pro</span>
                    </div>
                    <p class="text-xs sm:text-sm text-slate-600 max-w-sm leading-relaxed">
                        Showroom kendaraan premium, layanan bengkel resmi dengan pelacakan tiket pengerjaan secara langsung, serta pusat penyedia suku cadang orisinal.
                    </p>
                    <dl class="text-xs text-slate-600 space-y-1 pt-2 font-mono">
                        <div><dt class="inline font-sans font-bold text-slate-900">Alamat:</dt> <dd class="inline font-sans">Jl. Sudirman No. 88, Jakarta Selatan 12190</dd></div>
                        <div><dt class="inline font-sans font-bold text-slate-900">Telepon:</dt> <dd class="inline tabular-nums">+62-21-555-0199</dd></div>
                        <div><dt class="inline font-sans font-bold text-slate-900">Operasional:</dt> <dd class="inline tabular-nums">Senin - Sabtu / 08:00 - 18:00 WIB</dd></div>
                    </dl>
                </div>

                <div class="md:col-span-4 space-y-3">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900">Indeks Katalog &amp; Layanan</h3>
                    <ul class="space-y-2 text-xs sm:text-sm">
                        <li><a href="{{ route('home') }}" class="hover:text-slate-900 transition">01 / Showroom Interaktif 2.5D</a></li>
                        <li><a href="{{ route('home') }}#katalog-mobil" class="hover:text-slate-900 transition">02 / Katalog Mobil Lengkap</a></li>
                        <li><a href="{{ route('home') }}#katalog-suku-cadang" class="hover:text-slate-900 transition">03 / Suku Cadang OEM</a></li>
                        <li><a href="{{ route('home') }}#layanan-bengkel" class="hover:text-slate-900 transition">04 / Bengkel Resmi &amp; Alur Tiket</a></li>
                        <li><a href="{{ route('contact.index') }}" class="hover:text-slate-900 transition">05 / Hubungi Konsultan Dealer</a></li>
                    </ul>
                </div>

                <div class="md:col-span-3 space-y-3">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900">Layanan Cepat</h3>
                    <div class="flex flex-col gap-2.5">
                        <a href="{{ route('home') }}#katalog-mobil"
                           class="w-full text-center px-4 py-3 bg-slate-900 text-white text-xs font-bold uppercase tracking-wider hover:bg-blue-600 transition">
                            Lihat Unit Showroom
                        </a>
                        <a href="{{ route('home') }}#layanan-bengkel"
                           class="w-full text-center px-4 py-3 border border-slate-900 bg-white text-slate-900 text-xs font-bold uppercase tracking-wider hover:bg-slate-900 hover:text-white transition">
                            Jadwalkan Servis Bengkel
                        </a>
                    </div>
                </div>
            </div>

            <div class="pt-6 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500">
                <p>&copy; {{ date('Y') }} Dealer Pro (XYZ Motors Indonesia). Seluruh hak cipta dilindungi.</p>
                <p class="font-mono text-[11px] uppercase tracking-wider">Showroom Sales / Workshop Service / OEM Spare Parts</p>
            </div>
        </div>
    </footer>

    <!-- Toast Container -->
    <div class="fixed top-20 right-4 sm:right-6 z-40 space-y-3 max-w-sm w-full pointer-events-none"
         role="status"
         aria-live="polite">
        <template x-for="toast in toasts" :key="toast.id">
            <div x-show="toast.visible"
                 class="pointer-events-auto p-4 rounded-xl shadow-xl backdrop-blur-md border text-sm flex items-start gap-3"
                 :class="{
                     'bg-green-50/95 border-green-200 text-green-800': toast.type === 'success',
                     'bg-red-50/95 border-red-200 text-red-800': toast.type === 'error'
                 }">
                <div class="flex-1">
                    <p class="font-medium" x-text="toast.message"></p>
                </div>
                <button type="button" @click="dismissToast(toast.id)" class="text-slate-400 hover:text-slate-600">
                    <span class="sr-only">Tutup</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </template>
    </div>

    <script>
        function contactPageApp() {
            return {
                submitting: false,
                toasts: [],
                formData: {
                    name: @js(old('name', auth()->user()?->name ?? '')),
                    email: @js(old('email', auth()->user()?->email ?? '')),
                    phone: @js(old('phone', auth()->user()?->phone_number ?? '')),
                    subject: @js(old('subject', '')),
                    message: @js(old('message', ''))
                },
                addToast(message, type = 'info') {
                    const id = Date.now();
                    this.toasts.push({ id, message, type, visible: true });
                    setTimeout(() => { this.dismissToast(id); }, 5000);
                },
                dismissToast(id) {
                    const idx = this.toasts.findIndex(t => t.id === id);
                    if (idx !== -1) {
                        this.toasts[idx].visible = false;
                        setTimeout(() => {
                            this.toasts = this.toasts.filter(t => t.id !== id);
                        }, 300);
                    }
                },
                async submitContact(e) {
                    this.submitting = true;
                    const form = e.target;
                    const formData = new FormData(form);

                    try {
                        const res = await fetch('{{ route('contact.store') }}', {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: formData
                        });
                        const data = await res.json();
                        if (res.ok) {
                            this.addToast(data.message || 'Pesan Anda berhasil dikirim!', 'success');
                            this.formData.subject = '';
                            this.formData.message = '';
                            form.reset();
                        } else {
                            const errMsg = data.message || Object.values(data.errors || {}).flat()[0] || 'Gagal mengirimkan pesan.';
                            this.addToast(errMsg, 'error');
                        }
                    } catch (err) {
                        form.submit();
                    } finally {
                        this.submitting = false;
                    }
                }
            };
        }
    </script>
</body>
</html>
