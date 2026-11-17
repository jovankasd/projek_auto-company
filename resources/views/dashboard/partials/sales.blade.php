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
            <p class="text-slate-600 text-sm mt-1">Kelola permohonan test drive pelanggan, lakukan approval, dan hubungi pelanggan via WhatsApp.</p>
        </div>

        <!-- Filter Status -->
        <div class="inline-flex flex-wrap p-1 rounded-2xl bg-white border border-slate-200/80 shadow-sm gap-1 self-start sm:self-center">
            <a href="{{ route('dashboard', ['status' => 'all']) }}" 
               class="min-h-[44px] inline-flex items-center px-4 py-2 rounded-xl text-xs font-bold transition {{ $statusFilter === 'all' ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                Semua
            </a>
            <a href="{{ route('dashboard', ['status' => 'requested']) }}" 
               class="min-h-[44px] inline-flex items-center px-4 py-2 rounded-xl text-xs font-bold transition {{ $statusFilter === 'requested' ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                Menunggu Approval
            </a>
            <a href="{{ route('dashboard', ['status' => 'approved']) }}" 
               class="min-h-[44px] inline-flex items-center px-4 py-2 rounded-xl text-xs font-bold transition {{ $statusFilter === 'approved' ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                Disetujui
            </a>
        </div>
    </div>

    <!-- Quick Metrics -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-5">
            <span class="text-xs uppercase font-bold text-slate-600">Lead Baru (Menunggu)</span>
            <p class="font-mono tabular-nums text-3xl font-extrabold text-amber-700 mt-1">{{ $stats['requested_leads'] }}</p>
            <span class="text-[11px] text-slate-600 mt-1 block">Perlu verifikasi & approval</span>
        </div>
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-5">
            <span class="text-xs uppercase font-bold text-slate-600">Disetujui Bulan Ini</span>
            <p class="font-mono tabular-nums text-3xl font-extrabold text-blue-700 mt-1">{{ $stats['approved_this_month'] }}</p>
            <span class="text-[11px] text-slate-600 mt-1 block">Jadwal test drive aktif</span>
        </div>
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-5">
            <span class="text-xs uppercase font-bold text-slate-600">Selesai Dicoba</span>
            <p class="font-mono tabular-nums text-3xl font-extrabold text-emerald-700 mt-1">{{ $stats['completed_leads'] }}</p>
            <span class="text-[11px] text-slate-600 mt-1 block">Lead potensial transaksi</span>
        </div>
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-5">
            <span class="text-xs uppercase font-bold text-slate-600">Unit Mobil Siap</span>
            <p class="font-mono tabular-nums text-3xl font-extrabold text-slate-900 mt-1">{{ $stats['available_cars'] }}</p>
            <span class="text-[11px] text-slate-600 mt-1 block">Tersedia di katalog</span>
        </div>
    </div>

    <!-- Grafik Analitik Sales & Minat Unit -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Funnel Konversi & Tren 6 Bulan Test Drive -->
        <div class="lg:col-span-2 bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-8 flex flex-col justify-between">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-5 border-b border-slate-100 gap-3">
                <div>
                    <h2 class="text-lg font-black text-slate-900">Tren Pengajuan &amp; Konversi Test Drive (6 Bulan Terakhir)</h2>
                    <p class="text-xs text-slate-600 mt-0.5">Tahapan konversi prospek dari pengajuan hingga selesai uji berkendara.</p>
                </div>
                <div class="flex flex-wrap items-center gap-2 self-start sm:self-center">
                    <span class="text-xs font-mono tabular-nums font-bold text-emerald-800 bg-emerald-50 border border-emerald-200 px-3 py-1.5 rounded-xl">
                        Konversi: {{ number_format($leadFunnel['completion_rate'] ?? 0, 1, ',', '.') }}%
                    </span>
                    <div class="inline-flex p-1 rounded-xl bg-slate-100 border border-slate-200/80 text-xs" data-echarts-toggle-group>
                        <button type="button"
                                data-echarts-toggle="funnel"
                                data-echarts-target="#sales-lead-chart"
                                aria-pressed="true"
                                class="px-3 py-1.5 rounded-lg bg-white text-slate-900 font-bold shadow-sm transition">
                            Funnel Konversi
                        </button>
                        <button type="button"
                                data-echarts-toggle="trend"
                                data-echarts-target="#sales-lead-chart"
                                aria-pressed="false"
                                class="px-3 py-1.5 rounded-lg text-slate-600 font-semibold hover:text-slate-900 transition">
                            Tren 6 Bulan
                        </button>
                    </div>
                </div>
            </div>

            @php
                $salesFunnelPayload = [
                    'type' => 'sales-funnel',
                    'stages' => $leadFunnel['stages'] ?? [],
                    'monthly' => collect($monthlyLeadChart ?? [])->values()->all(),
                ];
            @endphp

            <div class="mt-5">
                <div id="sales-lead-chart"
                     role="img"
                     aria-label="Grafik Funnel Konversi dan Tren Test Drive 6 Bulan Terakhir"
                     class="relative h-64 w-full"
                     data-echarts-mode="funnel"
                     data-echarts-config='@json($salesFunnelPayload)'></div>

                <div class="mt-4 pt-4 border-t border-slate-100 grid grid-cols-3 gap-2.5">
                    @foreach($leadFunnel['stages'] ?? [] as $idx => $stage)
                        <div class="px-3 py-2.5 rounded-2xl bg-slate-50 border border-slate-200/70">
                            <span class="block text-[11px] font-bold text-slate-600 truncate">{{ $idx + 1 }}. {{ $stage['name'] }}</span>
                            <div class="flex items-baseline justify-between gap-2 mt-1">
                                <span class="font-mono tabular-nums text-base font-black text-slate-900">{{ $stage['value'] }}</span>
                                <span class="font-mono tabular-nums text-[11px] font-bold text-blue-700">{{ number_format($stage['rate'], 0) }}%</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Minat Unit Mobil & Status Lead -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-8 flex flex-col justify-between gap-5">
            <div>
                <h2 class="text-lg font-black text-slate-900">Minat Unit Test Drive</h2>
                <p class="text-xs text-slate-600 mt-0.5 mb-3">Popularitas model kendaraan di mata prospek.</p>

                @php
                    $salesInterestPayload = [
                        'type' => 'sales-lollipop',
                        'items' => collect($carInterestChart ?? [])->map(fn ($c) => [
                            'label' => trim($c->brand.' '.$c->model_name),
                            'value' => (int) $c->test_drives_count,
                            'category' => $c->category?->label() ?? '',
                        ])->values()->all(),
                    ];
                @endphp

                <div role="img"
                     aria-label="Grafik Minat Unit Test Drive per Model"
                     class="relative h-56 w-full"
                     data-echarts-config='@json($salesInterestPayload)'></div>
            </div>

            <div class="pt-4 border-t border-slate-100 grid grid-cols-2 gap-2">
                @foreach($leadStatusBreakdown ?? [] as $stItem)
                    <div class="px-3 py-2 rounded-xl border text-xs flex items-center justify-between {{ $stItem['badge'] }}">
                        <span class="font-semibold truncate">{{ $stItem['label'] }}</span>
                        <span class="font-mono tabular-nums font-black">{{ $stItem['count'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Table of Leads -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex flex-wrap items-center justify-between gap-2">
            <h2 class="font-black text-lg text-slate-900">Daftar Pengajuan Test Drive</h2>
            <span class="text-xs font-semibold text-slate-600">Total: {{ $testDrives->total() }} pengajuan</span>
        </div>

        @if($testDrives->isEmpty())
            <div class="py-16 text-center px-4">
                <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-600 mx-auto flex items-center justify-center mb-4">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-900">Belum Ada Pengajuan Test Drive</h3>
                <p class="text-sm text-slate-600 mt-1">Semua data lead test drive sudah tertangani atau belum ada permohonan baru.</p>
            </div>
        @else
            <!-- Desktop Table -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200 text-[11px] uppercase tracking-wider text-slate-600 font-bold">
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
                                $customerName = $td->customer?->name ?? 'Pelanggan';
                                $customerEmail = $td->customer?->email ?? '-';
                                $customerPhone = $td->customer?->phone_number ?? '';
                                $cleanPhone = preg_replace('/[^0-9]/', '', $customerPhone);
                                if (str_starts_with($cleanPhone, '0')) {
                                    $cleanPhone = '62' . substr($cleanPhone, 1);
                                }
                                $carName = trim(($td->car?->brand ?? '') . ' ' . ($td->car?->model_name ?? 'Unit Showroom'));
                                $dateStr = $td->preferred_date ? $td->preferred_date->translatedFormat('l, d F Y H:i') . ' WIB' : '-';
                                $waMessage = rawurlencode("Halo {$customerName}, saya dari tim Sales Dealer Pro ingin mengonfirmasi jadwal pengajuan test drive unit {$carName} pada tanggal {$dateStr}. Apakah jadwal tersebut masih sesuai?");
                                $waUrl = $cleanPhone !== '' ? "https://wa.me/{$cleanPhone}?text={$waMessage}" : null;
                                $statusVal = $td->status?->value ?? (string) $td->status;
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-4 px-6">
                                    @if($td->ticket_number)
                                        <span class="inline-block px-2 py-0.5 mb-1 rounded-md bg-slate-100 border border-slate-200 font-mono text-[11px] font-bold text-slate-800">{{ $td->ticket_number }}</span>
                                    @endif
                                    <p class="font-bold text-slate-900">{{ $customerName }}</p>
                                    <p class="text-xs text-slate-600 mt-0.5">{{ $customerEmail }}</p>
                                    @if($waUrl)
                                        <div class="mt-1.5 flex items-center gap-2">
                                            <a href="{{ $waUrl }}" target="_blank" rel="noopener noreferrer" 
                                               class="min-h-[36px] inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-emerald-50 text-emerald-800 text-xs font-bold hover:bg-emerald-100 transition border border-emerald-200"
                                               title="Chat via WhatsApp">
                                                <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.312.045-.694.074-2.02-.472-1.696-.697-2.772-2.428-2.855-2.541-.085-.113-.679-.902-.679-1.721 0-.82.429-1.222.582-1.391.153-.169.333-.212.444-.212.111 0 .222.002.319.006.103.004.24-.039.375.286.14.338.477 1.162.518 1.247.042.085.07.184.014.297-.056.113-.085.184-.169.282-.085.099-.178.22-.254.296-.085.085-.174.177-.075.347.099.17.439.723.942 1.172.647.577 1.192.756 1.362.841.17.085.27.071.37-.042.101-.113.435-.508.551-.682.117-.174.233-.145.392-.086s1.019.48 1.195.568c.175.088.292.131.334.204.043.073.043.424-.101.829z"/>
                                                </svg>
                                                <span>WA: {{ $customerPhone }}</span>
                                            </a>
                                        </div>
                                    @endif
                                </td>
                                <td class="py-4 px-6">
                                    <p class="font-bold text-slate-900">{{ $carName }}</p>
                                    @if($td->car)
                                        <p class="text-xs text-slate-600 font-mono tabular-nums">{{ $td->car->year }} &bull; Rp{{ number_format($td->car->price, 0, ',', '.') }}</p>
                                    @endif
                                </td>
                                <td class="py-4 px-6">
                                    <p class="font-medium text-slate-900">{{ $td->preferred_date ? $td->preferred_date->translatedFormat('d M Y') : '-' }}</p>
                                    <p class="text-xs text-slate-600">{{ $td->preferred_date ? $td->preferred_date->format('H:i') . ' WIB' : '-' }}</p>
                                    <p class="text-xs text-slate-600 mt-1 max-w-xs truncate inline-flex items-center gap-1" title="{{ $td->pickup_location }}">
                                        <svg class="w-3.5 h-3.5 text-slate-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        <span>{{ $td->pickup_location ?? 'Showroom Pusat' }}</span>
                                    </p>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="font-mono text-xs font-semibold text-slate-800 block">{{ $td->driver_license_id }}</span>
                                    @if($td->driver_license_image)
                                        <a href="{{ route('test-drives.license', $td) }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 text-xs font-semibold text-blue-700 hover:underline mt-1">
                                            <span>Lihat Foto SIM</span>
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                            </svg>
                                        </a>
                                    @else
                                        <span class="text-xs text-slate-600 italic mt-0.5 block">Tanpa unggahan foto</span>
                                    @endif
                                </td>
                                <td class="py-4 px-6">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider border {{ $td->status->color() }}">
                                        {{ $td->status->label() }}
                                    </span>
                                    @if($statusVal === 'rejected' && $td->rejection_reason)
                                        <p class="text-xs text-red-700 mt-1.5 max-w-xs">{{ $td->rejection_reason }}</p>
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-right space-x-1.5 whitespace-nowrap">
                                    @if($statusVal === 'requested')
                                        <form method="POST" action="{{ route('test-drives.approve', $td) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="min-h-[44px] px-3.5 py-2 bg-emerald-700 text-white rounded-xl text-xs font-bold hover:bg-emerald-800 transition">
                                                Setujui
                                            </button>
                                        </form>

                                        <button type="button" 
                                                @click="openReject({{ \Illuminate\Support\Js::from(['id' => $td->id, 'reject_url' => route('test-drives.reject', $td)]) }})"
                                                class="min-h-[44px] px-3.5 py-2 bg-red-50 text-red-700 border border-red-200 rounded-xl text-xs font-bold hover:bg-red-100 transition">
                                            Tolak
                                        </button>
                                    @elseif($statusVal === 'approved')
                                        <form method="POST" action="{{ route('test-drives.complete', $td) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="min-h-[44px] px-3.5 py-2 bg-blue-600 text-white rounded-xl text-xs font-bold hover:bg-blue-700 transition">
                                                Selesai
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs text-slate-600">Selesai diproses</span>
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
                        $customerName = $td->customer?->name ?? 'Pelanggan';
                        $customerPhone = $td->customer?->phone_number ?? '';
                        $cleanPhone = preg_replace('/[^0-9]/', '', $customerPhone);
                        if (str_starts_with($cleanPhone, '0')) {
                            $cleanPhone = '62' . substr($cleanPhone, 1);
                        }
                        $carName = trim(($td->car?->brand ?? '') . ' ' . ($td->car?->model_name ?? 'Unit Showroom'));
                        $dateStr = $td->preferred_date ? $td->preferred_date->translatedFormat('l, d F Y H:i') . ' WIB' : '-';
                        $waMessage = rawurlencode("Halo {$customerName}, saya dari tim Sales Dealer Pro ingin mengonfirmasi jadwal test drive unit {$carName} pada tanggal {$dateStr}.");
                        $waUrl = $cleanPhone !== '' ? "https://wa.me/{$cleanPhone}?text={$waMessage}" : null;
                        $statusVal = $td->status?->value ?? (string) $td->status;
                    @endphp
                    <div class="p-5 space-y-3">
                        <div class="flex items-center justify-between gap-2">
                            <span class="font-bold text-slate-900 text-sm">{{ $customerName }}</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider border {{ $td->status->color() }}">
                                {{ $td->status->label() }}
                            </span>
                        </div>
                        <p class="font-extrabold text-base text-slate-900">{{ $carName }}</p>
                        <p class="text-xs text-slate-600">Jadwal: {{ $td->preferred_date ? $td->preferred_date->translatedFormat('d M Y, H:i') . ' WIB' : '-' }}</p>
                        <p class="text-xs text-slate-600">Lokasi: {{ $td->pickup_location ?? 'Showroom Pusat' }}</p>
                        <div class="flex flex-wrap items-center justify-between gap-2 text-xs">
                            <span class="font-mono text-slate-700">SIM: {{ $td->driver_license_id }}</span>
                            @if($td->driver_license_image)
                                <a href="{{ route('test-drives.license', $td) }}" target="_blank" rel="noopener noreferrer" class="font-bold text-blue-700 hover:underline inline-flex items-center gap-1">
                                    <span>Lihat Foto SIM</span>
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                    </svg>
                                </a>
                            @endif
                        </div>

                        @if($statusVal === 'rejected' && $td->rejection_reason)
                            <div class="p-3 rounded-xl bg-red-50 border border-red-200 text-xs text-red-800">
                                <span class="font-bold block">Alasan Penolakan:</span>
                                <span>{{ $td->rejection_reason }}</span>
                            </div>
                        @endif

                        <div class="pt-2 flex flex-wrap gap-2">
                            @if($waUrl)
                                <a href="{{ $waUrl }}" target="_blank" rel="noopener noreferrer" class="flex-1 min-h-[44px] inline-flex items-center justify-center px-3 py-2 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold rounded-xl">
                                    Hubungi WA
                                </a>
                            @endif
                            @if($statusVal === 'requested')
                                <form method="POST" action="{{ route('test-drives.approve', $td) }}" class="flex-1">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="w-full min-h-[44px] px-3 py-2 bg-emerald-700 text-white text-xs font-bold rounded-xl hover:bg-emerald-800 transition">
                                        Setujui
                                    </button>
                                </form>
                                <button type="button" @click="openReject({{ \Illuminate\Support\Js::from(['id' => $td->id, 'reject_url' => route('test-drives.reject', $td)]) }})" class="flex-1 min-h-[44px] px-3 py-2 bg-red-50 text-red-700 border border-red-200 text-xs font-bold rounded-xl hover:bg-red-100 transition">
                                    Tolak
                                </button>
                            @elseif($statusVal === 'approved')
                                <form method="POST" action="{{ route('test-drives.complete', $td) }}" class="flex-1">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="w-full min-h-[44px] px-3 py-2 bg-blue-600 text-white text-xs font-bold rounded-xl hover:bg-blue-700 transition">
                                        Tandai Selesai
                                    </button>
                                </form>
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
         role="dialog"
         aria-modal="true"
         aria-labelledby="reject-td-title"
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         @keydown.escape.window="rejectModalOpen = false">
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" @click="rejectModalOpen = false"></div>
        <div class="relative w-full max-w-md rounded-3xl bg-white border border-slate-200/80 shadow-2xl p-6 sm:p-8 overflow-hidden"
             x-transition:enter="transition ease-out duration-200 transform"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h3 id="reject-td-title" class="font-black text-lg text-slate-900">Tolak Pengajuan Test Drive</h3>
                    <p class="text-xs text-slate-600 mt-1">Masukkan alasan penolakan untuk disampaikan kepada pelanggan.</p>
                </div>
                <button type="button" @click="rejectModalOpen = false" aria-label="Tutup modal penolakan" class="min-h-[44px] min-w-[44px] inline-flex items-center justify-center rounded-xl text-slate-600 hover:bg-slate-100 transition -mt-1 -mr-1">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form :action="selectedTestDrive ? selectedTestDrive.reject_url : '#'" method="POST" class="mt-4 space-y-4">
                @csrf
                @method('PATCH')
                <div>
                    <label for="rejection_reason_input" class="block text-xs font-bold uppercase text-slate-700 mb-1.5">Alasan Penolakan</label>
                    <textarea id="rejection_reason_input" name="rejection_reason" rows="3" required placeholder="Contoh: Unit sedang dalam jadwal servis berkala atau slot jam penuh..."
                              class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm text-slate-900 focus:ring-2 focus:ring-blue-600 focus:outline-none"></textarea>
                </div>
                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="rejectModalOpen = false" class="min-h-[44px] px-4 py-2 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-100 transition">
                        Batal
                    </button>
                    <button type="submit" class="min-h-[44px] px-5 py-2 rounded-xl text-xs font-bold bg-red-600 text-white hover:bg-red-700 transition">
                        Konfirmasi Penolakan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
