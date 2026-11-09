<?php

namespace App\Notifications;

use App\Models\TestDrive;
use App\Notifications\Channels\WhatsAppChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TestDriveApproved extends Notification implements ShouldQueue
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
        $salesName = $this->testDrive->sales ? $this->testDrive->sales->name : 'Sales Representative';

        return (new MailMessage)
            ->subject('Jadwal Test Drive Disetujui: '.$carModel)
            ->greeting('Halo, '.$notifiable->name.'!')
            ->line('Selamat! Pengajuan test drive Anda untuk unit '.$carModel.' telah disetujui.')
            ->line('Jadwal Pelaksanaan: '.$this->testDrive->preferred_date->translatedFormat('l, d F Y'))
            ->line('Sales Pendamping: '.$salesName)
            ->line('Lokasi: '.($this->testDrive->pickup_location ?? 'Showroom Resmi XYZ Motors'))
            ->action('Lihat Detail Jadwal', url('/dashboard/my-test-drives'))
            ->line('Harap membawa SIM A asli yang masih berlaku saat kehadiran.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $carModel = $this->testDrive->car->model_name ?? 'Mobil';

        return [
            'title' => 'Pengajuan Test Drive Disetujui',
            'message' => 'Pengajuan test drive unit '.$carModel.' telah disetujui untuk tanggal '.$this->testDrive->preferred_date->translatedFormat('d M Y'),
            'url' => '/dashboard/my-test-drives',
            'icon' => 'check-circle',
            'color' => 'emerald',
            'test_drive_id' => $this->testDrive->id,
        ];
    }

    public function toWhatsApp(object $notifiable): string
    {
        $carModel = $this->testDrive->car->model_name ?? 'Mobil';
        $salesName = $this->testDrive->sales ? $this->testDrive->sales->name : 'Sales Representative';

        return "Halo {$notifiable->name},\nSelamat! Jadwal test drive unit *{$carModel}* Anda telah disetujui untuk tanggal {$this->testDrive->preferred_date->translatedFormat('d M Y')}. Sales kami ({$salesName}) akan mendampingi Anda di lokasi. Harap membawa SIM A asli.";
    }
}
