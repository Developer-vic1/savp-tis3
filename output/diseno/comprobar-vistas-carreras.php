<?php
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
foreach (['components.carrera-orientacion','livewire.shared.notification-center','workspaces.conocimiento-universitario','livewire.admin.academica.vista-previa-universidad','livewire.admin.academica.universidad-visita'] as $view) {
    $path = app('view')->getFinder()->find($view);
    $compiler = app('blade.compiler');
    $compiler->compile($path);
    passthru('"'.PHP_BINARY.'" -l "'.$compiler->getCompiledPath($path).'"', $code);
    if ($code) exit($code);
}
