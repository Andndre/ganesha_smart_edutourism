<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TourPackage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PackageController extends Controller
{
    /**
     * Display a listing of tour packages.
     */
    public function index(): View
    {
        $packages = TourPackage::withCount(['reservations as sold_count' => function ($q) {
            $q->whereIn('status', ['confirmed', 'completed']);
        }])->get();

        return view('admin.packages.index', compact('packages'));
    }

    /**
     * Show the form for creating a new tour package.
     */
    public function create(): View
    {
        return view('admin.packages.create');
    }

    /**
     * Store a newly created tour package in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'array'],
            'name.en' => ['required', 'string', 'max:255'],
            'name.id' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'array'],
            'description.en' => ['nullable', 'string'],
            'description.id' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'duration_hours' => ['nullable', 'numeric', 'min:0'],
            'max_capacity' => ['nullable', 'integer', 'min:1'],
            'inclusions' => ['nullable', 'array'],
            'inclusions.en' => ['nullable', 'string'],
            'inclusions.id' => ['nullable', 'string'],
            'exclusions' => ['nullable', 'array'],
            'exclusions.en' => ['nullable', 'string'],
            'exclusions.id' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'mimes:jpeg,png,jpg,webp,gif', 'max:5120'],
        ]);

        $package = new TourPackage;
        $package->name = $validated['name'];
        $defaultLocale = config('app.fallback_locale', 'en');
        $slugValue = $validated['name'][$defaultLocale] ?? $validated['name']['en'] ?? reset($validated['name']);
        $package->slug = Str::slug($slugValue).'-'.Str::random(5);
        $package->description = $validated['description'];
        $package->price = $validated['price'];
        $package->duration_hours = $validated['duration_hours'] ?? null;
        $package->max_capacity = $validated['max_capacity'] ?? null;
        $package->inclusions = $this->parseLocaleTextarea('inclusions', $request);
        $package->exclusions = $this->parseLocaleTextarea('exclusions', $request);
        $package->is_active = $request->has('is_active') ? true : false;

        if ($request->hasFile('images')) {
            $imagePaths = [];
            foreach ($request->file('images') as $file) {
                $imagePaths[] = $file->store('images', 'public');
            }
            $package->images = $imagePaths;
        } else {
            $package->images = [];
        }

        $package->save();

        return redirect()->route('admin.packages')->with('success', __('Paket wisata berhasil ditambahkan.'));
    }

    /**
     * Show the form for editing the specified tour package.
     */
    public function edit(int $id): View
    {
        $package = TourPackage::findOrFail($id);

        return view('admin.packages.create', compact('package')); // Re-use create view for editing
    }

    /**
     * Update the specified tour package in storage.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $package = TourPackage::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'array'],
            'name.en' => ['required', 'string', 'max:255'],
            'name.id' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'array'],
            'description.en' => ['nullable', 'string'],
            'description.id' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'duration_hours' => ['nullable', 'numeric', 'min:0'],
            'max_capacity' => ['nullable', 'integer', 'min:1'],
            'inclusions' => ['nullable', 'array'],
            'inclusions.en' => ['nullable', 'string'],
            'inclusions.id' => ['nullable', 'string'],
            'exclusions' => ['nullable', 'array'],
            'exclusions.en' => ['nullable', 'string'],
            'exclusions.id' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'mimes:jpeg,png,jpg,webp,gif', 'max:5120'],
        ]);

        $package->name = $validated['name'];
        $defaultLocale = config('app.fallback_locale', 'en');
        $slugValue = $validated['name'][$defaultLocale] ?? $validated['name']['en'] ?? reset($validated['name']);
        $package->slug = Str::slug($slugValue).'-'.Str::random(5);
        $package->description = $validated['description'];
        $package->price = $validated['price'];
        $package->duration_hours = $validated['duration_hours'] ?? null;
        $package->max_capacity = $validated['max_capacity'] ?? null;
        $package->inclusions = $this->parseLocaleTextarea('inclusions', $request);
        $package->exclusions = $this->parseLocaleTextarea('exclusions', $request);
        $package->is_active = $request->has('is_active') ? true : false;

        if ($request->hasFile('images')) {
            $imagePaths = [];
            foreach ($request->file('images') as $file) {
                $imagePaths[] = $file->store('images', 'public');
            }
            $package->images = $imagePaths;
        }

        $package->save();

        return redirect()->route('admin.packages')->with('success', __('Paket wisata berhasil diperbarui.'));
    }

    /**
     * Remove the specified tour package from storage.
     */
    public function destroy(int $id): RedirectResponse
    {
        $package = TourPackage::findOrFail($id);
        $package->delete();

        return redirect()->route('admin.packages')->with('success', __('Paket wisata berhasil dihapus.'));
    }

    /**
     * Parse a per-locale textarea field into a per-locale array.
     */
    private function parseLocaleTextarea(string $field, Request $request): array
    {
        $result = [];

        foreach (['en', 'id'] as $locale) {
            $value = $request->input("{$field}.{$locale}");

            if (is_string($value) && trim($value) !== '') {
                $items = array_values(
                    array_filter(array_map('trim', explode("\n", $value)))
                );

                if (! empty($items)) {
                    $result[$locale] = $items;
                }
            }
        }

        return $result;
    }
}
