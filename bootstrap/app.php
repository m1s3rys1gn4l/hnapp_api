<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'firebase.auth' => \App\Http\Middleware\FirebaseAuthMiddleware::class,
            'admin.auth' => \App\Http\Middleware\AdminAuthMiddleware::class,
                'check.expired' => \App\Http\Middleware\CheckExpiredSubscriptions::class,
        ]);
        
            // Apply subscription expiry check globally to API routes
            $middleware->api(append: [
                \App\Http\Middleware\CheckExpiredSubscriptions::class,
            ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
