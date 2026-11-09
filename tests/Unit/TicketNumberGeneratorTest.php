<?php

use App\Enums\ServiceStatus;
use App\Models\Service;
use App\Models\User;
use App\Services\TicketNumberGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('generates ticket number with valid format SRV-YYYYMMDD-XXXX', function () {
    $generator = app(TicketNumberGenerator::class);
    $ticket = $generator->generate();

    expect($ticket)->toMatch('/^SRV-\d{8}-\d{4}$/');
});

test('generates sequential and unique ticket numbers', function () {
    $generator = app(TicketNumberGenerator::class);
    $user = User::factory()->create();

    $ticket1 = $generator->generate();

    Service::create([
        'ticket_number' => $ticket1,
        'customer_id' => $user->id,
        'vehicle_plate_number' => 'B 1111 TST',
        'vehicle_model' => 'Sedan',
        'vehicle_year' => 2024,
        'odometer_km' => 10000,
        'complaint_notes' => 'Pemeriksaan rem dan ganti oli.',
        'service_type' => 'regular',
        'service_date' => now()->addDay(),
        'slot_hour' => 10,
        'status' => ServiceStatus::Pending,
        'total_cost' => 0,
    ]);

    $ticket2 = $generator->generate();

    expect($ticket1)->not->toBe($ticket2);

    $seq1 = (int) substr($ticket1, -4);
    $seq2 = (int) substr($ticket2, -4);

    expect($seq2)->toBe($seq1 + 1);
});
