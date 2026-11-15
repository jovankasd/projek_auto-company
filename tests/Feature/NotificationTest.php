<?php

use App\Enums\CarCategory;
use App\Enums\ServiceStatus;
use App\Enums\TestDriveStatus;
use App\Enums\UserRole;
use App\Models\Car;
use App\Models\Service;
use App\Models\TestDrive;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

test('user receives database notification on service creation and test drive approval', function () {
    $customer = User::factory()->create([
        'role' => UserRole::Customer,
    ]);

    $sales = User::factory()->create([
        'role' => UserRole::Sales,
    ]);

    $car = Car::create([
        'slug' => 'test-car',
        'brand' => 'Tesla',
        'model_name' => 'Model 3',
        'year' => 2024,
        'category' => CarCategory::Sedan,
        'price' => 800000000,
        'color_name' => 'Pearl White',
        'color_hex' => '#FFFFFF',
        'engine_specs' => ['power' => '300 HP'],
        'hero_image_transparent' => 'images/cars/test.webp',
        'is_available' => true,
    ]);

    // 1. Service Created otomatis memicu observer & listener untuk notifikasi
    $service = Service::create([
        'ticket_number' => 'SRV-20241001-9988',
        'customer_id' => $customer->id,
        'vehicle_plate_number' => 'B 9999 NOTIF',
        'vehicle_model' => 'Tesla Model 3',
        'vehicle_year' => 2024,
        'odometer_km' => 15000,
        'complaint_notes' => 'Keluhan servis berkala.',
        'service_type' => 'regular',
        'service_date' => Carbon::now()->addDays(2),
        'slot_hour' => 10,
        'status' => ServiceStatus::Pending,
        'total_cost' => 0,
    ]);

    expect($customer->unreadNotifications()->count())->toBe(1);

    // 2. Test Drive Approved otomatis memicu observer & listener untuk notifikasi
    $testDrive = TestDrive::create([
        'customer_id' => $customer->id,
        'car_id' => $car->id,
        'sales_id' => $sales->id,
        'preferred_date' => Carbon::now()->addDays(3),
        'driver_license_id' => 'SIM-1234567890',
        'status' => TestDriveStatus::Approved,
    ]);

    expect($customer->unreadNotifications()->count())->toBe(2);
});

test('notification center index renders notifications and filter works', function () {
    $customer = User::factory()->create([
        'role' => UserRole::Customer,
    ]);

    $service = Service::create([
        'ticket_number' => 'SRV-20241001-7766',
        'customer_id' => $customer->id,
        'vehicle_plate_number' => 'B 7766 TEST',
        'vehicle_model' => 'Sedan',
        'vehicle_year' => 2024,
        'odometer_km' => 15000,
        'complaint_notes' => 'Keluhan servis berkala.',
        'service_type' => 'regular',
        'service_date' => Carbon::now()->addDays(2),
        'slot_hour' => 10,
        'status' => ServiceStatus::Pending,
        'total_cost' => 0,
    ]);

    $response = $this->actingAs($customer)->get(route('notifications.index'));
    $response->assertStatus(200);
    $response->assertSee('Pusat Pemberitahuan');
    $response->assertSee('SRV-20241001-7766');

    // Filter unread
    $responseUnread = $this->actingAs($customer)->get(route('notifications.index', ['filter' => 'unread']));
    $responseUnread->assertStatus(200);
    $responseUnread->assertSee('SRV-20241001-7766');
});

test('user can mark single notification as read', function () {
    $customer = User::factory()->create([
        'role' => UserRole::Customer,
    ]);

    $service = Service::create([
        'ticket_number' => 'SRV-20241001-5544',
        'customer_id' => $customer->id,
        'vehicle_plate_number' => 'B 5544 TEST',
        'vehicle_model' => 'Sedan',
        'vehicle_year' => 2024,
        'odometer_km' => 15000,
        'complaint_notes' => 'Keluhan servis berkala.',
        'service_type' => 'regular',
        'service_date' => Carbon::now()->addDays(2),
        'slot_hour' => 10,
        'status' => ServiceStatus::Pending,
        'total_cost' => 0,
    ]);

    $notification = $customer->unreadNotifications()->first();
    expect($notification)->not->toBeNull();

    $response = $this->actingAs($customer)->patchJson(route('notifications.read', $notification->id));
    $response->assertStatus(200);
    $response->assertJson(['success' => true, 'unread_count' => 0]);

    expect($customer->fresh()->unreadNotifications()->count())->toBe(0);
});

test('user can mark all notifications as read', function () {
    $customer = User::factory()->create([
        'role' => UserRole::Customer,
    ]);

    Service::create([
        'ticket_number' => 'SRV-20241001-3321',
        'customer_id' => $customer->id,
        'vehicle_plate_number' => 'B 3321 TEST',
        'vehicle_model' => 'Sedan',
        'vehicle_year' => 2024,
        'odometer_km' => 15000,
        'complaint_notes' => 'Servis berkala 1.',
        'service_type' => 'regular',
        'service_date' => Carbon::now()->addDays(2),
        'slot_hour' => 10,
        'status' => ServiceStatus::Pending,
        'total_cost' => 0,
    ]);

    Service::create([
        'ticket_number' => 'SRV-20241001-3322',
        'customer_id' => $customer->id,
        'vehicle_plate_number' => 'B 3322 TEST',
        'vehicle_model' => 'SUV',
        'vehicle_year' => 2024,
        'odometer_km' => 20000,
        'complaint_notes' => 'Servis berkala 2.',
        'service_type' => 'regular',
        'service_date' => Carbon::now()->addDays(3),
        'slot_hour' => 11,
        'status' => ServiceStatus::Pending,
        'total_cost' => 0,
    ]);

    expect($customer->unreadNotifications()->count())->toBe(2);

    $response = $this->actingAs($customer)->postJson(route('notifications.mark-all-read'));
    $response->assertStatus(200);
    $response->assertJson(['success' => true, 'unread_count' => 0]);

    expect($customer->fresh()->unreadNotifications()->count())->toBe(0);
});

test('unread count endpoint returns accurate count and recent items for topbar polling', function () {
    $customer = User::factory()->create([
        'role' => UserRole::Customer,
    ]);

    $service = Service::create([
        'ticket_number' => 'SRV-20241001-1100',
        'customer_id' => $customer->id,
        'vehicle_plate_number' => 'B 1100 TEST',
        'vehicle_model' => 'Sedan',
        'vehicle_year' => 2024,
        'odometer_km' => 15000,
        'complaint_notes' => 'Keluhan servis berkala.',
        'service_type' => 'regular',
        'service_date' => Carbon::now()->addDays(2),
        'slot_hour' => 10,
        'status' => ServiceStatus::Pending,
        'total_cost' => 0,
    ]);

    $response = $this->actingAs($customer)->getJson(route('notifications.unread-count'));
    $response->assertStatus(200);
    $response->assertJsonStructure(['count', 'recent' => [['id', 'title', 'message', 'url', 'read_url', 'created_at_human']]]);
    expect($response->json('count'))->toBe(1);
    expect($response->json('recent'))->toHaveCount(1);
    expect($response->json('recent.0.read_url'))->toContain('/dashboard/notifications/'.$customer->unreadNotifications()->first()->id.'/read');
});

test('owner can click notification GET read link for executive report and land on valid 200 target page', function () {
    $owner = User::factory()->create(['role' => UserRole::Owner]);
    $admin = User::factory()->create(['role' => UserRole::SuperAdmin]);

    $this->actingAs($admin)->post(route('reports.submit-to-owner'), [
        'note' => 'Laporan bulanan siap ditinjau.',
    ])->assertRedirect();

    $notification = $owner->unreadNotifications()->first();
    expect($notification)->not->toBeNull();

    $response = $this->actingAs($owner)->get(route('notifications.read', $notification->id));
    $response->assertRedirect(route('dashboard', [], false));

    expect($owner->fresh()->unreadNotifications()->count())->toBe(0);
    $this->actingAs($owner)->get(route('dashboard'))->assertStatus(200);
    $this->actingAs($owner)->get(route('reports.index'))->assertStatus(200);
});

test('notification read normalizes legacy test-drive url safely and blocks external open redirects', function () {
    $customer = User::factory()->create(['role' => UserRole::Customer]);
    $sales = User::factory()->create(['role' => UserRole::Sales]);
    $car = Car::create([
        'slug' => 'notif-sedan',
        'brand' => 'Honda',
        'model_name' => 'Civic Type R',
        'year' => 2024,
        'category' => CarCategory::Sedan,
        'price' => 900000000,
        'color_name' => 'Championship White',
        'color_hex' => '#FFFFFF',
        'engine_specs' => ['power' => '315 HP'],
        'hero_image_transparent' => 'images/cars/test.webp',
        'is_available' => true,
    ]);

    $testDrive = TestDrive::create([
        'customer_id' => $customer->id,
        'car_id' => $car->id,
        'sales_id' => $sales->id,
        'preferred_date' => Carbon::now()->addDays(2),
        'driver_license_id' => 'SIM-0987654321',
        'status' => TestDriveStatus::Requested,
    ]);

    // Notification pointing to legacy /dashboard/test-drives/{id}
    $legacyId = (string) Str::uuid();
    $customer->notifications()->create([
        'id' => $legacyId,
        'type' => 'App\\Notifications\\TestDriveApproved',
        'data' => [
            'title' => 'Test Drive Legacy URL',
            'message' => 'Menguji normalisasi link lama.',
            'url' => '/dashboard/test-drives/'.$testDrive->id,
        ],
    ]);

    $this->actingAs($customer)
        ->get(route('notifications.read', $legacyId))
        ->assertRedirect(route('test-drives.my', [], false));

    // Malicious external redirect attempt
    $evilId = (string) Str::uuid();
    $customer->notifications()->create([
        'id' => $evilId,
        'type' => 'App\\Notifications\\TestDriveApproved',
        'data' => [
            'title' => 'Open Redirect Probe',
            'message' => 'Menguji keamanan redirect.',
            'url' => 'https://evil.example.com/phish',
        ],
    ]);

    $this->actingAs($customer)
        ->get(route('notifications.read', $evilId))
        ->assertRedirect(route('notifications.index', [], false));
});
