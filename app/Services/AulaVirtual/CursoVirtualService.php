<?php

namespace App\Services\AulaVirtual;

use App\Models\AulaVirtual\AsistenciaClase;
use App\Models\AulaVirtual\AsistenciaEstudiante;
use App\Models\AulaVirtual\ClaseEstudiante;
use App\Models\AulaVirtual\ClaseVirtual;
use App\Models\AulaVirtual\EntregaTarea;
use App\Models\AulaVirtual\OrientacionActividad;
use App\Models\AulaVirtual\Tarea;
use App\Models\Docente;
use App\Models\Estudiante;
use App\Models\User;
use App\Services\RoleDashboardResolver;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CursoVirtualService
{
    public function estudianteDeUsuario(User $user): ?Estudiante
    {
        if (! $user->cod_per || app(RoleDashboardResolver::class)->roleFor($user) !== 'Estudiante') {
            return null;
        }

        return Estudiante::query()
            ->with('persona')
            ->where('est_est', 'ACTIVO')
            ->where('cod_per', $user->cod_per)
            ->first();
    }

    public function docenteDeUsuario(User $user): ?Docente
    {
        if (! $user->cod_per || app(RoleDashboardResolver::class)->roleFor($user) !== 'Docente') {
            return null;
        }

        return Docente::query()
            ->with('personalInstitucional.persona')
            ->where('est_doc', 'ACTIVO')
            ->whereHas('personalInstitucional', fn ($query) => $query->where('cod_per', $user->cod_per)->where('est_pin', 'ACTIVO'))
            ->first();
    }

    public function cursosEstudiante(User $user): Collection
    {
        return $this->studentQuery($user)->get();
    }

    public function studentQuery(User $user): Builder
    {
        $estudiante = $this->estudianteDeUsuario($user);

        if (! $estudiante) {
            return ClaseVirtual::query()->whereRaw('1 = 0');
        }

        return ClaseVirtual::query()
            ->with($this->relacionesCurso())
            ->withCount($this->summaryCounts($estudiante))
            ->whereHas('planAsignatura', function ($plan) use ($estudiante) {
                $plan->whereExists(function ($enrollment) use ($estudiante) {
                    $enrollment->selectRaw('1')->from('inscripcion_estudiante')->where('cod_est', $estudiante->cod_est)->where('est_ins', 'ACTIVA');
                    foreach (['cod_gea', 'cod_cur', 'cod_par', 'cod_tur'] as $field) {
                        $enrollment->whereColumn('inscripcion_estudiante.'.$field, 'plan_asignatura.'.$field);
                    }
                });
            })
            ->whereHas('estudiantes', function ($query) use ($estudiante) {
                $query->where('cod_est', $estudiante->cod_est)
                    ->where('est_cla_est', 'ACTIVO');
            })
            ->where('est_cla', 'ACTIVA')
            ->orderBy('nom_cla');
    }

    public function cursosDocente(User $user): Collection
    {
        return $this->teacherQuery($user)->get();
    }

    public function teacherQuery(User $user): Builder
    {
        $docente = $this->docenteDeUsuario($user);

        if (! $docente) {
            return ClaseVirtual::query()->whereRaw('1 = 0');
        }

        return ClaseVirtual::query()
            ->with($this->relacionesCurso())
            ->withCount($this->summaryCounts())
            ->whereHas('planAsignatura', fn ($query) => $query->where('cod_doc', $docente->cod_doc))
            ->whereIn('est_cla', ['ACTIVA', 'CERRADA'])
            ->orderBy('nom_cla');
    }

    public function cursoParaEstudiante(User $user, string $codClase): ?ClaseVirtual
    {
        return $this->studentQuery($user)->whereKey($codClase)->first();
    }

    public function cursoParaDocente(User $user, string $codClase): ?ClaseVirtual
    {
        return $this->teacherQuery($user)->whereKey($codClase)->first();
    }

    public function paginarCursos(User $user, bool $teacher, string $search = '', string $gestion = ''): LengthAwarePaginator
    {
        abort_unless(app(RoleDashboardResolver::class)->roleFor($user) === ($teacher ? 'Docente' : 'Estudiante')
            && $user->can('Acceso_Aula_Virtual') && $user->can($teacher ? 'Aula_Virtual_Docente' : 'Aula_Virtual_Estudiante'), 403);

        return ($teacher ? $this->teacherQuery($user) : $this->studentQuery($user))
            ->when($gestion !== '', fn ($q) => $q->whereHas('planAsignatura.gestionAcademica', fn ($g) => $g->where('ani_gea', $gestion)))
            ->when($search !== '', fn ($q) => $q->where(fn ($names) => $names->where('nom_cla', 'like', '%'.$search.'%')
                ->orWhereHas('planAsignatura.asignatura', fn ($subject) => $subject->where('nom_asi', 'like', '%'.$search.'%'))))
            ->paginate(12)->withQueryString();
    }

    public function vinculosVigentes(User $teacher): Builder
    {
        return ClaseEstudiante::query()->where('est_cla_est', 'ACTIVO')
            ->whereIn('cod_cla', $this->teacherQuery($teacher)->reorder()->select('cod_cla')->withoutEagerLoads())
            ->whereHas('estudiante', fn ($q) => $q->where('est_est', 'ACTIVO'))
            ->whereHas('claseVirtual.planAsignatura', function ($plan) {
                $plan->whereExists(function ($enrollment) {
                    $enrollment->selectRaw('1')->from('inscripcion_estudiante')->where('est_ins', 'ACTIVA')
                        ->whereColumn('inscripcion_estudiante.cod_est', 'clase_estudiante.cod_est');
                    foreach (['cod_gea', 'cod_cur', 'cod_par', 'cod_tur'] as $field) {
                        $enrollment->whereColumn('inscripcion_estudiante.'.$field, 'plan_asignatura.'.$field);
                    }
                });
            });
    }

    public function estudiantesVigentes(ClaseVirtual $class): HasMany
    {
        $query = $class->estudiantes()->where('est_cla_est', 'ACTIVO');
        $plan = $class->planAsignatura;
        if (! $plan) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereHas('estudiante', fn ($student) => $student->where('est_est', 'ACTIVO')
            ->whereHas('inscripciones', function ($enrollment) use ($plan) {
                $enrollment->where('est_ins', 'ACTIVA');
                foreach (['cod_gea', 'cod_cur', 'cod_par', 'cod_tur'] as $field) {
                    $enrollment->where($field, $plan->$field);
                }
            }));
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
            : new Collection;

        $pendientes = $tareas->filter(function (Tarea $tarea) use ($entregas) {
            $entregasTarea = $entregas->where('cod_tar', $tarea->cod_tar);
            if ($entregasTarea->isEmpty()) {
                return true;
            }
            $mejorEntrega = $entregasTarea->sortByDesc(fn ($e) => match ($e->est_ent) {
                'CALIFICADO' => 6,
                'ENTREGADO' => 5,
                'ENTREGADO_TARDE' => 4,
                'DEVUELTO' => 3,
                'PENDIENTE' => 2,
                'ANULADO' => 1,
                default => 0,
            })->first();

            return ! in_array($mejorEntrega->est_ent, ['ENTREGADO', 'ENTREGADO_TARDE', 'CALIFICADO']);
        });
        $calificaciones = $entregas->pluck('calificacion')->filter();
        $validGrades = $calificaciones->filter(fn ($grade) => (float) $grade->pun_max > 0);
        $promedio = $validGrades->isNotEmpty()
            ? round($validGrades->avg(fn ($grade) => 100 * (float) $grade->pun_obt / (float) $grade->pun_max), 2)
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

        $studentIds = $this->vinculosVigentes($user)->select('cod_est')->distinct();
        $orientacionPendiente = OrientacionActividad::query()
            ->whereIn('cod_est', $studentIds)
            ->whereIn('estado', ['pendiente', 'en_proceso', 'requiere_seguimiento'])
            ->count();

        return [
            'docente' => $docente,
            'cursos' => $cursos,
            'tareas' => $tareas,
            'metricas' => [
                'cursos_asignados' => $cursos->count(),
                'estudiantes_asignados' => $this->vinculosVigentes($user)->distinct()->count('cod_est'),
                'tareas_activas' => $tareas->where('est_tar', 'PUBLICADA')->count(),
                'entregas_por_revisar' => $entregasPorRevisar,
                'asistencias_pendientes' => AsistenciaClase::whereIn('cod_cla', $codClases)->where('est_asi_cla', 'ABIERTA')->count(),
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
        $tardanzas = $conteo('TARDANZA');
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
        if (! array_key_exists('tareas_publicadas_count', $curso->getAttributes())) {
            $curso->loadCount($this->summaryCounts($estudiante));
        }
        $published = (int) $curso->tareas_publicadas_count;
        $pending = $estudiante ? (int) $curso->tareas_pendientes_count : $published;

        return ['tareas_pendientes' => $pending, 'materiales' => (int) $curso->materiales_publicados_count,
            'entregas_pendientes' => (int) ($curso->entregas_por_revisar_count ?? 0),
            'progreso' => $estudiante && $published > 0 ? round(100 * ($published - $pending) / $published) : null];
    }

    private function summaryCounts(?Estudiante $student = null): array
    {
        $counts = ['materiales as materiales_publicados_count' => fn ($q) => $q->where('est_mat', 'ACTIVO'),
            'tareas as tareas_publicadas_count' => fn ($q) => $q->where('est_tar', 'PUBLICADA')];
        if ($student) {
            $counts['tareas as tareas_pendientes_count'] = fn ($q) => $q->where('est_tar', 'PUBLICADA')
                ->whereDoesntHave('entregas', fn ($e) => $e->where('cod_est', $student->cod_est)->whereIn('est_ent', ['ENTREGADO', 'ENTREGADO_TARDE', 'CALIFICADO']));
        } else {
            $counts['entregas as entregas_por_revisar_count'] = fn ($q) => $q->whereIn('est_ent', ['ENTREGADO', 'ENTREGADO_TARDE'])->whereDoesntHave('calificacion');
        }

        return $counts;
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
        ];
    }
}
