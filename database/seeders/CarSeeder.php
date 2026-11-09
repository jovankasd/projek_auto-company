<?php

namespace Database\Seeders;

use App\Enums\CarCategory;
use App\Models\Car;
use Illuminate\Database\Seeder;

class CarSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing']) || in_array(config('app.env'), ['production', 'staging'], true)) {
            return;
        }

        $cars = [
            [
                'slug' => 'ford-mustang-gt-2024',
                'brand' => 'Ford',
                'model_name' => 'Mustang GT',
                'year' => 2024,
                'category' => CarCategory::Sports,
                'color_name' => 'Race Red',
                'color_hex' => '#C41E3A',
                'price' => 980000000,
                'engine_specs' => [
                    'power' => '450 HP',
                    'torque' => '529 Nm',
                    'accel' => '4.3s',
                    'trans' => '8AT',
                ],
                'features' => ['Apple CarPlay', 'Android Auto', 'Bang & Olufsen Audio', 'Brembo Brakes', 'Lane Keeping'],
                'description' => 'Ikon otomotif Amerika dengan mesin V8 5.0L Coyote yang menggelegar. Perpaduan sempurna antara performa, desain agresif, dan teknologi modern.',
                'hero_image_transparent' => null,
                'hero_image_alt' => 'Ford Mustang GT 2024 Race Red tampak samping',
                'meta_title' => 'Ford Mustang GT 2024 | XYZ Motors',
                'meta_description' => 'Beli Ford Mustang GT 2024 di XYZ Motors. V8 450HP, 0-100 4.3 detik. Test drive sekarang.',
                'stock_quantity' => 3,
                'is_available' => true,
                'is_featured' => true,
                'sort_order' => 1,
            ],
            [
                'slug' => 'bmw-m4-competition-2024',
                'brand' => 'BMW',
                'model_name' => 'M4 Competition',
                'year' => 2024,
                'category' => CarCategory::Sports,
                'color_name' => 'Brooklyn Grey',
                'color_hex' => '#6E6E6E',
                'price' => 2100000000,
                'engine_specs' => [
                    'power' => '510 HP',
                    'torque' => '650 Nm',
                    'accel' => '3.9s',
                    'trans' => '8AT M-DCT',
                ],
                'features' => ['M xDrive AWD', 'Active M Differential', 'M Carbon Ceramic Brakes', 'Harman Kardon', 'Head-Up Display'],
                'description' => 'Puncak rekayasa M GmbH. Twin-turbo inline-6 S58 dengan teknologi M xDrive untuk grip sempurna di segala kondisi.',
                'hero_image_transparent' => null,
                'hero_image_alt' => 'BMW M4 Competition 2024 Brooklyn Grey tampak samping',
                'meta_title' => 'BMW M4 Competition 2024 | XYZ Motors',
                'meta_description' => 'Beli BMW M4 Competition 2024 di XYZ Motors. 510HP, xDrive AWD. Test drive sekarang.',
                'stock_quantity' => 2,
                'is_available' => true,
                'is_featured' => true,
                'sort_order' => 2,
            ],
            [
                'slug' => 'toyota-alphard-2024',
                'brand' => 'Toyota',
                'model_name' => 'Alphard',
                'year' => 2024,
                'category' => CarCategory::MPV,
                'color_name' => 'Precious Black',
                'color_hex' => '#1A1A1A',
                'price' => 1350000000,
                'engine_specs' => [
                    'power' => '250 HP',
                    'torque' => '308 Nm',
                    'accel' => '8.4s',
                    'trans' => 'CVT',
                ],
                'features' => ['Executive Lounge Seat', 'Power Sliding Door', 'JBL Audio 13 Speaker', 'Panoramic Roof', 'Toyota Safety Sense'],
                'description' => 'Kemewahan tanpa kompromi dalam balutan MPV premium. Kabin luas dengan executive lounge seat dan teknologi keselamatan terdepan.',
                'hero_image_transparent' => null,
                'hero_image_alt' => 'Toyota Alphard 2024 Precious Black tampak samping',
                'meta_title' => 'Toyota Alphard 2024 | XYZ Motors',
                'meta_description' => 'Beli Toyota Alphard 2024 di XYZ Motors. MPV premium dengan executive lounge. Test drive sekarang.',
                'stock_quantity' => 4,
                'is_available' => true,
                'is_featured' => false,
                'sort_order' => 3,
            ],
            [
                'slug' => 'tesla-model-3-2024',
                'brand' => 'Tesla',
                'model_name' => 'Model 3',
                'year' => 2024,
                'category' => CarCategory::EV,
                'color_name' => 'Pearl White',
                'color_hex' => '#F5F5F5',
                'price' => 820000000,
                'engine_specs' => [
                    'power' => '358 HP',
                    'torque' => '430 Nm',
                    'accel' => '4.4s',
                    'trans' => '1-Speed Direct',
                ],
                'features' => ['Autopilot', '15.4" Touchscreen', 'OTA Updates', 'Glass Roof', 'Supercharger Access'],
                'description' => 'Revolusi kendaraan listrik. Autopilot canggih, update OTA, dan akses ke jaringan Supercharger terluas di dunia.',
                'hero_image_transparent' => null,
                'hero_image_alt' => 'Tesla Model 3 2024 Pearl White tampak samping',
                'meta_title' => 'Tesla Model 3 2024 | XYZ Motors',
                'meta_description' => 'Beli Tesla Model 3 2024 di XYZ Motors. EV 358HP, Autopilot. Test drive sekarang.',
                'stock_quantity' => 5,
                'is_available' => true,
                'is_featured' => true,
                'sort_order' => 4,
            ],
            [
                'slug' => 'ford-ranger-raptor-2024',
                'brand' => 'Ford',
                'model_name' => 'Ranger Raptor',
                'year' => 2024,
                'category' => CarCategory::SUV,
                'color_name' => 'Meteor Grey',
                'color_hex' => '#7A8591',
                'price' => 950000000,
                'engine_specs' => [
                    'power' => '292 HP',
                    'torque' => '583 Nm',
                    'accel' => '7.1s',
                    'trans' => '10AT',
                ],
                'features' => ['Fox Racing Shocks', 'Terrain Management System', 'Trail Control', 'B&O Sound', 'Matrix LED'],
                'description' => 'Raja off-road dengan suspensi Fox Racing dan ban BF Goodrich. Siap menaklukkan segala medan dengan gaya.',
                'hero_image_transparent' => null,
                'hero_image_alt' => 'Ford Ranger Raptor 2024 Meteor Grey tampak samping',
                'meta_title' => 'Ford Ranger Raptor 2024 | XYZ Motors',
                'meta_description' => 'Beli Ford Ranger Raptor 2024 di XYZ Motors. Off-road SUV 292HP, Fox Racing Shocks. Test drive sekarang.',
                'stock_quantity' => 3,
                'is_available' => true,
                'is_featured' => true,
                'sort_order' => 5,
            ],
        ];

        foreach ($cars as $carData) {
            Car::firstOrCreate(['slug' => $carData['slug']], $carData);
        }
    }
}
