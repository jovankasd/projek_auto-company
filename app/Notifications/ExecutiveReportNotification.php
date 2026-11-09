<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ExecutiveReportNotification extends Notification
{
    use Queueable;

    /**
     * @param  array<string, mixed>  $summary
     */
    public function __construct(
        public string $title,
        public string $message,
        public ?string $url = null,
        public array $summary = []
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'url' => $this->url ?? route('dashboard'),
            'icon' => 'document-report',
            'color' => 'purple',
            'summary' => $this->summary,
        ];
    }
}
