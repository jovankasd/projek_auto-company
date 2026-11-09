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
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

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

        $validated = $request->validated();

        // 2. Round-robin sales assignment
        $salesList = User::where('role', UserRole::Sales)->where('is_active', true)->get();
        $assignedSales = $salesList->sortBy(function (User $user) {
            return $user->assignedTestDrives()->count();
        })->first();

        // 3. Driver license image upload (tersimpan di disk privat 'local')
        $imagePath = null;
        if ($request->hasFile('driver_license_image')) {
            $imagePath = $request->file('driver_license_image')->store('licenses', 'local');
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
     * Setujui pengajuan test drive oleh Sales atau Super Admin.
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
     * Tampilkan berkas foto SIM secara aman dari disk privat untuk pihak yang berwenang.
     */
    public function showLicenseImage(TestDrive $testDrive): Response
    {
        $this->authorize('viewDriverLicense', $testDrive);

        if (! $testDrive->driver_license_image) {
            abort(404, 'Foto SIM tidak ditemukan untuk pengajuan test drive ini.');
        }

        $path = $testDrive->driver_license_image;
        $normalized = ltrim(str_replace('\\', '/', $path), '/');

        // Normalisasikan prefix legacy 'public/' bila tersimpan di database
        if (str_starts_with($normalized, 'public/')) {
            $normalized = substr($normalized, 7);
        }

        // Cegah path traversal dan batasi direktori yang sah
        if (str_contains($normalized, '..') || ! str_starts_with($normalized, 'licenses/')) {
            abort(404, 'Berkas foto SIM tidak valid.');
        }

        $headers = [
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'DENY',
            'Cache-Control' => 'private, no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ];

        if (Storage::disk('local')->exists($normalized)) {
            return Storage::disk('local')->response($normalized, basename($normalized), $headers);
        }

        // Fallback untuk berkas lama di storage publik sebelum prosedur migrasi dijalankan
        if (Storage::disk('public')->exists($normalized)) {
            return Storage::disk('public')->response($normalized, basename($normalized), $headers);
        }

        abort(404, 'Berkas foto SIM tidak ditemukan di penyimpanan.');
    }
}
