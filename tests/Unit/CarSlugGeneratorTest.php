<?php

use App\Enums\CarCategory;
use App\Models\Car;
use App\Services\CarSlugGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('generates clean slug from brand and model name and year', function () {
    $generator = new CarSlugGenerator;
    $slug = $generator->generate('Ford', 'Mustang GT', 2024);

    expect($slug)->toBe('ford-mustang-gt-2024');
});

test('generates unique slug with incremental suffix on collision', function () {
    Car::create([
        'slug' => 'bmw-m4-competition-2024',
        'brand' => 'BMW',
        'model_name' => 'M4 Competition',
        'year' => 2024,
        'category' => CarCategory::Sports,
        'price' => 1500000000,
        'color_name' => 'Isle of Man Green',
        'color_hex' => '#004F30',
        'engine_specs' => ['power' => '503 HP'],
        'hero_image_transparent' => 'images/cars/m4.webp',
        'is_available' => true,
    ]);

    $generator = new CarSlugGenerator;
    $slug2 = $generator->generate('BMW', 'M4 Competition', 2024);

    expect($slug2)->toBe('bmw-m4-competition-2024-1');
});
