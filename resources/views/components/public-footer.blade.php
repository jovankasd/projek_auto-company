@props(['onShowroom' => false])

@php
    $prefix = $onShowroom ? '' : route('home');
@endphp

<footer class="bg-slate-50 border-t border-slate-200/80 text-slate-600 py-14 sm:py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-10 pb-12 border-b border-slate-200/80">
            <div class="md:col-span-5 space-y-3">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/logo.jpg') }}"
                         alt="Dealer Pro"
                         class="h-9 w-9 rounded-xl object-cover border border-slate-200 shadow-sm">
                    <div>
                        <span class="font-display font-extrabold text-lg tracking-tight text-slate-900 block leading-none">
                            Dealer Pro
                        </span>
                        <span class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-600 block mt-0.5">
                            AUTO COMPANY Indonesia
                        </span>
                    </div>
                </div>
                <p class="text-xs sm:text-sm text-slate-600 max-w-sm leading-relaxed">
                    Showroom kendaraan pilihan, layanan bengkel resmi dengan pelacakan tiket pengerjaan secara langsung, serta pusat penyedia suku cadang orisinal.
                </p>
                <dl class="text-xs text-slate-600 space-y-1.5 pt-2">
                    <div>
                        <dt class="inline font-semibold text-slate-900">Alamat:</dt>
                        <dd class="inline">Jl. Sudirman No. 88, Jakarta Selatan 12190</dd>
                    </div>
                    <div>
                        <dt class="inline font-semibold text-slate-900">Telepon:</dt>
                        <dd class="inline font-mono tabular-nums text-slate-800">+62-21-555-0199</dd>
                    </div>
                    <div>
                        <dt class="inline font-semibold text-slate-900">Operasional:</dt>
                        <dd class="inline font-mono tabular-nums text-slate-800">Senin - Sabtu, 08:00 - 18:00 WIB</dd>
                    </div>
                </dl>
            </div>

            <div class="md:col-span-4 space-y-3">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900">Indeks Katalog &amp; Layanan</h3>
                <ul class="space-y-1 text-xs sm:text-sm">
                    <li>
                        <a href="{{ route('home') }}" class="inline-flex items-center py-1.5 text-slate-700 hover:text-blue-700 transition">
                            Showroom Interaktif 2.5D
                        </a>
                    </li>
                    <li>
                        <a href="{{ $prefix }}#katalog-mobil" class="inline-flex items-center py-1.5 text-slate-700 hover:text-blue-700 transition">
                            Katalog Mobil Lengkap
                        </a>
                    </li>
                    <li>
                        <a href="{{ $prefix }}#katalog-suku-cadang" class="inline-flex items-center py-1.5 text-slate-700 hover:text-blue-700 transition">
                            Suku Cadang OEM
                        </a>
                    </li>
                    <li>
                        <a href="{{ $prefix }}#layanan-bengkel" class="inline-flex items-center py-1.5 text-slate-700 hover:text-blue-700 transition">
                            Bengkel Resmi &amp; Alur Tiket
                        </a>
                    </li>
                    <li>
                        <a href="{{ $prefix }}#promo" class="inline-flex items-center py-1.5 text-slate-700 hover:text-blue-700 transition">
                            Program Promo &amp; Voucher
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('contact.index') }}" class="inline-flex items-center py-1.5 text-slate-700 hover:text-blue-700 transition">
                            Hubungi Konsultan Dealer
                        </a>
                    </li>
                </ul>
            </div>

            <div class="md:col-span-3 space-y-3">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900">Layanan Cepat &amp; Legal</h3>
                <div class="flex flex-col gap-2.5">
                    <a href="{{ $prefix }}#katalog-mobil"
                       class="w-full inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-blue-600 text-white text-xs font-semibold shadow-sm hover:scale-[1.02] active:scale-[0.98] transition-all duration-200 min-h-[44px]">
                        Lihat Unit Showroom
                    </a>
                    <a href="{{ $prefix }}#lacak-tiket"
                       class="w-full inline-flex items-center justify-center px-4 py-2.5 rounded-xl border border-slate-300 bg-white text-slate-800 hover:bg-slate-900 hover:text-white hover:border-slate-900 text-xs font-semibold transition-all duration-200 min-h-[44px]">
                        Lacak Status Servis &amp; Test Drive
                    </a>
                    <a href="{{ route('contact.index') }}"
                       class="w-full inline-flex items-center justify-center px-4 py-2.5 rounded-xl border border-slate-300 bg-white text-slate-800 hover:bg-slate-900 hover:text-white hover:border-slate-900 text-xs font-semibold transition-all duration-200 min-h-[44px]">
                        Hubungi Konsultan Dealer
                    </a>
                </div>
                <div class="pt-3 flex flex-wrap gap-x-4 gap-y-2 text-xs">
                    <a href="{{ route('privacy-policy') }}" class="text-slate-700 hover:text-blue-700 underline underline-offset-4 py-1">
                        Kebijakan Privasi
                    </a>
                    <a href="{{ route('terms-of-service') }}" class="text-slate-700 hover:text-blue-700 underline underline-offset-4 py-1">
                        Syarat dan Ketentuan
                    </a>
                </div>
            </div>
        </div>

        <div class="pt-6 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-600">
            <p>&copy; {{ date('Y') }} Dealer Pro (AUTO COMPANY Indonesia). Seluruh hak cipta dilindungi.</p>
            <p class="font-mono text-[11px] uppercase tracking-wider text-slate-600">
                Showroom Sales / Workshop Service / OEM Spare Parts
            </p>
        </div>
    </div>
</footer>
