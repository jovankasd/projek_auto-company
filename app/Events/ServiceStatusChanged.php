<?php

namespace App\Events;

use App\Enums\ServiceStatus;
use App\Models\Service;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ServiceStatusChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Service $service,
        public ?ServiceStatus $fromStatus,
        public ServiceStatus $toStatus,
        public ?User $changedBy = null
    ) {}
}
