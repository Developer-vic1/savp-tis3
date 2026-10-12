<?php

/** QA artificial: no consulta usuarios ni guarda respuestas o estudios en PostgreSQL. */
require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'session.driver' => 'array', 'cache.default' => 'array']);
Illuminate\Support\Facades\DB::connection()->beforeExecuting(function () {
    throw new RuntimeException('Este ensayo visual no permite consultas a la base de datos.');
});
$session = $app->make('session')->driver('array');
$session->start();
$app->make('request')->setLaravelSession($session);
$app->make('view')->share('errors', new Illuminate\Support\ViewErrorBag());
Illuminate\Support\Facades\URL::forceRootUrl('http://127.0.0.1:8019');
$fixturePath = __DIR__.'/fixture-ui-orientacion.json';

if (!in_array('--render', $argv, true)) {
    $client = $app->make(App\Services\AporteIngenieril\AporteIngenierilClient::class);
    $instrument = $client->riasecInstrument();
    if (!$instrument->available) throw new RuntimeException('Instrumento real no disponible: '.$instrument->message);
    $public = ['instrument_version' => $instrument->data['instrument_version'], 'responses' => []];
    foreach ($instrument->data['items'] as $item) {
        $public['responses'][] = ['item_id' => $item['item_id'], 'value' => (($item['item_id'] * 3) % 5) + 1];
    }
    $score = $client->riasecScore($public);
    if (!$score->available) throw new RuntimeException('Puntaje real no disponible: '.$score->message);
    $record = static fn (string $subject, int $score, string $period = 'Primer trimestre'): array => ['subject' => $subject, 'score' => $score, 'scale_min' => 0, 'scale_max' => 100, 'period' => $period, 'period_order' => 1];
    $payload = ['schema_version' => '2.0', 'student_id' => 'FIXTURE_UI_ORIENTACION', 'academic_period' => 'QA_2026', 'academic' => ['records' => [$record('Matemática', 72), $record('Física', 68)]], 'history' => [['period' => 'QA_2025', 'records' => [$record('Matemática', 65), $record('Lenguaje', 77)]]], 'declared_interests' => [], 'riasec_public' => $public];
    $analysis = $client->analysisV2($payload);
    if (!$analysis->available) throw new RuntimeException('Análisis real no disponible: '.$analysis->message.' (HTTP '.($analysis->status ?? 'sin respuesta').')');
    file_put_contents($fixturePath, json_encode(['notice' => 'Datos artificiales de prueba; nunca resultado personal ni institucional.', 'instrument' => $instrument->data, 'public' => $public, 'score' => $score->data, 'analysis' => $analysis->data, 'payload' => $payload], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
}

$fixture = json_decode(file_get_contents($fixturePath), true, flags: JSON_THROW_ON_ERROR);
$activity = (object) ['riasec_public' => $fixture['public'], 'riasec_score' => $fixture['score'], 'analysis_snapshot' => $fixture['analysis'], 'analysis_completed_at' => now(), 'finalizado_at' => now()];
$context = ['student' => (object) ['cod_est' => 'FIXTURE_UI_ORIENTACION'], 'activity' => $activity, 'draft' => null, 'payload' => $fixture['payload'], 'storageReady' => true, 'gestiones' => ['QA_2026' => 2026, 'QA_2025' => 2025], 'precheck' => $app->make(App\Services\AporteIngenieril\OrientationReadiness::class)->evaluate(true, $fixture['payload'], 'NO_APLICA')];
$context['experiencia'] = App\Support\Orientacion\ExperienciaIntereses::presentar($context);
$context['instrument'] = new App\Services\AporteIngenieril\DTO\AporteResponse(true, $fixture['instrument']);
$manifest = json_decode(file_get_contents(dirname(__DIR__, 2).'/public/build/manifest.json'), true, flags: JSON_THROW_ON_ERROR);
$css = '<link rel="stylesheet" href="/build/'.$manifest['resources/css/app.css']['file'].'">';
foreach ($manifest['resources/js/app.js']['css'] ?? [] as $asset) $css .= '<link rel="stylesheet" href="/build/'.$asset.'">';
$javascript = '/build/'.$manifest['resources/js/app.js']['file'];
$navigation = ['intereses' => 'Cuestionario', 'analisis' => 'Mi análisis', 'futuro' => 'Carreras y universidades', 'plan' => 'Mi plan'];
$variants = ['intereses' => [], 'analisis' => [], 'futuro' => [], 'futuro-todas' => ['explore' => 'all'], 'futuro-universidades' => ['explore' => 'universities'], 'intereses-offline' => [], 'intereses-vacio' => [], 'intereses-borrador' => [], 'analisis-vacio' => [], 'analisis-historico' => [], 'futuro-vacio' => [], 'plan' => [], 'plan-activo' => [], 'plan-cambio' => []];
foreach ($variants as $variant => $query) {
    $page = explode('-', $variant)[0];
    $title = $navigation[$page];
    $app->make('request')->query->replace($query + (in_array($variant, ['futuro-todas', 'futuro-universidades'], true) ? ($qaFilters ?? []) : []));
    $variantContext = $context;
    if($page === 'plan') {
        $catalogoPlan = collect($fixture['analysis']['career_evidence_profiles'])->keyBy('career_id')->all();
        $programasPlan = array_values($catalogoPlan);
        $accionesPlan = [['id'=>'00000000-0000-4000-8000-000000000001','titulo'=>'Leer la malla y preparar tres preguntas para mi docente.','tipo'=>'COMPROMISO_PERSONAL','completada'=>true],['id'=>'00000000-0000-4000-8000-000000000002','titulo'=>'Explorar '.data_get($programasPlan[0],'academic_program.documented_subjects.0','las materias documentadas'),'tipo'=>'MATERIA_DOCUMENTADA','completada'=>false]];
        $planPrueba = ['schema_version'=>'plan-personal-1.0','id'=>'00000000-0000-4000-8000-000000000003','revision'=>2,'career_id'=>$programasPlan[0]['career_id'],'carrera'=>$programasPlan[0]['career_name'],'universidad'=>$programasPlan[0]['university'],'meta'=>'Explorar las materias de esta carrera con mi docente.','acciones'=>$accionesPlan,'cambio_pendiente'=>null];
        if($variant==='plan-cambio') $planPrueba['cambio_pendiente']=['career_id'=>$programasPlan[1]['career_id'],'motivo'=>'Quiero explorar otro enfoque después de revisar sus materias.','solicitado_at'=>now()->toIso8601String(),'score_trace_id'=>'solo-fixture'];
        $variantContext += ['errors'=>new Illuminate\Support\ViewErrorBag(),'academic'=>['metricas'=>['actividades_pendientes'=>0]],'planPersonal'=>$variant==='plan'?null:$planPrueba,'carrerasPlan'=>$catalogoPlan,'progresoPlan'=>$variant==='plan'?0:50,'historialPlanes'=>[],'gestionPlan'=>'QA_2026'];
    }
    if ($variant === 'intereses-offline') {
        $variantContext['instrument'] = new App\Services\AporteIngenieril\DTO\AporteResponse(false, [], 'Servicio indisponible para ensayo visual.');
    }
    if (in_array($variant, ['analisis-vacio', 'futuro-vacio', 'intereses-vacio', 'intereses-borrador'], true)) {
        $variantContext['activity'] = null;
        unset($variantContext['payload']['riasec_public']);
    }
    if (in_array($variant, ['intereses-vacio', 'intereses-borrador'], true) && is_file(__DIR__.'/qa-borrador-fixture.json')) {
        $storedDraft = json_decode(file_get_contents(__DIR__.'/qa-borrador-fixture.json'), true, flags: JSON_THROW_ON_ERROR);
        if (!empty($storedDraft['responses'])) {
            $draftResponses = [];
            foreach ($storedDraft['responses'] as $id => $value) $draftResponses[] = ['item_id' => (int) $id, 'value' => (int) $value];
            $variantContext['draft'] = (object) [
                'riasec_public' => ['instrument_version' => $storedDraft['instrument_version'], 'responses' => $draftResponses],
                'updated_at' => Illuminate\Support\Carbon::parse($storedDraft['savedAt']),
            ];
        }
    }
    if ($variant === 'analisis-historico') {
        $variantContext['activity'] = (object) ['riasec_public' => null, 'riasec_score' => ['raw_scores' => ['R' => 12, 'I' => 18]], 'analysis_snapshot' => null, 'analysis_completed_at' => null, 'finalizado_at' => now()];
        unset($variantContext['payload']['riasec_public']);
    }
    $variantContext['precheck'] = $app->make(App\Services\AporteIngenieril\OrientationReadiness::class)->evaluate(true, $variantContext['payload'], 'NO_APLICA');
    $variantContext['experiencia'] = App\Support\Orientacion\ExperienciaIntereses::presentar($variantContext);
    $source = file_get_contents(dirname(__DIR__, 2).'/resources/views/estudiante/orientacion/'.$page.'.blade.php');
    $source = preg_replace('/^@(?:extends|section)\([^\n]*\)\R/m', '', $source);
    $source = str_replace('@endsection', '', $source);
    $body = Illuminate\Support\Facades\Blade::render($source, $variantContext);
    $body = preg_replace('/(<form\b[^>]*\bmethod="POST"[^>]*\baction=")[^"]*(")/i', '$1/__qa/enviar$2', $body);
    $body = str_replace('http://127.0.0.1:8019/estudiante/intereses/borrador', '/__qa/intereses/borrador', $body);
    $body = preg_replace('/http:\/\/127\.0\.0\.1:8019\/estudiante\/(intereses|analisis|futuro)/', '/__qa/$1', $body);
    $body = preg_replace('/href="http:\/\/127\.0\.0\.1:8019\/estudiante\/[^"]*"/', 'href="#qa-fuera-de-alcance"', $body);
    $links = '';
    foreach ($navigation as $destination => $label) $links .= '<a class="ui-btn-secondary" href="/__qa/'.$destination.'">'.$label.'</a>';
    $html = '<!DOCTYPE html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="csrf-token" content="QA_SYNTHETIC_ONLY"><title>Prueba visual · '.$title.'</title>'.$css.'<style>[x-cloak]{display:none!important}body{background:var(--ui-bg);color:var(--ui-text);font-family:Figtree,system-ui,sans-serif;padding:1rem}.qa-banner{padding:1rem;border:1px solid var(--ui-warning-border);background:var(--ui-warning-soft);border-radius:1rem;max-width:80rem;margin:0 auto 1rem}.qa-header{display:flex;flex-wrap:wrap;gap:.5rem;margin-top:.5rem}.qa-main{max-width:80rem;margin:auto}</style></head><body><header class="qa-banner"><strong>Datos de prueba, no un resultado personal</strong><p>Respuestas y trayectoria artificiales. Puntajes, estudio y fuentes calculados por el servicio real. Ninguna escritura institucional.</p><nav class="qa-header">'.$links.'<button class="ui-btn-secondary" id="qa-tema">Cambiar tema</button></nav></header><main class="qa-main">'.$body.'</main><script type="module">import "'.$javascript.'";await import("/__qa/alpine.js");document.querySelector("#qa-tema").addEventListener("click",()=>document.documentElement.classList.toggle("dark"));</script></body></html>';
    file_put_contents(__DIR__.'/vista-prueba-'.$variant.'.html', $html);
}
echo 'Servicio real verificado: instrumento, puntaje y análisis. '.$fixture['analysis']['analysis_status'].'; '.count($fixture['analysis']['career_evidence_profiles'])." perfiles académicos. HTML de cuestionario, análisis y futuro listos. Sin acceso DB.\n";
