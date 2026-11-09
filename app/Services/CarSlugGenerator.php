<?php

namespace App\Services;

use App\Models\Car;
use Illuminate\Support\Str;

class CarSlugGenerator
{
    /**
     * Generate slug unik untuk mobil dari brand + model + year.
     */
    public function generate(string $brand, string $modelName, int $year): string
    {
        $base = Str::slug("{$brand} {$modelName} {$year}");
        $slug = $base;
        $counter = 1;

        while (Car::withTrashed()->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    /**
     * Generate slug unik untuk update (exclude mobil itu sendiri).
     */
    public function generateForUpdate(string $brand, string $modelName, int $year, int $excludeId): string
    {
        $base = Str::slug("{$brand} {$modelName} {$year}");
        $slug = $base;
        $counter = 1;

        while (Car::withTrashed()->where('slug', $slug)->where('id', '!=', $excludeId)->exists()) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
