<x-guest-layout>
    <div class="mb-6 text-center">
        <h1 class="text-xl font-black tracking-tight text-slate-900">Konfirmasi Kata Sandi</h1>
        <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
            Ini adalah area aman aplikasi Dealer Pro. Harap masukkan kata sandi Anda sebelum melanjutkan.
        </p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-4">
        @csrf

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

        <div class="pt-2">
            <x-primary-button class="w-full py-3 justify-center">
                Konfirmasi
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
