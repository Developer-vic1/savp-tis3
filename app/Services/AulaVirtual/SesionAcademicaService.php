<?php

declare(strict_types=1);

namespace App\Services\AulaVirtual;

use App\Models\Oficial\Academico\HorarioDetalle;
use App\Models\Oficial\Academico\SesionAcademica;
use App\Models\Oficial\AulaVirtual\ClaseVirtual;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;

/** Una asistencia exige día y bloque del horario oficial del plan exacto. */
class SesionAcademicaService
{
    public function resolver(ClaseVirtual $clase, string $fecha, ?string $bloque): SesionAcademica
    {
        $dia = [1 => 'LUNES', 'MARTES', 'MIERCOLES', 'JUEVES', 'VIERNES', 'SABADO', 'DOMINGO'][CarbonImmutable::parse($fecha)->dayOfWeekIso];
        $plan = $clase->planAsignatura ?? $clase->planEspecialidad;
        $detalles = HorarioDetalle::query()->where($clase->cod_pas ? 'cod_pas' : 'cod_pes', $clase->cod_pas ?? $clase->cod_pes)
            ->where('dia_hde', $dia)->where('est_hde', 'ACTIVO')
            ->when($bloque, fn ($query) => $query->where('cod_hbl', $bloque))
            ->whereHas('horario', fn ($query) => $query->where('cod_gac', $plan->cod_gac)
                ->where('fii_hor', '<=', $fecha)->where(fn ($fin) => $fin->whereNull('ffi_hor')->orWhere('ffi_hor', '>=', $fecha)))
            ->lockForUpdate()->limit(2)->get();
        if ($detalles->count() !== 1) {
            throw ValidationException::withMessages(['cod_hbl' => $detalles->isEmpty()
                ? 'No existe un día y bloque oficial para ese plan y fecha. No se puede registrar asistencia.'
                : 'Selecciona el bloque concreto; el plan tiene varias sesiones ese día.']);
        }

        return SesionAcademica::firstOrCreate(['cod_hde' => $detalles->first()->cod_hde, 'fec_ses' => $fecha], ['est_ses' => 'PROGRAMADA']);
    }
}
