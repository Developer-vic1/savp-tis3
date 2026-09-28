<?php

namespace Tests\Unit;

use App\Services\AporteIngenieril\AporteIngenierilClient;
use App\Services\AporteIngenieril\DTO\AcademicAnalysisData;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AporteIngenierilClientTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config()->set('services.peter3.url', 'https://peter3.test');
        config()->set('services.peter3.version', 'v1');
    }

    public function test_it_returns_data_when_service_is_available(): void
    {
        Http::fake(['peter3.test/*' => Http::response(['estado' => 'ok'])]);

        $result = app(AporteIngenierilClient::class)->analysis(['student_id' => 'EST_0001']);

        $this->assertTrue($result->available);
        $this->assertSame('ok', $result->data['estado']);
    }

    public function test_it_uses_safe_fallback_for_503_and_invalid_json(): void
    {
        Http::fakeSequence()->push([], 503)->push('no-json', 200);
        $client = app(AporteIngenierilClient::class);

        $this->assertFalse($client->analysis([])->available);
        $this->assertFalse($client->analysis([])->available);
    }

    public function test_it_uses_safe_fallback_for_validation_error(): void
    {
        Http::fake(['peter3.test/*' => Http::response(['detail' => 'invalid'], 422)]);

        $this->assertFalse(app(AporteIngenierilClient::class)->analysis([])->available);
    }

    public function test_it_uses_safe_fallback_for_timeout(): void
    {
        Http::fake(fn () => throw new ConnectionException('operation timed out'));

        $this->assertFalse(app(AporteIngenierilClient::class)->analysis([])->available);
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
    }

    public function test_empty_response_is_not_presented_as_evidence(): void
    {
        Http::fake(['peter3.test/*' => Http::response([])]);
        $this->assertFalse(app(AporteIngenierilClient::class)->analysis([])->available);
    }

    public function test_endpoint_is_configurable_without_changing_the_contract(): void
    {
        config()->set('services.peter3.paths.analysis', '/analysis');
        Http::fake(['peter3.test/analysis' => Http::response(['status' => 'partial', 'score' => null, 'count' => 0])]);
        $result = app(AporteIngenierilClient::class)->analysis([]);
        $this->assertTrue($result->available);
        $this->assertNull($result->data['score']);
        $this->assertSame(0, $result->data['count']);
        Http::assertSent(fn ($request) => $request->url() === 'https://peter3.test/analysis');
    }
}
