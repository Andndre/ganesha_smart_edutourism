<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RouteMissionAssetController extends Controller
{
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            // mimetypes (content-sniffed) + extensions (filename) instead of `mimes`: .m4a
            // is frequently sniffed as audio/mp4 or video/mp4, which `mimes:m4a` rejects.
            'file' => [
                'required', 'file', 'max:10240', // 10 MB
                'mimetypes:image/jpeg,image/png,image/webp,image/gif,audio/mpeg,audio/ogg,audio/wav,audio/x-wav,audio/mp4,audio/x-m4a,video/mp4',
                'extensions:jpg,jpeg,png,webp,gif,mp3,ogg,wav,m4a',
            ],
        ]);

        $path = $request->file('file')->store('mission_assets', 'public');

        return response()->json(['url' => Storage::disk('public')->url($path), 'path' => $path]);
    }
}
