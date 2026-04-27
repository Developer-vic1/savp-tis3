<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Livewire\Livewire;

try {
    Livewire::test(\App\Livewire\Admin\GestionUsuarios::class)
        ->assertOk()
        ->assertSee('Gestión de seleccionados');
    echo "Livewire test passed successfully!\n";
} catch (\Exception $e) {
    echo "Livewire test FAILED: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}
