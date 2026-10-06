<?php

namespace Tests\Unit;

use App\Support\Academico\ExpedienteParaleloInstitucional;
use App\Support\Academico\OrganizacionParalelosInteligente;
use App\Support\Academico\RespaldoCursoInstitucional;
use App\Support\Academico\ParaleloInteligente;
use PHPUnit\Framework\TestCase;

class ExpedienteParaleloInstitucionalTest extends TestCase
{
    public function test_texto_arbitrario_no_se_convierte_en_un_paralelo(): void
    {
        foreach (['sajdiajojdoiasjdosjao', 'A????', 'A y B', 'A mañana', '7mo E', 'general', 'integrado', '!!!!!!', '2026'] as $entrada) {
            $analisis = ParaleloInteligente::interpretar($entrada);
            $this->assertFalse($analisis['valido'], $entrada);
            $this->assertFalse($analisis['puede_crear'], $entrada);
        }
        $this->assertSame('E', ParaleloInteligente::interpretar('Paralelo E')['nombre_sugerido']);
        $this->assertTrue(ParaleloInteligente::interpretar('Único')['valido']);
        $this->assertFalse(ExpedienteParaleloInstitucional::justificacionComprensible('sajdiajojdoiasjdosjaosajdiajojdoiasjdosjao'));
        $this->assertFalse(ExpedienteParaleloInstitucional::justificacionComprensible('aula aula aula aula aula aula aula'));
        $this->assertTrue(ExpedienteParaleloInstitucional::justificacionComprensible('La demanda aumentó y se dispone de nuevos ambientes educativos.'));
    }
    private function documento(): string
    {
        return 'Dirección Departamental de Educación. Resolución Administrativa 123/2026. 04 de octubre de 2026. RESUELVE autorizar la incorporación del paralelo E para primer año de secundaria de la Unidad Educativa Franz Tamayo N°3, gestión 2026, de acuerdo al informe técnico distrital y a la disponibilidad de infraestructura.';
    }

    public function test_una_solicitud_no_equivale_a_una_resolucion(): void
    {
        $texto = str_replace(['Resolución Administrativa', 'RESUELVE'], ['Solicitud de apertura', 'Solicita'], $this->documento());
        $this->assertFalse($this->revisar($texto)['coherente']);
        $this->assertTrue($this->revisar($this->documento())['coherente']);
    }

    public function test_no_acepta_otra_institucion_ni_otra_letra_ni_una_denegacion(): void
    {
        foreach (['Franz Tamayo N°4', 'Otra Unidad Educativa 3'] as $institucion) {
            $this->assertFalse($this->revisar(str_replace('Franz Tamayo N°3', $institucion, $this->documento()))['coherente']);
        }
        $this->assertFalse($this->revisar(str_replace('paralelo E', 'paralelo B', $this->documento()))['coherente']);
        $this->assertFalse($this->revisar(str_replace('autorizar', 'no se autoriza', $this->documento()))['coherente']);
    }

    public function test_fecha_y_gestion_deben_coincidir(): void
    {
        $this->assertFalse(ExpedienteParaleloInstitucional::revisar($this->documento(), '123/2026', 'E', 'resolucion', '2026-10-03', '2026')['coherente']);
        $this->assertFalse(ExpedienteParaleloInstitucional::revisar($this->documento(), '123/2026', 'E', 'resolucion', '2026-10-04', '2027')['coherente']);
    }

    public function test_un_comunicado_general_no_autoriza_un_grado(): void
    {
        $analisis = (new RespaldoCursoInstitucional)->analizar(['texto'=>str_replace(['Resolución Administrativa', 'RESUELVE'], ['Resolución Ministerial', 'Normas generales'], $this->documento()), 'paginas'=>1, 'sha256'=>'prueba'], '123/2026', '2026-10-04', 1, 'crear', 2026);
        $this->assertFalse($analisis['coherente']);
    }

    public function test_no_confunde_cuatro_letras_con_ocho_grupos_de_dos_turnos(): void
    {
        $grupos = [];
        foreach (['Mañana', 'Tarde'] as $turno) foreach (['A', 'B', 'C', 'D'] as $codigo) $grupos[] = ['curso'=>'Primero', 'turno'=>$turno, 'codigo'=>$codigo, 'estudiantes'=>12, 'capacidad'=>35];
        $alertas = OrganizacionParalelosInteligente::revisar($grupos, 2026);
        $this->assertCount(1, $alertas);
        $this->assertStringContainsString('1 grados', $alertas[0]['mensaje']);
        $this->assertSame([], OrganizacionParalelosInteligente::revisar($grupos, 2027));
        $grupos[0]['estudiantes'] = 40;
        $this->assertSame('danger', OrganizacionParalelosInteligente::revisar($grupos, 2026)[0]['tipo']);
    }

    private function revisar(string $texto): array
    {
        return ExpedienteParaleloInstitucional::revisar($texto, '123/2026', 'E', 'resolucion', '2026-10-04', '2026');
    }
}
