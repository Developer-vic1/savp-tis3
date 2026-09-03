<?php

namespace Tests\Feature;

use App\Livewire\Admin\Calificaciones;
use App\Models\Asignatura;
use App\Models\Calificacion;
use App\Models\Estudiante;
use App\Models\PeriodoEvaluacion;
use App\Models\Persona;
use App\Models\User;
use App\Support\Evaluacion\CalificacionInteligente;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class BloqueDNucleoAcademicoTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::firstOrCreate(['name' => 'Calificaciones', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'Inscripciones', 'guard_name' => 'web']);
    }

    public function test_calificacion_inteligente_valida_escala_1_a_100(): void
    {
        $soporte = app(CalificacionInteligente::class);

        $analisisValido = $soporte->analizar([
            'not_cal' => 85,
            'cod_est' => 'EST_001',
            'cod_asi' => 'ASI_001',
            'cod_pev' => 'PEV_001',
        ]);

        $this->assertTrue($analisisValido['puede_guardar']);
        $this->assertSame(85.0, $analisisValido['datos']['not_cal']);

        $analisisInvalido = $soporte->analizar([
            'not_cal' => 105,
            'cod_est' => 'EST_001',
            'cod_asi' => 'ASI_001',
            'cod_pev' => 'PEV_001',
        ]);

        $this->assertFalse($analisisInvalido['puede_guardar']);
        $this->assertNotEmpty($analisisInvalido['bloqueos']);
    }

    public function test_guardar_calificacion_valida_en_livewire(): void
    {
        $admin = User::factory()->create([
            'cod_usu' => 'USU_ADMIN_TEST_D1',
            'email' => 'admin_d1@savp.edu.bo',
            'email_verified_at' => now(),
        ]);
        $admin->givePermissionTo('Calificaciones');

        $persona = Persona::create([
            'nom_per' => 'CARLOS',
            'ape_pat_per' => 'VARGAS',
            'ci_per' => '6543210',
            'exp_per' => 'LP',
            'fec_nac_per' => '2008-03-12',
            'gen_per' => 'M',
            'est_per' => 1,
        ]);

        $tve = \App\Models\TipoVinculacionEstudiante::firstOrCreate(
            ['nom_tve' => 'REGULAR'],
            ['des_tve' => 'Estudiante Regular', 'est_tve' => 'ACTIVO']
        );

        $estudiante = Estudiante::create([
            'cod_per' => $persona->cod_per,
            'rud_est' => 'RUDE_CAL_01',
            'cod_tve' => $tve->cod_tve,
            'est_est' => 'ACTIVO',
        ]);

        $asignatura = Asignatura::firstOrCreate(
            ['cod_asi' => 'ASI_MAT_TEST'],
            ['nom_asi' => 'Matemáticas Test', 'est_asi' => 'ACTIVO']
        );

        $periodo = PeriodoEvaluacion::firstOrCreate(
            ['cod_pev' => 'PEV_1T_TEST'],
            ['nom_pev' => '1er Trimestre Test', 'est_pev' => 'ACTIVO', 'ord_pev' => 1]
        );

        Livewire::actingAs($admin)
            ->test(Calificaciones::class)
            ->set('form.cod_est', $estudiante->cod_est)
            ->set('form.cod_asi', $asignatura->cod_asi)
            ->set('form.cod_pev', $periodo->cod_pev)
            ->set('form.not_cal', 95)
            ->set('form.est_cal', 'ACTIVO')
            ->call('guardar')
            ->assertDispatched('swal:success');

        $this->assertDatabaseHas('calificacion', [
            'cod_est' => $estudiante->cod_est,
            'cod_asi' => $asignatura->cod_asi,
            'cod_pev' => $periodo->cod_pev,
            'not_cal' => 95,
        ]);
    }

    public function test_calificacion_sin_permiso_es_bloqueada(): void
    {
        $usuarioSinPermiso = User::factory()->create([
            'cod_usu' => 'USU_SIN_PERMISO_D',
            'email' => 'sinpermiso_d@savp.edu.bo',
            'email_verified_at' => now(),
        ]);

        Livewire::actingAs($usuarioSinPermiso)
            ->test(Calificaciones::class)
            ->set('form.cod_est', 'EST_001')
            ->call('guardar')
            ->assertForbidden();
    }
}
