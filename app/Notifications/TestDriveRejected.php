<?php

namespace App\Notifications;

use App\Models\TestDrive;
use App\Notifications\Channels\WhatsAppChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TestDriveRejected extends Notification implements ShouldQueue
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

        return (new MailMessage)
            ->subject('Pemberitahuan Test Drive: '.$carModel)
            ->greeting('Halo, '.$notifiable->name.'!')
            ->line('Mohon maaf, pengajuan test drive Anda untuk unit '.$carModel.' pada tanggal '.$this->testDrive->preferred_date->translatedFormat('d F Y').' belum dapat kami setujui saat ini.')
            ->line('Alasan: '.($this->testDrive->rejection_reason ?? 'Unit sedang dalam masa perawatan / slot jadwal penuh.'))
            ->action('Ajukan Jadwal Baru', url('/'))
            ->line('Anda dapat mengajukan tanggal lain atau memilih unit mobil lain yang tersedia.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $carModel = $this->testDrive->car->model_name ?? 'Mobil';

        return [
            'title' => 'Pengajuan Test Drive Ditolak',
            'message' => 'Pengajuan test drive unit '.$carModel.' belum dapat disetujui. Alasan: '.($this->testDrive->rejection_reason ?? '-'),
            'url' => '/dashboard/my-test-drives',
            'icon' => 'x-circle',
            'color' => 'red',
            'test_drive_id' => $this->testDrive->id,
        ];
    }

    public function toWhatsApp(object $notifiable): string
    {
        $carModel = $this->testDrive->car->model_name ?? 'Mobil';

        return "Halo {$notifiable->name},\nMohon maaf, pengajuan test drive unit *{$carModel}* Anda belum dapat disetujui. Alasan: ".($this->testDrive->rejection_reason ?? '-').'. Silakan pilih jadwal lain di showroom kami.';
    }
}
