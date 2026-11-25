<?php

namespace App\Http\Controllers\Owner;

use App\Http\Requests\Owner\OwnerHouseRequest;
use App\Models\VillageSettings;
use App\Services\TraditionalHouseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OwnerHouseController extends BaseOwnerController
{
    public function edit(): View
    {
        $house = $this->profile?->managedHouse();
        abort_unless($house, 403);
        $house->load(['mapLocation', 'umkmProfile']);
        $houseCulture = VillageSettings::get()->houseCulturalObject()->roots()->with('children')->first();

        return view('admin.houses.form', [
            'house' => $house,
            'houseCulture' => $houseCulture, 'owners' => collect(), 'isOwner' => true, 'profile' => $this->profile,
        ]);
    }

    public function update(OwnerHouseRequest $request, TraditionalHouseService $service): RedirectResponse
    {
        $service->save($request, $request->house());

        return redirect()->route('owner.house')->with('success', 'Data rumah Anda berhasil diperbarui.');
    }
}
