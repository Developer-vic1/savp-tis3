<?php
namespace Tests\Unit;

use App\Support\SupportRolesInstitucionales as SupportRol;
use App\Support\InstitutionalRoleGovernance;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SupportRolesInstitucionalesTest extends TestCase
{
    public function test_secuencias_repeticion_y_contenido_inadecuado_se_bloquean():void
    {
        foreach(['SJKANMKJSANDJKNAKJN','asdasdasdasd qwertyuiop zxcvbnm asdfghjkl','motivo motivo motivo motivo','AAAAAAAAAAA BBBBBBBBBBB CCCCCCCCCC DDDDDDDDDD','<script>alert(1)</script> texto con cuatro palabras','https://ejemplo.test acceso de prueba autorizado'] as $texto){
            $this->assertNotNull(SupportRol::errorTexto($texto));
        }
        $this->assertNotNull(SupportRol::errorTexto('SJKANMKJSANDJKNAKJN',4,true));
        $this->assertNull(SupportRol::errorTexto('Coordinación de informes',4,true));
        $this->assertNull(SupportRol::errorTexto('La actividad concluyó y ya no requiere estos accesos.'));
    }
    public function test_otro_requiere_explicacion_y_los_catalogos_no_admiten_valores_inventados():void
    {
        foreach([['acceso','NO_EXISTE','texto válido para apoyar al equipo'],['acceso','OTRO','SJKANMKJSANDJKNAKJN']] as $datos){
            try{SupportRol::motivo(...$datos);$this->fail('Debe bloquear el motivo.');}catch(ValidationException $e){$this->assertArrayHasKey('motivo',$e->errors());}
        }
        $this->assertSame(SupportRol::MOTIVOS['acceso']['SUPLENCIA'],SupportRol::motivo('acceso','SUPLENCIA',''));
        try{SupportRol::funciones(['REPORTES','TAREA_INVENTADA']);$this->fail('Debe bloquear responsabilidades ajenas al catálogo.');}catch(ValidationException $e){$this->assertArrayHasKey('responsabilidades',$e->errors());}
    }
    public function test_support_de_gobernanza_rechaza_nombre_sin_sentido_aunque_los_permisos_existan():void
    {
        $a=app(InstitutionalRoleGovernance::class)->analyze('SJKANMKJSANDJKNAKJN','Necesitamos organizar reportes e informes institucionales.','Preparar y organizar reportes e informes institucionales.',['reportes.exportar.institucional'],[],['reportes.exportar.institucional']);
        $this->assertSame('RECHAZADO',$a['status']);$this->assertNotEmpty($a['reasons']);
    }
}
