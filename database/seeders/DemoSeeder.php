<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing']) || in_array(config('app.env'), ['production', 'staging'], true)) {
            if (isset($this->command)) {
                $this->command->warn('DemoSeeder dilewati di luar environment local/testing.');
            }

            return;
        }

        $this->call([
            SettingSeeder::class,
            UserSeeder::class,
            CarSeeder::class,
            SparePartSeeder::class,
            PromoSeeder::class,
            ServiceSeeder::class,
            TestDriveSeeder::class,
        ]);
    }
}
