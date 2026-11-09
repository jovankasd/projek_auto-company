<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Promo;
use App\Models\SparePart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(Request $request): View
    {
        $cars = Cache::remember('showroom_cars', 300, function () {
            return Car::available()
                ->orderBy('sort_order')
                ->with('promos')
                ->get();
        });

        $spareParts = SparePart::available()
            ->ordered()
            ->get();

        $promos = Promo::active()
            ->with('car')
            ->latest('starts_at')
            ->get();

        $pendingTestDrive = session('pending_test_drive');
        $pendingServiceBooking = session('pending_service_booking');

        return view('showroom.index', compact(
            'cars',
            'spareParts',
            'promos',
            'pendingTestDrive',
            'pendingServiceBooking'
        ));
    }
}
