<?php

use App\Enums\CarCategory;
use App\Enums\DiscountType;
use App\Enums\ServiceStatus;
use App\Enums\SparePartCategory;
use App\Enums\TestDriveStatus;
use App\Enums\UserRole;
use App\Models\Car;
use App\Models\Promo;
use App\Models\Service;
use App\Models\SparePart;
use App\Models\TestDrive;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->owner = User::factory()->create(['role' => UserRole::Owner]);
    $this->admin = User::factory()->create(['role' => UserRole::SuperAdmin]);
    $this->sales = User::factory()->create(['role' => UserRole::Sales]);
    $this->mechanic = User::factory()->create(['role' => UserRole::Mekanik]);
    $this->customer = User::factory()->create(['role' => UserRole::Customer]);

    $this->car = Car::create([
        'slug' => 'audit-mustang-gt',
        'brand' => 'Ford',
        'model_name' => 'Mustang GT',
        'year' => 2024,
        'category' => CarCategory::Sports,
        'color_name' => 'Race Red',
        'color_hex' => '#C41E3A',
        'price' => 980000000,
        'stock_quantity' => 3,
        'engine_specs' => ['power' => '450 HP', 'torque' => '529 Nm', 'trans' => '8AT'],
        'hero_image_transparent' => 'images/cars/ford-mustang-gt-2024.webp',
        'is_available' => true,
        'sort_order' => 1,
    ]);

    $this->sparePart = SparePart::create([
        'slug' => 'audit-brake-pad-brk-001',
        'name' => 'Brake Pad Audit',
        'part_number' => 'BRK-AUD-001',
        'brand' => 'Brembo',
        'category' => SparePartCategory::Brake,
        'price' => 1500000,
        'stock_quantity' => 10,
        'min_stock_alert' => 2,
        'is_available' => true,
    ]);

    $this->promo = Promo::create([
        'code' => 'AUDIT10',
        'title' => 'Promo Audit 10%',
        'discount_type' => DiscountType::Percentage,
        'discount_value' => 10,
        'car_id' => $this->car->id,
        'starts_at' => Carbon::now()->subDay(),
        'ends_at' => Carbon::now()->addDays(10),
        'is_active' => true,
    ]);

    $this->service = Service::create([
        'ticket_number' => 'SRV-20261115-0101',
        'customer_id' => $this->customer->id,
        'assigned_mechanic_id' => $this->mechanic->id,
        'vehicle_plate_number' => 'B 101 AUD',
        'vehicle_model' => 'Ford Mustang GT',
        'vehicle_year' => 2024,
        'odometer_km' => 12000,
        'complaint_notes' => 'Pengecekan rutin berkala.',
        'service_type' => 'regular',
        'service_date' => Carbon::now()->addDays(2),
        'slot_hour' => 10,
        'status' => ServiceStatus::Pending,
        'total_cost' => 0,
    ]);

    $this->testDrive = TestDrive::create([
        'customer_id' => $this->customer->id,
        'car_id' => $this->car->id,
        'sales_id' => $this->sales->id,
        'preferred_date' => Carbon::now()->addDays(3),
        'driver_license_id' => 'SIM-AUD-001',
        'pickup_location' => 'Showroom Pusat',
        'status' => TestDriveStatus::Requested,
    ]);
});

test('all five roles can open every permitted dashboard and resource route with HTTP 200', function () {
    // Owner
    $this->actingAs($this->owner)->get(route('dashboard'))->assertStatus(200);
    $this->actingAs($this->owner)->get(route('reports.index'))->assertStatus(200);
    $this->actingAs($this->owner)->get(route('notifications.index'))->assertStatus(200);
    $this->actingAs($this->owner)->get(route('services.show', $this->service))->assertStatus(200);
    $this->actingAs($this->owner)->get(route('services.track', $this->service))->assertStatus(200);
    $this->actingAs($this->owner)->get(route('profile.edit'))->assertStatus(200);

    // Super Admin
    foreach ([
        route('dashboard'),
        route('cars.index'),
        route('cars.create'),
        route('cars.show', $this->car),
        route('cars.edit', $this->car),
        route('spare-parts.index'),
        route('spare-parts.create'),
        route('spare-parts.show', $this->sparePart),
        route('spare-parts.edit', $this->sparePart),
        route('promos.index'),
        route('promos.create'),
        route('promos.edit', $this->promo),
        route('users.index'),
        route('users.create'),
        route('settings.edit'),
        route('audit.index'),
        route('reports.index'),
        route('services.show', $this->service),
        route('notifications.index'),
    ] as $url) {
        $this->actingAs($this->admin)->get($url)->assertStatus(200);
    }

    // Sales
    foreach ([
        route('dashboard'),
        route('cars.index'),
        route('cars.show', $this->car),
        route('promos.index'),
        route('spare-parts.index'),
        route('notifications.index'),
    ] as $url) {
        $this->actingAs($this->sales)->get($url)->assertStatus(200);
    }

    // Mekanik
    foreach ([
        route('dashboard'),
        route('spare-parts.index'),
        route('spare-parts.show', $this->sparePart),
        route('services.show', $this->service),
        route('services.track', $this->service),
        route('notifications.index'),
    ] as $url) {
        $this->actingAs($this->mechanic)->get($url)->assertStatus(200);
    }

    // Customer
    foreach ([
        route('dashboard'),
        route('services.my'),
        route('services.show', $this->service),
        route('services.track', $this->service),
        route('test-drives.my'),
        route('notifications.index'),
        route('profile.edit'),
    ] as $url) {
        $this->actingAs($this->customer)->get($url)->assertStatus(200);
    }
});

test('sales can complete approved test drive and customer can cancel pending service or test drive', function () {
    // Customer cannot open customer-only routes as staff, and staff cannot open customer-only routes
    $this->actingAs($this->sales)->get(route('services.my'))->assertStatus(403);
    $this->actingAs($this->mechanic)->get(route('test-drives.my'))->assertStatus(403);

    // Approve then complete test drive
    $this->actingAs($this->sales)->patch(route('test-drives.approve', $this->testDrive))->assertRedirect();
    $this->actingAs($this->sales)->patch(route('test-drives.complete', $this->testDrive), [
        'sales_notes' => 'Pelanggan selesai uji coba 30 menit.',
    ])->assertRedirect();

    expect($this->testDrive->fresh()->status)->toBe(TestDriveStatus::Completed);
    expect($this->testDrive->fresh()->completed_at)->not->toBeNull();

    // Customer cancels another requested test drive -> notifies Sales with customer-cancellation notice
    $tdToCancel = TestDrive::create([
        'customer_id' => $this->customer->id,
        'car_id' => $this->car->id,
        'sales_id' => $this->sales->id,
        'preferred_date' => Carbon::now()->addDays(4),
        'driver_license_id' => 'SIM-AUD-002',
        'status' => TestDriveStatus::Requested,
    ]);

    $this->actingAs($this->customer)->patch(route('test-drives.cancel', $tdToCancel))->assertRedirect();
    expect($tdToCancel->fresh()->status)->toBe(TestDriveStatus::Cancelled);
    expect($this->sales->unreadNotifications()->first()?->data['title'])->toBe('Pengajuan Test Drive Dibatalkan');

    // Customer cancels own pending service
    $this->actingAs($this->customer)->patch(route('services.cancel', $this->service), [
        'cancellation_reason' => 'Berhalangan hadir.',
    ])->assertRedirect();
    expect($this->service->fresh()->status)->toBe(ServiceStatus::Cancelled);
});

test('promo soft delete can be restored by sales and setting controller stores numeric contact whatsapp as string', function () {
    $promoId = $this->promo->id;
    $this->actingAs($this->sales)->delete(route('promos.destroy', $this->promo))->assertRedirect();
    expect(Promo::find($promoId))->toBeNull();

    $this->actingAs($this->sales)->patch(route('promos.restore', $promoId))->assertRedirect();
    expect(Promo::find($promoId))->not->toBeNull();

    $this->actingAs($this->admin)->patch(route('settings.update'), [
        'contact_whatsapp' => '6281234567890',
        'booking_open_hour' => '8',
    ])->assertRedirect();

    $this->assertDatabaseHas('settings', [
        'key' => 'contact.whatsapp',
        'value' => '6281234567890',
        'type' => 'string',
    ]);
    $this->assertDatabaseHas('settings', [
        'key' => 'booking.open_hour',
        'value' => '8',
        'type' => 'integer',
    ]);
});

test('registered customer with pending test drive session redirects back to showroom modal', function () {
    $response = $this->withSession([
        'pending_test_drive' => [
            'car_id' => $this->car->id,
            'preferred_date' => Carbon::now()->addDays(2)->format('Y-m-d'),
            'driver_license_id' => 'SIM-NEW-123',
        ],
    ])->post('/register', [
        'name' => 'New Customer',
        'email' => 'newcustomer@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertRedirect(route('home'));
    $response->assertSessionHas('open_modal', 'test_drive');
});
