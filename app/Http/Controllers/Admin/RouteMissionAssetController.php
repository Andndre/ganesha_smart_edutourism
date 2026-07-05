<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RouteMissionAssetController extends Controller
{
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'image', 'max:5120'], // 5 MB
        ]);

        $path = $request->file('file')->store('mission_assets', 'public');

        return response()->json(['url' => '/storage/'.$path]);
    }
}
