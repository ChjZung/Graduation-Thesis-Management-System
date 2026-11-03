<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->redirectGuestsTo(fn (Request $request) => ($request->expectsJson() || $request->ajax()) ? null : route('login'));
        $middleware->web(append: [
            \App\Http\Middleware\EnsurePasswordStatus::class,
            \App\Http\Middleware\MockTimeMiddleware::class,
        ]);
        $middleware->alias([
            'role'            => \App\Http\Middleware\CheckRole::class,
            'password.status' => \App\Http\Middleware\EnsurePasswordStatus::class,
        ]);
    })
    ->withProviders([
        \App\Providers\AuthServiceProvider::class,
    ])

    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson() || $request->ajax(),
        );
    })->create();
