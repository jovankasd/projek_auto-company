<?php

namespace App\Http\Controllers;

use App\Enums\DiscountType;
use App\Models\Car;
use App\Models\Promo;
use App\Services\ImageOptimizer;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PromoController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Promo::class);

        $query = Promo::with('car');

        if ($request->boolean('trashed')) {
            $query->onlyTrashed();
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%");
            });
        }

        if ($request->has('active') && $request->input('active') !== '') {
            $query->where('is_active', $request->boolean('active'));
        }

        $promos = $query->latest('starts_at')->paginate(15)->withQueryString();

        return view('dashboard.promos.index', compact('promos'));
    }

    public function create(): View
    {
        $this->authorize('create', Promo::class);

        $cars = Car::orderBy('brand')->get();
        $discountTypes = DiscountType::cases();

        return view('dashboard.promos.create', compact('cars', 'discountTypes'));
    }

    public function store(Request $request, ImageOptimizer $imageOptimizer): RedirectResponse
    {
        $this->authorize('create', Promo::class);

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'alpha_num', 'unique:promos,code'],
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'discount_type' => ['required', Rule::enum(DiscountType::class)],
            'discount_value' => ['required', 'numeric', 'min:0'],
            'car_id' => ['nullable', 'exists:cars,id'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'max_usage' => ['nullable', 'integer', 'min:1'],
            'banner_image' => ['nullable', 'file', 'mimes:png,webp,jpg,jpeg', 'max:2048'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'code.unique' => 'Kode promo ini sudah terdaftar.',
            'ends_at.after' => 'Tanggal berakhir promo harus setelah tanggal mulai promo.',
        ]);

        $bannerPath = null;
        if ($request->hasFile('banner_image')) {
            $bannerPath = $imageOptimizer->optimizeAndStore(
                $request->file('banner_image'),
                'promos',
                Str::slug($validated['code']).'-banner'
            );
        }

        $promo = Promo::create([
            'code' => strtoupper($validated['code']),
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'discount_type' => $validated['discount_type'],
            'discount_value' => $validated['discount_value'],
            'car_id' => $validated['car_id'] ?? null,
            'starts_at' => $validated['starts_at'],
            'ends_at' => $validated['ends_at'],
            'max_usage' => $validated['max_usage'] ?? null,
            'is_active' => $request->boolean('is_active', true),
            'banner_image' => $bannerPath,
        ]);

        return redirect()->route('promos.index')->with('success', "Promo {$promo->code} berhasil dibuat.");
    }

    public function edit(Promo $promo): View
    {
        $this->authorize('update', $promo);

        $cars = Car::orderBy('brand')->get();
        $discountTypes = DiscountType::cases();

        return view('dashboard.promos.edit', compact('promo', 'cars', 'discountTypes'));
    }

    public function update(Request $request, Promo $promo, ImageOptimizer $imageOptimizer): RedirectResponse
    {
        $this->authorize('update', $promo);

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'alpha_num', Rule::unique('promos', 'code')->ignore($promo->id)],
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'discount_type' => ['required', Rule::enum(DiscountType::class)],
            'discount_value' => ['required', 'numeric', 'min:0'],
            'car_id' => ['nullable', 'exists:cars,id'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'max_usage' => ['nullable', 'integer', 'min:1'],
            'banner_image' => ['nullable', 'file', 'mimes:png,webp,jpg,jpeg', 'max:2048'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'code.unique' => 'Kode promo ini sudah terdaftar.',
            'ends_at.after' => 'Tanggal berakhir promo harus setelah tanggal mulai promo.',
        ]);

        $bannerPath = $promo->banner_image;
        if ($request->hasFile('banner_image')) {
            $bannerPath = $imageOptimizer->optimizeAndStore(
                $request->file('banner_image'),
                'promos',
                Str::slug($validated['code']).'-banner-'.time()
            );
        }

        $promo->update([
            'code' => strtoupper($validated['code']),
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'discount_type' => $validated['discount_type'],
            'discount_value' => $validated['discount_value'],
            'car_id' => $validated['car_id'] ?? null,
            'starts_at' => $validated['starts_at'],
            'ends_at' => $validated['ends_at'],
            'max_usage' => $validated['max_usage'] ?? null,
            'is_active' => $request->boolean('is_active', true),
            'banner_image' => $bannerPath,
        ]);

        return redirect()->route('promos.index')->with('success', "Promo {$promo->code} berhasil diperbarui.");
    }

    public function destroy(Promo $promo): RedirectResponse
    {
        $this->authorize('delete', $promo);

        $code = $promo->code;
        $promo->delete();

        return redirect()->route('promos.index')->with('success', "Promo {$code} berhasil dihapus.");
    }
}
