<?php

namespace App\Services\AulaVirtual;

use App\Models\AulaVirtual\AsistenciaEstudiante;
use App\Models\AulaVirtual\ClaseVirtual;
use App\Models\AulaVirtual\EntregaTarea;
use App\Models\AulaVirtual\OrientacionActividad;
use App\Models\AulaVirtual\Tarea;
use App\Models\Docente;
use App\Models\Estudiante;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as BaseCollection;

class CursoVirtualService
{
    public function estudianteDeUsuario(User $user): ?Estudiante
    {
        return Estudiante::query()
            ->with('persona')
            ->where('cod_per', $user->cod_per)
            ->first();
    }

    public function docenteDeUsuario(User $user): ?Docente
    {
        return Docente::query()
            ->with('personalInstitucional.persona')
            ->whereHas('personalInstitucional', fn ($query) => $query->where('cod_per', $user->cod_per))
            ->first();
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

    public function cursoParaEstudiante(User $user, string $codClase): ?ClaseVirtual
    {
        return $this->cursosEstudiante($user)->firstWhere('cod_cla', $codClase);
    }

    public function cursoParaDocente(User $user, string $codClase): ?ClaseVirtual
    {
        return $this->cursosDocente($user)->firstWhere('cod_cla', $codClase);
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

        $pendientes = $tareas->filter(fn (Tarea $tarea) => ! $entregas->firstWhere('cod_tar', $tarea->cod_tar)?->estaEntregada());
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
                'tareas_entregadas' => $entregas->filter(fn (EntregaTarea $entrega) => $entrega->estaEntregada() || $entrega->estaCalificada())->count(),
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
        $tareas = $curso->tareas;
        $materiales = $curso->materiales->where('est_mat', 'ACTIVO');
        $entregas = $estudiante
            ? EntregaTarea::query()->where('cod_est', $estudiante->cod_est)->whereIn('cod_tar', $tareas->pluck('cod_tar'))->get()
            : new BaseCollection();

        return [
            'tareas_pendientes' => $estudiante
                ? $tareas->where('est_tar', 'PUBLICADA')->filter(fn ($tarea) => ! $entregas->firstWhere('cod_tar', $tarea->cod_tar)?->estaEntregada())->count()
                : $tareas->where('est_tar', 'PUBLICADA')->count(),
            'materiales' => $materiales->count(),
            'entregas_pendientes' => $tareas->sum(fn ($tarea) => $tarea->entregas->whereIn('est_ent', ['ENTREGADO', 'ENTREGADO_TARDE'])->count()),
            'progreso' => $tareas->where('est_tar', 'PUBLICADA')->count() > 0
                ? round(($entregas->whereIn('est_ent', ['ENTREGADO', 'ENTREGADO_TARDE', 'CALIFICADO'])->count() / max(1, $tareas->where('est_tar', 'PUBLICADA')->count())) * 100)
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
