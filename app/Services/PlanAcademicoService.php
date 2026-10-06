<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Oficial\Academico\GrupoAcademico;
use App\Models\Oficial\Academico\PlanAsignatura;
use App\Models\Oficial\Academico\PlanEspecialidad;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** Resuelve el contexto de los formularios a la FK del grupo existente. */
class PlanAcademicoService
{
    public function guardar(array $datos, bool $tecnico = false, PlanAsignatura|PlanEspecialidad|null $plan = null): PlanAsignatura|PlanEspecialidad
    {
        $sufijo = $tecnico ? 'pes' : 'pas';
        $datos['fii_'.$sufijo] ??= $datos['fii_plan'] ?? $plan?->{'fii_'.$sufijo}?->format('Y-m-d');
        $datos['ffi_'.$sufijo] ??= $datos['ffi_plan'] ?? $plan?->{'ffi_'.$sufijo}?->format('Y-m-d');
        validator($datos, [
            'cod_doc' => ['required', 'exists:docente,cod_doc'],
            $tecnico ? 'cod_esp' : 'cod_asi' => ['required', $tecnico ? 'exists:especialidad_tecnica,cod_esp' : 'exists:asignatura,cod_asi'],
            'fii_'.$sufijo => ['required', 'date_format:Y-m-d'],
            'ffi_'.$sufijo => ['nullable', 'date_format:Y-m-d', 'after_or_equal:fii_'.$sufijo],
        ])->validate();

        return DB::transaction(function () use ($datos, $tecnico, $plan) {
            $grupo = GrupoAcademico::query();
            if (! empty($datos['cod_gac'])) {
                $grupo->whereKey($datos['cod_gac']);
            } else {
                foreach (['cod_gea', 'cod_cur', 'cod_par', 'cod_tur'] as $campo) {
                    $grupo->where($campo, $datos[$campo] ?? null);
                }
            }
            $grupo = $grupo->lockForUpdate()->first();
            if (! $grupo) {
                throw ValidationException::withMessages(['cod_gac' => 'No existe un grupo configurado para esa gestión, curso, paralelo y turno.']);
            }
            $plan ??= $tecnico ? new PlanEspecialidad : new PlanAsignatura;
            $persistencia = array_intersect_key($datos, array_flip($plan->getFillable()));
            $persistencia['cod_gac'] = $grupo->cod_gac;
            $plan->fill($persistencia);
            $plan->save();

            return $plan;
        });
    }
}
