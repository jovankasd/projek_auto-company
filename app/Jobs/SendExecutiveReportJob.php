<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\ExecutiveReportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendExecutiveReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 60;

    public function __construct(
        public int $userId,
        public ?string $note = null
    ) {}

    public function handle(ExecutiveReportService $service): void
    {
        $user = User::find($this->userId);
        if ($user) {
            $service->submit($user, $this->note);
        }
    }
}
