<?php

namespace App\Notifications;

use App\Models\Service;
use App\Notifications\Channels\WhatsAppChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ServiceCreated extends Notification implements ShouldQueue
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
            ->subject('Booking Servis Diterima: '.$this->service->ticket_number)
            ->greeting('Halo, '.$notifiable->name.'!')
            ->line('Booking servis berkala Anda telah berhasil kami terima.')
            ->line('Nomor Tiket: '.$this->service->ticket_number)
            ->line('Unit Kendaraan: '.$this->service->vehicle_model.' ('.$this->service->vehicle_plate_number.')')
            ->line('Jadwal: '.$this->service->service_date->translatedFormat('l, d F Y').' pukul '.sprintf('%02d:00', $this->service->slot_hour).' WIB')
            ->action('Pantau Status Servis', $url)
            ->line('Tim kami akan segera memverifikasi dan menyiapkan slot pengerjaan Anda.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Booking Servis Diterima',
            'message' => 'Tiket '.$this->service->ticket_number.' - '.$this->service->vehicle_model.' ('.$this->service->vehicle_plate_number.')',
            'url' => '/dashboard/services/'.$this->service->id,
            'icon' => 'wrench',
            'color' => 'blue',
            'service_id' => $this->service->id,
            'ticket_number' => $this->service->ticket_number,
        ];
    }

    public function toWhatsApp(object $notifiable): string
    {
        return "Halo {$notifiable->name},\nBooking servis Anda telah diterima dengan nomor tiket *{$this->service->ticket_number}* untuk unit {$this->service->vehicle_model} ({$this->service->vehicle_plate_number}). Jadwal: {$this->service->service_date->translatedFormat('d M Y')} jam ".sprintf('%02d:00', $this->service->slot_hour).' WIB. Pantau status servis di: '.url('/dashboard/services/'.$this->service->id);
    }
}
