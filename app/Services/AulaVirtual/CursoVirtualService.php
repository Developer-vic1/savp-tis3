<?php

namespace App\Services\AulaVirtual;

use App\Models\AulaVirtual\AsistenciaClase;
use App\Models\AulaVirtual\AsistenciaEstudiante;
use App\Models\AulaVirtual\ClaseVirtual;
use App\Models\AulaVirtual\EntregaTarea;
use App\Models\AulaVirtual\MaterialClase;
use App\Models\AulaVirtual\OrientacionActividad;
use App\Models\AulaVirtual\Tarea;
use App\Models\Docente;
use App\Models\Estudiante;
use App\Models\User;
use App\Services\BitacoraService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Support\Facades\Log;

class CursoVirtualService
{
    /**
     * Resuelve el estudiante activo asociado al usuario autenticado.
     * En caso de múltiples coincidencias activas (inconsistencia de datos),
     * deniega el acceso y registra la anomalía en bitácora.
     */
    public function estudianteDeUsuario(User $user): ?Estudiante
    {
        if (! $user->cod_per) {
            return null;
        }

        $estudiantes = Estudiante::query()
            ->with('persona')
            ->where('cod_per', $user->cod_per)
            ->where('est_est', 'ACTIVO')
            ->get();

        if ($estudiantes->count() === 1) {
            return $estudiantes->first();
        }

        if ($estudiantes->count() > 1) {
            Log::warning("Inconsistencia de identidad detectada: Múltiples registros de Estudiante activos para cod_per {$user->cod_per} (Usuario: {$user->cod_usu}).");
            if (class_exists(BitacoraService::class)) {
                app(BitacoraService::class)->registrar(
                    accion: 'INCONSISTENCIA_IDENTIDAD_ESTUDIANTE',
                    tabla: 'estudiante',
                    registro: $user->cod_per,
                    descripcion: "Usuario {$user->cod_usu} posee múltiples ({$estudiantes->count()}) registros de estudiante activos.",
                    nivel: 'WARNING'
                );
            }
        }

        return null;
    }

    /**
     * Resuelve el docente activo asociado al usuario autenticado.
     * En caso de múltiples coincidencias activas, deniega y registra.
     */
    public function docenteDeUsuario(User $user): ?Docente
    {
        if (! $user->cod_per) {
            return null;
        }

        $docentes = Docente::query()
            ->with('personalInstitucional.persona')
            ->whereHas('personalInstitucional', function ($q) use ($user) {
                $q->where('cod_per', $user->cod_per)
                    ->where('est_pin', 'ACTIVO');
            })
            ->where('est_doc', 'ACTIVO')
            ->get();

        if ($docentes->count() === 1) {
            return $docentes->first();
        }

        if ($docentes->count() > 1) {
            Log::warning("Inconsistencia de identidad detectada: Múltiples registros de Docente activos para cod_per {$user->cod_per} (Usuario: {$user->cod_usu}).");
            if (class_exists(BitacoraService::class)) {
                app(BitacoraService::class)->registrar(
                    accion: 'INCONSISTENCIA_IDENTIDAD_DOCENTE',
                    tabla: 'docente',
                    registro: $user->cod_per,
                    descripcion: "Usuario {$user->cod_usu} posee múltiples ({$docentes->count()}) registros de docente activos.",
                    nivel: 'WARNING'
                );
            }
        }

        return null;
    }

    public function cursosEstudiante(User $user): Collection
    {
        $estudiante = $this->estudianteDeUsuario($user);

        if (! $estudiante) {
            return new Collection();
        }

        return ClaseVirtual::query()
            ->with($this->relacionesCurso())
            ->whereHas('estudiantes', function ($query) use ($estudiante) {
                $query->where('cod_est', $estudiante->cod_est)
                    ->where('est_cla_est', 'ACTIVO');
            })
            ->where('est_cla', 'ACTIVA')
            ->orderBy('nom_cla')
            ->get();
    }

    public function cursosDocente(User $user): Collection
    {
        $docente = $this->docenteDeUsuario($user);

        if (! $docente) {
            return new Collection();
        }

        return ClaseVirtual::query()
            ->with($this->relacionesCurso())
            ->whereHas('planAsignatura', fn ($query) => $query->where('cod_doc', $docente->cod_doc))
            ->whereIn('est_cla', ['ACTIVA', 'CERRADA'])
            ->orderBy('nom_cla')
            ->get();
    }

    /**
     * Búsqueda directa y optimizada de clase para estudiante (evita cargar todas las clases en memoria).
     */
    public function cursoParaEstudiante(User $user, string $codClase): ?ClaseVirtual
    {
        $estudiante = $this->estudianteDeUsuario($user);

        if (! $estudiante) {
            return null;
        }

        return ClaseVirtual::query()
            ->with($this->relacionesCurso())
            ->where('cod_cla', $codClase)
            ->whereHas('estudiantes', function ($query) use ($estudiante) {
                $query->where('cod_est', $estudiante->cod_est)
                    ->where('est_cla_est', 'ACTIVO');
            })
            ->where('est_cla', 'ACTIVA')
            ->first();
    }

    /**
     * Búsqueda directa y optimizada de clase para docente.
     */
    public function cursoParaDocente(User $user, string $codClase): ?ClaseVirtual
    {
        $docente = $this->docenteDeUsuario($user);

        if (! $docente) {
            return null;
        }

        return ClaseVirtual::query()
            ->with($this->relacionesCurso())
            ->where('cod_cla', $codClase)
            ->whereHas('planAsignatura', fn ($query) => $query->where('cod_doc', $docente->cod_doc))
            ->whereIn('est_cla', ['ACTIVA', 'CERRADA'])
            ->first();
    }

    public function tareaParaDocente(User $user, string $codTar): ?Tarea
    {
        $docente = $this->docenteDeUsuario($user);
        if (! $docente) {
            return null;
        }

        return Tarea::query()
            ->with('claseVirtual.planAsignatura')
            ->where('cod_tar', $codTar)
            ->whereHas('claseVirtual.planAsignatura', fn ($q) => $q->where('cod_doc', $docente->cod_doc))
            ->first();
    }

    public function tareaParaEstudiante(User $user, string $codTar): ?Tarea
    {
        $estudiante = $this->estudianteDeUsuario($user);
        if (! $estudiante) {
            return null;
        }

        return Tarea::query()
            ->with('claseVirtual')
            ->where('cod_tar', $codTar)
            ->whereHas('claseVirtual.estudiantes', function ($q) use ($estudiante) {
                $q->where('cod_est', $estudiante->cod_est)->where('est_cla_est', 'ACTIVO');
            })
            ->whereIn('est_tar', ['PUBLICADA', 'CERRADA'])
            ->first();
    }

    public function materialParaDocente(User $user, string $codMat): ?MaterialClase
    {
        $docente = $this->docenteDeUsuario($user);
        if (! $docente) {
            return null;
        }

        return MaterialClase::query()
            ->with('claseVirtual.planAsignatura')
            ->where('cod_mat', $codMat)
            ->whereHas('claseVirtual.planAsignatura', fn ($q) => $q->where('cod_doc', $docente->cod_doc))
            ->first();
    }

    public function materialParaEstudiante(User $user, string $codMat): ?MaterialClase
    {
        $estudiante = $this->estudianteDeUsuario($user);
        if (! $estudiante) {
            return null;
        }

        return MaterialClase::query()
            ->with('claseVirtual')
            ->where('cod_mat', $codMat)
            ->where('est_mat', 'ACTIVO')
            ->whereHas('claseVirtual.estudiantes', function ($q) use ($estudiante) {
                $q->where('cod_est', $estudiante->cod_est)->where('est_cla_est', 'ACTIVO');
            })
            ->first();
    }

    public function dashboardEstudiante(User $user): array
    {
        $cursos = $this->cursosEstudiante($user);
        $estudiante = $this->estudianteDeUsuario($user);
        $codClases = $cursos->pluck('cod_cla');

        $tareas = Tarea::query()
            ->with('claseVirtual.planAsignatura.asignatura')
            ->whereIn('cod_cla', $codClases)
            ->where('est_tar', 'PUBLICADA')
            ->orderBy('fec_lim_tar')
            ->get();

        $entregas = $estudiante
            ? EntregaTarea::query()
                ->with('calificacion')
                ->where('cod_est', $estudiante->cod_est)
                ->whereIn('cod_tar', $tareas->pluck('cod_tar'))
                ->get()
            : new Collection();

        $pendientes = $tareas->filter(function (Tarea $tarea) use ($entregas) {
            $entregasTarea = $entregas->where('cod_tar', $tarea->cod_tar);
            if ($entregasTarea->isEmpty()) {
                return true;
            }
            $mejorEntrega = $entregasTarea->sortByDesc(fn ($e) => match($e->est_ent) {
                'CALIFICADO' => 6,
                'ENTREGADO' => 5,
                'ENTREGADO_TARDE' => 4,
                'DEVUELTO' => 3,
                'PENDIENTE' => 2,
                'ANULADO' => 1,
                default => 0,
            })->first();

            return ! $mejorEntrega || ! in_array($mejorEntrega->est_ent, ['ENTREGADO', 'ENTREGADO_TARDE', 'CALIFICADO']);
        });

        $calificaciones = $entregas->pluck('calificacion')->filter();
        $promedio = $calificaciones->isNotEmpty()
            ? round($calificaciones->avg(fn ($calificacion) => (float) $calificacion->pun_obt), 2)
            : null;

        $asistencia = $this->resumenAsistenciaEstudiante($estudiante);

        $orientacionEnProceso = $estudiante
            ? OrientacionActividad::query()
                ->where('cod_est', $estudiante->cod_est)
                ->whereIn('estado', ['pendiente', 'en_proceso'])
                ->count()
            : 0;

        return [
            'cursos' => $cursos,
            'tareas' => $tareas,
            'entregas' => $entregas,
            'pendientes' => $pendientes,
            'calificaciones' => $calificaciones,
            'asistencia' => $asistencia,
            'metricas' => [
                'asignaturas' => $cursos->count(),
                'actividades_pendientes' => $pendientes->count(),
                'tareas_entregadas' => $entregas->unique('cod_tar')->filter(fn (EntregaTarea $entrega) => in_array($entrega->est_ent, ['ENTREGADO', 'ENTREGADO_TARDE', 'CALIFICADO']))->count(),
                'promedio_actual' => $promedio,
                'asistencia_general' => $asistencia['porcentaje'],
                'orientacion_en_proceso' => $orientacionEnProceso,
            ],
        ];
    }

    public function dashboardDocente(User $user): array
    {
        $cursos = $this->cursosDocente($user);
        $docente = $this->docenteDeUsuario($user);
        $codClases = $cursos->pluck('cod_cla');

        $tareas = Tarea::query()
            ->whereIn('cod_cla', $codClases)
            ->whereIn('est_tar', ['PUBLICADA', 'CERRADA'])
            ->get();

        $entregasPorRevisar = EntregaTarea::query()
            ->whereIn('cod_tar', $tareas->pluck('cod_tar'))
            ->whereIn('est_ent', ['ENTREGADO', 'ENTREGADO_TARDE', 'DEVUELTO'])
            ->whereDoesntHave('calificacion')
            ->count();

        $orientacionPendiente = OrientacionActividad::query()
            ->whereIn('cod_est', $cursos->flatMap(fn (ClaseVirtual $curso) => $curso->estudiantes->pluck('cod_est'))->unique())
            ->whereIn('estado', ['pendiente', 'en_proceso', 'requiere_seguimiento'])
            ->count();

        return [
            'docente' => $docente,
            'cursos' => $cursos,
            'tareas' => $tareas,
            'metricas' => [
                'cursos_asignados' => $cursos->count(),
                'estudiantes_asignados' => $cursos->sum(fn (ClaseVirtual $curso) => $curso->estudiantes->where('est_cla_est', 'ACTIVO')->count()),
                'tareas_activas' => $tareas->where('est_tar', 'PUBLICADA')->count(),
                'entregas_por_revisar' => $entregasPorRevisar,
                'asistencias_pendientes' => 0,
                'seguimiento_orientacion' => $orientacionPendiente,
            ],
        ];
    }

    public function resumenAsistenciaEstudiante(?Estudiante $estudiante): array
    {
        if (! $estudiante) {
            return ['total' => 0, 'presentes' => 0, 'tardanzas' => 0, 'faltas' => 0, 'justificadas' => 0, 'porcentaje' => null];
        }

        $registros = AsistenciaEstudiante::query()
            ->with('estadoAsistencia')
            ->where('cod_est', $estudiante->cod_est)
            ->where('est_asi_est', '!=', 'ANULADO')
            ->get();

        $conteo = fn (string $nombre) => $registros->filter(fn ($registro) => str_contains(strtoupper($registro->estadoAsistencia?->nom_est_asi ?? ''), $nombre))->count();
        $total = $registros->count();
        $presentes = $conteo('PRESENTE');
        $tardanzas = $conteo('TARDANZA') + $conteo('ATRASO');
        $faltas = $conteo('FALTA');
        $justificadas = $conteo('JUSTIFIC');

        return [
            'total' => $total,
            'presentes' => $presentes,
            'tardanzas' => $tardanzas,
            'faltas' => $faltas,
            'justificadas' => $justificadas,
            'porcentaje' => $total > 0 ? round((($presentes + $tardanzas + $justificadas) / $total) * 100, 2) : null,
        ];
    }

    public function cursoResumen(ClaseVirtual $curso, ?Estudiante $estudiante = null): array
    {
        $tareas = $curso->tareas;
        $materiales = $curso->materiales->where('est_mat', 'ACTIVO');
        $entregas = $estudiante
            ? EntregaTarea::query()->where('cod_est', $estudiante->cod_est)->whereIn('cod_tar', $tareas->pluck('cod_tar'))->get()
            : new BaseCollection();

        return [
            'tareas_pendientes' => $estudiante
                ? $tareas->where('est_tar', 'PUBLICADA')->filter(function ($tarea) use ($entregas) {
                    $mejorEntrega = $entregas->where('cod_tar', $tarea->cod_tar)->sortByDesc(fn ($e) => match($e->est_ent) {
                        'CALIFICADO' => 6,
                        'ENTREGADO' => 5,
                        'ENTREGADO_TARDE' => 4,
                        'DEVUELTO' => 3,
                        'PENDIENTE' => 2,
                        'ANULADO' => 1,
                        default => 0,
                    })->first();
                    return ! $mejorEntrega || ! in_array($mejorEntrega->est_ent, ['ENTREGADO', 'ENTREGADO_TARDE', 'CALIFICADO']);
                })->count()
                : $tareas->where('est_tar', 'PUBLICADA')->count(),
            'materiales' => $materiales->count(),
            'entregas_pendientes' => $tareas->sum(fn ($tarea) => $tarea->entregas->whereIn('est_ent', ['ENTREGADO', 'ENTREGADO_TARDE'])->count()),
            'progreso' => $tareas->where('est_tar', 'PUBLICADA')->count() > 0
                ? round(($entregas->unique('cod_tar')->whereIn('est_ent', ['ENTREGADO', 'ENTREGADO_TARDE', 'CALIFICADO'])->count() / max(1, $tareas->where('est_tar', 'PUBLICADA')->count())) * 100)
                : 0,
        ];
    }

    private function relacionesCurso(): array
    {
        return [
            'planAsignatura.asignatura',
            'planAsignatura.docente.personalInstitucional.persona',
            'planAsignatura.curso',
            'planAsignatura.paralelo',
            'planAsignatura.turno',
            'planAsignatura.gestionAcademica',
            'estudiantes.estudiante.persona',
            'materiales',
            'tareas.entregas.calificacion',
        ];
    }
}
