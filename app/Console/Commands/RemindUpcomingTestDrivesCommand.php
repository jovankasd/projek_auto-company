<?php

namespace App\Console\Commands;

use App\Enums\TestDriveStatus;
use App\Models\TestDrive;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class RemindUpcomingTestDrivesCommand extends Command
{
    protected $signature = 'test-drives:remind-upcoming';

    protected $description = 'Kirim pengingat jadwal test drive yang dijadwalkan besok kepada pelanggan dan sales';

    public function handle(): int
    {
        $tomorrow = Carbon::tomorrow();

        $testDrives = TestDrive::whereDate('preferred_date', $tomorrow->toDateString())
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
