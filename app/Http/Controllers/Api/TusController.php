<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use TusPhp\Tus\Server;
use TusPhp\Cache\FileStore;

class TusController extends Controller
{
    public function handle()
    {
        $tempDir = storage_path('app/tus/temp');
        $cacheDir = storage_path('app/tus/cache');
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        if (! is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        $server = new Server('file');
        $server->setApiPath('/admin/api/tus/upload');
        $server->setUploadDir($tempDir);

        $cache = new FileStore($cacheDir);
        $cache->setTtl(86400);
        $server->setCache($cache);

        $server->setMaxUploadSize(52428800);

        $response = $server->serve();

        return response(
            $response->getContent(),
            $response->getStatusCode(),
            $response->headers->all()
        );
    }
}
