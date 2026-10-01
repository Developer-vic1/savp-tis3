<?php

namespace Tests\Unit;

use App\Http\Middleware\EnsureActorRole;
use App\Models\User;
use App\Services\RoleDashboardResolver;
use Illuminate\Http\Request;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class RoleDashboardResolverTest extends TestCase
{
    #[DataProvider('roles')]
    public function test_each_actor_resolves_to_an_independent_workspace(string $role, string $route): void
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->est_usu = 'ACTIVO';
        $user->shouldReceive('hasRole')->andReturnUsing(fn (string $candidate) => $candidate === $role);

        $this->assertSame($route, app(RoleDashboardResolver::class)->routeFor($user));
    }

    public function test_user_without_supported_role_has_no_workspace(): void
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->est_usu = 'ACTIVO';
        $user->shouldReceive('hasRole')->andReturnFalse();

        $this->assertNull(app(RoleDashboardResolver::class)->routeFor($user));
    }

    public static function roles(): array
    {
        return [
            ['Administrador', 'admin.dashboard'],
            ['Director', 'direccion.dashboard'],
            ['Secretaria', 'secretaria.dashboard'],
            ['Regente', 'regencia.dashboard'],
            ['Docente', 'docente.dashboard'],
            ['Estudiante', 'estudiante.dashboard'],
        ];
    }

    public function test_multiple_actors_block_resolution_even_with_complementary_roles(): void
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->est_usu = 'ACTIVO';
        $user->shouldReceive('hasRole')->andReturnUsing(fn ($role) => in_array($role, ['Director', 'Docente'], true));
        $this->assertNull(app(RoleDashboardResolver::class)->roleFor($user));
        $this->assertNull(app(RoleDashboardResolver::class)->routeFor($user));
    }

    public function test_inactive_user_has_no_workspace(): void
    {
        $user = new User(['est_usu' => 'INACTIVO']);
        $this->assertNull(app(RoleDashboardResolver::class)->routeFor($user));
    }

    public function test_middleware_blocks_a_user_with_two_actors(): void
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->est_usu = 'ACTIVO';
        $user->shouldReceive('hasRole')->andReturnUsing(fn ($role) => in_array($role, ['Administrador', 'Estudiante'], true));
        $request = Request::create('/admin');
        $request->setUserResolver(fn () => $user);
        $this->expectException(HttpException::class);
        (new EnsureActorRole)->handle($request, fn () => response('No permitido'), 'Administrador');
    }
}
