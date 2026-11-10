<?php

use App\Http\Middleware\EnsureUserHasRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Vercel terminates HTTPS at its proxy; trust its X-Forwarded-* headers
        // so generated URLs (CSS/JS assets, redirects) use https://.
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'role' => EnsureUserHasRole::class,
        ]);
        $middleware->validateCsrfTokens(except: [
            'webhooks/paymongo',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
