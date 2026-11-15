<x-guest-layout>
    <div class="mb-5 text-sm text-slate-600 leading-relaxed">
        Lupa kata sandi akun Anda? Masukkan alamat email terdaftar dan kami akan mengirimkan tautan pemulihan kata sandi ke kotak masuk Anda.
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" value="Alamat Email" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between gap-4 pt-2">
            <a href="{{ route('login') }}" class="text-sm text-slate-600 hover:text-slate-900 underline underline-offset-4 py-2">
                Kembali ke Masuk
            </a>
            <x-primary-button>
                Kirim Tautan Reset Sandi
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
