<?php

namespace Tests\Feature;

use App\Livewire\Admin\GestionPersonas;
use App\Livewire\Admin\GestionUsuarios;
use App\Models\Persona;
use App\Models\User;
use App\Support\Usuarios\UsuarioInteligente;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BloqueCIdentidadAdministrativaTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::firstOrCreate(['name' => 'Registro_Personas', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'Gestion_Usuarios', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Administrador', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Docente', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Estudiante', 'guard_name' => 'web']);
    }

    public function test_gestion_personas_crea_persona_valida_y_rechaza_ci_duplicado(): void
    {
        $adminUser = User::factory()->create([
            'cod_usu' => 'USU_ADMIN_TEST_C1',
            'email' => 'admin_c1@savp.edu.bo',
            'email_verified_at' => now(),
        ]);
        $adminUser->givePermissionTo('Registro_Personas');

        $ciUnico = '88997711';

        // Creación exitosa
        Livewire::actingAs($adminUser)
            ->test(GestionPersonas::class)
            ->set('form.nom_per', 'Juan Carlos')
            ->set('form.ape_pat_per', 'Mamani')
            ->set('form.ape_mat_per', 'Quispe')
            ->set('form.ci_per', $ciUnico)
            ->set('form.exp_per', 'LP')
            ->set('form.fec_nac_per', '1990-05-15')
            ->set('form.gen_per', 'M')
            ->set('form.ema_per', 'juan.mamani.test@savp.edu.bo')
            ->set('form.est_per', 1)
            ->call('guardarPersona')
            ->assertDispatched('persona-creada');

        $this->assertDatabaseHas('persona', [
            'ci_per' => $ciUnico,
            'ape_pat_per' => 'Mamani',
        ]);

        // Intento de duplicado de CI es bloqueado por validación
        Livewire::actingAs($adminUser)
            ->test(GestionPersonas::class)
            ->set('form.nom_per', 'Pedro')
            ->set('form.ape_pat_per', 'Flores')
            ->set('form.ci_per', $ciUnico)
            ->set('form.exp_per', 'LP')
            ->set('form.fec_nac_per', '1992-03-10')
            ->set('form.gen_per', 'M')
            ->call('guardarPersona')
            ->assertHasErrors(['form.ci_per' => 'unique']);
    }

    public function test_gestion_usuarios_crea_usuario_con_persona_y_soporte_inteligente(): void
    {
        $adminUser = User::factory()->create([
            'cod_usu' => 'USU_ADMIN_TEST_C2',
            'email' => 'admin_c2@savp.edu.bo',
            'email_verified_at' => now(),
        ]);
        $adminUser->givePermissionTo('Gestion_Usuarios');

        $persona = Persona::create([
            'nom_per' => 'MARIA ELENA',
            'ape_pat_per' => 'CONDORI',
            'ape_mat_per' => 'ROJAS',
            'ci_per' => '77665544',
            'exp_per' => 'LP',
            'fec_nac_per' => '1995-08-20',
            'gen_per' => 'F',
            'ema_per' => 'maria.condori@savp.edu.bo',
            'est_per' => 1,
        ]);

        $soporte = app(UsuarioInteligente::class);
        $analisis = $soporte->analizarCreacion([
            'cod_per' => $persona->cod_per,
            'email' => 'maria.condori@savp.edu.bo',
            'role' => 'Docente',
            'est_usu' => 'ACTIVO',
        ]);

        $this->assertTrue($analisis['puede_guardar']);

        Livewire::actingAs($adminUser)
            ->test(GestionUsuarios::class)
            ->set('form.cod_per', $persona->cod_per)
            ->set('form.email', 'maria.condori.user@savp.edu.bo')
            ->set('form.password', 'Password@123')
            ->set('form.password_confirmation', 'Password@123')
            ->set('form.role', 'Docente')
            ->set('form.est_usu', 'ACTIVO')
            ->call('guardarUsuario')
            ->assertDispatched('usuario-creado');

        $this->assertDatabaseHas('users', [
            'cod_per' => $persona->cod_per,
            'email' => 'maria.condori.user@savp.edu.bo',
        ]);
    }

    public function test_mutacion_sin_permiso_es_bloqueada(): void
    {
        $usuarioSinPermiso = User::factory()->create([
            'cod_usu' => 'USU_SIN_PERMISO_C',
            'email' => 'sinpermiso_c@savp.edu.bo',
            'email_verified_at' => now(),
        ]);

        // Sin dar el permiso Gestion_Usuarios, al ejecutar guardarUsuario debe responder 403 Forbidden
        Livewire::actingAs($usuarioSinPermiso)
            ->test(GestionUsuarios::class)
            ->set('form.cod_per', 'PER_999')
            ->call('guardarUsuario')
            ->assertForbidden();
    }
}
