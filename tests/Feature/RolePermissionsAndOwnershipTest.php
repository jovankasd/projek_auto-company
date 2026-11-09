<?php

use App\Enums\CarCategory;
use App\Enums\ServiceItemType;
use App\Enums\ServiceStatus;
use App\Enums\SparePartCategory;
use App\Enums\UserRole;
use App\Models\Car;
use App\Models\Service;
use App\Models\ServiceItem;
use App\Models\SparePart;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->superAdmin = User::factory()->create([
        'name' => 'Super Admin',
        'email' => 'admin@test.com',
        'role' => UserRole::SuperAdmin,
        'is_active' => true,
    ]);

    $this->mechanicA = User::factory()->create([
        'name' => 'Mekanik A',
        'email' => 'mechanic.a@test.com',
        'role' => UserRole::Mekanik,
        'is_active' => true,
    ]);

    $this->mechanicB = User::factory()->create([
        'name' => 'Mekanik B',
        'email' => 'mechanic.b@test.com',
        'role' => UserRole::Mekanik,
        'is_active' => true,
    ]);

    $this->sales = User::factory()->create([
        'name' => 'Sales Representative',
        'email' => 'sales@test.com',
        'role' => UserRole::Sales,
        'is_active' => true,
    ]);

    $this->customer = User::factory()->create([
        'name' => 'Customer Satu',
        'email' => 'customer@test.com',
        'role' => UserRole::Customer,
        'is_active' => true,
    ]);

    $this->owner = User::factory()->create([
        'name' => 'Company Owner',
        'email' => 'owner@test.com',
        'role' => UserRole::Owner,
        'is_active' => true,
    ]);

    $this->car = Car::create([
        'slug' => 'test-car',
        'brand' => 'Toyota',
        'model_name' => 'Supra',
        'year' => 2024,
        'category' => CarCategory::Sports,
        'color_name' => 'Phantom Matte Gray',
        'color_hex' => '#4A4A4A',
        'price' => 1200000000,
        'hero_image_transparent' => 'images/cars/supra.webp',
        'is_available' => true,
        'sort_order' => 1,
    ]);

    $this->serviceA = Service::create([
        'ticket_number' => 'SRV-20261109-0001',
        'customer_id' => $this->customer->id,
        'assigned_mechanic_id' => $this->mechanicA->id,
        'vehicle_plate_number' => 'B 1111 AAA',
        'vehicle_model' => 'Toyota Supra',
        'vehicle_year' => 2024,
        'odometer_km' => 5000,
        'complaint_notes' => 'Ganti oli mesin',
        'service_type' => 'regular',
        'service_date' => Carbon::now()->addDays(1),
        'slot_hour' => 10,
        'status' => ServiceStatus::InProgress,
        'total_cost' => 0,
    ]);

    $this->serviceB = Service::create([
        'ticket_number' => 'SRV-20261109-0002',
        'customer_id' => $this->customer->id,
        'assigned_mechanic_id' => $this->mechanicB->id,
        'vehicle_plate_number' => 'B 2222 BBB',
        'vehicle_model' => 'Honda Civic',
        'vehicle_year' => 2023,
        'odometer_km' => 15000,
        'complaint_notes' => 'Tune up mesin',
        'service_type' => 'regular',
        'service_date' => Carbon::now()->addDays(1),
        'slot_hour' => 11,
        'status' => ServiceStatus::InProgress,
        'total_cost' => 0,
    ]);

    $this->itemB = ServiceItem::create([
        'service_id' => $this->serviceB->id,
        'item_type' => ServiceItemType::Part,
        'name' => 'Filter Oli',
        'part_number' => 'FLT-001',
        'quantity' => 1,
        'unit_price' => 100000,
        'subtotal' => 100000,
        'added_by' => $this->mechanicB->id,
    ]);

    $this->sparePart = SparePart::create([
        'slug' => 'filter-oli-toyota-flt-toy-01',
        'name' => 'Filter Oli Toyota',
        'part_number' => 'FLT-TOY-01',
        'brand' => 'Toyota',
        'category' => SparePartCategory::Engine,
        'price' => 150000,
        'stock_quantity' => 20,
        'min_stock_alert' => 5,
        'is_available' => true,
    ]);
});

/*
|--------------------------------------------------------------------------
| (a) Work Order Permissions & Ownership
|--------------------------------------------------------------------------
*/

test('mechanic A can view mechanic B work order in queue or detail', function () {
    $response = $this->actingAs($this->mechanicA)->get(route('services.show', $this->serviceB));
    $response->assertStatus(200);
});

test('mechanic A cannot update status of work order assigned to mechanic B', function () {
    $this->itemB; // ensure items exist for completed transition test
    $response = $this->actingAs($this->mechanicA)->patchJson(route('services.update-status', $this->serviceB), [
        'status' => ServiceStatus::Completed->value,
        'notes' => 'Mencoba menyelesaikan kerjaan mekanik B',
    ]);

    $response->assertStatus(403);
});

test('mechanic A cannot add item to work order assigned to mechanic B', function () {
    $response = $this->actingAs($this->mechanicA)->postJson(route('services.add-item', $this->serviceB), [
        'item_type' => ServiceItemType::Part->value,
        'name' => 'Busi Iridium',
        'part_number' => 'BSI-001',
        'quantity' => 4,
        'unit_price' => 50000,
    ]);

    $response->assertStatus(403);
});

test('mechanic A cannot remove item from work order assigned to mechanic B', function () {
    $response = $this->actingAs($this->mechanicA)->deleteJson(route('services.remove-item', [
        'service' => $this->serviceB,
        'item' => $this->itemB,
    ]));

    $response->assertStatus(403);
});

test('mechanic A can update status and mutate items on own assigned work order', function () {
    // Add item
    $addItemResponse = $this->actingAs($this->mechanicA)->postJson(route('services.add-item', $this->serviceA), [
        'item_type' => ServiceItemType::Labor->value,
        'name' => 'Jasa Ganti Oli',
        'quantity' => 1,
        'unit_price' => 75000,
    ]);
    $addItemResponse->assertStatus(200);

    // Update status to completed
    $updateStatusResponse = $this->actingAs($this->mechanicA)->patchJson(route('services.update-status', $this->serviceA), [
        'status' => ServiceStatus::Completed->value,
        'notes' => 'Ganti oli selesai',
    ]);
    $updateStatusResponse->assertStatus(200);
});

test('super admin can mutate any work order', function () {
    $response = $this->actingAs($this->superAdmin)->postJson(route('services.add-item', $this->serviceB), [
        'item_type' => ServiceItemType::Labor->value,
        'name' => 'Jasa Supervisi',
        'quantity' => 1,
        'unit_price' => 200000,
    ]);

    $response->assertStatus(200);
});

/*
|--------------------------------------------------------------------------
| (b) Spare Parts Master Permissions
|--------------------------------------------------------------------------
*/

test('mechanic can view spare parts master list and detail', function () {
    $indexResponse = $this->actingAs($this->mechanicA)->get(route('spare-parts.index'));
    $indexResponse->assertStatus(200);

    $showResponse = $this->actingAs($this->mechanicA)->get(route('spare-parts.show', $this->sparePart));
    $showResponse->assertStatus(200);
});

test('mechanic cannot access create or store master spare parts', function () {
    $createResponse = $this->actingAs($this->mechanicA)->get(route('spare-parts.create'));
    $createResponse->assertStatus(403);

    $storeResponse = $this->actingAs($this->mechanicA)->post(route('spare-parts.store'), [
        'name' => 'Radiator Coolant',
        'part_number' => 'RAD-001',
        'brand' => 'Denso',
        'category' => SparePartCategory::Cooling->value,
        'price' => 95000,
        'stock_quantity' => 10,
        'min_stock_alert' => 2,
    ]);
    $storeResponse->assertStatus(403);
});

test('mechanic cannot access edit or update master spare parts', function () {
    $editResponse = $this->actingAs($this->mechanicA)->get(route('spare-parts.edit', $this->sparePart));
    $editResponse->assertStatus(403);

    $updateResponse = $this->actingAs($this->mechanicA)->put(route('spare-parts.update', $this->sparePart), [
        'name' => 'Filter Oli Toyota Mod',
        'part_number' => 'FLT-TOY-01',
        'brand' => 'Toyota',
        'category' => SparePartCategory::Engine->value,
        'price' => 160000,
        'stock_quantity' => 25,
        'min_stock_alert' => 5,
    ]);
    $updateResponse->assertStatus(403);
});

test('mechanic cannot delete or restore master spare parts', function () {
    $deleteResponse = $this->actingAs($this->mechanicA)->delete(route('spare-parts.destroy', $this->sparePart));
    $deleteResponse->assertStatus(403);

    // Soft delete by admin first to test restore
    $this->sparePart->delete();

    $restoreResponse = $this->actingAs($this->mechanicA)->patch(route('spare-parts.restore', $this->sparePart->slug));
    $restoreResponse->assertStatus(403);
});

test('sales and super admin can create and manage master spare parts', function () {
    // Sales can create
    $salesResponse = $this->actingAs($this->sales)->post(route('spare-parts.store'), [
        'name' => 'Kampas Rem Belakang',
        'part_number' => 'BRK-REAR-01',
        'brand' => 'Brembo',
        'category' => SparePartCategory::Brake->value,
        'price' => 350000,
        'stock_quantity' => 12,
        'min_stock_alert' => 3,
    ]);
    $salesResponse->assertRedirect(route('spare-parts.index'));

    // Super Admin can edit
    $part = SparePart::where('part_number', 'BRK-REAR-01')->firstOrFail();
    $adminResponse = $this->actingAs($this->superAdmin)->put(route('spare-parts.update', $part), [
        'name' => 'Kampas Rem Belakang Updated',
        'part_number' => 'BRK-REAR-01',
        'brand' => 'Brembo',
        'category' => SparePartCategory::Brake->value,
        'price' => 375000,
        'stock_quantity' => 15,
        'min_stock_alert' => 3,
    ]);
    $adminResponse->assertRedirect(route('spare-parts.index'));
});

/*
|--------------------------------------------------------------------------
| (c) Booking Endpoints Role Enforcement (service-booking & test-drive)
|--------------------------------------------------------------------------
*/

test('customer can submit service booking and test drive', function () {
    $serviceDate = Carbon::now()->addDays(2)->setTime(14, 0)->format('Y-m-d H:i:s');
    $serviceResponse = $this->actingAs($this->customer)->post('/service-booking', [
        'vehicle_plate_number' => 'B 9999 CUS',
        'vehicle_model' => 'Toyota Supra',
        'vehicle_year' => 2024,
        'odometer_km' => 10000,
        'service_type' => 'regular',
        'service_date' => $serviceDate,
        'complaint_notes' => 'Service berkala pelanggan',
    ]);
    $serviceResponse->assertSessionHas('success');

    $tdResponse = $this->actingAs($this->customer)->post('/test-drive', [
        'car_id' => $this->car->id,
        'preferred_date' => Carbon::now()->addDays(3)->format('Y-m-d H:i:s'),
        'driver_license_id' => 'SIM-12345678',
        'pickup_location' => 'Showroom Sudirman',
    ]);
    $tdResponse->assertSessionHas('success');
});

test('super admin can submit service booking and test drive', function () {
    $serviceDate = Carbon::now()->addDays(2)->setTime(15, 0)->format('Y-m-d H:i:s');
    $serviceResponse = $this->actingAs($this->superAdmin)->post('/service-booking', [
        'vehicle_plate_number' => 'B 8888 ADM',
        'vehicle_model' => 'Toyota Supra',
        'vehicle_year' => 2024,
        'odometer_km' => 2000,
        'service_type' => 'inspection',
        'service_date' => $serviceDate,
        'complaint_notes' => 'Inspeksi unit display',
    ]);
    $serviceResponse->assertSessionHas('success');

    $tdResponse = $this->actingAs($this->superAdmin)->post('/test-drive', [
        'car_id' => $this->car->id,
        'preferred_date' => Carbon::now()->addDays(3)->format('Y-m-d H:i:s'),
        'driver_license_id' => 'SIM-88888888',
        'pickup_location' => 'Showroom Sudirman',
    ]);
    $tdResponse->assertSessionHas('success');
});

test('logged in sales, mechanic, and owner are rejected with 403 on service-booking and test-drive', function () {
    $unauthorizedUsers = [$this->sales, $this->mechanicA, $this->owner];

    foreach ($unauthorizedUsers as $user) {
        $serviceDate = Carbon::now()->addDays(2)->setTime(16, 0)->format('Y-m-d H:i:s');

        $serviceResponse = $this->actingAs($user)->post('/service-booking', [
            'vehicle_plate_number' => 'B 7777 NAH',
            'vehicle_model' => 'Toyota Supra',
            'vehicle_year' => 2024,
            'odometer_km' => 1000,
            'service_type' => 'regular',
            'service_date' => $serviceDate,
            'complaint_notes' => 'Tidak boleh diisi staf non-customer',
        ]);
        $serviceResponse->assertStatus(403);

        $tdResponse = $this->actingAs($user)->post('/test-drive', [
            'car_id' => $this->car->id,
            'preferred_date' => Carbon::now()->addDays(3)->format('Y-m-d H:i:s'),
            'driver_license_id' => 'SIM-77777777',
            'pickup_location' => 'Showroom Sudirman',
        ]);
        $tdResponse->assertStatus(403);
    }
});

test('guest submitting service-booking and test-drive retains session and redirects to login', function () {
    $serviceDate = Carbon::now()->addDays(2)->setTime(10, 0)->format('Y-m-d H:i:s');
    $serviceResponse = $this->post('/service-booking', [
        'vehicle_plate_number' => 'B 3333 GST',
        'vehicle_model' => 'Toyota Supra',
        'vehicle_year' => 2024,
        'odometer_km' => 8000,
        'service_type' => 'regular',
        'service_date' => $serviceDate,
        'complaint_notes' => 'Servis tamu',
    ]);
    $serviceResponse->assertRedirect(route('login'));
    $serviceResponse->assertSessionHas('pending_service_booking');

    $tdResponse = $this->post('/test-drive', [
        'car_id' => $this->car->id,
        'preferred_date' => Carbon::now()->addDays(3)->format('Y-m-d H:i:s'),
        'driver_license_id' => 'SIM-33333333',
        'pickup_location' => 'Showroom Sudirman',
    ]);
    $tdResponse->assertRedirect(route('login'));
    $tdResponse->assertSessionHas('pending_test_drive');
});
