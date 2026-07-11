<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminOrViewerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Allows full admins through unrestricted. Allows admin_viewer through
     * for read-only (GET/HEAD) requests only; any mutating method is blocked.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user?->isAdmin()) {
            return $next($request);
        }

        if ($user?->isAdminViewer()) {
            if ($request->isMethod('GET') || $request->isMethod('HEAD')) {
                return $next($request);
            }

            abort(403, 'Akun ini hanya memiliki akses lihat (view-only).');
        }

        abort(403, 'Akses ditolak. Halaman ini hanya untuk Administrator.');
    }
}
