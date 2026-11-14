<?php

namespace App\Observers;

use App\Enums\ServiceStatus;
use App\Events\ServiceStatusChanged;
use App\Models\Service;
use App\Models\ServiceStatusLog;
use App\Services\BookingSlotValidator;
use App\Services\ServiceCostCalculator;
use Illuminate\Support\Carbon;

class ServiceObserver
{
    public function __construct(
        private ServiceCostCalculator $costCalculator,
    ) {}

    public function saving(Service $service): void
    {
        if ($service->service_date) {
            $parsedDate = Carbon::parse($service->service_date);
            $date = $parsedDate->toDateString();
            $hour = (int) ($service->slot_hour ?: $parsedDate->hour);
            if ($hour < 8 || $hour > 17) {
                $hour = 9;
            }
            $service->slot_hour = $hour;
            $service->slot_date = $parsedDate->copy()->startOfDay();
            $service->slot_key = sprintf('%s:%02d', $date, $hour);

            $normalizedPlate = BookingSlotValidator::normalizePlate((string) $service->vehicle_plate_number);
            $isCancelled = in_array(
                $service->status instanceof ServiceStatus ? $service->status->value : (string) $service->status,
                ['cancelled', ServiceStatus::Cancelled->value],
                true
            );

            $service->active_plate_slot = $isCancelled ? null : sprintf('%s:%s', $service->slot_key, $normalizedPlate);
        }
    }

    public function created(Service $service): void
    {
        $status = $service->status instanceof ServiceStatus
            ? $service->status
            : ServiceStatus::tryFrom((string) $service->status) ?? ServiceStatus::Pending;

        ServiceStatusLog::create([
            'service_id' => $service->id,
            'from_status' => null,
            'to_status' => $status->value,
            'changed_by' => auth()->id(),
            'notes' => 'Booking servis baru dibuat.',
        ]);

        event(new ServiceStatusChanged($service, null, $status, auth()->user()));
    }

    public function updating(Service $service): void
    {
        if ($service->isDirty('status')) {
            $fromStatus = $service->getOriginal('status');
            $toStatus = $service->status instanceof \BackedEnum ? $service->status->value : (string) $service->status;

            ServiceStatusLog::create([
                'service_id' => $service->id,
                'from_status' => $fromStatus instanceof \BackedEnum ? $fromStatus->value : $fromStatus,
                'to_status' => $toStatus,
                'changed_by' => auth()->id(),
                'notes' => $service->status_change_notes ?? null,
            ]);
        }
    }

    public function updated(Service $service): void
    {
        if ($service->wasChanged('status')) {
            $rawFrom = $service->getOriginal('status');
            $fromStatus = $rawFrom instanceof ServiceStatus
                ? $rawFrom
                : ServiceStatus::tryFrom((string) $rawFrom);

            $toStatus = $service->status instanceof ServiceStatus
                ? $service->status
                : ServiceStatus::tryFrom((string) $service->status) ?? ServiceStatus::Pending;

            $this->costCalculator->recalculate($service->fresh());

            event(new ServiceStatusChanged($service, $fromStatus, $toStatus, auth()->user()));
        }
    }

    public function deleted(Service $service): void
    {
        if ($service->active_plate_slot !== null) {
            $service->active_plate_slot = null;
            $service->saveQuietly();
        }
    }

    public function restored(Service $service): void
    {
        if ($service->service_date) {
            $parsedDate = Carbon::parse($service->service_date);
            $date = $parsedDate->toDateString();
            $hour = (int) ($service->slot_hour ?: $parsedDate->hour);
            if ($hour < 8 || $hour > 17) {
                $hour = 9;
            }
            $service->slot_hour = $hour;
            $service->slot_date = $parsedDate->copy()->startOfDay();
            $slotKey = sprintf('%s:%02d', $date, $hour);
            $service->slot_key = $slotKey;
            $normalizedPlate = BookingSlotValidator::normalizePlate((string) $service->vehicle_plate_number);

            $isCancelled = in_array(
                $service->status instanceof ServiceStatus ? $service->status->value : (string) $service->status,
                ['cancelled', ServiceStatus::Cancelled->value],
                true
            );

            $service->active_plate_slot = $isCancelled ? null : sprintf('%s:%s', $slotKey, $normalizedPlate);
            $service->saveQuietly();
        }
    }
}
