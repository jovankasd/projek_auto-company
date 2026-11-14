<?php

namespace Tests\Feature;

use App\Enums\ServiceStatus;
use App\Enums\UserRole;
use App\Models\Service;
use App\Models\ServiceStatusLog;
use App\Models\User;
use App\Support\Timezone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class TimezoneTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::factory()->create([
            'role' => UserRole::SuperAdmin,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $this->customer = User::factory()->create([
            'role' => UserRole::Customer,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
    }

    public function test_timezone_helper_converts_utc_to_wib_and_formats_consistently(): void
    {
        // 2026-11-13 23:30:00 UTC = 2026-11-14 06:30:00 WIB
        $utc = Carbon::parse('2026-11-13 23:30:00', 'UTC');
        $wib = Timezone::toWib($utc);

        $this->assertEquals('Asia/Jakarta', $wib->timezoneName);
        $this->assertEquals('2026-11-14 06:30:00', $wib->format('Y-m-d H:i:s'));

        $formatted = Timezone::format($utc, 'd M Y, H:i');
        $this->assertEquals('14 Nov 2026, 06:30 WIB', $formatted);

        $withoutSuffix = Timezone::format($utc, 'd M Y, H:i', false);
        $this->assertEquals('14 Nov 2026, 06:30', $withoutSuffix);
    }

    public function test_service_status_stream_renders_explicit_wib_time(): void
    {
        $service = Service::create([
            'ticket_number' => 'SRV-20261114-0001',
            'customer_id' => $this->customer->id,
            'vehicle_plate_number' => 'B 1234 TZ',
            'vehicle_model' => 'Porsche 911',
            'vehicle_year' => 2024,
            'odometer_km' => 1000,
            'service_type' => 'regular',
            'service_date' => Carbon::parse('2026-11-14 09:00:00', 'UTC'),
            'slot_hour' => 9,
            'complaint_notes' => 'Pemeriksaan rutin.',
            'status' => ServiceStatus::Pending,
            'total_cost' => 0,
        ]);

        // Create log at 23:30 UTC yesterday -> 06:30 WIB today
        $log = ServiceStatusLog::create([
            'service_id' => $service->id,
            'from_status' => null,
            'to_status' => ServiceStatus::Pending->value,
            'changed_by' => $this->superAdmin->id,
            'notes' => 'Pendaftaran awal servis.',
        ]);
        $log->created_at = Carbon::parse('2026-11-13 23:30:00', 'UTC');
        $log->save();

        $response = $this->actingAs($this->superAdmin)
            ->getJson(route('services.status-stream', $service));

        $response->assertOk();
        $logs = $response->json('logs');
        $this->assertNotEmpty($logs);
        $this->assertEquals('14 Nov 2026, 06:30 WIB', $logs[0]['created_at_human']);
    }

    public function test_report_filter_respects_wib_boundaries_for_cross_day_utc_timestamps(): void
    {
        // Service A: 2026-11-13 23:30:00 UTC = 2026-11-14 06:30:00 WIB (Masuk tanggal 14 WIB)
        Service::create([
            'ticket_number' => 'SRV-20261114-0010',
            'customer_id' => $this->customer->id,
            'vehicle_plate_number' => 'B 1010 WIB',
            'vehicle_model' => 'BMW M3',
            'vehicle_year' => 2023,
            'odometer_km' => 5000,
            'service_type' => 'regular',
            'service_date' => Carbon::parse('2026-11-13 23:30:00', 'UTC'),
            'slot_hour' => 9,
            'complaint_notes' => 'Servis pagi.',
            'status' => ServiceStatus::Completed,
            'total_cost' => 1500000,
        ]);

        // Service B: 2026-11-14 17:05:00 UTC = 2026-11-15 00:05:00 WIB (Masuk tanggal 15 WIB)
        Service::create([
            'ticket_number' => 'SRV-20261114-0020',
            'customer_id' => $this->customer->id,
            'vehicle_plate_number' => 'B 2020 WIB',
            'vehicle_model' => 'Audi RS6',
            'vehicle_year' => 2024,
            'odometer_km' => 3000,
            'service_type' => 'regular',
            'service_date' => Carbon::parse('2026-11-14 17:05:00', 'UTC'),
            'slot_hour' => 10,
            'complaint_notes' => 'Servis esok hari WIB.',
            'status' => ServiceStatus::Completed,
            'total_cost' => 2500000,
        ]);

        // Filter untuk tanggal 2026-11-14 di WIB: harus menangkap Service A, BUKAN Service B
        $response14 = $this->actingAs($this->superAdmin)->get(route('reports.index', [
            'start_date' => '2026-11-14',
            'end_date' => '2026-11-14',
        ]));

        $response14->assertOk();
        $response14->assertViewHas('totalServices', 1);
        $response14->assertViewHas('totalRevenue', 1500000);

        // Filter untuk tanggal 2026-11-15 di WIB: harus menangkap Service B, BUKAN Service A
        $response15 = $this->actingAs($this->superAdmin)->get(route('reports.index', [
            'start_date' => '2026-11-15',
            'end_date' => '2026-11-15',
        ]));

        $response15->assertOk();
        $response15->assertViewHas('totalServices', 1);
        $response15->assertViewHas('totalRevenue', 2500000);
    }

    public function test_migrate_legacy_timezone_command_dry_run_and_execution(): void
    {
        $service = Service::create([
            'ticket_number' => 'SRV-20261114-9999',
            'customer_id' => $this->customer->id,
            'vehicle_plate_number' => 'B 9999 LEG',
            'vehicle_model' => 'Toyota Land Cruiser',
            'vehicle_year' => 2022,
            'odometer_km' => 20000,
            'service_type' => 'regular',
            'service_date' => Carbon::parse('2026-11-14 09:00:00'),
            'slot_hour' => 9,
            'complaint_notes' => 'Legacy data test.',
            'status' => ServiceStatus::Completed,
            'total_cost' => 1000000,
        ]);

        $service->created_at = Carbon::parse('2026-11-14 19:00:00');
        $service->saveQuietly();

        // 1. Dry run
        $this->artisan('timezone:migrate-legacy --dry-run')
            ->expectsOutputToContain('Simulasi selesai')
            ->assertSuccessful();

        $service->refresh();
        $this->assertEquals('2026-11-14 19:00:00', $service->created_at->format('Y-m-d H:i:s'));

        // 2. Real execution with --force
        $this->artisan('timezone:migrate-legacy --force')
            ->expectsOutputToContain('Migrasi timezone data lama berhasil diselesaikan secara atomik!')
            ->assertSuccessful();

        $service->refresh();
        $this->assertEquals('2026-11-14 12:00:00', $service->created_at->format('Y-m-d H:i:s'));
    }
}
