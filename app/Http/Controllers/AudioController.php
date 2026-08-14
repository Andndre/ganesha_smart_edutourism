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
        // The route pattern is `.*`, so `$path` can contain `..` segments. Resolve both sides and
        // confirm the result is still inside public storage before touching the filesystem —
        // this endpoint is unauthenticated.
        $root = realpath(storage_path('app/public'));
        $fullPath = realpath(storage_path('app/public/'.$path));

        if ($root === false || $fullPath === false || ! str_starts_with($fullPath, $root.\DIRECTORY_SEPARATOR)) {
            abort(404);
        }

        // BinaryFileResponse automatically handles HTTP range requests (Accept-Ranges: bytes)
        $response = new BinaryFileResponse($fullPath);
        BinaryFileResponse::trustXSendfileTypeHeader();

        return $response;
    }
}
