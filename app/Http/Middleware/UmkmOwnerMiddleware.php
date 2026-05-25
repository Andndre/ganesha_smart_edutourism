<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UmkmOwnerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->isUmkmOwner()) {
            return $next($request);
        }

        abort(403, 'Akses ditolak. Halaman ini hanya untuk Pemilik UMKM.');
    }
}
