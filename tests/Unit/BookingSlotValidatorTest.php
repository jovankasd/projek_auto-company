<?php

use App\Enums\ServiceStatus;
use App\Models\Service;
use App\Models\User;
use App\Services\BookingSlotValidator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

test('slot is available when bookings count is less than 3', function () {
    $validator = app(BookingSlotValidator::class);
    $date = Carbon::tomorrow()->toDateString();
    $hour = 10;

    expect($validator->isSlotAvailable($date, $hour))->toBeTrue();
    expect($validator->getRemainingQuota($date, $hour))->toBe(3);
});

test('slot becomes unavailable when 3 active bookings exist in the same hour', function () {
    $user = User::factory()->create();
    $validator = app(BookingSlotValidator::class);
    $date = Carbon::tomorrow()->toDateString();
    $hour = 14;

    for ($i = 1; $i <= 3; $i++) {
        Service::create([
            'ticket_number' => "SRV-TEST00-00{$i}",
            'customer_id' => $user->id,
            'vehicle_plate_number' => "B {$i} TST",
            'vehicle_model' => 'Sedan',
            'vehicle_year' => 2022,
            'odometer_km' => 15000,
            'complaint_notes' => 'Pemeriksaan rutin kendaraan.',
            'service_type' => 'regular',
            'service_date' => Carbon::parse($date)->setTime($hour, 0),
            'slot_hour' => $hour,
            'status' => ServiceStatus::Pending,
            'total_cost' => 0,
        ]);
    }

    expect($validator->isSlotAvailable($date, $hour))->toBeFalse();
    expect($validator->getRemainingQuota($date, $hour))->toBe(0);
});

test('plate availability check rejects duplicate plate on same date and hour slot', function () {
    $user = User::factory()->create();
    $validator = app(BookingSlotValidator::class);
    $date = Carbon::tomorrow()->toDateString();
    $hour = 11;
    $plate = 'B 1234 XYZ';

    Service::create([
        'ticket_number' => 'SRV-TEST00-0010',
        'customer_id' => $user->id,
        'vehicle_plate_number' => $plate,
        'vehicle_model' => 'SUV',
        'vehicle_year' => 2023,
        'odometer_km' => 12000,
        'complaint_notes' => 'Pemeriksaan rem dan ganti oli.',
        'service_type' => 'regular',
        'service_date' => Carbon::parse($date)->setTime($hour, 0),
        'slot_hour' => $hour,
        'status' => ServiceStatus::Pending,
        'total_cost' => 0,
    ]);

    expect($validator->isPlateAvailable($date, $hour, $plate))->toBeFalse();
    expect($validator->isPlateAvailable($date, $hour, 'B 9999 NEW'))->toBeTrue();
});
