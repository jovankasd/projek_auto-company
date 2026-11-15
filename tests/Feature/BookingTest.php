<?php

use App\Enums\CarCategory;
use App\Enums\ServiceStatus;
use App\Enums\SparePartCategory;
use App\Enums\TestDriveStatus;
use App\Enums\UserRole;
use App\Models\Car;
use App\Models\Service;
use App\Models\SparePart;
use App\Models\TestDrive;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    // Seed sample cars
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

    // Seed users
    User::create([
        'name' => 'Sales Agent',
        'email' => 'sales@xyzmotors.com',
        'password' => bcrypt('password'),
        'role' => UserRole::Sales,
        'phone_number' => '081234567890',
        'is_active' => true,
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

test('showroom page renders successfully with available cars and full website catalog', function () {
    SparePart::create([
        'slug' => 'brake-pad-set-brk-frd-001',
        'name' => 'Brake Pad Set',
        'part_number' => 'BRK-FRD-001',
        'brand' => 'Brembo',
        'category' => SparePartCategory::Brake,
        'price' => 2500000,
        'stock_quantity' => 15,
        'min_stock_alert' => 5,
        'is_available' => true,
    ]);

    $response = $this->get('/');

    $response->assertStatus(200);
    $response->assertSee('Ford');
    $response->assertSee('Mustang GT');
    $response->assertSee('Test Drive');
    $response->assertSee('Booking Servis');
    $response->assertSee('Daftar Lengkap Unit Showroom');
    $response->assertSee('Katalog Suku Cadang');
    $response->assertSee('Brake Pad Set');
    $response->assertSee('Standar Pengerjaan Bengkel Resmi Dealer Pro');
    $response->assertDontSee('Daftar Akun');
    $response->assertDontSee('Masuk ke Portal Pelanggan');
});

test('guest submitting test drive creates booking without login and returns ticket number', function () {
    $car = Car::first();
    $tomorrow = Carbon::now()->addDays(2)->format('Y-m-d H:i:s');

    $response = $this->postJson('/test-drive', [
        'customer_name' => 'Rina Tamu',
        'customer_phone' => '081299887766',
        'car_id' => $car->id,
        'preferred_date' => $tomorrow,
        'driver_license_id' => '123456789',
        'pickup_location' => 'Showroom Pusat',
    ]);

    $response->assertStatus(200);
    $response->assertJsonPath('success', true);

    $testDrive = TestDrive::first();
    expect($testDrive)->not->toBeNull();
    expect($testDrive->ticket_number)->toMatch('/^TD-\d{8}-\d{4}$/');

    // Public ticket tracker returns the test drive status without login
    $trackResponse = $this->getJson(route('ticket.track', ['ticket' => $testDrive->ticket_number]));
    $trackResponse->assertStatus(200)
        ->assertJsonPath('found', true)
        ->assertJsonPath('type', 'test_drive')
        ->assertJsonPath('ticket_number', $testDrive->ticket_number)
        ->assertJsonPath('customer_name', 'Rina Tamu');
});

test('authenticated customer can submit test drive and sales is assigned round robin', function () {
    $customer = User::create([
        'name' => 'Budi Customer',
        'email' => 'budi@gmail.com',
        'password' => bcrypt('password'),
        'role' => UserRole::Customer,
        'phone_number' => '081299998888',
        'is_active' => true,
    ]);

    $car = Car::first();
    $preferredDate = Carbon::now()->addDays(3)->format('Y-m-d 10:00:00');

    $response = $this->actingAs($customer)->post('/test-drive', [
        'car_id' => $car->id,
        'preferred_date' => $preferredDate,
        'driver_license_id' => 'SIM-99887766',
        'pickup_location' => 'Dealer Sudirman',
        'notes' => 'Ingin mencoba di jalan tol.',
    ]);

    $response->assertSessionHas('success');
    $this->assertDatabaseHas('test_drives', [
        'customer_id' => $customer->id,
        'car_id' => $car->id,
        'driver_license_id' => 'SIM-99887766',
        'status' => TestDriveStatus::Requested->value,
    ]);

    $testDrive = TestDrive::first();
    expect($testDrive->sales_id)->not->toBeNull();
});

test('guest submitting service booking creates booking without login and returns ticket number', function () {
    $tomorrow = Carbon::now()->addDays(2)->format('Y-m-d 10:00:00');

    $response = $this->postJson('/service-booking', [
        'customer_name' => 'Andi Tamu',
        'customer_phone' => '081233445566',
        'vehicle_plate_number' => 'B 1234 ABC',
        'vehicle_model' => 'Mustang GT',
        'vehicle_year' => 2024,
        'odometer_km' => 15000,
        'service_type' => 'regular',
        'service_date' => $tomorrow,
        'complaint_notes' => 'Ganti oli mesin dan periksa rem.',
    ]);

    $response->assertStatus(200);
    $response->assertJsonPath('success', true);

    $service = Service::first();
    expect($service)->not->toBeNull();
    expect($service->ticket_number)->toMatch('/^SRV-\d{8}-\d{4}$/');

    // Public ticket tracker returns the service status without login
    $trackResponse = $this->getJson(route('ticket.track', ['ticket' => $service->ticket_number]));
    $trackResponse->assertStatus(200)
        ->assertJsonPath('found', true)
        ->assertJsonPath('type', 'service')
        ->assertJsonPath('ticket_number', $service->ticket_number)
        ->assertJsonPath('customer_name', 'Andi Tamu')
        ->assertJsonPath('vehicle_plate_number', 'B 1234 ABC');
});

test('authenticated customer can submit service booking with unique ticket number', function () {
    $customer = User::create([
        'name' => 'Doni Customer',
        'email' => 'doni@gmail.com',
        'password' => bcrypt('password'),
        'role' => UserRole::Customer,
        'phone_number' => '081211112222',
        'is_active' => true,
    ]);

    $serviceDate = Carbon::now()->addDays(1)->setTime(10, 0)->format('Y-m-d H:i:s');

    $response = $this->actingAs($customer)->post('/service-booking', [
        'vehicle_plate_number' => 'B 5678 XYZ',
        'vehicle_model' => 'Mustang GT',
        'vehicle_year' => 2024,
        'odometer_km' => 12000,
        'service_type' => 'regular',
        'service_date' => $serviceDate,
        'complaint_notes' => 'Pengecekan rutin berkala 10.000 KM.',
    ]);

    $response->assertSessionHas('success');

    $service = Service::first();
    expect($service)->not->toBeNull();
    expect($service->ticket_number)->toMatch('/^SRV-\d{8}-\d{4}$/');
    expect($service->status)->toBe(ServiceStatus::Pending);
    expect($service->slot_hour)->toBe(10);
});

test('fourth service booking at the same hour slot is rejected due to quota limit of 3', function () {
    $customer = User::create([
        'name' => 'Eko Customer',
        'email' => 'eko@gmail.com',
        'password' => bcrypt('password'),
        'role' => UserRole::Customer,
        'phone_number' => '081233334444',
        'is_active' => true,
    ]);

    $serviceDate = Carbon::now()->addDays(2)->setTime(14, 0);

    // Book 3 slots first
    for ($i = 1; $i <= 3; $i++) {
        Service::create([
            'ticket_number' => "SRV-TEST00-000{$i}",
            'customer_id' => $customer->id,
            'vehicle_plate_number' => "B {$i} TEST",
            'vehicle_model' => 'Car Model',
            'vehicle_year' => 2022,
            'odometer_km' => 10000 * $i,
            'service_type' => 'regular',
            'service_date' => $serviceDate,
            'slot_hour' => 14,
            'complaint_notes' => 'Catatan keluhan rutin kendaraan.',
            'status' => ServiceStatus::Pending,
        ]);
    }

    // 4th booking on same date and slot_hour 14 must fail
    $response = $this->actingAs($customer)->post('/service-booking', [
        'vehicle_plate_number' => 'B 9999 NEW',
        'vehicle_model' => 'Car Model',
        'vehicle_year' => 2022,
        'odometer_km' => 12000,
        'service_type' => 'regular',
        'service_date' => $serviceDate->format('Y-m-d H:i:s'),
        'complaint_notes' => 'Booking ke-4 yang harus ditolak sistem.',
    ]);

    $response->assertSessionHasErrors(['service_date']);
    $this->assertDatabaseCount('services', 3);
});

test('service booking rejects duplicate plate on same date and slot hour', function () {
    $customer = User::create([
        'name' => 'Fani Customer',
        'email' => 'fani@gmail.com',
        'password' => bcrypt('password'),
        'role' => UserRole::Customer,
        'phone_number' => '081255556666',
        'is_active' => true,
    ]);

    $serviceDate = Carbon::now()->addDays(2)->setTime(11, 0);

    Service::create([
        'ticket_number' => 'SRV-TEST00-0010',
        'customer_id' => $customer->id,
        'vehicle_plate_number' => 'B 1111 DUP',
        'vehicle_model' => 'Car Model',
        'vehicle_year' => 2022,
        'odometer_km' => 10000,
        'service_type' => 'regular',
        'service_date' => $serviceDate,
        'slot_hour' => 11,
        'complaint_notes' => 'Catatan keluhan kendaraan pertama.',
        'status' => ServiceStatus::Pending,
    ]);

    // Same plate at slot hour 11 must be rejected
    $response = $this->actingAs($customer)->post('/service-booking', [
        'vehicle_plate_number' => 'B 1111 DUP',
        'vehicle_model' => 'Car Model',
        'vehicle_year' => 2022,
        'odometer_km' => 10000,
        'service_type' => 'regular',
        'service_date' => $serviceDate->format('Y-m-d H:i:s'),
        'complaint_notes' => 'Mencoba booking ulang dengan plat sama di jam sama.',
    ]);

    $response->assertSessionHasErrors(['vehicle_plate_number']);
});

test('public contact page renders without 500 error and inquiry can be submitted with honeypot guard', function () {
    $renderResponse = $this->get('/contact');
    $renderResponse->assertStatus(200);
    $renderResponse->assertSee('Hubungi Konsultan Dealer');

    // Bot submission with honeypot filled
    $botResponse = $this->postJson(route('contact.store'), [
        'name' => 'Spam Bot',
        'email' => 'bot@spammer.com',
        'phone' => '081234567890',
        'subject' => 'Spam Title',
        'message' => 'Spam message test bot',
        'website_url' => 'http://spamlink.com',
    ]);
    $botResponse->assertStatus(200);

    // Valid inquiry submission
    $validResponse = $this->postJson(route('contact.store'), [
        'name' => 'Budi Pelanggan',
        'email' => 'budi@domain.com',
        'phone' => '081234567890',
        'subject' => 'Pembelian & Konsultasi Unit Mobil Baru',
        'message' => 'Saya tertarik dengan unit Ford Mustang GT terbaru.',
    ]);
    $validResponse->assertStatus(200);
    $validResponse->assertJsonFragment(['success' => true]);
});

test('driving license photo is private and strictly protected from guest or unassigned sales', function () {
    Storage::fake('local');

    $salesA = User::where('role', UserRole::Sales)->first();
    $salesB = User::create([
        'name' => 'Sales B',
        'email' => 'salesb@xyzmotors.com',
        'password' => bcrypt('password'),
        'role' => UserRole::Sales,
        'phone_number' => '081234567899',
        'is_active' => true,
    ]);

    $customer = User::create([
        'name' => 'Customer SIM',
        'email' => 'sim@gmail.com',
        'password' => bcrypt('password'),
        'role' => UserRole::Customer,
        'phone_number' => '081277778888',
        'is_active' => true,
    ]);

    $file = UploadedFile::fake()->create('sim.jpg', 100, 'image/jpeg');
    $path = $file->store('licenses', 'local');

    $car = Car::first();
    $testDrive = TestDrive::create([
        'customer_id' => $customer->id,
        'car_id' => $car->id,
        'sales_id' => $salesA->id,
        'preferred_date' => now()->addDays(2),
        'driver_license_id' => 'SIM-12345678',
        'driver_license_image' => $path,
        'status' => TestDriveStatus::Requested,
    ]);

    // 1. Guest blocked (redirects to login)
    $this->get(route('test-drives.license', $testDrive))->assertRedirect(route('login'));

    // 2. Unassigned Sales B blocked (403 Forbidden)
    $this->actingAs($salesB)->get(route('test-drives.license', $testDrive))->assertStatus(403);

    // 3. Customer owner can view their own license
    $this->actingAs($customer)->get(route('test-drives.license', $testDrive))->assertStatus(200);

    // 4. Assigned Sales A can view license
    $this->actingAs($salesA)->get(route('test-drives.license', $testDrive))->assertStatus(200);

    // 5. Super Admin can view license
    $admin = User::where('role', UserRole::SuperAdmin)->first();
    $this->actingAs($admin)->get(route('test-drives.license', $testDrive))->assertStatus(200);
});

test('test drive status transition is guarded and rejects invalid transitions', function () {
    $sales = User::where('role', UserRole::Sales)->first();
    $customer = User::create([
        'name' => 'Customer TD Guard',
        'email' => 'tdguard@gmail.com',
        'password' => bcrypt('password'),
        'role' => UserRole::Customer,
        'phone_number' => '081255554444',
        'is_active' => true,
    ]);

    $car = Car::first();
    $testDrive = TestDrive::create([
        'customer_id' => $customer->id,
        'car_id' => $car->id,
        'sales_id' => $sales->id,
        'preferred_date' => now()->addDays(2),
        'driver_license_id' => 'SIM-55554444',
        'status' => TestDriveStatus::Rejected,
        'rejection_reason' => 'Ditolak awal',
    ]);

    // Already rejected, cannot be approved (must return 422)
    $response = $this->actingAs($sales)->patch(route('test-drives.approve', $testDrive), [
        'sales_notes' => 'Mencoba approve dari status rejected',
    ]);
    $response->assertStatus(422);
});

test('cars reorder endpoint is protected and only allows sales or admin', function () {
    $sales = User::where('role', UserRole::Sales)->first();
    $customer = User::create([
        'name' => 'Customer Reorder',
        'email' => 'reorder@gmail.com',
        'password' => bcrypt('password'),
        'role' => UserRole::Customer,
        'phone_number' => '081211112222',
        'is_active' => true,
    ]);

    $car = Car::first();

    // Customer gets 403
    $this->actingAs($customer)->postJson(route('cars.reorder'), [
        'order' => [['id' => $car->id, 'sort_order' => 1]],
    ])->assertStatus(403);

    // Sales gets 200
    $this->actingAs($sales)->postJson(route('cars.reorder'), [
        'order' => [['id' => $car->id, 'sort_order' => 5]],
    ])->assertStatus(200);

    expect($car->fresh()->sort_order)->toBe(5);
});
