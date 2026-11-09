<?php

namespace App\Models;

use App\Enums\CarCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Car extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'slug', 'brand', 'model_name', 'year', 'category',
        'color_name', 'color_hex', 'price', 'engine_specs',
        'dimensions', 'features', 'description',
        'hero_image_transparent', 'hero_image_alt', 'gallery_images',
        'stock_quantity', 'is_available', 'is_featured',
        'sort_order', 'view_count', 'meta_title', 'meta_description',
    ];

    protected function casts(): array
    {
        return [
            'engine_specs' => 'array',
            'dimensions' => 'array',
            'features' => 'array',
            'gallery_images' => 'array',
            'price' => 'decimal:2',
            'is_available' => 'boolean',
            'is_featured' => 'boolean',
            'category' => CarCategory::class,
        ];
    }

    // --- Scopes ---

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('is_available', true);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order');
    }

    // --- Relasi ---

    public function promos(): HasMany
    {
        return $this->hasMany(Promo::class);
    }

    public function testDrives(): HasMany
    {
        return $this->hasMany(TestDrive::class);
    }

    // --- Accessors ---

    protected function priceFormatted(): Attribute
    {
        return Attribute::make(
            get: fn () => 'Rp'.number_format((float) $this->price, 0, ',', '.'),
        );
    }

    protected function heroImageUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->hero_image_transparent
                ? asset($this->hero_image_transparent)
                : asset('images/cars/'.$this->slug.'.webp'),
        );
    }

    // --- Route Model Binding ---

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
