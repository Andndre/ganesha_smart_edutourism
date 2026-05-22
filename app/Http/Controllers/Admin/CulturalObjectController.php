<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CulturalObject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CulturalObjectController extends Controller
{
    /**
     * Store a newly created cultural object in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:cultural_objects'],
            'category' => ['required', 'string', 'in:temple,house,craft,tradition'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'description' => ['nullable', 'string'],
            'ar_marker_id' => ['nullable', 'string', 'max:255'],
            'model_3d_path' => ['nullable', 'string', 'max:255'],
            'audio_narration_path' => ['nullable', 'string', 'max:255'],
            'model_3d_file' => ['nullable', 'file', 'max:20480'],
            'audio_narration_file' => ['nullable', 'file', 'max:10240'],
            'historical_images' => ['nullable', 'array'],
            'historical_images.*' => ['image', 'mimes:jpeg,png,jpg,webp,gif', 'max:5120'],
        ]);

        if ($request->hasFile('model_3d_file')) {
            $validated['model_3d_path'] = $request->file('model_3d_file')->store('models', 'public');
        }

        if ($request->hasFile('audio_narration_file')) {
            $validated['audio_narration_path'] = $request->file('audio_narration_file')->store('audio', 'public');
        }

        if ($request->hasFile('historical_images')) {
            $images = [];
            foreach ($request->file('historical_images') as $file) {
                $images[] = $file->store('images', 'public');
            }
            $validated['historical_images'] = $images;
        }

        $validated['slug'] = Str::slug($validated['name']);

        // Null safety for database constraints
        if (empty($validated['description'])) {
            $validated['description'] = 'Deskripsi untuk '.$validated['name'];
        }
        if (empty($validated['ar_marker_id'])) {
            $validated['ar_marker_id'] = 'MARKER_'.strtoupper(Str::random(8));
        }

        $latitude = $validated['latitude'] ?? -8.4217504;
        $longitude = $validated['longitude'] ?? 115.3590021;

        // Clean up temporary variables not in DB schema
        unset($validated['model_3d_file'], $validated['audio_narration_file'], $validated['latitude'], $validated['longitude']);

        $object = CulturalObject::create($validated);

        $object->mapLocation()->create([
            'name' => $object->name,
            'category' => 'cultural',
            'latitude' => $latitude,
            'longitude' => $longitude,
            'is_accessible' => true,
            'accessibility_notes' => 'Akses jalan datar ramah kursi roda dan stroller bayi.',
        ]);

        return redirect()->route('admin.map-manager')->with('success', 'Objek budaya berhasil ditambahkan.');
    }

    /**
     * Update the specified cultural object in storage.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $object = CulturalObject::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:cultural_objects,name,'.$id],
            'category' => ['required', 'string', 'in:temple,house,craft,tradition'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'description' => ['nullable', 'string'],
            'ar_marker_id' => ['nullable', 'string', 'max:255'],
            'model_3d_path' => ['nullable', 'string', 'max:255'],
            'audio_narration_path' => ['nullable', 'string', 'max:255'],
            'model_3d_file' => ['nullable', 'file', 'max:20480'],
            'audio_narration_file' => ['nullable', 'file', 'max:10240'],
            'historical_images' => ['nullable', 'array'],
            'historical_images.*' => ['image', 'mimes:jpeg,png,jpg,webp,gif', 'max:5120'],
        ]);

        if ($request->hasFile('model_3d_file')) {
            $validated['model_3d_path'] = $request->file('model_3d_file')->store('models', 'public');
        } elseif (! isset($validated['model_3d_path'])) {
            $validated['model_3d_path'] = $object->model_3d_path;
        }

        if ($request->hasFile('audio_narration_file')) {
            $validated['audio_narration_path'] = $request->file('audio_narration_file')->store('audio', 'public');
        } elseif (! isset($validated['audio_narration_path'])) {
            $validated['audio_narration_path'] = $object->audio_narration_path;
        }

        if ($request->hasFile('historical_images')) {
            $images = [];
            foreach ($request->file('historical_images') as $file) {
                $images[] = $file->store('images', 'public');
            }
            $validated['historical_images'] = $images;
        } else {
            $validated['historical_images'] = $object->historical_images;
        }

        $validated['slug'] = Str::slug($validated['name']);

        // Null safety for database constraints
        if (empty($validated['description'])) {
            $validated['description'] = 'Deskripsi untuk '.$validated['name'];
        }
        if (empty($validated['ar_marker_id'])) {
            $validated['ar_marker_id'] = 'MARKER_'.strtoupper(Str::random(8));
        }

        $latitude = $validated['latitude'] ?? -8.4217504;
        $longitude = $validated['longitude'] ?? 115.3590021;

        // Clean up temporary variables not in DB schema
        unset($validated['model_3d_file'], $validated['audio_narration_file'], $validated['latitude'], $validated['longitude']);

        $object->update($validated);

        $object->mapLocation()->updateOrCreate(
            [],
            [
                'name' => $object->name,
                'category' => 'cultural',
                'latitude' => $latitude,
                'longitude' => $longitude,
                'is_accessible' => true,
                'accessibility_notes' => 'Akses jalan datar ramah kursi roda dan stroller bayi.',
            ]
        );

        return redirect()->route('admin.map-manager')->with('success', 'Objek budaya berhasil diperbarui.');
    }

    /**
     * Remove the specified cultural object from storage.
     */
    public function destroy(int $id): RedirectResponse
    {
        $object = CulturalObject::findOrFail($id);
        $object->delete();

        return redirect()->route('admin.map-manager')->with('success', 'Objek budaya berhasil dihapus.');
    }
}
