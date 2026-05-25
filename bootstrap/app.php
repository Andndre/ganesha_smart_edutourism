<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\RedirectIfAdmin;
use App\Http\Middleware\SetUserLocale;
use App\Http\Middleware\UmkmOwnerMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            SetUserLocale::class,
        ]);
        $middleware->alias([
            'admin' => AdminMiddleware::class,
            'redirect.admin' => RedirectIfAdmin::class,
            'umkm_owner' => UmkmOwnerMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
