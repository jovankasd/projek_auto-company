<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'XYZ Motors') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-slate-900 bg-slate-50 antialiased selection:bg-blue-600 selection:text-white">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-8 sm:pt-0 bg-slate-50 px-4 py-8">
            <div class="text-center">
                <a href="/" class="inline-flex flex-col items-center gap-1 focus:outline-none focus:ring-2 focus:ring-blue-600 rounded-lg p-2">
                    <span class="text-2xl font-black tracking-tight text-slate-900">XYZ <span class="text-blue-600">MOTORS</span></span>
                    <span class="text-[11px] uppercase font-bold tracking-widest text-slate-500">Official Dealer &amp; Service Workshop</span>
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-6 bg-white border border-slate-200/80 shadow-xl overflow-hidden rounded-2xl">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
