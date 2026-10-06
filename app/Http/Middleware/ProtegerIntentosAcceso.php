<?php

namespace App\Http\Middleware;

use App\Support\Seguridad\LimitadorIntentosAcceso;
use Closure;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Fortify;
use Symfony\Component\HttpFoundation\Response;

class ProtegerIntentosAcceso
{
    public function __construct(private LimitadorIntentosAcceso $limitador) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isMethod('post') || ! $request->routeIs('login.store')) {
            return $next($request);
        }

        $clave = $this->limitador->clave($request);

        try {
            return $this->limitador->serializar($clave, function () use ($clave, $request, $next) {
                $estado = $this->limitador->estado($clave);
                if ($estado['restantes'] > 0) {
                    return $this->bloqueado($request, $estado['restantes']);
                }

                try {
                    $respuesta = $next($request);
                } catch (ValidationException $error) {
                    return $this->fallido($request, $clave, $error);
                }

                // Routing\Pipeline puede renderizar la excepción dentro de $next.
                if (($respuesta->exception ?? null) instanceof ValidationException) {
                    return $this->fallido($request, $clave, $respuesta->exception);
                }

                if ($respuesta->getStatusCode() < 400) {
                    $this->limitador->limpiar($clave);
                    $request->session()->forget('acceso_bloqueado_hasta');
                }

                return $respuesta;
            });
        } catch (LockTimeoutException) {
            return $this->bloqueado($request, 3);
        }
    }

    private function fallido(Request $request, string $clave, ValidationException $error): Response
    {
        $estado = $this->limitador->registrarFallo($clave);
        if ($estado['restantes'] > 0) {
            return $this->bloqueado($request, $estado['restantes']);
        }

        $errores = $error->errors();
        $restantes = config('seguridad-acceso.intentos') - $estado['fallos'];
        $aviso = "Te quedan {$restantes} intentos antes de una pausa de seguridad.";
        $errores[Fortify::username()][] = $aviso;
        $request->session()->flash('acceso_intentos_restantes', $restantes);

        if ($request->expectsJson()) {
            return response()->json(['message' => $aviso, 'errors' => $errores, 'attempts_remaining' => $restantes], 422);
        }

        return redirect()->route('login')->withErrors($errores)->withInput($this->datosConservados($request));
    }

    private function datosConservados(Request $request): array
    {
        $identificador = $request->input(Fortify::username());

        return [Fortify::username() => is_string($identificador) ? $identificador : '', 'remember' => (int) $request->boolean('remember')];
    }

    private function bloqueado(Request $request, int $segundos): Response
    {
        $mensaje = "Por seguridad, espera {$segundos} segundos antes de volver a intentar.";
        $request->session()->put('acceso_bloqueado_hasta', now()->timestamp + $segundos);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $mensaje,
                'errors' => [Fortify::username() => [$mensaje]],
                'retry_after' => $segundos,
            ], 429, ['Retry-After' => $segundos]);
        }

        return redirect()->route('login')
            ->withErrors([Fortify::username() => $mensaje])
            ->withInput($this->datosConservados($request))
            ->header('Retry-After', (string) $segundos);
    }
}
