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

    public function getRedirectRoute(): string
    {
        return match ($this->role) {
            UserRole::SuperAdmin => 'dashboard',
            UserRole::Sales => 'dashboard',
            UserRole::Mekanik => 'dashboard',
            UserRole::Customer => 'dashboard',
            UserRole::Owner => 'dashboard',
        };
    }
}
