<?php

namespace Tests\Unit;

use App\Services\AporteIngenieril\AporteIngenierilClient;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ClienteGestionConocimientoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config()->set('services.aporte_ingenieril.url', 'https://aporte.test');
        config()->set('services.aporte_ingenieril.enabled', true);
        config()->set('services.aporte_ingenieril.key', 'test-only-internal-api-key-32-characters');
        config()->set('services.aporte_ingenieril.allowed_hosts', ['aporte.test']);
    }

    public function test_mantiene_gestion_de_fuentes_en_servicio_interno(): void
    {
        Http::fake(['aporte.test/*' => Http::response([
            'schema_version' => '1.0',
            'trace_id' => 'governance-trace',
            'source_count' => 16,
            'sources' => [],
            'trusted_universities' => [],
            'proposals' => [],
            'workflow' => [],
        ])]);

        $result = app(AporteIngenierilClient::class)->knowledgeGovernance();

        $this->assertTrue($result->available);
        $this->assertSame(16, $result->data['source_count']);
        Http::assertSent(fn ($request) => $request->url() === 'https://aporte.test/api/v1/knowledge/governance'
            && $request->hasHeader('X-SAVP-AI-Key'));
    }

    public function test_vista_previa_nunca_contacta_universidad_desde_laravel(): void
    {
        Http::fake(['aporte.test/*' => Http::response([
            'schema_version' => '1.0', 'trace_id' => 'preview-trace',
            'requested_url' => 'https://www.upb.edu/', 'status' => 'DUPLICADA',
            'can_use' => false, 'title' => 'UPB | Inicio', 'message' => 'Ya existe en estudio.',
            'excerpt' => '', 'assessment' => ['status' => 'ACEPTADA'], 'checked_at' => now()->toIso8601String(),
            'suggested_fields' => [], 'warnings' => [], 'duplicate' => null,
        ])]);
        $result = app(AporteIngenierilClient::class)->inspectKnowledgeSource(['url' => 'https://www.upb.edu/']);
        $this->assertTrue($result->available);
        Http::assertSentCount(1);
        Http::assertSent(fn ($request) => $request->url() === 'https://aporte.test/api/v1/knowledge/governance/preview'
            && $request->hasHeader('X-SAVP-AI-Key'));
    }

    public function test_vista_previa_rechaza_entrada_insegura_y_campos_forzados(): void
    {
        Http::fake();
        foreach ([['url' => 'https://user:secret@upb.edu/'], ['url' => 'https://127.0.0.1/'],
            ['url' => 'https://www.upb.edu/', 'can_use' => true]] as $payload) {
            $this->assertFalse(app(AporteIngenierilClient::class)->inspectKnowledgeSource($payload)->available);
        }
        Http::assertNothingSent();
    }

    public function test_analiza_fuente_completa_antes_del_envio(): void
    {
        Http::fake(['aporte.test/*' => Http::response([
            'schema_version' => '1.0', 'trace_id' => 'analysis-trace',
            'assessment' => ['host' => 'www.upb.edu', 'status' => 'ACEPTADA', 'is_bolivian_university' => true, 'recognized_institution' => 'Universidad Privada Boliviana', 'message' => 'Dominio válido.'],
            'readiness' => 'LISTA_PARA_PROPONER', 'risk_level' => 'BAJO', 'can_submit' => true,
            'recognized_institution' => 'Universidad Privada Boliviana', 'checks' => [], 'suggestions' => [],
        ])]);

        $result = app(AporteIngenierilClient::class)->analyzeKnowledgeSource($this->sourcePayload());

        $this->assertTrue($result->available);
        $this->assertTrue($result->data['can_submit']);
    }

    public function test_rechaza_propuesta_malformada_antes_del_acceso_a_red(): void
    {
        Http::fake();

        $result = app(AporteIngenierilClient::class)->submitKnowledgeSource([
            ...$this->sourcePayload(),
            'submitted_by_role' => 'Estudiante',
        ]);

        $this->assertFalse($result->available);
        Http::assertNothingSent();
    }

    private function sourcePayload(): array
    {
        return [
            'title' => 'Plan de estudios de Ingeniería de Sistemas',
            'declared_institution' => 'Universidad Privada Boliviana',
            'url' => 'https://www.upb.edu/documentos/sistemas-2027.pdf',
            'source_type' => 'OFFICIAL_CURRICULUM_PDF',
            'scope' => 'Malla curricular de Ingeniería de Sistemas.',
            'publication_date' => '2027',
            'version' => 'Gestión 2027',
            'campus' => 'La Paz',
            'city' => 'La Paz',
            'justification' => 'Amplía la comparación de materias y preparación académica.',
            'limitations' => [],
        ];
    }
}
