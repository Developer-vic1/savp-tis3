<?php
require dirname(__DIR__,2).'/vendor/autoload.php';
$app=require dirname(__DIR__,2).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;
use App\Models\Oficial\Academico\Estudiante;
use App\Models\Oficial\Academico\SeguimientoAcademico;
use App\Models\Oficial\AporteAcademicoVocacional\OrientacionActividad;
use App\Models\Oficial\Sistema\User;
use App\Services\AporteIngenieril\PlanPersonalEstudiante;
use App\Services\AporteIngenieril\StudentOrientationService;
use App\Services\AulaVirtual\CursoVirtualService;
$origen=DB::connection();
if($origen->getDriverName()!=='pgsql') throw new RuntimeException('Se requiere PostgreSQL.');
$nombreBase='savp_qa_plan_'.date('YmdHis');
if(!preg_match('/^savp_qa_plan_[0-9]{14}$/',$nombreBase)) throw new RuntimeException('Nombre de prueba inválido.');
$origen->statement('CREATE DATABASE "'.$nombreBase.'"');
try{
    $cfg=$origen->getConfig();$cfg['name']='qa_plan';$cfg['database']=$nombreBase;$cfg['url']=null;
    config(['database.connections.qa_plan'=>$cfg,'database.default'=>'qa_plan']);
    $db=DB::connection('qa_plan');
    if($db->getDatabaseName()!==$nombreBase || (new SeguimientoAcademico)->getConnection()->getName()!=='qa_plan') throw new RuntimeException('El destino no está aislado.');
    $db->statement('CREATE TABLE estudiante(cod_est varchar(30) PRIMARY KEY,cod_per varchar(30))');
    $db->statement('CREATE TABLE users(cod_usu varchar(30) PRIMARY KEY)');
    $db->statement("CREATE TABLE inscripcion_estudiante(cod_ins varchar(30) PRIMARY KEY,cod_est varchar(30) REFERENCES estudiante(cod_est),cod_gea varchar(30),est_ins varchar(30),fei_ins timestamp)");
    $db->statement("CREATE TABLE seguimiento_academico(cod_seg varchar(30) PRIMARY KEY,cod_ins varchar(30) NOT NULL REFERENCES inscripcion_estudiante(cod_ins),tip_seg varchar(100) NOT NULL,ori_seg varchar(100),mot_seg text NOT NULL,niv_ape_seg varchar(30),est_seg varchar(30) NOT NULL DEFAULT 'ABIERTO' CHECK(est_seg IN('ABIERTO','EN_SEGUIMIENTO','CERRADO','ANULADO')),vis_seg varchar(30),cod_usu_res varchar(30) REFERENCES users(cod_usu),fec_ape_seg timestamptz NOT NULL DEFAULT CURRENT_TIMESTAMP,fec_pro_seg timestamptz,fec_cie_seg timestamptz,res_seg text,pro_acc_seg text,obs_seg text,created_at timestamptz NOT NULL,updated_at timestamptz NOT NULL)");
    $db->table('estudiante')->insert(['cod_est'=>'EST_QA','cod_per'=>'PER_QA']);
    $db->table('users')->insert(['cod_usu'=>'USU_QA']);
    $db->table('inscripcion_estudiante')->insert(['cod_ins'=>'INS_QA','cod_est'=>'EST_QA','cod_gea'=>'GEA_QA','est_ins'=>'ACTIVA','fei_ins'=>'2026-01-01']);
    $usuario=Mockery::mock(User::class)->makePartial();$usuario->forceFill(['cod_usu'=>'USU_QA','cod_per'=>'PER_QA']);$usuario->shouldReceive('can')->andReturnTrue();
    $estudiante=Estudiante::findOrFail('EST_QA');
    $fixture=json_decode(file_get_contents(__DIR__.'/fixture-ui-orientacion.json'),true,flags:JSON_THROW_ON_ERROR);
    $actividad=new OrientacionActividad(['id'=>1,'cod_est'=>'EST_QA','cod_gea'=>'GEA_QA','riasec_public'=>$fixture['public'],'riasec_score'=>$fixture['score'],'analysis_snapshot'=>$fixture['analysis'],'riasec_input_hash'=>hash('sha256',json_encode($fixture['public'])),'finalizado_at'=>now()->subDay()]);
    $cursos=Mockery::mock(CursoVirtualService::class);$cursos->shouldReceive('estudianteDeUsuario')->andReturn($estudiante);
    $orientacion=Mockery::mock(StudentOrientationService::class);$orientacion->shouldReceive('context')->andReturnUsing(fn()=>['student'=>$estudiante,'activity'=>$actividad]);
    $planServicio=new PlanPersonalEstudiante($cursos,$orientacion);
    $programas=$fixture['analysis']['career_evidence_profiles'];
    $elegir=fn($id)=>$planServicio->elegir($usuario,['career_id'=>$id,'meta'=>'Investigar las materias de esta carrera con mi docente.','accion_personal'=>'Leer la malla y preparar tres preguntas para mi docente.','compromiso'=>1]);
    $plan=$elegir($programas[0]['career_id']);
    $original=SeguimientoAcademico::firstOrFail()->getRawOriginal();
    $planServicio->progreso($usuario,['plan_id'=>$plan['id'],'accion_id'=>$plan['acciones'][0]['id'],'completada'=>1]);
    $planServicio->solicitarCambio($usuario,['career_id'=>$programas[1]['career_id'],'motivo'=>'Quiero preparar otro enfoque después de investigar sus materias.','confirmar'=>1]);
    $bloqueado=false;
    try{$elegir($programas[1]['career_id']);}catch(Illuminate\Validation\ValidationException $error){$bloqueado=isset($error->errors()['career_id']);}
    if(!$bloqueado) throw new RuntimeException('Se permitió cambiar sin actualizar el cuestionario.');
    $puntajes=$actividad->riasec_score;$puntajes['trace_id']='cuestionario-nuevo-qa';$actividad->riasec_score=$puntajes;$actividad->finalizado_at=now()->addSecond();
    $nuevo=$elegir($programas[1]['career_id']);$estado=$planServicio->snapshot($usuario);
    if($estado['progresoPlan']!==0 || $nuevo['id']===$plan['id'] || !$estado['historialPlanes'][0]['acciones'][0]['completada'] || $original!==SeguimientoAcademico::orderBy('cod_seg')->firstOrFail()->getRawOriginal()) throw new RuntimeException('No se conservó historial o avance correctamente.');
    echo json_encode(['resultado'=>'PASS','motor'=>'PostgreSQL','versiones'=>SeguimientoAcademico::count(),'cambio_bloqueado_sin_cuestionario'=>true,'plan_nuevo_cero'=>true,'historial_conservado'=>true,'base_institucional_modificada'=>false]).PHP_EOL;
}finally{
    Mockery::close();DB::purge('qa_plan');config(['database.default'=>$origen->getName()]);$origen->statement('DROP DATABASE "'.$nombreBase.'"');
}
