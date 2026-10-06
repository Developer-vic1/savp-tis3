<?php
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$queries = 0;
$milliseconds = 0;
Illuminate\Support\Facades\DB::listen(function ($query) use (&$queries, &$milliseconds) {
    $queries++;
    $milliseconds += $query->time;
});
$start = microtime(true);
$component = app(App\Livewire\Admin\GestionCurso::class);
$data = $component->render()->getData();
echo json_encode(['consultas'=>$queries, 'ms_consultas'=>round($milliseconds), 'ms_preparacion'=>round((microtime(true)-$start)*1000), 'cursos'=>$data['totalCursos'], 'con_horario'=>$data['totalConHorarios'], 'con_materias'=>$data['totalConPlanAsignatura'], 'con_especialidad'=>$data['totalConPlanEspecialidad']], JSON_PRETTY_PRINT);
