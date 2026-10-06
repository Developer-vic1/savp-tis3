<?php
// Respaldo de solo lectura de la institución y ensayo exclusivo en PostgreSQL aislado.
// No aplica migraciones ni concede permisos en SAVPTIS3-OFICIAL.
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\{DB, Schema};
use Symfony\Component\Process\Process;

$origen = config('database.connections.'.config('database.default'));
if (($origen['driver'] ?? null) !== 'pgsql') throw new RuntimeException('Este ensayo requiere PostgreSQL.');
if (Schema::hasTable('concesion_acceso')) throw new RuntimeException('Ya existe historia de concesiones: revisar el estado antes de preparar otra migración.');
$bin = 'C:/Program Files/PostgreSQL/18/bin/';
$carpeta = storage_path('app/private/respaldos-accesos/'.date('Ymd-His'));
if (!is_dir($carpeta) && !mkdir($carpeta, 0700, true)) throw new RuntimeException('No se pudo preparar el directorio privado.');
$entorno = ['PGPASSWORD' => $origen['password']];
$base = ['--host='.$origen['host'], '--port='.$origen['port'], '--username='.$origen['username']];
$ejecutar = function(array $argumentos) use ($entorno) {
    $proceso = new Process($argumentos, base_path(), $entorno, null, 180);
    $proceso->mustRun(); return $proceso->getOutput();
};
$respaldo = $carpeta.'/institucional.dump'; $esquema = $carpeta.'/esquema.sql';
$ejecutar([$bin.'pg_dump.exe', ...$base, '--dbname='.$origen['database'], '--format=custom', '--no-owner', '--no-acl', '--file='.$respaldo]);
$lista = $ejecutar([$bin.'pg_restore.exe', '--list', $respaldo]);
if (!str_contains($lista, 'TABLE')) throw new RuntimeException('El respaldo no contiene un esquema verificable.');
$ejecutar([$bin.'pg_dump.exe', ...$base, '--dbname='.$origen['database'], '--schema-only', '--no-owner', '--no-acl', '--file='.$esquema]);
$aislada = 'savp_prueba_accesos_'.date('YmdHis').'_'.bin2hex(random_bytes(3));
if (!preg_match('/^savp_prueba_accesos_[0-9]{14}_[a-f0-9]{6}$/', $aislada) || $aislada === $origen['database']) throw new RuntimeException('Destino aislado inválido.');
$conexionAnterior = config('database.default');
config(['database.connections.ensayo_admin' => [...$origen, 'database' => 'postgres'],
    'database.connections.ensayo_accesos' => [...$origen, 'database' => $aislada]]);
$creada = false;
try {
    DB::connection('ensayo_admin')->statement('CREATE DATABASE "'.$aislada.'"'); $creada = true;
    $ejecutar([$bin.'psql.exe', ...$base, '--dbname='.$aislada, '--set=ON_ERROR_STOP=1', '--file='.$esquema]);
    DB::setDefaultConnection('ensayo_accesos'); Schema::clearResolvedInstance('db.schema');
    if (DB::connection()->getDatabaseName() !== $aislada) throw new RuntimeException('El ensayo no está aislado.');
    $migracion = require database_path('migrations/Sistema/2026_10_05_000000_crear_concesiones_temporales.php');
    $migracion->up();
    foreach (['concesion_acceso', 'concesion_acceso_usuario', 'concesion_acceso_permiso'] as $tabla) {
        if (!Schema::hasTable($tabla)) throw new RuntimeException('Falta una tabla de vigencias.');
    }
    $constraint = DB::selectOne("SELECT count(*) AS cantidad FROM pg_constraint WHERE conname = 'concesion_acceso_valores_ck'");
    if ((int)$constraint->cantidad !== 1) throw new RuntimeException('Falta la restricción de vigencia de PostgreSQL.');
    $migracion->down();
    if (Schema::hasTable('concesion_acceso')) throw new RuntimeException('El rollback aislado no se completó.');
    $resultado = ['institucion' => $origen['database'], 'migracion_institucional_aplicada' => false,
        'respaldo' => $respaldo, 'sha256' => hash_file('sha256', $respaldo),
        'ensayo_postgresql' => 'migración y rollback correctos sobre copia del esquema sin datos'];
    file_put_contents($carpeta.'/verificacion.json', json_encode($resultado, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
    echo json_encode($resultado, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
} finally {
    DB::setDefaultConnection($conexionAnterior); Schema::clearResolvedInstance('db.schema'); DB::purge('ensayo_accesos');
    // Solo se elimina la base vacía creada por este ensayo, con nombre validado arriba.
    if ($creada) DB::connection('ensayo_admin')->statement('DROP DATABASE "'.$aislada.'"');
    DB::purge('ensayo_admin');
}
