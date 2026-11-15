<?php

use App\Enums\CarCategory;
use App\Enums\UserRole;
use App\Models\Car;
use App\Models\User;

function createTestCar(array $attributes = []): Car
{
    return Car::create(array_merge([
        'slug' => 'test-car-'.uniqid(),
        'brand' => 'TestBrand',
        'model_name' => 'TestModel',
        'year' => 2024,
        'category' => CarCategory::EV,
        'color_name' => 'Pearl White',
        'color_hex' => '#FFFFFF',
        'price' => 1000000000,
        'engine_specs' => ['power' => '500 HP', 'torque' => '700 Nm'],
        'is_available' => true,
        'sort_order' => 1,
    ], $attributes));
}

it('generates a valid xml sitemap containing public urls and excluding private or auth urls', function () {
    $activeCar = createTestCar([
        'slug' => 'tesla-model-s-plaid',
        'brand' => 'Tesla',
        'model_name' => 'Model S Plaid',
        'is_available' => true,
    ]);

    $inactiveCar = createTestCar([
        'slug' => 'secret-prototype-x',
        'brand' => 'Secret',
        'model_name' => 'Prototype',
        'is_available' => false,
    ]);

    $response = $this->get('/sitemap.xml');

    $response->assertStatus(200);
    expect($response->headers->get('content-type'))->toContain('xml');

    $content = $response->getContent();
    expect($content)->toBeString();

    $xml = simplexml_load_string($content);
    expect($xml)->not->toBeFalse();

    $urls = [];
    foreach ($xml->url as $urlElement) {
        $urls[] = (string) $urlElement->loc;
    }

    // Must include public URLs
    expect($urls)->toContain(url('/'));
    expect($urls)->toContain(url('/privacy-policy'));
    expect($urls)->toContain(url('/terms-of-service'));
    expect($urls)->toContain(url('/contact'));
    expect($urls)->toContain(url('/cars/'.$activeCar->slug));

    // Must NOT include inactive car
    expect($urls)->not->toContain(url('/cars/'.$inactiveCar->slug));

    // Must NOT include private or authenticated URLs
    foreach ($urls as $url) {
        expect($url)->not->toContain('/dashboard');
        expect($url)->not->toContain('/login');
        expect($url)->not->toContain('/register');
        expect($url)->not->toContain('/password');
        expect($url)->not->toContain('/profile');
        expect($url)->not->toContain('/storage/licenses');
        expect($url)->not->toContain('/storage/private');
    }
});

it('provides a robots.txt that allows public paths and disallows private and auth paths with sitemap reference', function () {
    $robotsPath = public_path('robots.txt');
    expect(file_exists($robotsPath))->toBeTrue();

    $content = file_get_contents($robotsPath);
    expect($content)->toContain('User-agent: *');
    expect($content)->toContain('Disallow: /dashboard');
    expect($content)->toContain('Disallow: /login');
    expect($content)->toContain('Disallow: /register');
    expect($content)->toContain('Disallow: /storage/licenses/');
    expect($content)->toContain('Sitemap:');
    expect($content)->toContain('/sitemap.xml');

    // Also accessible via route for automated HTTP test tools
    $response = $this->get('/robots.txt');
    $response->assertStatus(200);
    $response->assertSee('Disallow: /dashboard');
    $response->assertSee('/sitemap.xml');
});

it('includes noindex nofollow meta tags on auth and dashboard layouts but not on public pages', function () {
    // Auth guest pages
    $this->get('/login')
        ->assertStatus(200)
        ->assertSee('<meta name="robots" content="noindex, nofollow">', false);

    $this->get('/register')
        ->assertStatus(200)
        ->assertSee('<meta name="robots" content="noindex, nofollow">', false);

    // Dashboard page (when authenticated)
    $customer = User::create([
        'name' => 'John Customer',
        'email' => 'customer-seo@example.com',
        'password' => bcrypt('password'),
        'role' => UserRole::Customer,
        'phone_number' => '081299998888',
        'is_active' => true,
    ]);

    $this->actingAs($customer)
        ->get('/dashboard')
        ->assertStatus(200)
        ->assertSee('<meta name="robots" content="noindex, nofollow">', false);

    // Public pages must NOT have noindex
    $this->get('/')
        ->assertStatus(200)
        ->assertDontSee('<meta name="robots" content="noindex, nofollow">', false);
});

it('serves privacy policy and terms of service pages with honest data disclosure and owner approval placeholders', function () {
    $privacyResponse = $this->get('/privacy-policy');
    $privacyResponse->assertStatus(200);
    $privacyResponse->assertSee('Kebijakan Privasi');
    $privacyResponse->assertSee('booking servis', false);
    $privacyResponse->assertSee('test drive', false);
    $privacyResponse->assertSee('SIM', false);
    $privacyResponse->assertSee('[PERLU PENGESAHAN PEMILIK:', false);

    $termsResponse = $this->get('/terms-of-service');
    $termsResponse->assertStatus(200);
    $termsResponse->assertSee('Syarat dan Ketentuan');
    $termsResponse->assertSee('test drive', false);
    $termsResponse->assertSee('servis', false);
    $termsResponse->assertSee('[PERLU PENGESAHAN PEMILIK:', false);

    // Aliases in Indonesian should also work
    $this->get('/kebijakan-privasi')->assertStatus(200);
    $this->get('/syarat-dan-ketentuan')->assertStatus(200);

    // Footer showroom must contain links to trust pages
    $homeResponse = $this->get('/');
    $homeResponse->assertStatus(200);
    $homeResponse->assertSee(route('privacy-policy'));
    $homeResponse->assertSee(route('terms-of-service'));
});

it('has consistent title, description, and canonical link tags across public pages', function () {
    $car = createTestCar([
        'slug' => 'porsche-911-gt3-rs',
        'brand' => 'Porsche',
        'model_name' => '911 GT3 RS',
        'meta_title' => 'Porsche 911 GT3 RS - Dealer Pro',
        'meta_description' => 'Eksplorasi Porsche 911 GT3 RS dengan mesin naturally aspirated 4.0L di Dealer Pro.',
        'is_available' => true,
    ]);

    // Homepage
    $this->get('/')
        ->assertStatus(200)
        ->assertSee('<link rel="canonical" href="'.url('/').'">', false);

    // Car detail page
    $this->get('/cars/'.$car->slug)
        ->assertStatus(200)
        ->assertSee('Porsche 911 GT3 RS')
        ->assertSee('<link rel="canonical" href="'.url('/cars/'.$car->slug).'">', false)
        ->assertSee('<meta name="description"', false);

    // Privacy Policy
    $this->get('/privacy-policy')
        ->assertStatus(200)
        ->assertSee('<link rel="canonical" href="'.url('/privacy-policy').'">', false);

    // Terms of Service
    $this->get('/terms-of-service')
        ->assertStatus(200)
        ->assertSee('<link rel="canonical" href="'.url('/terms-of-service').'">', false);
});

it('displays honest showroom empty state copy without false claims of automatic database healing', function () {
    // Clear cars table
    Car::query()->forceDelete();

    $response = $this->get('/');
    $response->assertStatus(200);

    // Must NOT contain the old false claim
    $response->assertDontSee('Sistem telah menginisialisasi ulang data default, silakan klik tombol di bawah untuk memuat kembali.');

    // Must contain honest copy
    $response->assertSee('Katalog');
    $response->assertSee('belum tersedia');
});
