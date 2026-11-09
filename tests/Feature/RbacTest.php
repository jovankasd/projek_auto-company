<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('customer is blocked with 403 on administrative routes', function () {
    $customer = User::factory()->create([
        'role' => UserRole::Customer,
    ]);

    $this->actingAs($customer)->get('/dashboard/cars')->assertStatus(403);
    $this->actingAs($customer)->get('/dashboard/users')->assertStatus(403);
    $this->actingAs($customer)->get('/dashboard/reports')->assertStatus(403);
    $this->actingAs($customer)->get('/dashboard/audit-logs')->assertStatus(403);
    $this->actingAs($customer)->get('/dashboard/settings')->assertStatus(403);
});

test('mekanik cannot access sales or master data catalog', function () {
    $mechanic = User::factory()->create([
        'role' => UserRole::Mekanik,
    ]);

    $this->actingAs($mechanic)->get('/dashboard/cars')->assertStatus(403);
    $this->actingAs($mechanic)->get('/dashboard/promos')->assertStatus(403);
    $this->actingAs($mechanic)->get('/dashboard/users')->assertStatus(403);
});

test('sales can access cars catalog and promos but cannot access user management', function () {
    $sales = User::factory()->create([
        'role' => UserRole::Sales,
    ]);

    $this->actingAs($sales)->get('/dashboard/cars')->assertStatus(200);
    $this->actingAs($sales)->get('/dashboard/promos')->assertStatus(200);
    $this->actingAs($sales)->get('/dashboard/users')->assertStatus(403);
    $this->actingAs($sales)->get('/dashboard/reports')->assertStatus(403);
});

test('super admin has full access across all administrative panels', function () {
    $admin = User::factory()->create([
        'role' => UserRole::SuperAdmin,
    ]);

    $this->actingAs($admin)->get('/dashboard/cars')->assertStatus(200);
    $this->actingAs($admin)->get('/dashboard/users')->assertStatus(200);
    $this->actingAs($admin)->get('/dashboard/reports')->assertStatus(200);
    $this->actingAs($admin)->get('/dashboard/audit-logs')->assertStatus(200);
    $this->actingAs($admin)->get('/dashboard/settings')->assertStatus(200);
});
