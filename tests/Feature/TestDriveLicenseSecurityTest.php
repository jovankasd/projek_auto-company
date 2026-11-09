<?php

use App\Enums\CarCategory;
use App\Enums\TestDriveStatus;
use App\Enums\UserRole;
use App\Models\Car;
use App\Models\TestDrive;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
    Storage::fake('public');

    $this->car = Car::first() ?? Car::create([
        'slug' => 'test-tesla-model-3',
        'brand' => 'Tesla',
        'model_name' => 'Model 3',
        'year' => 2024,
        'category' => CarCategory::EV,
        'color_name' => 'Pearl White',
        'color_hex' => '#FFFFFF',
        'price' => 1200000000,
        'stock_quantity' => 5,
        'is_available' => true,
        'is_featured' => true,
    ]);

    $this->superAdmin = User::factory()->create([
        'role' => UserRole::SuperAdmin,
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $this->salesAssigned = User::factory()->create([
        'role' => UserRole::Sales,
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $this->salesOther = User::factory()->create([
        'role' => UserRole::Sales,
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $this->customerOwner = User::factory()->create([
        'role' => UserRole::Customer,
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $this->customerOther = User::factory()->create([
        'role' => UserRole::Customer,
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $this->mechanic = User::factory()->create([
        'role' => UserRole::Mekanik,
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $this->owner = User::factory()->create([
        'role' => UserRole::Owner,
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
});

test('new driver license image upload is stored on private local disk and not in public storage', function () {
    $file = UploadedFile::fake()->create('sim_budi.jpg', 50, 'image/jpeg');

    $response = $this->actingAs($this->customerOwner)->post('/test-drive', [
        'car_id' => $this->car->id,
        'preferred_date' => now()->addDays(2)->format('Y-m-d 10:00:00'),
        'driver_license_id' => 'SIM-3171987654321',
        'driver_license_image' => $file,
        'pickup_location' => 'Showroom Sudirman',
    ]);

    $response->assertSessionHas('success');

    $testDrive = TestDrive::where('driver_license_id', 'SIM-3171987654321')->first();
    expect($testDrive)->not->toBeNull();
    expect($testDrive->driver_license_image)->not->toBeNull();

    // Verify stored on private local disk, NOT public disk
    Storage::disk('local')->assertExists($testDrive->driver_license_image);
    Storage::disk('public')->assertMissing($testDrive->driver_license_image);
});

test('customer owner can view their driver license image with secure headers', function () {
    $filePath = 'licenses/test_sim_owner.jpg';
    Storage::disk('local')->put($filePath, 'fake-jpeg-content');

    $testDrive = TestDrive::create([
        'customer_id' => $this->customerOwner->id,
        'car_id' => $this->car->id,
        'sales_id' => $this->salesAssigned->id,
        'preferred_date' => now()->addDays(2),
        'driver_license_id' => 'SIM-001',
        'driver_license_image' => $filePath,
        'status' => TestDriveStatus::Requested,
    ]);

    $response = $this->actingAs($this->customerOwner)
        ->get(route('test-drives.license-image', $testDrive));

    $response->assertOk();
    $response->assertHeader('X-Content-Type-Options', 'nosniff');
    expect($response->headers->get('Cache-Control'))->toContain('private');
    expect($response->headers->get('Cache-Control'))->toContain('no-store');
});

test('assigned sales can view driver license image', function () {
    $filePath = 'licenses/test_sim_assigned.jpg';
    Storage::disk('local')->put($filePath, 'fake-jpeg-content');

    $testDrive = TestDrive::create([
        'customer_id' => $this->customerOwner->id,
        'car_id' => $this->car->id,
        'sales_id' => $this->salesAssigned->id,
        'preferred_date' => now()->addDays(2),
        'driver_license_id' => 'SIM-002',
        'driver_license_image' => $filePath,
        'status' => TestDriveStatus::Requested,
    ]);

    $response = $this->actingAs($this->salesAssigned)
        ->get(route('test-drives.license-image', $testDrive));

    $response->assertOk();
    $response->assertHeader('X-Content-Type-Options', 'nosniff');
});

test('super admin can view driver license image', function () {
    $filePath = 'licenses/test_sim_admin.jpg';
    Storage::disk('local')->put($filePath, 'fake-jpeg-content');

    $testDrive = TestDrive::create([
        'customer_id' => $this->customerOwner->id,
        'car_id' => $this->car->id,
        'sales_id' => $this->salesAssigned->id,
        'preferred_date' => now()->addDays(2),
        'driver_license_id' => 'SIM-003',
        'driver_license_image' => $filePath,
        'status' => TestDriveStatus::Requested,
    ]);

    $response = $this->actingAs($this->superAdmin)
        ->get(route('test-drives.license-image', $testDrive));

    $response->assertOk();
    $response->assertHeader('X-Content-Type-Options', 'nosniff');
});

test('unassigned or other sales cannot view driver license image (403)', function () {
    $filePath = 'licenses/test_sim_forbidden_sales.jpg';
    Storage::disk('local')->put($filePath, 'fake-jpeg-content');

    $testDrive = TestDrive::create([
        'customer_id' => $this->customerOwner->id,
        'car_id' => $this->car->id,
        'sales_id' => $this->salesAssigned->id,
        'preferred_date' => now()->addDays(2),
        'driver_license_id' => 'SIM-004',
        'driver_license_image' => $filePath,
        'status' => TestDriveStatus::Requested,
    ]);

    $response = $this->actingAs($this->salesOther)
        ->get(route('test-drives.license-image', $testDrive));

    $response->assertForbidden();
});

test('other customer cannot view driver license image (403)', function () {
    $filePath = 'licenses/test_sim_forbidden_cust.jpg';
    Storage::disk('local')->put($filePath, 'fake-jpeg-content');

    $testDrive = TestDrive::create([
        'customer_id' => $this->customerOwner->id,
        'car_id' => $this->car->id,
        'sales_id' => $this->salesAssigned->id,
        'preferred_date' => now()->addDays(2),
        'driver_license_id' => 'SIM-005',
        'driver_license_image' => $filePath,
        'status' => TestDriveStatus::Requested,
    ]);

    $response = $this->actingAs($this->customerOther)
        ->get(route('test-drives.license-image', $testDrive));

    $response->assertForbidden();
});

test('mechanic cannot view driver license image (403)', function () {
    $filePath = 'licenses/test_sim_mekanik.jpg';
    Storage::disk('local')->put($filePath, 'fake-jpeg-content');

    $testDrive = TestDrive::create([
        'customer_id' => $this->customerOwner->id,
        'car_id' => $this->car->id,
        'sales_id' => $this->salesAssigned->id,
        'preferred_date' => now()->addDays(2),
        'driver_license_id' => 'SIM-006',
        'driver_license_image' => $filePath,
        'status' => TestDriveStatus::Requested,
    ]);

    $response = $this->actingAs($this->mechanic)
        ->get(route('test-drives.license-image', $testDrive));

    $response->assertForbidden();
});

test('owner role cannot view driver license image (403)', function () {
    $filePath = 'licenses/test_sim_owner_role.jpg';
    Storage::disk('local')->put($filePath, 'fake-jpeg-content');

    $testDrive = TestDrive::create([
        'customer_id' => $this->customerOwner->id,
        'car_id' => $this->car->id,
        'sales_id' => $this->salesAssigned->id,
        'preferred_date' => now()->addDays(2),
        'driver_license_id' => 'SIM-007',
        'driver_license_image' => $filePath,
        'status' => TestDriveStatus::Requested,
    ]);

    $response = $this->actingAs($this->owner)
        ->get(route('test-drives.license-image', $testDrive));

    $response->assertForbidden();
});

test('guest is redirected to login when accessing license image route', function () {
    $filePath = 'licenses/test_sim_guest.jpg';
    Storage::disk('local')->put($filePath, 'fake-jpeg-content');

    $testDrive = TestDrive::create([
        'customer_id' => $this->customerOwner->id,
        'car_id' => $this->car->id,
        'sales_id' => $this->salesAssigned->id,
        'preferred_date' => now()->addDays(2),
        'driver_license_id' => 'SIM-008',
        'driver_license_image' => $filePath,
        'status' => TestDriveStatus::Requested,
    ]);

    $response = $this->get(route('test-drives.license-image', $testDrive));

    $response->assertRedirect(route('login'));
});

test('returns 404 if test drive does not have license image', function () {
    $testDrive = TestDrive::create([
        'customer_id' => $this->customerOwner->id,
        'car_id' => $this->car->id,
        'sales_id' => $this->salesAssigned->id,
        'preferred_date' => now()->addDays(2),
        'driver_license_id' => 'SIM-009',
        'driver_license_image' => null,
        'status' => TestDriveStatus::Requested,
    ]);

    $response = $this->actingAs($this->customerOwner)
        ->get(route('test-drives.license-image', $testDrive));

    $response->assertNotFound();
});

test('returns 404 if license image file is missing on storage disk', function () {
    $testDrive = TestDrive::create([
        'customer_id' => $this->customerOwner->id,
        'car_id' => $this->car->id,
        'sales_id' => $this->salesAssigned->id,
        'preferred_date' => now()->addDays(2),
        'driver_license_id' => 'SIM-010',
        'driver_license_image' => 'licenses/non_existent.jpg',
        'status' => TestDriveStatus::Requested,
    ]);

    $response = $this->actingAs($this->customerOwner)
        ->get(route('test-drives.license-image', $testDrive));

    $response->assertNotFound();
});

test('path traversal attempts in driver license image are safely rejected with 404', function () {
    $testDrive = TestDrive::create([
        'customer_id' => $this->customerOwner->id,
        'car_id' => $this->car->id,
        'sales_id' => $this->salesAssigned->id,
        'preferred_date' => now()->addDays(2),
        'driver_license_id' => 'SIM-011',
        'driver_license_image' => 'licenses/../../sensitive.txt',
        'status' => TestDriveStatus::Requested,
    ]);

    $response = $this->actingAs($this->customerOwner)
        ->get(route('test-drives.license-image', $testDrive));

    $response->assertNotFound();
});

test('fallback allows viewing legacy file in public storage before migration is run', function () {
    $filePath = 'licenses/legacy_sim.jpg';
    Storage::disk('public')->put($filePath, 'legacy-public-jpeg');
    // Ensure it is not in local
    Storage::disk('local')->assertMissing($filePath);

    $testDrive = TestDrive::create([
        'customer_id' => $this->customerOwner->id,
        'car_id' => $this->car->id,
        'sales_id' => $this->salesAssigned->id,
        'preferred_date' => now()->addDays(2),
        'driver_license_id' => 'SIM-012',
        'driver_license_image' => $filePath,
        'status' => TestDriveStatus::Requested,
    ]);

    $response = $this->actingAs($this->customerOwner)
        ->get(route('test-drives.license-image', $testDrive));

    $response->assertOk();
    $response->assertHeader('X-Content-Type-Options', 'nosniff');
});

test('protect license photos artisan command migrates public files to private disk with checksum verification', function () {
    // 1. Setup legacy files in public disk
    $content1 = 'test-sim-photo-content-1';
    $content2 = 'test-sim-photo-content-2';
    Storage::disk('public')->put('licenses/sim_legacy_1.jpg', $content1);
    Storage::disk('public')->put('licenses/sim_legacy_2.jpg', $content2);

    $td1 = TestDrive::create([
        'customer_id' => $this->customerOwner->id,
        'car_id' => $this->car->id,
        'sales_id' => $this->salesAssigned->id,
        'preferred_date' => now()->addDays(2),
        'driver_license_id' => 'SIM-MIG-1',
        'driver_license_image' => 'licenses/sim_legacy_1.jpg',
        'status' => TestDriveStatus::Requested,
    ]);

    $td2 = TestDrive::create([
        'customer_id' => $this->customerOwner->id,
        'car_id' => $this->car->id,
        'sales_id' => $this->salesAssigned->id,
        'preferred_date' => now()->addDays(2),
        'driver_license_id' => 'SIM-MIG-2',
        'driver_license_image' => 'licenses/sim_legacy_2.jpg',
        'status' => TestDriveStatus::Requested,
    ]);

    // 2. Test --dry-run
    $dryRunExit = Artisan::call('test-drives:protect-license-photos', ['--dry-run' => true]);
    expect($dryRunExit)->toBe(0);
    Storage::disk('local')->assertMissing('licenses/sim_legacy_1.jpg');
    Storage::disk('public')->assertExists('licenses/sim_legacy_1.jpg');

    // 3. Test migration without --delete-public (copies and verifies checksum, preserves public files)
    $migrateExit = Artisan::call('test-drives:protect-license-photos');
    expect($migrateExit)->toBe(0);

    Storage::disk('local')->assertExists('licenses/sim_legacy_1.jpg');
    Storage::disk('local')->assertExists('licenses/sim_legacy_2.jpg');
    Storage::disk('public')->assertExists('licenses/sim_legacy_1.jpg');
    Storage::disk('public')->assertExists('licenses/sim_legacy_2.jpg');

    expect(Storage::disk('local')->get('licenses/sim_legacy_1.jpg'))->toBe($content1);
    expect(Storage::disk('local')->get('licenses/sim_legacy_2.jpg'))->toBe($content2);

    // 4. Test migration with --delete-public --force (deletes public copies after verification)
    $deleteExit = Artisan::call('test-drives:protect-license-photos', [
        '--delete-public' => true,
        '--force' => true,
    ]);
    expect($deleteExit)->toBe(0);

    Storage::disk('local')->assertExists('licenses/sim_legacy_1.jpg');
    Storage::disk('local')->assertExists('licenses/sim_legacy_2.jpg');
    Storage::disk('public')->assertMissing('licenses/sim_legacy_1.jpg');
    Storage::disk('public')->assertMissing('licenses/sim_legacy_2.jpg');
});

test('sales cannot view license image when lead is not yet assigned (sales_id is null)', function () {
    $filePath = 'licenses/test_sim_unassigned.jpg';
    Storage::disk('local')->put($filePath, 'fake-jpeg-content');

    $testDrive = TestDrive::create([
        'customer_id' => $this->customerOwner->id,
        'car_id' => $this->car->id,
        'sales_id' => null,
        'preferred_date' => now()->addDays(2),
        'driver_license_id' => 'SIM-UNASSIGNED',
        'driver_license_image' => $filePath,
        'status' => TestDriveStatus::Requested,
    ]);

    $response = $this->actingAs($this->salesAssigned)
        ->get(route('test-drives.license-image', $testDrive));

    $response->assertForbidden();
});

test('non-licenses directory stored in driver license image is rejected with 404', function () {
    $testDrive = TestDrive::create([
        'customer_id' => $this->customerOwner->id,
        'car_id' => $this->car->id,
        'sales_id' => $this->salesAssigned->id,
        'preferred_date' => now()->addDays(2),
        'driver_license_id' => 'SIM-AVATAR',
        'driver_license_image' => 'avatars/sample.jpg',
        'status' => TestDriveStatus::Requested,
    ]);

    $response = $this->actingAs($this->customerOwner)
        ->get(route('test-drives.license-image', $testDrive));

    $response->assertNotFound();
});

test('driver license image path with public prefix is normalized and served properly', function () {
    $filePath = 'licenses/test_sim_public_prefix.jpg';
    Storage::disk('local')->put($filePath, 'fake-jpeg-content');

    $testDrive = TestDrive::create([
        'customer_id' => $this->customerOwner->id,
        'car_id' => $this->car->id,
        'sales_id' => $this->salesAssigned->id,
        'preferred_date' => now()->addDays(2),
        'driver_license_id' => 'SIM-PREFIX',
        'driver_license_image' => 'public/licenses/test_sim_public_prefix.jpg',
        'status' => TestDriveStatus::Requested,
    ]);

    $response = $this->actingAs($this->customerOwner)
        ->get(route('test-drives.license-image', $testDrive));

    $response->assertOk();
    $response->assertHeader('X-Content-Type-Options', 'nosniff');
    $response->assertHeader('X-Frame-Options');
});

test('migration command ignores gitignore and handles overwrite with checksum verification', function () {
    Storage::disk('public')->put('licenses/.gitignore', "*\n!.gitignore");
    Storage::disk('public')->put('licenses/sim_overwrite.jpg', 'new-content-from-public');
    Storage::disk('local')->put('licenses/sim_overwrite.jpg', 'old-corrupted-content');

    $td = TestDrive::create([
        'customer_id' => $this->customerOwner->id,
        'car_id' => $this->car->id,
        'sales_id' => $this->salesAssigned->id,
        'preferred_date' => now()->addDays(2),
        'driver_license_id' => 'SIM-OVERWRITE',
        'driver_license_image' => 'licenses/sim_overwrite.jpg',
        'status' => TestDriveStatus::Requested,
    ]);

    $exitCode = Artisan::call('test-drives:protect-license-photos', [
        '--delete-public' => true,
        '--force' => true,
    ]);

    expect($exitCode)->toBe(0);
    // .gitignore must not be deleted from public disk
    Storage::disk('public')->assertExists('licenses/.gitignore');
    // Overwritten file in local must match new public content
    expect(Storage::disk('local')->get('licenses/sim_overwrite.jpg'))->toBe('new-content-from-public');
    // Public copy must be safely removed after verification
    Storage::disk('public')->assertMissing('licenses/sim_overwrite.jpg');
});
