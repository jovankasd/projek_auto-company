<?php

namespace App\Http\Controllers;

use App\Enums\ServiceItemType;
use App\Enums\ServiceStatus;
use App\Enums\TestDriveStatus;
use App\Enums\UserRole;
use App\Models\Car;
use App\Models\Service;
use App\Models\ServiceItem;
use App\Models\Setting;
use App\Models\SparePart;
use App\Models\TestDrive;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        $role = $user->role instanceof UserRole ? $user->role : UserRole::tryFrom((string) $user->role);

        return match ($role) {
            UserRole::Customer => $this->customerDashboard($user),
            UserRole::Mekanik => $this->mekanikDashboard($user, $request),
            UserRole::Sales => $this->salesDashboard($user, $request),
            UserRole::SuperAdmin => $this->superAdminDashboard(),
            UserRole::Owner => $this->ownerDashboard($request),
        };
    }

    protected function customerDashboard(User $user): View
    {
        $activeService = Service::where('customer_id', $user->id)
            ->active()
            ->with(['items', 'statusLogs.changedBy', 'mechanic'])
            ->latest('service_date')
            ->first();

        $upcomingTestDrive = TestDrive::where('customer_id', $user->id)
            ->whereIn('status', [TestDriveStatus::Requested, TestDriveStatus::Approved])
            ->with(['car', 'sales'])
            ->latest('preferred_date')
            ->first();

        $recentServices = Service::where('customer_id', $user->id)
            ->with(['items', 'statusLogs.changedBy', 'mechanic'])
            ->latest('service_date')
            ->take(5)
            ->get();

        $recentTestDrives = TestDrive::where('customer_id', $user->id)
            ->with(['car', 'sales'])
            ->latest('preferred_date')
            ->take(5)
            ->get();

        $stats = [
            'total_services' => Service::where('customer_id', $user->id)->count(),
            'completed_services' => Service::where('customer_id', $user->id)->where('status', ServiceStatus::Completed)->count(),
            'total_test_drives' => TestDrive::where('customer_id', $user->id)->count(),
        ];

        return view('dashboard.index', [
            'role' => 'customer',
            'activeService' => $activeService,
            'upcomingTestDrive' => $upcomingTestDrive,
            'recentServices' => $recentServices,
            'recentTestDrives' => $recentTestDrives,
            'stats' => $stats,
        ]);
    }

    protected function mekanikDashboard(User $user, Request $request): View
    {
        $filter = $request->input('filter', 'today'); // 'today', 'week', 'all'

        $query = Service::with(['customer', 'items', 'statusLogs.changedBy']);

        // Mekanik melihat work order yang ditugaskan kepadanya atau yang confirmed/in_progress
        $query->where(function ($q) use ($user) {
            $q->where('assigned_mechanic_id', $user->id)
                ->orWhereNull('assigned_mechanic_id');
        });

        if ($filter === 'today') {
            $query->whereDate('service_date', Carbon::today());
        } elseif ($filter === 'week') {
            $query->whereBetween('service_date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
        }

        $workOrders = $query->orderByRaw("CASE status WHEN 'in_progress' THEN 1 WHEN 'confirmed' THEN 2 WHEN 'pending' THEN 3 WHEN 'completed' THEN 4 ELSE 5 END")
            ->latest('service_date')
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'active_jobs' => Service::where('assigned_mechanic_id', $user->id)->where('status', ServiceStatus::InProgress)->count(),
            'pending_confirmed' => Service::where('assigned_mechanic_id', $user->id)->where('status', ServiceStatus::Confirmed)->count(),
            'completed_today' => Service::where('assigned_mechanic_id', $user->id)
                ->where('status', ServiceStatus::Completed)
                ->whereDate('completed_at', Carbon::today())
                ->count(),
            'completed_month' => Service::where('assigned_mechanic_id', $user->id)
                ->where('status', ServiceStatus::Completed)
                ->whereBetween('completed_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
                ->count(),
            'total_spare_parts' => SparePart::count(),
            'low_stock_parts' => SparePart::lowStock()->count(),
        ];

        // Ambil suku cadang mobil (sparepart) visual untuk perbaikan
        $spareParts = SparePart::available()->ordered()->get();

        // Ambil tiket servis aktif yang dapat dikerjakan mekanik untuk pilihan modal pasang sparepart
        $activeServicesList = Service::whereIn('status', [ServiceStatus::Confirmed, ServiceStatus::InProgress])
            ->where(function ($q) use ($user) {
                $q->where('assigned_mechanic_id', $user->id)
                    ->orWhereNull('assigned_mechanic_id');
            })
            ->latest('service_date')
            ->get();

        // Grafik beban kerja & penyelesaian 7 hari terakhir
        $sevenDaysAgo = Carbon::now()->subDays(6)->startOfDay();
        $mechanicScope = fn () => Service::where(function ($q) use ($user) {
            $q->where('assigned_mechanic_id', $user->id)
                ->orWhereNull('assigned_mechanic_id');
        });

        $dailyJobs = $mechanicScope()
            ->select(DB::raw('DATE(service_date) as date'), DB::raw('count(*) as count'))
            ->where('service_date', '>=', $sevenDaysAgo)
            ->groupBy(DB::raw('DATE(service_date)'))
            ->pluck('count', 'date');

        $dailyCompleted = $mechanicScope()
            ->select(DB::raw('DATE(COALESCE(completed_at, service_date)) as date'), DB::raw('count(*) as count'))
            ->where('status', ServiceStatus::Completed)
            ->where(DB::raw('COALESCE(completed_at, service_date)'), '>=', $sevenDaysAgo)
            ->groupBy(DB::raw('DATE(COALESCE(completed_at, service_date))'))
            ->pluck('count', 'date');

        $weeklyWorkChart = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = Carbon::now()->subDays($i);
            $dateKey = $day->toDateString();
            $weeklyWorkChart[] = [
                'date_label' => $day->format('d M'),
                'day_name' => $day->translatedFormat('D'),
                'total_jobs' => (int) $dailyJobs->get($dateKey, 0),
                'completed_jobs' => (int) $dailyCompleted->get($dateKey, 0),
            ];
        }

        $statusBreakdown = [
            ['label' => 'Sedang Dikerjakan', 'count' => $stats['active_jobs'], 'hex' => '#2563eb'],
            ['label' => 'Siap Dikerjakan', 'count' => $stats['pending_confirmed'], 'hex' => '#f59e0b'],
            [
                'label' => 'Menunggu Penugasan',
                'count' => Service::whereNull('assigned_mechanic_id')
                    ->whereIn('status', [ServiceStatus::Pending, ServiceStatus::Confirmed])
                    ->count(),
                'hex' => '#64748b',
            ],
            ['label' => 'Selesai Bulan Ini', 'count' => $stats['completed_month'], 'hex' => '#059669'],
        ];

        $monthOrdersDenominator = $mechanicScope()
            ->whereBetween('service_date', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
            ->where('status', '!=', ServiceStatus::Cancelled)
            ->count();

        $completionRateMonth = $monthOrdersDenominator > 0
            ? min(100, (int) round(($stats['completed_month'] / $monthOrdersDenominator) * 100))
            : ($stats['completed_month'] > 0 ? 100 : 0);

        $totalSpareParts = (int) $stats['total_spare_parts'];
        $lowStockPartsCount = (int) $stats['low_stock_parts'];
        $healthyStockParts = max(0, $totalSpareParts - $lowStockPartsCount);
        $stockHealthRate = $totalSpareParts > 0
            ? (int) round(($healthyStockParts / $totalSpareParts) * 100)
            : 100;

        $workshopMetrics = [
            'completion_rate' => $completionRateMonth,
            'completed_month' => (int) $stats['completed_month'],
            'month_target' => $monthOrdersDenominator,
            'stock_health_rate' => $stockHealthRate,
            'healthy_parts' => $healthyStockParts,
            'low_stock_parts' => $lowStockPartsCount,
            'total_parts' => $totalSpareParts,
        ];

        return view('dashboard.index', [
            'role' => 'mekanik',
            'workOrders' => $workOrders,
            'filter' => $filter,
            'stats' => $stats,
            'spareParts' => $spareParts,
            'activeServicesList' => $activeServicesList,
            'weeklyWorkChart' => $weeklyWorkChart,
            'statusBreakdown' => $statusBreakdown,
            'workshopMetrics' => $workshopMetrics,
        ]);
    }

    protected function salesDashboard(User $user, Request $request): View
    {
        $statusFilter = $request->input('status', 'all');

        $leadScope = fn () => TestDrive::where(function ($q) use ($user) {
            $q->where('sales_id', $user->id)
                ->orWhereNull('sales_id');
        });

        $query = TestDrive::with(['customer', 'car', 'sales'])
            ->where(function ($q) use ($user) {
                $q->where('sales_id', $user->id)
                    ->orWhereNull('sales_id');
            });

        if ($statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }

        $testDrives = $query->latest('preferred_date')->paginate(15)->withQueryString();

        $stats = [
            'requested_leads' => $leadScope()->where('status', TestDriveStatus::Requested)->count(),
            'approved_this_month' => $leadScope()
                ->where('status', TestDriveStatus::Approved)
                ->whereBetween('approved_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
                ->count(),
            'completed_leads' => $leadScope()->where('status', TestDriveStatus::Completed)->count(),
            'available_cars' => Car::available()->count(),
        ];

        // Grafik tren pengajuan & konversi Test Drive 6 bulan terakhir
        $now = Carbon::now();
        $monthlyLeadChart = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthDate = $now->copy()->subMonths($i);
            $mStart = $monthDate->copy()->startOfMonth();
            $mEnd = $monthDate->copy()->endOfMonth();

            $totalLeads = $leadScope()->whereBetween('preferred_date', [$mStart, $mEnd])->count();
            $completedLeads = $leadScope()
                ->where('status', TestDriveStatus::Completed)
                ->whereBetween('preferred_date', [$mStart, $mEnd])
                ->count();

            $monthlyLeadChart[] = [
                'month_label' => $monthDate->translatedFormat('M Y'),
                'total_leads' => $totalLeads,
                'completed_leads' => $completedLeads,
            ];
        }

        $carInterestChart = Car::withCount(['testDrives' => function ($q) use ($user) {
            $q->where(function ($sub) use ($user) {
                $sub->where('sales_id', $user->id)
                    ->orWhereNull('sales_id');
            });
        }])->orderByDesc('test_drives_count')->take(5)->get();

        $leadStatusBreakdown = [
            ['label' => 'Menunggu', 'count' => $stats['requested_leads'], 'badge' => 'bg-amber-50 text-amber-800 border-amber-200'],
            ['label' => 'Disetujui', 'count' => $leadScope()->where('status', TestDriveStatus::Approved)->count(), 'badge' => 'bg-blue-50 text-blue-800 border-blue-200'],
            ['label' => 'Selesai', 'count' => $stats['completed_leads'], 'badge' => 'bg-emerald-50 text-emerald-800 border-emerald-200'],
            ['label' => 'Ditolak', 'count' => $leadScope()->where('status', TestDriveStatus::Rejected)->count(), 'badge' => 'bg-red-50 text-red-800 border-red-200'],
        ];

        $totalLeadsAll = $leadScope()->count();
        $approvedOrCompletedLeads = $leadScope()
            ->whereIn('status', [TestDriveStatus::Approved, TestDriveStatus::Completed])
            ->count();
        $completedLeadsAll = (int) $stats['completed_leads'];

        $leadFunnel = [
            'stages' => [
                [
                    'name' => 'Pengajuan Masuk',
                    'value' => $totalLeadsAll,
                    'rate' => $totalLeadsAll > 0 ? 100.0 : 0.0,
                ],
                [
                    'name' => 'Lolos & Disetujui',
                    'value' => $approvedOrCompletedLeads,
                    'rate' => $totalLeadsAll > 0 ? round(($approvedOrCompletedLeads / $totalLeadsAll) * 100, 1) : 0.0,
                ],
                [
                    'name' => 'Selesai Test Drive',
                    'value' => $completedLeadsAll,
                    'rate' => $totalLeadsAll > 0 ? round(($completedLeadsAll / $totalLeadsAll) * 100, 1) : 0.0,
                ],
            ],
            'approval_rate' => $totalLeadsAll > 0 ? round(($approvedOrCompletedLeads / $totalLeadsAll) * 100, 1) : 0.0,
            'completion_rate' => $totalLeadsAll > 0 ? round(($completedLeadsAll / $totalLeadsAll) * 100, 1) : 0.0,
        ];

        return view('dashboard.index', [
            'role' => 'sales',
            'testDrives' => $testDrives,
            'statusFilter' => $statusFilter,
            'stats' => $stats,
            'monthlyLeadChart' => $monthlyLeadChart,
            'carInterestChart' => $carInterestChart,
            'leadStatusBreakdown' => $leadStatusBreakdown,
            'leadFunnel' => $leadFunnel,
        ]);
    }

    protected function superAdminDashboard(): View
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        // 4 KPI Cards
        $totalBookingsMonth = Service::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();
        $activeUnits = Car::available()->count();
        $newLeadsMonth = TestDrive::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();
        $serviceRevenueMonth = Service::where('status', ServiceStatus::Completed)
            ->whereBetween('completed_at', [$startOfMonth, $endOfMonth])
            ->sum('total_cost');

        // Tren 30 hari servis
        $thirtyDaysAgo = Carbon::now()->subDays(29)->startOfDay();
        $dailyBookings = Service::select(DB::raw('DATE(service_date) as date'), DB::raw('count(*) as count'))
            ->where('service_date', '>=', $thirtyDaysAgo)
            ->groupBy(DB::raw('DATE(service_date)'))
            ->pluck('count', 'date');

        $chartDates = [];
        $chartCounts = [];
        for ($i = 29; $i >= 0; $i--) {
            $d = Carbon::now()->subDays($i)->toDateString();
            $chartDates[] = Carbon::parse($d)->format('d M');
            $chartCounts[] = $dailyBookings->get($d, 0);
        }

        $avgDailyBookings = count($chartCounts) > 0
            ? round(array_sum($chartCounts) / count($chartCounts), 1)
            : 0.0;

        // Heatmap kepadatan slot servis 7 hari terakhir (jam 08-17)
        $heatmapStart = Carbon::now()->subDays(6)->startOfDay();
        $heatmapEnd = Carbon::now()->endOfDay();
        $recentHeatmapServices = Service::whereBetween('service_date', [$heatmapStart, $heatmapEnd])
            ->where('status', '!=', ServiceStatus::Cancelled)
            ->get(['service_date', 'slot_hour']);

        $slotLabels = ['08-09', '10-11', '12-13', '14-15', '16-17'];
        $dayKeys = [];
        $dayLabels = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = Carbon::now()->subDays($i);
            $dayKeys[] = $day->toDateString();
            $dayLabels[] = $day->translatedFormat('D d');
        }

        $slotCounts = [];
        foreach ($recentHeatmapServices as $srv) {
            $dateStr = $srv->service_date?->toDateString();
            $dayIdx = array_search($dateStr, $dayKeys, true);
            if ($dayIdx === false) {
                continue;
            }
            $hour = (int) $srv->slot_hour;
            $slotIdx = match (true) {
                $hour <= 9 => 0,
                $hour <= 11 => 1,
                $hour <= 13 => 2,
                $hour <= 15 => 3,
                default => 4,
            };
            $slotCounts[$dayIdx][$slotIdx] = ($slotCounts[$dayIdx][$slotIdx] ?? 0) + 1;
        }

        $heatmapPoints = [];
        $heatmapMax = 1;
        foreach ($dayKeys as $dIdx => $_) {
            foreach ($slotLabels as $sIdx => $_slot) {
                $val = $slotCounts[$dIdx][$sIdx] ?? 0;
                if ($val > $heatmapMax) {
                    $heatmapMax = $val;
                }
                $heatmapPoints[] = [$dIdx, $sIdx, $val];
            }
        }

        $slotHeatmap = [
            'days' => $dayLabels,
            'slots' => $slotLabels,
            'points' => $heatmapPoints,
            'maxCount' => $heatmapMax,
            'total7d' => $recentHeatmapServices->count(),
        ];

        $recentServices = Service::with(['customer', 'mechanic'])
            ->latest('created_at')
            ->take(10)
            ->get();

        $recentTestDrives = TestDrive::with(['customer', 'car', 'sales'])
            ->latest('created_at')
            ->take(10)
            ->get();

        $mechanics = User::where('role', UserRole::Mekanik)->where('is_active', true)->get();

        // Data Pusat Pengendali 3 Divisi untuk Super Admin
        $pendingTestDrives = TestDrive::where('status', TestDriveStatus::Requested)
            ->with(['car', 'customer'])
            ->take(6)
            ->get();

        $unassignedServices = Service::whereNull('assigned_mechanic_id')
            ->whereIn('status', [ServiceStatus::Pending, ServiceStatus::Confirmed])
            ->with(['customer'])
            ->take(6)
            ->get();

        $lowStockParts = SparePart::lowStock()->take(6)->get();

        $totalCustomers = User::where('role', UserRole::Customer)->count();
        $newCustomersMonth = User::where('role', UserRole::Customer)
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->count();

        // Status Laporan Resmi Terakhir ke Owner
        $lastReportSubmittedAt = Setting::get('last_report_submitted_at');
        $lastReportSubmittedBy = Setting::get('last_report_submitted_by', 'Super Admin');
        $lastReportNote = Setting::get('last_report_note');

        $kpis = [
            'total_bookings_month' => $totalBookingsMonth,
            'active_units' => $activeUnits,
            'new_leads_month' => $newLeadsMonth,
            'service_revenue_month' => $serviceRevenueMonth,
        ];

        return view('dashboard.index', [
            'role' => 'super_admin',
            'kpis' => $kpis,
            'chartDates' => $chartDates,
            'chartCounts' => $chartCounts,
            'avgDailyBookings' => $avgDailyBookings,
            'slotHeatmap' => $slotHeatmap,
            'recentServices' => $recentServices,
            'recentTestDrives' => $recentTestDrives,
            'mechanics' => $mechanics,
            'pendingTestDrives' => $pendingTestDrives,
            'unassignedServices' => $unassignedServices,
            'lowStockParts' => $lowStockParts,
            'totalCustomers' => $totalCustomers,
            'newCustomersMonth' => $newCustomersMonth,
            'lastReportSubmittedAt' => $lastReportSubmittedAt,
            'lastReportSubmittedBy' => $lastReportSubmittedBy,
            'lastReportNote' => $lastReportNote,
        ]);
    }

    protected function ownerDashboard(Request $request): View
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();
        $startOfYear = $now->copy()->startOfYear();
        $endOfYear = $now->copy()->endOfYear();

        // 1. Total Pemasukan Servis (Bulan ini & Sepanjang Waktu)
        $revenueThisMonth = (float) Service::where('status', ServiceStatus::Completed)
            ->whereBetween('completed_at', [$startOfMonth, $endOfMonth])
            ->sum('total_cost');

        $revenueThisYear = (float) Service::where('status', ServiceStatus::Completed)
            ->whereBetween('completed_at', [$startOfYear, $endOfYear])
            ->sum('total_cost');

        $totalAllTimeRevenue = (float) Service::where('status', ServiceStatus::Completed)->sum('total_cost');

        // Rincian Pemasukan Bulan Ini (Part vs Labor vs Consumable)
        $completedServiceIdsThisMonth = Service::where('status', ServiceStatus::Completed)
            ->whereBetween('completed_at', [$startOfMonth, $endOfMonth])
            ->pluck('id');

        $revenueParts = (float) ServiceItem::whereIn('service_id', $completedServiceIdsThisMonth)
            ->where('item_type', ServiceItemType::Part)
            ->sum('subtotal');

        $revenueLabor = (float) ServiceItem::whereIn('service_id', $completedServiceIdsThisMonth)
            ->where('item_type', ServiceItemType::Labor)
            ->sum('subtotal');

        $revenueConsumable = (float) ServiceItem::whereIn('service_id', $completedServiceIdsThisMonth)
            ->where('item_type', ServiceItemType::Consumable)
            ->sum('subtotal');

        // 2. Metrik Penjualan & Inventaris Mobil (Divisi Sales)
        $totalCarUnits = Car::count();
        $activeCarsForSale = Car::available()->count();
        $carInventoryValue = (float) Car::available()->sum('price');
        $totalCarStockQty = (int) Car::available()->sum('stock_quantity');
        $testDriveLeadsThisMonth = TestDrive::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();
        $testDriveCompletedThisMonth = TestDrive::where('status', TestDriveStatus::Completed)
            ->where(function ($query) use ($startOfMonth, $endOfMonth) {
                $query->whereBetween('completed_at', [$startOfMonth, $endOfMonth])
                    ->orWhere(function ($fallback) use ($startOfMonth, $endOfMonth) {
                        $fallback->whereNull('completed_at')
                            ->whereBetween('updated_at', [$startOfMonth, $endOfMonth]);
                    });
            })
            ->count();

        // 3. Metrik Suku Cadang & Kinerja Servis (Divisi Admin Mekanik)
        $totalSparePartsCount = SparePart::count();
        $sparePartInventoryValue = (float) SparePart::select(DB::raw('SUM(price * stock_quantity) as total_val'))->value('total_val');
        $lowStockSpareParts = SparePart::whereColumn('stock_quantity', '<=', 'min_stock_alert')->count();
        $totalServicesCompletedThisMonth = Service::where('status', ServiceStatus::Completed)
            ->whereBetween('completed_at', [$startOfMonth, $endOfMonth])
            ->count();

        // Top suku cadang yang paling banyak digunakan perbaikan bulan ini
        $topUsedSpareParts = ServiceItem::where('item_type', ServiceItemType::Part)
            ->select('name', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(subtotal) as total_revenue'))
            ->groupBy('name')
            ->orderByDesc('total_qty')
            ->take(5)
            ->get();

        // 4. Metrik Customer
        $totalCustomers = User::where('role', UserRole::Customer)->count();
        $newCustomersMonth = User::where('role', UserRole::Customer)
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->count();

        // 5. Tren Pemasukan 6 Bulan Terakhir (Breakdown Stacked + Jumlah Unit)
        $sixMonthsStart = $now->copy()->subMonths(5)->startOfMonth();
        $sixMonthsEnd = $now->copy()->endOfMonth();

        $sixMonthServices = Service::where('status', ServiceStatus::Completed)
            ->whereBetween('completed_at', [$sixMonthsStart, $sixMonthsEnd])
            ->with('items')
            ->get();

        $groupedServices = $sixMonthServices->groupBy(fn (Service $service) => $service->completed_at?->format('Y-m'));

        $monthlyRevenueChart = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthDate = $now->copy()->subMonths($i);
            $monthKey = $monthDate->format('Y-m');
            $servicesInMonth = $groupedServices->get($monthKey, collect());

            $parts = 0.0;
            $labor = 0.0;
            $consumable = 0.0;

            foreach ($servicesInMonth as $service) {
                if ($service->items->isEmpty()) {
                    $labor += (float) $service->total_cost;

                    continue;
                }

                foreach ($service->items as $item) {
                    $sub = (float) $item->subtotal;
                    match ($item->item_type) {
                        ServiceItemType::Part => $parts += $sub,
                        ServiceItemType::Labor => $labor += $sub,
                        ServiceItemType::Consumable => $consumable += $sub,
                        default => $labor += $sub,
                    };
                }
            }

            $rev = (float) $servicesInMonth->sum('total_cost');

            $monthlyRevenueChart[] = [
                'month_label' => $monthDate->translatedFormat('M Y'),
                'revenue' => $rev,
                'parts' => round($parts, 2),
                'labor' => round($labor, 2),
                'consumable' => round($consumable, 2),
                'count' => $servicesInMonth->count(),
            ];
        }

        // 6. Transaksi Servis Terakhir Bernilai Tinggi (Top Transactions)
        $topRecentTransactions = Service::where('status', ServiceStatus::Completed)
            ->with(['customer', 'mechanic', 'items'])
            ->latest('completed_at')
            ->take(8)
            ->get();

        // 7. Status Pengiriman Laporan Terakhir dari Super Admin
        $lastReportSubmittedAt = Setting::get('last_report_submitted_at');
        $lastReportSubmittedBy = Setting::get('last_report_submitted_by', 'Super Admin');
        $lastReportNote = Setting::get('last_report_note');

        return view('dashboard.index', [
            'role' => 'owner',
            'revenueThisMonth' => $revenueThisMonth,
            'revenueThisYear' => $revenueThisYear,
            'totalAllTimeRevenue' => $totalAllTimeRevenue,
            'revenueParts' => $revenueParts,
            'revenueLabor' => $revenueLabor,
            'revenueConsumable' => $revenueConsumable,
            'totalCarUnits' => $totalCarUnits,
            'activeCarsForSale' => $activeCarsForSale,
            'carInventoryValue' => $carInventoryValue,
            'totalCarStockQty' => $totalCarStockQty,
            'testDriveLeadsThisMonth' => $testDriveLeadsThisMonth,
            'testDriveCompletedThisMonth' => $testDriveCompletedThisMonth,
            'totalSparePartsCount' => $totalSparePartsCount,
            'sparePartInventoryValue' => $sparePartInventoryValue,
            'lowStockSpareParts' => $lowStockSpareParts,
            'totalServicesCompletedThisMonth' => $totalServicesCompletedThisMonth,
            'topUsedSpareParts' => $topUsedSpareParts,
            'totalCustomers' => $totalCustomers,
            'newCustomersMonth' => $newCustomersMonth,
            'monthlyRevenueChart' => $monthlyRevenueChart,
            'topRecentTransactions' => $topRecentTransactions,
            'lastReportSubmittedAt' => $lastReportSubmittedAt,
            'lastReportSubmittedBy' => $lastReportSubmittedBy,
            'lastReportNote' => $lastReportNote,
        ]);
    }
}
