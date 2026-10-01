<?php

namespace App\Livewire;

use App\Services\RoleDashboardResolver;
use Livewire\ComponentHook;

/** Reautoriza componentes reutilizados, también en peticiones Livewire posteriores. */
class InstitutionalAuthorization extends ComponentHook
{
    private const MODULES = [
        'GestionUsuarios' => 'Gestion_Usuarios',
        'RolesPermisos' => 'roles-permisos.gestionar',
        'AsignacionesRegencia' => 'regencia.asignaciones.gestionar',
        'GestionPersonas' => 'Registro_Personas',
        'GestionEstudiantes' => 'Estudiantes',
        'GestionInscripciones' => 'Inscripciones',
        'GestionAcademica' => 'Gestion_Academica',
        'GestionCurso' => 'Cursos',
        'GestionParalelo' => 'Paralelos',
        'GestionTurnos' => 'Turnos',
        'GestionAsignatura' => 'Asignaturas',
        'GestionDocente' => 'Docentes',
        'PersonalInstitucional' => 'Personal_Institucional',
        'EspecialidadesTecnicas' => 'Especialidades_Tecnicas',
        'PeriodoEvaluacion' => 'Periodo_Evaluacion',
        'PlanesAsignatura' => 'Planes_Asignatura',
        'InstitucionProcedencia' => 'Institucion_Procedencia',
        'TipoVinculacionEstudiante' => 'Tipo_Vinculacion_Estudiante',
        'Calificaciones' => 'Calificaciones',
        'ReportesAcademicos' => 'Reportes_Academicos',
        'ReportesAdministrativos' => 'Reportes_Administrativos',
        'Bitacora' => 'Bitacora',
    ];

    private const SECRETARY = [
        'GestionPersonas', 'GestionEstudiantes', 'GestionInscripciones',
        'GestionParalelo',
        'InstitucionProcedencia', 'TipoVinculacionEstudiante',
    ];

    public function skip(): bool
    {
        return ! str_starts_with($this->component::class, 'App\\Livewire\\Admin\\')
            && ! str_starts_with($this->component::class, 'App\\Livewire\\AulaVirtual\\')
            && ! str_starts_with($this->component::class, 'App\\Livewire\\Secretaria\\');
    }

    public function boot(): void
    {
        $this->authorizeComponent();
    }

    public function call($method, $params, $returnEarly): void
    {
        $this->authorizeComponent();
        // Estos auxiliares escriben datos y nunca son acciones de interfaz.
        abort_if(in_array($method, ['registrarBitacora', 'sincronizarDatosUsuarios'], true)
            && ! auth()->user()->hasRole('Administrador'), 403, 'No tienes autorización para realizar esta acción.');
    }

    private function authorizeComponent(): void
    {
        $user = auth()->user();
        $name = class_basename($this->component);
        if (str_starts_with($this->component::class, 'App\\Livewire\\AulaVirtual\\')) {
            $actor = $user ? app(RoleDashboardResolver::class)->roleFor($user) : null;
            $student = in_array($name, ['DashboardEstudiante', 'MisAsignaturasEstudiante', 'CursoDetalleEstudiante', 'EntregarTarea', 'MiAsistenciaEstudiante', 'OrientacionEstudiante', 'ResultadoOrientacion', 'ExploradorVocacional'], true);
            abort_unless($actor === ($student ? 'Estudiante' : 'Docente') && $user->can('Acceso_Aula_Virtual')
                && $user->can($student ? 'Aula_Virtual_Estudiante' : 'Aula_Virtual_Docente'), 403);
            if (str_contains($this->component::class, '\\Orientacion\\')) {
                abort_unless($user->can('Orientacion_Academica_Profesional'), 403);
            }
            foreach (['Materiales' => 'Materiales_Aula', 'Tareas' => 'Tareas_Aula', 'Entregas' => 'Entregas_Aula', 'Asistencia' => 'Asistencia_Aula'] as $folder => $required) {
                if (str_contains($this->component::class, '\\'.$folder.'\\')) {
                    abort_unless($user->can($required), 403);
                }
            }

            return;
        }
        if (str_starts_with($this->component::class, 'App\\Livewire\\Secretaria\\')) {
            abort_unless($user && app(RoleDashboardResolver::class)->roleFor($user) === 'Secretaria'
                && $user->can('usuarios.ver.institucional'), 403);

            return;
        }
        $permission = self::MODULES[$name] ?? null;
        abort_unless($user && $user->est_usu === 'ACTIVO' && $permission
            && (app(RoleDashboardResolver::class)->roleFor($user) === 'Administrador'
                || (app(RoleDashboardResolver::class)->roleFor($user) === 'Secretaria' && in_array($name, self::SECRETARY, true)))
            && $user->can($permission), 403, 'No tienes autorización para realizar esta acción.');
    }
}
