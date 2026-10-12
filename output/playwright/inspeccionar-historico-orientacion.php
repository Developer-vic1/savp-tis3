<?php
require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;
$queries = [
    'protecciones' => "select pg_get_triggerdef(t.oid) as defin from pg_trigger t where t.tgrelid = 'orientacion_actividades'::regclass and not t.tgisinternal",
    'funcion' => "select pg_get_functiondef('public.ofi_proteger_historico()'::regprocedure) as defin",
    'restricciones' => "select conname, pg_get_constraintdef(oid) as defin from pg_constraint where conrelid = 'orientacion_actividades'::regclass",
    'indices' => "select indexdef from pg_indexes where tablename = 'orientacion_actividades'",
    'estado_estudiante' => "select id,cod_gea,estado,avance,riasec_input_hash,analysis_completed_at,finalizado_at,(riasec_public is not null) as tiene_respuestas,(riasec_score is not null) as tiene_perfil,(analysis_snapshot is not null) as tiene_estudio from orientacion_actividades where cod_est = 'EST000017' order by id desc limit 4",
];
foreach ($queries as $nombre => $consulta) echo json_encode([$nombre => DB::select($consulta)], JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT).PHP_EOL;
