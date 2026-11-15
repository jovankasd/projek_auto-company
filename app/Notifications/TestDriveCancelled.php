<?php

namespace App\Notifications;

use App\Models\TestDrive;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TestDriveCancelled extends Notification
{
    use Queueable;

    public function __construct(
        public TestDrive $testDrive
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $carModel = $this->testDrive->car->model_name ?? 'Unit Kendaraan';
        $customerName = $this->testDrive->customer->name ?? 'Pelanggan';

        return [
            'title' => 'Pengajuan Test Drive Dibatalkan',
            'message' => "{$customerName} membatalkan pengajuan test drive untuk unit {$carModel}.",
            'url' => '/dashboard',
            'icon' => 'x-circle',
            'color' => 'amber',
            'test_drive_id' => $this->testDrive->id,
        ];
    }
}
