<?php

namespace App\Http\Controllers;

use App\Models\ArModel;

class ArViewerController extends Controller
{
    /**
     * Redirect to AR scanner page with model_id param to skip QR scan.
     */
    public function __invoke(int $id)
    {
        ArModel::findOrFail($id);

        return redirect()->route('ar-scan', ['model_id' => $id]);
    }
}
