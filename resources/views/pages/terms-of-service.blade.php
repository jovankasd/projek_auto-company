<!DOCTYPE html>
<html lang="id" class="h-full bg-gray-50 text-slate-900 scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Syarat dan Ketentuan — Dealer Pro (XYZ Motors)</title>
    <meta name="description" content="Syarat dan Ketentuan resmi penggunaan layanan showroom mobil, pengajuan test drive, dan pemesanan booking servis di bengkel resmi Dealer Pro XYZ Motors.">
    <link rel="canonical" href="{{ route('terms-of-service') }}">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="article">
    <meta property="og:url" content="{{ route('terms-of-service') }}">
    <meta property="og:title" content="Syarat dan Ketentuan — Dealer Pro XYZ Motors">
    <meta property="og:description" content="Syarat dan Ketentuan resmi penggunaan layanan showroom mobil, pengajuan test drive, dan pemesanan booking servis di bengkel resmi Dealer Pro XYZ Motors.">
    <meta property="og:image" content="{{ asset('images/logo.jpg') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full font-sans antialiased text-slate-800 bg-slate-50 selection:bg-black selection:text-white">
    <!-- Header Navigasi -->
    <header class="sticky top-0 z-30 bg-white/95 backdrop-blur-md border-b border-slate-200">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                <img src="{{ asset('images/logo.jpg') }}" alt="Dealer Pro" class="h-8 w-auto object-contain">
                <span class="font-extrabold text-base tracking-tight text-slate-900 uppercase">Dealer Pro</span>
            </a>
            <div class="flex items-center gap-4 text-xs sm:text-sm font-semibold">
                <a href="{{ route('home') }}" class="text-slate-600 hover:text-slate-900 transition flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Kembali ke Showroom
                </a>
            </div>
        </div>
    </header>

    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14">
        <!-- Breadcrumbs -->
        <nav aria-label="Breadcrumb" class="mb-6">
            <ol class="flex items-center space-x-2 text-xs font-mono text-slate-500">
                <li><a href="{{ route('home') }}" class="hover:text-slate-900 transition">Beranda</a></li>
                <li><span class="text-slate-400">/</span></li>
                <li class="text-slate-800 font-semibold" aria-current="page">Syarat dan Ketentuan</li>
            </ol>
        </nav>

        <article class="bg-white border border-slate-200 rounded-xl p-6 sm:p-10 shadow-sm space-y-8">
            <header class="border-b border-slate-200 pb-6 space-y-2">
                <span class="inline-block px-2.5 py-1 text-[11px] font-mono uppercase tracking-wider font-semibold bg-slate-100 text-slate-700 rounded">
                    Ketentuan Layanan Resmi
                </span>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                    Syarat dan Ketentuan
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 font-mono">
                    Terakhir diperbarui: 14 November 2026 &bull; XYZ Motors Indonesia
                </p>
            </header>

            <section class="space-y-4 text-sm leading-relaxed text-slate-700">
                <h2 class="text-lg font-bold text-slate-900">1. Ketentuan Umum Portal</h2>
                <p>
                    Dengan mengakses portal Dealer Pro (XYZ Motors), pengguna menyetujui seluruh ketentuan operasional di bawah ini. Layanan digital mencakup penelusuran showroom visual, pemesanan jadwal uji coba kendaraan (test drive), pendaftaran booking perawatan servis berkala, serta pengecekan ketersediaan suku cadang resmi.
                </p>
            </section>

            <section class="space-y-4 text-sm leading-relaxed text-slate-700">
                <h2 class="text-lg font-bold text-slate-900">2. Ketentuan Uji Coba Kendaraan (Test Drive)</h2>
                <div class="space-y-2 text-xs sm:text-sm text-slate-600">
                    <p>Setiap pemohon jadwal test drive wajib mematuhi persyaratan keselamatan berikut:</p>
                    <ul class="list-disc pl-5 space-y-1.5">
                        <li>Pemohon berusia minimal 17 tahun dan memiliki Surat Izin Mengemudi golongan A (SIM A) yang masih sah dan berlaku di wilayah Republik Indonesia.</li>
                        <li>Pemohon bersedia melampirkan foto dokumen fisik SIM A asli untuk proses verifikasi legalitas berkendara sebelum unit kendaraan dapat dicoba di jalan raya.</li>
                        <li>Sesi test drive didampingi langsung oleh staf konsultan penjualan resmi XYZ Motors pada rute uji coba yang telah ditentukan demi alasan keselamatan bersama.</li>
                        <li>Pengemudi wajib mematuhi rambu-rambu lalu lintas, batas kecepatan jalan umum, serta petunjuk keselamatan dari pendamping teknis.</li>
                    </ul>
                </div>

                <div class="mt-4 p-4 bg-amber-50 border border-amber-200 rounded-lg text-amber-900 text-xs sm:text-sm space-y-2">
                    <p class="font-bold flex items-center gap-2">
                        <svg class="w-4 h-4 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Catatan Pengesahan Batas Tanggung Jawab Asuransi
                    </p>
                    <p class="font-mono text-xs text-amber-800">
                        [PERLU PENGESAHAN PEMILIK: Penetapan besaran klausul risiko sendiri (deductible) atau pertanggungan pihak ketiga jika terjadi kelalaian mengemudi atau insiden jalan raya selama sesi uji coba berlangsung. Saat ini unit dilindungi asuransi komprehensif dasar dealer.]
                    </p>
                </div>
            </section>

            <section class="space-y-4 text-sm leading-relaxed text-slate-700">
                <h2 class="text-lg font-bold text-slate-900">3. Ketentuan Reservasi Servis Bengkel</h2>
                <div class="space-y-2 text-xs sm:text-sm text-slate-600">
                    <p>Untuk menjamin mutu pengerjaan mekanik dan ketepatan estimasi waktu pengerjaan:</p>
                    <ul class="list-disc pl-5 space-y-1.5">
                        <li>Bengkel resmi menerapkan batas kuota maksimal kendaraan pada setiap slot jam operasional (08:00 – 17:00 WIB).</li>
                        <li>Setiap pengajuan booking servis yang terkonfirmasi akan mendapatkan nomor tiket unik harian berformat <code class="bg-slate-100 px-1 py-0.5 rounded font-mono">SRV-YYYYMMDD-XXXX</code>.</li>
                        <li>Satu nomor polisi hanya dapat memesan satu slot pengerjaan pada tanggal dan jam yang sama untuk mencegah duplikasi antrean bengkel.</li>
                        <li>Penambahan suku cadang dan jasa perbaikan di luar estimasi awal akan dikonfirmasikan kepada pelanggan sebelum tindakan mekanik dilanjutkan.</li>
                    </ul>
                </div>
            </section>

            <section class="space-y-4 text-sm leading-relaxed text-slate-700">
                <h2 class="text-lg font-bold text-slate-900">4. Pembatalan dan Penjadwalan Ulang</h2>
                <p class="text-xs sm:text-sm text-slate-600">
                    Pelanggan dapat membatalkan atau mengubah jadwal booking servis dan pengajuan test drive melalui panel akun pengguna sebelum status pengerjaan beralih ke tahap pelaksanaan mekanik atau penyerahan unit.
                </p>
            </section>

            <section class="space-y-4 text-sm leading-relaxed text-slate-700">
                <h2 class="text-lg font-bold text-slate-900">5. Penyelesaian Perselisihan</h2>
                <p class="text-xs sm:text-sm text-slate-600">
                    Setiap perselisihan yang timbul dari penafsiran atau pelaksanaan ketentuan ini akan diselesaikan terlebih dahulu melalui musyawarah untuk mufakat secara kekeluargaan.
                </p>

                <div class="p-4 bg-amber-50 border border-amber-200 rounded-lg text-amber-900 text-xs sm:text-sm space-y-2">
                    <p class="font-bold flex items-center gap-2">
                        <svg class="w-4 h-4 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Catatan Klausul Yurisdiksi Hukum
                    </p>
                    <p class="font-mono text-xs text-amber-800">
                        [PERLU PENGESAHAN PEMILIK: Penetapan pilihan forum penyelesaian hukum dan domisili pengadilan yang disepakati (misalnya Pengadilan Negeri Jakarta Selatan) apabila musyawarah mufakat tidak tercapai.]
                    </p>
                </div>
            </section>
        </article>
    </main>

    <footer class="border-t border-slate-200 bg-white py-8 text-center text-xs text-slate-500">
        <p>&copy; {{ date('Y') }} Dealer Pro (XYZ Motors Indonesia). Seluruh hak cipta dilindungi.</p>
    </footer>
</body>
</html>
