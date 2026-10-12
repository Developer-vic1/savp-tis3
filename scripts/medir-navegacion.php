<?php

// Render de rutas GET existentes con un actor autorizado y PostgreSQL de solo lectura.
require dirname(__DIR__).'/vendor/autoload.php';
$inicio = microtime(true);
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
Illuminate\Support\Facades\View::share('errors', new Illuminate\Support\ViewErrorBag);
Illuminate\Support\Facades\DB::beginTransaction();
try {
    Illuminate\Support\Facades\DB::statement('SET TRANSACTION READ ONLY');
    $actor = App\Models\Oficial\Sistema\User::role('Administrador')->where('est_usu','ACTIVO')->get()->first(fn ($u) => $u->can('Calificaciones'));
    if (!$actor) throw new RuntimeException('Falta administrador autorizado para la medición.');
    auth()->setUser($actor);
    $consultas = [];
    Illuminate\Support\Facades\DB::listen(function ($e) use (&$consultas) { $consultas[] = ['ms'=>$e->time,'sql'=>$e->sql]; });
    foreach (['/admin', '/admin/calificaciones', '/admin/gestion-personas', '/admin/gestion-cursos', '/admin/reportes-administrativos', '/admin/gestion-academica', '/admin/gestion-turnos'] as $url) {
        $permiso = ['/admin/calificaciones'=>'Calificaciones', '/admin/gestion-personas'=>'Registro_Personas', '/admin/gestion-cursos'=>'Cursos', '/admin/reportes-administrativos'=>'Reportes_Administrativos', '/admin/gestion-academica'=>'Gestion_Academica', '/admin/gestion-turnos'=>'Turnos'][$url] ?? null;
        if ($permiso && !$actor->can($permiso)) { echo $url.': lectura omitida por permiso.'.PHP_EOL; continue; }
        $request = Illuminate\Http\Request::create('http://127.0.0.1:8000'.$url, 'GET');
        $session = new Illuminate\Session\Store('diagnostico', new Illuminate\Session\ArraySessionHandler(120));
        $session->start(); $request->setLaravelSession($session);
        $route = app('router')->getRoutes()->match($request); $route->bind($request); $route->setContainer($app);
        $request->setRouteResolver(fn () => $route); $app->instance('request', $request);
        $consultas = []; $t = microtime(true);
        try {
            $resultado = $route->run();
            $html = $resultado instanceof Illuminate\Contracts\View\View ? $resultado->render() : (string)$resultado;
            echo json_encode(['ruta'=>$url,'render_ms'=>round(1000*(microtime(true)-$t),1),'consultas'=>count($consultas),'sql_ms'=>round(array_sum(array_column($consultas,'ms')),1),'html_bytes'=>strlen($html),'vite_desarrollo'=>str_contains($html,'5173'),'lentas'=>collect($consultas)->sortByDesc('ms')->take(3)->values()->map(fn($q)=>['ms'=>$q['ms'],'sql'=>substr($q['sql'],0,130)])->all()], JSON_UNESCAPED_UNICODE).PHP_EOL;
        } catch (Throwable $e) { echo $url.': '.$e->getMessage().PHP_EOL; }
    }
    echo 'Total CLI: '.round(microtime(true)-$inicio,2).' s'.PHP_EOL;
} finally { Illuminate\Support\Facades\DB::rollBack(); }
