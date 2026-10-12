<?php
require dirname(__DIR__,2).'/vendor/autoload.php';
$app=require dirname(__DIR__,2).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;
foreach ([
'columnas'=>"select column_name,data_type,is_nullable,column_default from information_schema.columns where table_name='seguimiento_academico' and table_schema='public' order by ordinal_position",
'restricciones'=>"select conname,pg_get_constraintdef(oid) as defin from pg_constraint where conrelid='seguimiento_academico'::regclass",
'disparadores'=>"select pg_get_triggerdef(t.oid) as defin from pg_trigger t where t.tgrelid='seguimiento_academico'::regclass and not t.tgisinternal",
'tipos'=>"select tip_seg,ori_seg,vis_seg,est_seg,count(*) as registros from seguimiento_academico group by tip_seg,ori_seg,vis_seg,est_seg limit 10",
] as $clave=>$sql) echo json_encode([$clave=>DB::select($sql)],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE).PHP_EOL;
