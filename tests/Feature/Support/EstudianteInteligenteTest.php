<?php

namespace Tests\Feature\Support;

use App\Models\Estudiante;
use App\Models\Persona;
use App\Support\Comunidad\EstudianteInteligente;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class EstudianteInteligenteTest extends TestCase
{
    use DatabaseTransactions;

    protected EstudianteInteligente $soporte;

    protected function setUp(): void
    {
        parent::setUp();
        $this->soporte = app(EstudianteInteligente::class);

        \Illuminate\Support\Facades\DB::table('tipo_vinculacion_estudiante')->updateOrInsert(
            ['cod_tve' => 'TVE_REGULAR'],
            ['nom_tve' => 'Estudiante Regular', 'est_tve' => 'ACTIVO']
        );
    }

    public function test_analizar_sin_rude_bloquea(): void
    {
        $persona = Persona::create([
            'nom_per' => 'Mateo',
            'ape_pat_per' => 'Salazar',
            'ape_mat_per' => 'Cruz',
            'ci_per' => '8881234',
            'exp_per' => 'LP',
            'fec_nac_per' => '2010-04-10',
            'gen_per' => 'MASCULINO',
            'est_per' => true,
        ]);

        $res = $this->soporte->analizarRegistro([
            'cod_per' => $persona->cod_per,
            'rud_est' => '',
            'cod_tve' => 'TVE_REGULAR',
        ]);

        $this->assertFalse($res['puede_guardar']);
        $this->assertFalse($res['puede_continuar']);
        $this->assertSame('BLOQUEADO', $res['estado']);
    }

    public function test_analizar_rude_duplicado_bloquea(): void
    {
        $persona1 = Persona::create([
            'nom_per' => 'Andres',
            'ape_pat_per' => 'Gomez',
            'ci_per' => '8882345',
            'exp_per' => 'LP',
            'fec_nac_per' => '2009-02-14',
            'est_per' => true,
        ]);

        Estudiante::create([
            'cod_est' => 'EST_TEST_01',
            'rud_est' => 'RUDE8882345001',
            'cod_per' => $persona1->cod_per,
            'cod_tve' => 'TVE_REGULAR',
            'est_est' => 'ACTIVO',
        ]);

        $persona2 = Persona::create([
            'nom_per' => 'Santiago',
            'ape_pat_per' => 'Perez',
            'ci_per' => '8883456',
            'exp_per' => 'LP',
            'fec_nac_per' => '2009-06-20',
            'est_per' => true,
        ]);

        $res = $this->soporte->analizarRegistro([
            'cod_per' => $persona2->cod_per,
            'rud_est' => 'RUDE8882345001',
            'cod_tve' => 'TVE_REGULAR',
        ]);

        $this->assertFalse($res['puede_guardar']);
        $this->assertSame('BLOQUEADO', $res['estado']);
        $this->assertTrue(collect($res['hallazgos'])->contains('codigo', 'EST_RUDE_DUPLICADO'));
    }

    public function test_analizar_edad_atipica_advierte_pero_no_bloquea(): void
    {
        $persona = Persona::create([
            'nom_per' => 'Alejandro',
            'ape_pat_per' => 'Quiroga',
            'ci_per' => '8884567',
            'exp_per' => 'LP',
            'fec_nac_per' => '2000-01-01', // 26 años, fuera de rango regular de secundaria
            'est_per' => true,
        ]);

        $res = $this->soporte->analizarRegistro([
            'cod_per' => $persona->cod_per,
            'rud_est' => 'RUDE8884567001',
            'cod_tve' => 'TVE_REGULAR',
        ]);

        $this->assertTrue($res['puede_guardar']);
        $this->assertTrue($res['puede_continuar']);
        $this->assertNotEmpty($res['advertencias']);
        $this->assertTrue(collect($res['hallazgos'])->contains('codigo', 'EST_EDAD_ATIPICA_SECUNDARIA'));
    }
}
