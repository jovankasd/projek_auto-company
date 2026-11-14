<?php

use App\Enums\ServiceItemType;
use App\Enums\ServiceStatus;
use App\Enums\SparePartCategory;
use App\Enums\UserRole;
use App\Models\Service;
use App\Models\SparePart;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->mechanic = User::factory()->create([
        'name' => 'Mekanik Ahli',
        'role' => UserRole::Mekanik,
    ]);

    $this->customer = User::factory()->create([
        'name' => 'Pelanggan Setia',
        'role' => UserRole::Customer,
    ]);

    $this->service = Service::create([
        'ticket_number' => 'SRV-20241001-0001',
        'customer_id' => $this->customer->id,
        'assigned_mechanic_id' => $this->mechanic->id,
        'vehicle_plate_number' => 'B 1234 XYZ',
        'vehicle_model' => 'Tesla Model 3',
        'vehicle_year' => 2023,
        'odometer_km' => 20000,
        'complaint_notes' => 'Pemeriksaan rutin kelistrikan dan rem.',
        'service_type' => 'inspection',
        'service_date' => Carbon::now()->addDays(2),
        'slot_hour' => 10,
        'status' => ServiceStatus::Confirmed,
        'total_cost' => 0,
    ]);
});

test('mechanic can start confirmed service moving status to in_progress', function () {
    $response = $this->actingAs($this->mechanic)->patchJson(route('services.update-status', $this->service), [
        'status' => ServiceStatus::InProgress->value,
        'notes' => 'Mulai membongkar komponen rem dan suspensi.',
    ]);

    $response->assertStatus(200);
    expect($this->service->fresh()->status)->toBe(ServiceStatus::InProgress);
    expect($this->service->fresh()->started_at)->not->toBeNull();
});

test('service cannot be marked completed without any service items', function () {
    $this->service->update(['status' => ServiceStatus::InProgress]);

    $response = $this->actingAs($this->mechanic)->patchJson(route('services.update-status', $this->service), [
        'status' => ServiceStatus::Completed->value,
        'notes' => 'Pengerjaan selesai.',
    ]);

    $response->assertStatus(422);
    expect($this->service->fresh()->status)->toBe(ServiceStatus::InProgress);
});

test('adding and removing items dynamically recalculates service total_cost', function () {
    $this->service->update(['status' => ServiceStatus::InProgress]);

    $part = SparePart::create([
        'name' => 'Kampas Rem Depan',
        'slug' => 'kampas-rem-depan',
        'part_number' => 'BP-TSL-01',
        'brand' => 'Brembo',
        'category' => SparePartCategory::Brake,
        'price' => 500000,
        'stock_quantity' => 10,
        'is_available' => true,
    ]);

    // Tambah Part
    $responseAddPart = $this->actingAs($this->mechanic)->postJson(route('services.add-item', $this->service), [
        'item_type' => ServiceItemType::Part->value,
        'spare_part_id' => $part->id,
        'quantity' => 1,
    ]);

    $responseAddPart->assertStatus(200);
    expect((float) $this->service->fresh()->total_cost)->toBe(500000.0);

    // Tambah Labor
    $responseAddLabor = $this->actingAs($this->mechanic)->postJson(route('services.add-item', $this->service), [
        'item_type' => ServiceItemType::Labor->value,
        'name' => 'Ongkos Pemasangan Rem',
        'quantity' => 1,
        'unit_price' => 150000,
    ]);

    $responseAddLabor->assertStatus(200);
    expect((float) $this->service->fresh()->total_cost)->toBe(650000.0);

    // Selesaikan servis sekarang berhasil karena item sudah ada
    $responseComplete = $this->actingAs($this->mechanic)->patchJson(route('services.update-status', $this->service), [
        'status' => ServiceStatus::Completed->value,
        'notes' => 'Pemasangan rem baru selesai diuji jalan.',
    ]);

    $responseComplete->assertStatus(200);
    expect($this->service->fresh()->status)->toBe(ServiceStatus::Completed);
    expect($this->service->fresh()->completed_at)->not->toBeNull();
});

test('service cannot be cancelled once it is in_progress', function () {
    $this->service->update(['status' => ServiceStatus::InProgress]);

    $response = $this->actingAs($this->mechanic)->patchJson(route('services.update-status', $this->service), [
        'status' => ServiceStatus::Cancelled->value,
        'cancellation_reason' => 'Ingin dibatalkan',
    ]);

    $response->assertStatus(422);
    expect($this->service->fresh()->status)->toBe(ServiceStatus::InProgress);
});
