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
        if (auth()->check()) {
            $this->authorize('create', TestDrive::class);
            /** @var User $user */
            $user = auth()->user();
        } else {
            $user = User::resolveGuestCustomer(
                $request->input('customer_name'),
                $request->input('customer_phone'),
                $request->input('customer_email'),
            );
        }

        $validated = $request->validated();

        // Round-robin sales assignment
        $salesList = User::where('role', UserRole::Sales)->where('is_active', true)->get();
        $assignedSales = $salesList->sortBy(function (User $salesUser) {
            return $salesUser->assignedTestDrives()->count();
        })->first();

        $imagePath = null;
        if ($request->hasFile('driver_license_image')) {
            $imagePath = $request->file('driver_license_image')->store('licenses', 'local');
        }

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

        // Send notification to Sales
        if ($assignedSales) {
            $assignedSales->notify(new TestDriveRequestedNotification($testDrive));
        }

        session()->forget('pending_test_drive');

        $ticketNumber = (string) $testDrive->ticket_number;
        $message = "Pengajuan test drive terkirim! Nomor tiket Anda: {$ticketNumber}";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'ticket_number' => $ticketNumber,
                'message' => $message,
                'data' => $testDrive,
            ]);
        }

        return redirect()->back()
            ->with('success', $message)
            ->with('booking_ticket', $ticketNumber);
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

        if (! $testDrive->status->canTransitionTo(TestDriveStatus::Approved)) {
            abort(422, 'Transisi status ke Disetujui tidak diizinkan untuk status saat ini.');
        }

        $validated = $request->validate([
            'sales_notes' => ['nullable', 'string', 'max:500'],
        ]);

        $testDrive->update([
            'status' => TestDriveStatus::Approved,
            'approved_at' => now(),
            'sales_notes' => $validated['sales_notes'] ?? $testDrive->sales_notes,
        ]);

        $customerName = $testDrive->customer->name ?? 'Pelanggan';
        $message = "Test drive untuk {$customerName} berhasil disetujui.";

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

        if (! $testDrive->status->canTransitionTo(TestDriveStatus::Rejected)) {
            abort(422, 'Transisi status ke Ditolak tidak diizinkan untuk status saat ini.');
        }

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ], [
            'rejection_reason.required' => 'Alasan penolakan pengajuan test drive wajib diisi.',
        ]);

        $testDrive->update([
            'status' => TestDriveStatus::Rejected,
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        $customerName = $testDrive->customer->name ?? 'Pelanggan';
        $message = "Test drive untuk {$customerName} telah ditolak.";

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
     * Tandai test drive telah selesai dilaksanakan oleh Sales atau Super Admin.
     */
    public function complete(Request $request, TestDrive $testDrive): JsonResponse|RedirectResponse
    {
        $this->authorize('complete', $testDrive);

        if (! $testDrive->status->canTransitionTo(TestDriveStatus::Completed)) {
            abort(422, 'Transisi status ke Selesai tidak diizinkan untuk status saat ini.');
        }

        $validated = $request->validate([
            'sales_notes' => ['nullable', 'string', 'max:500'],
        ]);

        $testDrive->update([
            'status' => TestDriveStatus::Completed,
            'completed_at' => now(),
            'sales_notes' => $validated['sales_notes'] ?? $testDrive->sales_notes,
        ]);

        $customerName = $testDrive->customer->name ?? 'Pelanggan';
        $message = "Test drive untuk {$customerName} ditandai selesai.";

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
     * Batalkan pengajuan test drive oleh Pelanggan pemilik pengajuan atau Super Admin.
     */
    public function cancel(Request $request, TestDrive $testDrive): JsonResponse|RedirectResponse
    {
        $this->authorize('cancel', $testDrive);

        if (! $testDrive->status->canTransitionTo(TestDriveStatus::Cancelled)) {
            abort(422, 'Pengajuan test drive pada status saat ini tidak dapat dibatalkan.');
        }

        $testDrive->update([
            'status' => TestDriveStatus::Cancelled,
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

    /**
     * Akses dokumen foto SIM yang dilindungi hak akses.
     */
    public function viewLicense(TestDrive $testDrive): Response
    {
        $this->authorize('viewLicense', $testDrive);

        if (! $testDrive->driver_license_image || ! Storage::disk('local')->exists($testDrive->driver_license_image)) {
            abort(404, 'Dokumen SIM tidak ditemukan.');
        }

        return Storage::disk('local')->response($testDrive->driver_license_image);
    }
}
