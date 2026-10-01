<?php

namespace Tests\Unit;

use App\Http\Middleware\EnsureActorRole;
use App\Livewire\AulaVirtual\Cursos\CursoDetalleEstudiante;
use App\Livewire\AulaVirtual\Materiales\CrearMaterial;
use App\Livewire\InstitutionalAuthorization;
use App\Livewire\Shared\ModuleSearch;
use App\Models\AulaVirtual\ClaseVirtual;
use App\Models\Estudiante;
use App\Models\User;
use App\Services\AcademicAccessService;
use App\Services\AulaVirtual\CursoVirtualService;
use Illuminate\Http\Request;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use Mockery;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class WorkspaceIntegrityTest extends TestCase
{
    private function actor(string $role): User
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->cod_usu = 'USU_TEST';
        $user->est_usu = 'ACTIVO';
        $user->shouldReceive('hasRole')->andReturnUsing(fn ($name) => $name === $role);
        $user->shouldReceive('can')->andReturn(true);
        $user->shouldReceive('checkPermissionTo')->andReturn(false);
        auth()->setUser($user);

        return $user;
    }

    public function test_module_search_only_shows_current_actor_routes(): void
    {
        $this->actor('Estudiante');
        Livewire::test(ModuleSearch::class)->set('search', 'materias')->assertSee('Mis materias')
            ->set('search', 'usuarios')->assertSee('No hay áreas disponibles')->assertDontSee('Gestión de usuarios');
    }

    public function test_livewire_lms_action_is_reauthorized_after_role_revocation(): void
    {
        $user = $this->actor('Director');
        $hook = new InstitutionalAuthorization;
        $hook->setComponent(new CrearMaterial);
        $this->expectException(HttpException::class);
        $hook->call('guardar', [], fn () => null);
    }

    public function test_two_institutional_actors_cannot_read_academic_data_through_a_service(): void
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->est_usu = 'ACTIVO';
        $user->shouldReceive('hasRole')->andReturnUsing(fn ($name) => in_array($name, ['Director', 'Estudiante'], true));
        $courses = Mockery::mock(CursoVirtualService::class);
        $courses->shouldNotReceive('estudianteDeUsuario');
        $this->assertFalse((new AcademicAccessService($courses))->canViewStudent($user, new Estudiante));
    }

    public function test_teacher_routes_cannot_be_entered_by_director_with_complementary_permissions(): void
    {
        $user = $this->actor('Director');
        $request = Request::create('/docente/cursos/CLA_FOREIGN');
        $request->setUserResolver(fn () => $user);
        $this->expectException(HttpException::class);
        (new EnsureActorRole)->handle($request, fn () => response('No autorizado'), 'Docente');
    }

    public function test_student_course_context_is_locked_in_livewire(): void
    {
        $this->actor('Estudiante');
        $class = new ClaseVirtual(['cod_cla' => 'CLA_OWN', 'nom_cla' => 'Materia propia']);
        $class->setRelation('planAsignatura', null);
        $service = Mockery::mock(CursoVirtualService::class);
        $service->shouldReceive('cursoParaEstudiante')->with(Mockery::type(User::class), 'CLA_OWN')->andReturn($class);
        $service->shouldReceive('estudianteDeUsuario')->andReturn(new Estudiante(['cod_est' => 'EST_OWN']));
        $service->shouldReceive('cursoResumen')->andReturn(['materiales' => 0, 'tareas_pendientes' => 0, 'progreso' => 0]);
        $this->app->instance(CursoVirtualService::class, $service);
        $component = Livewire::test(CursoDetalleEstudiante::class, ['curso' => 'CLA_OWN']);
        $component->assertSee('Materia propia');
        $this->expectException(CannotUpdateLockedPropertyException::class);
        $component->set('curso', 'CLA_FOREIGN');
    }
}
