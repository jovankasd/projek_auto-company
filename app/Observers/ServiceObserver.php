<?php

namespace App\Observers;

use App\Enums\ServiceStatus;
use App\Events\ServiceStatusChanged;
use App\Models\Service;
use App\Models\ServiceStatusLog;

class ServiceObserver
{
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

            event(new ServiceStatusChanged($service, $fromStatus, $toStatus, auth()->user()));
        }
    }
}
