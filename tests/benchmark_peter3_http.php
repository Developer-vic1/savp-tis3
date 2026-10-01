<?php

// Explicit loopback experiment: never migrate, seed, or write student records.
use App\Models\User;
use App\Services\AporteIngenieril\AporteIngenierilClient;
use App\Services\AporteIngenieril\StudentOrientationService;

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config()->set('database.default', 'sqlite');
config()->set('database.connections.sqlite.database', storage_path('framework/fusion-e2e.sqlite'));
config()->set('database.connections.sqlite.url', null);
config()->set('services.peter3.enabled', true);
config()->set('services.peter3.url', 'http://127.0.0.1:8001');
config()->set('services.peter3.key', trim(file_get_contents(storage_path('logs/peter3-session.key'))));
config()->set('services.peter3.timeout', 30);
$client = app(AporteIngenierilClient::class);
$context = app(StudentOrientationService::class)->context(User::where('email', 'orientation@example.test')->firstOrFail());
$public = $context['payload']['riasec_public'] ?? throw new RuntimeException('Complete isolated browser RIASEC first.');
$knowledge = ['schema_version'=>'1.0','query'=>'materias iniciales de Ingeniería Civil','top_k'=>5,'official_only'=>true];
$tutor = ['schema_version'=>'1.0','question'=>$knowledge['query']];
$operations = [
    'health'=>fn()=>[$client->health()], 'riasec'=>fn()=>[$client->riasecScore($public)],
    'analysis_v2'=>fn()=>[$client->analysisV2($context['payload'])],
    'knowledge'=>fn()=>[$client->knowledge($knowledge)], 'tutor'=>fn()=>[$client->tutor($tutor)],
    'pipeline'=>fn()=>[$client->analysisV2($context['payload']),$client->knowledge($knowledge),$client->tutor($tutor)],
];
$start = hrtime(true);
$warmup = $client->warmUp();
if (!$warmup->available) throw new RuntimeException($warmup->message);
$result = ['classification'=>'ISOLATED_TEST_DATA','transport'=>'REAL_LOOPBACK_HTTP','timestamp'=>gmdate('c'),'php'=>PHP_VERSION,'corpus_sha256'=>hash_file('sha256',base_path('ai-service/data/processed/corpus.jsonl')),'preload_ms'=>(hrtime(true)-$start)/1e6,'preload_cache_state'=>'See validation report; not assumed cold','overhead_definition'=>'Outer PHP client duration minus summed Server-Timing app duration; includes loopback transport, PHP contract validation and scheduling; not pure PHP cost','repetitions'=>20,'operations'=>[]];
foreach ($operations as $name=>$operation) {
    $samples=[];
    for ($i=0;$i<20;$i++) {
        $start=hrtime(true); $responses=$operation(); $total=(hrtime(true)-$start)/1e6;
        $server=0;
        foreach ($responses as $response) {
            if (!$response->available || $response->serverLatencyMs===null) throw new RuntimeException('Unmeasurable HTTP response: '.$name);
            $server += $response->serverLatencyMs;
        }
        $samples[]=['total_ms'=>$total,'fastapi_ms'=>$server,'transport_php_scheduling_ms'=>max(0,$total-$server),'trace_ids'=>array_map(fn($r)=>$r->traceId,$responses)];
    }
    $summary=[];
    foreach (['total_ms','fastapi_ms','transport_php_scheduling_ms'] as $metric) {
        $values=array_column($samples,$metric); sort($values);
        $summary[$metric]=['min'=>$values[0],'median'=>($values[9]+$values[10])/2,'p95'=>$values[18],'max'=>$values[19]];
    }
    $result['operations'][$name]=['summary'=>$summary,'samples'=>$samples];
}
file_put_contents(base_path('ai-service/data/evaluation/laravel_http_performance.json'),json_encode($result,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
echo json_encode(array_map(fn($r)=>$r['summary'],$result['operations']),JSON_PRETTY_PRINT),PHP_EOL;
