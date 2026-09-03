<?php

namespace Tests\Feature\Support;

use App\Models\Calificacion;
use App\Models\Persona;
use App\Support\Evaluacion\CalificacionInteligente;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CalificacionInteligenteTest extends TestCase
{
    use DatabaseTransactions;

    protected CalificacionInteligente $soporte;

    protected function setUp(): void
    {
        parent::setUp();
        $this->soporte = app(CalificacionInteligente::class);
    }

    public function test_calificacion_fuera_de_rango_bloquea(): void
    {
        $res = $this->soporte->analizar([
            'cod_est' => 'EST_0001',
            'cod_asi' => 'ASI_0001',
            'cod_pev' => 'PEV_0001',
            'not_cal' => 105,
        ]);

        $this->assertFalse($res['puede_guardar']);
        $this->assertFalse($res['puede_continuar']);
        $this->assertTrue(collect($res['hallazgos'])->contains('codigo', 'CAL_FUERA_RANGO'));
    }

    public function test_calificacion_valida_destacada(): void
    {
        $res = $this->soporte->analizar([
            'cod_est' => 'EST_0001',
            'cod_asi' => 'ASI_0001',
            'cod_pev' => 'PEV_0001',
            'not_cal' => 95,
        ]);

        $this->assertTrue($res['puede_guardar']);
        $this->assertSame('Destacado', $res['desempeno']);
        $this->assertFalse($res['riesgo']);
    }

    public function test_calificacion_en_riesgo_sugiere_reforzamiento(): void
    {
        $res = $this->soporte->analizar([
            'cod_est' => 'EST_0001',
            'cod_asi' => 'ASI_0001',
            'cod_pev' => 'PEV_0001',
            'not_cal' => 45,
        ]);

        $this->assertTrue($res['puede_guardar']);
        $this->assertTrue($res['riesgo']);
        $this->assertSame('En riesgo', $res['desempeno']);
        $this->assertTrue(collect($res['hallazgos'])->contains('codigo', 'CAL_EN_RIESGO_PEDAGOGICO'));
    }
}
