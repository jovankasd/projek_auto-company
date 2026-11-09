<?php

namespace App\Services\Contracts;

interface WhatsAppGateway
{
    /**
     * Kirim pesan WhatsApp ke nomor tujuan.
     *
     * @param  string  $to  Nomor telepon tujuan (format internasional atau lokal)
     * @param  string  $message  Isi pesan teks
     * @return bool Status pengiriman berhasil atau tidak
     */
    public function send(string $to, string $message): bool;
}
