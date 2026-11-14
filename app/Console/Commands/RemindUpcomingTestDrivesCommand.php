<?php

namespace App\Console\Commands;

use App\Enums\TestDriveStatus;
use App\Models\TestDrive;
use App\Support\Timezone;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RemindUpcomingTestDrivesCommand extends Command
{
    protected $signature = 'test-drives:remind-upcoming';

    protected $description = 'Kirim pengingat jadwal test drive yang dijadwalkan besok kepada pelanggan dan sales';

    public function handle(): int
    {
        $tomorrow = Timezone::todayWib()->addDay();
        $startUtc = Timezone::startOfDayUtc($tomorrow);
        $endUtc = Timezone::endOfDayUtc($tomorrow);

        $testDrives = TestDrive::where(function ($query) use ($tomorrow, $startUtc, $endUtc) {
            $query->whereBetween('preferred_date', [$startUtc, $endUtc])
                ->orWhereDate('preferred_date', $tomorrow->toDateString());
        })
            ->where('status', TestDriveStatus::Approved)
            ->with(['customer', 'car', 'sales'])
            ->get();

        $this->info("Menemukan {$testDrives->count()} sesi test drive untuk jadwal besok ({$tomorrow->format('Y-m-d')}).");

        foreach ($testDrives as $testDrive) {
            $carName = $testDrive->car->model_name ?? 'Unit Kendaraan';
            Log::info("[Pengingat Test Drive] Mengingatkan {$testDrive->customer->name} untuk unit {$carName} bersama sales {$testDrive->sales?->name}");
        }

        $this->info('Pengingat jadwal test drive berhasil diproses.');

        return Command::SUCCESS;
    }
}
