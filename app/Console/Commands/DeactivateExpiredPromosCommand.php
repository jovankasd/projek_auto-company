<?php

namespace App\Console\Commands;

use App\Models\Promo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class DeactivateExpiredPromosCommand extends Command
{
    protected $signature = 'promos:deactivate-expired';

    protected $description = 'Nonaktifkan promo yang telah melewati batas tanggal berakhir';

    public function handle(): int
    {
        $updated = Promo::where('is_active', true)
            ->where('ends_at', '<', now())
            ->update(['is_active' => false]);

        if ($updated > 0) {
            Cache::forget('active_promos_banner');
            $this->info("Berhasil menonaktifkan {$updated} promo yang telah kedaluwarsa.");
        } else {
            $this->info('Tidak ada promo kedaluwarsa yang perlu dinonaktifkan.');
        }

        return Command::SUCCESS;
    }
}
