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

    public function privacyPolicy(): View
    {
        return view('pages.privacy-policy');
    }

    public function termsOfService(): View
    {
        return view('pages.terms-of-service');
    }

    public function showCar(Car $car): View
    {
        if (! $car->is_available) {
            abort(404);
        }

        $car->loadMissing([
            'promos' => fn ($q) => $q->active(),
        ]);

        $relatedCars = Car::available()
            ->where('id', '!=', $car->id)
            ->ordered()
            ->take(3)
            ->get();

        $pendingTestDrive = session('pending_test_drive');
        $pendingServiceBooking = session('pending_service_booking');

        return view('cars.show', compact('car', 'relatedCars', 'pendingTestDrive', 'pendingServiceBooking'));
    }

    public function contact(): View
    {
        return view('contact.index');
    }
}
