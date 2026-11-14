<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\Service;
use App\Models\ServiceStatusLog;
use App\Models\Setting;
use App\Models\TestDrive;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class MigrateLegacyTimezoneCommand extends Command
{
    protected $signature = 'timezone:migrate-legacy 
                            {--dry-run : Jalankan simulasi tanpa mengubah data database} 
                            {--force : Paksa eksekusi migrasi tanpa konfirmasi interaktif} 
                            {--hours=7 : Offset jam yang akan dikurangkan (WIB ke UTC = 7 jam)}';

    protected $description = 'Menyesuaikan timestamp historis yang tersimpan dalam waktu lokal Asia/Jakarta menjadi standar UTC.';

    public function handle(): int
    {
        $isDryRun = (bool) $this->option('dry-run');
        $hours = (int) $this->option('hours');

        $this->info('Memulai verifikasi migrasi timezone data lama...');
        $this->line('Mode: '.($isDryRun ? '<comment>DRY-RUN (Simulasi)</comment>' : '<fg=green>EKSEKUSI NYATA</fg=green>'));
        $this->line("Offset pengurangan: <comment>-{$hours} jam</comment>");

        $servicesCount = Service::count();
        $statusLogsCount = ServiceStatusLog::count();
        $testDrivesCount = TestDrive::count();
        $auditLogsCount = AuditLog::count();
        $lastReport = Setting::where('key', 'last_report_submitted_at')->first();

        $this->table(
            ['Tabel / Entitas', 'Jumlah Baris Terdampak'],
            [
                ['services', $servicesCount],
                ['service_status_logs', $statusLogsCount],
                ['test_drives', $testDrivesCount],
                ['audit_logs', $auditLogsCount],
                ['settings (last_report_submitted_at)', $lastReport ? 1 : 0],
            ]
        );

        if ($isDryRun) {
            $this->info('Simulasi selesai. Jalankan kembali tanpa opsi --dry-run untuk menerapkan migrasi ke database.');

            return Command::SUCCESS;
        }

        if (! $this->option('force') && ! $this->confirm('Apakah Anda yakin ingin mengonversi timestamp tabel di atas mundur 7 jam ke UTC?', true)) {
            $this->warn('Migrasi dibatalkan oleh pengguna.');

            return Command::FAILURE;
        }

        DB::transaction(function () use ($hours, $lastReport) {
            // Update services
            Service::query()->lazyById(100)->each(function (Service $service) use ($hours) {
                $updates = [];
                foreach (['created_at', 'updated_at', 'confirmed_at', 'started_at', 'completed_at', 'cancelled_at'] as $col) {
                    if ($service->{$col}) {
                        $updates[$col] = Carbon::parse($service->{$col})->subHours($hours);
                    }
                }
                if (! empty($updates)) {
                    $service->timestamps = false;
                    $service->forceFill($updates)->saveQuietly();
                }
            });

            // Update service status logs
            ServiceStatusLog::query()->lazyById(100)->each(function (ServiceStatusLog $log) use ($hours) {
                if ($log->created_at) {
                    $log->timestamps = false;
                    $log->forceFill([
                        'created_at' => Carbon::parse($log->created_at)->subHours($hours),
                    ])->saveQuietly();
                }
            });

            // Update test drives
            TestDrive::query()->lazyById(100)->each(function (TestDrive $td) use ($hours) {
                $updates = [];
                foreach (['created_at', 'updated_at', 'approved_at', 'completed_at'] as $col) {
                    if ($td->{$col}) {
                        $updates[$col] = Carbon::parse($td->{$col})->subHours($hours);
                    }
                }
                if (! empty($updates)) {
                    $td->timestamps = false;
                    $td->forceFill($updates)->saveQuietly();
                }
            });

            // Update audit logs
            AuditLog::query()->lazyById(100)->each(function (AuditLog $audit) use ($hours) {
                if ($audit->created_at) {
                    $audit->timestamps = false;
                    $audit->forceFill([
                        'created_at' => Carbon::parse($audit->created_at)->subHours($hours),
                    ])->saveQuietly();
                }
            });

            // Update setting last_report_submitted_at
            if ($lastReport && $lastReport->value) {
                $converted = Carbon::parse($lastReport->value)->subHours($hours)->toIso8601String();
                Setting::set('last_report_submitted_at', $converted, 'reports');
            }
        });

        $this->info('Migrasi timezone data lama berhasil diselesaikan secara atomik!');

        return Command::SUCCESS;
    }
}
