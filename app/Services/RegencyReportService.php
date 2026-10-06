<?php

namespace App\Services;

use App\Models\Oficial\Academico\Calificacion;
use App\Models\Oficial\Academico\InscripcionEstudiante;
use App\Models\Oficial\Academico\PlanAsignatura;
use App\Models\Oficial\Sistema\User;
use Illuminate\Database\Eloquent\Builder;

class RegencyReportService
{
    public function query(User $user): Builder
    {
        abort_unless(app(RoleDashboardResolver::class)->roleFor($user) === 'Regente'
            && $user->can('reportes.ver.institucional') && $user->can('cursos.ver.institucional'), 403);
        $query = app(RegencyAccessService::class)->constrain(PlanAsignatura::query(), $user, 'plan_asignatura')
            ->where('est_pas', 'ACTIVO')->with('asignatura', 'curso', 'paralelo', 'turno', 'gestionAcademica');
        $enrollments = InscripcionEstudiante::selectRaw('COUNT(*)')->where('est_ins', 'ACTIVA')
            ->whereHas('estudiante', fn ($q) => $q->where('est_est', 'ACTIVO'));
        $enrollments->whereHas('inscripcionVigenciaRegistros', fn ($vigencia) => $vigencia
            ->whereColumn('inscripcion_vigencia.cod_gac', 'plan_asignatura.cod_gac')->whereNull('cod_esp_tec')
            ->where('fii_ivg', '<=', now()->toDateString())
            ->where(fn ($fin) => $fin->whereNull('ffi_ivg')->orWhere('ffi_ivg', '>=', now()->toDateString())));
        $query->addSelect(['inscripciones_vigentes' => $enrollments]);
        if ($user->can('calificaciones.ver.institucional') && app(GradeService::class)->available()) {
            $grades = Calificacion::whereColumn('calificacion.cod_pas', 'plan_asignatura.cod_pas')->whereIn('est_cal', ['VIGENTE', 'RECTIFICADA']);
            $query->addSelect(['notas_registradas' => (clone $grades)->selectRaw('COUNT(*)'),
                'promedio_notas' => (clone $grades)->selectRaw('AVG(not_cal)')]);
        }

        return $query;
    }

    public function find(User $user, string $plan): PlanAsignatura
    {
        // El ID enviado por el navegador nunca sustituye el scope gestión/grado.
        return $this->query($user)->whereKey($plan)->firstOrFail();
    }
}
