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
    public function test_invitado_no_accede_a_operaciones_de_gestion_de_conocimiento(): void
    {
        $this->getJson('/conocimiento/fuentes')->assertUnauthorized();
        $this->getJson('/conocimiento/conexion')->assertUnauthorized();
        $this->postJson('/conocimiento/fuentes/analizar', $this->sourcePayload())->assertUnauthorized();
        $this->postJson('/conocimiento/fuentes/comprobar-url', ['url' => $this->sourcePayload()['url']])->assertUnauthorized();
        $this->postJson('/conocimiento/fuentes', $this->sourcePayload())->assertUnauthorized();
        $this->postJson('/conocimiento/tutor/probar', ['question' => 'Hola'])->assertUnauthorized();
        $this->postJson('/conocimiento/fuentes/KGI-ABCDEF123456/revision', [])->assertUnauthorized();
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
