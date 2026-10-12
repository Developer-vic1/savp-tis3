<?php

// Servidor temporal de QA: solo HTML artificial, recursos públicos y respuestas simuladas.
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$root = dirname(__DIR__, 2);
// Estas rutas se resuelven aquí: nunca se despachan al kernel HTTP de Laravel.
if (in_array($path, ['/estudiante/intereses/borrador', '/__qa/intereses/borrador'], true) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $mode = is_file(__DIR__.'/qa-borrador-modo.txt') ? trim(file_get_contents(__DIR__.'/qa-borrador-modo.txt')) : 'success';
    usleep(750000);
    if ($mode !== 'success') {
        http_response_code(503);
        echo json_encode(['notice' => ['title' => 'No pudimos guardar el borrador de prueba', 'message' => 'Fallo 503 simulado por el servidor aislado.', 'preserved' => 'El borrador anterior no fue modificado; las respuestas actuales permanecen en pantalla.', 'next_step' => 'Conserva esta pestaña y vuelve a intentarlo.']]);
        return;
    }
    $fixture = json_decode(file_get_contents(__DIR__.'/fixture-ui-orientacion.json'), true, flags: JSON_THROW_ON_ERROR);
    $responses = $_POST['responses'] ?? [];
    $valid = is_array($responses) && count($responses) >= 1 && count($responses) <= 30
        && !isset($_POST['student_id']) && ($_POST['instrument_version'] ?? '') === $fixture['instrument']['instrument_version'];
    foreach (is_array($responses) ? $responses : [] as $id => $value) {
        if (!preg_match('/^(?:[1-9]|[12][0-9]|30)$/', (string) $id) || !is_scalar($value) || !preg_match('/^[1-5]$/', (string) $value)) $valid = false;
    }
    if (!$valid) {
        http_response_code(422);
        echo json_encode(['notice' => ['title' => 'Revisa el borrador artificial', 'message' => 'La fixture admite entre 1 y 30 respuestas de la versión vigente, con valores de 1 a 5.']]);
        return;
    }
    $responses = array_map('intval', $responses);
    ksort($responses, SORT_NUMERIC);
    $draft = ['notice' => 'Borrador artificial de QA; nunca datos institucionales.', 'instrument_version' => $_POST['instrument_version'], 'responses' => (object) $responses, 'savedAt' => gmdate(DATE_ATOM)];
    file_put_contents(__DIR__.'/qa-borrador-fixture.json', json_encode($draft, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR), LOCK_EX);
    echo json_encode(['draftSaved' => true, 'answered' => count($responses), 'responses' => (object) $responses, 'savedAt' => $draft['savedAt']]);
    return;
}
if ($path === '/__qa/enviar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $mode = is_file(__DIR__.'/qa-enviar-modo.txt') ? trim(file_get_contents(__DIR__.'/qa-enviar-modo.txt')) : 'unavailable';
    file_put_contents(__DIR__.'/qa-envio.json', json_encode(['count' => count($_POST['responses'] ?? []), 'instrument_version' => $_POST['instrument_version'] ?? null]));
    sleep(5);
    header('Content-Type: application/json');
    if ($mode === 'success') {
        echo json_encode(['redirect' => '/__qa/analisis#mi-perfil']);
    } else {
        http_response_code(503);
        echo json_encode(['notice' => ['title' => 'El servicio está temporalmente ocupado', 'message' => 'Vuelve a intentarlo cuando esté disponible.', 'preserved' => 'Tus respuestas permanecen en pantalla.']]);
    }
    return;
}
if (str_starts_with($path, '/build/') && preg_match('~^/build/[a-zA-Z0-9/_\-.]+$~', $path)) {
    $file = $root.'/public'.$path;
} elseif ($path === '/__qa/alpine.js') {
    $file = $root.'/node_modules/alpinejs/dist/cdn.min.js';
} else {
    parse_str(parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY) ?? '', $query);
    $pages = ['intereses', 'analisis', 'futuro', 'intereses-offline', 'intereses-vacio', 'intereses-borrador', 'analisis-vacio', 'analisis-historico', 'futuro-vacio', 'plan', 'plan-activo', 'plan-cambio'];
    $page = substr($path, strlen('/__qa/'));
    if (! str_starts_with($path, '/__qa/') || ! in_array($page, $pages, true)) {
        http_response_code(404);
        return;
    }
    if ($page === 'futuro' && ($query['explore'] ?? '') === 'all') $page = 'futuro-todas';
    if ($page === 'futuro' && ($query['explore'] ?? '') === 'universities') $page = 'futuro-universidades';
    if (in_array($page, ['futuro-todas', 'futuro-universidades', 'intereses-vacio', 'intereses-borrador'], true)) {
        $qaPage = $page;
        $qaFilters = $query;
        $argv = ['preparar-vista-orientacion.php', '--render'];
        ob_start();
        require __DIR__.'/preparar-vista-orientacion.php';
        ob_end_clean();
        $page = $qaPage;
    }
    $file = __DIR__.'/vista-prueba-'.$page.'.html';
}
if (! is_file($file)) {
    http_response_code(404);
    return;
}
$types = ['css' => 'text/css', 'js' => 'text/javascript', 'html' => 'text/html', 'woff2' => 'font/woff2', 'woff' => 'font/woff', 'ttf' => 'font/ttf', 'svg' => 'image/svg+xml'];
header('Content-Type: '.($types[pathinfo($file, PATHINFO_EXTENSION)] ?? 'application/octet-stream'));
readfile($file);
