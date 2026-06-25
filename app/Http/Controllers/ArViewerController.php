<?php

namespace App\Http\Controllers;

use App\Models\ArModel;

class ArViewerController extends Controller
{
    /**
     * Show standalone 3D model viewer page.
     */
    public function __invoke(int $id)
    {
        $model = ArModel::with('mapLocation.locationable')->findOrFail($id);

        $locationable = $model->mapLocation?->locationable;
        $model->resolved_name = $locationable?->name ?? $model->name;
        $model->resolved_description = $locationable?->description ?? $model->description;
        $model->resolved_short_description = $locationable?->short_description ?? $model->name;

        return view('user.ar.viewer', compact('model'));
    }
}
