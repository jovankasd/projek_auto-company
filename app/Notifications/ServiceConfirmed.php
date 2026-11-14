<?php

namespace App\Notifications;

use App\Models\Service;
use App\Notifications\Channels\WhatsAppChannel;
use App\Support\Timezone;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ServiceConfirmed extends Notification implements ShouldQueue
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
        $mechanicName = $this->service->mechanic ? $this->service->mechanic->name : 'Mekanik Resmi';

        return (new MailMessage)
            ->subject('Servis Dikonfirmasi: '.$this->service->ticket_number)
            ->greeting('Halo, '.$notifiable->name.'!')
            ->line('Kabar baik! Tiket servis '.$this->service->ticket_number.' telah dikonfirmasi oleh bengkel.')
            ->line('Mekanik yang ditugaskan: '.$mechanicName)
            ->line('Jadwal Kedatangan: '.Timezone::format($this->service->service_date, 'l, d F Y', false).' pukul '.sprintf('%02d:00', $this->service->slot_hour).' WIB')
            ->action('Lihat Detail Tiket', $url)
            ->line('Mohon hadir tepat waktu sesuai jadwal yang telah ditentukan.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $mechanicName = $this->service->mechanic ? $this->service->mechanic->name : 'Ditugaskan';

        return [
            'title' => 'Servis Telah Dikonfirmasi',
            'message' => 'Tiket '.$this->service->ticket_number.' telah dikonfirmasi. Mekanik: '.$mechanicName,
            'url' => '/dashboard/services/'.$this->service->id,
            'icon' => 'check-circle',
            'color' => 'indigo',
            'service_id' => $this->service->id,
            'ticket_number' => $this->service->ticket_number,
        ];
    }

    public function toWhatsApp(object $notifiable): string
    {
        $mechanicName = $this->service->mechanic ? $this->service->mechanic->name : 'Mekanik Resmi';

        return "Halo {$notifiable->name},\nTiket servis *{$this->service->ticket_number}* telah dikonfirmasi oleh bengkel XYZ Motors. Mekanik: {$mechanicName}. Silakan datang pada ".Timezone::format($this->service->service_date, 'd M Y', false).' jam '.sprintf('%02d:00', $this->service->slot_hour).' WIB. Detail: '.url('/dashboard/services/'.$this->service->id);
    }
}
