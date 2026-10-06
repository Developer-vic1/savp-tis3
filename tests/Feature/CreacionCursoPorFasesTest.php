<?php
namespace Tests\Feature;

use App\Livewire\Admin\GestionCurso;
use App\Models\Oficial\Academico\Bitacora;
use App\Models\Oficial\Sistema\User;
use App\Support\Academico\CursoInteligente;
use App\Support\Academico\RespaldoCursoInstitucional;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Mockery;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CreacionCursoPorFasesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();$this->assertSame('sqlite',DB::connection()->getDriverName());$this->assertSame(':memory:',DB::connection()->getDatabaseName());
        $this->travelTo(\Carbon\Carbon::parse('2026-01-10 12:00:00'));
        foreach([
            'curso'=>['cod_cur','nom_cur','niv_cur','ord_cur','est_cur'],
            'gestion_academica'=>['cod_gea','ani_gea','fii_gea','ffi_gea','est_gea'],
            'grupo_academico'=>['cod_gac','cod_cur','cod_gea','cod_par','cod_tur','cap_gac','est_gac','obs_gac'],
            'paralelo'=>['cod_par','nom_par','est_par'], 'turno'=>['cod_tur','nom_tur','est_tur'],
            'inscripcion_estudiante'=>['cod_ins','cod_gea','cod_cur','est_ins'], 'calificacion'=>['cod_ins','est_cal'],
        ] as $tabla=>$columnas)Schema::create($tabla,function($t)use($columnas){foreach($columnas as $c)$t->string($c)->nullable();$t->timestamps();});
        Schema::create('bitacora',function($t){foreach((new Bitacora)->getFillable() as $c)$t->text($c)->nullable();});
        DB::table('gestion_academica')->insert(['cod_gea'=>'G','ani_gea'=>2026,'fii_gea'=>'2026-02-02','ffi_gea'=>'2026-12-02','est_gea'=>'PLANIFICADA']);
        DB::table('paralelo')->insert(['cod_par'=>'A','nom_par'=>'A','est_par'=>'ACTIVO']);
        DB::table('turno')->insert([['cod_tur'=>'TM','nom_tur'=>'Mañana','est_tur'=>'ACTIVO'],['cod_tur'=>'TT','nom_tur'=>'Tarde','est_tur'=>'ACTIVO']]);
        Storage::fake('local');$actor=new User(['cod_usu'=>'actor-aislado']);$actor->setRelation('roles',collect([(new Role)->forceFill(['name'=>'Administrador','guard_name'=>'web'])]));auth()->setUser($actor);
        Gate::shouldReceive('authorize')->with('Cursos')->andReturn(null);
    }
    private function solicitud(string $texto): GestionCurso
    {
        $c=new GestionCurso;$c->gestionFiltro='G';$c->abrirFormularioCurso();$c->entradaCurso='séptimo de secundaria';$c->paralelosNuevos=['A'];$c->continuarFaseCurso();
        $c->motivo='Ampliación documentada de la oferta educativa para la gestión solicitada.';$c->causaInstitucional='reorganizacion';$c->autoridadEmisora='Dirección Departamental de Educación';$c->numeroResolucion='123/2026';$c->fechaResolucion='2026-01-10';$c->normaAdicional='999/2026';
        $c->respaldoPdf=UploadedFile::fake()->create('respaldo.pdf',1,'application/pdf');
        $lector=Mockery::mock(RespaldoCursoInstitucional::class)->makePartial();$lector->shouldReceive('leer')->andReturn(['texto'=>$texto,'paginas'=>1,'sha256'=>hash_file('sha256',$c->respaldoPdf->getRealPath())]);$this->app->instance(RespaldoCursoInstitucional::class,$lector);return $c;
    }
    private function texto(): string
    {
        return 'Ministerio de Educación. Dirección Departamental de Educación. Resolución Administrativa 123/2026. Fecha 10/01/2026. RESUELVE: Se autoriza la creación del séptimo año de secundaria de la Unidad Educativa Franz Tamayo N°3, para la gestión 2026, paralelo A, turno Mañana. La ampliación del nivel se fundamenta en la norma 999/2026 y en la aprobación institucional de la estructura educativa.';
    }
    public function test_septimo_y_octavo_se_reconocen_sin_suponer_un_tramo_tecnico(): void
    {
        foreach(['séptimo','7mo','octavo','8vo'] as $entrada){$r=CursoInteligente::interpretar($entrada.' secundaria');$this->assertTrue($r['valido']);$this->assertTrue($r['extraordinario']);$this->assertFalse($r['requiere_plan_especialidad']);}
        $this->assertFalse(CursoInteligente::interpretar('sexto y séptimo secundaria')['valido']);
        $c=new GestionCurso;$c->gestionFiltro='G';$c->modalClaseHorario=true;$c->claseContexto=['cod_hor'=>'previo'];$c->abrirFormularioCurso();$this->assertSame('',$c->turnoNuevo);$this->assertFalse($c->modalClaseHorario);$this->assertSame([],$c->claseContexto);
        $c->entradaCurso='7mo';$c->turnoNuevo='TT';$c->paralelosNuevos=['A'];
        $c->continuarFaseCurso();
        $this->assertSame(2,$c->faseCurso);$this->assertSame(0,DB::table('curso')->count());
    }
    public function test_solo_rechazo_de_pdf_leido_registra_intento_sin_duplicarlo(): void
    {
        $c=$this->solicitud(str_replace('La ampliación del nivel se fundamenta en la norma 999/2026','Documento enviado sin norma de ampliación',$this->texto()));
        $this->assertSame(0,DB::table('bitacora')->count());$c->escanearRespaldo();$c->escanearRespaldo();
        $this->assertSame('',$c->pdfEscaneado);
        try{$c->continuarFaseCurso();$this->fail('Sin escaneo válido no puede continuar.');}catch(ValidationException $e){$this->assertArrayHasKey('respaldoPdf',$e->errors());}
        $this->assertSame(2,$c->faseCurso);$this->assertFalse($c->analisisDocumento['coherente']);$this->assertSame(1,DB::table('bitacora')->count());
        $this->assertSame(0,DB::table('curso')->count());$this->assertSame(0,DB::table('grupo_academico')->count());
        $bit=Bitacora::first();$this->assertSame('INTENTO_CREAR_CURSO_PDF_RECHAZADO',$bit->acc_bit);Storage::disk('local')->assertExists($bit->val_nue_bit['documento']['ruta']);
    }
    public function test_fases_completas_crean_solo_grado_con_documento_y_motivo_en_bitacora(): void
    {
        $c=$this->solicitud($this->texto());$c->escanearRespaldo();$c->continuarFaseCurso();$this->assertSame(3,$c->faseCurso);$this->assertSame(0,DB::table('curso')->count());
        try{$c->guardarCambioCurso();$this->fail('La lectura no sustituye la comprobación con la autoridad.');}catch(ValidationException $e){$this->assertArrayHasKey('verificacionAutoridad',$e->errors());}
        $c->verificacionAutoridad='Comprobación del documento, la norma y su vigencia mediante el canal oficial de la autoridad.';$c->autenticidadConfirmada=true;$c->confirmarCambio=true;$c->guardarCambioCurso();
        $this->assertFalse($c->modalFormulario);$this->assertSame(1,DB::table('curso')->count());$this->assertSame('7mo de Secundaria',DB::table('curso')->value('nom_cur'));$this->assertSame('ACTIVO',DB::table('curso')->value('est_cur'));
        $this->assertSame(0,DB::table('grupo_academico')->count());
        $bit=Bitacora::firstOrFail();$this->assertSame($c->motivo,$bit->des_bit);$this->assertSame('999/2026',$bit->val_nue_bit['respaldo']['norma_ampliacion']);Storage::disk('local')->assertExists($bit->val_nue_bit['respaldo']['ruta']);
    }
    public function test_gestion_iniciada_y_documento_alterado_impiden_guardado(): void
    {
        $c=$this->solicitud($this->texto());$c->escanearRespaldo();$c->continuarFaseCurso();$c->verificacionAutoridad='Referencia comprobada con la autoridad y el expediente original.';$c->autenticidadConfirmada=true;$c->confirmarCambio=true;
        $c->normaAdicional='otra-norma';
        try{$c->guardarCambioCurso();$this->fail('No debe aceptar un contexto alterado.');}catch(ValidationException $e){$this->assertArrayHasKey('respaldoPdf',$e->errors());}
        $c->normaAdicional='999/2026';DB::table('gestion_academica')->update(['fii_gea'=>'2026-01-01']);
        try{$c->guardarCambioCurso();$this->fail('No debe ampliar durante clases.');}catch(ValidationException $e){$this->assertArrayHasKey('cambio',$e->errors());}
        $this->assertSame(0,DB::table('curso')->count());$this->assertSame(0,DB::table('bitacora')->count());
    }
    public function test_escaneo_completa_referencias_y_rechazo_conserva_datos(): void
    {
        $c=$this->solicitud($this->texto());$c->numeroResolucion='dato anterior';$c->fechaResolucion='2025-01-01';$c->autoridadEmisora='Otra autoridad';$c->normaAdicional='anterior';
        $c->escanearRespaldo();$this->assertNotSame('',$c->pdfEscaneado);$this->assertSame('123/2026',$c->numeroResolucion);$this->assertSame('2026-01-10',$c->fechaResolucion);$this->assertSame('Dirección Departamental de Educación',$c->autoridadEmisora);$this->assertSame('999/2026',$c->normaAdicional);
        $this->assertFalse($c->autenticidadConfirmada);$this->assertSame(0,DB::table('curso')->count());$this->assertSame(0,DB::table('bitacora')->count());
        $c=$this->solicitud(str_replace('Se autoriza','No se autoriza',$this->texto()));$c->numeroResolucion='conservar';$c->escanearRespaldo();$this->assertSame('conservar',$c->numeroResolucion);$this->assertSame('',$c->pdfEscaneado);$this->assertSame(1,DB::table('bitacora')->count());
    }
    public function test_referencia_sin_numero_y_motivo_incomprensible_bloquean_el_avance(): void
    {
        $c=$this->solicitud($this->texto());$c->escanearRespaldo();$c->numeroResolucion='sajdiajojdoiasjdosjao';
        try{$c->continuarFaseCurso();$this->fail('La resolución requiere número.');}catch(ValidationException $e){$this->assertArrayHasKey('numeroResolucion',$e->errors());}
        $c->numeroResolucion='123/2026';$c->motivo='sajdiajojdoiasjdosjao';
        try{$c->continuarFaseCurso();$this->fail('El motivo requiere explicación.');}catch(ValidationException $e){$this->assertArrayHasKey('motivo',$e->errors());}
        $this->assertSame(0,DB::table('curso')->count());
    }
}

