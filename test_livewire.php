<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use App\Livewire\Admin\GestionUsuarios;
use Illuminate\Contracts\Console\Kernel;
use Livewire\Livewire;

try {
    Livewire::test(GestionUsuarios::class)
        ->assertOk()
        ->assertSee('Gestión de seleccionados');
    echo "Livewire test passed successfully!\n";
} catch (Exception $e) {
    echo 'Livewire test FAILED: '.$e->getMessage()."\n".$e->getTraceAsString()."\n";
}
