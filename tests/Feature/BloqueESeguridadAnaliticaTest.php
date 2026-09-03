<?php

namespace Tests\Feature;

use App\Livewire\Admin\Bitacora;
use App\Models\User;
use App\Services\BitacoraService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class BloqueESeguridadAnaliticaTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::firstOrCreate(['name' => 'Bitacora', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'Gestion_Academica', 'guard_name' => 'web']);
    }

    public function test_bitacora_registra_y_enmascara_campos_sensibles(): void
    {
        BitacoraService::registrar(
            accion: 'LOGIN_TEST',
            tabla: 'users',
            registro: 'USU_001',
            modulo: 'Seguridad',
            nombreRegistro: 'Usuario Test',
            descripcion: 'Inicio de sesión de prueba.',
            valoresAnteriores: null,
            valoresNuevos: [
                'email' => 'admin@savp.edu.bo',
                'password' => 'Secreto123!',
                'token' => 'abc123token',
            ],
            nivel: 'INFO'
        );

        $bitacoraEntry = \App\Models\Bitacora::where('acc_bit', 'LOGIN_TEST')->latest('cod_bit')->first();
        $this->assertNotNull($bitacoraEntry);

        $payload = json_encode($bitacoraEntry->val_nue_bit);
        $this->assertStringNotContainsString('Secreto123!', $payload);
    }

    public function test_reportes_requieren_permiso_gestion_academica(): void
    {
        $usuarioSinPermiso = User::factory()->create([
            'cod_usu' => 'USU_SIN_PERMISO_E',
            'email' => 'sinpermiso_e@savp.edu.bo',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($usuarioSinPermiso)->get(route('admin.reportes.academico-general.pdf'));
        $response->assertStatus(403);
    }
}
