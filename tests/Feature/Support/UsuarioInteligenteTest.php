<?php

namespace Tests\Feature\Support;

use App\Models\Persona;
use App\Models\User;
use App\Support\Usuarios\UsuarioInteligente;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class UsuarioInteligenteTest extends TestCase
{
    use DatabaseTransactions;

    protected UsuarioInteligente $soporte;

    protected function setUp(): void
    {
        parent::setUp();
        $this->soporte = app(UsuarioInteligente::class);
    }

    public function test_analizar_creacion_persona_invalida_bloquea(): void
    {
        $res = $this->soporte->analizarCreacion([
            'cod_per' => 'PER_INEXISTENTE_999',
            'email' => 'test@savp.edu.bo',
            'role' => 'Secretaria',
        ]);

        $this->assertFalse($res['puede_guardar']);
        $this->assertFalse($res['puede_continuar']);
        $this->assertSame('BLOQUEADO', $res['estado']);
        $this->assertNotEmpty($res['bloqueos']);
    }

    public function test_analizar_creacion_persona_inactiva_bloquea(): void
    {
        $persona = Persona::create([
            'nom_per' => 'Carlos',
            'ape_pat_per' => 'Mendoza',
            'ape_mat_per' => 'Rios',
            'ci_per' => '9991234',
            'exp_per' => 'LP',
            'fec_nac_per' => '1990-05-15',
            'gen_per' => 'MASCULINO',
            'tel_per' => '71234567',
            'ema_per' => 'carlos.mendoza@savp.edu.bo',
            'dir_per' => 'Calle 5 #123',
            'est_per' => 'INACTIVO',
        ]);

        $res = $this->soporte->analizarCreacion([
            'cod_per' => $persona->cod_per,
            'email' => 'carlos.mendoza@savp.edu.bo',
            'role' => 'Administrador',
        ]);

        $this->assertFalse($res['puede_guardar']);
        $this->assertSame('BLOQUEADO', $res['estado']);
    }

    public function test_analizar_creacion_valida(): void
    {
        $persona = Persona::create([
            'nom_per' => 'Lucia',
            'ape_pat_per' => 'Vargas',
            'ape_mat_per' => 'Flores',
            'ci_per' => '9995678',
            'exp_per' => 'CBBA',
            'fec_nac_per' => '1992-08-20',
            'gen_per' => 'FEMENINO',
            'tel_per' => '72345678',
            'ema_per' => 'lucia.vargas@savp.edu.bo',
            'dir_per' => 'Av. Heroinas 456',
            'est_per' => 'ACTIVO',
        ]);

        $res = $this->soporte->analizarCreacion([
            'cod_per' => $persona->cod_per,
            'email' => 'lucia.vargas@savp.edu.bo',
            'role' => 'Secretaria',
        ]);

        $this->assertTrue($res['puede_guardar']);
        $this->assertTrue($res['puede_continuar']);
        $this->assertEmpty($res['bloqueos']);
    }
}
