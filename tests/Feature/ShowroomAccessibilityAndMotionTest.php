<?php

use App\Enums\CarCategory;
use App\Enums\SparePartCategory;
use App\Models\Car;
use App\Models\SparePart;

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

    SparePart::create([
        'slug' => 'brembo-brake-pad',
        'name' => 'Kampas Rem Brembo Racing',
        'part_number' => 'BRM-BP-001',
        'brand' => 'Brembo',
        'category' => SparePartCategory::Brake,
        'price' => 1250000,
        'stock_quantity' => 25,
        'car_brand_compatibility' => ['Ford', 'BMW'],
        'image_path' => 'images/spare-parts/brembo-brake-pad.jpg',
        'is_available' => true,
    ]);
});

test('showroom category filter uses accessible group and aria-pressed semantics instead of invalid tablist', function () {
    $response = $this->get(route('home'));

    $response->assertOk();
    $html = $response->getContent();

    // Must not contain role="tablist" without role="tab"
    expect($html)->not->toContain('role="tablist"');
    // Must contain role="group" for category filters
    expect($html)->toContain('role="group"');
    expect($html)->toContain('aria-label="Filter Kategori Mobil"');
    expect($html)->toContain('aria-pressed');
});

test('showroom carousel includes accessible pause and resume control button', function () {
    $response = $this->get(route('home'));

    $response->assertOk();
    $html = $response->getContent();

    expect($html)->toContain('data-slide-pause-play');
    expect($html)->toContain('aria-label="Jeda putar otomatis"');
    expect($html)->toContain('aria-pressed');
});

test('showroom slide counter prevents line wrapping on 375px mobile screens', function () {
    $response = $this->get(route('home'));

    $response->assertOk();
    $html = $response->getContent();

    // The counter or its direct parent container must enforce whitespace-nowrap
    expect($html)->toMatch('/class="[^"]*whitespace-nowrap[^"]*"[^>]*>\s*<span id="slide-counter-current">/');
});

test('all lazy loaded car and spare part images have explicit width and height dimensions', function () {
    $response = $this->get(route('home'));

    $response->assertOk();
    $html = $response->getContent();

    // Car ledger image must have explicit width and height
    expect($html)->toMatch('/<img[^>]+src="[^"]*cars\/ford-mustang-gt-2024\.webp"[^>]+width="1060"[^>]+height="480"/');

    // Spare part image must have explicit width and height
    expect($html)->toMatch('/<img[^>]+src="[^"]*brembo-brake-pad\.jpg"[^>]+width="400"[^>]+height="400"/');
});

test('showroom text does not use low-contrast text-slate-400 on light backgrounds', function () {
    $response = $this->get(route('home'));

    $response->assertOk();
    $html = $response->getContent();

    // Division labels must use text-slate-600 or higher
    expect($html)->not->toContain('text-slate-400 uppercase tracking-widest block">DIVISI 01');
    expect($html)->toContain('text-slate-600 uppercase tracking-widest block">DIVISI 01');

    // Telemetry labels must not use text-slate-400
    expect($html)->not->toMatch('/<dt class="[^"]*text-slate-400[^"]*">Tenaga<\/dt>/');

    // Price subtitle must not use text-slate-400
    expect($html)->not->toContain('text-slate-400 font-normal">OTR Jakarta');
});
