<?php
namespace Tests\Feature;

use App\Support\Evaluacion\PanelResultadosAcademicos;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB, Schema};
use Tests\TestCase;

class PanelResultadosAcademicosTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default'=>'sqlite','database.connections.sqlite.database'=>':memory:']);
        DB::purge('sqlite');
        foreach (['persona'=>['cod_per','nom_per','ape_pat_per','ape_mat_per','gen_per'],'estudiante'=>['cod_est','cod_per'],'curso'=>['cod_cur','nom_cur'],'paralelo'=>['cod_par','nom_par'],'gestion_academica'=>['cod_gea','ani_gea'],'inscripcion_estudiante'=>['cod_ins','cod_est','cod_cur','cod_par','cod_gea','est_ins'],'calificacion'=>['cod_ins','cod_pev','est_cal','not_cal','obs_cal'],'orientacion_actividades'=>['id','cod_est','cod_gea','avance','finalizado_at','analysis_completed_at','analysis_snapshot','riasec_score'],'resultado_anual'=>['cod_ins','est_ran','res_ran'],'periodo_evaluacion'=>['cod_pev','nom_pev','ord_pev']] as $tabla=>$campos) {
            Schema::create($tabla, function(Blueprint $t) use($campos) { foreach($campos as $c) $t->string($c)->nullable(); });
        }
        DB::table('persona')->insert(['cod_per'=>'P','nom_per'=>'Ana','ape_pat_per'=>'Quispe']);
        DB::table('estudiante')->insert(['cod_est'=>'E','cod_per'=>'P']);
        DB::table('curso')->insert(['cod_cur'=>'C','nom_cur'=>'Primero']);
        DB::table('paralelo')->insert(['cod_par'=>'A','nom_par'=>'A']);
        DB::table('gestion_academica')->insert([['cod_gea'=>'G1','ani_gea'=>'2025'],['cod_gea'=>'G2','ani_gea'=>'2026']]);
        DB::table('inscripcion_estudiante')->insert([['cod_ins'=>'I1','cod_est'=>'E','cod_cur'=>'C','cod_par'=>'A','cod_gea'=>'G1','est_ins'=>'RETIRADA'],['cod_ins'=>'I2','cod_est'=>'E','cod_cur'=>'C','cod_par'=>'A','cod_gea'=>'G2','est_ins'=>'ACTIVA']]);
        DB::table('periodo_evaluacion')->insert([['cod_pev'=>'T1','nom_pev'=>'Primero','ord_pev'=>'1'],['cod_pev'=>'T2','nom_pev'=>'Segundo','ord_pev'=>'2']]);
        DB::table('calificacion')->insert([['cod_ins'=>'I2','cod_pev'=>'T1','est_cal'=>'VIGENTE','not_cal'=>'40'],['cod_ins'=>'I2','cod_pev'=>'T1','est_cal'=>'RECTIFICADA','not_cal'=>'45'],['cod_ins'=>'I2','cod_pev'=>'T2','est_cal'=>'VIGENTE','not_cal'=>'80'],['cod_ins'=>'I2','cod_pev'=>'T2','est_cal'=>'ANULADA','not_cal'=>'0']]);
    }
    private function consulta(array $f=[]){ return (new PanelResultadosAcademicos)->consulta(array_merge(['gestion'=>'G2','orientacion'=>true],$f)); }
    public function test_destacados_use_best_average_per_registered_gender_and_filtered_year(): void
    {
        DB::table('persona')->where('cod_per', 'P')->update(['gen_per' => 'F']);
        foreach ([['M1','M',90], ['M2','M',80], ['F1','F',95], ['N','M',null]] as [$id,$genero,$nota]) {
            DB::table('persona')->insert(['cod_per'=>$id,'nom_per'=>$id,'gen_per'=>$genero]);
            DB::table('estudiante')->insert(['cod_est'=>$id,'cod_per'=>$id]);
            DB::table('inscripcion_estudiante')->insert(['cod_ins'=>$id,'cod_est'=>$id,'cod_cur'=>'C','cod_gea'=>'G2','est_ins'=>'ACTIVA']);
            if ($nota !== null) DB::table('calificacion')->insert(['cod_ins'=>$id,'cod_pev'=>'T1','est_cal'=>'VIGENTE','not_cal'=>$nota]);
        }
        DB::table('calificacion')->insert(['cod_ins'=>'I1','cod_pev'=>'T1','est_cal'=>'VIGENTE','not_cal'=>100]);
        $panel = new PanelResultadosAcademicos;
        $destacados = $panel->destacados($this->consulta(), false);
        $this->assertSame(['M1','F1'], array_map(fn($d) => $d['registro']->cod_est, $destacados));
        $this->assertSame([], $destacados[0]['carreras']);
        $this->assertSame([], $destacados[1]['carreras']);
        $filtrados = $panel->destacados($this->consulta(['periodo'=>'T2']), false);
        $this->assertCount(1, $filtrados);
        $this->assertSame('E', $filtrados[0]['registro']->cod_est);
    }
    public function test_destacado_without_valid_saved_analysis_hides_career_section(): void
    {
        DB::table('persona')->where('cod_per','P')->update(['gen_per'=>'F']);
        DB::table('orientacion_actividades')->insert(['id'=>'1','cod_est'=>'E','cod_gea'=>'G2','analysis_completed_at'=>'2026-01-01','analysis_snapshot'=>'{"career_evidence_profiles":[{"career_name":"Carrera sin validar"}]}']);
        $destacados = (new PanelResultadosAcademicos)->destacados($this->consulta(), true);
        $html = view('livewire.admin.calificaciones-destacados', compact('destacados'))->render();
        $this->assertStringContainsString('55.00', $html);
        $this->assertStringNotContainsString('Opciones de carrera analizadas', $html);
        $this->assertStringNotContainsString('Carrera sin validar', $html);
    }
    public function test_destacado_reuses_validated_latest_study_and_hides_it_without_permission(): void
    {
        DB::table('persona')->where('cod_per','P')->update(['gen_per'=>'F']);
        DB::table('orientacion_actividades')->insert([
            ['id'=>'1','cod_est'=>'E','cod_gea'=>'G1','analysis_completed_at'=>'2025-01-01'],
            ['id'=>'2','cod_est'=>'E','cod_gea'=>'G2','analysis_completed_at'=>'2026-01-01'],
        ]);
        $servicio = \Mockery::mock(\App\Support\Evaluacion\RendimientoEstudiantil::class);
        $servicio->shouldReceive('estudio')->once()->with(\Mockery::on(fn($actividad) => (string)$actividad->id === '2' && $actividad->cod_gea === 'G2'))
            ->andReturn(['estado'=>'contrato_valido','datos'=>['career_evidence_profiles'=>[['career_name'=>'Opción documentada','university'=>'Universidad de la fuente']]]]);
        $this->app->instance(\App\Support\Evaluacion\RendimientoEstudiantil::class, $servicio);
        $destacados = (new PanelResultadosAcademicos)->destacados($this->consulta(), true);
        $html = view('livewire.admin.calificaciones-destacados', compact('destacados'))->render();
        $this->assertStringContainsString('Opción documentada', $html);
        $this->assertStringContainsString('01/01/2026', $html);
        $sinPermiso = (new PanelResultadosAcademicos)->destacados($this->consulta(['orientacion'=>false]), false);
        $this->assertSame([], $sinPermiso[0]['carreras']);
    }
    public function test_chart_values_distinguish_missing_data_from_zero_and_escape_labels(): void
    {
        $html = view('components.grafico-resultados', ['identificador'=>'prueba','titulo'=>'Comparativa','descripcion'=>'Notas',
            'attributes'=>new \Illuminate\View\ComponentAttributeBag,
            'datos'=>['labels'=>['<script>','Período vacío'],'unidad'=>'puntos','series'=>[['label'=>'Notas','data'=>[0,null],'token'=>'primary']]]])->render();
        $this->assertStringContainsString('0.00', $html);
        $this->assertStringContainsString('Sin datos', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
        $this->assertStringNotContainsString('<th scope="row"><script>', $html);
    }
    public function test_counts_students_once_and_does_not_infer_annual_failure(): void
    {
        $s=(new PanelResultadosAcademicos)->resumen($this->consulta());
        $this->assertEquals(1,$s->total); $this->assertEquals(1,$s->riesgo); $this->assertEquals(0,$s->retenidos);
        $this->assertEqualsWithDelta(55,$s->promedio,.001);
    }
    public function test_period_filter_excludes_annulled_grades(): void
    {
        $s=(new PanelResultadosAcademicos)->resumen($this->consulta(['periodo'=>'T2']));
        $this->assertEquals(0,$s->riesgo); $this->assertEquals(80,$s->promedio);
    }
    public function test_only_current_annual_result_and_correct_year_are_counted(): void
    {
        DB::table('resultado_anual')->insert([['cod_ins'=>'I1','est_ran'=>'VIGENTE','res_ran'=>'RETENIDO'],['cod_ins'=>'I2','est_ran'=>'ANULADO','res_ran'=>'RETENIDO']]);
        $s=(new PanelResultadosAcademicos)->resumen($this->consulta());
        $this->assertEquals(0,$s->retenidos); $this->assertEquals(0,$s->retirados);
        DB::table('resultado_anual')->insert(['cod_ins'=>'I2','est_ran'=>'VIGENTE','res_ran'=>'RETENIDO']);
        $this->assertEquals(1,(new PanelResultadosAcademicos)->resumen($this->consulta())->retenidos);
    }
    public function test_latest_orientation_attempt_does_not_multiply_students(): void
    {
        DB::table('orientacion_actividades')->insert([['id'=>'1','cod_est'=>'E','cod_gea'=>'G2','avance'=>'100','finalizado_at'=>'2026-01-01','analysis_completed_at'=>'2026-01-01','analysis_snapshot'=>'{}'],['id'=>'2','cod_est'=>'E','cod_gea'=>'G2','avance'=>'20','finalizado_at'=>null,'analysis_completed_at'=>null,'analysis_snapshot'=>null]]);
        $s=(new PanelResultadosAcademicos)->resumen($this->consulta());
        $this->assertEquals(1,$s->total); $this->assertEquals(0,$s->riasec); $this->assertEquals(0,$s->analisis);
    }
    public function test_period_comparison_counts_distinct_students(): void
    {
        $p=(new PanelResultadosAcademicos)->periodos(['gestion'=>'G2']);
        $this->assertCount(2,$p); $this->assertEquals(1,$p[0]->riesgo); $this->assertEquals(1,$p[0]->evaluados);
    }
    public function test_synthetic_evidence_is_explicitly_counted(): void
    {
        DB::table('calificacion')->where('not_cal', '40')->update(['obs_cal'=>'VALOR SINTETICO; DATASET DE VALIDACION']);
        $this->assertEquals(1,(new PanelResultadosAcademicos)->resumen($this->consulta())->sinteticas);
    }
    public function test_missing_period_is_not_zero_and_orientation_requires_permission(): void
    {
        DB::table('periodo_evaluacion')->insert(['cod_pev'=>'T3','nom_pev'=>'Tercero','ord_pev'=>'3']);
        $p=(new PanelResultadosAcademicos)->periodos(['gestion'=>'G2']);
        $this->assertNull($p[2]->promedio); $this->assertEquals(0,$p[2]->evaluados);
        DB::table('orientacion_actividades')->insert(['id'=>'1','cod_est'=>'E','cod_gea'=>'G2','avance'=>'100','finalizado_at'=>'2026-01-01','analysis_completed_at'=>'2026-01-01','analysis_snapshot'=>'{}']);
        $this->assertEquals(0,(new PanelResultadosAcademicos)->resumen($this->consulta(['orientacion'=>false]))->analisis);
    }
    public function test_read_only_actions_reject_direct_calls(): void
    {
        $c=new \App\Livewire\Admin\Calificaciones;
        foreach(['abrirCrear'=>[], 'abrirEditar'=>['X'], 'guardar'=>[], 'cambiarEstado'=>['X']] as $metodo=>$args) {
            try { $c->$metodo(...$args); $this->fail('Acción no bloqueada'); }
            catch(\Symfony\Component\HttpKernel\Exception\HttpException $e){ $this->assertSame(403,$e->getStatusCode()); }
        }
    }
    public function test_profiles_use_latest_attempt_and_preserve_year_scope(): void
    {
        Schema::create('orientacion_resultados',function(Blueprint $t){$t->integer('orientacion_actividad_id');$t->string('perfil_predominante');});
        DB::table('orientacion_actividades')->insert([['id'=>'1','cod_est'=>'E','cod_gea'=>'G1','finalizado_at'=>'2025-01-01'],['id'=>'2','cod_est'=>'E','cod_gea'=>'G2','finalizado_at'=>'2026-01-01'],['id'=>'3','cod_est'=>'E','cod_gea'=>'G2','finalizado_at'=>'2026-02-01']]);
        DB::table('orientacion_resultados')->insert([['orientacion_actividad_id'=>1,'perfil_predominante'=>'AAA'],['orientacion_actividad_id'=>2,'perfil_predominante'=>'AAA'],['orientacion_actividad_id'=>3,'perfil_predominante'=>'IRC']]);
        $p=(new PanelResultadosAcademicos)->perfiles(['gestion'=>'G2','orientacion'=>true]);
        $this->assertCount(1,$p);$this->assertSame('IRC',$p[0]->perfil_predominante);$this->assertEquals(1,$p[0]->estudiantes);
        $this->assertCount(0,(new PanelResultadosAcademicos)->perfiles(['gestion'=>'G2','orientacion'=>false]));
        DB::table('orientacion_actividades')->insert(['id'=>'4','cod_est'=>'E','cod_gea'=>'G2','finalizado_at'=>'2026-03-01','riasec_score'=>json_encode(['holland_code'=>'ISA','scores'=>['I'=>14]])]);
        $p=(new PanelResultadosAcademicos)->perfiles(['gestion'=>'G2','orientacion'=>true]);
        $this->assertCount(1,$p);$this->assertSame('ISA',$p[0]->perfil_predominante);
    }
    public function test_saved_study_renders_evidence_without_calling_analysis(): void
    {
        $actividad=new \App\Models\Oficial\AporteAcademicoVocacional\OrientacionActividad(['estado'=>'finalizado','avance'=>100,'analysis_completed_at'=>'2026-01-01','analysis_snapshot'=>['career_evidence_profiles'=>[], 'student_snapshot'=>['academic_evidence'=>['status'=>'AVAILABLE']], 'limitations'=>['Falta evidencia de asistencia']]]);
        $actividad->setRelation('gestionAcademica',null)->setRelation('resultado',null)->setRelation('respuestas',collect());
        $detalle=['registro'=>(object)['nom_per'=>'Ana','ape_pat_per'=>'Quispe','ape_mat_per'=>'','ani_gea'=>2026,'nom_cur'=>'Primero','nom_par'=>'A'],'notas'=>collect(),'actividades'=>collect([$actividad])];
        $html=view('livewire.admin.calificaciones-trayectoria',['detalle'=>$detalle,'puedeVerOrientacion'=>true])->render();
        $this->assertStringContainsString('Evidencia disponible',$html);
        $this->assertStringContainsString('Falta evidencia de asistencia',$html);
    }
    public function test_project_uses_current_year_latest_snapshot_and_counts_each_career_once(): void
    {
        $perfil=['career_name'=>'Ingeniería de Sistemas','university'=>'Universidad documentada','evidence_quality'=>['academic_record_count'=>3]];
        $snapshot=json_encode(['student_snapshot'=>['academic_evidence'=>['status'=>'AVAILABLE'],'attendance_evidence'=>['status'=>'PARTIAL']], 'career_evidence_profiles'=>[$perfil,$perfil]]);
        DB::table('orientacion_actividades')->insert([
            ['id'=>'1','cod_est'=>'E','cod_gea'=>'G1','analysis_completed_at'=>'2025-01-01','analysis_snapshot'=>$snapshot],
            ['id'=>'2','cod_est'=>'E','cod_gea'=>'G2','analysis_completed_at'=>'2026-01-01','analysis_snapshot'=>$snapshot],
        ]);
        $p=(new PanelResultadosAcademicos)->estudios(['gestion'=>'G2','orientacion'=>true]);
        $this->assertSame(1,$p['estudios']);
        $this->assertSame(1,$p['evidencias']['academic']['AVAILABLE']);
        $this->assertSame(1,$p['evidencias']['attendance']['PARTIAL']);
        $this->assertSame(1,$p['evidencias']['historical']['UNAVAILABLE']);
        $this->assertSame(1,$p['carreras'][0]['estudios']);
        $this->assertSame(1,$p['carreras'][0]['con_notas']);
        DB::table('orientacion_actividades')->insert(['id'=>'3','cod_est'=>'E','cod_gea'=>'G2','analysis_completed_at'=>'2026-02-01','analysis_snapshot'=>'invalid']);
        $p=(new PanelResultadosAcademicos)->estudios(['gestion'=>'G2','orientacion'=>true]);
        $this->assertSame(0,$p['estudios']);
        $this->assertSame(1,$p['no_interpretables']);
        $this->assertSame([], $p['carreras']);
        $this->assertSame(0,(new PanelResultadosAcademicos)->estudios(['gestion'=>'G2','orientacion'=>false])['no_interpretables']);
    }
    public function test_grade_modal_renders_and_compares_assignment_keys_instead_of_model_primary_keys(): void
    {
        $notas=[];
        foreach ([['T1',60,1],['T2',80,2]] as [$periodo,$nota,$orden]) {
            $n=new \App\Models\Oficial\Academico\Calificacion(['cod_pas'=>'MISMA_MATERIA','cod_pev'=>$periodo,'not_cal'=>$nota,'obs_cal'=>'Observación registrada']);
            $n->setAttribute('cod_cal','NOTA_'.$periodo);
            $n->setRelation('inscripcionEstudiante',(object)['gestionAcademica'=>(object)['ani_gea'=>2026]]);
            $n->setRelation('periodoEvaluacion',(object)['nom_pev'=>$periodo,'ord_pev'=>$orden]);
            $n->setRelation('asignatura',(object)['nom_asi'=>'Matemática']);
            $notas[]=$n;
        }
        $detalle=['registro'=>(object)['nom_per'=>'Ana','ape_pat_per'=>'Quispe','ape_mat_per'=>'','ani_gea'=>2026,'nom_cur'=>'Primero','nom_par'=>'A'],'notas'=>new \Illuminate\Database\Eloquent\Collection($notas),'actividades'=>collect()];
        $html=view('livewire.admin.calificaciones-trayectoria',['detalle'=>$detalle,'puedeVerOrientacion'=>true])->render();
        $this->assertStringContainsString('+20.00 puntos',$html);
        $this->assertStringContainsString('Observación registrada',$html);
    }
    public function test_project_empty_state_and_connection_status_render_without_invented_projections(): void
    {
        $html=view('livewire.admin.calificaciones-proyecto',[
            'resumen'=>(object)['total'=>1,'evaluados'=>1,'riasec'=>0,'analisis'=>0],
            'grados'=>collect(), 'puedeVerOrientacion'=>true,
            'proyecto'=>(new PanelResultadosAcademicos)->estudios(['gestion'=>'G2','orientacion'=>true]),
            'conexionEstudio'=>'no_disponible','conexionComprobada'=>'05/10/2026 09:00',
        ])->render();
        $this->assertStringContainsString('Servicio no disponible',$html);
        $this->assertStringContainsString('Las opciones aparecerán al guardar los estudios',$html);
        $this->assertStringNotContainsString('Ingeniería de Sistemas',$html);
    }
    public function test_connection_check_uses_only_health_and_requires_orientation_permission(): void
    {
        $usuario=\Mockery::mock(\App\Models\Oficial\Sistema\User::class)->makePartial();
        $usuario->shouldReceive('can')->with('orientacion.ver.institucional')->twice()->andReturn(true,false);
        auth()->setUser($usuario);
        $cliente=\Mockery::mock(\App\Services\AporteIngenieril\AporteIngenierilClient::class);
        $cliente->shouldReceive('health')->once()->andReturn(new \App\Services\AporteIngenieril\DTO\AporteResponse(true));
        $this->app->instance(\App\Services\AporteIngenieril\AporteIngenierilClient::class,$cliente);
        $c=new \App\Livewire\Admin\Calificaciones;
        $c->comprobarConexion();
        $this->assertSame('disponible',$c->conexionEstudio);
        $this->assertNotNull($c->conexionComprobada);
        try {$c->comprobarConexion(); $this->fail('Acceso sin permiso');}
        catch(\Symfony\Component\HttpKernel\Exception\HttpException $e){$this->assertSame(403,$e->getStatusCode());}
    }
}
