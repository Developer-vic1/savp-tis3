<?php

namespace Tests\Feature;

use App\Livewire\Shared\InstitutionalQuery;
use App\Models\Curso;
use App\Models\User;
use App\Services\InstitutionalQueryService;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Livewire;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class InstitutionalQueryInteractionTest extends TestCase
{
    private function actor(string $role, bool $allowed = true): User
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->forceFill(['cod_usu' => 'TEST_QUERY', 'est_usu' => 'ACTIVO', 'email_verified_at' => now()]);
        $user->shouldReceive('hasRole')->andReturnUsing(fn ($name) => $name === $role);
        $user->shouldReceive('can', 'checkPermissionTo')->andReturn($allowed);

        return $user;
    }

    public static function forbiddenQueries(): array
    {
        return [['Secretaria', 'secretaria', 'rendimiento'], ['Secretaria', 'admin', 'cursos'],
            ['Regente', 'regencia', 'docentes'], ['Docente', 'direccion', 'cursos'], ['Estudiante', 'secretaria', 'turnos']];
    }

    #[DataProvider('forbiddenQueries')]
    public function test_actor_cannot_expand_workspace_or_area(string $role, string $workspace, string $area): void
    {
        try {
            (new InstitutionalQueryService)->authorizeQuery($this->actor($role), $area, $workspace);
            $this->fail('El área ajena debe rechazarse antes de consultar datos.');
        } catch (HttpException $error) {
            $this->assertSame(403, $error->getStatusCode());
        }
    }

    public function test_secretary_course_and_shift_routes_use_read_only_controller(): void
    {
        foreach (['cursos', 'turnos', 'gestion-academica'] as $path) {
            $route = app('router')->getRoutes()->getByName('secretaria.'.$path);
            $this->assertStringContainsString('InstitutionalQueryController@index', $route->getActionName());
        }
    }

    public function test_revoked_permission_denies_query_before_database_access(): void
    {
        $this->expectException(HttpException::class);
        (new InstitutionalQueryService)->authorizeQuery($this->actor('Director', false), 'cursos', 'direccion');
    }

    public function test_filters_retain_student_context_and_reset_dependent_course_without_reload(): void
    {
        $this->actingAs($this->actor('Director'));
        $service = Mockery::mock(InstitutionalQueryService::class)->makePartial();
        $service->shouldReceive('search')->andReturnUsing(function ($request, $area, $workspace) {
            $this->assertSame('direccion', $workspace);
            $this->assertSame('estudiantes', $area);

            return ['title' => 'Estudiantes', 'rows' => new LengthAwarePaginator([], 0, 20), 'columns' => ['cod_est' => 'Código'],
                'dashboardRoute' => 'direccion.dashboard', 'years' => collect(), 'courses' => collect(), 'area' => $area,
                'search' => $request->input('search'), 'gestion' => $request->input('gestion'), 'course' => $request->input('curso'), 'studentFilter' => $request->input('estudiante')];
        });
        $this->app->instance(InstitutionalQueryService::class, $service);
        Livewire::test(InstitutionalQuery::class, ['area' => 'estudiantes', 'workspace' => 'direccion'])
            ->set('studentFilter', 'EST1')->set('course', 'CUR1')->set('search', 'Ana')
            ->assertSet('studentFilter', 'EST1')->assertSet('course', 'CUR1')
            ->set('parallel', 'PAR1')->set('shift', 'TUR1')->set('level', 'Primaria')
            ->set('gestion', 'GEA2')->assertSet('course', '')->assertSet('studentFilter', 'EST1')
            ->assertSet('parallel', '')->assertSet('shift', '')->assertSet('level', 'Primaria')
            ->assertSee('No hay resultados para la búsqueda.')
            ->call('limpiar')->assertSet('studentFilter', '')->assertSet('search', '')
            ->assertSet('level', '')
            ->assertSee('No existen registros disponibles.');
    }

    public function test_secretary_course_catalog_keeps_filters_and_read_only_detail_action(): void
    {
        $this->actingAs($this->actor('Secretaria'));
        $service = Mockery::mock(InstitutionalQueryService::class)->makePartial();
        $service->shouldReceive('search')->andReturnUsing(function ($request, $area, $workspace) {
            $this->assertSame('secretaria', $workspace);
            $this->assertSame('cursos', $area);
            $record = (new Curso)->forceFill(['cod_cur' => 'CUR1', 'nom_cur' => 'Primero', 'niv_cur' => 'Primaria', 'est_cur' => 'ACTIVO']);

            return ['title' => 'Cursos', 'rows' => new LengthAwarePaginator([$record], 1, 20), 'columns' => ['cod_cur' => 'Código', 'nom_cur' => 'Curso'],
                'dashboardRoute' => 'secretaria.dashboard', 'years' => collect(), 'courses' => collect(), 'area' => $area,
                'search' => $request->input('search'), 'gestion' => $request->input('gestion'), 'course' => $request->input('curso'),
                'studentFilter' => $request->input('estudiante'), 'levels' => collect(['Primaria']), 'parallels' => collect(), 'shifts' => collect()];
        });
        $this->app->instance(InstitutionalQueryService::class, $service);
        Livewire::test(InstitutionalQuery::class, ['area' => 'cursos', 'workspace' => 'secretaria'])
            ->assertSee('Nivel registrado')->assertSee('Paralelo')->assertSee('Turno')->assertSee('Ver detalle')
            ->assertDontSee('Editar')->assertDontSee('Ver estudiantes')
            ->set('level', 'Primaria')->set('parallel', 'PAR1')->set('shift', 'TUR1')
            ->assertSet('level', 'Primaria')->assertSet('parallel', 'PAR1')->assertSet('shift', 'TUR1')
            ->call('limpiar')->assertSet('level', '')->assertSet('parallel','')->assertSet('shift','');
    }
}
