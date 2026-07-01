<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Container\EntryNotFoundException;
use Illuminate\Contracts\Container\CircularDependencyException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Response;

class SetUserLocale
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure(Request): (Response) $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->has('locale')) {
            $locale = $request->query('locale');
            if (\in_array($locale, ['en', 'id'])) {
                session()->put('locale', $locale);
                if (Auth::check()) {
                    Auth::user()->update(['preferred_language' => $locale]);
                }
            }
        }

        if (Auth::check()) {
            App::setLocale(Auth::user()->preferred_language ?? 'id');
        } elseif (session()->has('locale')) {
            try {
                App::setLocale(session()->get('locale'));
            } catch (EntryNotFoundException|CircularDependencyException|NotFoundExceptionInterface|ContainerExceptionInterface $e) {

            }
        } else {
            App::setLocale(config('app.locale'));
        }

        return $next($request);
    }
}
