<!DOCTYPE html>
<html lang="id" class="h-full bg-gray-50 text-slate-900 scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $cars->first()->meta_title ?? 'Dealer Pro : Showroom Mobil Premium & Booking Servis' }}</title>
    <meta name="description" content="{{ $cars->first()->meta_description ?? 'Jelajahi koleksi mobil mewah dan sport terbaik dengan pengalaman visual 2.5D di Dealer Pro. Layanan booking servis dan test drive resmi.' }}">

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

    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/showroom.js'])
</head>
<body class="min-h-full font-sans antialiased overflow-x-hidden selection:bg-black selection:text-white"
      x-data="showroomApp({
          initialModal: '{{ session('open_modal') }}',
          pendingTestDrive: {{ json_encode($pendingTestDrive ?? null) }},
          pendingServiceBooking: {{ json_encode($pendingServiceBooking ?? null) }}
      })"
      :class="{ 'overflow-hidden': testDriveModalOpen || serviceModalOpen }">

    <!-- Accessibility: Skip to Content (WCAG 2.1 AA) -->
    <a href="#main-showroom"
       class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-70 focus:px-4 focus:py-2 focus:bg-black focus:text-white focus:rounded-lg shadow-lg">
        Lewati ke konten utama
    </a>

    <!-- Layer 30 (z-30): Sticky Architectural Header -->
    <header class="fixed top-0 inset-x-0 w-full z-30 backdrop-blur-md bg-white/95 border-b border-slate-200 transition-colors duration-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 sm:h-20 flex items-center justify-between">
            <!-- Brand Identity -->
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

            <!-- Desktop Navigation -->
            <nav class="hidden lg:flex items-center gap-7 text-xs font-semibold uppercase tracking-wider text-slate-600" aria-label="Navigasi Utama">
                <a href="#main-showroom" class="text-slate-900 hover:text-blue-600 transition py-1">Showroom 2.5D</a>
                <a href="#tentang-kami" class="hover:text-slate-900 transition py-1">Divisi Resmi</a>
                <a href="#katalog-mobil" class="hover:text-slate-900 transition py-1">Unit Kendaraan</a>
                <a href="#katalog-suku-cadang" class="hover:text-slate-900 transition py-1">Suku Cadang OEM</a>
                <a href="#layanan-bengkel" class="hover:text-slate-900 transition py-1">Bengkel Resmi</a>
                <a href="#promo" class="hover:text-slate-900 transition py-1">Program</a>
            </nav>

            <!-- Direct Action Bar (Zero Guest Login/Register Buttons) -->
            <div class="hidden lg:flex items-center gap-2.5">
                <button type="button"
                        @click="openServiceModal()"
                        class="px-4 py-2.5 border border-slate-300 bg-white text-slate-900 hover:border-slate-900 text-xs font-bold uppercase tracking-wider transition">
                    Booking Servis
                </button>
                <button type="button"
                        @click="openTestDriveModal(null, '')"
                        class="px-4 py-2.5 bg-slate-900 text-white hover:bg-blue-600 text-xs font-bold uppercase tracking-wider transition">
                    Test Drive
                </button>

                @auth
                    <span class="h-5 w-px bg-slate-200 mx-1" aria-hidden="true"></span>
                    <a href="{{ route('dashboard') }}"
                       class="text-xs font-bold uppercase tracking-wider text-blue-600 hover:text-slate-900 transition px-2 py-2">
                        Dashboard ({{ auth()->user()->name }})
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-xs font-semibold text-slate-500 hover:text-red-600 transition px-2 py-1">
                            Keluar
                        </button>
                    </form>
                @endauth
            </div>

            <!-- Mobile Hamburger Button -->
            <div class="flex lg:hidden items-center">
                <button type="button"
                        @click="mobileMenuOpen = !mobileMenuOpen"
                        aria-label="Buka menu navigasi"
                        :aria-expanded="mobileMenuOpen"
                        class="p-2.5 text-slate-900 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-black">
                    <svg x-show="!mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg x-show="mobileMenuOpen" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile Drawer Navigation -->
        <div x-show="mobileMenuOpen"
             x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="lg:hidden border-b border-slate-300 bg-white px-6 py-6 space-y-2 shadow-xl">
            <a href="#main-showroom" class="block text-xs font-bold uppercase tracking-wider text-slate-900 py-2 border-b border-slate-100" @click="mobileMenuOpen = false">01 / Showroom 2.5D</a>
            <a href="#tentang-kami" class="block text-xs font-bold uppercase tracking-wider text-slate-700 py-2 border-b border-slate-100" @click="mobileMenuOpen = false">02 / Divisi Resmi</a>
            <a href="#katalog-mobil" class="block text-xs font-bold uppercase tracking-wider text-slate-700 py-2 border-b border-slate-100" @click="mobileMenuOpen = false">03 / Katalog Unit Kendaraan</a>
            <a href="#katalog-suku-cadang" class="block text-xs font-bold uppercase tracking-wider text-slate-700 py-2 border-b border-slate-100" @click="mobileMenuOpen = false">04 / Suku Cadang OEM</a>
            <a href="#layanan-bengkel" class="block text-xs font-bold uppercase tracking-wider text-slate-700 py-2 border-b border-slate-100" @click="mobileMenuOpen = false">05 / Bengkel Resmi</a>
            <a href="#promo" class="block text-xs font-bold uppercase tracking-wider text-slate-700 py-2" @click="mobileMenuOpen = false">06 / Program Penawaran</a>

            <div class="pt-4 grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                <button type="button" @click="mobileMenuOpen = false; openTestDriveModal(null, '')" class="w-full text-center py-3 bg-slate-900 text-white font-bold text-xs uppercase tracking-wider">
                    Jadwalkan Test Drive
                </button>
                <button type="button" @click="mobileMenuOpen = false; openServiceModal()" class="w-full text-center py-3 border border-slate-300 text-slate-900 font-bold text-xs uppercase tracking-wider">
                    Booking Servis Resmi
                </button>
            </div>

            @auth
                <div class="pt-4 mt-2 border-t border-slate-200 flex items-center justify-between">
                    <a href="{{ route('dashboard') }}" class="text-xs font-bold uppercase tracking-wider text-blue-600">
                        Dashboard ({{ auth()->user()->name }})
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-xs text-red-600 font-bold uppercase tracking-wider">Keluar</button>
                    </form>
                </div>
            @endauth
        </div>
    </header>

    <!-- Main 2.5D Kinetic Showroom -->
    <main id="main-showroom" class="relative h-[100dvh] w-full overflow-hidden bg-gray-50 flex items-center justify-center select-none pt-16 sm:pt-20">
        <!-- Swiper Container -->
        <div class="swiper showroom-swiper w-full h-full relative" aria-label="Katalog Showroom Mobil 2.5D">
            <div class="swiper-wrapper">
                @forelse ($cars as $car)
                    @php
                        $mobileTuned = in_array($car->slug, ['tesla-model-3-2024', 'ford-ranger-raptor-2024'], true);
                    @endphp
                    <section class="swiper-slide relative w-full h-full overflow-hidden flex items-center justify-center {{ $mobileTuned ? 'showroom-slide--mobile-tuned' : '' }}"
                             data-car-id="{{ $car->id }}"
                             data-car-slug="{{ $car->slug }}"
                             data-car-name="{{ $car->brand }} {{ $car->model_name }}"
                             aria-label="{{ $car->brand }} {{ $car->model_name }} {{ $car->year }}">

                        <!-- Layer 0 (z-0): Ambient Glow & Giant Typography Backdrop -->
                        <div class="absolute inset-0 z-0 bg-gradient-radial from-blue-50/90 via-slate-100/40 to-transparent pointer-events-none"></div>

                        @php
                            $nameLen = strlen($car->model_name);
                            $bgSize = $nameLen > 10 ? 'clamp(2.8rem, 9.5vw, 10.5rem)' : 'clamp(4rem, 16vw, 18rem)';
                        @endphp
                        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 font-black uppercase tracking-tighter text-slate-200 mix-blend-multiply select-none pointer-events-none z-0 whitespace-nowrap text-center transition-all duration-300"
                             style="font-size: {{ $bgSize }}; line-height: 0.85;"
                             data-bg-text
                             aria-hidden="true">
                            {{ $car->model_name }}
                        </div>

                        <!-- Car Top Header / Meta -->
                        <div class="absolute top-2 sm:top-4 md:top-4 lg:top-6 inset-x-0 z-20 flex flex-col items-center justify-center text-center px-4" data-car-meta>
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wider {{ $car->category->color() ?? 'bg-blue-100 text-blue-700' }} border shadow-xs mb-1.5 sm:mb-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                {{ $car->category->label() }}
                            </div>
                            <h1 class="text-2xl sm:text-4xl md:text-5xl font-extrabold text-slate-900 tracking-tight">
                                {{ $car->brand }} <span class="text-blue-600">{{ $car->model_name }}</span>
                            </h1>
                            <p class="mt-1.5 sm:mt-2 inline-flex items-center gap-2 px-3.5 sm:px-4 py-1 sm:py-1.5 rounded-full bg-white/90 backdrop-blur-md border border-slate-200/90 shadow-xs">
                                <span class="font-mono text-base sm:text-xl font-extrabold text-slate-900 tracking-tight">{{ $car->price_formatted }}</span>
                                <span class="text-xs font-sans text-slate-600 font-medium">OTR Jakarta</span>
                            </p>
                        </div>

                        <!-- Layer 10 (z-10): Car Floating Image with Drop Shadow -->
                        <div class="absolute inset-0 z-10 flex items-center justify-center pointer-events-none px-4 md:px-8 mt-0 sm:mt-1 md:mt-2" data-car-visual>
                            <img src="{{ $car->hero_image_url }}"
                                 alt="{{ $car->hero_image_alt ?: ($car->brand . ' ' . $car->model_name . ' tampak samping') }}"
                                 width="1060"
                                 height="480"
                                 class="max-h-[30vh] sm:max-h-[32vh] md:max-h-[33vh] lg:max-h-[34vh] w-auto max-w-[92vw] md:max-w-[78vw] object-contain drop-shadow-car pointer-events-auto transform translate-3d"
                                 data-car-img
                                 {{ $loop->first ? 'loading="eager" fetchpriority="high"' : 'loading="lazy" decoding="async"' }} />
                        </div>

                        <!-- Layer 20 (z-20): Desktop Specs Grid (absolute bottom-28) -->
                        <div class="absolute bottom-28 left-1/2 -translate-x-1/2 z-20 hidden md:grid grid-cols-4 gap-6 lg:gap-8 w-full max-w-4xl px-4 pointer-events-auto">
                            <div data-spec-item class="bg-white/80 backdrop-blur-md border border-white/60 shadow-sm rounded-xl p-3.5 text-center">
                                <div class="text-[11px] font-semibold uppercase tracking-wider text-slate-500 mb-0.5">Tenaga Mesin</div>
                                <div class="font-mono text-xl lg:text-2xl font-bold text-slate-900 truncate">{{ $car->engine_specs['power'] ?? '-' }}</div>
                            </div>
                            <div data-spec-item class="bg-white/80 backdrop-blur-md border border-white/60 shadow-sm rounded-xl p-3.5 text-center">
                                <div class="text-[11px] font-semibold uppercase tracking-wider text-slate-500 mb-0.5">Torsi Maksimum</div>
                                <div class="font-mono text-xl lg:text-2xl font-bold text-slate-900 truncate">{{ $car->engine_specs['torque'] ?? '-' }}</div>
                            </div>
                            <div data-spec-item class="bg-white/80 backdrop-blur-md border border-white/60 shadow-sm rounded-xl p-3.5 text-center">
                                <div class="text-[11px] font-semibold uppercase tracking-wider text-slate-500 mb-0.5">0-100 KM/H</div>
                                <div class="font-mono text-xl lg:text-2xl font-bold text-slate-900 truncate">{{ $car->engine_specs['accel'] ?? '-' }}</div>
                            </div>
                            <div data-spec-item class="bg-white/80 backdrop-blur-md border border-white/60 shadow-sm rounded-xl p-3.5 text-center">
                                <div class="text-[11px] font-semibold uppercase tracking-wider text-slate-500 mb-0.5">Transmisi</div>
                                <div class="font-mono text-xl lg:text-2xl font-bold text-slate-900 truncate">{{ $car->engine_specs['trans'] ?? '-' }}</div>
                            </div>
                        </div>

                        <!-- Layer 20 (z-20): Mobile Specs HUD (Horizontal scroll snap-x) -->
                        <div class="absolute bottom-32 inset-x-0 z-20 flex md:hidden gap-3 overflow-x-auto snap-x snap-mandatory px-6 hud-scroll pointer-events-auto" data-car-specs>
                            <div data-spec-item class="snap-center shrink-0 min-w-[125px] bg-white/85 backdrop-blur-md border border-white/60 shadow-sm rounded-xl p-3 text-center">
                                <div class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Tenaga Mesin</div>
                                <div class="font-mono text-lg font-bold text-slate-900">{{ $car->engine_specs['power'] ?? '-' }}</div>
                            </div>
                            <div data-spec-item class="snap-center shrink-0 min-w-[125px] bg-white/85 backdrop-blur-md border border-white/60 shadow-sm rounded-xl p-3 text-center">
                                <div class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Torsi Maks</div>
                                <div class="font-mono text-lg font-bold text-slate-900">{{ $car->engine_specs['torque'] ?? '-' }}</div>
                            </div>
                            <div data-spec-item class="snap-center shrink-0 min-w-[125px] bg-white/85 backdrop-blur-md border border-white/60 shadow-sm rounded-xl p-3 text-center">
                                <div class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Akselerasi</div>
                                <div class="font-mono text-lg font-bold text-slate-900">{{ $car->engine_specs['accel'] ?? '-' }}</div>
                            </div>
                            <div data-spec-item class="snap-center shrink-0 min-w-[125px] bg-white/85 backdrop-blur-md border border-white/60 shadow-sm rounded-xl p-3 text-center">
                                <div class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Transmisi</div>
                                <div class="font-mono text-lg font-bold text-slate-900">{{ $car->engine_specs['trans'] ?? '-' }}</div>
                            </div>
                        </div>

                        <!-- Layer 20 (z-20): Action Buttons (CTA) -->
                        <div class="absolute bottom-16 sm:bottom-14 left-1/2 -translate-x-1/2 z-20 flex items-center gap-3 sm:gap-4 pointer-events-auto" data-car-actions>
                            <!-- Primary CTA: Test Drive -->
                            <button type="button"
                                    @click="openTestDriveModal({{ $car->id }}, '{{ addslashes($car->brand . ' ' . $car->model_name) }}')"
                                    data-car-id="{{ $car->id }}"
                                    data-car-name="{{ $car->brand }} {{ $car->model_name }}"
                                    class="bg-black text-white px-6 sm:px-8 py-3 rounded-lg font-semibold hover:bg-slate-800 hover:scale-105 active:scale-95 focus-visible:ring-2 focus-visible:ring-black focus-visible:ring-offset-2 transition duration-200 shadow-lg text-sm sm:text-base whitespace-nowrap">
                                Test Drive
                            </button>

                            <!-- Secondary CTA: Booking Servis -->
                            <button type="button"
                                    @click="openServiceModal('{{ addslashes($car->brand . ' ' . $car->model_name) }}')"
                                    data-car-id="{{ $car->id }}"
                                    data-car-name="{{ $car->brand }} {{ $car->model_name }}"
                                    class="bg-white/90 backdrop-blur-md text-slate-900 border border-slate-300 px-6 sm:px-8 py-3 rounded-lg font-semibold hover:bg-slate-100 hover:scale-105 active:scale-95 focus-visible:ring-2 focus-visible:ring-black transition duration-200 shadow-md text-sm sm:text-base whitespace-nowrap">
                                Booking Servis
                            </button>
                        </div>
                    </section>
                @empty
                    <div class="w-full h-full flex flex-col items-center justify-center text-center p-8 z-20">
                        <div class="w-20 h-20 rounded-full bg-slate-100 flex items-center justify-center mb-4 text-slate-400">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        </div>
                        <h2 class="text-2xl font-bold text-slate-800 mb-2">Showroom Sedang Disiapkan</h2>
                        <p class="text-slate-500 text-sm max-w-md mb-6">Data mobil belum tersedia di database. Sistem telah menginisialisasi ulang data default, silakan klik tombol di bawah untuk memuat kembali.</p>
                        <a href="{{ route('home') }}" class="bg-black text-white px-6 py-3 rounded-lg text-sm font-semibold hover:bg-slate-800 transition active:scale-95 shadow-md">
                            Muat Ulang Showroom
                        </a>
                    </div>
                @endforelse
            </div>

            @if(count($cars) > 0)
            <!-- Layer 20: Slider Controls & Counter -->
            <div class="absolute bottom-3 sm:bottom-4 left-1/2 -translate-x-1/2 z-20 flex items-center gap-4 bg-white/70 backdrop-blur-md border border-slate-200/80 rounded-full px-4 py-1.5 shadow-sm">
                <button type="button"
                        data-slide-prev
                        aria-label="Mobil sebelumnya"
                        class="p-2 rounded-full hover:bg-slate-100 active:scale-90 transition text-slate-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>

                <div class="font-mono text-sm tracking-widest text-slate-800 font-bold select-none">
                    <span id="slide-counter-current">01</span> / <span id="slide-counter-total">{{ sprintf('%02d', count($cars)) }}</span>
                </div>

                <button type="button"
                        data-slide-next
                        aria-label="Mobil berikutnya"
                        class="p-2 rounded-full hover:bg-slate-100 active:scale-90 transition text-slate-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>

            <!-- Scroll Indicator to Catalog -->
            <a href="#tentang-kami"
               class="hidden sm:inline-flex items-center gap-2 absolute bottom-4 right-6 z-20 bg-white/80 backdrop-blur-md border border-slate-200/80 rounded-full px-4 py-2 text-xs font-semibold text-slate-700 hover:text-black hover:bg-white shadow-sm transition">
                <span>Jelajahi Katalog Lengkap</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
    <section id="tentang-kami" class="bg-white border-t border-slate-300 py-20 sm:py-28">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16 items-start">
                <!-- Left Column: Editorial Lead + Operational Specification Table -->
                <div class="lg:col-span-5 space-y-8">
                    <div>
                        <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight leading-tight">
                            Pusat Penjualan Kendaraan, Bengkel Resmi, dan Gudang Suku Cadang Orisinal.
                        </h2>
                        <p class="text-slate-600 text-base leading-relaxed mt-4">
                            <strong>Dealer Pro (XYZ Motors)</strong> mengoperasikan fasilitas showroom kendaraan performa tinggi, bengkel perawatan berkala dengan kuota terjadwal, serta penyediaan suku cadang OEM dalam satu sistem terintegrasi di Jakarta Selatan.
                        </p>
                    </div>

                    <!-- Architectural Operational Ledger -->
                    <dl class="border-t border-b border-slate-300 divide-y divide-slate-200 text-sm">
                        <div class="py-3.5 flex items-baseline justify-between gap-4">
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Lokasi Showroom &amp; Workshop</dt>
                            <dd class="font-semibold text-slate-900 text-right">Jl. Sudirman No. 88, Jakarta Selatan</dd>
                        </div>
                        <div class="py-3.5 flex items-baseline justify-between gap-4">
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Jam Operasional Resmi</dt>
                            <dd class="font-mono font-bold text-slate-900 text-right tabular-nums">Senin - Sabtu / 08:00 - 18:00 WIB</dd>
                        </div>
                        <div class="py-3.5 flex items-baseline justify-between gap-4">
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Kapasitas Stall Bengkel</dt>
                            <dd class="font-mono font-bold text-slate-900 text-right tabular-nums">Maks. 3 Unit / Slot Jam</dd>
                        </div>
                        <div class="py-3.5 flex items-baseline justify-between gap-4">
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Inventaris Katalog Aktif</dt>
                            <dd class="font-mono font-bold text-blue-600 text-right tabular-nums">
                                {{ count($cars) }} Unit Mobil / {{ isset($spareParts) ? count($spareParts) : 0 }} SKU Part
                            </dd>
                        </div>
                    </dl>

                    <div class="flex flex-wrap items-center gap-3">
                        <a href="#katalog-mobil" class="bg-slate-900 text-white px-5 py-3 text-xs font-bold uppercase tracking-wider hover:bg-blue-600 transition">
                            Lihat Spesifikasi Unit ({{ count($cars) }})
                        </a>
                        <a href="#katalog-suku-cadang" class="border border-slate-300 bg-white text-slate-900 px-5 py-3 text-xs font-bold uppercase tracking-wider hover:border-slate-900 transition">
                            Buka Manifest Suku Cadang
                        </a>
                    </div>
                </div>

                <!-- Right Column: 3-Row Horizontal Division Specification Ledger -->
                <div class="lg:col-span-7 border-t border-b border-slate-900 divide-y divide-slate-200">
                    <!-- Divisi 01 -->
                    <div class="py-7 grid grid-cols-1 sm:grid-cols-12 gap-4 items-start">
                        <div class="sm:col-span-3">
                            <span class="font-mono text-xs font-bold text-slate-500 uppercase tracking-widest block">DIVISI 01</span>
                            <span class="font-mono text-xs font-bold text-slate-900 uppercase tracking-wider mt-1 block">Showroom &amp; Sales</span>
                        </div>
                        <div class="sm:col-span-6 space-y-2">
                            <h3 class="text-lg font-extrabold text-slate-900 tracking-tight">Eksplorasi Unit 2.5D &amp; Sesi Test Drive</h3>
                            <p class="text-sm text-slate-600 leading-relaxed">
                                Bandingkan output daya, torsi puncak, akselerasi 0-100 km/jam, serta harga OTR Jakarta. Pengajuan jadwal Test Drive dapat dilakukan di showroom pusat maupun melalui pengantaran unit ke lokasi Anda bersama konsultan Sales.
                            </p>
                        </div>
                        <div class="sm:col-span-3 sm:text-right pt-1">
                            <button type="button"
                                    @click="openTestDriveModal(null, '')"
                                    class="inline-flex items-center justify-center px-4 py-2.5 border border-slate-900 text-slate-900 hover:bg-slate-900 hover:text-white text-xs font-bold uppercase tracking-wider transition">
                                Jadwalkan Test Drive
                            </button>
                        </div>
                    </div>

                    <!-- Divisi 02 -->
                    <div class="py-7 grid grid-cols-1 sm:grid-cols-12 gap-4 items-start">
                        <div class="sm:col-span-3">
                            <span class="font-mono text-xs font-bold text-slate-500 uppercase tracking-widest block">DIVISI 02</span>
                            <span class="font-mono text-xs font-bold text-slate-900 uppercase tracking-wider mt-1 block">Bengkel Resmi</span>
                        </div>
                        <div class="sm:col-span-6 space-y-2">
                            <h3 class="text-lg font-extrabold text-slate-900 tracking-tight">Perawatan Berkala &amp; Kuota Presisi</h3>
                            <p class="text-sm text-slate-600 leading-relaxed">
                                Sistem penjadwalan bengkel membatasi maksimal 3 kendaraan per jam (08:00 - 17:00 WIB) agar pengerjaan mekanik berjalan tanpa antrean. Setiap reservasi menerbitkan tiket kerja resmi <code class="font-mono text-xs bg-slate-100 px-1.5 py-0.5 border border-slate-200 text-slate-800">SRV-YYYYMMDD-XXXX</code>.
                            </p>
                        </div>
                        <div class="sm:col-span-3 sm:text-right pt-1">
                            <button type="button"
                                    @click="openServiceModal()"
                                    class="inline-flex items-center justify-center px-4 py-2.5 bg-blue-600 text-white hover:bg-slate-900 text-xs font-bold uppercase tracking-wider transition">
                                Booking Servis
                            </button>
                        </div>
                    </div>

                    <!-- Divisi 03 -->
                    <div class="py-7 grid grid-cols-1 sm:grid-cols-12 gap-4 items-start">
                        <div class="sm:col-span-3">
                            <span class="font-mono text-xs font-bold text-slate-500 uppercase tracking-widest block">DIVISI 03</span>
                            <span class="font-mono text-xs font-bold text-slate-900 uppercase tracking-wider mt-1 block">Gudang Suku Cadang</span>
                        </div>
                        <div class="sm:col-span-6 space-y-2">
                            <h3 class="text-lg font-extrabold text-slate-900 tracking-tight">Suku Cadang OEM &amp; Aftermarket Teruji</h3>
                            <p class="text-sm text-slate-600 leading-relaxed">
                                Seluruh penggantian komponen menggunakan suku cadang bergaransi resmi (Brembo, Toyota Genuine Parts, Bosch, NGK, Fox, Aisin, Philips) dengan transparansi nomor part (SKU), ketersediaan stok gudang, dan harga satuan.
                            </p>
                        </div>
                        <div class="sm:col-span-3 sm:text-right pt-1">
                            <a href="#katalog-suku-cadang"
                               class="inline-flex items-center justify-center px-4 py-2.5 border border-slate-300 text-slate-900 hover:border-slate-900 text-xs font-bold uppercase tracking-wider transition">
                                Cek Stok Part
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- =====================================================================
         SEKSI 2: DAFTAR LENGKAP UNIT SHOWROOM (SPECIFICATION LEDGER)
         ===================================================================== -->
    <section id="katalog-mobil" class="bg-slate-50 border-t border-slate-300 py-20 sm:py-28">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
            <!-- Header & Segmented Technical Filter Bar -->
            <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6 border-b border-slate-300 pb-8">
                <div>
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                        Daftar Lengkap Unit Showroom
                    </h2>
                    <p class="text-slate-600 text-sm sm:text-base mt-2 max-w-2xl">
                        Lembar spesifikasi teknis unit kendaraan yang tersedia di showroom kami beserta harga On The Road (OTR) Jakarta dan penjadwalan sesi uji kendara.
                    </p>
                </div>

                <!-- Segmented Technical Filter Bar -->
                <div class="inline-flex flex-wrap border border-slate-300 bg-white divide-x divide-slate-300" role="tablist" aria-label="Filter Kategori Mobil">
                    <button type="button"
                            @click="carCategoryFilter = 'all'"
                            :class="carCategoryFilter === 'all' ? 'bg-slate-900 text-white' : 'bg-white text-slate-700 hover:bg-slate-50'"
                            class="px-4 py-2.5 text-xs font-bold uppercase tracking-wider transition">
                        Semua ({{ count($cars) }})
                    </button>
                    @foreach(\App\Enums\CarCategory::cases() as $cat)
                        @php
                            $catCount = $cars->filter(fn($c) => ($c->category->value ?? $c->category) === $cat->value)->count();
                        @endphp
                        @if($catCount > 0)
                            <button type="button"
                                    @click="carCategoryFilter = '{{ $cat->value }}'"
                                    :class="carCategoryFilter === '{{ $cat->value }}' ? 'bg-slate-900 text-white' : 'bg-white text-slate-700 hover:bg-slate-50'"
                                    class="px-4 py-2.5 text-xs font-bold uppercase tracking-wider transition">
                                {{ $cat->label() }} ({{ $catCount }})
                            </button>
                        @endif
                    @endforeach
                </div>
            </div>

            <!-- Specification Sheet Rows -->
            @if($cars->isEmpty())
                <div class="bg-white border border-slate-300 p-12 text-center">
                    <p class="text-base font-bold text-slate-900">Belum ada unit kendaraan yang terdaftar di katalog.</p>
                    <p class="text-sm text-slate-500 mt-1">Silakan hubungi konsultan showroom kami atau jadwalkan servis kendaraan Anda.</p>
                </div>
            @else
                <div class="bg-white border border-slate-300 divide-y divide-slate-300">
                    @foreach($cars as $index => $car)
                        @php
                            $carCatVal = $car->category->value ?? (string) $car->category;
                        @endphp
                        <article x-show="carCategoryFilter === 'all' || carCategoryFilter === '{{ $carCatVal }}'"
                                 x-transition:enter="transition ease-out duration-150"
                                 x-transition:enter-start="opacity-0"
                                 x-transition:enter-end="opacity-100"
                                 class="grid grid-cols-1 lg:grid-cols-12 divide-y lg:divide-y-0 lg:divide-x divide-slate-200 hover:bg-slate-50/70 transition-colors">

                            <!-- Column 1 (4 Cols): Studio Side-Profile Plate -->
                            <div class="lg:col-span-4 p-6 sm:p-8 flex flex-col justify-between bg-slate-50/50">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="font-mono text-xs font-bold text-slate-500 uppercase tracking-widest tabular-nums">
                                        UNIT {{ sprintf('%02d', $index + 1) }} / {{ $car->year }}
                                    </span>
                                    <span class="font-mono text-[11px] font-bold uppercase tracking-wider px-2.5 py-0.5 border border-slate-300 bg-white text-slate-800">
                                        {{ $car->category->label() }}
                                    </span>
                                </div>

                                <div class="py-6 flex items-center justify-center min-h-[160px]">
                                    <img src="{{ $car->hero_image_url }}"
                                         alt="{{ $car->brand }} {{ $car->model_name }}"
                                         loading="lazy"
                                         class="max-h-36 sm:max-h-40 w-auto object-contain drop-shadow-md" />
                                </div>

                                <div class="flex items-center justify-between text-xs text-slate-500 pt-2 border-t border-slate-200/80">
                                    <span>Warna Eksterior</span>
                                    @if($car->color_name)
                                        <span class="inline-flex items-center gap-1.5 font-semibold text-slate-800">
                                            <span class="w-2.5 h-2.5 border border-slate-400" style="background-color: {{ $car->color_hex ?: '#0f172a' }}"></span>
                                            {{ $car->color_name }}
                                        </span>
                                    @else
                                        <span class="font-mono text-slate-700">Standar Pabrik</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Column 2 (5 Cols): Identity, Description & Telemetry Table -->
                            <div class="lg:col-span-5 p-6 sm:p-8 flex flex-col justify-between space-y-6">
                                <div class="space-y-2.5">
                                    <div class="font-mono text-xs font-bold uppercase tracking-widest text-blue-600">
                                        {{ $car->brand }}
                                    </div>
                                    <h3 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                                        {{ $car->brand }} {{ $car->model_name }}
                                    </h3>
                                    @if($car->description)
                                        <p class="text-sm text-slate-600 leading-relaxed pt-1">
                                            {{ $car->description }}
                                        </p>
                                    @endif
                                </div>

                                <!-- 4-Column Hairline Telemetry Table -->
                                <dl class="grid grid-cols-2 sm:grid-cols-4 border border-slate-200 divide-y sm:divide-y-0 sm:divide-x divide-slate-200 bg-white">
                                    <div class="p-3">
                                        <dt class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Tenaga</dt>
                                        <dd class="font-mono text-xs sm:text-sm font-bold text-slate-900 mt-1 tabular-nums">{{ $car->engine_specs['power'] ?? '-' }}</dd>
                                    </div>
                                    <div class="p-3">
                                        <dt class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Torsi</dt>
                                        <dd class="font-mono text-xs sm:text-sm font-bold text-slate-900 mt-1 tabular-nums">{{ $car->engine_specs['torque'] ?? '-' }}</dd>
                                    </div>
                                    <div class="p-3">
                                        <dt class="text-[10px] font-bold uppercase tracking-wider text-slate-500">0-100 KM/J</dt>
                                        <dd class="font-mono text-xs sm:text-sm font-bold text-slate-900 mt-1 tabular-nums">{{ $car->engine_specs['accel'] ?? '-' }}</dd>
                                    </div>
                                    <div class="p-3">
                                        <dt class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Transmisi</dt>
                                        <dd class="font-mono text-xs sm:text-sm font-bold text-slate-900 mt-1 tabular-nums">{{ $car->engine_specs['trans'] ?? '-' }}</dd>
                                    </div>
                                </dl>
                            </div>

                            <!-- Column 3 (3 Cols): OTR Pricing & Direct Booking Triggers -->
                            <div class="lg:col-span-3 p-6 sm:p-8 flex flex-col justify-between bg-white">
                                <div>
                                    <span class="block text-[11px] font-bold uppercase tracking-wider text-slate-500">Harga Resmi OTR Jakarta</span>
                                    <p class="font-mono text-xl sm:text-2xl font-extrabold text-slate-900 mt-1 tabular-nums">
                                        {{ $car->price_formatted }}
                                    </p>
                                    <span class="inline-block mt-2 font-mono text-[11px] font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5">
                                        Tersedia untuk Test Drive
                                    </span>
                                </div>

                                <div class="space-y-2.5 pt-6 mt-6 border-t border-slate-200">
                                    <button type="button"
                                            @click="openTestDriveModal({{ $car->id }}, '{{ addslashes($car->brand . ' ' . $car->model_name) }}')"
                                            class="w-full py-3 px-4 bg-slate-900 hover:bg-blue-600 text-white text-xs font-bold uppercase tracking-wider transition text-center">
                                        Jadwalkan Test Drive
                                    </button>
                                    <button type="button"
                                            @click="openServiceModal('{{ addslashes($car->brand . ' ' . $car->model_name) }}')"
                                            class="w-full py-2.5 px-4 border border-slate-300 hover:border-slate-900 text-slate-800 text-xs font-bold uppercase tracking-wider transition text-center">
                                        Booking Servis Unit Ini
                                    </button>
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
    <section id="katalog-suku-cadang" class="bg-white border-t border-slate-300 py-20 sm:py-28">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
            <!-- Header & Search/Category Controls -->
            <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6 border-b border-slate-300 pb-8">
                <div>
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                        Katalog Suku Cadang &amp; Komponen Orisinal
                    </h2>
                    <p class="text-slate-600 text-sm sm:text-base mt-2 max-w-2xl">
                        Daftar inventaris suku cadang resmi di gudang bengkel kami. Pilih komponen di bawah ini untuk langsung menyertakannya ke dalam formulir reservasi servis kendaraan Anda.
                    </p>
                </div>

                <!-- Search & Category Filter Suku Cadang -->
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                    <div class="relative">
                        <input type="text"
                               x-model="partSearch"
                               placeholder="Cari nama part, merk, atau SKU..."
                               aria-label="Cari suku cadang"
                               class="w-full sm:w-64 px-4 py-2.5 border border-slate-300 bg-white text-sm text-slate-900 focus:ring-2 focus:ring-black focus:outline-none" />
                    </div>
                    <select x-model="partCategoryFilter"
                            aria-label="Filter kategori suku cadang"
                            class="px-4 py-2.5 border border-slate-300 bg-white text-sm font-semibold text-slate-800 focus:ring-2 focus:ring-black focus:outline-none">
                        <option value="all">Semua Kategori Komponen</option>
                        @foreach(\App\Enums\SparePartCategory::cases() as $spCat)
                            <option value="{{ $spCat->value }}">{{ $spCat->label() }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Hairline Matrix Inventory -->
            @if(!isset($spareParts) || $spareParts->isEmpty())
                <div class="bg-slate-50 border border-slate-300 p-12 text-center">
                    <p class="text-base font-bold text-slate-900">Inventaris suku cadang sedang diperbarui.</p>
                    <p class="text-sm text-slate-500 mt-1">Anda tetap dapat memesan jadwal servis dan berkonsultasi langsung dengan mekanik kami mengenai kebutuhan suku cadang mobil Anda.</p>
                    <button type="button" @click="openServiceModal()" class="mt-4 bg-slate-900 text-white px-5 py-2.5 text-xs font-bold uppercase tracking-wider hover:bg-blue-600 transition">
                        Booking Servis Sekarang
                    </button>
                </div>
            @else
                <div class="border-t border-l border-slate-300 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($spareParts as $part)
                        @php
                            $partCatVal = $part->category->value ?? (string) $part->category;
                            $compatBrands = is_array($part->car_brand_compatibility) ? implode(', ', $part->car_brand_compatibility) : '';
                            $searchHaystack = strtolower($part->name . ' ' . $part->brand . ' ' . $part->part_number . ' ' . $compatBrands);
                        @endphp
                        <div x-show="(partCategoryFilter === 'all' || partCategoryFilter === '{{ $partCatVal }}') && (partSearch.trim() === '' || '{{ addslashes($searchHaystack) }}'.includes(partSearch.toLowerCase().trim()))"
                             class="border-r border-b border-slate-300 bg-white hover:bg-slate-50/80 p-6 flex flex-col justify-between transition-colors">

                            <!-- Top Technical Header: SKU & Stock Status -->
                            <div>
                                <div class="flex items-center justify-between gap-2 pb-4 border-b border-slate-200">
                                    <span class="font-mono text-xs font-bold text-slate-600 tabular-nums">
                                        SKU: {{ $part->part_number }}
                                    </span>
                                    <span class="font-mono text-[11px] font-bold px-2 py-0.5 border tabular-nums {{ $part->isLowStock() ? 'bg-amber-50 text-amber-800 border-amber-300' : 'bg-emerald-50 text-emerald-800 border-emerald-300' }}">
                                        STOK: {{ $part->stock_quantity }} UNIT
                                    </span>
                                </div>

                                <!-- Middle: Studio Image + Technical Metadata -->
                                <div class="grid grid-cols-12 gap-4 py-5 items-center">
                                    <div class="col-span-4 aspect-square bg-slate-50 border border-slate-200 p-2.5 flex items-center justify-center">
                                        <img src="{{ $part->imageUrl }}"
                                             alt="{{ $part->name }}"
                                             loading="lazy"
                                             class="w-full h-full object-contain" />
                                    </div>
                                    <div class="col-span-8 space-y-1.5">
                                        <div class="flex items-center gap-2">
                                            <span class="font-mono text-[11px] font-bold uppercase tracking-wider text-blue-600">{{ $part->brand }}</span>
                                            <span class="text-slate-300">/</span>
                                            <span class="text-[11px] font-semibold text-slate-500">{{ $part->category?->label() ?? 'Komponen Umum' }}</span>
                                        </div>
                                        <h3 class="font-extrabold text-base text-slate-900 leading-snug" title="{{ $part->name }}">
                                            {{ $part->name }}
                                        </h3>
                                        <p class="text-xs text-slate-500 pt-0.5">
                                            Kompatibilitas:
                                            <span class="font-semibold text-slate-800">
                                                {{ !empty($part->car_brand_compatibility) ? implode(', ', $part->car_brand_compatibility) : 'Universal Semua Unit' }}
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Bottom: Unit Price & Direct Installation Trigger -->
                            <div class="pt-4 border-t border-slate-200 flex items-center justify-between gap-3">
                                <div>
                                    <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-500">Harga Satuan</span>
                                    <span class="font-mono text-base font-extrabold text-slate-900 tabular-nums">{{ $part->price_formatted }}</span>
                                </div>
                                <button type="button"
                                        @click="openServiceWithPart('{{ addslashes($part->name) }}', '{{ addslashes($part->part_number) }}', '{{ addslashes($part->car_brand_compatibility[0] ?? '') }}')"
                                        class="px-4 py-2.5 bg-slate-900 hover:bg-blue-600 text-white text-xs font-bold uppercase tracking-wider transition shrink-0">
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
    <section id="layanan-bengkel" class="bg-slate-100 border-t border-slate-300 py-20 sm:py-28">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-start">
                <!-- Left Column (7 Cols): Service Scope Schedule Table -->
                <div class="lg:col-span-7 space-y-6">
                    <div>
                        <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900">
                            Standar Pengerjaan Bengkel Resmi Dealer Pro
                        </h2>
                        <p class="text-slate-600 text-sm sm:text-base mt-2 max-w-2xl">
                            Pilih cakupan layanan pemeliharaan kendaraan Anda. Sistem kami memvalidasi kuota slot per jam secara otomatis agar kendaraan langsung ditangani mekanik di stall bengkel tanpa waktu tunggu berlebih.
                        </p>
                    </div>

                    <div class="bg-white border border-slate-300 divide-y divide-slate-200">
                        <!-- Layanan 01 -->
                        <div class="p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="space-y-1.5 max-w-xl">
                                <div class="flex items-center gap-2.5">
                                    <span class="font-mono text-xs font-bold text-slate-500">01</span>
                                    <h3 class="text-base font-extrabold text-slate-900">Servis Rutin / Berkala (Periodic Maintenance)</h3>
                                </div>
                                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                                    Penggantian oli mesin sintetis, filter oli, filter udara kabin, inspeksi ketebalan kampas rem, fluida pendingin radiator, dan rotasi ban sesuai buku servis.
                                </p>
                            </div>
                            <button type="button"
                                    @click="openServiceWithType('regular')"
                                    class="shrink-0 px-4 py-2.5 border border-slate-900 text-slate-900 hover:bg-slate-900 hover:text-white text-xs font-bold uppercase tracking-wider transition">
                                Pilih Berkala
                            </button>
                        </div>

                        <!-- Layanan 02 -->
                        <div class="p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="space-y-1.5 max-w-xl">
                                <div class="flex items-center gap-2.5">
                                    <span class="font-mono text-xs font-bold text-slate-500">02</span>
                                    <h3 class="text-base font-extrabold text-slate-900">Servis Besar (Major Overhaul &amp; Drivetrain)</h3>
                                </div>
                                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                                    Perawatan komprehensif meliputi penggantian sabuk timing belt, kuras penuh fluida transmisi otomatis (ATF), kalibrasi suspensi, dan penggantian busi iridium.
                                </p>
                            </div>
                            <button type="button"
                                    @click="openServiceWithType('major')"
                                    class="shrink-0 px-4 py-2.5 border border-slate-900 text-slate-900 hover:bg-slate-900 hover:text-white text-xs font-bold uppercase tracking-wider transition">
                                Pilih Servis Besar
                            </button>
                        </div>

                        <!-- Layanan 03 -->
                        <div class="p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="space-y-1.5 max-w-xl">
                                <div class="flex items-center gap-2.5">
                                    <span class="font-mono text-xs font-bold text-slate-500">03</span>
                                    <h3 class="text-base font-extrabold text-slate-900">Inspeksi &amp; Diagnosa Komputer (OBD / EV Telemetry)</h3>
                                </div>
                                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                                    Pemindaian modul ECU menyeluruh, evaluasi kesehatan sel baterai tegangan tinggi kendaraan listrik (EV), serta pemeriksaan sistem kelistrikan dan kaki-kaki.
                                </p>
                            </div>
                            <button type="button"
                                    @click="openServiceWithType('inspection')"
                                    class="shrink-0 px-4 py-2.5 border border-slate-900 text-slate-900 hover:bg-slate-900 hover:text-white text-xs font-bold uppercase tracking-wider transition">
                                Pilih Diagnosa
                            </button>
                        </div>

                        <!-- Layanan 04 -->
                        <div class="p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="space-y-1.5 max-w-xl">
                                <div class="flex items-center gap-2.5">
                                    <span class="font-mono text-xs font-bold text-slate-500">04</span>
                                    <h3 class="text-base font-extrabold text-slate-900">Klaim Garansi Resmi Pabrik</h3>
                                </div>
                                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                                    Penanganan pemeriksaan dan penggantian suku cadang yang masih berada dalam cakupan masa garansi resmi dengan pencatatan riwayat terverifikasi.
                                </p>
                            </div>
                            <button type="button"
                                    @click="openServiceWithType('warranty')"
                                    class="shrink-0 px-4 py-2.5 border border-slate-900 text-slate-900 hover:bg-slate-900 hover:text-white text-xs font-bold uppercase tracking-wider transition">
                                Ajukan Klaim
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Right Column (5 Cols): Workshop Protocol & Ticket Status Ledger -->
                <div class="lg:col-span-5 bg-white border border-slate-300">
                    <div class="p-6 border-b border-slate-300 flex items-center justify-between">
                        <div>
                            <span class="font-mono text-[11px] font-bold uppercase tracking-widest text-slate-500">Protokol Tiket Servis</span>
                            <h3 class="text-lg font-extrabold text-slate-900 mt-0.5">Alur Pelacakan Status Kendaraan</h3>
                        </div>
                        <span class="font-mono text-xs font-bold px-2.5 py-1 bg-slate-100 text-slate-800 border border-slate-300">
                            SRV-YYYYMMDD-XXXX
                        </span>
                    </div>

                    <ol class="divide-y divide-slate-200 text-sm">
                        <li class="p-5 flex items-start gap-4">
                            <span class="font-mono text-xs font-bold px-2 py-1 bg-slate-100 text-slate-900 border border-slate-300 shrink-0 tabular-nums">TAHAP 01</span>
                            <div>
                                <p class="font-bold text-slate-900">Pending (Penerbitan Tiket &amp; Kunci Kuota)</p>
                                <p class="text-xs text-slate-600 mt-1 leading-relaxed">Pelanggan menentukan tanggal dan jam kedatangan. Sistem menerbitkan nomor tiket unik dan mengunci 1 dari 3 kuota stall pada jam tersebut.</p>
                            </div>
                        </li>
                        <li class="p-5 flex items-start gap-4">
                            <span class="font-mono text-xs font-bold px-2 py-1 bg-slate-100 text-slate-900 border border-slate-300 shrink-0 tabular-nums">TAHAP 02</span>
                            <div>
                                <p class="font-bold text-slate-900">Confirmed (Penugasan Mekanik Tersertifikasi)</p>
                                <p class="text-xs text-slate-600 mt-1 leading-relaxed">Service Advisor memverifikasi keluhan kendaraan dan menugaskan mekanik spesifik yang bertanggung jawab menangani unit Anda.</p>
                            </div>
                        </li>
                        <li class="p-5 flex items-start gap-4">
                            <span class="font-mono text-xs font-bold px-2 py-1 bg-blue-50 text-blue-800 border border-blue-200 shrink-0 tabular-nums">TAHAP 03</span>
                            <div>
                                <p class="font-bold text-slate-900">In Progress (Pengerjaan &amp; Rincian Suku Cadang)</p>
                                <p class="text-xs text-slate-600 mt-1 leading-relaxed">Kendaraan masuk stall bengkel. Seluruh suku cadang dan biaya jasa yang digunakan mekanik tercatat langsung secara transparan pada lembar kerja.</p>
                            </div>
                        </li>
                        <li class="p-5 flex items-start gap-4">
                            <span class="font-mono text-xs font-bold px-2 py-1 bg-emerald-50 text-emerald-800 border border-emerald-200 shrink-0 tabular-nums">TAHAP 04</span>
                            <div>
                                <p class="font-bold text-slate-900">Completed (Inspeksi Akhir &amp; Serah Terima)</p>
                                <p class="text-xs text-slate-600 mt-1 leading-relaxed">Pekerjaan selesai diverifikasi, rincian biaya terhitung akurat, dan pelanggan menerima notifikasi bahwa kendaraan siap diambil.</p>
                            </div>
                        </li>
                    </ol>

                    <div class="p-6 bg-slate-50 border-t border-slate-300 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
                        <div class="text-xs text-slate-600">
                            <span class="block font-bold text-slate-900 uppercase tracking-wider">Jam Operasional Bengkel</span>
                            <span class="font-mono tabular-nums">Senin - Sabtu / 08:00 - 17:00 WIB</span>
                        </div>
                        <button type="button"
                                @click="openServiceModal()"
                                class="bg-blue-600 hover:bg-slate-900 text-white px-5 py-3 text-xs font-bold uppercase tracking-wider transition text-center">
                            Jadwalkan Servis
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- =====================================================================
         SEKSI 5: PROGRAM PROMO & PENAWARAN RESMI
         ===================================================================== -->
    <section id="promo" class="bg-white border-t border-slate-300 py-16 sm:py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 border-b border-slate-300 pb-6">
                <div>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                        Program Penawaran &amp; Voucher Resmi
                    </h2>
                    <p class="text-slate-600 text-sm mt-1">
                        Gunakan kode program di bawah ini saat melakukan konfirmasi pembelian unit atau reservasi perawatan berkala.
                    </p>
                </div>
            </div>

            @if(!isset($promos) || $promos->isEmpty())
                <div class="bg-slate-50 border border-slate-300 p-8 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Program penawaran khusus bulan ini tersedia langsung melalui konsultan Sales kami.</h3>
                        <p class="text-xs sm:text-sm text-slate-600 mt-1">Ajukan jadwal Test Drive untuk mendapatkan simulasi pembiayaan dan paket purnajual resmi.</p>
                    </div>
                    <button type="button"
                            @click="openTestDriveModal(null, '')"
                            class="shrink-0 bg-slate-900 text-white px-5 py-3 text-xs font-bold uppercase tracking-wider hover:bg-blue-600 transition">
                        Jadwalkan Test Drive
                    </button>
                </div>
            @else
                <div class="border border-slate-300 divide-y divide-slate-300 bg-white">
                    @foreach($promos as $promo)
                        <div class="p-6 grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
                            <div class="md:col-span-3 flex flex-col">
                                <span class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Kode Voucher Resmi</span>
                                <span class="font-mono text-base font-extrabold text-slate-900 mt-1">{{ $promo->code }}</span>
                                <span class="font-mono text-xs font-bold text-emerald-700 mt-1 tabular-nums">
                                    @if(($promo->discount_type->value ?? $promo->discount_type) === 'percentage')
                                        Potongan {{ (float) $promo->discount_value }}%
                                    @else
                                        Potongan Rp {{ number_format((float) $promo->discount_value, 0, ',', '.') }}
                                    @endif
                                </span>
                            </div>

                            <div class="md:col-span-6 space-y-1">
                                <h3 class="text-base font-extrabold text-slate-900">{{ $promo->title }}</h3>
                                @if($promo->car)
                                    <p class="text-xs font-bold text-blue-600">
                                        Unit Berlaku: {{ $promo->car->brand }} {{ $promo->car->model_name }}
                                    </p>
                                @endif
                                @if($promo->description)
                                    <p class="text-xs text-slate-600 leading-relaxed">{{ $promo->description }}</p>
                                @endif
                            </div>

                            <div class="md:col-span-3 flex md:flex-col items-center md:items-end justify-between gap-2">
                                <span class="font-mono text-[11px] text-slate-500 tabular-nums">
                                    Berlaku s/d {{ $promo->ends_at ? $promo->ends_at->format('d M Y') : '-' }}
                                </span>
                                <button type="button"
                                        @click="copyPromoCode('{{ addslashes($promo->code) }}')"
                                        class="px-4 py-2 border border-slate-900 text-slate-900 hover:bg-slate-900 hover:text-white text-xs font-bold uppercase tracking-wider transition">
                                    Salin Kode
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <!-- =====================================================================
         FOOTER RESMI DEALER PRO (CLEAN PUBLIC INTERFACE)
         ===================================================================== -->
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
                        <li><a href="#main-showroom" class="hover:text-slate-900 transition">01 / Showroom Interaktif 2.5D</a></li>
                        <li><a href="#tentang-kami" class="hover:text-slate-900 transition">02 / Divisi Resmi Dealer Pro</a></li>
                        <li><a href="#katalog-mobil" class="hover:text-slate-900 transition">03 / Spesifikasi Lengkap Unit Mobil</a></li>
                        <li><a href="#katalog-suku-cadang" class="hover:text-slate-900 transition">04 / Manifest Suku Cadang Orisinal</a></li>
                        <li><a href="#layanan-bengkel" class="hover:text-slate-900 transition">05 / Standar Bengkel &amp; Alur Tiket</a></li>
                        <li><a href="#promo" class="hover:text-slate-900 transition">06 / Program Penawaran Resmi</a></li>
                    </ul>
                </div>

                <div class="md:col-span-3 space-y-3">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900">Reservasi Langsung</h3>
                    <div class="flex flex-col gap-2.5">
                        <button type="button"
                                @click="openTestDriveModal(null, '')"
                                class="w-full text-center px-4 py-3 bg-slate-900 text-white text-xs font-bold uppercase tracking-wider hover:bg-blue-600 transition">
                            Jadwalkan Test Drive
                        </button>
                        <button type="button"
                                @click="openServiceModal()"
                                class="w-full text-center px-4 py-3 border border-slate-900 bg-white text-slate-900 text-xs font-bold uppercase tracking-wider hover:bg-slate-900 hover:text-white transition">
                            Booking Servis Bengkel
                        </button>
                        @auth
                            <a href="{{ route('dashboard') }}"
                               class="w-full text-center px-4 py-2.5 border border-slate-300 text-slate-800 text-xs font-bold uppercase tracking-wider hover:bg-white transition">
                                Buka Dashboard ({{ auth()->user()->name }})
                            </a>
                        @endauth
                    </div>
                </div>
            </div>

            <div class="pt-6 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500">
                <p>&copy; {{ date('Y') }} Dealer Pro (XYZ Motors Indonesia). Seluruh hak cipta dilindungi.</p>
                <p class="font-mono text-[11px] uppercase tracking-wider">Showroom Sales / Workshop Service / OEM Spare Parts</p>
            </div>
        </div>
    </footer>

    <!-- Layer 40: Toast Notifications Container (Design §4.6) -->
    <div class="fixed top-20 right-4 sm:right-6 z-40 space-y-3 max-w-sm w-full pointer-events-none"
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
                 class="pointer-events-auto p-4 rounded-xl shadow-xl backdrop-blur-md border text-sm flex items-start gap-3"
                 :class="{
                     'bg-green-50/95 border-green-200 text-green-800': toast.type === 'success',
                     'bg-red-50/95 border-red-200 text-red-800': toast.type === 'error',
                     'bg-blue-50/95 border-blue-200 text-blue-800': toast.type === 'info'
                 }">
                <div class="shrink-0 mt-0.5">
                    <template x-if="toast.type === 'success'">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </template>
                    <template x-if="toast.type === 'error'">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </template>
                    <template x-if="toast.type === 'info'">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </template>
                </div>
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
        <div class="relative w-full max-w-lg rounded-2xl backdrop-blur-md bg-white/95 border border-white/40 shadow-2xl p-6 sm:p-8 max-h-[90vh] overflow-y-auto transform transition-all duration-300"
             x-show="testDriveModalOpen"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">

            <div class="flex items-center justify-between pb-4 border-b border-slate-200">
                <div>
                    <h2 id="test-drive-title" class="text-xl font-bold text-slate-900">Jadwalkan Test Drive</h2>
                    <p class="text-xs text-slate-500 mt-0.5" x-text="selectedCarName ? 'Unit: ' + selectedCarName : 'Pilih unit mobil yang Anda minati'"></p>
                </div>
                <button type="button" @click="closeModals()" class="p-2 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition" aria-label="Tutup modal">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form @submit.prevent="submitTestDrive" class="mt-6 space-y-4" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="car_id" :value="testDriveForm.car_id">

                <!-- Pilihan Mobil jika belum terpilih -->
                <div x-show="!testDriveForm.car_id" class="space-y-1.5">
                    <label for="td_select_car" class="block text-sm font-medium text-slate-700">Pilih Model Mobil <span class="text-red-500">*</span></label>
                    <select id="td_select_car" x-model="testDriveForm.car_id" class="w-full px-4 py-2.5 rounded-lg border border-slate-300 bg-white text-slate-900 focus:ring-2 focus:ring-black focus:border-black text-sm">
                        <option value="">-- Pilih Mobil --</option>
                        @foreach ($cars as $car)
                            <option value="{{ $car->id }}">{{ $car->brand }} {{ $car->model_name }} ({{ $car->year }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Tanggal & Jam Test Drive -->
                <div class="space-y-1.5">
                    <label for="td_preferred_date" class="block text-sm font-medium text-slate-700">Tanggal & Waktu Preferensi <span class="text-red-500">*</span></label>
                    <input type="datetime-local"
                           id="td_preferred_date"
                           name="preferred_date"
                           x-model="testDriveForm.preferred_date"
                           :min="minDate"
                           required
                           class="w-full px-4 py-2.5 rounded-lg border border-slate-300 bg-white text-slate-900 focus:ring-2 focus:ring-black focus:border-black text-sm transition">
                </div>

                <!-- Nomor SIM -->
                <div class="space-y-1.5">
                    <label for="td_license_id" class="block text-sm font-medium text-slate-700">Nomor SIM A <span class="text-red-500">*</span></label>
                    <input type="text"
                           id="td_license_id"
                           name="driver_license_id"
                           x-model="testDriveForm.driver_license_id"
                           placeholder="Contoh: 1234-5678-9012"
                           minlength="5"
                           required
                           class="w-full px-4 py-2.5 rounded-lg border border-slate-300 bg-white text-slate-900 focus:ring-2 focus:ring-black focus:border-black text-sm transition">
                </div>

                <!-- Foto SIM (Opsional) -->
                <div class="space-y-1.5">
                    <label for="td_license_image" class="block text-sm font-medium text-slate-700">Unggah Foto SIM (Opsional)</label>
                    <input type="file"
                           id="td_license_image"
                           name="driver_license_image"
                           @change="testDriveForm.driver_license_image = $event.target.files[0]"
                           accept="image/*"
                           class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
                    <p class="text-[11px] text-slate-500">Format: JPG, PNG, WebP. Maks 5MB.</p>
                </div>

                <!-- Lokasi Penjemputan / Dealer -->
                <div class="space-y-1.5">
                    <label for="td_pickup_location" class="block text-sm font-medium text-slate-700">Lokasi Pertemuan (Opsional)</label>
                    <input type="text"
                           id="td_pickup_location"
                           name="pickup_location"
                           x-model="testDriveForm.pickup_location"
                           placeholder="Dealer Dealer Pro Pusat / Alamat Rumah Anda"
                           class="w-full px-4 py-2.5 rounded-lg border border-slate-300 bg-white text-slate-900 focus:ring-2 focus:ring-black focus:border-black text-sm transition">
                </div>

                <!-- Catatan Tambahan -->
                <div class="space-y-1.5">
                    <label for="td_notes" class="block text-sm font-medium text-slate-700">Catatan Tambahan (Opsional)</label>
                    <textarea id="td_notes"
                              name="notes"
                              x-model="testDriveForm.notes"
                              rows="2"
                              placeholder="Kebutuhan khusus atau preferensi saat sesi test drive"
                              class="w-full px-4 py-2.5 rounded-lg border border-slate-300 bg-white text-slate-900 focus:ring-2 focus:ring-black focus:border-black text-sm transition"></textarea>
                </div>

                <!-- Submit Button -->
                <div class="pt-4 flex items-center justify-end gap-3">
                    <button type="button" @click="closeModals()" class="px-5 py-2.5 rounded-lg border border-slate-300 text-slate-700 font-semibold hover:bg-slate-50 text-sm transition">
                        Batal
                    </button>
                    <button type="submit"
                            :disabled="isSubmitting"
                            class="bg-black text-white px-6 py-2.5 rounded-lg font-semibold hover:bg-slate-800 text-sm transition flex items-center gap-2 disabled:opacity-50">
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
        <div class="relative w-full max-w-lg rounded-2xl backdrop-blur-md bg-white/95 border border-white/40 shadow-2xl p-6 sm:p-8 max-h-[90vh] overflow-y-auto transform transition-all duration-300"
             x-show="serviceModalOpen"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">

            <div class="flex items-center justify-between pb-4 border-b border-slate-200">
                <div>
                    <h2 id="service-booking-title" class="text-xl font-bold text-slate-900">Booking Servis Kendaraan</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Layanan bengkel resmi dengan mekanik tersertifikasi</p>
                </div>
                <button type="button" @click="closeModals()" class="p-2 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition" aria-label="Tutup modal">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form @submit.prevent="submitServiceBooking" class="mt-6 space-y-4">
                @csrf

                <!-- Nomor Plat Kendaraan -->
                <div class="space-y-1.5">
                    <label for="srv_plate" class="block text-sm font-medium text-slate-700">Nomor Plat Kendaraan <span class="text-red-500">*</span></label>
                    <input type="text"
                           id="srv_plate"
                           name="vehicle_plate_number"
                           x-model="serviceForm.vehicle_plate_number"
                           @input="serviceForm.vehicle_plate_number = $event.target.value.toUpperCase()"
                           placeholder="Contoh: B 1234 XYZ"
                           required
                           class="w-full px-4 py-2.5 rounded-lg border border-slate-300 bg-white text-slate-900 focus:ring-2 focus:ring-black focus:border-black text-sm uppercase font-mono transition">
                    <p class="text-[11px] text-slate-500">Gunakan format plat Indonesia (contoh: B 1234 ABC).</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Model Kendaraan -->
                    <div class="space-y-1.5">
                        <label for="srv_model" class="block text-sm font-medium text-slate-700">Model / Tipe <span class="text-red-500">*</span></label>
                        <input type="text"
                               id="srv_model"
                               name="vehicle_model"
                               x-model="serviceForm.vehicle_model"
                               placeholder="Contoh: Mustang GT"
                               required
                               class="w-full px-4 py-2.5 rounded-lg border border-slate-300 bg-white text-slate-900 focus:ring-2 focus:ring-black focus:border-black text-sm transition">
                    </div>

                    <!-- Tahun Pembuatan -->
                    <div class="space-y-1.5">
                        <label for="srv_year" class="block text-sm font-medium text-slate-700">Tahun <span class="text-red-500">*</span></label>
                        <input type="number"
                               id="srv_year"
                               name="vehicle_year"
                               x-model="serviceForm.vehicle_year"
                               min="1990"
                               max="{{ date('Y') + 1 }}"
                               required
                               class="w-full px-4 py-2.5 rounded-lg border border-slate-300 bg-white text-slate-900 focus:ring-2 focus:ring-black focus:border-black text-sm transition">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Odometer KM -->
                    <div class="space-y-1.5">
                        <label for="srv_odometer" class="block text-sm font-medium text-slate-700">Odometer (KM) <span class="text-red-500">*</span></label>
                        <input type="number"
                               id="srv_odometer"
                               name="odometer_km"
                               x-model="serviceForm.odometer_km"
                               min="0"
                               placeholder="15000"
                               required
                               class="w-full px-4 py-2.5 rounded-lg border border-slate-300 bg-white text-slate-900 focus:ring-2 focus:ring-black focus:border-black text-sm transition">
                    </div>

                    <!-- Jenis Servis -->
                    <div class="space-y-1.5">
                        <label for="srv_type" class="block text-sm font-medium text-slate-700">Jenis Layanan <span class="text-red-500">*</span></label>
                        <select id="srv_type"
                                name="service_type"
                                x-model="serviceForm.service_type"
                                required
                                class="w-full px-4 py-2.5 rounded-lg border border-slate-300 bg-white text-slate-900 focus:ring-2 focus:ring-black focus:border-black text-sm transition">
                            <option value="regular">Servis Rutin / Berkala</option>
                            <option value="major">Servis Besar</option>
                            <option value="inspection">Inspeksi & Diagnosa</option>
                            <option value="warranty">Klaim Garansi</option>
                        </select>
                    </div>
                </div>

                <!-- Tanggal & Jam Servis -->
                <div class="space-y-1.5">
                    <label for="srv_date" class="block text-sm font-medium text-slate-700">Tanggal & Jam Kedatangan <span class="text-red-500">*</span></label>
                    <input type="datetime-local"
                           id="srv_date"
                           name="service_date"
                           x-model="serviceForm.service_date"
                           :min="minDate"
                           required
                           class="w-full px-4 py-2.5 rounded-lg border border-slate-300 bg-white text-slate-900 focus:ring-2 focus:ring-black focus:border-black text-sm transition">
                    <p class="text-[11px] text-slate-500">Jam operasional: 08:00 - 17:00 WIB (Maks 3 kendaraan per slot jam).</p>
                </div>

                <!-- Keluhan Kendaraan -->
                <div class="space-y-1.5">
                    <label for="srv_complaint" class="block text-sm font-medium text-slate-700">Keluhan atau Permintaan Khusus <span class="text-red-500">*</span></label>
                    <textarea id="srv_complaint"
                              name="complaint_notes"
                              x-model="serviceForm.complaint_notes"
                              rows="3"
                              minlength="10"
                              placeholder="Deskripsikan kondisi atau gejala kerusakan pada mobil Anda (minimal 10 karakter)..."
                              required
                              class="w-full px-4 py-2.5 rounded-lg border border-slate-300 bg-white text-slate-900 focus:ring-2 focus:ring-black focus:border-black text-sm transition"></textarea>
                </div>

                <!-- Submit Button -->
                <div class="pt-4 flex items-center justify-end gap-3">
                    <button type="button" @click="closeModals()" class="px-5 py-2.5 rounded-lg border border-slate-300 text-slate-700 font-semibold hover:bg-slate-50 text-sm transition">
                        Batal
                    </button>
                    <button type="submit"
                            :disabled="isSubmitting"
                            class="bg-blue-600 text-white px-6 py-2.5 rounded-lg font-semibold hover:bg-blue-700 text-sm transition flex items-center gap-2 disabled:opacity-50">
                        <span x-show="!isSubmitting">Konfirmasi Booking</span>
                        <span x-show="isSubmitting" x-cloak>Menyimpan...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Alpine.js Application Store & Logic -->
    <script>
        function showroomApp(config) {
            return {
                mobileMenuOpen: false,
                testDriveModalOpen: false,
                serviceModalOpen: false,
                selectedCarId: null,
                selectedCarName: '',
                isSubmitting: false,
                toasts: [],
                minDate: '',
                carCategoryFilter: 'all',
                partCategoryFilter: 'all',
                partSearch: '',

                testDriveForm: {
                    car_id: '',
                    preferred_date: '',
                    driver_license_id: '',
                    driver_license_image: null,
                    pickup_location: '',
                    notes: ''
                },

                serviceForm: {
                    vehicle_plate_number: '',
                    vehicle_model: '',
                    vehicle_year: new Date().getFullYear(),
                    odometer_km: '',
                    service_type: 'regular',
                    service_date: '',
                    complaint_notes: ''
                },

                init() {
                    // Set minDate to current local datetime
                    const now = new Date();
                    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
                    this.minDate = now.toISOString().slice(0, 16);

                    // Restore pending test drive session if present
                    if (config.pendingTestDrive) {
                        this.testDriveForm = { ...this.testDriveForm, ...config.pendingTestDrive };
                        this.selectedCarId = this.testDriveForm.car_id;
                    }

                    // Restore pending service booking session if present
                    if (config.pendingServiceBooking) {
                        this.serviceForm = { ...this.serviceForm, ...config.pendingServiceBooking };
                    }

                    // Auto-open modal if requested after login
                    if (config.initialModal === 'test_drive') {
                        this.testDriveModalOpen = true;
                    } else if (config.initialModal === 'service_booking') {
                        this.serviceModalOpen = true;
                    }

                    // Flash messages from server session
                    @if (session('success'))
                        this.addToast('{{ session('success') }}', 'success', 4000);
                    @endif
                    @if (session('error'))
                        this.addToast('{{ session('error') }}', 'error', 6000);
                    @endif
                    @if (session('info'))
                        this.addToast('{{ session('info') }}', 'info', 4000);
                    @endif
                },

                openTestDriveModal(carId = null, carName = '') {
                    if (carId) {
                        this.testDriveForm.car_id = carId;
                        this.selectedCarName = carName;
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
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(code);
                    }
                    this.addToast('Kode promo ' + code + ' berhasil disalin!', 'success', 3500);
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
                            // Guest flow: stored in session, redirect to login
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
                        // Reset form
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

                        if (response.status === 401 && data.redirect) {
                            // Guest flow: stored in session, redirect to login
                            window.location.href = data.redirect;
                            return;
                        }

                        if (!response.ok) {
                            const errMessage = data.message || (data.errors ? Object.values(data.errors)[0][0] : 'Terjadi kesalahan validasi.');
                            this.addToast(errMessage, 'error', 6000);
                            return;
                        }

                        this.addToast(data.message || 'Booking servis berhasil!', 'success', 5000);
                        this.closeModals();
                        // Reset form
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
    <!-- Auto-slide Showroom setiap 2 detik -->
    <script>
        (function() {
            let isPaused = false;

            document.addEventListener('DOMContentLoaded', function() {
                const swiperEl = document.querySelector('.showroom-swiper');
                if (swiperEl) {
                    swiperEl.addEventListener('mouseenter', function() { isPaused = true; });
                    swiperEl.addEventListener('mouseleave', function() { isPaused = false; });
                    swiperEl.addEventListener('touchstart', function() { isPaused = true; }, { passive: true });
                    swiperEl.addEventListener('touchend', function() {
                        setTimeout(function() { isPaused = false; }, 3000);
                    }, { passive: true });
                }

                // Otomatis bergeser setiap 2 detik (2000ms) sebagai fallback jika native autoplay belum aktif
                setInterval(function() {
                    if (isPaused || window.__showroom_has_autoplay) return;

                    // 1. Prioritaskan instance Swiper langsung jika tersedia
                    if (window.__showroomSwiper && typeof window.__showroomSwiper.slideNext === 'function') {
                        window.__showroomSwiper.slideNext();
                        return;
                    }
                    if (swiperEl && swiperEl.swiper && typeof swiperEl.swiper.slideNext === 'function') {
                        swiperEl.swiper.slideNext();
                        return;
                    }

                    // 2. Trigger tombol navigasi data-slide-next
                    const nextBtn = document.querySelector('[data-slide-next]');
                    if (nextBtn) {
                        nextBtn.click();
                    }
                }, 2000);
            });
        })();
    </script>
</body>
</html>
