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
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ServiceBookingController extends Controller
{
    public function __construct(
        private BookingSlotValidator $slotValidator,
        private TicketNumberGenerator $ticketGenerator,
    ) {}

    /**
     * Tampilkan formulir mandiri untuk booking servis (progressive enhancement non-JS).
     */
    public function create(Request $request): View
    {
        $selectedModel = $request->query('vehicle_model');
        $complaintNotes = $request->query('complaint_notes');
        /** @var array<string, mixed> $pendingServiceBooking */
        $pendingServiceBooking = session('pending_service_booking', []);

        return view('showroom.service-booking', compact('selectedModel', 'complaintNotes', 'pendingServiceBooking'));
    }

    public function store(StoreServiceBookingRequest $request): JsonResponse|RedirectResponse
    {
        // 1. Session Retention & Guest redirect
        if (! auth()->check()) {
            session()->put('pending_service_booking', $request->validated());

            $message = 'Silakan masuk atau buat akun baru untuk melanjutkan. Data booking Anda sudah kami simpan.';

            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'unauthenticated',
                    'redirect' => route('login'),
                    'message' => $message,
                ], 401);
            }

            return redirect()->route('login')->with('info', $message);
        }

        $validated = $request->validated();

        $serviceDateTime = Carbon::parse($validated['service_date']);
        $slotHour = isset($validated['slot_hour']) && (int) $validated['slot_hour'] > 0
            ? (int) $validated['slot_hour']
            : (int) $serviceDateTime->hour;

        if ($slotHour < 8 || $slotHour > 17) {
            throw ValidationException::withMessages([
                'service_date' => 'Jam kedatangan servis harus berada dalam jam operasional bengkel (08:00 - 17:00 WIB).',
            ]);
        }

        $serviceDateTime = $serviceDateTime->copy()->setHour($slotHour)->setMinute(0)->setSecond(0);

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
