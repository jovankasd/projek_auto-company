<?php

namespace Database\Seeders;

use App\Enums\DiscountType;
use App\Models\Car;
use App\Models\Promo;
use Illuminate\Database\Seeder;

class PromoSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing']) || in_array(config('app.env'), ['production', 'staging'], true)) {
            return;
        }

        $mustang = Car::where('slug', 'ford-mustang-gt-2024')->first();
        $bmw = Car::where('slug', 'bmw-m4-competition-2024')->first();

        $promos = [
            [
                'code' => 'XYZSUPER2024',
                'title' => 'Promo Diskon Servis Berkala 20%',
                'description' => 'Dapatkan potongan 20% untuk seluruh paket servis berkala di bengkel resmi XYZ Motors.',
                'discount_type' => DiscountType::Percentage,
                'discount_value' => 20.00,
                'car_id' => null,
                'starts_at' => now()->subDays(5),
                'ends_at' => now()->addDays(25),
                'max_usage' => 100,
                'used_count' => 12,
                'is_active' => true,
            ],
            [
                'code' => 'M4LUXURY50JT',
                'title' => 'Potongan Khusus BMW M4 Competition',
                'description' => 'Cashback langsung Rp50.000.000 untuk setiap pemesanan unit BMW M4 Competition bulan ini.',
                'discount_type' => DiscountType::Fixed,
                'discount_value' => 50000000.00,
                'car_id' => $bmw?->id,
                'starts_at' => now()->subDays(2),
                'ends_at' => now()->addDays(40),
                'max_usage' => 5,
                'used_count' => 1,
                'is_active' => true,
            ],
        ];

        foreach ($promos as $promoData) {
            Promo::firstOrCreate(['code' => $promoData['code']], $promoData);
        }
    }
}
