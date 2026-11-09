<?php

namespace App\Listeners;

use App\Enums\ServiceStatus;
use App\Enums\UserRole;
use App\Events\ServiceStatusChanged;
use App\Models\User;
use App\Notifications\ServiceCancelled;
use App\Notifications\ServiceCompleted;
use App\Notifications\ServiceConfirmed;
use App\Notifications\ServiceCreated;
use App\Notifications\ServiceStarted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Throwable;

class SendServiceStatusNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;

    public int $timeout = 30;

    public function handle(ServiceStatusChanged $event): void
    {
        $service = $event->service->loadMissing(['customer', 'mechanic']);
        $toStatus = $event->toStatus;
        $customer = $service->customer;

        try {
            switch ($toStatus) {
                case ServiceStatus::Pending:
                    if ($customer) {
                        $customer->notify(new ServiceCreated($service));
                    }
                    // Beritahu juga Super Admin mengenai booking baru
                    $superAdmins = User::where('role', UserRole::SuperAdmin)->where('is_active', true)->get();
                    if ($superAdmins->isNotEmpty()) {
                        Notification::send($superAdmins, new ServiceCreated($service));
                    }
                    break;

                case ServiceStatus::Confirmed:
                    if ($customer) {
                        $customer->notify(new ServiceConfirmed($service));
                    }
                    if ($service->mechanic) {
                        $service->mechanic->notify(new ServiceConfirmed($service));
                    }
                    break;

                case ServiceStatus::InProgress:
                    if ($customer) {
                        $customer->notify(new ServiceStarted($service));
                    }
                    break;

                case ServiceStatus::Completed:
                    if ($customer) {
                        $customer->notify(new ServiceCompleted($service));
                    }
                    break;

                case ServiceStatus::Cancelled:
                    if ($customer) {
                        $customer->notify(new ServiceCancelled($service));
                    }
                    if ($service->mechanic) {
                        $service->mechanic->notify(new ServiceCancelled($service));
                    }
                    break;
            }
        } catch (Throwable $e) {
            Log::error('[SendServiceStatusNotification] Gagal mengirim notifikasi status servis: '.$e->getMessage(), [
                'service_id' => $service->id,
                'status' => $toStatus->value,
            ]);
        }
    }
}
