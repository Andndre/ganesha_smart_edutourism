<?php

namespace App\Http\Controllers;

use App\Models\ArModel;

class ArViewerController extends Controller
{
    /**
     * Redirect to AR scanner page with model_id param to skip QR scan.
     */
    public function __invoke(string $arMarkerId)
    {
        $model = ArModel::where('ar_marker_id', $arMarkerId)->firstOrFail();

        return redirect()->route('ar-scan', ['model_id' => $model->id]);
    }
}