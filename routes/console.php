<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Pengingat Servis Harian Pukul 08:00 WIB
Schedule::command('services:remind-upcoming')->dailyAt('08:00');

// Pengingat Test Drive Harian Pukul 08:30 WIB
Schedule::command('test-drives:remind-upcoming')->dailyAt('08:30');

// Nonaktifkan Promo Kedaluwarsa Setiap Jam
Schedule::command('promos:deactivate-expired')->hourly();
