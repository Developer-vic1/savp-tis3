<?php

declare(strict_types=1);
use App\Models\Oficial\Sistema\User;
use App\Services\AporteIngenieril\StudentOrientationService;
use Database\Seeders\Demostracion\HistorialSeisGestionesSeeder;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

// Crea exclusivamente una base desechable con nombre aleatorio; nunca migra la conexión configurada.
require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
$cfg = config('database.connections.pgsql');
$base = 'savp_demo_prueba_'.bin2hex(random_bytes(6));
$admin = new PDO('pgsql:host='.$cfg['host'].';port='.$cfg['port'].';dbname=postgres', $cfg['username'], $cfg['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$admin->exec('CREATE DATABASE "'.$base.'"');
$conservar = in_array('--conservar', $argv, true);
$codigoSalida = 0;
try {
    config(['database.default' => 'pgsql', 'database.connections.pgsql.database' => $base, 'database.connections.pgsql.url' => null, 'cache.default' => 'database', 'session.driver' => 'array']);
    DB::purge('pgsql');
    app(PermissionRegistrar::class)->initializeCache();
    if (DB::selectOne('SELECT current_database() AS nombre')->nombre !== $base) {
        throw new RuntimeException('La conexión aislada no coincide.');
    }
    $codigo = Artisan::call('migrate', ['--force' => true]);
    if ($codigo !== 0) {
        throw new RuntimeException(Artisan::output());
    }
    config(['demostracion.habilitada' => true, 'demostracion.base_autorizada' => $base, 'demostracion.password' => bin2hex(random_bytes(16)), 'demostracion.ultimo_anio' => (int) date('Y')]);
    (new HistorialSeisGestionesSeeder)->run();
    $esperados = ['gestion_academica' => 6, 'estudiante' => 12, 'docente' => 36, 'turno' => 2, 'horario_bloque' => 14, 'horario' => 72, 'horario_detalle' => 2160, 'inscripcion_estudiante' => 42];
    foreach ($esperados as $tabla => $cantidad) {
        $actual = DB::table($tabla)->count();
        if ($actual !== $cantidad) {
            throw new RuntimeException("Conteo inesperado en {$tabla}: {$actual} / {$cantidad}");
        }
    }
    $db = DB::connection();
    foreach ([1, 2] as $turno) {
        if ($db->table('inscripcion_estudiante')->where('cod_est', "EST_DEMO_6_{$turno}")->count() !== 6) {
            throw new RuntimeException('Sexto no tiene su trayectoria de seis gestiones.');
        }
        $user = User::where('email', 'estudiante6.'.($turno === 1 ? 'm' : 't').'@savp.test')->sole();
        if (! $user->hasRole('Estudiante') || ! $user->can('Orientacion_Academica_Profesional') || $user->can('Gestion_Usuarios')) {
            throw new RuntimeException('El acceso del estudiante no respeta su rol.');
        }
        app(StudentOrientationService::class)->student($user);
    }
    $antes = $db->table('persona')->count();
    try {
        (new HistorialSeisGestionesSeeder)->run();
        throw new LogicException('Se permitió repetir una carga sobre datos existentes.');
    } catch (RuntimeException $error) {
        if (! str_contains($error->getMessage(), 'ya hay registros')) {
            throw $error;
        }
    }
    if ($db->table('persona')->count() !== $antes) {
        throw new RuntimeException('La carga repetida alteró los registros.');
    }
    $db->setDatabaseName('SAVPTIS3-OFICIAL');
    try {
        (new HistorialSeisGestionesSeeder)->run();
        throw new LogicException('No se bloqueó el nombre institucional protegido.');
    } catch (RuntimeException $error) {
        if (! str_contains($error->getMessage(), 'Demostración bloqueada')) {
            throw $error;
        }
    } finally {
        $db->setDatabaseName($base);
    }
    echo json_encode(['resultado' => 'correcto', 'base_aislada' => $base, 'conteos' => $esperados, 'repeticion_bloqueada' => true, 'nombre_oficial_bloqueado' => true], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE).PHP_EOL;
} catch (Throwable $error) {
    fwrite(STDERR, $error->getMessage().PHP_EOL);
    $codigoSalida = 1;
} finally {
    DB::disconnect('pgsql');
    if (! $conservar) {
        $consulta = $admin->prepare('SELECT pg_terminate_backend(pid) FROM pg_stat_activity WHERE datname = ? AND pid <> pg_backend_pid()');
        $consulta->execute([$base]);
        $admin->exec('DROP DATABASE "'.$base.'"');
    }
}
exit($codigoSalida);
