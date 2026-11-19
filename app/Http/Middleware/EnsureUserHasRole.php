<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // An administrator opening the parishioner /dashboard lands on their own dashboard.
        if ($request->is('dashboard') && $request->user()?->isAdministrator()) {
            return redirect()->route('admin.dashboard');
        }

        abort_unless($request->user()?->role === $role, 403);

        return $next($request);
    }
}
