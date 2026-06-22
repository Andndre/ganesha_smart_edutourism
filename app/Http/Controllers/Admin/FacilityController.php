<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FacilityController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'array'],
            'name.en' => ['required', 'string', 'max:255'],
            'name.id' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:toilet,information,emergency,parking,accessibility'],
            'description' => ['nullable', 'array'],
            'description.en' => ['nullable', 'string'],
            'description.id' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'is_accessible' => ['nullable', 'boolean'],
            'accessibility_notes' => ['nullable', 'string'],
        ]);

        $latitude = $validated['latitude'];
        $longitude = $validated['longitude'];
        $is_accessible = $request->has('is_accessible');
        $accessibility_notes = $validated['accessibility_notes'] ?? null;

        unset($validated['latitude'], $validated['longitude'], $validated['is_accessible'], $validated['accessibility_notes']);

        $validated['is_active'] = $request->has('is_active');

        $facility = Facility::create($validated);

        $facility->mapLocation()->create([
            'name' => is_string($facility->name) ? $facility->name : ($facility->name[config('app.fallback_locale')] ?? $facility->name['en'] ?? ''),
            'category' => 'facility',
            'latitude' => $latitude,
            'longitude' => $longitude,
            'is_accessible' => $is_accessible,
            'accessibility_notes' => $accessibility_notes,
        ]);

        return redirect()->route('admin.map-manager')->with('success', 'Fasilitas berhasil ditambahkan.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Facility $facility): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'array'],
            'name.en' => ['required', 'string', 'max:255'],
            'name.id' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:toilet,information,emergency,parking,accessibility'],
            'description' => ['nullable', 'array'],
            'description.en' => ['nullable', 'string'],
            'description.id' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'is_accessible' => ['nullable', 'boolean'],
            'accessibility_notes' => ['nullable', 'string'],
        ]);

        $latitude = $validated['latitude'];
        $longitude = $validated['longitude'];
        $is_accessible = $request->has('is_accessible');
        $accessibility_notes = $validated['accessibility_notes'] ?? null;

        unset($validated['latitude'], $validated['longitude'], $validated['is_accessible'], $validated['accessibility_notes']);

        $validated['is_active'] = $request->has('is_active');

        $facility->update($validated);

        $facility->mapLocation()->updateOrCreate(
            [],
            [
                'name' => is_string($facility->name) ? $facility->name : ($facility->name[config('app.fallback_locale')] ?? $facility->name['en'] ?? ''),
                'category' => 'facility',
                'latitude' => $latitude,
                'longitude' => $longitude,
                'is_accessible' => $is_accessible,
                'accessibility_notes' => $accessibility_notes,
            ]
        );

        return redirect()->route('admin.map-manager')->with('success', 'Fasilitas berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Facility $facility): RedirectResponse
    {
        $facility->delete();

        return redirect()->route('admin.map-manager')->with('success', 'Fasilitas berhasil dihapus.');
    }
}
