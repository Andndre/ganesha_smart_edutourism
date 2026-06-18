<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ArModel;
use App\Models\UmkmProduct;
use App\Models\UmkmProductCategory;
use App\Models\UmkmProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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

        $categories = UmkmProductCategory::orderBy('name')->get();

        return view('admin.umkm.index', compact('products', 'profiles', 'categories', 'totalProfiles', 'totalProducts', 'totalSoldThisMonth'));
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'umkm_profile_id' => ['required', 'exists:umkm_profiles,id'],
            'umkm_product_category_id' => ['nullable', 'exists:umkm_product_categories,id'],
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
            'umkm_product_category_id' => ['nullable', 'exists:umkm_product_categories,id'],
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

    /**
     * Store a newly created UMKM profile in storage.
     */
    public function storeProfile(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['nullable', 'exists:users,id'],
            'business_name' => ['required', 'string', 'max:255'],
            'owner_name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'in:culinary,craft,souvenir,service'],
            'description' => ['nullable', 'string'],
            'ar_marker_id' => ['nullable', 'string', 'max:255'],
            'rating' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'is_active' => ['nullable', 'boolean'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'is_accessible' => ['nullable', 'boolean'],
            'accessibility_notes' => ['nullable', 'string'],
        ]);

        $validated['is_active'] = $request->has('is_active');

        if (empty($validated['ar_marker_id'])) {
            $validated['ar_marker_id'] = 'UMKM_'.strtoupper(Str::random(8));
        }

        $slug = Str::slug($validated['business_name']);
        $originalSlug = $slug;
        $count = 1;
        while (UmkmProfile::where('slug', $slug)->exists()) {
            $slug = $originalSlug.'-'.$count++;
        }
        $validated['slug'] = $slug;

        $latitude = $validated['latitude'];
        $longitude = $validated['longitude'];
        $is_accessible = $request->has('is_accessible');
        $accessibility_notes = $validated['accessibility_notes'] ?? null;

        unset($validated['latitude'], $validated['longitude'], $validated['is_accessible'], $validated['accessibility_notes'], $validated['ar_marker_id']);

        $profile = UmkmProfile::create($validated);

        $mapLocation = $profile->mapLocation()->create([
            'name' => $profile->business_name,
            'category' => 'umkm',
            'latitude' => $latitude,
            'longitude' => $longitude,
            'is_accessible' => $is_accessible,
            'accessibility_notes' => $accessibility_notes,
        ]);

        // AR marker via ArModel (marker-only, no 3D model)
        $arMarkerId = $request->input('ar_marker_id');
        if (! empty($arMarkerId)) {
            ArModel::create([
                'name'            => $profile->business_name.' Marker',
                'ar_marker_id'    => $arMarkerId,
                'map_location_id' => $mapLocation->id,
            ]);
        }

        return redirect()->route('admin.map-manager')->with('success', 'Profil UMKM berhasil ditambahkan.');
    }

    /**
     * Update the specified UMKM profile in storage.
     */
    public function updateProfile(Request $request, int $id): RedirectResponse
    {
        $profile = UmkmProfile::findOrFail($id);

        $validated = $request->validate([
            'user_id' => ['nullable', 'exists:users,id'],
            'business_name' => ['required', 'string', 'max:255'],
            'owner_name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'in:culinary,craft,souvenir,service'],
            'description' => ['nullable', 'string'],
            'ar_marker_id' => ['nullable', 'string', 'max:255'],
            'rating' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'is_active' => ['nullable', 'boolean'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'is_accessible' => ['nullable', 'boolean'],
            'accessibility_notes' => ['nullable', 'string'],
        ]);

        $validated['is_active'] = $request->has('is_active');

        if (empty($validated['ar_marker_id'])) {
            $validated['ar_marker_id'] = 'UMKM_'.strtoupper(Str::random(8));
        }

        if ($profile->business_name !== $validated['business_name']) {
            $slug = Str::slug($validated['business_name']);
            $originalSlug = $slug;
            $count = 1;
            while (UmkmProfile::where('slug', $slug)->where('id', '!=', $id)->exists()) {
                $slug = $originalSlug.'-'.$count++;
            }
            $validated['slug'] = $slug;
        }

        $latitude = $validated['latitude'];
        $longitude = $validated['longitude'];
        $is_accessible = $request->has('is_accessible');
        $accessibility_notes = $validated['accessibility_notes'] ?? null;

        unset($validated['latitude'], $validated['longitude'], $validated['is_accessible'], $validated['accessibility_notes'], $validated['ar_marker_id']);

        $profile->update($validated);

        $mapLocation = $profile->mapLocation()->updateOrCreate(
            [],
            [
                'name' => $profile->business_name,
                'category' => 'umkm',
                'latitude' => $latitude,
                'longitude' => $longitude,
                'is_accessible' => $is_accessible,
                'accessibility_notes' => $accessibility_notes,
            ]
        );

        // AR marker via ArModel
        $arMarkerId = $request->input('ar_marker_id');
        $existingModel = $mapLocation->arModel;
        if (! empty($arMarkerId)) {
            if ($existingModel) {
                $existingModel->update(['ar_marker_id' => $arMarkerId]);
            } else {
                ArModel::create([
                    'name'            => $profile->business_name.' Marker',
                    'ar_marker_id'    => $arMarkerId,
                    'map_location_id' => $mapLocation->id,
                ]);
            }
        } else {
            if ($existingModel) {
                $existingModel->update(['ar_marker_id' => null, 'map_location_id' => null]);
            }
        }

        return redirect()->route('admin.map-manager')->with('success', 'Profil UMKM berhasil diperbarui.');
    }

    /**
     * Remove the specified UMKM profile from storage.
     */
    public function destroyProfile(int $id): RedirectResponse
    {
        $profile = UmkmProfile::findOrFail($id);
        $profile->delete();

        return redirect()->route('admin.map-manager')->with('success', 'Profil UMKM berhasil dihapus.');
    }

    /**
     * Display a listing of UMKM owner accounts.
     */
    public function ownersList(): View
    {
        $owners = User::where('role', 'umkm_owner')->with('umkmProfile')->orderBy('name')->get();

        return view('admin.umkm.owners', compact('owners'));
    }

    /**
     * Store a newly created UMKM owner in storage.
     */
    public function storeOwner(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $validated['role'] = 'umkm_owner';
        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()->route('admin.umkm.owners')->with('success', 'Akun pemilik UMKM berhasil dibuat.');
    }

    /**
     * Update the specified UMKM owner in storage.
     */
    public function updateOwner(Request $request, int $id): RedirectResponse
    {
        $owner = User::where('role', 'umkm_owner')->findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$id],
            'phone' => ['nullable', 'string', 'max:50'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        if (! empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $owner->update($validated);

        return redirect()->route('admin.umkm.owners')->with('success', 'Akun pemilik UMKM berhasil diperbarui.');
    }

    /**
     * Remove the specified UMKM owner from storage.
     */
    public function destroyOwner(int $id): RedirectResponse
    {
        $owner = User::where('role', 'umkm_owner')->findOrFail($id);
        $owner->delete();

        return redirect()->route('admin.umkm.owners')->with('success', 'Akun pemilik UMKM berhasil dihapus.');
    }
}
