<?php

namespace Tests\Feature;

use App\Models\Oficial\Sistema\User;
use App\Services\AulaVirtual\CursoVirtualService;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/** HTTP con identidades simuladas; no usa RefreshDatabase ni consulta datos institucionales. */
class WorkspaceHttpBoundaryTest extends TestCase
{
    private function actor(array $roles, string $state = 'ACTIVO'): User
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->forceFill(['cod_usu' => 'TEST_USER', 'est_usu' => $state, 'email_verified_at' => now()]);
        $user->shouldReceive('hasRole')->andReturnUsing(fn ($role) => in_array($role, $roles, true));
        $user->shouldReceive('can')->andReturnTrue();
        $user->shouldReceive('checkPermissionTo')->andReturnTrue();

        return $user;
    }

    public static function actors(): array
    {
        return [
            ['Administrador', 'admin.dashboard', '/direccion'],
            ['Director', 'direccion.dashboard', '/admin'],
            ['Secretaria', 'secretaria.dashboard', '/docente'],
            ['Regente', 'regencia.dashboard', '/secretaria'],
            ['Docente', 'docente.dashboard', '/regencia'],
            ['Estudiante', 'estudiante.dashboard', '/docente'],
        ];
    }

    #[DataProvider('actors')]
    public function test_dashboard_redirects_each_actor_and_blocks_another_workspace(string $role, string $route, string $foreign): void
    {
        $this->actingAs($this->actor([$role]), 'web');
        $this->get('/dashboard')->assertRedirect(route($route));
        $this->get($foreign)->assertForbidden();
    }

    public static function invalidIdentities(): array
    {
        return [[[], 'ACTIVO'], [['Docente', 'Director'], 'ACTIVO'], [['Administrador'], 'INACTIVO']];
    }

    #[DataProvider('invalidIdentities')]
    public function test_invalid_identity_has_no_dashboard(array $roles, string $state): void
    {
        $this->actingAs($this->actor($roles, $state), 'web')->get('/dashboard')->assertForbidden();
    }

    public static function courseActors(): array
    {
        return [['Docente', 'cursoParaDocente', '/aula-virtual/mis-cursos/FOREIGN'],
            ['Estudiante', 'cursoParaEstudiante', '/aula-virtual/mis-asignaturas/FOREIGN']];
    }

    #[DataProvider('courseActors')]
    public function test_foreign_course_is_forbidden_even_with_module_permissions(string $role, string $method, string $url): void
    {
        $user = $this->actor([$role]);
        $courses = Mockery::mock(CursoVirtualService::class);
        $courses->shouldReceive($method)->with($user, 'FOREIGN')->once()->andReturnNull();
        $this->app->instance(CursoVirtualService::class, $courses);
        $this->actingAs($user, 'web')->get($url)->assertForbidden();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect(route('login'));
    }
}
