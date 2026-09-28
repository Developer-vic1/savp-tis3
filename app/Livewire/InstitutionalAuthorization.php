<?php

namespace App\Livewire;

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
        'GestionAcademica', 'GestionCurso', 'GestionParalelo', 'GestionTurnos',
        'InstitucionProcedencia', 'TipoVinculacionEstudiante',
    ];

    public function skip(): bool
    {
        return ! str_starts_with($this->component::class, 'App\\Livewire\\Admin\\');
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
        $permission = self::MODULES[$name] ?? null;
        abort_unless($user && $user->est_usu === 'ACTIVO' && $permission
            && ($user->hasRole('Administrador') || ($user->hasRole('Secretaria') && in_array($name, self::SECRETARY, true)))
            && $user->can($permission), 403, 'No tienes autorización para realizar esta acción.');
    }
}
