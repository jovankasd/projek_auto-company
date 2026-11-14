<?php

namespace App\Http\Controllers;

use App\Enums\ServiceItemType;
use App\Enums\ServiceStatus;
use App\Enums\UserRole;
use App\Models\Promo;
use App\Models\Service;
use App\Models\ServiceItem;
use App\Models\ServiceStatusLog;
use App\Models\User;
use App\Services\ServiceCostCalculator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ServiceController extends Controller
{
    use AuthorizesRequests;

    /**
     * Tampilkan riwayat dan status servis milik customer yang sedang login.
     */
    public function myServices(Request $request): View
    {
        $user = $request->user();

        $services = Service::where('customer_id', $user->id)
            ->with(['items', 'statusLogs.changedBy', 'mechanic', 'promo'])
            ->latest('service_date')
            ->paginate(10);

        $activeService = Service::where('customer_id', $user->id)
            ->active()
            ->with(['items', 'statusLogs.changedBy', 'mechanic', 'promo'])
            ->latest('service_date')
            ->first();

        return view('dashboard.services.my', compact('services', 'activeService'));
    }

    /**
     * Detail Work Order servis.
     */
    public function show(Service $service): View
    {
        $this->authorize('view', $service);

        $service->load(['customer', 'mechanic', 'promo', 'items.addedBy', 'statusLogs.changedBy']);

        return view('dashboard.services.show', compact('service'));
    }

    /**
     * Perbarui status pengerjaan servis (State Machine PRD §5.1).
     */
    public function updateStatus(Request $request, Service $service): JsonResponse|RedirectResponse
    {
        $this->authorize('updateStatus', $service);

        $validated = $request->validate([
            'status' => ['required', Rule::enum(ServiceStatus::class)],
            'notes' => ['nullable', 'string', 'max:1000'],
            'mechanic_notes' => ['nullable', 'string', 'max:1000'],
            'recommendation' => ['nullable', 'string', 'max:1000'],
            'cancellation_reason' => [
                'nullable',
                'string',
                'max:500',
                Rule::requiredIf(fn () => $request->input('status') === ServiceStatus::Cancelled->value),
            ],
        ], [
            'status.required' => 'Status servis wajib dipilih.',
            'cancellation_reason.required' => 'Alasan pembatalan wajib diisi jika status dibatalkan.',
        ]);

        $nextStatus = ServiceStatus::from($validated['status']);
        $currentStatus = $service->status instanceof ServiceStatus ? $service->status : ServiceStatus::from((string) $service->status);

        // 1. Guard: Transisi yang diizinkan sesuai state machine
        if (! $currentStatus->canTransitionTo($nextStatus)) {
            $message = "Transisi dari status {$currentStatus->label()} ke {$nextStatus->label()} tidak diizinkan.";
            if ($request->wantsJson()) {
                return response()->json(['message' => $message], 422);
            }

            return back()->with('error', $message);
        }

        // 2. Guard: Tidak boleh selesai tanpa ada service_items
        if ($nextStatus === ServiceStatus::Completed && $service->items()->count() === 0) {
            $message = 'Tidak dapat menyelesaikan servis tanpa rincian item onderdil atau jasa pengerjaan.';
            if ($request->wantsJson()) {
                return response()->json(['message' => $message], 422);
            }

            return back()->with('error', $message);
        }

        // 3. Set timestamp, metadata, dan update status dalam transaksi atomik
        DB::transaction(function () use ($service, $nextStatus, $currentStatus, $validated) {
            if ($nextStatus === ServiceStatus::Confirmed) {
                $service->confirmed_at = now();
            } elseif ($nextStatus === ServiceStatus::InProgress) {
                $service->started_at = now();
            } elseif ($nextStatus === ServiceStatus::Completed) {
                $service->completed_at = now();
            } elseif ($nextStatus === ServiceStatus::Cancelled) {
                $service->cancelled_at = now();
                $service->cancellation_reason = $validated['cancellation_reason'] ?? null;

                // Restitusi kuota voucher jika servis dibatalkan (atomic pessimistic lock)
                if ($currentStatus !== ServiceStatus::Cancelled && $service->promo_id) {
                    $promo = Promo::where('id', $service->promo_id)->lockForUpdate()->first();
                    if ($promo && $promo->used_count > 0) {
                        $promo->decrement('used_count');
                    }
                }
            }

            if (! empty($validated['mechanic_notes'])) {
                $service->mechanic_notes = $validated['mechanic_notes'];
            }

            if (! empty($validated['recommendation'])) {
                $service->recommendation = $validated['recommendation'];
            }

            $service->status_change_notes = $validated['notes'] ?? "Status diubah menjadi {$nextStatus->label()} oleh ".auth()->user()->name;
            $service->status = $nextStatus;
            $service->save();
        });

        $message = "Status servis berhasil diperbarui menjadi {$nextStatus->label()}.";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'service' => $service->fresh(['items', 'statusLogs.changedBy']),
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Tambah item suku cadang / jasa pada Work Order servis.
     */
    public function addItem(Request $request, Service $service, ServiceCostCalculator $calculator): JsonResponse|RedirectResponse
    {
        $this->authorize('addItem', $service);

        if (in_array($service->status, [ServiceStatus::Completed, ServiceStatus::Cancelled], true)) {
            $message = 'Tidak dapat menambah item pada servis yang sudah selesai atau dibatalkan.';
            if ($request->wantsJson()) {
                return response()->json(['message' => $message], 422);
            }

            return back()->with('error', $message);
        }

        $validated = $request->validate([
            'item_type' => ['required', Rule::enum(ServiceItemType::class)],
            'name' => ['required', 'string', 'max:255'],
            'part_number' => ['nullable', 'string', 'max:100'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'unit_price' => ['required', 'numeric', 'min:0'],
        ], [
            'name.required' => 'Nama item atau pekerjaan wajib diisi.',
            'quantity.required' => 'Jumlah kuantitas wajib diisi.',
            'unit_price.required' => 'Harga satuan wajib diisi.',
        ]);

        $subtotal = $calculator->calculateSubtotal((float) $validated['quantity'], (float) $validated['unit_price']);

        $item = $service->items()->create([
            'item_type' => $validated['item_type'],
            'name' => $validated['name'],
            'part_number' => $validated['part_number'] ?? null,
            'quantity' => $validated['quantity'],
            'unit_price' => $validated['unit_price'],
            'subtotal' => $subtotal,
            'added_by' => auth()->id(),
        ]);

        $calculator->recalculate($service);

        $message = 'Item berhasil ditambahkan ke work order.';

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'item' => $item,
                'total_cost' => $service->fresh()->total_cost,
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Hapus item dari work order servis.
     */
    public function removeItem(Service $service, ServiceItem $item, ServiceCostCalculator $calculator): JsonResponse|RedirectResponse
    {
        $this->authorize('removeItem', $service);

        if (in_array($service->status, [ServiceStatus::Completed, ServiceStatus::Cancelled], true)) {
            $message = 'Tidak dapat menghapus item pada servis yang sudah selesai atau dibatalkan.';
            if (request()->wantsJson()) {
                return response()->json(['message' => $message], 422);
            }

            return back()->with('error', $message);
        }

        if ($item->service_id !== $service->id) {
            abort(404, 'Item tidak ditemukan pada servis ini.');
        }

        $item->delete();
        $calculator->recalculate($service);

        $message = 'Item berhasil dihapus dari work order.';

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'total_cost' => $service->fresh()->total_cost,
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Tugaskan mekanik ke servis oleh Super Admin.
     */
    public function assignMechanic(Request $request, Service $service): JsonResponse|RedirectResponse
    {
        $this->authorize('assignMechanic', $service);

        $validated = $request->validate([
            'assigned_mechanic_id' => [
                'required',
                'exists:users,id',
                function ($attribute, $value, $fail) {
                    $mechanic = User::find($value);
                    if (! $mechanic || $mechanic->role !== UserRole::Mekanik) {
                        $fail('Pengguna yang dipilih bukan mekanik aktif.');
                    }
                },
            ],
        ], [
            'assigned_mechanic_id.required' => 'Pilih mekanik yang akan ditugaskan.',
        ]);

        $mechanic = User::findOrFail($validated['assigned_mechanic_id']);
        $service->assigned_mechanic_id = $mechanic->id;

        // Jika status masih pending, transisikan ke confirmed
        if ($service->status === ServiceStatus::Pending) {
            $service->status_change_notes = "Mekanik {$mechanic->name} ditugaskan oleh Super Admin.";
            $service->status = ServiceStatus::Confirmed;
            $service->confirmed_at = now();
        }

        $service->save();

        $message = "Mekanik {$mechanic->name} berhasil ditugaskan untuk tiket {$service->ticket_number}.";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'service' => $service->fresh(['mechanic', 'statusLogs.changedBy']),
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Pelacakan status real-time untuk customer dengan visual timeline & polling.
     */
    public function track(Service $service): View
    {
        $this->authorize('view', $service);

        $service->load(['customer', 'mechanic', 'items.addedBy', 'statusLogs.changedBy']);

        $statusLogsData = $service->statusLogs->map(function (ServiceStatusLog $log): array {
            $changedByName = $log->changedBy ? $log->changedBy->name : 'Sistem';
            $createdAt = Carbon::parse($log->created_at);

            return [
                'id' => $log->id,
                'to_status' => $log->to_status,
                'to_status_label' => $log->to_status ? (ServiceStatus::tryFrom($log->to_status)?->label() ?? $log->to_status) : 'Dibuat',
                'notes' => $log->notes,
                'changed_by' => $changedByName,
                'created_at_human' => $createdAt->translatedFormat('d M Y, H:i').' WIB',
                'time_ago' => $createdAt->diffForHumans(),
            ];
        })->values()->all();

        return view('dashboard.services.track', compact('service', 'statusLogsData'));
    }

    /**
     * Endpoint polling data status servis (JSON 30 detik untuk Alpine.js).
     */
    public function statusStream(Service $service): JsonResponse
    {
        $this->authorize('view', $service);

        $service->loadMissing(['mechanic', 'statusLogs.changedBy']);

        $mechanicName = $service->mechanic ? $service->mechanic->name : 'Belum Ditugaskan';

        return response()->json([
            'id' => $service->id,
            'ticket_number' => $service->ticket_number,
            'status' => $service->status->value,
            'status_label' => $service->status->label(),
            'status_color' => $service->status->color(),
            'progress_percent' => $service->progressPercent(),
            'total_cost' => (float) $service->total_cost,
            'total_cost_formatted' => 'Rp'.number_format((float) $service->total_cost, 0, ',', '.'),
            'mechanic_name' => $mechanicName,
            'mechanic_notes' => $service->mechanic_notes,
            'recommendation' => $service->recommendation,
            'updated_at' => $service->updated_at?->toIso8601String() ?? now()->toIso8601String(),
            'logs' => $service->statusLogs->map(function (ServiceStatusLog $log): array {
                $changedByName = $log->changedBy ? $log->changedBy->name : 'Sistem';
                $createdAt = Carbon::parse($log->created_at);

                return [
                    'id' => $log->id,
                    'to_status' => $log->to_status,
                    'to_status_label' => $log->to_status ? (ServiceStatus::tryFrom($log->to_status)?->label() ?? $log->to_status) : 'Dibuat',
                    'notes' => $log->notes,
                    'changed_by' => $changedByName,
                    'created_at_human' => $createdAt->translatedFormat('d M Y, H:i').' WIB',
                    'time_ago' => $createdAt->diffForHumans(),
                ];
            }),
        ]);
    }
}
