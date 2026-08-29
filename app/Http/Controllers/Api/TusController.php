<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use TusPhp\Cache\FileStore;
use TusPhp\Tus\Server;

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

        // Trailing slash required — FileStore joins dir + filename without separator
        $cache = new FileStore($cacheDir.'/');
        $cache->setTtl(86400);
        $server->setCache($cache);

        $server->setMaxUploadSize(209715200); // 200 MB — covers intro videos, not just AR models

        $response = $server->serve();

        // tus-php membangun Location dari $_SERVER-nya sendiri, di luar TrustProxies Laravel.
        // Paksa https supaya browser tidak kena mixed-content saat di belakang Traefik.
        // HeaderBag::all() selalu lowercase key.
        $headers = $response->headers->all();
        if (str_starts_with(config('app.url'), 'https://') && isset($headers['location'][0])) {
            $headers['location'][0] = preg_replace('/^http:\/\//i', 'https://', $headers['location'][0]);
        }

        return response(
            $response->getContent(),
            $response->getStatusCode(),
            $headers
        );
    }
}
