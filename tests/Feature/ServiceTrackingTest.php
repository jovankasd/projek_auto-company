<?php

use App\Enums\ServiceStatus;
use App\Enums\UserRole;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

test('customer can view service live tracking page', function () {
    $customer = User::factory()->create([
        'role' => UserRole::Customer,
    ]);

    $service = Service::create([
        'ticket_number' => 'SRV-20241001-8888',
        'customer_id' => $customer->id,
        'vehicle_plate_number' => 'B 8888 TRACK',
        'vehicle_model' => 'BMW M4 Competition',
        'vehicle_year' => 2024,
        'odometer_km' => 15000,
        'complaint_notes' => 'Pemeriksaan rutin kelistrikan.',
        'service_type' => 'major',
        'service_date' => Carbon::now()->addDays(2),
        'slot_hour' => 10,
        'status' => ServiceStatus::Pending,
        'total_cost' => 0,
    ]);

    $response = $this->actingAs($customer)->get(route('services.track', $service));
    $response->assertStatus(200);
    $response->assertSee('SRV-20241001-8888');
    $response->assertSee('BMW M4 Competition');
    $response->assertSee('Pelacakan Langsung Aktif');
});

test('service status stream endpoint returns structured json with progress and logs', function () {
    $customer = User::factory()->create([
        'role' => UserRole::Customer,
    ]);

    $service = Service::create([
        'ticket_number' => 'SRV-20241001-7777',
        'customer_id' => $customer->id,
        'vehicle_plate_number' => 'B 7777 STRM',
        'vehicle_model' => 'Ford Mustang GT',
        'vehicle_year' => 2024,
        'odometer_km' => 15000,
        'complaint_notes' => 'Pemeriksaan rutin kelistrikan.',
        'service_type' => 'regular',
        'service_date' => Carbon::now()->addDays(1),
        'slot_hour' => 14,
        'status' => ServiceStatus::InProgress,
        'total_cost' => 500000,
    ]);

    $response = $this->actingAs($customer)->getJson(route('services.status-stream', $service));
    $response->assertStatus(200);
    $response->assertJson([
        'ticket_number' => 'SRV-20241001-7777',
        'status' => 'in_progress',
        'progress_percent' => 75,
    ]);
    $response->assertJsonStructure([
        'id', 'ticket_number', 'status', 'status_label', 'status_color',
        'progress_percent', 'total_cost', 'total_cost_formatted',
        'mechanic_name', 'logs',
    ]);
});
