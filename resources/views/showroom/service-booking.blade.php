<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Booking Servis Kendaraan — XYZ Motors</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900&display=swap" rel="stylesheet" />

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans text-slate-900 bg-slate-50 antialiased selection:bg-blue-600 selection:text-white min-h-screen flex flex-col justify-between">
    <!-- Header Minimalis -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-2 text-slate-900 font-black text-xl tracking-tight">
                <span>XYZ <span class="text-blue-600">MOTORS</span></span>
            </a>
            <div class="flex items-center gap-4 text-xs font-semibold uppercase tracking-wider">
                <a href="{{ route('home') }}" class="text-slate-600 hover:text-slate-900 transition flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Kembali ke Showroom</span>
                </a>
                @guest
                    <a href="{{ route('login') }}" class="text-slate-700 hover:text-slate-900 transition px-2 py-1">Masuk</a>
                    <a href="{{ route('register') }}" class="text-blue-600 hover:text-blue-700 transition px-2 py-1 font-bold">Daftar</a>
                @else
                    <a href="{{ route('dashboard') }}" class="text-blue-600 hover:text-slate-900 transition font-bold">Dashboard</a>
                @endguest
            </div>
        </div>
    </header>

    <main class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10 w-full flex-1">
        <!-- Breadcrumb / Backlink -->
        <nav class="mb-6 text-xs font-medium text-slate-500" aria-label="Breadcrumb">
            <ol class="flex items-center gap-2">
                <li><a href="{{ route('home') }}" class="hover:text-slate-800 transition">Beranda</a></li>
                <li class="text-slate-400">/</li>
                <li class="text-slate-900 font-semibold" aria-current="page">Booking Servis Kendaraan</li>
            </ol>
        </nav>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-xl p-6 sm:p-10">
            <div class="border-b border-slate-200 pb-6 mb-6">
                <span class="inline-block px-3 py-1 bg-blue-50 text-blue-700 text-xs font-bold uppercase tracking-wider rounded-full mb-2">
                    Bengkel Resmi &amp; Garansi OEM
                </span>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                    Reservasi Servis Kendaraan
                </h1>
                <p class="text-slate-600 text-sm mt-1.5">
                    Perawatan berkala, perbaikan mekanikal, dan suku cadang asli bergaransi dengan kuota terkendali (maks. 3 kendaraan per slot jam).
                </p>
            </div>

            <!-- Flash Alerts -->
            @if (session('success'))
                <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-200 text-green-800 text-sm font-medium flex items-center justify-between">
                    <span>{{ session('success') }}</span>
                    @auth
                        <a href="{{ route('services.my') }}" class="underline font-bold hover:text-green-900 ml-4">Lacak Servis Saya</a>
                    @endauth
                </div>
            @endif

            @if (session('info'))
                <div class="mb-6 p-4 rounded-xl bg-blue-50 border border-blue-200 text-blue-900 text-sm">
                    <p class="font-semibold">{{ session('info') }}</p>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm">
                    <p class="font-bold">Mohon periksa data formulir Anda:</p>
                    <ul class="list-disc list-inside mt-1.5 text-xs space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @guest
                <div class="mb-6 p-4 rounded-xl bg-blue-50 border border-blue-200 text-blue-900 text-xs sm:text-sm">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-blue-600 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <p class="font-bold text-blue-900">Alur Reservasi Pengunjung (Guest)</p>
                            <p class="mt-0.5 text-blue-800 leading-relaxed">
                                Anda dapat melengkapi rincian kendaraan dan tanggal sekarang. Setelah dikirim, Anda akan diarahkan untuk masuk atau membuat akun gratis agar tiket servis terbit resmi atas nama Anda.
                            </p>
                        </div>
                    </div>
                </div>
            @endguest

            <!-- Standard HTML Form (Works Without JavaScript) -->
            <form action="{{ route('service-booking.store') }}" method="POST" class="space-y-6">
                @csrf

                <!-- Nomor Plat Kendaraan -->
                <div>
                    <label for="vehicle_plate_number" class="block text-sm font-semibold text-slate-900 mb-1.5">
                        Nomor Plat Kendaraan <span class="text-red-600">*</span>
                    </label>
                    <input type="text"
                           id="vehicle_plate_number"
                           name="vehicle_plate_number"
                           value="{{ old('vehicle_plate_number', $pendingServiceBooking['vehicle_plate_number'] ?? '') }}"
                           placeholder="Contoh: B 1234 XYZ"
                           required
                           class="w-full px-4 py-3 rounded-xl border @error('vehicle_plate_number') border-red-500 @else border-slate-300 @enderror bg-white text-slate-900 focus:ring-2 focus:ring-blue-600 focus:border-blue-600 text-sm uppercase font-mono">
                    <p class="mt-1 text-xs text-slate-500">Format plat nomor Indonesia (contoh: B 1234 ABC).</p>
                    @error('vehicle_plate_number')
                        <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Model Kendaraan -->
                    <div>
                        <label for="vehicle_model" class="block text-sm font-semibold text-slate-900 mb-1.5">
                            Model / Tipe Kendaraan <span class="text-red-600">*</span>
                        </label>
                        <input type="text"
                               id="vehicle_model"
                               name="vehicle_model"
                               value="{{ old('vehicle_model', $pendingServiceBooking['vehicle_model'] ?? ($selectedModel ?? '')) }}"
                               placeholder="Contoh: Mustang GT"
                               required
                               class="w-full px-4 py-3 rounded-xl border @error('vehicle_model') border-red-500 @else border-slate-300 @enderror bg-white text-slate-900 focus:ring-2 focus:ring-blue-600 focus:border-blue-600 text-sm">
                        @error('vehicle_model')
                            <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Tahun Pembuatan -->
                    <div>
                        <label for="vehicle_year" class="block text-sm font-semibold text-slate-900 mb-1.5">
                            Tahun Pembuatan <span class="text-red-600">*</span>
                        </label>
                        <input type="number"
                               id="vehicle_year"
                               name="vehicle_year"
                               value="{{ old('vehicle_year', $pendingServiceBooking['vehicle_year'] ?? date('Y')) }}"
                               min="1990"
                               max="{{ date('Y') + 1 }}"
                               required
                               class="w-full px-4 py-3 rounded-xl border @error('vehicle_year') border-red-500 @else border-slate-300 @enderror bg-white text-slate-900 focus:ring-2 focus:ring-blue-600 focus:border-blue-600 text-sm">
                        @error('vehicle_year')
                            <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Odometer KM -->
                    <div>
                        <label for="odometer_km" class="block text-sm font-semibold text-slate-900 mb-1.5">
                            Jarak Tempuh / Odometer (KM) <span class="text-red-600">*</span>
                        </label>
                        <input type="number"
                               id="odometer_km"
                               name="odometer_km"
                               value="{{ old('odometer_km', $pendingServiceBooking['odometer_km'] ?? '') }}"
                               min="0"
                               placeholder="15000"
                               required
                               class="w-full px-4 py-3 rounded-xl border @error('odometer_km') border-red-500 @else border-slate-300 @enderror bg-white text-slate-900 focus:ring-2 focus:ring-blue-600 focus:border-blue-600 text-sm">
                        @error('odometer_km')
                            <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Jenis Servis -->
                    <div>
                        <label for="service_type" class="block text-sm font-semibold text-slate-900 mb-1.5">
                            Jenis Layanan Servis <span class="text-red-600">*</span>
                        </label>
                        <select id="service_type"
                                name="service_type"
                                required
                                class="w-full px-4 py-3 rounded-xl border @error('service_type') border-red-500 @else border-slate-300 @enderror bg-white text-slate-900 focus:ring-2 focus:ring-blue-600 focus:border-blue-600 text-sm">
                            @php
                                $currentType = old('service_type', $pendingServiceBooking['service_type'] ?? 'regular');
                            @endphp
                            <option value="regular" @selected($currentType === 'regular')>Servis Rutin / Berkala</option>
                            <option value="major" @selected($currentType === 'major')>Servis Besar</option>
                            <option value="inspection" @selected($currentType === 'inspection')>Inspeksi & Diagnosa</option>
                            <option value="warranty" @selected($currentType === 'warranty')>Klaim Garansi</option>
                        </select>
                        @error('service_type')
                            <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Tanggal & Jam Servis -->
                <div>
                    <label for="service_date" class="block text-sm font-semibold text-slate-900 mb-1.5">
                        Tanggal &amp; Waktu Kedatangan <span class="text-red-600">*</span>
                    </label>
                    <input type="datetime-local"
                           id="service_date"
                           name="service_date"
                           value="{{ old('service_date', $pendingServiceBooking['service_date'] ?? '') }}"
                           required
                           class="w-full px-4 py-3 rounded-xl border @error('service_date') border-red-500 @else border-slate-300 @enderror bg-white text-slate-900 focus:ring-2 focus:ring-blue-600 focus:border-blue-600 text-sm">
                    <p class="mt-1 text-xs text-slate-500">Jam operasional bengkel: 08:00 - 17:00 WIB (Maksimal 3 kendaraan per slot jam).</p>
                    @error('service_date')
                        <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Keluhan atau Catatan Servis -->
                <div>
                    <label for="complaint_notes" class="block text-sm font-semibold text-slate-900 mb-1.5">
                        Keluhan atau Catatan Servis <span class="text-red-600">*</span>
                    </label>
                    <textarea id="complaint_notes"
                              name="complaint_notes"
                              rows="3"
                              minlength="10"
                              placeholder="Deskripsikan kondisi, gejala kendala, atau permintaan khusus pada mobil Anda (minimal 10 karakter)..."
                              required
                              class="w-full px-4 py-3 rounded-xl border @error('complaint_notes') border-red-500 @else border-slate-300 @enderror bg-white text-slate-900 focus:ring-2 focus:ring-blue-600 focus:border-blue-600 text-sm">{{ old('complaint_notes', $pendingServiceBooking['complaint_notes'] ?? ($complaintNotes ?? '')) }}</textarea>
                    @error('complaint_notes')
                        <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Action Buttons -->
                <div class="pt-4 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-3">
                    <a href="{{ route('home') }}" class="w-full sm:w-auto px-6 py-3 rounded-xl border border-slate-300 text-slate-700 font-semibold hover:bg-slate-50 text-sm transition text-center">
                        Batal
                    </a>
                    <button type="submit" class="w-full sm:w-auto px-8 py-3 rounded-xl bg-blue-600 text-white font-bold hover:bg-blue-700 text-sm transition shadow-md text-center">
                        Konfirmasi Booking Servis
                    </button>
                </div>
            </form>
        </div>
    </main>

    <!-- Footer Sederhana -->
    <footer class="bg-white border-t border-slate-200 py-6 text-center text-xs text-slate-500">
        <div class="max-w-5xl mx-auto px-4">
            <p>&copy; {{ date('Y') }} XYZ Motors. Seluruh hak cipta dilindungi undang-undang.</p>
        </div>
    </footer>
</body>
</html>
