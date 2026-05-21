<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TourRoute;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TourRouteController extends Controller
{
    /**
     * Display a listing of tour routes.
     */
    public function index(): View
    {
        $routes = TourRoute::with('routePoints.locationable')->get();

        return view('admin.tour-routes.index', compact('routes'));
    }

    /**
     * Store a newly created tour route in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'difficulty' => ['required', 'string', 'in:Mudah,Sedang,Sulit,Edukasi,Alam,Belanja,Difabel,easy,moderate,challenging'],
            'estimated_duration_minutes' => ['required', 'integer', 'min:1'],
            'distance_meters' => ['required', 'integer', 'min:1'],
            'is_smart_route' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = true;
        $validated['is_smart_route'] = $request->has('is_smart_route') ? true : false;

        $difficultyMap = [
            'Mudah' => 'easy',
            'easy' => 'easy',
            'Sedang' => 'moderate',
            'moderate' => 'moderate',
            'Sulit' => 'challenging',
            'challenging' => 'challenging',
        ];
        $validated['difficulty'] = $difficultyMap[$validated['difficulty']] ?? 'easy';

        TourRoute::create($validated);

        return redirect()->route('admin.tour-routes')->with('success', 'Rute wisata berhasil ditambahkan.');
    }

    /**
     * Update the specified tour route in storage.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $route = TourRoute::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'difficulty' => ['required', 'string', 'in:Mudah,Sedang,Sulit,Edukasi,Alam,Belanja,Difabel,easy,moderate,challenging'],
            'estimated_duration_minutes' => ['required', 'integer', 'min:1'],
            'distance_meters' => ['required', 'integer', 'min:1'],
            'is_smart_route' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_smart_route'] = $request->has('is_smart_route') ? true : false;
        $validated['is_active'] = $request->has('is_active') ? true : false;

        $difficultyMap = [
            'Mudah' => 'easy',
            'easy' => 'easy',
            'Sedang' => 'moderate',
            'moderate' => 'moderate',
            'Sulit' => 'challenging',
            'challenging' => 'challenging',
        ];
        $validated['difficulty'] = $difficultyMap[$validated['difficulty']] ?? 'easy';

        $route->update($validated);

        return redirect()->route('admin.tour-routes')->with('success', 'Rute wisata berhasil diperbarui.');
    }

    /**
     * Toggle the active status of the specified tour route.
     */
    public function toggleActive(int $id): RedirectResponse
    {
        $route = TourRoute::findOrFail($id);
        $route->is_active = ! $route->is_active;
        $route->save();

        $status = $route->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->route('admin.tour-routes')->with('success', "Rute wisata berhasil {$status}.");
    }

    /**
     * Remove the specified tour route from storage.
     */
    public function destroy(int $id): RedirectResponse
    {
        $route = TourRoute::findOrFail($id);
        $route->routePoints()->delete();
        $route->delete();

        return redirect()->route('admin.tour-routes')->with('success', 'Rute wisata berhasil dihapus.');
    }
}
