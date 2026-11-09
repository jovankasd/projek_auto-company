<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use PDO;

class DatabaseBootstrapper
{
    protected static bool $initialized = false;

    /**
     * Pastikan database, tabel, dan akun demo siap digunakan.
     *
     * IMPORTANT: Helper standalone ini TIDAK dipanggil otomatis oleh HTTP middleware
     * atau controller web; jalankan `php artisan migrate:fresh --seed` untuk inisialisasi
     * awal dan jangan memanggil `ensureDemoAccounts()` di lingkungan produksi publik.
     */
    public static function ensureReady(): void
    {
        if (self::$initialized) {
            return;
        }

        self::$initialized = true;

        // 1. Jika koneksi MySQL, pastikan database xyz_motors sudah dibuat
        $defaultConn = config('database.default', 'mysql');
        if ($defaultConn === 'mysql') {
            $host = config('database.connections.mysql.host', '127.0.0.1');
            $port = config('database.connections.mysql.port', '3306');
            $database = config('database.connections.mysql.database', 'xyz_motors');
            $username = config('database.connections.mysql.username', 'root');
            $password = config('database.connections.mysql.password', '');

            try {
                // Koneksi ke server MySQL tanpa dbname untuk membuat database jika belum ada
                $pdo = new PDO("mysql:host={$host};port={$port}", $username, $password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_TIMEOUT => 2,
                ]);
                $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
            } catch (\Throwable $e) {
                Log::warning('Auto-create MySQL DB note: '.$e->getMessage());
                // Jika MySQL mati total (connection refused), siapkan fallback sqlite agar app tetap jalan
                if (str_contains($e->getMessage(), '2002') || str_contains($e->getMessage(), 'Connection refused')) {
                    self::fallbackToSqlite();
                }
            }
        }

        // 2. Jalankan migrasi jika tabel belum lengkap
        try {
            if (! Schema::hasTable('users')) {
                Artisan::call('migrate', ['--force' => true]);
                Artisan::call('db:seed', ['--force' => true]);
            }
        } catch (\Throwable $e) {
            Log::warning('Auto-migration note: '.$e->getMessage());
        }

        // 3. Pastikan kolom role bertipe VARCHAR(50) agar mendukung role 'owner'
        try {
            DB::statement("ALTER TABLE users MODIFY COLUMN role VARCHAR(50) NOT NULL DEFAULT 'customer'");
        } catch (\Throwable $e) {
            // Abaikan jika sqlite atau sudah sesuai
        }

        // 4. Pastikan kolom-kolom pelengkap di tabel users ada
        try {
            $columns = [
                'role' => fn ($table) => $table->string('role')->default('customer')->after('email'),
                'phone_number' => fn ($table) => $table->string('phone_number')->nullable(),
                'avatar_path' => fn ($table) => $table->string('avatar_path')->nullable(),
                'is_active' => fn ($table) => $table->boolean('is_active')->default(true),
                'last_login_at' => fn ($table) => $table->timestamp('last_login_at')->nullable(),
                'deleted_at' => fn ($table) => $table->softDeletes(),
            ];

            foreach ($columns as $column => $callback) {
                if (! Schema::hasColumn('users', $column)) {
                    Schema::table('users', $callback);
                }
            }
        } catch (\Throwable $e) {
        }

        // 5. Pastikan semua akun bawaan (Owner, Super Admin, Sales, Admin Mekanik, Customer) ada dan aktif
        self::ensureDemoAccounts();
    }

    protected static function fallbackToSqlite(): void
    {
        try {
            $sqlitePath = database_path('database.sqlite');
            if (! file_exists($sqlitePath)) {
                touch($sqlitePath);
            }
            config(['database.default' => 'sqlite']);
            DB::purge('mysql');
            DB::reconnect('sqlite');
        } catch (\Throwable $e) {
            Log::error('SQLite fallback failed: '.$e->getMessage());
        }
    }

    public static function ensureDemoAccounts(): void
    {
        $accounts = [
            'owner@xyzmotors.id' => [
                'name' => 'Bapak Hartono (Owner)',
                'role' => 'owner',
                'phone_number' => '081200000006',
            ],
            'superadmin@xyzmotors.id' => [
                'name' => 'Super Admin',
                'role' => 'super_admin',
                'phone_number' => '081200000001',
            ],
            'sales@xyzmotors.id' => [
                'name' => 'Budi Sales',
                'role' => 'sales',
                'phone_number' => '081200000002',
            ],
            'mekanik1@xyzmotors.id' => [
                'name' => 'Agus Mekanik',
                'role' => 'mekanik',
                'phone_number' => '081200000003',
            ],
            'mekanik2@xyzmotors.id' => [
                'name' => 'Sari Mekanik',
                'role' => 'mekanik',
                'phone_number' => '081200000004',
            ],
            'customer@xyzmotors.id' => [
                'name' => 'Andi Customer',
                'role' => 'customer',
                'phone_number' => '081200000005',
            ],
        ];

        foreach ($accounts as $email => $data) {
            try {
                $user = User::withTrashed()->where('email', $email)->first();
                if (! $user) {
                    $user = new User;
                    $user->email = $email;
                } elseif (method_exists($user, 'trashed') && $user->trashed()) {
                    $user->restore();
                }

                $user->name = $data['name'];
                $user->password = 'password'; // Otomatis di-hash oleh cast 'password' => 'hashed'
                $user->role = UserRole::from($data['role']);
                $user->phone_number = $data['phone_number'];
                $user->is_active = true;
                $user->email_verified_at = now();
                $user->save();
            } catch (\Throwable $e) {
                // Fallback direct DB table insert / update
                try {
                    $exists = DB::table('users')->where('email', $email)->first();
                    if ($exists) {
                        DB::table('users')->where('email', $email)->update([
                            'password' => Hash::make('password'),
                            'role' => $data['role'],
                            'is_active' => 1,
                            'deleted_at' => null,
                            'email_verified_at' => now(),
                            'updated_at' => now(),
                        ]);
                    } else {
                        DB::table('users')->insert([
                            'name' => $data['name'],
                            'email' => $email,
                            'password' => Hash::make('password'),
                            'role' => $data['role'],
                            'phone_number' => $data['phone_number'],
                            'is_active' => 1,
                            'email_verified_at' => now(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                } catch (\Throwable $e2) {
                    Log::warning("Could not sync account {$email}: ".$e2->getMessage());
                }
            }
        }
    }
}
