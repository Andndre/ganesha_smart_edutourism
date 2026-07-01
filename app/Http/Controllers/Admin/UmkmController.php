<?php

namespace App\Http\Controllers\Admin;

use App\Http\Concerns\NormalizesMultilingualInput;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUmkmOwnerJsonRequest;
use App\Http\Requests\Admin\UmkmOwnerRequest;
use App\Http\Requests\Admin\UmkmProductRequest;
use App\Http\Requests\Admin\UmkmProfileRequest;
use App\Models\ArModel;
use App\Models\UmkmProduct;
use App\Models\UmkmProductCategory;
use App\Models\UmkmProfile;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class UmkmController extends Controller
{
    use NormalizesMultilingualInput;

    /**
     * Display a listing of UMKM products and profiles.
     */
    public function index(Request $request): View
    {
        $query = UmkmProduct::with('umkmProfile');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name->en', 'like', '%'.$search.'%')
                    ->orWhere('name->id', 'like', '%'.$search.'%')
                    ->orWhereHas('umkmProfile', function ($qp) use ($search) {
                        $qp->where('business_name->en', 'like', '%'.$search.'%')
                            ->orWhere('business_name->id', 'like', '%'.$search.'%')
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
        $profiles = UmkmProfile::orderBy('business_name->'.app()->getLocale())->get();

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

        $categories = UmkmProductCategory::orderBy('name->'.app()->getLocale())->get();

        return view('admin.umkm.index', compact('products', 'profiles', 'categories', 'totalProfiles', 'totalProducts', 'totalSoldThisMonth'));
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(UmkmProductRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        if ($request->hasFile('images')) {
            $imagePaths = [];
            foreach ($request->file('images') as $file) {
                $imagePaths[] = $file->store('images', 'public');
            }
            $validated['images'] = $imagePaths;
        }

        $validated['slug'] = (new UmkmProduct)->generateUniqueSlug(slugFromTranslatable($validated['name']));
        $validated['is_active'] = true;

        if (! isset($validated['unit'])) {
            $validated['unit'] = 'pcs';
        }

        UmkmProduct::create($validated);

        return redirect()->route('admin.umkm')->with('success', __('Produk UMKM berhasil ditambahkan.'));
    }

    /**
     * Update the specified product in storage.
     */
    public function update(UmkmProductRequest $request, int $id): RedirectResponse
    {
        $product = UmkmProduct::findOrFail($id);

        $validated = $request->validated();

        if ($request->hasFile('images')) {
            $imagePaths = [];
            foreach ($request->file('images') as $file) {
                $imagePaths[] = $file->store('images', 'public');
            }
            $validated['images'] = $imagePaths;
        } else {
            $validated['images'] = $product->images;
        }

        $validated['slug'] = $product->generateUniqueSlug(slugFromTranslatable($validated['name']));
        $validated['is_active'] = $request->has('is_active') ? true : false;

        $product->update($validated);

        return redirect()->route('admin.umkm')->with('success', __('Produk UMKM berhasil diperbarui.'));
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy(int $id): RedirectResponse
    {
        $product = UmkmProduct::findOrFail($id);
        $product->delete();

        return redirect()->route('admin.umkm')->with('success', __('Produk UMKM berhasil dihapus.'));
    }

    /**
     * Store a newly created UMKM profile in storage.
     */
    public function storeProfile(UmkmProfileRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $validated['is_active'] = $request->has('is_active');

        if (empty($validated['ar_marker_id'])) {
            $validated['ar_marker_id'] = 'UMKM_'.strtoupper(Str::random(8));
        }

        $validated['slug'] = (new UmkmProfile)->generateCollisionFreeSlug(slugFromTranslatable($validated['business_name']));

        $latitude = $validated['latitude'];
        $longitude = $validated['longitude'];
        $is_accessible = $request->has('is_accessible');
        $accessibility_notes = $validated['accessibility_notes'] ?? null;

        unset($validated['latitude'], $validated['longitude'], $validated['is_accessible'], $validated['accessibility_notes'], $validated['ar_marker_id']);

        $profile = UmkmProfile::create($validated);

        $mapLocation = $profile->syncMapLocation([
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
                'name' => $profile->getMapDisplayName().' Marker',
                'ar_marker_id' => $arMarkerId,
                'map_location_id' => $mapLocation->id,
            ]);
        }

        return redirect()->route('admin.map-manager')->with('success', __('Profil UMKM berhasil ditambahkan.'));
    }

    /**
     * Update the specified UMKM profile in storage.
     */
    public function updateProfile(UmkmProfileRequest $request, int $id): RedirectResponse
    {
        $profile = UmkmProfile::findOrFail($id);

        $validated = $request->validated();

        $validated['is_active'] = $request->has('is_active');

        if (empty($validated['ar_marker_id'])) {
            $validated['ar_marker_id'] = 'UMKM_'.strtoupper(Str::random(8));
        }

        $defaultLocale = config('app.fallback_locale', 'en');
        $currentName = \is_string($profile->business_name) ? $profile->business_name : ($profile->business_name[$defaultLocale] ?? '');
        $newName = $validated['business_name'][$defaultLocale] ?? $validated['business_name']['en'] ?? '';
        if ($currentName !== $newName) {
            $validated['slug'] = $profile->generateCollisionFreeSlug(slugFromTranslatable($validated['business_name']), $profile->id);
        }

        $latitude = $validated['latitude'];
        $longitude = $validated['longitude'];
        $is_accessible = $request->has('is_accessible');
        $accessibility_notes = $validated['accessibility_notes'] ?? null;

        unset($validated['latitude'], $validated['longitude'], $validated['is_accessible'], $validated['accessibility_notes'], $validated['ar_marker_id']);

        $profile->update($validated);

        $mapLocation = $profile->syncMapLocation([
            'category' => 'umkm',
            'latitude' => $latitude,
            'longitude' => $longitude,
            'is_accessible' => $is_accessible,
            'accessibility_notes' => $accessibility_notes,
        ], isUpdate: true);

        // AR marker via ArModel
        $arMarkerId = $request->input('ar_marker_id');
        $existingModel = $mapLocation->arModel;
        if (! empty($arMarkerId)) {
            if ($existingModel) {
                $existingModel->update(['ar_marker_id' => $arMarkerId]);
            } else {
                ArModel::create([
                    'name' => $profile->getMapDisplayName().' Marker',
                    'ar_marker_id' => $arMarkerId,
                    'map_location_id' => $mapLocation->id,
                ]);
            }
        } else {
            if ($existingModel) {
                $existingModel->update(['ar_marker_id' => null, 'map_location_id' => null]);
            }
        }

        return redirect()->route('admin.map-manager')->with('success', __('Profil UMKM berhasil diperbarui.'));
    }

    /**
     * Remove the specified UMKM profile from storage.
     */
    public function destroyProfile(int $id): RedirectResponse
    {
        $profile = UmkmProfile::findOrFail($id);
        $profile->delete();

        return redirect()->route('admin.map-manager')->with('success', __('Profil UMKM berhasil dihapus.'));
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
    public function storeOwner(UmkmOwnerRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $validated['role'] = 'umkm_owner';
        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()->route('admin.umkm.owners')->with('success', __('Akun pemilik UMKM berhasil dibuat.'));
    }

    /**
     * Store a new UMKM owner via AJAX (returns JSON for inline creation).
     */
    public function storeOwnerJson(StoreUmkmOwnerJsonRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['role'] = 'umkm_owner';
        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        return response()->json([
            'success' => true,
            'owner' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'message' => 'Akun pemilik UMKM berhasil dibuat.',
        ]);
    }

    /**
     * Check if an email is already taken (AJAX).
     */
    public function checkEmail(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        return response()->json(['taken' => User::where('email', $request->email)->exists()]);
    }

    /**
     * Update the specified UMKM owner in storage.
     */
    public function updateOwner(UmkmOwnerRequest $request, int $id): RedirectResponse
    {
        $owner = User::where('role', 'umkm_owner')->findOrFail($id);

        $validated = $request->validated();

        if (! empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $owner->update($validated);

        return redirect()->route('admin.umkm.owners')->with('success', __('Akun pemilik UMKM berhasil diperbarui.'));
    }

    /**
     * Remove the specified UMKM owner from storage.
     */
    public function destroyOwner(int $id): RedirectResponse
    {
        $owner = User::where('role', 'umkm_owner')->findOrFail($id);
        $owner->delete();

        return redirect()->route('admin.umkm.owners')->with('success', __('Akun pemilik UMKM berhasil dihapus.'));
    }
}
