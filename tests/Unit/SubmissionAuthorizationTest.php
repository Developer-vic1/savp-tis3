<?php

namespace Tests\Unit;

use App\Models\AulaVirtual\ClaseVirtual;
use App\Models\AulaVirtual\EntregaTarea;
use App\Models\AulaVirtual\Tarea;
use App\Models\Estudiante;
use App\Models\User;
use App\Policies\AulaVirtualEntregaPolicy;
use App\Services\AulaVirtual\CursoVirtualService;
use Mockery;
use Tests\TestCase;

class SubmissionAuthorizationTest extends TestCase
{
    private function delivery(): EntregaTarea
    {
        return (new EntregaTarea(['cod_est' => 'OWN']))->setRelation('tarea', new Tarea(['cod_cla' => 'CLASS']));
    }

    public function test_own_submission_download_requires_current_course_access(): void
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->shouldReceive('can')->andReturnUsing(fn ($permission) => $permission !== 'Aula_Virtual_Docente');
        $courses = Mockery::mock(CursoVirtualService::class);
        $courses->shouldReceive('estudianteDeUsuario')->with($user)->andReturn(new Estudiante(['cod_est' => 'OWN']));
        $courses->shouldReceive('cursoParaEstudiante')->with($user, 'CLASS')->andReturnNull();
        $this->app->instance(CursoVirtualService::class, $courses);
        $this->assertFalse((new AulaVirtualEntregaPolicy)->view($user, $this->delivery()));
    }

    public function test_enrolled_student_can_download_own_submission(): void
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->shouldReceive('can')->andReturnTrue();
        $courses = Mockery::mock(CursoVirtualService::class);
        $courses->shouldReceive('estudianteDeUsuario')->with($user)->andReturn(new Estudiante(['cod_est' => 'OWN']));
        $courses->shouldReceive('cursoParaEstudiante')->with($user, 'CLASS')->andReturn(new ClaseVirtual);
        $this->app->instance(CursoVirtualService::class, $courses);
        $this->assertTrue((new AulaVirtualEntregaPolicy)->view($user, $this->delivery()));
    }

    public function test_revoked_grading_permission_blocks_grade_but_allows_authorized_return(): void
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->shouldReceive('can')->andReturnUsing(fn ($permission) => $permission !== 'Calificaciones_Aula');
        $courses = Mockery::mock(CursoVirtualService::class);
        $courses->shouldReceive('cursoParaDocente')->with($user, 'CLASS')->andReturn(new ClaseVirtual(['est_cla' => 'ACTIVA']));
        $this->app->instance(CursoVirtualService::class, $courses);
        $policy = new AulaVirtualEntregaPolicy;
        $this->assertFalse($policy->grade($user, $this->delivery()));
        $this->assertTrue($policy->returnForCorrection($user, $this->delivery()));
    }

    public function test_closed_class_cannot_be_graded_or_returned(): void
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->shouldReceive('can')->andReturnTrue();
        $courses = Mockery::mock(CursoVirtualService::class);
        $courses->shouldReceive('cursoParaDocente')->with($user, 'CLASS')->andReturn(new ClaseVirtual(['est_cla' => 'CERRADA']));
        $this->app->instance(CursoVirtualService::class, $courses);
        $policy = new AulaVirtualEntregaPolicy;
        $this->assertFalse($policy->grade($user, $this->delivery()));
        $this->assertFalse($policy->returnForCorrection($user, $this->delivery()));
    }
}
