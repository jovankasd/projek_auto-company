<?php

namespace App\Services\WhatsApp;

use App\Services\Contracts\WhatsAppGateway;
use Illuminate\Support\Facades\Log;

class LogWhatsAppGateway implements WhatsAppGateway
{
    public function send(string $to, string $message): bool
    {
        Log::info('[WhatsApp Log Gateway] Outgoing message', [
            'to' => $to,
            'message' => $message,
            'timestamp' => now()->toIso8601String(),
        ]);

        return true;
    }
}
