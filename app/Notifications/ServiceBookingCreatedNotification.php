<?php

namespace App\Notifications;

use App\Models\Service;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ServiceBookingCreatedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Service $service
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
            ->subject('Booking Servis Baru: '.$this->service->ticket_number)
            ->line('Booking servis baru telah dibuat dengan nomor tiket '.$this->service->ticket_number.'.')
            ->action('Lihat Detail', url('/dashboard/services/'.$this->service->id))
            ->line('Kendaraan: '.$this->service->vehicle_model.' ('.$this->service->vehicle_plate_number.')');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Booking Servis Baru',
            'message' => 'Tiket '.$this->service->ticket_number.' - '.$this->service->vehicle_model.' ('.$this->service->vehicle_plate_number.')',
            'url' => '/dashboard',
            'icon' => 'wrench',
            'color' => 'amber',
        ];
    }
}
