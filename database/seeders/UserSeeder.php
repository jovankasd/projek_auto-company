<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Pastikan kolom penunjang di tabel users tersedia
        $columns = [
            'role' => fn ($table) => $table->string('role')->default('customer')->after('email'),
            'phone_number' => fn ($table) => $table->string('phone_number')->nullable(),
            'avatar_path' => fn ($table) => $table->string('avatar_path')->nullable(),
            'is_active' => fn ($table) => $table->boolean('is_active')->default(true),
            'last_login_at' => fn ($table) => $table->timestamp('last_login_at')->nullable(),
            'deleted_at' => fn ($table) => $table->softDeletes(),
        ];

        foreach ($columns as $column => $callback) {
            try {
                if (! Schema::hasColumn('users', $column)) {
                    Schema::table('users', $callback);
                }
            } catch (\Throwable $e) {
                // Abaikan jika sudah ada
            }
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
            try {
                $userQuery = User::query();
                if (Schema::hasColumn('users', 'deleted_at')) {
                    $userQuery->withTrashed();
                }
                $user = $userQuery->where('email', $userData['email'])->first();

                if (! $user) {
                    $user = new User;
                    $user->email = $userData['email'];
                } elseif (method_exists($user, 'trashed') && $user->trashed()) {
                    $user->restore();
                }

                $user->name = $userData['name'];
                $user->password = 'password';
                if (Schema::hasColumn('users', 'role')) {
                    $user->role = $userData['role'];
                }
                if (Schema::hasColumn('users', 'phone_number')) {
                    $user->phone_number = $userData['phone_number'];
                }
                if (Schema::hasColumn('users', 'is_active')) {
                    $user->is_active = true;
                }
                $user->email_verified_at = now();
                $user->save();
            } catch (\Throwable $e) {
                // Abaikan jika ada error parsial
            }
        }
    }
}
