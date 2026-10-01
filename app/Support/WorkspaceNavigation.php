<?php

namespace App\Support;

use App\Models\User;
use App\Services\RoleDashboardResolver;
use Illuminate\Support\Facades\Route;

final class WorkspaceNavigation
{
    public function for(User $user): array
    {
        $actor = app(RoleDashboardResolver::class)->roleFor($user);
        $items = match ($actor) {
            'Administrador' => [
                ['Personas', 'admin.gestion-personas', 'Registro_Personas', 'Administración'],
                ['Usuarios', 'admin.gestion-usuarios', 'Gestion_Usuarios', 'Administración'],
                ['Personal institucional', 'admin.personal-institucional', 'Personal_Institucional', 'Administración'],
                ['Estudiantes', 'admin.gestion-estudiantes', 'Estudiantes', 'Administración'],
                ['Docentes', 'admin.gestion-docentes', 'Docentes', 'Administración'],
                ['Gestión', 'admin.gestion-academica', 'Gestion_Academica', 'Gestión académica'],
                ['Cursos', 'admin.gestion-cursos', 'Cursos', 'Gestión académica'],
                ['Asignaturas', 'admin.gestion-asignaturas', 'Asignaturas', 'Gestión académica'],
                ['Paralelos', 'admin.gestion-paralelos', 'Paralelos', 'Gestión académica'],
                ['Turnos', 'admin.gestion-turnos', 'Turnos', 'Gestión académica'],
                ['Inscripciones', 'admin.gestion-inscripciones', 'Inscripciones', 'Gestión académica'],
                ['Especialidades técnicas', 'admin.especialidades-tecnicas', 'Especialidades_Tecnicas', 'Gestión académica'],
                ['Planes de asignatura', 'admin.planes-asignatura', 'Planes_Asignatura', 'Gestión académica'],
                ['Calendario', 'admin.calendario', 'Gestion_Academica', 'Gestión académica'],
                ['LMS institucional', 'admin.consulta', 'cursos.ver.institucional', 'Académico'],
                ['Periodos', 'admin.periodo-evaluacion', 'Periodo_Evaluacion', 'Gestión académica'],
                ['Calificaciones', 'admin.calificaciones', 'Calificaciones', 'Académico'],
                ['Roles y permisos', 'admin.roles-permisos', 'roles-permisos.ver', 'Seguridad'],
                ['Asignaciones de Regencia', 'admin.asignaciones-regencia', 'regencia.asignaciones.gestionar', 'Seguridad'],
                ['Reportes académicos', 'admin.reportes-academicos', 'Reportes_Academicos', 'Reportes'],
                ['Reportes administrativos', 'admin.reportes-administrativos', 'Reportes_Administrativos', 'Reportes'],
                ['Bitácora', 'admin.bitacora', 'Bitacora', 'Seguridad'],
            ],
            'Secretaria' => [
                ['Personas', 'secretaria.personas', 'Registro_Personas', 'Administración'],
                ['Estudiantes', 'secretaria.estudiantes', 'Estudiantes', 'Administración'],
                ['Inscripciones', 'secretaria.inscripciones', 'Inscripciones', 'Administración'],
                ['Documentación', 'secretaria.documentacion', 'Inscripciones', 'Administración'],
                ['Calendario', 'secretaria.calendario', 'Gestion_Academica', 'Gestión académica'],
                ['Cuentas operativas', 'secretaria.cuentas', 'usuarios.ver.institucional', 'Administración'],
                ['Reportes administrativos', 'secretaria.reportes', 'Reportes_Administrativos', 'Reportes'],
                ['Procedencia', 'secretaria.procedencia', 'Institucion_Procedencia', 'Administración'],
                ['Vinculación', 'secretaria.vinculacion', 'Tipo_Vinculacion_Estudiante', 'Administración'],
                ['Cursos', 'secretaria.cursos', 'Cursos', 'Gestión académica'],
                ['Paralelos', 'secretaria.paralelos', 'Paralelos', 'Gestión académica'],
                ['Turnos', 'secretaria.turnos', 'Turnos', 'Gestión académica'],
            ],
            'Director', 'Regente' => $this->institutional($actor),
            'Docente' => [
                ['Mis cursos', 'docente.cursos', 'Aula_Virtual_Docente', 'Académico'],
                ['Calendario', 'docente.calendario', 'Calendario_Aula', 'Académico'],
                ['Orientación', 'aula-virtual.docente.orientacion.seguimiento', 'Orientacion_Academica_Profesional', 'Seguimiento'],
                ['Reportes', 'aula-virtual.docente.reportes', 'Reportes_Aula', 'Seguimiento'],
            ],
            'Estudiante' => [
                ['Mis materias', 'estudiante.materias', 'Aula_Virtual_Estudiante', 'Académico'],
                ['Mi progreso', 'estudiante.area', 'calificaciones.ver.propias', 'Académico', ['area' => 'progreso']],
                ['Mi asistencia', 'estudiante.asistencia', 'Asistencia_Aula', 'Académico'],
                ['Calendario', 'estudiante.calendario', 'Calendario_Aula', 'Académico'],
                ['Mis intereses', 'estudiante.intereses', 'Orientacion_Academica_Profesional', 'Orientación'],
                ['Mi futuro académico', 'estudiante.area', 'Orientacion_Academica_Profesional', 'Orientación', ['area' => 'futuro']],
                ['Mi preparación', 'estudiante.area', 'Perfil_Academico', 'Orientación', ['area' => 'preparacion']],
                ['Mi plan', 'estudiante.area', 'Perfil_Academico', 'Orientación', ['area' => 'plan']],
                ['Fuentes académicas', 'estudiante.area', 'Materiales_Aula', 'Orientación', ['area' => 'fuentes']],
                ['Asistente de estudio', 'estudiante.area', 'Perfil_Academico', 'Orientación', ['area' => 'asistente']],
            ],
            default => [],
        };

        $links = [];
        foreach ($items as $item) {
            [$label, $route, $permission, $group] = $item;
            if (! Route::has($route) || ! $user->can($permission)) {
                continue;
            }
            $params = $item[4] ?? [];
            $links[] = compact('label', 'route', 'permission', 'group', 'params');
        }

        return $links;
    }

    private function institutional(string $actor): array
    {
        $route = $actor === 'Regente' ? 'regencia.consulta' : 'direccion.consulta';
        $areas = [
            ['Estudiantes', 'estudiantes', 'estudiantes.ver.institucional'],
            ['Cursos', 'cursos', 'cursos.ver.institucional'],
            ['Inscripciones', 'inscripciones', 'inscripciones.ver.institucional'],
            ['Rendimiento', 'rendimiento', 'calificaciones.ver.institucional'],
            ['Asistencia', 'asistencia', 'asistencia.ver.institucional'],
            ['LMS', 'lms', 'cursos.ver.institucional'],
        ];
        $extra = [['Calendario', ($actor === 'Regente' ? 'regencia' : 'direccion').'.calendario', $actor === 'Regente' ? 'cursos.ver.institucional' : 'cursos.ver.institucional', 'Consulta y seguimiento']];
        if ($actor === 'Regente') {
            $extra[] = ['Mis grados', 'regencia.grados', 'cursos.ver.institucional', 'Consulta y seguimiento'];
            $extra[] = ['Reportes de mis grados', 'regencia.reportes', 'reportes.ver.institucional', 'Reportes'];
        }
        if ($actor === 'Director') {
            $areas = [...$areas,
                ['Docentes', 'docentes', 'Docentes'],
                ['Orientación', 'orientacion', 'orientacion.ver.institucional'],
                ['Reportes', 'reportes', 'reportes.ver.institucional'],
                ['Gestión académica', 'gestion', 'Gestion_Academica'],
            ];
        }

        return [...array_map(fn ($item) => [$item[0], $route, $item[2], 'Consulta y seguimiento', ['area' => $item[1]]], $areas), ...$extra];
    }
}
