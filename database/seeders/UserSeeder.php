<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing']) || in_array(config('app.env'), ['production', 'staging'], true)) {
            if (isset($this->command)) {
                $this->command->warn('UserSeeder dilewati di luar environment local/testing.');
            }

            return;
        }

        $users = [
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@xyzmotors.id',
                'phone_number' => '081200000001',
                'role' => UserRole::SuperAdmin,
            ],
            [
                'name' => 'Budi Sales',
                'email' => 'sales@xyzmotors.id',
                'phone_number' => '081200000002',
                'role' => UserRole::Sales,
            ],
            [
                'name' => 'Agus Mekanik',
                'email' => 'mekanik@xyzmotors.id',
                'phone_number' => '081200000007',
                'role' => UserRole::Mekanik,
            ],
            [
                'name' => 'Agus Mekanik',
                'email' => 'mekanik1@xyzmotors.id',
                'phone_number' => '081200000003',
                'role' => UserRole::Mekanik,
            ],
            [
                'name' => 'Sari Mekanik',
                'email' => 'mekanik2@xyzmotors.id',
                'phone_number' => '081200000004',
                'role' => UserRole::Mekanik,
            ],
            [
                'name' => 'Andi Customer',
                'email' => 'customer@xyzmotors.id',
                'phone_number' => '081200000005',
                'role' => UserRole::Customer,
            ],
            [
                'name' => 'Bapak Hartono (Owner)',
                'email' => 'owner@xyzmotors.id',
                'phone_number' => '081200000006',
                'role' => UserRole::Owner,
            ],
        ];

        foreach ($users as $userData) {
            if (User::withTrashed()->where('email', $userData['email'])->exists()) {
                continue;
            }

            User::forceCreate($userData + [
                'password' => 'password',
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
        }
    }
}
