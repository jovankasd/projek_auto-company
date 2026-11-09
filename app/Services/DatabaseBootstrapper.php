<?php

namespace App\Services;

use Database\Seeders\UserSeeder;

class DatabaseBootstrapper
{
    /**
     * Pastikan akun demo lokal tersedia tanpa menimpa kredensial atau status akun yang sudah ada.
     *
     * Helper ini tidak dipanggil otomatis oleh HTTP middleware atau controller web.
     */
    public static function ensureDemoAccounts(): void
    {
        if (! app()->environment(['local', 'testing']) || in_array(config('app.env'), ['production', 'staging'], true)) {
            return;
        }

        (new UserSeeder)->run();
    }
}
