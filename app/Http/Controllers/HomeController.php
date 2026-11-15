<?php

namespace App\Http\Controllers;

use App\Enums\ServiceStatus;
use App\Enums\TestDriveStatus;
use App\Models\Car;
use App\Models\Promo;
use App\Models\Service;
use App\Models\ServiceItem;
use App\Models\ServiceStatusLog;
use App\Models\SparePart;
use App\Models\TestDrive;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $cars = Cache::remember('showroom_cars', 300, function () {
            return Car::available()
                ->orderBy('sort_order')
                ->with('promos')
                ->get();
        });

        $spareParts = SparePart::available()
            ->ordered()
            ->get();

        $promos = Promo::active()
            ->with('car')
            ->latest('starts_at')
            ->get();

        $pendingTestDrive = session('pending_test_drive');
        $pendingServiceBooking = session('pending_service_booking');

        return view('showroom.index', compact(
            'cars',
            'spareParts',
            'promos',
            'pendingTestDrive',
            'pendingServiceBooking'
        ));
    }

    public function privacyPolicy(): View
    {
        return view('pages.privacy-policy');
    }

    public function termsOfService(): View
    {
        return view('pages.terms-of-service');
    }

    public function showCar(Car $car): View
    {
        if (! $car->is_available) {
            abort(404);
        }

        $car->loadMissing([
            'promos' => fn ($q) => $q->active(),
        ]);

        $relatedCars = Car::available()
            ->where('id', '!=', $car->id)
            ->ordered()
            ->take(3)
            ->get();

        $pendingTestDrive = session('pending_test_drive');
        $pendingServiceBooking = session('pending_service_booking');

        return view('cars.show', compact('car', 'relatedCars', 'pendingTestDrive', 'pendingServiceBooking'));
    }

    public function contact(): View
    {
        return view('contact.index');
    }

    public function storeContact(Request $request): JsonResponse|RedirectResponse
    {
        // Anti-bot honeypot check
        if (! empty($request->input('website_url'))) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pesan Anda berhasil dikirim. Tim kami akan segera menghubungi Anda.',
                ]);
            }

            return back()->with('success', 'Pesan Anda berhasil dikirim. Tim kami akan segera menghubungi Anda.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150'],
            'phone' => ['required', 'string', 'min:9', 'max:30'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'min:10', 'max:2000'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'phone.required' => 'Nomor telepon/WhatsApp wajib diisi.',
            'phone.min' => 'Nomor telepon minimal 9 digit.',
            'subject.required' => 'Subjek/topik konsultasi wajib dipilih.',
            'message.required' => 'Pesan atau kebutuhan wajib diisi.',
            'message.min' => 'Pesan minimal berisi 10 karakter.',
        ]);

        Log::info('Inquiry kontak pelanggan diterima', [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'subject' => $validated['subject'],
        ]);

        $message = 'Pesan Anda berhasil dikirim. Tim konsultan Dealer Pro akan segera menghubungi Anda.';

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }

    public function trackTicket(Request $request): JsonResponse
    {
        $ticket = strtoupper(trim((string) $request->query('ticket', '')));

        if ($ticket === '') {
            return response()->json([
                'found' => false,
                'message' => 'Masukkan nomor tiket terlebih dahulu (contoh: SRV-20261115-0001 atau TD-20261115-0001).',
            ], 422);
        }

        $service = Service::where('ticket_number', $ticket)
            ->with(['customer', 'mechanic', 'items', 'statusLogs'])
            ->first();

        if ($service) {
            return response()->json([
                'found' => true,
                'type' => 'service',
                'ticket_number' => $service->ticket_number,
                'customer_name' => $service->customer->name ?? 'Pelanggan Showroom',
                'vehicle_model' => $service->vehicle_model.' ('.$service->vehicle_year.')',
                'vehicle_plate_number' => $service->vehicle_plate_number,
                'service_type_label' => match ($service->service_type) {
                    'regular' => 'Servis Berkala / Tune-Up',
                    'major' => 'Servis Besar (Major)',
                    'inspection' => 'Pemeriksaan / Inspeksi',
                    'warranty' => 'Garansi & Pemasangan',
                    default => ucfirst($service->service_type),
                },
                'service_date_formatted' => $service->service_date->translatedFormat('d M Y').' • '.sprintf('%02d:00 WIB', (int) $service->slot_hour),
                'status' => $service->status->value,
                'status_label' => $service->status->label(),
                'progress_percent' => $service->progressPercent(),
                'mechanic_name' => $service->mechanic->name ?? 'Menunggu Penugasan Mekanik',
                'complaint_notes' => $service->complaint_notes,
                'mechanic_notes' => $service->mechanic_notes,
                'recommendation' => $service->recommendation,
                'cancellation_reason' => $service->cancellation_reason,
                'total_cost_formatted' => 'Rp '.number_format((float) $service->total_cost, 0, ',', '.'),
                'items' => $service->items->toBase()->map(fn (ServiceItem $item): array => [
                    'name' => $item->name,
                    'type' => $item->item_type instanceof \BackedEnum ? $item->item_type->value : (string) $item->item_type,
                    'quantity' => $item->quantity,
                    'subtotal_formatted' => 'Rp '.number_format((float) $item->subtotal, 0, ',', '.'),
                ])->values(),
                'logs' => $service->statusLogs->toBase()->map(fn (ServiceStatusLog $log): array => [
                    'to_status_label' => ServiceStatus::tryFrom((string) $log->to_status)?->label() ?? (string) $log->to_status,
                    'notes' => $log->notes,
                    'created_at' => $log->created_at?->format('d M Y, H:i'),
                ])->values(),
            ]);
        }

        $testDrive = TestDrive::where('ticket_number', $ticket)
            ->with(['customer', 'car', 'sales'])
            ->first();

        if ($testDrive) {
            return response()->json([
                'found' => true,
                'type' => 'test_drive',
                'ticket_number' => $testDrive->ticket_number,
                'customer_name' => $testDrive->customer->name ?? 'Pelanggan Showroom',
                'car_name' => $testDrive->car->brand.' '.$testDrive->car->model_name.' ('.$testDrive->car->year.')',
                'preferred_date_formatted' => $testDrive->preferred_date->translatedFormat('d M Y • H:i').' WIB',
                'pickup_location' => $testDrive->pickup_location ?: 'Showroom Pusat',
                'status' => $testDrive->status->value,
                'status_label' => $testDrive->status->label(),
                'progress_percent' => match ($testDrive->status) {
                    TestDriveStatus::Requested => 35,
                    TestDriveStatus::Approved => 75,
                    TestDriveStatus::Completed => 100,
                    TestDriveStatus::Rejected, TestDriveStatus::Cancelled => 0,
                },
                'sales_name' => $testDrive->sales->name ?? 'Menunggu Konfirmasi Konsultan Sales',
                'sales_phone' => $testDrive->sales?->phone_number,
                'sales_notes' => $testDrive->sales_notes,
                'rejection_reason' => $testDrive->rejection_reason,
            ]);
        }

        return response()->json([
            'found' => false,
            'message' => 'Nomor tiket tidak ditemukan. Pastikan format nomor tiket benar (contoh: SRV-20261115-0001 atau TD-20261115-0001).',
        ], 404);
    }
}
