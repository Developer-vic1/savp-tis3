<?php

namespace Tests\Feature\Support;

use App\Support\AulaVirtual\EntregaTareaInteligente;
use App\Support\AulaVirtual\TareaInteligente;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TareaEntregaInteligenteTest extends TestCase
{
    use DatabaseTransactions;

    protected TareaInteligente $soporteTarea;
    protected EntregaTareaInteligente $soporteEntrega;

    protected function setUp(): void
    {
        parent::setUp();
        $this->soporteTarea = app(TareaInteligente::class);
        $this->soporteEntrega = app(EntregaTareaInteligente::class);
    }

    public function test_tarea_sin_titulo_bloquea(): void
    {
        $res = $this->soporteTarea->analizar([
            'cod_cla' => 'CLA_0001',
            'tit_tar' => '',
            'pun_max_tar' => 100,
        ]);

        $this->assertFalse($res['puede_guardar']);
        $this->assertTrue(collect($res['hallazgos'])->contains('codigo', 'AV_TAREA_TITULO_REQUERIDO'));
    }

    public function test_tarea_puntaje_invalido_bloquea(): void
    {
        $res = $this->soporteTarea->analizar([
            'cod_cla' => 'CLA_0001',
            'tit_tar' => 'Informe de Laboratorio',
            'pun_max_tar' => 150, // Mayor a 100
        ]);

        $this->assertFalse($res['puede_guardar']);
        $this->assertTrue(collect($res['hallazgos'])->contains('codigo', 'AV_TAREA_PUNTAJE_INVALIDO'));
    }

    public function test_entrega_vacia_bloquea(): void
    {
        $res = $this->soporteEntrega->analizarEnvio(
            codTar: 'TAR_0001',
            codEst: 'EST_0001',
            datos: [
                'tex_ent' => '',
                'archivos' => [],
            ]
        );

        $this->assertFalse($res['puede_guardar']);
    }
}
