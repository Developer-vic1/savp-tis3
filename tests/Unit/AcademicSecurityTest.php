<?php

namespace Tests\Unit;

use App\Models\Calificacion;
use App\Models\Curso;
use App\Models\Estudiante;
use App\Models\ReporteGenerado;
use App\Models\User;
use App\Policies\ReporteGeneradoPolicy;
use App\Services\AcademicAccessService;
use App\Services\AulaVirtual\CursoVirtualService;
use App\Services\GradeService;
use App\Services\OperationalAccountService;
use App\Services\RegencyAccessService;
use Mockery;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class AcademicSecurityTest extends TestCase
{
    public function test_hidden_material_is_denied_even_when_student_has_course_access(): void
    {
        $actor = Mockery::mock(User::class)->makePartial();
        $actor->shouldReceive('can')->with('Acceso_Aula_Virtual')->andReturn(true);
        $courses = Mockery::mock(CursoVirtualService::class);
        $courses->shouldNotReceive('cursoParaEstudiante');
        $courses->shouldReceive('cursoParaDocente')->andReturnNull();
        $this->app->instance(CursoVirtualService::class, $courses);
        $material = new \App\Models\AulaVirtual\MaterialClase(['cod_cla' => 'A', 'est_mat' => 'OCULTO']);
        $this->assertFalse((new \App\Policies\AulaVirtualMaterialPolicy())->view($actor, $material));
    }

    public function test_teacher_cannot_grade_delivery_from_another_course(): void
    {
        $actor = Mockery::mock(User::class)->makePartial();
        $actor->shouldReceive('can')->with('Aula_Virtual_Docente')->andReturn(true);
        $courses = Mockery::mock(CursoVirtualService::class);
        $courses->shouldReceive('cursoParaDocente')->with($actor, 'B')->andReturnNull();
        $this->app->instance(CursoVirtualService::class, $courses);
        $delivery = new \App\Models\AulaVirtual\EntregaTarea();
        $delivery->setRelation('tarea', new \App\Models\AulaVirtual\Tarea(['cod_cla' => 'B']));
        $this->assertFalse((new \App\Policies\AulaVirtualEntregaPolicy())->grade($actor, $delivery));
    }

    public function test_student_cannot_submit_draft_task(): void
    {
        $this->assertFalse((new \App\Policies\AulaVirtualTareaPolicy())->submit(new User(), new \App\Models\AulaVirtual\Tarea(['est_tar' => 'BORRADOR'])));
    }

    public function test_inactive_account_is_denied_by_all_academic_entry_points(): void
    {
        $actor = new User(['est_usu' => 'INACTIVO']);
        $courses = Mockery::mock(CursoVirtualService::class);
        $access = new AcademicAccessService($courses);
        $this->assertFalse($access->canViewStudent($actor, new Estudiante()));
        $this->assertFalse($access->canViewCourse($actor, new Curso()));
        $this->assertFalse($access->canViewGrade($actor, new Calificacion()));
        $this->assertFalse($access->canManageGrade($actor, 'E', 'A', 'P'));
    }

    public function test_teacher_without_active_profile_cannot_match_a_missing_plan(): void
    {
        $actor = Mockery::mock(User::class)->makePartial();
        $actor->est_usu = 'ACTIVO';
        $actor->shouldReceive('hasAnyRole')->andReturn(false);
        $actor->shouldReceive('hasRole')->andReturnUsing(fn ($role) => $role === 'Docente');
        $actor->shouldReceive('can')->andReturn(true);
        $courses = Mockery::mock(CursoVirtualService::class);
        $courses->shouldReceive('docenteDeUsuario')->andReturnNull();
        $grade = new Calificacion(['cod_pas' => 'P']);
        $grade->setRelation('planAsignatura', null);
        $this->assertFalse((new AcademicAccessService($courses))->canViewGrade($actor, $grade));
    }

    public function test_regent_cannot_read_unscoped_report_despite_legacy_permission(): void
    {
        $actor = Mockery::mock(User::class)->makePartial();
        $actor->est_usu = 'ACTIVO';
        $actor->shouldReceive('hasAnyRole')->with(['Administrador', 'Director'])->andReturn(false);
        $actor->shouldReceive('canAny')->andReturn(true);
        $this->assertFalse((new ReporteGeneradoPolicy())->view($actor, new ReporteGenerado()));
    }

    public function test_inactive_administrator_cannot_assign_regency(): void
    {
        $this->expectException(HttpException::class);
        (new RegencyAccessService())->assign(new User(['est_usu' => 'INACTIVO']), []);
    }

    public function test_regent_cannot_write_grades_even_with_accidentally_granted_permission(): void
    {
        $actor = Mockery::mock(User::class)->makePartial();
        $actor->est_usu = 'ACTIVO';
        $actor->shouldReceive('hasRole')->andReturnUsing(fn ($role) => $role === 'Regente');
        $actor->shouldReceive('can')->andReturn(true);
        $this->expectException(HttpException::class);
        (new GradeService(Mockery::mock(CursoVirtualService::class)))->save($actor, 'P', 'E', 'T', 90, null);
    }

    public function test_revoked_secretary_permission_blocks_account_write_before_database_access(): void
    {
        $actor = Mockery::mock(User::class)->makePartial();
        $actor->est_usu = 'ACTIVO';
        $actor->shouldReceive('hasRole')->with('Secretaria')->andReturn(true);
        $actor->shouldReceive('can')->with('usuarios.ver.institucional')->andReturn(true);
        $actor->shouldReceive('can')->with('usuarios.crear')->andReturn(false);
        $this->expectException(HttpException::class);
        (new OperationalAccountService())->save($actor, [], null);
    }
}
