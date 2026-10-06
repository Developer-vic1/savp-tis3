<?php
namespace Tests\Feature;

use App\Support\Comunidad\PerfilEspecialidadDocente;
use App\Support\Comunidad\DocenteInteligente;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PerfilEspecialidadDocenteTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();$this->assertSame('sqlite',DB::connection()->getDriverName());$this->assertSame(':memory:',DB::connection()->getDatabaseName());
        foreach(['asignatura'=>['cod_asi','nom_asi','est_asi'],'especialidad_tecnica'=>['cod_esp','nom_esp','est_esp'],'docente'=>['cod_doc','cod_pin','esp_doc','num_mod_doc','est_doc'],'personal_institucional'=>['cod_pin','cod_per'],'persona'=>['cod_per','nom_per']] as $tabla=>$campos)Schema::create($tabla,function($t)use($campos){foreach($campos as $c)$t->string($c)->nullable();$t->timestamps();});
        Schema::create('bitacora',function($t){foreach((new \App\Models\Oficial\Academico\Bitacora)->getFillable() as $c)$t->text($c)->nullable();});
        DB::table('asignatura')->insert([['cod_asi'=>'I','nom_asi'=>'Lengua Extranjera - Inglés','est_asi'=>'ACTIVO'],['cod_asi'=>'L','nom_asi'=>'Comunicación y Lenguaje','est_asi'=>'ACTIVO'],['cod_asi'=>'X','nom_asi'=>'Histórica','est_asi'=>'INACTIVO']]);
        DB::table('especialidad_tecnica')->insert(['cod_esp'=>'E','nom_esp'=>'Sistemas Informáticos','est_esp'=>'ACTIVO']);
        DB::table('docente')->insert(['cod_doc'=>'D','esp_doc'=>'Inglés y Lenguaje','num_mod_doc'=>0,'est_doc'=>'ACTIVO']);
        Gate::shouldReceive('authorize')->with('Personal_Institucional')->andReturn(null);
    }
    public function test_perfil_multiple_se_reconoce_en_ambas_materias_y_otro_se_conserva(): void
    {
        $s=app(PerfilEspecialidadDocente::class);$opciones=$s->opciones();$this->assertCount(3,$opciones);
        $perfil=$s->interpretar('Inglés y Lenguaje',$opciones);$this->assertSame(['materia:I','materia:L'],$perfil['seleccion']);$this->assertSame('',$perfil['otro']);
        $texto=$s->componer($perfil['seleccion'],false,'',$opciones);$d=app(DocenteInteligente::class);
        $this->assertTrue($d->correspondencia('Inglés y Lenguaje','Lengua Extranjera - Inglés')['coincide']);$this->assertTrue($d->correspondencia('Inglés y Lenguaje','Comunicación y Lenguaje')['coincide']);$this->assertTrue($d->correspondencia($texto,'Lengua Extranjera - Inglés')['coincide']);$this->assertTrue($d->correspondencia($texto,'Comunicación y Lenguaje')['coincide']);
        $this->assertSame('Literatura comparada',$s->interpretar('Literatura comparada',$opciones)['otro']);
    }
    public function test_seleccion_fuera_del_catalogo_no_se_guarda(): void
    {
        try{app(PerfilEspecialidadDocente::class)->guardar('D',['materia:X'],false,'');$this->fail('Debe rechazar un valor inactivo.');}catch(ValidationException $e){$this->assertArrayHasKey('seleccionEspecialidad',$e->errors());}
        $this->assertSame('Inglés y Lenguaje',DB::table('docente')->value('esp_doc'));$this->assertSame(0,DB::table('bitacora')->count());
    }
    public function test_guardado_multiple_conserva_estado_y_audita_sin_consumir_cambio_identico(): void
    {
        $s=app(PerfilEspecialidadDocente::class);$s->guardar('D',['materia:I','materia:L'],false,'');
        $this->assertSame('ACTIVO',DB::table('docente')->value('est_doc'));$this->assertEquals(1,DB::table('docente')->value('num_mod_doc'));$this->assertSame(1,DB::table('bitacora')->count());
        $s->guardar('D',['materia:I','materia:L'],false,'');$this->assertEquals(1,DB::table('docente')->value('num_mod_doc'));$this->assertSame(1,DB::table('bitacora')->count());
    }
    public function test_limite_y_bitacora_ausente_conservan_el_perfil(): void
    {
        $s=app(PerfilEspecialidadDocente::class);DB::table('docente')->update(['num_mod_doc'=>3]);
        try{$s->guardar('D',['materia:I'],false,'');$this->fail('No puede superar tres cambios.');}catch(ValidationException $e){$this->assertArrayHasKey('seleccionEspecialidad',$e->errors());}
        DB::table('docente')->update(['num_mod_doc'=>0]);Schema::drop('bitacora');
        try{$s->guardar('D',['materia:I'],false,'');$this->fail('Necesita bitácora.');}catch(ValidationException $e){$this->assertArrayHasKey('seleccionEspecialidad',$e->errors());}
        $this->assertSame('Inglés y Lenguaje',DB::table('docente')->value('esp_doc'));
    }
}
