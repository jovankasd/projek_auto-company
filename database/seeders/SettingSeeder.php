<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['group' => 'booking', 'key' => 'booking.open_hour',   'value' => '8',                    'type' => 'integer'],
            ['group' => 'booking', 'key' => 'booking.close_hour',  'value' => '17',                   'type' => 'integer'],
            ['group' => 'booking', 'key' => 'booking.max_per_slot', 'value' => '3',                    'type' => 'integer'],
            ['group' => 'contact', 'key' => 'contact.whatsapp',    'value' => '6281234567890',        'type' => 'string'],
            ['group' => 'contact', 'key' => 'contact.email',       'value' => 'info@xyzmotors.id',    'type' => 'string'],
            ['group' => 'contact', 'key' => 'contact.address',     'value' => 'Jl. Sudirman No.1, Jakarta', 'type' => 'string'],
            ['group' => 'seo',    'key' => 'seo.site_name',        'value' => 'XYZ Motors',           'type' => 'string'],
            ['group' => 'seo',    'key' => 'seo.tagline',          'value' => 'Drive Your Dream',     'type' => 'string'],
        ];

        foreach ($settings as $setting) {
            Setting::firstOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
