<?php

namespace App\Services\WhatsApp;

use App\Services\Contracts\WhatsAppGateway;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class FonnteGateway implements WhatsAppGateway
{
    public function __construct(
        private string $token = '',
        private string $endpoint = 'https://api.fonnte.com/send'
    ) {}

    public function send(string $to, string $message): bool
    {
        if (empty($this->token)) {
            Log::warning('[FonnteGateway] Token kosong. Mengalihkan ke log sistem.', ['to' => $to]);

            return false;
        }

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'Authorization' => $this->token,
                ])
                ->post($this->endpoint, [
                    'target' => $to,
                    'message' => $message,
                ]);

            if ($response->successful()) {
                return true;
            }

            Log::error('[FonnteGateway] Gagal mengirim pesan', [
                'to' => $to,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        } catch (Throwable $e) {
            Log::error('[FonnteGateway] Exception: '.$e->getMessage(), ['to' => $to]);

            return false;
        }
    }
}
