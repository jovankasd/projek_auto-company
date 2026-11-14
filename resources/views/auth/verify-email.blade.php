<x-guest-layout>
    <div class="mb-6 text-center">
        <h1 class="text-xl font-black tracking-tight text-slate-900">Verifikasi Alamat Email</h1>
        <p class="text-xs text-slate-500 mt-2 leading-relaxed">
            Terima kasih telah mendaftar di Dealer Pro. Sebelum memulai, silakan verifikasi alamat email Anda dengan mengeklik tautan yang baru saja kami kirimkan. Jika belum menerima email tersebut, kami dapat mengirimkan tautan baru.
        </p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-xs font-semibold text-emerald-800 text-center">
            Tautan verifikasi baru telah dikirimkan ke alamat email yang Anda gunakan saat mendaftar.
        </div>
    @endif

    <div class="space-y-4 pt-2">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-primary-button class="w-full py-3 justify-center">
                Kirim Ulang Email Verifikasi
            </x-primary-button>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="text-center pt-2">
            @csrf
            <button type="submit" class="text-xs font-semibold text-slate-500 hover:text-slate-900 hover:underline focus:outline-none focus:ring-2 focus:ring-black rounded py-1 px-2">
                Keluar dari Akun
            </button>
        </form>
    </div>
</x-guest-layout>
