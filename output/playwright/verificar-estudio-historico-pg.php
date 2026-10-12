<?php
// Prueba focal en una base PostgreSQL temporal; nunca escribe tablas institucionales.
require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;
use App\Models\Oficial\Academico\Estudiante;
use App\Models\Oficial\Sistema\User;
use App\Models\Oficial\AporteAcademicoVocacional\OrientacionActividad;
use App\Services\AporteIngenieril\StudentOrientationService;
use App\Services\AporteIngenieril\AporteIngenierilClient;
use App\Services\AporteIngenieril\DTO\AporteResponse;
use App\Services\AporteIngenieril\OrientationReadiness;
use App\Services\AulaVirtual\CursoVirtualService;
$institucion = DB::connection();
if ($institucion->getDriverName() !== 'pgsql') throw new RuntimeException('Se requiere PostgreSQL para copiar la protección real.');
$funcion = $institucion->selectOne("select pg_get_functiondef('public.ofi_proteger_historico()'::regprocedure) as defin")->defin;
$basePrueba = 'savp_qa_historico_'.date('YmdHis');
if (!preg_match('/^savp_qa_historico_[0-9]{14}$/', $basePrueba)) throw new RuntimeException('Nombre de base de prueba inválido.');
$institucion->statement('CREATE DATABASE "'.$basePrueba.'"');
try {
    $configuracion = $institucion->getConfig();
    $configuracion['database'] = $basePrueba;
    $configuracion['name'] = 'qa_historico';
    $configuracion['url'] = null;
    config(['database.connections.qa_historico' => $configuracion, 'database.default' => 'qa_historico']);
    $prueba = DB::connection('qa_historico');
    if ($prueba->getDatabaseName() !== $basePrueba) throw new RuntimeException('Destino de prueba incorrecto.');
    $prueba->statement('CREATE TABLE estudiante(cod_est varchar(30) PRIMARY KEY)');
    $prueba->statement('CREATE TABLE inscripcion_estudiante(cod_est varchar(30),cod_gea varchar(30),est_ins varchar(30),fei_ins timestamp)');
    $prueba->statement("CREATE TABLE orientacion_actividades(id bigserial PRIMARY KEY,cod_est varchar(30) NOT NULL,cod_gea varchar(30),estado varchar(30) NOT NULL DEFAULT 'pendiente',avance integer NOT NULL DEFAULT 0,iniciado_at timestamp,finalizado_at timestamp,revisado_por varchar(30),riasec_public jsonb,riasec_score jsonb,analysis_snapshot jsonb,analysis_completed_at timestamp,riasec_input_hash varchar(64),cod_ior varchar(30),int_oac integer DEFAULT 1,rev_oac timestamp,created_at timestamp,updated_at timestamp,UNIQUE(cod_est,cod_ior,int_oac))");
    $prueba->statement($funcion);
    $prueba->statement('CREATE TRIGGER ofi_historico BEFORE UPDATE OR DELETE ON orientacion_actividades FOR EACH ROW EXECUTE FUNCTION public.ofi_proteger_historico()');
    $prueba->table('estudiante')->insert(['cod_est'=>'EST_QA_HISTORICO']);
    $prueba->table('inscripcion_estudiante')->insert(['cod_est'=>'EST_QA_HISTORICO','cod_gea'=>'GEA_QA','est_ins'=>'ACTIVA','fei_ins'=>'2026-01-01 00:00:00']);
    foreach ([new OrientacionActividad(),new Estudiante()] as $modelo) {
        if ($modelo->getConnection()->getDatabaseName() !== $basePrueba || $modelo->getConnection()->getName() !== 'qa_historico') throw new RuntimeException('El modelo no usa la conexión aislada.');
    }
    $fixture = json_decode(file_get_contents(__DIR__.'/fixture-ui-orientacion.json'),true,flags:JSON_THROW_ON_ERROR);
    $publico = $fixture['public'] ?? ['instrument_version'=>'2.0-es-2025','responses'=>array_map(fn($id)=>['item_id'=>$id,'value'=>3],range(1,30))];
    $puntaje = $fixture['score'];
    $origen = OrientacionActividad::create(['cod_est'=>'EST_QA_HISTORICO','cod_gea'=>'GEA_QA','estado'=>'finalizado','avance'=>100,'iniciado_at'=>'2026-10-01 08:00:00','finalizado_at'=>'2026-10-01 09:00:00','riasec_public'=>$publico,'riasec_score'=>$puntaje,'riasec_input_hash'=>hash('sha256',json_encode($publico,JSON_THROW_ON_ERROR))]);
    $origen->refresh();
    $antes = $origen->getRawOriginal();
    $bloqueo = false;
    try { $origen->update(['analysis_snapshot'=>['prohibido'=>true]]); } catch (Illuminate\Database\QueryException $error) { $bloqueo = str_contains($error->getMessage(),'No sobrescribir prueba finalizada'); }
    if (!$bloqueo) throw new RuntimeException('La protección real no bloqueó la sobrescritura.');
    $origen->refresh();
    $cliente = Mockery::mock(AporteIngenierilClient::class);
    $cliente->shouldReceive('analysisV2')->twice()->andReturn(new AporteResponse(true,$fixture['analysis']));
    $servicio = Mockery::mock(StudentOrientationService::class,[app(CursoVirtualService::class),$cliente,app(OrientationReadiness::class)])->makePartial();
    $servicio->shouldReceive('context')->andReturn(['student'=>Estudiante::findOrFail('EST_QA_HISTORICO'),'activity'=>$origen,'payload'=>['academic_period'=>'GEA_QA','riasec_public'=>$publico],'precheck'=>['ready'=>true]]);
    $respuesta = $servicio->analyze(new User());
    if (!$respuesta->available) throw new RuntimeException('El estudio no se guardó: '.$respuesta->message);
    $estudio = OrientacionActividad::latest('id')->firstOrFail();
    $repetida = $servicio->analyze(new User());
    if (!$repetida->available || OrientacionActividad::count() !== 2) throw new RuntimeException('El mismo estudio se duplicó después de leer JSONB.');
    $origen->refresh();
    $comprobaciones = ['historial'=>$antes === $origen->getRawOriginal(),'nuevo_registro'=>$estudio->id !== $origen->id,'snapshot'=>$estudio->analysis_snapshot == $fixture['analysis'],'fecha'=>$estudio->finalizado_at == $origen->finalizado_at,'respuestas'=>$estudio->riasec_public == $origen->riasec_public];
    if (in_array(false,$comprobaciones,true)) throw new RuntimeException('Falló comprobación: '.json_encode($comprobaciones));
    echo json_encode(['resultado'=>'PASS','motor'=>'PostgreSQL','sobrescritura_bloqueada'=>true,'historial_conservado'=>true,'version_estudio_guardada'=>true,'estudio_sin_duplicar'=>true,'base_institucional_modificada'=>false]).PHP_EOL;
} finally {
    Mockery::close();
    DB::purge('qa_historico');
    config(['database.default'=>$institucion->getName()]);
    $institucion->statement('DROP DATABASE "'.$basePrueba.'"');
}
