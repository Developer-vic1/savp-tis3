<?php

namespace App\Services\AporteIngenieril;

use App\Services\AporteIngenieril\DTO\AporteResponse;
use Illuminate\Http\Client\Factory;
use Throwable;

class AporteIngenierilClient
{
    public function __construct(private readonly Factory $http) {}

    public function health(): AporteResponse
    {
        return $this->request('get', config('services.peter3.paths.health', '/health'));
    }

    public function analysis(array $payload): AporteResponse
    {
        return $this->request('post', config('services.peter3.paths.analysis') ?: '/api/'.config('services.peter3.version', 'v1').'/analysis', $payload);
    }

    public function knowledge(array $payload): AporteResponse
    {
        return $this->request('post', config('services.peter3.paths.knowledge') ?: '/api/'.config('services.peter3.version', 'v1').'/knowledge/search', $payload);
    }

    public function tutor(array $payload): AporteResponse
    {
        return $this->request('post', config('services.peter3.paths.tutor') ?: '/api/'.config('services.peter3.version', 'v1').'/tutor/query', $payload);
    }

    private function request(string $method, string $path, array $payload = []): AporteResponse
    {
        $baseUrl = trim((string) config('services.peter3.url'));
        if ($baseUrl === '') {
            return AporteResponse::unavailable();
        }

        try {
            $request = $this->http
                ->baseUrl(rtrim($baseUrl, '/'))
                ->acceptJson()
                ->connectTimeout((int) config('services.peter3.connect_timeout', 3))
                ->timeout((int) config('services.peter3.timeout', 10));
            $response = $method === 'get' ? $request->get($path) : $request->post($path, $payload);

            if (! $response->successful() || ! is_array($response->json()) || $response->json() === []) {
                return AporteResponse::unavailable();
            }

            return new AporteResponse(true, $response->json(), 'Información académica disponible.');
        } catch (Throwable) {
            // Las excepciones HTTP pueden incluir URL, credenciales o cuerpos académicos.
            return AporteResponse::unavailable();
        }
    }
}
