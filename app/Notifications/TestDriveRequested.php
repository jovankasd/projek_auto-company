<?php

namespace App\Notifications;

use App\Models\TestDrive;
use App\Notifications\Channels\WhatsAppChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TestDriveRequested extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public TestDrive $testDrive
    ) {}

    /**
     * @return array<int, string|class-string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['database', 'mail'];
        if (config('services.whatsapp.enabled', false)) {
            $channels[] = WhatsAppChannel::class;
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $carModel = $this->testDrive->car->model_name ?? 'Unit Kendaraan';
        $customerName = $this->testDrive->customer->name ?? 'Pelanggan';

        return (new MailMessage)
            ->subject('Pengajuan Test Drive Baru: '.$carModel)
            ->greeting('Halo, '.$notifiable->name.'!')
            ->line('Ada pengajuan test drive baru untuk unit '.$carModel.' dari '.$customerName.'.')
            ->line('Jadwal Permintaan: '.$this->testDrive->preferred_date->translatedFormat('l, d F Y'))
            ->action('Lihat Detail Pengajuan', url('/dashboard/test-drives/'.$this->testDrive->id))
            ->line('Silakan hubungi pelanggan dan proses persetujuan jadwal.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $carModel = $this->testDrive->car->model_name ?? 'Mobil';
        $customerName = $this->testDrive->customer->name ?? 'Pelanggan';

        return [
            'title' => 'Pengajuan Test Drive Baru',
            'message' => 'Pengajuan test drive unit '.$carModel.' oleh '.$customerName,
            'url' => '/dashboard',
            'icon' => 'car',
            'color' => 'blue',
            'test_drive_id' => $this->testDrive->id,
        ];
    }

    public function toWhatsApp(object $notifiable): string
    {
        $carModel = $this->testDrive->car->model_name ?? 'Mobil';

        return "Halo {$notifiable->name},\nPengajuan test drive baru diterima untuk unit *{$carModel}* pada tanggal {$this->testDrive->preferred_date->translatedFormat('d M Y')}. Silakan cek di panel dashboard.";
    }
}
