<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CulturalObject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CulturalObjectController extends Controller
{
    /**
     * Display a listing of cultural objects.
     */
    public function index(Request $request): View
    {
        $query = CulturalObject::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        if ($request->filled('category') && $request->category !== 'Semua Kategori') {
            $query->category($request->category);
        }

        $objects = $query->paginate(10)->withQueryString();

        return view('admin.cultural-objects.index', compact('objects'));
    }

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
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        // Null safety for database constraints
        if (empty($validated['description'])) {
            $validated['description'] = 'Deskripsi untuk '.$validated['name'];
        }
        if (empty($validated['latitude'])) {
            $validated['latitude'] = -8.5878;
        }
        if (empty($validated['longitude'])) {
            $validated['longitude'] = 115.1622;
        }
        if (empty($validated['ar_marker_id'])) {
            $validated['ar_marker_id'] = 'MARKER_'.strtoupper(Str::random(8));
        }

        CulturalObject::create($validated);

        return redirect()->route('admin.cultural-objects')->with('success', 'Objek budaya berhasil ditambahkan.');
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
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        // Null safety for database constraints
        if (empty($validated['description'])) {
            $validated['description'] = 'Deskripsi untuk '.$validated['name'];
        }
        if (empty($validated['latitude'])) {
            $validated['latitude'] = -8.5878;
        }
        if (empty($validated['longitude'])) {
            $validated['longitude'] = 115.1622;
        }
        if (empty($validated['ar_marker_id'])) {
            $validated['ar_marker_id'] = 'MARKER_'.strtoupper(Str::random(8));
        }

        $object->update($validated);

        return redirect()->route('admin.cultural-objects')->with('success', 'Objek budaya berhasil diperbarui.');
    }

    /**
     * Remove the specified cultural object from storage.
     */
    public function destroy(int $id): RedirectResponse
    {
        $object = CulturalObject::findOrFail($id);
        $object->delete();

        return redirect()->route('admin.cultural-objects')->with('success', 'Objek budaya berhasil dihapus.');
    }
}
