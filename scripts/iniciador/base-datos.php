<?php

declare(strict_types=1);

// Independiente de Laravel: verifica el destino antes de escribir .env o migrar.
$accion = $argv[1] ?? 'inspeccionar';
$nombre = (string) getenv('DB_DATABASE');
$servidor = (string) (getenv('DB_HOST') ?: '127.0.0.1');
$puerto = (string) (getenv('DB_PORT') ?: '5432');
if (! preg_match('/\A[A-Za-z0-9_-]{1,63}\z/', $nombre)
    || ! preg_match('/\A[A-Za-z0-9.:-]+\z/', $servidor)
    || ! ctype_digit($puerto) || (int) $puerto < 1 || (int) $puerto > 65535
    || ! in_array($accion, ['inspeccionar', 'crear'], true)) {
    fwrite(STDERR, "Nombre, servidor, puerto o acción no válidos.\n");
    exit(2);
}
$conectar = static fn (string $base): PDO => new PDO(
    "pgsql:host={$servidor};port={$puerto};dbname={$base};connect_timeout=5",
    (string) getenv('DB_USERNAME'), (string) getenv('DB_PASSWORD'),
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION],
);
try {
    if ($accion === 'crear') {
        if (! preg_match('/\Asavp_demo_[a-z0-9_]+\z/i', $nombre)) {
            throw new RuntimeException('Solo se crean bases nuevas cuyo nombre empieza por savp_demo_.');
        }
        $administrativa = $conectar('postgres');
        $consulta = $administrativa->prepare('SELECT 1 FROM pg_database WHERE datname = ?');
        $consulta->execute([$nombre]);
        if ($consulta->fetchColumn()) {
            throw new RuntimeException('La base ya existe. No se reemplazó ni eliminó.');
        }
        // Identificador restringido y citado; nunca ejecuta SQL escrito por el operador.
        $administrativa->exec('CREATE DATABASE "'.$nombre.'"');
    }
    try {
        $db = $conectar($nombre);
    } catch (PDOException $error) {
        if ($error->getCode() === '3D000' || str_contains($error->getMessage(), 'does not exist')) {
            echo json_encode(['existe' => false, 'base' => $nombre], JSON_THROW_ON_ERROR).PHP_EOL;
            exit(0);
        }
        throw $error;
    }
    $db->beginTransaction();
    $db->exec('SET TRANSACTION READ ONLY');
    $real = $db->query('SELECT current_database()')->fetchColumn();
    if ($real !== $nombre) {
        throw new RuntimeException('La conexión efectiva no coincide con la base solicitada.');
    }
    $tablas = $db->query("SELECT table_name FROM information_schema.tables WHERE table_type='BASE TABLE' AND table_schema NOT IN ('pg_catalog','information_schema') ORDER BY table_name")->fetchAll(PDO::FETCH_COLUMN);
    $requeridas = ['users', 'roles', 'permissions', 'model_has_roles', 'model_has_permissions', 'role_has_permissions', 'sessions', 'migrations'];
    foreach (['Academico', 'AulaVirtual', 'AporteAcademicoVocacional'] as $dominio) {
        foreach (require dirname(__DIR__, 2).'/database/migrations/Oficial/Canonico/'.$dominio.'/estructura.php' as $tabla) {
            $requeridas[] = $tabla['tabla'];
        }
    }
    $faltan = array_values(array_diff($requeridas, $tablas));
    if ($tablas !== []) {
        $consulta = $db->query("SELECT table_name,column_name FROM information_schema.columns WHERE table_schema='public'");
        $columnas = [];
        foreach ($consulta->fetchAll(PDO::FETCH_ASSOC) as $columna) {
            $columnas[$columna['table_name']][$columna['column_name']] = true;
        }
        foreach (['users' => ['cod_usu', 'cod_per', 'est_usu'], 'calificacion' => ['cod_ins', 'cod_pas', 'fea_cal'], 'horario' => ['cod_gac', 'cod_pho'], 'inscripcion_vigencia' => ['cod_ins', 'cod_gac']] as $tabla => $campos) {
            foreach ($campos as $campo) {
                if (! isset($columnas[$tabla][$campo])) {
                    $faltan[] = $tabla.'.'.$campo;
                }
            }
        }
    }
    $demoVacia = preg_match('/\Asavp_demo_[a-z0-9_]+\z/i', $nombre) && $faltan === [];
    if ($demoVacia) {
        foreach (array_unique($tablas) as $tabla) {
            if ($tabla !== 'migrations' && $db->query('SELECT EXISTS(SELECT 1 FROM public."'.str_replace('"', '""', $tabla).'" LIMIT 1)')->fetchColumn()) {
                $demoVacia = false;
                break;
            }
        }
    }
    echo json_encode([
        'existe' => true, 'base' => $real, 'tablas' => count($tablas),
        'vacia' => $tablas === [], 'faltantes' => $faltan,
        'puede_inicializar_demo' => (bool) $demoVacia,
        'protegida' => str_contains(strtolower($nombre), 'oficial') || in_array(strtolower($nombre), ['postgres', 'template0', 'template1'], true),
    ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE).PHP_EOL;
    $db->rollBack();
} catch (Throwable $error) {
    // Las excepciones PDO pueden contener datos de conexión: no imprimirlas completas.
    fwrite(STDERR, $error instanceof PDOException
        ? "No se pudo verificar PostgreSQL. Revisa servidor, puerto, usuario, contraseña y servicio.\n"
        : $error->getMessage().PHP_EOL);
    exit(1);
}
