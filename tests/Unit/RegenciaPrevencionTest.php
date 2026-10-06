<?php
namespace Tests\Unit;

use App\Livewire\Admin\AsignacionesRegencia;
use App\Models\Oficial\Academico\RegenteAsignacion;
use App\Support\Academico\AsignacionRegenciaInteligente;
use Tests\TestCase;

class RegenciaPrevencionTest extends TestCase
{
    public function test_rotacion_reinicia_en_primero_y_no_inventa_pares_irregulares(): void
    {
        $this->assertSame([1,2],\App\Services\RotacionRegenciaService::siguientePar([5,6]));
        $this->assertSame([3,4],\App\Services\RotacionRegenciaService::siguientePar([1,2]));
        $this->assertSame([5,6],\App\Services\RotacionRegenciaService::siguientePar([3,4]));
        $this->assertSame([],\App\Services\RotacionRegenciaService::siguientePar([1,4]));
    }
    public function test_intervalo_no_puede_salir_de_la_designacion_o_gestion(): void
    {
        $comprobar=fn($a,$b)=>AsignacionRegenciaInteligente::intervaloDentro($a,$b,'2026-02-02','2026-12-02');
        $this->assertTrue($comprobar('2026-02-02','2026-12-02'));
        $this->assertFalse($comprobar('2026-02-01','2026-12-02'));
        $this->assertFalse($comprobar('2026-02-02','2027-01-01'));
        $this->assertFalse($comprobar('2026-10-05','2026-10-04'));
        $this->assertFalse($comprobar('2026-02-02',null));
    }
    public function test_estado_activo_no_confunde_programadas_o_finalizadas_con_vigentes(): void
    {
        $r=new RegenteAsignacion(['est_ras'=>'ACTIVO','fii_ras'=>'2026-02-02','ffi_ras'=>'2026-12-02']);
        $this->assertSame('Vigente',AsignacionesRegencia::estadoTemporal($r,'2026-10-05'));
        $this->assertSame('Programada',AsignacionesRegencia::estadoTemporal($r,'2026-02-01'));
        $this->assertSame('Finalizada',AsignacionesRegencia::estadoTemporal($r,'2026-12-03'));
        $r->est_ras='INACTIVO';
        $this->assertSame('Retirada',AsignacionesRegencia::estadoTemporal($r,'2026-10-05'));
    }
    public function test_datos_vacios_y_fechas_imposibles_no_generan_una_revision_valida(): void
    {
        $s=new AsignacionRegenciaInteligente;
        $this->assertFalse($s->revisar([])['puede_guardar']);
        $r=$s->revisar(['cod_vpe'=>'VPE_0001','cod_gea'=>'GEA_0001','cod_cur'=>'CUR_0001','fii_ras'=>'2026-02-30','ffi_ras'=>'2026-12-02','obs_ras'=>'Designación institucional comunicada por Dirección para acompañar el grado.']);
        $this->assertFalse($r['puede_guardar']);
        $this->assertArrayHasKey('fii_ras',$r['errores']);
    }
}
