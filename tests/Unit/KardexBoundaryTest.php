<?php

namespace Tests\Unit;

use App\Contracts\KardexRepository;
use App\Models\Estudiante;
use App\Models\User;
use App\Policies\KardexPolicy;
use App\Services\AcademicAccessService;
use App\Services\Kardex\KardexService;
use App\Services\Kardex\UnavailableKardexRepository;
use Mockery;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class KardexBoundaryTest extends TestCase
{
    public function test_missing_contract_returns_explicit_conflict_without_fake_timeline(): void
    {
        $policy = Mockery::mock(KardexPolicy::class);
        $policy->shouldReceive('view')->once()->andReturnTrue();
        $service = new KardexService(new UnavailableKardexRepository, $policy);
        $this->assertFalse($service->available());
        try {
            $service->timeline(new User, new Estudiante);
            $this->fail('No debe entregar un historial vacío que parezca persistencia habilitada.');
        } catch (HttpException $error) {
            $this->assertSame(409, $error->getStatusCode());
        }
    }

    public function test_denied_student_access_never_calls_repository(): void
    {
        $repository = Mockery::mock(KardexRepository::class);
        $repository->shouldNotReceive('available');
        $repository->shouldNotReceive('timeline');
        $policy = Mockery::mock(KardexPolicy::class);
        $policy->shouldReceive('view')->once()->andReturnFalse();
        try {
            (new KardexService($repository, $policy))->timeline(new User, new Estudiante);
            $this->fail('El acceso ajeno debe rechazarse.');
        } catch (HttpException $error) {
            $this->assertSame(403, $error->getStatusCode());
        }
    }

    public function test_secretary_cannot_write_even_with_proposed_write_permission(): void
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->est_usu = 'ACTIVO';
        $user->shouldReceive('hasRole')->andReturnUsing(fn ($role) => $role === 'Secretaria');
        $user->shouldReceive('can')->andReturnTrue();
        $this->assertFalse((new KardexPolicy)->create($user, new Estudiante));
    }

    public function test_granted_read_permission_does_not_bypass_student_scope(): void
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->est_usu = 'ACTIVO';
        $user->shouldReceive('hasRole')->andReturnUsing(fn ($role) => $role === 'Docente');
        $user->shouldReceive('can')->with('kardex.ver.curso')->andReturnTrue();
        $access = Mockery::mock(AcademicAccessService::class);
        $access->shouldReceive('canViewStudent')->andReturnFalse();
        $this->app->instance(AcademicAccessService::class, $access);
        $this->assertFalse((new KardexPolicy)->view($user, new Estudiante));
    }
}
