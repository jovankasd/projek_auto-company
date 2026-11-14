<?php

use App\Enums\CarCategory;
use App\Enums\SparePartCategory;
use App\Enums\UserRole;
use App\Models\Car;
use App\Models\ContactInquiry;
use App\Models\SparePart;
use App\Models\User;
use App\Notifications\ContactInquiryNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

test('can view public car detail page with specifications and seo metadata', function () {
    $car = Car::create([
        'slug' => 'tesla-model-3-performance-2024',
        'brand' => 'Tesla',
        'model_name' => 'Model 3 Performance',
        'year' => 2024,
        'category' => CarCategory::Sedan,
        'price' => 1250000000,
        'color_name' => 'Solid Black',
        'color_hex' => '#000000',
        'engine_specs' => [
            'power' => '510 HP',
            'torque' => '650 Nm',
            'accel' => '3.1 detik',
            'trans' => 'Single-speed',
        ],
        'dimensions' => [
            'length' => '4720 mm',
            'width' => '1850 mm',
            'height' => '1440 mm',
        ],
        'features' => ['Autopilot Hardware 4.0', 'Adaptive Suspension', 'Track Mode V3'],
        'description' => 'Sedan performa tinggi bertenaga listrik murni dengan akselerasi instan.',
        'hero_image_transparent' => 'images/cars/tesla-model-3.webp',
        'is_available' => true,
        'is_featured' => true,
        'meta_title' => 'Tesla Model 3 Performance 2024 - Dealer Resmi XYZ Motors',
        'meta_description' => 'Spesifikasi lengkap dan harga resmi Tesla Model 3 Performance 2024 di XYZ Motors Jakarta.',
    ]);

    $response = $this->get(route('cars.public.show', $car->slug));

    $response->assertOk();
    $response->assertSee('Tesla Model 3 Performance');
    $response->assertSee('510 HP');
    $response->assertSee('650 Nm');
    $response->assertSee('3.1 detik');
    $response->assertSee('Single-speed');
    $response->assertSee($car->price_formatted);
    $response->assertSee('Autopilot Hardware 4.0');
    $response->assertSee('Tesla Model 3 Performance 2024 - Dealer Resmi XYZ Motors');
    $response->assertSee('og:title', false);
    $response->assertSee('Test Drive Unit Ini', false);
    $response->assertSee('name="driver_license_id"', false);
    $response->assertSee('name="preferred_date"', false);
});

test('cannot view unavailable car on public detail page and returns 404', function () {
    $car = Car::create([
        'slug' => 'hidden-concept-car',
        'brand' => 'XYZ',
        'model_name' => 'Concept One',
        'year' => 2025,
        'category' => CarCategory::Sports,
        'price' => 2000000000,
        'color_name' => 'Matte Black',
        'color_hex' => '#000000',
        'is_available' => false,
    ]);

    $response = $this->get('/cars/'.$car->slug);

    $response->assertNotFound();
});

test('cannot view non-existent car and returns 404', function () {
    $response = $this->get('/cars/mobil-tidak-ada-9999');

    $response->assertNotFound();
});

test('can view public spare part detail page with specifications and stock status', function () {
    $part = SparePart::create([
        'slug' => 'brembo-gt-brake-kit-f40',
        'name' => 'Brembo GT 6-Piston Brake Kit',
        'part_number' => 'BRM-GT6-F40',
        'brand' => 'Brembo',
        'category' => SparePartCategory::Brake,
        'price' => 38500000,
        'stock_quantity' => 4,
        'min_stock_alert' => 2,
        'car_brand_compatibility' => ['Porsche', 'BMW', 'Mercedes-Benz'],
        'description' => 'Kaliper monoblok 6 piston dengan cakram floating berlubang ventilasi.',
        'is_available' => true,
    ]);

    $response = $this->get(route('spare-parts.public.show', $part->slug));

    $response->assertOk();
    $response->assertSee('Brembo GT 6-Piston Brake Kit');
    $response->assertSee('BRM-GT6-F40');
    $response->assertSee('Brembo');
    $response->assertSee('Porsche, BMW, Mercedes-Benz');
    $response->assertSee('Pesan & Pasang');
});

test('cannot view unavailable spare part and returns 404', function () {
    $part = SparePart::create([
        'slug' => 'discontinued-part',
        'name' => 'Discontinued Part',
        'part_number' => 'DISC-001',
        'brand' => 'Generic',
        'category' => SparePartCategory::Engine,
        'price' => 100000,
        'stock_quantity' => 0,
        'is_available' => false,
    ]);

    $response = $this->get('/spare-parts/'.$part->slug);

    $response->assertNotFound();
});

test('can view public contact page', function () {
    $response = $this->get(route('contact.index'));

    $response->assertOk();
    $response->assertSee('Hubungi Tim Konsultan');
    $response->assertSee('name="name"', false);
    $response->assertSee('name="email"', false);
    $response->assertSee('name="phone"', false);
    $response->assertSee('name="subject"', false);
    $response->assertSee('name="message"', false);
});

test('guest can submit contact inquiry via standard html post', function () {
    Notification::fake();

    $response = $this->from(route('contact.index'))->post(route('contact.store'), [
        'name' => 'Budi Santoso',
        'email' => 'budi.santoso@example.com',
        'phone' => '081234567890',
        'subject' => 'Konsultasi Unit Tesla Model 3',
        'message' => 'Halo tim XYZ Motors, saya tertarik dengan unit Tesla Model 3 dan ingin tahu opsi pembiayaannya.',
    ]);

    $response->assertRedirect(route('contact.index'));
    $response->assertSessionHas('contact_success');

    $this->assertDatabaseHas('contact_inquiries', [
        'name' => 'Budi Santoso',
        'email' => 'budi.santoso@example.com',
        'phone' => '081234567890',
        'subject' => 'Konsultasi Unit Tesla Model 3',
        'status' => 'unread',
    ]);
});

test('guest can submit contact inquiry via ajax json', function () {
    Notification::fake();

    $response = $this->postJson(route('contact.store'), [
        'name' => 'Siti Nurhaliza',
        'email' => 'siti@example.com',
        'phone' => '+6281987654321',
        'subject' => 'Jadwal Servis Berkala SUV',
        'message' => 'Apakah bengkel melayani servis berkala pada hari Sabtu sore?',
    ]);

    $response->assertStatus(201);
    $response->assertJsonPath('status', 'success');
    $response->assertJsonPath('data.name', 'Siti Nurhaliza');

    $this->assertDatabaseHas('contact_inquiries', [
        'name' => 'Siti Nurhaliza',
        'email' => 'siti@example.com',
        'status' => 'unread',
    ]);
});

test('contact inquiry validates required fields and formats', function () {
    $response = $this->post(route('contact.store'), [
        'name' => '',
        'email' => 'not-an-email',
        'phone' => 'abc',
        'subject' => '',
        'message' => 'pendek',
    ]);

    $response->assertSessionHasErrors(['name', 'email', 'phone', 'subject', 'message']);
    expect(ContactInquiry::count())->toBe(0);
});

test('contact inquiry detects honeypot and rejects spam', function () {
    $response = $this->post(route('contact.store'), [
        'name' => 'Spammer Bot',
        'email' => 'spammer@botnet.xyz',
        'phone' => '08111222333',
        'subject' => 'Free Bitcoin',
        'message' => 'Visit my dangerous website now for free money and crypto.',
        'website_url' => 'https://spam-bot.xyz',
    ]);

    $response->assertSessionHasErrors(['website_url']);
    expect(ContactInquiry::count())->toBe(0);
});

test('contact inquiry triggers notifications to staff', function () {
    Notification::fake();

    $admin = User::factory()->create([
        'role' => UserRole::SuperAdmin,
        'is_active' => true,
    ]);

    $sales = User::factory()->create([
        'role' => UserRole::Sales,
        'is_active' => true,
    ]);

    $mechanic = User::factory()->create([
        'role' => UserRole::Mekanik,
        'is_active' => true,
    ]);

    $this->post(route('contact.store'), [
        'name' => 'Rahmat Hidayat',
        'email' => 'rahmat@example.com',
        'phone' => '081345678901',
        'subject' => 'Ketersediaan Suku Cadang Toyota',
        'message' => 'Apakah filter oli dan kampas rem OEM Toyota Alphard ready stock hari ini?',
    ]);

    Notification::assertSentTo([$admin, $sales], ContactInquiryNotification::class);
    Notification::assertNotSentTo([$mechanic], ContactInquiryNotification::class);
});

test('contact inquiry route enforces rate limiting', function () {
    Notification::fake();

    $payload = [
        'name' => 'Test Throttling',
        'email' => 'throttle@example.com',
        'phone' => '081234567899',
        'subject' => 'Test Pertanyaan',
        'message' => 'Pesan uji coba rate limit yang memenuhi batas minimal karakter.',
    ];

    // Throttle is 5 requests per minute
    for ($i = 0; $i < 5; $i++) {
        $response = $this->post(route('contact.store'), $payload);
        $response->assertSessionHasNoErrors();
    }

    $response = $this->post(route('contact.store'), $payload);
    $response->assertStatus(429);
});
