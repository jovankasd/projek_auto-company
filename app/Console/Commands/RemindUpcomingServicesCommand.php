<?php

namespace App\Console\Commands;

use App\Enums\ServiceStatus;
use App\Models\Service;
use App\Support\Timezone;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RemindUpcomingServicesCommand extends Command
{
    protected $signature = 'services:remind-upcoming';

    protected $description = 'Kirim pengingat servis yang dijadwalkan besok kepada pelanggan';

    public function handle(): int
    {
        $tomorrow = Timezone::todayWib()->addDay();
        $startUtc = Timezone::startOfDayUtc($tomorrow);
        $endUtc = Timezone::endOfDayUtc($tomorrow);

        $services = Service::where(function ($query) use ($tomorrow, $startUtc, $endUtc) {
            $query->whereBetween('service_date', [$startUtc, $endUtc])
                ->orWhereDate('service_date', $tomorrow->toDateString());
        })
            ->whereIn('status', [ServiceStatus::Pending, ServiceStatus::Confirmed])
            ->with(['customer'])
            ->get();

        $this->info("Menemukan {$services->count()} servis untuk jadwal besok ({$tomorrow->format('Y-m-d')}).");

        foreach ($services as $service) {
            if ($service->customer) {
                Log::info("[Pengingat Servis] Mengirim pengingat untuk tiket {$service->ticket_number} ke {$service->customer->email}");
                // Dispatched reminder log / notification
            }
        }

        $this->info('Pengingat jadwal servis berhasil diproses.');

        return Command::SUCCESS;
    }
}
