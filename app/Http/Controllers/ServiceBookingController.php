<?php

namespace App\Http\Controllers;

use App\Enums\ServiceStatus;
use App\Enums\UserRole;
use App\Http\Requests\StoreServiceBookingRequest;
use App\Models\BookingSlot;
use App\Models\Service;
use App\Models\User;
use App\Notifications\ServiceBookingCreatedNotification;
use App\Services\BookingSlotValidator;
use App\Services\TicketNumberGenerator;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

class ServiceBookingController extends Controller
{
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

        $validated = $request->validated();

        $serviceDateTime = Carbon::parse($validated['service_date']);
        $slotHour = isset($validated['slot_hour']) && (int) $validated['slot_hour'] > 0
            ? (int) $validated['slot_hour']
            : (int) $serviceDateTime->hour;

        if ($slotHour < 8 || $slotHour > 17) {
            $slotHour = 9; // Jam operasional default 09:00
        }

        $slotDate = $serviceDateTime->toDateString();
        $slotKey = $this->slotValidator->getSlotKey($slotDate, $slotHour);
        $cleanPlate = strtoupper(preg_replace('/\s+/', ' ', trim($validated['vehicle_plate_number'])));
        $normalizedPlate = BookingSlotValidator::normalizePlate($cleanPlate);
        $activePlateSlot = sprintf('%s:%s', $slotKey, $normalizedPlate);

        /** @var User $user */
        $user = auth()->user();

        // 2-5. Atomic slot lock, quota validation, plate uniqueness, ticket generation, and service creation
        $service = $this->createBookingAtomically(
            user: $user,
            validated: $validated,
            serviceDateTime: $serviceDateTime,
            slotDate: $slotDate,
            slotHour: $slotHour,
            slotKey: $slotKey,
            cleanPlate: $cleanPlate,
            activePlateSlot: $activePlateSlot,
        );

        // 6. Notify Super Admins outside the transaction
        $superAdmins = User::where('role', UserRole::SuperAdmin)->where('is_active', true)->get();
        if ($superAdmins->isNotEmpty()) {
            Notification::send($superAdmins, new ServiceBookingCreatedNotification($service));
        }

        session()->forget('pending_service_booking');

        $message = "Booking servis berhasil! Nomor tiket: {$service->ticket_number}";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $service,
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Eksekusi transaksi atomik booking servis dengan row-lock pada slot dan bounded retry.
     *
     * @param  array<string, mixed>  $validated
     */
    private function createBookingAtomically(
        User $user,
        array $validated,
        Carbon $serviceDateTime,
        string $slotDate,
        int $slotHour,
        string $slotKey,
        string $cleanPlate,
        string $activePlateSlot,
        int $maxRetries = 5,
    ): Service {
        $attempt = 0;

        while ($attempt < $maxRetries) {
            try {
                return DB::transaction(function () use (
                    $user,
                    $validated,
                    $serviceDateTime,
                    $slotDate,
                    $slotHour,
                    $slotKey,
                    $cleanPlate,
                    $activePlateSlot,
                ) {
                    // 1. Mutex row-lock slot di booking_slots
                    $slot = BookingSlot::firstOrCreate(
                        ['slot_key' => $slotKey],
                        ['slot_date' => $slotDate, 'slot_hour' => $slotHour]
                    );
                    BookingSlot::where('id', $slot->id)->lockForUpdate()->first();

                    // 2. Validasi kuota slot (maks 3 kendaraan per slot jam)
                    if (! $this->slotValidator->isSlotAvailable($slotDate, $slotHour)) {
                        throw ValidationException::withMessages([
                            'service_date' => 'Kuota servis untuk slot jam '.sprintf('%02d:00', $slotHour).' sudah penuh (maksimal 3 kendaraan). Silakan pilih jam lain.',
                        ]);
                    }

                    // 3. Validasi anti double-booking plat kendaraan per slot jam
                    if (! $this->slotValidator->isPlateAvailable($slotDate, $slotHour, $cleanPlate)) {
                        throw ValidationException::withMessages([
                            'vehicle_plate_number' => 'Kendaraan dengan nomor plat ini sudah terdaftar servis pada jam yang sama.',
                        ]);
                    }

                    // 4. Generate tiket unik berurutan di dalam transaksi yang sama
                    $ticketNumber = $this->ticketGenerator->generate();

                    // 5. Buat data booking servis
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
                        'slot_date' => $slotDate,
                        'slot_key' => $slotKey,
                        'active_plate_slot' => $activePlateSlot,
                        'complaint_notes' => $validated['complaint_notes'],
                        'status' => ServiceStatus::Pending,
                        'total_cost' => 0,
                    ]);
                });
            } catch (ValidationException $e) {
                // Jangan retry validasi bisnis
                throw $e;
            } catch (QueryException $e) {
                // Tangani tabrakan constraint unik active_plate_slot secara ramah
                $message = $e->getMessage();
                $isUniqueViolation = $e->getCode() === '23000' ||
                    (isset($e->errorInfo[1]) && $e->errorInfo[1] === 1062) ||
                    str_contains($message, 'UNIQUE constraint failed') ||
                    str_contains($message, 'Duplicate entry');

                if ($isUniqueViolation && (
                    str_contains($message, 'uq_services_active_plate_slot') ||
                    str_contains($message, 'active_plate_slot') ||
                    str_contains($message, 'uq_services_slot') ||
                    str_contains($message, 'vehicle_plate_number')
                )) {
                    throw ValidationException::withMessages([
                        'vehicle_plate_number' => 'Kendaraan dengan nomor plat ini sudah terdaftar servis pada jam yang sama.',
                    ]);
                }

                // Untuk deadlock atau tabrakan nomor tiket, lakukan retry terbatas
                $attempt++;
                if ($attempt >= $maxRetries) {
                    throw $e;
                }
                usleep(random_int(10000, 50000));
            } catch (\Throwable $e) {
                $attempt++;
                if ($attempt >= $maxRetries) {
                    throw $e;
                }
                usleep(random_int(10000, 50000));
            }
        }

        throw new \RuntimeException('Gagal memproses booking servis setelah '.$maxRetries.' percobaan.');
    }
}
