<?php

use App\Http\Middleware\EnsureActorRole;
use App\Http\Middleware\VerificarCuentaActiva;
use App\Support\Interfaz\ErrorInstitucional;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [VerificarCuentaActiva::class]);
        $middleware->alias([
            'actor' => EnsureActorRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->respond(function (Response $response) {
            $estado = $response->getStatusCode();
            if (! in_array($estado, [400, 401, 403, 404, 405, 408, 419, 429, 500, 502, 503, 504], true)) {
                return $response;
            }
            $request = request();
            if ($request->expectsJson() || $request->hasHeader('X-Livewire')) {
                if ($estado >= 500) {
                    return response()->json(['message' => 'No pudimos completar la operación. Tus datos escritos siguen disponibles; vuelve a intentarlo o contacta con soporte.'], $estado);
                }

                return $response;
            }

            $mostrar = fn () => response()->view('errors.institucional', ErrorInstitucional::contexto($request, $estado), $estado, array_intersect_key($response->headers->all(), array_flip(['retry-after', 'allow'])));
            // Las rutas inexistentes no recorren el middleware web: recupera la sesión para personalizar la ayuda.
            if (! $request->hasSession() || ! $request->session()->isStarted()) {
                try {
                    return app(\Illuminate\Pipeline\Pipeline::class)->send($request)->through([
                        \Illuminate\Cookie\Middleware\EncryptCookies::class,
                        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
                        \Illuminate\Session\Middleware\StartSession::class,
                    ])->then($mostrar);
                } catch (\Throwable) {
                    return $mostrar();
                }
            }
            return $mostrar();
        });
    })->create();
