<?php

namespace App\Http\Controllers\Admin;

use App\Http\Concerns\NormalizesMultilingualInput;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TourRouteRequest;
use App\Models\MapLocation;
use App\Models\TourRoute;
use App\Services\TusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
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
        $route = TourRoute::with(['routePoints.locationable', 'routePoints.missions'])->findOrFail($id);

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
    public function store(TourRouteRequest $request): RedirectResponse
    {
        $validated = $request->validated();

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

        $this->syncPointsAndMissions($route, $request->input('points', []));

        return redirect()->route('admin.tour-routes')->with('success', __('Rute wisata berhasil ditambahkan.'));
    }

    /**
     * Update the specified tour route in storage.
     */
    public function update(TourRouteRequest $request, int $id): RedirectResponse
    {
        $route = TourRoute::findOrFail($id);

        $validated = $request->validated();

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

        $points = $request->input('points', []);

        if ($route->routePoints()->exists() && empty($points)) {
            return back()->withErrors([
                'points' => __('Rute yang sudah memiliki titik tidak dapat disimpan tanpa titik sama sekali.'),
            ]);
        }

        $route->update($validated);

        $this->syncPointsAndMissions($route, $points);

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

    /**
     * Sync a route's points (and, from Task 3, their missions) without delete-recreate,
     * so point and mission IDs stay stable — route_sessions.missions_completed references
     * mission IDs by value.
     */
    private function syncPointsAndMissions(TourRoute $route, array $points): void
    {
        DB::transaction(function () use ($route, $points) {
            $keptPointIds = [];

            foreach (array_values($points) as $index => $point) {
                // Video arrives as a TUS temp key per locale (uploaded separately, moved to
                // final storage here); audio arrives already-final from the asset upload
                // endpoint. Untouched locales keep whatever path was already submitted.
                $introVideoPaths = $point['intro_video_paths'] ?? [];
                foreach (['en', 'id'] as $locale) {
                    $tmpKey = $point['intro_video_tmp'][$locale] ?? null;
                    if ($tmpKey) {
                        $introVideoPaths[$locale] = TusService::moveFromTemp($tmpKey, 'route_point_media');
                    }
                }
                $introAudioPaths = $point['intro_audio_paths'] ?? [];

                $model = $route->routePoints()->updateOrCreate(
                    ['id' => $point['id'] ?? null],
                    [
                        'locationable_type' => $point['locationable_type'],
                        'locationable_id' => $point['locationable_id'],
                        'order' => $index + 1,
                        'estimated_visit_minutes' => $point['estimated_visit_minutes'] ?? 15,
                        'storytelling_content' => $point['storytelling_content'] ?? null,
                        'intro_video_paths' => $introVideoPaths ?: null,
                        'intro_audio_paths' => $introAudioPaths ?: null,
                    ]
                );
                $keptPointIds[] = $model->id;

                $keptMissionIds = [];
                foreach (array_values($point['missions'] ?? []) as $mIndex => $mission) {
                    $order = $mIndex + 1;
                    $missionModel = $model->missions()->updateOrCreate(
                        ['id' => $mission['id'] ?? null],
                        [
                            'type' => $mission['type'],
                            'title' => $mission['title'] ?? ['en' => '', 'id' => ''],
                            'points' => $mission['points'] ?? 100,
                            'time_limit_seconds' => $mission['time_limit_seconds'] ?? null,
                            'config' => $mission['config'] ?? [],
                            'order' => $order,
                        ]
                    );
                    $keptMissionIds[] = $missionModel->id;
                }
                $model->missions()->whereNotIn('id', $keptMissionIds)->delete();
            }

            $route->routePoints()->whereNotIn('id', $keptPointIds)->delete();
        });
    }
}
