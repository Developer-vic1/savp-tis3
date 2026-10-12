<?php

namespace Tests\Unit;

use App\Services\AporteIngenieril\AporteIngenierilClient;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ClienteGestionConocimientoTest extends TestCase
{
    public function test_actualizacion_solo_acepta_identificador_y_valida_respuesta(): void
    {
        Http::preventStrayRequests();
        Http::fake(['aporte.test/*' => Http::sequence()->push([
            'schema_version' => '1.0', 'trace_id' => 'check-trace',
            'source_id' => 'BO-UPB-SIS', 'checked_at' => '2026-10-11T10:00:00Z',
            'status' => 'VERIFICADA', 'fingerprint' => str_repeat('a', 64), 'message' => 'Revisada.',
        ])->push([
            'schema_version' => '1.0', 'trace_id' => 'check-trace', 'source_id' => 'BO-UPB-SIS',
            'checked_at' => '2026-10-11T10:00:00Z', 'status' => 'VERIFICADA', 'fingerprint' => null, 'message' => 'Revisada.',
        ])]);
        $cliente = app(AporteIngenierilClient::class);
        $this->assertFalse($cliente->revisarActualizacionFuente('https://localhost/privado')->available);
        Http::assertNothingSent();
        $this->assertTrue($cliente->revisarActualizacionFuente('BO-UPB-SIS')->available);
        Http::assertSent(fn ($r) => $r->data() === ['source_id' => 'BO-UPB-SIS'] && $r->url() === 'https://aporte.test/api/v1/knowledge/governance/check-update');
        $this->assertFalse($cliente->revisarActualizacionFuente('BO-UPB-SIS')->available);
    }

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

    public function test_extraccion_aprobada_solo_consulta_servicio_interno_y_marca_vectores_no_generados(): void
    {
        $cuerpo = $this->extractionPayload();
        Http::preventStrayRequests();
        Http::fake(['aporte.test/*' => Http::response($cuerpo)]);

        $resultado = app(AporteIngenierilClient::class)->extractKnowledgeSource('KGI-ABCDEF123456');

        $this->assertTrue($resultado->available);
        $this->assertSame('Álgebra', $resultado->data['facts'][0]['value']);
        Http::assertSentCount(1);
        Http::assertSent(fn ($request) => $request->url() === 'https://aporte.test/api/v1/knowledge/governance/proposals/KGI-ABCDEF123456/extract'
            && $request->method() === 'POST' && $request->data() === [] && $request->hasHeader('X-SAVP-AI-Key'));
    }

    public function test_extraccion_acepta_vectores_generados_en_staging_para_todos_los_chunks_sin_publicarlos(): void
    {
        $cuerpo = $this->extractionPayload();
        $cuerpo['metrics']['chunks'] = 12;
        $cuerpo['embeddings'] = [
            'status' => 'GENERADOS_PENDIENTES_PUBLICACION',
            'model' => 'sentence-transformers/paraphrase-multilingual-MiniLM-L12-v2',
            'vectors' => 12,
            'reason' => 'Vectores verificados en staging; publicación pendiente de revisión.',
        ];
        Http::preventStrayRequests();
        Http::fake(['aporte.test/*' => Http::response($cuerpo)]);

        $resultado = app(AporteIngenierilClient::class)->extractKnowledgeSource('KGI-ABCDEF123456');

        $this->assertTrue($resultado->available);
        $this->assertSame('EXTRAIDA_PENDIENTE_REVISION', $resultado->data['status']);
        $this->assertSame($cuerpo['embeddings'], $resultado->data['embeddings']);
        Http::assertSentCount(1);
    }

    public function test_consulta_extraccion_guardada_por_get_con_contrato_validado_sin_ordenar_extraccion(): void
    {
        $cuerpo = $this->extractionPayload();
        $cuerpo['embeddings'] = [
            'status' => 'GENERADOS_PENDIENTES_PUBLICACION', 'model' => 'modelo-local',
            'vectors' => 1, 'reason' => 'Vectores verificados en staging.',
        ];
        Http::preventStrayRequests();
        Http::fake(['aporte.test/*' => Http::response($cuerpo)]);

        $resultado = app(AporteIngenierilClient::class)->readKnowledgeExtraction('KGI-ABCDEF123456');

        $this->assertTrue($resultado->available);
        $this->assertSame($cuerpo['embeddings'], $resultado->data['embeddings']);
        Http::assertSentCount(1);
        Http::assertSent(fn ($request) => $request->url() === 'https://aporte.test/api/v1/knowledge/governance/proposals/KGI-ABCDEF123456/extract'
            && $request->method() === 'GET' && $request->data() === [] && $request->hasHeader('X-SAVP-AI-Key'));
    }

    public function test_consulta_guardada_rechaza_identificador_inseguro_y_vectores_incoherentes(): void
    {
        $cuerpo = $this->extractionPayload();
        $cuerpo['embeddings'] = [
            'status' => 'GENERADOS_PENDIENTES_PUBLICACION', 'model' => 'modelo-local',
            'vectors' => 2, 'reason' => 'Recuento incorrecto.',
        ];
        Http::preventStrayRequests();
        Http::fake(['aporte.test/*' => Http::response($cuerpo)]);

        $cliente = app(AporteIngenierilClient::class);
        $this->assertFalse($cliente->readKnowledgeExtraction('../otra')->available);
        Http::assertNothingSent();
        $this->assertFalse($cliente->readKnowledgeExtraction('KGI-ABCDEF123456')->available);
        Http::assertSentCount(1);
    }

    #[DataProvider('embeddingsIncoherentes')]
    public function test_extraccion_rechaza_estados_y_campos_incoherentes_de_vectores(array $embeddings, string $estado = 'EXTRAIDA_PENDIENTE_REVISION'): void
    {
        $cuerpo = array_replace($this->extractionPayload(), ['embeddings' => $embeddings, 'status' => $estado]);
        Http::preventStrayRequests();
        Http::fake(['aporte.test/*' => Http::response($cuerpo)]);

        $this->assertFalse(app(AporteIngenierilClient::class)->extractKnowledgeSource('KGI-ABCDEF123456')->available);
        Http::assertSentCount(1);
    }

    public static function embeddingsIncoherentes(): array
    {
        $generados = ['status' => 'GENERADOS_PENDIENTES_PUBLICACION', 'model' => 'modelo-local', 'vectors' => 1, 'reason' => 'Pendiente de publicación.'];
        $sinGenerar = ['status' => 'NO_GENERADOS', 'model' => null, 'vectors' => 0, 'reason' => 'Revisión pendiente.'];

        return [
            'generados sin modelo' => [array_replace($generados, ['model' => null])],
            'modelo vacío' => [array_replace($generados, ['model' => ''])],
            'modelo no textual' => [array_replace($generados, ['model' => 42])],
            'generados sin vectores' => [array_replace($generados, ['vectors' => 0])],
            'cardinalidad distinta de chunks' => [array_replace($generados, ['vectors' => 2])],
            'cardinalidad textual' => [array_replace($generados, ['vectors' => '1'])],
            'cardinalidad booleana' => [array_replace($generados, ['vectors' => true])],
            'cardinalidad negativa' => [array_replace($generados, ['vectors' => -1])],
            'estado publicado' => [array_replace($generados, ['status' => 'GENERADOS'])],
            'generados con extracción indisponible' => [$generados, 'NO_DISPONIBLE'],
            'sin generar con modelo' => [array_replace($sinGenerar, ['model' => 'modelo-local'])],
            'sin generar con vectores' => [array_replace($sinGenerar, ['vectors' => 1])],
            'cero textual' => [array_replace($sinGenerar, ['vectors' => '0'])],
            'indisponible con modelo' => [array_replace($sinGenerar, ['model' => 'modelo-local']), 'NO_DISPONIBLE'],
            'razón ausente' => [array_diff_key($generados, ['reason' => true])],
            'razón vacía' => [array_replace($generados, ['reason' => ''])],
            'razón no textual' => [array_replace($generados, ['reason' => true])],
            'modelo ausente' => [array_diff_key($sinGenerar, ['model' => true])],
            'clave extra' => [$generados + ['dimension' => 384]],
        ];
    }

    public function test_extraccion_rechaza_identificador_y_contrato_que_finge_embeddings(): void
    {
        Http::fake(['aporte.test/*' => Http::response([
            'schema_version' => '1.0', 'trace_id' => 'extract-false',
            'proposal_id' => 'KGI-ABCDEF123456', 'status' => 'EXTRAIDA_PENDIENTE_REVISION',
            'source_url' => 'https://www.upb.edu/sistemas', 'document_hash' => 'sha256:'.str_repeat('a', 64),
            'metrics' => ['pages' => 1, 'segments' => 1, 'chunks' => 0, 'facts' => 0],
            'facts' => [], 'chunks' => [],
            'embeddings' => ['status' => 'GENERADOS', 'model' => 'inventado', 'vectors' => 10, 'reason' => ''],
            'warnings' => [],
        ])]);
        $this->assertFalse(app(AporteIngenierilClient::class)->extractKnowledgeSource('../otra')->available);
        Http::assertNothingSent();
        $this->assertFalse(app(AporteIngenierilClient::class)->extractKnowledgeSource('KGI-ABCDEF123456')->available);
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

    public function test_documento_publico_legible_puede_usarse_aunque_universidad_bloquee_iframe(): void
    {
        $preview = $this->previewPayload();
        Http::preventStrayRequests();
        Http::fake(['aporte.test/*' => Http::response($preview)]);

        $result = app(AporteIngenierilClient::class)->inspectKnowledgeSource(['url' => $preview['requested_url']]);

        $this->assertTrue($result->available);
        $this->assertTrue($result->data['can_use']);
        $this->assertFalse($result->data['embedding']['allowed']);
        Http::assertSentCount(1);
        Http::assertSent(fn ($request) => str_starts_with($request->url(), 'https://aporte.test/'));
    }

    public function test_portal_estudiantil_y_autenticacion_reconocen_universidad_sin_habilitar_ficha_academica(): void
    {
        $preview = [];
        Http::fake(['aporte.test/*' => function () use (&$preview) {
            return Http::response($preview);
        }]);
        foreach (['STUDENT_PORTAL', 'AUTHENTICATION'] as $kind) {
            $preview = array_replace($this->previewPayload(), ['requested_url' => 'https://academico.udabol.edu.bo/estudiante/',
                'document_kind' => $kind, 'can_use' => false, 'excerpt' => '', 'reading_fragments' => [],
                'documents' => [], 'suggested_fields' => [],
                'assessment' => ['status' => 'ACEPTADA', 'recognized_institution' => 'Universidad de Aquino Bolivia'],
                'embedding' => ['allowed' => null, 'reason' => null]]);
            $result = app(AporteIngenierilClient::class)->inspectKnowledgeSource(['url' => $preview['requested_url']]);

            $this->assertTrue($result->available, $kind);
            $this->assertFalse($result->data['can_use']);
            $this->assertSame('ACEPTADA', $result->data['assessment']['status']);
            $this->assertSame($kind, $result->data['document_kind']);
        }
    }

    public function test_cliente_rechaza_contrato_incoherente_o_manipulado_de_portal_y_embedding(): void
    {
        $publico = $this->previewPayload();
        $portal = array_replace($publico, ['document_kind' => 'STUDENT_PORTAL', 'can_use' => false, 'excerpt' => '']);
        $casos = [
            array_replace($publico, ['document_kind' => 'INVENTED']),
            array_replace($publico, ['embedding' => ['allowed' => 'false', 'reason' => null]]),
            array_replace($publico, ['embedding' => ['allowed' => 0, 'reason' => null]]),
            array_replace($publico, ['embedding' => ['allowed' => false]]),
            array_replace($publico, ['embedding' => ['allowed' => false, 'reason' => null, 'script' => 'arbitrario']]),
            array_replace($portal, ['can_use' => true]),
            array_replace($portal, ['excerpt' => 'Calificaciones privadas']),
            array_replace($portal, ['suggested_fields' => ['title' => 'Ficha académica arbitraria']]),
            array_replace($portal, ['documents' => [['url' => 'https://www.upb.edu/malla.pdf', 'title' => 'Malla curricular', 'kind' => 'malla']]]),
            array_replace($portal, ['academic_pages' => [['url' => 'https://www.upb.edu/ingenieria/sistemas/', 'title' => 'Ingeniería de Sistemas', 'kind' => 'carrera']]]),
            array_replace($portal, ['reading_fragments' => [['id' => str_repeat('a', 24), 'text' => 'Historial privado',
                'url' => $publico['requested_url'], 'document_hash' => $publico['document_hash']]]]),
        ];
        $preview = [];
        Http::fake(['aporte.test/*' => function () use (&$preview) {
            return Http::response($preview);
        }]);
        foreach ($casos as $indice => $preview) {
            $this->assertFalse(app(AporteIngenierilClient::class)->inspectKnowledgeSource(['url' => $preview['requested_url']])->available, (string) $indice);
        }
    }

    public function test_documento_desconocido_no_confunde_clasificacion_con_registro_universitario(): void
    {
        $preview = array_replace($this->previewPayload(), ['document_kind' => 'UNKNOWN', 'can_use' => false,
            'status' => 'REQUIERE_REVISION', 'requested_url' => 'https://universidad-no-registrada.edu.bo/',
            'assessment' => ['status' => 'REQUIERE_REVISION'], 'embedding' => ['allowed' => null, 'reason' => null]]);
        Http::fake(['aporte.test/*' => Http::response($preview)]);
        $result = app(AporteIngenierilClient::class)->inspectKnowledgeSource(['url' => $preview['requested_url']]);

        $this->assertTrue($result->available);
        $this->assertFalse($result->data['can_use']);
        $this->assertSame('REQUIERE_REVISION', $result->data['assessment']['status']);
    }

    public function test_paginas_academicas_admiten_enlaces_publicados_de_la_misma_universidad_sin_contactarlos(): void
    {
        $paginas = [
            ['url' => 'https://carreras.upb.edu/sistemas/', 'title' => 'Ingeniería de Sistemas', 'kind' => 'carrera'],
            ['url' => 'https://www.upb.edu/sistemas/plan?gestion=2026', 'title' => 'Plan de estudios', 'kind' => 'plan'],
            ['url' => 'https://www.upb.edu/normativa/', 'title' => 'Reglamento académico', 'kind' => 'reglamento'],
        ];
        Http::preventStrayRequests();
        Http::fake(['aporte.test/*' => Http::response($this->previewPayload() + ['academic_pages' => $paginas])]);
        $resultado = app(AporteIngenierilClient::class)->inspectKnowledgeSource(['url' => $this->previewPayload()['requested_url']]);

        $this->assertTrue($resultado->available);
        $this->assertSame($paginas, $resultado->data['academic_pages']);
        Http::assertSentCount(1);
        Http::assertSent(fn ($request) => $request->url() === 'https://aporte.test/api/v1/knowledge/governance/preview');
    }

    public function test_paginas_academicas_rechazan_contrato_invalido_y_destinos_ajenos_o_pdf(): void
    {
        $pagina = ['url' => 'https://www.upb.edu/sistemas/', 'title' => 'Ingeniería de Sistemas', 'kind' => 'carrera'];
        $listas = [null, ['pagina' => $pagina], array_fill(0, 7, $pagina),
            [array_replace($pagina, ['url' => 'http://www.upb.edu/sistemas/'])],
            [array_replace($pagina, ['url' => 'https://www.udabol.edu.bo/sistemas/'])],
            [array_replace($pagina, ['url' => 'https://universidad-no-registrada.edu.bo/sistemas/'])],
            [array_replace($pagina, ['url' => 'https://user:password@www.upb.edu/sistemas/'])],
            [array_replace($pagina, ['url' => 'https://www.upb.edu:8443/sistemas/'])],
            [array_replace($pagina, ['url' => 'https://127.0.0.1/sistemas/'])],
            [array_replace($pagina, ['url' => 'https://www.upb.edu/malla.PDF?gestion=2026'])],
            [array_replace($pagina, ['title' => 'ab'])], [array_replace($pagina, ['title' => str_repeat('a', 241)])],
            [array_replace($pagina, ['kind' => 'portal'])], [$pagina + ['can_use' => true]],
            [array_diff_key($pagina, ['url' => true])],
        ];
        $preview = [];
        Http::fake(['aporte.test/*' => function () use (&$preview) {
            return Http::response($preview);
        }]);
        foreach ($listas as $indice => $paginas) {
            $preview = $this->previewPayload() + ['academic_pages' => $paginas];
            $this->assertFalse(app(AporteIngenierilClient::class)->inspectKnowledgeSource(['url' => $preview['requested_url']])->available, (string) $indice);
        }
    }

    private function extractionPayload(): array
    {
        return [
            'schema_version' => '1.0', 'trace_id' => 'extract-trace',
            'proposal_id' => 'KGI-ABCDEF123456', 'status' => 'EXTRAIDA_PENDIENTE_REVISION',
            'source_url' => 'https://www.upb.edu/sistemas',
            'document_hash' => 'sha256:'.str_repeat('a', 64),
            'metrics' => ['pages' => 1, 'segments' => 2, 'chunks' => 1, 'facts' => 1],
            'facts' => [[
                'kind' => 'MATERIA', 'value' => 'Álgebra', 'evidence' => 'Primer semestre: Álgebra',
                'page' => 1, 'source_url' => 'https://www.upb.edu/sistemas',
            ]],
            'chunks' => [['chunk_id' => 'fuente-1', 'text' => 'Primer semestre: Álgebra', 'page' => 1]],
            'embeddings' => ['status' => 'NO_GENERADOS', 'model' => null, 'vectors' => 0, 'reason' => 'Revisión pendiente.'],
            'warnings' => [],
        ];
    }

    private function previewPayload(): array
    {
        return ['schema_version' => '1.0', 'trace_id' => 'preview-classification',
            'requested_url' => 'https://www.upb.edu/ingenieria/sistemas/', 'status' => 'VERIFICADA', 'can_use' => true,
            'title' => 'Ingeniería de Sistemas', 'message' => 'Texto público comprobado.', 'excerpt' => 'Materias por semestre.',
            'assessment' => ['status' => 'ACEPTADA'], 'checked_at' => now()->toIso8601String(),
            'suggested_fields' => [], 'warnings' => [], 'duplicate' => null, 'reachable' => true,
            'http_status' => 200, 'document_hash' => 'sha256:'.str_repeat('a', 64),
            'document_kind' => 'PUBLIC_DOCUMENT',
            'embedding' => ['allowed' => false, 'reason' => 'La universidad restringe la vista integrada.']];
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
