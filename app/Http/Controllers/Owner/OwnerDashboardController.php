<?php

namespace App\Http\Controllers\Owner;

use App\Http\Concerns\NormalizesMultilingualInput;
use App\Http\Requests\Owner\OwnerLocationRequest;
use App\Http\Requests\Owner\OwnerProfileRequest;
use App\Models\UmkmProfile;
use App\Services\ImageOptimizerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class OwnerDashboardController extends BaseOwnerController
{
    use NormalizesMultilingualInput;

    /**
     * Display the UMKM Owner dashboard summary.
     */
    public function index(): View
    {
        // Statistics
        $productCount = $this->profile ? $this->profile->products()->count() : 0;
        $activeProductCount = $this->profile ? $this->profile->activeProducts()->count() : 0;

        return view('owner.dashboard', ['profile' => $this->profile] + compact('productCount', 'activeProductCount'));
    }

    /**
     * Display the UMKM Owner profile edit form.
     */
    public function editProfile(): View
    {
        return view('owner.profile', ['profile' => $this->profile]);
    }

    /**
     * Update the UMKM Owner profile.
     */
    public function updateProfile(OwnerProfileRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        unset($validated['image']);

        if ($request->hasFile('image')) {
            $optimizer = app(ImageOptimizerService::class);
            $newPath = $optimizer->optimizeAndStore($request->file('image'), 'umkm_profiles');

            if ($this->profile && $this->profile->image_path && str_starts_with($this->profile->image_path, 'umkm_profiles/')) {
                Storage::disk('public')->delete($this->profile->image_path);
            }

            $validated['image_path'] = $newPath;
        }

        $defaultLocale = config('app.fallback_locale', 'en');

        if (! $this->profile) {
            // Create a default profile if they don't have one
            $validated['user_id'] = $this->user->id;
            $validated['owner_name'] = $this->user->name;
            $validated['slug'] = (new UmkmProfile)->generateUniqueSlug(slugFromTranslatable($validated['business_name']));
            $validated['is_active'] = true;
            $validated['rating'] = 5.0;

            UmkmProfile::create($validated);
        } else {
            // Update existing profile
            $currentName = \is_string($this->profile->business_name) ? $this->profile->business_name : ($this->profile->business_name[$defaultLocale] ?? '');
            $newName = $validated['business_name'][$defaultLocale] ?? $validated['business_name']['en'] ?? '';
            if ($currentName !== $newName) {
                $validated['slug'] = $this->profile->generateCollisionFreeSlug(slugFromTranslatable($validated['business_name']), $this->profile->id);
            }

            $this->profile->update($validated);
        }

        Cache::tags(['explore', 'umkm'])->flush();

        return redirect()->route('owner.profile')->with('success', __('Informasi toko Anda berhasil diperbarui.'));
    }

    /**
     * Display the map marker/location editor.
     */
    public function editLocation(): View
    {
        $location = $this->profile ? $this->profile->mapLocation : null;

        return view('owner.location', ['profile' => $this->profile, 'location' => $location]);
    }

    /**
     * Update the UMKM location on the map.
     */
    public function updateLocation(OwnerLocationRequest $request): RedirectResponse
    {
        $profile = $this->requireProfile();

        $validated = $request->validated();

        $latitude = $validated['latitude'];
        $longitude = $validated['longitude'];
        $is_accessible = $request->has('is_accessible');
        $accessibility_notes = $validated['accessibility_notes'] ?? null;

        $profile->syncMapLocation([
            'category' => 'umkm',
            'latitude' => $latitude,
            'longitude' => $longitude,
            'is_accessible' => $is_accessible,
            'accessibility_notes' => $accessibility_notes,
        ], isUpdate: true);

        return redirect()->route('owner.location')->with('success', __('Lokasi toko Anda berhasil diperbarui.'));
    }

    /**
     * Display the UMKM Owner complaints/feedbacks page.
     */
    public function complaints(): View
    {
        $profile = $this->requireProfile();

        $complaints = $profile->complaints()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('owner.complaints', ['profile' => $this->profile] + compact('complaints'));
    }
}
