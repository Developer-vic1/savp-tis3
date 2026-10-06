<?php

namespace App\Services\AporteIngenieril;

use App\Contracts\SpecializedAcademicClient;
use App\Rules\FechaPublicacionAcademica;
use App\Rules\UrlFuenteUniversitaria;
use App\Services\AporteIngenieril\DTO\AporteResponse;
use App\Services\AporteIngenieril\DTO\ContratoAporteIngenieril;
use App\Services\AporteIngenieril\DTO\ContratoAporteIngenierilV2;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Throwable;

class AporteIngenierilClient implements SpecializedAcademicClient
{
    public function __construct(private readonly Factory $http) {}

    public function health(): AporteResponse
    {
        return $this->request('get', config('services.aporte_ingenieril.paths.health', '/health'));
    }

    public function analysis(array $payload): AporteResponse
    {
        return $this->request('post', config('services.aporte_ingenieril.paths.analysis') ?: '/api/'.config('services.aporte_ingenieril.version', 'v1').'/analysis', $payload, 'analysis');
    }

    public function knowledge(array $payload): AporteResponse
    {
        return $this->request('post', config('services.aporte_ingenieril.paths.knowledge') ?: '/api/'.config('services.aporte_ingenieril.version', 'v1').'/knowledge/search', $payload, 'knowledge');
    }

    public function knowledgeGovernance(): AporteResponse
    {
        return $this->request('get', config('services.aporte_ingenieril.paths.knowledge_governance') ?: '/api/v1/knowledge/governance', [], 'knowledge_governance');
    }

    public function analyzeKnowledgeSource(array $payload): AporteResponse
    {
        return $this->request('post', '/api/v1/knowledge/governance/analyze', $payload, 'knowledge_governance_analyze');
    }

    public function inspectKnowledgeSource(array $payload): AporteResponse
    {
        return $this->request('post', '/api/v1/knowledge/governance/preview', $payload, 'knowledge_governance_preview');
    }

    public function inspectUniversity(array $payload): AporteResponse
    {
        return $this->request('post', '/api/v1/knowledge/governance/university-preview', $payload, 'knowledge_governance_university_preview');
    }

    public function submitKnowledgeSource(array $payload): AporteResponse
    {
        return $this->request('post', '/api/v1/knowledge/governance/proposals', $payload, 'knowledge_governance_submit');
    }

    public function reviewKnowledgeSource(string $proposalId, array $payload): AporteResponse
    {
        if (! preg_match('/^KGI-[A-F0-9]{12}$/', $proposalId)) {
            return AporteResponse::unavailable();
        }

        return $this->request('post', '/api/v1/knowledge/governance/proposals/'.$proposalId.'/review', $payload, 'knowledge_governance_review');
    }

    public function tutor(array $payload): AporteResponse
    {
        return $this->request('post', config('services.aporte_ingenieril.paths.tutor') ?: '/api/'.config('services.aporte_ingenieril.version', 'v1').'/tutor/query', $payload, 'tutor');
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
        $baseUrl = trim((string) config('services.aporte_ingenieril.url'));
        if (! config('services.aporte_ingenieril.enabled', false) || $baseUrl === '') {
            return AporteResponse::unavailable();
        }

        $scheme = parse_url($baseUrl, PHP_URL_SCHEME);
        $host = strtolower(rtrim((string) parse_url($baseUrl, PHP_URL_HOST), '.'));
        $allowedHosts = array_map(
            fn ($allowedHost) => strtolower(rtrim((string) $allowedHost, '.')),
            (array) config('services.aporte_ingenieril.allowed_hosts', [])
        );
        $apiKey = config('services.aporte_ingenieril.key');
        if (! filter_var($baseUrl, FILTER_VALIDATE_URL) || ! in_array($scheme, ['http', 'https'], true)
            || ! in_array($host, $allowedHosts, true)
            || ($scheme === 'http' && ! in_array($host, ['127.0.0.1', 'localhost', '::1'], true))
            || parse_url($baseUrl, PHP_URL_USER) !== null || parse_url($baseUrl, PHP_URL_PASS) !== null
            || parse_url($baseUrl, PHP_URL_QUERY) !== null || parse_url($baseUrl, PHP_URL_FRAGMENT) !== null
            || ! str_starts_with($path, '/') || str_starts_with($path, '//') || str_contains($path, '://')) {
            return AporteResponse::unavailable();
        }
        if ($operation !== 'health' && (! is_string($apiKey) || strlen($apiKey) < 32)) {
            return AporteResponse::unavailable();
        }
        $v2 = in_array($operation, ['analysis_v2', 'riasec_instrument', 'riasec_score'], true);
        $governance = str_starts_with($operation, 'knowledge_governance');
        if ($method === 'post' && ! ($governance
            ? $this->validKnowledgeGovernanceRequest($operation, $payload)
            : ($v2 ? ContratoAporteIngenierilV2::solicitudValida($operation, $payload) : ContratoAporteIngenieril::solicitudValida($operation, $payload)))) {
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
                ->withHeaders(array_filter(['X-SAVP-AI-Key' => $apiKey]))
                ->withoutRedirecting()
                ->connectTimeout(max(1, min(10, (int) config('services.aporte_ingenieril.connect_timeout', 3))))
                ->timeout($coldStart ? max(1, min(180, (int) config('services.aporte_ingenieril.cold_start_timeout', 90)))
                    : (in_array($operation, ['health', 'riasec_instrument', 'riasec_score'], true)
                        ? 5
                        : (in_array($operation, ['knowledge_governance_preview','knowledge_governance_university_preview'], true) ? 20
                        : max(1, min($operation === 'tutor' ? 120 : 30, (int) config('services.aporte_ingenieril.timeout', 10))))));
            $response = $method === 'get' ? $request->get($path) : $request->post($path, $payload);
            $status = $response->status();
            $body = $response->json();
            $traceId = $response->header('X-Trace-Id') ?: (is_array($body) ? ($body['trace_id'] ?? $body['error']['trace_id'] ?? null) : null);
            $traceId = is_string($traceId) && preg_match('/^[A-Za-z0-9._:-]{1,100}$/', $traceId) ? $traceId : null;
            $latency = (hrtime(true) - $started) / 1e6;
            $validResponse = $governance
                ? $this->validKnowledgeGovernanceResponse($operation, $body)
                : ($v2 ? ContratoAporteIngenierilV2::respuestaValida($operation, $body) : ContratoAporteIngenieril::respuestaValida($operation, $body));
            if (! $response->successful() || ! $validResponse
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
            Log::info('aporte_ingenieril.request', ['endpoint' => $path, 'status' => $status, 'trace_id' => $traceId, 'latency_ms' => round((hrtime(true) - $started) / 1e6, 3), 'timestamp' => now()->toIso8601String()]);
        }
    }

    private function validKnowledgeGovernanceRequest(string $operation, array $payload): bool
    {
        return match ($operation) {
            'knowledge_governance_preview', 'knowledge_governance_university_preview' => $this->hasExactKeys($payload, ['url'])
                && Validator::make($payload, ['url' => ['required', 'string', 'url:https', 'max:2000', new UrlFuenteUniversitaria]])->passes(),
            'knowledge_governance_analyze' => $this->validSourceCandidate($payload),
            'knowledge_governance_submit' => $this->validSourceCandidate($payload, true),
            'knowledge_governance_review' => $this->hasExactKeys($payload, ['approved', 'review_note', 'reviewed_by_role'])
                && is_bool($payload['approved'])
                && is_string($payload['review_note']) && mb_strlen($payload['review_note']) <= 800
                && $payload['reviewed_by_role'] === 'Administrador',
            default => false,
        };
    }

    private function validKnowledgeGovernanceResponse(string $operation, mixed $body): bool
    {
        if (! is_array($body) || ($body['schema_version'] ?? null) !== '1.0' || ! is_string($body['trace_id'] ?? null)) {
            return false;
        }

        return match ($operation) {
            'knowledge_governance_preview', 'knowledge_governance_university_preview' => Validator::make($body, [
                'requested_url' => ['required', 'string', 'max:2000'],
                'status' => ['required', 'in:VERIFICADA,DUPLICADA,BLOQUEADA,NO_DISPONIBLE,REQUIERE_REVISION'],
                'can_use' => ['present', function ($attribute, $value, $fail) {
                    if (! is_bool($value)) {
                        $fail('Invalid boolean.');
                    }
                }],
                'title' => ['present', 'nullable', 'string', 'max:240'],
                'message' => ['required', 'string', 'max:2000'],
                'excerpt' => ['present', 'string', 'max:1500'],
                'assessment' => ['required', 'array'],
                'checked_at' => ['required', 'date'],
                'suggested_fields' => ['present', 'array:title,declared_institution,source_type,scope,publication_date'],
                'suggested_fields.*' => ['string', 'max:500'],
                'warnings' => ['present', 'array', 'max:10'],
                'warnings.*' => ['string', 'max:1000'],
                'duplicate' => ['present', 'nullable', 'array:kind,id,title,status,url'],
                'duplicate.*' => ['string', 'max:2000'],
                'institutional_url' => ['nullable','url:https','max:2000',new UrlFuenteUniversitaria],
                'university_key' => ['nullable','string','max:253'],
                'career_links' => ['sometimes','array','max:30'],
                'career_links.*' => ['array:name,url,status'],
                'career_links.*.name' => ['required','string','max:180'],
                'career_links.*.url' => ['required','url:https','max:2000',new UrlFuenteUniversitaria],
                'career_links.*.status' => ['required','in:POR_REVISAR'],
                'reading_fragments' => ['sometimes','array','max:8'],
                'reading_fragments.*' => ['array:id,text,url,document_hash'],
                'reading_fragments.*.id' => ['required','regex:/^[a-f0-9]{24}$/'],
                'reading_fragments.*.text' => ['required','string','max:600'],
                'reading_fragments.*.url' => ['required','url:https','max:2000',new UrlFuenteUniversitaria],
                'reading_fragments.*.document_hash' => ['required','regex:/^sha256:[a-f0-9]{64}$/'],
            ])->passes()
                && (! $body['can_use'] || ($body['status'] === 'VERIFICADA' && ($body['reachable'] ?? null) === true
                    && ($body['http_status'] ?? null) === 200 && is_string($body['title']) && mb_strlen($body['title']) >= 3
                    && preg_match('/^sha256:[a-f0-9]{64}$/', (string) ($body['document_hash'] ?? '')) === 1)),
            'knowledge_governance' => is_int($body['source_count'] ?? null)
                && is_array($body['sources'] ?? null)
                && is_array($body['trusted_universities'] ?? null)
                && is_array($body['proposals'] ?? null),
            'knowledge_governance_analyze' => is_array($body['assessment'] ?? null)
                && is_string($body['readiness'] ?? null)
                && is_string($body['risk_level'] ?? null)
                && is_bool($body['can_submit'] ?? null)
                && is_array($body['checks'] ?? null)
                && is_array($body['suggestions'] ?? null),
            'knowledge_governance_submit' => is_bool($body['accepted'] ?? null)
                && is_string($body['message'] ?? null)
                && is_array($body['assessment'] ?? null),
            'knowledge_governance_review' => is_string($body['message'] ?? null)
                && is_array($body['proposal'] ?? null),
            default => false,
        };
    }

    private function validSourceCandidate(array $payload, bool $withRole = false): bool
    {
        $fields = ['title', 'declared_institution', 'url', 'source_type', 'scope', 'publication_date', 'version', 'campus', 'city', 'justification', 'limitations'];
        if ($withRole) {
            $fields[] = 'submitted_by_role';
        }
        if (! $this->hasExactKeys($payload, $fields)) {
            return false;
        }
        $rules = [
            'title' => 'required|string|min:3|max:240',
            'declared_institution' => 'required|string|min:3|max:240',
            'url' => ['bail', 'required', 'string', 'url:https', 'max:2000', new UrlFuenteUniversitaria],
            'source_type' => 'required|in:OFFICIAL_CURRICULUM_PDF,OFFICIAL_CAREER_HTML,OFFICIAL_REGULATION_PDF,OFFICIAL_UNIVERSITY_PAGE',
            'scope' => 'required|string|min:10|max:500',
            'publication_date' => ['nullable', 'string', new FechaPublicacionAcademica],
            'version' => 'required|string|max:120',
            'campus' => 'required|string|min:2|max:160',
            'city' => 'required|string|min:2|max:120',
            'justification' => 'required|string|min:20|max:1000',
            'limitations' => 'present|array|max:10',
            'limitations.*' => 'string|min:3|max:500',
        ];
        if ($withRole) {
            $rules['submitted_by_role'] = 'required|in:Administrador,Director';
        }

        return ! Validator::make($payload, $rules)->fails();
    }

    private function hasExactKeys(array $payload, array $expected): bool
    {
        $keys = array_keys($payload);
        sort($keys);
        sort($expected);

        return $keys === $expected;
    }
}
