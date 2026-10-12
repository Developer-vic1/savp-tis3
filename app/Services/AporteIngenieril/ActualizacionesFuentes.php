<?php

namespace App\Services\AporteIngenieril;

use App\Models\Oficial\Sistema\User;
use App\Services\NotificationService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\ValidationException;

class ActualizacionesFuentes
{
    public const CADENCIAS = ['MANUAL' => 'Solo cuando lo solicite', 'MENSUAL' => 'Cada mes', 'SEMESTRAL' => 'Cada seis meses', 'ANUAL' => 'Cada gestión (12 meses)'];

    public function __construct(private readonly AporteIngenierilClient $cliente) {}

    public function snapshot(): array
    {
        $fuentes = $this->fuentes();
        $estado = $this->leer();

        return ['cadences' => collect(self::CADENCIAS)->map(fn ($label, $value) => compact('value', 'label'))->values()->all(),
            'sources' => array_values(array_map(fn ($s) => $this->publica($s, $estado[$s['source_id']] ?? []), $fuentes)),
            'message' => 'La primera revisión establece una referencia. Los cambios posteriores requieren revisión antes de incorporarse.'];
    }

    public function configurar(string $id, string $cadencia): array
    {
        $this->fuente($id);
        if (! array_key_exists($cadencia, self::CADENCIAS)) {
            throw ValidationException::withMessages(['cadence' => 'Seleccione una frecuencia disponible.']);
        }
        Cache::lock('conocimiento:actualizaciones', 60)->block(2, function () use ($id, $cadencia) {
            $estado = $this->leer();
            $fila = $estado[$id] ?? [];
            $fila['cadence'] = $cadencia;
            $fila['next_check_at'] = isset($fila['last_checked_at']) ? $this->proxima($cadencia, Carbon::parse($fila['last_checked_at'])) : ($cadencia === 'MANUAL' ? null : now()->toIso8601String());
            $estado[$id] = $fila;
            $this->guardar($estado);
        });

        return $this->snapshot();
    }

    public function revisar(string $id): array
    {
        $fuente = $this->fuente($id);
        Cache::lock('conocimiento:actualizaciones', 60)->block(2, function () use ($id, $fuente) {
            $estado = $this->leer();
            $fila = $estado[$id] ?? ['cadence' => 'MANUAL'];
            $resultado = $this->cliente->revisarActualizacionFuente($id);
            $datos = $resultado->data;
            $fila['last_checked_at'] = now()->toIso8601String();
            $fila['next_check_at'] = $this->proxima($fila['cadence'] ?? 'MANUAL', now());
            $valida = $resultado->available && ($datos['source_id'] ?? null) === $id
                && ($datos['status'] ?? '') === 'VERIFICADA' && preg_match('/^[a-f0-9]{64}$/', (string) ($datos['fingerprint'] ?? ''));
            if (! $valida) {
                $fila['status'] = 'NO_DISPONIBLE';
                $fila['message'] = 'No se pudo comprobar la publicación. Conservamos la referencia anterior y puedes reintentar.';
                if ($resultado->available && is_string($datos['message'] ?? null)) {
                    $fila['message'] = $datos['message'];
                }
                if (($fila['cadence'] ?? 'MANUAL') !== 'MANUAL') {
                    $fila['next_check_at'] = now()->addDay()->toIso8601String();
                }
            } else {
                $huella = $datos['fingerprint'];
                $anterior = $fila['fingerprint'] ?? null;
                if ($anterior !== null && ! hash_equals($anterior, $huella)) {
                    $fila['change_detected'] = true;
                    $fila['pending_event'] = hash('sha256', $id.':'.$anterior.':'.$huella);
                    $fila['status'] = 'CAMBIO_DETECTADO';
                    $fila['message'] = 'La publicación cambió. Revisa sus datos antes de actualizar la biblioteca.';
                } elseif (! empty($fila['change_detected'])) {
                    $fila['status'] = 'CAMBIO_DETECTADO';
                    $fila['message'] = 'El cambio detectado sigue pendiente de revisión. La versión vigente se conserva.';
                } else {
                    $fila['status'] = 'SIN_CAMBIOS';
                    $fila['message'] = $anterior === null ? 'Referencia inicial guardada. Las próximas revisiones detectarán cambios.' : 'No encontramos cambios respecto a la última referencia.';
                }
                $fila['fingerprint'] = $huella;
            }
            $estado[$id] = $fila;
            $this->guardar($estado);
            if (! empty($fila['pending_event'])) {
                if ($this->avisar($fuente, $fila['pending_event'])) {
                    unset($estado[$id]['pending_event']);
                    $this->guardar($estado);
                }
            }
        });

        return $this->snapshot();
    }

    public function procesarPendientes(int $limite = 3): int
    {
        $fuentes = array_column($this->fuentes(), null, 'source_id');
        $procesadas = 0;
        foreach ($this->leer() as $id => $fila) {
            if ($procesadas >= max(1, min(3, $limite))) {
                break;
            }
            if (isset($fuentes[$id]) && ($fila['cadence'] ?? 'MANUAL') !== 'MANUAL'
                && ! empty($fila['next_check_at']) && Carbon::parse($fila['next_check_at'])->lte(now())) {
                $this->revisar($id);
                $procesadas++;
            }
        }

        return $procesadas;
    }

    protected function avisar(array $fuente, string $evento): bool
    {
        $usuarios = User::where('est_usu', 'ACTIVO')->whereHas('roles', fn ($q) => $q->where('name', 'Administrador'))->get()
            ->filter(fn ($u) => $u->can('conocimiento.revisar'));

        return app(NotificationService::class)->publicar([
            'clave_evento' => 'fuente:cambio:'.$evento, 'origen' => 'APORTE', 'tipo' => 'ACCION',
            'titulo' => 'Una publicación universitaria cambió',
            'mensaje' => mb_substr($fuente['title'], 0, 240).' · '.mb_substr($fuente['institution'], 0, 240).'. Revisa la publicación antes de incorporar sus cambios; la versión vigente se conserva.',
            'permiso' => 'conocimiento.ver', 'ruta' => 'conocimiento.fuentes.index',
        ], $usuarios) !== null;
    }

    private function fuentes(): array
    {
        $resultado = $this->cliente->knowledgeGovernance();
        if (! $resultado->available) {
            throw ValidationException::withMessages(['knowledge' => 'La biblioteca no está disponible. Reintente cuando vuelva el servicio.']);
        }
        $dominios = array_column($resultado->data['trusted_universities'] ?? [], 'domain');

        return array_values(array_filter($resultado->data['sources'] ?? [], function ($s) use ($dominios) {
            if (! is_array($s) || parse_url($s['url'] ?? '', PHP_URL_SCHEME) !== 'https') {
                return false;
            }
            $host = strtolower((string) parse_url($s['url'] ?? '', PHP_URL_HOST));

            return ($s['verification_status'] ?? '') === 'VALID' && is_string($s['source_id'] ?? null)
                && preg_match('/^[A-Za-z0-9_-]{1,160}$/', $s['source_id']) && collect($dominios)->contains(fn ($d) => $host === $d || str_ends_with($host, '.'.$d));
        }));
    }

    private function fuente(string $id): array
    {
        foreach ($this->fuentes() as $fuente) {
            if ($fuente['source_id'] === $id) {
                return $fuente;
            }
        }
        throw ValidationException::withMessages(['source_id' => 'Seleccione una fuente universitaria validada de la biblioteca.']);
    }

    private function publica(array $fuente, array $fila): array
    {
        return array_intersect_key($fuente, array_flip(['source_id', 'title', 'institution'])) + [
            'cadence' => $fila['cadence'] ?? 'MANUAL', 'last_checked_at' => $fila['last_checked_at'] ?? null,
            'next_check_at' => $fila['next_check_at'] ?? null, 'status' => $fila['status'] ?? 'SIN_REVISAR',
            'message' => $fila['message'] ?? 'Todavía no se ha establecido una referencia de revisión.',
        ];
    }

    private function proxima(string $cadencia, Carbon $desde): ?string
    {
        $desde = $desde->copy()->timezone('America/La_Paz');

        return match ($cadencia) {
            'MENSUAL' => $desde->copy()->addMonthNoOverflow()->toIso8601String(),
            'SEMESTRAL' => $desde->copy()->addMonthsNoOverflow(6)->toIso8601String(),
            'ANUAL' => $desde->copy()->addYearNoOverflow()->toIso8601String(), default => null,
        };
    }

    private function ruta(): string
    {
        return config('services.aporte_ingenieril.actualizaciones_path', storage_path('app/private/conocimiento/actualizaciones.json'));
    }

    private function leer(): array
    {
        if (! File::exists($this->ruta())) {
            return [];
        }
        $contenido = File::get($this->ruta());
        if (strlen($contenido) > 2 * 1024 * 1024) {
            throw new \RuntimeException('El registro de revisiones requiere inspección.');
        }
        $datos = json_decode($contenido, true, 32, JSON_THROW_ON_ERROR);
        if (! is_array($datos)) {
            throw new \RuntimeException('El registro de revisiones no es válido.');
        }

        return $datos;
    }

    private function guardar(array $datos): void
    {
        File::ensureDirectoryExists(dirname($this->ruta()));
        $temporal = $this->ruta().'.tmp';
        File::put($temporal, json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR), true);
        if (! File::move($temporal, $this->ruta())) {
            throw new \RuntimeException('No se pudo guardar la configuración de revisiones.');
        }
    }
}
