<?php

namespace App\Http\Controllers;

use App\Enums\TestDriveStatus;
use App\Enums\UserRole;
use App\Http\Requests\StoreTestDriveRequest;
use App\Models\TestDrive;
use App\Models\User;
use App\Notifications\TestDriveRequestedNotification;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TestDriveController extends Controller
{
    use AuthorizesRequests;

    public function store(StoreTestDriveRequest $request): JsonResponse|RedirectResponse
    {
        // 1. Session Retention & Guest redirect
        if (! auth()->check()) {
            session()->put('pending_test_drive', $request->validated());

            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'unauthenticated',
                    'redirect' => route('login'),
                    'message' => 'Silakan masuk untuk melanjutkan. Data Anda sudah kami simpan.',
                ], 401);
            }

            return redirect()->route('login')->with('info', 'Silakan masuk untuk melanjutkan. Data Anda sudah kami simpan.');
        }

        $this->authorize('create', TestDrive::class);

        $validated = $request->validated();

        // 2. Round-robin sales assignment
        $salesList = User::where('role', UserRole::Sales)->where('is_active', true)->get();
        $assignedSales = $salesList->sortBy(function (User $user) {
            return $user->assignedTestDrives()->count();
        })->first();

        // 3. Driver license image upload
        $imagePath = null;
        if ($request->hasFile('driver_license_image')) {
            $imagePath = $request->file('driver_license_image')->store('licenses', 'public');
        }

        // 4. Create Test Drive record
        /** @var User $user */
        $user = auth()->user();

        $testDrive = TestDrive::create([
            'customer_id' => $user->id,
            'car_id' => $validated['car_id'],
            'sales_id' => $assignedSales?->id,
            'preferred_date' => $validated['preferred_date'],
            'driver_license_id' => $validated['driver_license_id'],
            'driver_license_image' => $imagePath,
            'pickup_location' => $validated['pickup_location'] ?? null,
            'status' => TestDriveStatus::Requested,
            'sales_notes' => $validated['notes'] ?? null,
        ]);

        // 5. Send notification to Sales
        if ($assignedSales) {
            $assignedSales->notify(new TestDriveRequestedNotification($testDrive));
        }

        session()->forget('pending_test_drive');

        $message = 'Pengajuan test drive terkirim. Tim sales akan menghubungi Anda.';

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $testDrive,
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Tampilkan detail pengajuan test drive terotorisasi.
     */
    public function show(Request $request, TestDrive $testDrive): View|JsonResponse
    {
        $this->authorize('view', $testDrive);

        $testDrive->loadMissing(['customer', 'car', 'sales']);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $testDrive,
            ]);
        }

        return view('dashboard.test-drives.show', compact('testDrive'));
    }

    /**
     * Tampilkan riwayat pengajuan test drive milik customer yang login.
     */
    public function myTestDrives(Request $request): View
    {
        $user = $request->user();

        $testDrives = TestDrive::where('customer_id', $user->id)
            ->with(['car', 'sales'])
            ->latest('preferred_date')
            ->paginate(10);

        return view('dashboard.test-drives.my', compact('testDrives'));
    }

    /**
     * Setujui pengajuan test drive oleh Sales pemilik atau Super Admin.
     */
    public function approve(Request $request, TestDrive $testDrive): JsonResponse|RedirectResponse
    {
        $this->authorize('approve', $testDrive);

        $validated = $request->validate([
            'sales_notes' => ['nullable', 'string', 'max:500'],
        ]);

        $testDrive->update([
            'status' => TestDriveStatus::Approved,
            'approved_at' => now(),
            'sales_notes' => $validated['sales_notes'] ?? $testDrive->sales_notes,
        ]);

        $message = "Test drive untuk {$testDrive->customer->name} berhasil disetujui.";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'test_drive' => $testDrive->fresh(['car', 'customer', 'sales']),
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Tolak pengajuan test drive dengan alasan penolakan.
     */
    public function reject(Request $request, TestDrive $testDrive): JsonResponse|RedirectResponse
    {
        $this->authorize('reject', $testDrive);

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ], [
            'rejection_reason.required' => 'Alasan penolakan pengajuan test drive wajib diisi.',
        ]);

        $testDrive->update([
            'status' => TestDriveStatus::Rejected,
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        $message = "Test drive untuk {$testDrive->customer->name} telah ditolak.";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'test_drive' => $testDrive->fresh(['car', 'customer', 'sales']),
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Tandai sesi test drive telah selesai dilakukan.
     */
    public function complete(Request $request, TestDrive $testDrive): JsonResponse|RedirectResponse
    {
        $this->authorize('complete', $testDrive);

        $validated = $request->validate([
            'sales_notes' => ['nullable', 'string', 'max:500'],
        ]);

        $testDrive->update([
            'status' => TestDriveStatus::Completed,
            'completed_at' => now(),
            'sales_notes' => $validated['sales_notes'] ?? $testDrive->sales_notes,
        ]);

        $message = "Sesi test drive untuk {$testDrive->customer->name} telah berhasil diselesaikan.";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'test_drive' => $testDrive->fresh(['car', 'customer', 'sales']),
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Batalkan pengajuan test drive oleh customer pemilik, sales pemilik, atau Super Admin.
     */
    public function cancel(Request $request, TestDrive $testDrive): JsonResponse|RedirectResponse
    {
        $this->authorize('cancel', $testDrive);

        $validated = $request->validate([
            'cancellation_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $canceller = auth()->user();
        $reason = $validated['cancellation_reason']
            ?? ('Dibatalkan oleh '.($canceller && $canceller->isCustomer() ? 'pelanggan' : 'tim sales').'.');

        $testDrive->update([
            'status' => TestDriveStatus::Cancelled,
            'rejection_reason' => $reason,
        ]);

        $message = 'Pengajuan test drive berhasil dibatalkan.';

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'test_drive' => $testDrive->fresh(['car', 'customer', 'sales']),
            ]);
        }

        return back()->with('success', $message);
    }
}
