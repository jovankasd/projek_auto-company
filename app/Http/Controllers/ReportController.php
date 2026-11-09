<?php

namespace App\Http\Controllers;

use App\Enums\ServiceStatus;
use App\Enums\UserRole;
use App\Models\Car;
use App\Models\Service;
use App\Models\Setting;
use App\Models\TestDrive;
use App\Models\User;
use App\Notifications\ExecutiveReportNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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

        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

        // 1. Service Stats
        $serviceQuery = Service::whereBetween('service_date', [
            Carbon::parse($startDate)->startOfDay(),
            Carbon::parse($endDate)->endOfDay(),
        ]);

        $totalServices = (clone $serviceQuery)->count();
        $completedServices = (clone $serviceQuery)->where('status', ServiceStatus::Completed)->count();
        $totalRevenue = (clone $serviceQuery)->where('status', ServiceStatus::Completed)->sum('total_cost');

        $servicesByType = (clone $serviceQuery)
            ->select('service_type', DB::raw('count(*) as count'))
            ->groupBy('service_type')
            ->pluck('count', 'service_type');

        // 2. Test Drive Stats
        $testDriveQuery = TestDrive::whereBetween('preferred_date', [
            Carbon::parse($startDate)->startOfDay(),
            Carbon::parse($endDate)->endOfDay(),
        ]);

        $totalTestDrives = (clone $testDriveQuery)->count();
        $testDrivesByStatus = (clone $testDriveQuery)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        // 3. Mechanic Performance
        $mechanics = User::where('role', UserRole::Mekanik)->get()->map(function (User $mechanic) use ($startDate, $endDate) {
            $assignedCount = Service::where('assigned_mechanic_id', $mechanic->id)
                ->whereBetween('service_date', [
                    Carbon::parse($startDate)->startOfDay(),
                    Carbon::parse($endDate)->endOfDay(),
                ])->count();

            $completedCount = Service::where('assigned_mechanic_id', $mechanic->id)
                ->where('status', ServiceStatus::Completed)
                ->whereBetween('service_date', [
                    Carbon::parse($startDate)->startOfDay(),
                    Carbon::parse($endDate)->endOfDay(),
                ])->count();

            return [
                'name' => $mechanic->name,
                'assigned' => $assignedCount,
                'completed' => $completedCount,
            ];
        });

        // 4. Most popular cars for test drive
        $popularCars = Car::withCount(['testDrives' => function ($q) use ($startDate, $endDate) {
            $q->whereBetween('preferred_date', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay(),
            ]);
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
    public function submitToOwner(Request $request): RedirectResponse
    {
        $user = auth()->user();
        if (! $user->isSuperAdmin()) {
            abort(403, 'Hanya Super Admin yang berhak memverifikasi dan mengirimkan laporan ke Owner.');
        }

        $note = $request->input('note', 'Laporan operasional 3 divisi (Sales, Admin Mekanik, Customer) telah diverifikasi lengkap.');

        // Simpan catatan waktu pengiriman
        Setting::set('last_report_submitted_at', now()->toDateTimeString(), 'reports');
        Setting::set('last_report_submitted_by', $user->name, 'reports');
        Setting::set('last_report_note', $note, 'reports');

        $summary = [
            'total_services' => Service::where('status', ServiceStatus::Completed)->count(),
            'available_cars' => Car::available()->count(),
            'total_test_drives' => TestDrive::count(),
            'total_revenue' => (float) Service::where('status', ServiceStatus::Completed)->sum('total_cost'),
        ];

        // Kirimkan notifikasi ke semua akun Owner
        $owners = User::where('role', UserRole::Owner)->get();
        foreach ($owners as $owner) {
            try {
                $owner->notify(new ExecutiveReportNotification(
                    title: 'Laporan Eksekutif Terpadu Dikirim oleh Super Admin',
                    message: "Super Admin ({$user->name}) telah memverifikasi dan mengirimkan laporan keseluruhan (Sales, Admin Mekanik, Customer).",
                    url: route('dashboard'),
                    summary: $summary
                ));
            } catch (\Throwable $e) {
            }
        }

        return back()->with('success', 'Laporan operasional 3 divisi berhasil diverifikasi & dikirimkan ke Owner!');
    }
}
