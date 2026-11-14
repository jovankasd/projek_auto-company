<?php

namespace App\Notifications;

use App\Models\ContactInquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContactInquiryNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public ContactInquiry $inquiry
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
            ->subject('Pesan Masuk Baru dari: '.$this->inquiry->name)
            ->line('Subjek: '.$this->inquiry->subject)
            ->line('Dari: '.$this->inquiry->name.' ('.$this->inquiry->email.', '.$this->inquiry->phone.')')
            ->line('Pesan: '.$this->inquiry->message)
            ->action('Lihat Dashboard', url('/dashboard'))
            ->line('Silakan tindak lanjuti prospek ini segera.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Pesan Masuk Baru: '.$this->inquiry->subject,
            'message' => 'Pesan dari '.$this->inquiry->name.' ('.$this->inquiry->email.'): '.$this->inquiry->message,
            'url' => '/dashboard',
            'icon' => 'chat',
            'color' => 'blue',
            'inquiry_id' => $this->inquiry->id,
        ];
    }
}
