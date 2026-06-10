<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(self)');
        $response->headers->remove('X-Powered-By');

        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        $scriptSrc = "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://unpkg.com https://cdnjs.cloudflare.com https://esm.sh https://app.midtrans.com https://api.midtrans.com";
        $styleSrc = "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net https://unpkg.com https://cdnjs.cloudflare.com";
        $fontSrc = "font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com";
        $connectSrc = "connect-src 'self' https://nominatim.openstreetmap.org https://router.project-osrm.org https://overpass-api.de https://api.midtrans.com https://app.midtrans.com";

        if (app()->environment('local')) {
            $scriptSrc .= ' http://127.0.0.1:5173 http://localhost:5173';
            $styleSrc .= ' http://127.0.0.1:5173 http://localhost:5173';
            $fontSrc .= ' http://127.0.0.1:5173 http://localhost:5173';
            $connectSrc .= ' http://127.0.0.1:5173 http://localhost:5173 ws://127.0.0.1:5173 ws://localhost:5173 https://cdn.jsdelivr.net';
        }

        $response->headers->set(
            'Content-Security-Policy',
            implode('; ', [
                "default-src 'self'",
                $scriptSrc,
                $styleSrc,
                $fontSrc,
                "img-src 'self' data: blob: https:",
                "media-src 'self' blob:",
                $connectSrc,
                "frame-src 'self' https://app.midtrans.com https://api.midtrans.com",
                "worker-src 'self' blob:",
                "object-src 'none'",
            ])
        );

        return $response;
    }
}
