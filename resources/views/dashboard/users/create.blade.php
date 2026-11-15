<x-dashboard-layout title="Tambah Pengguna Baru" breadcrumb="Tambah Pengguna">
    <div class="max-w-2xl mx-auto space-y-8">
        <div>
            <a href="{{ route('users.index') }}" class="text-xs font-bold text-slate-600 hover:text-slate-900 inline-flex items-center gap-1.5 mb-2 min-h-[44px] sm:min-h-0 py-1.5 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Kembali ke Daftar Pengguna</span>
            </a>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900">Tambah Akun Pengguna Baru</h1>
            <p class="text-slate-600 text-sm mt-1">Daftarkan akun staf baru dengan hak akses peran tertentu (RBAC).</p>
        </div>

        <form action="{{ route('users.store') }}" method="POST" class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-8 space-y-6">
            @csrf

            <div>
                <label for="user_name" class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Nama Lengkap <span class="text-red-600">*</span></label>
                <input id="user_name" type="text" name="name" value="{{ old('name') }}" required placeholder="Contoh: Budi Santoso"
                       class="w-full min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                @error('name') <p class="text-xs text-red-700 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label for="user_email" class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Alamat Email <span class="text-red-600">*</span></label>
                    <input id="user_email" type="email" name="email" value="{{ old('email') }}" required placeholder="budi@xyzmotors.id"
                           class="w-full min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                    @error('email') <p class="text-xs text-red-700 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="user_phone_number" class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Nomor Telepon / WhatsApp <span class="text-red-600">*</span></label>
                    <input id="user_phone_number" type="tel" name="phone_number" value="{{ old('phone_number') }}" required placeholder="081234567890"
                           class="w-full min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-300 text-sm font-mono focus:ring-2 focus:ring-blue-600 focus:outline-none">
                    @error('phone_number') <p class="text-xs text-red-700 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label for="user_role" class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Hak Akses / Peran (Role) <span class="text-red-600">*</span></label>
                    <select id="user_role" name="role" required class="w-full min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                        @foreach($roles as $role)
                            <option value="{{ $role->value }}" {{ old('role') === $role->value ? 'selected' : '' }}>
                                {{ $role->label() }}
                            </option>
                        @endforeach
                    </select>
                    @error('role') <p class="text-xs text-red-700 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="user_password" class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Password Sementara <span class="text-red-600">*</span></label>
                    <input id="user_password" type="password" name="password" required placeholder="Minimal 8 karakter..."
                           class="w-full min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none font-mono">
                    @error('password') <p class="text-xs text-red-700 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="pt-2">
                <label class="inline-flex items-center gap-2.5 min-h-[44px] cursor-pointer">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" @checked(filter_var(old('is_active', true), FILTER_VALIDATE_BOOLEAN))
                           class="rounded border-slate-300 text-slate-900 focus:ring-blue-600 w-5 h-5">
                    <span class="text-sm font-bold text-slate-800">Akun Langsung Aktif</span>
                </label>
                @error('is_active') <p class="text-xs text-red-700 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('users.index') }}" class="inline-flex items-center justify-center min-h-[44px] px-5 py-2.5 rounded-xl font-bold text-sm text-slate-600 hover:bg-slate-100 transition">
                    Batal
                </a>
                <button type="submit" class="inline-flex items-center justify-center min-h-[44px] px-6 py-2.5 rounded-xl font-bold text-sm bg-slate-900 text-white hover:bg-slate-800 active:scale-[0.98] shadow-sm transition">
                    Simpan Pengguna
                </button>
            </div>
        </form>
    </div>
</x-dashboard-layout>
