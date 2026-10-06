<?php

namespace App\Support;

use App\Models\Oficial\Sistema\{Role, User};
use App\Services\{InstitutionalQueryService, RoleDashboardResolver};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/** Describe puertas reales; no sustituye middleware, políticas ni ámbitos de datos. */
final class CatalogoVentanasPermisos
{
    public function ventanas(User $operador): array
    {
        $ventanas = [];
        $actorActual = app(RoleDashboardResolver::class)->roleFor($operador);
        foreach (Role::INSTITUTIONAL as $actor) {
            foreach (app(WorkspaceNavigation::class)->catalogo($actor) as $link) {
                $ruta = Route::getRoutes()->getByName($link['route']);
                $requisitos = [$link['permission']];
                foreach ($this->rutasDeEntrada($ruta) as $entrada) {
                    foreach ($entrada->gatherMiddleware() as $regla) {
                        if (str_starts_with($regla, 'can:')) $requisitos[] = explode(',', substr($regla, 4))[0];
                    }
                }
                // La consulta institucional comprueba un permiso adicional en el servicio.
                if (in_array($link['route'], ['admin.consulta', 'direccion.consulta', 'regencia.consulta'], true)) {
                    $area = $link['params']['area'] ?? $ruta->defaults['area'] ?? null;
                    if (isset(InstitutionalQueryService::AREAS[$area])) {
                        $requisitos[] = $actor === 'Administrador'
                            ? match ($area) {'cursos', 'lms' => 'cursos.ver.global', 'estudiantes' => 'estudiantes.ver.global', 'rendimiento' => 'calificaciones.ver.global', default => InstitutionalQueryService::AREAS[$area][2]}
                            : InstitutionalQueryService::AREAS[$area][2];
                    }
                }
                $requisitos = array_values(array_unique(array_filter($requisitos)));
                $ventanas[] = [...$link, 'id' => $actor.':'.$link['route'].':'.json_encode($link['params']),
                    'actor' => $actor, 'requisitos' => $requisitos,
                    'ruta' => '/'.$ruta->uri(), 'url' => route($link['route'], $link['params'], false),
                    'puede_ir' => $actorActual === $actor && collect($requisitos)->every(fn ($p) => $operador->can($p)),
                    'limite' => $link['route'] === 'docente.gestion-cursos'
                        ? 'Catálogo institucional completo: edición de cursos respaldada y planificación con sus permisos adicionales. No habilita Usuarios, Roles ni otras ventanas administrativas.'
                        : match ($actor) {'Docente' => 'Cursos y estudiantes asignados; cada operación comprueba su ámbito.', 'Estudiante' => 'Información propia y materias inscritas.', 'Regente' => 'Grados asignados en la gestión vigente.', default => 'Ámbito institucional y reglas propias del módulo.'},
                ];
            }
        }
        return $ventanas;
    }

    /** Una concesión granular no implica entrada al módulo. Las alternativas son explícitas. */
    public static function alternativas(string $permiso, string $actor): array
    {
        if ($permiso === 'roles-permisos.ver' && $actor === 'Administrador') {
            return ['roles-permisos.gestionar', 'Gestion_Roles_Permisos'];
        }
        return array_values(array_unique([$permiso, ...(isset(LegacyReadPermission::FALLBACKS[$permiso][$actor])
            ? [LegacyReadPermission::FALLBACKS[$permiso][$actor]] : [])]));
    }

    public static function cumple(array $ventana, string $actor, array $permisos): bool
    {
        return $ventana['actor'] === $actor && collect($ventana['requisitos'])->every(fn ($p) =>
            count(array_intersect(self::alternativas($p, $actor), $permisos)) > 0);
    }

    public function describir(array $permiso, array $ventanas): array
    {
        $entradas = array_values(array_filter($ventanas, fn ($v) => collect($v['requisitos'])->contains(fn ($p) =>
            in_array($permiso['name'], self::alternativas($p, $v['actor']), true))));
        // Vínculos de operaciones comprobados en los servicios y políticas, no inferidos por prefijo.
        $operaciones = match ($permiso['name']) {
            'usuarios.crear', 'usuarios.editar', 'usuarios.activar', 'usuarios.desactivar', 'usuarios.reset_password', 'usuarios.asignar_roles' => ['admin.gestion-usuarios'],
            'roles-permisos.gestionar', 'roles.permisos.asignar', 'roles.crear', 'roles.solicitudes.crear', 'roles.solicitudes.ver', 'roles.solicitudes.analizar', 'roles.solicitudes.cancelar' => ['admin.roles-permisos'],
            'cursos.gestionar.global' => ['admin.gestion-cursos', 'docente.gestion-cursos'],
            'estudiantes.gestionar.institucional' => ['admin.gestion-estudiantes', 'secretaria.estudiantes'],
            'inscripciones.gestionar.institucional' => ['admin.gestion-inscripciones', 'secretaria.inscripciones'],
            default => [],
        };
        $acciones = array_values(array_filter($ventanas, fn ($v) => in_array($v['route'], $operaciones, true)));
        return [...$permiso, 'ventanas' => $entradas, 'operaciones' => $acciones,
            'detalle' => $entradas ? 'Interviene en la entrada a estas ventanas. También deben cumplirse los otros requisitos y el rol indicado.'
                : ($acciones ? 'Autoriza una operación dentro del módulo; no concede por sí solo entrada a la ventana.'
                    : 'Registro del catálogo sin entrada de menú vinculada. Su uso debe comprobarse en la operación o política correspondiente.'),
        ];
    }

    private function rutasDeEntrada($ruta): array
    {
        $resultado = [$ruta];
        $destino = $ruta->defaults['destination'] ?? null;
        if (is_string($destino) && str_starts_with($destino, '/')) {
            try { $resultado[] = Route::getRoutes()->match(Request::create($destino, 'GET')); }
            catch (\Symfony\Component\HttpKernel\Exception\HttpException) { /* La ruta conserva su propia validación. */ }
        }
        return $resultado;
    }
}
