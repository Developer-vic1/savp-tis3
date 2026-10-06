<?php
namespace Tests\Unit;

use App\Services\InstitutionalDocumentAnalyzer;
use App\Support\Academico\RespaldoCursoInstitucional;
use Mockery;
use Tests\TestCase;

class CartaRolInstitucionalTest extends TestCase
{
    public function test_la_lectura_exige_autorizacion_director_gestion_y_rol_sin_certificar_firmas():void
    {
        $lector=Mockery::mock(RespaldoCursoInstitucional::class);
        $texto='La Directora María Quispe autoriza el rol Coordinación de informes para la gestión 2026.';
        $lector->shouldReceive('leer')->andReturnUsing(fn()=>['texto'=>$texto,'sha256'=>'huella','paginas'=>1]);
        $this->app->instance(RespaldoCursoInstitucional::class,$lector);
        $a=app(InstitutionalDocumentAnalyzer::class)->leerAutorizacion('carta.pdf','Coordinación de informes','María Quispe',2026);
        $this->assertTrue($a['coherente']);$this->assertNotEmpty($a['advertencias']);
        $this->assertFalse(app(InstitutionalDocumentAnalyzer::class)->leerAutorizacion('carta.pdf','Otro rol','María Quispe',2026)['coherente']);
        $this->assertFalse(app(InstitutionalDocumentAnalyzer::class)->leerAutorizacion('carta.pdf','Coordinación de informes','Otra directora',2026)['coherente']);
        $this->assertFalse(app(InstitutionalDocumentAnalyzer::class)->leerAutorizacion('carta.pdf','Coordinación de informes','María Quispe',2027)['coherente']);
    }

    public function test_una_denegacion_nunca_se_interpreta_como_autorizacion():void
    {
        foreach(['no autoriza','no se autoriza','no autorizamos','no se aprueba','autoriza pero queda rechazada'] as $decision){
            $lector=Mockery::mock(RespaldoCursoInstitucional::class);
            $lector->shouldReceive('leer')->andReturn(['texto'=>"La Directora María Quispe $decision el rol Coordinación de informes para la gestión 2026.",'sha256'=>'huella','paginas'=>1]);
            $this->app->instance(RespaldoCursoInstitucional::class,$lector);
            $this->assertFalse(app(InstitutionalDocumentAnalyzer::class)->leerAutorizacion('carta.pdf','Coordinación de informes','María Quispe',2026)['coherente']);
        }
    }
}
