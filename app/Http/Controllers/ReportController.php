<?php

namespace App\Http\Controllers;

use App\Enums\ServiceStatus;
use App\Enums\UserRole;
use App\Models\Car;
use App\Models\Service;
use App\Models\TestDrive;
use App\Models\User;
use App\Services\ExecutiveReportService;
use App\Support\Timezone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();
        if (! $user->isSuperAdmin() && ! $user->isOwner()) {
            abort(403, 'Akses ditolak.');
        }

        $startDate = $request->input('start_date', Timezone::nowWib()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Timezone::nowWib()->endOfMonth()->toDateString());

        $startUtc = Timezone::startOfDayUtc($startDate);
        $endUtc = Timezone::endOfDayUtc($endDate);

        // 1. Service Stats
        $serviceQuery = Service::whereBetween('service_date', [$startUtc, $endUtc]);

        $totalServices = (clone $serviceQuery)->count();
        $completedServices = (clone $serviceQuery)->where('status', ServiceStatus::Completed)->count();
        $totalRevenue = (clone $serviceQuery)->where('status', ServiceStatus::Completed)->sum('total_cost');

        $servicesByType = (clone $serviceQuery)
            ->select('service_type', DB::raw('count(*) as count'))
            ->groupBy('service_type')
            ->pluck('count', 'service_type');

        // 2. Test Drive Stats
        $testDriveQuery = TestDrive::whereBetween('preferred_date', [$startUtc, $endUtc]);

        $totalTestDrives = (clone $testDriveQuery)->count();
        $testDrivesByStatus = (clone $testDriveQuery)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        // 3. Mechanic Performance
        $mechanics = User::where('role', UserRole::Mekanik)->get()->map(function (User $mechanic) use ($startUtc, $endUtc) {
            $assignedCount = Service::where('assigned_mechanic_id', $mechanic->id)
                ->whereBetween('service_date', [$startUtc, $endUtc])->count();

            $completedCount = Service::where('assigned_mechanic_id', $mechanic->id)
                ->where('status', ServiceStatus::Completed)
                ->whereBetween('service_date', [$startUtc, $endUtc])->count();

            return [
                'name' => $mechanic->name,
                'assigned' => $assignedCount,
                'completed' => $completedCount,
            ];
        });

        // 4. Most popular cars for test drive
        $popularCars = Car::withCount(['testDrives' => function ($q) use ($startUtc, $endUtc) {
            $q->whereBetween('preferred_date', [$startUtc, $endUtc]);
        }])->orderByDesc('test_drives_count')->take(5)->get();

        return view('dashboard.reports.index', compact(
            'startDate',
            'endDate',
            'totalServices',
            'completedServices',
            'totalRevenue',
            'servicesByType',
            'totalTestDrives',
            'testDrivesByStatus',
            'mechanics',
            'popularCars'
        ));
    }

    /**
     * Super Admin memverifikasi dan mengirimkan laporan keseluruhan resmi ke Owner.
     */
    public function submitToOwner(Request $request, ExecutiveReportService $reportService): RedirectResponse
    {
        $user = auth()->user();
        if (! $user->isSuperAdmin()) {
            abort(403, 'Hanya Super Admin yang berhak memverifikasi dan mengirimkan laporan ke Owner.');
        }

        $note = $request->input('note', 'Laporan operasional 3 divisi (Sales, Admin Mekanik, Customer) telah diverifikasi lengkap.');

        $result = $reportService->submit($user, $note);

        if (! $result['success']) {
            return back()->with('error', $result['message']);
        }

        return back()->with('success', $result['message']);
    }

    /**
     * Super Admin melakukan retry pengiriman laporan yang gagal secara idempoten.
     */
    public function retrySubmission(Request $request, ExecutiveReportService $reportService): RedirectResponse
    {
        $user = auth()->user();
        if (! $user->isSuperAdmin()) {
            abort(403, 'Hanya Super Admin yang berhak memverifikasi dan mengirimkan laporan ke Owner.');
        }

        $result = $reportService->retry($user);

        if ($result['status'] === 'already_sent') {
            return back()->with('info', $result['message']);
        }

        if (! $result['success']) {
            return back()->with('error', $result['message']);
        }

        return back()->with('success', $result['message']);
    }
}
