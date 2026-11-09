<?php

namespace App\Notifications\Channels;

use App\Services\Contracts\WhatsAppGateway;
use Illuminate\Notifications\Notification;

class WhatsAppChannel
{
    public function __construct(
        private WhatsAppGateway $gateway
    ) {}

    public function send(object $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toWhatsApp')) {
            return;
        }

        $phone = $notifiable->phone_number ?? null;
        if (! $phone) {
            return;
        }

        $message = $notification->toWhatsApp($notifiable);
        if ($message) {
            $this->gateway->send((string) $phone, (string) $message);
        }
    }
}
