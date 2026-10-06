<?php

// Ensayo del migrador normal sobre una base PostgreSQL nueva y desechable.
// No vacía ni escribe en la base institucional, ni ejecuta seeders.
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\{Artisan, DB, Schema};

$anterior = config('database.default');
$origen = config('database.connections.'.$anterior);
if (($origen['driver'] ?? null) !== 'pgsql') {
    throw new RuntimeException('El ensayo de instalación requiere PostgreSQL.');
}
$aislada = 'savp_instalacion_accesos_'.date('YmdHis').'_'.bin2hex(random_bytes(3));
if (!preg_match('/^savp_instalacion_accesos_[0-9]{14}_[a-f0-9]{6}$/D', $aislada)
    || $aislada === $origen['database']) {
    throw new RuntimeException('Destino aislado inválido.');
}
config(['database.connections.ensayo_instalacion_admin' => [...$origen, 'database' => 'postgres', 'url' => null],
    'database.connections.ensayo_instalacion_accesos' => [...$origen, 'database' => $aislada, 'url' => null]]);
$creada = false;
try {
    DB::connection('ensayo_instalacion_admin')->statement('CREATE DATABASE "'.$aislada.'"');
    $creada = true;
    DB::setDefaultConnection('ensayo_instalacion_accesos');
    Schema::clearResolvedInstance('db.schema');
    if (DB::connection()->getDatabaseName() !== $aislada) {
        throw new RuntimeException('La instalación no está aislada.');
    }
    $salida = Artisan::call('migrate', ['--database' => 'ensayo_instalacion_accesos', '--force' => true]);
    if ($salida !== 0) {
        throw new RuntimeException('Falló la instalación aislada: '.Artisan::output());
    }
    $tablas = ['concesion_acceso', 'concesion_acceso_usuario', 'concesion_acceso_permiso'];
    foreach ($tablas as $tabla) {
        if (!Schema::hasTable($tabla) || DB::table($tabla)->count() !== 0) {
            throw new RuntimeException('La instalación no creó correctamente '.$tabla.'.');
        }
    }
    DB::statement("SET TIME ZONE 'America/La_Paz'");
    DB::statement('CREATE TABLE prueba_reloj_concesion (cod_cac varchar(20) PRIMARY KEY, inicio timestamptz, fin timestamptz, created_at timestamptz, updated_at timestamptz)');
    $instante = \Carbon\CarbonImmutable::parse('2026-10-05T05:53:00Z');
    $prueba = new \App\Models\Oficial\Sistema\ConcesionAcceso;
    $prueba->setTable('prueba_reloj_concesion');
    $prueba->forceFill(['cod_cac'=>'CAC_000001','inicio'=>$instante,'fin'=>$instante->addDay()])->save();
    $guardado = (new \App\Models\Oficial\Sistema\ConcesionAcceso)->setTable('prueba_reloj_concesion')->findOrFail('CAC_000001');
    if (!$guardado->inicio->equalTo($instante) || !$guardado->fin->equalTo($instante->addDay())) {
        throw new RuntimeException('PostgreSQL desplazó el instante de la concesión.');
    }
    echo json_encode(['institucion_modificada' => false, 'instalacion_vacia' => 'correcta',
        'migraciones' => DB::table('migrations')->count(), 'tablas_accesos' => $tablas,
        'hora_bolivia' => 'UTC conservado en sesión America/La_Paz'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} finally {
    DB::setDefaultConnection($anterior);
    Schema::clearResolvedInstance('db.schema');
    DB::purge('ensayo_instalacion_accesos');
    // Únicamente la base creada por este proceso, con nombre comprobado arriba.
    if ($creada) {
        DB::connection('ensayo_instalacion_admin')->statement('DROP DATABASE "'.$aislada.'"');
    }
    DB::purge('ensayo_instalacion_admin');
}
