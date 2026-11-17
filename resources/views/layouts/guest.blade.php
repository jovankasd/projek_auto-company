<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="robots" content="noindex, nofollow">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <x-font-head />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-slate-900 antialiased bg-slate-50 selection:bg-blue-600 selection:text-white">
        <div class="min-h-screen flex flex-col sm:justify-center items-center px-4 py-8 sm:pt-0 bg-slate-50">
            <div class="mb-6">
                <a href="{{ route('home') }}" class="flex items-center gap-3 group rounded-xl focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2 p-1">
                    <img src="{{ asset('images/logo.jpg') }}" alt="Dealer Pro" class="h-10 w-10 rounded-xl object-cover border border-slate-200 shadow-sm">
                    <div>
                        <span class="font-display font-extrabold text-xl tracking-tight text-slate-900 block leading-none">Dealer Pro</span>
                        <span class="text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-600 block mt-1">AUTO COMPANY Portal</span>
                    </div>
                </a>
            </div>

            <div class="w-full sm:max-w-md px-6 py-7 sm:px-8 sm:py-8 bg-white border border-slate-200/80 shadow-sm overflow-hidden rounded-3xl">
                {{ $slot }}
            </div>

            <p class="mt-6 text-xs text-slate-600 text-center">
                <a href="{{ route('home') }}" class="hover:text-blue-700 underline underline-offset-4 py-2 inline-block">
                    Kembali ke Showroom Utama
                </a>
            </p>
        </div>
    </body>
</html>
