<div class="space-y-8" x-data="{ 
    rejectModalOpen: false, 
    selectedTestDrive: null,
    rejectionReason: '',
    openReject(td) {
        this.selectedTestDrive = td;
        this.rejectionReason = '';
        this.rejectModalOpen = true;
    }
}">
    <!-- Header Title -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900">Manajemen Lead Test Drive</h1>
            <p class="text-slate-500 text-sm mt-1">Kelola permohonan test drive pelanggan, lakukan approval, dan hubungi pelanggan via WhatsApp.</p>
        </div>

        <!-- Filter Status -->
        <div class="inline-flex p-1 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <a href="{{ route('dashboard', ['status' => 'all']) }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold transition {{ $statusFilter === 'all' ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                Semua
            </a>
            <a href="{{ route('dashboard', ['status' => 'requested']) }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold transition {{ $statusFilter === 'requested' ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                Menunggu Approval
            </a>
            <a href="{{ route('dashboard', ['status' => 'approved']) }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold transition {{ $statusFilter === 'approved' ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                Disetujui
            </a>
        </div>
    </div>

    <!-- Quick Metrics -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
            <span class="text-xs uppercase font-bold text-slate-400">Lead Baru (Menunggu)</span>
            <p class="font-mono text-3xl font-extrabold text-amber-600 mt-1">{{ $stats['requested_leads'] }}</p>
            <span class="text-[11px] text-slate-500 mt-1 block">Perlu verifikasi & approval</span>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
            <span class="text-xs uppercase font-bold text-slate-400">Disetujui Bulan Ini</span>
            <p class="font-mono text-3xl font-extrabold text-blue-600 mt-1">{{ $stats['approved_this_month'] }}</p>
            <span class="text-[11px] text-slate-500 mt-1 block">Jadwal test drive aktif</span>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
            <span class="text-xs uppercase font-bold text-slate-400">Selesai Dicoba</span>
            <p class="font-mono text-3xl font-extrabold text-emerald-600 mt-1">{{ $stats['completed_leads'] }}</p>
            <span class="text-[11px] text-slate-500 mt-1 block">Lead potensial transaksi</span>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
            <span class="text-xs uppercase font-bold text-slate-400">Unit Mobil Siap</span>
            <p class="font-mono text-3xl font-extrabold text-slate-900 mt-1">{{ $stats['available_cars'] }}</p>
            <span class="text-[11px] text-slate-500 mt-1 block">Tersedia di katalog</span>
        </div>
    </div>

    <!-- Table of Leads -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-black text-lg text-slate-900">Daftar Pengajuan Test Drive</h2>
            <span class="text-xs font-semibold text-slate-500">Total: {{ $testDrives->total() }} pengajuan</span>
        </div>

        @if($testDrives->isEmpty())
            <div class="py-16 text-center">
                <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-4">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-800">Belum Ada Pengajuan Test Drive</h3>
                <p class="text-sm text-slate-500 mt-1">Semua data lead test drive sudah tertangani atau belum ada permohonan baru.</p>
            </div>
        @else
            <!-- Desktop Table -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/75 border-b border-slate-200 text-[11px] uppercase tracking-wider text-slate-500 font-bold">
                            <th class="py-3.5 px-6">Pelanggan & Kontak</th>
                            <th class="py-3.5 px-6">Mobil Diminati</th>
                            <th class="py-3.5 px-6">Waktu & Lokasi</th>
                            <th class="py-3.5 px-6">SIM / Identitas</th>
                            <th class="py-3.5 px-6">Status</th>
                            <th class="py-3.5 px-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @foreach($testDrives as $td)
                            @php
                                $cleanPhone = preg_replace('/[^0-9]/', '', $td->customer->phone_number ?? '');
                                if (str_starts_with($cleanPhone, '0')) {
                                    $cleanPhone = '62' . substr($cleanPhone, 1);
                                }
                                $carName = ($td->car->brand ?? '') . ' ' . ($td->car->model_name ?? '');
                                $dateStr = $td->preferred_date ? $td->preferred_date->translatedFormat('l, d F Y H:i') . ' WIB' : '';
                                $waMessage = rawurlencode("Halo {$td->customer->name}, saya dari tim Sales Dealer Pro ingin mengonfirmasi jadwal pengajuan test drive unit {$carName} pada tanggal {$dateStr}. Apakah jadwal tersebut masih sesuai?");
                                $waUrl = "https://wa.me/{$cleanPhone}?text={$waMessage}";
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-4 px-6">
                                    <p class="font-bold text-slate-900">{{ $td->customer->name }}</p>
                                    <p class="text-xs text-slate-500 mt-0.5">{{ $td->customer->email }}</p>
                                    <div class="mt-1.5 flex items-center gap-2">
                                        <a href="{{ $waUrl }}" target="_blank" rel="noopener noreferrer" 
                                           class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 text-xs font-bold hover:bg-emerald-100 transition border border-emerald-200"
                                           title="Chat via WhatsApp">
                                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24">
                                                <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.312.045-.694.074-2.02-.472-1.696-.697-2.772-2.428-2.855-2.541-.085-.113-.679-.902-.679-1.721 0-.82.429-1.222.582-1.391.153-.169.333-.212.444-.212.111 0 .222.002.319.006.103.004.24-.039.375.286.14.338.477 1.162.518 1.247.042.085.07.184.014.297-.056.113-.085.184-.169.282-.085.099-.178.22-.254.296-.085.085-.174.177-.075.347.099.17.439.723.942 1.172.647.577 1.192.756 1.362.841.17.085.27.071.37-.042.101-.113.435-.508.551-.682.117-.174.233-.145.392-.086s1.019.48 1.195.568c.175.088.292.131.334.204.043.073.043.424-.101.829z"/>
                                            </svg>
                                            WA: {{ $td->customer->phone_number }}
                                        </a>
                                    </div>
                                </td>
                                <td class="py-4 px-6">
                                    <p class="font-bold text-slate-900">{{ $td->car->brand }} {{ $td->car->model_name }}</p>
                                    <p class="text-xs text-slate-500 font-mono">{{ $td->car->year }} &bull; Rp{{ number_format($td->car->price, 0, ',', '.') }}</p>
                                </td>
                                <td class="py-4 px-6">
                                    <p class="font-medium text-slate-900">{{ $td->preferred_date->translatedFormat('d M Y') }}</p>
                                    <p class="text-xs text-slate-500">{{ $td->preferred_date->format('H:i') }} WIB</p>
                                    <p class="text-[11px] text-slate-400 mt-1 max-w-xs truncate" title="{{ $td->pickup_location }}">
                                        📍 {{ $td->pickup_location ?? 'Showroom Pusat' }}
                                    </p>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="font-mono text-xs font-semibold text-slate-700 block">{{ $td->driver_license_id }}</span>
                                    @if($td->driver_license_image)
                                        <a href="{{ asset('storage/' . $td->driver_license_image) }}" target="_blank" class="text-[11px] text-blue-600 hover:underline mt-0.5 inline-block">
                                            Lihat Foto SIM &nearr;
                                        </a>
                                    @else
                                        <span class="text-[11px] text-slate-400 italic">Tanpa unggahan foto</span>
                                    @endif
                                </td>
                                <td class="py-4 px-6">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider border {{ $td->status->color() }}">
                                        <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                        {{ $td->status->label() }}
                                    </span>
                                    @if($td->status->value === 'rejected' && $td->rejection_reason)
                                        <p class="text-[11px] text-red-600 mt-1 max-w-xs italic">{{ $td->rejection_reason }}</p>
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-right space-x-1.5">
                                    @if($td->status->value === 'requested')
                                        @can('approve', $td)
                                            <form method="POST" action="{{ route('test-drives.approve', $td) }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="px-3 py-1.5 bg-emerald-600 text-white rounded-lg text-xs font-bold hover:bg-emerald-700 transition">
                                                    Approve
                                                </button>
                                            </form>
                                        @endcan

                                        @can('reject', $td)
                                            <button type="button" 
                                                    @click="openReject({{ $td->toJson() }})"
                                                    class="px-3 py-1.5 bg-red-50 text-red-600 border border-red-200 rounded-lg text-xs font-bold hover:bg-red-100 transition">
                                                Reject
                                            </button>
                                        @endcan
                                    @else
                                        <span class="text-xs text-slate-400 italic">Selesai diproses</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card Stack -->
            <div class="md:hidden divide-y divide-slate-100">
                @foreach($testDrives as $td)
                    @php
                        $cleanPhone = preg_replace('/[^0-9]/', '', $td->customer->phone_number ?? '');
                        if (str_starts_with($cleanPhone, '0')) {
                            $cleanPhone = '62' . substr($cleanPhone, 1);
                        }
                        $carName = ($td->car->brand ?? '') . ' ' . ($td->car->model_name ?? '');
                        $dateStr = $td->preferred_date ? $td->preferred_date->translatedFormat('l, d F Y H:i') . ' WIB' : '';
                        $waMessage = rawurlencode("Halo {$td->customer->name}, saya dari tim Sales Dealer Pro ingin mengonfirmasi jadwal test drive unit {$carName} pada tanggal {$dateStr}.");
                        $waUrl = "https://wa.me/{$cleanPhone}?text={$waMessage}";
                    @endphp
                    <div class="p-5 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-slate-900 text-sm">{{ $td->customer->name }}</span>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider border {{ $td->status->color() }}">
                                {{ $td->status->label() }}
                            </span>
                        </div>
                        <p class="font-extrabold text-base text-slate-900">{{ $td->car->brand }} {{ $td->car->model_name }}</p>
                        <p class="text-xs text-slate-500">Jadwal: {{ $td->preferred_date->translatedFormat('d M Y, H:i') }} WIB</p>
                        <p class="text-xs text-slate-500">Lokasi: {{ $td->pickup_location ?? 'Showroom Pusat' }}</p>
                        <p class="text-xs font-mono text-slate-600">SIM: {{ $td->driver_license_id }}</p>

                        <div class="pt-2 flex flex-wrap gap-2">
                            <a href="{{ $waUrl }}" target="_blank" class="flex-1 py-2 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold rounded-xl text-center">
                                Hubungi WA
                            </a>
                            @if($td->status->value === 'requested')
                                @can('approve', $td)
                                    <form method="POST" action="{{ route('test-drives.approve', $td) }}" class="flex-1">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="w-full py-2 bg-emerald-600 text-white text-xs font-bold rounded-xl">
                                            Approve
                                        </button>
                                    </form>
                                @endcan
                                @can('reject', $td)
                                    <button type="button" @click="openReject({{ $td->toJson() }})" class="flex-1 py-2 bg-red-50 text-red-600 border border-red-200 text-xs font-bold rounded-xl">
                                        Reject
                                    </button>
                                @endcan
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="p-6 border-t border-slate-100">
                {{ $testDrives->links() }}
            </div>
        @endif
    </div>

    <!-- Reject Reason Modal -->
    <div x-show="rejectModalOpen" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         @keydown.escape.window="rejectModalOpen = false">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="rejectModalOpen = false"></div>
        <div class="relative w-full max-w-md rounded-3xl bg-white border border-slate-200 shadow-2xl p-6 sm:p-8 overflow-hidden"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            <h3 class="font-black text-lg text-slate-900">Tolak Pengajuan Test Drive</h3>
            <p class="text-xs text-slate-500 mt-1">Masukkan alasan penolakan untuk disampaikan kepada pelanggan.</p>

            <form :action="selectedTestDrive ? '/dashboard/test-drives/' + selectedTestDrive.id + '/reject' : '#'" method="POST" class="mt-4 space-y-4">
                @csrf
                @method('PATCH')
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Alasan Penolakan</label>
                    <textarea name="rejection_reason" rows="3" required placeholder="Contoh: Unit sedang dalam jadwal servis berkala atau slot jam penuh..."
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
</div>
