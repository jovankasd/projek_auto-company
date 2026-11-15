<section class="space-y-6">
    <header>
        <h2 class="font-display text-lg font-bold text-slate-900">
            Hapus Akun
        </h2>

        <p class="mt-1 text-sm text-slate-600">
            Setelah akun dihapus, seluruh data dan riwayat terkait akan dihapus permanen. Unduh data penting Anda sebelum melanjutkan.
        </p>
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >Hapus Akun</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6 sm:p-8">
            @csrf
            @method('delete')

            <h2 class="font-display text-lg font-bold text-slate-900">
                Apakah Anda yakin ingin menghapus akun ini?
            </h2>

            <p class="mt-1.5 text-sm text-slate-600 leading-relaxed">
                Tindakan ini tidak dapat dibatalkan. Masukkan kata sandi Anda untuk mengonfirmasi penghapusan akun secara permanen.
            </p>

            <div class="mt-6">
                <x-input-label for="password" value="Kata Sandi" class="sr-only" />

                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-full sm:w-3/4"
                    placeholder="Kata Sandi"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex flex-wrap justify-end gap-3">
                <x-secondary-button x-on:click="$dispatch('close')">
                    Batal
                </x-secondary-button>

                <x-danger-button>
                    Hapus Akun Permanen
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
