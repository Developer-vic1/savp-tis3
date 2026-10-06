<?php

namespace App\Support\Seguridad;

use Closure;
use Illuminate\Contracts\Cache\Factory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;

class LimitadorIntentosAcceso
{
    public function __construct(private Factory $cache) {}

    public function clave(Request $request): string
    {
        $identificador = $request->input(Fortify::username());
        $identificador = is_string($identificador) ? Str::lower(trim($identificador)) : '';

        return 'acceso:'.hash('sha256', $identificador.'|'.$request->ip());
    }

    public function serializar(string $clave, Closure $accion): mixed
    {
        return $this->almacen()->lock($clave.':cerrojo', 30)->block(3, $accion);
    }

    public function estado(string $clave): array
    {
        $estado = $this->almacen()->get($clave, ['fallos' => 0, 'nivel' => 0, 'hasta' => 0]);
        $estado['restantes'] = max(0, $estado['hasta'] - now()->timestamp);

        return $estado;
    }

    // Llamar dentro de serializar para que peticiones concurrentes no pierdan fallos.
    public function registrarFallo(string $clave): array
    {
        $estado = $this->estado($clave);
        if ($estado['restantes'] > 0) {
            return $estado;
        }

        $estado['fallos']++;
        if ($estado['fallos'] >= config('seguridad-acceso.intentos')) {
            $estado['nivel']++;
            $pausa = min(
                config('seguridad-acceso.pausa_maxima'),
                config('seguridad-acceso.pausa_inicial') * (2 ** min($estado['nivel'] - 1, 20))
            );
            $estado['fallos'] = 0;
            $estado['hasta'] = now()->timestamp + (int) $pausa;
        }

        unset($estado['restantes']);
        $this->almacen()->put($clave, $estado, config('seguridad-acceso.vigencia'));

        return $this->estado($clave);
    }

    public function limpiar(string $clave): void
    {
        $this->almacen()->forget($clave);
    }

    private function almacen()
    {
        return $this->cache->store(config('seguridad-acceso.cache'));
    }
}
