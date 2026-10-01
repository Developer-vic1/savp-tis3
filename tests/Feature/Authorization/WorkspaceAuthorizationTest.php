<?php

namespace Tests\Feature\Authorization;

use App\Models\User;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class WorkspaceAuthorizationTest extends TestCase
{
    #[DataProvider('workspaceRoles')]
    public function test_role_identity_does_not_bypass_revoked_module_permission(string $role, string $url): void
    {
        $user = $this->userWithRole($role);
        $user->shouldReceive('can', 'checkPermissionTo')->andReturnFalse();
        $this->actingAs($user)->get($url)->assertForbidden();
    }

    public function test_student_cannot_enter_administration(): void
    {
        $this->actingAs($this->userWithRole('Estudiante'))->get('/admin')->assertForbidden();
    }

    public function test_director_cannot_use_admin_workspace(): void
    {
        $this->actingAs($this->userWithRole('Director'))->get('/admin')->assertForbidden();
    }

    public function test_secretary_cannot_use_teacher_grade_route(): void
    {
        $this->actingAs($this->userWithRole('Secretaria'))
            ->get('/docente/cursos/CLA_X/calificaciones')
            ->assertForbidden();
    }

    private function userWithRole(string $role): User
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->forceFill([
            'cod_usu' => 'TEST_WORKSPACE',
            'email' => strtolower($role).'@example.test',
            'email_verified_at' => now(),
            'est_usu' => 'ACTIVO',
        ]);
        $user->shouldReceive('hasRole')->andReturnUsing(fn ($name) => $name === $role);

        return $user;
    }

    public static function workspaceRoles(): array
    {
        return [
            ['Administrador', '/admin/gestion-personas'],
            ['Director', '/direccion/consultas/cursos'],
            ['Secretaria', '/secretaria/personas'],
            ['Regente', '/regencia/consultas/estudiantes'],
            ['Docente', '/docente'],
            ['Estudiante', '/estudiante'],
        ];
    }
}
