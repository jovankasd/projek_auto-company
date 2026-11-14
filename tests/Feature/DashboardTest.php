<?php

namespace Tests\Feature;

use App\Enums\CarCategory;
use App\Enums\DiscountType;
use App\Enums\ServiceItemType;
use App\Enums\ServiceStatus;
use App\Enums\SparePartCategory;
use App\Enums\TestDriveStatus;
use App\Enums\UserRole;
use App\Models\Car;
use App\Models\Service;
use App\Models\Setting;
use App\Models\SparePart;
use App\Models\TestDrive;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected User $sales;

    protected User $mekanik;

    protected User $customer;

    protected User $otherCustomer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::factory()->create([
            'role' => UserRole::SuperAdmin,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $this->sales = User::factory()->create([
            'role' => UserRole::Sales,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $this->mekanik = User::factory()->create([
            'role' => UserRole::Mekanik,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $this->customer = User::factory()->create([
            'role' => UserRole::Customer,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $this->otherCustomer = User::factory()->create([
            'role' => UserRole::Customer,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
    }

    public function test_customer_sees_customer_dashboard(): void
    {
        $response = $this->actingAs($this->customer)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Portal Pelanggan Dealer Pro');
        $response->assertSee('Servis Aktif');
        $response->assertDontSee('Executive Control Panel');
        $response->assertDontSee('Antrean Work Order Bengkel');
    }

    public function test_mekanik_sees_mekanik_dashboard_without_sales_menus(): void
    {
        $response = $this->actingAs($this->mekanik)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Antrean Work Order Bengkel');
        $response->assertDontSee('Katalog Mobil');
        $response->assertDontSee('Program Promo');
        $response->assertDontSee('Executive Control Panel');
    }

    public function test_sales_sees_sales_dashboard(): void
    {
        $response = $this->actingAs($this->sales)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Manajemen Lead Test Drive');
        $response->assertSee('Katalog Mobil');
    }

    public function test_super_admin_sees_executive_dashboard_with_kpi(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Executive Control Panel');
        $response->assertSee('Booking Servis Bulan Ini');
        $response->assertSee('Unit Mobil Aktif');
        $response->assertSeeText('Laporan Keuangan & Operasional', false);
    }

    public function test_mekanik_access_to_cars_route_is_forbidden_403(): void
    {
        $response = $this->actingAs($this->mekanik)->get(route('cars.index'));

        $response->assertForbidden();
    }

    public function test_sales_and_super_admin_can_access_cars_route(): void
    {
        $this->actingAs($this->sales)->get(route('cars.index'))->assertOk();
        $this->actingAs($this->superAdmin)->get(route('cars.index'))->assertOk();
    }

    public function test_customer_only_sees_their_own_services(): void
    {
        $myService = Service::create([
            'ticket_number' => 'SRV-20261001-0001',
            'customer_id' => $this->customer->id,
            'vehicle_plate_number' => 'B 1111 MY',
            'vehicle_model' => 'Ford Mustang GT',
            'vehicle_year' => 2024,
            'odometer_km' => 10000,
            'service_type' => 'regular',
            'service_date' => now(),
            'slot_hour' => 9,
            'complaint_notes' => 'Ganti oli rutin.',
            'status' => ServiceStatus::Pending,
        ]);

        $otherService = Service::create([
            'ticket_number' => 'SRV-20261001-0002',
            'customer_id' => $this->otherCustomer->id,
            'vehicle_plate_number' => 'B 2222 OT',
            'vehicle_model' => 'BMW M4 Competition',
            'vehicle_year' => 2024,
            'odometer_km' => 5000,
            'service_type' => 'regular',
            'service_date' => now(),
            'slot_hour' => 10,
            'complaint_notes' => 'Pemeriksaan mesin.',
            'status' => ServiceStatus::Pending,
        ]);

        $response = $this->actingAs($this->customer)->get(route('services.my'));

        $response->assertOk();
        $response->assertSee('B 1111 MY');
        $response->assertDontSee('B 2222 OT');
    }

    public function test_updating_service_status_creates_service_status_log(): void
    {
        $service = Service::create([
            'ticket_number' => 'SRV-20261001-0010',
            'customer_id' => $this->customer->id,
            'vehicle_plate_number' => 'B 3333 LOG',
            'vehicle_model' => 'Tesla Model 3',
            'vehicle_year' => 2024,
            'odometer_km' => 8000,
            'service_type' => 'regular',
            'service_date' => now(),
            'slot_hour' => 10,
            'complaint_notes' => 'Rotasi ban.',
            'assigned_mechanic_id' => $this->mekanik->id,
            'status' => ServiceStatus::Confirmed,
        ]);

        $response = $this->actingAs($this->mekanik)->patch(route('services.update-status', $service), [
            'status' => ServiceStatus::InProgress->value,
            'notes' => 'Unit masuk ke stall nomor 2.',
        ]);

        $response->assertRedirect();
        $this->assertEquals(ServiceStatus::InProgress, $service->fresh()->status);
        $this->assertNotNull($service->fresh()->started_at);

        $this->assertDatabaseHas('service_status_logs', [
            'service_id' => $service->id,
            'from_status' => ServiceStatus::Confirmed->value,
            'to_status' => ServiceStatus::InProgress->value,
            'changed_by' => $this->mekanik->id,
            'notes' => 'Unit masuk ke stall nomor 2.',
        ]);
    }

    public function test_completing_service_without_items_is_rejected(): void
    {
        $service = Service::create([
            'ticket_number' => 'SRV-20261001-0011',
            'customer_id' => $this->customer->id,
            'vehicle_plate_number' => 'B 4444 NOITEM',
            'vehicle_model' => 'Toyota Alphard',
            'vehicle_year' => 2023,
            'odometer_km' => 20000,
            'service_type' => 'regular',
            'service_date' => now(),
            'slot_hour' => 11,
            'complaint_notes' => 'Pemeriksaan rem.',
            'assigned_mechanic_id' => $this->mekanik->id,
            'status' => ServiceStatus::InProgress,
        ]);

        // Attempt to complete without items
        $response = $this->actingAs($this->mekanik)->patch(route('services.update-status', $service), [
            'status' => ServiceStatus::Completed->value,
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals(ServiceStatus::InProgress, $service->fresh()->status);
    }

    public function test_adding_item_to_service_recalculates_total_cost(): void
    {
        $service = Service::create([
            'ticket_number' => 'SRV-20261001-0012',
            'customer_id' => $this->customer->id,
            'vehicle_plate_number' => 'B 5555 ITEM',
            'vehicle_model' => 'Ford Mustang GT',
            'vehicle_year' => 2024,
            'odometer_km' => 15000,
            'service_type' => 'regular',
            'service_date' => now(),
            'slot_hour' => 13,
            'complaint_notes' => 'Ganti kanvas rem.',
            'assigned_mechanic_id' => $this->mekanik->id,
            'status' => ServiceStatus::InProgress,
            'total_cost' => 0,
        ]);

        $part = SparePart::create([
            'name' => 'Brake Pad OEM',
            'slug' => 'brake-pad-oem',
            'part_number' => 'BP-OEM-01',
            'brand' => 'Brembo',
            'category' => SparePartCategory::Brake,
            'price' => 500000,
            'stock_quantity' => 10,
            'is_available' => true,
        ]);

        $response = $this->actingAs($this->mekanik)->post(route('services.add-item', $service), [
            'item_type' => ServiceItemType::Part->value,
            'spare_part_id' => $part->id,
            'quantity' => 2,
        ]);

        $response->assertRedirect();
        $this->assertEquals(1000000.00, (float) $service->fresh()->total_cost);
        $this->assertDatabaseHas('service_items', [
            'service_id' => $service->id,
            'name' => 'Brake Pad OEM',
            'subtotal' => 1000000.00,
        ]);
    }

    public function test_sales_can_approve_test_drive(): void
    {
        $car = Car::create([
            'slug' => 'test-car-mustang',
            'brand' => 'Ford',
            'model_name' => 'Mustang GT',
            'year' => 2024,
            'category' => CarCategory::Sports,
            'color_name' => 'Red',
            'color_hex' => '#FF0000',
            'price' => 900000000,
            'stock_quantity' => 2,
            'is_available' => true,
        ]);

        $testDrive = TestDrive::create([
            'customer_id' => $this->customer->id,
            'car_id' => $car->id,
            'sales_id' => $this->sales->id,
            'preferred_date' => now()->addDays(2),
            'driver_license_id' => '1234567890',
            'status' => TestDriveStatus::Requested,
        ]);

        $response = $this->actingAs($this->sales)->patch(route('test-drives.approve', $testDrive), [
            'sales_notes' => 'Unit siap dicoba jam 10 pagi.',
        ]);

        $response->assertRedirect();
        $this->assertEquals(TestDriveStatus::Approved, $testDrive->fresh()->status);
        $this->assertNotNull($testDrive->fresh()->approved_at);
    }

    public function test_sales_can_reject_test_drive_with_reason(): void
    {
        $car = Car::create([
            'slug' => 'test-car-bmw',
            'brand' => 'BMW',
            'model_name' => 'M4 Competition',
            'year' => 2024,
            'category' => CarCategory::Sports,
            'color_name' => 'Grey',
            'color_hex' => '#666666',
            'price' => 2000000000,
            'stock_quantity' => 1,
            'is_available' => true,
        ]);

        $testDrive = TestDrive::create([
            'customer_id' => $this->customer->id,
            'car_id' => $car->id,
            'sales_id' => $this->sales->id,
            'preferred_date' => now()->addDays(3),
            'driver_license_id' => '1234567890',
            'status' => TestDriveStatus::Requested,
        ]);

        $response = $this->actingAs($this->sales)->patch(route('test-drives.reject', $testDrive), [
            'rejection_reason' => 'Unit sedang dalam pemeliharaan berkala.',
        ]);

        $response->assertRedirect();
        $this->assertEquals(TestDriveStatus::Rejected, $testDrive->fresh()->status);
        $this->assertEquals('Unit sedang dalam pemeliharaan berkala.', $testDrive->fresh()->rejection_reason);
    }

    public function test_super_admin_can_assign_mechanic(): void
    {
        $service = Service::create([
            'ticket_number' => 'SRV-20261001-0020',
            'customer_id' => $this->customer->id,
            'vehicle_plate_number' => 'B 7777 ASSIGN',
            'vehicle_model' => 'Ford Ranger Raptor',
            'vehicle_year' => 2024,
            'odometer_km' => 12000,
            'service_type' => 'regular',
            'service_date' => now(),
            'slot_hour' => 14,
            'complaint_notes' => 'Servis 10k.',
            'status' => ServiceStatus::Pending,
        ]);

        $response = $this->actingAs($this->superAdmin)->patch(route('services.assign', $service), [
            'assigned_mechanic_id' => $this->mekanik->id,
        ]);

        $response->assertRedirect();
        $this->assertEquals($this->mekanik->id, $service->fresh()->assigned_mechanic_id);
        $this->assertEquals(ServiceStatus::Confirmed, $service->fresh()->status);
        $this->assertNotNull($service->fresh()->confirmed_at);
    }

    public function test_promo_crud_validates_ends_at_after_starts_at(): void
    {
        $response = $this->actingAs($this->sales)->post(route('promos.store'), [
            'code' => 'INVALIDDATE',
            'title' => 'Promo Tanggal Salah',
            'discount_type' => DiscountType::Percentage->value,
            'discount_value' => 15,
            'starts_at' => now()->addDays(10)->toDateTimeString(),
            'ends_at' => now()->addDays(5)->toDateTimeString(), // Invalid: ends before starts
        ]);

        $response->assertSessionHasErrors('ends_at');
    }

    public function test_settings_save_and_cache_invalidation(): void
    {
        Setting::set('booking.max_per_slot', 3, 'booking', 'integer');
        $this->assertEquals(3, Setting::get('booking.max_per_slot'));

        $response = $this->actingAs($this->superAdmin)->patch(route('settings.update'), [
            'booking_max_per_slot' => 4,
            'contact_whatsapp' => '628999888777',
        ]);

        $response->assertRedirect();
        $this->assertEquals(4, Setting::get('booking.max_per_slot'));
        $this->assertEquals('628999888777', Setting::get('contact.whatsapp'));
    }

    public function test_mekanik_dashboard_displays_spare_parts_catalog_with_valid_images(): void
    {
        $sparePart = SparePart::create([
            'slug' => 'brake-pad-set-sport-brk-frd-999',
            'name' => 'Brake Pad Set Sport',
            'part_number' => 'BRK-FRD-999',
            'brand' => 'Brembo',
            'category' => SparePartCategory::Brake,
            'price' => 2500000,
            'stock_quantity' => 10,
            'min_stock_alert' => 3,
            'is_available' => true,
        ]);

        $response = $this->actingAs($this->mekanik)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Katalog Suku Cadang');
        $response->assertSee('Brake Pad Set Sport');
        $response->assertSee('Pasang ke Mobil Servis');
        $this->assertNotEmpty($sparePart->imageUrl);
    }

    public function test_super_admin_can_submit_executive_report_to_owner(): void
    {
        $owner = User::factory()->create([
            'role' => UserRole::Owner,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->superAdmin)->post(route('reports.submit-to-owner'), [
            'note' => 'Laporan mingguan 3 divisi telah diverifikasi.',
        ]);

        $response->assertRedirect();
        $this->assertNotNull(Setting::get('last_report_submitted_at'));
        $this->assertEquals('Laporan mingguan 3 divisi telah diverifikasi.', Setting::get('last_report_note'));
    }

    public function test_mekanik_can_login_via_hidden_logind_and_render_work_orders(): void
    {
        Service::create([
            'ticket_number' => 'SRV-20261109-0099',
            'customer_id' => $this->customer->id,
            'vehicle_plate_number' => 'B 9999 MEK',
            'vehicle_model' => 'Porsche 911 Carrera',
            'vehicle_year' => 2024,
            'odometer_km' => 5000,
            'service_type' => 'regular',
            'service_date' => now(),
            'slot_hour' => 9,
            'complaint_notes' => 'Pengecekan oli dan kampas rem.',
            'assigned_mechanic_id' => $this->mekanik->id,
            'status' => ServiceStatus::InProgress,
        ]);

        $this->get('/logind')->assertOk();

        $loginResponse = $this->post('/logind', [
            'email' => $this->mekanik->email,
            'password' => 'password',
        ]);

        $loginResponse->assertRedirect(route('dashboard', absolute: false));

        $dashboardResponse = $this->actingAs($this->mekanik)->get(route('dashboard'));
        $dashboardResponse->assertOk();
        $dashboardResponse->assertSee('SRV-20261109-0099');
        $dashboardResponse->assertSee('B 9999 MEK');
        $dashboardResponse->assertSee('Porsche 911 Carrera');
    }
}
