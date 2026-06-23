<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\UmkmProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OwnerDashboardController extends Controller
{
    /**
     * Display the UMKM Owner dashboard summary.
     */
    public function index(): View
    {
        $user = Auth::user();
        $profile = $user->umkmProfile;

        // Statistics
        $productCount = $profile ? $profile->products()->count() : 0;
        $activeProductCount = $profile ? $profile->activeProducts()->count() : 0;

        return view('owner.dashboard', compact('profile', 'productCount', 'activeProductCount'));
    }

    /**
     * Display the UMKM Owner profile edit form.
     */
    public function editProfile(): View
    {
        $user = Auth::user();
        $profile = $user->umkmProfile;

        return view('owner.profile', compact('profile'));
    }

    /**
     * Update the UMKM Owner profile.
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $profile = $user->umkmProfile;

        $validated = $request->validate([
            'business_name' => ['required', 'array'],
            'business_name.en' => ['required', 'string', 'max:255'],
            'business_name.id' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'array'],
            'description.en' => ['nullable', 'string'],
            'description.id' => ['nullable', 'string'],
        ]);

        $defaultLocale = config('app.fallback_locale', 'en');

        if (! $profile) {
            // Create a default profile if they don't have one
            $validated['user_id'] = $user->id;
            $validated['owner_name'] = $user->name;
            $slugValue = $validated['business_name'][$defaultLocale] ?? $validated['business_name']['en'] ?? reset($validated['business_name']);
            $validated['slug'] = Str::slug($slugValue).'-'.Str::random(5);
            $validated['is_active'] = true;
            $validated['rating'] = 5.0;

            UmkmProfile::create($validated);
        } else {
            // Update existing profile
            $slugValue = $validated['business_name'][$defaultLocale] ?? $validated['business_name']['en'] ?? reset($validated['business_name']);
            $validated['slug'] = Str::slug($slugValue);
            $currentName = is_string($profile->business_name) ? $profile->business_name : ($profile->business_name[$defaultLocale] ?? '');
            $newName = $validated['business_name'][$defaultLocale] ?? $validated['business_name']['en'] ?? '';
            if ($currentName !== $newName) {
                $originalSlug = $validated['slug'];
                $count = 1;
                while (UmkmProfile::where('slug', $validated['slug'])->where('id', '!=', $profile->id)->exists()) {
                    $validated['slug'] = $originalSlug.'-'.$count++;
                }
            }

            $profile->update($validated);
        }

        return redirect()->route('owner.profile')->with('success', __('Informasi toko Anda berhasil diperbarui.'));
    }

    /**
     * Display the map marker/location editor.
     */
    public function editLocation(): View
    {
        $user = Auth::user();
        $profile = $user->umkmProfile;
        $location = $profile ? $profile->mapLocation : null;

        return view('owner.location', compact('profile', 'location'));
    }

    /**
     * Update the UMKM location on the map.
     */
    public function updateLocation(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $profile = $user->umkmProfile;

        if (! $profile) {
            return redirect()->route('owner.profile')->with('error', __('Silakan buat profil toko terlebih dahulu.'));
        }

        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'is_accessible' => ['nullable', 'boolean'],
            'accessibility_notes' => ['nullable', 'string'],
        ]);

        $latitude = $validated['latitude'];
        $longitude = $validated['longitude'];
        $is_accessible = $request->has('is_accessible');
        $accessibility_notes = $validated['accessibility_notes'] ?? null;

        $profile->mapLocation()->updateOrCreate(
            [],
            [
                'name' => is_string($profile->business_name) ? $profile->business_name : ($profile->business_name[config('app.fallback_locale')] ?? $profile->business_name['en'] ?? ''),
                'category' => 'umkm',
                'latitude' => $latitude,
                'longitude' => $longitude,
                'is_accessible' => $is_accessible,
                'accessibility_notes' => $accessibility_notes,
            ]
        );

        return redirect()->route('owner.location')->with('success', __('Lokasi toko Anda berhasil diperbarui.'));
    }
}
