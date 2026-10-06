<?php
// Operación acotada: respaldo y comprobación PostgreSQL aislada. Nunca vacía la BD oficial.
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Process\Process;

$conexion = DB::connection();
$c = $conexion->getConfig();
$destino = $conexion->selectOne('SELECT current_database() AS db, current_user AS usuario');
if ($destino->db !== 'SAVPTIS3-OFICIAL' || $c['driver'] !== 'pgsql') throw new RuntimeException('Destino distinto del autorizado.');
$baseAislada = 'savp_revision_notificaciones_20261004';
if ($conexion->selectOne('SELECT 1 FROM pg_database WHERE datname = ?', [$baseAislada])) throw new RuntimeException('La base aislada ya existe; revisar antes de reutilizar.');
$carpeta = storage_path('app/private/respaldos');
if (! is_dir($carpeta)) mkdir($carpeta, 0700, true);
$respaldo = $carpeta.'/oficial-antes-notificaciones-20261004-220555.dump';
$bin = 'C:/Program Files/PostgreSQL/18/bin/';
$proceso = new Process([$bin.'pg_dump.exe', '-h', $c['host'], '-p', (string)$c['port'], '-U', $c['username'], '-d', $destino->db, '-Fc', '-f', $respaldo], null, ['PGPASSWORD' => $c['password']]);
$proceso->setTimeout(600);
if (! is_file($respaldo) || filesize($respaldo) < 1000) $proceso->mustRun();
$lista = new Process([$bin.'pg_restore.exe', '--list', $respaldo]);
$lista->mustRun();
if (filesize($respaldo) < 1000 || ! str_contains($lista->getOutput(), 'TABLE DATA public users')) throw new RuntimeException('El respaldo no contiene el catálogo de usuarios.');
echo json_encode(['respaldo' => $respaldo, 'bytes' => filesize($respaldo), 'sha256' => hash_file('sha256', $respaldo)], JSON_UNESCAPED_SLASHES).PHP_EOL;

$conexion->statement('CREATE DATABASE "savp_revision_notificaciones_20261004"');
try {
    config(['database.connections.revision_notificaciones' => array_merge($c, ['name' => 'revision_notificaciones', 'database' => $baseAislada, 'url' => null])]);
    DB::setDefaultConnection('revision_notificaciones');
    Schema::create('users', fn ($t) => $t->string('cod_usu', 20)->primary());
    Schema::create('gestion_academica', fn ($t) => $t->string('cod_gea', 20)->primary());
    $migracion = require database_path('migrations/Sistema/2026_10_04_230000_crear_notificaciones_institucionales.php');
    $migracion->up();
    DB::beginTransaction();
    DB::table('users')->insert([['cod_usu' => 'USU_000001'], ['cod_usu' => 'USU_000002']]);
    $aviso = App\Models\Oficial\Sistema\Notificacion::on('revision_notificaciones')->create(['clave_evento'=>'revision', 'origen'=>'SISTEMA', 'tipo'=>'INFORMACION', 'titulo'=>'Aviso de revisión', 'mensaje'=>'Solo en la base aislada.', 'publicada_en'=>now()]);
    $destinatario = App\Models\Oficial\Sistema\NotificacionUsuario::on('revision_notificaciones')->create(['cod_not'=>$aviso->getKey(), 'cod_usu'=>'USU_000001']);
    if (DB::table('notificacion_usuario')->where('cod_usu', 'USU_000002')->exists()) throw new RuntimeException('Aislamiento incorrecto.');
    try {
        DB::transaction(fn () => App\Models\Oficial\Sistema\NotificacionUsuario::on('revision_notificaciones')->create(['cod_not'=>$aviso->getKey(), 'cod_usu'=>'USU_000001']));
        throw new RuntimeException('La BD aceptó un destinatario duplicado.');
    } catch (Illuminate\Database\QueryException $e) {
        if ($e->getCode() !== '23505') throw $e;
    }
    try {
        DB::transaction(fn () => $aviso->update(['tipo'=>'OTRO_NO_VALIDO']));
        throw new RuntimeException('La BD aceptó un tipo desconocido.');
    } catch (Illuminate\Database\QueryException $e) {
        if ($e->getCode() !== '23514') throw $e;
        $aviso->refresh();
    }
    $destinatario->update(['leida_en'=>now(), 'archivada_en'=>now()]);
    $destinatario->update(['archivada_en'=>null]);
    if (! $destinatario->fresh()->leida_en) throw new RuntimeException('Restaurar perdió la lectura.');
    $otro = App\Models\Oficial\Sistema\NotificacionUsuario::on('revision_notificaciones')->create(['cod_not'=>$aviso->getKey(), 'cod_usu'=>'USU_000002']);
    // Aísla la regla de destinatario del catálogo de roles: no modifica permisos institucionales.
    $app->instance(App\Services\RoleDashboardResolver::class, new class {
        public function roleFor($usuario) { return 'Estudiante'; }
    });
    $app->instance(App\Support\WorkspaceNavigation::class, new class {
        public function for($usuario) { return []; }
    });
    config(['features.notifications'=>true]);
    $usuario = App\Models\Oficial\Sistema\User::on('revision_notificaciones')->findOrFail('USU_000001');
    Illuminate\Support\Facades\Auth::setUser($usuario);
    $servicio = new App\Services\NotificationService;
    $bandeja = $servicio->snapshot($usuario);
    if ($bandeja['rows']->total() !== 1) throw new RuntimeException('La bandeja incluyó avisos ajenos.');
    try {
        $servicio->mark($usuario, $otro->getKey(), true);
        throw new RuntimeException('Se permitió modificar un destinatario ajeno.');
    } catch (Illuminate\Database\Eloquent\ModelNotFoundException) {}
    $servicio->archive($usuario, $destinatario->getKey(), true);
    if ($servicio->snapshot($usuario, 'ARCHIVADAS')['rows']->total() !== 1) throw new RuntimeException('Filtro de archivo incorrecto.');
    $servicio->archive($usuario, $destinatario->getKey(), false);
    $servicio->mark($usuario, $destinatario->getKey(), false);
    if ($servicio->snapshot($usuario, 'NO_LEIDAS')['unread'] !== 1 || $otro->fresh()->leida_en !== null) throw new RuntimeException('Lectura o conteo incorrectos.');
    echo "Servicio: aislamiento por destinatario, rechazo de modificación ajena y filtros comprobados.\n";
    try {
        $migracion->down();
        throw new LogicException('No se protegió el historial.');
    } catch (RuntimeException $e) {
        if (! str_contains($e->getMessage(), 'contienen historial')) throw $e;
    }
    DB::rollBack();
    $migracion->down();
    if (Schema::hasTable('notificacion') || Schema::hasTable('notificacion_usuario')) throw new RuntimeException('Rollback incompleto.');
    $migracion->up();
    $migracion->down();
    echo "Migración, códigos, aislamiento, lectura, archivo y rollback verificados en PostgreSQL aislado.\n";
} finally {
    DB::disconnect('revision_notificaciones');
    DB::setDefaultConnection($c['name'] ?? 'pgsql');
    $conexion->statement('DROP DATABASE "savp_revision_notificaciones_20261004"');
}
