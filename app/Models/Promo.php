<?php

namespace App\Models;

use App\Enums\DiscountType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Promo extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code', 'title', 'description', 'discount_type',
        'discount_value', 'car_id', 'banner_image',
        'starts_at', 'ends_at', 'max_usage', 'used_count', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'discount_value' => 'decimal:2',
            'is_active' => 'boolean',
            'discount_type' => DiscountType::class,
        ];
    }

    // --- Scopes ---

    public function scopeActive(Builder $query): Builder
    {
        $now = Carbon::now();

        return $query
            ->where('is_active', true)
            ->where('starts_at', '<=', $now)
            ->where('ends_at', '>=', $now);
    }

    public function scopeAvailableForService(Builder $query): Builder
    {
        $now = Carbon::now();

        return $query
            ->where('is_active', true)
            ->whereNull('car_id')
            ->where('starts_at', '<=', $now)
            ->where('ends_at', '>=', $now)
            ->where(function (Builder $q) {
                $q->whereNull('max_usage')
                    ->orWhereColumn('used_count', '<', 'max_usage');
            });
    }

    public function calculateDiscount(float $grossTotal): float
    {
        if ($grossTotal <= 0) {
            return 0.0;
        }

        $type = $this->discount_type instanceof DiscountType
            ? $this->discount_type
            : DiscountType::tryFrom((string) $this->discount_type);

        return match ($type) {
            DiscountType::Percentage => min($grossTotal, round($grossTotal * ((float) $this->discount_value / 100), 2)),
            DiscountType::Fixed => min($grossTotal, (float) $this->discount_value),
            default => 0.0,
        };
    }

    // --- Relasi ---

    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }
}
