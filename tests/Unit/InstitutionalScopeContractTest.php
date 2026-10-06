<?php

namespace Tests\Unit;

use App\Models\Oficial\AulaVirtual\ClaseVirtual;
use App\Models\Oficial\Academico\Docente;
use App\Models\Oficial\Academico\Estudiante;
use App\Models\Oficial\Academico\InscripcionEstudiante;
use App\Models\Oficial\Sistema\User;
use App\Services\AulaVirtual\CursoVirtualService;
use App\Services\RegencyAccessService;
use Mockery;
use Tests\TestCase;

class InstitutionalScopeContractTest extends TestCase
{
    private function user(string $actor): User
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->forceFill(['est_usu' => 'ACTIVO', 'cod_per' => 'SELF']);
        $user->shouldReceive('hasRole')->andReturnUsing(fn ($role) => $role === $actor);

        return $user;
    }

    public function test_regency_sql_correlates_both_year_and_grade_and_active_management(): void
    {
        $access = Mockery::mock(RegencyAccessService::class)->makePartial();
        $access->shouldReceive('available')->once()->andReturnTrue();
        $query = $access->constrain(InscripcionEstudiante::query(), $this->user('Regente'), 'inscripcion_estudiante');
        $sql = $query->toSql();
        $this->assertStringContainsString('"ra"."cod_gea" = "inscripcion_estudiante"."cod_gea"', $sql);
        $this->assertStringContainsString('"ra"."cod_cur" = "inscripcion_estudiante"."cod_cur"', $sql);
        $this->assertStringContainsString('"scope_gea"."est_gea" = ?', $sql);
        $this->assertContains('SELF', $query->getBindings());
        $this->assertContains('ACTIVO', $query->getBindings());
    }

    public function test_missing_regency_structure_cannot_fall_back_to_global_scope(): void
    {
        $access = Mockery::mock(RegencyAccessService::class)->makePartial();
        $access->shouldReceive('available')->andReturnFalse();
        $this->assertStringContainsString('1 = 0', $access->constrain(InscripcionEstudiante::query(), $this->user('Regente'), 'inscripcion_estudiante')->toSql());
    }

    public function test_student_course_query_requires_all_four_enrollment_dimensions(): void
    {
        $user = $this->user('Estudiante');
        $courses = Mockery::mock(CursoVirtualService::class)->makePartial();
        $courses->shouldReceive('estudianteDeUsuario')->with($user)->andReturn(new Estudiante(['cod_est' => 'SELF_STUDENT']));
        $query = $courses->studentQuery($user);
        foreach (['cod_gea', 'cod_cur', 'cod_par', 'cod_tur'] as $field) {
            $this->assertStringContainsString('"inscripcion_estudiante"."'.$field.'" = "plan_asignatura"."'.$field.'"', $query->toSql());
        }
        $this->assertContains('ACTIVA', $query->getBindings());
        $this->assertContains('ACTIVO', $query->getBindings());
        $this->assertContains('SELF_STUDENT', $query->getBindings());
    }

    public function test_current_teacher_student_links_require_correlated_student_and_enrollment(): void
    {
        $user = $this->user('Docente');
        $courses = Mockery::mock(CursoVirtualService::class)->makePartial();
        $courses->shouldReceive('docenteDeUsuario')->with($user)->andReturn(new Docente(['cod_doc' => 'SELF_TEACHER']));
        $query = $courses->vinculosVigentes($user);
        $this->assertStringContainsString('"inscripcion_estudiante"."cod_est" = "clase_estudiante"."cod_est"', $query->toSql());
        $this->assertContains('SELF_TEACHER', $query->getBindings());
        $this->assertContains('ACTIVA', $query->getBindings());
    }

    public function test_no_tasks_do_not_show_fabricated_completion(): void
    {
        $class = new ClaseVirtual;
        $class->forceFill(['tareas_publicadas_count' => 0, 'tareas_pendientes_count' => 0, 'materiales_publicados_count' => 0]);
        $this->assertNull((new CursoVirtualService)->cursoResumen($class, new Estudiante)['progreso']);
    }
}
