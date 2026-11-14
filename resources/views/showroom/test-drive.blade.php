<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Jadwalkan Test Drive — XYZ Motors</title>

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
                <li class="text-slate-900 font-semibold" aria-current="page">Jadwalkan Test Drive</li>
            </ol>
        </nav>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-xl p-6 sm:p-10">
            <div class="border-b border-slate-200 pb-6 mb-6">
                <span class="inline-block px-3 py-1 bg-blue-50 text-blue-700 text-xs font-bold uppercase tracking-wider rounded-full mb-2">
                    Layanan Resmi Dealer
                </span>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                    Jadwalkan Test Drive Kendaraan
                </h1>
                <p class="text-slate-600 text-sm mt-1.5">
                    Rasakan performa dan kenyamanan armada pilihan kami secara langsung dengan pendampingan profesional.
                </p>
            </div>

            <!-- Flash Alerts -->
            @if (session('success'))
                <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-200 text-green-800 text-sm font-medium flex items-center justify-between">
                    <span>{{ session('success') }}</span>
                    @auth
                        <a href="{{ route('test-drives.my') }}" class="underline font-bold hover:text-green-900 ml-4">Lihat Pengajuan Saya</a>
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
                                Anda dapat mengisi formulir sekarang. Data Anda akan disimpan di sesi peramban dan Anda akan diarahkan untuk masuk atau membuat akun gratis guna mengonfirmasi jadwal.
                            </p>
                        </div>
                    </div>
                </div>
            @endguest

            @if (session('pending_test_drive_license_reupload_required') || (!empty($pendingTestDrive) && empty($pendingTestDrive['driver_license_image'])))
                <div class="mb-6 p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs sm:text-sm">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-amber-600 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <p class="font-bold text-amber-900">Perhatian Berkas Foto SIM</p>
                            <p class="mt-0.5 text-amber-800 leading-relaxed">
                                Demi perlindungan privasi dan keamanan berkas Anda, berkas foto SIM tidak disimpan sebelum login. Silakan pilih dan unggah kembali berkas foto SIM Anda pada isian di bawah.
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Standard HTML Form (Works Without JavaScript) -->
            <form action="{{ route('test-drive.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <!-- Pilihan Mobil -->
                <div>
                    <label for="car_id" class="block text-sm font-semibold text-slate-900 mb-1.5">
                        Pilih Model Kendaraan <span class="text-red-600">*</span>
                    </label>
                    <select id="car_id"
                            name="car_id"
                            required
                            class="w-full px-4 py-3 rounded-xl border @error('car_id') border-red-500 @else border-slate-300 @enderror bg-white text-slate-900 focus:ring-2 focus:ring-blue-600 focus:border-blue-600 text-sm">
                        <option value="">-- Pilih Model Armada --</option>
                        @foreach ($cars as $car)
                            <option value="{{ $car->id }}" @selected((string) old('car_id', $pendingTestDrive['car_id'] ?? $selectedCarId) === (string) $car->id)>
                                {{ $car->brand }} {{ $car->model_name }} ({{ $car->year }}) — Rp {{ number_format($car->price, 0, ',', '.') }}
                            </option>
                        @endforeach
                    </select>
                    @error('car_id')
                        <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tanggal & Jam Preferensi -->
                <div>
                    <label for="preferred_date" class="block text-sm font-semibold text-slate-900 mb-1.5">
                        Tanggal &amp; Waktu Preferensi <span class="text-red-600">*</span>
                    </label>
                    <input type="datetime-local"
                           id="preferred_date"
                           name="preferred_date"
                           value="{{ old('preferred_date', $pendingTestDrive['preferred_date'] ?? '') }}"
                           required
                           class="w-full px-4 py-3 rounded-xl border @error('preferred_date') border-red-500 @else border-slate-300 @enderror bg-white text-slate-900 focus:ring-2 focus:ring-blue-600 focus:border-blue-600 text-sm">
                    <p class="mt-1 text-xs text-slate-500">Pilih waktu antara 1 hari hingga 90 hari ke depan.</p>
                    @error('preferred_date')
                        <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Nomor SIM -->
                <div>
                    <label for="driver_license_id" class="block text-sm font-semibold text-slate-900 mb-1.5">
                        Nomor Surat Izin Mengemudi (SIM A) <span class="text-red-600">*</span>
                    </label>
                    <input type="text"
                           id="driver_license_id"
                           name="driver_license_id"
                           value="{{ old('driver_license_id', $pendingTestDrive['driver_license_id'] ?? '') }}"
                           placeholder="Contoh: 1234-5678-9012"
                           minlength="5"
                           required
                           class="w-full px-4 py-3 rounded-xl border @error('driver_license_id') border-red-500 @else border-slate-300 @enderror bg-white text-slate-900 focus:ring-2 focus:ring-blue-600 focus:border-blue-600 text-sm">
                    @error('driver_license_id')
                        <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Foto SIM (Opsional) -->
                <div>
                    <label for="driver_license_image" class="block text-sm font-semibold text-slate-900 mb-1.5">
                        Unggah Foto SIM A (Opsional)
                    </label>
                    <input type="file"
                           id="driver_license_image"
                           name="driver_license_image"
                           accept="image/jpeg,image/png,image/jpg,image/webp"
                           class="w-full text-xs text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 cursor-pointer">
                    <p class="mt-1 text-xs text-slate-500">Format yang diterima: JPG, PNG, WebP (Maksimal 5MB).</p>
                    @error('driver_license_image')
                        <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Lokasi Pertemuan / Dealer -->
                <div>
                    <label for="pickup_location" class="block text-sm font-semibold text-slate-900 mb-1.5">
                        Lokasi Pertemuan atau Dealer (Opsional)
                    </label>
                    <input type="text"
                           id="pickup_location"
                           name="pickup_location"
                           value="{{ old('pickup_location', $pendingTestDrive['pickup_location'] ?? '') }}"
                           placeholder="Contoh: Dealer Pusat XYZ Motors / Alamat Kantor"
                           class="w-full px-4 py-3 rounded-xl border @error('pickup_location') border-red-500 @else border-slate-300 @enderror bg-white text-slate-900 focus:ring-2 focus:ring-blue-600 focus:border-blue-600 text-sm">
                    @error('pickup_location')
                        <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Catatan Tambahan -->
                <div>
                    <label for="notes" class="block text-sm font-semibold text-slate-900 mb-1.5">
                        Catatan Khusus (Opsional)
                    </label>
                    <textarea id="notes"
                              name="notes"
                              rows="3"
                              placeholder="Preferensi rute uji coba, waktu spesifik, atau informasi penting lainnya..."
                              class="w-full px-4 py-3 rounded-xl border @error('notes') border-red-500 @else border-slate-300 @enderror bg-white text-slate-900 focus:ring-2 focus:ring-blue-600 focus:border-blue-600 text-sm">{{ old('notes', $pendingTestDrive['notes'] ?? '') }}</textarea>
                    @error('notes')
                        <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Action Buttons -->
                <div class="pt-4 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-3">
                    <a href="{{ route('home') }}" class="w-full sm:w-auto px-6 py-3 rounded-xl border border-slate-300 text-slate-700 font-semibold hover:bg-slate-50 text-sm transition text-center">
                        Batal
                    </a>
                    <button type="submit" class="w-full sm:w-auto px-8 py-3 rounded-xl bg-slate-900 text-white font-bold hover:bg-blue-600 text-sm transition shadow-md text-center">
                        Kirim Pengajuan Test Drive
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
