<?php

namespace Tests\Unit;

use App\Services\AporteIngenieril\AporteIngenierilClient;
use App\Services\AporteIngenieril\DTO\ContratoAporteIngenierilV2;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ClienteAporteIngenierilV2Test extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config()->set('services.aporte_ingenieril.enabled', true);
        config()->set('services.aporte_ingenieril.url', 'http://127.0.0.1:8001');
        config()->set('services.aporte_ingenieril.key', 'test-only-internal-api-key-32-characters');
    }

    private function input(): array
    {
        return ['instrument_version' => 'test-version', 'responses' => array_map(fn ($id) => ['item_id' => $id, 'value' => 3], range(1, 30))];
    }

    public function test_puntuacion_preserva_escala_publica_y_cabeceras_de_trazabilidad(): void
    {
        Http::fake(['*' => Http::response(['schema_version' => '2.0', 'instrument_version' => 'test-version', 'scores' => array_fill_keys(str_split('RIASEC'), 10), 'top_codes' => str_split('RIASEC'), 'holland_code' => 'RIA', 'limitations' => [], 'trace_id' => 'fixture-trace'], 200, ['X-Trace-Id' => 'fixture-trace'])]);
        $response = app(AporteIngenierilClient::class)->riasecScore($this->input());
        $this->assertTrue($response->available);
        $this->assertSame('fixture-trace', $response->traceId);
        Http::assertSent(fn ($request) => $request['responses'][0]['value'] === 3 && $request->hasHeader('X-SAVP-AI-Key', 'test-only-internal-api-key-32-characters'));
    }

    #[DataProvider('errors')]
    public function test_errores_http_conservan_trazabilidad_sin_exponer_detalles(int $status): void
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

    public function test_identificadores_duplicados_y_valores_publicos_falsos_se_rechazan_antes_de_red(): void
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

    public function test_v2_rechaza_identidad_y_conserva_conversion_de_escala_en_python(): void
    {
        $valid = ['schema_version' => '2.0', 'student_id' => 'EST_PRIVATE', 'riasec_public' => $this->input(), 'academic' => ['records' => [['subject' => 'Matemática', 'score' => 0, 'scale_min' => 0, 'scale_max' => 100]]]];
        $this->assertTrue(ContratoAporteIngenierilV2::solicitudValida('analysis_v2', $valid));
        $this->assertFalse(ContratoAporteIngenierilV2::solicitudValida('analysis_v2', $valid + ['email' => 'private@example.test']));
        $this->assertSame(3, $valid['riasec_public']['responses'][0]['value']);
    }

    public function test_informacion_externa_debe_excluirse_explicitamente_de_recomendacion(): void
    {
        $response = [
            'schema_version' => '2.0',
            'trace_id' => 'fixture-trace',
            'student_ref' => 'fixture-student',
            'analysis_status' => 'COMPLETE',
            'student_snapshot' => collect(['academic', 'attendance', 'learning_activity', 'historical', 'technical', 'declared_interest'])
                ->mapWithKeys(fn ($component) => [$component.'_evidence' => ['status' => 'AVAILABLE']])
                ->all(),
            'career_evidence_profiles' => [],
            'informational_external_careers' => [[
                'career_id' => 'BO-EMI-LP-ING-SISTEMAS',
                'university' => 'Escuela Militar de Ingeniería, La Paz',
                'evidence_layer' => 'FUENTE_OFICIAL_EXTERNA',
                'recommendation_eligible' => false,
                'source_ids' => ['BO-EMI-LP-SIS-PROFILE-EXTERNAL-2026'],
                'sources' => [['source_id' => 'BO-EMI-LP-SIS-PROFILE-EXTERNAL-2026']],
            ]],
            'traceability' => ['input_hash' => str_repeat('a', 64)],
            'sources_used' => [],
            'limitations' => [],
            'warnings' => [],
        ];

        $this->assertTrue(ContratoAporteIngenierilV2::respuestaValida('analysis_v2', $response));

        $response['informational_external_careers'][0]['recommendation_eligible'] = true;
        $this->assertFalse(ContratoAporteIngenierilV2::respuestaValida('analysis_v2', $response));
    }

    public function test_json_invalido_y_trazabilidad_inconsistente_no_estan_disponibles(): void
    {
        Http::fake(['*' => Http::response('not-json', 200)]);
        $this->assertFalse(app(AporteIngenierilClient::class)->riasecScore($this->input())->available);
        Http::fake(['*' => Http::response(['schema_version' => '2.0', 'instrument_version' => 'test-version', 'scores' => array_fill_keys(str_split('RIASEC'), 10), 'top_codes' => str_split('RIASEC'), 'holland_code' => 'RIA', 'limitations' => [], 'trace_id' => 'body-trace'], 200, ['X-Trace-Id' => 'different-header-trace'])]);
        $this->assertFalse(app(AporteIngenierilClient::class)->riasecScore($this->input())->available);
    }
}
