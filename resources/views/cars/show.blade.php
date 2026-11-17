<!DOCTYPE html>
<html lang="id" class="h-full bg-gray-50 text-slate-900 scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $car->meta_title ?: "{$car->brand} {$car->model_name} ({$car->year}) - Spesifikasi & Harga Resmi | Dealer Pro" }}</title>
    <meta name="description" content="{{ $car->meta_description ?: ($car->description ?: "Spesifikasi resmi {$car->brand} {$car->model_name} tahun {$car->year}. Cek harga OTR, performa mesin, dan jadwalkan test drive di Dealer Pro AUTO COMPANY.") }}">
    <link rel="canonical" href="{{ url()->current() }}">

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
    @php
        $carSchema = [
            '@context' => 'https://schema.org',
            '@type' => ['Product', 'Car'],
            'name' => "{$car->brand} {$car->model_name}",
            'brand' => [
                '@type' => 'Brand',
                'name' => $car->brand,
            ],
            'model' => $car->model_name,
            'modelDate' => (string) $car->year,
            'description' => $car->description ?: 'Unit kendaraan premium resmi Dealer Pro AUTO COMPANY.',
            'image' => $car->hero_image_url,
            'offers' => [
                '@type' => 'Offer',
                'priceCurrency' => 'IDR',
                'price' => (string) (int) $car->price,
                'availability' => 'https://schema.org/InStock',
                'itemCondition' => 'https://schema.org/NewCondition',
                'url' => url()->current(),
            ],
        ];
    @endphp
    <script type="application/ld+json">@json($carSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)</script>

    <x-font-head />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-slate-50 font-sans text-slate-900 antialiased overflow-x-hidden selection:bg-blue-600 selection:text-white"
      x-data="carDetailApp({
          carId: {{ $car->id }},
          carName: @js($car->brand . ' ' . $car->model_name),
          pendingTestDrive: @js($pendingTestDrive ?? null),
          pendingServiceBooking: @js($pendingServiceBooking ?? null)
      })"
      :class="{ 'overflow-hidden': testDriveModalOpen || serviceModalOpen }">

    <!-- Accessibility: Skip to Content -->
    <a href="#main-content"
       class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-[70] focus:px-4 focus:py-2 focus:bg-slate-900 focus:text-white focus:rounded-xl focus:shadow-lg">
        Lewati ke konten utama
    </a>

    <x-public-navbar />

    <main id="main-content" class="pt-10 sm:pt-14 pb-20">
        <!-- Breadcrumb & Back Link -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-6">
            <nav class="flex items-center gap-2 text-xs font-medium text-slate-600" aria-label="Breadcrumb">
                <a href="{{ route('home') }}" class="hover:text-slate-900 transition">Beranda</a>
                <span class="text-slate-400" aria-hidden="true">/</span>
                <a href="{{ route('home') }}#katalog-mobil" class="hover:text-slate-900 transition">Katalog Unit</a>
                <span class="text-slate-400" aria-hidden="true">/</span>
                <span class="font-bold text-slate-900" aria-current="page">{{ $car->brand }} {{ $car->model_name }}</span>
            </nav>
        </div>

        <!-- Studio Showcase Header Section -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="relative rounded-3xl bg-white border border-slate-200/80 overflow-hidden shadow-sm">
                <!-- Large Subtle Brand Backdrop -->
                <div class="absolute -top-10 -right-10 select-none pointer-events-none text-[9rem] sm:text-[14rem] font-black text-slate-100/90 tracking-tighter leading-none z-0" aria-hidden="true">
                    {{ strtoupper($car->brand) }}
                </div>

                <div class="relative z-10 grid grid-cols-1 lg:grid-cols-12 divide-y lg:divide-y-0 lg:divide-x divide-slate-200/80">
                    <!-- Left (7 Cols): Studio Image Stage -->
                    <div class="lg:col-span-7 p-6 sm:p-10 lg:p-12 flex flex-col justify-between bg-gradient-to-b from-slate-50/80 to-white">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <span class="font-mono text-xs font-bold uppercase tracking-widest text-blue-600">
                                {{ $car->brand }} RESMI / TAHUN {{ $car->year }}
                            </span>
                            <span class="inline-flex items-center rounded-full font-mono text-[11px] font-bold uppercase tracking-wider px-3.5 py-1 border border-slate-200/80 bg-white text-slate-800 shadow-sm">
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
                        <div class="flex flex-wrap items-center justify-between gap-4 pt-5 border-t border-slate-200/80">
                            <div class="flex items-center gap-2.5">
                                <span class="text-xs font-semibold text-slate-600 uppercase tracking-wider">Warna:</span>
                                @if($car->color_name)
                                    <span class="inline-flex items-center gap-2 rounded-full font-bold text-xs text-slate-800 bg-slate-100 px-3.5 py-1.5 border border-slate-200">
                                        <span class="w-3.5 h-3.5 rounded-full border border-slate-300" style="background-color: {{ $car->color_hex ?: '#0f172a' }}"></span>
                                        {{ $car->color_name }}
                                    </span>
                                @else
                                    <span class="font-mono text-xs font-bold text-slate-700">Standar Pabrik</span>
                                @endif
                            </div>

                            <span class="inline-flex items-center gap-1.5 rounded-full font-mono text-xs font-bold px-3.5 py-1.5 bg-emerald-50 text-emerald-800 border border-emerald-200">
                                <svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
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
                                <span class="font-mono text-xs font-bold uppercase tracking-widest text-slate-600">Unit Baru Resmi Dealer Pro</span>
                                <h1 class="font-display text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900 mt-1.5">
                                    {{ $car->brand }} {{ $car->model_name }}
                                </h1>
                            </div>

                            @if($car->promos->isNotEmpty())
                                <div class="rounded-2xl bg-blue-50/90 border border-blue-200 p-4 space-y-1.5">
                                    <div class="text-[11px] font-bold uppercase tracking-wider text-blue-700">Program Penawaran Aktif</div>
                                    @foreach($car->promos as $promo)
                                        <div class="text-xs font-semibold text-slate-900">
                                            {{ $promo->title }} (Kode: <span class="font-mono font-bold text-blue-700">{{ $promo->code }}</span>)
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
                        <div class="rounded-2xl p-5 bg-slate-50 border border-slate-200/80 space-y-2">
                            <span class="block text-xs font-bold uppercase tracking-wider text-slate-600">
                                Harga Resmi OTR Jakarta (Estimasi)
                            </span>
                            <div class="font-mono text-3xl sm:text-4xl font-extrabold text-slate-900 tabular-nums">
                                {{ $car->price_formatted }}
                            </div>
                            <p class="text-[11px] text-slate-600">
                                *Harga belum termasuk potongan program berjalan atau skema pembiayaan khusus showroom.
                            </p>
                        </div>

                        <!-- CTA Actions Suite -->
                        <div class="space-y-3 pt-4 border-t border-slate-200/80">
                            <button type="button"
                                    @click="openTestDriveModal()"
                                    class="w-full min-h-[48px] py-3.5 px-6 rounded-xl bg-slate-900 hover:bg-blue-600 text-white text-xs sm:text-sm font-bold uppercase tracking-wider transition-all duration-200 text-center shadow-sm hover:shadow-lg hover:scale-[1.01] active:scale-[0.99]">
                                Test Drive Unit Ini
                            </button>

                            <button type="button"
                                    @click="openServiceModal()"
                                    class="w-full min-h-[48px] py-3.5 px-6 rounded-xl border border-slate-300 hover:border-slate-900 hover:bg-slate-900 hover:text-white text-slate-900 text-xs sm:text-sm font-bold uppercase tracking-wider transition-all duration-200 text-center bg-white hover:scale-[1.01] active:scale-[0.99]">
                                Booking Servis Kendaraan Ini
                            </button>

                            <a href="https://wa.me/6281234567890?text={{ urlencode('Halo Dealer Pro, saya ingin konsultasi mengenai unit ' . $car->brand . ' ' . $car->model_name . ' (' . $car->year . ')') }}"
                               target="_blank"
                               rel="noopener noreferrer"
                               class="w-full min-h-[44px] inline-flex items-center justify-center gap-2 py-3 px-4 rounded-xl border border-emerald-600 text-emerald-800 hover:bg-emerald-50 text-xs font-bold uppercase tracking-wider transition-all duration-200">
                                <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
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
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-14 space-y-10">
            <div>
                <span class="font-mono text-xs font-bold uppercase tracking-widest text-blue-600">Spesifikasi Mesin &amp; Performa</span>
                <h2 class="font-display text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight mt-1">
                    Telemetri Performa &amp; Rekayasa Pabrikan
                </h2>
            </div>

            <!-- 4-Column Telemetry Cards -->
            <dl class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="rounded-2xl bg-white border border-slate-200/80 p-5 sm:p-6 shadow-sm">
                    <dt class="text-xs font-bold uppercase tracking-wider text-slate-600">Tenaga Mesin</dt>
                    <dd class="font-mono text-2xl sm:text-3xl font-extrabold text-slate-900 mt-2 tabular-nums">
                        {{ $car->engine_specs['power'] ?? '-' }}
                    </dd>
                    <span class="block text-xs text-slate-600 mt-1.5">Output Maksimum HP/kW</span>
                </div>
                <div class="rounded-2xl bg-white border border-slate-200/80 p-5 sm:p-6 shadow-sm">
                    <dt class="text-xs font-bold uppercase tracking-wider text-slate-600">Torsi Maksimum</dt>
                    <dd class="font-mono text-2xl sm:text-3xl font-extrabold text-slate-900 mt-2 tabular-nums">
                        {{ $car->engine_specs['torque'] ?? '-' }}
                    </dd>
                    <span class="block text-xs text-slate-600 mt-1.5">Puncak Torsi Putaran</span>
                </div>
                <div class="rounded-2xl bg-white border border-slate-200/80 p-5 sm:p-6 shadow-sm">
                    <dt class="text-xs font-bold uppercase tracking-wider text-slate-600">Akselerasi 0-100 KM/J</dt>
                    <dd class="font-mono text-2xl sm:text-3xl font-extrabold text-slate-900 mt-2 tabular-nums">
                        {{ $car->engine_specs['accel'] ?? '-' }}
                    </dd>
                    <span class="block text-xs text-slate-600 mt-1.5">Uji Lintasan Standar</span>
                </div>
                <div class="rounded-2xl bg-white border border-slate-200/80 p-5 sm:p-6 shadow-sm">
                    <dt class="text-xs font-bold uppercase tracking-wider text-slate-600">Sistem Transmisi</dt>
                    <dd class="font-mono text-2xl sm:text-3xl font-extrabold text-slate-900 mt-2 tabular-nums">
                        {{ $car->engine_specs['trans'] ?? '-' }}
                    </dd>
                    <span class="block text-xs text-slate-600 mt-1.5">Penggerak Roda &amp; Girboks</span>
                </div>
            </dl>

            <!-- Dimensions and Features Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                <!-- Dimensions (5 Cols) -->
                <div class="lg:col-span-5 rounded-2xl bg-white border border-slate-200/80 p-6 sm:p-8 shadow-sm space-y-6">
                    <h3 class="font-display text-base font-extrabold uppercase tracking-tight text-slate-900 border-b border-slate-200 pb-3">
                        Dimensi &amp; Kapasitas Bodi
                    </h3>

                    @if(!empty($car->dimensions) && is_array($car->dimensions))
                        <dl class="divide-y divide-slate-200 text-sm">
                            @foreach($car->dimensions as $dimKey => $dimVal)
                                <div class="py-2.5 flex items-center justify-between gap-4">
                                    <dt class="text-slate-600 uppercase font-semibold text-xs">{{ ucwords(str_replace('_', ' ', $dimKey)) }}</dt>
                                    <dd class="font-mono font-bold text-slate-900">{{ $dimVal }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    @else
                        <dl class="divide-y divide-slate-200 text-sm">
                            <div class="py-2.5 flex items-center justify-between gap-4">
                                <dt class="text-slate-600 text-xs font-semibold">Tipe Bodi / Sasis</dt>
                                <dd class="font-bold text-slate-900">{{ $car->category->label() }}</dd>
                            </div>
                            <div class="py-2.5 flex items-center justify-between gap-4">
                                <dt class="text-slate-600 text-xs font-semibold">Tahun Produksi</dt>
                                <dd class="font-mono font-bold text-slate-900">{{ $car->year }}</dd>
                            </div>
                            <div class="py-2.5 flex items-center justify-between gap-4">
                                <dt class="text-slate-600 text-xs font-semibold">Standar Emisi &amp; Sertifikasi</dt>
                                <dd class="font-bold text-slate-900">Euro 5 / Standar Pabrikan</dd>
                            </div>
                        </dl>
                    @endif
                </div>

                <!-- Features Checklist (7 Cols) -->
                <div class="lg:col-span-7 rounded-2xl bg-white border border-slate-200/80 p-6 sm:p-8 shadow-sm space-y-6">
                    <h3 class="font-display text-base font-extrabold uppercase tracking-tight text-slate-900 border-b border-slate-200 pb-3">
                        Fitur Utama &amp; Paket Keselamatan
                    </h3>

                    @if(!empty($car->features) && is_array($car->features))
                        <ul class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                            @foreach($car->features as $feat)
                                <li class="flex items-start gap-2.5 text-slate-800">
                                    <svg class="w-4 h-4 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="font-medium">{{ $feat }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-sm text-slate-600">
                            Fitur standar pabrik lengkap dengan sistem keselamatan aktif, kontrol traksi elektronik, dan sistem infotainment terintegrasi.
                        </p>
                    @endif
                </div>
            </div>
        </section>

        <!-- Other Available Units Section -->
        @if($relatedCars->isNotEmpty())
            <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-16 pt-12 border-t border-slate-200">
                <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
                    <div>
                        <span class="font-mono text-xs font-bold uppercase tracking-widest text-slate-600">Rekomendasi Showroom</span>
                        <h2 class="font-display text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight mt-1">
                            Unit Kendaraan Pilihan Lainnya
                        </h2>
                    </div>
                    <a href="{{ route('home') }}#katalog-mobil" class="inline-flex items-center min-h-[44px] px-3 text-xs font-bold uppercase tracking-wider text-blue-600 hover:text-slate-900 transition">
                        Semua Unit &rarr;
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach($relatedCars as $relCar)
                        <div class="rounded-3xl bg-white border border-slate-200/80 p-6 flex flex-col justify-between shadow-sm hover:shadow-xl transition-all duration-200">
                            <div class="space-y-4">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="font-mono font-bold text-slate-600">{{ $relCar->year }}</span>
                                    <span class="inline-flex items-center rounded-full font-mono font-bold uppercase px-2.5 py-0.5 bg-slate-100 text-slate-700 border border-slate-200">
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
                                    <h3 class="font-display text-lg font-bold text-slate-900">{{ $relCar->brand }} {{ $relCar->model_name }}</h3>
                                    <p class="font-mono text-base font-extrabold text-slate-900 mt-2 tabular-nums">{{ $relCar->price_formatted }}</p>
                                </div>
                            </div>
                            <div class="pt-4 mt-6 border-t border-slate-200/80">
                                <a href="{{ route('cars.public.show', $relCar->slug) }}"
                                   class="flex items-center justify-center w-full min-h-[44px] py-2.5 rounded-xl bg-slate-900 hover:bg-blue-600 text-white text-xs font-bold uppercase tracking-wider transition-all duration-200 hover:scale-[1.02] active:scale-[0.98]">
                                    Lihat Detail Spesifikasi
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
    </main>

    <x-public-footer />

    <!-- Toast Notifications -->
    <div class="fixed top-24 right-4 sm:right-6 z-40 space-y-3 max-w-sm w-full pointer-events-none"
         role="status"
         aria-live="polite">
        <template x-for="toast in toasts" :key="toast.id">
            <div x-show="toast.visible"
                 class="pointer-events-auto p-4 rounded-2xl shadow-lg border text-sm flex items-start gap-3"
                 :class="{
                     'bg-emerald-50 border-emerald-200 text-emerald-900': toast.type === 'success',
                     'bg-red-50 border-red-200 text-red-900': toast.type === 'error',
                     'bg-blue-50 border-blue-200 text-blue-900': toast.type === 'info'
                 }">
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

    <!-- Modal A: Test Drive Form -->
    <div x-show="testDriveModalOpen"
         x-cloak
         role="dialog"
         aria-modal="true"
         aria-labelledby="test-drive-title"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
         @keydown.escape.window="closeModals()">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" @click="closeModals()"></div>

        <div class="relative w-full max-w-lg rounded-3xl bg-white border border-slate-200/80 shadow-2xl p-6 sm:p-8 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between pb-4 border-b border-slate-200/80">
                <div>
                    <h3 id="test-drive-title" class="font-display text-lg font-extrabold text-slate-900">Jadwalkan Test Drive Resmi</h3>
                    <p class="text-xs text-slate-600 mt-0.5">Unit: <span class="font-bold text-slate-900">{{ $car->brand }} {{ $car->model_name }}</span></p>
                </div>
                <button type="button" @click="closeModals()" class="min-w-[44px] min-h-[44px] flex items-center justify-center rounded-xl text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition">
                    <span class="sr-only">Tutup</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="{{ route('test-drive.store') }}"
                  method="POST"
                  enctype="multipart/form-data"
                  @submit.prevent="submitTestDrive"
                  class="mt-6 space-y-4">
                @csrf
                <input type="hidden" name="car_id" value="{{ $car->id }}">

                @guest
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="td_customer_name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                Nama Lengkap <span class="text-red-600">*</span>
                            </label>
                            <input type="text"
                                   id="td_customer_name"
                                   name="customer_name"
                                   x-model="testDriveForm.customer_name"
                                   placeholder="Nama sesuai KTP / SIM"
                                   required
                                   class="w-full min-h-[44px] px-3.5 py-2.5 text-sm border border-slate-200/90 rounded-xl focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 focus:outline-none shadow-sm">
                        </div>
                        <div>
                            <label for="td_customer_phone" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                Nomor WhatsApp / HP <span class="text-red-600">*</span>
                            </label>
                            <input type="tel"
                                   id="td_customer_phone"
                                   name="customer_phone"
                                   x-model="testDriveForm.customer_phone"
                                   placeholder="081234567890"
                                   required
                                   class="w-full min-h-[44px] px-3.5 py-2.5 text-sm border border-slate-200/90 rounded-xl focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 focus:outline-none shadow-sm">
                        </div>
                    </div>
                @endguest

                <div>
                    <label for="td_date" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Pilihan Tanggal &amp; Waktu <span class="text-red-600">*</span>
                    </label>
                    <input type="datetime-local"
                           id="td_date"
                           name="preferred_date"
                           x-model="testDriveForm.preferred_date"
                           :min="minDateTime"
                           required
                           class="w-full min-h-[44px] px-3.5 py-2.5 text-sm border border-slate-200/90 rounded-xl focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 focus:outline-none shadow-sm">
                </div>

                <div>
                    <label for="td_pickup" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Lokasi Test Drive
                    </label>
                    <input type="text"
                           id="td_pickup"
                           name="pickup_location"
                           x-model="testDriveForm.pickup_location"
                           placeholder="Showroom Pusat atau alamat antar"
                           class="w-full min-h-[44px] px-3.5 py-2.5 text-sm border border-slate-200/90 rounded-xl focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 focus:outline-none shadow-sm">
                </div>

                <div>
                    <label for="td_license_num" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Nomor SIM A <span class="text-red-600">*</span>
                    </label>
                    <input type="text"
                           id="td_license_num"
                           name="driver_license_id"
                           x-model="testDriveForm.driver_license_id"
                           placeholder="Contoh: 1234-5678-9012"
                           required
                           class="w-full min-h-[44px] px-3.5 py-2.5 text-sm border border-slate-200/90 rounded-xl focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 focus:outline-none shadow-sm">
                </div>

                <div>
                    <label for="td_license_img" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Foto SIM A (JPG / PNG / WebP, Maks 5MB)
                    </label>
                    <input type="file"
                           id="td_license_img"
                           name="driver_license_image"
                           accept="image/jpeg,image/png,image/webp"
                           class="w-full text-xs text-slate-700 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-900 file:text-white hover:file:bg-blue-600 transition cursor-pointer">
                </div>

                <div>
                    <label for="td_notes" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Catatan Khusus (Opsional)
                    </label>
                    <textarea id="td_notes"
                              name="notes"
                              x-model="testDriveForm.notes"
                              rows="2"
                              placeholder="Kebutuhan khusus atau pertanyaan sebelum sesi..."
                              class="w-full px-3.5 py-2.5 text-sm border border-slate-200/90 rounded-xl focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 focus:outline-none shadow-sm"></textarea>
                </div>

                <div class="pt-4">
                    <button type="submit"
                            :disabled="submitting"
                            class="w-full min-h-[48px] py-3.5 px-4 rounded-xl bg-slate-900 hover:bg-blue-600 text-white text-xs sm:text-sm font-semibold tracking-wide transition-all duration-200 text-center disabled:opacity-50 shadow-sm hover:shadow-md hover:scale-[1.01] active:scale-[0.99]">
                        <span x-show="!submitting">Kirim Pengajuan Test Drive</span>
                        <span x-show="submitting" x-cloak>Memproses...</span>
                    </button>
                </div>
                @guest
                    <p class="text-center text-xs text-slate-600 pt-1">
                        Sudah punya nomor tiket pengajuan?
                        <a href="{{ route('home') }}#lacak-tiket" class="font-bold text-blue-700 hover:underline">Lacak dengan Nomor Tiket</a>
                    </p>
                @endguest
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

        <div class="relative w-full max-w-lg rounded-3xl bg-white border border-slate-200/80 shadow-2xl p-6 sm:p-8 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between pb-4 border-b border-slate-200/80">
                <div>
                    <h3 id="service-modal-title" class="font-display text-lg font-extrabold text-slate-900">Reservasi Slot Bengkel Resmi</h3>
                    <p class="text-xs text-slate-600 mt-0.5">Maksimal 3 slot/jam dengan kuota otomatis</p>
                </div>
                <button type="button" @click="closeModals()" class="min-w-[44px] min-h-[44px] flex items-center justify-center rounded-xl text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition">
                    <span class="sr-only">Tutup</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="{{ route('service-booking.store') }}"
                  method="POST"
                  @submit.prevent="submitService"
                  class="mt-6 space-y-4">
                @csrf
                @guest
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="srv_customer_name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                Nama Lengkap Pemilik <span class="text-red-600">*</span>
                            </label>
                            <input type="text"
                                   id="srv_customer_name"
                                   name="customer_name"
                                   x-model="serviceForm.customer_name"
                                   placeholder="Nama lengkap Anda"
                                   required
                                   class="w-full min-h-[44px] px-3.5 py-2.5 text-sm border border-slate-200/90 rounded-xl focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 focus:outline-none shadow-sm">
                        </div>
                        <div>
                            <label for="srv_customer_phone" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                Nomor WhatsApp / HP <span class="text-red-600">*</span>
                            </label>
                            <input type="tel"
                                   id="srv_customer_phone"
                                   name="customer_phone"
                                   x-model="serviceForm.customer_phone"
                                   placeholder="081234567890"
                                   required
                                   class="w-full min-h-[44px] px-3.5 py-2.5 text-sm border border-slate-200/90 rounded-xl focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 focus:outline-none shadow-sm">
                        </div>
                    </div>
                @endguest
                <div>
                    <label for="srv_vehicle_model" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Model Kendaraan <span class="text-red-600">*</span>
                    </label>
                    <input type="text"
                           id="srv_vehicle_model"
                           name="vehicle_model"
                           x-model="serviceForm.vehicle_model"
                           required
                           class="w-full min-h-[44px] px-3.5 py-2.5 text-sm border border-slate-200/90 rounded-xl focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 focus:outline-none shadow-sm">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="srv_plate" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Nomor Plat Polisi <span class="text-red-600">*</span>
                        </label>
                        <input type="text"
                               id="srv_plate"
                               name="vehicle_plate_number"
                               x-model="serviceForm.vehicle_plate_number"
                               placeholder="B 1234 XYZ"
                               required
                               class="w-full min-h-[44px] px-3.5 py-2.5 text-sm border border-slate-200/90 rounded-xl focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 focus:outline-none shadow-sm uppercase">
                    </div>
                    <div>
                        <label for="srv_year" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Tahun Kendaraan <span class="text-red-600">*</span>
                        </label>
                        <input type="number"
                               id="srv_year"
                               name="vehicle_year"
                               x-model="serviceForm.vehicle_year"
                               min="1990"
                               max="{{ date('Y') + 1 }}"
                               required
                               class="w-full min-h-[44px] px-3.5 py-2.5 text-sm border border-slate-200/90 rounded-xl focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 focus:outline-none shadow-sm">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="srv_date" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Tanggal Servis <span class="text-red-600">*</span>
                        </label>
                        <input type="date"
                               id="srv_date"
                               name="service_date"
                               x-model="serviceForm.service_date"
                               min="{{ date('Y-m-d') }}"
                               required
                               class="w-full min-h-[44px] px-3.5 py-2.5 text-sm border border-slate-200/90 rounded-xl focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 focus:outline-none shadow-sm">
                    </div>
                    <div>
                        <label for="srv_slot" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Slot Jam Kedatangan <span class="text-red-600">*</span>
                        </label>
                        <select id="srv_slot"
                                name="slot_hour"
                                x-model="serviceForm.slot_hour"
                                required
                                class="w-full min-h-[44px] px-3.5 py-2.5 text-sm border border-slate-200/90 rounded-xl focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 focus:outline-none shadow-sm">
                            @for($h = 8; $h <= 17; $h++)
                                <option value="{{ $h }}">{{ sprintf('%02d:00 WIB', $h) }}</option>
                            @endfor
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="srv_odometer" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Odometer (KM) <span class="text-red-600">*</span>
                        </label>
                        <input type="number"
                               id="srv_odometer"
                               name="odometer_km"
                               x-model="serviceForm.odometer_km"
                               min="0"
                               max="999999"
                               placeholder="Contoh: 15000"
                               required
                               class="w-full min-h-[44px] px-3.5 py-2.5 text-sm border border-slate-200/90 rounded-xl focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 focus:outline-none shadow-sm">
                    </div>
                    <div>
                        <label for="srv_type" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Kategori Layanan <span class="text-red-600">*</span>
                        </label>
                        <select id="srv_type"
                                name="service_type"
                                x-model="serviceForm.service_type"
                                required
                                class="w-full min-h-[44px] px-3.5 py-2.5 text-sm border border-slate-200/90 rounded-xl focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 focus:outline-none shadow-sm">
                            <option value="regular">Servis Berkala / Tune-Up</option>
                            <option value="major">Servis Besar (Major)</option>
                            <option value="inspection">Pemeriksaan / Inspeksi</option>
                            <option value="warranty">Garansi &amp; Pemasangan</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="srv_complaint" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Keluhan atau Catatan Perawatan <span class="text-red-600">*</span>
                    </label>
                    <textarea id="srv_complaint"
                              name="complaint_notes"
                              x-model="serviceForm.complaint_notes"
                              rows="3"
                              placeholder="Deskripsikan keluhan atau bagian yang ingin diperiksa..."
                              required
                              class="w-full px-3.5 py-2.5 text-sm border border-slate-200/90 rounded-xl focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 focus:outline-none shadow-sm"></textarea>
                </div>

                <div class="pt-4">
                    <button type="submit"
                            :disabled="submitting"
                            class="w-full min-h-[48px] py-3.5 px-4 rounded-xl bg-slate-900 hover:bg-blue-600 text-white text-xs sm:text-sm font-semibold tracking-wide transition-all duration-200 text-center disabled:opacity-50 shadow-sm hover:shadow-md hover:scale-[1.01] active:scale-[0.99]">
                        <span x-show="!submitting">Konfirmasi Reservasi Bengkel</span>
                        <span x-show="submitting" x-cloak>Memproses...</span>
                    </button>
                </div>
                @guest
                    <p class="text-center text-xs text-slate-600 pt-1">
                        Sudah punya nomor tiket servis?
                        <a href="{{ route('home') }}#lacak-tiket" class="font-bold text-blue-700 hover:underline">Lacak dengan Nomor Tiket</a>
                    </p>
                @endguest
            </form>
        </div>
    </div>

    <!-- Modal C: Bukti Nomor Tiket Reservasi -->
    <div x-show="createdTicketModalOpen"
         x-cloak
         role="dialog"
         aria-modal="true"
         aria-labelledby="car-ticket-proof-title"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
         @keydown.escape.window="createdTicketModalOpen = false">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="createdTicketModalOpen = false"></div>
        <div class="relative w-full max-w-md rounded-3xl bg-white p-6 sm:p-8 shadow-2xl border border-slate-200/80 z-10 text-center space-y-5">
            <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center mx-auto">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <div>
                <h3 id="car-ticket-proof-title" class="font-display text-xl font-bold text-slate-900">Reservasi Berhasil Tercatat</h3>
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
                <a :href="'{{ route('home') }}?ticket=' + encodeURIComponent(createdTicketNumber) + '#lacak-tiket'"
                   class="w-full min-h-[44px] inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-slate-900 text-white text-xs font-bold uppercase tracking-wider transition shadow-sm">
                    Cek Status Sekarang
                </a>
            </div>
        </div>
    </div>

    <script>
        function carDetailApp(config) {
            return {
                testDriveModalOpen: false,
                serviceModalOpen: false,
                createdTicketModalOpen: false,
                createdTicketNumber: '',
                submitting: false,
                minDateTime: (() => {
                    const now = new Date();
                    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
                    return now.toISOString().slice(0, 16);
                })(),
                toasts: [],
                testDriveForm: {
                    customer_name: '',
                    customer_phone: '',
                    preferred_date: config.pendingTestDrive?.preferred_date || '',
                    pickup_location: config.pendingTestDrive?.pickup_location || 'Showroom Pusat Dealer Pro',
                    driver_license_id: config.pendingTestDrive?.driver_license_id || '',
                    notes: config.pendingTestDrive?.notes || ''
                },
                serviceForm: {
                    customer_name: '',
                    customer_phone: '',
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
                copyTicketNumber(code) {
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(code).catch(() => {});
                    }
                    this.addToast('Nomor tiket ' + code + ' berhasil disalin!', 'success');
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
                            if (data.ticket_number) {
                                this.createdTicketNumber = data.ticket_number;
                                this.createdTicketModalOpen = true;
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
                            if (data.ticket_number) {
                                this.createdTicketNumber = data.ticket_number;
                                this.createdTicketModalOpen = true;
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
