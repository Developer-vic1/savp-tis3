<?php

namespace App\Services\AporteIngenieril;

use App\Contracts\SpecializedAcademicClient;
use App\Services\AporteIngenieril\DTO\AporteResponse;
use App\Services\AporteIngenieril\DTO\Peter3Contract;
use App\Services\AporteIngenieril\DTO\Peter3V2Contract;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\Facades\Log;
use Throwable;

class AporteIngenierilClient implements SpecializedAcademicClient
{
    public function __construct(private readonly Factory $http) {}

    public function health(): AporteResponse
    {
        return $this->request('get', config('services.peter3.paths.health', '/health'));
    }

    public function analysis(array $payload): AporteResponse
    {
        return $this->request('post', config('services.peter3.paths.analysis') ?: '/api/'.config('services.peter3.version', 'v1').'/analysis', $payload, 'analysis');
    }

    public function knowledge(array $payload): AporteResponse
    {
        return $this->request('post', config('services.peter3.paths.knowledge') ?: '/api/'.config('services.peter3.version', 'v1').'/knowledge/search', $payload, 'knowledge');
    }

    public function tutor(array $payload): AporteResponse
    {
        return $this->request('post', config('services.peter3.paths.tutor') ?: '/api/'.config('services.peter3.version', 'v1').'/tutor/query', $payload, 'tutor');
    }

    public function riasecInstrument(): AporteResponse
    {
        return $this->request('get', '/api/v2/riasec/instrument', [], 'riasec_instrument');
    }

    public function riasecScore(array $payload): AporteResponse
    {
        return $this->request('post', '/api/v2/riasec/score', $payload, 'riasec_score');
    }

    public function analysisV2(array $payload): AporteResponse
    {
        return $this->request('post', '/api/v2/analysis', $payload, 'analysis_v2');
    }

    public function warmUp(): AporteResponse
    {
        return $this->request('post', '/api/v1/knowledge/search', ['schema_version' => '1.0', 'query' => 'materias iniciales de Ingeniería Civil', 'top_k' => 1, 'official_only' => true], 'knowledge', true);
    }

    private function request(string $method, string $path, array $payload = [], string $operation = 'health', bool $coldStart = false): AporteResponse
    {
        $baseUrl = trim((string) config('services.peter3.url'));
        if (! config('services.peter3.enabled', false) || $baseUrl === '') {
            return AporteResponse::unavailable();
        }

        if (! filter_var($baseUrl, FILTER_VALIDATE_URL) || ! in_array(parse_url($baseUrl, PHP_URL_SCHEME), ['http', 'https'], true)
            || parse_url($baseUrl, PHP_URL_USER) !== null || parse_url($baseUrl, PHP_URL_PASS) !== null
            || parse_url($baseUrl, PHP_URL_QUERY) !== null || parse_url($baseUrl, PHP_URL_FRAGMENT) !== null
            || ! str_starts_with($path, '/') || str_starts_with($path, '//') || str_contains($path, '://')) {
            return AporteResponse::unavailable();
        }
        $v2 = in_array($operation, ['analysis_v2', 'riasec_instrument', 'riasec_score'], true);
        if ($method === 'post' && ! ($v2 ? Peter3V2Contract::validRequest($operation, $payload) : Peter3Contract::validRequest($operation, $payload))) {
            return AporteResponse::unavailable();
        }

        if (isset($payload['student_id'])) {
            $payload['student_id'] = hash_hmac('sha256', (string) $payload['student_id'], (string) config('app.key'));
        }

        $started = hrtime(true);
        $status = null;
        $traceId = null;
        try {
            $request = $this->http
                ->baseUrl(rtrim($baseUrl, '/'))
                ->acceptJson()
                ->withHeaders(array_filter(['X-SAVP-AI-Key' => config('services.peter3.key')]))
                ->withoutRedirecting()
                ->connectTimeout(max(1, min(10, (int) config('services.peter3.connect_timeout', 3))))
                ->timeout($coldStart ? max(1, min(180, (int) config('services.peter3.cold_start_timeout', 90)))
                    : (in_array($operation, ['health', 'riasec_instrument', 'riasec_score'], true) ? 5 : max(1, min(30, (int) config('services.peter3.timeout', 10)))));
            $response = $method === 'get' ? $request->get($path) : $request->post($path, $payload);
            $status = $response->status();
            $body = $response->json();
            $traceId = $response->header('X-Trace-Id') ?: (is_array($body) ? ($body['trace_id'] ?? $body['error']['trace_id'] ?? null) : null);
            $traceId = is_string($traceId) && preg_match('/^[A-Za-z0-9._:-]{1,100}$/', $traceId) ? $traceId : null;
            $latency = (hrtime(true) - $started) / 1e6;
            if (! $response->successful() || ! ($v2 ? Peter3V2Contract::validResponse($operation, $body) : Peter3Contract::validResponse($operation, $body))
                || (isset($body['trace_id']) && $traceId !== $body['trace_id'])
                || ($operation === 'riasec_score' && ($body['instrument_version'] ?? null) !== $payload['instrument_version'])
                || ($operation === 'analysis_v2' && (($body['student_ref'] ?? null) !== $payload['student_id'] || ($body['traceability']['trace_id'] ?? null) !== ($body['trace_id'] ?? null)))) {
                $message = $status === 422 ? 'Algunos datos del perfil necesitan revisión antes de continuar.' : 'El servicio de análisis está temporalmente no disponible. Tus datos no se han perdido.';
                return new AporteResponse(false, [], $message, $status, $traceId, $latency);
            }

            $serverLatency = preg_match('/^app;dur=([0-9]+(?:\.[0-9]+)?)$/', $response->header('Server-Timing'), $timing) ? (float) $timing[1] : null;
            return new AporteResponse(true, $body, 'Información académica disponible.', $status, $traceId, $latency, $serverLatency);
        } catch (Throwable) {
            // Las excepciones HTTP pueden incluir URL, credenciales o cuerpos académicos.
            return new AporteResponse(false, [], 'El análisis académico no está disponible temporalmente.', null, $traceId, (hrtime(true) - $started) / 1e6);
        } finally {
            Log::info('peter3.request', ['endpoint' => $path, 'status' => $status, 'trace_id' => $traceId, 'latency_ms' => round((hrtime(true) - $started) / 1e6, 3), 'timestamp' => now()->toIso8601String()]);
        }
    }
}
