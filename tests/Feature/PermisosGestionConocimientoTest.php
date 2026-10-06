<?php

namespace Tests\Feature;

use App\Models\Oficial\Sistema\Role;
use App\Models\Oficial\Sistema\User;
use Database\Seeders\PermisosGestionConocimientoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class PermisosGestionConocimientoTest extends TestCase
{
    use RefreshDatabase;

    public function test_permisos_oficiales_son_idempotentes_y_limitados_a_actores_aprobados(): void
    {
        $this->seed(PermisosGestionConocimientoSeeder::class);
        $this->seed(PermisosGestionConocimientoSeeder::class);
        $this->assertSame(3, Permission::where('name', 'like', 'conocimiento.%')->count());
        foreach (PermisosGestionConocimientoSeeder::PERMISSIONS as $name => $roles) {
            $expected = collect($roles)->sort()->values()->all();
            $actual = Permission::where('name', $name)->firstOrFail()->roles->pluck('name')->sort()->values()->all();
            $this->assertSame($expected, $actual);
        }
    }

    public function test_director_sin_permiso_de_lectura_no_accede_a_gestion_de_conocimiento(): void
    {
        $this->seed(PermisosGestionConocimientoSeeder::class);
        $role = Role::where('name', 'Director')->firstOrFail();
        $user = User::factory()->create();
        $user->assignRole($role);
        $role->revokePermissionTo('conocimiento.ver');

        $this->actingAs($user)->get('/conocimiento/fuentes')->assertForbidden();
        $this->get('/conocimiento/conexion')->assertForbidden();
        $this->postJson('/conocimiento/tutor/probar', ['question' => 'Hola'])->assertForbidden();
    }

    public function test_permisos_de_accion_son_obligatorios_ademas_del_rol(): void
    {
        $this->seed(PermisosGestionConocimientoSeeder::class);
        $role = Role::where('name', 'Administrador')->firstOrFail();
        $user = User::factory()->create();
        $user->assignRole($role);
        $role->revokePermissionTo(['conocimiento.proponer', 'conocimiento.revisar']);

        $this->actingAs($user)->postJson('/conocimiento/fuentes/analizar', [])->assertForbidden();
        $this->postJson('/conocimiento/fuentes/comprobar-url', ['url' => 'https://www.upb.edu/'])->assertForbidden();
        $this->postJson('/conocimiento/fuentes', [])->assertForbidden();
        $this->postJson('/conocimiento/fuentes/KGI-ABCDEF123456/revision', [])->assertForbidden();
    }
}
