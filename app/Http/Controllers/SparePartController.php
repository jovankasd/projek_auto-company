<?php

namespace App\Http\Controllers;

use App\Enums\SparePartCategory;
use App\Models\SparePart;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SparePartController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): View
    {
        $this->authorize('viewAny', SparePart::class);

        $query = SparePart::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('part_number', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%");
            });
        }

        if ($category = $request->input('category')) {
            $query->where('category', $category);
        }

        if ($brand = $request->input('brand')) {
            $query->where('brand', $brand);
        }

        if ($request->has('status') && $request->input('status') !== '') {
            $query->where('is_available', $request->boolean('status'));
        }

        $spareParts = $query->orderBy('sort_order')->paginate(15)->withQueryString();

        $brands = SparePart::distinct()->pluck('brand');
        $categories = SparePartCategory::cases();

        return view('dashboard.spare-parts.index', compact('spareParts', 'brands', 'categories'));
    }

    public function create(): View
    {
        $this->authorize('create', SparePart::class);

        $categories = SparePartCategory::cases();

        return view('dashboard.spare-parts.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', SparePart::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'part_number' => ['required', 'string', 'max:100'],
            'brand' => ['required', 'string', 'max:100'],
            'category' => ['required', Rule::enum(SparePartCategory::class)],
            'car_brand_compatibility' => ['nullable', 'array'],
            'car_model_compatibility' => ['nullable', 'array'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'min_stock_alert' => ['required', 'integer', 'min:0'],
            'weight_kg' => ['nullable', 'numeric', 'min:0'],
            'dimensions_cm' => ['nullable', 'array'],
            'is_available' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
        ]);

        $slug = Str::slug($validated['name'].'-'.$validated['part_number']);

        // Ensure unique slug
        $originalSlug = $slug;
        $counter = 1;
        while (SparePart::where('slug', $slug)->exists()) {
            $slug = $originalSlug.'-'.$counter;
            $counter++;
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->storeAs('spare-parts', $slug.'.'.$request->file('image')->extension(), 'public');
        }

        $sparePart = SparePart::create([
            'slug' => $slug,
            'name' => $validated['name'],
            'part_number' => $validated['part_number'],
            'brand' => $validated['brand'],
            'category' => $validated['category'],
            'car_brand_compatibility' => array_values(array_filter($validated['car_brand_compatibility'] ?? [])),
            'car_model_compatibility' => array_values(array_filter($validated['car_model_compatibility'] ?? [])),
            'description' => $validated['description'] ?? null,
            'image_path' => $imagePath,
            'price' => $validated['price'],
            'stock_quantity' => $validated['stock_quantity'],
            'min_stock_alert' => $validated['min_stock_alert'] ?? 5,
            'weight_kg' => $validated['weight_kg'] ?? null,
            'dimensions_cm' => $validated['dimensions_cm'] ?? null,
            'is_available' => $request->boolean('is_available', true),
            'is_featured' => $request->boolean('is_featured', false),
            'sort_order' => $validated['sort_order'] ?? (SparePart::max('sort_order') + 1),
            'meta_title' => $validated['meta_title'] ?? "{$validated['name']} | XYZ Motors",
            'meta_description' => $validated['meta_description'] ?? "Spare part {$validated['name']} tersedia di XYZ Motors.",
        ]);

        return redirect()->route('spare-parts.index')->with('success', "Spare part {$sparePart->name} berhasil ditambahkan.");
    }

    public function show(SparePart $sparePart): View
    {
        $this->authorize('view', $sparePart);

        return view('dashboard.spare-parts.show', compact('sparePart'));
    }

    public function edit(SparePart $sparePart): View
    {
        $this->authorize('update', $sparePart);

        $categories = SparePartCategory::cases();

        return view('dashboard.spare-parts.edit', compact('sparePart', 'categories'));
    }

    public function update(Request $request, SparePart $sparePart): RedirectResponse
    {
        $this->authorize('update', $sparePart);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'part_number' => ['required', 'string', 'max:100'],
            'brand' => ['required', 'string', 'max:100'],
            'category' => ['required', Rule::enum(SparePartCategory::class)],
            'car_brand_compatibility' => ['nullable', 'array'],
            'car_model_compatibility' => ['nullable', 'array'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'min_stock_alert' => ['required', 'integer', 'min:0'],
            'weight_kg' => ['nullable', 'numeric', 'min:0'],
            'dimensions_cm' => ['nullable', 'array'],
            'is_available' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
        ]);

        $slug = $sparePart->slug;
        if ($sparePart->name !== $validated['name'] || $sparePart->part_number !== $validated['part_number']) {
            $slug = Str::slug($validated['name'].'-'.$validated['part_number']);
            $originalSlug = $slug;
            $counter = 1;
            while (SparePart::where('slug', $slug)->where('id', '!=', $sparePart->id)->exists()) {
                $slug = $originalSlug.'-'.$counter;
                $counter++;
            }
        }

        $imagePath = $sparePart->image_path;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->storeAs('spare-parts', $slug.'-'.time().'.'.$request->file('image')->extension(), 'public');
        }

        $sparePart->update([
            'slug' => $slug,
            'name' => $validated['name'],
            'part_number' => $validated['part_number'],
            'brand' => $validated['brand'],
            'category' => $validated['category'],
            'car_brand_compatibility' => array_values(array_filter($validated['car_brand_compatibility'] ?? [])),
            'car_model_compatibility' => array_values(array_filter($validated['car_model_compatibility'] ?? [])),
            'description' => $validated['description'] ?? null,
            'image_path' => $imagePath,
            'price' => $validated['price'],
            'stock_quantity' => $validated['stock_quantity'],
            'min_stock_alert' => $validated['min_stock_alert'] ?? 5,
            'weight_kg' => $validated['weight_kg'] ?? null,
            'dimensions_cm' => $validated['dimensions_cm'] ?? null,
            'is_available' => $request->boolean('is_available', true),
            'is_featured' => $request->boolean('is_featured', false),
            'sort_order' => $validated['sort_order'] ?? $sparePart->sort_order,
            'meta_title' => $validated['meta_title'] ?? $sparePart->meta_title,
            'meta_description' => $validated['meta_description'] ?? $sparePart->meta_description,
        ]);

        return redirect()->route('spare-parts.index')->with('success', "Data spare part {$sparePart->name} berhasil diperbarui.");
    }

    public function destroy(SparePart $sparePart): RedirectResponse
    {
        $this->authorize('delete', $sparePart);

        $name = $sparePart->name;
        $sparePart->delete();

        return redirect()->route('spare-parts.index')->with('success', "Spare part {$name} berhasil dihapus.");
    }

    public function restore(string $slug): RedirectResponse
    {
        $sparePart = SparePart::onlyTrashed()->where('slug', $slug)->firstOrFail();
        $this->authorize('restore', $sparePart);

        $sparePart->restore();

        return redirect()->route('spare-parts.index')->with('success', "Spare part {$sparePart->name} berhasil dipulihkan.");
    }
}
