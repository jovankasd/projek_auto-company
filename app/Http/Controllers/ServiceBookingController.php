<?php

namespace App\Http\Controllers;

use App\Enums\ServiceStatus;
use App\Enums\UserRole;
use App\Http\Requests\StoreServiceBookingRequest;
use App\Models\Service;
use App\Models\User;
use App\Notifications\ServiceBookingCreatedNotification;
use App\Services\BookingSlotValidator;
use App\Services\TicketNumberGenerator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

class ServiceBookingController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private BookingSlotValidator $slotValidator,
        private TicketNumberGenerator $ticketGenerator,
    ) {}

    public function store(StoreServiceBookingRequest $request): JsonResponse|RedirectResponse
    {
        // 1. Session Retention & Guest redirect
        if (! auth()->check()) {
            session()->put('pending_service_booking', $request->validated());

            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'unauthenticated',
                    'redirect' => route('login'),
                    'message' => 'Silakan masuk untuk melanjutkan. Data Anda sudah kami simpan.',
                ], 401);
            }

            return redirect()->route('login')->with('info', 'Silakan masuk untuk melanjutkan. Data Anda sudah kami simpan.');
        }

        $this->authorize('create', Service::class);

        $validated = $request->validated();

        $serviceDateTime = Carbon::parse($validated['service_date']);
        $slotHour = isset($validated['slot_hour']) && (int) $validated['slot_hour'] > 0
            ? (int) $validated['slot_hour']
            : (int) $serviceDateTime->hour;

        if ($slotHour < 8 || $slotHour > 17) {
            $slotHour = 9; // Jam operasional default 09:00
        }

        $cleanPlate = strtoupper(preg_replace('/\s+/', ' ', trim($validated['vehicle_plate_number'])));

        // 2. Booking slot quota validation (maks 3 / jam)
        if (! $this->slotValidator->isSlotAvailable($serviceDateTime->toDateString(), $slotHour)) {
            throw ValidationException::withMessages([
                'service_date' => 'Kuota servis untuk slot jam '.sprintf('%02d:00', $slotHour).' sudah penuh (maksimal 3 kendaraan). Silakan pilih jam lain.',
            ]);
        }

        // 3. Anti double-booking validation (plat + jam sama)
        if (! $this->slotValidator->isPlateAvailable($serviceDateTime->toDateString(), $slotHour, $cleanPlate)) {
            throw ValidationException::withMessages([
                'vehicle_plate_number' => 'Kendaraan dengan nomor plat ini sudah terdaftar servis pada jam yang sama.',
            ]);
        }

        // 4. Generate unique ticket number with transaction
        $ticketNumber = $this->ticketGenerator->generate();

        /** @var User $user */
        $user = auth()->user();

        // 5. Create service in transaction
        $service = DB::transaction(function () use ($user, $ticketNumber, $validated, $cleanPlate, $serviceDateTime, $slotHour) {
            return Service::create([
                'ticket_number' => $ticketNumber,
                'customer_id' => $user->id,
                'vehicle_plate_number' => $cleanPlate,
                'vehicle_model' => $validated['vehicle_model'],
                'vehicle_year' => $validated['vehicle_year'],
                'odometer_km' => $validated['odometer_km'],
                'service_type' => $validated['service_type'],
                'service_date' => $serviceDateTime,
                'slot_hour' => $slotHour,
                'complaint_notes' => $validated['complaint_notes'],
                'status' => ServiceStatus::Pending,
                'total_cost' => 0,
            ]);
        });

        // 6. Notify Super Admins
        $superAdmins = User::where('role', UserRole::SuperAdmin)->where('is_active', true)->get();
        if ($superAdmins->isNotEmpty()) {
            Notification::send($superAdmins, new ServiceBookingCreatedNotification($service));
        }

        session()->forget('pending_service_booking');

        $message = "Booking servis berhasil! Nomor tiket: {$ticketNumber}";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $service,
            ]);
        }

        return redirect()->back()->with('success', $message);
    }
}
