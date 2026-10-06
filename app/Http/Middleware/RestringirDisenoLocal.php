<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class RestringirDisenoLocal
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! app()->environment('local') || ! config('diseno-local.habilitado')
            || ! is_string(config('diseno-local.clave_hash')) || ! config('diseno-local.clave_hash')
            || ! in_array($request->server('REMOTE_ADDR'), ['127.0.0.1', '::1'], true)
            || ! in_array($request->getHost(), ['localhost', '127.0.0.1', '::1', '[::1]'], true)) {
            return response('Vista de diseño no disponible.', 410);
        }

        $respuesta = $next($request);
        $respuesta->headers->set('Cache-Control', 'no-store, private');
        $respuesta->headers->set('X-Robots-Tag', 'noindex, nofollow');
        return $respuesta;
    }
}
