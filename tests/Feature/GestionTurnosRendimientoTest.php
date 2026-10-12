<?php

namespace Tests\Feature;

use App\Livewire\Admin\GestionTurnos;
use App\Support\Academico\TurnoInteligente;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class GestionTurnosRendimientoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        $tablas = [
            'turno' => ['cod_tur', 'nom_tur', 'hor_ini_tur', 'hor_fin_tur', 'est_tur'],
            'plantilla_horaria' => ['cod_pho', 'cod_tur', 'nom_pho', 'tip_pho', 'des_pho', 'fec_ini_pho', 'fec_fin_pho', 'dur_blo_pho', 'ord_pho', 'act_pho', 'est_pho'],
            'horario_bloque' => ['cod_hbl', 'cod_pho', 'num_hbl', 'hor_ini_hbl', 'hor_fin_hbl', 'nom_hbl', 'tip_hbl', 'obs_hbl', 'est_hbl'],
            'horario' => ['cod_hor', 'cod_pho', 'est_hor'],
            'horario_detalle' => ['cod_hde', 'cod_hor', 'cod_hbl', 'est_hde'],
            'gestion_academica' => ['cod_gea', 'ani_gea', 'fii_gea', 'ffi_gea', 'est_gea'],
        ];
        foreach ($tablas as $tabla => $campos) {
            Schema::create($tabla, function (Blueprint $estructura) use ($campos) {
                foreach ($campos as $campo) $estructura->string($campo)->nullable();
            });
        }
        DB::table('turno')->insert(['cod_tur' => 'T1', 'nom_tur' => 'Mañana', 'hor_ini_tur' => '07:00', 'hor_fin_tur' => '13:00', 'est_tur' => 'ACTIVO']);
        DB::table('gestion_academica')->insert(['cod_gea' => 'G1', 'ani_gea' => 2026, 'fii_gea' => '2026-02-01', 'ffi_gea' => '2026-11-30', 'est_gea' => 'ACTIVO']);
        $this->plantilla('P1', 'REGULAR');
        $this->plantilla('P2', 'INVIERNO');
        $this->bloque('B1', 'P1');
        $this->bloque('B2', 'P2');
        DB::table('horario')->insert(['cod_hor' => 'H1', 'cod_pho' => 'P1', 'est_hor' => 'INACTIVO']);
        DB::table('horario_detalle')->insert([
            ['cod_hde' => 'D1', 'cod_hor' => 'H1', 'cod_hbl' => 'B1', 'est_hde' => 'ACTIVO'],
            ['cod_hde' => 'D2', 'cod_hor' => 'H1', 'cod_hbl' => 'B1', 'est_hde' => 'INACTIVO'],
            ['cod_hde' => 'D3', 'cod_hor' => 'H1', 'cod_hbl' => 'B2', 'est_hde' => 'ACTIVO'],
        ]);
    }

    private function plantilla(string $codigo, string $tipo = 'REGULAR'): void
    {
        DB::table('plantilla_horaria')->insert(['cod_pho' => $codigo, 'cod_tur' => 'T1', 'nom_pho' => $codigo, 'tip_pho' => $tipo, 'fec_ini_pho' => $tipo === 'INVIERNO' ? '2026-06-01' : '2026-02-01', 'fec_fin_pho' => $tipo === 'INVIERNO' ? '2026-07-31' : '2026-11-30', 'dur_blo_pho' => 40, 'ord_pho' => 1, 'act_pho' => $codigo === 'P1' ? 1 : 0, 'est_pho' => 1]);
    }

    private function bloque(string $codigo, ?string $plantilla): void
    {
        DB::table('horario_bloque')->insert(['cod_hbl' => $codigo, 'cod_pho' => $plantilla, 'num_hbl' => 1, 'hor_ini_hbl' => '07:00', 'hor_fin_hbl' => '07:40', 'nom_hbl' => 'Clase', 'tip_hbl' => 'CLASE', 'est_hbl' => 'ACTIVO']);
    }

    private function componente(): GestionTurnos
    {
        $componente = new GestionTurnos;
        $componente->boot(new TurnoInteligente);
        return $componente;
    }

    public function test_conteos_agrupados_conservan_uso_historico_y_huerfanos_y_contrato_preventivo(): void
    {
        $this->bloque('HUERFANO_NULO', null);
        $this->bloque('HUERFANO_VACIO', '');
        $componente = $this->componente();
        $componente->mount();
        $datos = $componente->render()->getData();
        $turno = $datos['turnosCatalogo']->first();
        $this->assertSame(2, $turno['bloques_total']);
        $this->assertSame(2, $turno['bloques_sin_plantilla']);
        $this->assertSame(1, $turno['horarios_total']);
        $regular = $datos['plantillasCatalogo']->firstWhere('cod_pho', 'P1');
        $this->assertSame(1, $regular['bloques_total']);
        $this->assertSame(2, $regular['detalles_total']);
        $this->assertSame('2 registros en horario', $regular['uso_academico']);
        $this->assertEquals(4, $datos['resumen']['bloques_horarios']);
        $this->assertSame(2, $datos['auditoria']['resumen']['bloques_sin_plantilla']);
        $this->assertSame($datos['plantillasCatalogo']->all(), $datos['plantillasAgrupadas']->get('T1')->all());
        $this->assertTrue($datos['vistaPrevia']['disponible']);
        $this->assertCount(3, $datos['vistaPrevia']['segmentos']);
    }

    public function test_memoizacion_de_render_no_conserva_datos_tras_cambios_en_la_misma_instancia(): void
    {
        $componente = $this->componente();
        $antes = $componente->render()->getData();
        $this->assertSame(2, $antes['plantillasCatalogo']->firstWhere('cod_pho', 'P1')['detalles_total']);
        $this->bloque('B3', 'P1');
        DB::table('horario_detalle')->insert(['cod_hde' => 'D4', 'cod_hor' => 'H1', 'cod_hbl' => 'B3', 'est_hde' => 'ACTIVO']);
        $validacion = new \ReflectionMethod(GestionTurnos::class, 'contarDetallesPlantilla');
        $this->assertSame(3, $validacion->invoke($componente, 'P1'));
        $despues = $componente->render()->getData();
        $this->assertSame(3, $despues['plantillasCatalogo']->firstWhere('cod_pho', 'P1')['detalles_total']);
        $this->assertSame(2, $despues['plantillasCatalogo']->firstWhere('cod_pho', 'P1')['bloques_total']);
        $this->assertSame(3, $despues['turnosCatalogo']->first()['bloques_total']);
    }

    public function test_consultas_de_render_no_crecen_con_cantidad_de_plantillas(): void
    {
        $componente = $this->componente();
        DB::enableQueryLog();
        $componente->render();
        $pocas = count(DB::getQueryLog());
        DB::flushQueryLog();
        for ($i = 0; $i < 80; $i++) {
            $this->plantilla('EXTRA'.$i);
            $this->bloque('BE'.$i, 'EXTRA'.$i);
        }
        DB::flushQueryLog();
        $datos = $componente->render()->getData();
        $muchas = count(DB::getQueryLog());
        DB::disableQueryLog();
        $this->assertCount(82, $datos['plantillasCatalogo']);
        $this->assertLessThanOrEqual($pocas, $muchas, 'Agregar plantillas no debe multiplicar consultas de conteos o turnos.');
        $this->assertLessThan(70, $muchas);
        $this->assertSame(0, $datos['plantillasCatalogo']->firstWhere('cod_pho', 'EXTRA79')['detalles_total']);
    }
}
