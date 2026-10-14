<?php

declare(strict_types=1);

require dirname(__DIR__, 2).'/vendor/autoload.php';
$raiz = dirname(__DIR__, 2);
$archivo = $raiz.'/.env';
$accion = $argv[1] ?? '';
try {
    if ($accion === 'leer') {
        echo json_encode(is_file($archivo) ? Dotenv\Dotenv::parse(file_get_contents($archivo)) : [], JSON_THROW_ON_ERROR);
        exit;
    }
    if ($accion !== 'escribir') {
        throw new RuntimeException('Acción de entorno no válida.');
    }
    $cambios = json_decode(stream_get_contents(STDIN), true, 32, JSON_THROW_ON_ERROR);
    $contenido = file_get_contents(is_file($archivo) ? $archivo : $raiz.'/.env.example');
    foreach ($cambios as $clave => $valor) {
        if (! preg_match('/\A[A-Z][A-Z0-9_]*\z/', $clave) || ! is_scalar($valor) || strpbrk((string) $valor, "\r\n\0") !== false) {
            throw new RuntimeException('Variable de configuración no válida.');
        }
        $literal = '"'.strtr((string) $valor, ['\\' => '\\\\', '"' => '\\"', '$' => '\\$']).'"';
        $linea = $clave.'='.$literal;
        $patron = '/^'.preg_quote($clave, '/').'\s*=.*$/m';
        $contenido = preg_match($patron, $contenido)
            ? preg_replace_callback($patron, static fn () => $linea, $contenido)
            : rtrim($contenido).PHP_EOL.$linea.PHP_EOL;
    }
    // Validar con el parser real antes de sustituir el archivo.
    $verificado = Dotenv\Dotenv::parse($contenido);
    foreach ($cambios as $clave => $valor) {
        if (($verificado[$clave] ?? null) !== (string) $valor) {
            throw new RuntimeException('No se pudo conservar literalmente una variable de configuración.');
        }
    }
    if (is_file($archivo) && ! copy($archivo, $archivo.'.iniciador-respaldo')) {
        throw new RuntimeException('No se pudo respaldar .env; no se cambió la configuración.');
    }
    if (file_put_contents($archivo.'.iniciador.tmp', $contenido, LOCK_EX) === false || ! rename($archivo.'.iniciador.tmp', $archivo)) {
        throw new RuntimeException('No se pudo guardar la configuración local.');
    }
    echo "Configuración guardada; respaldo local excluido de Git.\n";
} catch (Throwable) {
    // No revelar valores de configuración, incluso cuando el parser falla.
    fwrite(STDERR, "No se pudo procesar el archivo de entorno. No se muestran sus valores privados.\n");
    exit(1);
}
