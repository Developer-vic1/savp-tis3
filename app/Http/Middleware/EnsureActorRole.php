<?php

namespace App\Http\Middleware;

use App\Services\RoleDashboardResolver;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActorRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        $actor = $user ? app(RoleDashboardResolver::class)->roleFor($user) : null;
        abort_if(! $actor || ! in_array($actor, $roles, true), 403, 'No tienes autorización para acceder a este espacio de trabajo.');

        return $next($request);
    }
}
