<?php

namespace Database\Seeders;

use App\Enums\SparePartCategory;
use App\Models\SparePart;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SparePartSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing']) || in_array(config('app.env'), ['production', 'staging'], true)) {
            return;
        }

        $spareParts = [
            [
                'name' => 'Brake Pad Set',
                'part_number' => 'BRK-FRD-001',
                'brand' => 'Brembo',
                'category' => SparePartCategory::Brake,
                'car_brand_compatibility' => ['Ford'],
                'car_model_compatibility' => ['Mustang'],
                'price' => 2500000,
                'stock_quantity' => 15,
                'min_stock_alert' => 5,
            ],
            [
                'name' => 'Oil Filter',
                'part_number' => 'OIL-TYT-002',
                'brand' => 'Toyota Genuine Parts',
                'category' => SparePartCategory::Engine,
                'car_brand_compatibility' => ['Toyota'],
                'car_model_compatibility' => ['Camry', 'Corolla'],
                'price' => 150000,
                'stock_quantity' => 100,
                'min_stock_alert' => 20,
            ],
            [
                'name' => 'Air Filter',
                'part_number' => 'AIR-BMW-003',
                'brand' => 'Bosch',
                'category' => SparePartCategory::Engine,
                'car_brand_compatibility' => ['BMW'],
                'car_model_compatibility' => ['3 Series', '5 Series'],
                'price' => 850000,
                'stock_quantity' => 30,
                'min_stock_alert' => 10,
            ],
            [
                'name' => 'Spark Plug Set',
                'part_number' => 'SPK-FRD-004',
                'brand' => 'NGK',
                'category' => SparePartCategory::Electrical,
                'car_brand_compatibility' => ['Ford'],
                'car_model_compatibility' => ['Ranger', 'Everest'],
                'price' => 1200000,
                'stock_quantity' => 45,
                'min_stock_alert' => 10,
            ],
            [
                'name' => 'Battery 12V',
                'part_number' => 'BAT-TSL-005',
                'brand' => 'Tesla',
                'category' => SparePartCategory::Electrical,
                'car_brand_compatibility' => ['Tesla'],
                'car_model_compatibility' => ['Model 3', 'Model Y'],
                'price' => 45000000,
                'stock_quantity' => 5,
                'min_stock_alert' => 2,
            ],
            [
                'name' => 'Shock Absorber Front',
                'part_number' => 'SHK-FRD-006',
                'brand' => 'Fox',
                'category' => SparePartCategory::Suspension,
                'car_brand_compatibility' => ['Ford'],
                'car_model_compatibility' => ['Ranger Raptor'],
                'price' => 5500000,
                'stock_quantity' => 12,
                'min_stock_alert' => 4,
            ],
            [
                'name' => 'Radiator Coolant 4L',
                'part_number' => 'RAD-GEN-007',
                'brand' => 'Prestone',
                'category' => SparePartCategory::Cooling,
                'car_brand_compatibility' => ['Toyota', 'Honda', 'Suzuki'],
                'car_model_compatibility' => [],
                'price' => 250000,
                'stock_quantity' => 50,
                'min_stock_alert' => 15,
            ],
            [
                'name' => 'Transmission Fluid ATF',
                'part_number' => 'TRN-TYT-008',
                'brand' => 'Toyota Genuine Parts',
                'category' => SparePartCategory::Transmission,
                'car_brand_compatibility' => ['Toyota'],
                'car_model_compatibility' => ['Fortuner', 'Innova'],
                'price' => 450000,
                'stock_quantity' => 40,
                'min_stock_alert' => 10,
            ],
            [
                'name' => 'Cabin Air Filter',
                'part_number' => 'CAB-BMW-009',
                'brand' => 'Mann Filter',
                'category' => SparePartCategory::Interior,
                'car_brand_compatibility' => ['BMW'],
                'car_model_compatibility' => ['X5', 'X3'],
                'price' => 600000,
                'stock_quantity' => 25,
                'min_stock_alert' => 5,
            ],
            [
                'name' => 'Exhaust Muffler',
                'part_number' => 'EXH-FRD-010',
                'brand' => 'Borla',
                'category' => SparePartCategory::Exhaust,
                'car_brand_compatibility' => ['Ford'],
                'car_model_compatibility' => ['Mustang'],
                'price' => 15000000,
                'stock_quantity' => 3,
                'min_stock_alert' => 1,
            ],
            [
                'name' => 'Wiper Blade Set',
                'part_number' => 'WIP-TYT-011',
                'brand' => 'Bosch',
                'category' => SparePartCategory::Accessories,
                'car_brand_compatibility' => ['Toyota'],
                'car_model_compatibility' => ['Avanza', 'Veloz'],
                'price' => 180000,
                'stock_quantity' => 80,
                'min_stock_alert' => 20,
            ],
            [
                'name' => 'Alternator Assembly',
                'part_number' => 'ALT-FRD-012',
                'brand' => 'Motorcraft',
                'category' => SparePartCategory::Electrical,
                'car_brand_compatibility' => ['Ford'],
                'car_model_compatibility' => ['Everest'],
                'price' => 4800000,
                'stock_quantity' => 8,
                'min_stock_alert' => 2,
            ],
            [
                'name' => 'Timing Belt Kit',
                'part_number' => 'TIM-TYT-013',
                'brand' => 'Aisin',
                'category' => SparePartCategory::Engine,
                'car_brand_compatibility' => ['Toyota'],
                'car_model_compatibility' => ['Hilux'],
                'price' => 1850000,
                'stock_quantity' => 15,
                'min_stock_alert' => 5,
            ],
            [
                'name' => 'Headlight Bulb LED H4',
                'part_number' => 'LGT-GEN-014',
                'brand' => 'Philips',
                'category' => SparePartCategory::Electrical,
                'car_brand_compatibility' => ['Toyota', 'Honda', 'Suzuki', 'Mitsubishi'],
                'car_model_compatibility' => [],
                'price' => 850000,
                'stock_quantity' => 60,
                'min_stock_alert' => 15,
            ],
            [
                'name' => 'Rear Bumper Guard',
                'part_number' => 'BMP-FRD-015',
                'brand' => 'ARB',
                'category' => SparePartCategory::Body,
                'car_brand_compatibility' => ['Ford'],
                'car_model_compatibility' => ['Ranger'],
                'price' => 12500000,
                'stock_quantity' => 4,
                'min_stock_alert' => 1,
            ],
        ];

        foreach ($spareParts as $index => $part) {
            $slug = Str::slug($part['name'].'-'.$part['part_number']);

            SparePart::firstOrCreate(
                ['slug' => $slug],
                array_merge($part, [
                    'sort_order' => $index,
                    'is_available' => true,
                    'is_featured' => ($index < 3),
                ])
            );
        }
    }
}
