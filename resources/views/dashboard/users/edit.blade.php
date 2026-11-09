<x-dashboard-layout title="Edit Pengguna {{ $user->name }}" breadcrumb="Edit Pengguna">
    <div class="max-w-2xl mx-auto space-y-8">
        <div>
            <a href="{{ route('users.index') }}" class="text-xs font-bold text-slate-500 hover:text-slate-900 inline-flex items-center gap-1 mb-2">
                &larr; Kembali ke Daftar Pengguna
            </a>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900">Edit Akun: {{ $user->name }}</h1>
            <p class="text-slate-500 text-sm mt-1">Perbarui data profil, peran sistem, atau reset kata sandi pengguna.</p>
        </div>

        <form action="{{ route('users.update', $user) }}" method="POST" class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required 
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-slate-900 focus:outline-none">
                @error('name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Alamat Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required 
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-slate-900 focus:outline-none">
                    @error('email') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Nomor Telepon / WhatsApp <span class="text-red-500">*</span></label>
                    <input type="text" name="phone_number" value="{{ old('phone_number', $user->phone_number) }}" required 
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm font-mono focus:ring-2 focus:ring-slate-900 focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Peran (Role) <span class="text-red-500">*</span></label>
                    <select name="role" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-slate-900 focus:outline-none">
                        @foreach($roles as $role)
                            <option value="{{ $role->value }}" {{ old('role', $user->role->value ?? $user->role) === $role->value ? 'selected' : '' }}>
                                {{ $role->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Reset Password (Opsional)</label>
                    <input type="password" name="password" placeholder="Kosongkan jika tidak diubah..." 
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-slate-900 focus:outline-none font-mono">
                </div>
            </div>

            <div class="pt-2">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }} 
                           class="rounded border-slate-300 text-slate-900 focus:ring-slate-900 w-4 h-4">
                    <span class="text-sm font-bold text-slate-800">Status Akun Aktif</span>
                </label>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('users.index') }}" class="px-5 py-2.5 rounded-xl font-bold text-sm text-slate-600 hover:bg-slate-100">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl font-bold text-sm bg-slate-900 text-white hover:bg-slate-800 shadow-sm">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</x-dashboard-layout>
