<?php

namespace App\Http\Controllers\Admin;

use App\Http\Concerns\ExtractsGeoFields;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUmkmOwnerJsonRequest;
use App\Http\Requests\Admin\UmkmOwnerRequest;
use App\Http\Requests\Admin\UmkmProfileRequest;
use App\Models\TraditionalHouse;
use App\Models\UmkmProfile;
use App\Models\User;
use App\Services\HouseProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UmkmController extends Controller
{
    use ExtractsGeoFields;

    /**
     * Store a newly created UMKM profile in storage.
     */
    public function storeProfile(UmkmProfileRequest $request): RedirectResponse
    {
        return DB::transaction(function () use ($request) {
            $validated = $request->validated();

            $validated['is_active'] = $request->has('is_active');

            if (($validated['profile_type'] ?? 'business') === 'house') {
                $house = TraditionalHouse::findOrFail($validated['traditional_house_id']);
                $validated['business_name'] = HouseProfileService::names($house->number);
            }
            $validated['slug'] = (new UmkmProfile)->generateCollisionFreeSlug(slugFromTranslatable($validated['business_name']));

            $geo = $this->extractGeoFields($request, $validated);

            $profile = UmkmProfile::create($validated);

            $profile->syncMapLocation(['category' => 'umkm', ...$geo]);

            return redirect()->route('admin.map-manager')->with('success', __('Profil UMKM berhasil ditambahkan.'));
        });
    }

    /**
     * Update the specified UMKM profile in storage.
     */
    public function updateProfile(UmkmProfileRequest $request, int $id): RedirectResponse
    {
        return DB::transaction(function () use ($request, $id) {
            $profile = UmkmProfile::findOrFail($id);

            $validated = $request->validated();

            $validated['is_active'] = $request->has('is_active');

            $defaultLocale = config('app.fallback_locale', 'en');
            $currentName = \is_string($profile->business_name) ? $profile->business_name : ($profile->business_name[$defaultLocale] ?? '');
            $newName = $validated['business_name'][$defaultLocale] ?? $validated['business_name']['en'] ?? '';
            if (! $profile->isHouse() && ($validated['profile_type'] ?? 'business') !== 'house' && $currentName !== $newName) {
                $validated['slug'] = $profile->generateCollisionFreeSlug(slugFromTranslatable($validated['business_name']), $profile->id);
            }

            $geo = $this->extractGeoFields($request, $validated);

            $profile->update($validated);

            $profile->syncMapLocation(['category' => 'umkm', ...$geo], isUpdate: true);

            return redirect()->route('admin.map-manager')->with('success', __('Profil UMKM berhasil diperbarui.'));
        });
    }

    /**
     * Remove the specified UMKM profile from storage.
     */
    public function destroyProfile(Request $request, int $id): RedirectResponse
    {
        return DB::transaction(function () use ($request, $id) {
            $profile = UmkmProfile::findOrFail($id);
            $profile->delete();

            $route = $request->input('redirect_to') === 'owners' ? 'admin.umkm.owners' : 'admin.map-manager';

            return redirect()->route($route)->with('success', __('Profil UMKM berhasil dihapus.'));
        });
    }

    /**
     * Display a listing of UMKM owner accounts.
     */
    public function ownersList(Request $request): View
    {
        $query = User::where('role', 'umkm_owner')
            ->with(['umkmProfile' => fn ($query) => $query->withExists(['mapLocation as has_map_pin'])]);

        if ($request->filled('search')) {
            $search = trim((string) $request->query('search'));
            $query->where(function ($query) use ($search): void {
                $query->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')
                    ->orWhere('phone', 'like', '%'.$search.'%')
                    ->orWhereHas('umkmProfile', function ($query) use ($search): void {
                        $query->where('business_name->id', 'like', '%'.$search.'%')
                            ->orWhere('business_name->en', 'like', '%'.$search.'%');
                    });
            });
        }

        $owners = $query
            ->orderBy('name')
            ->orderBy('id')
            ->paginate(15)->withQueryString();
        $unmappedProfiles = UmkmProfile::whereDoesntHave('mapLocation')
            ->with('user:id,name,email')
            ->withCount('products')
            ->orderBy('id')
            ->paginate(15, ['id', 'user_id', 'owner_name', 'business_name', 'is_active'], 'unmapped_page')
            ->withQueryString();

        return view('admin.umkm.owners', compact('owners', 'unmappedProfiles'));
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
