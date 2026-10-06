<?php
namespace Tests\Unit;

use App\Support\Academico\DocumentoCambioCurricular;
use App\Support\Academico\AsignaturaInteligente;
use Tests\TestCase;

class DocumentoCambioCurricularTest extends TestCase
{
    private function carta(): string
    {
        return "Unidad Educativa Franz Tamayo N° 3\nDirector: GERMAN CAREAGA CANTUTA\nAsignatura: Programación\nSigla: PRO\nHoras académicas: 3\nGestión: 2027\nReferencia: DIR-021/2026\nFecha: 2026-10-04\nFundamento: Se implementará formación en programación con docentes capacitados y laboratorio institucional disponible.\nSe autoriza la incorporación de Programación para ampliar la formación tecnológica de los estudiantes.";
    }

    public function test_el_nombre_del_director_en_hoja_vacia_no_es_autorizacion(): void
    {
        $this->travelTo(\Carbon\Carbon::parse('2026-10-04'));
        $revisar = fn($t)=>DocumentoCambioCurricular::revisar($t,'GERMAN CAREAGA CANTUTA',2027,'crear');
        $this->assertTrue($revisar($this->carta())['coherente']);
        foreach (['Director GERMAN CAREAGA CANTUTA', str_replace('Se autoriza','No se autoriza',$this->carta()), str_replace('2027','2026',$this->carta()), str_replace('GERMAN CAREAGA CANTUTA','OTRO DIRECTOR',$this->carta()), $this->carta()."\nAsignatura: Otra materia", str_replace('2026-10-04','2026-02-30',$this->carta())] as $carta) $this->assertFalse($revisar($carta)['coherente']);
    }

    public function test_sigla_debe_corresponder_al_nombre(): void
    {
        $this->assertTrue(AsignaturaInteligente::siglaCompatible('Matemática','MAT'));
        $this->assertFalse(AsignaturaInteligente::siglaCompatible('Matemática','ABC'));
    }

    public function test_especialidades_rechaza_carta_de_materia_aunque_tenga_autorizacion(): void
    {
        $this->travelTo(\Carbon\Carbon::parse('2026-10-05'));
        $revision = fn($texto) => DocumentoCambioCurricular::revisar($texto,'GERMAN CAREAGA CANTUTA',2027,'crear','','especialidad');
        $this->assertFalse($revision($this->carta())['coherente']);
        $carta = str_replace(['Asignatura: Programación','Programación'], ['Especialidad: Sistemas Informáticos','Sistemas Informáticos'], $this->carta());
        $this->assertTrue($revision($carta)['coherente']);
        $this->assertFalse($revision(str_replace('Sistemas Informáticos','Matemática',$carta))['coherente']);
        $this->assertFalse($revision($carta."\nMateria: Sistemas Informáticos")['coherente']);
    }

    public function test_distingue_materias_especialidades_y_nombres_sin_evidencia(): void
    {
        $soporte = new \App\Support\Academico\EspecialidadTecnicaInteligente;
        foreach (['Matemática','Educación Musical','Técnica Tecnología General','Técnica Tecnología Especializada','Programación'] as $nombre) {
            $resultado = $soporte->orientacion($nombre);
            $this->assertFalse($resultado['reconocida']);
            $this->assertSame('asignatura',$resultado['tipo']);
        }
        foreach (['Sistemas Informáticos','Mecánica Industrial','Gastronomía','Contabilidad'] as $nombre) {
            $this->assertTrue($soporte->orientacion($nombre)['reconocida']);
        }
        $this->assertFalse($soporte->orientacion('sajdiajojdoiasjdosjao')['reconocida']);
        $this->assertFalse($soporte->orientacion('Matemática aplicada a Gastronomía')['reconocida']);
    }
}
