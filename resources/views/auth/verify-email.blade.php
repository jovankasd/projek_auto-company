<x-guest-layout>
    <div class="mb-5 text-sm text-slate-600 leading-relaxed">
        Terima kasih telah mendaftar. Sebelum melanjutkan, silakan verifikasi alamat email Anda dengan mengklik tautan yang telah kami kirimkan. Jika belum menerima email, Anda dapat meminta kirim ulang di bawah ini.
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 p-3 rounded-xl bg-emerald-50 border border-emerald-200 font-medium text-sm text-emerald-800">
            Tautan verifikasi baru telah dikirim ke alamat email yang Anda daftarkan.
        </div>
    @endif

    <div class="mt-6 flex flex-wrap items-center justify-between gap-3">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <div>
                <x-primary-button>
                    Kirim Ulang Email Verifikasi
                </x-primary-button>
            </div>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="min-h-[44px] px-3 underline text-sm text-slate-600 hover:text-slate-900 rounded-xl focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-blue-600">
                Keluar Akun
            </button>
        </form>
    </div>
</x-guest-layout>
