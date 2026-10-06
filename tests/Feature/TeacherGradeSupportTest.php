<?php

namespace Tests\Feature;

use App\Livewire\Shared\TeacherGradeForm;
use App\Models\Oficial\AulaVirtual\ClaseVirtual;
use App\Models\Oficial\Academico\Calificacion;
use App\Models\Oficial\Academico\PlanAsignatura;
use App\Models\Oficial\Sistema\User;
use App\Services\AulaVirtual\CursoVirtualService;
use App\Services\GradeService;
use App\Support\Evaluacion\CalificacionInteligente;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Livewire;
use Mockery;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class TeacherGradeSupportTest extends TestCase
{
    private function actor(string $role = 'Docente', bool $allowed = true): User
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->forceFill(['cod_usu' => 'TEST_GRADE', 'est_usu' => 'ACTIVO']);
        $user->shouldReceive('hasRole')->andReturnUsing(fn ($name) => $name === $role);
        $user->shouldReceive('can', 'canAny')->andReturn($allowed);

        return $user;
    }

    public function test_livewire_grade_block_stops_save_and_suggestion_requires_explicit_application(): void
    {
        $user = $this->actor();
        $this->actingAs($user);
        $course = (new ClaseVirtual)->forceFill(['cod_cla' => 'CLA1', 'cod_pas' => 'PAS1']);
        $service = Mockery::mock(GradeService::class);
        $service->shouldReceive('teacherContext')->with($user, 'CLA1')->andReturn(['course' => $course, 'ready' => true, 'students' => collect(), 'periods' => collect()]);
        $service->shouldReceive('previewTeacherGrade')->with($user, 'CLA1', Mockery::type('array'), null)->andReturnUsing(function ($user, $courseId, $draft) {
            $valid = is_numeric($draft['not_cal']) && $draft['not_cal'] >= 0 && $draft['not_cal'] <= 100;

            return ['puede_guardar' => $valid, 'bloqueos' => $valid ? [] : ['La nota debe estar entre 0 y 100.'],
                'desempeno' => 'En seguimiento', 'completitud' => 100, 'datos' => ['obs_cal' => $draft['obs_cal'] ?: 'Acompañamiento académico sugerido.']];
        });
        // Frontera simulada: verifica la invocación autorizada, no una escritura real.
        $service->shouldReceive('save')->with($user, 'PAS1', 'EST1', 'PEV1', 55.0, '', null)->once()->andReturn(new Calificacion);
        $this->app->instance(GradeService::class, $service);
        $form = Livewire::test(TeacherGradeForm::class, ['curso' => 'CLA1'])
            ->set('form.cod_est', 'EST1')->set('form.cod_pev', 'PEV1')->set('form.not_cal', 120)
            ->assertSee('La nota debe estar entre 0 y 100.')->call('guardar')->assertHasErrors('form.not_cal')
            ->set('form.not_cal', 55)->assertHasNoErrors()->assertDontSee('La nota debe estar entre 0 y 100.')
            ->assertSee('Observación sugerida')->assertSet('form.obs_cal', '')
            ->call('aplicarObservacion')->assertSet('form.obs_cal', 'Acompañamiento académico sugerido.')
            ->set('form.obs_cal', '')->call('guardar')->assertRedirect(route('docente.cursos.calificaciones', 'CLA1'));
    }

    public function test_foreign_actor_is_rejected_before_grade_service(): void
    {
        $this->actingAs($this->actor('Secretaria'));
        $service = Mockery::mock(GradeService::class);
        $service->shouldReceive('teacherContext', 'previewTeacherGrade', 'save')->never();
        $this->app->instance(GradeService::class, $service);
        Livewire::test(TeacherGradeForm::class, ['curso' => 'CLA1'])->assertForbidden();
    }

    public function test_review_uses_server_grade_identity_and_cancel_resets_create_form(): void
    {
        $user = $this->actor();
        $this->actingAs($user);
        $course = (new ClaseVirtual)->forceFill(['cod_cla' => 'CLA1', 'cod_pas' => 'PAS1']);
        $grade = (new Calificacion)->forceFill(['cod_cal' => 'CAL1', 'cod_pas' => 'PAS1', 'cod_est' => 'EST1', 'cod_pev' => 'PEV1', 'not_cal' => 60, 'obs_cal' => 'Seguimiento', 'est_cal' => 'ACTIVO']);
        $service = Mockery::mock(GradeService::class);
        $service->shouldReceive('teacherContext')->with($user, 'CLA1')->andReturn(['course' => $course, 'ready' => true, 'students' => collect(), 'periods' => collect()]);
        $service->shouldReceive('teacherGrade')->with($user, 'CLA1', 'CAL1')->andReturn($grade);
        $service->shouldReceive('previewTeacherGrade')->with($user, 'CLA1', Mockery::type('array'), 'CAL1')
            ->andReturn(['puede_guardar' => true, 'bloqueos' => [], 'completitud' => 100, 'datos' => ['obs_cal' => 'Seguimiento']]);
        $service->shouldReceive('save')->with($user, 'PAS1', 'EST1', 'PEV1', 70.0, 'Seguimiento', $grade)->once()->andReturn($grade);
        $this->app->instance(GradeService::class, $service);
        $form = Livewire::test(TeacherGradeForm::class, ['curso' => 'CLA1'])->call('editar', 'CAL1')
            ->assertSet('gradeId', 'CAL1')->assertSet('form.cod_est', 'EST1')->assertSee('Guardar revisión')
            ->call('cancelarEdicion')->assertSet('gradeId', null)->assertSet('form.not_cal', null)->assertSet('analisis', [])
            ->call('editar', 'CAL1')->set('form.not_cal', 70)->call('guardar')
            ->assertRedirect(route('docente.cursos.calificaciones', 'CLA1'));
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_review_lookup_is_scoped_to_teacher_plan_before_loading_grade(): void
    {
        $user = $this->actor();
        $course = (new ClaseVirtual)->forceFill(['cod_cla' => 'CLA1', 'cod_pas' => 'PAS1']);
        $courses = Mockery::mock(CursoVirtualService::class);
        $courses->shouldReceive('cursoParaDocente')->with($user, 'CLA1')->once()->andReturn($course);
        $service = Mockery::mock(GradeService::class, [$courses])->makePartial();
        $service->shouldReceive('available')->once()->andReturnTrue();
        $grades = Mockery::mock('alias:App\Models\Oficial\Academico\Calificacion');
        $query = Mockery::mock();
        $grades->shouldReceive('where')->with('cod_pas', 'PAS1')->once()->andReturn($query);
        $query->shouldReceive('whereKey')->with('FOREIGN')->once()->andReturnSelf();
        $query->shouldReceive('firstOrFail')->once()->andThrow(new ModelNotFoundException);
        $this->expectException(ModelNotFoundException::class);
        $service->teacherGrade($user, 'CLA1', 'FOREIGN');
    }

    public function test_review_cannot_change_student_or_period_to_hide_another_duplicate(): void
    {
        $user = $this->actor();
        $course = (new ClaseVirtual)->forceFill(['cod_cla' => 'CLA1', 'cod_pas' => 'PAS1']);
        $grade = (new Calificacion)->forceFill(['cod_cal' => 'CAL1', 'cod_est' => 'EST1', 'cod_pev' => 'PEV1']);
        $courses = Mockery::mock(CursoVirtualService::class);
        $courses->shouldReceive('cursoParaDocente')->with($user, 'CLA1')->once()->andReturn($course);
        $service = Mockery::mock(GradeService::class, [$courses])->makePartial();
        $service->shouldReceive('available')->once()->andReturnTrue();
        $service->shouldReceive('teacherGrade')->with($user, 'CLA1', 'CAL1')->once()->andReturn($grade);
        $support = Mockery::mock(CalificacionInteligente::class);
        $support->shouldReceive('analizar')->never();
        $this->app->instance(CalificacionInteligente::class, $support);
        $this->expectException(HttpException::class);
        $service->previewTeacherGrade($user, 'CLA1', ['cod_est' => 'EST1', 'cod_pev' => 'OTHER', 'not_cal' => 60, 'obs_cal' => ''], 'CAL1');
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_grade_support_never_receives_student_outside_correlated_enrollment(): void
    {
        $user = $this->actor();
        $plan = (new PlanAsignatura)->forceFill(['cod_gea' => 'GEA1', 'cod_cur' => 'CUR1', 'cod_par' => 'PAR1', 'cod_tur' => 'TUR1', 'cod_asi' => 'ASI1']);
        $course = (new ClaseVirtual)->forceFill(['cod_cla' => 'CLA1', 'cod_pas' => 'PAS1'])->setRelation('planAsignatura', $plan);
        $courses = Mockery::mock(CursoVirtualService::class);
        $courses->shouldReceive('cursoParaDocente')->with($user, 'CLA1')->once()->andReturn($course);
        $service = Mockery::mock(GradeService::class, [$courses])->makePartial();
        $service->shouldReceive('available')->once()->andReturnTrue();
        $enrollment = Mockery::mock('alias:App\Models\Oficial\Academico\InscripcionEstudiante');
        $query = Mockery::mock();
        $enrollment->shouldReceive('where')->with('cod_est', 'FOREIGN')->once()->andReturn($query);
        foreach (['est_ins' => 'ACTIVA', 'cod_gea' => 'GEA1', 'cod_cur' => 'CUR1', 'cod_par' => 'PAR1', 'cod_tur' => 'TUR1'] as $field => $value) {
            $query->shouldReceive('where')->with($field, $value)->once()->andReturnSelf();
        }
        $query->shouldReceive('exists')->once()->andReturnFalse();
        $support = Mockery::mock(CalificacionInteligente::class);
        $support->shouldReceive('analizar')->never();
        $this->app->instance(CalificacionInteligente::class, $support);
        $this->expectException(HttpException::class);
        $service->previewTeacherGrade($user, 'CLA1', ['cod_est' => 'FOREIGN', 'cod_pev' => 'PEV1', 'not_cal' => 55, 'obs_cal' => '']);
    }
}
