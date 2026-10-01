<?php

namespace Tests\Unit;

use App\Http\Middleware\EnsureActorRole;
use App\Livewire\Admin\GestionPersonas;
use App\Livewire\Admin\GestionUsuarios;
use App\Livewire\InstitutionalAuthorization;
use App\Models\Curso;
use App\Models\Estudiante;
use App\Models\User;
use App\Services\AcademicAccessService;
use App\Services\AulaVirtual\CursoVirtualService;
use App\Services\InstitutionalDashboardService;
use Illuminate\Http\Request;
use Mockery;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class AuthorizationBoundariesTest extends TestCase
{
    public function test_regent_without_assignment_has_no_institutional_scope(): void
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->est_usu = 'ACTIVO';
        $user->shouldReceive('hasAnyRole')->andReturn(false);
        $user->shouldReceive('hasRole')->andReturn(false);
        $courses = Mockery::mock(CursoVirtualService::class);
        $courses->shouldNotReceive('docenteDeUsuario');
        $courses->shouldNotReceive('estudianteDeUsuario');
        $service = new AcademicAccessService($courses);

        $this->assertFalse($service->canViewStudent($user, new Estudiante));
        $this->assertFalse($service->canViewCourse($user, new Curso));
        $this->assertFalse($service->canManageGrade($user, 'EST_1', 'ASI_1'));
        $this->assertSame([], (new InstitutionalDashboardService)->for('Regente')['metrics']);
    }

    public function test_student_cannot_read_another_student(): void
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->est_usu = 'ACTIVO';
        $user->shouldReceive('hasAnyRole')->andReturn(false);
        $user->shouldReceive('hasRole')->andReturnUsing(fn ($role) => $role === 'Estudiante');
        $user->shouldReceive('canAny')->andReturn(true);
        $courses = Mockery::mock(CursoVirtualService::class);
        $courses->shouldReceive('estudianteDeUsuario')->with($user)->andReturn(new Estudiante(['cod_est' => 'EST_A']));
        $service = new AcademicAccessService($courses);

        $this->assertTrue($service->canViewStudent($user, new Estudiante(['cod_est' => 'EST_A'])));
        $this->assertFalse($service->canViewStudent($user, new Estudiante(['cod_est' => 'EST_B'])));
    }

    public function test_inactive_account_cannot_enter_workspace(): void
    {
        $user = new User(['est_usu' => 'INACTIVO']);
        $request = Request::create('/admin');
        $request->setUserResolver(fn () => $user);
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('No tienes autorización');
        (new EnsureActorRole)->handle($request, fn () => response('No debe ejecutarse'), 'Administrador');
    }

    public function test_unlinked_user_cannot_resolve_null_person_profiles(): void
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->shouldReceive('hasRole')->andReturn(true);
        $user->cod_per = null;
        $user->est_usu = 'ACTIVO';
        $courses = new CursoVirtualService;
        $this->assertNull($courses->estudianteDeUsuario($user));
        $this->assertNull($courses->docenteDeUsuario($user));
    }

    public function test_secretary_cannot_call_admin_account_component(): void
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->est_usu = 'ACTIVO';
        $user->shouldReceive('hasRole')->andReturnUsing(fn ($role) => $role === 'Secretaria');
        auth()->setUser($user);
        $hook = new InstitutionalAuthorization;
        $hook->setComponent(new GestionUsuarios);
        $this->expectException(HttpException::class);
        $hook->call('guardarUsuario', [], fn () => null);
    }

    public function test_revoked_permission_blocks_subsequent_livewire_action(): void
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->est_usu = 'ACTIVO';
        $user->shouldReceive('hasRole')->andReturnUsing(fn ($role) => $role === 'Administrador');
        $user->shouldReceive('can')->with('Registro_Personas')->andReturn(false);
        auth()->setUser($user);
        $hook = new InstitutionalAuthorization;
        $hook->setComponent(new GestionPersonas);
        $this->expectException(HttpException::class);
        $hook->call('guardarPersona', [], fn () => null);
    }
}
