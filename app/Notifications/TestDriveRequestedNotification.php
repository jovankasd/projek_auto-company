<?php

namespace App\Notifications;

use App\Models\TestDrive;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TestDriveRequestedNotification extends Notification
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

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Pengajuan Test Drive Baru: '.($this->testDrive->car->model_name ?? 'Unit Mobil'))
            ->line('Ada pengajuan test drive baru dari pelanggan '.($this->testDrive->customer->name ?? 'Pelanggan').'.')
            ->action('Lihat Detail', url('/dashboard/test-drives/'.$this->testDrive->id))
            ->line('Silakan tinjau dan tindak lanjuti jadwal test drive tersebut.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Pengajuan Test Drive Baru',
            'message' => 'Pengajuan test drive untuk '.($this->testDrive->car->model_name ?? 'Mobil').' oleh '.($this->testDrive->customer->name ?? 'Pelanggan'),
            'url' => '/dashboard',
            'icon' => 'car',
            'color' => 'blue',
        ];
    }
}
