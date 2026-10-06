<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class VerificarCuentaActiva
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && $request->user()->est_usu !== 'ACTIVO') {
            return $this->rechazar($request);
        }
        $respuesta = $next($request);
        // También impide completar un desafío 2FA iniciado antes de retirar el acceso.
        if ($request->user() && $request->user()->est_usu !== 'ACTIVO') {
            return $this->rechazar($request);
        }

        return $respuesta;
    }

    private function rechazar(Request $request): Response
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $mensaje = 'Tu cuenta no tiene acceso habilitado. Comunícate con administración.';

        return $request->expectsJson()
            ? response()->json(['message' => $mensaje], 403)
            : redirect()->route('login')->withErrors(['email' => $mensaje]);
    }
}
