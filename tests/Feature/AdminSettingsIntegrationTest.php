<?php

use App\Enums\ServiceStatus;
use App\Enums\UserRole;
use App\Models\Service;
use App\Models\Setting;
use App\Models\User;
use App\Services\BookingSlotValidator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->superAdmin = User::factory()->create([
        'role' => UserRole::SuperAdmin,
        'is_active' => true,
    ]);

    $this->customer = User::factory()->create([
        'role' => UserRole::Customer,
        'is_active' => true,
    ]);
});

test('changing operational hours and quota in settings affects BookingSlotValidator', function () {
    $validator = app(BookingSlotValidator::class);
    $tomorrow = Carbon::tomorrow()->toDateString();

    // Default values
    expect($validator->getMaxPerSlot())->toBe(3);
    expect($validator->getOpenHour())->toBe(8);
    expect($validator->getCloseHour())->toBe(17);
    expect($validator->isSlotAvailable($tomorrow, 8))->toBeTrue();
    expect($validator->isSlotAvailable($tomorrow, 17))->toBeTrue();

    // Update settings via super admin
    $this->actingAs($this->superAdmin)->patch(route('settings.update'), [
        'booking_open_hour' => 9,
        'booking_close_hour' => 15,
        'booking_max_per_slot' => 5,
    ])->assertRedirect();

    expect($validator->getMaxPerSlot())->toBe(5);
    expect($validator->getOpenHour())->toBe(9);
    expect($validator->getCloseHour())->toBe(15);

    // Slot 8 and 16 must now be unavailable because outside 9-15
    expect($validator->isSlotAvailable($tomorrow, 8))->toBeFalse();
    expect($validator->isSlotAvailable($tomorrow, 16))->toBeFalse();
    expect($validator->isSlotAvailable($tomorrow, 10))->toBeTrue();
    expect($validator->getRemainingQuota($tomorrow, 10))->toBe(5);
});

test('changing operational hours affects StoreServiceBookingRequest validation', function () {
    // Admin sets hours to 9 - 15
    Setting::set('booking.open_hour', 9, 'booking', 'integer');
    Setting::set('booking.close_hour', 15, 'booking', 'integer');

    $futureDate = Carbon::now()->addDays(2);

    // Submitting with slot_hour 8 (outside 9-15) must be rejected
    $response = $this->actingAs($this->customer)->post('/service-booking', [
        'vehicle_plate_number' => 'B 1234 ABC',
        'vehicle_model' => 'Sedan City',
        'vehicle_year' => 2022,
        'odometer_km' => 15000,
        'service_type' => 'regular',
        'service_date' => $futureDate->format('Y-m-d 08:00:00'),
        'slot_hour' => 8,
        'complaint_notes' => 'Servis berkala di jam yang seharusnya ditolak.',
    ]);

    $response->assertSessionHasErrors(['slot_hour']);

    // Submitting with slot_hour 10 (inside 9-15) must succeed
    $validResponse = $this->actingAs($this->customer)->post('/service-booking', [
        'vehicle_plate_number' => 'B 1234 ABC',
        'vehicle_model' => 'Sedan City',
        'vehicle_year' => 2022,
        'odometer_km' => 15000,
        'service_type' => 'regular',
        'service_date' => $futureDate->format('Y-m-d 10:00:00'),
        'slot_hour' => 10,
        'complaint_notes' => 'Servis berkala di jam operasional yang valid.',
    ]);

    $validResponse->assertSessionHas('success');
    expect(Service::where('slot_hour', 10)->exists())->toBeTrue();
});

test('changing operational hours and quota reflects in showroom booking UI slot options and copy', function () {
    // Update settings
    Setting::set('booking.open_hour', 9, 'booking', 'integer');
    Setting::set('booking.close_hour', 14, 'booking', 'integer');
    Setting::set('booking.max_per_slot', 6, 'booking', 'integer');

    $response = $this->get(route('home'));
    $response->assertOk();

    // Verify copy reflects updated settings
    $response->assertSee('09:00 - 14:00 WIB');
    $response->assertSee('Maks. 6');

    // Verify select options for slot hours reflect 9 to 14
    $response->assertSee('value="9"', false);
    $response->assertSee('value="14"', false);
    $response->assertDontSee('value="8"', false);
    $response->assertDontSee('value="15"', false);
});

test('changing contact and SEO metadata in settings displays on public showroom page', function () {
    $this->actingAs($this->superAdmin)->patch(route('settings.update'), [
        'seo_site_name' => 'Apex Horizon Motors',
        'seo_tagline' => 'Eksklusif dan Bergaransi Resmi',
        'contact_whatsapp' => '628889997771',
        'contact_email' => 'halo@apexhorizon.id',
        'contact_address' => 'Gedung Bursa Efek Tower 2, Jakarta Pusat',
    ])->assertRedirect();

    $response = $this->get(route('home'));
    $response->assertOk();

    // Site name & Tagline
    $response->assertSee('Apex Horizon Motors');
    $response->assertSee('Eksklusif dan Bergaransi Resmi');

    // Contact info
    $response->assertSee('628889997771');
    $response->assertSee('halo@apexhorizon.id');
    $response->assertSee('Gedung Bursa Efek Tower 2, Jakarta Pusat');
});

test('settings validation enforces strict operational rules and safe defaults', function () {
    // 1. Close hour must be greater than open hour
    $invalidHours = $this->actingAs($this->superAdmin)->patch(route('settings.update'), [
        'booking_open_hour' => 17,
        'booking_close_hour' => 8,
    ]);
    $invalidHours->assertSessionHasErrors(['booking_close_hour']);

    // 2. Max per slot must be >= 1
    $invalidQuota = $this->actingAs($this->superAdmin)->patch(route('settings.update'), [
        'booking_max_per_slot' => 0,
    ]);
    $invalidQuota->assertSessionHasErrors(['booking_max_per_slot']);

    // 3. WhatsApp number must be numeric/plus only
    $invalidWa = $this->actingAs($this->superAdmin)->patch(route('settings.update'), [
        'contact_whatsapp' => 'bukan-nomor-wa',
    ]);
    $invalidWa->assertSessionHasErrors(['contact_whatsapp']);

    // 4. Email must be valid email
    $invalidEmail = $this->actingAs($this->superAdmin)->patch(route('settings.update'), [
        'contact_email' => 'invalid-email-format',
    ]);
    $invalidEmail->assertSessionHasErrors(['contact_email']);

    // 5. Non-super-admin receives 403
    $this->actingAs($this->customer)->patch(route('settings.update'), [
        'booking_max_per_slot' => 5,
    ])->assertStatus(403);
});

test('changing settings does not cancel or corrupt existing recorded bookings', function () {
    $date = Carbon::now()->addDays(3);

    $service = Service::create([
        'ticket_number' => 'SRV-TEST20-0001',
        'customer_id' => $this->customer->id,
        'vehicle_plate_number' => 'B 9999 VIP',
        'vehicle_model' => 'Sportscar',
        'vehicle_year' => 2024,
        'odometer_km' => 5000,
        'service_type' => 'major',
        'service_date' => $date->setTime(17, 0),
        'slot_hour' => 17,
        'complaint_notes' => 'Servis berkala besar sebelum perubahan jam.',
        'status' => ServiceStatus::Pending,
        'total_cost' => 0,
    ]);

    // Admin now restricts close hour to 15:00 and quota to 1
    $this->actingAs($this->superAdmin)->patch(route('settings.update'), [
        'booking_close_hour' => 15,
        'booking_max_per_slot' => 1,
    ])->assertRedirect();

    // Verify the previously recorded booking is completely intact
    $reloadedService = Service::find($service->id);
    expect($reloadedService)->not->toBeNull();
    expect($reloadedService->ticket_number)->toBe('SRV-TEST20-0001');
    expect($reloadedService->status)->toBe(ServiceStatus::Pending);
    expect($reloadedService->slot_hour)->toBe(17);
    expect((float) $reloadedService->total_cost)->toEqual(0);
});

test('cache invalidation functions properly when settings are saved', function () {
    Setting::set('booking.max_per_slot', 3, 'booking', 'integer');

    // Warm cache
    expect(Setting::get('booking.max_per_slot'))->toBe(3);
    expect(Cache::has('setting.booking.max_per_slot'))->toBeTrue();

    // Update via controller
    $this->actingAs($this->superAdmin)->patch(route('settings.update'), [
        'booking_max_per_slot' => 7,
    ]);

    // Cache must have been invalidated and new value fetched
    expect(Setting::get('booking.max_per_slot'))->toBe(7);
});
