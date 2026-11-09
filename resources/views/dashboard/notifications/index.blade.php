<x-dashboard-layout title="Pusat Pemberitahuan" breadcrumb="Notifikasi">
    <div class="space-y-6 max-w-4xl mx-auto">
        <!-- Header & Bulk Action -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900">Pusat Pemberitahuan</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">
                    Semua riwayat pembaruan status servis, jadwal test drive, dan informasi akun Anda.
                </p>
            </div>

            @if($unreadCount > 0)
                <form method="POST" action="{{ route('notifications.mark-all-read') }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 transition shadow-xs">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Tandai Semua Dibaca</span>
                    </button>
                </form>
            @endif
        </div>

        <!-- Filter Tabs -->
        <div class="flex items-center gap-2 border-b border-slate-200 pb-3">
            <a href="{{ route('notifications.index', ['filter' => 'all']) }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold transition {{ $filter === 'all' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                Semua Notifikasi
            </a>
            <a href="{{ route('notifications.index', ['filter' => 'unread']) }}" 
               class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition {{ $filter === 'unread' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                <span>Belum Dibaca</span>
                @if($unreadCount > 0)
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold {{ $filter === 'unread' ? 'bg-white text-slate-900' : 'bg-red-500 text-white' }}">
                        {{ $unreadCount }}
                    </span>
                @endif
            </a>
        </div>

        <!-- Notification List -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-xs divide-y divide-slate-100 overflow-hidden">
            @forelse($notifications as $notification)
                @php
                    $isUnread = is_null($notification->read_at);
                    $data = $notification->data;
                    $color = $data['color'] ?? 'blue';
                    $targetUrl = $data['url'] ?? null;
                @endphp
                <div class="p-5 sm:p-6 transition flex items-start gap-4 {{ $isUnread ? 'bg-blue-50/30 hover:bg-blue-50/50' : 'hover:bg-slate-50' }}">
                    <!-- Icon Indicator -->
                    <div class="w-10 h-10 rounded-2xl flex items-center justify-center shrink-0 mt-0.5 
                        {{ $color === 'emerald' ? 'bg-emerald-100 text-emerald-600' : ($color === 'red' ? 'bg-red-100 text-red-600' : ($color === 'amber' ? 'bg-amber-100 text-amber-600' : ($color === 'indigo' ? 'bg-indigo-100 text-indigo-600' : 'bg-blue-100 text-blue-600'))) }}">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </div>

                    <!-- Body Content -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2 mb-1">
                            <h2 class="text-sm font-bold text-slate-900 truncate">
                                {{ $data['title'] ?? 'Pemberitahuan Sistem' }}
                            </h2>
                            <span class="text-xs text-slate-400 font-mono whitespace-nowrap">
                                {{ $notification->created_at->diffForHumans() }}
                            </span>
                        </div>

                        <p class="text-xs sm:text-sm text-slate-600 leading-relaxed mb-3">
                            {{ $data['message'] ?? '-' }}
                        </p>

                        <!-- Action Links -->
                        <div class="flex flex-wrap items-center gap-3 text-xs font-bold">
                            @if($targetUrl)
                                <a href="{{ route('notifications.read', ['id' => $notification->id, 'redirect' => 1]) }}" 
                                   class="text-slate-900 hover:underline inline-flex items-center gap-1">
                                    <span>Buka Halaman Terkait</span>
                                    <span aria-hidden="true">&rarr;</span>
                                </a>
                            @endif

                            @if($isUnread)
                                <form method="POST" action="{{ route('notifications.read', $notification->id) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="text-slate-500 hover:text-slate-800">
                                        Tandai Dibaca
                                    </button>
                                </form>
                            @else
                                <span class="text-slate-400 font-normal">Sudah dibaca</span>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="py-16 text-center px-4">
                    <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </div>
                    <h2 class="text-base font-bold text-slate-800">Tidak ada pemberitahuan</h2>
                    <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
                        {{ $filter === 'unread' ? 'Semua pemberitahuan sudah Anda baca.' : 'Saat ini belum ada notifikasi aktivitas baru di akun Anda.' }}
                    </p>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($notifications->hasPages())
            <div class="pt-2">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
</x-dashboard-layout>
