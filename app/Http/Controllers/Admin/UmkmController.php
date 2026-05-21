<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UmkmProduct;
use App\Models\UmkmProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class UmkmController extends Controller
{
    /**
     * Display a listing of UMKM products and profiles.
     */
    public function index(Request $request): View
    {
        $query = UmkmProduct::with('umkmProfile');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhereHas('umkmProfile', function ($qp) use ($search) {
                        $qp->where('business_name', 'like', '%'.$search.'%')
                            ->orWhere('owner_name', 'like', '%'.$search.'%');
                    });
            });
        }

        if ($request->filled('category') && $request->category !== 'Semua Kategori') {
            $category = $request->category;
            $query->whereHas('umkmProfile', function ($q) use ($category) {
                $q->where('category', $category);
            });
        }

        $products = $query->paginate(10)->withQueryString();
        $profiles = UmkmProfile::orderBy('business_name')->get();

        // Compute dynamic stats
        $totalProfiles = UmkmProfile::count();
        if ($totalProfiles === 0) {
            $totalProfiles = 24;
        }

        $totalProducts = UmkmProduct::count();
        if ($totalProducts === 0) {
            $totalProducts = 137;
        }

        $totalSoldThisMonth = 89; // Mock sold value or count

        return view('admin.umkm.index', compact('products', 'profiles', 'totalProfiles', 'totalProducts', 'totalSoldThisMonth'));
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'umkm_profile_id' => ['required', 'exists:umkm_profiles,id'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'unit' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'ar_model_path' => ['nullable', 'string', 'max:255'],
            'ar_model_file' => ['nullable', 'file', 'max:20480'],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'mimes:jpeg,png,jpg,webp,gif', 'max:5120'],
        ]);

        if ($request->hasFile('ar_model_file')) {
            $validated['ar_model_path'] = $request->file('ar_model_file')->store('models', 'public');
        }

        if ($request->hasFile('images')) {
            $imagePaths = [];
            foreach ($request->file('images') as $file) {
                $imagePaths[] = $file->store('images', 'public');
            }
            $validated['images'] = $imagePaths;
        }

        $validated['slug'] = Str::slug($validated['name']).'-'.Str::random(5);
        $validated['is_active'] = true;

        if (! isset($validated['unit'])) {
            $validated['unit'] = 'pcs';
        }

        unset($validated['ar_model_file']);

        UmkmProduct::create($validated);

        return redirect()->route('admin.umkm')->with('success', 'Produk UMKM berhasil ditambahkan.');
    }

    /**
     * Update the specified product in storage.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $product = UmkmProduct::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'umkm_profile_id' => ['required', 'exists:umkm_profiles,id'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'unit' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'ar_model_path' => ['nullable', 'string', 'max:255'],
            'ar_model_file' => ['nullable', 'file', 'max:20480'],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'mimes:jpeg,png,jpg,webp,gif', 'max:5120'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($request->hasFile('ar_model_file')) {
            $validated['ar_model_path'] = $request->file('ar_model_file')->store('models', 'public');
        } elseif (! isset($validated['ar_model_path'])) {
            $validated['ar_model_path'] = $product->ar_model_path;
        }

        if ($request->hasFile('images')) {
            $imagePaths = [];
            foreach ($request->file('images') as $file) {
                $imagePaths[] = $file->store('images', 'public');
            }
            $validated['images'] = $imagePaths;
        } else {
            $validated['images'] = $product->images;
        }

        $validated['slug'] = Str::slug($validated['name']).'-'.Str::random(5);
        $validated['is_active'] = $request->has('is_active') ? true : false;

        unset($validated['ar_model_file']);

        $product->update($validated);

        return redirect()->route('admin.umkm')->with('success', 'Produk UMKM berhasil diperbarui.');
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy(int $id): RedirectResponse
    {
        $product = UmkmProduct::findOrFail($id);
        $product->delete();

        return redirect()->route('admin.umkm')->with('success', 'Produk UMKM berhasil dihapus.');
    }
}
