<?php

use App\Services\AporteIngenieril\AporteIngenierilClient;
use App\Support\Conocimiento\FuenteUniversitariaInteligente;
use Illuminate\Contracts\Console\Kernel;

// Comprueba Laravel -> cliente autorizado -> FastAPI -> directorio documental.
// No inicia extracción, inferencia, sesiones ni escrituras institucionales.
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
$cliente = $app->make(AporteIngenierilClient::class);
$respuesta = $cliente->nationalDirectory();
$gobernanza = $cliente->knowledgeGovernance();
$dominiosPython = array_column($gobernanza->data['trusted_universities'] ?? [], 'domain');
$dominiosLaravel = array_column($app->make(FuenteUniversitariaInteligente::class)->contratoParaInterfaz()['universidades'], 'domain');
sort($dominiosPython);
sort($dominiosLaravel);
$dominiosCoherentes = $gobernanza->available && $dominiosPython === $dominiosLaravel;
echo json_encode([
    'available' => $respuesta->available,
    'status' => $respuesta->status,
    'message' => $respuesta->message,
    'trace_id' => $respuesta->traceId ?? $respuesta->data['trace_id'] ?? null,
    'statistics' => $respuesta->data['statistics'] ?? [],
    'source_domains' => count($dominiosPython),
    'source_domains_match' => $dominiosCoherentes,
    'collection_updated_at' => $respuesta->data['collection_updated_at'] ?? null,
    'coverage_note' => $respuesta->data['coverage_note'] ?? null,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES).PHP_EOL;
exit($respuesta->available && $dominiosCoherentes ? 0 : 1);
