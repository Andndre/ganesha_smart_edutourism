<?php

namespace App\Http\Controllers\Admin;

use App\Http\Concerns\ExtractsGeoFields;
use App\Http\Concerns\NormalizesMultilingualInput;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FacilityRequest;
use App\Models\Facility;
use Illuminate\Http\RedirectResponse;

class FacilityController extends Controller
{
    use ExtractsGeoFields;
    use NormalizesMultilingualInput;

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

        return redirect()->route('admin.map-manager')->with('success', __('Fasilitas berhasil ditambahkan.'));
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

        return redirect()->route('admin.map-manager')->with('success', __('Fasilitas berhasil diperbarui.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Facility $facility): RedirectResponse
    {
        $facility->delete();

        return redirect()->route('admin.map-manager')->with('success', __('Fasilitas berhasil dihapus.'));
    }
}
