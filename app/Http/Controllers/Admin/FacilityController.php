<?php

namespace App\Http\Controllers\Admin;

use App\Http\Concerns\ExtractsGeoFields;
use App\Http\Concerns\NormalizesMultilingualInput;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FacilityRequest;
use App\Models\Facility;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FacilityController extends Controller
{
    use ExtractsGeoFields;
    use NormalizesMultilingualInput;

    /**
     * Display a listing of facilities (dedicated management page, separate from map-manager).
     */
    public function index(Request $request): View
    {
        $query = Facility::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(fn ($q) => $q->where('name->en', 'like', '%'.$search.'%')
                ->orWhere('name->id', 'like', '%'.$search.'%'));
        }

        $facilities = $query->with('mapLocation')->withCount('mapLocations')->orderBy('name->'.app()->getLocale())->paginate(15)->withQueryString();

        return view('admin.facilities.index', compact('facilities'));
    }

    /**
     * Show the form for creating a new facility.
     */
    public function create(): View
    {
        return view('admin.facilities.create');
    }

    /**
     * Show the form for editing an existing facility.
     */
    public function edit(Facility $facility): View
    {
        $facility->load('mapLocation');

        return view('admin.facilities.edit', compact('facility'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(FacilityRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $geo = $this->extractGeoFields($request, $validated);

        $validated['is_active'] = $request->has('is_active');

        $facility = Facility::create($validated);

        // TODO: map_locations.name hanya dipakai di admin AR manager — pertimbangkan untuk drop column
        $facility->syncMapLocation(['category' => 'facility', ...$geo]);

        return $this->redirectAfterSave($request, __('Fasilitas berhasil ditambahkan.'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(FacilityRequest $request, Facility $facility): RedirectResponse
    {
        $validated = $request->validated();

        $geo = $this->extractGeoFields($request, $validated);

        $validated['is_active'] = $request->has('is_active');

        $facility->update($validated);

        $facility->syncMapLocation(['category' => 'facility', ...$geo], isUpdate: true);

        return $this->redirectAfterSave($request, __('Fasilitas berhasil diperbarui.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Facility $facility): RedirectResponse
    {
        $facility->delete();

        return $this->redirectAfterSave($request, __('Fasilitas berhasil dihapus.'));
    }

    /**
     * Redirect to the dedicated management page when the request came from there
     * (via a `redirect_to=facilities` hidden field), otherwise back to map-manager.
     */
    private function redirectAfterSave(Request $request, string $message): RedirectResponse
    {
        $route = $request->input('redirect_to') === 'facilities' ? 'admin.facilities' : 'admin.map-manager';

        return redirect()->route($route)->with('success', $message);
    }
}
