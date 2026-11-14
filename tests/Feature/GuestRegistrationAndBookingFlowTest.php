<?php

use App\Enums\CarCategory;
use App\Enums\UserRole;
use App\Models\Car;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Car::create([
        'slug' => 'mustang-gt',
        'brand' => 'Ford',
        'model_name' => 'Mustang GT',
        'year' => 2024,
        'category' => CarCategory::Sports,
        'color_name' => 'Race Red',
        'color_hex' => '#C41E3A',
        'price' => 980000000,
        'engine_specs' => ['power' => '450 HP', 'torque' => '529 Nm', 'accel' => '4.3s', 'trans' => '8AT'],
        'hero_image_transparent' => 'images/cars/ford-mustang-gt-2024.webp',
        'is_available' => true,
        'sort_order' => 1,
    ]);

    User::create([
        'name' => 'Sales Agent',
        'email' => 'sales@xyzmotors.com',
        'password' => bcrypt('password'),
        'role' => UserRole::Sales,
        'phone_number' => '081234567890',
        'is_active' => true,
    ]);
});

test('guest submitting test drive redirects to login with pending session and info message', function () {
    $car = Car::first();
    $tomorrow = Carbon::now()->addDays(2)->format('Y-m-d H:i:s');

    $response = $this->post('/test-drive', [
        'car_id' => $car->id,
        'preferred_date' => $tomorrow,
        'driver_license_id' => 'SIM-123456789',
        'pickup_location' => 'Showroom Pusat',
    ]);

    $response->assertRedirect(route('login'));
    $response->assertSessionHas('pending_test_drive');
    $response->assertSessionHas('info');
    $this->assertDatabaseCount('test_drives', 0);
});

test('guest submitting service booking redirects to login with pending session and info message', function () {
    $tomorrow = Carbon::now()->addDays(2)->setTime(10, 0)->format('Y-m-d H:i:s');

    $response = $this->post('/service-booking', [
        'vehicle_plate_number' => 'B 1234 ABC',
        'vehicle_model' => 'Mustang GT',
        'vehicle_year' => 2024,
        'odometer_km' => 15000,
        'service_type' => 'regular',
        'service_date' => $tomorrow,
        'complaint_notes' => 'Ganti oli mesin dan periksa rem kendaraan.',
    ]);

    $response->assertRedirect(route('login'));
    $response->assertSessionHas('pending_service_booking');
    $response->assertSessionHas('info');
    $this->assertDatabaseCount('services', 0);
});

test('login and register screens display registration link and pending booking notice', function () {
    $responseLogin = $this->withSession([
        'pending_service_booking' => ['vehicle_model' => 'Mustang GT'],
        'info' => 'Silakan masuk untuk melanjutkan booking Anda.',
    ])->get('/login');

    $responseLogin->assertStatus(200);
    $responseLogin->assertSee(route('register'));
    $responseLogin->assertSee('Daftar');
    $responseLogin->assertSee('Silakan masuk untuk melanjutkan booking Anda.');

    $responseRegister = $this->withSession([
        'pending_test_drive' => ['car_id' => 1],
    ])->get('/register');

    $responseRegister->assertStatus(200);
    $responseRegister->assertSee(route('login'));
    $responseRegister->assertSee('Data Booking');
});

test('new user registration restores pending_test_drive and redirects to home with open_modal without double booking', function () {
    $car = Car::first();
    $preferredDate = Carbon::now()->addDays(3)->format('Y-m-d H:i:s');

    $response = $this->withSession([
        'pending_test_drive' => [
            'car_id' => $car->id,
            'preferred_date' => $preferredDate,
            'driver_license_id' => 'SIM-998877',
            'pickup_location' => 'Dealer Utama',
            'notes' => 'Catatan test drive',
        ],
    ])->post('/register', [
        'name' => 'Customer Baru',
        'email' => 'baru@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('home'));
    $response->assertSessionHas('open_modal', 'test_drive');
    $response->assertSessionHas('pending_test_drive');
    $this->assertDatabaseCount('test_drives', 0);
});

test('new user registration restores pending_service_booking and redirects to home with open_modal without double booking', function () {
    $serviceDate = Carbon::now()->addDays(2)->setTime(10, 0)->format('Y-m-d H:i:s');

    $response = $this->withSession([
        'pending_service_booking' => [
            'vehicle_plate_number' => 'B 9999 NEW',
            'vehicle_model' => 'Mustang GT',
            'vehicle_year' => 2024,
            'odometer_km' => 5000,
            'service_type' => 'regular',
            'service_date' => $serviceDate,
            'complaint_notes' => 'Servis pertama setelah beli kendaraan.',
        ],
    ])->post('/register', [
        'name' => 'Customer Servis',
        'email' => 'servis@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('home'));
    $response->assertSessionHas('open_modal', 'service_booking');
    $response->assertSessionHas('pending_service_booking');
    $this->assertDatabaseCount('services', 0);
});

test('guest submitting test drive with license image does not store dead uploaded file in session and sets honest notice', function () {
    Storage::fake('public');
    $car = Car::first();
    $tomorrow = Carbon::now()->addDays(2)->format('Y-m-d H:i:s');

    $fakeFile = UploadedFile::fake()->create('sim.jpg', 100, 'image/jpeg');

    $response = $this->post('/test-drive', [
        'car_id' => $car->id,
        'preferred_date' => $tomorrow,
        'driver_license_id' => 'SIM-123456789',
        'driver_license_image' => $fakeFile,
    ]);

    $response->assertRedirect(route('login'));
    $response->assertSessionHas('pending_test_drive');
    $response->assertSessionHas('pending_test_drive_license_reupload_required', true);

    $savedSession = session('pending_test_drive');
    expect($savedSession)->not->toHaveKey('driver_license_image');
});

test('dedicated GET /test-drive and GET /service-booking pages render successfully without JavaScript', function () {
    $responseTd = $this->get('/test-drive');
    $responseTd->assertStatus(200);
    $responseTd->assertSee('Jadwalkan Test Drive');
    $responseTd->assertSee(route('test-drive.store'));

    $responseSrv = $this->get('/service-booking');
    $responseSrv->assertStatus(200);
    $responseSrv->assertSee('Booking Servis');
    $responseSrv->assertSee(route('service-booking.store'));
});

test('standard HTML POST with validation errors redirects back with errors and old input', function () {
    $response = $this->from('/service-booking')->post('/service-booking', [
        'vehicle_plate_number' => 'INVALID_PLAT',
    ]);

    $response->assertRedirect('/service-booking');
    $response->assertSessionHasErrors(['vehicle_plate_number', 'vehicle_model', 'service_date']);
});

test('showroom page displays login and register links for guest', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
    $response->assertSee(route('login'));
    $response->assertSee(route('register'));
});

test('service booking rejects appointment time outside operational hours', function () {
    $user = User::create([
        'name' => 'Pelanggan Malam',
        'email' => 'malam@example.com',
        'password' => bcrypt('password'),
        'role' => UserRole::Customer,
        'phone_number' => '081299887766',
        'is_active' => true,
    ]);

    $outsideHours = Carbon::now()->addDays(2)->setTime(21, 0)->format('Y-m-d H:i:s');

    $response = $this->actingAs($user)->post('/service-booking', [
        'vehicle_plate_number' => 'B 8888 MLM',
        'vehicle_model' => 'Mustang GT',
        'vehicle_year' => 2024,
        'odometer_km' => 10000,
        'service_type' => 'regular',
        'service_date' => $outsideHours,
        'complaint_notes' => 'Mencoba servis di luar jam operasional.',
    ]);

    $response->assertSessionHasErrors(['service_date']);
    $this->assertDatabaseCount('services', 0);
});
