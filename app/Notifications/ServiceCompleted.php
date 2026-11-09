<?php

namespace App\Notifications;

use App\Models\Service;
use App\Notifications\Channels\WhatsAppChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ServiceCompleted extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Service $service
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
        $url = url('/dashboard/services/'.$this->service->id);

        return (new MailMessage)
            ->subject('Servis Selesai: '.$this->service->ticket_number)
            ->greeting('Halo, '.$notifiable->name.'!')
            ->line('Pengerjaan servis kendaraan Anda telah selesai dengan baik.')
            ->line('Tiket Servis: '.$this->service->ticket_number)
            ->line('Kendaraan: '.$this->service->vehicle_model.' ('.$this->service->vehicle_plate_number.')')
            ->line('Total Biaya: Rp'.number_format((float) $this->service->total_cost, 0, ',', '.'))
            ->action('Lihat Rincian & Nota Servis', $url)
            ->line('Kendaraan siap diambil di bengkel resmi XYZ Motors. Terima kasih atas kepercayaan Anda!');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Servis Telah Selesai',
            'message' => 'Tiket '.$this->service->ticket_number.' telah selesai dikerjakan. Total biaya: Rp'.number_format((float) $this->service->total_cost, 0, ',', '.'),
            'url' => '/dashboard/services/'.$this->service->id,
            'icon' => 'check-badge',
            'color' => 'emerald',
            'service_id' => $this->service->id,
            'ticket_number' => $this->service->ticket_number,
        ];
    }

    public function toWhatsApp(object $notifiable): string
    {
        return "Halo {$notifiable->name},\nServis kendaraan Anda ({$this->service->vehicle_model} - {$this->service->ticket_number}) telah selesai! Total biaya: Rp".number_format((float) $this->service->total_cost, 0, ',', '.').'. Kendaraan siap diambil. Rincian: '.url('/dashboard/services/'.$this->service->id);
    }
}
