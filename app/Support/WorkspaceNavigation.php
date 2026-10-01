<?php

namespace App\Support;

use App\Models\User;
use App\Services\RoleDashboardResolver;
use Illuminate\Support\Facades\Route;

final class WorkspaceNavigation
{
    private const ITEM_ICONS = [
        'Personas' => 'ph-identification-card',
        'Usuarios' => 'ph-users-three',
        'Personal institucional' => 'ph-buildings',
        'Estudiantes' => 'ph-student',
        'Docentes' => 'ph-chalkboard-teacher',
        'Gestión' => 'ph-graduation-cap',
        'Gestión académica' => 'ph-graduation-cap',
        'Cursos' => 'ph-squares-four',
        'Asignaturas' => 'ph-books',
        'Paralelos' => 'ph-git-branch',
        'Turnos' => 'ph-clock',
        'Inscripciones' => 'ph-notebook',
        'Especialidades técnicas' => 'ph-wrench',
        'Planes de asignatura' => 'ph-notepad',
        'Calendario' => 'ph-calendar-dots',
        'LMS institucional' => 'ph-book-open',
        'LMS' => 'ph-book-open',
        'Periodos' => 'ph-calendar-check',
        'Calificaciones' => 'ph-exam',
        'Roles y permisos' => 'ph-shield-check',
        'Asignaciones de Regencia' => 'ph-tree-structure',
        'Reportes académicos' => 'ph-chart-bar',
        'Reportes administrativos' => 'ph-chart-line-up',
        'Reportes de mis grados' => 'ph-chart-bar',
        'Reportes' => 'ph-chart-bar',
        'Bitácora' => 'ph-scroll',
        'Documentación' => 'ph-file-text',
        'Cuentas operativas' => 'ph-user-focus',
        'Procedencia' => 'ph-buildings',
        'Vinculación' => 'ph-arrows-left-right',
        'Rendimiento' => 'ph-chart-line-up',
        'Asistencia' => 'ph-calendar-check',
        'Orientación' => 'ph-compass',
        'Mis grados' => 'ph-squares-four',
        'Mis cursos' => 'ph-book-open',
        'Mis materias' => 'ph-books',
        'Mi progreso' => 'ph-chart-line-up',
        'Mi asistencia' => 'ph-calendar-check',
        'Mis intereses' => 'ph-compass',
        'Mi futuro académico' => 'ph-path',
        'Mi preparación' => 'ph-brain',
        'Mi plan' => 'ph-target',
        'Fuentes académicas' => 'ph-books',
        'Asistente de estudio' => 'ph-sparkle',
    ];

    private const GROUP_ICONS = [
        'Administración' => 'ph-gear-six',
        'Gestión académica' => 'ph-graduation-cap',
        'Académico' => 'ph-book-open',
        'Seguridad' => 'ph-shield-check',
        'Reportes' => 'ph-chart-bar',
        'Orientación' => 'ph-compass',
        'Seguimiento' => 'ph-pulse',
        'Consulta y seguimiento' => 'ph-magnifying-glass',
    ];

    private const ACTIVE_ALIASES = [
        'docente.cursos' => ['aula-virtual.docente.cursos', 'aula-virtual.docente.curso', 'docente.curso', 'docente.cursos.calificaciones'],
        'estudiante.materias' => ['aula-virtual.estudiante.asignaturas', 'aula-virtual.estudiante.curso', 'estudiante.materia'],
        'estudiante.asistencia' => ['aula-virtual.estudiante.asistencia'],
        'estudiante.intereses' => ['aula-virtual.estudiante.orientacion', 'aula-virtual.estudiante.orientacion.explorador', 'aula-virtual.estudiante.orientacion.resultados', 'aula-virtual.estudiante.orientacion.peter3', 'aula-virtual.estudiante.orientacion.peter3.score', 'aula-virtual.estudiante.orientacion.peter3.analysis', 'aula-virtual.estudiante.orientacion.peter3.query'],
    ];

    public static function groupIcon(string $group): string
    {
        return self::GROUP_ICONS[$group] ?? 'ph-squares-four';
    }

    public static function groupTone(string $group): string
    {
        return match ($group) {
            'Académico', 'Gestión académica', 'Seguimiento', 'Seguridad' => 'teal',
            'Orientación', 'Reportes', 'Consulta y seguimiento' => 'sky',
            default => 'emerald',
        };
    }

    public function isActive(array $link): bool
    {
        $route = request()->route()?->getName();
        if (! in_array($route, [$link['route'], ...(self::ACTIVE_ALIASES[$link['route']] ?? [])], true)) {
            return false;
        }

        foreach ($link['params'] as $key => $value) {
            if (request()->route($key) !== $value) {
                return false;
            }
        }

        return true;
    }

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
            $icon = self::ITEM_ICONS[$label] ?? 'ph-squares-four';
            $tone = self::groupTone($group);
            $links[] = compact('label', 'route', 'permission', 'group', 'params', 'icon', 'tone');
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
