<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use TusPhp\Tus\Server;
use TusPhp\Cache\FileStore;

class TusController extends Controller
{
    public function handle()
    {
        $server = new Server('file');

        // Ensure dirs exist
        $uploadDir = storage_path('app/tus/temp');
        $cacheDir = storage_path('app/tus/cache');
        if (! is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        if (! is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        $server->setApiPath('/admin/api/tus/upload');
        $server->setUploadDir($uploadDir);

        $cache = new FileStore($cacheDir);
        $cache->setTtl(86400); // 24 hours
        $server->setCache($cache);

        $server->setMaxUploadSize(52428800); // 50MB

        $response = $server->serve();

        // Convert Symfony response to Laravel response
        return response(
            $response->getContent(),
            $response->getStatusCode(),
            $response->headers->all()
        );
    }
}
