<x-guest-layout>
    <div class="mb-6 text-center">
        <h1 class="text-xl font-black tracking-tight text-slate-900">Masuk ke Akun</h1>
        <p class="text-xs text-slate-500 mt-1">Akses dashboard, riwayat servis, dan pengajuan test drive Anda</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ request()->routeIs('login.hidden') ? route('login.hidden') : route('login') }}" class="space-y-4">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="'Alamat Email'" />
            <x-text-input id="email" class="block w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="nama@email.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="'Kata Sandi'" />
            <x-text-input id="password" class="block w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password"
                            placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
        </div>

        <!-- Remember Me & Forgot Password -->
        <div class="flex items-center justify-between text-xs pt-1">
            <label for="remember_me" class="inline-flex items-center cursor-pointer select-none">
                <input id="remember_me" type="checkbox" class="rounded border-slate-300 text-slate-900 shadow-2xs focus:ring-black focus:ring-2 h-4 w-4" name="remember">
                <span class="ms-2 text-slate-600 font-medium">Ingat saya</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-xs text-blue-600 hover:text-blue-700 hover:underline font-semibold focus:outline-none focus:ring-2 focus:ring-black rounded" href="{{ route('password.request') }}">
                    Lupa kata sandi?
                </a>
            @endif
        </div>

        <div class="pt-2">
            <x-primary-button class="w-full py-3 justify-center">
                Masuk
            </x-primary-button>
        </div>

        <!-- Register Link -->
        <div class="mt-6 pt-4 border-t border-slate-100 text-center text-xs text-slate-600">
            Belum memiliki akun?
            <a href="{{ route('register') }}" class="font-bold text-blue-600 hover:text-blue-700 hover:underline ml-1 focus:outline-none focus:ring-2 focus:ring-black rounded">
                Daftar sekarang
            </a>
        </div>
    </form>
</x-guest-layout>
