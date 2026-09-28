<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\RoleDashboardResolver;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RoleDashboardResolverTest extends TestCase
{
    #[DataProvider('roles')]
    public function test_each_actor_resolves_to_an_independent_workspace(string $role, string $route): void
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('hasRole')->andReturnUsing(fn (string $candidate) => $candidate === $role);

        $this->assertSame($route, app(RoleDashboardResolver::class)->routeFor($user));
    }

    public function test_user_without_supported_role_has_no_workspace(): void
    {
        $user = Mockery::mock(User::class);
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
}
