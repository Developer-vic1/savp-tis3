<?php

namespace App\Support\Comunidad;

use App\Models\Oficial\Academico\PlanAsignatura;
use App\Models\Oficial\Academico\PlanEspecialidad;
use Illuminate\Database\Eloquent\Builder;

class IndicadoresPersonal
{
    public function calcular(Builder $consulta, int $limite): array
    {
        $docentes = (clone $consulta)->withoutEagerLoads()->with('personalInstitucional.persona')->get();
        $identificadores = $docentes->pluck('cod_doc');
        $materias = PlanAsignatura::query()->where('est_pas', 'ACTIVO')->whereIn('cod_doc', $identificadores)
            ->select('cod_asi')->selectRaw('SUM(hor_pas) as horas, COUNT(*) as asignaciones')
            ->groupBy('cod_asi')->with('asignatura')->get()
            ->map(fn ($plan) => ['nombre' => $plan->asignatura?->nom_asi ?: 'Materia sin nombre', 'horas' => (int) $plan->horas, 'asignaciones' => (int) $plan->asignaciones])
            ->sortByDesc('horas')->values()->all();
        $especialidades = PlanEspecialidad::query()->where('est_pes', 'ACTIVO')->whereIn('cod_doc', $identificadores)
            ->select('cod_esp')->selectRaw('SUM(hor_pes) as horas, COUNT(*) as asignaciones')
            ->groupBy('cod_esp')->with('especialidad')->get()
            ->map(fn ($plan) => ['nombre' => $plan->especialidad?->nom_esp ?: 'Especialidad sin nombre', 'horas' => (int) $plan->horas, 'asignaciones' => (int) $plan->asignaciones])
            ->sortByDesc('horas')->values()->all();
        $distribucion = $docentes->map(function ($docente) use ($limite) {
            $persona = $docente->personalInstitucional?->persona;
            $horas = (int) $docente->total_horas_materias + (int) $docente->total_horas_especialidades;

            return ['nombre' => mb_strtoupper(trim(($persona?->nom_per ?? '').' '.($persona?->ape_pat_per ?? '').' '.($persona?->ape_mat_per ?? '')) ?: 'Identidad por revisar'),
                'horas' => $horas, 'nivel' => $horas > $limite ? 'exceso' : ($horas >= 19 ? 'alta' : ($horas >= 11 ? 'media' : ($horas > 0 ? 'normal' : 'vacia')))];
        })->values()->all();

        return ['total' => $docentes->count(), 'materias' => $materias, 'especialidades' => $especialidades,
            'horas_materias' => array_sum(array_column($materias, 'horas')), 'horas_especialidades' => array_sum(array_column($especialidades, 'horas')),
            'distribucion' => $distribucion, 'exceso' => count(array_filter($distribucion, fn ($docente) => $docente['nivel'] === 'exceso'))];
    }
}
