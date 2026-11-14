<?php

use App\Enums\CarCategory;
use App\Enums\TestDriveStatus;
use App\Enums\UserRole;
use App\Models\Car;
use App\Models\TestDrive;
use App\Models\User;
use App\Notifications\TestDriveApproved;
use App\Notifications\TestDriveCompleted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create([
        'name' => 'Super Admin',
        'role' => UserRole::SuperAdmin,
    ]);

    $this->salesA = User::factory()->create([
        'name' => 'Sales Officer A',
        'role' => UserRole::Sales,
    ]);

    $this->salesB = User::factory()->create([
        'name' => 'Sales Officer B',
        'role' => UserRole::Sales,
    ]);

    $this->customer = User::factory()->create([
        'name' => 'Customer A',
        'role' => UserRole::Customer,
    ]);

    $this->otherCustomer = User::factory()->create([
        'name' => 'Customer B',
        'role' => UserRole::Customer,
    ]);

    $this->car = Car::create([
        'slug' => 'porsche-911-gt3-rs',
        'brand' => 'Porsche',
        'model_name' => '911 GT3 RS',
        'year' => 2024,
        'category' => CarCategory::Sports,
        'color_name' => 'Guards Red',
        'color_hex' => '#D50000',
        'price' => 4500000000,
        'stock_quantity' => 2,
        'is_available' => true,
    ]);

    $this->testDriveA = TestDrive::create([
        'customer_id' => $this->customer->id,
        'car_id' => $this->car->id,
        'sales_id' => $this->salesA->id,
        'preferred_date' => Carbon::now()->addDays(2)->setHour(10)->setMinute(0),
        'driver_license_id' => 'SIM-1234567890',
        'pickup_location' => 'Showroom Sudirman',
        'status' => TestDriveStatus::Requested,
        'sales_notes' => 'Customer tertarik opsi warna Guards Red.',
    ]);
});

test('sales B receives 403 when viewing or accessing detail of lead belonging to sales A', function () {
    // Sales B gets 403 on show route
    $responseB = $this->actingAs($this->salesB)->get(route('test-drives.show', $this->testDriveA));
    $responseB->assertStatus(403);

    // Another customer receives 403
    $responseOtherCust = $this->actingAs($this->otherCustomer)->get(route('test-drives.show', $this->testDriveA));
    $responseOtherCust->assertStatus(403);

    // Sales A (owner) can view
    $responseA = $this->actingAs($this->salesA)->get(route('test-drives.show', $this->testDriveA));
    $responseA->assertStatus(200);

    // Customer owner can view
    $responseCust = $this->actingAs($this->customer)->get(route('test-drives.show', $this->testDriveA));
    $responseCust->assertStatus(200);

    // Super Admin can view
    $responseAdmin = $this->actingAs($this->admin)->get(route('test-drives.show', $this->testDriveA));
    $responseAdmin->assertStatus(200);
});

test('sales B receives 403 when trying to approve or reject lead belonging to sales A', function () {
    // Sales B cannot approve
    $resApprove = $this->actingAs($this->salesB)->patch(route('test-drives.approve', $this->testDriveA), [
        'sales_notes' => 'Mencoba approve lead orang lain',
    ]);
    $resApprove->assertStatus(403);
    expect($this->testDriveA->fresh()->status)->toBe(TestDriveStatus::Requested);

    // Sales B cannot reject
    $resReject = $this->actingAs($this->salesB)->patch(route('test-drives.reject', $this->testDriveA), [
        'rejection_reason' => 'Mencoba reject lead orang lain',
    ]);
    $resReject->assertStatus(403);
    expect($this->testDriveA->fresh()->status)->toBe(TestDriveStatus::Requested);
});

test('approve and reject are only valid from requested status', function () {
    // 1. Sales A approves from requested -> succeeds
    $responseApprove = $this->actingAs($this->salesA)->patch(route('test-drives.approve', $this->testDriveA), [
        'sales_notes' => 'Jadwal test drive disetujui.',
    ]);
    $responseApprove->assertRedirect();

    $fresh = $this->testDriveA->fresh();
    expect($fresh->status)->toBe(TestDriveStatus::Approved);
    expect($fresh->approved_at)->not->toBeNull();

    // 2. Cannot approve again once already approved
    $resApproveAgain = $this->actingAs($this->salesA)->patch(route('test-drives.approve', $fresh));
    $resApproveAgain->assertStatus(403);

    // 3. Cannot reject once already approved
    $resRejectApproved = $this->actingAs($this->salesA)->patch(route('test-drives.reject', $fresh), [
        'rejection_reason' => 'Batal setelah disetujui',
    ]);
    $resRejectApproved->assertStatus(403);
});

test('sales A and super admin can reject lead directly from requested status', function () {
    $resReject = $this->actingAs($this->salesA)->patch(route('test-drives.reject', $this->testDriveA), [
        'rejection_reason' => 'Unit sedang dalam jadwal servis berkala.',
    ]);
    $resReject->assertRedirect();

    $fresh = $this->testDriveA->fresh();
    expect($fresh->status)->toBe(TestDriveStatus::Rejected);
    expect($fresh->rejection_reason)->toBe('Unit sedang dalam jadwal servis berkala.');

    // Once rejected, cannot approve
    $resApproveRejected = $this->actingAs($this->salesA)->patch(route('test-drives.approve', $fresh));
    $resApproveRejected->assertStatus(403);
});

test('transition approved to completed is available and authorized for assigned sales and super admin', function () {
    // Set to approved
    $this->testDriveA->update([
        'status' => TestDriveStatus::Approved,
        'approved_at' => now(),
    ]);

    // Unassigned sales B gets 403
    $resB = $this->actingAs($this->salesB)->patch(route('test-drives.complete', $this->testDriveA));
    $resB->assertStatus(403);

    // Customer gets 403
    $resCust = $this->actingAs($this->customer)->patch(route('test-drives.complete', $this->testDriveA));
    $resCust->assertStatus(403);

    // Assigned Sales A completes -> succeeds
    $resA = $this->actingAs($this->salesA)->patch(route('test-drives.complete', $this->testDriveA), [
        'sales_notes' => 'Customer sangat puas setelah mencoba 911 GT3 RS di rute Sudirman-Thamrin.',
    ]);
    $resA->assertRedirect();

    $fresh = $this->testDriveA->fresh();
    expect($fresh->status)->toBe(TestDriveStatus::Completed);
    expect($fresh->completed_at)->not->toBeNull();
});

test('transition cannot jump from requested directly to completed', function () {
    // Attempting to complete directly from requested status is forbidden
    $resDirect = $this->actingAs($this->salesA)->patch(route('test-drives.complete', $this->testDriveA));
    $resDirect->assertStatus(403);
    expect($this->testDriveA->fresh()->status)->toBe(TestDriveStatus::Requested);
});

test('customer owner and assigned sales can cancel test drive from requested or approved status', function () {
    // 1. Customer cancels from requested
    $resCancelCust = $this->actingAs($this->customer)->patch(route('test-drives.cancel', $this->testDriveA), [
        'cancellation_reason' => 'Ada keperluan mendadak di luar kota.',
    ]);
    $resCancelCust->assertRedirect();

    $fresh = $this->testDriveA->fresh();
    expect($fresh->status)->toBe(TestDriveStatus::Cancelled);
    expect($fresh->rejection_reason)->toBe('Ada keperluan mendadak di luar kota.');

    // 2. Create another test drive in approved state
    $approvedTd = TestDrive::create([
        'customer_id' => $this->customer->id,
        'car_id' => $this->car->id,
        'sales_id' => $this->salesA->id,
        'preferred_date' => Carbon::now()->addDays(3)->setHour(14),
        'driver_license_id' => 'SIM-99998888',
        'status' => TestDriveStatus::Approved,
        'approved_at' => now(),
    ]);

    // Unassigned sales B cannot cancel
    $resCancelB = $this->actingAs($this->salesB)->patch(route('test-drives.cancel', $approvedTd));
    $resCancelB->assertStatus(403);

    // Another customer cannot cancel
    $resCancelOther = $this->actingAs($this->otherCustomer)->patch(route('test-drives.cancel', $approvedTd));
    $resCancelOther->assertStatus(403);

    // Assigned Sales A cancels approved test drive -> succeeds
    $resCancelA = $this->actingAs($this->salesA)->patch(route('test-drives.cancel', $approvedTd), [
        'cancellation_reason' => 'Cuaca buruk dan hujan badai di rute penjemputan.',
    ]);
    $resCancelA->assertRedirect();
    expect($approvedTd->fresh()->status)->toBe(TestDriveStatus::Cancelled);
});

test('terminal states (completed, rejected, cancelled) cannot be mutated via direct requests', function () {
    // 1. Completed test drive
    $completedTd = TestDrive::create([
        'customer_id' => $this->customer->id,
        'car_id' => $this->car->id,
        'sales_id' => $this->salesA->id,
        'preferred_date' => Carbon::now()->subDays(1),
        'driver_license_id' => 'SIM-11112222',
        'status' => TestDriveStatus::Completed,
        'completed_at' => now()->subDay(),
    ]);

    $this->actingAs($this->salesA)->patch(route('test-drives.approve', $completedTd))->assertStatus(403);
    $this->actingAs($this->salesA)->patch(route('test-drives.reject', $completedTd), ['rejection_reason' => 'X'])->assertStatus(403);
    $this->actingAs($this->salesA)->patch(route('test-drives.complete', $completedTd))->assertStatus(403);
    $this->actingAs($this->salesA)->patch(route('test-drives.cancel', $completedTd))->assertStatus(403);
    expect($completedTd->fresh()->status)->toBe(TestDriveStatus::Completed);

    // 2. Rejected test drive
    $rejectedTd = TestDrive::create([
        'customer_id' => $this->customer->id,
        'car_id' => $this->car->id,
        'sales_id' => $this->salesA->id,
        'preferred_date' => Carbon::now()->addDays(2),
        'driver_license_id' => 'SIM-33334444',
        'status' => TestDriveStatus::Rejected,
        'rejection_reason' => 'Jadwal bentrok',
    ]);

    $this->actingAs($this->salesA)->patch(route('test-drives.approve', $rejectedTd))->assertStatus(403);
    $this->actingAs($this->salesA)->patch(route('test-drives.complete', $rejectedTd))->assertStatus(403);
    $this->actingAs($this->salesA)->patch(route('test-drives.cancel', $rejectedTd))->assertStatus(403);
    expect($rejectedTd->fresh()->status)->toBe(TestDriveStatus::Rejected);

    // 3. Cancelled test drive
    $cancelledTd = TestDrive::create([
        'customer_id' => $this->customer->id,
        'car_id' => $this->car->id,
        'sales_id' => $this->salesA->id,
        'preferred_date' => Carbon::now()->addDays(2),
        'driver_license_id' => 'SIM-55556666',
        'status' => TestDriveStatus::Cancelled,
        'rejection_reason' => 'Pelanggan membatalkan',
    ]);

    $this->actingAs($this->salesA)->patch(route('test-drives.approve', $cancelledTd))->assertStatus(403);
    $this->actingAs($this->salesA)->patch(route('test-drives.reject', $cancelledTd), ['rejection_reason' => 'X'])->assertStatus(403);
    $this->actingAs($this->salesA)->patch(route('test-drives.complete', $cancelledTd))->assertStatus(403);
    $this->actingAs($this->salesA)->patch(route('test-drives.cancel', $cancelledTd))->assertStatus(403);
    expect($cancelledTd->fresh()->status)->toBe(TestDriveStatus::Cancelled);
});

test('eloquent model level guard throws DomainException when attempting illegal transition on terminal test drive', function () {
    $completedTd = TestDrive::create([
        'customer_id' => $this->customer->id,
        'car_id' => $this->car->id,
        'sales_id' => $this->salesA->id,
        'preferred_date' => Carbon::now()->subDays(1),
        'driver_license_id' => 'SIM-11112222',
        'status' => TestDriveStatus::Completed,
        'completed_at' => now()->subDay(),
    ]);

    expect(function () use ($completedTd) {
        $completedTd->update(['status' => TestDriveStatus::Approved]);
    })->toThrow(DomainException::class);
});

test('sales dashboard listing strictly uses forSales and enforces ownership boundary BR-07', function () {
    // Lead for Sales A
    $leadA = $this->testDriveA;

    // Lead for Sales B
    $leadB = TestDrive::create([
        'customer_id' => $this->otherCustomer->id,
        'car_id' => $this->car->id,
        'sales_id' => $this->salesB->id,
        'preferred_date' => Carbon::now()->addDays(4)->setHour(11),
        'driver_license_id' => 'SIM-BBBB9999',
        'pickup_location' => 'Showroom Kelapa Gading',
        'status' => TestDriveStatus::Requested,
    ]);

    // Sales A views dashboard: sees lead A, does NOT see lead B
    $resA = $this->actingAs($this->salesA)->get(route('dashboard'));
    $resA->assertOk();
    $resA->assertSee($leadA->customer->name);
    $resA->assertDontSee($leadB->customer->name);

    // Sales B views dashboard: sees lead B, does NOT see lead A
    $resB = $this->actingAs($this->salesB)->get(route('dashboard'));
    $resB->assertOk();
    $resB->assertSee($leadB->customer->name);
    $resB->assertDontSee($leadA->customer->name);
});

test('notifications are triggered appropriately on test drive state changes', function () {
    Notification::fake();

    // 1. Approve
    $this->actingAs($this->salesA)->patch(route('test-drives.approve', $this->testDriveA));
    Notification::assertSentTo($this->customer, TestDriveApproved::class);

    // 2. Complete
    $this->actingAs($this->salesA)->patch(route('test-drives.complete', $this->testDriveA));
    Notification::assertSentTo($this->customer, TestDriveCompleted::class);
    Notification::assertSentTo($this->salesA, TestDriveCompleted::class);
});
