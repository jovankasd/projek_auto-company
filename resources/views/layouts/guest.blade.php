<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? 'Autentikasi' }} : {{ config('app.name', 'Dealer Pro') }}</title>

        <!-- Scripts & Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-full font-sans antialiased text-slate-900 bg-slate-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8 selection:bg-black selection:text-white">
        <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
            <a href="{{ route('home') }}" class="inline-flex flex-col items-center group focus:outline-none focus-visible:ring-2 focus-visible:ring-black rounded-xl p-2 transition">
                <div class="h-12 w-auto flex items-center justify-center overflow-hidden group-hover:scale-105 transition duration-200">
                    <img src="{{ asset('images/logo.jpg') }}" alt="Dealer Pro" class="h-12 w-auto object-contain">
                </div>
                <span class="mt-3 font-black text-xl tracking-tight text-slate-900 uppercase">
                    Dealer Pro
                </span>
                <span class="font-mono text-xs tracking-widest text-slate-500 uppercase mt-0.5">
                    Showroom & Layanan Servis Resmi
                </span>
            </a>
        </div>

        <div class="mt-6 sm:mx-auto sm:w-full sm:max-w-md px-4 sm:px-0">
            <div class="bg-white py-8 px-6 sm:px-8 shadow-sm rounded-2xl border border-slate-200/80">
                {{ $slot }}
            </div>
            <p class="mt-6 text-center text-xs text-slate-500 font-medium">
                &copy; {{ date('Y') }} Dealer Pro. Hak cipta dilindungi undang-undang.
            </p>
        </div>
    </body>
</html>
