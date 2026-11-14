<?php

namespace App\Notifications;

use App\Models\TestDrive;
use App\Notifications\Channels\WhatsAppChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TestDriveCancelled extends Notification implements ShouldQueue
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
            ->subject('Pembatalan Jadwal Test Drive: '.$carModel)
            ->greeting('Halo, '.$notifiable->name.'!')
            ->line('Jadwal pengajuan test drive untuk unit '.$carModel.' pada tanggal '.$this->testDrive->preferred_date->translatedFormat('d F Y').' telah dibatalkan.')
            ->line('Keterangan: '.($this->testDrive->rejection_reason ?? 'Dibatalkan.'))
            ->action('Pilih Jadwal Lain', url('/'))
            ->line('Silakan ajukan kembali jadwal yang lebih sesuai di showroom kami.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $carModel = $this->testDrive->car->model_name ?? 'Mobil';

        return [
            'title' => 'Test Drive Dibatalkan',
            'message' => 'Pengajuan test drive unit '.$carModel.' telah dibatalkan. Keterangan: '.($this->testDrive->rejection_reason ?? '-'),
            'url' => '/dashboard/my-test-drives',
            'icon' => 'x-circle',
            'color' => 'amber',
            'test_drive_id' => $this->testDrive->id,
        ];
    }

    public function toWhatsApp(object $notifiable): string
    {
        $carModel = $this->testDrive->car->model_name ?? 'Mobil';

        return "Halo {$notifiable->name},\nPengajuan test drive unit *{$carModel}* Anda telah dibatalkan. Keterangan: ".($this->testDrive->rejection_reason ?? '-').'. Silakan ajukan jadwal baru melalui website kami.';
    }
}
