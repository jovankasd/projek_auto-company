<?php

use App\Enums\CarCategory;
use App\Enums\UserRole;
use App\Http\Middleware\EnsureUserIsActive;
use App\Models\Car;
use App\Models\Service;
use App\Models\TestDrive;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

function resetAuthGuards(): void
{
    Auth::forgetGuards();
    app()->forgetInstance('auth.driver');
}

function resetAuthSession(): void
{
    resetAuthGuards();
    Session::flush();
}

function createAvailableCar(string $slug = 'civic-type-r'): Car
{
    return Car::create([
        'slug' => $slug,
        'brand' => 'Honda',
        'model_name' => 'Civic Type R',
        'year' => 2025,
        'category' => CarCategory::Sports,
        'color_name' => 'Championship White',
        'color_hex' => '#FFFFFF',
        'price' => 1400000000,
        'engine_specs' => ['power' => '315 HP'],
        'hero_image_transparent' => "images/cars/{$slug}.webp",
        'is_available' => true,
        'sort_order' => 1,
    ]);
}

test('inactive user cannot log in via login or hidden login and receives generic error', function (string $loginPath) {
    $user = User::factory()->create([
        'role' => UserRole::Customer,
        'is_active' => false,
        'remember_token' => null,
    ]);

    $response = $this->from($loginPath)->post($loginPath, [
        'email' => $user->email,
        'password' => 'password',
        'remember' => 'on',
    ]);

    $this->assertGuest();
    $response->assertRedirect($loginPath);
    $response->assertSessionHasErrors('email');
    $response->assertCookieMissing(Auth::guard('web')->getRecallerName());

    $errorMessage = (string) collect(session('errors')->get('email'))->first();
    expect($errorMessage)->toBe(trans('auth.failed'));
    expect($user->fresh()->remember_token)->toBeNull();
})->with(['/login', '/logind']);

test('super admin deactivation via update form omits or unchecks is_active and revokes db session and remember token', function () {
    $originalDriver = config('session.driver');
    config(['session.driver' => 'database']);
    Session::forgetDrivers();
    app()->forgetInstance('session.store');
    resetAuthSession();

    try {
        $admin = User::factory()->create([
            'role' => UserRole::SuperAdmin,
            'is_active' => true,
        ]);

        $customer = User::factory()->create([
            'role' => UserRole::Customer,
            'phone_number' => '081234567890',
            'is_active' => true,
            'remember_token' => null,
        ]);

        // Edit view renders hidden fallback value=0 so unchecked checkboxes disable the account.
        $editResponse = $this->actingAs($admin)->get(route('users.edit', $customer));
        $editResponse->assertOk();
        $editResponse->assertSee('<input type="hidden" name="is_active" value="0">', false);

        resetAuthSession();

        $loginResponse = $this->post('/login', [
            'email' => $customer->email,
            'password' => 'password',
            'remember' => 'on',
        ]);
        $loginResponse->assertRedirect(route('dashboard', absolute: false));

        $sessionCookie = $loginResponse->getCookie(config('session.cookie'))?->getValue();
        $recallerName = Auth::guard('web')->getRecallerName();
        $rememberCookie = $loginResponse->getCookie($recallerName)?->getValue();

        expect($sessionCookie)->not->toBeNull();
        expect($rememberCookie)->not->toBeNull();
        expect($customer->fresh()->remember_token)->not->toBeNull();
        expect(DB::table(config('session.table', 'sessions'))->where('user_id', $customer->id)->count())->toBe(1);

        // Verify captured customer cookie works before deactivation.
        resetAuthSession();
        $this->withCookie(config('session.cookie'), $sessionCookie)->get('/dashboard')->assertOk();

        // Simulate form submission without is_active key.
        resetAuthSession();
        $this->actingAs($admin)->put(route('users.update', $customer), [
            'name' => $customer->name,
            'email' => $customer->email,
            'phone_number' => '081234567890',
            'role' => UserRole::Customer->value,
        ])->assertRedirect(route('users.index'));

        $customer->refresh();
        expect($customer->is_active)->toBeFalse();
        expect($customer->remember_token)->toBeNull();
        expect(DB::table(config('session.table', 'sessions'))->where('user_id', $customer->id)->count())->toBe(0);

        resetAuthSession();
        $blockedResponse = $this->withCookies([
            config('session.cookie') => $sessionCookie,
            $recallerName => $rememberCookie,
        ])->get('/dashboard');

        $blockedResponse->assertRedirect(route('login'));
        $blockedResponse->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->assertGuest();
    } finally {
        config(['session.driver' => $originalDriver]);
        Session::forgetDrivers();
        app()->forgetInstance('session.store');
    }
});

test('non-database session driver still revokes remember token and terminates session on next request after controller deactivation or deletion', function () {
    expect(config('session.driver'))->not->toBe('database');

    $admin = User::factory()->create([
        'role' => UserRole::SuperAdmin,
        'is_active' => true,
    ]);

    $customer = User::factory()->create([
        'role' => UserRole::Customer,
        'phone_number' => '081234567890',
        'is_active' => true,
        'remember_token' => null,
    ]);

    $loginResponse = $this->post('/login', [
        'email' => $customer->email,
        'password' => 'password',
        'remember' => 'on',
    ]);
    $sessionCookie = $loginResponse->getCookie(config('session.cookie'))?->getValue();
    $recallerName = Auth::guard('web')->getRecallerName();
    $rememberCookie = $loginResponse->getCookie($recallerName)?->getValue();

    // Super admin deactivates customer in a separate session while array store still retains customer session.
    resetAuthGuards();
    Session::setId(Str::random(40));

    $this->actingAs($admin)->put(route('users.update', $customer), [
        'name' => $customer->name,
        'email' => $customer->email,
        'phone_number' => '081234567890',
        'role' => UserRole::Customer->value,
        'is_active' => '0',
    ])->assertRedirect(route('users.index'));

    expect($customer->fresh()->is_active)->toBeFalse();
    expect($customer->fresh()->remember_token)->toBeNull();

    resetAuthGuards();
    $this->withCookies([
        config('session.cookie') => $sessionCookie,
        $recallerName => $rememberCookie,
    ])->get('/profile')->assertRedirect(route('login'));
    $this->assertGuest();

    // Reactivate then verify destroy() revokes access under non-database session driver.
    $customer->update(['is_active' => true]);
    resetAuthSession();

    $secondLogin = $this->post('/login', [
        'email' => $customer->email,
        'password' => 'password',
        'remember' => 'on',
    ]);
    $secondSessionCookie = $secondLogin->getCookie(config('session.cookie'))?->getValue();

    resetAuthGuards();
    Session::setId(Str::random(40));
    $this->actingAs($admin)->delete(route('users.destroy', $customer))
        ->assertRedirect(route('users.index'));

    resetAuthGuards();
    $this->withCookie(config('session.cookie'), $secondSessionCookie)
        ->get('/dashboard')
        ->assertRedirect(route('login'));
    $this->assertGuest();
});

test('logged-in user loses access on next request across dashboard, role, and non-dashboard auth routes when deactivated mid-session', function (string $method, string $uri, array $payload, bool $verified, UserRole $role) {
    $user = User::factory()->create([
        'role' => $role,
        'is_active' => true,
        'email_verified_at' => $verified ? now() : null,
    ]);

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('dashboard', absolute: false));

    $oldCsrfToken = session()->token();

    // Simulate out-of-band deactivation while PHP session/guard stays active.
    $user->update(['is_active' => false]);

    $response = match (strtoupper($method)) {
        'GET' => $this->get($uri),
        'PUT' => $this->put($uri, $payload),
        default => $this->post($uri, $payload),
    };

    $response->assertRedirect(route('login'));
    $response->assertSessionHasErrors('email');
    $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
    $this->assertGuest();
    expect(session()->token())->not->toBe($oldCsrfToken);
})->with([
    'dashboard home (auth+verified)' => ['GET', '/dashboard', [], true, UserRole::Customer],
    'my services (auth+verified without role)' => ['GET', '/dashboard/my-services', [], true, UserRole::Customer],
    'users admin (auth+verified+role)' => ['GET', '/dashboard/users', [], true, UserRole::SuperAdmin],
    'profile edit (auth outside dashboard)' => ['GET', '/profile', [], true, UserRole::Customer],
    'password update (PUT auth outside dashboard)' => ['PUT', '/password', [
        'current_password' => 'password',
        'password' => 'new-password-123',
        'password_confirmation' => 'new-password-123',
    ], true, UserRole::Customer],
    'verify email prompt (unverified auth outside dashboard)' => ['GET', '/verify-email', [], false, UserRole::Customer],
]);

test('revoked session renders inactive message clearly on login screen after redirect', function () {
    $user = User::factory()->create([
        'role' => UserRole::Customer,
        'is_active' => true,
    ]);

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('dashboard', absolute: false));

    $user->update(['is_active' => false]);

    $redirected = $this->followingRedirects()->get('/profile');
    $redirected->assertOk();
    $redirected->assertSee(EnsureUserIsActive::INACTIVE_MESSAGE);
    $this->assertGuest();
});

test('deactivated user holding a remember-me cookie cannot regain access to profile or dashboard', function () {
    $user = User::factory()->create([
        'role' => UserRole::Customer,
        'is_active' => true,
    ]);

    $loginResponse = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
        'remember' => 'on',
    ]);

    $recallerName = Auth::guard('web')->getRecallerName();
    $rememberCookie = $loginResponse->getCookie($recallerName)?->getValue();
    $originalRememberToken = $user->fresh()->remember_token;

    expect($rememberCookie)->not->toBeNull();
    expect($originalRememberToken)->not->toBeNull();

    // Simulate out-of-band deactivation even before remember_token is nulled.
    $user->update(['is_active' => false]);

    resetAuthSession();

    $profileResponse = $this->withCookie($recallerName, $rememberCookie)->get('/profile');
    $profileResponse->assertRedirect(route('login'));
    $profileResponse->assertSessionHasErrors('email');
    $profileResponse->assertCookieExpired($recallerName);
    $this->assertGuest();

    // Old remember token must have been rotated or cleared so replaying the cookie stays rejected.
    expect($user->fresh()->remember_token)->not->toBe($originalRememberToken);

    resetAuthSession();

    $secondAttempt = $this->withCookie($recallerName, $rememberCookie)->get('/dashboard');
    $secondAttempt->assertRedirect(route('login'));
    $this->assertGuest();
});

test('expectsJson requests by deactivated user receive 403 json on auth and booking endpoints', function () {
    $car = createAvailableCar();
    $user = User::factory()->create([
        'role' => UserRole::Customer,
        'is_active' => true,
    ]);

    foreach (['/dashboard/notifications/unread-count', '/profile'] as $uri) {
        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard', absolute: false));

        $user->update(['is_active' => false]);

        $response = $this->get($uri, ['Accept' => 'application/json']);
        $response->assertStatus(403);
        $response->assertJson(['message' => EnsureUserIsActive::INACTIVE_MESSAGE]);
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->assertGuest();

        $user->update(['is_active' => true]);
        resetAuthSession();
    }

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('dashboard', absolute: false));
    $user->update(['is_active' => false]);

    $tdJson = $this->post('/test-drive', [
        'car_id' => $car->id,
        'preferred_date' => Carbon::now()->addDays(2)->format('Y-m-d 10:00:00'),
        'driver_license_id' => 'SIM-12345678',
    ], ['Accept' => 'application/json']);

    $tdJson->assertStatus(403);
    $tdJson->assertJson(['message' => EnsureUserIsActive::INACTIVE_MESSAGE]);
    $tdJson->assertSessionMissing('pending_test_drive');
    $this->assertGuest();
    expect(TestDrive::count())->toBe(0);

    $user->update(['is_active' => true]);
    resetAuthSession();
    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('dashboard', absolute: false));
    $user->update(['is_active' => false]);

    $sbJson = $this->post('/service-booking', [
        'vehicle_plate_number' => 'B 1234 XYZ',
        'vehicle_model' => 'Honda Civic',
        'vehicle_year' => 2024,
        'odometer_km' => 10000,
        'service_type' => 'regular',
        'service_date' => Carbon::now()->addDays(3)->format('Y-m-d 10:00:00'),
        'slot_hour' => 10,
        'complaint_notes' => 'Servis berkala rutin 10.000 km.',
    ], ['Accept' => 'application/json']);

    $sbJson->assertStatus(403);
    $sbJson->assertJson(['message' => EnsureUserIsActive::INACTIVE_MESSAGE]);
    $sbJson->assertSessionMissing('pending_service_booking');
    $this->assertGuest();
    expect(Service::count())->toBe(0);
});

test('deactivated user with active session cannot submit web test drive or service booking forms', function () {
    $car = createAvailableCar('accord-rs');

    $customer = User::factory()->create([
        'role' => UserRole::Customer,
        'is_active' => true,
    ]);

    $this->post('/login', [
        'email' => $customer->email,
        'password' => 'password',
    ])->assertRedirect(route('dashboard', absolute: false));

    $customer->update(['is_active' => false]);

    $testDriveResponse = $this->post('/test-drive', [
        'car_id' => $car->id,
        'preferred_date' => Carbon::now()->addDays(2)->format('Y-m-d 10:00:00'),
        'driver_license_id' => 'SIM-12345678',
    ]);

    $testDriveResponse->assertRedirect(route('login'));
    $testDriveResponse->assertSessionHasErrors('email');
    $testDriveResponse->assertSessionMissing('pending_test_drive');
    $this->assertGuest();
    expect(TestDrive::count())->toBe(0);

    $customer->update(['is_active' => true]);
    resetAuthSession();

    $this->post('/login', [
        'email' => $customer->email,
        'password' => 'password',
    ])->assertRedirect(route('dashboard', absolute: false));

    $customer->update(['is_active' => false]);

    $serviceResponse = $this->post('/service-booking', [
        'vehicle_plate_number' => 'B 5678 ABC',
        'vehicle_model' => 'Honda Accord',
        'vehicle_year' => 2024,
        'odometer_km' => 15000,
        'service_type' => 'regular',
        'service_date' => Carbon::now()->addDays(3)->format('Y-m-d 10:00:00'),
        'slot_hour' => 10,
        'complaint_notes' => 'Servis berkala rutin 15.000 km.',
    ]);

    $serviceResponse->assertRedirect(route('login'));
    $serviceResponse->assertSessionHasErrors('email');
    $serviceResponse->assertSessionMissing('pending_service_booking');
    $this->assertGuest();
    expect(Service::count())->toBe(0);
});

test('soft-deleted user loses session access and actingAs unsaved inactive model does not persist to db', function () {
    $originalDriver = config('session.driver');
    config(['session.driver' => 'database']);
    Session::forgetDrivers();
    app()->forgetInstance('session.store');
    resetAuthSession();

    try {
        $admin = User::factory()->create([
            'role' => UserRole::SuperAdmin,
            'is_active' => true,
        ]);

        $customer = User::factory()->create([
            'role' => UserRole::Customer,
            'is_active' => true,
        ]);

        $loginResponse = $this->post('/login', [
            'email' => $customer->email,
            'password' => 'password',
            'remember' => 'on',
        ]);

        $sessionCookie = $loginResponse->getCookie(config('session.cookie'))?->getValue();
        $recallerName = Auth::guard('web')->getRecallerName();
        $rememberCookie = $loginResponse->getCookie($recallerName)?->getValue();

        resetAuthSession();

        $this->actingAs($admin)->delete(route('users.destroy', $customer))
            ->assertRedirect(route('users.index'));

        $trashed = User::withTrashed()->findOrFail($customer->id);
        expect($trashed->trashed())->toBeTrue();
        expect($trashed->is_active)->toBeFalse();
        expect($trashed->remember_token)->toBeNull();
        expect(DB::table(config('session.table', 'sessions'))->where('user_id', $customer->id)->count())->toBe(0);

        resetAuthSession();
        $this->withCookies([
            config('session.cookie') => $sessionCookie,
            $recallerName => $rememberCookie,
        ])->get('/dashboard')->assertRedirect(route('login'));

        resetAuthSession();
        $this->post('/login', [
            'email' => $customer->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');
        $this->assertGuest();

        // In-memory unsaved inactive User::factory()->make() must be logged out without triggering DB save().
        resetAuthSession();
        $countBefore = User::withTrashed()->count();
        $unsavedInactive = User::factory()->make([
            'is_active' => false,
            'role' => UserRole::Customer,
        ]);

        $this->actingAs($unsavedInactive)->get('/dashboard')->assertRedirect(route('login'));
        $this->assertGuest();
        expect(User::withTrashed()->count())->toBe($countBefore);
    } finally {
        config(['session.driver' => $originalDriver]);
        Session::forgetDrivers();
        app()->forgetInstance('session.store');
    }
});

test('visiting login, home, or logout with stale deactivated session behaves safely without redirect loop', function () {
    $user = User::factory()->create([
        'role' => UserRole::Customer,
        'is_active' => true,
    ]);

    $loginResponse = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
        'remember' => 'on',
    ]);

    $recallerName = Auth::guard('web')->getRecallerName();
    $rememberCookie = $loginResponse->getCookie($recallerName)?->getValue();

    $user->update(['is_active' => false]);

    $this->get('/login')->assertOk();
    $this->assertGuest();

    resetAuthSession();

    $this->withCookie($recallerName, $rememberCookie)->get('/')->assertOk();
    $this->assertGuest();

    resetAuthSession();
    $this->actingAs($user)->post('/logout')->assertRedirect('/');
    $this->assertGuest();
});

test('withoutMiddleware auth route does not misclassify as denied and super admin self-lockout, policy, and password reset blocks work', function () {
    Notification::fake();

    Route::middleware(['web', 'auth'])->group(function () {
        Route::get('/__test-public-in-auth-group', fn () => response('ok'))->withoutMiddleware('auth');
    });

    $admin = User::factory()->create([
        'role' => UserRole::SuperAdmin,
        'phone_number' => '081200001111',
        'is_active' => true,
    ]);

    $customer = User::factory()->create([
        'role' => UserRole::Customer,
        'phone_number' => '081200002222',
        'is_active' => true,
    ]);

    // Policy rejects non-super-admin updates.
    expect($customer->can('update', $customer))->toBeFalse();
    expect($admin->can('update', $customer))->toBeTrue();

    // Super admin cannot self-deactivate or self-demote.
    $selfEdit = $this->actingAs($admin)->get(route('users.edit', $admin));
    $selfEdit->assertOk();
    $selfEdit->assertSee('Anda tidak dapat menonaktifkan akun sendiri');

    $this->actingAs($admin)->from(route('users.edit', $admin))->put(route('users.update', $admin), [
        'name' => $admin->name,
        'email' => $admin->email,
        'phone_number' => $admin->phone_number,
        'role' => UserRole::SuperAdmin->value,
        'is_active' => '0',
    ])->assertSessionHasErrors('is_active');
    expect($admin->fresh()->is_active)->toBeTrue();

    $this->actingAs($admin)->from(route('users.edit', $admin))->put(route('users.update', $admin), [
        'name' => $admin->name,
        'email' => $admin->email,
        'phone_number' => $admin->phone_number,
        'role' => UserRole::Customer->value,
        'is_active' => '1',
    ])->assertSessionHasErrors('role');
    expect($admin->fresh()->role)->toBe(UserRole::SuperAdmin);

    // Admin resetting another user's password revokes remember_token and DB sessions.
    $customer->forceFill(['remember_token' => 'stale-token-xyz'])->save();
    $this->actingAs($admin)->put(route('users.update', $customer), [
        'name' => $customer->name,
        'email' => $customer->email,
        'phone_number' => $customer->phone_number,
        'role' => UserRole::Customer->value,
        'password' => 'NewAdminSetPass123!',
        'is_active' => '1',
    ])->assertRedirect(route('users.index'));
    expect($customer->fresh()->remember_token)->toBeNull();

    // Inactive user cannot request or complete password reset, even with a pre-issued token.
    $resetToken = Password::broker()->createToken($customer);
    $customer->update(['is_active' => false]);
    resetAuthSession();

    $this->post('/forgot-password', ['email' => $customer->email])
        ->assertSessionHasErrors('email');
    Notification::assertNothingSent();

    $this->post('/reset-password', [
        'token' => $resetToken,
        'email' => $customer->email,
        'password' => 'ResetAttemptPass123!',
        'password_confirmation' => 'ResetAttemptPass123!',
    ])->assertSessionHasErrors('email');

    // Route inside 'auth' group that explicitly excludes 'auth' via withoutMiddleware degrades to guest instead of redirecting.
    resetAuthSession();
    $this->actingAs($customer)->get('/__test-public-in-auth-group')
        ->assertOk()
        ->assertSee('ok');
    $this->assertGuest();
});

test('super admin can create inactive user, reactivate user, and active accounts across all roles including owner work normally', function () {
    $admin = User::factory()->create([
        'role' => UserRole::SuperAdmin,
        'is_active' => true,
    ]);

    $createResponse = $this->actingAs($admin)->get(route('users.create'));
    $createResponse->assertOk();
    $createResponse->assertSee('<input type="hidden" name="is_active" value="0">', false);

    $this->actingAs($admin)->post(route('users.store'), [
        'name' => 'Inactive Staff',
        'email' => 'inactive-staff@xyzmotors.id',
        'phone_number' => '081234560000',
        'role' => UserRole::Mekanik->value,
        'password' => 'SecretPass123!',
        'is_active' => '0',
    ])->assertRedirect(route('users.index'));

    $staff = User::where('email', 'inactive-staff@xyzmotors.id')->firstOrFail();
    expect($staff->is_active)->toBeFalse();

    resetAuthSession();

    $this->post('/login', [
        'email' => $staff->email,
        'password' => 'SecretPass123!',
    ])->assertSessionHasErrors('email');
    $this->assertGuest();

    resetAuthSession();

    $this->actingAs($admin)->put(route('users.update', $staff), [
        'name' => $staff->name,
        'email' => $staff->email,
        'phone_number' => $staff->phone_number,
        'role' => UserRole::Mekanik->value,
        'is_active' => '1',
    ])->assertRedirect(route('users.index'));

    expect($staff->fresh()->is_active)->toBeTrue();

    resetAuthSession();

    $this->post('/login', [
        'email' => $staff->email,
        'password' => 'SecretPass123!',
    ])->assertRedirect(route('dashboard', absolute: false));
    $this->assertAuthenticatedAs($staff);
    $this->get('/dashboard')->assertOk();

    foreach (UserRole::cases() as $role) {
        $user = User::factory()->create([
            'role' => $role,
            'is_active' => true,
        ]);

        resetAuthSession();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs($user);
        $this->get('/dashboard')->assertOk();
        $this->get('/profile')->assertOk();
        $this->get('/dashboard/notifications')->assertOk();
        $this->getJson('/dashboard/notifications/unread-count')->assertOk();

        if ($role === UserRole::Owner) {
            $this->get('/dashboard/reports')->assertOk();
        }
    }
});
