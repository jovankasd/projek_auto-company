<?php

use App\Enums\ServiceItemType;
use App\Enums\ServiceStatus;
use App\Models\Service;
use App\Models\User;
use App\Services\ServiceCostCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

test('calculates correct subtotal for quantity and unit price', function () {
    $calculator = new ServiceCostCalculator;

    $subtotal = $calculator->calculateSubtotal(2.5, 100000);
    expect($subtotal)->toBe(250000.0);

    $subtotalInt = $calculator->calculateSubtotal(3, 50000);
    expect($subtotalInt)->toBe(150000.0);
});

test('recalculate updates service total_cost accurately based on service items', function () {
    $user = User::factory()->create();
    $calculator = new ServiceCostCalculator;

    $service = Service::create([
        'ticket_number' => 'SRV-TEST00-9999',
        'customer_id' => $user->id,
        'vehicle_plate_number' => 'B 7777 ABC',
        'vehicle_model' => 'Sportscar',
        'vehicle_year' => 2024,
        'odometer_km' => 12000,
        'complaint_notes' => 'Perawatan rutin ganti oli dan filter.',
        'service_type' => 'major',
        'service_date' => Carbon::now()->addDay(),
        'slot_hour' => 10,
        'status' => ServiceStatus::InProgress,
        'total_cost' => 0,
    ]);

    $service->items()->create([
        'item_type' => ServiceItemType::Part,
        'name' => 'Oli Mesin Full Sintetik 4L',
        'quantity' => 1,
        'unit_price' => 450000,
        'subtotal' => 450000,
        'added_by' => $user->id,
    ]);

    $service->items()->create([
        'item_type' => ServiceItemType::Labor,
        'name' => 'Jasa Tune Up & Ganti Oli',
        'quantity' => 1,
        'unit_price' => 200000,
        'subtotal' => 200000,
        'added_by' => $user->id,
    ]);

    $newTotal = $calculator->recalculate($service);

    expect($newTotal)->toBe(650000.0);
    expect((float) $service->fresh()->total_cost)->toBe(650000.0);
});
