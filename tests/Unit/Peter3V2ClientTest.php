<?php

namespace Tests\Unit;

use App\Services\AporteIngenieril\AporteIngenierilClient;
use App\Services\AporteIngenieril\DTO\Peter3V2Contract;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class Peter3V2ClientTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config()->set('services.peter3.enabled', true);
        config()->set('services.peter3.url', 'http://127.0.0.1:8001');
        config()->set('services.peter3.key', 'test-only-key');
    }

    private function input(): array
    {
        return ['instrument_version' => 'test-version', 'responses' => array_map(fn ($id) => ['item_id' => $id, 'value' => 3], range(1, 30))];
    }

    public function test_scoring_preserves_public_scale_and_trace_headers(): void
    {
        Http::fake(['*' => Http::response(['schema_version' => '2.0', 'instrument_version' => 'test-version', 'scores' => array_fill_keys(str_split('RIASEC'), 10), 'top_codes' => str_split('RIASEC'), 'holland_code' => 'RIA', 'limitations' => [], 'trace_id' => 'fixture-trace'], 200, ['X-Trace-Id' => 'fixture-trace'])]);
        $response = app(AporteIngenierilClient::class)->riasecScore($this->input());
        $this->assertTrue($response->available);
        $this->assertSame('fixture-trace', $response->traceId);
        Http::assertSent(fn ($request) => $request['responses'][0]['value'] === 3 && $request->hasHeader('X-SAVP-AI-Key', 'test-only-key'));
    }

    #[DataProvider('errors')]
    public function test_http_errors_keep_trace_and_never_expose_response_details(int $status): void
    {
        Http::fake(['*' => Http::response(['error' => ['trace_id' => 'error-trace', 'message' => 'sensitive traceback']], $status, ['X-Trace-Id' => 'error-trace'])]);
        $response = app(AporteIngenierilClient::class)->riasecScore($this->input());
        $this->assertFalse($response->available);
        $this->assertSame($status, $response->status);
        $this->assertSame('error-trace', $response->traceId);
        $this->assertSame([], $response->data);
        $this->assertStringNotContainsString('traceback', $response->message);
        Http::assertSentCount(1);
    }

    public static function errors(): array
    {
        return [[401], [403], [422], [500], [503]];
    }

    public function test_duplicate_ids_and_false_public_values_are_rejected_before_network(): void
    {
        Http::fake();
        $input = $this->input();
        $input['responses'][29]['item_id'] = 1;
        $this->assertFalse(app(AporteIngenierilClient::class)->riasecScore($input)->available);
        $input = $this->input();
        $input['responses'][0]['value'] = true;
        $this->assertFalse(app(AporteIngenierilClient::class)->riasecScore($input)->available);
        Http::assertNothingSent();
    }

    public function test_v2_rejects_identity_fields_and_keeps_scale_conversion_in_python(): void
    {
        $valid = ['schema_version' => '2.0', 'student_id' => 'EST_PRIVATE', 'riasec_public' => $this->input(), 'academic' => ['records' => [['subject' => 'Matemática', 'score' => 0, 'scale_min' => 0, 'scale_max' => 100]]]];
        $this->assertTrue(Peter3V2Contract::validRequest('analysis_v2', $valid));
        $this->assertFalse(Peter3V2Contract::validRequest('analysis_v2', $valid + ['email' => 'private@example.test']));
        $this->assertSame(3, $valid['riasec_public']['responses'][0]['value']);
    }

    public function test_invalid_json_and_mismatched_trace_are_unavailable(): void
    {
        Http::fake(['*' => Http::response('not-json', 200)]);
        $this->assertFalse(app(AporteIngenierilClient::class)->riasecScore($this->input())->available);
        Http::fake(['*' => Http::response(['schema_version'=>'2.0','instrument_version'=>'test-version','scores'=>array_fill_keys(str_split('RIASEC'),10),'top_codes'=>str_split('RIASEC'),'holland_code'=>'RIA','limitations'=>[],'trace_id'=>'body-trace'],200,['X-Trace-Id'=>'different-header-trace'])]);
        $this->assertFalse(app(AporteIngenierilClient::class)->riasecScore($this->input())->available);
    }
}
