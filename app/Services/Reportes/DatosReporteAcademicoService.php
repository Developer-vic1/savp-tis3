<?php

namespace App\Services\Reportes;

use App\Models\Asignatura;
use App\Models\Calificacion;
use App\Models\Curso;
use App\Models\EspecialidadTecnica;
use App\Models\Estudiante;
use App\Models\Paralelo;
use App\Models\PeriodoEvaluacion;
use App\Support\Evaluacion\CalificacionInteligente;
use App\Support\Reportes\ReporteAcademicoInteligente;
use Illuminate\Support\Collection;

class DatosReporteAcademicoService
{
    public function __construct(
        protected ReporteAcademicoInteligente $soporte,
        protected CalificacionInteligente $clasificador,
    ) {}

    /**
     * Obtiene y prepara todos los datos académicos para los reportes.
     */
    public function obtener(array $filtros = []): array
    {
        // ── Calificaciones activas con relaciones ─────────────────────────────
        $query = Calificacion::with([
            'estudiante.persona',
            'estudiante.especialidad',
            'estudiante.inscripciones.curso',
            'estudiante.inscripciones.paralelo',
            'asignatura',
            'periodoEvaluacion',
        ])->where('est_cal', 'ACTIVO');

        if (!empty($filtros['periodo'])) {
            $query->where('cod_pev', $filtros['periodo']);
        }
        if (!empty($filtros['asignatura'])) {
            $query->where('cod_asi', $filtros['asignatura']);
        }
        if (!empty($filtros['estudiante'])) {
            $query->where('cod_est', $filtros['estudiante']);
        }
        if (!empty($filtros['especialidad'])) {
            $query->whereHas('estudiante', fn ($q) => $q->where('cod_esp', $filtros['especialidad']));
        }

        $calificaciones = $query->get()->map(function (Calificacion $c) {
            $c->setAttribute('desempeno', $this->clasificador->clasificar((float) $c->not_cal));
            return $c;
        });

        $totalEstudiantes   = max(1, $calificaciones->pluck('cod_est')->unique()->count());
        $promedioGeneral    = round((float) $calificaciones->avg('not_cal'), 2);
        $destacados         = $calificaciones->where('desempeno', 'Destacado')->pluck('cod_est')->unique()->count();
        $riesgo             = $calificaciones->where('desempeno', 'En riesgo')->pluck('cod_est')->unique()->count();
        $aprobados          = $calificaciones->whereIn('desempeno', ['Aprobado', 'Destacado'])->pluck('cod_est')->unique()->count();
        $reprobados         = $riesgo; // por ahora se asume igual

        // ── Rendimiento por asignatura ────────────────────────────────────────
        $rendimientoAsignatura = $calificaciones
            ->groupBy('cod_asi')
            ->map(fn ($items) => [
                'nombre'   => $items->first()->asignatura?->nom_asi ?? 'Sin asignatura',
                'promedio' => round((float) $items->avg('not_cal'), 2),
                'registros' => $items->count(),
                'riesgo'   => $items->where('desempeno', 'En riesgo')->count(),
                'max'      => (float) $items->max('not_cal'),
                'min'      => (float) $items->min('not_cal'),
            ])
            ->sortByDesc('promedio')
            ->values();

        // ── Rendimiento por periodo ───────────────────────────────────────────
        $rendimientoPeriodo = $calificaciones
            ->groupBy('cod_pev')
            ->map(fn ($items) => [
                'nombre'   => $items->first()->periodoEvaluacion?->nom_pev ?? 'Sin periodo',
                'promedio' => round((float) $items->avg('not_cal'), 2),
                'registros' => $items->count(),
            ])
            ->sortByDesc('promedio')
            ->values();

        // ── Distribución cualitativa ──────────────────────────────────────────
        $distribucion = $calificaciones->countBy('desempeno');

        // ── Estudiantes en riesgo ─────────────────────────────────────────────
        $estudiantesRiesgo = $calificaciones
            ->where('desempeno', 'En riesgo')
            ->groupBy('cod_est')
            ->map(function ($items) {
                $est     = $items->first()->estudiante;
                $persona = $est?->persona;
                return [
                    'nombre'       => trim(($persona?->nom_per ?? '') . ' ' . ($persona?->ape_pat_per ?? '') . ' ' . ($persona?->ape_mat_per ?? '')),
                    'especialidad' => $est?->especialidad?->nom_esp ?? 'Sin especialidad',
                    'promedio'     => round((float) $items->avg('not_cal'), 2),
                    'nivel_riesgo' => $this->nivelRiesgo((float) $items->avg('not_cal')),
                    'asignaturas_criticas' => $items->pluck('asignatura.nom_asi')->filter()->unique()->values()->toArray(),
                ];
            })
            ->sortBy('promedio')
            ->values();

        // ── Estudiantes destacados ────────────────────────────────────────────
        $estudiantesDestacados = $calificaciones
            ->where('desempeno', 'Destacado')
            ->groupBy('cod_est')
            ->map(function ($items) {
                $est     = $items->first()->estudiante;
                $persona = $est?->persona;
                return [
                    'nombre'       => trim(($persona?->nom_per ?? '') . ' ' . ($persona?->ape_pat_per ?? '') . ' ' . ($persona?->ape_mat_per ?? '')),
                    'especialidad' => $est?->especialidad?->nom_esp ?? 'Sin especialidad',
                    'promedio'     => round((float) $items->avg('not_cal'), 2),
                ];
            })
            ->sortByDesc('promedio')
            ->values();

        // ── Compatibilidad por especialidad ───────────────────────────────────
        $compatibilidad = $calificaciones
            ->filter(fn ($c) => $c->estudiante?->especialidad)
            ->groupBy(fn ($c) => $c->estudiante->especialidad->nom_esp)
            ->map(function ($items, $esp) use ($totalEstudiantes) {
                [$area, $carreras] = $this->soporte->orientacionPorEspecialidad($esp);
                $cantidad = $items->pluck('cod_est')->unique()->count();
                return [
                    'especialidad' => $esp,
                    'area'         => $area,
                    'carreras'     => $carreras,
                    'estudiantes'  => $cantidad,
                    'porcentaje'   => round(($cantidad / $totalEstudiantes) * 100, 1),
                    'promedio'     => round((float) $items->avg('not_cal'), 2),
                ];
            })
            ->sortByDesc('estudiantes')
            ->values();

        // ── Filtros aplicados ─────────────────────────────────────────────────
        $filtrosAplicados = [];
        if (!empty($filtros['periodo'])) {
            $filtrosAplicados[] = 'Periodo: ' . (PeriodoEvaluacion::find($filtros['periodo'])?->nom_pev ?? $filtros['periodo']);
        }
        if (!empty($filtros['asignatura'])) {
            $filtrosAplicados[] = 'Asignatura: ' . (Asignatura::find($filtros['asignatura'])?->nom_asi ?? $filtros['asignatura']);
        }
        if (!empty($filtros['especialidad'])) {
            $filtrosAplicados[] = 'Especialidad: ' . (EspecialidadTecnica::find($filtros['especialidad'])?->nom_esp ?? $filtros['especialidad']);
        }

        return [
            'promedio_general'        => $promedioGeneral,
            'total_registros'         => $calificaciones->count(),
            'total_estudiantes'       => $totalEstudiantes,
            'aprobados'               => $aprobados,
            'reprobados'              => $reprobados,
            'en_riesgo'               => $riesgo,
            'destacados'              => $destacados,
            'rendimiento_asignatura'  => $rendimientoAsignatura,
            'rendimiento_periodo'     => $rendimientoPeriodo,
            'distribucion'            => $distribucion,
            'estudiantes_riesgo'      => $estudiantesRiesgo,
            'estudiantes_destacados'  => $estudiantesDestacados,
            'compatibilidad'          => $compatibilidad,
            'calificaciones'          => $calificaciones,
            'filtros_aplicados'       => $filtrosAplicados,
            'filtros_raw'             => $filtros,
            'periodos'                => PeriodoEvaluacion::orderBy('ord_pev')->get(),
            'asignaturas'             => Asignatura::orderBy('nom_asi')->get(),
            'especialidades'          => EspecialidadTecnica::orderBy('nom_esp')->get(),
        ];
    }

    protected function nivelRiesgo(float $promedio): string
    {
        return match (true) {
            $promedio < 40 => 'Crítico',
            $promedio < 51 => 'Alto',
            $promedio < 61 => 'Medio',
            default        => 'Bajo',
        };
    }
}
