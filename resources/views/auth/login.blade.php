<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ request()->is('logind') ? route('login.hidden') : route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="ms-3">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>

    <!-- Quick Demo Accounts -->
    <div class="mt-6 pt-6 border-t border-slate-200">
        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2.5">Akun Bawaan (Klik untuk Isi Otomatis):</p>
        <div class="grid grid-cols-2 gap-2 text-xs">
            <button type="button" onclick="fillLogin('customer@xyzmotors.id', 'password')" class="text-left p-2.5 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 transition">
                <span class="font-bold block">Customer</span>
                customer@xyzmotors.id
            </button>
            <button type="button" onclick="fillLogin('sales@xyzmotors.id', 'password')" class="text-left p-2.5 rounded-lg bg-green-50 hover:bg-green-100 text-green-700 border border-green-200 transition">
                <span class="font-bold block">Sales</span>
                sales@xyzmotors.id
            </button>
            <button type="button" onclick="fillLogin('mekanik1@xyzmotors.id', 'password')" class="text-left p-2.5 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 transition">
                <span class="font-bold block">Admin Mekanik</span>
                mekanik1@xyzmotors.id
            </button>
            <button type="button" onclick="fillLogin('superadmin@xyzmotors.id', 'password')" class="text-left p-2.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-800 border border-slate-300 transition">
                <span class="font-bold block">Super Admin</span>
                superadmin@xyzmotors.id
            </button>
            <button type="button" onclick="fillLogin('owner@xyzmotors.id', 'password')" class="text-left p-2.5 rounded-lg bg-purple-50 hover:bg-purple-100 text-purple-900 border border-purple-200 transition col-span-2 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="font-bold flex items-center gap-1.5 text-purple-950">
                        <span>Owner (Pemilik)</span>
                    </span>
                    <span class="text-[10px] bg-purple-200 text-purple-900 font-semibold px-2 py-0.5 rounded-full">Dashboard Finansial & Penjualan</span>
                </div>
                <span class="text-purple-600 font-mono mt-0.5 block">owner@xyzmotors.id</span>
            </button>
        </div>
        <p class="text-[11px] text-slate-500 mt-2.5 text-center">Password semua akun bawaan: <strong class="font-mono bg-slate-100 px-1.5 py-0.5 rounded text-slate-800 border border-slate-200">password</strong></p>
    </div>

    <script>
        function fillLogin(email, password) {
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            if (emailInput && passwordInput) {
                emailInput.value = email;
                passwordInput.value = password;
                const form = emailInput.closest('form') || document.querySelector('form');
                if (form) {
                    form.submit();
                }
            }
        }
    </script>
</x-guest-layout>
