<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActorRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        abort_if(! $user || $user->est_usu !== 'ACTIVO' || ! $user->hasAnyRole($roles), 403, 'No tienes autorización para acceder a este espacio de trabajo.');

        return $next($request);
    }
}
