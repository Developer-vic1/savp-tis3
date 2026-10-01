<?php

namespace Tests\Unit;

use App\Models\MetaAcademica;
use App\Models\User;
use App\Policies\MetaAcademicaPolicy;
use App\Services\AcademicGoalService;
use App\Services\AulaVirtual\CursoVirtualService;
use App\Services\AulaVirtual\UnitContentService;
use App\Services\CalendarService;
use App\Services\Kardex\ScopedKardexRepository;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class PreparedPersistenceBoundaryTest extends TestCase
{
    private function actor(string $role): User
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->est_usu = 'ACTIVO';
        $user->shouldReceive('hasRole')->andReturnUsing(fn ($candidate) => $candidate === $role);
        $user->shouldReceive('can')->andReturnTrue();

        return $user;
    }

    public function test_unapproved_persistence_flags_do_not_inspect_a_database(): void
    {
        config(['features.kardex' => false, 'features.curricular_units' => false,
            'features.academic_goals' => false, 'features.institutional_calendar' => false]);
        Schema::shouldReceive('hasTable')->never();
        $this->assertFalse((new ScopedKardexRepository)->available());
        $this->assertFalse((new UnitContentService)->available());
        $this->assertFalse((new CalendarService)->institutionalAvailable());
        $this->assertSame(['available' => false, 'goals' => null], (new AcademicGoalService)->snapshot($this->actor('Estudiante')));
    }

    public function test_unapproved_goal_write_is_closed_before_profile_or_storage_queries(): void
    {
        config(['features.academic_goals' => false]);
        $courses = Mockery::mock(CursoVirtualService::class);
        $courses->shouldNotReceive('estudianteDeUsuario');
        $this->app->instance(CursoVirtualService::class, $courses);
        try {
            (new AcademicGoalService)->save($this->actor('Estudiante'), ['titulo' => 'Mi objetivo']);
            $this->fail('No debe escribir mientras el destino no está aprobado.');
        } catch (HttpException $error) {
            $this->assertSame(409, $error->getStatusCode());
        }
    }

    public function test_teacher_cannot_read_or_update_a_personal_student_goal(): void
    {
        $courses = Mockery::mock(CursoVirtualService::class);
        $courses->shouldNotReceive('estudianteDeUsuario');
        $this->app->instance(CursoVirtualService::class, $courses);
        $policy = new MetaAcademicaPolicy;
        $goal = new MetaAcademica;
        $this->assertFalse($policy->view($this->actor('Docente'), $goal));
        $this->assertFalse($policy->update($this->actor('Docente'), $goal));
    }
}
