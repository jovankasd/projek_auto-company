<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">

    <title>{{ $title ?? 'Dashboard' }} — {{ config('app.name', 'Dealer Pro') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased text-slate-800 bg-slate-50/50" x-data="{ sidebarOpen: false }">
    <!-- Skip to content for Accessibility -->
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-50 focus:px-4 focus:py-2 focus:bg-black focus:text-white focus:rounded-lg shadow-lg">
        Lewati ke konten utama
    </a>

    <!-- Mobile Sidebar Drawer Backdrop -->
    <div x-show="sidebarOpen" 
         x-cloak
         @click="sidebarOpen = false" 
         class="fixed inset-0 z-40 bg-slate-900/40 backdrop-blur-sm lg:hidden transition-opacity"
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
    </div>

    <!-- Sidebar Container -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
           class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-slate-200 transition-transform duration-300 ease-in-out flex flex-col">
        <!-- Brand Header -->
        <div class="h-16 flex items-center justify-between px-6 border-b border-slate-100">
            <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                <div class="h-8 rounded-lg overflow-hidden flex items-center justify-center group-hover:scale-105 transition duration-200">
                    <img src="{{ asset('images/logo.jpg') }}" alt="Dealer Pro" class="h-full w-auto object-contain">
                </div>
                <div class="leading-none">
                    <span class="font-extrabold text-base tracking-tight text-slate-900">Dealer Pro</span>
                    <span class="block text-[10px] uppercase tracking-widest text-slate-400 font-semibold mt-0.5">Control Panel</span>
                </div>
            </a>
            <button @click="sidebarOpen = false" class="lg:hidden p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Role Indicator Pill -->
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full {{ auth()->user()->isOwner() ? 'bg-purple-600' : (auth()->user()->isSuperAdmin() ? 'bg-slate-900' : (auth()->user()->isSales() ? 'bg-emerald-500' : (auth()->user()->isMekanik() ? 'bg-amber-500' : 'bg-blue-500'))) }}"></span>
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Mode Akses</span>
            </div>
            <div class="mt-1 font-bold text-sm text-slate-800 flex items-center justify-between">
                <span>{{ auth()->user()->role instanceof \App\Enums\UserRole ? auth()->user()->role->label() : ucfirst((string) (auth()->user()->role ?? 'User')) }}</span>
                <span class="text-[11px] px-2 py-0.5 rounded-full font-medium border {{ auth()->user()->role instanceof \App\Enums\UserRole ? auth()->user()->role->color() : 'bg-slate-100 text-slate-700' }}">
                    Aktif
                </span>
            </div>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 overflow-y-auto px-4 py-4 space-y-1">
            <!-- Link Dashboard Utama -->
            <a href="{{ route('dashboard') }}" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('dashboard') ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                <svg class="w-5 h-5 opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span>Dashboard</span>
            </a>

            <!-- Link Pemberitahuan (Semua Role) -->
            <a href="{{ route('notifications.index') }}" 
               class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('notifications.*') ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <span>Notifikasi</span>
                </div>
                @if(auth()->user()->unreadNotifications->count() > 0)
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-red-500 text-white">
                        {{ auth()->user()->unreadNotifications->count() }}
                    </span>
                @endif
            </a>

            {{-- MENU CUSTOMER --}}
            @if(auth()->user()->isCustomer())
                <div class="pt-4 pb-1 px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">Layanan Saya</div>
                <a href="{{ route('services.my') }}" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('services.my') ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                    <svg class="w-5 h-5 opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    <span>Servis Berkala</span>
                </a>
                <a href="{{ route('test-drives.my') }}" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('test-drives.my') ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                    <svg class="w-5 h-5 opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span>Test Drive Saya</span>
                </a>
            @endif

            {{-- MENU ADMIN MEKANIK --}}
            @if(auth()->user()->isMekanik())
                <div class="pt-4 pb-1 px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">Bengkel & Suku Cadang</div>
                <a href="{{ route('dashboard') }}" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('dashboard') ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                    <svg class="w-5 h-5 opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    <span>Work Order Unit</span>
                </a>
                <a href="{{ route('spare-parts.index') }}" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('spare-parts.*') ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                    <svg class="w-5 h-5 opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span>Suku Cadang (Sparepart)</span>
                </a>
            @endif

            {{-- MENU SALES & SUPER ADMIN --}}
            @if(auth()->user()->isSales() || auth()->user()->isSuperAdmin())
                <div class="pt-4 pb-1 px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">Master Data</div>
                <a href="{{ route('cars.index') }}" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('cars.*') ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                    <svg class="w-5 h-5 opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                    </svg>
                    <span>Katalog Mobil</span>
                </a>
                <a href="{{ route('promos.index') }}" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('promos.*') ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                    <svg class="w-5 h-5 opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                    <span>Program Promo</span>
                </a>
            @endif

            {{-- MENU KHUSUS OWNER --}}
            @if(auth()->user()->isOwner())
                <div class="pt-4 pb-1 px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">Eksekutif</div>
                <a href="{{ route('reports.index') }}" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('reports.*') ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                    <svg class="w-5 h-5 opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    <span>Laporan & Analitik</span>
                </a>
            @endif

            {{-- MENU KHUSUS SUPER ADMIN --}}
            @if(auth()->user()->isSuperAdmin())
                <div class="pt-4 pb-1 px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">Administrasi</div>
                <a href="{{ route('users.index') }}" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('users.*') ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                    <svg class="w-5 h-5 opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <span>Manajemen User</span>
                </a>
                <a href="{{ route('reports.index') }}" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('reports.*') ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                    <svg class="w-5 h-5 opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    <span>Laporan & Analitik</span>
                </a>
                <a href="{{ route('audit.index') }}" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('audit.*') ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                    <svg class="w-5 h-5 opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Audit Log</span>
                </a>
                <a href="{{ route('settings.edit') }}" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('settings.*') ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                    <svg class="w-5 h-5 opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span>Pengaturan Sistem</span>
                </a>
            @endif

            <div class="pt-4 pb-1 px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">Eksternal</div>
            <a href="{{ route('home') }}" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition">
                <svg class="w-5 h-5 opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                </svg>
                <span>Lihat Showroom</span>
            </a>
        </nav>

        <!-- User Profile Footer -->
        <div class="p-4 border-t border-slate-100 bg-white">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-slate-900 text-white flex items-center justify-center font-bold text-sm">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold text-slate-900 truncate">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-slate-500 truncate">{{ auth()->user()->email }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" title="Keluar" class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Content Wrapper -->
    <div class="lg:pl-64 flex flex-col min-h-screen">
        <!-- Topbar -->
        <header class="h-16 bg-white/80 backdrop-blur-md border-b border-slate-200 sticky top-0 z-30 flex items-center justify-between px-6 lg:px-8">
            <div class="flex items-center gap-4">
                <button @click="sidebarOpen = true" class="lg:hidden p-2 text-slate-600 hover:bg-slate-100 rounded-lg transition">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <!-- Breadcrumb -->
                <nav class="flex items-center gap-2 text-xs font-medium text-slate-500">
                    <a href="{{ route('dashboard') }}" class="hover:text-slate-900 transition">Dashboard</a>
                    @isset($breadcrumb)
                        <svg class="w-3.5 h-3.5 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                        <span class="text-slate-900 font-semibold truncate max-w-xs">{{ $breadcrumb }}</span>
                    @endisset
                </nav>
            </div>

            <!-- Topbar Right Items -->
            <!-- Topbar Right Items -->
            <div class="flex items-center gap-3">
                <!-- Notifications Bell with 60s Polling -->
                <div class="relative" x-data="topbarNotification({{ auth()->user()->unreadNotifications->count() }})" x-init="initBell()">
                    <button @click="open = !open" 
                            class="relative p-2 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-lg transition"
                            aria-label="Pemberitahuan">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <span x-show="unreadCount > 0" x-cloak class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-red-500 ring-2 ring-white"></span>
                    </button>

                    <!-- Dropdown Notif -->
                    <div x-show="open" 
                         x-cloak 
                         @click.outside="open = false"
                         class="absolute right-0 mt-2 w-80 rounded-2xl bg-white border border-slate-200 shadow-xl py-2 z-50">
                        <div class="px-4 py-2 border-b border-slate-100 flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-700">Notifikasi</span>
                            <span class="text-[11px] font-semibold text-slate-400" x-text="unreadCount + ' Baru'"></span>
                        </div>
                        <div class="max-h-72 overflow-y-auto divide-y divide-slate-50">
                            <template x-for="item in recentItems" :key="item.id">
                                <a :href="item.url" class="block p-3 text-xs hover:bg-slate-50 transition">
                                    <p class="font-bold text-slate-800" x-text="item.title"></p>
                                    <p class="text-[11px] text-slate-600 truncate mt-0.5" x-text="item.message"></p>
                                    <span class="text-[10px] text-slate-400 mt-1 block" x-text="item.created_at_human"></span>
                                </a>
                            </template>
                            <div x-show="recentItems.length === 0" class="p-6 text-center text-xs text-slate-400">
                                Tidak ada pemberitahuan baru.
                            </div>
                        </div>
                        <div class="p-2 border-t border-slate-100 text-center bg-slate-50/50">
                            <a href="{{ route('notifications.index') }}" class="text-xs font-bold text-slate-900 hover:underline">
                                Buka Pusat Pemberitahuan &rarr;
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Live Clock or Quick Status -->
                <div class="hidden sm:block text-right border-l border-slate-200 pl-4">
                    <p class="text-xs font-bold text-slate-800">{{ now()->translatedFormat('l, d M Y') }}</p>
                    <p class="text-[11px] text-slate-400 font-mono">{{ now()->format('H:i') }} WIB</p>
                </div>
            </div>
        </header>

        <!-- Flash Messages & Toasts -->
        @if(session('success'))
            <div class="mx-6 lg:mx-8 mt-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold flex items-center justify-between" role="status">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900">&times;</button>
            </div>
        @endif

        @if(session('error'))
            <div class="mx-6 lg:mx-8 mt-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm font-semibold flex items-center justify-between" role="alert">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-red-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-red-600 hover:text-red-900">&times;</button>
            </div>
        @endif

        @if(session('info'))
            <div class="mx-6 lg:mx-8 mt-6 p-4 rounded-xl bg-blue-50 border border-blue-200 text-blue-800 text-sm font-semibold flex items-center justify-between" role="status">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-blue-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('info') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-blue-600 hover:text-blue-900">&times;</button>
            </div>
        @endif

        <!-- Main Body Content -->
        <main id="main-content" class="flex-1 max-w-[1440px] w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
            {{ $slot }}
        </main>
    </div>

    <!-- Topbar Notification Polling Script (60 detik) -->
    <script>
        function topbarNotification(initialCount) {
            return {
                open: false,
                unreadCount: initialCount,
                recentItems: [],
                pollingInterval: null,

                initBell() {
                    this.fetchUnread();
                    // Polling setiap 60 detik (PRD §7)
                    this.pollingInterval = setInterval(() => {
                        this.fetchUnread();
                    }, 60000);
                },

                async fetchUnread() {
                    try {
                        const response = await fetch('/dashboard/notifications/unread-count', {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        if (response.ok) {
                            const data = await response.json();
                            this.unreadCount = data.count;
                            if (data.recent && data.recent.length > 0) {
                                this.recentItems = data.recent;
                            }
                        }
                    } catch (e) {
                        // Silent fail for polling
                    }
                }
            };
        }
    </script>
</body>
</html>
