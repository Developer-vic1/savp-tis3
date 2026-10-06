<?php
// Verifica las dos migraciones en PostgreSQL aislado y respalda la base real; no aplica cambios institucionales.
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$config = config('database.connections.pgsql');
if ($config['database'] !== 'SAVPTIS3-OFICIAL') throw new RuntimeException('Destino institucional inesperado.');
$pdo = \Illuminate\Support\Facades\DB::connection()->getPdo();
$aislada = 'savp_documentacion_prueba_'.bin2hex(random_bytes(5));
$pdo->exec('CREATE DATABASE "'.$aislada.'"');
config(['database.connections.documentacion_prueba'=>$config + []]);
config(['database.connections.documentacion_prueba.database'=>$aislada]);
$anterior = \Illuminate\Support\Facades\DB::getDefaultConnection();
\Illuminate\Support\Facades\DB::setDefaultConnection('documentacion_prueba');
try {
    foreach (['000020_create_incorporaciones_curriculares_table','000021_create_referencias_documentales_table'] as $archivo) {
        $m = require __DIR__.'/../../database/migrations/Academico/2026_10_04_'.$archivo.'.php';
        $m->up();
        $m->down();
        $m->up();
    }
    if (!\Illuminate\Support\Facades\Schema::hasTable('referencia_documental') || !\Illuminate\Support\Facades\Schema::hasTable('incorporacion_curricular')) throw new RuntimeException('Esquema no verificado.');
    echo "Migraciones y reversión verificadas en PostgreSQL aislado.\n";
} finally {
    \Illuminate\Support\Facades\DB::disconnect('documentacion_prueba');
    \Illuminate\Support\Facades\DB::setDefaultConnection($anterior);
    $pdo->exec('DROP DATABASE "'.$aislada.'"');
}
$ruta = storage_path('app/private/respaldos/documentacion-'.date('Ymd-His').'.dump');
if (!is_dir(dirname($ruta))) mkdir(dirname($ruta), 0700, true);
$p = new \Symfony\Component\Process\Process(['C:/Program Files/PostgreSQL/18/bin/pg_dump.exe', '-h', $config['host'], '-p', (string)$config['port'], '-U', $config['username'], '-d', $config['database'], '-Fc', '-f', $ruta], null, ['PGPASSWORD'=>$config['password']]);
$p->setTimeout(600)->mustRun();
$v = new \Symfony\Component\Process\Process(['C:/Program Files/PostgreSQL/18/bin/pg_restore.exe', '-l', $ruta]);
$v->setTimeout(30)->mustRun();
if (!str_contains($v->getOutput(), 'personal_institucional') || !str_contains($v->getOutput(), 'bitacora')) throw new RuntimeException('Respaldo incompleto.');
echo 'Respaldo privado verificado: '.basename($ruta).' ('.filesize($ruta).' bytes).'.PHP_EOL;
