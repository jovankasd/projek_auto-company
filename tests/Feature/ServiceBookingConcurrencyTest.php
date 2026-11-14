<?php

use App\Enums\CarCategory;
use App\Enums\ServiceStatus;
use App\Enums\UserRole;
use App\Models\Car;
use App\Models\Service;
use App\Models\User;
use App\Services\BookingSlotValidator;
use App\Services\TicketNumberGenerator;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;

beforeEach(function () {
    Car::create([
        'slug' => 'mustang-gt',
        'brand' => 'Ford',
        'model_name' => 'Mustang GT',
        'year' => 2024,
        'category' => CarCategory::Sports,
        'color_name' => 'Race Red',
        'color_hex' => '#C41E3A',
        'price' => 980000000,
        'engine_specs' => ['power' => '450 HP', 'torque' => '529 Nm', 'accel' => '4.3s', 'trans' => '8AT'],
        'hero_image_transparent' => 'images/cars/ford-mustang-gt-2024.webp',
        'is_available' => true,
        'sort_order' => 1,
    ]);

    User::create([
        'name' => 'Super Admin',
        'email' => 'admin@xyzmotors.com',
        'password' => bcrypt('password'),
        'role' => UserRole::SuperAdmin,
        'phone_number' => '081234567891',
        'is_active' => true,
    ]);
});

test('fourth service booking at the same slot hour is rejected due to quota limit of 3', function () {
    $customer = User::create([
        'name' => 'Joko Customer',
        'email' => 'joko@gmail.com',
        'password' => bcrypt('password'),
        'role' => UserRole::Customer,
        'phone_number' => '081211110001',
        'is_active' => true,
    ]);

    $bookingDate = Carbon::now()->addDays(3)->setTime(10, 0);

    // Book 3 slots
    for ($i = 1; $i <= 3; $i++) {
        $response = $this->actingAs($customer)->post('/service-booking', [
            'vehicle_plate_number' => "B 100{$i} TST",
            'vehicle_model' => 'Ford Mustang GT',
            'vehicle_year' => 2024,
            'odometer_km' => 10000,
            'service_type' => 'regular',
            'service_date' => $bookingDate->format('Y-m-d H:i:s'),
            'slot_hour' => 10,
            'complaint_notes' => 'Servis berkala ke-'.$i,
        ]);
        $response->assertSessionHas('success');
    }

    expect(Service::where('slot_key', $bookingDate->toDateString().':10')->count())->toBe(3);

    // Attempt 4th booking in the same slot
    $response4 = $this->actingAs($customer)->post('/service-booking', [
        'vehicle_plate_number' => 'B 9999 NEW',
        'vehicle_model' => 'Ford Mustang GT',
        'vehicle_year' => 2024,
        'odometer_km' => 10000,
        'service_type' => 'regular',
        'service_date' => $bookingDate->format('Y-m-d H:i:s'),
        'slot_hour' => 10,
        'complaint_notes' => 'Percobaan booking ke-4 yang harus gagal',
    ]);

    $response4->assertSessionHasErrors(['service_date']);
    expect(Service::where('slot_key', $bookingDate->toDateString().':10')->count())->toBe(3);
});

test('duplicate plate on the same date and slot hour with different minutes is strictly rejected', function () {
    $customer = User::create([
        'name' => 'Agus Customer',
        'email' => 'agus@gmail.com',
        'password' => bcrypt('password'),
        'role' => UserRole::Customer,
        'phone_number' => '081211110002',
        'is_active' => true,
    ]);

    $day = Carbon::now()->addDays(2)->toDateString();
    $minute15 = "{$day} 11:15:00";
    $minute45 = "{$day} 11:45:00";
    $plate = 'B 8888 PLT';

    // First booking at 11:15
    $resp1 = $this->actingAs($customer)->post('/service-booking', [
        'vehicle_plate_number' => $plate,
        'vehicle_model' => 'Ford Mustang GT',
        'vehicle_year' => 2024,
        'odometer_km' => 12000,
        'service_type' => 'regular',
        'service_date' => $minute15,
        'slot_hour' => 11,
        'complaint_notes' => 'Pemeriksaan rutin pertama jam 11:15',
    ]);
    $resp1->assertSessionHas('success');

    // Second booking with same plate at 11:45 (different minutes, same slot hour 11)
    $resp2 = $this->actingAs($customer)->post('/service-booking', [
        'vehicle_plate_number' => 'b  8888  plt', // also tests case and whitespace tolerance
        'vehicle_model' => 'Ford Mustang GT',
        'vehicle_year' => 2024,
        'odometer_km' => 12000,
        'service_type' => 'regular',
        'service_date' => $minute45,
        'slot_hour' => 11,
        'complaint_notes' => 'Pemeriksaan rutin kedua jam 11:45 harus ditolak',
    ]);

    $resp2->assertSessionHasErrors(['vehicle_plate_number']);
    expect(Service::where('slot_key', "{$day}:11")->count())->toBe(1);
});

test('same plate is allowed on different slot hours on the same date', function () {
    $customer = User::create([
        'name' => 'Dewi Customer',
        'email' => 'dewi@gmail.com',
        'password' => bcrypt('password'),
        'role' => UserRole::Customer,
        'phone_number' => '081211110003',
        'is_active' => true,
    ]);

    $day = Carbon::now()->addDays(4)->toDateString();
    $plate = 'B 7777 DIF';

    // Morning slot at 09:00
    $resp1 = $this->actingAs($customer)->post('/service-booking', [
        'vehicle_plate_number' => $plate,
        'vehicle_model' => 'Ford Mustang GT',
        'vehicle_year' => 2024,
        'odometer_km' => 5000,
        'service_type' => 'inspection',
        'service_date' => "{$day} 09:00:00",
        'slot_hour' => 9,
        'complaint_notes' => 'Inspeksi pagi hari jam 09:00',
    ]);
    $resp1->assertSessionHas('success');

    // Afternoon slot at 14:00
    $resp2 = $this->actingAs($customer)->post('/service-booking', [
        'vehicle_plate_number' => $plate,
        'vehicle_model' => 'Ford Mustang GT',
        'vehicle_year' => 2024,
        'odometer_km' => 5000,
        'service_type' => 'regular',
        'service_date' => "{$day} 14:00:00",
        'slot_hour' => 14,
        'complaint_notes' => 'Servis berkala siang hari jam 14:00',
    ]);
    $resp2->assertSessionHas('success');

    $services = Service::where('vehicle_plate_number', $plate)->get();
    expect($services)->toHaveCount(2);
    expect($services[0]->ticket_number)->not->toBe($services[1]->ticket_number);
});

test('cancelled booking releases slot quota and frees vehicle plate for rebooking', function () {
    $customer = User::create([
        'name' => 'Rina Customer',
        'email' => 'rina@gmail.com',
        'password' => bcrypt('password'),
        'role' => UserRole::Customer,
        'phone_number' => '081211110004',
        'is_active' => true,
    ]);

    $day = Carbon::now()->addDays(2)->toDateString();
    $plate = 'B 5555 CAN';

    $resp1 = $this->actingAs($customer)->post('/service-booking', [
        'vehicle_plate_number' => $plate,
        'vehicle_model' => 'Ford Mustang GT',
        'vehicle_year' => 2024,
        'odometer_km' => 8000,
        'service_type' => 'regular',
        'service_date' => "{$day} 13:00:00",
        'slot_hour' => 13,
        'complaint_notes' => 'Booking awal yang nantinya dibatalkan',
    ]);
    $resp1->assertSessionHas('success');

    $service = Service::where('vehicle_plate_number', $plate)->first();
    expect($service->active_plate_slot)->not->toBeNull();

    // Cancel the service
    $service->status = ServiceStatus::Cancelled;
    $service->cancelled_at = now();
    $service->cancellation_reason = 'Perubahan rencana pelanggan';
    $service->save();

    expect($service->fresh()->active_plate_slot)->toBeNull();

    // Re-booking with the same plate in the same slot should now succeed
    $resp2 = $this->actingAs($customer)->post('/service-booking', [
        'vehicle_plate_number' => $plate,
        'vehicle_model' => 'Ford Mustang GT',
        'vehicle_year' => 2024,
        'odometer_km' => 8000,
        'service_type' => 'regular',
        'service_date' => "{$day} 13:30:00",
        'slot_hour' => 13,
        'complaint_notes' => 'Booking kembali setelah pembatalan sebelumnya',
    ]);
    $resp2->assertSessionHas('success');

    $activeServices = Service::where('vehicle_plate_number', $plate)->where('status', ServiceStatus::Pending)->get();
    expect($activeServices)->toHaveCount(1);
    expect($activeServices->first()->active_plate_slot)->toBe("{$day}:13:B5555CAN");
});

test('database level unique constraint on active_plate_slot prevents duplicate entry bypass', function () {
    $customer = User::create([
        'name' => 'Hadi Customer',
        'email' => 'hadi@gmail.com',
        'password' => bcrypt('password'),
        'role' => UserRole::Customer,
        'phone_number' => '081211110005',
        'is_active' => true,
    ]);

    $day = Carbon::now()->addDays(5)->toDateString();
    $slotKey = "{$day}:15";
    $plate = 'B 1111 RAW';

    Service::create([
        'ticket_number' => 'SRV-TEST00-0101',
        'customer_id' => $customer->id,
        'vehicle_plate_number' => $plate,
        'vehicle_model' => 'Ford Mustang GT',
        'vehicle_year' => 2024,
        'odometer_km' => 15000,
        'service_type' => 'regular',
        'service_date' => "{$day} 15:00:00",
        'slot_hour' => 15,
        'complaint_notes' => 'Booking pertama langsung ke database',
        'status' => ServiceStatus::Pending,
    ]);

    expect(function () use ($customer, $day, $plate) {
        Service::create([
            'ticket_number' => 'SRV-TEST00-0102',
            'customer_id' => $customer->id,
            'vehicle_plate_number' => $plate,
            'vehicle_model' => 'Ford Mustang GT',
            'vehicle_year' => 2024,
            'odometer_km' => 15000,
            'service_type' => 'regular',
            'service_date' => "{$day} 15:30:00",
            'slot_hour' => 15,
            'complaint_notes' => 'Booking kedua langsung ke database dengan plate sama di slot sama',
            'status' => ServiceStatus::Pending,
        ]);
    })->toThrow(QueryException::class);
});

test('simulated concurrent booking on the last remaining slot permits exactly one winner and rejects the other', function () {
    $customer = User::create([
        'name' => 'Siti Customer',
        'email' => 'siti@gmail.com',
        'password' => bcrypt('password'),
        'role' => UserRole::Customer,
        'phone_number' => '081211110006',
        'is_active' => true,
    ]);

    $day = Carbon::now()->addDays(6)->toDateString();
    $slotHour = 16;
    $slotKey = "{$day}:{$slotHour}";

    // Seed 2 initial bookings so remaining quota is exactly 1
    for ($i = 1; $i <= 2; $i++) {
        Service::create([
            'ticket_number' => "SRV-TEST00-020{$i}",
            'customer_id' => $customer->id,
            'vehicle_plate_number' => "B 200{$i} PRE",
            'vehicle_model' => 'Ford Mustang GT',
            'vehicle_year' => 2024,
            'odometer_km' => 10000,
            'service_type' => 'regular',
            'service_date' => "{$day} 16:00:00",
            'slot_hour' => $slotHour,
            'complaint_notes' => 'Pre-booked slot',
            'status' => ServiceStatus::Pending,
        ]);
    }

    $validator = app(BookingSlotValidator::class);
    expect($validator->getRemainingQuota("{$day} 16:00:00", $slotHour))->toBe(1);

    // Simulate concurrent execution:
    // Request 1 takes the slot and commits.
    // Request 2 evaluates quota under the same slot mutex and is rejected.
    $results = [];

    // Worker 1
    $response1 = $this->actingAs($customer)->postJson('/service-booking', [
        'vehicle_plate_number' => 'B 3001 WIN',
        'vehicle_model' => 'Ford Mustang GT',
        'vehicle_year' => 2024,
        'odometer_km' => 10000,
        'service_type' => 'regular',
        'service_date' => "{$day} 16:10:00",
        'slot_hour' => $slotHour,
        'complaint_notes' => 'Calon booking pemenang slot terakhir',
    ]);
    $results[] = $response1->status();

    // Worker 2 tries right after
    $response2 = $this->actingAs($customer)->postJson('/service-booking', [
        'vehicle_plate_number' => 'B 3002 LOS',
        'vehicle_model' => 'Ford Mustang GT',
        'vehicle_year' => 2024,
        'odometer_km' => 10000,
        'service_type' => 'regular',
        'service_date' => "{$day} 16:20:00",
        'slot_hour' => $slotHour,
        'complaint_notes' => 'Calon booking yang melebihi kuota 3',
    ]);
    $results[] = $response2->status();

    expect($results)->toContain(200);
    expect($results)->toContain(422);

    // Assert database has exactly 3 bookings, never 4
    expect(Service::where('slot_key', $slotKey)->count())->toBe(3);
});

test('ticket number generator produces sequential and monotonic values under load', function () {
    $generator = app(TicketNumberGenerator::class);
    $customer = User::create([
        'name' => 'Ferry Customer',
        'email' => 'ferry@gmail.com',
        'password' => bcrypt('password'),
        'role' => UserRole::Customer,
        'phone_number' => '081211110007',
        'is_active' => true,
    ]);

    $day = Carbon::now()->addDays(7)->toDateString();
    $generatedTickets = [];

    for ($i = 1; $i <= 3; $i++) {
        $resp = $this->actingAs($customer)->postJson('/service-booking', [
            'vehicle_plate_number' => "B 400{$i} SEQ",
            'vehicle_model' => 'Ford Mustang GT',
            'vehicle_year' => 2024,
            'odometer_km' => 10000,
            'service_type' => 'regular',
            'service_date' => "{$day} ".sprintf('%02d:00:00', 8 + $i),
            'slot_hour' => 8 + $i,
            'complaint_notes' => 'Testing sequential ticket numbers',
        ]);

        $resp->assertStatus(200);
        $ticket = $resp->json('data.ticket_number');
        expect($ticket)->toMatch('/^SRV-\d{8}-\d{4}$/');
        $generatedTickets[] = $ticket;
    }

    expect(count(array_unique($generatedTickets)))->toBe(3);

    $seqNumbers = array_map(fn ($t) => (int) substr($t, -4), $generatedTickets);
    expect($seqNumbers[1])->toBe($seqNumbers[0] + 1);
    expect($seqNumbers[2])->toBe($seqNumbers[1] + 1);
});

test('plate matching rejects input variance without spaces such as B1234XYZ vs B 1234 XYZ', function () {
    $customer = User::create([
        'name' => 'Kiki Customer',
        'email' => 'kiki@gmail.com',
        'password' => bcrypt('password'),
        'role' => UserRole::Customer,
        'phone_number' => '081211110008',
        'is_active' => true,
    ]);

    $day = Carbon::now()->addDays(3)->toDateString();

    // First booking with standard spacing
    $resp1 = $this->actingAs($customer)->post('/service-booking', [
        'vehicle_plate_number' => 'B 1234 XYZ',
        'vehicle_model' => 'Ford Mustang GT',
        'vehicle_year' => 2024,
        'odometer_km' => 10000,
        'service_type' => 'regular',
        'service_date' => "{$day} 10:00:00",
        'slot_hour' => 10,
        'complaint_notes' => 'Booking awal dengan format plat spasi normal',
    ]);
    $resp1->assertSessionHas('success');

    // Second booking attempting bypass without any spaces
    $resp2 = $this->actingAs($customer)->post('/service-booking', [
        'vehicle_plate_number' => 'B1234XYZ',
        'vehicle_model' => 'Ford Mustang GT',
        'vehicle_year' => 2024,
        'odometer_km' => 10000,
        'service_type' => 'regular',
        'service_date' => "{$day} 10:20:00",
        'slot_hour' => 10,
        'complaint_notes' => 'Mencoba bypass duplicate check tanpa spasi',
    ]);

    $resp2->assertSessionHasErrors(['vehicle_plate_number']);
    expect(Service::where('slot_key', "{$day}:10")->count())->toBe(1);
});

test('soft deleting service releases active_plate_slot and restoring it reclaims slot', function () {
    $customer = User::create([
        'name' => 'Lutfi Customer',
        'email' => 'lutfi@gmail.com',
        'password' => bcrypt('password'),
        'role' => UserRole::Customer,
        'phone_number' => '081211110009',
        'is_active' => true,
    ]);

    $day = Carbon::now()->addDays(5)->toDateString();
    $plate = 'B 6666 DEL';

    $resp1 = $this->actingAs($customer)->post('/service-booking', [
        'vehicle_plate_number' => $plate,
        'vehicle_model' => 'Ford Mustang GT',
        'vehicle_year' => 2024,
        'odometer_km' => 10000,
        'service_type' => 'regular',
        'service_date' => "{$day} 10:00:00",
        'slot_hour' => 10,
        'complaint_notes' => 'Servis yang akan di-soft-delete',
    ]);
    $resp1->assertSessionHas('success');

    $service = Service::where('vehicle_plate_number', 'B 6666 DEL')->first();
    expect($service->active_plate_slot)->toBe("{$day}:10:B6666DEL");

    // Soft delete releases the active plate slot
    $service->delete();
    expect($service->fresh()->active_plate_slot)->toBeNull();

    // Plate can now be booked in the same slot
    $resp2 = $this->actingAs($customer)->post('/service-booking', [
        'vehicle_plate_number' => $plate,
        'vehicle_model' => 'Ford Mustang GT',
        'vehicle_year' => 2024,
        'odometer_km' => 10000,
        'service_type' => 'regular',
        'service_date' => "{$day} 10:30:00",
        'slot_hour' => 10,
        'complaint_notes' => 'Booking baru setelah servis lama dihapus',
    ]);
    $resp2->assertSessionHas('success');

    $newService = Service::where('vehicle_plate_number', 'B 6666 DEL')->whereNull('deleted_at')->first();
    expect($newService->active_plate_slot)->toBe("{$day}:10:B6666DEL");

    // Delete the new service, then restore original service
    $newService->delete();
    $service->restore();

    expect($service->fresh()->active_plate_slot)->toBe("{$day}:10:B6666DEL");
});
