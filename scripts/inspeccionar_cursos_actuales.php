<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
DB::beginTransaction();
DB::statement('SET TRANSACTION READ ONLY');
if (in_array('--estados', $argv, true)) {
    echo json_encode(['inscripciones'=>DB::table('inscripcion_estudiante')->selectRaw('est_ins, COUNT(*) cantidad')->groupBy('est_ins')->get(), 'grupos'=>DB::table('grupo_academico')->selectRaw('est_gac, COUNT(*) cantidad')->groupBy('est_gac')->get()]);
    DB::rollBack(); exit;
}
if (in_array('--lectura', $argv, true)) {
    DB::enableQueryLog();
    $inicio = microtime(true);
    $consulta = app(App\Support\Academico\ConsultaCursosInstitucionales::class);
    $resumen = $consulta->resumen('GEA_0001');
    $curso = $resumen->first();
    $eventos = $consulta->horario($curso['cod_cur'],'GEA_0001',$curso['paralelos'][0]['valor']);
    $materias = $consulta->materias($curso['cod_cur'],'GEA_0001');
    echo json_encode(['resumen'=>$resumen,'eventos'=>count($eventos),'asignaciones'=>count($materias),'consultas'=>count(DB::getQueryLog()),'ms'=>round((microtime(true)-$inicio)*1000),'turnos_horario'=>array_values(array_unique(array_column($eventos,'turno')))], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
    DB::rollBack();
    exit;
}
$salida = [];
foreach (['curso','grupo_academico','plan_asignatura','plan_especialidad','horario','horario_detalle','horario_bloque','plantilla_horaria','gestion_academica','asignatura','docente','calificacion','inscripcion_vigencia'] as $tabla) {
    $salida[$tabla] = ['columnas'=>Schema::getColumnListing($tabla),'filas'=>DB::table($tabla)->count()];
}
$salida['gestiones'] = DB::table('gestion_academica')->orderBy('ani_gea')->get()->toArray();
$salida['plantillas'] = DB::table('plantilla_horaria')->get()->toArray();
$salida['turnos'] = DB::table('turno')->get()->toArray();
$salida['horarios_por_gestion'] = DB::table('horario as h')->join('grupo_academico as g','g.cod_gac','=','h.cod_gac')->join('gestion_academica as a','a.cod_gea','=','g.cod_gea')->selectRaw('a.ani_gea, COUNT(*) cantidad')->groupBy('a.ani_gea')->get()->toArray();
$salida['bloques_muestra'] = DB::table('horario_bloque')->limit(10)->get()->toArray();
echo json_encode($salida, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
DB::rollBack();
