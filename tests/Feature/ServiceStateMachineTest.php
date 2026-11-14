<?php

use App\Enums\ServiceItemType;
use App\Enums\ServiceStatus;
use App\Enums\UserRole;
use App\Models\Service;
use App\Models\ServiceItem;
use App\Models\ServiceStatusLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create([
        'name' => 'Super Admin',
        'role' => UserRole::SuperAdmin,
    ]);

    $this->mechanicA = User::factory()->create([
        'name' => 'Mekanik A',
        'role' => UserRole::Mekanik,
    ]);

    $this->mechanicB = User::factory()->create([
        'name' => 'Mekanik B',
        'role' => UserRole::Mekanik,
    ]);

    $this->customer = User::factory()->create([
        'name' => 'Customer A',
        'role' => UserRole::Customer,
    ]);

    $this->otherCustomer = User::factory()->create([
        'name' => 'Customer B',
        'role' => UserRole::Customer,
    ]);

    $this->sales = User::factory()->create([
        'name' => 'Sales Officer',
        'role' => UserRole::Sales,
    ]);

    $this->pendingService = Service::create([
        'ticket_number' => 'SRV-20261114-0001',
        'customer_id' => $this->customer->id,
        'assigned_mechanic_id' => null,
        'vehicle_plate_number' => 'B 1001 XYZ',
        'vehicle_model' => 'Honda Civic RS',
        'vehicle_year' => 2023,
        'odometer_km' => 12000,
        'service_type' => 'regular',
        'service_date' => Carbon::now()->addDays(2),
        'slot_hour' => 10,
        'complaint_notes' => 'Ganti oli dan cek sistem kelistrikan.',
        'status' => ServiceStatus::Pending,
        'total_cost' => 0,
    ]);
});

test('only super admin can confirm pending service and assign mechanic', function () {
    // Mekanik cannot confirm pending service directly
    $responseMech = $this->actingAs($this->mechanicA)->patchJson(route('services.update-status', $this->pendingService), [
        'status' => ServiceStatus::Confirmed->value,
    ]);
    $responseMech->assertStatus(403);

    // Super Admin assigns mechanic and confirms
    $responseAdmin = $this->actingAs($this->admin)->patch(route('services.assign', $this->pendingService), [
        'assigned_mechanic_id' => $this->mechanicA->id,
    ]);
    $responseAdmin->assertRedirect();

    $freshService = $this->pendingService->fresh();
    expect($freshService->status)->toBe(ServiceStatus::Confirmed);
    expect($freshService->assigned_mechanic_id)->toBe($this->mechanicA->id);
    expect($freshService->confirmed_at)->not->toBeNull();
});

test('only assigned mechanic can start confirmed service into in_progress', function () {
    $this->pendingService->update([
        'status' => ServiceStatus::Confirmed,
        'assigned_mechanic_id' => $this->mechanicA->id,
        'confirmed_at' => now(),
    ]);

    // Unassigned Mechanic B receives 403
    $responseB = $this->actingAs($this->mechanicB)->patchJson(route('services.update-status', $this->pendingService), [
        'status' => ServiceStatus::InProgress->value,
        'notes' => 'Mulai servis oleh B',
    ]);
    $responseB->assertStatus(403);

    // Assigned Mechanic A succeeds
    $responseA = $this->actingAs($this->mechanicA)->patchJson(route('services.update-status', $this->pendingService), [
        'status' => ServiceStatus::InProgress->value,
        'notes' => 'Unit masuk stall bengkel.',
    ]);
    $responseA->assertStatus(200);

    $fresh = $this->pendingService->fresh();
    expect($fresh->status)->toBe(ServiceStatus::InProgress);
    expect($fresh->started_at)->not->toBeNull();
});

test('unassigned mechanic cannot add or remove items on another mechanics service', function () {
    $this->pendingService->update([
        'status' => ServiceStatus::InProgress,
        'assigned_mechanic_id' => $this->mechanicA->id,
        'started_at' => now(),
    ]);

    // Mechanic B cannot add item
    $resAddB = $this->actingAs($this->mechanicB)->postJson(route('services.add-item', $this->pendingService), [
        'item_type' => ServiceItemType::Part->value,
        'name' => 'Filter Udara',
        'quantity' => 1,
        'unit_price' => 150000,
    ]);
    $resAddB->assertStatus(403);

    // Mechanic A can add item
    $resAddA = $this->actingAs($this->mechanicA)->postJson(route('services.add-item', $this->pendingService), [
        'item_type' => ServiceItemType::Part->value,
        'name' => 'Filter Udara',
        'quantity' => 1,
        'unit_price' => 150000,
    ]);
    $resAddA->assertStatus(200);
    $item = ServiceItem::where('service_id', $this->pendingService->id)->first();

    // Mechanic B cannot remove item
    $resRemB = $this->actingAs($this->mechanicB)->deleteJson(route('services.remove-item', [
        'service' => $this->pendingService,
        'item' => $item,
    ]));
    $resRemB->assertStatus(403);
});

test('in_progress service cannot be completed without service items', function () {
    $this->pendingService->update([
        'status' => ServiceStatus::InProgress,
        'assigned_mechanic_id' => $this->mechanicA->id,
        'started_at' => now(),
    ]);

    $response = $this->actingAs($this->mechanicA)->patchJson(route('services.update-status', $this->pendingService), [
        'status' => ServiceStatus::Completed->value,
    ]);

    $response->assertStatus(422);
    expect($this->pendingService->fresh()->status)->toBe(ServiceStatus::InProgress);
});

test('assigned mechanic completes service with items setting completed_at and locking total_cost', function () {
    $this->pendingService->update([
        'status' => ServiceStatus::InProgress,
        'assigned_mechanic_id' => $this->mechanicA->id,
        'started_at' => now(),
    ]);

    // Add 2 items
    $this->actingAs($this->mechanicA)->postJson(route('services.add-item', $this->pendingService), [
        'item_type' => ServiceItemType::Part->value,
        'name' => 'Oli Mesin 4L',
        'quantity' => 1,
        'unit_price' => 450000,
    ]);
    $this->actingAs($this->mechanicA)->postJson(route('services.add-item', $this->pendingService), [
        'item_type' => ServiceItemType::Labor->value,
        'name' => 'Jasa Ganti Oli',
        'quantity' => 1,
        'unit_price' => 100000,
    ]);

    expect((float) $this->pendingService->fresh()->total_cost)->toBe(550000.0);

    // Complete service
    $response = $this->actingAs($this->mechanicA)->patchJson(route('services.update-status', $this->pendingService), [
        'status' => ServiceStatus::Completed->value,
        'notes' => 'Pengerjaan selesai dan unit telah dicuci.',
    ]);

    $response->assertStatus(200);
    $fresh = $this->pendingService->fresh();
    expect($fresh->status)->toBe(ServiceStatus::Completed);
    expect($fresh->completed_at)->not->toBeNull();
    expect((float) $fresh->total_cost)->toBe(550000.0);
});

test('customer owner can cancel pending and confirmed service via dedicated cancel endpoint', function () {
    // 1. Cancel from Pending
    $responsePending = $this->actingAs($this->customer)->patchJson(route('services.cancel', $this->pendingService), [
        'cancellation_reason' => 'Ada keperluan mendadak ke luar kota.',
    ]);
    $responsePending->assertStatus(200);

    $fresh = $this->pendingService->fresh();
    expect($fresh->status)->toBe(ServiceStatus::Cancelled);
    expect($fresh->cancelled_at)->not->toBeNull();
    expect($fresh->cancellation_reason)->toBe('Ada keperluan mendadak ke luar kota.');

    // 2. Cancel from Confirmed
    $confirmedService = Service::create([
        'ticket_number' => 'SRV-20261114-0002',
        'customer_id' => $this->customer->id,
        'assigned_mechanic_id' => $this->mechanicA->id,
        'vehicle_plate_number' => 'B 2002 XYZ',
        'vehicle_model' => 'Honda CR-V',
        'vehicle_year' => 2022,
        'odometer_km' => 18000,
        'complaint_notes' => 'Servis berkala rutin.',
        'service_type' => 'regular',
        'service_date' => Carbon::now()->addDays(3),
        'slot_hour' => 11,
        'status' => ServiceStatus::Confirmed,
        'confirmed_at' => now(),
        'total_cost' => 0,
    ]);

    $responseConfirmed = $this->actingAs($this->customer)->patchJson(route('services.cancel', $confirmedService), [
        'cancellation_reason' => 'Mobil sedang dipinjam keluarga.',
    ]);
    $responseConfirmed->assertStatus(200);
    expect($confirmedService->fresh()->status)->toBe(ServiceStatus::Cancelled);
});

test('cancellation requires valid reason and is forbidden for other roles or status in_progress', function () {
    // Short reason rejected
    $resShort = $this->actingAs($this->customer)->patchJson(route('services.cancel', $this->pendingService), [
        'cancellation_reason' => 'Gak',
    ]);
    $resShort->assertStatus(422);

    // Other customer cannot cancel
    $resOther = $this->actingAs($this->otherCustomer)->patchJson(route('services.cancel', $this->pendingService), [
        'cancellation_reason' => 'Batal oleh orang lain',
    ]);
    $resOther->assertStatus(403);

    // Sales cannot cancel
    $resSales = $this->actingAs($this->sales)->patchJson(route('services.cancel', $this->pendingService), [
        'cancellation_reason' => 'Batal oleh sales',
    ]);
    $resSales->assertStatus(403);

    // In Progress service cannot be cancelled by anyone
    $inProgressService = Service::create([
        'ticket_number' => 'SRV-20261114-0003',
        'customer_id' => $this->customer->id,
        'assigned_mechanic_id' => $this->mechanicA->id,
        'vehicle_plate_number' => 'B 3003 XYZ',
        'vehicle_model' => 'Honda HR-V',
        'vehicle_year' => 2021,
        'odometer_km' => 22000,
        'complaint_notes' => 'Pemeriksaan rem dan transmisi.',
        'service_type' => 'regular',
        'service_date' => Carbon::now()->addDays(1),
        'slot_hour' => 9,
        'status' => ServiceStatus::InProgress,
        'started_at' => now(),
        'total_cost' => 0,
    ]);

    $resCancelInProgress = $this->actingAs($this->customer)->patchJson(route('services.cancel', $inProgressService), [
        'cancellation_reason' => 'Mau batal di tengah jalan',
    ]);
    $resCancelInProgress->assertStatus(403);

    // Assigned mechanic cannot cancel service via updateStatus
    $confirmedForMech = Service::create([
        'ticket_number' => 'SRV-20261114-0077',
        'customer_id' => $this->customer->id,
        'assigned_mechanic_id' => $this->mechanicA->id,
        'vehicle_plate_number' => 'B 7700 XYZ',
        'vehicle_model' => 'Honda City',
        'vehicle_year' => 2022,
        'odometer_km' => 10000,
        'complaint_notes' => 'Servis berkala.',
        'service_type' => 'regular',
        'service_date' => Carbon::now()->addDays(2),
        'slot_hour' => 11,
        'status' => ServiceStatus::Confirmed,
        'confirmed_at' => now(),
        'total_cost' => 0,
    ]);

    $resMechCancel = $this->actingAs($this->mechanicA)->patchJson(route('services.update-status', $confirmedForMech), [
        'status' => ServiceStatus::Cancelled->value,
        'cancellation_reason' => 'Mekanik membatalkan sepihak.',
    ]);
    $resMechCancel->assertStatus(403);
});

test('terminal services completed and cancelled cannot be mutated via status or items', function () {
    // 1. Test Completed Service
    $completedService = Service::create([
        'ticket_number' => 'SRV-20261114-0004',
        'customer_id' => $this->customer->id,
        'assigned_mechanic_id' => $this->mechanicA->id,
        'vehicle_plate_number' => 'B 4004 XYZ',
        'vehicle_model' => 'Honda Brio',
        'vehicle_year' => 2020,
        'odometer_km' => 15000,
        'complaint_notes' => 'Pemeriksaan busi dan sistem pengapian.',
        'service_type' => 'regular',
        'service_date' => Carbon::now()->subDays(1),
        'slot_hour' => 14,
        'status' => ServiceStatus::InProgress,
        'started_at' => now()->subDay(),
        'total_cost' => 0,
    ]);

    $item = ServiceItem::create([
        'service_id' => $completedService->id,
        'item_type' => ServiceItemType::Part,
        'name' => 'Busi Iridium',
        'quantity' => 4,
        'unit_price' => 125000,
        'subtotal' => 500000,
        'added_by' => $this->mechanicA->id,
    ]);

    $completedService->update([
        'status' => ServiceStatus::Completed,
        'completed_at' => now()->subDay(),
        'total_cost' => 500000,
    ]);

    // Cannot change status from Completed
    $resStatus = $this->actingAs($this->admin)->patchJson(route('services.update-status', $completedService), [
        'status' => ServiceStatus::InProgress->value,
    ]);
    $resStatus->assertStatus(403);

    // Cannot add item to Completed service
    $resAdd = $this->actingAs($this->mechanicA)->postJson(route('services.add-item', $completedService), [
        'item_type' => ServiceItemType::Labor->value,
        'name' => 'Jasa Tambahan Ilegal',
        'quantity' => 1,
        'unit_price' => 100000,
    ]);
    $resAdd->assertStatus(403);
    expect((float) $completedService->fresh()->total_cost)->toBe(500000.0);

    // Cannot remove item from Completed service
    $resRemove = $this->actingAs($this->mechanicA)->deleteJson(route('services.remove-item', [
        'service' => $completedService,
        'item' => $item,
    ]));
    $resRemove->assertStatus(403);
    expect((float) $completedService->fresh()->total_cost)->toBe(500000.0);
    expect(ServiceItem::find($item->id))->not->toBeNull();

    // 2. Test Cancelled Service
    $cancelledService = Service::create([
        'ticket_number' => 'SRV-20261114-0005',
        'customer_id' => $this->customer->id,
        'assigned_mechanic_id' => null,
        'vehicle_plate_number' => 'B 5005 XYZ',
        'vehicle_model' => 'Honda Jazz',
        'vehicle_year' => 2019,
        'odometer_km' => 25000,
        'complaint_notes' => 'Pemeriksaan kaki-kaki.',
        'service_type' => 'regular',
        'service_date' => Carbon::now()->addDays(2),
        'slot_hour' => 10,
        'status' => ServiceStatus::Cancelled,
        'cancelled_at' => now(),
        'cancellation_reason' => 'Dibatalkan pelanggan.',
        'total_cost' => 0,
    ]);

    $resStatusCancel = $this->actingAs($this->admin)->patchJson(route('services.update-status', $cancelledService), [
        'status' => ServiceStatus::Pending->value,
    ]);
    $resStatusCancel->assertStatus(403);

    $resAddCancel = $this->actingAs($this->admin)->postJson(route('services.add-item', $cancelledService), [
        'item_type' => ServiceItemType::Part->value,
        'name' => 'Barang',
        'quantity' => 1,
        'unit_price' => 10000,
    ]);
    $resAddCancel->assertStatus(403);
    expect((float) $cancelledService->fresh()->total_cost)->toBe(0.0);
});

test('service status logs are recorded accurately for each transition', function () {
    $service = $this->pendingService;

    // Confirm by Admin
    $this->actingAs($this->admin)->patch(route('services.assign', $service), [
        'assigned_mechanic_id' => $this->mechanicA->id,
    ]);

    // Start by Mechanic A
    $this->actingAs($this->mechanicA)->patchJson(route('services.update-status', $service), [
        'status' => ServiceStatus::InProgress->value,
        'notes' => 'Unit dinaikkan ke lift.',
    ]);

    $logs = ServiceStatusLog::where('service_id', $service->id)->orderBy('created_at')->get();
    expect($logs->count())->toBeGreaterThanOrEqual(2);

    $confirmedLog = $logs->firstWhere('to_status', ServiceStatus::Confirmed->value);
    expect($confirmedLog)->not->toBeNull();
    expect($confirmedLog->changed_by)->toBe($this->admin->id);

    $inProgressLog = $logs->firstWhere('to_status', ServiceStatus::InProgress->value);
    expect($inProgressLog)->not->toBeNull();
    expect($inProgressLog->changed_by)->toBe($this->mechanicA->id);
    expect($inProgressLog->notes)->toContain('Unit dinaikkan ke lift.');
});

test('eloquent model integrity guards prevent mutations directly at database model level on terminal services', function () {
    $service = Service::create([
        'ticket_number' => 'SRV-20261114-0099',
        'customer_id' => $this->customer->id,
        'assigned_mechanic_id' => $this->mechanicA->id,
        'vehicle_plate_number' => 'B 9999 XYZ',
        'vehicle_model' => 'Honda Civic Type R',
        'vehicle_year' => 2024,
        'odometer_km' => 5000,
        'complaint_notes' => 'Inspeksi performa mesin.',
        'service_type' => 'inspection',
        'service_date' => Carbon::now()->subDays(2),
        'slot_hour' => 10,
        'status' => ServiceStatus::InProgress,
        'started_at' => now()->subDays(2),
        'total_cost' => 0,
    ]);

    $item = ServiceItem::create([
        'service_id' => $service->id,
        'item_type' => ServiceItemType::Labor,
        'name' => 'Dyno Test & Tuning',
        'quantity' => 1,
        'unit_price' => 1500000,
        'subtotal' => 1500000,
        'added_by' => $this->mechanicA->id,
    ]);

    $service->update([
        'status' => ServiceStatus::Completed,
        'completed_at' => now()->subDay(),
        'total_cost' => 1500000,
    ]);

    // 1. Adding item to completed service via model throws DomainException
    expect(function () use ($service) {
        ServiceItem::create([
            'service_id' => $service->id,
            'item_type' => ServiceItemType::Part,
            'name' => 'Busi Ilegal',
            'quantity' => 1,
            'unit_price' => 100000,
            'subtotal' => 100000,
            'added_by' => $this->mechanicA->id,
        ]);
    })->toThrow(DomainException::class);

    // 2. Deleting item from completed service via model throws DomainException
    expect(function () use ($item) {
        $item->delete();
    })->toThrow(DomainException::class);

    // 3. Mutating status of completed service directly throws DomainException
    expect(function () use ($service) {
        $service->update(['status' => ServiceStatus::InProgress]);
    })->toThrow(DomainException::class);

    // 4. Mutating total_cost of completed service directly throws DomainException
    expect(function () use ($service) {
        $service->update(['total_cost' => 9999999]);
    })->toThrow(DomainException::class);
});
