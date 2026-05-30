<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->isAdmin()) {
            return redirect()->route('admin.dashboard');
        } elseif ($request->user()?->isTicketOfficer()) {
            return redirect()->route('staff.ticketing');
        } elseif ($request->user()?->isUmkmOwner()) {
            return redirect()->route('owner.dashboard');
        }

        return $next($request);
    }
}
