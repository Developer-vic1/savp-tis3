<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

DB::beginTransaction();
DB::statement('SET TRANSACTION READ ONLY');
try {
    $usuario = App\Models\Oficial\Sistema\User::role('Administrador')->firstOrFail();
    auth()->setUser($usuario);
    view()->share('errors', new Illuminate\Support\ViewErrorBag());
    DB::enableQueryLog();
    $inicio = microtime(true);
    $componente = app(App\Livewire\Admin\GestionCurso::class);
    $componente->mount();
    if (in_array('--ficha', $argv, true)) {
        $componente->consultarCurso($componente->resumen->first()['cod_cur']);
    }
    $vista = $componente->render();
    $html = $vista->with($componente->all())->render();
    $consultas = DB::getQueryLog();
    echo json_encode([
        'ms' => round((microtime(true) - $inicio) * 1000, 1),
        'consultas' => count($consultas),
        'sql_ms' => round(array_sum(array_column($consultas, 'time')), 1),
        'html_bytes' => strlen($html),
        'lentas' => array_values(array_map(fn ($q) => ['sql' => $q['query'], 'ms' => $q['time']], array_filter($consultas, fn ($q) => $q['time'] > 20))),
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} finally {
    DB::rollBack();
}
