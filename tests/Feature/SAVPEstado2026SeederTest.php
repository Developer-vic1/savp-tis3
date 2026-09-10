<?php

namespace Tests\Feature;

use App\Models\Administrador;
use App\Models\Asignatura;
use App\Models\ConfiguracionCalendarioGestion;
use App\Models\Curso;
use App\Models\Director;
use App\Models\Docente;
use App\Models\EspecialidadTecnica;
use App\Models\EstadoAsistencia;
use App\Models\Estudiante;
use App\Models\GestionAcademica;
use App\Models\Horario;
use App\Models\HorarioBloque;
use App\Models\HorarioDetalle;
use App\Models\InscripcionEstudiante;
use App\Models\InscripcionVigencia;
use App\Models\Paralelo;
use App\Models\PeriodoEvaluacion;
use App\Models\Persona;
use App\Models\PersonalInstitucional;
use App\Models\PlanAsignatura;
use App\Models\PlanEspecialidad;
use App\Models\PlantillaHoraria;
use App\Models\SecretariaGeneral;
use App\Models\TipoVinculacionEstudiante;
use App\Models\Turno;
use App\Models\User;
use Database\Seeders\SAVPEstado2026Seeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SAVPEstado2026SeederTest extends TestCase
{
    /**
     * Verifica que SAVPEstado2026Seeder reconstruya íntegramente y de manera
     * idempotente el 100% de la base de datos oficial 2026.
     */
    public function test_savp_estado_2026_seeder_reconstruye_bd_e_idempotencia(): void
    {
        // 1. Ejecutar Seeder Maestro
        $this->seed(SAVPEstado2026Seeder::class);

        // 2. Validar Conteos Estructurales
        $this->assertSame(6, Role::count(), 'Deben existir exactamente 6 roles.');
        $this->assertSame(49, Permission::count(), 'Deben existir exactamente 49 permisos.');
        $this->assertSame(128, DB::table('role_has_permissions')->count(), 'Deben existir 128 asignaciones de permisos a roles.');
        $this->assertSame(668, Persona::count(), 'Deben existir 668 personas.');
        $this->assertSame(668, User::count(), 'Deben existir 668 usuarios.');
        $this->assertSame(663, DB::table('model_has_roles')->count(), 'Deben existir 663 asignaciones de roles a usuarios.');
        $this->assertSame(56, PersonalInstitucional::count(), 'Deben existir 56 personales institucionales.');
        $this->assertSame(1, Administrador::count(), 'Debe existir exactamente 1 Administrador.');
        $this->assertSame(1, Director::count(), 'Debe existir exactamente 1 Director.');
        $this->assertSame(1, SecretariaGeneral::count(), 'Debe existir exactamente 1 Secretaria General.');
        $this->assertSame(48, Docente::count(), 'Deben existir exactamente 48 docentes.');
        $this->assertSame(1, GestionAcademica::count(), 'Debe existir 1 gestión académica (2026).');
        $this->assertSame(3, ConfiguracionCalendarioGestion::count(), 'Deben existir 3 trimestres de calendario.');
        $this->assertSame(3, PeriodoEvaluacion::count(), 'Deben existir 3 periodos de evaluación.');
        $this->assertSame(6, Curso::count(), 'Deben existir 6 cursos.');
        $this->assertSame(4, Paralelo::count(), 'Deben existir 4 paralelos.');
        $this->assertSame(14, Asignatura::count(), 'Deben existir 14 asignaturas.');
        $this->assertSame(11, EspecialidadTecnica::count(), 'Deben existir 11 especialidades técnicas.');
        $this->assertSame(2, Turno::count(), 'Deben existir 2 turnos.');
        $this->assertSame(3, TipoVinculacionEstudiante::count(), 'Deben existir 3 tipos de vinculación.');
        $this->assertSame(5, DB::table('estado_asistencia')->count(), 'Deben existir 5 estados de asistencia.');
        $this->assertSame(6, PlantillaHoraria::count(), 'Deben existir 6 plantillas horarias.');
        $this->assertSame(51, HorarioBloque::count(), 'Deben existir 51 bloques horarios.');
        $this->assertSame(300, PlanAsignatura::count(), 'Deben existir 300 planes de asignatura.');
        $this->assertSame(68, PlanEspecialidad::count(), 'Deben existir 68 planes de especialidad.');
        $this->assertSame(136, Horario::count(), 'Deben existir 136 horarios.');
        $this->assertSame(4120, HorarioDetalle::count(), 'Deben existir 4,120 detalles de horario.');
        $this->assertSame(612, Estudiante::count(), 'Deben existir 612 estudiantes.');
        $this->assertSame(600, InscripcionEstudiante::count(), 'Deben existir 600 inscripciones.');
        $this->assertSame(0, InscripcionVigencia::count(), 'Deben existir 0 vigencias en estado inicial.');

        // 3. Validar Estudiantes sin Inscripción (exactamente 12)
        $estudiantesSinInscripcion = Estudiante::whereNotIn('cod_est', function ($query) {
            $query->select('cod_est')->from('inscripcion_estudiante');
        })->pluck('cod_est')->toArray();
        $this->assertCount(12, $estudiantesSinInscripcion, 'Deben existir exactamente 12 estudiantes sin inscripción.');
        $this->assertContains('EST_0601', $estudiantesSinInscripcion);
        $this->assertContains('EST_0612', $estudiantesSinInscripcion);

        // 4. Validar Ausencia de Códigos Legacy FT3
        $ft3Personas = Persona::where('cod_per', 'like', 'PER_FT3%')->count();
        $ft3Docentes = Docente::where('cod_doc', 'like', 'DOC_FT3%')->count();
        $this->assertSame(0, $ft3Personas, 'No deben existir códigos legacy PER_FT3.');
        $this->assertSame(0, $ft3Docentes, 'No deben existir códigos legacy DOC_FT3.');

        // 5. Validar que Psicología (ASI_0011) no tiene asignación artificial
        $planesPsicologia = PlanAsignatura::where('cod_asi', 'ASI_0011')->count();
        $this->assertSame(0, $planesPsicologia, 'Psicología (ASI_0011) no debe tener planes artificiales.');

        // 6. Validar Administrador Oficial
        $admin = User::where('cod_usu', 'USU_0001')->first();
        $this->assertNotNull($admin);
        $this->assertSame('PER_0001', $admin->cod_per);
        $this->assertTrue($admin->hasRole('Administrador'));

        // 7. Validar Prueba de Idempotencia (Segunda Ejecución)
        $this->seed(SAVPEstado2026Seeder::class);

        $this->assertSame(668, Persona::count(), 'Segunda ejecución no debe duplicar personas.');
        $this->assertSame(668, User::count(), 'Segunda ejecución no debe duplicar usuarios.');
        $this->assertSame(56, PersonalInstitucional::count(), 'Segunda ejecución no debe duplicar personal.');
        $this->assertSame(48, Docente::count(), 'Segunda ejecución no debe duplicar docentes.');
        $this->assertSame(612, Estudiante::count(), 'Segunda ejecución no debe duplicar estudiantes.');
        $this->assertSame(600, InscripcionEstudiante::count(), 'Segunda ejecución no debe duplicar inscripciones.');
        $this->assertSame(300, PlanAsignatura::count(), 'Segunda ejecución no debe duplicar planes de asignatura.');
        $this->assertSame(68, PlanEspecialidad::count(), 'Segunda ejecución no debe duplicar planes de especialidad.');
        $this->assertSame(136, Horario::count(), 'Segunda ejecución no debe duplicar horarios.');
        $this->assertSame(4120, HorarioDetalle::count(), 'Segunda ejecución no debe duplicar detalles de horario.');
    }
}
