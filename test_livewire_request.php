<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use App\Livewire\Admin\GestionUsuarios;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Livewire\Livewire;

try {
    Livewire::actingAs(User::first())
        ->test(GestionUsuarios::class)
        ->set('search', 'A')
        ->assertOk()
        ->call('limpiarFiltros')
        ->assertOk()
        ->call('abrirModalCrear')
        ->assertOk();
    echo 'Livewire requests worked perfectly without PHP errors.';
} catch (Throwable $e) {
    echo 'ERROR: '.$e->getMessage()."\n".$e->getTraceAsString();
}
