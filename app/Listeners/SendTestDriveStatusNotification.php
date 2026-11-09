<?php

namespace App\Listeners;

use App\Enums\TestDriveStatus;
use App\Events\TestDriveStatusChanged;
use App\Notifications\TestDriveApproved;
use App\Notifications\TestDriveCompleted;
use App\Notifications\TestDriveRejected;
use App\Notifications\TestDriveRequested;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendTestDriveStatusNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;

    public int $timeout = 30;

    public function handle(TestDriveStatusChanged $event): void
    {
        $testDrive = $event->testDrive->loadMissing(['customer', 'car', 'sales']);
        $toStatus = $event->toStatus;
        $customer = $testDrive->customer;
        $sales = $testDrive->sales;

        try {
            switch ($toStatus) {
                case TestDriveStatus::Requested:
                    if ($sales) {
                        $sales->notify(new TestDriveRequested($testDrive));
                    }
                    if ($customer) {
                        $customer->notify(new TestDriveRequested($testDrive));
                    }
                    break;

                case TestDriveStatus::Approved:
                    if ($customer) {
                        $customer->notify(new TestDriveApproved($testDrive));
                    }
                    break;

                case TestDriveStatus::Rejected:
                    if ($customer) {
                        $customer->notify(new TestDriveRejected($testDrive));
                    }
                    break;

                case TestDriveStatus::Completed:
                    if ($customer) {
                        $customer->notify(new TestDriveCompleted($testDrive));
                    }
                    break;

                case TestDriveStatus::Cancelled:
                    if ($sales) {
                        $sales->notify(new TestDriveRejected($testDrive));
                    }
                    break;
            }
        } catch (Throwable $e) {
            Log::error('[SendTestDriveStatusNotification] Gagal mengirim notifikasi status test drive: '.$e->getMessage(), [
                'test_drive_id' => $testDrive->id,
                'status' => $toStatus->value,
            ]);
        }
    }
}
