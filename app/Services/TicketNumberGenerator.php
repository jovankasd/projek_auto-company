<?php

namespace App\Services;

use App\Models\Service;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TicketNumberGenerator
{
    /**
     * Generate nomor tiket unik dengan format SRV-YYYYMMDD-XXXX.
     * Menggunakan transaction + lockForUpdate untuk mencegah race condition.
     */
    public function generate(int $maxRetries = 5): string
    {
        $attempt = 0;

        while ($attempt < $maxRetries) {
            try {
                return DB::transaction(function () {
                    $date = Carbon::now()->format('Ymd');
                    $prefix = "SRV-{$date}-";

                    $lastTicket = Service::withTrashed()
                        ->where('ticket_number', 'like', "{$prefix}%")
                        ->lockForUpdate()
                        ->orderBy('ticket_number', 'desc')
                        ->value('ticket_number');

                    if ($lastTicket) {
                        $lastSequence = (int) substr($lastTicket, -4);
                        $sequence = $lastSequence + 1;
                    } else {
                        $sequence = 1;
                    }

                    $ticket = $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);

                    if (Service::withTrashed()->where('ticket_number', $ticket)->exists()) {
                        throw new \RuntimeException('Ticket collision, retrying.');
                    }

                    return $ticket;
                });
            } catch (\RuntimeException $e) {
                $attempt++;
                usleep(random_int(10000, 50000));
            }
        }

        throw new \RuntimeException('Gagal generate nomor tiket setelah '.$maxRetries.' percobaan.');
    }
}
