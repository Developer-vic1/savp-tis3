<?php

namespace Tests\Feature;

use App\Models\Oficial\Sistema\User;
use App\Services\AporteIngenieril\AporteIngenierilClient;
use App\Services\AporteIngenieril\DTO\AporteResponse;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AutorizacionConocimientoUniversitarioTest extends TestCase
{
    public function test_actualizaciones_exigen_administrador_y_cadencia_validada(): void
    {
        $this->getJson('/conocimiento/actualizaciones')->assertUnauthorized();
        $this->postJson('/conocimiento/actualizaciones/configurar', [])->assertUnauthorized();
        $this->actingAs($this->actor('Director'))->getJson('/conocimiento/actualizaciones')->assertForbidden();
        $this->postJson('/conocimiento/actualizaciones/revisar', ['source_id' => 'BO-UPB-SIS'])->assertForbidden();
        $this->actingAs($this->actor('Administrador'))->postJson('/conocimiento/actualizaciones/configurar', [
            'source_id' => 'BO-UPB-SIS', 'cadence' => 'cada minuto',
        ])->assertUnprocessable()->assertJsonValidationErrors('cadence');
        $this->postJson('/conocimiento/actualizaciones/revisar', ['source_id' => '../../privado'])->assertUnprocessable();
    }

    public function test_invitado_no_accede_a_operaciones_de_gestion_de_conocimiento(): void
    {
        $this->getJson('/conocimiento/fuentes')->assertUnauthorized();
        $this->getJson('/conocimiento/conexion')->assertUnauthorized();
        $this->postJson('/conocimiento/fuentes/analizar', $this->sourcePayload())->assertUnauthorized();
        $this->postJson('/conocimiento/fuentes/comprobar-url', ['url' => $this->sourcePayload()['url']])->assertUnauthorized();
        $this->postJson('/conocimiento/fuentes', $this->sourcePayload())->assertUnauthorized();
        $this->postJson('/conocimiento/tutor/probar', ['question' => 'Hola'])->assertUnauthorized();
        $this->postJson('/conocimiento/fuentes/KGI-ABCDEF123456/revision', [])->assertUnauthorized();
        $this->postJson('/conocimiento/fuentes/KGI-ABCDEF123456/extraccion', [])->assertUnauthorized();
        $this->getJson('/conocimiento/fuentes/KGI-ABCDEF123456/extraccion')->assertUnauthorized();
    }

    public function test_otros_actores_permanecen_bloqueados_aunque_tengan_permisos_forzados(): void
    {
        foreach (['Docente', 'Estudiante', 'Secretaria', 'Regente'] as $role) {
            $this->actingAs($this->actor($role));
            $this->getJson('/conocimiento/fuentes')->assertForbidden();
            $this->postJson('/conocimiento/fuentes/analizar', $this->sourcePayload())->assertForbidden();
            $this->postJson('/conocimiento/fuentes/comprobar-url', ['url' => $this->sourcePayload()['url']])->assertForbidden();
            $this->postJson('/conocimiento/fuentes', $this->sourcePayload())->assertForbidden();
            $this->postJson('/conocimiento/fuentes/KGI-ABCDEF123456/revision', [])->assertForbidden();
        }
    }

    #[DataProvider('invalidSourceFields')]
    public function test_servidor_rechaza_campos_invalidos_antes_de_contactar_el_servicio(string $field, string $value): void
    {
        $client = Mockery::mock(AporteIngenierilClient::class);
        $client->shouldNotReceive('analyzeKnowledgeSource');
        $client->shouldNotReceive('submitKnowledgeSource');
        $this->app->instance(AporteIngenierilClient::class, $client);
        $this->actingAs($this->actor('Director'));
        $payload = array_replace($this->sourcePayload(), [$field => $value]);
        $this->postJson('/conocimiento/fuentes/analizar', $payload)->assertUnprocessable()->assertJsonValidationErrors($field);
        $this->postJson('/conocimiento/fuentes', $payload + ['analysis_token' => str_repeat('a', 64)])
            ->assertUnprocessable()->assertJsonValidationErrors($field);
    }

    public static function invalidSourceFields(): array
    {
        return [
            ['publication_date', '2026-02-29'],
            ['publication_date', '2026-00'],
            ['publication_date', '0000'],
            ['limitations_text', implode("\n", array_fill(0, 11, 'No informa costos.'))],
            ['limitations_text', 'ab'],
            ['limitations_text', '0'],
            ['limitations_text', str_repeat('a', 501)],
            ['url', 'https://user:pass@www.upb.edu/'],
            ['url', 'https://www.upb.edu:8080/'],
            ['url', 'https://127.0.0.1/'],
            ['url', 'https://www.upb.edu/%0d%0aHost:localhost'],
            ['submitted_by_role', 'Administrador'],
        ];
    }

    public function test_analisis_valido_se_envia_una_vez_con_actor_asignado_por_servidor(): void
    {
        $client = Mockery::mock(AporteIngenierilClient::class);
        $this->mockVerifiedPreview($client);
        $client->shouldReceive('analyzeKnowledgeSource')->once()->andReturn(new AporteResponse(true, [
            'can_submit' => true,
        ]));
        $client->shouldReceive('submitKnowledgeSource')->once()->withArgs(function (array $payload): bool {
            return $payload['submitted_by_role'] === 'Director'
                && $payload['publication_date'] === null
                && $payload['limitations'] === ['No informa costos de matrícula.'];
        })->andReturn(new AporteResponse(true, [
            'accepted' => true, 'message' => 'Propuesta registrada; pendiente de revisión.',
        ]));
        $this->app->instance(AporteIngenierilClient::class, $client);
        $payload = $this->sourcePayload();
        unset($payload['publication_date']);
        $analysis = $this->actingAs($this->actor('Director'))->postJson('/conocimiento/fuentes/analizar', $payload);
        $analysis->assertOk()->assertHeader('Cache-Control', 'no-store, private');
        $submission = $payload + ['analysis_token' => $analysis->json('analysis_token')];
        $this->post('/conocimiento/fuentes', $submission)->assertRedirect('/conocimiento/fuentes')->assertSessionHas('status');
        $this->post('/conocimiento/fuentes', $submission)->assertSessionHasErrors('analysis_token');
    }

    public function test_solo_administrador_y_director_ven_la_gestion_de_fuentes(): void
    {
        $this->actingAs($this->actor('Administrador'))->get('/conocimiento/fuentes')
            ->assertOk()->assertViewIs('workspaces.conocimiento-universitario')
            ->assertSee('Analizar fuente')->assertSee('Ver casos de prueba');
        $this->actingAs($this->actor('Director'))->get('/conocimiento/fuentes')->assertOk();
        $this->actingAs($this->actor('Estudiante'))->get('/conocimiento/fuentes')->assertForbidden();
    }

    public function test_token_de_analisis_pertenece_al_formulario_analizado(): void
    {
        $client = Mockery::mock(AporteIngenierilClient::class);
        $this->mockVerifiedPreview($client);
        $client->shouldReceive('analyzeKnowledgeSource')->once()->andReturn(new AporteResponse(true, [
            'schema_version' => '1.0', 'trace_id' => 'trace-analysis',
            'assessment' => ['host' => 'www.upb.edu', 'status' => 'ACEPTADA', 'is_bolivian_university' => true, 'recognized_institution' => 'Universidad Privada Boliviana', 'message' => 'Dominio válido.'],
            'readiness' => 'LISTA_PARA_PROPONER', 'risk_level' => 'BAJO', 'can_submit' => true,
            'checks' => [], 'suggestions' => [],
        ]));
        $client->shouldNotReceive('submitKnowledgeSource');
        $this->app->instance(AporteIngenierilClient::class, $client);

        $payload = $this->sourcePayload();
        $analysis = $this->actingAs($this->actor('Director'))->postJson('/conocimiento/fuentes/analizar', $payload);
        $analysis->assertOk()->assertJson(['can_submit' => true]);
        $token = $analysis->json('analysis_token');

        $this->post('/conocimiento/fuentes', array_merge($payload, [
            'title' => 'Otro título después del análisis',
            'analysis_token' => $token,
        ]))->assertSessionHasErrors('analysis_token');
    }

    public function test_solo_administrador_revisa_una_propuesta_de_fuente(): void
    {
        $this->actingAs($this->actor('Director'))
            ->post('/conocimiento/fuentes/KGI-ABCDEF123456/revision', [
                'approved' => true,
                'review_note' => 'Debe bloquearse antes de llamar al servicio.',
            ])
            ->assertForbidden();
    }

    public function test_extraccion_exige_administrador_y_propuesta_aprobada(): void
    {
        $ruta = '/conocimiento/fuentes/KGI-ABCDEF123456/extraccion';
        $this->actingAs($this->actor('Director'))->postJson($ruta, [])->assertForbidden();
        $this->getJson($ruta)->assertForbidden();
        $this->actingAs($this->actor('Estudiante'))->postJson($ruta, [])->assertForbidden();
        $this->getJson($ruta)->assertForbidden();

        $cliente = Mockery::mock(AporteIngenierilClient::class);
        $cliente->shouldReceive('knowledgeGovernance')->twice()->andReturn(new AporteResponse(true, [
            'proposals' => [[
                'proposal_id' => 'KGI-ABCDEF123456', 'status' => 'PENDIENTE_REVISION',
                'url' => 'https://www.upb.edu/carreras/sistemas',
            ]],
        ]));
        $cliente->shouldNotReceive('extractKnowledgeSource');
        $cliente->shouldNotReceive('readKnowledgeExtraction');
        $this->app->instance(AporteIngenierilClient::class, $cliente);
        $this->actingAs($this->actor('Administrador'))->postJson($ruta, [])->assertStatus(409);
        $this->getJson($ruta)->assertStatus(409);
    }

    public function test_consulta_guardada_revalida_propuesta_y_lee_sin_iniciar_otra_extraccion(): void
    {
        $ruta = '/conocimiento/fuentes/KGI-ABCDEF123456/extraccion';
        $url = 'https://www.upb.edu/carreras/sistemas';
        $cliente = Mockery::mock(AporteIngenierilClient::class);
        $cliente->shouldReceive('knowledgeGovernance')->once()->andReturn(new AporteResponse(true, [
            'proposals' => [['proposal_id' => 'KGI-ABCDEF123456', 'status' => 'APROBADA_PENDIENTE_INGESTA', 'url' => $url]],
        ]));
        $cliente->shouldNotReceive('extractKnowledgeSource');
        $cliente->shouldReceive('readKnowledgeExtraction')->once()->with('KGI-ABCDEF123456')
            ->andReturn(new AporteResponse(true, [
                'proposal_id' => 'KGI-ABCDEF123456', 'source_url' => $url,
                'status' => 'EXTRAIDA_PENDIENTE_REVISION',
                'embeddings' => ['status' => 'GENERADOS_PENDIENTES_PUBLICACION', 'model' => 'modelo-local', 'vectors' => 1, 'reason' => 'Pendiente de publicación.'],
            ]));
        $this->app->instance(AporteIngenierilClient::class, $cliente);
        $this->actingAs($this->actor('Administrador'));
        $this->getJson($ruta.'?url=https://www.ucb.edu.bo/otra')->assertUnprocessable();
        $this->getJson($ruta)->assertOk()->assertHeader('Cache-Control', 'no-store, private')
            ->assertJsonPath('embeddings.status', 'GENERADOS_PENDIENTES_PUBLICACION');
    }

    public function test_extraccion_solo_acepta_la_fuente_de_la_propuesta_y_muestra_lectura_pendiente(): void
    {
        $ruta = '/conocimiento/fuentes/KGI-ABCDEF123456/extraccion';
        $url = 'https://www.upb.edu/carreras/sistemas';
        $cliente = Mockery::mock(AporteIngenierilClient::class);
        $cliente->shouldReceive('knowledgeGovernance')->once()->andReturn(new AporteResponse(true, [
            'proposals' => [[
                'proposal_id' => 'KGI-ABCDEF123456', 'status' => 'APROBADA_PENDIENTE_INGESTA', 'url' => $url,
            ]],
        ]));
        $cliente->shouldReceive('extractKnowledgeSource')->once()->with('KGI-ABCDEF123456')
            ->andReturn(new AporteResponse(true, [
                'proposal_id' => 'KGI-ABCDEF123456', 'source_url' => $url,
                'status' => 'EXTRAIDA_PENDIENTE_REVISION', 'facts' => [['kind' => 'MATERIA', 'value' => 'Álgebra']],
            ]));
        $this->app->instance(AporteIngenierilClient::class, $cliente);
        $this->actingAs($this->actor('Administrador'))->postJson($ruta, [])
            ->assertOk()->assertJsonPath('status', 'EXTRAIDA_PENDIENTE_REVISION')
            ->assertJsonPath('facts.0.value', 'Álgebra');
    }

    public function test_operaciones_de_fuentes_no_consumen_el_limite_de_tres_extracciones_por_minuto(): void
    {
        $propuestas = array_map(fn (int $numero): array => [
            'proposal_id' => 'KGI-ABCDEF12345'.$numero,
            'status' => 'APROBADA_PENDIENTE_INGESTA',
            'url' => $this->sourcePayload()['url'],
        ], range(1, 4));
        $cliente = Mockery::mock(AporteIngenierilClient::class);
        $cliente->shouldReceive('knowledgeGovernance')->times(7)
            ->andReturn(new AporteResponse(true, ['proposals' => $propuestas]));
        $cliente->shouldReceive('readKnowledgeExtraction')->twice()->with('KGI-ABCDEF123451')
            ->andReturn(new AporteResponse(true, [
                'proposal_id' => 'KGI-ABCDEF123451', 'source_url' => $this->sourcePayload()['url'],
                'status' => 'EXTRAIDA_PENDIENTE_REVISION',
            ]));
        $this->mockVerifiedPreview($cliente);
        $cliente->shouldReceive('analyzeKnowledgeSource')->once()
            ->andReturn(new AporteResponse(true, ['can_submit' => true]));
        $cliente->shouldReceive('reviewKnowledgeSource')->once()->with('KGI-ABCDEF123451', [
            'approved' => true,
            'review_note' => 'Documento oficial revisado.',
            'reviewed_by_role' => 'Administrador',
        ])->andReturn(new AporteResponse(true, ['message' => 'Propuesta aprobada.']));
        $cliente->shouldReceive('extractKnowledgeSource')->times(3)
            ->withArgs(fn (string $id): bool => in_array($id, array_column(array_slice($propuestas, 0, 3), 'proposal_id'), true))
            ->andReturnUsing(fn (string $id): AporteResponse => new AporteResponse(true, [
                'proposal_id' => $id,
                'source_url' => $this->sourcePayload()['url'],
                'status' => 'EXTRAIDA_PENDIENTE_REVISION',
            ]));
        $this->app->instance(AporteIngenierilClient::class, $cliente);
        $this->actingAs($this->actor('Administrador'));

        $this->get('/conocimiento/fuentes')->assertOk();
        $this->postJson('/conocimiento/fuentes/comprobar-url', ['url' => $this->sourcePayload()['url']])->assertOk();
        $this->postJson('/conocimiento/fuentes/analizar', $this->sourcePayload())->assertOk();
        $this->post('/conocimiento/fuentes/KGI-ABCDEF123451/revision', [
            'approved' => true, 'review_note' => 'Documento oficial revisado.',
        ])->assertRedirect('/conocimiento/fuentes');

        $this->getJson('/conocimiento/fuentes/KGI-ABCDEF123451/extraccion')
            ->assertOk()->assertHeader('X-RateLimit-Remaining', '29');

        foreach (array_slice($propuestas, 0, 3) as $indice => $propuesta) {
            $this->postJson('/conocimiento/fuentes/'.$propuesta['proposal_id'].'/extraccion', [])
                ->assertOk()->assertHeader('X-RateLimit-Remaining', (string) (2 - $indice));
        }

        $this->get('/conocimiento/fuentes')->assertOk()->assertHeader('X-RateLimit-Remaining', '58');
        $this->postJson('/conocimiento/fuentes/KGI-ABCDEF123454/extraccion', [])
            ->assertTooManyRequests()->assertHeader('Retry-After');
        $this->getJson('/conocimiento/fuentes/KGI-ABCDEF123451/extraccion')
            ->assertOk()->assertHeader('X-RateLimit-Remaining', '28');
    }

    public function test_extraccion_rechaza_url_del_navegador_y_respuesta_de_otra_fuente(): void
    {
        $ruta = '/conocimiento/fuentes/KGI-ABCDEF123456/extraccion';
        $cliente = Mockery::mock(AporteIngenierilClient::class);
        $cliente->shouldReceive('knowledgeGovernance')->once()->andReturn(new AporteResponse(true, [
            'proposals' => [[
                'proposal_id' => 'KGI-ABCDEF123456', 'status' => 'APROBADA_PENDIENTE_INGESTA',
                'url' => 'https://www.upb.edu/carreras/sistemas',
            ]],
        ]));
        $cliente->shouldReceive('extractKnowledgeSource')->once()->andReturn(new AporteResponse(true, [
            'proposal_id' => 'KGI-ABCDEF123456', 'source_url' => 'https://www.ucb.edu.bo/otra',
        ]));
        $this->app->instance(AporteIngenierilClient::class, $cliente);
        $this->actingAs($this->actor('Administrador'));
        $this->postJson($ruta, ['url' => 'https://www.ucb.edu.bo/otra'])->assertUnprocessable();
        $this->postJson($ruta, [])->assertServiceUnavailable();
    }

    public function test_token_de_analisis_expira_y_no_puede_reutilizarse(): void
    {
        $client = Mockery::mock(AporteIngenierilClient::class);
        $this->mockVerifiedPreview($client);
        $client->shouldReceive('analyzeKnowledgeSource')->once()->andReturn(new AporteResponse(true, [
            'schema_version' => '1.0', 'trace_id' => 'trace-expiry',
            'assessment' => ['host' => 'www.upb.edu', 'status' => 'ACEPTADA', 'is_bolivian_university' => true, 'recognized_institution' => 'Universidad Privada Boliviana', 'message' => 'Dominio válido.'],
            'readiness' => 'LISTA_PARA_PROPONER', 'risk_level' => 'BAJO', 'can_submit' => true,
            'checks' => [], 'suggestions' => [],
        ]));
        $client->shouldNotReceive('submitKnowledgeSource');
        $this->app->instance(AporteIngenierilClient::class, $client);

        $payload = $this->sourcePayload();
        $analysis = $this->actingAs($this->actor('Director'))
            ->postJson('/conocimiento/fuentes/analizar', $payload);
        $token = $analysis->json('analysis_token');

        $this->travel(11)->minutes();
        $this->post('/conocimiento/fuentes', $payload + ['analysis_token' => $token])
            ->assertSessionHasErrors('analysis_token');
    }

    public function test_titulo_detectado_es_obligatorio_incluso_sin_validacion_frontend(): void
    {
        $client = Mockery::mock(AporteIngenierilClient::class);
        $this->mockVerifiedPreview($client);
        $client->shouldNotReceive('analyzeKnowledgeSource');
        $this->app->instance(AporteIngenierilClient::class, $client);
        $this->actingAs($this->actor('Director'))->postJson('/conocimiento/fuentes/analizar',
            array_replace($this->sourcePayload(), ['title' => 'nsjkandkjanskdnas']))
            ->assertUnprocessable()->assertJsonValidationErrors('title');
    }

    public function test_vista_previa_duplicada_bloquea_analisis_y_no_emite_token(): void
    {
        $client = Mockery::mock(AporteIngenierilClient::class);
        $client->shouldReceive('inspectKnowledgeSource')->once()->andReturn(new AporteResponse(true, [
            'requested_url' => $this->sourcePayload()['url'], 'status' => 'DUPLICADA', 'can_use' => false,
            'message' => 'La fuente ya está en estudio.',
        ]));
        $client->shouldNotReceive('analyzeKnowledgeSource');
        $this->app->instance(AporteIngenierilClient::class, $client);
        $this->actingAs($this->actor('Director'))->postJson('/conocimiento/fuentes/analizar', $this->sourcePayload())
            ->assertUnprocessable()->assertJsonValidationErrors('url');
        $this->assertNull(session('knowledge.source_analysis'));
    }

    public function test_vista_previa_solo_necesita_url_y_preserva_resultado_del_servidor(): void
    {
        $client = Mockery::mock(AporteIngenierilClient::class);
        $this->mockVerifiedPreview($client);
        $this->app->instance(AporteIngenierilClient::class, $client);
        $this->actingAs($this->actor('Administrador'))->postJson('/conocimiento/fuentes/comprobar-url', [
            'url' => $this->sourcePayload()['url'], 'can_use' => true, 'title' => 'Texto arbitrario',
        ])->assertOk()->assertJsonPath('title', $this->sourcePayload()['title'])->assertHeader('Cache-Control', 'no-store, private');
        $this->assertSame('Administrador', session('knowledge.source_preview.actor'));
    }

    private function mockVerifiedPreview($client): void
    {
        $client->shouldReceive('inspectKnowledgeSource')->once()->andReturn(new AporteResponse(true, [
            'requested_url' => $this->sourcePayload()['url'], 'status' => 'VERIFICADA', 'can_use' => true,
            'title' => $this->sourcePayload()['title'], 'content_type' => 'application/pdf',
            'suggested_fields' => ['source_type' => 'OFFICIAL_CURRICULUM_PDF'],
        ]));
    }

    private function actor(string $role): User
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->forceFill([
            'cod_usu' => 'TEST_KNOWLEDGE_'.$role,
            'email' => strtolower($role).'@example.test',
            'email_verified_at' => now(),
            'est_usu' => 'ACTIVO',
        ]);
        $user->shouldReceive('hasRole')->andReturnUsing(fn ($name) => $name === $role);
        $user->shouldReceive('can')->andReturnTrue();
        $user->shouldReceive('checkPermissionTo')->andReturnTrue();

        return $user;
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
            'limitations_text' => 'No informa costos de matrícula.',
        ];
    }
}
