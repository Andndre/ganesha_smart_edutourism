<?php

namespace App\Http\Controllers\Admin;

use App\Http\Concerns\NormalizesMultilingualInput;
use App\Http\Controllers\Controller;
use App\Models\MapLocation;
use App\Models\TourRoute;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TourRouteController extends Controller
{
    use NormalizesMultilingualInput;

    /**
     * Display a listing of tour routes.
     */
    public function index(): View
    {
        $routes = TourRoute::with('routePoints.locationable')->get();

        return view('admin.tour-routes.index', compact('routes'));
    }

    /**
     * Show the form for creating a new tour route.
     */
    public function create(): View
    {
        $locations = MapLocation::all()->map(function ($loc) {
            return [
                'id' => $loc->id,
                'name' => $loc->name,
                'category' => $loc->category === 'facility' ? 'facilities' : ($loc->category === 'toilet' ? 'toilets' : $loc->category),
                'latitude' => $loc->latitude,
                'longitude' => $loc->longitude,
                'locationable_type' => $loc->locationable_type,
                'locationable_id' => $loc->locationable_id,
            ];
        });

        return view('admin.tour-routes.create', compact('locations'));
    }

    /**
     * Show the form for editing the specified tour route.
     */
    public function edit(int $id): View
    {
        $route = TourRoute::with('routePoints.locationable')->findOrFail($id);

        $locations = MapLocation::all()->map(function ($loc) {
            return [
                'id' => $loc->id,
                'name' => $loc->name,
                'category' => $loc->category === 'facility' ? 'facilities' : ($loc->category === 'toilet' ? 'toilets' : $loc->category),
                'latitude' => $loc->latitude,
                'longitude' => $loc->longitude,
                'locationable_type' => $loc->locationable_type,
                'locationable_id' => $loc->locationable_id,
            ];
        });

        return view('admin.tour-routes.edit', compact('route', 'locations'));
    }

    /**
     * Store a newly created tour route in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->normalizeLocaleFields($request, ['name', 'description']);

        if ($request->has('points') && is_array($request->input('points'))) {
            $points = $request->input('points');
            foreach ($points as $index => $point) {
                if (isset($point['storytelling_content']) && is_string($point['storytelling_content'])) {
                    $points[$index]['storytelling_content'] = [
                        'en' => $point['storytelling_content'],
                        'id' => $point['storytelling_content'],
                    ];
                }
            }
            $request->merge(['points' => $points]);
        }

        $validated = $request->validate([
            'name' => ['required', 'array'],
            'name.en' => ['required', 'string', 'max:255'],
            'name.id' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'array'],
            'description.en' => ['nullable', 'string'],
            'description.id' => ['nullable', 'string'],
            'difficulty' => ['required', 'string', 'in:Mudah,Sedang,Sulit,Edukasi,Alam,Belanja,Difabel,easy,moderate,challenging'],
            'estimated_duration_minutes' => ['required', 'integer', 'min:1'],
            'distance_meters' => ['required', 'integer', 'min:1'],

            'points' => ['nullable', 'array'],
            'points.*.locationable_type' => ['required', 'string'],
            'points.*.locationable_id' => ['required', 'integer'],
            'points.*.estimated_visit_minutes' => ['nullable', 'integer', 'min:1'],
            'points.*.storytelling_content' => ['nullable', 'array'],
            'points.*.storytelling_content.en' => ['nullable', 'string'],
            'points.*.storytelling_content.id' => ['nullable', 'string'],
        ]);

        $validated['is_active'] = true;

        $difficultyMap = [
            'Mudah' => 'easy',
            'easy' => 'easy',
            'Sedang' => 'moderate',
            'moderate' => 'moderate',
            'Sulit' => 'challenging',
            'challenging' => 'challenging',
        ];
        $validated['difficulty'] = $difficultyMap[$validated['difficulty']] ?? 'easy';

        $route = TourRoute::create($validated);

        if ($request->has('points') && is_array($request->points)) {
            foreach ($request->points as $index => $point) {
                $route->routePoints()->create([
                    'locationable_type' => $point['locationable_type'],
                    'locationable_id' => $point['locationable_id'],
                    'order' => $index + 1,
                    'estimated_visit_minutes' => $point['estimated_visit_minutes'] ?? 15,
                    'storytelling_content' => $point['storytelling_content'] ?? null,
                ]);
            }
        }

        return redirect()->route('admin.tour-routes')->with('success', __('Rute wisata berhasil ditambahkan.'));
    }

    /**
     * Update the specified tour route in storage.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $route = TourRoute::findOrFail($id);

        $this->normalizeLocaleFields($request, ['name', 'description']);

        if ($request->has('points') && is_array($request->input('points'))) {
            $points = $request->input('points');
            foreach ($points as $index => $point) {
                if (isset($point['storytelling_content']) && is_string($point['storytelling_content'])) {
                    $points[$index]['storytelling_content'] = [
                        'en' => $point['storytelling_content'],
                        'id' => $point['storytelling_content'],
                    ];
                }
            }
            $request->merge(['points' => $points]);
        }

        $validated = $request->validate([
            'name' => ['required', 'array'],
            'name.en' => ['required', 'string', 'max:255'],
            'name.id' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'array'],
            'description.en' => ['nullable', 'string'],
            'description.id' => ['nullable', 'string'],
            'difficulty' => ['required', 'string', 'in:Mudah,Sedang,Sulit,Edukasi,Alam,Belanja,Difabel,easy,moderate,challenging'],
            'estimated_duration_minutes' => ['required', 'integer', 'min:1'],
            'distance_meters' => ['required', 'integer', 'min:1'],

            'is_active' => ['nullable', 'boolean'],
            'points' => ['nullable', 'array'],
            'points.*.locationable_type' => ['required', 'string'],
            'points.*.locationable_id' => ['required', 'integer'],
            'points.*.estimated_visit_minutes' => ['nullable', 'integer', 'min:1'],
            'points.*.storytelling_content' => ['nullable', 'array'],
            'points.*.storytelling_content.en' => ['nullable', 'string'],
            'points.*.storytelling_content.id' => ['nullable', 'string'],
        ]);

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

        // Re-sync points
        $route->routePoints()->delete();
        if ($request->has('points') && is_array($request->points)) {
            foreach ($request->points as $index => $point) {
                $route->routePoints()->create([
                    'locationable_type' => $point['locationable_type'],
                    'locationable_id' => $point['locationable_id'],
                    'order' => $index + 1,
                    'estimated_visit_minutes' => $point['estimated_visit_minutes'] ?? 15,
                    'storytelling_content' => $point['storytelling_content'] ?? null,
                ]);
            }
        }

        return redirect()->route('admin.tour-routes')->with('success', __('Rute wisata berhasil diperbarui.'));
    }

    /**
     * Toggle the active status of the specified tour route.
     */
    public function toggleActive(int $id): RedirectResponse
    {
        $route = TourRoute::findOrFail($id);
        $route->is_active = ! $route->is_active;
        $route->save();

        $status = $route->is_active ? __('diaktifkan') : __('dinonaktifkan');

        return redirect()->route('admin.tour-routes')->with('success', __('Rute wisata berhasil :status', ['status' => $status]));
    }

    /**
     * Remove the specified tour route from storage.
     */
    public function destroy(int $id): RedirectResponse
    {
        $route = TourRoute::findOrFail($id);
        $route->routePoints()->delete();
        $route->delete();

        return redirect()->route('admin.tour-routes')->with('success', __('Rute wisata berhasil dihapus.'));
    }
}
