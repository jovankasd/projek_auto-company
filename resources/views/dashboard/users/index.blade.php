<x-dashboard-layout title="Manajemen Pengguna" breadcrumb="Pengguna">
    <div class="space-y-8">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900">Manajemen Pengguna &amp; Staf</h1>
                <p class="text-slate-600 text-sm mt-1">Kelola data akun customer, perwakilan sales, staf mekanik, dan super administrator.</p>
            </div>
            <a href="{{ route('users.create') }}" class="inline-flex items-center justify-center min-h-[44px] gap-2 bg-slate-900 text-white px-5 py-2.5 rounded-xl font-bold text-sm hover:bg-slate-800 active:scale-[0.98] transition shadow-sm">
                + Tambah Pengguna Baru
            </a>
        </div>

        <!-- Filter & Search -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm">
            <form method="GET" action="{{ route('users.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <div class="lg:col-span-2">
                    <label for="user_filter_search" class="sr-only">Cari pengguna</label>
                    <input id="user_filter_search" type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, email, atau no handphone..."
                           class="w-full min-h-[44px] px-4 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                </div>

                <div>
                    <label for="user_filter_role" class="sr-only">Filter peran</label>
                    <select id="user_filter_role" name="role" class="w-full min-h-[44px] px-4 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                        <option value="">Semua Peran (Role)</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->value }}" {{ request('role') === $role->value ? 'selected' : '' }}>
                                {{ $role->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="flex-1 min-h-[44px] py-2 px-4 bg-slate-900 text-white rounded-xl text-xs font-bold hover:bg-slate-800 transition">
                        Filter
                    </button>
                    <a href="{{ route('users.index') }}" class="inline-flex items-center justify-center min-h-[44px] px-3.5 py-2 bg-slate-100 text-slate-700 rounded-xl text-xs font-bold hover:bg-slate-200 transition">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
            @if($users->isEmpty())
                <div class="py-16 px-6 text-center text-slate-600">
                    <p class="text-base font-bold text-slate-800">Tidak ada pengguna ditemukan.</p>
                    <p class="text-sm mt-1">Ubah filter peran atau kata kunci pencarian Anda.</p>
                </div>
            @else
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-[11px] uppercase tracking-wider text-slate-600 font-bold">
                                <th class="py-3.5 px-6">Nama &amp; Email</th>
                                <th class="py-3.5 px-6">Peran (Role)</th>
                                <th class="py-3.5 px-6">Nomor Telepon</th>
                                <th class="py-3.5 px-6">Status Akun</th>
                                <th class="py-3.5 px-6">Terdaftar Sejak</th>
                                <th class="py-3.5 px-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @foreach($users as $u)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-4 px-6">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-full bg-slate-900 text-white flex items-center justify-center font-bold text-xs shrink-0">
                                                {{ strtoupper(substr($u->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <p class="font-extrabold text-slate-900 leading-snug">{{ $u->name }}</p>
                                                <p class="text-xs text-slate-600 font-mono">{{ $u->email }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $u->role->color() }}">
                                            {{ $u->role->label() }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 font-mono text-xs text-slate-700">
                                        {{ $u->phone_number ?? '-' }}
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider {{ $u->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $u->is_active ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
                                            {{ $u->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-xs text-slate-600 tabular-nums">
                                        {{ $u->created_at?->translatedFormat('d M Y') ?? '-' }}
                                    </td>
                                    <td class="py-4 px-6 text-right">
                                        <div class="inline-flex items-center justify-end gap-2">
                                            <a href="{{ route('users.edit', $u) }}" class="inline-flex items-center justify-center min-h-[44px] px-3 py-1.5 rounded-xl bg-slate-100 text-slate-800 text-xs font-bold hover:bg-slate-200 transition">
                                                Edit
                                            </a>

                                            @if($u->id !== auth()->id())
                                                <form method="POST" action="{{ route('users.destroy', $u) }}" class="inline" onsubmit="return confirm('Yakin ingin menghapus user ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="inline-flex items-center justify-center min-h-[44px] px-3 py-1.5 rounded-xl bg-red-50 text-red-700 text-xs font-bold hover:bg-red-100 transition">
                                                        Hapus
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Card Stack -->
                <div class="md:hidden divide-y divide-slate-100">
                    @foreach($users as $u)
                        <div class="p-5 space-y-3">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-10 h-10 rounded-full bg-slate-900 text-white flex items-center justify-center font-bold text-xs shrink-0">
                                        {{ strtoupper(substr($u->name, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-extrabold text-sm text-slate-900 truncate">{{ $u->name }}</p>
                                        <p class="text-xs text-slate-600 font-mono truncate">{{ $u->email }}</p>
                                    </div>
                                </div>
                                <span class="inline-block px-2.5 py-0.5 rounded-full text-[11px] font-bold border shrink-0 {{ $u->role->color() }}">
                                    {{ $u->role->label() }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between text-xs text-slate-600">
                                <span class="font-mono">{{ $u->phone_number ?? '-' }}</span>
                                <span class="font-bold {{ $u->is_active ? 'text-emerald-700' : 'text-red-700' }}">
                                    {{ $u->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </div>

                            <div class="flex flex-wrap items-center gap-2 pt-1">
                                <a href="{{ route('users.edit', $u) }}" class="flex-1 inline-flex items-center justify-center min-h-[44px] px-4 py-2 rounded-xl bg-slate-100 text-slate-800 text-xs font-bold hover:bg-slate-200 transition">
                                    Edit
                                </a>
                                @if($u->id !== auth()->id())
                                    <form method="POST" action="{{ route('users.destroy', $u) }}" class="flex-1" onsubmit="return confirm('Yakin ingin menghapus user ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-full inline-flex items-center justify-center min-h-[44px] px-4 py-2 rounded-xl bg-red-50 text-red-700 text-xs font-bold hover:bg-red-100 transition">
                                            Hapus
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="p-6 border-t border-slate-100">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
</x-dashboard-layout>
