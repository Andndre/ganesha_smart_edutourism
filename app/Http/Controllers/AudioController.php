<?php

namespace App\Http\Controllers;

use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AudioController extends Controller
{
    /**
     * Stream an audio file from public storage to support concurrent range requests (seeking) in Google Chrome.
     */
    public function stream(string $path): BinaryFileResponse
    {
        $fullPath = storage_path('app/public/'.$path);

        if (! file_exists($fullPath)) {
            abort(404);
        }

        // BinaryFileResponse automatically handles HTTP range requests (Accept-Ranges: bytes)
        $response = new BinaryFileResponse($fullPath);
        BinaryFileResponse::trustXSendfileTypeHeader();

        return $response;
    }
}
