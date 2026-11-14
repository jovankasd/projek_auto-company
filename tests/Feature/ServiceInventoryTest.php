<?php

use App\Enums\ServiceItemType;
use App\Enums\ServiceStatus;
use App\Enums\SparePartCategory;
use App\Enums\UserRole;
use App\Models\Service;
use App\Models\ServiceItem;
use App\Models\SparePart;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->mechanic = User::factory()->create([
        'name' => 'Mekanik Handal',
        'role' => UserRole::Mekanik,
    ]);

    $this->customer = User::factory()->create([
        'name' => 'Pelanggan Uji',
        'role' => UserRole::Customer,
    ]);

    $this->superAdmin = User::factory()->create([
        'name' => 'Super Admin',
        'role' => UserRole::SuperAdmin,
    ]);

    $this->owner = User::factory()->create([
        'name' => 'Owner Bengkel',
        'role' => UserRole::Owner,
    ]);

    $this->service = Service::create([
        'ticket_number' => 'SRV-20261109-0001',
        'customer_id' => $this->customer->id,
        'assigned_mechanic_id' => $this->mechanic->id,
        'vehicle_plate_number' => 'B 1008 XYZ',
        'vehicle_model' => 'Honda Civic Type R',
        'vehicle_year' => 2023,
        'odometer_km' => 15000,
        'complaint_notes' => 'Ganti oli dan kampas rem.',
        'service_type' => 'regular',
        'service_date' => Carbon::now()->addDay(),
        'slot_hour' => 10,
        'status' => ServiceStatus::InProgress,
        'total_cost' => 0,
    ]);

    $this->sparePart = SparePart::create([
        'name' => 'Kampas Rem Brembo Original',
        'slug' => 'kampas-rem-brembo-original',
        'part_number' => 'BRK-BRM-001',
        'brand' => 'Brembo',
        'category' => SparePartCategory::Brake,
        'price' => 750000.00,
        'stock_quantity' => 10,
        'min_stock_alert' => 2,
        'is_available' => true,
    ]);
});

test('(a) client manipulation of unit_price and name is ignored in favor of official spare part data', function () {
    $response = $this->actingAs($this->mechanic)->postJson(route('services.add-item', $this->service), [
        'item_type' => ServiceItemType::Part->value,
        'spare_part_id' => $this->sparePart->id,
        'name' => 'Manipulated Cheap Part Name',
        'part_number' => 'FAKE-PART-999',
        'unit_price' => 1000, // Client tries to buy 750,000 part for 1,000
        'quantity' => 2,
    ]);

    $response->assertStatus(200);

    $item = ServiceItem::where('service_id', $this->service->id)->first();
    expect($item)->not->toBeNull();
    expect($item->spare_part_id)->toBe($this->sparePart->id);
    expect($item->name)->toBe('Kampas Rem Brembo Original');
    expect($item->part_number)->toBe('BRK-BRM-001');
    expect((float) $item->unit_price)->toBe(750000.00);
    expect((float) $item->subtotal)->toBe(1500000.00);
    expect((float) $this->service->fresh()->total_cost)->toBe(1500000.00);
});

test('(b) adding part item reduces stock atomically and rejects when stock is insufficient', function () {
    // 1. Sukses mengurangi stok
    $response = $this->actingAs($this->mechanic)->postJson(route('services.add-item', $this->service), [
        'item_type' => ServiceItemType::Part->value,
        'spare_part_id' => $this->sparePart->id,
        'quantity' => 4,
    ]);

    $response->assertStatus(200);
    expect($this->sparePart->fresh()->stock_quantity)->toBe(6);

    // 2. Gagal jika stok kurang (stok sisa 6, minta 7)
    $responseOver = $this->actingAs($this->mechanic)->postJson(route('services.add-item', $this->service), [
        'item_type' => ServiceItemType::Part->value,
        'spare_part_id' => $this->sparePart->id,
        'quantity' => 7,
    ]);

    $responseOver->assertStatus(422);
    expect($this->sparePart->fresh()->stock_quantity)->toBe(6); // Stok tidak berubah
});

test('(b) rejects part when spare part is marked unavailable', function () {
    $this->sparePart->update(['is_available' => false]);

    $response = $this->actingAs($this->mechanic)->postJson(route('services.add-item', $this->service), [
        'item_type' => ServiceItemType::Part->value,
        'spare_part_id' => $this->sparePart->id,
        'quantity' => 1,
    ]);

    $response->assertStatus(422);
    expect($this->sparePart->fresh()->stock_quantity)->toBe(10);
});

test('(c) removing part item restores stock exactly once and is idempotent', function () {
    // Tambah part (stok 10 -> 8)
    $this->actingAs($this->mechanic)->postJson(route('services.add-item', $this->service), [
        'item_type' => ServiceItemType::Part->value,
        'spare_part_id' => $this->sparePart->id,
        'quantity' => 2,
    ])->assertStatus(200);

    expect($this->sparePart->fresh()->stock_quantity)->toBe(8);

    $item = ServiceItem::where('service_id', $this->service->id)->first();

    // Hapus item part -> stok kembali 10
    $deleteResponse = $this->actingAs($this->mechanic)->deleteJson(
        route('services.remove-item', ['service' => $this->service, 'item' => $item])
    );

    $deleteResponse->assertStatus(200);
    expect($this->sparePart->fresh()->stock_quantity)->toBe(10);
    expect((float) $this->service->fresh()->total_cost)->toBe(0.00);

    // Panggilan hapus kedua pada item yang sama harus 404 dan TIDAK menambah stok lagi
    $secondDelete = $this->actingAs($this->mechanic)->deleteJson(
        route('services.remove-item', ['service' => $this->service, 'item' => $item->id])
    );

    $secondDelete->assertStatus(404);
    expect($this->sparePart->fresh()->stock_quantity)->toBe(10);
});

test('(c) cannot add or remove items on terminal service status', function () {
    $this->actingAs($this->mechanic)->postJson(route('services.add-item', $this->service), [
        'item_type' => ServiceItemType::Part->value,
        'spare_part_id' => $this->sparePart->id,
        'quantity' => 1,
    ])->assertStatus(200);

    $item = ServiceItem::where('service_id', $this->service->id)->first();

    // Selesaikan servis (terminal status)
    $this->service->update([
        'status' => ServiceStatus::Completed,
        'completed_at' => now(),
    ]);

    // Coba tambah item baru pada servis yang sudah completed
    $addResponse = $this->actingAs($this->mechanic)->postJson(route('services.add-item', $this->service), [
        'item_type' => ServiceItemType::Labor->value,
        'name' => 'Jasa Cuci Kendaraan',
        'unit_price' => 50000,
        'quantity' => 1,
    ]);
    $addResponse->assertStatus(422);

    // Coba hapus item pada servis yang sudah completed
    $removeResponse = $this->actingAs($this->mechanic)->deleteJson(
        route('services.remove-item', ['service' => $this->service, 'item' => $item])
    );
    $removeResponse->assertStatus(422);

    // Stok dan total_cost tidak berubah
    expect($this->sparePart->fresh()->stock_quantity)->toBe(9);
    expect((float) $this->service->fresh()->total_cost)->toBe(750000.00);
});

test('(d) labor and consumable items work with manual pricing and do not mutate spare part stock', function () {
    $initialStock = $this->sparePart->stock_quantity;

    $responseLabor = $this->actingAs($this->mechanic)->postJson(route('services.add-item', $this->service), [
        'item_type' => ServiceItemType::Labor->value,
        'name' => 'Jasa Penggantian Kampas Rem',
        'unit_price' => 150000,
        'quantity' => 1,
    ]);

    $responseLabor->assertStatus(200);

    $responseConsumable = $this->actingAs($this->mechanic)->postJson(route('services.add-item', $this->service), [
        'item_type' => ServiceItemType::Consumable->value,
        'name' => 'Brake Cleaner Aerosol',
        'unit_price' => 75000,
        'quantity' => 2,
    ]);

    $responseConsumable->assertStatus(200);

    expect($this->sparePart->fresh()->stock_quantity)->toBe($initialStock);

    $laborItem = ServiceItem::where('item_type', ServiceItemType::Labor)->first();
    expect($laborItem->spare_part_id)->toBeNull();
    expect((float) $laborItem->unit_price)->toBe(150000.00);

    $consumableItem = ServiceItem::where('item_type', ServiceItemType::Consumable)->first();
    expect($consumableItem->spare_part_id)->toBeNull();
    expect((float) $consumableItem->subtotal)->toBe(150000.00);

    expect((float) $this->service->fresh()->total_cost)->toBe(300000.00);
});

test('(e) legacy items without spare_part_id render and calculate total_cost seamlessly', function () {
    // Buat item lama langsung ke database tanpa spare_part_id
    $legacyItem = $this->service->items()->create([
        'item_type' => ServiceItemType::Part,
        'name' => 'Item Lama Tanpa ID Suku Cadang',
        'part_number' => 'LEGACY-001',
        'quantity' => 2,
        'unit_price' => 200000,
        'subtotal' => 400000,
        'added_by' => $this->mechanic->id,
    ]);

    expect($legacyItem->spare_part_id)->toBeNull();

    $response = $this->actingAs($this->mechanic)->get(route('services.show', $this->service));
    $response->assertStatus(200);
    $response->assertSee('Item Lama Tanpa ID Suku Cadang');
    $response->assertSee('LEGACY-001');

    // Menghapus item lama tanpa spare_part_id tetap berhasil dan tidak melempar error
    $deleteResponse = $this->actingAs($this->mechanic)->deleteJson(
        route('services.remove-item', ['service' => $this->service, 'item' => $legacyItem])
    );
    $deleteResponse->assertStatus(200);
    expect($this->service->fresh()->items()->count())->toBe(0);
});

test('(f) owner metrics in dashboard reflect updated stock quantity and inventory value', function () {
    // 1. Tambah part ke service
    $this->actingAs($this->mechanic)->postJson(route('services.add-item', $this->service), [
        'item_type' => ServiceItemType::Part->value,
        'spare_part_id' => $this->sparePart->id,
        'quantity' => 3,
    ])->assertStatus(200);

    // 2. Cek dashboard owner
    // Stok awal 10 - 3 = 7. Nilai inventaris = 7 * 750,000 = 5,250,000
    $response = $this->actingAs($this->owner)->get(route('dashboard'));
    $response->assertStatus(200);
    $response->assertViewHas('sparePartInventoryValue', 5250000.00);
    $response->assertViewHas('topUsedSpareParts', function ($topUsed) {
        return $topUsed->contains(function ($item) {
            return $item->name === 'Kampas Rem Brembo Original' && (float) $item->total_qty == 3;
        });
    });
});
