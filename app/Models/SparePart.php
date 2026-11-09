<?php

namespace App\Models;

use App\Enums\SparePartCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SparePart extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'slug', 'name', 'part_number', 'brand', 'category',
        'car_brand_compatibility', 'car_model_compatibility',
        'description', 'image_path', 'price', 'stock_quantity', 'min_stock_alert',
        'weight_kg', 'dimensions_cm', 'is_available', 'is_featured',
        'sort_order', 'view_count', 'meta_title', 'meta_description',
    ];

    protected function casts(): array
    {
        return [
            'car_brand_compatibility' => 'array',
            'car_model_compatibility' => 'array',
            'dimensions_cm' => 'array',
            'price' => 'decimal:2',
            'weight_kg' => 'decimal:2',
            'stock_quantity' => 'integer',
            'min_stock_alert' => 'integer',
            'is_available' => 'boolean',
            'is_featured' => 'boolean',
            'category' => SparePartCategory::class,
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

    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereColumn('stock_quantity', '<=', 'min_stock_alert');
    }

    public function isLowStock(): bool
    {
        return $this->stock_quantity <= $this->min_stock_alert;
    }

    // --- Accessors ---

    protected function priceFormatted(): Attribute
    {
        return Attribute::make(
            get: fn () => 'Rp '.number_format((float) $this->price, 0, ',', '.'),
        );
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->image_path) {
                    if (str_starts_with($this->image_path, 'http')) {
                        return $this->image_path;
                    }
                    if (file_exists(public_path($this->image_path))) {
                        return asset($this->image_path);
                    }
                    if (file_exists(public_path('storage/'.$this->image_path))) {
                        return asset('storage/'.$this->image_path);
                    }
                    if (file_exists(public_path('images/spare-parts/'.basename($this->image_path)))) {
                        return asset('images/spare-parts/'.basename($this->image_path));
                    }
                }

                $key = strtolower(($this->slug ?? '').' '.($this->name ?? '').' '.($this->part_number ?? '').' '.($this->brand ?? ''));

                // Prioritaskan pencocokan spesifik sebelum kata kunci generik
                $mapping = [
                    'brake' => 'brembo-brake-pad.jpg',
                    'brembo' => 'brembo-brake-pad.jpg',
                    'brk' => 'brembo-brake-pad.jpg',

                    'spark' => 'ngk-spark-plug.jpg',
                    'plug' => 'ngk-spark-plug.jpg',
                    'spk' => 'ngk-spark-plug.jpg',
                    'ngk' => 'ngk-spark-plug.jpg',

                    'battery' => 'tesla-battery.jpg',
                    'bat' => 'tesla-battery.jpg',
                    'tsl' => 'tesla-battery.jpg',
                    'tesla' => 'tesla-battery.jpg',

                    'coolant' => 'prestone-coolant.jpg',
                    'radiator' => 'prestone-coolant.jpg',
                    'rad' => 'prestone-coolant.jpg',
                    'prestone' => 'prestone-coolant.jpg',

                    'atf' => 'toyota-atf-fluid.jpg',
                    'trn' => 'toyota-atf-fluid.jpg',
                    'transmission' => 'toyota-atf-fluid.jpg',

                    'cabin' => 'mann-cabin-filter.jpg',
                    'cab' => 'mann-cabin-filter.jpg',
                    'mann' => 'mann-cabin-filter.jpg',

                    'wiper' => 'bosch-wiper-blade.jpg',
                    'wip' => 'bosch-wiper-blade.jpg',

                    'alternator' => 'motorcraft-alternator.jpg',
                    'alt' => 'motorcraft-alternator.jpg',
                    'motorcraft' => 'motorcraft-alternator.jpg',

                    'timing' => 'aisin-timing-belt.jpg',
                    'belt' => 'aisin-timing-belt.jpg',
                    'tim' => 'aisin-timing-belt.jpg',
                    'aisin' => 'aisin-timing-belt.jpg',

                    'bulb' => 'philips-headlight-led.jpg',
                    'headlight' => 'philips-headlight-led.jpg',
                    'led' => 'philips-headlight-led.jpg',
                    'lgt' => 'philips-headlight-led.jpg',
                    'philips' => 'philips-headlight-led.jpg',

                    'bumper' => 'arb-rear-bumper.jpg',
                    'bmp' => 'arb-rear-bumper.jpg',
                    'arb' => 'arb-rear-bumper.jpg',

                    'shock' => 'fox-shock-absorber.jpg',
                    'shk' => 'fox-shock-absorber.jpg',
                    'fox' => 'fox-shock-absorber.jpg',
                    'suspension' => 'fox-shock-absorber.jpg',

                    'exhaust' => 'borla-exhaust.jpg',
                    'exh' => 'borla-exhaust.jpg',
                    'muffler' => 'borla-exhaust.jpg',
                    'borla' => 'borla-exhaust.jpg',

                    'oil' => 'toyota-oil-filter.jpg',
                    'air' => 'bosch-air-filter.jpg',
                ];

                foreach ($mapping as $needle => $file) {
                    if (str_contains($key, $needle)) {
                        if (file_exists(public_path('images/spare-parts/'.$file))) {
                            return asset('images/spare-parts/'.$file);
                        }
                    }
                }

                if (file_exists(public_path('images/spare-parts/default.webp'))) {
                    return asset('images/spare-parts/default.webp');
                }
                if (file_exists(public_path('images/spare-parts/default.jpg'))) {
                    return asset('images/spare-parts/default.jpg');
                }
                if (file_exists(public_path('images/spare-parts/default.svg'))) {
                    return asset('images/spare-parts/default.svg');
                }

                return asset('images/spare-parts/toyota-oil-filter.jpg');
            }
        );
    }

    // --- Route Model Binding ---

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
