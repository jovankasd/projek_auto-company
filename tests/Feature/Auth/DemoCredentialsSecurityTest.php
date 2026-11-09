<?php

use App\Enums\UserRole;
use App\Models\Car;
use App\Models\Promo;
use App\Models\Service;
use App\Models\Setting;
use App\Models\SparePart;
use App\Models\TestDrive;
use App\Models\User;
use App\Services\DatabaseBootstrapper;
use Database\Seeders\CarSeeder;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoSeeder;
use Database\Seeders\PromoSeeder;
use Database\Seeders\ServiceSeeder;
use Database\Seeders\SparePartSeeder;
use Database\Seeders\TestDriveSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Support\Facades\Hash;

test('login and hidden login pages do not expose demo credentials or autofill in any environment', function (string $environment) {
    $originalEnv = app()->environment();
    app()->instance('env', $environment);

    try {
        foreach (['/login', '/logind'] as $path) {
            $response = $this->get($path);

            $response->assertOk();
            $response->assertDontSee('customer@xyzmotors.id');
            $response->assertDontSee('sales@xyzmotors.id');
            $response->assertDontSee('mekanik@xyzmotors.id');
            $response->assertDontSee('mekanik1@xyzmotors.id');
            $response->assertDontSee('mekanik2@xyzmotors.id');
            $response->assertDontSee('superadmin@xyzmotors.id');
            $response->assertDontSee('owner@xyzmotors.id');
            $response->assertDontSee('fillLogin');
            $response->assertDontSee('Akun Bawaan');
            $response->assertDontSee('Password semua akun bawaan');
            $response->assertDontSee("'password'", false);
            $response->assertDontSee('>password<', false);
        }

        $failedHiddenLogin = $this->from('/logind')->followingRedirects()->post('/logind', [
            '_token' => session()->token(),
            'email' => "nobody-{$environment}@xyzmotors.id",
            'password' => 'wrong-password',
        ]);

        $failedHiddenLogin->assertOk();
        $failedHiddenLogin->assertSee('action="'.route('login.hidden').'"', false);
        $failedHiddenLogin->assertDontSee('superadmin@xyzmotors.id');
        $failedHiddenLogin->assertDontSee('fillLogin');
    } finally {
        app()->instance('env', $originalEnv);
    }
})->with(['testing', 'staging', 'production', 'local']);

test('database seeder never creates demo accounts or demo business data in any environment', function (string $environment) {
    $originalEnv = app()->environment();
    app()->instance('env', $environment);

    try {
        $this->artisan('db:seed', [
            '--class' => DatabaseSeeder::class,
            '--force' => true,
        ])->assertSuccessful();

        expect(Setting::count())->toBeGreaterThan(0);
        expect(User::withTrashed()->count())->toBe(0);
        expect(Car::count())->toBe(0);
        expect(SparePart::count())->toBe(0);
        expect(Promo::count())->toBe(0);
        expect(Service::count())->toBe(0);
        expect(TestDrive::count())->toBe(0);
    } finally {
        app()->instance('env', $originalEnv);
    }
})->with(['production', 'staging', 'testing', 'local']);

test('demo seeders are blocked outside local and testing environments', function (string $environment) {
    $originalEnv = app()->environment();
    app()->instance('env', $environment);

    try {
        foreach ([
            UserSeeder::class,
            DemoSeeder::class,
            CarSeeder::class,
            SparePartSeeder::class,
            PromoSeeder::class,
            ServiceSeeder::class,
            TestDriveSeeder::class,
        ] as $seeder) {
            $this->artisan('db:seed', [
                '--class' => $seeder,
                '--force' => true,
            ])->assertSuccessful();
        }

        expect(User::withTrashed()->count())->toBe(0);
        expect(Car::count())->toBe(0);
        expect(SparePart::count())->toBe(0);
        expect(Promo::count())->toBe(0);
        expect(Service::count())->toBe(0);
        expect(TestDrive::count())->toBe(0);
    } finally {
        app()->instance('env', $originalEnv);
    }
})->with(['production', 'staging']);

test('demo seeders honor cached production or staging config even when runtime env is local', function (string $cachedConfigEnv) {
    $originalEnv = app()->environment();
    $originalConfigEnv = config('app.env');

    app()->instance('env', 'local');
    config(['app.env' => $cachedConfigEnv]);

    try {
        foreach ([UserSeeder::class, DemoSeeder::class] as $seeder) {
            $this->artisan('db:seed', [
                '--class' => $seeder,
                '--force' => true,
            ])->assertSuccessful();
        }

        expect(User::withTrashed()->count())->toBe(0);
        expect(Car::count())->toBe(0);
    } finally {
        config(['app.env' => $originalConfigEnv]);
        app()->instance('env', $originalEnv);
    }
})->with(['production', 'staging']);

test('user seeder creates verified demo accounts and preserves credentials, active status, roles, and soft deletes on rerun', function () {
    $this->seed(UserSeeder::class);

    $expectedRoles = [
        'superadmin@xyzmotors.id' => UserRole::SuperAdmin,
        'sales@xyzmotors.id' => UserRole::Sales,
        'mekanik@xyzmotors.id' => UserRole::Mekanik,
        'mekanik1@xyzmotors.id' => UserRole::Mekanik,
        'mekanik2@xyzmotors.id' => UserRole::Mekanik,
        'customer@xyzmotors.id' => UserRole::Customer,
        'owner@xyzmotors.id' => UserRole::Owner,
    ];

    expect(User::withTrashed()->count())->toBe(count($expectedRoles));

    foreach ($expectedRoles as $email => $role) {
        $seeded = User::where('email', $email)->firstOrFail();
        expect($seeded->role)->toBe($role);
        expect($seeded->is_active)->toBeTrue();
        expect($seeded->email_verified_at)->not->toBeNull();
    }

    $loginResponse = $this->post('/logind', [
        'email' => 'superadmin@xyzmotors.id',
        'password' => 'password',
    ]);
    $loginResponse->assertRedirect(route('dashboard', absolute: false));
    $this->get(route('dashboard'))->assertOk();
    $this->post('/logout');

    $superAdmin = User::where('email', 'superadmin@xyzmotors.id')->firstOrFail();
    $sales = User::where('email', 'sales@xyzmotors.id')->firstOrFail();
    $customer = User::where('email', 'customer@xyzmotors.id')->firstOrFail();

    $superAdmin->update(['password' => 'SuperSecretRotated#2026']);
    $sales->update(['password' => 'SalesSecret#2026', 'is_active' => false, 'role' => UserRole::Mekanik]);
    $customer->delete();

    $this->seed(UserSeeder::class);

    expect(User::withTrashed()->count())->toBe(count($expectedRoles));

    $superAdmin->refresh();
    expect(Hash::check('SuperSecretRotated#2026', $superAdmin->password))->toBeTrue();
    expect(Hash::check('password', $superAdmin->password))->toBeFalse();

    $sales->refresh();
    expect($sales->is_active)->toBeFalse();
    expect($sales->role)->toBe(UserRole::Mekanik);
    expect(Hash::check('SalesSecret#2026', $sales->password))->toBeTrue();

    $deletedCustomer = User::withTrashed()->find($customer->id);
    expect($deletedCustomer?->trashed())->toBeTrue();
});

test('demo seeder is idempotent in local environment and handles soft-deleted accounts safely', function () {
    $originalEnv = app()->environment();
    app()->instance('env', 'local');

    try {
        $existingOwner = User::factory()->create([
            'email' => 'owner@xyzmotors.id',
            'role' => UserRole::Owner,
            'password' => 'OwnerVault#2026',
            'is_active' => false,
        ]);

        $deletedMechanic = User::factory()->create([
            'email' => 'mekanik2@xyzmotors.id',
            'role' => UserRole::Mekanik,
            'is_active' => true,
        ]);
        $deletedMechanic->delete();

        $this->seed(DemoSeeder::class);

        $firstCounts = [
            'cars' => Car::count(),
            'parts' => SparePart::count(),
            'promos' => Promo::count(),
            'services' => Service::count(),
            'test_drives' => TestDrive::count(),
            'settings' => Setting::count(),
        ];

        $this->seed(DemoSeeder::class);

        $existingOwner->refresh();
        expect(Hash::check('OwnerVault#2026', $existingOwner->password))->toBeTrue();
        expect($existingOwner->is_active)->toBeFalse();
        expect(User::withTrashed()->find($deletedMechanic->id)?->trashed())->toBeTrue();
        expect(User::where('email', 'superadmin@xyzmotors.id')->exists())->toBeTrue();

        foreach ($firstCounts as $count) {
            expect($count)->toBeGreaterThan(0);
        }

        expect(Car::count())->toBe($firstCounts['cars']);
        expect(SparePart::count())->toBe($firstCounts['parts']);
        expect(Promo::count())->toBe($firstCounts['promos']);
        expect(Service::count())->toBe($firstCounts['services']);
        expect(TestDrive::count())->toBe($firstCounts['test_drives']);
    } finally {
        app()->instance('env', $originalEnv);
    }
});

test('database bootstrapper blocks non-local environments and seeds missing accounts in local without overwriting existing ones', function () {
    $originalEnv = app()->environment();
    $originalConfigEnv = config('app.env');

    try {
        foreach (['production', 'staging'] as $blockedEnv) {
            app()->instance('env', $blockedEnv);
            DatabaseBootstrapper::ensureDemoAccounts();
            expect(User::withTrashed()->count())->toBe(0);
        }

        app()->instance('env', 'local');
        config(['app.env' => 'production']);
        DatabaseBootstrapper::ensureDemoAccounts();
        expect(User::withTrashed()->count())->toBe(0);

        config(['app.env' => 'testing']);
        $admin = User::factory()->create([
            'email' => 'superadmin@xyzmotors.id',
            'role' => UserRole::SuperAdmin,
            'password' => 'CustomAdmin#2026',
            'is_active' => false,
        ]);

        DatabaseBootstrapper::ensureDemoAccounts();

        $admin->refresh();
        expect(Hash::check('CustomAdmin#2026', $admin->password))->toBeTrue();
        expect($admin->is_active)->toBeFalse();
        expect(User::count())->toBe(7);
    } finally {
        config(['app.env' => $originalConfigEnv]);
        app()->instance('env', $originalEnv);
    }
});

test('mekanik dashboard does not auto-seed spare parts over HTTP when empty', function () {
    $mekanik = User::factory()->create([
        'role' => UserRole::Mekanik,
        'email_verified_at' => now(),
        'is_active' => true,
    ]);

    $response = $this->actingAs($mekanik)->get(route('dashboard'));

    $response->assertOk();
    $response->assertSee('Belum ada data suku cadang yang dimuat.');
    expect(SparePart::count())->toBe(0);
});
