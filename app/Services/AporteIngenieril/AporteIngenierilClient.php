<?php

namespace App\Services\AporteIngenieril;

use App\Contracts\SpecializedAcademicClient;
use App\Services\AporteIngenieril\DTO\AporteResponse;
use App\Services\AporteIngenieril\DTO\Peter3Contract;
use Illuminate\Http\Client\Factory;
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

    private function request(string $method, string $path, array $payload = [], string $operation = 'health'): AporteResponse
    {
        $baseUrl = trim((string) config('services.peter3.url'));
        if (! config('services.peter3.enabled', false) || $baseUrl === '') {
            return AporteResponse::unavailable();
        }

        if (! filter_var($baseUrl, FILTER_VALIDATE_URL) || ! in_array(parse_url($baseUrl, PHP_URL_SCHEME), ['http', 'https'], true)
            || ! str_starts_with($path, '/') || str_starts_with($path, '//') || str_contains($path, '://')) {
            return AporteResponse::unavailable();
        }
        if ($method === 'post' && ! Peter3Contract::validRequest($operation, $payload)) {
            return AporteResponse::unavailable();
        }

        if (isset($payload['student_id'])) {
            $payload['student_id'] = hash_hmac('sha256', (string) $payload['student_id'], (string) config('app.key'));
        }

        try {
            $request = $this->http
                ->baseUrl(rtrim($baseUrl, '/'))
                ->acceptJson()
                ->withHeaders(array_filter(['X-SAVP-AI-Key' => config('services.peter3.key')]))
                ->withoutRedirecting()
                ->connectTimeout(max(1, min(10, (int) config('services.peter3.connect_timeout', 3))))
                ->timeout(max(1, min(30, (int) config('services.peter3.timeout', 10))));
            $response = $method === 'get' ? $request->get($path) : $request->post($path, $payload);

            if (! $response->successful() || ! Peter3Contract::validResponse($operation, $response->json())) {
                return AporteResponse::unavailable();
            }

            return new AporteResponse(true, $response->json(), 'Información académica disponible.');
        } catch (Throwable) {
            // Las excepciones HTTP pueden incluir URL, credenciales o cuerpos académicos.
            return AporteResponse::unavailable();
        }
    }
}
