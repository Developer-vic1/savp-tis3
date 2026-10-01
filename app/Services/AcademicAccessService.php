<?php

namespace App\Services;

use App\Models\Calificacion;
use App\Models\Curso;
use App\Models\Estudiante;
use App\Models\InscripcionEstudiante;
use App\Models\PlanAsignatura;
use App\Models\User;
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
            return app(RegencyAccessService::class)->constrain(PlanAsignatura::where('cod_cur', $course->cod_cur), $user, 'plan_asignatura')->exists();
        }

        if ($user->hasRole('Docente')) {
            if (! $user->canAny(['cursos.ver.asignados', 'Aula_Virtual_Docente'])) {
                return false;
            }
            $teacher = $this->virtualCourses->docenteDeUsuario($user);

            return $teacher && PlanAsignatura::query()->where('cod_doc', $teacher->cod_doc)->where('cod_cur', $course->cod_cur)->exists();
        }

        if (! $user->hasRole('Estudiante') || ! $user->canAny(['cursos.ver.propios', 'Aula_Virtual_Estudiante'])) {
            return false;
        }

        $student = $this->studentFor($user);

        return $student && $student->inscripciones()->where('cod_cur', $course->cod_cur)->where('est_ins', 'ACTIVA')->exists();
    }

    public function canManageGrade(User $user, string $studentId, string $subjectId, ?string $planId = null): bool
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

        return PlanAsignatura::query()
            ->where('cod_pas', $planId)
            ->where('cod_doc', $teacher->cod_doc)
            ->where('cod_asi', $subjectId)
            ->whereExists(function ($query) use ($studentId) {
                $query->selectRaw('1')
                    ->from('inscripcion_estudiante')
                    ->whereColumn('inscripcion_estudiante.cod_cur', 'plan_asignatura.cod_cur')
                    ->whereColumn('inscripcion_estudiante.cod_gea', 'plan_asignatura.cod_gea')
                    ->whereColumn('inscripcion_estudiante.cod_par', 'plan_asignatura.cod_par')
                    ->whereColumn('inscripcion_estudiante.cod_tur', 'plan_asignatura.cod_tur')
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
        if (! $grade->cod_pas) {
            return false;
        }
        if ($user->hasRole('Regente') && $user->can('calificaciones.ver.institucional')) {
            return app(RegencyAccessService::class)->constrain(PlanAsignatura::whereKey($grade->cod_pas), $user, 'plan_asignatura')->exists();
        }

        $teacher = $user->hasRole('Docente') ? $this->virtualCourses->docenteDeUsuario($user) : null;

        return $teacher && $user->can('calificaciones.ver.curso')
            && $grade->planAsignatura?->cod_doc === $teacher->cod_doc;
    }
}
