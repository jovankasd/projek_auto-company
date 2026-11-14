<x-guest-layout>
    <div class="mb-6 text-center">
        <h1 class="text-xl font-black tracking-tight text-slate-900">Lupa Kata Sandi</h1>
        <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
            Masukkan alamat email yang terdaftar. Kami akan mengirimkan tautan untuk mengatur ulang kata sandi Anda.
        </p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="'Alamat Email'" />
            <x-text-input id="email" class="block w-full" type="email" name="email" :value="old('email')" required autofocus placeholder="nama@email.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
        </div>

        <div class="pt-2">
            <x-primary-button class="w-full py-3 justify-center">
                Kirim Tautan Atur Ulang Sandi
            </x-primary-button>
        </div>

        <div class="mt-6 pt-4 border-t border-slate-100 text-center text-xs text-slate-600">
            <a href="{{ route('login') }}" class="font-bold text-blue-600 hover:text-blue-700 hover:underline focus:outline-none focus:ring-2 focus:ring-black rounded">
                &larr; Kembali ke halaman masuk
            </a>
        </div>
    </form>
</x-guest-layout>
