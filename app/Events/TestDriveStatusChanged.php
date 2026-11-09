<?php

namespace App\Events;

use App\Enums\TestDriveStatus;
use App\Models\TestDrive;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TestDriveStatusChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public TestDrive $testDrive,
        public ?TestDriveStatus $fromStatus,
        public TestDriveStatus $toStatus,
        public ?User $changedBy = null
    ) {}
}
