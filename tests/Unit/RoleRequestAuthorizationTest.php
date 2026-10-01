<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\InstitutionalAuthorityService;
use App\Services\InstitutionalDocumentAnalyzer;
use App\Services\RoleRequestService;
use App\Support\InstitutionalRoleGovernance;
use Illuminate\Auth\Access\AuthorizationException;
use Mockery;
use PHPUnit\Framework\TestCase;

class RoleRequestAuthorizationTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function service(): RoleRequestService
    {
        return new RoleRequestService(new InstitutionalRoleGovernance, new InstitutionalAuthorityService, new InstitutionalDocumentAnalyzer);
    }

    private function actor(bool $admin, bool $module, bool $operation, string $status = 'ACTIVO'): User
    {
        $actor = Mockery::mock(User::class)->makePartial();
        $actor->est_usu = $status;
        $actor->shouldReceive('hasRole')->andReturnUsing(fn ($role) => $role === 'Administrador' && $admin);
        $actor->shouldReceive('can')->with('roles-permisos.gestionar')->andReturn($module);
        $actor->shouldReceive('can')->with('roles.solicitudes.crear')->andReturn($operation);

        return $actor;
    }

    public function test_non_administrator_is_rejected(): void
    {
        $this->expectException(AuthorizationException::class);
        $this->service()->authorize($this->actor(false, true, true), 'roles.solicitudes.crear');
    }

    public function test_revoked_granular_permission_is_rejected(): void
    {
        $this->expectException(AuthorizationException::class);
        $this->service()->authorize($this->actor(true, true, false), 'roles.solicitudes.crear');
    }

    public function test_missing_module_permission_is_rejected(): void
    {
        $this->expectException(AuthorizationException::class);
        $this->service()->authorize($this->actor(true, false, true), 'roles.solicitudes.crear');
    }

    public function test_valid_authorization_passes(): void
    {
        $this->service()->authorize($this->actor(true, true, true), 'roles.solicitudes.crear');
        $this->addToAssertionCount(1);
    }
}
