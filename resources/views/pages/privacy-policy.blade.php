<!DOCTYPE html>
<html lang="id" class="h-full bg-gray-50 text-slate-900 scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Kebijakan Privasi — Dealer Pro (XYZ Motors)</title>
    <meta name="description" content="Kebijakan Privasi resmi Dealer Pro XYZ Motors. Informasi transparan mengenai pengumpulan, perlindungan data booking servis, verifikasi foto SIM test drive, dan hak privasi pengguna.">
    <link rel="canonical" href="{{ route('privacy-policy') }}">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="article">
    <meta property="og:url" content="{{ route('privacy-policy') }}">
    <meta property="og:title" content="Kebijakan Privasi — Dealer Pro XYZ Motors">
    <meta property="og:description" content="Informasi transparan mengenai pengumpulan, perlindungan data booking servis, verifikasi foto SIM test drive, dan hak privasi pengguna.">
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
                <li class="text-slate-800 font-semibold" aria-current="page">Kebijakan Privasi</li>
            </ol>
        </nav>

        <article class="bg-white border border-slate-200 rounded-xl p-6 sm:p-10 shadow-sm space-y-8">
            <header class="border-b border-slate-200 pb-6 space-y-2">
                <span class="inline-block px-2.5 py-1 text-[11px] font-mono uppercase tracking-wider font-semibold bg-slate-100 text-slate-700 rounded">
                    Dokumen Tata Kelola Data
                </span>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                    Kebijakan Privasi
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 font-mono">
                    Terakhir diperbarui: 14 November 2026 &bull; Berlaku untuk seluruh layanan digital XYZ Motors
                </p>
            </header>

            <section class="space-y-4 text-sm leading-relaxed text-slate-700">
                <h2 class="text-lg font-bold text-slate-900">1. Pendahuluan</h2>
                <p>
                    Dealer Pro (XYZ Motors Indonesia) berkomitmen melindungi kerahasiaan dan keamanan data pribadi pelanggan. Kebijakan ini menjelaskan jenis informasi yang kami kumpulkan saat Anda menggunakan portal showroom, mengajukan uji coba kendaraan (test drive), menjadwalkan booking servis bengkel resmi, maupun mengirimkan pertanyaan melalui formulir kontak.
                </p>
            </section>

            <section class="space-y-4 text-sm leading-relaxed text-slate-700">
                <h2 class="text-lg font-bold text-slate-900">2. Data Pribadi yang Kami Kumpulkan</h2>
                <p>
                    Kami hanya mengumpulkan data yang relevan dan dibutuhkan untuk menjalankan operasional otomotif dan layanan purnajual:
                </p>
                <div class="space-y-3 pl-2">
                    <div class="border-l-2 border-slate-300 pl-4 py-1">
                        <h3 class="font-bold text-slate-900">A. Formulir Booking Servis Bengkel</h3>
                        <p class="text-xs sm:text-sm text-slate-600 mt-1">
                            Nama lengkap, nomor telepon atau WhatsApp aktif, nomor polisi kendaraan, merek dan model kendaraan, keluhan atau kebutuhan servis berkala, serta alamat penjemputan apabila pelanggan memilih opsi layanan antar-jemput.
                        </p>
                    </div>

                    <div class="border-l-2 border-slate-300 pl-4 py-1">
                        <h3 class="font-bold text-slate-900">B. Formulir Pengajuan Test Drive</h3>
                        <p class="text-xs sm:text-sm text-slate-600 mt-1">
                            Nama lengkap, nomor telepon/WhatsApp, jadwal uji coba yang diinginkan, nomor Surat Izin Mengemudi (SIM A) yang sah, serta berkas digital foto SIM untuk keperluan verifikasi kepatuhan mengemudi sebelum unit kendaraan diserahkan di bawah pengawasan konsultan penjualan resmi.
                        </p>
                    </div>

                    <div class="border-l-2 border-slate-300 pl-4 py-1">
                        <h3 class="font-bold text-slate-900">C. Formulir Kontak dan Konsultasi Showroom</h3>
                        <p class="text-xs sm:text-sm text-slate-600 mt-1">
                            Nama pengirim, alamat surel (email), nomor telepon, subjek pertanyaan, dan rincian pesan konsultasi mengenai pembelian unit mobil atau ketersediaan suku cadang OEM.
                        </p>
                    </div>
                </div>
            </section>

            <section class="space-y-4 text-sm leading-relaxed text-slate-700">
                <h2 class="text-lg font-bold text-slate-900">3. Keamanan dan Perlindungan Berkas Foto SIM</h2>
                <p>
                    Kami memperlakukan foto kartu identitas SIM pelanggan sebagai data sensitif berkeamanan tinggi:
                </p>
                <ul class="list-disc pl-5 space-y-2 text-xs sm:text-sm text-slate-600">
                    <li>Berkas foto SIM disimpan secara terisolasi pada media penyimpanan privat (private storage disk), bukan pada direktori publik web server.</li>
                    <li>Akses ke berkas foto hanya diizinkan melalui jalur terotentikasi dan terotorisasi ketat oleh pelanggan pemilik pengajuan, tenaga sales yang ditugaskan, atau super administrator yang berwenang.</li>
                    <li>Sistem tidak menyajikan tautan publik langsung ke foto identitas dan menerapkan respons HTTP dengan header keamanan protektif.</li>
                </ul>

                <div class="mt-4 p-4 bg-amber-50 border border-amber-200 rounded-lg text-amber-900 text-xs sm:text-sm space-y-2">
                    <p class="font-bold flex items-center gap-2">
                        <svg class="w-4 h-4 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Catatan Pengesahan Kebijakan Internal
                    </p>
                    <p class="font-mono text-xs text-amber-800">
                        [PERLU PENGESAHAN PEMILIK: Penetapan jangka waktu pasti retensi pemusnahan berkas digital foto SIM setelah sesi test drive selesai (misalnya otomatis dihapus dalam 30 hari kalender sejak status test drive completed atau cancelled). Saat ini berkas disimpan terenkripsi selama status lead aktif.]
                    </p>
                </div>
            </section>

            <section class="space-y-4 text-sm leading-relaxed text-slate-700">
                <h2 class="text-lg font-bold text-slate-900">4. Pembatasan Berbagi Data Pihak Ketiga</h2>
                <p>
                    Data pribadi pelanggan digunakan semata-mata untuk penyelenggaraan transaksi, penyampaian notifikasi status tiket servis, konfirmasi jadwal test drive, dan layanan purnajual resmi. XYZ Motors tidak memperjualbelikan, menyewakan, atau mendistribusikan data pelanggan kepada jaringan pengiklan pihak ketiga mana pun.
                </p>
            </section>

            <section class="space-y-4 text-sm leading-relaxed text-slate-700">
                <h2 class="text-lg font-bold text-slate-900">5. Hak Akses dan Permohonan Koreksi</h2>
                <p>
                    Pengguna berhak memeriksa status pengajuan, memperbarui data profil akun, atau meminta penutupan data operasional dengan menghubungi kami melalui saluran resmi.
                </p>
                <div class="p-4 bg-slate-100 rounded-lg text-xs space-y-1 font-mono text-slate-700">
                    <p class="font-sans font-bold text-slate-900">Kontak Pengaduan Data Pribadi:</p>
                    <p>Surel: privacy@xyzmotors.id</p>
                    <p>Telepon: +62-21-555-0199 (Senin – Sabtu, 08:00 – 18:00 WIB)</p>
                    <p>Alamat: Jl. Sudirman No. 88, Jakarta Selatan 12190</p>
                </div>
            </section>
        </article>
    </main>

    <footer class="border-t border-slate-200 bg-white py-8 text-center text-xs text-slate-500">
        <p>&copy; {{ date('Y') }} Dealer Pro (XYZ Motors Indonesia). Seluruh hak cipta dilindungi.</p>
    </footer>
</body>
</html>
