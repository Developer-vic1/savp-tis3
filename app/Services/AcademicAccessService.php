<?php

namespace App\Services;

use App\Models\Oficial\Academico\Calificacion;
use App\Models\Oficial\Academico\Curso;
use App\Models\Oficial\Academico\Estudiante;
use App\Models\Oficial\Academico\InscripcionEstudiante;
use App\Models\Oficial\Academico\PlanAsignatura;
use App\Models\Oficial\Academico\PlanEspecialidad;
use App\Models\Oficial\Sistema\User;
use App\Services\AulaVirtual\CursoVirtualService;

class AcademicAccessService
{
    public function __construct(private readonly CursoVirtualService $virtualCourses) {}

    public function studentFor(User $user): ?Estudiante
    {
        return $this->virtualCourses->estudianteDeUsuario($user);
    }

    public function canViewStudent(User $user, Estudiante $student): bool
    {
        if (! app(RoleDashboardResolver::class)->roleFor($user)) {
            return false;
        }
        if ($user->hasAnyRole(['Administrador', 'Director', 'Secretaria'])) {
            return $user->canAny(['estudiantes.ver.global', 'estudiantes.ver.institucional', 'Estudiantes']);
        }

        if ($user->hasRole('Estudiante')) {
            return $user->canAny(['estudiantes.ver.propio', 'Aula_Virtual_Estudiante'])
                && $this->studentFor($user)?->cod_est === $student->cod_est;
        }

        if ($user->hasRole('Regente') && $user->can('estudiantes.ver.institucional')) {
            return app(RegencyAccessService::class)->constrain(InscripcionEstudiante::where('cod_est', $student->cod_est)->where('est_ins', 'ACTIVA'), $user, 'inscripcion_estudiante')->exists();
        }

        if (! $user->hasRole('Docente') || ! $user->canAny(['estudiantes.ver.curso', 'Aula_Virtual_Docente'])) {
            return false;
        }

        $teacher = $this->virtualCourses->docenteDeUsuario($user);

        return $teacher && $this->virtualCourses->vinculosVigentes($user)->where('cod_est', $student->cod_est)->exists();
    }

    public function canViewCourse(User $user, Curso $course): bool
    {
        if (! app(RoleDashboardResolver::class)->roleFor($user)) {
            return false;
        }
        if ($user->hasAnyRole(['Administrador', 'Director', 'Secretaria'])) {
            return $user->canAny(['cursos.ver.global', 'cursos.ver.institucional', 'Cursos']);
        }

        if ($user->hasRole('Regente') && $user->can('cursos.ver.institucional')) {
            return app(RegencyAccessService::class)->constrain(PlanAsignatura::deCurso($course->cod_cur), $user, 'plan_asignatura')->exists();
        }

        if ($user->hasRole('Docente')) {
            if (! $user->canAny(['cursos.ver.asignados', 'Aula_Virtual_Docente'])) {
                return false;
            }
            $teacher = $this->virtualCourses->docenteDeUsuario($user);

            return $teacher && PlanAsignatura::query()->where('cod_doc', $teacher->cod_doc)->deCurso($course->cod_cur)->exists();
        }

        if (! $user->hasRole('Estudiante') || ! $user->canAny(['cursos.ver.propios', 'Aula_Virtual_Estudiante'])) {
            return false;
        }

        $student = $this->studentFor($user);

        return $student && $student->inscripciones()->where('cod_cur', $course->cod_cur)->where('est_ins', 'ACTIVA')->exists();
    }

    public function canManageGrade(User $user, string $studentId, ?string $subjectId, ?string $planId = null, ?string $fechaAcademica = null): bool
    {
        if (! app(RoleDashboardResolver::class)->roleFor($user)) {
            return false;
        }
        if (! $planId) {
            return false;
        }
        if ($user->hasRole('Administrador')) {
            return $user->can('calificaciones.gestionar.global');
        }

        if (! $user->hasRole('Docente') || ! $user->can('calificaciones.gestionar.curso')) {
            return false;
        }

        $teacher = $this->virtualCourses->docenteDeUsuario($user);
        if (! $teacher) {
            return false;
        }

        $plan = PlanAsignatura::find($planId) ?? PlanEspecialidad::find($planId);
        if (! $plan || $plan->cod_doc !== $teacher->cod_doc
            || ($plan instanceof PlanAsignatura && $plan->cod_asi !== $subjectId)) {
            return false;
        }
        $fecha = $fechaAcademica ?? now()->toDateString();
        $tecnico = $plan instanceof PlanEspecialidad;

        return $plan->newQuery()->whereKey($planId)
            ->whereExists(function ($query) use ($studentId, $plan, $fecha, $tecnico) {
                $query->selectRaw('1')
                    ->from('inscripcion_estudiante')
                    ->join('inscripcion_vigencia as permiso_ivg', 'permiso_ivg.cod_ins', '=', 'inscripcion_estudiante.cod_ins')
                    ->whereColumn('permiso_ivg.cod_gac', $plan->getTable().'.cod_gac')
                    ->where('permiso_ivg.cod_esp_tec', $tecnico ? $plan->cod_esp : null)
                    ->where('permiso_ivg.fii_ivg', '<=', $fecha)
                    ->where(fn ($fin) => $fin->whereNull('permiso_ivg.ffi_ivg')->orWhere('permiso_ivg.ffi_ivg', '>=', $fecha))
                    ->where('inscripcion_estudiante.cod_est', $studentId)
                    ->where('inscripcion_estudiante.est_ins', 'ACTIVA');
            })
            ->exists();
    }

    public function canViewGrade(User $user, Calificacion $grade): bool
    {
        if (! app(RoleDashboardResolver::class)->roleFor($user)) {
            return false;
        }
        if ($user->hasAnyRole(['Administrador', 'Director'])) {
            return $user->canAny(['calificaciones.ver.global', 'calificaciones.ver.institucional']);
        }
        if ($user->hasRole('Estudiante')) {
            return $user->can('calificaciones.ver.propias') && $this->studentFor($user)?->cod_est === $grade->cod_est;
        }
        $plan = $grade->planAsignatura ?? $grade->planEspecialidad;
        if (! $plan) {
            return false;
        }
        if ($user->hasRole('Regente') && $user->can('calificaciones.ver.institucional')) {
            return app(RegencyAccessService::class)->constrain($plan->newQuery()->whereKey($plan->getKey()), $user, $plan->getTable())->exists();
        }

        $teacher = $user->hasRole('Docente') ? $this->virtualCourses->docenteDeUsuario($user) : null;

        return $teacher && $user->can('calificaciones.ver.curso')
            && $plan->cod_doc === $teacher->cod_doc;
    }
}
