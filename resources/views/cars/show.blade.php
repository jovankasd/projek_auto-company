<!DOCTYPE html>
<html lang="id" class="h-full bg-gray-50 text-slate-900 scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $car->meta_title ?: "{$car->brand} {$car->model_name} ({$car->year}) - Spesifikasi & Harga Resmi | Dealer Pro" }}</title>
    <meta name="description" content="{{ $car->meta_description ?: ($car->description ?: "Spesifikasi resmi {$car->brand} {$car->model_name} tahun {$car->year}. Cek harga OTR, performa mesin, dan jadwalkan test drive di Dealer Pro XYZ Motors.") }}">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="product">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="{{ $car->meta_title ?: "{$car->brand} {$car->model_name} ({$car->year}) - Dealer Pro" }}">
    <meta property="og:description" content="{{ $car->meta_description ?: ($car->description ?: "Spesifikasi lengkap dan harga resmi {$car->brand} {$car->model_name}.") }}">
    <meta property="og:image" content="{{ $car->hero_image_url }}">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $car->brand }} {{ $car->model_name }} ({{ $car->year }})">
    <meta name="twitter:description" content="{{ $car->meta_description ?: ($car->description ?: "Spesifikasi resmi {$car->brand} {$car->model_name}.") }}">
    <meta name="twitter:image" content="{{ $car->hero_image_url }}">

    <!-- Schema.org JSON-LD (Product + Car) -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": ["Product", "Car"],
        "name": "{{ $car->brand }} {{ $car->model_name }}",
        "brand": {
            "@type": "Brand",
            "name": "{{ $car->brand }}"
        },
        "model": "{{ $car->model_name }}",
        "modelDate": "{{ $car->year }}",
        "description": "{{ $car->description ?: 'Unit kendaraan premium resmi Dealer Pro XYZ Motors.' }}",
        "image": "{{ $car->hero_image_url }}",
        "offers": {
            "@type": "Offer",
            "priceCurrency": "IDR",
            "price": "{{ (int) $car->price }}",
            "availability": "https://schema.org/InStock",
            "itemCondition": "https://schema.org/NewCondition",
            "url": "{{ url()->current() }}"
        }
    }
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full font-sans antialiased overflow-x-hidden selection:bg-black selection:text-white"
      x-data="carDetailApp({
          carId: {{ $car->id }},
          carName: @js($car->brand . ' ' . $car->model_name),
          pendingTestDrive: {{ json_encode($pendingTestDrive ?? null) }},
          pendingServiceBooking: {{ json_encode($pendingServiceBooking ?? null) }}
      })"
      :class="{ 'overflow-hidden': testDriveModalOpen || serviceModalOpen }">

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
                <a href="{{ route('home') }}#katalog-mobil" class="text-slate-900 hover:text-blue-600 transition py-1">Katalog Mobil</a>
                <a href="{{ route('home') }}#katalog-suku-cadang" class="hover:text-slate-900 transition py-1">Suku Cadang OEM</a>
                <a href="{{ route('home') }}#layanan-bengkel" class="hover:text-slate-900 transition py-1">Bengkel Resmi</a>
                <a href="{{ route('contact.index') }}" class="hover:text-slate-900 transition py-1">Hubungi Kami</a>
            </nav>

            <div class="flex items-center gap-3">
                <button type="button"
                        @click="openTestDriveModal()"
                        class="hidden sm:inline-flex items-center px-4 py-2 bg-slate-900 hover:bg-blue-600 text-white text-xs font-bold uppercase tracking-wider transition">
                    Test Drive
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
        <!-- Breadcrumb & Back Link -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-6">
            <nav class="flex items-center gap-2 text-xs font-medium text-slate-500" aria-label="Breadcrumb">
                <a href="{{ route('home') }}" class="hover:text-slate-900 transition">Beranda</a>
                <span class="text-slate-300">/</span>
                <a href="{{ route('home') }}#katalog-mobil" class="hover:text-slate-900 transition">Katalog Unit</a>
                <span class="text-slate-300">/</span>
                <span class="font-bold text-slate-900" aria-current="page">{{ $car->brand }} {{ $car->model_name }}</span>
            </nav>
        </div>

        <!-- Studio Showcase Header Section -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="relative bg-white border border-slate-300 overflow-hidden shadow-sm">
                <!-- Large Subtle Brand Backdrop -->
                <div class="absolute -top-10 -right-10 select-none pointer-events-none aria-hidden text-[9rem] sm:text-[14rem] font-black text-slate-100 tracking-tighter leading-none z-0">
                    {{ strtoupper($car->brand) }}
                </div>

                <div class="relative z-10 grid grid-cols-1 lg:grid-cols-12 divide-y lg:divide-y-0 lg:divide-x divide-slate-300">
                    <!-- Left (7 Cols): Studio Image Stage -->
                    <div class="lg:col-span-7 p-6 sm:p-12 flex flex-col justify-between bg-gradient-to-b from-slate-50/70 to-white">
                        <div class="flex items-center justify-between gap-3">
                            <span class="font-mono text-xs font-bold uppercase tracking-widest text-blue-600">
                                {{ $car->brand }} RESMI / TAHUN {{ $car->year }}
                            </span>
                            <span class="font-mono text-xs font-bold uppercase tracking-wider px-3 py-1 border border-slate-300 bg-white text-slate-800">
                                KATEGORI: {{ $car->category->label() }}
                            </span>
                        </div>

                        <!-- Vehicle Hero Profile Image -->
                        <div class="py-10 sm:py-16 flex items-center justify-center">
                            <img src="{{ $car->hero_image_url }}"
                                 alt="{{ $car->hero_image_alt ?: $car->brand . ' ' . $car->model_name }}"
                                 width="800"
                                 height="450"
                                 class="max-h-64 sm:max-h-96 w-auto object-contain drop-shadow-2xl" />
                        </div>

                        <!-- Exterior Swatch & Availability Badge -->
                        <div class="flex flex-wrap items-center justify-between gap-4 pt-4 border-t border-slate-200">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Warna:</span>
                                @if($car->color_name)
                                    <span class="inline-flex items-center gap-2 font-bold text-xs text-slate-800 bg-slate-100 px-3 py-1 border border-slate-200">
                                        <span class="w-3.5 h-3.5 border border-slate-400" style="background-color: {{ $car->color_hex ?: '#0f172a' }}"></span>
                                        {{ $car->color_name }}
                                    </span>
                                @else
                                    <span class="font-mono text-xs font-bold text-slate-700">Standar Pabrik</span>
                                @endif
                            </div>

                            <span class="inline-flex items-center gap-1.5 font-mono text-xs font-bold px-3 py-1 bg-emerald-50 text-emerald-800 border border-emerald-300">
                                <svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                Tersedia untuk Test Drive &amp; Pemesanan
                            </span>
                        </div>
                    </div>

                    <!-- Right (5 Cols): Identity, Pricing & Direct Action Suite -->
                    <div class="lg:col-span-5 p-6 sm:p-10 flex flex-col justify-between space-y-8 bg-white">
                        <div class="space-y-4">
                            <div>
                                <span class="font-mono text-xs font-bold uppercase tracking-widest text-slate-500">Unit Baru Resmi Dealer Pro</span>
                                <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900 mt-1">
                                    {{ $car->brand }} {{ $car->model_name }}
                                </h1>
                            </div>

                            @if($car->promos->isNotEmpty())
                                <div class="bg-blue-50 border border-blue-200 p-3 space-y-1">
                                    <div class="text-[11px] font-bold uppercase tracking-wider text-blue-700">Program Penawaran Aktif:</div>
                                    @foreach($car->promos as $promo)
                                        <div class="text-xs font-semibold text-slate-900">
                                            {{ $promo->title }} (Kode: <span class="font-mono font-bold text-blue-600">{{ $promo->code }}</span>)
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            @if($car->description)
                                <p class="text-sm text-slate-600 leading-relaxed">
                                    {{ $car->description }}
                                </p>
                            @endif
                        </div>

                        <!-- OTR Price Box -->
                        <div class="p-5 bg-slate-50 border border-slate-200 space-y-2">
                            <span class="block text-xs font-bold uppercase tracking-wider text-slate-500">
                                Harga Resmi OTR Jakarta (Estimasi)
                            </span>
                            <div class="font-mono text-3xl sm:text-4xl font-extrabold text-slate-900 tabular-nums">
                                {{ $car->price_formatted }}
                            </div>
                            <p class="text-[11px] text-slate-500">
                                *Harga belum termasuk potongan program berjalan atau skema pembiayaan khusus showroom.
                            </p>
                        </div>

                        <!-- CTA Actions Suite -->
                        <div class="space-y-3 pt-4 border-t border-slate-200">
                            <button type="button"
                                    @click="openTestDriveModal()"
                                    class="w-full py-4 px-6 bg-slate-900 hover:bg-blue-600 text-white text-xs font-bold uppercase tracking-widest transition text-center shadow-md">
                                Test Drive Unit Ini
                            </button>

                            <button type="button"
                                    @click="openServiceModal()"
                                    class="w-full py-3.5 px-6 border border-slate-900 hover:bg-slate-900 hover:text-white text-slate-900 text-xs font-bold uppercase tracking-widest transition text-center bg-white">
                                Booking Servis Kendaraan Ini
                            </button>

                            <a href="https://wa.me/6281234567890?text={{ urlencode('Halo Dealer Pro, saya ingin konsultasi mengenai unit ' . $car->brand . ' ' . $car->model_name . ' (' . $car->year . ')') }}"
                               target="_blank"
                               rel="noopener noreferrer"
                               class="w-full inline-flex items-center justify-center gap-2 py-3 px-4 border border-emerald-600 text-emerald-800 hover:bg-emerald-50 text-xs font-bold uppercase tracking-wider transition">
                                <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.312.045-.694.077-2.027-.47-1.525-.626-2.525-2.183-2.603-2.288-.077-.104-.626-.833-.626-1.589 0-.756.398-1.127.54-1.28.143-.153.311-.191.415-.191.104 0 .208.002.299.006.096.004.225-.036.35.265.13.312.446 1.085.485 1.164.039.078.065.17.013.273-.052.104-.078.169-.156.26-.078.091-.163.203-.233.273-.078.077-.16.162-.069.317.091.156.403.666.866 1.077.595.528 1.097.691 1.253.769.156.078.247.065.338-.039.091-.104.39-.455.494-.611.104-.156.208-.13.35-.078.144.052.91.43 1.066.508.156.078.26.117.299.182.039.065.039.377-.105.782z"/>
                                </svg>
                                Konsultasi WhatsApp Sales
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Engineering Telemetry Grid Section -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-12 space-y-10">
            <div>
                <span class="font-mono text-xs font-bold uppercase tracking-widest text-blue-600">Spesifikasi Mesin &amp; Performa</span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight mt-1">
                    Telemetri Performa &amp; Rekayasa Pabrikan
                </h2>
            </div>

            <!-- 4-Column Hairline Telemetry Grid -->
            <dl class="grid grid-cols-2 md:grid-cols-4 border border-slate-300 divide-y md:divide-y-0 md:divide-x divide-slate-300 bg-white shadow-sm">
                <div class="p-6">
                    <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Tenaga Mesin</dt>
                    <dd class="font-mono text-2xl sm:text-3xl font-extrabold text-slate-900 mt-2 tabular-nums">
                        {{ $car->engine_specs['power'] ?? '-' }}
                    </dd>
                    <span class="block text-[11px] text-slate-400 mt-1">Output Maksimum HP/kW</span>
                </div>
                <div class="p-6">
                    <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Torsi Maksimum</dt>
                    <dd class="font-mono text-2xl sm:text-3xl font-extrabold text-slate-900 mt-2 tabular-nums">
                        {{ $car->engine_specs['torque'] ?? '-' }}
                    </dd>
                    <span class="block text-[11px] text-slate-400 mt-1">Puncak Torsi Putaran</span>
                </div>
                <div class="p-6">
                    <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Akselerasi 0-100 KM/J</dt>
                    <dd class="font-mono text-2xl sm:text-3xl font-extrabold text-slate-900 mt-2 tabular-nums">
                        {{ $car->engine_specs['accel'] ?? '-' }}
                    </dd>
                    <span class="block text-[11px] text-slate-400 mt-1">Uji Lintasan Standar</span>
                </div>
                <div class="p-6">
                    <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Sistem Transmisi</dt>
                    <dd class="font-mono text-2xl sm:text-3xl font-extrabold text-slate-900 mt-2 tabular-nums">
                        {{ $car->engine_specs['trans'] ?? '-' }}
                    </dd>
                    <span class="block text-[11px] text-slate-400 mt-1">Penggerak Roda &amp; Girboks</span>
                </div>
            </dl>

            <!-- Dimensions and Features Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                <!-- Dimensions (5 Cols) -->
                <div class="lg:col-span-5 bg-white border border-slate-300 p-6 sm:p-8 space-y-6">
                    <h3 class="text-base font-extrabold uppercase tracking-tight text-slate-900 border-b border-slate-200 pb-3">
                        Dimensi &amp; Kapasitas Bodi
                    </h3>

                    @if(!empty($car->dimensions) && is_array($car->dimensions))
                        <dl class="divide-y divide-slate-200 text-sm">
                            @foreach($car->dimensions as $dimKey => $dimVal)
                                <div class="py-2.5 flex items-center justify-between">
                                    <dt class="text-slate-600 uppercase font-semibold text-xs">{{ ucwords(str_replace('_', ' ', $dimKey)) }}</dt>
                                    <dd class="font-mono font-bold text-slate-900">{{ $dimVal }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    @else
                        <dl class="divide-y divide-slate-200 text-sm">
                            <div class="py-2.5 flex items-center justify-between">
                                <dt class="text-slate-600 text-xs font-semibold">Tipe Bodi / Sasis</dt>
                                <dd class="font-bold text-slate-900">{{ $car->category->label() }}</dd>
                            </div>
                            <div class="py-2.5 flex items-center justify-between">
                                <dt class="text-slate-600 text-xs font-semibold">Tahun Produksi</dt>
                                <dd class="font-mono font-bold text-slate-900">{{ $car->year }}</dd>
                            </div>
                            <div class="py-2.5 flex items-center justify-between">
                                <dt class="text-slate-600 text-xs font-semibold">Standar Emisi &amp; Sertifikasi</dt>
                                <dd class="font-bold text-slate-900">Euro 5 / Standar Pabrikan</dd>
                            </div>
                        </dl>
                    @endif
                </div>

                <!-- Features Checklist (7 Cols) -->
                <div class="lg:col-span-7 bg-white border border-slate-300 p-6 sm:p-8 space-y-6">
                    <h3 class="text-base font-extrabold uppercase tracking-tight text-slate-900 border-b border-slate-200 pb-3">
                        Fitur Utama &amp; Paket Keselamatan
                    </h3>

                    @if(!empty($car->features) && is_array($car->features))
                        <ul class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                            @foreach($car->features as $feat)
                                <li class="flex items-start gap-2.5 text-slate-800">
                                    <svg class="w-4 h-4 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="font-medium">{{ $feat }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-xs text-slate-500">
                            Fitur standar pabrik lengkap dengan sistem keselamatan aktif, kontrol traksi elektronik, dan sistem infotainment terintegrasi.
                        </p>
                    @endif
                </div>
            </div>
        </section>

        <!-- Other Available Units Section -->
        @if($relatedCars->isNotEmpty())
            <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-16 pt-12 border-t border-slate-300">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <span class="font-mono text-xs font-bold uppercase tracking-widest text-slate-500">Rekomendasi Showroom</span>
                        <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight mt-1">
                            Unit Kendaraan Pilihan Lainnya
                        </h2>
                    </div>
                    <a href="{{ route('home') }}#katalog-mobil" class="text-xs font-bold uppercase tracking-wider text-blue-600 hover:text-slate-900 transition">
                        Semua Unit &rarr;
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach($relatedCars as $relCar)
                        <div class="bg-white border border-slate-300 p-6 flex flex-col justify-between hover:shadow-md transition">
                            <div class="space-y-4">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="font-mono font-bold text-slate-500">{{ $relCar->year }}</span>
                                    <span class="font-mono font-bold uppercase px-2 py-0.5 bg-slate-100 text-slate-700 border border-slate-200">
                                        {{ $relCar->category->label() }}
                                    </span>
                                </div>
                                <div class="py-4 flex items-center justify-center h-32">
                                    <img src="{{ $relCar->hero_image_url }}"
                                         alt="{{ $relCar->brand }} {{ $relCar->model_name }}"
                                         loading="lazy"
                                         width="320"
                                         height="180"
                                         class="max-h-28 w-auto object-contain drop-shadow" />
                                </div>
                                <div>
                                    <div class="font-mono text-[11px] font-bold uppercase text-blue-600">{{ $relCar->brand }}</div>
                                    <h3 class="text-lg font-bold text-slate-900">{{ $relCar->brand }} {{ $relCar->model_name }}</h3>
                                    <p class="font-mono text-base font-extrabold text-slate-900 mt-2">{{ $relCar->price_formatted }}</p>
                                </div>
                            </div>
                            <div class="pt-4 mt-6 border-t border-slate-200">
                                <a href="{{ route('cars.public.show', $relCar->slug) }}"
                                   class="block w-full py-2.5 text-center bg-slate-900 hover:bg-blue-600 text-white text-xs font-bold uppercase tracking-wider transition">
                                    Lihat Detail Spesifikasi
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
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900">Reservasi Langsung</h3>
                    <div class="flex flex-col gap-2.5">
                        <button type="button"
                                @click="openTestDriveModal()"
                                class="w-full text-center px-4 py-3 bg-slate-900 text-white text-xs font-bold uppercase tracking-wider hover:bg-blue-600 transition">
                            Jadwalkan Test Drive
                        </button>
                        <button type="button"
                                @click="openServiceModal()"
                                class="w-full text-center px-4 py-3 border border-slate-900 bg-white text-slate-900 text-xs font-bold uppercase tracking-wider hover:bg-slate-900 hover:text-white transition">
                            Booking Servis Bengkel
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

    <!-- Toast Notifications -->
    <div class="fixed top-20 right-4 sm:right-6 z-40 space-y-3 max-w-sm w-full pointer-events-none"
         role="status"
         aria-live="polite">
        <template x-for="toast in toasts" :key="toast.id">
            <div x-show="toast.visible"
                 class="pointer-events-auto p-4 rounded-xl shadow-xl backdrop-blur-md border text-sm flex items-start gap-3"
                 :class="{
                     'bg-green-50/95 border-green-200 text-green-800': toast.type === 'success',
                     'bg-red-50/95 border-red-200 text-red-800': toast.type === 'error',
                     'bg-blue-50/95 border-blue-200 text-blue-800': toast.type === 'info'
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

    <!-- Modal A: Test Drive Form -->
    <div x-show="testDriveModalOpen"
         x-cloak
         role="dialog"
         aria-modal="true"
         aria-labelledby="test-drive-title"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
         @keydown.escape.window="closeModals()">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" @click="closeModals()"></div>

        <div class="relative w-full max-w-lg rounded-2xl bg-white border border-slate-300 shadow-2xl p-6 sm:p-8 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between pb-4 border-b border-slate-200">
                <div>
                    <h3 id="test-drive-title" class="text-lg font-extrabold text-slate-900">Jadwalkan Test Drive Resmi</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Unit: <span class="font-bold text-slate-900">{{ $car->brand }} {{ $car->model_name }}</span></p>
                </div>
                <button type="button" @click="closeModals()" class="text-slate-400 hover:text-slate-700 p-1">
                    <span class="sr-only">Tutup</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="{{ route('test-drive.store') }}"
                  method="POST"
                  enctype="multipart/form-data"
                  @submit.prevent="submitTestDrive"
                  class="mt-6 space-y-4">
                @csrf
                <input type="hidden" name="car_id" value="{{ $car->id }}">

                <div>
                    <label for="td_date" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                        Pilihan Tanggal &amp; Waktu <span class="text-red-500">*</span>
                    </label>
                    <input type="datetime-local"
                           id="td_date"
                           name="preferred_date"
                           x-model="testDriveForm.preferred_date"
                           required
                           class="w-full text-sm border-slate-300 rounded-lg focus:border-blue-600 focus:ring-blue-600">
                </div>

                <div>
                    <label for="td_license_num" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                        Nomor SIM A <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           id="td_license_num"
                           name="driver_license_id"
                           x-model="testDriveForm.driver_license_id"
                           placeholder="Contoh: 1234-5678-9012"
                           required
                           class="w-full text-sm border-slate-300 rounded-lg focus:border-blue-600 focus:ring-blue-600">
                </div>

                <div>
                    <label for="td_license_img" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                        Foto SIM A (JPG / PNG, Maks 2MB)
                    </label>
                    <input type="file"
                           id="td_license_img"
                           name="driver_license_image"
                           accept="image/jpeg,image/png,image/webp"
                           class="w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-slate-900 file:text-white hover:file:bg-blue-600">
                </div>

                <div>
                    <label for="td_notes" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                        Catatan Khusus (Opsional)
                    </label>
                    <textarea id="td_notes"
                              name="notes"
                              x-model="testDriveForm.notes"
                              rows="2"
                              placeholder="Kebutuhan khusus atau pertanyaan sebelum sesi..."
                              class="w-full text-sm border-slate-300 rounded-lg focus:border-blue-600 focus:ring-blue-600"></textarea>
                </div>

                <div class="pt-4">
                    <button type="submit"
                            :disabled="submitting"
                            class="w-full py-3.5 px-4 bg-slate-900 hover:bg-blue-600 text-white text-xs font-bold uppercase tracking-wider transition text-center disabled:opacity-50">
                        <span x-show="!submitting">Kirim Pengajuan Test Drive</span>
                        <span x-show="submitting" x-cloak>Memproses...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal B: Booking Servis Form -->
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
                    <h3 id="service-modal-title" class="text-lg font-extrabold text-slate-900">Reservasi Slot Bengkel Resmi</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Maksimal 3 slot/jam dengan kuota otomatis</p>
                </div>
                <button type="button" @click="closeModals()" class="text-slate-400 hover:text-slate-700 p-1">
                    <span class="sr-only">Tutup</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="{{ route('service-booking.store') }}"
                  method="POST"
                  @submit.prevent="submitService"
                  class="mt-6 space-y-4">
                @csrf
                <div>
                    <label for="srv_vehicle_model" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                        Model Kendaraan <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           id="srv_vehicle_model"
                           name="vehicle_model"
                           x-model="serviceForm.vehicle_model"
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
                               x-model="serviceForm.vehicle_plate_number"
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
                               x-model="serviceForm.vehicle_year"
                               min="1990"
                               max="{{ date('Y') + 1 }}"
                               required
                               class="w-full text-sm border-slate-300 rounded-lg focus:border-blue-600 focus:ring-blue-600">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="srv_date" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                            Tanggal Servis <span class="text-red-500">*</span>
                        </label>
                        <input type="date"
                               id="srv_date"
                               name="service_date"
                               x-model="serviceForm.service_date"
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
                                x-model="serviceForm.slot_hour"
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
                               x-model="serviceForm.odometer_km"
                               min="0"
                               max="999999"
                               placeholder="Contoh: 15000"
                               required
                               class="w-full text-sm border-slate-300 rounded-lg focus:border-blue-600 focus:ring-blue-600">
                    </div>
                    <div>
                        <label for="srv_type" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                            Kategori Layanan <span class="text-red-500">*</span>
                        </label>
                        <select id="srv_type"
                                name="service_type"
                                x-model="serviceForm.service_type"
                                required
                                class="w-full text-sm border-slate-300 rounded-lg focus:border-blue-600 focus:ring-blue-600">
                            <option value="regular">Servis Berkala / Tune-Up</option>
                            <option value="major">Servis Besar (Major)</option>
                            <option value="inspection">Pemeriksaan / Inspeksi</option>
                            <option value="warranty">Garansi &amp; Pemasangan</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="srv_complaint" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                        Keluhan atau Catatan Perawatan <span class="text-red-500">*</span>
                    </label>
                    <textarea id="srv_complaint"
                              name="complaint_notes"
                              x-model="serviceForm.complaint_notes"
                              rows="3"
                              placeholder="Deskripsikan keluhan atau bagian yang ingin diperiksa..."
                              required
                              class="w-full text-sm border-slate-300 rounded-lg focus:border-blue-600 focus:ring-blue-600"></textarea>
                </div>

                <div class="pt-4">
                    <button type="submit"
                            :disabled="submitting"
                            class="w-full py-3.5 px-4 bg-slate-900 hover:bg-blue-600 text-white text-xs font-bold uppercase tracking-wider transition text-center disabled:opacity-50">
                        <span x-show="!submitting">Konfirmasi Reservasi Bengkel</span>
                        <span x-show="submitting" x-cloak>Memproses...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function carDetailApp(config) {
            return {
                testDriveModalOpen: false,
                serviceModalOpen: false,
                submitting: false,
                toasts: [],
                testDriveForm: {
                    preferred_date: config.pendingTestDrive?.preferred_date || '',
                    driver_license_id: config.pendingTestDrive?.driver_license_id || '',
                    notes: config.pendingTestDrive?.notes || ''
                },
                serviceForm: {
                    vehicle_model: config.pendingServiceBooking?.vehicle_model || config.carName || '',
                    vehicle_plate_number: config.pendingServiceBooking?.vehicle_plate_number || '',
                    vehicle_year: config.pendingServiceBooking?.vehicle_year || {{ $car->year }},
                    odometer_km: config.pendingServiceBooking?.odometer_km || 10000,
                    service_date: config.pendingServiceBooking?.service_date || '',
                    slot_hour: config.pendingServiceBooking?.slot_hour || 9,
                    service_type: config.pendingServiceBooking?.service_type || 'regular',
                    complaint_notes: config.pendingServiceBooking?.complaint_notes || ''
                },
                openTestDriveModal() {
                    this.testDriveModalOpen = true;
                },
                openServiceModal() {
                    this.serviceModalOpen = true;
                },
                closeModals() {
                    this.testDriveModalOpen = false;
                    this.serviceModalOpen = false;
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
                async submitTestDrive(e) {
                    this.submitting = true;
                    const form = e.target;
                    const formData = new FormData(form);

                    try {
                        const res = await fetch('{{ route('test-drive.store') }}', {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: formData
                        });
                        const data = await res.json();
                        if (res.ok) {
                            this.closeModals();
                            this.addToast(data.message || 'Pengajuan test drive berhasil dikirim!', 'success');
                            if (data.redirect) {
                                window.location.href = data.redirect;
                            }
                        } else {
                            const errMsg = data.message || Object.values(data.errors || {}).flat()[0] || 'Gagal mengirim pengajuan.';
                            this.addToast(errMsg, 'error');
                        }
                    } catch (err) {
                        form.submit();
                    } finally {
                        this.submitting = false;
                    }
                },
                async submitService(e) {
                    this.submitting = true;
                    const form = e.target;
                    const formData = new FormData(form);

                    try {
                        const res = await fetch('{{ route('service-booking.store') }}', {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: formData
                        });
                        const data = await res.json();
                        if (res.ok) {
                            this.closeModals();
                            this.addToast(data.message || 'Booking servis berhasil!', 'success');
                            if (data.redirect) {
                                window.location.href = data.redirect;
                            }
                        } else {
                            const errMsg = data.message || Object.values(data.errors || {}).flat()[0] || 'Gagal memproses booking servis.';
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
