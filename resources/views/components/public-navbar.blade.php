@props(['onShowroom' => false])

<header x-data="{ mobileMenuOpen: false }"
        @keydown.escape.window="mobileMenuOpen = false"
        class="sticky top-0 z-40 h-[72px] backdrop-blur-md bg-white/80 border-b border-slate-200/80 transition-colors">
    <div class="max-w-7xl mx-auto h-full px-4 sm:px-6 lg:px-8 flex items-center justify-between gap-4">
        <!-- Brand Logo -->
        <a href="{{ route('home') }}"
           class="flex items-center gap-3 shrink-0 min-h-[44px] pr-2 rounded-xl focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2">
            <img src="{{ asset('images/logo.jpg') }}"
                 alt="Dealer Pro"
                 class="h-9 w-9 rounded-xl object-cover border border-slate-200/80 shadow-sm">
            <div>
                <span class="font-display font-extrabold text-base sm:text-lg tracking-tight text-slate-900 leading-none block">
                    Dealer Pro
                </span>
                <span class="text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-600 leading-none block mt-0.5">
                    AUTO COMPANY
                </span>
            </div>
        </a>

        <!-- Desktop Navigation -->
        <nav aria-label="Navigasi Utama" class="hidden lg:flex items-center gap-1">
            @php
                $prefix = $onShowroom ? '' : route('home');
                $navItems = [
                    ['label' => 'Showroom', 'href' => $onShowroom ? '#main-showroom' : route('home'), 'active' => request()->routeIs('home')],
                    ['label' => 'Katalog Mobil', 'href' => $prefix . '#katalog-mobil', 'active' => request()->routeIs('cars.public.show')],
                    ['label' => 'Suku Cadang', 'href' => $prefix . '#katalog-suku-cadang', 'active' => false],
                    ['label' => 'Bengkel Resmi', 'href' => $prefix . '#layanan-bengkel', 'active' => false],
                    ['label' => 'Promo', 'href' => $prefix . '#promo', 'active' => false],
                    ['label' => 'Hubungi Kami', 'href' => route('contact.index'), 'active' => request()->routeIs('contact.*')],
                ];
            @endphp

            @foreach($navItems as $item)
                <a href="{{ $item['href'] }}"
                   class="inline-flex items-center whitespace-nowrap px-3.5 py-2 rounded-xl text-sm font-semibold transition-all duration-200 min-h-[44px] focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-1 {{ $item['active'] ? 'bg-blue-50 text-blue-700 ring-1 ring-blue-200/80' : 'text-slate-700 hover:text-slate-900 hover:bg-slate-100/80' }}">
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>

        <!-- Actions & Mobile Toggle -->
        <div class="flex items-center gap-2 sm:gap-3 shrink-0">
            @auth
                <a href="{{ route('dashboard') }}"
                   class="hidden sm:inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-blue-600 text-white text-xs sm:text-sm font-semibold shadow-sm hover:shadow-md hover:scale-[1.02] active:scale-[0.98] transition-all duration-200 min-h-[44px] focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2">
                    <span>Dashboard</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </a>
            @else
                <a href="{{ $prefix }}#lacak-tiket"
                   class="hidden sm:inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-blue-600 text-white text-xs sm:text-sm font-semibold shadow-sm hover:shadow-md hover:scale-[1.02] active:scale-[0.98] transition-all duration-200 min-h-[44px] focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <span>Cek Tiket</span>
                </a>
            @endauth

            <button type="button"
                    @click="mobileMenuOpen = !mobileMenuOpen"
                    :aria-expanded="mobileMenuOpen.toString()"
                    aria-controls="public-mobile-drawer"
                    aria-label="Toggle menu navigasi"
                    class="lg:hidden inline-flex items-center justify-center min-w-[44px] min-h-[44px] rounded-xl border border-slate-200/80 bg-white/90 text-slate-800 hover:bg-slate-100 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600">
                <svg x-show="!mobileMenuOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                <svg x-show="mobileMenuOpen" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- Mobile Navigation Drawer -->
    <div id="public-mobile-drawer"
         x-show="mobileMenuOpen"
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="lg:hidden absolute top-[72px] inset-x-0 bg-white/95 backdrop-blur-md border-b border-slate-200/80 shadow-xl z-40">
        <nav aria-label="Navigasi Mobile" class="max-w-7xl mx-auto px-4 py-4 space-y-1.5">
            @foreach($navItems as $item)
                <a href="{{ $item['href'] }}"
                   @click="mobileMenuOpen = false"
                   class="flex items-center justify-between px-4 py-3 rounded-xl text-sm font-semibold min-h-[44px] transition {{ $item['active'] ? 'bg-blue-50 text-blue-700' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900' }}">
                    <span>{{ $item['label'] }}</span>
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            @endforeach

            <div class="pt-3 mt-2 border-t border-slate-200/80">
                @auth
                    <a href="{{ route('dashboard') }}"
                       @click="mobileMenuOpen = false"
                       class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-slate-900 hover:bg-blue-600 text-white text-sm font-semibold min-h-[44px] transition">
                        Buka Dashboard
                    </a>
                @else
                    <a href="{{ $prefix }}#lacak-tiket"
                       @click="mobileMenuOpen = false"
                       class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-slate-900 hover:bg-blue-600 text-white text-sm font-semibold min-h-[44px] transition">
                        Cek Status Tiket
                    </a>
                @endauth
            </div>
        </nav>
    </div>
</header>
