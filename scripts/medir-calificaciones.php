<?php

// Diagnóstico de lectura: no iniciar sesión ni modificar la base institucional.
require dirname(__DIR__).'/vendor/autoload.php';
$inicio = microtime(true);
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$inicioConsultas = microtime(true);
Illuminate\Support\Facades\View::share('errors', new Illuminate\Support\ViewErrorBag);
$consultas = [];
Illuminate\Support\Facades\DB::listen(function ($evento) use (&$consultas) {
    $consultas[] = ['ms' => $evento->time, 'sql' => $evento->sql];
});
Illuminate\Support\Facades\DB::beginTransaction();
try {
    if (Illuminate\Support\Facades\DB::getDriverName() === 'pgsql') Illuminate\Support\Facades\DB::statement('SET TRANSACTION READ ONLY');
    $actor = App\Models\Oficial\Sistema\User::role('Administrador')->where('est_usu', 'ACTIVO')->get()->first(fn ($u) => $u->can('Calificaciones'));
    if (! $actor) throw new RuntimeException('No existe administrador autorizado para la medición.');
    auth()->setUser($actor);
    $c = new App\Livewire\Admin\Calificaciones;
    $c->boot(); $c->mount();
    $c->actualizarDatos();
    foreach (['honor', 'honor', 'comparativas', 'proyecto', 'estudiantes'] as $vista) {
        $c->cambiarVista($vista);
        $consultas = [];
        $t = microtime(true);
        $html = $c->render()->with(get_object_vars($c))->with('__livewire', $c)->render();
        echo json_encode(['vista' => $vista, 'render_ms' => round(1000*(microtime(true)-$t),1), 'consultas' => count($consultas), 'sql_ms' => round(array_sum(array_column($consultas,'ms')),1), 'html_bytes' => strlen($html),
            'lentas' => collect($consultas)->sortByDesc('ms')->take(4)->values()->map(fn ($q) => ['ms' => $q['ms'], 'sql' => mb_substr($q['sql'],0,240)])->all()], JSON_UNESCAPED_UNICODE).PHP_EOL;
    }
    echo 'Arranque CLI: '.round(1000*($inicioConsultas-$inicio),1).' ms'.PHP_EOL;
} finally { Illuminate\Support\Facades\DB::rollBack(); }
