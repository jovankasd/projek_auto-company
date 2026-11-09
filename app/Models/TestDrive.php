<?php

namespace App\Models;

use App\Enums\TestDriveStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $customer_id
 * @property int $car_id
 * @property int|null $sales_id
 * @property Carbon $preferred_date
 * @property string|null $driver_license_id
 * @property string|null $driver_license_image
 * @property string|null $pickup_location
 * @property TestDriveStatus $status
 * @property string|null $sales_notes
 * @property string|null $rejection_reason
 * @property Carbon|null $approved_at
 * @property Carbon|null $completed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $customer
 * @property-read Car $car
 * @property-read User|null $sales
 */
class TestDrive extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id', 'car_id', 'sales_id',
        'preferred_date', 'driver_license_id',
        'driver_license_image', 'pickup_location',
        'status', 'sales_notes', 'rejection_reason',
        'approved_at', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'preferred_date' => 'datetime',
            'approved_at' => 'datetime',
            'completed_at' => 'datetime',
            'status' => TestDriveStatus::class,
        ];
    }

    // --- Scopes ---

    public function scopeForSales(Builder $query, int $salesId): Builder
    {
        return $query->where('sales_id', $salesId);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', TestDriveStatus::Requested);
    }

    // --- Relasi ---

    /**
     * @return BelongsTo<User, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /**
     * @return BelongsTo<Car, $this>
     */
    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function sales(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sales_id');
    }
}
