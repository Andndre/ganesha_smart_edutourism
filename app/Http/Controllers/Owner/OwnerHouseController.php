<?php

namespace App\Http\Controllers\Owner;

use App\Http\Requests\Owner\OwnerHouseRequest;
use App\Models\CulturalObject;
use App\Services\TraditionalHouseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OwnerHouseController extends BaseOwnerController
{
    public function edit(): View
    {
        $house = $this->profile?->managedHouse();
        abort_unless($house, 403);
        $house->load(['mapLocation', 'umkmProfiles', 'culturalObjects']);
        $objects = CulturalObject::orderBy('name->'.app()->getLocale())->get();

        return view('admin.houses.form', [
            'house' => $house, 'profiles' => $house->umkmProfiles,
            'objects' => $objects, 'owners' => collect(), 'isOwner' => true, 'profile' => $this->profile,
        ]);
    }

    public function update(OwnerHouseRequest $request, TraditionalHouseService $service): RedirectResponse
    {
        $service->save($request, $request->house());

        return redirect()->route('owner.house')->with('success', 'Data rumah Anda berhasil diperbarui.');
    }
}
