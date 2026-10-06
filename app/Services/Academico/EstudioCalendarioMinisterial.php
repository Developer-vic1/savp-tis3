<?php

namespace App\Services\Academico;

use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\Process\Process;
use Throwable;

final class EstudioCalendarioMinisterial
{
    public function directorio(): string
    {
        $ruta = storage_path('app/private/estudio-calendario');
        File::ensureDirectoryExists($ruta);
        return $ruta;
    }

    public function leer(int $anio): array
    {
        abort_unless($anio >= 2020 && $anio <= 2100, 422, 'Selecciona un año válido.');
        $ruta = $this->directorio().'/'.$anio.'.json';
        return is_file($ruta) ? (json_decode(File::get($ruta), true) ?: []) : [];
    }

    private function guardar(int $anio, array $datos): void
    {
        $ruta = $this->directorio().'/'.$anio.'.json';
        $temporal = $ruta.'.'.bin2hex(random_bytes(5)).'.tmp';
        File::put($temporal, json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
        File::move($temporal, $ruta);
    }

    private function validarResultado(array $resultado, int $anio): void
    {
        Validator::make($resultado, [
            'anio' => ['required', 'integer', 'in:'.$anio],
            'estado' => ['required', 'in:RESULTADO,SIN_PUBLICACION,REVISION_REQUERIDA,SIN_CONEXION,FUENTE_NO_DISPONIBLE,NO_DISPONIBLE'],
            'mensaje' => ['required', 'string', 'max:1000'],
        ])->validate();
        if ($resultado['estado'] !== 'RESULTADO') {
            return;
        }
        Validator::make($resultado, [
            'resultado.inicio' => ['required', 'date_format:Y-m-d', 'before:resultado.cierre'],
            'resultado.cierre' => ['required', 'date_format:Y-m-d'],
            'resultado.url' => ['required', 'url:https', 'max:2000'],
            'resultado.documento' => ['required', 'string', 'max:200'],
            'resultado.paginas' => ['required', 'array', 'min:1'],
            'resultado.paginas.*' => ['integer', 'min:1', 'max:120'],
            'resultado.sha256' => ['required', 'regex:/^[a-f0-9]{64}$/'],
        ])->validate();
        $datos = $resultado['resultado'];
        $url = parse_url($datos['url']);
        if (! in_array($url['host'] ?? '', ['minedu.gob.bo', 'www.minedu.gob.bo'], true)
            || isset($url['user']) || isset($url['pass'])
            || ! in_array($url['port'] ?? 443, [443], true)
            || substr($datos['inicio'], 0, 4) !== (string) $anio || substr($datos['cierre'], 0, 4) !== (string) $anio) {
            throw new \RuntimeException('La fuente o el año del estudio no son válidos.');
        }
    }

    public function solicitar(int $anio): array
    {
        $actual = $this->leer($anio);
        if (in_array($actual['estado'] ?? '', ['EN_COLA', 'INVESTIGANDO'], true)) {
            $this->asegurarTrabajador();
            return $actual;
        }
        try {
            $estado = Cache::store('file')->lock('academica-estudio-'.$anio, 90)->block(2, function () use ($anio) {
            $actual = $this->leer($anio);
            if (in_array($actual['estado'] ?? '', ['EN_COLA', 'INVESTIGANDO'], true) || (($actual['estado'] ?? '') === 'RESULTADO' && ($actual['consultado'] ?? 0) > time() - 3600)) {
                return $actual;
            }
            $datos = ['anio' => $anio, 'estado' => 'EN_COLA', 'mensaje' => 'El estudio está en cola. Puedes seguir trabajando.', 'proximo_intento' => time(), 'consultado' => $actual['consultado'] ?? 0];
            $this->guardar($anio, $datos);
            return $datos;
            });
        } catch (LockTimeoutException) {
            $estado = $this->leer($anio);
        }
        $this->asegurarTrabajador();
        return $estado;
    }

    public function asegurarTrabajador(): void
    {
        if (! app()->environment('local') || PHP_OS_FAMILY !== 'Windows' || ! config('calendario-estudio.arranque_local')) {
            return;
        }
        $latido = $this->directorio().'/latido';
        if (is_file($latido) && (int) File::get($latido) > time() - 90) {
            return;
        }
        if (! Cache::store('file')->add('academica-arranque-estudio', true, 15)) {
            return;
        }
        $php = str_ends_with(strtolower(PHP_BINARY), 'php-cgi.exe') ? dirname(PHP_BINARY).'/php.exe' : PHP_BINARY;
        $citar = fn (string $valor) => "'".str_replace("'", "''", $valor)."'";
        $temporal = $this->directorio().'/temporal';
        File::ensureDirectoryExists($temporal);
        $script = '$env:TEMP = '.$citar($temporal).'; $env:TMP = '.$citar($temporal).'; Start-Process -WindowStyle Hidden -FilePath '.$citar($php).' -ArgumentList @('.$citar('"'.base_path('artisan').'"').", 'academica:estudiar-calendario', '--seguir') -WorkingDirectory ".$citar(base_path()).' -RedirectStandardOutput '.$citar($this->directorio().'/trabajador.log').' -RedirectStandardError '.$citar($this->directorio().'/trabajador-error.log');
        try {
            $comando = base64_encode(mb_convert_encoding($script, 'UTF-16LE', 'UTF-8'));
            (new Process([config('calendario-estudio.powershell'), '-NoProfile', '-NonInteractive', '-EncodedCommand', $comando]))->setTimeout(5)->mustRun();
        } catch (Throwable $error) {
            report($error);
        }
    }

    public function procesarPendientes(): void
    {
        File::put($this->directorio().'/latido', (string) time());
        foreach (File::glob($this->directorio().'/*.json') as $ruta) {
            if (! preg_match('/^(20\d{2}|2100)\.json$/', basename($ruta))) {
                continue;
            }
            $anio = (int) pathinfo($ruta, PATHINFO_FILENAME);
            $lock = Cache::store('file')->lock('academica-estudio-'.$anio, 90);
            if (! $lock->get()) {
                continue;
            }
            try {
                $estado = $this->leer($anio);
                if (($estado['estado'] ?? '') === 'RESULTADO' || ($estado['proximo_intento'] ?? PHP_INT_MAX) > time()) {
                    continue;
                }
                $this->guardar($anio, array_merge($estado, ['estado' => 'INVESTIGANDO', 'mensaje' => 'Revisando publicaciones oficiales y sus fechas…']));
                try {
                    $proceso = new Process([config('calendario-estudio.python'), base_path('scripts/academico/estudiar_calendario.py'), '--anio', (string) $anio], base_path(), ['PYTHONIOENCODING' => 'utf-8']);
                    $proceso->setTimeout(60)->mustRun();
                    $resultado = json_decode($proceso->getOutput(), true, flags: JSON_THROW_ON_ERROR);
                    $this->validarResultado($resultado, $anio);
                } catch (Throwable $error) {
                    report($error);
                    $resultado = ['anio' => $anio, 'estado' => 'NO_DISPONIBLE', 'mensaje' => 'No pudimos completar el estudio. El pendiente se conserva y volveremos a intentarlo.'];
                }
                $espera = match ($resultado['estado']) {
                    'SIN_CONEXION', 'FUENTE_NO_DISPONIBLE' => 60,
                    'SIN_PUBLICACION', 'REVISION_REQUERIDA' => 21600,
                    default => 3600,
                };
                $this->guardar($anio, $resultado + ['consultado' => time(), 'proximo_intento' => time() + $espera]);
                File::put($this->directorio().'/latido', (string) time());
            } finally {
                $lock->release();
            }
        }
    }

    private function rutaAviso(string $usuario, int $anio): string
    {
        return $this->directorio().'/aviso-'.hash('sha256', $usuario).'-'.$anio.'.json';
    }

    public function suscribir(string $usuario, int $anio): void
    {
        $this->leer($anio);
        File::put($this->rutaAviso($usuario, $anio), json_encode(['avisar' => true, 'visto' => 0]), true);
    }

    public function aviso(string $usuario, int $anio): array
    {
        $ruta = $this->rutaAviso($usuario, $anio);
        return is_file($ruta) ? (json_decode(File::get($ruta), true) ?: []) : [];
    }

    public function marcarAvisado(string $usuario, int $anio, int $version): void
    {
        File::put($this->rutaAviso($usuario, $anio), json_encode(['avisar' => true, 'visto' => $version]), true);
    }
}
