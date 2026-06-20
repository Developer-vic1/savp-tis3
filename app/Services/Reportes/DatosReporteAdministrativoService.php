<?php

namespace App\Services\Reportes;

use App\Models\Asignatura;
use App\Models\Bitacora;
use App\Models\Curso;
use App\Models\Docente;
use App\Models\EspecialidadTecnica;
use App\Models\Estudiante;
use App\Models\InscripcionEstudiante;
use App\Models\InstitucionProcedencia;
use App\Models\Paralelo;
use App\Models\PeriodoEvaluacion;
use App\Models\PersonalInstitucional;
use App\Models\TipoVinculacionEstudiante;
use App\Models\Turno;
use App\Models\User;

class DatosReporteAdministrativoService
{
    /**
     * Prepara todos los datos administrativos para los reportes.
     */
    public function obtener(array $filtros = []): array
    {
        // ── Métricas generales ────────────────────────────────────────────────
        $metricas = [
            'usuarios_registrados'      => User::count(),
            'usuarios_activos'          => User::where('est_usu', 'ACTIVO')->count(),
            'estudiantes_activos'       => Estudiante::where('est_est', 'ACTIVO')->count(),
            'estudiantes_registrados'   => Estudiante::count(),
            'docentes_activos'          => Docente::count(),
            'inscripciones_totales'     => InscripcionEstudiante::count(),
            'asignaturas'               => Asignatura::count(),
            'cursos'                    => Curso::count(),
            'paralelos'                 => Paralelo::count(),
            'turnos'                    => Turno::count(),
            'especialidades_tecnicas'   => EspecialidadTecnica::count(),
            'periodos_evaluacion'       => PeriodoEvaluacion::count(),
            'instituciones_procedencia' => InstitucionProcedencia::count(),
            'tipos_vinculacion'         => TipoVinculacionEstudiante::count(),
        ];

        // ── Usuarios por rol ──────────────────────────────────────────────────
        $usuariosPorRol = [];
        try {
            $usuariosPorRol = User::with('roles')
                ->get()
                ->flatMap(fn ($u) => $u->roles->pluck('name'))
                ->countBy()
                ->sortDesc()
                ->toArray();
        } catch (\Throwable) {
            $usuariosPorRol = [];
        }

        // ── Estudiantes por curso ─────────────────────────────────────────────
        $estudiantesPorCurso = [];
        try {
            $estudiantesPorCurso = InscripcionEstudiante::with('curso')
                ->get()
                ->groupBy('cod_cur')
                ->map(fn ($items) => [
                    'curso'    => $items->first()->curso?->nom_cur ?? 'Sin curso',
                    'cantidad' => $items->count(),
                ])
                ->sortByDesc('cantidad')
                ->values()
                ->toArray();
        } catch (\Throwable) {
            $estudiantesPorCurso = [];
        }

        // ── Distribución por turno ────────────────────────────────────────────
        $distribucionTurno = [];
        try {
            $distribucionTurno = InscripcionEstudiante::with('turno')
                ->get()
                ->groupBy('cod_tur')
                ->map(fn ($items) => [
                    'turno'    => $items->first()->turno?->nom_tur ?? 'Sin turno',
                    'cantidad' => $items->count(),
                ])
                ->sortByDesc('cantidad')
                ->values()
                ->toArray();
        } catch (\Throwable) {
            $distribucionTurno = [];
        }

        // ── Distribución de inscripciones por estado ──────────────────────────
        $estadosInscripcion = InscripcionEstudiante::all()
            ->countBy('est_ins')
            ->sortDesc()
            ->toArray();

        // ── Bitácora reciente ─────────────────────────────────────────────────
        $bitacoraQuery = Bitacora::with('usuario.persona')
            ->orderByDesc('fec_bit');

        if (!empty($filtros['modulo'])) {
            $bitacoraQuery->where('mod_bit', $filtros['modulo']);
        }
        if (!empty($filtros['search'])) {
            $s = $filtros['search'];
            $bitacoraQuery->where(fn ($q) =>
                $q->where('acc_bit', 'ILIKE', "%$s%")
                  ->orWhere('des_bit', 'ILIKE', "%$s%")
                  ->orWhere('nom_reg_bit', 'ILIKE', "%$s%")
            );
        }

        $bitacora = $bitacoraQuery->limit(100)->get();

        // ── Módulos activos ───────────────────────────────────────────────────
        $modulos = Bitacora::whereNotNull('mod_bit')
            ->pluck('mod_bit')
            ->unique()
            ->sort()
            ->values()
            ->toArray();

        // ── Diagnóstico del sistema ───────────────────────────────────────────
        $advertencias = collect($metricas)
            ->filter(fn ($v) => $v === 0)
            ->keys()
            ->map(fn ($k) => str_replace('_', ' ', ucfirst($k)))
            ->values()
            ->toArray();

        $completitud = (int) round(
            (collect($metricas)->filter(fn ($v) => $v > 0)->count() / max(1, count($metricas))) * 100
        );

        $diagnostico = [
            'estado'       => empty($advertencias) ? 'Operativo' : 'Con observaciones',
            'completitud'  => $completitud,
            'advertencias' => $advertencias,
        ];

        return [
            'metricas'             => $metricas,
            'usuarios_por_rol'     => $usuariosPorRol,
            'estudiantes_por_curso'=> $estudiantesPorCurso,
            'distribucion_turno'   => $distribucionTurno,
            'estados_inscripcion'  => $estadosInscripcion,
            'bitacora'             => $bitacora,
            'modulos'              => $modulos,
            'diagnostico'          => $diagnostico,
            'filtros_raw'          => $filtros,
        ];
    }
}
