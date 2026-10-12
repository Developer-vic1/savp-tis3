<?php

namespace App\Services\AporteIngenieril;

use App\Contracts\SpecializedAcademicClient;
use App\Rules\FechaPublicacionAcademica;
use App\Rules\UrlFuenteUniversitaria;
use App\Services\AporteIngenieril\DTO\AporteResponse;
use App\Services\AporteIngenieril\DTO\ContratoAporteIngenieril;
use App\Services\AporteIngenieril\DTO\ContratoAporteIngenierilV2;
use App\Support\Conocimiento\ContratoColeccionUniversitaria;
use App\Support\Conocimiento\FuenteUniversitariaInteligente;
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

    public function nationalDirectory(): AporteResponse
    {
        return $this->request('get', '/api/v1/knowledge/national-directory', [], 'knowledge_governance_national_directory');
    }

    public function unifranzCollection(bool $iniciar = false): AporteResponse
    {
        return $this->request($iniciar ? 'post' : 'get', '/api/v1/knowledge/collections/unifranz', [], 'knowledge_governance_university_collection');
    }

    public function analizarColeccionUnifranz(): AporteResponse
    {
        return $this->request('post', '/api/v1/knowledge/collections/unifranz/analyze', [], 'knowledge_governance_university_collection');
    }

    public function documentoUnifranz(string $hash): AporteResponse
    {
        if (! preg_match('/^[a-f0-9]{64}$/', $hash)) {
            return AporteResponse::unavailable();
        }

        return $this->request('get', '/api/v1/knowledge/collections/unifranz/documents/'.$hash, [], 'knowledge_governance_collection_document');
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

    public function revisarActualizacionFuente(string $sourceId): AporteResponse
    {
        return $this->request('post', '/api/v1/knowledge/governance/check-update', ['source_id' => $sourceId], 'knowledge_governance_update');
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

    public function extractKnowledgeSource(string $proposalId): AporteResponse
    {
        if (! preg_match('/^KGI-[A-F0-9]{12}$/', $proposalId)) {
            return AporteResponse::unavailable();
        }

        return $this->request('post', '/api/v1/knowledge/governance/proposals/'.$proposalId.'/extract', [], 'knowledge_governance_extract');
    }

    public function readKnowledgeExtraction(string $proposalId): AporteResponse
    {
        if (! preg_match('/^KGI-[A-F0-9]{12}$/', $proposalId)) {
            return AporteResponse::unavailable();
        }

        return $this->request('get', '/api/v1/knowledge/governance/proposals/'.$proposalId.'/extract', [], 'knowledge_governance_extract');
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
        $timeout = $coldStart
            ? max(1, min(180, (int) config('services.aporte_ingenieril.cold_start_timeout', 90)))
            : match (true) {
                in_array($operation, ['health', 'riasec_instrument', 'riasec_score'], true) => 5,
                in_array($operation, ['knowledge_governance_preview', 'knowledge_governance_university_preview', 'knowledge_governance_update'], true) => 20,
                $operation === 'knowledge_governance_extract' => 30,
                default => max(1, min($operation === 'tutor' ? 120 : 30, (int) config('services.aporte_ingenieril.timeout', 10))),
            };
        try {
            $request = $this->http
                ->baseUrl(rtrim($baseUrl, '/'))
                ->acceptJson()
                ->withHeaders(array_filter(['X-SAVP-AI-Key' => $apiKey]))
                ->withoutRedirecting()
                ->connectTimeout(max(1, min(10, (int) config('services.aporte_ingenieril.connect_timeout', 3))))
                ->timeout($timeout);
            $response = $method === 'get' ? $request->get($path) : $request->post($path, $payload);
            $status = $response->status();
            if ($operation === 'knowledge_governance_collection_document') {
                $content = $response->body();
                $hash = basename($path);
                $name = rawurldecode((string) $response->header('X-SAVP-Document-Name'));
                if (! $response->successful() || ! str_starts_with(strtolower((string) $response->header('Content-Type')), 'application/pdf')
                    || ! str_starts_with($content, '%PDF-') || strlen($content) > 12 * 1024 * 1024
                    || ! hash_equals($hash, hash('sha256', $content))
                    || ! preg_match('/^[\p{L}\p{N} ()._-]{1,200}\.pdf$/u', $name)) {
                    return AporteResponse::unavailable();
                }

                return new AporteResponse(true, ['content' => $content, 'filename' => $name]);
            }
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
            'knowledge_governance_update' => $this->hasExactKeys($payload, ['source_id'])
                && Validator::make($payload, ['source_id' => ['required', 'string', 'max:160', 'regex:/^[A-Za-z0-9_-]+$/']])->passes(),
            'knowledge_governance_preview', 'knowledge_governance_university_preview' => $this->hasExactKeys($payload, ['url'])
                && Validator::make($payload, ['url' => ['required', 'string', 'url:https', 'max:2000', new UrlFuenteUniversitaria]])->passes(),
            'knowledge_governance_analyze' => $this->validSourceCandidate($payload),
            'knowledge_governance_submit' => $this->validSourceCandidate($payload, true),
            'knowledge_governance_review' => $this->hasExactKeys($payload, ['approved', 'review_note', 'reviewed_by_role'])
                && is_bool($payload['approved'])
                && is_string($payload['review_note']) && mb_strlen($payload['review_note']) <= 800
                && $payload['reviewed_by_role'] === 'Administrador',
            'knowledge_governance_extract', 'knowledge_governance_university_collection' => $payload === [],
            default => false,
        };
    }

    private function validKnowledgeGovernanceResponse(string $operation, mixed $body): bool
    {
        if (! is_array($body) || ($body['schema_version'] ?? null) !== '1.0' || ! is_string($body['trace_id'] ?? null)) {
            return false;
        }

        return match ($operation) {
            'knowledge_governance_national_directory' => $this->validNationalDirectoryResponse($body),
            'knowledge_governance_update' => Validator::make($body, [
                'source_id' => ['required', 'string', 'max:160', 'regex:/^[A-Za-z0-9_-]+$/'],
                'checked_at' => ['required', 'date'],
                'status' => ['required', 'in:VERIFICADA,BLOQUEADA,NO_DISPONIBLE'],
                'fingerprint' => ['present', 'nullable', 'regex:/^[a-f0-9]{64}$/'],
                'message' => ['required', 'string', 'max:500'],
            ])->passes() && (($body['status'] ?? '') !== 'VERIFICADA' || is_string($body['fingerprint'] ?? null)),
            'knowledge_governance_university_collection' => ContratoColeccionUniversitaria::valido($body),
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
                'institutional_url' => ['nullable', 'url:https', 'max:2000', new UrlFuenteUniversitaria],
                'university_key' => ['nullable', 'string', 'max:253'],
                'career_links' => ['sometimes', 'array', 'max:30'],
                'career_links.*' => ['array:name,url,status'],
                'career_links.*.name' => ['required', 'string', 'max:180'],
                'career_links.*.url' => ['required', 'url:https', 'max:2000', new UrlFuenteUniversitaria],
                'career_links.*.status' => ['required', 'in:POR_REVISAR'],
                'reading_fragments' => ['sometimes', 'array', 'max:8'],
                'reading_fragments.*' => ['array:id,text,url,document_hash'],
                'reading_fragments.*.id' => ['required', 'regex:/^[a-f0-9]{24}$/'],
                'reading_fragments.*.text' => ['required', 'string', 'max:600'],
                'reading_fragments.*.url' => ['required', 'url:https', 'max:2000', new UrlFuenteUniversitaria],
                'reading_fragments.*.document_hash' => ['required', 'regex:/^sha256:[a-f0-9]{64}$/'],
                'documents' => ['sometimes', 'array', 'max:6'],
                'documents.*' => ['required', 'array:url,title,kind'],
                'documents.*.url' => ['required', 'string', 'url:https', 'max:2000', new UrlFuenteUniversitaria],
                'documents.*.title' => ['required', 'string', 'min:3', 'max:240'],
                'documents.*.kind' => ['required', 'string', 'in:malla,reglamento'],
                'academic_pages' => ['sometimes', 'array', 'max:6'],
                'academic_pages.*' => ['required', 'array:url,title,kind'],
                'academic_pages.*.url' => ['required', 'string', 'url:https', 'max:2000', new UrlFuenteUniversitaria],
                'academic_pages.*.title' => ['required', 'string', 'min:3', 'max:240'],
                'academic_pages.*.kind' => ['required', 'string', 'in:carrera,plan,reglamento'],
                'document_kind' => ['sometimes', 'required', 'string', 'in:PUBLIC_DOCUMENT,STUDENT_PORTAL,AUTHENTICATION,UNKNOWN'],
                'embedding' => ['sometimes', 'required', 'array:allowed,reason', 'required_array_keys:allowed,reason', function ($attribute, $value, $fail) {
                    if (is_array($value) && array_key_exists('allowed', $value) && $value['allowed'] !== null && ! is_bool($value['allowed'])) {
                        $fail('Invalid embedding boolean.');
                    }
                }],
                'embedding.reason' => ['nullable', 'string', 'max:1000'],
            ])->passes()
                && $this->enlacesAcademicosMismaUniversidad($body, 'documents', true)
                && $this->enlacesAcademicosMismaUniversidad($body, 'academic_pages', false)
                && $this->contenidoNoAcademicoRestringido($body)
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
            'knowledge_governance_extract' => $this->validKnowledgeExtractionResponse($body),
            default => false,
        };
    }

    private function validNationalDirectoryResponse(array $body): bool
    {
        return Validator::make($body, [
            'generated_at' => ['present', 'nullable', 'string', 'max:80'],
            'coverage_note' => ['required', 'string', 'max:8000'],
            'evidence_sources' => ['present', 'array'],
            'coverage_discrepancies' => ['present', 'array'],
            'institutions' => ['present', 'array', 'max:500'],
            'institutions.*.institution_id' => ['required', 'string', 'max:120', 'distinct'],
            'institutions.*.name' => ['required', 'string', 'max:500'],
            'institutions.*.regime' => ['required', 'string', 'max:100'],
            'institutions.*.departments' => ['present', 'array'],
            'institutions.*.departments.*' => ['string', 'max:120'],
            'institutions.*.programs' => ['present', 'array'],
            'institutions.*.programs.*.name' => ['required', 'string', 'max:500'],
            'institutions.*.programs.*.duration_text' => ['nullable', 'string', 'max:300'],
            'institutions.*.programs.*.duration_semesters' => ['nullable', 'integer', 'min:1'],
            'institutions.*.programs.*.initial_subjects' => ['sometimes', 'array'],
            'institutions.*.programs.*.sources' => ['sometimes', 'array'],
            'institutions.*.programs.*.curriculum_status' => ['required', 'string', 'max:100'],
            'institutions.*.collection' => ['present', 'array'],
            'statistics' => ['required', 'array'],
            'statistics.institutions' => ['required', 'integer', 'min:0'],
            'statistics.domains_verified' => ['required', 'integer', 'min:0'],
            'statistics.documented_programs' => ['required', 'integer', 'min:0'],
            'statistics.extracted_documents' => ['required', 'integer', 'min:0'],
            'statistics.pending_urls' => ['required', 'integer', 'min:0'],
            'collection_updated_at' => ['present', 'nullable', 'string', 'max:80'],
        ])->passes();
    }

    private function validKnowledgeExtractionResponse(array $body): bool
    {
        $validator = Validator::make($body, [
            'proposal_id' => ['required', 'regex:/^KGI-[A-F0-9]{12}$/'],
            'status' => ['required', 'in:EXTRAIDA_PENDIENTE_REVISION,NO_DISPONIBLE'],
            'source_url' => ['required', 'string', 'url:https', 'max:2000', new UrlFuenteUniversitaria],
            'final_url' => ['sometimes', 'nullable', 'string', 'url:https', 'max:2000', new UrlFuenteUniversitaria],
            'document_hash' => ['sometimes', 'nullable', 'regex:/^sha256:[a-f0-9]{64}$/'],
            'content_type' => ['sometimes', 'nullable', 'string', 'max:120'],
            'metrics' => ['present', 'array:pages,segments,chunks,facts'],
            'metrics.*' => ['integer', 'min:0', 'max:100000'],
            'facts' => ['present', 'array', 'max:500'],
            'facts.*' => ['required', 'array:kind,value,evidence,page,section,source_url'],
            'facts.*.kind' => ['required', 'in:DURACION,MATERIA,CARRERA'],
            'facts.*.value' => ['required', 'string', 'max:500'],
            'facts.*.evidence' => ['required', 'string', 'max:2000'],
            'facts.*.source_url' => ['required', 'url:https', 'max:2000', new UrlFuenteUniversitaria],
            'facts.*.page' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'facts.*.section' => ['sometimes', 'nullable', 'string', 'max:240'],
            'chunks' => ['present', 'array', 'max:8'],
            'chunks.*' => ['required', 'array:chunk_id,text,page,section'],
            'chunks.*.chunk_id' => ['required', 'string', 'max:120'],
            'chunks.*.text' => ['required', 'string', 'max:2000'],
            'chunks.*.page' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'chunks.*.section' => ['sometimes', 'nullable', 'string', 'max:240'],
            'embeddings' => ['required', 'array:status,model,vectors,reason', 'required_array_keys:status,model,vectors,reason'],
            'embeddings.status' => ['required', 'in:NO_GENERADOS,GENERADOS_PENDIENTES_PUBLICACION'],
            'embeddings.model' => ['present', 'nullable', 'string', 'max:240', 'required_if:embeddings.status,GENERADOS_PENDIENTES_PUBLICACION'],
            'embeddings.vectors' => ['required', 'integer', 'min:0', 'max:100000'],
            'embeddings.reason' => ['required', 'string', 'max:1000'],
            'warnings' => ['present', 'array', 'max:30'],
            'warnings.*' => ['string', 'max:1000'],
            'linked_sources' => ['sometimes', 'array', 'max:3'],
            'linked_sources.*' => ['array:url,title,kind', 'required_array_keys:url,title,kind'],
            'linked_sources.*.url' => ['required', 'url:https', 'max:2000', new UrlFuenteUniversitaria],
            'linked_sources.*.title' => ['required', 'string', 'min:3', 'max:240'],
            'linked_sources.*.kind' => ['required', 'in:malla,reglamento,carrera,plan'],
            'coverage' => ['sometimes', 'array:fetched_documents,maximum_documents'],
            'coverage.fetched_documents' => ['sometimes', 'integer', 'min:0', 'max:4'],
            'coverage.maximum_documents' => ['sometimes', 'integer', 'min:1', 'max:4'],
        ]);
        if (! $validator->passes()) {
            return false;
        }

        $embeddings = $body['embeddings'];
        $embeddingsValidos = $embeddings['status'] === 'NO_GENERADOS'
            ? $embeddings['model'] === null && $embeddings['vectors'] === 0
            : $body['status'] === 'EXTRAIDA_PENDIENTE_REVISION'
                && is_int($embeddings['vectors']) && $embeddings['vectors'] > 0
                && $embeddings['vectors'] === ($body['metrics']['chunks'] ?? null);
        if (! $embeddingsValidos) {
            return false;
        }

        $soporte = app(FuenteUniversitariaInteligente::class);
        $universidad = $soporte->universidad($body['source_url']);
        if ($universidad === null) {
            return false;
        }
        foreach ([$body['final_url'] ?? null, ...array_column($body['facts'], 'source_url'), ...array_column($body['linked_sources'] ?? [], 'url')] as $enlace) {
            if ($enlace !== null && ($soporte->universidad($enlace)['domain'] ?? null) !== $universidad['domain']) {
                return false;
            }
        }

        return $body['status'] === 'NO_DISPONIBLE'
            || (isset($body['document_hash']) && is_array($body['metrics'])
                && count(array_intersect(['pages', 'segments', 'chunks', 'facts'], array_keys($body['metrics']))) === 4);
    }

    private function contenidoNoAcademicoRestringido(array $body): bool
    {
        if (! in_array($body['document_kind'] ?? null, ['STUDENT_PORTAL', 'AUTHENTICATION'], true)) {
            return true;
        }

        // Un dominio universitario reconocido no convierte un acceso privado en evidencia académica.
        return $body['can_use'] === false && $body['excerpt'] === ''
            && ($body['reading_fragments'] ?? []) === []
            && ($body['academic_pages'] ?? []) === []
            && ($body['documents'] ?? []) === [] && $body['suggested_fields'] === [];
    }

    private function enlacesAcademicosMismaUniversidad(array $body, string $campo, bool $pdf): bool
    {
        $documentos = $body[$campo] ?? [];
        if (! array_is_list($documentos)) {
            return false;
        }
        if ($documentos === []) {
            return true;
        }
        $soporte = app(FuenteUniversitariaInteligente::class);
        $universidad = $soporte->universidad($body['requested_url']);
        if ($universidad === null || $soporte->errorUrl($body['requested_url']) !== null) {
            return false;
        }
        foreach ($documentos as $documento) {
            $destino = $soporte->universidad($documento['url']);
            if ($destino === null || $destino['domain'] !== $universidad['domain']
                || $soporte->errorUrl($documento['url']) !== null
                || str_ends_with(strtolower((string) parse_url($documento['url'], PHP_URL_PATH)), '.pdf') !== $pdf) {
                return false;
            }
        }

        return true;
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
