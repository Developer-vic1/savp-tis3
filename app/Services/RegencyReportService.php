<?php

namespace App\Services;

use App\Models\Calificacion;
use App\Models\InscripcionEstudiante;
use App\Models\PlanAsignatura;
use App\Models\User;
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
        foreach (['cod_gea', 'cod_cur', 'cod_par', 'cod_tur'] as $field) {
            $enrollments->whereColumn('inscripcion_estudiante.'.$field, 'plan_asignatura.'.$field);
        }
        $query->addSelect(['inscripciones_vigentes' => $enrollments]);
        if ($user->can('calificaciones.ver.institucional') && app(GradeService::class)->available()) {
            $grades = Calificacion::whereColumn('calificacion.cod_pas', 'plan_asignatura.cod_pas')->where('est_cal', 'ACTIVO');
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
