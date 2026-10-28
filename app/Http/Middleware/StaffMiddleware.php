<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StaffMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Allows admin and ticket_officer unrestricted access.
     * Allows admin_viewer read-only (GET/HEAD) access only.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ($user->isAdmin() || $user->isTicketOfficer())) {
            return $next($request);
        }

        if ($user?->isAdminViewer()) {
            if ($request->isMethod('GET') || $request->isMethod('HEAD')) {
                return $next($request);
            }

            abort(403, 'Akun ini hanya memiliki akses lihat (view-only).');
        }

        abort(403, 'Akses ditolak. Halaman ini hanya untuk Staff/Administrator.');
    }
}
