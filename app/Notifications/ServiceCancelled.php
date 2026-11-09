<?php

namespace App\Notifications;

use App\Models\Service;
use App\Notifications\Channels\WhatsAppChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ServiceCancelled extends Notification implements ShouldQueue
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
            ->subject('Pembatalan Tiket Servis: '.$this->service->ticket_number)
            ->greeting('Halo, '.$notifiable->name.'!')
            ->line('Tiket servis '.$this->service->ticket_number.' untuk unit '.$this->service->vehicle_model.' telah dibatalkan.')
            ->line('Alasan: '.($this->service->cancellation_reason ?? 'Dibatalkan oleh pelanggan/bengkel.'))
            ->action('Lihat Detail Tiket', $url)
            ->line('Apabila ada pertanyaan atau ingin mengatur jadwal ulang, silakan hubungi tim layanan pelanggan kami.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Tiket Servis Dibatalkan',
            'message' => 'Tiket '.$this->service->ticket_number.' telah dibatalkan. Alasan: '.($this->service->cancellation_reason ?? '-'),
            'url' => '/dashboard/services/'.$this->service->id,
            'icon' => 'x-circle',
            'color' => 'red',
            'service_id' => $this->service->id,
            'ticket_number' => $this->service->ticket_number,
        ];
    }

    public function toWhatsApp(object $notifiable): string
    {
        return "Halo {$notifiable->name},\nTiket servis *{$this->service->ticket_number}* telah dibatalkan. Alasan: ".($this->service->cancellation_reason ?? '-').'. Hubungi customer service untuk informasi lebih lanjut.';
    }
}
