<?php

namespace App\Http\Controllers;

use App\Models\ArModel;
use App\Models\CulturalObject;
use App\Models\TraditionalHouse;
use Illuminate\Http\RedirectResponse;

class ArScanController extends Controller
{
    /**
     * Redirect based on AR marker relationship.
     *
     * Resolves ar_marker_id from ArModel → smart redirect:
     * - CulturalObject → cultural-object page
     * - Otherwise → ar-viewer page
     */
    public function __invoke(string $arMarkerId): RedirectResponse
    {
        $house = TraditionalHouse::published()->where('ar_marker_id', $arMarkerId)->first();
        if ($house) {
            return redirect()->route('houses.show', ['number' => $house->number]);
        }

        $arModel = ArModel::with('culturalObject')
            ->where('ar_marker_id', $arMarkerId)
            ->first();

        if (! $arModel) {
            abort(404);
        }

        if ($arModel->culturalObject instanceof CulturalObject) {
            return redirect()->route('cultural-object', ['slug' => $arModel->culturalObject->slug]);
        }

        return redirect()->route('ar-viewer', ['arMarkerId' => $arModel->ar_marker_id]);
    }
}
