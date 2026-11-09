<?php

namespace App\Observers;

use App\Enums\TestDriveStatus;
use App\Events\TestDriveStatusChanged;
use App\Models\TestDrive;

class TestDriveObserver
{
    public function created(TestDrive $testDrive): void
    {
        $status = $testDrive->status instanceof TestDriveStatus
            ? $testDrive->status
            : TestDriveStatus::tryFrom((string) $testDrive->status) ?? TestDriveStatus::Requested;

        event(new TestDriveStatusChanged($testDrive, null, $status, auth()->user()));
    }

    public function updated(TestDrive $testDrive): void
    {
        if ($testDrive->wasChanged('status')) {
            $rawFrom = $testDrive->getOriginal('status');
            $fromStatus = $rawFrom instanceof TestDriveStatus
                ? $rawFrom
                : TestDriveStatus::tryFrom((string) $rawFrom);

            $toStatus = $testDrive->status instanceof TestDriveStatus
                ? $testDrive->status
                : TestDriveStatus::tryFrom((string) $testDrive->status) ?? TestDriveStatus::Requested;

            event(new TestDriveStatusChanged($testDrive, $fromStatus, $toStatus, auth()->user()));
        }
    }
}
