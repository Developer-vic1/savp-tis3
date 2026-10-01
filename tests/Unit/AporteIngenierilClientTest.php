<?php

namespace Tests\Unit;

use App\Services\AporteIngenieril\AporteIngenierilClient;
use App\Services\AporteIngenieril\DTO\AcademicAnalysisData;
use App\Services\AporteIngenieril\KnowledgeService;
use App\Services\AporteIngenieril\TutorService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AporteIngenierilClientTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config()->set('services.peter3.url', 'https://peter3.test');
        config()->set('services.peter3.enabled', true);
        config()->set('services.peter3.version', 'v1');
    }

    private function payload(): array
    {
        return ['schema_version' => '1.0', 'student_id' => 'EST_0001'];
    }

    private function response(): array
    {
        return ['schema_version' => '1.0', 'status' => 'PARTIAL', 'trace_id' => 'test-trace', 'student_ref' => 'test-reference',
            'generated_at' => '2026-09-30T12:00:00Z', 'input_hash' => hash('sha256', 'fixture'), 'coverage' => ['ratio' => 0, 'components' => []],
            'warnings' => [], 'career_ranking' => [], 'ranking_status' => 'INSUFFICIENT_EVIDENCE'];
    }

    public function test_it_returns_valid_versioned_data_when_service_is_available(): void
    {
        Http::fake(['peter3.test/*' => Http::response($this->response())]);
        $result = app(AporteIngenierilClient::class)->analysis($this->payload());
        $this->assertTrue($result->available);
        $this->assertSame('PARTIAL', $result->data['status']);
    }

    public function test_it_uses_safe_fallback_for_503_and_invalid_json(): void
    {
        Http::fakeSequence()->push([], 503)->push('no-json', 200);
        $client = app(AporteIngenierilClient::class);
        $this->assertFalse($client->analysis($this->payload())->available);
        $this->assertFalse($client->analysis($this->payload())->available);
    }

    public function test_it_uses_safe_fallback_for_validation_error(): void
    {
        Http::fake(['peter3.test/*' => Http::response(['detail' => 'invalid'], 422)]);
        $this->assertFalse(app(AporteIngenierilClient::class)->analysis($this->payload())->available);
    }

    public function test_it_uses_safe_fallback_for_timeout(): void
    {
        Http::fake(fn () => throw new ConnectionException('operation timed out'));
        $this->assertFalse(app(AporteIngenierilClient::class)->analysis($this->payload())->available);
    }

    public function test_it_uses_safe_fallback_for_connection_failure(): void
    {
        Http::fake(fn () => throw new ConnectionException('connection refused'));
        $result = app(AporteIngenierilClient::class)->health();
        $this->assertFalse($result->available);
        $this->assertSame('El análisis académico no está disponible temporalmente.', $result->message);
    }

    public function test_academic_payload_excludes_personal_identity_fields(): void
    {
        $payload = (new AcademicAnalysisData('EST_0001', '2026', [], [], []))->toPayload();
        $this->assertArrayNotHasKey('email', $payload);
        $this->assertArrayNotHasKey('nombre', $payload);
        $this->assertArrayNotHasKey('ci', $payload);
        $this->assertSame('1.0', $payload['schema_version']);
        $this->assertSame('2026', $payload['academic_period']);
    }

    public function test_empty_response_is_not_presented_as_evidence(): void
    {
        Http::fake(['peter3.test/*' => Http::response([])]);
        $this->assertFalse(app(AporteIngenierilClient::class)->analysis($this->payload())->available);
    }

    public function test_integration_stays_off_until_explicitly_enabled(): void
    {
        config()->set('services.peter3.enabled', false);
        Http::fake();
        $this->assertFalse(app(AporteIngenierilClient::class)->analysis($this->payload())->available);
        Http::assertNothingSent();
    }

    public function test_internal_student_identifier_is_replaced_with_a_reference(): void
    {
        Http::fake(['peter3.test/*' => Http::response($this->response())]);
        app(AporteIngenierilClient::class)->analysis($this->payload());
        Http::assertSent(fn ($request) => is_string($request['student_id']) && strlen($request['student_id']) === 64 && $request['student_id'] !== 'EST_0001');
    }

    public function test_json_list_is_an_invalid_specialized_response(): void
    {
        Http::fake(['peter3.test/*' => Http::response(['unexpected', 'list'])]);
        $this->assertFalse(app(AporteIngenierilClient::class)->analysis($this->payload())->available);
    }

    public function test_endpoint_is_configurable_without_changing_the_contract(): void
    {
        config()->set('services.peter3.paths.analysis', '/analysis');
        Http::fake(['peter3.test/analysis' => Http::response($this->response())]);
        $this->assertTrue(app(AporteIngenierilClient::class)->analysis($this->payload())->available);
        Http::assertSent(fn ($request) => $request->url() === 'https://peter3.test/analysis');
    }

    public function test_tutor_uses_the_existing_contract_without_sending_identity(): void
    {
        Http::fake(['peter3.test/*' => Http::response(['schema_version' => '1.0', 'trace_id' => 'test', 'answer' => 'Explicación de prueba',
            'answer_mode' => 'STRUCTURED', 'sources' => [], 'warnings' => [], 'suggested_topics' => [], 'insufficient_evidence' => true])]);
        $this->assertTrue(app(TutorService::class)->ask('EST_PRIVATE', 'Explica álgebra', ['email' => 'private@example.test'])->available);
        Http::assertSent(fn ($request) => $request['schema_version'] === '1.0' && $request['question'] === 'Explica álgebra' && ! isset($request['student_id']) && ! isset($request['academic_context']));
    }

    public function test_knowledge_request_uses_official_corpus_filters(): void
    {
        Http::fake(['peter3.test/*' => Http::response(['schema_version' => '1.0', 'trace_id' => 'test', 'results' => [], 'warnings' => [],
            'insufficient_evidence' => true, 'corpus_version' => 'fixture', 'embedding_model' => 'fixture', 'retrieval_version' => 'fixture'])]);
        $this->assertTrue(app(KnowledgeService::class)->search('Matemáticas')->available);
        Http::assertSent(fn ($request) => $request['query'] === 'Matemáticas' && $request['official_only'] === true && $request['top_k'] === 5);
    }

    public function test_civil_identity_and_invalid_scales_are_rejected_before_network(): void
    {
        Http::fake();
        $client = app(AporteIngenierilClient::class);
        $this->assertFalse($client->analysis($this->payload() + ['email' => 'secret@example.test'])->available);
        $this->assertFalse($client->analysis($this->payload() + ['academic' => ['records' => [['subject' => 'Álgebra', 'score' => 120, 'scale_min' => 0, 'scale_max' => 100]]]])->available);
        Http::assertNothingSent();
    }

    public function test_unsupported_or_malformed_response_never_becomes_available(): void
    {
        $invalid = $this->response();
        $invalid['schema_version'] = '2.0';
        Http::fakeSequence()->push($invalid)->push(['schema_version' => '1.0', 'status' => 'PARTIAL']);
        $client = app(AporteIngenierilClient::class);
        $this->assertFalse($client->analysis($this->payload())->available);
        $this->assertFalse($client->analysis($this->payload())->available);
    }

    public function test_authentication_header_is_sent_only_to_configured_service(): void
    {
        config()->set('services.peter3.key', 'fixture-internal-key');
        Http::fake(['peter3.test/*' => Http::response($this->response())]);
        app(AporteIngenierilClient::class)->analysis($this->payload());
        Http::assertSent(fn ($request) => $request->hasHeader('X-SAVP-AI-Key', 'fixture-internal-key'));
    }

    public function test_absolute_endpoint_cannot_redirect_credentials_to_another_host(): void
    {
        config()->set('services.peter3.paths.analysis', 'https://foreign.test/analysis');
        Http::fake();
        $this->assertFalse(app(AporteIngenierilClient::class)->analysis($this->payload())->available);
        Http::assertNothingSent();
    }
}
