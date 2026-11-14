<!DOCTYPE html>
<html lang="id" class="h-full bg-gray-50 text-slate-900 scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $sparePart->meta_title ?: "{$sparePart->name} ({$sparePart->part_number}) - Suku Cadang OEM | Dealer Pro" }}</title>
    <meta name="description" content="{{ $sparePart->meta_description ?: ($sparePart->description ?: "Suku cadang orisinal {$sparePart->name} dengan nomor part {$sparePart->part_number}. Cek kompatibilitas dan jadwalkan pemasangan di bengkel resmi Dealer Pro.") }}">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="product">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="{{ $sparePart->meta_title ?: "{$sparePart->name} - Dealer Pro" }}">
    <meta property="og:description" content="{{ $sparePart->meta_description ?: ($sparePart->description ?: "Suku cadang OEM resmi {$sparePart->name}.") }}">
    <meta property="og:image" content="{{ $sparePart->imageUrl }}">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $sparePart->name }} ({{ $sparePart->part_number }})">
    <meta name="twitter:description" content="{{ $sparePart->meta_description ?: ($sparePart->description ?: "Suku cadang OEM {$sparePart->name}.") }}">
    <meta name="twitter:image" content="{{ $sparePart->imageUrl }}">

    <!-- Schema.org JSON-LD (Product) -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Product",
        "name": "{{ $sparePart->name }}",
        "image": "{{ $sparePart->imageUrl }}",
        "description": "{{ $sparePart->description ?: 'Suku cadang resmi OEM bergaransi pabrik.' }}",
        "sku": "{{ $sparePart->part_number }}",
        "mpn": "{{ $sparePart->part_number }}",
        "brand": {
            "@type": "Brand",
            "name": "{{ $sparePart->brand }}"
        },
        "offers": {
            "@type": "Offer",
            "priceCurrency": "IDR",
            "price": "{{ (int) $sparePart->price }}",
            "availability": "{{ $sparePart->stock_quantity > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock' }}",
            "itemCondition": "https://schema.org/NewCondition",
            "url": "{{ url()->current() }}"
        }
    }
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full font-sans antialiased overflow-x-hidden selection:bg-black selection:text-white"
      x-data="partDetailApp()"
      :class="{ 'overflow-hidden': serviceModalOpen }">

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
                <a href="{{ route('home') }}#katalog-suku-cadang" class="text-slate-900 hover:text-blue-600 transition py-1">Suku Cadang OEM</a>
                <a href="{{ route('home') }}#layanan-bengkel" class="hover:text-slate-900 transition py-1">Bengkel Resmi</a>
                <a href="{{ route('contact.index') }}" class="hover:text-slate-900 transition py-1">Hubungi Kami</a>
            </nav>

            <div class="flex items-center gap-3">
                <button type="button"
                        @click="openServiceModal()"
                        class="hidden sm:inline-flex items-center px-4 py-2 bg-slate-900 hover:bg-blue-600 text-white text-xs font-bold uppercase tracking-wider transition">
                    Booking Pasang
                </button>

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
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-6">
            <nav class="flex items-center gap-2 text-xs font-medium text-slate-500" aria-label="Breadcrumb">
                <a href="{{ route('home') }}" class="hover:text-slate-900 transition">Beranda</a>
                <span class="text-slate-300">/</span>
                <a href="{{ route('home') }}#katalog-suku-cadang" class="hover:text-slate-900 transition">Suku Cadang OEM</a>
                <span class="text-slate-300">/</span>
                <span class="font-bold text-slate-900" aria-current="page">{{ $sparePart->name }}</span>
            </nav>
        </div>

        <!-- Studio Product Detail Stage -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white border border-slate-300 shadow-sm overflow-hidden">
                <div class="grid grid-cols-1 lg:grid-cols-12 divide-y lg:divide-y-0 lg:divide-x divide-slate-300">
                    <!-- Left: Studio Image (5 Cols) -->
                    <div class="lg:col-span-5 p-8 sm:p-12 flex flex-col justify-between bg-slate-50/70">
                        <div class="flex items-center justify-between gap-3">
                            <span class="font-mono text-xs font-bold uppercase tracking-widest text-slate-500">
                                SKU: {{ $sparePart->part_number }}
                            </span>
                            <span class="font-mono text-[11px] font-bold px-2.5 py-0.5 border tabular-nums {{ $sparePart->isLowStock() ? 'bg-amber-50 text-amber-800 border-amber-300' : 'bg-emerald-50 text-emerald-800 border-emerald-300' }}">
                                STOK: {{ $sparePart->stock_quantity }} UNIT
                            </span>
                        </div>

                        <!-- Product Image -->
                        <div class="py-12 flex items-center justify-center min-h-[280px]">
                            <img src="{{ $sparePart->imageUrl }}"
                                 alt="{{ $sparePart->name }}"
                                 width="400"
                                 height="400"
                                 class="max-h-64 sm:max-h-72 w-auto object-contain drop-shadow-md" />
                        </div>

                        <!-- Brand Info -->
                        <div class="pt-4 border-t border-slate-200 flex items-center justify-between text-xs">
                            <span class="text-slate-500">Pabrikan / Brand:</span>
                            <span class="font-bold text-slate-900">{{ $sparePart->brand }}</span>
                        </div>
                    </div>

                    <!-- Right: Info, Price, Compatibility & CTA (7 Cols) -->
                    <div class="lg:col-span-7 p-6 sm:p-10 flex flex-col justify-between space-y-8 bg-white">
                        <div class="space-y-4">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-mono text-xs font-bold uppercase tracking-wider text-blue-600">
                                    {{ $sparePart->brand }}
                                </span>
                                <span class="text-slate-300">•</span>
                                <span class="font-mono text-[11px] font-bold uppercase px-2 py-0.5 border border-slate-300 bg-slate-50 text-slate-700">
                                    Kategori: {{ $sparePart->category->label() }}
                                </span>
                            </div>

                            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                                {{ $sparePart->name }}
                            </h1>

                            <div class="p-5 bg-slate-50 border border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                <div>
                                    <span class="block text-xs font-bold uppercase tracking-wider text-slate-500">
                                        Harga Satuan Resmi OEM
                                    </span>
                                    <div class="font-mono text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1 tabular-nums">
                                        {{ $sparePart->price_formatted }}
                                    </div>
                                </div>
                                <span class="text-xs text-slate-500">
                                    *Belum termasuk jasa pasang mekanik bengkel
                                </span>
                            </div>

                            @if($sparePart->description)
                                <div class="pt-2">
                                    <h2 class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Deskripsi &amp; Spesifikasi Komponen</h2>
                                    <p class="text-sm text-slate-600 leading-relaxed">
                                        {{ $sparePart->description }}
                                    </p>
                                </div>
                            @endif

                            <!-- Compatibility Box -->
                            <div class="pt-2 border-t border-slate-200 space-y-2">
                                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">Kompatibilitas Kendaraan</h3>
                                <p class="text-xs font-mono font-bold text-slate-900 bg-slate-100 p-3 border border-slate-200">
                                    {{ !empty($sparePart->car_brand_compatibility) ? implode(', ', $sparePart->car_brand_compatibility) : 'Universal untuk seluruh lini kendaraan kompatibel' }}
                                </p>
                            </div>
                        </div>

                        <!-- CTA Actions Suite -->
                        <div class="space-y-3 pt-6 border-t border-slate-200">
                            <button type="button"
                                    @click="openServiceModal()"
                                    class="w-full py-4 px-6 bg-slate-900 hover:bg-blue-600 text-white text-xs font-bold uppercase tracking-widest transition text-center shadow-md">
                                Pesan &amp; Pasang di Bengkel Resmi
                            </button>

                            <a href="https://wa.me/6281234567890?text={{ urlencode('Halo Dealer Pro, saya ingin menanyakan ketersediaan suku cadang: ' . $sparePart->name . ' (SKU: ' . $sparePart->part_number . ')') }}"
                               target="_blank"
                               rel="noopener noreferrer"
                               class="w-full inline-flex items-center justify-center gap-2 py-3 px-4 border border-emerald-600 text-emerald-800 hover:bg-emerald-50 text-xs font-bold uppercase tracking-wider transition">
                                <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.312.045-.694.077-2.027-.47-1.525-.626-2.525-2.183-2.603-2.288-.077-.104-.626-.833-.626-1.589 0-.756.398-1.127.54-1.28.143-.153.311-.191.415-.191.104 0 .208.002.299.006.096.004.225-.036.35.265.13.312.446 1.085.485 1.164.039.078.065.17.013.273-.052.104-.078.169-.156.26-.078.091-.163.203-.233.273-.078.077-.16.162-.069.317.091.156.403.666.866 1.077.595.528 1.097.691 1.253.769.156.078.247.065.338-.039.091-.104.39-.455.494-.611.104-.156.208-.13.35-.078.144.052.91.43 1.066.508.156.078.26.117.299.182.039.065.039.377-.105.782z"/>
                                </svg>
                                Tanya Ketersediaan via WhatsApp
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Related / Similar Parts -->
        @if($relatedParts->isNotEmpty())
            <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-16 pt-12 border-t border-slate-300">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <span class="font-mono text-xs font-bold uppercase tracking-widest text-slate-500">Katalog Komponen Sejenis</span>
                        <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight mt-1">
                            Suku Cadang {{ $sparePart->category->label() }} Lainnya
                        </h2>
                    </div>
                    <a href="{{ route('home') }}#katalog-suku-cadang" class="text-xs font-bold uppercase tracking-wider text-blue-600 hover:text-slate-900 transition">
                        Semua Suku Cadang &rarr;
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach($relatedParts as $relPart)
                        <div class="bg-white border border-slate-300 p-6 flex flex-col justify-between hover:shadow-md transition">
                            <div class="space-y-4">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="font-mono font-bold text-slate-500">SKU: {{ $relPart->part_number }}</span>
                                    <span class="font-mono text-[11px] font-bold px-2 py-0.5 border {{ $relPart->isLowStock() ? 'bg-amber-50 text-amber-800 border-amber-300' : 'bg-emerald-50 text-emerald-800 border-emerald-300' }}">
                                        {{ $relPart->stock_quantity }} UNIT
                                    </span>
                                </div>
                                <div class="py-4 flex items-center justify-center h-28">
                                    <img src="{{ $relPart->imageUrl }}"
                                         alt="{{ $relPart->name }}"
                                         loading="lazy"
                                         width="200"
                                         height="200"
                                         class="max-h-24 w-auto object-contain drop-shadow" />
                                </div>
                                <div>
                                    <div class="font-mono text-[11px] font-bold uppercase text-blue-600">{{ $relPart->brand }}</div>
                                    <h3 class="text-base font-bold text-slate-900">{{ $relPart->name }}</h3>
                                    <p class="font-mono text-base font-extrabold text-slate-900 mt-2">{{ $relPart->price_formatted }}</p>
                                </div>
                            </div>
                            <div class="pt-4 mt-6 border-t border-slate-200">
                                <a href="{{ route('spare-parts.public.show', $relPart->slug) }}"
                                   class="block w-full py-2.5 text-center bg-slate-900 hover:bg-blue-600 text-white text-xs font-bold uppercase tracking-wider transition">
                                    Lihat Detail Part
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
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
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900">Navigasi Showroom</h3>
                    <ul class="space-y-2 text-xs sm:text-sm">
                        <li><a href="{{ route('home') }}" class="hover:text-slate-900 transition">01 / Showroom Interaktif</a></li>
                        <li><a href="{{ route('home') }}#katalog-mobil" class="hover:text-slate-900 transition">02 / Katalog Mobil Lengkap</a></li>
                        <li><a href="{{ route('home') }}#katalog-suku-cadang" class="hover:text-slate-900 transition">03 / Suku Cadang OEM</a></li>
                        <li><a href="{{ route('home') }}#layanan-bengkel" class="hover:text-slate-900 transition">04 / Servis Bengkel Resmi</a></li>
                        <li><a href="{{ route('contact.index') }}" class="hover:text-slate-900 transition">05 / Hubungi Konsultan Dealer</a></li>
                    </ul>
                </div>

                <div class="md:col-span-3 space-y-3">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900">Reservasi Cepat</h3>
                    <div class="flex flex-col gap-2.5">
                        <button type="button"
                                @click="openServiceModal()"
                                class="w-full text-center px-4 py-3 bg-slate-900 text-white text-xs font-bold uppercase tracking-wider hover:bg-blue-600 transition">
                            Booking Servis &amp; Pasang
                        </button>
                    </div>
                </div>
            </div>

            <div class="pt-6 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500">
                <p>&copy; {{ date('Y') }} Dealer Pro (XYZ Motors Indonesia). Seluruh hak cipta dilindungi.</p>
                <p class="font-mono text-[11px] uppercase tracking-wider">Showroom Sales / Workshop Service / OEM Spare Parts</p>
            </div>
        </div>
    </footer>

    <!-- Service Booking Modal -->
    <div x-show="serviceModalOpen"
         x-cloak
         role="dialog"
         aria-modal="true"
         aria-labelledby="service-modal-title"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
         @keydown.escape.window="closeModals()">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" @click="closeModals()"></div>

        <div class="relative w-full max-w-lg rounded-2xl bg-white border border-slate-300 shadow-2xl p-6 sm:p-8 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between pb-4 border-b border-slate-200">
                <div>
                    <h3 id="service-modal-title" class="text-lg font-extrabold text-slate-900">Pesan &amp; Pasang Suku Cadang</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Komponen: <span class="font-bold text-slate-900">{{ $sparePart->name }}</span> ({{ $sparePart->part_number }})</p>
                </div>
                <button type="button" @click="closeModals()" class="text-slate-400 hover:text-slate-700 p-1">
                    <span class="sr-only">Tutup</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="{{ route('service-booking.store') }}"
                  method="POST"
                  class="mt-6 space-y-4">
                @csrf
                <div>
                    <label for="srv_vehicle_model" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                        Model Kendaraan Anda <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           id="srv_vehicle_model"
                           name="vehicle_model"
                           value="{{ !empty($sparePart->car_brand_compatibility) ? $sparePart->car_brand_compatibility[0] : '' }}"
                           required
                           class="w-full text-sm border-slate-300 rounded-lg focus:border-blue-600 focus:ring-blue-600">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="srv_plate" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                            Nomor Plat Polisi <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               id="srv_plate"
                               name="vehicle_plate_number"
                               placeholder="B 1234 XYZ"
                               required
                               class="w-full text-sm border-slate-300 rounded-lg focus:border-blue-600 focus:ring-blue-600 uppercase">
                    </div>
                    <div>
                        <label for="srv_year" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                            Tahun Kendaraan <span class="text-red-500">*</span>
                        </label>
                        <input type="number"
                               id="srv_year"
                               name="vehicle_year"
                               value="{{ date('Y') }}"
                               min="1990"
                               max="{{ date('Y') + 1 }}"
                               required
                               class="w-full text-sm border-slate-300 rounded-lg focus:border-blue-600 focus:ring-blue-600">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="srv_date" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                            Tanggal Pemasangan <span class="text-red-500">*</span>
                        </label>
                        <input type="date"
                               id="srv_date"
                               name="service_date"
                               min="{{ date('Y-m-d') }}"
                               required
                               class="w-full text-sm border-slate-300 rounded-lg focus:border-blue-600 focus:ring-blue-600">
                    </div>
                    <div>
                        <label for="srv_slot" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                            Slot Jam Kedatangan <span class="text-red-500">*</span>
                        </label>
                        <select id="srv_slot"
                                name="slot_hour"
                                required
                                class="w-full text-sm border-slate-300 rounded-lg focus:border-blue-600 focus:ring-blue-600">
                            @for($h = 8; $h <= 17; $h++)
                                <option value="{{ $h }}">{{ sprintf('%02d:00 WIB', $h) }}</option>
                            @endfor
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="srv_odometer" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                            Odometer (KM) <span class="text-red-500">*</span>
                        </label>
                        <input type="number"
                               id="srv_odometer"
                               name="odometer_km"
                               value="10000"
                               min="0"
                               max="999999"
                               required
                               class="w-full text-sm border-slate-300 rounded-lg focus:border-blue-600 focus:ring-blue-600">
                    </div>
                    <div>
                        <label for="srv_type" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                            Kategori Layanan <span class="text-red-500">*</span>
                        </label>
                        <select id="srv_type"
                                name="service_type"
                                required
                                class="w-full text-sm border-slate-300 rounded-lg focus:border-blue-600 focus:ring-blue-600">
                            <option value="warranty">Pemasangan Komponen &amp; Garansi</option>
                            <option value="major">Servis Besar &amp; Penggantian</option>
                            <option value="regular">Servis Berkala</option>
                            <option value="inspection">Inspeksi &amp; Diagnosa</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="srv_complaint" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                        Catatan Permintaan Pemasangan <span class="text-red-500">*</span>
                    </label>
                    <textarea id="srv_complaint"
                              name="complaint_notes"
                              rows="3"
                              required
                              class="w-full text-sm border-slate-300 rounded-lg focus:border-blue-600 focus:ring-blue-600">Permintaan pasang part: {{ $sparePart->name }} (SKU: {{ $sparePart->part_number }})</textarea>
                </div>

                <div class="pt-4">
                    <button type="submit"
                            class="w-full py-3.5 px-4 bg-slate-900 hover:bg-blue-600 text-white text-xs font-bold uppercase tracking-wider transition text-center">
                        Konfirmasi Booking Pemasangan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function partDetailApp(config) {
            return {
                serviceModalOpen: false,
                openServiceModal() {
                    this.serviceModalOpen = true;
                },
                closeModals() {
                    this.serviceModalOpen = false;
                }
            };
        }
    </script>
</body>
</html>
