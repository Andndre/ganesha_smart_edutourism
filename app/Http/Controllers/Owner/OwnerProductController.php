<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\UmkmProduct;
use App\Models\UmkmProductCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OwnerProductController extends Controller
{
    /**
     * Display a listing of the owner's products.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $profile = $user->umkmProfile;

        if (! $profile) {
            return view('owner.products', [
                'products' => collect(),
                'categories' => collect(),
                'noProfile' => true,
            ]);
        }

        $query = UmkmProduct::where('umkm_profile_id', $profile->id)->with('category');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name->en', 'like', '%'.$search.'%')
                    ->orWhere('name->id', 'like', '%'.$search.'%');
            });
        }

        if ($request->filled('category') && $request->category !== 'Semua Kategori') {
            $categoryName = $request->category;
            $query->whereHas('category', function ($q) use ($categoryName) {
                $q->where('name', $categoryName);
            });
        }

        $products = $query->paginate(10)->withQueryString();
        $categories = UmkmProductCategory::orderBy('name->'.app()->getLocale())->get();

        return view('owner.products', compact('products', 'categories') + ['noProfile' => false]);
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $profile = $user->umkmProfile;

        if (! $profile) {
            return redirect()->route('owner.products')->with('error', __('Silakan buat profil toko terlebih dahulu.'));
        }

        $validated = $request->validate([
            'name' => ['required', 'array'],
            'name.en' => ['required', 'string', 'max:255'],
            'name.id' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'unit' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'array'],
            'description.en' => ['nullable', 'string'],
            'description.id' => ['nullable', 'string'],
            'umkm_product_category_id' => ['nullable', 'exists:umkm_product_categories,id'],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'mimes:jpeg,png,jpg,webp,gif', 'max:5120'],
        ]);

        if ($request->hasFile('images')) {
            $imagePaths = [];
            foreach ($request->file('images') as $file) {
                $imagePaths[] = $file->store('images', 'public');
            }
            $validated['images'] = $imagePaths;
        }

        $validated['umkm_profile_id'] = $profile->id;
        $defaultLocale = config('app.fallback_locale', 'en');
        $slugValue = $validated['name'][$defaultLocale] ?? $validated['name']['en'] ?? reset($validated['name']);
        $validated['slug'] = (new UmkmProduct)->generateUniqueSlug($slugValue);
        $validated['is_active'] = true;

        if (! isset($validated['unit'])) {
            $validated['unit'] = 'pcs';
        }

        UmkmProduct::create($validated);

        return redirect()->route('owner.products')->with('success', __('Produk berhasil ditambahkan.'));
    }

    /**
     * Update the specified product in storage.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $user = Auth::user();
        $profile = $user->umkmProfile;

        if (! $profile) {
            return redirect()->route('owner.products')->with('error', __('Silakan buat profil toko terlebih dahulu.'));
        }

        $product = UmkmProduct::where('umkm_profile_id', $profile->id)->findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'array'],
            'name.en' => ['required', 'string', 'max:255'],
            'name.id' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'unit' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'array'],
            'description.en' => ['nullable', 'string'],
            'description.id' => ['nullable', 'string'],
            'umkm_product_category_id' => ['nullable', 'exists:umkm_product_categories,id'],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'mimes:jpeg,png,jpg,webp,gif', 'max:5120'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($request->hasFile('images')) {
            $imagePaths = [];
            foreach ($request->file('images') as $file) {
                $imagePaths[] = $file->store('images', 'public');
            }
            $validated['images'] = $imagePaths;
        } else {
            $validated['images'] = $product->images;
        }

        $defaultLocale = config('app.fallback_locale', 'en');
        $slugValue = $validated['name'][$defaultLocale] ?? $validated['name']['en'] ?? reset($validated['name']);
        $validated['slug'] = $product->generateUniqueSlug($slugValue);
        $validated['is_active'] = $request->has('is_active') ? true : false;

        $product->update($validated);

        return redirect()->route('owner.products')->with('success', __('Produk berhasil diperbarui.'));
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy(int $id): RedirectResponse
    {
        $user = Auth::user();
        $profile = $user->umkmProfile;

        if (! $profile) {
            return redirect()->route('owner.products')->with('error', __('Silakan buat profil toko terlebih dahulu.'));
        }

        $product = UmkmProduct::where('umkm_profile_id', $profile->id)->findOrFail($id);
        $product->delete();

        return redirect()->route('owner.products')->with('success', __('Produk berhasil dihapus.'));
    }
}
