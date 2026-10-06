<?php

declare(strict_types=1);

/**
 * Enrutador para el servidor de desarrollo integrado de PHP en Windows.
 *
 * El enrutador de Laravel escribe cada solicitud en php://stdout. Ese flujo
 * puede ser inválido cuando un IDE inicia el proceso sin consola y producir
 * `file_put_contents(): errno=22`. Esta variante delega las rutas dinámicas a
 * Laravel y permite que PHP sirva archivos existentes sin escribir allí.
 */
$rutaPublica = __DIR__;
$rutaSolicitada = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '');

if ($rutaSolicitada !== '/' && is_file($rutaPublica.$rutaSolicitada)) {
    return false;
}

require_once $rutaPublica.'/index.php';
