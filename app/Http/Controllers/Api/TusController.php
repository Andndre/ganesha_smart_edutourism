<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use TusPhp\Cache\FileStore;
use TusPhp\Tus\Server;

class TusController extends Controller
{
    public function handle()
    {
        // Ensure HTTPS awareness when behind reverse proxy (Traefik / Nginx)
        if (
            (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
            || (isset($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on')
            || str_starts_with(config('app.url'), 'https://')
        ) {
            $_SERVER['HTTPS'] = 'on';
            $_SERVER['SERVER_PORT'] = '443';
            SymfonyRequest::setTrustedProxies(
                ['127.0.0.1', '10.0.0.0/8', '172.16.0.0/12', '192.168.0.0/16'],
                SymfonyRequest::HEADER_X_FORWARDED_FOR | SymfonyRequest::HEADER_X_FORWARDED_HOST | SymfonyRequest::HEADER_X_FORWARDED_PORT | SymfonyRequest::HEADER_X_FORWARDED_PROTO
            );
        }

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

        $headers = $response->headers->all();
        // Force Location header to https if app.url is https
        if (str_starts_with(config('app.url'), 'https://')) {
            if (isset($headers['location'][0])) {
                $headers['location'][0] = preg_replace('/^http:\/\//i', 'https://', $headers['location'][0]);
            }
            if (isset($headers['Location'][0])) {
                $headers['Location'][0] = preg_replace('/^http:\/\//i', 'https://', $headers['Location'][0]);
            }
        }

        return response(
            $response->getContent(),
            $response->getStatusCode(),
            $headers
        );
    }
}
