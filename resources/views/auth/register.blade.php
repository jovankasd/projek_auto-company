<x-guest-layout>
    <div class="mb-6 text-center">
        <h1 class="text-xl font-black tracking-tight text-slate-900">Daftar Akun Baru</h1>
        <p class="text-xs text-slate-500 mt-1">Lengkapi data untuk booking servis dan pengajuan test drive kendaraan</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="'Nama Lengkap'" />
            <x-text-input id="name" class="block w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="Nama Anda" />
            <x-input-error :messages="$errors->get('name')" class="mt-1.5" />
        </div>

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="'Alamat Email'" />
            <x-text-input id="email" class="block w-full" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="nama@email.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="'Kata Sandi'" />
            <x-text-input id="password" class="block w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password"
                            placeholder="Minimal 8 karakter" />
            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
        </div>

        <!-- Confirm Password -->
        <div>
            <x-input-label for="password_confirmation" :value="'Konfirmasi Kata Sandi'" />
            <x-text-input id="password_confirmation" class="block w-full"
                            type="password"
                            name="password_confirmation"
                            required autocomplete="new-password"
                            placeholder="Ulangi kata sandi" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1.5" />
        </div>

        <div class="pt-2">
            <x-primary-button class="w-full py-3 justify-center">
                Daftar Akun
            </x-primary-button>
        </div>

        <div class="mt-6 pt-4 border-t border-slate-100 text-center text-xs text-slate-600">
            Sudah memiliki akun?
            <a href="{{ route('login') }}" class="font-bold text-blue-600 hover:text-blue-700 hover:underline ml-1 focus:outline-none focus:ring-2 focus:ring-black rounded">
                Masuk di sini
            </a>
        </div>
    </form>
</x-guest-layout>
