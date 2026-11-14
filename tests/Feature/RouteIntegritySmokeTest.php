<?php

use App\Enums\CarCategory;
use App\Enums\DiscountType;
use App\Enums\SparePartCategory;
use App\Enums\UserRole;
use App\Models\Car;
use App\Models\Promo;
use App\Models\SparePart;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

beforeEach(function () {
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

    $this->owner = User::factory()->create([
        'role' => UserRole::Owner,
        'email_verified_at' => now(),
        'is_active' => true,
    ]);
});

test('promos show route does not produce 500 and returns 404 or 405 when detail page is excluded', function () {
    $promo = Promo::create([
        'code' => 'DISC10',
        'title' => 'Diskon 10%',
        'discount_type' => DiscountType::Percentage,
        'discount_value' => 10,
        'starts_at' => now(),
        'ends_at' => now()->addDays(7),
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->superAdmin)->get('/dashboard/promos/'.$promo->id);

    // Harus 404/405 dan BUKAN 500 (BadMethodCallException)
    expect($response->status())->toBeIn([404, 405]);
});

test('users show route does not produce 500 and returns 404 or 405 when detail page is excluded', function () {
    $targetUser = User::factory()->create([
        'role' => UserRole::Customer,
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->superAdmin)->get('/dashboard/users/'.$targetUser->id);

    // Harus 404/405 dan BUKAN 500 (BadMethodCallException)
    expect($response->status())->toBeIn([404, 405]);
});

test('all registered routes point to existing controller classes and callable methods', function () {
    $missingRoutes = [];

    foreach (Route::getRoutes() as $route) {
        $action = $route->getActionName();

        if ($action === 'Closure') {
            continue;
        }

        if (str_contains($action, '@')) {
            [$controller, $method] = explode('@', $action);

            if (! class_exists($controller)) {
                $missingRoutes[] = "Class {$controller} missing for URI: {$route->uri()}";
            } elseif (! method_exists($controller, $method)) {
                $missingRoutes[] = "Method {$controller}@{$method} missing for URI: {$route->uri()}";
            }
        } elseif (class_exists($action)) {
            if (! method_exists($action, '__invoke')) {
                $missingRoutes[] = "Invokable controller {$action} missing __invoke for URI: {$route->uri()}";
            }
        } else {
            $missingRoutes[] = "Handler {$action} missing for URI: {$route->uri()}";
        }
    }

    expect($missingRoutes)->toBeEmpty(
        'Found registered routes pointing to non-existent controllers or methods: '.implode(', ', $missingRoutes)
    );
});

test('super admin can access all dashboard main GET routes without 500', function () {
    $car = Car::create([
        'slug' => 'toyota-innova-zenix-2024',
        'brand' => 'Toyota',
        'model_name' => 'Innova Zenix',
        'year' => 2024,
        'category' => CarCategory::MPV,
        'color_name' => 'Platinum White Pearl',
        'color_hex' => '#FFFFFF',
        'price' => 425000000,
        'stock_quantity' => 5,
        'is_available' => true,
        'engine_specs' => ['power' => '174 HP', 'torque' => '205 Nm', 'trans' => 'CVT'],
    ]);

    $sparePart = SparePart::create([
        'slug' => 'filter-oli-innova-zenix',
        'name' => 'Filter Oli Innova Zenix',
        'part_number' => 'FO-TY-ZNX-001',
        'category' => SparePartCategory::Engine,
        'brand' => 'Toyota Genuine Parts',
        'price' => 85000,
        'stock_quantity' => 50,
        'is_available' => true,
    ]);

    $promo = Promo::create([
        'code' => 'SUPERPROMO',
        'title' => 'Super Promo',
        'discount_type' => DiscountType::Fixed,
        'discount_value' => 50000,
        'starts_at' => now(),
        'ends_at' => now()->addDays(7),
        'is_active' => true,
    ]);

    $routes = [
        route('dashboard'),
        route('cars.index'),
        route('cars.create'),
        route('cars.show', $car),
        route('cars.edit', $car),
        route('promos.index'),
        route('promos.create'),
        route('promos.edit', $promo),
        route('spare-parts.index'),
        route('spare-parts.create'),
        route('spare-parts.show', $sparePart),
        route('spare-parts.edit', $sparePart),
        route('users.index'),
        route('users.create'),
        route('users.edit', $this->customer),
        route('audit.index'),
        route('settings.edit'),
        route('reports.index'),
        route('notifications.index'),
        route('profile.edit'),
    ];

    foreach ($routes as $url) {
        $response = $this->actingAs($this->superAdmin)->get($url);
        $response->assertOk();
    }
});

test('role based dashboard access smoke test verifies boundaries and returns no 500', function () {
    // Sales
    $this->actingAs($this->sales)->get(route('dashboard'))->assertOk();
    $this->actingAs($this->sales)->get(route('cars.index'))->assertOk();
    $this->actingAs($this->sales)->get(route('promos.index'))->assertOk();
    $this->actingAs($this->sales)->get(route('spare-parts.index'))->assertOk();
    $this->actingAs($this->sales)->get(route('test-drives.my'))->assertOk();
    $this->actingAs($this->sales)->get(route('users.index'))->assertForbidden();
    $this->actingAs($this->sales)->get(route('reports.index'))->assertForbidden();

    // Mekanik
    $this->actingAs($this->mekanik)->get(route('dashboard'))->assertOk();
    $this->actingAs($this->mekanik)->get(route('spare-parts.index'))->assertOk();
    $this->actingAs($this->mekanik)->get(route('cars.index'))->assertForbidden();
    $this->actingAs($this->mekanik)->get(route('promos.index'))->assertForbidden();
    $this->actingAs($this->mekanik)->get(route('users.index'))->assertForbidden();

    // Customer
    $this->actingAs($this->customer)->get(route('dashboard'))->assertOk();
    $this->actingAs($this->customer)->get(route('services.my'))->assertOk();
    $this->actingAs($this->customer)->get(route('test-drives.my'))->assertOk();
    $this->actingAs($this->customer)->get(route('cars.index'))->assertForbidden();
    $this->actingAs($this->customer)->get(route('users.index'))->assertForbidden();

    // Owner
    $this->actingAs($this->owner)->get(route('dashboard'))->assertOk();
    $this->actingAs($this->owner)->get(route('reports.index'))->assertOk();
    $this->actingAs($this->owner)->get(route('cars.index'))->assertForbidden();
    $this->actingAs($this->owner)->get(route('users.index'))->assertForbidden();
    $this->actingAs($this->owner)->get(route('settings.edit'))->assertForbidden();
});

test('sales role visiting promo detail route returns 404 or 405 without 500', function () {
    $promo = Promo::create([
        'code' => 'SALESPROMO',
        'title' => 'Promo Khusus Sales',
        'discount_type' => DiscountType::Percentage,
        'discount_value' => 15,
        'starts_at' => now(),
        'ends_at' => now()->addDays(5),
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->sales)->get('/dashboard/promos/'.$promo->id);

    expect($response->status())->toBeIn([404, 405]);
});

test('car detail view correctly displays formatted price and active state without 500', function () {
    $car = Car::create([
        'slug' => 'honda-civic-type-r-2024',
        'brand' => 'Honda',
        'model_name' => 'Civic Type R',
        'year' => 2024,
        'category' => CarCategory::Sports,
        'color_name' => 'Championship White',
        'color_hex' => '#FFFFFF',
        'price' => 1400000000,
        'stock_quantity' => 2,
        'is_available' => true,
        'engine_specs' => [
            'power' => '315 HP',
            'torque' => '420 Nm',
            'accel' => '5.4s',
            'trans' => '6MT',
            'fuel_type' => 'Bensin Pertamax Turbo',
        ],
        'dimensions' => [
            'seats' => '4 Kursi',
        ],
    ]);

    $response = $this->actingAs($this->sales)->get(route('cars.show', $car));

    $response->assertOk();
    $response->assertSee('Honda Civic Type R (2024)');
    $response->assertSee('Rp 1.400.000.000');
    $response->assertSee('Katalog Aktif');
    $response->assertSee('315 HP');
    $response->assertSee('420 Nm');
    $response->assertSee('6MT');
});
