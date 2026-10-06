<?php
require __DIR__.'/../../vendor/autoload.php';
$app=require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
echo json_encode(['database'=>Illuminate\Support\Facades\DB::selectOne('select current_database() as db')->db,'habilitado'=>config('features.notifications'),'tablas'=>[Illuminate\Support\Facades\Schema::hasTable('notificacion'),Illuminate\Support\Facades\Schema::hasTable('notificacion_usuario')],'avisos'=>Illuminate\Support\Facades\DB::table('notificacion')->count(),'destinatarios'=>Illuminate\Support\Facades\DB::table('notificacion_usuario')->count(),'migracion'=>Illuminate\Support\Facades\DB::table('migrations')->where('migration','2026_10_04_230000_crear_notificaciones_institucionales')->exists()]);
