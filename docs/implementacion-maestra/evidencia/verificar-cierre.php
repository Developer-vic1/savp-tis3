<?php

// Solo autoload/reflexión/rutas y compilación Blade. No invoca consultas ni migrations.
require __DIR__.'/../../../vendor/autoload.php';
$app = require __DIR__.'/../../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$errors = [];
$checked = 0;
foreach ($app['router']->getRoutes() as $route) {
    $action = $route->getActionName();
    if (! str_starts_with($action, 'App\\') || ! str_contains($action, '@')) {
        continue;
    }
    [$class, $method] = explode('@', $action, 2);
    if (! class_exists($class) || ! method_exists($class, $method)) {
        $errors[] = $action;
    }
    $checked++;
}
$views = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__.'/../../../resources/views'));
$count = 0;
foreach ($views as $file) {
    if ($file->isFile() && str_ends_with($file->getFilename(), '.blade.php')) {
        $app['blade.compiler']->compile($file->getRealPath());
        $count++;
    }
}
echo json_encode(['rutas_app_reflexion' => $checked, 'errores' => $errors, 'blades_compilados' => $count,
    'consultas_bd' => 'NO', 'migrations_up_down' => 'NO'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
exit($errors ? 1 : 0);
