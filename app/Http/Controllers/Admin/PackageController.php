<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PackageRequest;
use App\Models\TourPackage;
use Illuminate\Http\RedirectResponse;
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
    public function store(PackageRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $package = new TourPackage;
        $package->name = $validated['name'];
        $package->slug = $package->generateUniqueSlug(slugFromTranslatable($validated['name']));
        $package->description = $validated['description'];
        $package->price = $validated['price'];
        $package->duration_hours = $validated['duration_hours'] ?? null;
        $package->max_capacity = $validated['max_capacity'] ?? null;
        $package->setAttribute('inclusions', self::parseLocaleTextarea('inclusions', $validated));
        $package->setAttribute('exclusions', self::parseLocaleTextarea('exclusions', $validated));
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
    public function update(PackageRequest $request, int $id): RedirectResponse
    {
        $package = TourPackage::findOrFail($id);

        $validated = $request->validated();

        $package->name = $validated['name'];
        $package->slug = $package->generateUniqueSlug(slugFromTranslatable($validated['name']));
        $package->description = $validated['description'];
        $package->price = $validated['price'];
        $package->duration_hours = $validated['duration_hours'] ?? null;
        $package->max_capacity = $validated['max_capacity'] ?? null;
        $package->setAttribute('inclusions', self::parseLocaleTextarea('inclusions', $validated));
        $package->setAttribute('exclusions', self::parseLocaleTextarea('exclusions', $validated));
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
    private static function parseLocaleTextarea(string $field, array $data): array
    {
        $result = [];

        foreach (['en', 'id'] as $locale) {
            $value = $data[$field][$locale] ?? null;

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
