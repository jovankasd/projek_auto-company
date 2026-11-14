<x-dashboard-layout title="Detail Test Drive #{{ $testDrive->id }}" breadcrumb="Detail Test Drive">
    <div class="space-y-8" x-data="{ rejectModalOpen: false, cancelModalOpen: false, reasonText: '' }">
        <!-- Top Action Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <a href="{{ auth()->user()->isCustomer() ? route('test-drives.my') : route('dashboard') }}" class="text-xs font-bold text-slate-500 hover:text-slate-900 inline-flex items-center gap-1 mb-2">
                    &larr; Kembali ke Daftar
                </a>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900">
                        {{ $testDrive->car->brand }} {{ $testDrive->car->model_name }}
                    </h1>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider border {{ $testDrive->status->color() }}">
                        <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                        {{ $testDrive->status->label() }}
                    </span>
                </div>
            </div>

            <!-- Action Buttons based on Role & State -->
            <div class="flex flex-wrap items-center gap-2">
                @can('approve', $testDrive)
                    <form method="POST" action="{{ route('test-drives.approve', $testDrive) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 text-white font-bold text-xs hover:bg-emerald-700 transition">
                            Approve Pengajuan
                        </button>
                    </form>
                @endcan

                @can('reject', $testDrive)
                    <button type="button" @click="rejectModalOpen = true" class="px-4 py-2 rounded-xl bg-red-50 text-red-600 border border-red-200 font-bold text-xs hover:bg-red-100 transition">
                        Tolak Pengajuan
                    </button>
                @endcan

                @can('complete', $testDrive)
                    <form method="POST" action="{{ route('test-drives.complete', $testDrive) }}" onsubmit="return confirm('Konfirmasi bahwa sesi test drive telah selesai dilaksanakan?')">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 text-white font-bold text-xs hover:bg-emerald-700 transition">
                            Selesaikan Test Drive
                        </button>
                    </form>
                @endcan

                @can('cancel', $testDrive)
                    <button type="button" @click="cancelModalOpen = true" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 border border-slate-200 font-bold text-xs hover:bg-slate-200 transition">
                        Batalkan
                    </button>
                @endcan
            </div>
        </div>

        <!-- Meta Info Cards -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Pelanggan & Kontak -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 space-y-4">
                <span class="text-xs uppercase tracking-wider text-slate-400 font-bold">Informasi Pelanggan</span>
                <div>
                    <p class="font-extrabold text-lg text-slate-900">{{ $testDrive->customer->name }}</p>
                    <p class="text-xs text-slate-500 font-mono mt-0.5">{{ $testDrive->customer->email }}</p>
                    <p class="text-xs text-slate-700 font-semibold mt-1">Telp: {{ $testDrive->customer->phone_number }}</p>
                </div>
                @php
                    $cleanPhone = preg_replace('/[^0-9]/', '', $testDrive->customer->phone_number ?? '');
                    if (str_starts_with($cleanPhone, '0')) {
                        $cleanPhone = '62' . substr($cleanPhone, 1);
                    }
                    $carName = $testDrive->car->brand . ' ' . $testDrive->car->model_name;
                    $waUrl = "https://wa.me/{$cleanPhone}?text=" . rawurlencode("Halo {$testDrive->customer->name}, saya dari tim Sales Dealer Pro terkait pengajuan test drive unit {$carName}.");
                @endphp
                @if($cleanPhone)
                    <a href="{{ $waUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 text-xs font-bold border border-emerald-200 hover:bg-emerald-100 transition">
                        Chat WhatsApp
                    </a>
                @endif
            </div>

            <!-- Jadwal & Lokasi -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 space-y-4">
                <span class="text-xs uppercase tracking-wider text-slate-400 font-bold">Waktu & Lokasi</span>
                <div>
                    <p class="font-extrabold text-lg text-slate-900">{{ $testDrive->preferred_date->translatedFormat('l, d F Y') }}</p>
                    <p class="text-sm font-semibold text-slate-700 mt-0.5">Pukul {{ $testDrive->preferred_date->format('H:i') }} WIB</p>
                    <p class="text-xs text-slate-500 mt-2">📍 {{ $testDrive->pickup_location ?? 'Showroom Pusat' }}</p>
                </div>
                @if($testDrive->sales)
                    <div class="pt-2 border-t border-slate-100">
                        <span class="text-[11px] uppercase tracking-wider text-slate-400 font-bold">Sales Pendamping</span>
                        <p class="font-bold text-sm text-slate-900 mt-0.5">{{ $testDrive->sales->name }}</p>
                        <p class="text-xs text-slate-500 font-mono">{{ $testDrive->sales->email }}</p>
                    </div>
                @endif
            </div>

            <!-- SIM / Identitas Pengemudi -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 space-y-4">
                <span class="text-xs uppercase tracking-wider text-slate-400 font-bold">Identitas Pengemudi</span>
                <div>
                    <span class="text-xs text-slate-400 block">Nomor SIM A</span>
                    <p class="font-mono text-base font-bold text-slate-900 mt-0.5">{{ $testDrive->driver_license_id }}</p>
                </div>
                @if($testDrive->driver_license_image)
                    <div>
                        <span class="text-xs text-slate-400 block mb-1">Foto SIM</span>
                        <a href="{{ asset('storage/' . $testDrive->driver_license_image) }}" target="_blank" class="text-xs font-bold text-blue-600 hover:underline inline-flex items-center gap-1">
                            Lihat Foto SIM &nearr;
                        </a>
                    </div>
                @else
                    <p class="text-xs text-slate-400 italic">Tanpa unggahan foto SIM.</p>
                @endif
            </div>
        </div>

        <!-- Catatan & Status Details -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-6">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400">Catatan & Riwayat Status</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6 text-sm">
                <div>
                    <span class="text-xs text-slate-400 block">Tanggal Diajukan</span>
                    <p class="font-semibold text-slate-900 mt-0.5">{{ $testDrive->created_at->translatedFormat('d M Y, H:i') }} WIB</p>
                </div>
                <div>
                    <span class="text-xs text-slate-400 block">Tanggal Disetujui</span>
                    <p class="font-semibold text-slate-900 mt-0.5">
                        {{ $testDrive->approved_at ? $testDrive->approved_at->translatedFormat('d M Y, H:i') . ' WIB' : '-' }}
                    </p>
                </div>
                <div>
                    <span class="text-xs text-slate-400 block">Tanggal Selesai</span>
                    <p class="font-semibold text-slate-900 mt-0.5">
                        {{ $testDrive->completed_at ? $testDrive->completed_at->translatedFormat('d M Y, H:i') . ' WIB' : '-' }}
                    </p>
                </div>
                <div>
                    <span class="text-xs text-slate-400 block">Status Terakhir</span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider border {{ $testDrive->status->color() }} mt-0.5">
                        {{ $testDrive->status->label() }}
                    </span>
                </div>
            </div>

            @if($testDrive->sales_notes)
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                    <span class="text-xs uppercase font-bold text-slate-500 block mb-1">Catatan Sales</span>
                    <p class="text-sm text-slate-800">{{ $testDrive->sales_notes }}</p>
                </div>
            @endif

            @if($testDrive->rejection_reason)
                <div class="p-4 rounded-2xl bg-red-50 border border-red-100">
                    <span class="text-xs uppercase font-bold text-red-700 block mb-1">
                        {{ $testDrive->status->value === 'cancelled' ? 'Alasan Pembatalan' : 'Alasan Penolakan' }}
                    </span>
                    <p class="text-sm text-red-900">{{ $testDrive->rejection_reason }}</p>
                </div>
            @endif
        </div>

        <!-- Reject Modal -->
        <div x-show="rejectModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="rejectModalOpen = false"></div>
            <div class="relative w-full max-w-md rounded-3xl bg-white border border-slate-200 shadow-2xl p-6 sm:p-8 overflow-hidden">
                <h3 class="font-black text-lg text-slate-900">Tolak Pengajuan Test Drive</h3>
                <p class="text-xs text-slate-500 mt-1">Masukkan alasan penolakan untuk disampaikan kepada pelanggan.</p>
                <form action="{{ route('test-drives.reject', $testDrive) }}" method="POST" class="mt-4 space-y-4">
                    @csrf
                    @method('PATCH')
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Alasan Penolakan</label>
                        <textarea name="rejection_reason" rows="3" required placeholder="Contoh: Unit sedang dalam jadwal pemeliharaan berkala..."
                                  class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-slate-900 focus:outline-none"></textarea>
                    </div>
                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" @click="rejectModalOpen = false" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-red-600 text-white hover:bg-red-700">
                            Konfirmasi Penolakan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Cancel Modal -->
        <div x-show="cancelModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="cancelModalOpen = false"></div>
            <div class="relative w-full max-w-md rounded-3xl bg-white border border-slate-200 shadow-2xl p-6 sm:p-8 overflow-hidden">
                <h3 class="font-black text-lg text-slate-900">Batalkan Pengajuan Test Drive</h3>
                <p class="text-xs text-slate-500 mt-1">Apakah Anda yakin ingin membatalkan jadwal test drive ini?</p>
                <form action="{{ route('test-drives.cancel', $testDrive) }}" method="POST" class="mt-4 space-y-4">
                    @csrf
                    @method('PATCH')
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Alasan Pembatalan (Opsional)</label>
                        <textarea name="cancellation_reason" rows="3" placeholder="Contoh: Terjadi perubahan jadwal atau kebutuhan..."
                                  class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-slate-900 focus:outline-none"></textarea>
                    </div>
                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" @click="cancelModalOpen = false" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100">
                            Kembali
                        </button>
                        <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-slate-900 text-white hover:bg-slate-800">
                            Konfirmasi Pembatalan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-dashboard-layout>
