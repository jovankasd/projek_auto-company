<?php

namespace App\Observers;

use App\Models\Car;
use Illuminate\Support\Facades\Cache;

class CarObserver
{
    public function saved(Car $car): void
    {
        Cache::forget('showroom_cars');
    }

    public function deleted(Car $car): void
    {
        Cache::forget('showroom_cars');
    }

    public function restored(Car $car): void
    {
        Cache::forget('showroom_cars');
    }
}
