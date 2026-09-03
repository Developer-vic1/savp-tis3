<?php

namespace Tests\Feature\Support;

use App\Support\AulaVirtual\AsistenciaInteligente;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AsistenciaInteligenteTest extends TestCase
{
    use DatabaseTransactions;

    protected AsistenciaInteligente $soporte;

    protected function setUp(): void
    {
        parent::setUp();
        $this->soporte = app(AsistenciaInteligente::class);
    }

    public function test_analizar_sesion_clase_inexistente_bloquea(): void
    {
        $res = $this->soporte->analizarSesion(
            codCla: 'CLA_INEXISTENTE_999',
            estudiantesMarcados: ['EST_01' => 'PRESENTE'],
            fecha: '2026-09-03',
            modoCierre: false
        );

        $this->assertFalse($res['puede_guardar']);
        $this->assertFalse($res['puede_continuar']);
        $this->assertSame('BLOQUEADO', $res['estado']);
    }
}
