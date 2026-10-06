<?php

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

// Exclusivamente lectura. No ejecuta seeders, migraciones ni cambios de configuración persistentes.
$target = $argv[1] ?? '';
if (! in_array($target, ['configurada', 'entrenamiento'], true)) {
    fwrite(STDERR, "Uso: php scripts/auditar_flujo_estudiantil.php configurada|entrenamiento\n");
    exit(2);
}
if ($target === 'entrenamiento') {
    $path = storage_path('framework/savp-training.sqlite');
    if (! is_file($path)) {
        throw new RuntimeException('La SQLite de entrenamiento no existe.');
    }
    config(['database.connections.auditoria' => array_merge(config('database.connections.sqlite'), [
        'url' => null, 'database' => $path,
    ])]);
    $connection = DB::connection('auditoria');
} else {
    $connection = DB::connection();
}
$driver = $connection->getDriverName();
if (! in_array($driver, ['pgsql', 'sqlite'], true)) {
    throw new RuntimeException('La auditoría solo admite PostgreSQL o SQLite con lectura protegida.');
}
if ($driver === 'sqlite') {
    $connection->statement('PRAGMA query_only = ON');
}

$report = $connection->transaction(function () use ($connection, $driver, $target): array {
    if ($driver === 'pgsql') {
        $connection->statement('SET TRANSACTION READ ONLY');
    }
    $schema = $connection->getSchemaBuilder();
    $tables = $schema->getTables($driver === 'pgsql' ? 'public' : null);
    $inventory = [];
    foreach ($tables as $table) {
        $name = $table['name'];
        $inventory[$name] = [
            'rows' => $connection->table($name)->count(),
            'columns' => $schema->getColumns($name),
            'indexes' => $schema->getIndexes($name),
            'foreign_keys' => $schema->getForeignKeys($name),
        ];
    }
    ksort($inventory);
    $catalogs = [];
    foreach (['gestion_academica', 'curso', 'paralelo', 'turno', 'periodo_evaluacion',
        'asignatura', 'estado_asistencia', 'tipo_vinculacion_estudiante', 'especialidad_tecnica'] as $name) {
        if (isset($inventory[$name])) {
            $catalogs[$name] = $connection->table($name)->get()->all();
        }
    }
    $studentTables = [];
    foreach ($inventory as $name => $table) {
        $columnNames = array_column($table['columns'], 'name');
        $references = array_filter($table['foreign_keys'], fn (array $fk): bool => in_array($fk['foreign_table'], ['estudiante', 'inscripcion_estudiante', 'persona', 'users'], true));
        if ($references || array_intersect($columnNames, ['cod_est', 'cod_ins'])) {
            $studentTables[$name] = [
                'rows' => $table['rows'],
                'identity_columns' => array_values(array_intersect($columnNames,
                    ['cod_est', 'cod_ins', 'cod_per', 'cod_usu', 'cod_usu_reg'])),
                'references' => array_values($references),
            ];
        }
    }
    $checks = $driver === 'pgsql'
        ? $connection->select("SELECT t.relname AS tabla, c.conname AS nombre,
            pg_get_constraintdef(c.oid) AS definicion
            FROM pg_constraint c JOIN pg_class t ON t.oid = c.conrelid
            JOIN pg_namespace n ON n.oid = t.relnamespace
            WHERE c.contype = 'c' AND n.nspname = 'public' ORDER BY t.relname, c.conname")
        : [];
    $enrollmentGroups = isset($inventory['inscripcion_estudiante'])
        ? $connection->table('inscripcion_estudiante')
            ->select('cod_gea', 'cod_cur', 'cod_par', 'cod_tur', 'est_ins')
            ->selectRaw('COUNT(*) AS estudiantes')
            ->groupBy('cod_gea', 'cod_cur', 'cod_par', 'cod_tur', 'est_ins')
            ->orderBy('cod_gea')->orderBy('cod_cur')->get()->all()
        : [];
    $schedule = isset($inventory['horario_detalle'])
        ? $connection->table('horario_detalle as d')
            ->join('horario as h', 'h.cod_hor', '=', 'd.cod_hor')
            ->join('plan_asignatura as p', 'p.cod_pas', '=', 'd.cod_pas')
            ->join('asignatura as a', 'a.cod_asi', '=', 'p.cod_asi')
            ->join('plantilla_horaria as ph', 'ph.cod_pho', '=', 'h.cod_pho')
            ->select('h.cod_gea', 'h.cod_cur', 'h.cod_par', 'h.cod_pho', 'ph.cod_tur',
                'p.cod_pas', 'p.cod_doc', 'a.cod_asi', 'a.nom_asi')
            ->selectRaw('COUNT(*) AS bloques_semanales')
            ->groupBy('h.cod_gea', 'h.cod_cur', 'h.cod_par', 'h.cod_pho', 'ph.cod_tur',
                'p.cod_pas', 'p.cod_doc', 'a.cod_asi', 'a.nom_asi')
            ->orderBy('h.cod_gea')->orderBy('h.cod_cur')->orderBy('h.cod_par')->get()->all()
        : [];

    return [
        'generated_at_utc' => gmdate(DATE_ATOM),
        'mode' => 'READ_ONLY', 'target' => $target, 'driver' => $driver,
        'database' => $connection->getDatabaseName(),
        'tables' => $inventory, 'student_dependencies' => $studentTables,
        'check_constraints' => $checks, 'catalogs' => $catalogs,
        'enrollment_groups' => $enrollmentGroups, 'schedule_assignments' => $schedule,
    ];
});
echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR).PHP_EOL;
