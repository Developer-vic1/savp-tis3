<?php

namespace App\Http\Controllers;

use App\Services\AulaVirtual\CursoVirtualService;
use App\Services\InstitutionalDashboardService;
use Illuminate\Http\Request;

class WorkspaceController extends Controller
{
    public function director(Request $request, InstitutionalDashboardService $dashboard)
    {
        return view('workspaces.institucional', $this->data($request, 'Director', $dashboard, [
            ['label' => 'Comunidad educativa', 'description' => 'Consulta institucional de estudiantes y docentes.'],
            ['label' => 'Gestión académica', 'description' => 'Seguimiento de cursos, asignaturas e inscripciones.'],
            ['label' => 'Rendimiento', 'description' => 'Lectura institucional de calificaciones registradas.'],
            ['label' => 'Asistencia y orientación', 'description' => 'Seguimiento agregado, sin modificar registros.'],
            ['label' => 'Reportes', 'description' => 'Información institucional autorizada.'],
        ]));
    }

    public function secretaria(Request $request, InstitutionalDashboardService $dashboard)
    {
        return view('workspaces.institucional', $this->data($request, 'Secretaria', $dashboard, [
            ['label' => 'Personas', 'description' => 'Identidad institucional y búsqueda previa al registro.'],
            ['label' => 'Estudiantes', 'description' => 'Perfiles estudiantiles y datos operativos.'],
            ['label' => 'Inscripciones', 'description' => 'Curso, paralelo, turno y especialidad.'],
            ['label' => 'Cuentas', 'description' => 'Operación de cuentas sin asignar privilegios críticos.'],
            ['label' => 'Reportes administrativos', 'description' => 'Consulta operativa autorizada.'],
        ]));
    }

    public function regencia(Request $request, InstitutionalDashboardService $dashboard)
    {
        return view('workspaces.institucional', $this->data($request, 'Regente', $dashboard, [
            ['label' => 'Cursos y estudiantes', 'description' => 'Consulta de grupos e inscripciones activas.'],
            ['label' => 'Asistencia', 'description' => 'Seguimiento de asistencia según alcance autorizado.'],
            ['label' => 'Estado académico', 'description' => 'Lectura de calificaciones, sin permisos de edición.'],
            ['label' => 'Reportes', 'description' => 'Información para seguimiento institucional.'],
        ]));
    }

    public function docente(Request $request, CursoVirtualService $cursos)
    {
        return view('aula-virtual.dashboard.docente', $cursos->dashboardDocente($request->user()));
    }

    public function estudiante(Request $request, CursoVirtualService $cursos)
    {
        return view('aula-virtual.dashboard.estudiante', $cursos->dashboardEstudiante($request->user()));
    }

    private function data(Request $request, string $actor, InstitutionalDashboardService $dashboard, array $modules): array
    {
        return $dashboard->for($actor) + [
            'actor' => $actor,
            'user' => $request->user(),
            'modules' => $modules,
        ];
    }
}
