<?php

namespace App\Support;

use App\Models\Oficial\Sistema\User;
use App\Services\RoleDashboardResolver;

/**
 * Read-only bridge while the institutional database still has legacy permissions.
 * A new permission explicitly assigned in the database always remains effective.
 */
final class LegacyReadPermission
{
    public const FALLBACKS = [
        'estudiantes.ver.institucional' => ['Administrador' => 'Estudiantes', 'Director' => 'Estudiantes', 'Regente' => 'Estudiantes'],
        'cursos.ver.institucional' => ['Administrador' => 'Cursos', 'Director' => 'Cursos', 'Regente' => 'Cursos'],
        'inscripciones.ver.institucional' => ['Administrador' => 'Inscripciones', 'Director' => 'Inscripciones', 'Regente' => 'Inscripciones'],
        'calificaciones.ver.institucional' => ['Administrador' => 'Calificaciones', 'Director' => 'Calificaciones', 'Regente' => 'Calificaciones'],
        'asistencia.ver.institucional' => ['Administrador' => 'Asistencia_Aula'],
        'reportes.ver.institucional' => ['Administrador' => 'Reportes_Academicos', 'Director' => 'Reportes_Academicos', 'Regente' => 'Reportes_Academicos'],
        'orientacion.ver.institucional' => ['Administrador' => 'Orientacion_Academica_Profesional'],
        'usuarios.ver.institucional' => ['Secretaria' => 'Gestion_Usuarios'],
        'estudiantes.ver.curso' => ['Docente' => 'Estudiantes_Curso'],
        'estudiantes.ver.propio' => ['Estudiante' => 'Aula_Virtual_Estudiante'],
        'cursos.ver.asignados' => ['Docente' => 'Mis_Cursos'],
        'cursos.ver.propios' => ['Estudiante' => 'Mis_Cursos'],
        'calificaciones.ver.curso' => ['Docente' => 'Calificaciones'],
        'calificaciones.ver.propias' => ['Estudiante' => 'Calificaciones'],
        'orientacion.ver.curso' => ['Docente' => 'Orientacion_Academica_Profesional'],
        'orientacion.ver.propia' => ['Estudiante' => 'Orientacion_Academica_Profesional'],
    ];

    public function allows(User $user, string $permission): bool
    {
        $actor = app(RoleDashboardResolver::class)->roleFor($user);
        if (! $actor) {
            return false;
        }

        $assigned = $user->getAllPermissions()->pluck('name');
        if ($assigned->contains($permission)) {
            return true;
        }

        $legacy = self::FALLBACKS[$permission][$actor] ?? null;

        return $legacy !== null && $assigned->contains($legacy);
    }
}
