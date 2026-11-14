<?php

namespace App\Models;

use App\Enums\ServiceStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $ticket_number
 * @property int $customer_id
 * @property string $vehicle_plate_number
 * @property string $vehicle_model
 * @property int $vehicle_year
 * @property int|null $odometer_km
 * @property string $service_type
 * @property Carbon $service_date
 * @property int|string $slot_hour
 * @property Carbon|null $slot_date
 * @property string|null $slot_key
 * @property string|null $active_plate_slot
 * @property string|null $complaint_notes
 * @property int|null $assigned_mechanic_id
 * @property string|null $mechanic_notes
 * @property string|null $recommendation
 * @property string $estimated_cost
 * @property string $total_cost
 * @property ServiceStatus $status
 * @property Carbon|null $confirmed_at
 * @property Carbon|null $started_at
 * @property Carbon|null $completed_at
 * @property Carbon|null $cancelled_at
 * @property string|null $cancellation_reason
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $customer
 * @property-read User|null $mechanic
 * @property-read Collection<int, ServiceItem> $items
 * @property-read Collection<int, ServiceStatusLog> $statusLogs
 */
class Service extends Model
{
    use HasFactory, SoftDeletes;

    public ?string $status_change_notes = null;

    protected $fillable = [
        'ticket_number', 'customer_id', 'vehicle_plate_number',
        'vehicle_model', 'vehicle_year', 'odometer_km',
        'service_type', 'service_date', 'slot_hour',
        'slot_date', 'slot_key', 'active_plate_slot',
        'complaint_notes', 'assigned_mechanic_id',
        'mechanic_notes', 'recommendation',
        'estimated_cost', 'total_cost', 'status',
        'confirmed_at', 'started_at', 'completed_at',
        'cancelled_at', 'cancellation_reason',
    ];

    protected $appends = [
        'car_model',
        'license_plate',
        'complaint',
    ];

    protected function casts(): array
    {
        return [
            'service_date' => 'datetime',
            'slot_date' => 'date',
            'confirmed_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'estimated_cost' => 'decimal:2',
            'total_cost' => 'decimal:2',
            'status' => ServiceStatus::class,
        ];
    }

    // --- Scopes ---

    public function scopeForMechanic(Builder $query, int $mechanicId): Builder
    {
        return $query->where('assigned_mechanic_id', $mechanicId);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNotIn('status', [
            ServiceStatus::Completed->value,
            ServiceStatus::Cancelled->value,
        ]);
    }

    // --- Relasi ---

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function mechanic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_mechanic_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ServiceItem::class);
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(ServiceStatusLog::class)->orderBy('created_at');
    }

    // --- Accessors ---

    protected function statusLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->status->label(),
        );
    }

    protected function statusColor(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->status->color(),
        );
    }

    protected function totalCostFormatted(): Attribute
    {
        return Attribute::make(
            get: fn () => 'Rp'.number_format((float) $this->total_cost, 0, ',', '.'),
        );
    }

    protected function carModel(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->vehicle_model,
        );
    }

    protected function licensePlate(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->vehicle_plate_number,
        );
    }

    protected function complaint(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->complaint_notes,
        );
    }

    public function progressPercent(): int
    {
        return match ($this->status) {
            ServiceStatus::Pending => 25,
            ServiceStatus::Confirmed => 50,
            ServiceStatus::InProgress => 75,
            ServiceStatus::Completed => 100,
            ServiceStatus::Cancelled => 0,
        };
    }
}
