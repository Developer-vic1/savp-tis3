<?php
namespace Tests\Feature;

use App\Models\Oficial\Academico\Bitacora;
use App\Models\Oficial\Sistema\User;
use App\Support\Academico\PlanificacionClaseInteligente;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PlanificacionClaseInteligenteTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('sqlite',DB::connection()->getDriverName());
        $this->assertSame(':memory:',DB::connection()->getDatabaseName());
        $tablas=[
            'gestion_academica'=>['cod_gea','est_gea','ani_gea','fii_gea','ffi_gea'],
            'curso'=>['cod_cur','nom_cur','ord_cur'], 'paralelo'=>['cod_par','nom_par'], 'turno'=>['cod_tur','nom_tur'],
            'grupo_academico'=>['cod_gac','cod_gea','cod_cur','cod_par','cod_tur','est_gac'],
            'plantilla_horaria'=>['cod_pho','est_pho'],
            'horario'=>['cod_hor','cod_gac','cod_pho','fii_hor','ffi_hor','est_hor'],
            'horario_bloque'=>['cod_hbl','cod_pho','num_hbl','hor_ini_hbl','hor_fin_hbl','tip_hbl','est_hbl'],
            'plan_asignatura'=>['cod_pas','cod_gac','cod_asi','cod_doc','hor_pas','fii_pas','ffi_pas','est_pas'],
            'plan_especialidad'=>['cod_pes','cod_gac','cod_esp','cod_doc','hor_pes','fii_pes','ffi_pes','est_pes'],
            'horario_detalle'=>['cod_hde','cod_hor','cod_hbl','dia_hde','cod_pas','cod_pes','aul_hde','obs_hde','est_hde'],
            'asignatura'=>['cod_asi','nom_asi','est_asi','hor_asi'],
            'especialidad_tecnica'=>['cod_esp','nom_esp','est_esp'],
            'docente'=>['cod_doc','cod_pin','est_doc','esp_doc'], 'personal_institucional'=>['cod_pin','cod_per','est_pin'],
            'persona'=>['cod_per','nom_per','ape_pat_per','ape_mat_per'],
        ];
        foreach($tablas as $tabla=>$columnas)Schema::create($tabla,function($t)use($columnas){foreach($columnas as $c)$t->string($c)->nullable();$t->timestamps();});
        Schema::create('bitacora',function($t){foreach((new Bitacora)->getFillable() as $c)$t->text($c)->nullable();});
        DB::connection()->getPdo()->sqliteCreateFunction('CONCAT_WS',fn($separador,...$partes)=>implode($separador,array_filter($partes,fn($p)=>$p!==null)));
        $this->travelTo(\Carbon\Carbon::parse('2026-10-04'));
        DB::table('gestion_academica')->insert(['cod_gea'=>'G','est_gea'=>'ACTIVO','ani_gea'=>'2026','fii_gea'=>'2026-02-02','ffi_gea'=>'2026-12-02']);
        DB::table('curso')->insert(['cod_cur'=>'C','nom_cur'=>'Primero','ord_cur'=>1]);
        DB::table('paralelo')->insert(['cod_par'=>'A','nom_par'=>'A']);
        DB::table('turno')->insert(['cod_tur'=>'T','nom_tur'=>'Mañana']);
        foreach(['G1','G2'] as $id)DB::table('grupo_academico')->insert(['cod_gac'=>$id,'cod_gea'=>'G','cod_cur'=>'C','cod_par'=>'A','cod_tur'=>'T','est_gac'=>'ACTIVO']);
        foreach(['P1','P2'] as $id)DB::table('plantilla_horaria')->insert(['cod_pho'=>$id,'est_pho'=>1]);
        foreach(['H1'=>'G1','H2'=>'G2'] as $h=>$g)DB::table('horario')->insert(['cod_hor'=>$h,'cod_gac'=>$g,'cod_pho'=>$h==='H1'?'P1':'P2','fii_hor'=>'2026-09-07','ffi_hor'=>'2026-12-02','est_hor'=>'ACTIVO']);
        DB::table('horario_bloque')->insert([
            ['cod_hbl'=>'B1','cod_pho'=>'P1','num_hbl'=>1,'hor_ini_hbl'=>'07:45','hor_fin_hbl'=>'08:25','tip_hbl'=>'CLASE','est_hbl'=>'ACTIVO'],
            ['cod_hbl'=>'B2','cod_pho'=>'P2','num_hbl'=>17,'hor_ini_hbl'=>'08:00','hor_fin_hbl'=>'08:40','tip_hbl'=>'CLASE','est_hbl'=>'ACTIVO'],
        ]);
        DB::table('asignatura')->insert(['cod_asi'=>'A1','nom_asi'=>'Matemática','est_asi'=>'ACTIVO','hor_asi'=>4]);
        foreach(['D1','D2'] as $id){DB::table('docente')->insert(['cod_doc'=>$id,'cod_pin'=>$id,'est_doc'=>'ACTIVO','esp_doc'=>'Matemática y Física']);DB::table('personal_institucional')->insert(['cod_pin'=>$id,'cod_per'=>$id,'est_pin'=>'ACTIVO']);DB::table('persona')->insert(['cod_per'=>$id,'nom_per'=>'Docente','ape_pat_per'=>$id,'ape_mat_per'=>'Prueba']);}
        foreach(['PAS_000001'=>'G1','PAS_000002'=>'G2'] as $id=>$g)DB::table('plan_asignatura')->insert(['cod_pas'=>$id,'cod_gac'=>$g,'cod_asi'=>'A1','cod_doc'=>'D1','hor_pas'=>4,'fii_pas'=>'2026-02-02','ffi_pas'=>'2026-12-02','est_pas'=>'ACTIVO']);
        $actor=\Mockery::mock(User::class)->makePartial();$actor->forceFill(['cod_usu'=>'actor-aislado','est_usu'=>'ACTIVO']);$actor->shouldReceive('can')->with('Cursos')->andReturn(true);$actor->setRelation('roles',collect([(new Role)->forceFill(['name'=>'Administrador','guard_name'=>'web'])]));auth()->setUser($actor);
        Gate::shouldReceive('authorize')->andReturn(null);Gate::shouldReceive('allows')->andReturn(true);
    }
    private function formulario(): array{return ['tipo_plan'=>'MATERIA','cod_mat'=>'A1','cod_doc'=>'D1','carga_horaria'=>4,'aul_hor'=>'Aula 1','obs_hor'=>''];}
    private function claseOtra(): void{DB::table('horario_detalle')->insert(['cod_hde'=>'HDE_000001','cod_hor'=>'H2','cod_hbl'=>'B2','dia_hde'=>'LUNES','cod_pas'=>'PAS_000002','est_hde'=>'ACTIVO']);}

    public function test_cruce_docente_se_detecta_por_horas_aunque_numeros_y_plantillas_difieran(): void
    {
        $this->claseOtra();$r=app(PlanificacionClaseInteligente::class)->analizar('H1','B1','LUNES',$this->formulario());
        $this->assertFalse($r['valido']);$this->assertStringContainsString('docente ya imparte',implode(' ',$r['bloqueos']));
        try{app(PlanificacionClaseInteligente::class)->guardar('H1','B1','LUNES',$this->formulario());$this->fail('No puede guardar un cruce.');}catch(ValidationException $e){$this->assertArrayHasKey('clase',$e->errors());}
        $this->assertSame(1,DB::table('horario_detalle')->count());$this->assertSame(0,DB::table('bitacora')->count());
    }
    public function test_contacto_de_limites_y_periodos_separados_no_son_cruces(): void
    {
        $this->claseOtra();DB::table('horario_bloque')->where('cod_hbl','B2')->update(['hor_ini_hbl'=>'08:25']);
        $this->assertTrue(app(PlanificacionClaseInteligente::class)->analizar('H1','B1','LUNES',$this->formulario())['valido']);
        DB::table('horario_bloque')->where('cod_hbl','B2')->update(['hor_ini_hbl'=>'08:00']);DB::table('horario')->where('cod_hor','H2')->update(['ffi_hor'=>'2026-09-06']);
        $this->assertTrue(app(PlanificacionClaseInteligente::class)->analizar('H1','B1','LUNES',$this->formulario())['valido']);
    }
    public function test_bloque_ajeno_recreo_docente_distinto_y_gestion_historica_se_bloquean(): void
    {
        $s=app(PlanificacionClaseInteligente::class);
        $this->assertFalse($s->contexto('H1','B2','LUNES')['valido']);
        $this->assertFalse($s->analizar('H1','B1','LUNES',array_replace($this->formulario(),['cod_doc'=>'D2']))['valido']);
        DB::table('horario_bloque')->where('cod_hbl','B1')->update(['tip_hbl'=>'RECREO']);$this->assertFalse($s->contexto('H1','B1','LUNES')['valido']);
        DB::table('horario_bloque')->where('cod_hbl','B1')->update(['tip_hbl'=>'CLASE']);DB::table('gestion_academica')->update(['est_gea'=>'FINALIZADO']);$this->assertFalse($s->contexto('H1','B1','LUNES')['valido']);
    }
    public function test_guardar_valido_conserva_plan_y_registra_clase_y_bitacora_atomicamente(): void
    {
        $s=app(PlanificacionClaseInteligente::class);$clase=$s->guardar('H1','B1','LUNES',$this->formulario());
        $this->assertSame('PAS_000001',$clase->cod_pas);$this->assertSame(2,DB::table('plan_asignatura')->count());
        $this->assertSame(1,DB::table('horario_detalle')->count());$this->assertSame('CREAR_CLASE_HORARIO',Bitacora::first()->acc_bit);
        $this->assertFalse($s->contexto('H1','B1','LUNES')['valido']);
        try{$s->guardar('H1','B1','LUNES',$this->formulario());$this->fail('No debe duplicar el bloque.');}catch(ValidationException $e){$this->assertArrayHasKey('clase',$e->errors());}
        $this->assertSame(1,DB::table('horario_detalle')->count());
    }
    public function test_carga_agotada_nuevo_plan_sin_confirmar_y_bitacora_ausente_impiden_guardado(): void
    {
        DB::table('plan_asignatura')->where('cod_pas','PAS_000001')->update(['hor_pas'=>1]);
        DB::table('horario_detalle')->insert(['cod_hde'=>'HDE_000001','cod_hor'=>'H1','cod_hbl'=>'B1','dia_hde'=>'MARTES','cod_pas'=>'PAS_000001','est_hde'=>'ACTIVO']);
        $s=app(PlanificacionClaseInteligente::class);$this->assertFalse($s->analizar('H1','B1','LUNES',$this->formulario())['valido']);
        DB::table('asignatura')->insert(['cod_asi'=>'A2','nom_asi'=>'Física','est_asi'=>'ACTIVO','hor_asi'=>2]);
        $form=array_replace($this->formulario(),['cod_mat'=>'A2','carga_horaria'=>2]);$this->assertFalse($s->analizar('H1','B1','LUNES',$form)['valido']);
        $this->assertTrue($s->analizar('H1','B1','LUNES',$form+['confirmar_plan'=>true])['valido']);
        Schema::drop('bitacora');
        try{$s->guardar('H1','B1','LUNES',$form+['confirmar_plan'=>true]);$this->fail('Necesita bitácora.');}catch(ValidationException $e){$this->assertArrayHasKey('clase',$e->errors());}
        $this->assertSame(2,DB::table('plan_asignatura')->count());
    }
    public function test_nueva_planificacion_confirmada_usa_grupo_y_conserva_periodo(): void
    {
        DB::table('asignatura')->insert(['cod_asi'=>'A2','nom_asi'=>'Física','est_asi'=>'ACTIVO','hor_asi'=>2]);
        $clase=app(PlanificacionClaseInteligente::class)->guardar('H1','B1','LUNES',array_replace($this->formulario(),['cod_mat'=>'A2','carga_horaria'=>2,'confirmar_plan'=>true]));
        $plan=DB::table('plan_asignatura')->where('cod_pas',$clase->cod_pas)->first();
        $this->assertSame('G1',$plan->cod_gac);$this->assertSame('D1',$plan->cod_doc);$this->assertStringStartsWith('2026-09-07',$plan->fii_pas);$this->assertSame(3,DB::table('plan_asignatura')->count());
        $this->assertTrue(Bitacora::first()->val_nue_bit['plan_creado']);
    }

    public function test_especialidad_distinta_permite_excepcion_justificada_y_conserva_evidencia(): void
    {
        DB::table('docente')->where('cod_doc','D1')->update(['esp_doc'=>'Educación Musical']);
        $s=app(PlanificacionClaseInteligente::class);$form=$this->formulario();$r=$s->analizar('H1','B1','LUNES',$form);
        $this->assertTrue($r['requiere_motivo']);$this->assertSame([],$r['bloqueos']);$this->assertFalse($r['valido']);
        try{$s->guardar('H1','B1','LUNES',$form);$this->fail('Debe documentar la excepción.');}catch(ValidationException $e){$this->assertArrayHasKey('formClaseHorario.motivo_especialidad',$e->errors());}
        $form['motivo_especialidad']='sajdiajojdoiasjdosjao';$this->assertFalse($s->analizar('H1','B1','LUNES',$form)['valido']);
        $form['motivo_especialidad']='Suplencia temporal autorizada mientras retorna el docente titular.';$this->assertTrue($s->analizar('H1','B1','LUNES',$form)['valido']);
        $clase=$s->guardar('H1','B1','LUNES',$form);$this->assertSame('Primero · A',$clase->aul_hde);
        $evidencia=Bitacora::first()->val_nue_bit;$this->assertSame($form['motivo_especialidad'],$evidencia['motivo_excepcion']);$this->assertSame('Educación Musical',$evidencia['revision']['correspondencia']['especialidad']);
    }
    public function test_aula_del_curso_es_predeterminada_y_otro_espacio_debe_identificarse(): void
    {
        $s=app(PlanificacionClaseInteligente::class);$form=$this->formulario();$form['aul_hor']='valor alterado';$r=$s->analizar('H1','B1','LUNES',$form);
        $this->assertSame('Primero · A',$r['aula']);$this->assertFalse($r['requiere_motivo']);
        $form['modalidad_aula']='otro';$form['aul_hor']='';$this->assertFalse($s->analizar('H1','B1','LUNES',$form)['valido']);
        $form['aul_hor']='Laboratorio de Física';$clase=$s->guardar('H1','B1','LUNES',$form);$this->assertSame('Laboratorio de Física',$clase->aul_hde);
    }
    public function test_sin_especialidad_no_se_afirma_compatibilidad_y_se_pide_motivo(): void
    {
        DB::table('docente')->where('cod_doc','D1')->update(['esp_doc'=>null]);$s=app(PlanificacionClaseInteligente::class);
        $r=$s->analizar('H1','B1','LUNES',$this->formulario());$this->assertTrue($r['requiere_motivo']);$this->assertStringContainsString('no tiene especialidad',$r['correspondencia']['mensaje']);
    }

    public function test_educacion_fisica_no_se_confunde_con_fisica_y_se_preservan_los_cruces(): void
    {
        $soporte=app(\App\Support\Comunidad\DocenteInteligente::class);
        $this->assertFalse($soporte->correspondencia('Educación Física','Física')['coincide']);
        $this->assertTrue($soporte->correspondencia('Educación Física','Educación Física')['coincide']);
        DB::table('docente')->where('cod_doc','D1')->update(['esp_doc'=>'Educación Musical']);$this->claseOtra();
        $form=$this->formulario();$form['motivo_especialidad']='Suplencia temporal autorizada mientras retorna el docente titular.';
        $this->assertFalse(app(PlanificacionClaseInteligente::class)->analizar('H1','B1','LUNES',$form)['valido']);
    }
    public function test_opciones_incluyen_docentes_activos_que_no_son_el_planificado(): void
    {
        $opciones=app(PlanificacionClaseInteligente::class)->opciones('H1');
        $this->assertSame(['D1','D2'],collect($opciones['docentes'])->pluck('valor')->all());
        $this->assertSame('Matemática y Física',$opciones['docentes'][1]['especialidad']);
    }
    public function test_filtro_de_area_incluye_todos_los_perfiles_afines_y_separa_excepciones(): void
    {
        $s=app(PlanificacionClaseInteligente::class);
        $docentes=[
            ['valor'=>'musica1','especialidad'=>'Educación Musical'],
            ['valor'=>'musica2','especialidad'=>'Educación Musical · Comunicación y Lenguaje'],
            ['valor'=>'idiomas','especialidad'=>'Inglés y Lenguaje'],
            ['valor'=>'fisica','especialidad'=>'Física'],
            ['valor'=>'pendiente','especialidad'=>null],
        ];
        $this->assertSame(['musica1','musica2'],array_column($s->docentesDelArea($docentes,'Educación Musical'),'valor'));
        $this->assertSame(['idiomas'],array_column($s->docentesDelArea($docentes,'Lengua Extranjera - Inglés'),'valor'));
        $this->assertSame(['musica2','idiomas'],array_column($s->docentesDelArea($docentes,'Comunicación y Lenguaje'),'valor'));
        $this->assertSame(['fisica'],array_column($s->docentesDelArea($docentes,'Física'),'valor'));
        $this->assertSame([], $s->docentesDelArea($docentes,'Robótica',true));
    }
}
