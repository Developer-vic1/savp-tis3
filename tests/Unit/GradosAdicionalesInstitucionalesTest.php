<?php
namespace Tests\Unit;
use App\Support\Academico\CursoInteligente;
use App\Support\Academico\RespaldoCursoInstitucional;
use PHPUnit\Framework\TestCase;

class GradosAdicionalesInstitucionalesTest extends TestCase
{
    public function test_autorizacion_general_antigua_denegada_o_para_otro_grado_no_aprueba_septimo(): void
    {
        $texto='Ministerio de Educación. Dirección Departamental de Educación. Resolución Administrativa 123/2036, 10/01/2036. RESUELVE autorizar la creación del séptimo año de secundaria en Franz Tamayo N°3 para gestión 2036. La ampliación del nivel se basa en la norma 999/2036 y en el expediente de aprobación institucional.';
        $revisar=fn($t,$norma='999/2036')=>(new RespaldoCursoInstitucional)->analizar(['texto'=>$t,'paginas'=>1,'sha256'=>'prueba'],'123/2036','2036-01-10',7,'crear',2036,$norma);
        $this->assertTrue($revisar($texto)['coherente']);
        $this->assertTrue($revisar(str_replace('séptimo','7°',$texto))['coherente']);
        $this->assertTrue($revisar(str_replace('séptimo','7mo',$texto))['coherente']);
        foreach([str_replace('séptimo','octavo',$texto),str_replace('autorizar','no se autoriza',$texto),str_replace('gestión 2036','gestión 2026',$texto),str_replace('ampliación del nivel','solicitud enviada',$texto)] as $invalido)$this->assertFalse($revisar($invalido)['coherente']);
        $this->assertFalse($revisar($texto,'otra norma')['coherente']);
        $this->assertSame('7mo de Secundaria',CursoInteligente::desdeOrden(7)['nombre']);
        $documento=['texto'=>$texto.' Se habilitan los paralelos A y B, turno Mañana.','paginas'=>1,'sha256'=>'prueba'];
        $s=new RespaldoCursoInstitucional;
        $this->assertTrue($s->analizar($documento,'123/2036','2036-01-10',7,'crear',2036,'999/2036',['paralelos'=>['A','B'],'turno'=>'Mañana'])['coherente']);
        $this->assertFalse($s->analizar($documento,'123/2036','2036-01-10',7,'crear',2036,'999/2036',['paralelos'=>['C'],'turno'=>'Mañana'])['coherente']);
        $this->assertFalse($s->analizar($documento,'123/2036','2036-01-10',7,'crear',2036,'999/2036',['paralelos'=>['A'],'turno'=>'Tarde'])['coherente']);
    }
}
