<?php

namespace App\Http\Controllers;

use App\Enums\CarCategory;
use App\Models\Car;
use App\Services\CarSlugGenerator;
use App\Services\ImageOptimizer;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CarController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Car::class);

        $query = Car::query();

        // Include trashed if requested
        if ($request->boolean('trashed')) {
            $query->onlyTrashed();
        }

        // Search keyword (model, brand, color)
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('model_name', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%")
                    ->orWhere('color_name', 'like', "%{$search}%");
            });
        }

        // Filter brand
        if ($brand = $request->input('brand')) {
            $query->where('brand', $brand);
        }

        // Filter category
        if ($category = $request->input('category')) {
            $query->where('category', $category);
        }

        // Filter availability status
        if ($request->has('status') && $request->input('status') !== '') {
            $query->where('is_available', $request->boolean('status'));
        }

        $cars = $query->orderBy('sort_order')->paginate(15)->withQueryString();

        $brands = Car::distinct()->pluck('brand');
        $categories = CarCategory::cases();

        return view('dashboard.cars.index', compact('cars', 'brands', 'categories'));
    }

    public function create(): View
    {
        $this->authorize('create', Car::class);

        $categories = CarCategory::cases();

        return view('dashboard.cars.create', compact('categories'));
    }

    public function store(Request $request, CarSlugGenerator $slugGenerator, ImageOptimizer $imageOptimizer): RedirectResponse
    {
        $this->authorize('create', Car::class);

        $validated = $request->validate([
            'brand' => ['required', 'string', 'max:50'],
            'model_name' => ['required', 'string', 'max:100'],
            'year' => ['required', 'integer', 'min:2000', 'max:'.(date('Y') + 2)],
            'category' => ['required', Rule::enum(CarCategory::class)],
            'color_name' => ['required', 'string', 'max:50'],
            'color_hex' => ['required', 'string', 'regex:/^#[a-fA-F0-9]{6}$/'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'is_available' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
            'engine_specs' => ['nullable', 'array'],
            'engine_specs.power' => ['nullable', 'string', 'max:50'],
            'engine_specs.torque' => ['nullable', 'string', 'max:50'],
            'engine_specs.accel' => ['nullable', 'string', 'max:50'],
            'engine_specs.trans' => ['nullable', 'string', 'max:50'],
            'engine_specs.fuel_type' => ['nullable', 'string', 'max:50'],
            'dimensions' => ['nullable', 'array'],
            'features' => ['nullable', 'array'],
            'features.*' => ['nullable', 'string', 'max:100'],
            'hero_image' => ['nullable', 'file', 'mimes:png,webp,jpg,jpeg', 'max:2048'],
            'gallery_images.*' => ['nullable', 'file', 'mimes:png,webp,jpg,jpeg', 'max:2048'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
        ]);

        $slug = $slugGenerator->generate($validated['brand'], $validated['model_name'], (int) $validated['year']);

        $heroImagePath = null;
        if ($request->hasFile('hero_image')) {
            $heroImagePath = $imageOptimizer->optimizeAndStore(
                $request->file('hero_image'),
                'cars',
                $slug.'-hero'
            );
        }

        $galleryPaths = [];
        if ($request->hasFile('gallery_images')) {
            foreach ($request->file('gallery_images') as $idx => $galleryFile) {
                $galleryPaths[] = $imageOptimizer->optimizeAndStore(
                    $galleryFile,
                    'cars/gallery',
                    $slug.'-gallery-'.($idx + 1)
                );
            }
        }

        $features = array_values(array_filter($validated['features'] ?? []));

        $car = Car::create([
            'slug' => $slug,
            'brand' => $validated['brand'],
            'model_name' => $validated['model_name'],
            'year' => $validated['year'],
            'category' => $validated['category'],
            'color_name' => $validated['color_name'],
            'color_hex' => $validated['color_hex'],
            'price' => $validated['price'],
            'stock_quantity' => $validated['stock_quantity'],
            'is_available' => $request->boolean('is_available', true),
            'is_featured' => $request->boolean('is_featured', false),
            'sort_order' => $validated['sort_order'] ?? (Car::max('sort_order') + 1),
            'description' => $validated['description'] ?? null,
            'engine_specs' => $validated['engine_specs'] ?? null,
            'dimensions' => $validated['dimensions'] ?? null,
            'features' => $features,
            'hero_image_transparent' => $heroImagePath,
            'hero_image_alt' => "{$validated['brand']} {$validated['model_name']} {$validated['year']} {$validated['color_name']}",
            'gallery_images' => $galleryPaths,
            'meta_title' => $validated['meta_title'] ?? "{$validated['brand']} {$validated['model_name']} | XYZ Motors",
            'meta_description' => $validated['meta_description'] ?? "Unit {$validated['brand']} {$validated['model_name']} {$validated['year']} tersedia di XYZ Motors.",
        ]);

        return redirect()->route('cars.index')->with('success', "Mobil {$car->brand} {$car->model_name} berhasil ditambahkan.");
    }

    public function show(Car $car): View
    {
        $this->authorize('view', $car);

        return view('dashboard.cars.show', compact('car'));
    }

    public function edit(Car $car): View
    {
        $this->authorize('update', $car);

        $categories = CarCategory::cases();

        return view('dashboard.cars.edit', compact('car', 'categories'));
    }

    public function update(Request $request, Car $car, CarSlugGenerator $slugGenerator, ImageOptimizer $imageOptimizer): RedirectResponse
    {
        $this->authorize('update', $car);

        $validated = $request->validate([
            'brand' => ['required', 'string', 'max:50'],
            'model_name' => ['required', 'string', 'max:100'],
            'year' => ['required', 'integer', 'min:2000', 'max:'.(date('Y') + 2)],
            'category' => ['required', Rule::enum(CarCategory::class)],
            'color_name' => ['required', 'string', 'max:50'],
            'color_hex' => ['required', 'string', 'regex:/^#[a-fA-F0-9]{6}$/'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'is_available' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
            'engine_specs' => ['nullable', 'array'],
            'engine_specs.power' => ['nullable', 'string', 'max:50'],
            'engine_specs.torque' => ['nullable', 'string', 'max:50'],
            'engine_specs.accel' => ['nullable', 'string', 'max:50'],
            'engine_specs.trans' => ['nullable', 'string', 'max:50'],
            'engine_specs.fuel_type' => ['nullable', 'string', 'max:50'],
            'dimensions' => ['nullable', 'array'],
            'features' => ['nullable', 'array'],
            'features.*' => ['nullable', 'string', 'max:100'],
            'hero_image' => ['nullable', 'file', 'mimes:png,webp,jpg,jpeg', 'max:2048'],
            'gallery_images.*' => ['nullable', 'file', 'mimes:png,webp,jpg,jpeg', 'max:2048'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
        ]);

        if ($car->brand !== $validated['brand'] || $car->model_name !== $validated['model_name'] || (int) $car->year !== (int) $validated['year']) {
            $car->slug = $slugGenerator->generateForUpdate($validated['brand'], $validated['model_name'], (int) $validated['year'], $car->id);
        }

        if ($request->hasFile('hero_image')) {
            $car->hero_image_transparent = $imageOptimizer->optimizeAndStore(
                $request->file('hero_image'),
                'cars',
                $car->slug.'-hero-'.time()
            );
        }

        if ($request->hasFile('gallery_images')) {
            $currentGallery = $car->gallery_images ?? [];
            foreach ($request->file('gallery_images') as $idx => $galleryFile) {
                $currentGallery[] = $imageOptimizer->optimizeAndStore(
                    $galleryFile,
                    'cars/gallery',
                    $car->slug.'-gallery-'.time().'-'.($idx + 1)
                );
            }
            $car->gallery_images = array_slice($currentGallery, 0, 10);
        }

        $features = array_values(array_filter($validated['features'] ?? []));

        $car->update([
            'brand' => $validated['brand'],
            'model_name' => $validated['model_name'],
            'year' => $validated['year'],
            'category' => $validated['category'],
            'color_name' => $validated['color_name'],
            'color_hex' => $validated['color_hex'],
            'price' => $validated['price'],
            'stock_quantity' => $validated['stock_quantity'],
            'is_available' => $request->boolean('is_available', true),
            'is_featured' => $request->boolean('is_featured', false),
            'sort_order' => $validated['sort_order'] ?? $car->sort_order,
            'description' => $validated['description'] ?? null,
            'engine_specs' => $validated['engine_specs'] ?? $car->engine_specs,
            'dimensions' => $validated['dimensions'] ?? $car->dimensions,
            'features' => $features,
            'hero_image_alt' => "{$validated['brand']} {$validated['model_name']} {$validated['year']} {$validated['color_name']}",
            'meta_title' => $validated['meta_title'] ?? $car->meta_title,
            'meta_description' => $validated['meta_description'] ?? $car->meta_description,
        ]);

        return redirect()->route('cars.index')->with('success', "Data mobil {$car->brand} {$car->model_name} berhasil diperbarui.");
    }

    public function destroy(Car $car): RedirectResponse
    {
        $this->authorize('delete', $car);

        $name = "{$car->brand} {$car->model_name}";
        $car->delete();

        return redirect()->route('cars.index')->with('success', "Mobil {$name} berhasil dihapus (soft delete).");
    }

    public function restore(string $slug): RedirectResponse
    {
        $car = Car::onlyTrashed()->where('slug', $slug)->firstOrFail();
        $this->authorize('restore', $car);

        $car->restore();

        return redirect()->route('cars.index')->with('success', "Mobil {$car->brand} {$car->model_name} berhasil dipulihkan.");
    }

    public function reorder(Request $request): JsonResponse
    {
        $this->authorize('update', Car::class);

        $validated = $request->validate([
            'order' => ['required', 'array'],
            'order.*.id' => ['required', 'exists:cars,id'],
            'order.*.sort_order' => ['required', 'integer'],
        ]);

        foreach ($validated['order'] as $item) {
            Car::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json(['success' => true, 'message' => 'Urutan mobil berhasil disimpan.']);
    }
}
