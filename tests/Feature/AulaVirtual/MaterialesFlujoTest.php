<?php

namespace Tests\Feature\AulaVirtual;

use App\Livewire\AulaVirtual\Materiales\CrearMaterial;
use App\Models\AulaVirtual\ClaseVirtual;
use App\Models\Docente;
use App\Models\Persona;
use App\Models\PersonalInstitucional;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class MaterialesFlujoTest extends TestCase
{
    use DatabaseTransactions;

    protected User $docenteUser;
    protected string $codCla;

    protected function setUp(): void
    {
        parent::setUp();

        $personaDoc = Persona::create([
            'nom_per' => 'Carla',
            'ape_pat_per' => 'Vargas',
            'ci_per' => '5550001',
            'exp_per' => 'LP',
            'fec_nac_per' => '1990-02-14',
            'est_per' => true,
        ]);

        $pin = PersonalInstitucional::create([
            'cod_pin' => 'PIN_TEST_03',
            'cod_per' => $personaDoc->cod_per,
            'car_pin' => 'DOCENTE',
            'est_pin' => 'ACTIVO',
        ]);

        $docente = Docente::create([
            'cod_doc' => 'DOC_TEST_03',
            'cod_pin' => $pin->cod_pin,
            'esp_doc' => 'FISICA',
            'est_doc' => 'ACTIVO',
        ]);

        $this->docenteUser = User::create([
            'cod_usu' => 'USU_DOC_03',
            'cod_per' => $personaDoc->cod_per,
            'email' => 'carla.vargas@savp.edu.bo',
            'password' => bcrypt('password'),
            'est_usu' => 'ACTIVO',
        ]);

        DB::table('gestion_academica')->updateOrInsert(
            ['cod_gea' => 'GEA_2026'],
            ['ani_gea' => 2026, 'fii_gea' => '2026-02-01', 'ffi_gea' => '2026-11-30', 'est_gea' => 'ACTIVO']
        );
        DB::table('curso')->updateOrInsert(
            ['cod_cur' => 'CUR_3SEC'],
            ['nom_cur' => '3ro de Secundaria', 'est_cur' => 'ACTIVO']
        );
        DB::table('paralelo')->updateOrInsert(
            ['cod_par' => 'PAR_C'],
            ['nom_par' => 'Paralelo C', 'est_par' => 'ACTIVO']
        );
        DB::table('turno')->updateOrInsert(
            ['cod_tur' => 'TUR_MAN'],
            ['nom_tur' => 'Mañana', 'hor_ini_tur' => '07:30:00', 'hor_fin_tur' => '13:00:00', 'est_tur' => 'ACTIVO']
        );
        DB::table('asignatura')->updateOrInsert(
            ['cod_asi' => 'ASI_FIS'],
            ['nom_asi' => 'Física', 'est_asi' => 'ACTIVO']
        );

        DB::table('plan_asignatura')->updateOrInsert(
            ['cod_pas' => 'PAS_FIS_3C'],
            [
                'cod_asi' => 'ASI_FIS',
                'cod_doc' => $docente->cod_doc,
                'cod_cur' => 'CUR_3SEC',
                'cod_par' => 'PAR_C',
                'cod_tur' => 'TUR_MAN',
                'cod_gea' => 'GEA_2026',
                'hor_pas' => 4,
                'est_pas' => 'ACTIVO',
            ]
        );

        $this->codCla = 'CLA_FIS_3C';
        ClaseVirtual::updateOrCreate(
            ['cod_cla' => $this->codCla],
            [
                'cod_pas' => 'PAS_FIS_3C',
                'nom_cla' => 'Física 3ro C',
                'est_cla' => 'ACTIVA',
            ]
        );
    }

    public function test_material_sin_contenido_es_bloqueado(): void
    {
        $this->actingAs($this->docenteUser);

        Livewire::test(CrearMaterial::class, ['codCla' => $this->codCla])
            ->set('nombre', 'Guía de Cinemática')
            ->set('url', '')
            ->assertSet('analisis.puede_guardar', false)
            ->call('guardarMaterial')
            ->assertDispatched('error-general');
    }

    public function test_material_con_url_valida_se_guarda_correctamente(): void
    {
        $this->actingAs($this->docenteUser);

        Livewire::test(CrearMaterial::class, ['codCla' => $this->codCla])
            ->set('nombre', 'Simulador Interactivo de Movimiento Rectilíneo')
            ->set('url', 'https://phet.colorado.edu/sims/html/forces-and-motion-basics/latest/forces-and-motion-basics_es.html')
            ->set('tipo', 'ENLACE')
            ->call('guardarMaterial')
            ->assertDispatched('success-general');

        $this->assertDatabaseHas('material_clase', [
            'cod_cla' => $this->codCla,
            'nom_mat' => 'Simulador Interactivo de Movimiento Rectilíneo',
            'tip_mat' => 'ENLACE',
        ]);
    }
}
