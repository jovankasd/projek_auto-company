<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string|null $phone_number
 * @property string $password
 * @property UserRole $role
 * @property string|null $avatar_path
 * @property bool $is_active
 * @property Carbon|null $email_verified_at
 * @property Carbon|null $last_login_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    protected $attributes = [
        'is_active' => true,
    ];

    protected $fillable = [
        'name',
        'email',
        'phone_number',
        'password',
        'role',
        'avatar_path',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_active' => 'boolean',
        ];
    }

    // --- Relasi ---

    public function services(): HasMany
    {
        return $this->hasMany(Service::class, 'customer_id');
    }

    public function assignedServices(): HasMany
    {
        return $this->hasMany(Service::class, 'assigned_mechanic_id');
    }

    public function testDrives(): HasMany
    {
        return $this->hasMany(TestDrive::class, 'customer_id');
    }

    public function assignedTestDrives(): HasMany
    {
        return $this->hasMany(TestDrive::class, 'sales_id');
    }

    // --- Helper ---

    public function hasRole(UserRole $role): bool
    {
        return $this->role === $role;
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === UserRole::SuperAdmin;
    }

    public function isSales(): bool
    {
        return $this->role === UserRole::Sales;
    }

    public function isMekanik(): bool
    {
        return $this->role === UserRole::Mekanik;
    }

    public function isCustomer(): bool
    {
        return $this->role === UserRole::Customer;
    }

    public function isOwner(): bool
    {
        return $this->role === UserRole::Owner;
    }

    /**
     * Resolve atau buat entitas pemesan (guest customer) tanpa akun login aktif.
     */
    public static function resolveGuestCustomer(?string $name = null, ?string $phone = null, ?string $email = null): self
    {
        $cleanName = trim((string) $name) !== '' ? trim((string) $name) : 'Pelanggan Showroom';
        $cleanPhone = trim((string) $phone) !== '' ? trim((string) $phone) : '081200000000';
        $cleanEmail = trim((string) $email) !== '' ? strtolower(trim((string) $email)) : null;

        if ($cleanEmail) {
            $existingByEmail = self::withTrashed()->where('email', $cleanEmail)->first();
            if ($existingByEmail) {
                if ($existingByEmail->trashed()) {
                    $existingByEmail->restore();
                }

                return $existingByEmail;
            }
        }

        $digits = preg_replace('/\D+/', '', $cleanPhone) ?: '0000';
        $generatedEmail = $cleanEmail ?: 'pelanggan.'.$digits.'.'.strtolower(Str::random(6)).'@guest.xyzmotors.id';

        return self::create([
            'name' => $cleanName,
            'email' => $generatedEmail,
            'phone_number' => $cleanPhone,
            'password' => Str::random(32),
            'role' => UserRole::Customer,
            'is_active' => false,
        ]);
    }

    public function revokeAccess(): void
    {
        $this->forceFill([
            'remember_token' => null,
        ])->saveQuietly();

        if (config('session.driver') === 'database') {
            /** @var string|null $connection */
            $connection = config('session.connection');
            /** @var string $table */
            $table = config('session.table', 'sessions');

            DB::connection($connection)
                ->table($table)
                ->where('user_id', $this->getKey())
                ->delete();
        }
    }
}
