<?php

namespace App\Services\WhatsApp;

use App\Services\Contracts\WhatsAppGateway;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class TwilioGateway implements WhatsAppGateway
{
    public function __construct(
        private string $sid = '',
        private string $token = '',
        private string $from = ''
    ) {}

    public function send(string $to, string $message): bool
    {
        if (empty($this->sid) || empty($this->token) || empty($this->from)) {
            Log::warning('[TwilioGateway] Kredensial tidak lengkap.', ['to' => $to]);

            return false;
        }

        // Pastikan prefix whatsapp: tersedia
        $fromNumber = str_starts_with($this->from, 'whatsapp:') ? $this->from : 'whatsapp:'.$this->from;
        $toNumber = str_starts_with($to, 'whatsapp:') ? $to : 'whatsapp:'.$to;

        try {
            $url = "https://api.twilio.com/2010-04-01/Accounts/{$this->sid}/Messages.json";

            $response = Http::timeout(10)
                ->withBasicAuth($this->sid, $this->token)
                ->asForm()
                ->post($url, [
                    'From' => $fromNumber,
                    'To' => $toNumber,
                    'Body' => $message,
                ]);

            if ($response->successful()) {
                return true;
            }

            Log::error('[TwilioGateway] Gagal mengirim pesan', [
                'to' => $to,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        } catch (Throwable $e) {
            Log::error('[TwilioGateway] Exception: '.$e->getMessage(), ['to' => $to]);

            return false;
        }
    }
}
