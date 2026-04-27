<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Livewire\Livewire;

try {
    Livewire::actingAs(\App\Models\User::first())
        ->test(\App\Livewire\Admin\GestionUsuarios::class)
        ->set('search', 'A')
        ->assertOk()
        ->call('limpiarFiltros')
        ->assertOk()
        ->call('abrirModalCrear')
        ->assertOk();
    echo "Livewire requests worked perfectly without PHP errors.";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString();
}
