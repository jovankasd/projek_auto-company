<!DOCTYPE html>
<html lang="id" class="h-full bg-gray-50 text-slate-900 scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $cars->first()->meta_title ?? 'Dealer Pro : Showroom Mobil Premium & Booking Servis' }}</title>
    <meta name="description" content="{{ $cars->first()->meta_description ?? 'Jelajahi koleksi mobil mewah dan sport terbaik dengan pengalaman visual 2.5D di Dealer Pro. Layanan booking servis dan test drive resmi.' }}">
    <link rel="canonical" href="{{ url()->current() }}">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="Dealer Pro : Showroom Mobil Premium & Booking Servis">
    <meta property="og:description" content="Showroom mobil premium masa depan dengan pengalaman 2.5D. Jadwalkan test drive dan servis kendaraan Anda dengan mudah.">
    <meta property="og:image" content="{{ asset('images/cars/ford-mustang-gt-2024.webp') }}">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Dealer Pro : Showroom Mobil Premium">
    <meta name="twitter:description" content="Showroom mobil premium masa depan dengan pengalaman 2.5D. Jadwalkan test drive dan servis resmi.">
    <meta name="twitter:image" content="{{ asset('images/cars/ford-mustang-gt-2024.webp') }}">

    <!-- Schema.org JSON-LD (AutoDealer) -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "AutoDealer",
        "name": "Dealer Pro",
        "description": "Showroom mobil premium dan pusat servis kendaraan modern di Indonesia.",
        "url": "{{ url('/') }}",
        "telephone": "+62-21-555-0199",
        "address": {
            "@type": "PostalAddress",
            "streetAddress": "Jl. Sudirman No. 88",
            "addressLocality": "Jakarta Selatan",
            "addressRegion": "DKI Jakarta",
            "postalCode": "12190",
            "addressCountry": "ID"
        },
        "openingHoursSpecification": [
            {
                "@type": "OpeningHoursSpecification",
                "dayOfWeek": ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"],
                "opens": "08:00",
                "closes": "18:00"
            }
        ]
    }
    </script>

    <x-font-head />
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/showroom.js'])
</head>
<body class="min-h-full bg-slate-50 font-sans text-slate-900 antialiased overflow-x-hidden selection:bg-blue-600 selection:text-white"
      x-data="showroomApp({
          initialModal: @js(session('open_modal')),
          pendingTestDrive: @js($pendingTestDrive ?? null),
          pendingServiceBooking: @js($pendingServiceBooking ?? null),
          carNames: @js($cars->mapWithKeys(fn ($c) => [$c->id => $c->brand . ' ' . $c->model_name]))
      })"
      :class="{ 'overflow-hidden': testDriveModalOpen || serviceModalOpen }">

    <!-- Accessibility: Skip to Content (WCAG 2.1 AA) -->
    <a href="#main-showroom"
       class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-[70] focus:px-4 focus:py-2 focus:bg-slate-900 focus:text-white focus:rounded-xl focus:shadow-lg">
        Lewati ke konten utama
    </a>

    <x-public-navbar :on-showroom="true" />

    <!-- Main 2.5D Kinetic Showroom -->
    <main id="main-showroom" class="relative min-h-[calc(100svh-72px)] w-full overflow-hidden bg-gradient-to-b from-white via-slate-50 to-slate-100/80 flex items-stretch justify-center select-none">
        <!-- Swiper Container -->
        <div class="swiper showroom-swiper w-full min-h-[calc(100svh-72px)] relative" aria-label="Katalog Showroom Mobil 2.5D">
            <div class="swiper-wrapper">
                @forelse ($cars as $car)
                    <section class="swiper-slide relative w-full min-h-[calc(100svh-72px)] overflow-hidden flex items-stretch"
                             data-car-id="{{ $car->id }}"
                             data-car-slug="{{ $car->slug }}"
                             data-car-name="{{ $car->brand }} {{ $car->model_name }}"
                             aria-label="{{ $car->brand }} {{ $car->model_name }} {{ $car->year }}">

                        @php
                            $nameLen = strlen($car->model_name);
                            $bgSize = $nameLen > 10 ? 'clamp(2.5rem, 8.5vw, 9rem)' : 'clamp(3.75rem, 14vw, 15rem)';
                        @endphp

                        <div class="relative z-10 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 sm:pt-8 pb-20 sm:pb-24 flex flex-col justify-between gap-4 sm:gap-6">
                            <!-- Car Top Header / Meta -->
                            <div class="flex flex-col items-center justify-center text-center" data-car-meta>
                                <span class="inline-flex items-center rounded-full px-3.5 py-1 text-[11px] font-bold uppercase tracking-wider {{ $car->category->color() ?? 'bg-blue-50 text-blue-700 border-blue-200' }} border shadow-sm mb-2.5">
                                    {{ $car->category->label() }}
                                </span>
                                <h1 class="font-display text-2xl sm:text-4xl lg:text-5xl font-extrabold text-slate-900 tracking-tight leading-tight">
                                    {{ $car->brand }} <span class="text-blue-600">{{ $car->model_name }}</span>
                                </h1>
                                <p class="font-mono text-base sm:text-xl font-bold text-slate-800 mt-1.5 tabular-nums">
                                    {{ $car->price_formatted }}
                                    <span class="text-xs font-sans text-slate-600 font-medium ml-1">OTR Jakarta</span>
                                </p>
                            </div>

                            <!-- Center Stage: Typographic Watermark + Centered Floating Vehicle -->
                            <div class="relative flex-1 min-h-[210px] sm:min-h-[280px] lg:min-h-[340px] flex items-center justify-center pointer-events-none" data-car-visual>
                                <div class="absolute inset-0 flex items-center justify-center font-display font-black uppercase tracking-tighter text-slate-200/75 select-none pointer-events-none whitespace-nowrap text-center overflow-hidden"
                                     style="font-size: {{ $bgSize }}; line-height: 0.9;"
                                     data-bg-text
                                     aria-hidden="true">
                                    {{ $car->model_name }}
                                </div>
                                <img src="{{ $car->hero_image_url }}"
                                     alt="{{ $car->hero_image_alt ?: ($car->brand . ' ' . $car->model_name . ' tampak samping') }}"
                                     width="1060"
                                     height="480"
                                     class="relative z-10 max-h-[28vh] sm:max-h-[36vh] lg:max-h-[42vh] w-auto max-w-[90vw] md:max-w-[74vw] object-contain drop-shadow-car pointer-events-auto"
                                     data-car-img
                                     {{ $loop->first ? 'loading="eager" fetchpriority="high"' : 'loading="lazy" decoding="async"' }} />
                            </div>

                            <!-- Bottom HUD Specs & Action Buttons -->
                            <div class="space-y-4 sm:space-y-5">
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-2.5 sm:gap-4 w-full max-w-4xl mx-auto" data-car-specs>
                                    <div data-spec-item class="bg-white border border-slate-200/80 shadow-sm rounded-2xl p-3 sm:p-4 text-center">
                                        <div class="text-[11px] font-semibold uppercase tracking-wider text-slate-600 mb-0.5">Tenaga Mesin</div>
                                        <div class="font-mono text-base sm:text-xl font-extrabold text-slate-900 truncate tabular-nums">{{ $car->engine_specs['power'] ?? '-' }}</div>
                                    </div>
                                    <div data-spec-item class="bg-white border border-slate-200/80 shadow-sm rounded-2xl p-3 sm:p-4 text-center">
                                        <div class="text-[11px] font-semibold uppercase tracking-wider text-slate-600 mb-0.5">Torsi Maksimum</div>
                                        <div class="font-mono text-base sm:text-xl font-extrabold text-slate-900 truncate tabular-nums">{{ $car->engine_specs['torque'] ?? '-' }}</div>
                                    </div>
                                    <div data-spec-item class="bg-white border border-slate-200/80 shadow-sm rounded-2xl p-3 sm:p-4 text-center">
                                        <div class="text-[11px] font-semibold uppercase tracking-wider text-slate-600 mb-0.5">0-100 KM/H</div>
                                        <div class="font-mono text-base sm:text-xl font-extrabold text-slate-900 truncate tabular-nums">{{ $car->engine_specs['accel'] ?? '-' }}</div>
                                    </div>
                                    <div data-spec-item class="bg-white border border-slate-200/80 shadow-sm rounded-2xl p-3 sm:p-4 text-center">
                                        <div class="text-[11px] font-semibold uppercase tracking-wider text-slate-600 mb-0.5">Transmisi</div>
                                        <div class="font-mono text-base sm:text-xl font-extrabold text-slate-900 truncate tabular-nums">{{ $car->engine_specs['trans'] ?? '-' }}</div>
                                    </div>
                                </div>

                                <div class="flex flex-wrap items-center justify-center gap-2.5 sm:gap-3.5 pointer-events-auto" data-car-actions>
                                    <button type="button"
                                            @click="openTestDriveModal({{ $car->id }}, @js($car->brand . ' ' . $car->model_name))"
                                            data-car-id="{{ $car->id }}"
                                            data-car-name="{{ $car->brand }} {{ $car->model_name }}"
                                            class="inline-flex items-center justify-center min-h-[44px] bg-slate-900 text-white px-5 sm:px-7 py-2.5 rounded-xl font-semibold hover:bg-blue-600 hover:scale-[1.02] active:scale-[0.98] focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2 transition-all duration-200 shadow-sm hover:shadow-md text-xs sm:text-sm whitespace-nowrap">
                                        Test Drive
                                    </button>

                                    <button type="button"
                                            @click="openServiceModal(@js($car->brand . ' ' . $car->model_name))"
                                            data-car-id="{{ $car->id }}"
                                            data-car-name="{{ $car->brand }} {{ $car->model_name }}"
                                            class="inline-flex items-center justify-center min-h-[44px] bg-white text-slate-900 border border-slate-300 px-5 sm:px-7 py-2.5 rounded-xl font-semibold hover:bg-slate-100 hover:border-slate-400 hover:scale-[1.02] active:scale-[0.98] focus-visible:ring-2 focus-visible:ring-blue-600 transition-all duration-200 shadow-sm text-xs sm:text-sm whitespace-nowrap">
                                        Booking Servis
                                    </button>

                                    <a href="{{ route('cars.public.show', $car->slug) }}"
                                       class="inline-flex items-center justify-center min-h-[44px] bg-blue-50 text-blue-700 border border-blue-200 px-4 sm:px-5 py-2.5 rounded-xl font-semibold hover:bg-blue-100 transition-all duration-200 text-xs sm:text-sm whitespace-nowrap">
                                        Detail Unit
                                    </a>
                                </div>
                            </div>
                        </div>
                    </section>
                @empty
                    <div class="w-full min-h-[calc(100svh-72px)] flex flex-col items-center justify-center text-center p-8 z-20">
                        <div class="w-20 h-20 rounded-full bg-slate-100 flex items-center justify-center mb-4 text-slate-600">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        </div>
                        <h2 class="font-display text-2xl font-bold text-slate-800 mb-2">Showroom Sedang Disiapkan</h2>
                        <p class="text-slate-600 text-sm max-w-md mb-6">Katalog mobil belum tersedia di database saat ini. Silakan jalankan seeder di lingkungan pengembangan atau hubungi staf kami.</p>
                        <a href="{{ route('home') }}" class="inline-flex items-center justify-center min-h-[44px] bg-slate-900 text-white px-6 py-3 rounded-xl text-sm font-semibold hover:bg-blue-600 transition active:scale-[0.98] shadow-sm">
                            Muat Ulang Halaman
                        </a>
                    </div>
                @endforelse
            </div>

            @if(count($cars) > 0)
            <!-- Layer 20: Slider Controls, Play/Pause Toggle & Counter -->
            <div class="absolute bottom-3 sm:bottom-4 left-1/2 -translate-x-1/2 z-20 flex items-center gap-1.5 sm:gap-2 bg-white border border-slate-200/80 rounded-full px-2 py-1 shadow-md">
                <button type="button"
                        data-slide-prev
                        aria-label="Mobil sebelumnya"
                        class="min-w-[44px] min-h-[44px] flex items-center justify-center rounded-full hover:bg-slate-100 active:scale-95 transition text-slate-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>

                <div class="font-mono text-xs sm:text-sm tracking-widest text-slate-800 font-bold select-none whitespace-nowrap tabular-nums px-1.5">
                    <span id="slide-counter-current">01</span> / <span id="slide-counter-total">{{ sprintf('%02d', count($cars)) }}</span>
                </div>

                <button type="button"
                        data-slide-next
                        aria-label="Mobil berikutnya"
                        class="min-w-[44px] min-h-[44px] flex items-center justify-center rounded-full hover:bg-slate-100 active:scale-95 transition text-slate-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                    </svg>
                </button>

                <span class="h-5 w-px bg-slate-200" aria-hidden="true"></span>

                <button type="button"
                        data-slider-toggle
                        aria-pressed="true"
                        aria-label="Jeda putar otomatis showroom"
                        class="min-h-[44px] min-w-[44px] sm:px-3 inline-flex items-center justify-center gap-1.5 rounded-full hover:bg-slate-100 active:scale-95 transition text-xs font-semibold text-slate-700">
                    <svg data-icon-pause class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6" />
                    </svg>
                    <svg data-icon-play class="w-4 h-4 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                    </svg>
                    <span data-toggle-label class="hidden sm:inline">Jeda</span>
                </button>
            </div>

            <!-- Scroll Indicator to Catalog -->
            <a href="#tentang-kami"
               class="hidden lg:inline-flex items-center gap-2 absolute bottom-4 right-6 z-20 min-h-[44px] bg-white border border-slate-200/80 rounded-full px-4 py-2 text-xs font-semibold text-slate-700 hover:text-blue-600 hover:border-blue-200 shadow-sm transition">
                <span>Jelajahi Katalog Lengkap</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                </svg>
            </a>
            @endif

            <!-- Screen reader live updates -->
            <div id="showroom-aria-live" class="sr-only" aria-live="polite"></div>

            <!-- Progressive Enhancement Fallback -->
            <noscript>
                <div class="fixed bottom-0 inset-x-0 bg-amber-50 border-t border-amber-200 p-4 text-center text-amber-800 text-sm z-50">
                    JavaScript dinonaktifkan di peramban Anda. Menggunakan mode tampilan statis untuk pengalaman browsing terbaik.
                </div>
            </noscript>
        </div>
    </main>

    <!-- =====================================================================
         SEKSI 1: PROFIL ARSITEKTUR OPERASIONAL & DIVISI RESMI DEALER PRO
         ===================================================================== -->
    <section id="tentang-kami" class="bg-white border-t border-slate-200/80 py-20 sm:py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-14 items-start">
                <!-- Left Column: Editorial Lead + Operational Specification Table -->
                <div class="lg:col-span-5 space-y-8">
                    <div>
                        <span class="inline-flex items-center rounded-full bg-blue-50 border border-blue-200 px-3 py-1 font-mono text-[11px] font-bold uppercase tracking-widest text-blue-700 mb-3">
                            Ekosistem Otomotif Terpadu
                        </span>
                        <h2 class="font-display text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight leading-tight">
                            Pusat Penjualan Kendaraan, Bengkel Resmi, dan Gudang Suku Cadang Orisinal.
                        </h2>
                        <p class="text-slate-600 text-base leading-relaxed mt-4">
                            <strong>Dealer Pro (AUTO COMPANY)</strong> mengoperasikan fasilitas showroom kendaraan performa tinggi, bengkel perawatan berkala dengan kuota terjadwal, serta penyediaan suku cadang OEM dalam satu sistem terintegrasi di Jakarta Selatan.
                        </p>
                    </div>

                    <!-- Operational Ledger Card -->
                    <dl class="rounded-2xl bg-slate-50/90 border border-slate-200/80 divide-y divide-slate-200/80 text-sm px-5">
                        <div class="py-3.5 flex items-baseline justify-between gap-4">
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-600">Lokasi Showroom &amp; Workshop</dt>
                            <dd class="font-semibold text-slate-900 text-right">Jl. Sudirman No. 88, Jakarta Selatan</dd>
                        </div>
                        <div class="py-3.5 flex items-baseline justify-between gap-4">
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-600">Jam Operasional Resmi</dt>
                            <dd class="font-mono font-bold text-slate-900 text-right tabular-nums">Senin - Sabtu / 08:00 - 18:00 WIB</dd>
                        </div>
                        <div class="py-3.5 flex items-baseline justify-between gap-4">
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-600">Kapasitas Stall Bengkel</dt>
                            <dd class="font-mono font-bold text-slate-900 text-right tabular-nums">Maks. 3 Unit / Slot Jam</dd>
                        </div>
                        <div class="py-3.5 flex items-baseline justify-between gap-4">
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-600">Inventaris Katalog Aktif</dt>
                            <dd class="font-mono font-bold text-blue-600 text-right tabular-nums">
                                {{ count($cars) }} Unit Mobil / {{ isset($spareParts) ? count($spareParts) : 0 }} SKU Part
                            </dd>
                        </div>
                    </dl>

                    <div class="flex flex-wrap items-center gap-3">
                        <a href="#katalog-mobil" class="inline-flex items-center justify-center min-h-[44px] rounded-xl bg-slate-900 text-white px-5 py-2.5 text-xs font-bold uppercase tracking-wider hover:bg-blue-600 hover:scale-[1.02] active:scale-[0.98] transition-all duration-200 shadow-sm">
                            Lihat Spesifikasi Unit ({{ count($cars) }})
                        </a>
                        <a href="#katalog-suku-cadang" class="inline-flex items-center justify-center min-h-[44px] rounded-xl border border-slate-300 bg-white text-slate-900 px-5 py-2.5 text-xs font-bold uppercase tracking-wider hover:border-slate-900 transition-all duration-200">
                            Buka Manifest Suku Cadang
                        </a>
                    </div>
                </div>

                <!-- Right Column: 3 Division Specification Cards -->
                <div class="lg:col-span-7 space-y-4">
                    <!-- Divisi 01 -->
                    <div class="rounded-3xl bg-slate-50/70 border border-slate-200/80 p-6 sm:p-7 grid grid-cols-1 sm:grid-cols-12 gap-4 items-center shadow-sm hover:shadow-md transition-all">
                        <div class="sm:col-span-3">
                            <span class="font-mono text-xs font-bold text-slate-600 uppercase tracking-widest block">DIVISI 01</span>
                            <span class="font-mono text-xs font-bold text-slate-900 uppercase tracking-wider mt-1 block">Showroom &amp; Sales</span>
                        </div>
                        <div class="sm:col-span-6 space-y-1.5">
                            <h3 class="font-display text-lg font-extrabold text-slate-900 tracking-tight">Eksplorasi Unit 2.5D &amp; Sesi Test Drive</h3>
                            <p class="text-sm text-slate-600 leading-relaxed">
                                Bandingkan output daya, torsi puncak, akselerasi 0-100 km/jam, serta harga OTR Jakarta. Pengajuan jadwal Test Drive dapat dilakukan di showroom pusat maupun pengantaran ke lokasi Anda.
                            </p>
                        </div>
                        <div class="sm:col-span-3 sm:text-right">
                            <button type="button"
                                    @click="openTestDriveModal(null, '')"
                                    class="inline-flex items-center justify-center w-full sm:w-auto min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-900 bg-white text-slate-900 hover:bg-slate-900 hover:text-white text-xs font-bold uppercase tracking-wider transition-all duration-200">
                                Jadwalkan Test Drive
                            </button>
                        </div>
                    </div>

                    <!-- Divisi 02 -->
                    <div class="rounded-3xl bg-slate-50/70 border border-slate-200/80 p-6 sm:p-7 grid grid-cols-1 sm:grid-cols-12 gap-4 items-center shadow-sm hover:shadow-md transition-all">
                        <div class="sm:col-span-3">
                            <span class="font-mono text-xs font-bold text-slate-600 uppercase tracking-widest block">DIVISI 02</span>
                            <span class="font-mono text-xs font-bold text-slate-900 uppercase tracking-wider mt-1 block">Bengkel Resmi</span>
                        </div>
                        <div class="sm:col-span-6 space-y-1.5">
                            <h3 class="font-display text-lg font-extrabold text-slate-900 tracking-tight">Perawatan Berkala &amp; Kuota Presisi</h3>
                            <p class="text-sm text-slate-600 leading-relaxed">
                                Penjadwalan bengkel membatasi maksimal 3 kendaraan per jam (08:00 - 17:00 WIB) agar bebas antrean. Setiap reservasi menerbitkan tiket kerja <code class="font-mono text-xs bg-white px-1.5 py-0.5 rounded border border-slate-200 text-slate-800">SRV-YYYYMMDD-XXXX</code>.
                            </p>
                        </div>
                        <div class="sm:col-span-3 sm:text-right">
                            <button type="button"
                                    @click="openServiceModal()"
                                    class="inline-flex items-center justify-center w-full sm:w-auto min-h-[44px] px-4 py-2.5 rounded-xl bg-blue-600 text-white hover:bg-slate-900 text-xs font-bold uppercase tracking-wider transition-all duration-200 shadow-sm">
                                Booking Servis
                            </button>
                        </div>
                    </div>

                    <!-- Divisi 03 -->
                    <div class="rounded-3xl bg-slate-50/70 border border-slate-200/80 p-6 sm:p-7 grid grid-cols-1 sm:grid-cols-12 gap-4 items-center shadow-sm hover:shadow-md transition-all">
                        <div class="sm:col-span-3">
                            <span class="font-mono text-xs font-bold text-slate-600 uppercase tracking-widest block">DIVISI 03</span>
                            <span class="font-mono text-xs font-bold text-slate-900 uppercase tracking-wider mt-1 block">Gudang Suku Cadang</span>
                        </div>
                        <div class="sm:col-span-6 space-y-1.5">
                            <h3 class="font-display text-lg font-extrabold text-slate-900 tracking-tight">Suku Cadang OEM &amp; Aftermarket Teruji</h3>
                            <p class="text-sm text-slate-600 leading-relaxed">
                                Penggantian komponen menggunakan suku cadang bergaransi resmi dengan transparansi nomor part (SKU), ketersediaan stok gudang, dan harga satuan yang jelas.
                            </p>
                        </div>
                        <div class="sm:col-span-3 sm:text-right">
                            <a href="#katalog-suku-cadang"
                               class="inline-flex items-center justify-center w-full sm:w-auto min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-300 bg-white text-slate-900 hover:border-slate-900 text-xs font-bold uppercase tracking-wider transition-all duration-200">
                                Cek Stok Part
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- =====================================================================
         SEKSI 2: DAFTAR LENGKAP UNIT SHOWROOM (SPECIFICATION CARDS)
         ===================================================================== -->
    <section id="katalog-mobil" class="bg-slate-50 border-t border-slate-200/80 py-20 sm:py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
            <!-- Header & Segmented Pill Filter Tabs -->
            <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6 border-b border-slate-200/80 pb-8">
                <div>
                    <h2 class="font-display text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                        Daftar Lengkap Unit Showroom
                    </h2>
                    <p class="text-slate-600 text-sm sm:text-base mt-2 max-w-2xl leading-relaxed">
                        Lembar spesifikasi teknis unit kendaraan yang tersedia di showroom kami beserta harga On The Road (OTR) Jakarta dan penjadwalan sesi uji kendara.
                    </p>
                </div>

                <!-- Segmented Pill Filter Tabs with ARIA + Roving Keyboard Focus -->
                <div class="inline-flex flex-wrap gap-1.5 rounded-2xl border border-slate-200/80 bg-white p-1.5 shadow-sm"
                     role="tablist"
                     aria-label="Filter Kategori Mobil"
                     @keydown.arrow-right.prevent="moveCarTab($event, 1)"
                     @keydown.arrow-left.prevent="moveCarTab($event, -1)">
                    <button type="button"
                            role="tab"
                            :aria-selected="(carCategoryFilter === 'all').toString()"
                            :tabindex="carCategoryFilter === 'all' ? 0 : -1"
                            @click="carCategoryFilter = 'all'"
                            :class="carCategoryFilter === 'all' ? 'bg-slate-900 text-white shadow-sm' : 'bg-transparent text-slate-700 hover:bg-slate-100'"
                            class="min-h-[44px] px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition">
                        Semua ({{ count($cars) }})
                    </button>
                    @foreach(\App\Enums\CarCategory::cases() as $cat)
                        @php
                            $catCount = $cars->filter(fn($c) => ($c->category->value ?? $c->category) === $cat->value)->count();
                        @endphp
                        @if($catCount > 0)
                            <button type="button"
                                    role="tab"
                                    :aria-selected="(carCategoryFilter === '{{ $cat->value }}').toString()"
                                    :tabindex="carCategoryFilter === '{{ $cat->value }}' ? 0 : -1"
                                    @click="carCategoryFilter = '{{ $cat->value }}'"
                                    :class="carCategoryFilter === '{{ $cat->value }}' ? 'bg-slate-900 text-white shadow-sm' : 'bg-transparent text-slate-700 hover:bg-slate-100'"
                                    class="min-h-[44px] px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition">
                                {{ $cat->label() }} ({{ $catCount }})
                            </button>
                        @endif
                    @endforeach
                </div>
            </div>

            <!-- Specification Cards -->
            @if($cars->isEmpty())
                <div class="rounded-3xl bg-white border border-slate-200/80 p-12 text-center shadow-sm">
                    <p class="text-base font-bold text-slate-900">Belum ada unit kendaraan yang terdaftar di katalog.</p>
                    <p class="text-sm text-slate-600 mt-1">Silakan hubungi konsultan showroom kami atau jadwalkan servis kendaraan Anda.</p>
                </div>
            @else
                <div class="space-y-6">
                    @foreach($cars as $index => $car)
                        @php
                            $carCatVal = $car->category->value ?? (string) $car->category;
                        @endphp
                        <article x-show="carCategoryFilter === 'all' || carCategoryFilter === '{{ $carCatVal }}'"
                                 x-transition:enter="transition ease-out duration-150"
                                 x-transition:enter-start="opacity-0"
                                 x-transition:enter-end="opacity-100"
                                 class="rounded-3xl bg-white border border-slate-200/80 grid grid-cols-1 lg:grid-cols-12 divide-y lg:divide-y-0 lg:divide-x divide-slate-200/80 shadow-sm hover:shadow-xl transition-all duration-200 overflow-hidden">

                            <!-- Column 1 (4 Cols): Studio Side-Profile Plate -->
                            <div class="lg:col-span-4 p-6 sm:p-8 flex flex-col justify-between bg-slate-50/60">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="font-mono text-xs font-bold text-slate-600 uppercase tracking-widest tabular-nums">
                                        UNIT {{ sprintf('%02d', $index + 1) }} / {{ $car->year }}
                                    </span>
                                    <span class="inline-flex items-center rounded-full font-mono text-[11px] font-bold uppercase tracking-wider px-3 py-0.5 border border-slate-200/90 bg-white text-slate-800">
                                        {{ $car->category->label() }}
                                    </span>
                                </div>

                                <a href="{{ route('cars.public.show', $car->slug) }}" class="py-6 flex items-center justify-center min-h-[160px] group">
                                    <img src="{{ $car->hero_image_url }}"
                                         alt="{{ $car->brand }} {{ $car->model_name }}"
                                         loading="lazy"
                                         class="max-h-36 sm:max-h-40 w-auto object-contain drop-shadow-md group-hover:scale-105 transition-transform duration-300" />
                                </a>

                                <div class="flex items-center justify-between text-xs text-slate-600 pt-3 border-t border-slate-200/80">
                                    <span>Warna Eksterior</span>
                                    @if($car->color_name)
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-white px-2.5 py-1 border border-slate-200 font-semibold text-slate-800">
                                            <span class="w-2.5 h-2.5 rounded-full border border-slate-300" style="background-color: {{ $car->color_hex ?: '#0f172a' }}"></span>
                                            {{ $car->color_name }}
                                        </span>
                                    @else
                                        <span class="font-mono text-slate-700">Standar Pabrik</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Column 2 (5 Cols): Identity, Description & Telemetry Pills -->
                            <div class="lg:col-span-5 p-6 sm:p-8 flex flex-col justify-between space-y-6">
                                <div class="space-y-2.5">
                                    <div class="font-mono text-xs font-bold uppercase tracking-widest text-blue-600">
                                        {{ $car->brand }}
                                    </div>
                                    <h3 class="font-display text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                                        <a href="{{ route('cars.public.show', $car->slug) }}" class="hover:text-blue-600 transition">
                                            {{ $car->brand }} {{ $car->model_name }}
                                        </a>
                                    </h3>
                                    @if($car->description)
                                        <p class="text-sm text-slate-600 leading-relaxed pt-1">
                                            {{ $car->description }}
                                        </p>
                                    @endif
                                </div>

                                <!-- 4-Column Telemetry Mini Cards -->
                                <dl class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                                    <div class="rounded-xl bg-slate-50 border border-slate-200/80 p-3">
                                        <dt class="text-[10px] font-bold uppercase tracking-wider text-slate-600">Tenaga</dt>
                                        <dd class="font-mono text-xs sm:text-sm font-bold text-slate-900 mt-1 tabular-nums">{{ $car->engine_specs['power'] ?? '-' }}</dd>
                                    </div>
                                    <div class="rounded-xl bg-slate-50 border border-slate-200/80 p-3">
                                        <dt class="text-[10px] font-bold uppercase tracking-wider text-slate-600">Torsi</dt>
                                        <dd class="font-mono text-xs sm:text-sm font-bold text-slate-900 mt-1 tabular-nums">{{ $car->engine_specs['torque'] ?? '-' }}</dd>
                                    </div>
                                    <div class="rounded-xl bg-slate-50 border border-slate-200/80 p-3">
                                        <dt class="text-[10px] font-bold uppercase tracking-wider text-slate-600">0-100 KM/J</dt>
                                        <dd class="font-mono text-xs sm:text-sm font-bold text-slate-900 mt-1 tabular-nums">{{ $car->engine_specs['accel'] ?? '-' }}</dd>
                                    </div>
                                    <div class="rounded-xl bg-slate-50 border border-slate-200/80 p-3">
                                        <dt class="text-[10px] font-bold uppercase tracking-wider text-slate-600">Transmisi</dt>
                                        <dd class="font-mono text-xs sm:text-sm font-bold text-slate-900 mt-1 tabular-nums">{{ $car->engine_specs['trans'] ?? '-' }}</dd>
                                    </div>
                                </dl>
                            </div>

                            <!-- Column 3 (3 Cols): OTR Pricing & Direct Booking Triggers -->
                            <div class="lg:col-span-3 p-6 sm:p-8 flex flex-col justify-between bg-white">
                                <div>
                                    <span class="block text-[11px] font-bold uppercase tracking-wider text-slate-600">Harga Resmi OTR Jakarta</span>
                                    <p class="font-mono text-xl sm:text-2xl font-extrabold text-slate-900 mt-1 tabular-nums">
                                        {{ $car->price_formatted }}
                                    </p>
                                    <span class="inline-flex items-center rounded-full mt-2.5 font-mono text-[11px] font-semibold text-emerald-800 bg-emerald-50 border border-emerald-200 px-3 py-0.5">
                                        Tersedia untuk Test Drive
                                    </span>
                                </div>

                                <div class="space-y-2.5 pt-6 mt-6 border-t border-slate-200/80">
                                    <button type="button"
                                            @click="openTestDriveModal({{ $car->id }}, @js($car->brand . ' ' . $car->model_name))"
                                            class="w-full min-h-[44px] py-2.5 px-4 rounded-xl bg-slate-900 hover:bg-blue-600 text-white text-xs font-bold uppercase tracking-wider transition-all duration-200 text-center shadow-sm hover:scale-[1.02] active:scale-[0.98]">
                                        Jadwalkan Test Drive
                                    </button>
                                    <button type="button"
                                            @click="openServiceModal(@js($car->brand . ' ' . $car->model_name))"
                                            class="w-full min-h-[44px] py-2.5 px-4 rounded-xl border border-slate-300 hover:border-slate-900 hover:bg-slate-50 text-slate-800 text-xs font-bold uppercase tracking-wider transition text-center">
                                        Booking Servis Unit Ini
                                    </button>
                                    <a href="{{ route('cars.public.show', $car->slug) }}"
                                       class="w-full min-h-[44px] inline-flex items-center justify-center py-2.5 px-4 rounded-xl bg-blue-50 text-blue-700 hover:bg-blue-100 text-xs font-bold uppercase tracking-wider transition text-center">
                                        Lihat Detail Spesifikasi
                                    </a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <!-- =====================================================================
         SEKSI 3: KATALOG SUKU CADANG & KOMPONEN ORISINAL (ENGINEERING MANIFEST)
         ===================================================================== -->
    <section id="katalog-suku-cadang" class="bg-white border-t border-slate-200/80 py-20 sm:py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
            <!-- Header & Search/Category Controls -->
            <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6 border-b border-slate-200/80 pb-8">
                <div>
                    <h2 class="font-display text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                        Katalog Suku Cadang &amp; Komponen Orisinal
                    </h2>
                    <p class="text-slate-600 text-sm sm:text-base mt-2 max-w-2xl leading-relaxed">
                        Daftar inventaris suku cadang resmi di gudang bengkel kami. Pilih komponen di bawah ini untuk langsung menyertakannya ke dalam formulir reservasi servis kendaraan Anda.
                    </p>
                </div>

                <!-- Search & Category Filter Suku Cadang -->
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                    <div class="relative">
                        <input type="text"
                               id="part_search"
                               name="part_search"
                               x-model="partSearch"
                               placeholder="Cari nama part, merk, atau SKU..."
                               aria-label="Cari suku cadang"
                               class="w-full sm:w-64 min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-200/90 bg-white text-sm text-slate-900 shadow-sm focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 focus:outline-none" />
                    </div>
                    <select id="part_category_filter"
                            name="part_category_filter"
                            x-model="partCategoryFilter"
                            aria-label="Filter kategori suku cadang"
                            class="min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-200/90 bg-white text-sm font-semibold text-slate-800 shadow-sm focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 focus:outline-none">
                        <option value="all">Semua Kategori Komponen</option>
                        @foreach(\App\Enums\SparePartCategory::cases() as $spCat)
                            <option value="{{ $spCat->value }}">{{ $spCat->label() }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Spare Part Cards -->
            @if(!isset($spareParts) || $spareParts->isEmpty())
                <div class="rounded-3xl bg-slate-50 border border-slate-200/80 p-12 text-center shadow-sm">
                    <p class="text-base font-bold text-slate-900">Inventaris suku cadang sedang diperbarui.</p>
                    <p class="text-sm text-slate-600 mt-1">Anda tetap dapat memesan jadwal servis dan berkonsultasi langsung dengan mekanik kami mengenai kebutuhan suku cadang mobil Anda.</p>
                    <button type="button" @click="openServiceModal()" class="mt-5 inline-flex items-center justify-center min-h-[44px] rounded-xl bg-slate-900 text-white px-5 py-2.5 text-xs font-bold uppercase tracking-wider hover:bg-blue-600 transition shadow-sm">
                        Booking Servis Sekarang
                    </button>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($spareParts as $part)
                        @php
                            $partCatVal = $part->category->value ?? (string) $part->category;
                            $compatBrands = is_array($part->car_brand_compatibility) ? implode(', ', $part->car_brand_compatibility) : '';
                            $searchHaystack = strtolower($part->name . ' ' . $part->brand . ' ' . $part->part_number . ' ' . $compatBrands);
                        @endphp
                        <div x-show="(partCategoryFilter === 'all' || partCategoryFilter === @js($partCatVal)) && (partSearch.trim() === '' || @js($searchHaystack).includes(partSearch.toLowerCase().trim()))"
                             class="rounded-3xl border border-slate-200/80 bg-white p-6 flex flex-col justify-between shadow-sm hover:shadow-xl transition-all duration-200">

                            <!-- Top Technical Header: SKU & Stock Status -->
                            <div>
                                <div class="flex items-center justify-between gap-2 pb-4 border-b border-slate-200/80">
                                    <span class="font-mono text-xs font-bold text-slate-600 tabular-nums">
                                        SKU: {{ $part->part_number }}
                                    </span>
                                    <span class="inline-flex items-center rounded-full font-mono text-[11px] font-bold px-2.5 py-0.5 border tabular-nums {{ $part->isLowStock() ? 'bg-amber-50 text-amber-800 border-amber-200' : 'bg-emerald-50 text-emerald-800 border-emerald-200' }}">
                                        STOK: {{ $part->stock_quantity }} UNIT
                                    </span>
                                </div>

                                <!-- Middle: Studio Image + Technical Metadata -->
                                <div class="grid grid-cols-12 gap-4 py-5 items-center">
                                    <div class="col-span-4 aspect-square rounded-2xl bg-slate-50 border border-slate-200/80 p-2.5 flex items-center justify-center">
                                        <img src="{{ $part->imageUrl }}"
                                             alt="{{ $part->name }}"
                                             loading="lazy"
                                             class="w-full h-full object-contain" />
                                    </div>
                                    <div class="col-span-8 space-y-1.5">
                                        <div class="flex items-center gap-2">
                                            <span class="font-mono text-[11px] font-bold uppercase tracking-wider text-blue-600">{{ $part->brand }}</span>
                                            <span class="text-slate-400" aria-hidden="true">/</span>
                                            <span class="text-[11px] font-semibold text-slate-600">{{ $part->category?->label() ?? 'Komponen Umum' }}</span>
                                        </div>
                                        <h3 class="font-display font-extrabold text-base text-slate-900 leading-snug" title="{{ $part->name }}">
                                            {{ $part->name }}
                                        </h3>
                                        <p class="text-xs text-slate-600 pt-0.5">
                                            Kompatibilitas:
                                            <span class="font-semibold text-slate-800">
                                                {{ !empty($part->car_brand_compatibility) ? implode(', ', $part->car_brand_compatibility) : 'Universal Semua Unit' }}
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Bottom: Unit Price & Direct Installation Trigger -->
                            <div class="pt-4 border-t border-slate-200/80 flex items-center justify-between gap-3">
                                <div>
                                    <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-600">Harga Satuan</span>
                                    <span class="font-mono text-base font-extrabold text-slate-900 tabular-nums">{{ $part->price_formatted }}</span>
                                </div>
                                <button type="button"
                                        @click="openServiceWithPart(@js($part->name), @js($part->part_number), @js($part->car_brand_compatibility[0] ?? ''))"
                                        class="min-h-[44px] px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-blue-600 text-white text-xs font-bold uppercase tracking-wider transition-all duration-200 shrink-0 shadow-sm hover:scale-[1.02] active:scale-[0.98]">
                                    Pesan &amp; Pasang
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <!-- =====================================================================
         SEKSI 4: STANDAR PENGERJAAN BENGKEL RESMI & ALUR STATUS TIKET
         ===================================================================== -->
    <section id="layanan-bengkel" class="bg-slate-100/80 border-t border-slate-200/80 py-20 sm:py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-12 items-start">
                <!-- Left Column (7 Cols): Service Scope Schedule Cards -->
                <div class="lg:col-span-7 space-y-6">
                    <div>
                        <h2 class="font-display text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900">
                            Standar Pengerjaan Bengkel Resmi Dealer Pro
                        </h2>
                        <p class="text-slate-600 text-sm sm:text-base mt-2 max-w-2xl leading-relaxed">
                            Pilih cakupan layanan pemeliharaan kendaraan Anda. Sistem kami memvalidasi kuota slot per jam secara otomatis agar kendaraan langsung ditangani mekanik di stall bengkel tanpa waktu tunggu berlebih.
                        </p>
                    </div>

                    <div class="rounded-3xl bg-white border border-slate-200/80 divide-y divide-slate-200/80 shadow-sm overflow-hidden">
                        <!-- Layanan 01 -->
                        <div class="p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="space-y-1.5 max-w-xl">
                                <div class="flex items-center gap-2.5">
                                    <span class="font-mono text-xs font-bold px-2 py-0.5 rounded-lg bg-slate-100 text-slate-700">01</span>
                                    <h3 class="font-display text-base font-extrabold text-slate-900">Servis Rutin / Berkala (Periodic Maintenance)</h3>
                                </div>
                                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                                    Penggantian oli mesin sintetis, filter oli, filter udara kabin, inspeksi ketebalan kampas rem, fluida pendingin radiator, dan rotasi ban sesuai buku servis.
                                </p>
                            </div>
                            <button type="button"
                                    @click="openServiceWithType('regular')"
                                    class="shrink-0 min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-900 text-slate-900 hover:bg-slate-900 hover:text-white text-xs font-bold uppercase tracking-wider transition">
                                Pilih Berkala
                            </button>
                        </div>

                        <!-- Layanan 02 -->
                        <div class="p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="space-y-1.5 max-w-xl">
                                <div class="flex items-center gap-2.5">
                                    <span class="font-mono text-xs font-bold px-2 py-0.5 rounded-lg bg-slate-100 text-slate-700">02</span>
                                    <h3 class="font-display text-base font-extrabold text-slate-900">Servis Besar (Major Overhaul &amp; Drivetrain)</h3>
                                </div>
                                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                                    Perawatan komprehensif meliputi penggantian sabuk timing belt, kuras penuh fluida transmisi otomatis (ATF), kalibrasi suspensi, dan penggantian busi iridium.
                                </p>
                            </div>
                            <button type="button"
                                    @click="openServiceWithType('major')"
                                    class="shrink-0 min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-900 text-slate-900 hover:bg-slate-900 hover:text-white text-xs font-bold uppercase tracking-wider transition">
                                Pilih Servis Besar
                            </button>
                        </div>

                        <!-- Layanan 03 -->
                        <div class="p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="space-y-1.5 max-w-xl">
                                <div class="flex items-center gap-2.5">
                                    <span class="font-mono text-xs font-bold px-2 py-0.5 rounded-lg bg-slate-100 text-slate-700">03</span>
                                    <h3 class="font-display text-base font-extrabold text-slate-900">Inspeksi &amp; Diagnosa Komputer (OBD / EV Telemetry)</h3>
                                </div>
                                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                                    Pemindaian modul ECU menyeluruh, evaluasi kesehatan sel baterai tegangan tinggi kendaraan listrik (EV), serta pemeriksaan sistem kelistrikan dan kaki-kaki.
                                </p>
                            </div>
                            <button type="button"
                                    @click="openServiceWithType('inspection')"
                                    class="shrink-0 min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-900 text-slate-900 hover:bg-slate-900 hover:text-white text-xs font-bold uppercase tracking-wider transition">
                                Pilih Diagnosa
                            </button>
                        </div>

                        <!-- Layanan 04 -->
                        <div class="p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="space-y-1.5 max-w-xl">
                                <div class="flex items-center gap-2.5">
                                    <span class="font-mono text-xs font-bold px-2 py-0.5 rounded-lg bg-slate-100 text-slate-700">04</span>
                                    <h3 class="font-display text-base font-extrabold text-slate-900">Klaim Garansi Resmi Pabrik</h3>
                                </div>
                                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                                    Penanganan pemeriksaan dan penggantian suku cadang yang masih berada dalam cakupan masa garansi resmi dengan pencatatan riwayat terverifikasi.
                                </p>
                            </div>
                            <button type="button"
                                    @click="openServiceWithType('warranty')"
                                    class="shrink-0 min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-900 text-slate-900 hover:bg-slate-900 hover:text-white text-xs font-bold uppercase tracking-wider transition">
                                Ajukan Klaim
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Right Column (5 Cols): Workshop Protocol & Ticket Status Card -->
                <div class="lg:col-span-5 rounded-3xl bg-white border border-slate-200/80 shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-slate-200/80 flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <span class="font-mono text-[11px] font-bold uppercase tracking-widest text-slate-600">Protokol Tiket Servis</span>
                            <h3 class="font-display text-lg font-extrabold text-slate-900 mt-0.5">Alur Pelacakan Status Kendaraan</h3>
                        </div>
                        <span class="rounded-full font-mono text-xs font-bold px-3 py-1 bg-slate-100 text-slate-800 border border-slate-200">
                            SRV-YYYYMMDD-XXXX
                        </span>
                    </div>

                    <ol class="divide-y divide-slate-200/80 text-sm">
                        <li class="p-5 flex items-start gap-4">
                            <span class="rounded-lg font-mono text-xs font-bold px-2.5 py-1 bg-slate-100 text-slate-900 border border-slate-200 shrink-0 tabular-nums">TAHAP 01</span>
                            <div>
                                <p class="font-bold text-slate-900">Pending (Penerbitan Tiket &amp; Kunci Kuota)</p>
                                <p class="text-xs text-slate-600 mt-1 leading-relaxed">Pelanggan menentukan tanggal dan jam kedatangan. Sistem menerbitkan nomor tiket unik dan mengunci 1 dari 3 kuota stall pada jam tersebut.</p>
                            </div>
                        </li>
                        <li class="p-5 flex items-start gap-4">
                            <span class="rounded-lg font-mono text-xs font-bold px-2.5 py-1 bg-slate-100 text-slate-900 border border-slate-200 shrink-0 tabular-nums">TAHAP 02</span>
                            <div>
                                <p class="font-bold text-slate-900">Confirmed (Penugasan Mekanik Tersertifikasi)</p>
                                <p class="text-xs text-slate-600 mt-1 leading-relaxed">Service Advisor memverifikasi keluhan kendaraan dan menugaskan mekanik spesifik yang bertanggung jawab menangani unit Anda.</p>
                            </div>
                        </li>
                        <li class="p-5 flex items-start gap-4">
                            <span class="rounded-lg font-mono text-xs font-bold px-2.5 py-1 bg-blue-50 text-blue-800 border border-blue-200 shrink-0 tabular-nums">TAHAP 03</span>
                            <div>
                                <p class="font-bold text-slate-900">In Progress (Pengerjaan &amp; Rincian Suku Cadang)</p>
                                <p class="text-xs text-slate-600 mt-1 leading-relaxed">Kendaraan masuk stall bengkel. Seluruh suku cadang dan biaya jasa yang digunakan mekanik tercatat langsung secara transparan pada lembar kerja.</p>
                            </div>
                        </li>
                        <li class="p-5 flex items-start gap-4">
                            <span class="rounded-lg font-mono text-xs font-bold px-2.5 py-1 bg-emerald-50 text-emerald-800 border border-emerald-200 shrink-0 tabular-nums">TAHAP 04</span>
                            <div>
                                <p class="font-bold text-slate-900">Completed (Inspeksi Akhir &amp; Serah Terima)</p>
                                <p class="text-xs text-slate-600 mt-1 leading-relaxed">Pekerjaan selesai diverifikasi, rincian biaya terhitung akurat, dan pelanggan menerima notifikasi bahwa kendaraan siap diambil.</p>
                            </div>
                        </li>
                    </ol>

                    <div class="p-6 bg-slate-50/90 border-t border-slate-200/80 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
                        <div class="text-xs text-slate-600">
                            <span class="block font-bold text-slate-900 uppercase tracking-wider">Jam Operasional Bengkel</span>
                            <span class="font-mono tabular-nums">Senin - Sabtu / 08:00 - 17:00 WIB</span>
                        </div>
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5">
                            <a href="#lacak-tiket"
                               class="min-h-[44px] inline-flex items-center justify-center gap-1.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-900 text-slate-800 hover:text-white hover:border-slate-900 px-4 py-3 text-xs font-bold uppercase tracking-wider transition-all duration-200 text-center">
                                <span>Lacak Status Tiket</span>
                            </a>
                            <button type="button"
                                    @click="openServiceModal()"
                                    class="min-h-[44px] rounded-xl bg-blue-600 hover:bg-slate-900 text-white px-5 py-3 text-xs font-bold uppercase tracking-wider transition-all duration-200 text-center shadow-sm hover:scale-[1.02] active:scale-[0.98]">
                                Jadwalkan Servis
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel Lacak Status Booking & Progres Servis (Tanpa Login) -->
            <div id="lacak-tiket" class="scroll-mt-24 rounded-3xl border border-slate-200/80 bg-white shadow-sm p-6 sm:p-8">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                    <div class="max-w-xl">
                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-50 border border-blue-200/80 text-[11px] font-bold uppercase tracking-wider text-blue-700">
                            Live Ticket Tracker
                        </span>
                        <h3 class="mt-2 font-display text-xl sm:text-2xl font-bold text-slate-900">
                            Cek Status Booking Test Drive &amp; Progres Servis
                        </h3>
                        <p class="mt-1 text-sm text-slate-600">
                            Masukkan nomor tiket yang Anda terima saat reservasi (contoh: <span class="font-mono font-semibold text-slate-800">SRV-20261115-0001</span> atau <span class="font-mono font-semibold text-slate-800">TD-20261115-0001</span>) untuk memantau status terkini secara langsung.
                        </p>
                    </div>

                    <form @submit.prevent="lookupTicket()" class="w-full lg:w-auto flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                        <label for="ticket_lookup_input" class="sr-only">Nomor Tiket Reservasi</label>
                        <input id="ticket_lookup_input"
                               name="ticket"
                               type="text"
                               x-model="ticketQuery"
                               @input="ticketQuery = $event.target.value.toUpperCase()"
                               placeholder="Masukkan Nomor Tiket (SRV-... / TD-...)"
                               class="w-full sm:w-80 min-h-[48px] px-4 py-3 rounded-xl border border-slate-300 bg-slate-50/70 focus:bg-white text-slate-900 font-mono text-sm uppercase tracking-wider focus:outline-none focus:ring-2 focus:ring-blue-600/20 focus:border-blue-600 transition">
                        <button type="submit"
                                :disabled="ticketLoading"
                                class="min-h-[48px] px-6 py-3 rounded-xl bg-slate-900 hover:bg-blue-600 text-white text-xs sm:text-sm font-bold uppercase tracking-wider transition-all duration-200 shadow-sm hover:scale-[1.02] active:scale-[0.98] disabled:opacity-50 shrink-0">
                            <span x-show="!ticketLoading">Cek Status Tiket</span>
                            <span x-show="ticketLoading" x-cloak>Mencari...</span>
                        </button>
                    </form>
                </div>

                <!-- Pesan Error Pencarian Tiket -->
                <div x-show="ticketError" x-cloak class="mt-6 rounded-2xl border border-red-200 bg-red-50/90 p-4 text-sm text-red-800 flex items-center justify-between gap-4">
                    <span x-text="ticketError"></span>
                    <button type="button" @click="ticketError = ''" class="text-xs font-bold uppercase tracking-wider text-red-700 hover:underline">Tutup</button>
                </div>

                <!-- Hasil Pelacakan Tiket -->
                <div x-show="ticketResult" x-cloak class="mt-6 rounded-2xl border border-slate-200/90 bg-slate-50/70 p-5 sm:p-6 space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200/80">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="px-2.5 py-1 rounded-lg bg-slate-900 text-white font-mono text-xs font-bold tracking-wider" x-text="ticketResult?.ticket_number"></span>
                                <span class="px-2.5 py-1 rounded-lg bg-blue-100 text-blue-800 text-xs font-bold uppercase tracking-wider"
                                      x-text="ticketResult?.type === 'service' ? 'Tiket Servis Bengkel' : 'Tiket Test Drive'"></span>
                                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-emerald-100 text-emerald-800"
                                      x-text="ticketResult?.status_label"></span>
                            </div>
                            <p class="mt-2 text-sm text-slate-700">
                                Pemesan: <strong class="text-slate-900" x-text="ticketResult?.customer_name"></strong>
                            </p>
                        </div>
                        <div class="w-full sm:w-56">
                            <div class="flex items-center justify-between text-xs font-bold text-slate-700 mb-1.5">
                                <span>Progres Tahapan</span>
                                <span class="font-mono" x-text="(ticketResult?.progress_percent ?? 0) + '%'"></span>
                            </div>
                            <div class="w-full h-2.5 rounded-full bg-slate-200 overflow-hidden">
                                <div class="h-full bg-blue-600 rounded-full transition-all duration-500"
                                     :style="'width: ' + (ticketResult?.progress_percent ?? 0) + '%'"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Detail Untuk Tiket Servis -->
                    <template x-if="ticketResult && ticketResult.type === 'service'">
                        <div class="space-y-5">
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                                <div class="p-4 rounded-xl bg-white border border-slate-200/80">
                                    <span class="block text-[11px] font-bold uppercase tracking-wider text-slate-500">Kendaraan &amp; Plat</span>
                                    <span class="mt-1 block text-sm font-bold text-slate-900" x-text="ticketResult.vehicle_model"></span>
                                    <span class="mt-0.5 inline-block font-mono text-xs font-semibold text-blue-700" x-text="ticketResult.vehicle_plate_number"></span>
                                </div>
                                <div class="p-4 rounded-xl bg-white border border-slate-200/80">
                                    <span class="block text-[11px] font-bold uppercase tracking-wider text-slate-500">Jadwal &amp; Kategori</span>
                                    <span class="mt-1 block text-sm font-bold text-slate-900" x-text="ticketResult.service_date_formatted"></span>
                                    <span class="mt-0.5 block text-xs text-slate-600" x-text="ticketResult.service_type_label"></span>
                                </div>
                                <div class="p-4 rounded-xl bg-white border border-slate-200/80">
                                    <span class="block text-[11px] font-bold uppercase tracking-wider text-slate-500">Mekanik Bertugas</span>
                                    <span class="mt-1 block text-sm font-bold text-slate-900" x-text="ticketResult.mechanic_name"></span>
                                </div>
                                <div class="p-4 rounded-xl bg-white border border-slate-200/80">
                                    <span class="block text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Biaya Saat Ini</span>
                                    <span class="mt-1 block font-mono text-base font-extrabold text-slate-900" x-text="ticketResult.total_cost_formatted"></span>
                                </div>
                            </div>

                            <template x-if="ticketResult.mechanic_notes || ticketResult.recommendation || ticketResult.cancellation_reason">
                                <div class="p-4 rounded-xl bg-blue-50/70 border border-blue-200/80 text-xs text-slate-700 space-y-1.5">
                                    <template x-if="ticketResult.mechanic_notes">
                                        <p><strong class="text-slate-900">Catatan Pemeriksaan Mekanik:</strong> <span x-text="ticketResult.mechanic_notes"></span></p>
                                    </template>
                                    <template x-if="ticketResult.recommendation">
                                        <p><strong class="text-slate-900">Rekomendasi Tindak Lanjut:</strong> <span x-text="ticketResult.recommendation"></span></p>
                                    </template>
                                    <template x-if="ticketResult.cancellation_reason">
                                        <p class="text-red-700"><strong>Alasan Pembatalan:</strong> <span x-text="ticketResult.cancellation_reason"></span></p>
                                    </template>
                                </div>
                            </template>

                            <template x-if="ticketResult.items && ticketResult.items.length > 0">
                                <div class="rounded-xl bg-white border border-slate-200/80 p-4">
                                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-3">Rincian Suku Cadang &amp; Jasa Pengerjaan</h4>
                                    <div class="divide-y divide-slate-200/70 text-xs">
                                        <template x-for="(item, idx) in ticketResult.items" :key="idx">
                                            <div class="py-2 flex items-center justify-between gap-4">
                                                <div>
                                                    <span class="font-semibold text-slate-900" x-text="item.name"></span>
                                                    <span class="text-slate-500 ml-1" x-text="'(x' + item.quantity + ')'"></span>
                                                </div>
                                                <span class="font-mono font-bold text-slate-900" x-text="item.subtotal_formatted"></span>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>

                            <template x-if="ticketResult.logs && ticketResult.logs.length > 0">
                                <div class="rounded-xl bg-white border border-slate-200/80 p-4">
                                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-3">Riwayat Pembaruan Status Bengkel</h4>
                                    <div class="space-y-2 text-xs">
                                        <template x-for="(log, idx) in ticketResult.logs" :key="idx">
                                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 py-1.5 border-b border-slate-100 last:border-0">
                                                <div>
                                                    <span class="font-bold text-slate-900" x-text="log.to_status_label"></span>
                                                    <span x-show="log.notes" class="text-slate-600 ml-1" x-text="'(' + log.notes + ')'"></span>
                                                </div>
                                                <span class="font-mono text-slate-500" x-text="log.created_at"></span>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>

                    <!-- Detail Untuk Tiket Test Drive -->
                    <template x-if="ticketResult && ticketResult.type === 'test_drive'">
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div class="p-4 rounded-xl bg-white border border-slate-200/80">
                                    <span class="block text-[11px] font-bold uppercase tracking-wider text-slate-500">Unit Kendaraan</span>
                                    <span class="mt-1 block text-sm font-bold text-slate-900" x-text="ticketResult.car_name"></span>
                                </div>
                                <div class="p-4 rounded-xl bg-white border border-slate-200/80">
                                    <span class="block text-[11px] font-bold uppercase tracking-wider text-slate-500">Jadwal &amp; Lokasi Sesi</span>
                                    <span class="mt-1 block text-sm font-bold text-slate-900" x-text="ticketResult.preferred_date_formatted"></span>
                                    <span class="mt-0.5 block text-xs text-slate-600" x-text="ticketResult.pickup_location"></span>
                                </div>
                                <div class="p-4 rounded-xl bg-white border border-slate-200/80">
                                    <span class="block text-[11px] font-bold uppercase tracking-wider text-slate-500">Konsultan Sales Pendamping</span>
                                    <span class="mt-1 block text-sm font-bold text-slate-900" x-text="ticketResult.sales_name"></span>
                                    <span x-show="ticketResult.sales_phone" class="mt-0.5 block font-mono text-xs text-blue-700" x-text="ticketResult.sales_phone"></span>
                                </div>
                            </div>

                            <template x-if="ticketResult.sales_notes || ticketResult.rejection_reason">
                                <div class="p-4 rounded-xl bg-blue-50/70 border border-blue-200/80 text-xs text-slate-700 space-y-1.5">
                                    <template x-if="ticketResult.sales_notes">
                                        <p><strong class="text-slate-900">Pesan / Catatan Konsultan Sales:</strong> <span x-text="ticketResult.sales_notes"></span></p>
                                    </template>
                                    <template x-if="ticketResult.rejection_reason">
                                        <p class="text-red-700"><strong>Keterangan Penolakan / Penjadwalan Ulang:</strong> <span x-text="ticketResult.rejection_reason"></span></p>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </section>

    <!-- =====================================================================
         SEKSI 5: PROGRAM PROMO & PENAWARAN RESMI
         ===================================================================== -->
    <section id="promo" class="bg-white border-t border-slate-200/80 py-16 sm:py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 border-b border-slate-200/80 pb-6">
                <div>
                    <h2 class="font-display text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                        Program Penawaran &amp; Voucher Resmi
                    </h2>
                    <p class="text-slate-600 text-sm mt-1">
                        Gunakan kode program di bawah ini saat melakukan konfirmasi pengajuan Test Drive atau reservasi perawatan berkala.
                    </p>
                </div>
            </div>

            @if(!isset($promos) || $promos->isEmpty())
                <div class="rounded-3xl bg-slate-50 border border-slate-200/80 p-8 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-sm">
                    <div>
                        <h3 class="font-display text-base font-bold text-slate-900">Program penawaran khusus bulan ini tersedia langsung melalui konsultan Sales kami.</h3>
                        <p class="text-xs sm:text-sm text-slate-600 mt-1">Ajukan jadwal Test Drive untuk mendapatkan simulasi pembiayaan dan paket purnajual resmi.</p>
                    </div>
                    <button type="button"
                            @click="openTestDriveModal(null, '')"
                            class="shrink-0 min-h-[44px] rounded-xl bg-slate-900 text-white px-5 py-3 text-xs font-bold uppercase tracking-wider hover:bg-blue-600 transition shadow-sm">
                        Jadwalkan Test Drive
                    </button>
                </div>
            @else
                <div class="space-y-4">
                    @foreach($promos as $promo)
                        <div class="rounded-3xl border border-slate-200/80 bg-white p-6 grid grid-cols-1 md:grid-cols-12 gap-6 items-center shadow-sm hover:shadow-md transition-all">
                            <div class="md:col-span-3 flex flex-col">
                                <span class="text-[11px] font-bold uppercase tracking-widest text-slate-600">Kode Voucher Resmi</span>
                                <span class="font-mono text-base font-extrabold text-slate-900 mt-1">{{ $promo->code }}</span>
                                <span class="inline-flex items-center self-start rounded-full bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 font-mono text-xs font-bold text-emerald-800 mt-2 tabular-nums">
                                    @if(($promo->discount_type->value ?? $promo->discount_type) === 'percentage')
                                        Potongan {{ (float) $promo->discount_value }}%
                                    @else
                                        Potongan Rp {{ number_format((float) $promo->discount_value, 0, ',', '.') }}
                                    @endif
                                </span>
                            </div>

                            <div class="md:col-span-6 space-y-1">
                                <h3 class="font-display text-base font-extrabold text-slate-900">{{ $promo->title }}</h3>
                                @if($promo->car)
                                    <p class="text-xs font-bold text-blue-600">
                                        Unit Berlaku: {{ $promo->car->brand }} {{ $promo->car->model_name }}
                                    </p>
                                @endif
                                @if($promo->description)
                                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">{{ $promo->description }}</p>
                                @endif
                            </div>

                            <div class="md:col-span-3 flex md:flex-col items-center md:items-end justify-between gap-2.5">
                                <span class="font-mono text-xs text-slate-600 tabular-nums">
                                    Berlaku s/d {{ $promo->ends_at ? $promo->ends_at->format('d M Y') : '-' }}
                                </span>
                                <button type="button"
                                        @click="copyPromoCode(@js($promo->code))"
                                        class="min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-900 text-slate-900 hover:bg-slate-900 hover:text-white text-xs font-bold uppercase tracking-wider transition">
                                    Salin &amp; Pakai Kode
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <x-public-footer :on-showroom="true" />

    <!-- Layer 40: Toast Notifications Container -->
    <div class="fixed top-24 right-4 sm:right-6 z-40 space-y-3 max-w-sm w-full pointer-events-none"
         role="status"
         aria-live="polite">
        <template x-for="toast in toasts" :key="toast.id">
            <div x-show="toast.visible"
                 x-transition:enter="transition ease-out duration-300 transform"
                 x-transition:enter-start="opacity-0 translate-x-8"
                 x-transition:enter-end="opacity-100 translate-x-0"
                 x-transition:leave="transition ease-in duration-200 transform"
                 x-transition:leave-start="opacity-100 translate-x-0"
                 x-transition:leave-end="opacity-0 translate-x-8"
                 class="pointer-events-auto p-4 rounded-2xl shadow-lg border text-sm flex items-start gap-3"
                 :class="{
                     'bg-emerald-50 border-emerald-200 text-emerald-900': toast.type === 'success',
                     'bg-red-50 border-red-200 text-red-900': toast.type === 'error',
                     'bg-blue-50 border-blue-200 text-blue-900': toast.type === 'info'
                 }">
                <div class="shrink-0 mt-0.5">
                    <template x-if="toast.type === 'success'">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </template>
                    <template x-if="toast.type === 'error'">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </template>
                    <template x-if="toast.type === 'info'">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </template>
                </div>
                <div class="flex-1">
                    <p class="font-medium" x-text="toast.message"></p>
                </div>
                <button type="button" @click="dismissToast(toast.id)" class="min-w-[44px] min-h-[44px] -my-2 -mr-2 flex items-center justify-center rounded-xl text-slate-600 hover:text-slate-900 hover:bg-black/5 transition">
                    <span class="sr-only">Tutup</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </template>
    </div>

    <!-- Layer 50 (z-50): Modal A - Test Drive -->
    <div x-show="testDriveModalOpen"
         x-cloak
         role="dialog"
         aria-modal="true"
         aria-labelledby="test-drive-title"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
         @keydown.escape.window="closeModals()">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity"
             x-show="testDriveModalOpen"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="closeModals()"></div>

        <!-- Modal Dialog Panel -->
        <div class="relative w-full max-w-lg rounded-3xl bg-white border border-slate-200/80 shadow-2xl p-6 sm:p-8 max-h-[90vh] overflow-y-auto transform transition-all duration-300"
             x-show="testDriveModalOpen"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">

            <div class="flex items-center justify-between pb-4 border-b border-slate-200/80">
                <div>
                    <h2 id="test-drive-title" class="font-display text-xl font-bold text-slate-900">Jadwalkan Test Drive</h2>
                    <p class="text-xs text-slate-600 mt-0.5" x-text="selectedCarName ? 'Unit: ' + selectedCarName : 'Pilih unit mobil yang Anda minati'"></p>
                </div>
                <button type="button" @click="closeModals()" class="min-w-[44px] min-h-[44px] flex items-center justify-center rounded-xl text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition" aria-label="Tutup modal">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            @guest
                <div class="mt-4 px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200/80 text-xs text-slate-600 flex items-center justify-between gap-2">
                    <span>Sudah punya nomor tiket pengajuan?</span>
                    <button type="button" @click="closeModals(); scrollToTracker()" class="font-bold text-blue-700 hover:underline shrink-0">Lacak dengan Nomor Tiket &rarr;</button>
                </div>
            @endguest

            <form action="{{ route('test-drive.store') }}" method="POST" @submit.prevent="submitTestDrive" class="mt-6 space-y-4" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="car_id" :value="testDriveForm.car_id">

                @guest
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label for="td_customer_name" class="block text-sm font-semibold text-slate-700">Nama Lengkap <span class="text-red-600">*</span></label>
                            <input type="text"
                                   id="td_customer_name"
                                   name="customer_name"
                                   x-model="testDriveForm.customer_name"
                                   placeholder="Nama sesuai KTP / SIM"
                                   required
                                   class="w-full min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-200/90 bg-white text-slate-900 shadow-sm focus:ring-2 focus:ring-blue-600/20 focus:border-blue-600 focus:outline-none text-sm transition">
                        </div>
                        <div class="space-y-1.5">
                            <label for="td_customer_phone" class="block text-sm font-semibold text-slate-700">Nomor WhatsApp / HP <span class="text-red-600">*</span></label>
                            <input type="tel"
                                   id="td_customer_phone"
                                   name="customer_phone"
                                   x-model="testDriveForm.customer_phone"
                                   placeholder="Contoh: 081234567890"
                                   required
                                   class="w-full min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-200/90 bg-white text-slate-900 shadow-sm focus:ring-2 focus:ring-blue-600/20 focus:border-blue-600 focus:outline-none text-sm transition">
                        </div>
                    </div>
                @endguest

                <!-- Pilihan Mobil jika belum terpilih -->
                <div x-show="!testDriveForm.car_id" class="space-y-1.5">
                    <label for="td_select_car" class="block text-sm font-semibold text-slate-700">Pilih Model Mobil <span class="text-red-600">*</span></label>
                    <select id="td_select_car" x-model="testDriveForm.car_id" class="w-full min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-200/90 bg-white text-slate-900 shadow-sm focus:ring-2 focus:ring-blue-600/20 focus:border-blue-600 focus:outline-none text-sm">
                        <option value="">-- Pilih Mobil --</option>
                        @foreach ($cars as $car)
                            <option value="{{ $car->id }}">{{ $car->brand }} {{ $car->model_name }} ({{ $car->year }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Tanggal & Jam Test Drive -->
                <div class="space-y-1.5">
                    <label for="td_preferred_date" class="block text-sm font-semibold text-slate-700">Tanggal &amp; Waktu Preferensi <span class="text-red-600">*</span></label>
                    <input type="datetime-local"
                           id="td_preferred_date"
                           name="preferred_date"
                           x-model="testDriveForm.preferred_date"
                           :min="minDate"
                           required
                           class="w-full min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-200/90 bg-white text-slate-900 shadow-sm focus:ring-2 focus:ring-blue-600/20 focus:border-blue-600 focus:outline-none text-sm transition">
                </div>

                <!-- Nomor SIM -->
                <div class="space-y-1.5">
                    <label for="td_license_id" class="block text-sm font-semibold text-slate-700">Nomor SIM A <span class="text-red-600">*</span></label>
                    <input type="text"
                           id="td_license_id"
                           name="driver_license_id"
                           x-model="testDriveForm.driver_license_id"
                           placeholder="Contoh: 1234-5678-9012"
                           minlength="5"
                           required
                           class="w-full min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-200/90 bg-white text-slate-900 shadow-sm focus:ring-2 focus:ring-blue-600/20 focus:border-blue-600 focus:outline-none text-sm transition">
                </div>

                <!-- Foto SIM (Opsional) -->
                <div class="space-y-1.5">
                    <label for="td_license_image" class="block text-sm font-semibold text-slate-700">Unggah Foto SIM (Opsional)</label>
                    <input type="file"
                           id="td_license_image"
                           name="driver_license_image"
                           @change="testDriveForm.driver_license_image = $event.target.files[0]"
                           accept="image/*"
                           class="w-full text-xs text-slate-600 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-900 file:text-white hover:file:bg-blue-600 transition cursor-pointer">
                    <p class="text-[11px] text-slate-600">Format: JPG, PNG, WebP. Maks 5MB.</p>
                </div>

                <!-- Lokasi Penjemputan / Dealer -->
                <div class="space-y-1.5">
                    <label for="td_pickup_location" class="block text-sm font-semibold text-slate-700">Lokasi Pertemuan (Opsional)</label>
                    <input type="text"
                           id="td_pickup_location"
                           name="pickup_location"
                           x-model="testDriveForm.pickup_location"
                           placeholder="Showroom Dealer Pro Pusat / Alamat Rumah Anda"
                           class="w-full min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-200/90 bg-white text-slate-900 shadow-sm focus:ring-2 focus:ring-blue-600/20 focus:border-blue-600 focus:outline-none text-sm transition">
                </div>

                <!-- Kode Promo (Opsional) -->
                <div class="space-y-1.5">
                    <label for="td_promo_code" class="block text-sm font-semibold text-slate-700">Kode Promo / Voucher (Opsional)</label>
                    <input type="text"
                           id="td_promo_code"
                           name="promo_code"
                           x-model="promoCode"
                           placeholder="Otomatis terisi saat menyalin voucher"
                           class="w-full min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-200/90 bg-white font-mono uppercase text-slate-900 shadow-sm focus:ring-2 focus:ring-blue-600/20 focus:border-blue-600 focus:outline-none text-sm transition">
                </div>

                <!-- Catatan Tambahan -->
                <div class="space-y-1.5">
                    <label for="td_notes" class="block text-sm font-semibold text-slate-700">Catatan Tambahan (Opsional)</label>
                    <textarea id="td_notes"
                              name="notes"
                              x-model="testDriveForm.notes"
                              rows="2"
                              placeholder="Kebutuhan khusus atau preferensi saat sesi test drive"
                              class="w-full px-4 py-2.5 rounded-xl border border-slate-200/90 bg-white text-slate-900 shadow-sm focus:ring-2 focus:ring-blue-600/20 focus:border-blue-600 focus:outline-none text-sm transition"></textarea>
                </div>

                <!-- Submit Button -->
                <div class="pt-4 flex items-center justify-end gap-3">
                    <button type="button" @click="closeModals()" class="min-h-[44px] px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 font-semibold hover:bg-slate-50 text-sm transition">
                        Batal
                    </button>
                    <button type="submit"
                            :disabled="isSubmitting"
                            class="min-h-[44px] bg-slate-900 text-white px-6 py-2.5 rounded-xl font-semibold hover:bg-blue-600 hover:scale-[1.02] active:scale-[0.98] text-sm transition-all duration-200 flex items-center gap-2 disabled:opacity-50 shadow-sm">
                        <span x-show="!isSubmitting">Kirim Pengajuan</span>
                        <span x-show="isSubmitting" x-cloak>Memproses...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Layer 50 (z-50): Modal B - Booking Servis -->
    <div x-show="serviceModalOpen"
         x-cloak
         role="dialog"
         aria-modal="true"
         aria-labelledby="service-booking-title"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
         @keydown.escape.window="closeModals()">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity"
             x-show="serviceModalOpen"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="closeModals()"></div>

        <!-- Modal Dialog Panel -->
        <div class="relative w-full max-w-lg rounded-3xl bg-white border border-slate-200/80 shadow-2xl p-6 sm:p-8 max-h-[90vh] overflow-y-auto transform transition-all duration-300"
             x-show="serviceModalOpen"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">

            <div class="flex items-center justify-between pb-4 border-b border-slate-200/80">
                <div>
                    <h2 id="service-booking-title" class="font-display text-xl font-bold text-slate-900">Booking Servis Kendaraan</h2>
                    <p class="text-xs text-slate-600 mt-0.5">Layanan bengkel resmi dengan mekanik tersertifikasi</p>
                </div>
                <button type="button" @click="closeModals()" class="min-w-[44px] min-h-[44px] flex items-center justify-center rounded-xl text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition" aria-label="Tutup modal">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            @guest
                <div class="mt-4 px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200/80 text-xs text-slate-600 flex items-center justify-between gap-2">
                    <span>Sudah punya nomor tiket servis?</span>
                    <button type="button" @click="closeModals(); scrollToTracker()" class="font-bold text-blue-700 hover:underline shrink-0">Lacak dengan Nomor Tiket &rarr;</button>
                </div>
            @endguest

            <form action="{{ route('service-booking.store') }}" method="POST" @submit.prevent="submitServiceBooking" class="mt-6 space-y-4">
                @csrf

                @guest
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label for="srv_customer_name" class="block text-sm font-semibold text-slate-700">Nama Lengkap Pemilik <span class="text-red-600">*</span></label>
                            <input type="text"
                                   id="srv_customer_name"
                                   name="customer_name"
                                   x-model="serviceForm.customer_name"
                                   placeholder="Nama lengkap Anda"
                                   required
                                   class="w-full min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-200/90 bg-white text-slate-900 shadow-sm focus:ring-2 focus:ring-blue-600/20 focus:border-blue-600 focus:outline-none text-sm transition">
                        </div>
                        <div class="space-y-1.5">
                            <label for="srv_customer_phone" class="block text-sm font-semibold text-slate-700">Nomor WhatsApp / HP <span class="text-red-600">*</span></label>
                            <input type="tel"
                                   id="srv_customer_phone"
                                   name="customer_phone"
                                   x-model="serviceForm.customer_phone"
                                   placeholder="Contoh: 081234567890"
                                   required
                                   class="w-full min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-200/90 bg-white text-slate-900 shadow-sm focus:ring-2 focus:ring-blue-600/20 focus:border-blue-600 focus:outline-none text-sm transition">
                        </div>
                    </div>
                @endguest

                <!-- Nomor Plat Kendaraan -->
                <div class="space-y-1.5">
                    <label for="srv_plate" class="block text-sm font-semibold text-slate-700">Nomor Plat Kendaraan <span class="text-red-600">*</span></label>
                    <input type="text"
                           id="srv_plate"
                           name="vehicle_plate_number"
                           x-model="serviceForm.vehicle_plate_number"
                           @input="serviceForm.vehicle_plate_number = $event.target.value.toUpperCase()"
                           placeholder="Contoh: B 1234 XYZ"
                           required
                           class="w-full min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-200/90 bg-white text-slate-900 shadow-sm focus:ring-2 focus:ring-blue-600/20 focus:border-blue-600 focus:outline-none text-sm uppercase font-mono transition">
                    <p class="text-[11px] text-slate-600">Gunakan format plat Indonesia (contoh: B 1234 ABC).</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Model Kendaraan -->
                    <div class="space-y-1.5">
                        <label for="srv_model" class="block text-sm font-semibold text-slate-700">Model / Tipe <span class="text-red-600">*</span></label>
                        <input type="text"
                               id="srv_model"
                               name="vehicle_model"
                               x-model="serviceForm.vehicle_model"
                               placeholder="Contoh: Mustang GT"
                               required
                               class="w-full min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-200/90 bg-white text-slate-900 shadow-sm focus:ring-2 focus:ring-blue-600/20 focus:border-blue-600 focus:outline-none text-sm transition">
                    </div>

                    <!-- Tahun Pembuatan -->
                    <div class="space-y-1.5">
                        <label for="srv_year" class="block text-sm font-semibold text-slate-700">Tahun <span class="text-red-600">*</span></label>
                        <input type="number"
                               id="srv_year"
                               name="vehicle_year"
                               x-model="serviceForm.vehicle_year"
                               min="1990"
                               max="{{ date('Y') + 1 }}"
                               required
                               class="w-full min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-200/90 bg-white text-slate-900 shadow-sm focus:ring-2 focus:ring-blue-600/20 focus:border-blue-600 focus:outline-none text-sm transition">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Odometer KM -->
                    <div class="space-y-1.5">
                        <label for="srv_odometer" class="block text-sm font-semibold text-slate-700">Odometer (KM) <span class="text-red-600">*</span></label>
                        <input type="number"
                               id="srv_odometer"
                               name="odometer_km"
                               x-model="serviceForm.odometer_km"
                               min="0"
                               placeholder="15000"
                               required
                               class="w-full min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-200/90 bg-white text-slate-900 shadow-sm focus:ring-2 focus:ring-blue-600/20 focus:border-blue-600 focus:outline-none text-sm transition">
                    </div>

                    <!-- Jenis Servis -->
                    <div class="space-y-1.5">
                        <label for="srv_type" class="block text-sm font-semibold text-slate-700">Jenis Layanan <span class="text-red-600">*</span></label>
                        <select id="srv_type"
                                name="service_type"
                                x-model="serviceForm.service_type"
                                required
                                class="w-full min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-200/90 bg-white text-slate-900 shadow-sm focus:ring-2 focus:ring-blue-600/20 focus:border-blue-600 focus:outline-none text-sm transition">
                            <option value="regular">Servis Rutin / Berkala</option>
                            <option value="major">Servis Besar</option>
                            <option value="inspection">Inspeksi &amp; Diagnosa</option>
                            <option value="warranty">Klaim Garansi</option>
                        </select>
                    </div>
                </div>

                <!-- Tanggal & Jam Servis -->
                <div class="space-y-1.5">
                    <label for="srv_date" class="block text-sm font-semibold text-slate-700">Tanggal &amp; Jam Kedatangan <span class="text-red-600">*</span></label>
                    <input type="datetime-local"
                           id="srv_date"
                           name="service_date"
                           x-model="serviceForm.service_date"
                           :min="minDate"
                           required
                           class="w-full min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-200/90 bg-white text-slate-900 shadow-sm focus:ring-2 focus:ring-blue-600/20 focus:border-blue-600 focus:outline-none text-sm transition">
                    <p class="text-[11px] text-slate-600">Jam operasional: 08:00 - 17:00 WIB (Maks 3 kendaraan per slot jam).</p>
                </div>

                <!-- Keluhan Kendaraan -->
                <div class="space-y-1.5">
                    <label for="srv_complaint" class="block text-sm font-semibold text-slate-700">Keluhan atau Permintaan Khusus <span class="text-red-600">*</span></label>
                    <textarea id="srv_complaint"
                              name="complaint_notes"
                              x-model="serviceForm.complaint_notes"
                              rows="3"
                              minlength="10"
                              placeholder="Deskripsikan kondisi atau gejala kerusakan pada mobil Anda (minimal 10 karakter)..."
                              required
                              class="w-full px-4 py-2.5 rounded-xl border border-slate-200/90 bg-white text-slate-900 shadow-sm focus:ring-2 focus:ring-blue-600/20 focus:border-blue-600 focus:outline-none text-sm transition"></textarea>
                </div>

                <!-- Submit Button -->
                <div class="pt-4 flex items-center justify-end gap-3">
                    <button type="button" @click="closeModals()" class="min-h-[44px] px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 font-semibold hover:bg-slate-50 text-sm transition">
                        Batal
                    </button>
                    <button type="submit"
                            :disabled="isSubmitting"
                            class="min-h-[44px] bg-blue-600 text-white px-6 py-2.5 rounded-xl font-semibold hover:bg-blue-700 hover:scale-[1.02] active:scale-[0.98] text-sm transition-all duration-200 flex items-center gap-2 disabled:opacity-50 shadow-sm">
                        <span x-show="!isSubmitting">Konfirmasi Booking</span>
                        <span x-show="isSubmitting" x-cloak>Menyimpan...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 3: Bukti Nomor Tiket Reservasi -->
    <div x-show="createdTicketModalOpen"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 overflow-y-auto"
         role="dialog"
         aria-modal="true"
         aria-labelledby="ticket-proof-title"
         @keydown.escape.window="createdTicketModalOpen = false">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="createdTicketModalOpen = false"></div>
        <div class="relative w-full max-w-md rounded-3xl bg-white p-6 sm:p-8 shadow-2xl border border-slate-200/80 z-10 text-center space-y-5">
            <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center mx-auto">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <div>
                <h2 id="ticket-proof-title" class="font-display text-xl font-bold text-slate-900">Reservasi Berhasil Tercatat</h2>
                <p class="mt-1 text-xs text-slate-600">
                    Simpan nomor tiket di bawah ini untuk memantau status persetujuan dan progres pengerjaan kendaraan Anda kapan saja.
                </p>
            </div>
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/90 space-y-2">
                <span class="block text-[11px] font-bold uppercase tracking-wider text-slate-500">Nomor Tiket Resmi Anda</span>
                <span class="block font-mono text-xl sm:text-2xl font-extrabold tracking-wider text-blue-700 select-all" x-text="createdTicketNumber"></span>
            </div>
            <div class="flex flex-col sm:flex-row gap-2.5">
                <button type="button"
                        @click="copyTicketNumber(createdTicketNumber)"
                        class="w-full min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-100 text-slate-800 text-xs font-bold uppercase tracking-wider transition">
                    Salin Nomor Tiket
                </button>
                <button type="button"
                        @click="trackCreatedTicket()"
                        class="w-full min-h-[44px] px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-slate-900 text-white text-xs font-bold uppercase tracking-wider transition shadow-sm">
                    Cek Status Sekarang
                </button>
            </div>
        </div>
    </div>

    <!-- Alpine.js Application Store & Logic -->
    <script>
        function showroomApp(config) {
            return {
                mobileMenuOpen: false,
                testDriveModalOpen: false,
                serviceModalOpen: false,
                createdTicketModalOpen: false,
                createdTicketNumber: '',
                selectedCarId: null,
                selectedCarName: '',
                promoCode: '',
                isSubmitting: false,
                toasts: [],
                minDate: '',
                carCategoryFilter: 'all',
                partCategoryFilter: 'all',
                partSearch: '',
                ticketQuery: '',
                ticketLoading: false,
                ticketError: '',
                ticketResult: null,

                testDriveForm: {
                    customer_name: '',
                    customer_phone: '',
                    car_id: '',
                    preferred_date: '',
                    driver_license_id: '',
                    driver_license_image: null,
                    pickup_location: '',
                    notes: ''
                },

                serviceForm: {
                    customer_name: '',
                    customer_phone: '',
                    vehicle_plate_number: '',
                    vehicle_model: '',
                    vehicle_year: new Date().getFullYear(),
                    odometer_km: '',
                    service_type: 'regular',
                    service_date: '',
                    complaint_notes: ''
                },

                init() {
                    const now = new Date();
                    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
                    this.minDate = now.toISOString().slice(0, 16);

                    if (config.pendingTestDrive) {
                        this.testDriveForm = { ...this.testDriveForm, ...config.pendingTestDrive };
                        this.selectedCarId = this.testDriveForm.car_id;
                        if (this.selectedCarId && config.carNames && config.carNames[this.selectedCarId]) {
                            this.selectedCarName = config.carNames[this.selectedCarId];
                        }
                    }

                    if (config.pendingServiceBooking) {
                        this.serviceForm = { ...this.serviceForm, ...config.pendingServiceBooking };
                    }

                    const params = new URLSearchParams(window.location.search);
                    const initialTicket = params.get('ticket');
                    if (initialTicket) {
                        this.ticketQuery = initialTicket.toUpperCase();
                        this.lookupTicket();
                        setTimeout(() => this.scrollToTracker(), 250);
                    }

                    @if (session('booking_ticket'))
                        this.createdTicketNumber = @js(session('booking_ticket'));
                        this.createdTicketModalOpen = true;
                    @endif

                    @if (session('success'))
                        this.addToast(@js(session('success')), 'success', 4000);
                    @endif
                    @if (session('error'))
                        this.addToast(@js(session('error')), 'error', 6000);
                    @endif
                    @if (session('info'))
                        this.addToast(@js(session('info')), 'info', 4000);
                    @endif
                },

                scrollToTracker() {
                    const el = document.getElementById('lacak-tiket');
                    if (el) {
                        el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        const input = document.getElementById('ticket_lookup_input');
                        if (input) setTimeout(() => input.focus(), 350);
                    }
                },

                copyTicketNumber(code) {
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(code).catch(() => {});
                    }
                    this.addToast('Nomor tiket ' + code + ' berhasil disalin!', 'success', 3500);
                },

                trackCreatedTicket() {
                    this.ticketQuery = this.createdTicketNumber;
                    this.createdTicketModalOpen = false;
                    this.lookupTicket();
                    this.scrollToTracker();
                },

                async lookupTicket() {
                    const clean = (this.ticketQuery || '').trim().toUpperCase();
                    if (!clean) {
                        this.ticketError = 'Masukkan nomor tiket terlebih dahulu (contoh: SRV-20261115-0001 atau TD-20261115-0001).';
                        this.ticketResult = null;
                        return;
                    }

                    this.ticketLoading = true;
                    this.ticketError = '';

                    try {
                        const res = await fetch('{{ route('ticket.track') }}?ticket=' + encodeURIComponent(clean), {
                            headers: { 'Accept': 'application/json' }
                        });
                        const data = await res.json();
                        if (!res.ok || !data.found) {
                            this.ticketResult = null;
                            this.ticketError = data.message || 'Nomor tiket tidak ditemukan.';
                            return;
                        }
                        this.ticketResult = data;
                    } catch (e) {
                        this.ticketResult = null;
                        this.ticketError = 'Gagal memuat status tiket. Silakan coba kembali.';
                    } finally {
                        this.ticketLoading = false;
                    }
                },

                moveCarTab(event, direction) {
                    const container = event.currentTarget;
                    const tabs = Array.from(container.querySelectorAll('[role="tab"]'));
                    const currentIdx = tabs.indexOf(document.activeElement);
                    if (currentIdx === -1 || tabs.length === 0) return;
                    const nextIdx = (currentIdx + direction + tabs.length) % tabs.length;
                    tabs[nextIdx].focus();
                    tabs[nextIdx].click();
                },

                openTestDriveModal(carId = null, carName = '') {
                    if (carId) {
                        this.testDriveForm.car_id = carId;
                        this.selectedCarName = carName;
                    } else if (!this.testDriveForm.car_id) {
                        this.selectedCarName = '';
                    }
                    this.testDriveModalOpen = true;
                },

                openServiceModal(carModel = '') {
                    if (carModel) {
                        this.serviceForm.vehicle_model = carModel;
                    }
                    this.serviceModalOpen = true;
                },

                openServiceWithPart(partName, partNumber, defaultBrand = '') {
                    if (defaultBrand && !this.serviceForm.vehicle_model) {
                        this.serviceForm.vehicle_model = defaultBrand;
                    }
                    this.serviceForm.complaint_notes = 'Permintaan pemasangan suku cadang: ' + partName + ' (No. Part: ' + partNumber + ').';
                    this.serviceModalOpen = true;
                },

                openServiceWithType(type) {
                    this.serviceForm.service_type = type;
                    this.serviceModalOpen = true;
                },

                copyPromoCode(code) {
                    this.promoCode = code;
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(code).catch(() => {});
                    }
                    this.addToast('Kode promo ' + code + ' disalin dan siap digunakan pada formulir.', 'success', 3500);
                },

                closeModals() {
                    this.testDriveModalOpen = false;
                    this.serviceModalOpen = false;
                },

                addToast(message, type = 'info', duration = 4000) {
                    const id = Date.now() + Math.random();
                    this.toasts.push({ id, message, type, visible: true });
                    setTimeout(() => {
                        this.dismissToast(id);
                    }, duration);
                },

                dismissToast(id) {
                    const toast = this.toasts.find(t => t.id === id);
                    if (toast) toast.visible = false;
                },

                async submitTestDrive(e) {
                    this.isSubmitting = true;
                    const formData = new FormData(e.target);

                    try {
                        const response = await fetch('{{ route('test-drive.store') }}', {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                            },
                            body: formData
                        });

                        const data = await response.json();

                        if (response.status === 401 && data.redirect) {
                            window.location.href = data.redirect;
                            return;
                        }

                        if (!response.ok) {
                            const errMessage = data.message || (data.errors ? Object.values(data.errors)[0][0] : 'Terjadi kesalahan validasi.');
                            this.addToast(errMessage, 'error', 6000);
                            return;
                        }

                        this.addToast(data.message || 'Pengajuan test drive berhasil dikirim!', 'success', 4000);
                        this.closeModals();
                        if (data.ticket_number) {
                            this.createdTicketNumber = data.ticket_number;
                            this.createdTicketModalOpen = true;
                        }
                        this.testDriveForm.preferred_date = '';
                        this.testDriveForm.driver_license_id = '';
                        this.testDriveForm.notes = '';
                    } catch (error) {
                        this.addToast('Gagal mengirim pengajuan. Silakan coba kembali.', 'error', 6000);
                    } finally {
                        this.isSubmitting = false;
                    }
                },

                async submitServiceBooking(e) {
                    this.isSubmitting = true;
                    const formData = new FormData(e.target);

                    try {
                        const response = await fetch('{{ route('service-booking.store') }}', {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                            },
                            body: formData
                        });

                        const data = await response.json();

                        if (!response.ok) {
                            const errMessage = data.message || (data.errors ? Object.values(data.errors)[0][0] : 'Terjadi kesalahan validasi.');
                            this.addToast(errMessage, 'error', 6000);
                            return;
                        }

                        this.addToast(data.message || 'Booking servis berhasil!', 'success', 5000);
                        this.closeModals();
                        if (data.ticket_number) {
                            this.createdTicketNumber = data.ticket_number;
                            this.createdTicketModalOpen = true;
                        }
                        this.serviceForm.complaint_notes = '';
                    } catch (error) {
                        this.addToast('Gagal memproses booking. Silakan coba kembali.', 'error', 6000);
                    } finally {
                        this.isSubmitting = false;
                    }
                }
            };
        }
    </script>
    <!-- Fallback Auto-slide (hanya aktif jika Swiper module belum aktif & tidak reduced motion) -->
    <script>
        (function() {
            const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            if (prefersReducedMotion) return;

            let isPaused = false;
            document.addEventListener('DOMContentLoaded', function() {
                const swiperEl = document.querySelector('.showroom-swiper');
                if (swiperEl) {
                    swiperEl.addEventListener('mouseenter', function() { isPaused = true; });
                    swiperEl.addEventListener('mouseleave', function() { isPaused = false; });
                    swiperEl.addEventListener('touchstart', function() { isPaused = true; }, { passive: true });
                    swiperEl.addEventListener('touchend', function() {
                        setTimeout(function() { isPaused = false; }, 3500);
                    }, { passive: true });
                }

                setInterval(function() {
                    if (isPaused || window.__showroom_has_autoplay) return;
                    const nextBtn = document.querySelector('[data-slide-next]');
                    if (nextBtn) {
                        nextBtn.click();
                    }
                }, 6500);
            });
        })();
    </script>
</body>
</html>
