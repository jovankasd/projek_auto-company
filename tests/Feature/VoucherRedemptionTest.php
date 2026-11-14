<?php

use App\Enums\CarCategory;
use App\Enums\DiscountType;
use App\Enums\ServiceItemType;
use App\Enums\ServiceStatus;
use App\Enums\UserRole;
use App\Models\Car;
use App\Models\Promo;
use App\Models\Service;
use App\Models\User;
use App\Services\ServiceCostCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->customer = User::create([
        'name' => 'Budi Customer',
        'email' => 'budi@example.com',
        'password' => bcrypt('password'),
        'role' => UserRole::Customer,
        'phone_number' => '08123456789',
        'is_active' => true,
    ]);

    $this->superAdmin = User::create([
        'name' => 'Super Admin',
        'email' => 'admin@example.com',
        'password' => bcrypt('password'),
        'role' => UserRole::SuperAdmin,
        'phone_number' => '08123456780',
        'is_active' => true,
    ]);

    $this->mechanic = User::create([
        'name' => 'Mekanik Handal',
        'email' => 'mekanik@example.com',
        'password' => bcrypt('password'),
        'role' => UserRole::Mekanik,
        'phone_number' => '08123456781',
        'is_active' => true,
    ]);
});

test('service model has promo relationship and discount amount fields in database', function () {
    $promo = Promo::create([
        'code' => 'SERVIS20',
        'title' => 'Diskon Servis 20%',
        'discount_type' => DiscountType::Percentage,
        'discount_value' => 20,
        'starts_at' => now()->subDay(),
        'ends_at' => now()->addDays(10),
        'max_usage' => 50,
        'used_count' => 0,
        'is_active' => true,
    ]);

    $service = Service::create([
        'ticket_number' => 'SRV-20261114-0001',
        'customer_id' => $this->customer->id,
        'promo_id' => $promo->id,
        'vehicle_plate_number' => 'B 1234 XYZ',
        'vehicle_model' => 'Sedan Pro',
        'vehicle_year' => 2024,
        'odometer_km' => 10000,
        'service_type' => 'regular',
        'service_date' => now()->addDays(2),
        'slot_hour' => 10,
        'complaint_notes' => 'Ganti oli rutin dan tune up.',
        'status' => ServiceStatus::Pending,
        'discount_amount' => 0,
        'total_cost' => 0,
    ]);

    expect($service->promo)->not->toBeNull();
    expect($service->promo->code)->toBe('SERVIS20');
    expect((float) $service->discount_amount)->toBe(0.0);
    expect($promo->services)->toHaveCount(1);
});

test('authenticated customer can submit service booking with valid promo code and increments used_count', function () {
    $promo = Promo::create([
        'code' => 'PROMOBERKALA',
        'title' => 'Diskon Servis Berkala',
        'discount_type' => DiscountType::Percentage,
        'discount_value' => 15,
        'starts_at' => now()->subDays(2),
        'ends_at' => now()->addDays(10),
        'max_usage' => 20,
        'used_count' => 5,
        'is_active' => true,
    ]);

    $serviceDate = Carbon::now()->addDays(2)->format('Y-m-d 10:00:00');

    $response = $this->actingAs($this->customer)->post('/service-booking', [
        'vehicle_plate_number' => 'B 9999 ABC',
        'vehicle_model' => 'SUV Extreme',
        'vehicle_year' => 2023,
        'odometer_km' => 25000,
        'service_type' => 'regular',
        'service_date' => $serviceDate,
        'complaint_notes' => 'Pemeriksaan rutin berkala mesin.',
        'promo_code' => 'PROMOBERKALA',
    ]);

    $response->assertSessionHas('success');

    $service = Service::where('vehicle_plate_number', 'B 9999 ABC')->first();
    expect($service)->not->toBeNull();
    expect($service->promo_id)->toBe($promo->id);

    $promo->refresh();
    expect($promo->used_count)->toBe(6);
});

test('service booking rejects expired promo code', function () {
    Promo::create([
        'code' => 'KEDALUWARSA',
        'title' => 'Promo Lama',
        'discount_type' => DiscountType::Percentage,
        'discount_value' => 10,
        'starts_at' => now()->subDays(20),
        'ends_at' => now()->subDays(2),
        'max_usage' => 10,
        'used_count' => 0,
        'is_active' => true,
    ]);

    $serviceDate = Carbon::now()->addDays(2)->format('Y-m-d 10:00:00');

    $response = $this->actingAs($this->customer)->post('/service-booking', [
        'vehicle_plate_number' => 'B 1111 EXP',
        'vehicle_model' => 'Sedan',
        'vehicle_year' => 2022,
        'odometer_km' => 15000,
        'service_type' => 'regular',
        'service_date' => $serviceDate,
        'complaint_notes' => 'Pemeriksaan oli dan rem.',
        'promo_code' => 'KEDALUWARSA',
    ]);

    $response->assertSessionHasErrors(['promo_code']);
    $this->assertDatabaseMissing('services', ['vehicle_plate_number' => 'B 1111 EXP']);
});

test('service booking rejects promo code when max_usage has been reached', function () {
    Promo::create([
        'code' => 'KUOTAHABIS',
        'title' => 'Promo Terbatas',
        'discount_type' => DiscountType::Fixed,
        'discount_value' => 50000,
        'starts_at' => now()->subDays(2),
        'ends_at' => now()->addDays(5),
        'max_usage' => 10,
        'used_count' => 10,
        'is_active' => true,
    ]);

    $serviceDate = Carbon::now()->addDays(2)->format('Y-m-d 10:00:00');

    $response = $this->actingAs($this->customer)->post('/service-booking', [
        'vehicle_plate_number' => 'B 2222 HAB',
        'vehicle_model' => 'Sedan',
        'vehicle_year' => 2022,
        'odometer_km' => 15000,
        'service_type' => 'regular',
        'service_date' => $serviceDate,
        'complaint_notes' => 'Pemeriksaan oli dan rem.',
        'promo_code' => 'KUOTAHABIS',
    ]);

    $response->assertSessionHasErrors(['promo_code']);
    $this->assertDatabaseMissing('services', ['vehicle_plate_number' => 'B 2222 HAB']);
});

test('service booking rejects car purchase promo code for workshop service', function () {
    $car = Car::create([
        'slug' => 'mustang-gt-test',
        'brand' => 'Ford',
        'model_name' => 'Mustang GT',
        'year' => 2024,
        'category' => CarCategory::Sports,
        'color_name' => 'Race Red',
        'color_hex' => '#FF0000',
        'price' => 980000000,
        'stock_quantity' => 2,
        'is_available' => true,
    ]);

    Promo::create([
        'code' => 'MUSTANG50JT',
        'title' => 'Cashback Mustang',
        'discount_type' => DiscountType::Fixed,
        'discount_value' => 50000000,
        'car_id' => $car->id,
        'starts_at' => now()->subDays(2),
        'ends_at' => now()->addDays(5),
        'max_usage' => 5,
        'used_count' => 1,
        'is_active' => true,
    ]);

    $serviceDate = Carbon::now()->addDays(2)->format('Y-m-d 10:00:00');

    $response = $this->actingAs($this->customer)->post('/service-booking', [
        'vehicle_plate_number' => 'B 3333 CAR',
        'vehicle_model' => 'Mustang GT',
        'vehicle_year' => 2024,
        'odometer_km' => 5000,
        'service_type' => 'regular',
        'service_date' => $serviceDate,
        'complaint_notes' => 'Servis pertama mobil baru.',
        'promo_code' => 'MUSTANG50JT',
    ]);

    $response->assertSessionHasErrors(['promo_code']);
    $this->assertDatabaseMissing('services', ['vehicle_plate_number' => 'B 3333 CAR']);
});

test('guest service booking retains promo code in session and restores it after login', function () {
    $serviceDate = Carbon::now()->addDays(2)->format('Y-m-d 10:00:00');

    $response = $this->post('/service-booking', [
        'vehicle_plate_number' => 'B 4444 GST',
        'vehicle_model' => 'Sportscar',
        'vehicle_year' => 2023,
        'odometer_km' => 12000,
        'service_type' => 'regular',
        'service_date' => $serviceDate,
        'complaint_notes' => 'Periksa kondisi transmisi.',
        'promo_code' => 'XYZSUPER2024',
    ]);

    $response->assertRedirect(route('login'));
    $response->assertSessionHas('pending_service_booking');

    $pending = session('pending_service_booking');
    expect($pending['promo_code'])->toBe('XYZSUPER2024');
});

test('service cost calculator recalculates total_cost and discount_amount accurately for percentage promo', function () {
    $promo = Promo::create([
        'code' => 'DISC20',
        'title' => 'Diskon 20%',
        'discount_type' => DiscountType::Percentage,
        'discount_value' => 20,
        'starts_at' => now()->subDays(2),
        'ends_at' => now()->addDays(10),
        'is_active' => true,
    ]);

    $service = Service::create([
        'ticket_number' => 'SRV-CALC-0001',
        'customer_id' => $this->customer->id,
        'promo_id' => $promo->id,
        'vehicle_plate_number' => 'B 5555 CLC',
        'vehicle_model' => 'Sedan',
        'vehicle_year' => 2024,
        'odometer_km' => 10000,
        'service_type' => 'major',
        'service_date' => now()->addDay(),
        'slot_hour' => 10,
        'complaint_notes' => 'Servis besar dan ganti spare part.',
        'status' => ServiceStatus::InProgress,
        'discount_amount' => 0,
        'total_cost' => 0,
    ]);

    $service->items()->create([
        'item_type' => ServiceItemType::Part,
        'name' => 'Kampas Rem Depan',
        'quantity' => 1,
        'unit_price' => 600000,
        'subtotal' => 600000,
        'added_by' => $this->mechanic->id,
    ]);

    $service->items()->create([
        'item_type' => ServiceItemType::Labor,
        'name' => 'Jasa Pasang & Cek Rem',
        'quantity' => 1,
        'unit_price' => 150000,
        'subtotal' => 150000,
        'added_by' => $this->mechanic->id,
    ]);

    $calculator = new ServiceCostCalculator;
    $netTotal = $calculator->recalculate($service);

    // Gross: 750.000, Diskon 20%: 150.000, Net: 600.000
    expect($netTotal)->toBe(600000.0);
    $service->refresh();
    expect((float) $service->discount_amount)->toBe(150000.0);
    expect((float) $service->total_cost)->toBe(600000.0);
});

test('service cost calculator handles fixed discount and does not exceed gross total', function () {
    $promo = Promo::create([
        'code' => 'FIXED50K',
        'title' => 'Potongan 50rb',
        'discount_type' => DiscountType::Fixed,
        'discount_value' => 50000,
        'starts_at' => now()->subDays(2),
        'ends_at' => now()->addDays(10),
        'is_active' => true,
    ]);

    $service = Service::create([
        'ticket_number' => 'SRV-CALC-0002',
        'customer_id' => $this->customer->id,
        'promo_id' => $promo->id,
        'vehicle_plate_number' => 'B 6666 FIX',
        'vehicle_model' => 'Sedan',
        'vehicle_year' => 2024,
        'odometer_km' => 10000,
        'service_type' => 'regular',
        'service_date' => now()->addDay(),
        'slot_hour' => 11,
        'complaint_notes' => 'Ganti filter kabin.',
        'status' => ServiceStatus::InProgress,
        'discount_amount' => 0,
        'total_cost' => 0,
    ]);

    $service->items()->create([
        'item_type' => ServiceItemType::Part,
        'name' => 'Filter Kabin AC',
        'quantity' => 1,
        'unit_price' => 120000,
        'subtotal' => 120000,
        'added_by' => $this->mechanic->id,
    ]);

    $calculator = new ServiceCostCalculator;
    $netTotal = $calculator->recalculate($service);

    // Gross: 120.000, Diskon Fixed: 50.000, Net: 70.000
    expect($netTotal)->toBe(70000.0);
    $service->refresh();
    expect((float) $service->discount_amount)->toBe(50000.0);
    expect((float) $service->total_cost)->toBe(70000.0);
});

test('cancelling a pending service restores the voucher used_count', function () {
    $promo = Promo::create([
        'code' => 'RESTOREVOUCHER',
        'title' => 'Voucher Kuota Restitusi',
        'discount_type' => DiscountType::Percentage,
        'discount_value' => 10,
        'starts_at' => now()->subDays(1),
        'ends_at' => now()->addDays(5),
        'max_usage' => 5,
        'used_count' => 3,
        'is_active' => true,
    ]);

    $service = Service::create([
        'ticket_number' => 'SRV-CAN-0001',
        'customer_id' => $this->customer->id,
        'promo_id' => $promo->id,
        'vehicle_plate_number' => 'B 7777 CAN',
        'vehicle_model' => 'Sedan',
        'vehicle_year' => 2024,
        'odometer_km' => 10000,
        'service_type' => 'regular',
        'service_date' => now()->addDays(3),
        'slot_hour' => 11,
        'complaint_notes' => 'Rencana servis yang akan dibatalkan.',
        'status' => ServiceStatus::Pending,
        'discount_amount' => 0,
        'total_cost' => 0,
    ]);

    // Admin membatalkan servis
    $response = $this->actingAs($this->superAdmin)->patch("/dashboard/services/{$service->id}/status", [
        'status' => ServiceStatus::Cancelled->value,
        'cancellation_reason' => 'Customer berhalangan hadir.',
    ]);

    $response->assertSessionHas('success');
    $promo->refresh();
    expect($promo->used_count)->toBe(2);
});

test('showroom page displays honest labels and actions for workshop vs car unit promos', function () {
    $car = Car::create([
        'slug' => 'bmw-m4-test',
        'brand' => 'BMW',
        'model_name' => 'M4 Competition',
        'year' => 2024,
        'category' => CarCategory::Sports,
        'color_name' => 'Isle of Man Green',
        'color_hex' => '#004F39',
        'price' => 2100000000,
        'stock_quantity' => 1,
        'is_available' => true,
    ]);

    Promo::create([
        'code' => 'XYZSUPER2024',
        'title' => 'Diskon Servis Berkala',
        'discount_type' => DiscountType::Percentage,
        'discount_value' => 20,
        'car_id' => null,
        'starts_at' => now()->subDay(),
        'ends_at' => now()->addDays(20),
        'max_usage' => 100,
        'used_count' => 10,
        'is_active' => true,
    ]);

    Promo::create([
        'code' => 'M4LUXURY50JT',
        'title' => 'Cashback BMW M4',
        'discount_type' => DiscountType::Fixed,
        'discount_value' => 50000000,
        'car_id' => $car->id,
        'starts_at' => now()->subDay(),
        'ends_at' => now()->addDays(20),
        'max_usage' => 5,
        'used_count' => 1,
        'is_active' => true,
    ]);

    $response = $this->get('/');

    $response->assertStatus(200);
    $response->assertSee('Bengkel Resmi');
    $response->assertSee('Pakai untuk Servis');
    $response->assertSee('Dealer Offline');
    $response->assertSee('Konsultasi Sales');
    $response->assertSee('Kode Voucher Promo');
});

test('service booking fails if promo is inactive', function () {
    Promo::create([
        'code' => 'INACTIVEPROMO',
        'title' => 'Promo Nonaktif',
        'discount_type' => DiscountType::Fixed,
        'discount_value' => 50000,
        'starts_at' => now()->subDays(5),
        'ends_at' => now()->addDays(5),
        'is_active' => false,
    ]);

    $serviceDate = Carbon::now()->addDays(2)->format('Y-m-d 10:00:00');

    $response = $this->actingAs($this->customer)->post('/service-booking', [
        'vehicle_plate_number' => 'B 1111 INA',
        'vehicle_model' => 'Sedan Nonaktif',
        'vehicle_year' => 2022,
        'odometer_km' => 15000,
        'service_type' => 'regular',
        'service_date' => $serviceDate,
        'complaint_notes' => 'Cek mesin berkala.',
        'promo_code' => 'INACTIVEPROMO',
    ]);

    $response->assertSessionHasErrors(['promo_code']);
});

test('service booking fails if promo code is non-existent', function () {
    $serviceDate = Carbon::now()->addDays(2)->format('Y-m-d 10:00:00');

    $response = $this->actingAs($this->customer)->post('/service-booking', [
        'vehicle_plate_number' => 'B 2222 NON',
        'vehicle_model' => 'Sedan Hantu',
        'vehicle_year' => 2022,
        'odometer_km' => 15000,
        'service_type' => 'regular',
        'service_date' => $serviceDate,
        'complaint_notes' => 'Cek rem dan suspensi.',
        'promo_code' => 'KODEPALSU999',
    ]);

    $response->assertSessionHasErrors(['promo_code']);
});

test('soft deleted promo still retains relationship and discount calculation on existing service via withTrashed', function () {
    $promo = Promo::create([
        'code' => 'PROMOARCHIVED',
        'title' => 'Promo Diskon Arsip',
        'discount_type' => DiscountType::Percentage,
        'discount_value' => 25,
        'starts_at' => now()->subDays(5),
        'ends_at' => now()->addDays(5),
        'is_active' => true,
    ]);

    $service = Service::create([
        'ticket_number' => 'SRV-ARCHIVED-001',
        'customer_id' => $this->customer->id,
        'promo_id' => $promo->id,
        'vehicle_plate_number' => 'B 3333 DEL',
        'vehicle_model' => 'Coupe Sport',
        'vehicle_year' => 2023,
        'odometer_km' => 8000,
        'service_type' => 'major',
        'service_date' => now()->addDays(2),
        'slot_hour' => 11,
        'complaint_notes' => 'Pemeriksaan rutin kendaraan.',
        'status' => ServiceStatus::InProgress,
        'discount_amount' => 0,
        'total_cost' => 0,
    ]);

    // Admin menghapus promo (soft delete)
    $promo->delete();
    expect($promo->trashed())->toBeTrue();

    // Relasi promo tetap ada berkat withTrashed()
    $service->refresh();
    expect($service->promo)->not->toBeNull();
    expect($service->promo->code)->toBe('PROMOARCHIVED');

    // Tambah item dan kalkulator tetap berhasil menghitung diskon 25%
    $service->items()->create([
        'item_type' => ServiceItemType::Labor,
        'name' => 'Paket Tune Up',
        'quantity' => 1,
        'unit_price' => 400000,
        'subtotal' => 400000,
        'added_by' => $this->mechanic->id,
    ]);

    $calculator = new ServiceCostCalculator;
    $netTotal = $calculator->recalculate($service);

    expect($netTotal)->toBe(300000.0);
    expect((float) $service->fresh()->discount_amount)->toBe(100000.0);
});

test('mechanic cannot add item to completed or cancelled service', function () {
    $service = Service::create([
        'ticket_number' => 'SRV-CLOSED-001',
        'customer_id' => $this->customer->id,
        'vehicle_plate_number' => 'B 4444 FIN',
        'vehicle_model' => 'Sedan Selesai',
        'vehicle_year' => 2021,
        'odometer_km' => 50000,
        'service_type' => 'regular',
        'service_date' => now()->subDay(),
        'slot_hour' => 9,
        'complaint_notes' => 'Servis selesai kemarin.',
        'status' => ServiceStatus::Completed,
        'discount_amount' => 0,
        'total_cost' => 500000,
    ]);

    $response = $this->actingAs($this->mechanic)->postJson(route('services.add-item', $service), [
        'item_type' => ServiceItemType::Part->value,
        'name' => 'Busi Iridium Tambahan',
        'quantity' => 4,
        'unit_price' => 75000,
    ]);

    $response->assertStatus(422);
    $response->assertJson(['message' => 'Tidak dapat menambah item pada servis yang sudah selesai atau dibatalkan.']);
});

test('percentage discount is capped at gross total if discount value exceeds 100 percent', function () {
    $promo = Promo::create([
        'code' => 'OVER100PERCENT',
        'title' => 'Diskon Ekstrem 150%',
        'discount_type' => DiscountType::Percentage,
        'discount_value' => 150,
        'starts_at' => now()->subDay(),
        'ends_at' => now()->addDays(10),
        'is_active' => true,
    ]);

    $discount = $promo->calculateDiscount(200000);
    // Diskon maksimal sebesar gross total (200000), tidak boleh 300000
    expect($discount)->toBe(200000.0);
});
