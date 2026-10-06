<?php

namespace Tests\Feature;

use App\Livewire\Admin\RendimientoEstudiantes;
use App\Models\Oficial\AporteAcademicoVocacional\OrientacionActividad;
use App\Models\Oficial\Sistema\User;
use App\Services\AporteIngenieril\{AporteIngenierilClient, StudentOrientationService};
use App\Services\AporteIngenieril\DTO\AporteResponse;
use App\Support\Evaluacion\{CatalogoCarrerasRendimiento, RendimientoEstudiantil};
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB, Http, Schema};
use Mockery;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class RendimientoEstudiantilTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        foreach (['persona'=>['cod_per','nom_per','ape_pat_per','ape_mat_per'], 'estudiante'=>['cod_est','cod_per'], 'curso'=>['cod_cur','nom_cur','niv_cur'],
            'paralelo'=>['cod_par','nom_par'], 'gestion_academica'=>['cod_gea','ani_gea'], 'inscripcion_estudiante'=>['cod_ins','cod_est','cod_cur','cod_par','cod_tur','cod_gea','est_ins'],
            'calificacion'=>['cod_ins','cod_pev','est_cal','not_cal','obs_cal'], 'orientacion_actividades'=>['id','cod_est','cod_gea','avance','finalizado_at','analysis_completed_at','analysis_snapshot'],
            'resultado_anual'=>['cod_ins','est_ran','res_ran']] as $tabla=>$columnas) {
            Schema::create($tabla, function (Blueprint $t) use ($columnas) { foreach ($columnas as $columna) $t->string($columna)->nullable(); });
        }
        DB::table('persona')->insert(['cod_per'=>'P','nom_per'=>'Ana','ape_pat_per'=>'Quispe']);
        DB::table('estudiante')->insert(['cod_est'=>'E','cod_per'=>'P']);
        DB::table('curso')->insert(['cod_cur'=>'C','nom_cur'=>'Primero','niv_cur'=>'Secundaria']);
        DB::table('paralelo')->insert(['cod_par'=>'A','nom_par'=>'A']);
        DB::table('gestion_academica')->insert([['cod_gea'=>'G1','ani_gea'=>2025],['cod_gea'=>'G2','ani_gea'=>2026]]);
        DB::table('inscripcion_estudiante')->insert([['cod_ins'=>'I1','cod_est'=>'E','cod_cur'=>'C','cod_par'=>'A','cod_tur'=>'T','cod_gea'=>'G1','est_ins'=>'ACTIVA'],['cod_ins'=>'I2','cod_est'=>'E','cod_cur'=>'C','cod_par'=>'A','cod_tur'=>'T','cod_gea'=>'G2','est_ins'=>'ACTIVA']]);
    }

    private function snapshot(string $estudiante = 'E'): array
    {
        return ['schema_version'=>'2.0','trace_id'=>'fixture-trace','student_ref'=>hash_hmac('sha256',$estudiante,(string)config('app.key')),'analysis_status'=>'PARTIAL',
            'student_snapshot'=>collect(['academic','attendance','learning_activity','historical','technical','declared_interest'])->mapWithKeys(fn ($t)=>[$t.'_evidence'=>['status'=>'AVAILABLE']])->all(),
            'career_evidence_profiles'=>[],'informational_external_careers'=>[],'sources_used'=>[],'limitations'=>[],'warnings'=>[],
            'traceability'=>['trace_id'=>'fixture-trace','input_hash'=>str_repeat('a',64)]];
    }

    private function perfilCarrera(string $id = 'BO-UCB-LP-ING-SISTEMAS'): array
    {
        return ['career_id'=>$id,'career_name'=>'Ingeniería de Sistemas','university'=>'UCB',
            'academic_program'=>['knowledge_areas'=>[],'documented_subjects'=>[],'curriculum_status'=>'AVAILABLE','curriculum_scope'=>'OFFICIAL_CURRICULUM_SOURCE_ONLY','curriculum_note'=>'Referencia conservada','sources'=>[]],
            'vocational_interest_relation'=>['status'=>'AVAILABLE'],'technical_relation'=>['status'=>'UNAVAILABLE'],
            'preparation'=>['interpretation'=>'Relación observada','reinforcement_areas'=>[],'relations_with_observed_academic_evidence'=>1,'academic_evidence'=>[['subject'=>'Matemática']]],
            'evidence_quality'=>['academic_record_count'=>1,'distinct_subject_count'=>1,'ordered_period_count'=>1], 'areas_without_evidence'=>[],'sources'=>[],'limitations'=>[]];
    }

    private function actividad(int $id, string $gestion, array $datos): void
    {
        DB::table('orientacion_actividades')->insert(['id'=>$id,'cod_est'=>'E','cod_gea'=>$gestion,'avance'=>100,'finalizado_at'=>'2026-01-01','analysis_completed_at'=>'2026-01-02','analysis_snapshot'=>json_encode($datos)]);
    }

    private function consulta(array $f = [])
    {
        return app(RendimientoEstudiantil::class)->consulta(array_merge(['gestion'=>'G2','orientacion'=>true],$f));
    }

    public function test_gestion_is_required_and_all_group_filters_apply_to_same_enrollment(): void
    {
        $this->assertSame(0,$this->consulta(['gestion'=>''])->count());
        $this->assertSame(1,$this->consulta(['search'=>'E','nivel'=>'Secundaria','paralelo'=>'A','turno'=>'T'])->count());
        $this->assertSame(0,$this->consulta(['paralelo'=>'B'])->count());
        $this->assertSame(0,$this->consulta(['estudiante'=>'OTRO'])->count());
        $this->assertSame(1,$this->consulta(['search'=>'Quispe'])->count());
    }

    public function test_missing_study_is_distinct_from_invalid_or_wrong_student_study(): void
    {
        $servicio=app(RendimientoEstudiantil::class);
        $this->assertSame('pendiente',$servicio->estudio(null)['estado']);
        $actividad=new OrientacionActividad(['cod_est'=>'E','analysis_completed_at'=>'2026-01-02','analysis_snapshot'=>$this->snapshot()]);
        $this->assertSame('contrato_valido',$servicio->estudio($actividad)['estado']);
        $actividad->analysis_snapshot=$this->snapshot('OTRO');
        $this->assertSame('requiere_revision',$servicio->estudio($actividad)['estado']);
        $actividad->analysis_snapshot=['student_snapshot'=>[]];
        $this->assertNull($servicio->estudio($actividad)['datos']);
    }

    public function test_latest_attempt_and_year_bound_graphs_do_not_count_every_candidate_as_compatible(): void
    {
        $datos=$this->snapshot();$perfil=$this->perfilCarrera();
        $datos['career_evidence_profiles']=[$perfil,$perfil];
        $this->actividad(1,'G1',$datos);$this->actividad(2,'G2',$datos);
        $servicio=app(RendimientoEstudiantil::class);
        $grupo=$servicio->grupo($this->consulta(),true,$perfil['career_id'],'academica');
        $carrera=collect($grupo['carreras'])->firstWhere('id',$perfil['career_id']);
        $this->assertSame(1,$grupo['validos']);$this->assertSame(1,$carrera['estudios']);$this->assertSame(1,$carrera['academica']);
        $this->assertSame(0,$carrera['tecnica']);$this->assertSame(0,$carrera['declarados']);
        $this->assertSame('I2',$grupo['vinculados'][0]['inscripcion']);
        $this->assertCount(count(app(CatalogoCarrerasRendimiento::class)->leer()['carreras']),$grupo['carreras']);
        DB::table('orientacion_actividades')->insert(['id'=>3,'cod_est'=>'E','cod_gea'=>'G2','avance'=>20]);
        $this->assertSame(0,$servicio->grupo($this->consulta(),true)['validos']);
    }

    public function test_external_career_cannot_contribute_to_relationship_graphs_and_permissions_hide_catalog(): void
    {
        $datos=$this->snapshot();$datos['career_evidence_profiles']=[$this->perfilCarrera('BO-EMI-LP-ING-SISTEMAS')];$this->actividad(1,'G2',$datos);
        $servicio=app(RendimientoEstudiantil::class);$grupo=$servicio->grupo($this->consulta(),true);
        $externa=collect($grupo['carreras'])->firstWhere('id','BO-EMI-LP-ING-SISTEMAS');
        $this->assertFalse($externa['elegible']);$this->assertSame(0,$externa['academica']);
        $this->assertSame([],$servicio->grupo($this->consulta(['orientacion'=>false]),false)['carreras']);
    }

    public function test_career_presence_or_available_riasec_reference_does_not_imply_relationship(): void
    {
        $perfil=$this->perfilCarrera();$perfil['preparation']['academic_evidence']=[];
        $this->assertSame(['academica'=>false,'tecnica'=>false,'declarados'=>false],app(RendimientoEstudiantil::class)->relacionesCarrera($perfil));
    }

    public function test_public_actions_reject_revoked_permissions_before_database_or_network(): void
    {
        $actor=Mockery::mock(User::class)->makePartial();$actor->shouldReceive('can')->andReturn(false);$this->actingAs($actor);
        Http::fake();
        try { (new RendimientoEstudiantes)->comprobarConexion();$this->fail('Debe rechazar el acceso'); }
        catch(HttpException $error) { $this->assertSame(403,$error->getStatusCode()); }
        try { app(StudentOrientationService::class)->institutionalContext($actor,'I2');$this->fail('Debe rechazar el actor'); }
        catch(HttpException $error) { $this->assertSame(403,$error->getStatusCode()); }
        Http::assertNothingSent();
    }

    public function test_direct_analysis_call_without_readiness_cannot_call_python(): void
    {
        $actor=Mockery::mock(User::class)->makePartial();$actor->shouldReceive('can')->andReturn(true);$this->actingAs($actor);
        $servicio=Mockery::mock(StudentOrientationService::class);$servicio->shouldReceive('institutionalContext')->once()->with($actor,'I2')->andReturn(['precheck'=>['ready'=>false,'requirements'=>[]]]);
        $this->app->instance(StudentOrientationService::class,$servicio);Http::fake();
        $componente=new RendimientoEstudiantes;$componente->gestion='G2';$componente->seleccionado='I2';$componente->analizarEvidencia();
        $this->assertNull($componente->vistaPrevia);Http::assertNothingSent();
    }

    public function test_analysis_preview_does_not_write_institutional_snapshots(): void
    {
        $actor=Mockery::mock(User::class)->makePartial();$actor->shouldReceive('can')->andReturn(true);$this->actingAs($actor);
        $contexto=['precheck'=>['ready'=>true,'requirements'=>[]],'payload'=>['student_id'=>'E']];
        $servicio=Mockery::mock(StudentOrientationService::class);$servicio->shouldReceive('institutionalContext')->twice()->andReturn($contexto);
        $this->app->instance(StudentOrientationService::class,$servicio);
        $cliente=Mockery::mock(AporteIngenierilClient::class);$cliente->shouldReceive('analysisV2')->once()->with($contexto['payload'])->andReturn(new AporteResponse(true,$this->snapshot()));
        $this->app->instance(AporteIngenierilClient::class,$cliente);
        $componente=new RendimientoEstudiantes;$componente->gestion='G2';$componente->seleccionado='I2';$componente->analizarEvidencia();
        $this->assertSame('PARTIAL',$componente->vistaPrevia['analysis_status']);$this->assertSame(0,DB::table('orientacion_actividades')->count());
    }
}
