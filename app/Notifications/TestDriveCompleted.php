<?php

namespace App\Notifications;

use App\Models\TestDrive;
use App\Notifications\Channels\WhatsAppChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TestDriveCompleted extends Notification implements ShouldQueue
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
            ->subject('Terima Kasih Telah Mencoba: '.$carModel)
            ->greeting('Halo, '.$notifiable->name.'!')
            ->line('Terima kasih telah mencoba sesi test drive unit '.$carModel.' bersama XYZ Motors.')
            ->line('Kami berharap Anda menikmati sensasi berkendara yang luar biasa.')
            ->action('Jelajahi Promo & Paket Pembelian', url('/'))
            ->line('Tim sales kami siap membantu Anda dengan informasi harga, simulasi kredit, dan penawaran terbaik.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $carModel = $this->testDrive->car->model_name ?? 'Mobil';

        return [
            'title' => 'Test Drive Telah Selesai',
            'message' => 'Terima kasih telah melakukan test drive unit '.$carModel.'. Penawaran spesial menanti Anda!',
            'url' => '/dashboard/my-test-drives',
            'icon' => 'sparkles',
            'color' => 'emerald',
            'test_drive_id' => $this->testDrive->id,
        ];
    }

    public function toWhatsApp(object $notifiable): string
    {
        $carModel = $this->testDrive->car->model_name ?? 'Mobil';

        return "Halo {$notifiable->name},\nTerima kasih telah mencoba sesi test drive unit *{$carModel}* bersama XYZ Motors. Hubungi sales kami untuk penawaran dan promo spesial pembelian unit!";
    }
}
