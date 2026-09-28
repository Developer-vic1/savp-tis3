<?php

namespace Tests\Feature\Authorization;

use App\Models\Persona;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class WorkspaceAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('workspaceRoles')]
    public function test_dashboard_redirects_each_role_to_its_workspace(string $role, string $route): void
    {
        $user = $this->userWithRole($role);

        $this->actingAs($user)->get('/dashboard')->assertRedirect(route($route));
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
        $number = str_pad((string) (Persona::query()->count() + 1), 4, '0', STR_PAD_LEFT);
        $person = Persona::query()->create([
            'cod_per' => 'PER_'.$number,
            'nom_per' => $role,
            'ape_pat_per' => 'Prueba',
            'ci_per' => '9'.$number,
            'est_per' => true,
        ]);
        $user = User::query()->create([
            'cod_usu' => 'USU_'.$number,
            'cod_per' => $person->cod_per,
            'email' => strtolower($role).$number.'@example.test',
            'email_verified_at' => now(),
            'password' => Hash::make('Testing#123'),
            'est_usu' => 'ACTIVO',
        ]);
        $user->assignRole(Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']));

        return $user;
    }

    public static function workspaceRoles(): array
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
