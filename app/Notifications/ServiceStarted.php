<?php

namespace App\Notifications;

use App\Models\Service;
use App\Notifications\Channels\WhatsAppChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ServiceStarted extends Notification implements ShouldQueue
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
            ->subject('Pengerjaan Dimulai: '.$this->service->ticket_number)
            ->greeting('Halo, '.$notifiable->name.'!')
            ->line('Kendaraan Anda '.$this->service->vehicle_model.' ('.$this->service->vehicle_plate_number.') saat ini sedang dalam proses pengerjaan oleh tim mekanik.')
            ->line('Tiket Servis: '.$this->service->ticket_number)
            ->action('Pantau Progres Langsung', $url)
            ->line('Anda dapat melihat rincian pembaruan suku cadang dan log pengerjaan secara langsung di dashboard.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Pengerjaan Servis Dimulai',
            'message' => 'Mekanik telah memulai pengerjaan unit '.$this->service->vehicle_model.' ('.$this->service->ticket_number.')',
            'url' => '/dashboard/services/'.$this->service->id,
            'icon' => 'clock',
            'color' => 'amber',
            'service_id' => $this->service->id,
            'ticket_number' => $this->service->ticket_number,
        ];
    }

    public function toWhatsApp(object $notifiable): string
    {
        return "Halo {$notifiable->name},\nKendaraan Anda ({$this->service->vehicle_model} - {$this->service->vehicle_plate_number}) dengan tiket *{$this->service->ticket_number}* sedang dikerjakan oleh tim mekanik. Pantau langsung di: ".url('/dashboard/services/'.$this->service->id);
    }
}
