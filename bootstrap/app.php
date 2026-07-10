<?php

use App\Http\Middleware\RedirectByRole;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\EnsureKasirShiftIsActive;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\EnsureLocationVerified;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        $middleware->preventRequestForgery(except: [
            'midtrans/notification',
        ]);  
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'redirect.role' => RedirectByRole::class,
            'shift.active' => EnsureKasirShiftIsActive::class,
            'location.verified' => EnsureLocationVerified::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {

    })->create();
