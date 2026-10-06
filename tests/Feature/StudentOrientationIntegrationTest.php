<?php

namespace Tests\Feature;

use App\Models\Oficial\AporteAcademicoVocacional\OrientacionActividad;
use App\Models\Oficial\Sistema\User;
use App\Services\AporteIngenieril\AporteIngenierilClient;
use App\Services\AporteIngenieril\StudentOrientationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\OrientationFixture;
use Tests\TestCase;

class StudentOrientationIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('Run with php -d extension=pdo_sqlite; isolated SQLite is required.');
        }
        $this->artisan('migrate', ['--force' => true])->assertExitCode(0);
    }

    public function test_real_login_riasec_persistence_analysis_knowledge_and_tutor(): void
    {
        if (! getenv('APORTE_INGENIERIL_REAL_E2E')) {
            $this->markTestSkipped('Opt-in real loopback service: APORTE_INGENIERIL_REAL_E2E=1.');
        }
        config()->set('services.aporte_ingenieril.enabled', true);
        config()->set('services.aporte_ingenieril.url', 'http://127.0.0.1:8001');
        config()->set('services.aporte_ingenieril.key', trim(file_get_contents(storage_path('logs/aporte-ingenieril-session.key'))));
        config()->set('services.aporte_ingenieril.timeout', 30);
        $user = OrientationFixture::create();
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect();
        $this->assertAuthenticatedAs($user);
        $this->get('/estudiante')->assertOk()->assertSee('Mi orientación');
        $this->get('/aula-virtual/mi-orientacion')->assertOk()->assertSee('Tu perfil todavía no está listo');
        $instrument = app(AporteIngenierilClient::class)->riasecInstrument();
        $this->assertTrue($instrument->available);
        $this->get('/aula-virtual/mi-orientacion?section=riasec')->assertOk()->assertSee($instrument->data['items'][0]['text']);
        $input = ['instrument_version' => $instrument->data['instrument_version'], 'responses' => array_fill(1, 30, 3)];
        $this->post('/aula-virtual/mi-orientacion/riasec', $input)->assertRedirect();
        $activity = OrientacionActividad::firstOrFail();
        $this->assertCount(30, $activity->riasec_public['responses']);
        $this->assertSame(3, $activity->riasec_public['responses'][0]['value']);
        $this->assertSame(10, $activity->riasec_score['scores']['R']);
        $this->assertNotEmpty($activity->riasec_score['trace_id']);
        $this->post('/aula-virtual/mi-orientacion/analisis')->assertRedirect();
        $activity->refresh();
        $this->assertSame('COMPLETE', $activity->analysis_snapshot['analysis_status']);
        $this->assertNotEmpty($activity->analysis_snapshot['traceability']['input_hash']);
        $this->get('/aula-virtual/mi-orientacion?section=analisis')->assertRedirect('/estudiante/intereses');
        $this->get('/estudiante/intereses')->assertOk()->assertSee('Mi análisis')->assertSee('Tú tomas la decisión final');
        $warmup = app(AporteIngenierilClient::class)->warmUp();
        $this->assertTrue($warmup->available, $warmup->message);
        $this->post('/aula-virtual/mi-orientacion/consulta', ['mode' => 'fuentes', 'question' => 'materias iniciales de Ingeniería Civil'])->assertOk()->assertSee('Trazabilidad');
        $this->post('/aula-virtual/mi-orientacion/consulta', ['mode' => 'tutor', 'question' => 'materias iniciales de Ingeniería Civil'])->assertOk()->assertSee('Trazabilidad');
        $this->post('/aula-virtual/mi-orientacion/analisis', ['student_id' => 'EST_OTHER'])->assertSessionHasErrors('student_id');
        $this->assertSame('EST_TEST', $activity->cod_est);
        file_put_contents(storage_path('logs/aporte-ingenieril-real-e2e.json'), json_encode(['fixture_classification' => 'ISOLATED_TEST_DATA', 'status' => 'PASS', 'login' => true, 'persistence' => true, 'riasec_trace_id' => $activity->riasec_score['trace_id'], 'analysis_trace_id' => $activity->analysis_snapshot['trace_id'], 'corpus_version' => $warmup->data['corpus_version'] ?? null, 'corpus_sha256' => hash_file('sha256', base_path('ai-service/data/processed/corpus.jsonl'))], JSON_PRETTY_PRINT));
    }

    public function test_missing_riasec_blocks_analysis_before_network_and_revoked_permission_blocks_ui(): void
    {
        $user = OrientationFixture::create();
        $this->actingAs($user);
        Http::fake();
        $this->post('/estudiante/intereses/analizar')->assertSessionHas('orientation_notice');
        $this->post('/aula-virtual/mi-orientacion/analisis')->assertSessionHas('orientation_notice');
        Http::assertNothingSent();
        $user->roles->first()->revokePermissionTo('Orientacion_Academica_Profesional');
        $this->get('/aula-virtual/mi-orientacion')->assertForbidden();
        $this->get('/estudiante/intereses')->assertForbidden();
        $this->get('/estudiante/futuro')->assertForbidden();
        $this->get('/estudiante/asistente')->assertForbidden();
    }

    public function test_preparation_and_plan_are_independent_windows_with_their_own_permission(): void
    {
        $user = OrientationFixture::create();
        $this->actingAs($user);

        $this->get('/estudiante/preparacion')
            ->assertOk()
            ->assertSee('Organiza lo que necesitas reforzar ahora')
            ->assertSee('Actividades pendientes')
            ->assertSee('Ir a Mi plan')
            ->assertDontSee('Carreras, universidades y planes de estudio')
            ->assertDontSee('Cuestionario RIASEC')
            ->assertDontSee('Pregunta, practica y comprende');

        $this->get('/estudiante/plan')
            ->assertOk()
            ->assertSee('Define metas claras y acciones alcanzables')
            ->assertSee('Mis objetivos y acciones')
            ->assertSee('Ver Mi preparación')
            ->assertDontSee('Carreras, universidades y planes de estudio')
            ->assertDontSee('Cuestionario RIASEC')
            ->assertDontSee('Pregunta, practica y comprende');

        $user->roles->first()->revokePermissionTo('Perfil_Academico');
        $this->get('/estudiante/preparacion')->assertForbidden();
        $this->get('/estudiante/plan')->assertForbidden();
    }

    public function test_career_explorer_prioritizes_academic_program_and_limits_comparison_to_three_options(): void
    {
        $user = OrientationFixture::create();
        $this->actingAs($user);
        OrientacionActividad::create([
            'cod_est' => 'EST_TEST',
            'estado' => 'finalizado',
            'avance' => 100,
            'riasec_public' => ['instrument_version' => 'test', 'responses' => []],
            'riasec_score' => ['scores' => [], 'holland_code' => 'IS'],
            'analysis_snapshot' => [
                'analysis_status' => 'PARTIAL',
                'student_snapshot' => [],
                'trace_id' => 'student-hidden-trace-id',
                'traceability' => ['input_hash' => str_repeat('a', 64)],
                'limitations' => [
                    ['code' => 'DECISION_SUPPORT_ONLY', 'message' => 'SAVP apoya la exploración y no decide una carrera.'],
                    ['code' => 'NO_SUCCESS_PROBABILITY', 'message' => 'La salida no estima probabilidad de éxito universitario.'],
                    ['code' => 'LEGACY_BASELINE_SEPARATE', 'message' => 'V1 permanece como LEGACY_EXPERIMENTAL_BASELINE.'],
                ],
                'career_evidence_profiles' => [
                    $this->careerProfile('BO-UPB-LP-SISC', 'Ingeniería de Sistemas Computacionales', 'Universidad Privada Boliviana'),
                    $this->careerProfile('BO-UCB-LP-SISC', 'Ingeniería de Sistemas Computacionales', 'Universidad Católica Boliviana San Pablo'),
                    $this->careerProfile('BO-UCB-LP-SIS', 'Ingeniería de Sistemas', 'Universidad Católica Boliviana San Pablo'),
                    $this->careerProfile('BO-UCB-LP-CIVIL', 'Ingeniería Civil', 'Universidad Católica Boliviana San Pablo'),
                ],
            ],
        ]);

        $this->get('/aula-virtual/mi-orientacion?section=analisis')->assertRedirect('/estudiante/intereses');

        $this->get('/estudiante/intereses')
            ->assertOk()
            ->assertSee('Comprende tus intereses antes de explorar carreras')
            ->assertSee('Este espacio contiene únicamente tu cuestionario RIASEC')
            ->assertSee('Tú tomas la decisión final')
            ->assertSee('Es una guía, no una predicción')
            ->assertSee('Carreras relacionadas con tus intereses RIASEC')
            ->assertSee('Ingeniería de Sistemas Computacionales')
            ->assertSee('Ingeniería de Sistemas')
            ->assertSee('Ingeniería Civil')
            ->assertSee('Ver universidades para estas carreras')
            ->assertDontSee('Universidad Privada Boliviana')
            ->assertDontSee('Universidad Católica Boliviana San Pablo')
            ->assertDontSee('Programación I')
            ->assertDontSee('LEGACY_EXPERIMENTAL_BASELINE')
            ->assertDontSee('Trazabilidad técnica')
            ->assertDontSee('student-hidden-trace-id')
            ->assertDontSee(str_repeat('a', 64))
            ->assertDontSee('Bridge')
            ->assertDontSee('crosswalk')
            ->assertDontSee('corpus')
            ->assertDontSee('CORPUS_VALIDADO')
            ->assertDontSee('Universidades disponibles');

        $this->get('/estudiante/futuro')
            ->assertOk()
            ->assertSee('Carreras, universidades y planes de estudio')
            ->assertSee('Según mis intereses')
            ->assertSee('Elegir universidad')
            ->assertSee('Todas las carreras')
            ->assertSee('Programación I')
            ->assertSee('Ver malla oficial')
            ->assertSee('Universidades para tus tres opciones de exploración')
            ->assertSee('4 opción(es)');

        $this->get('/estudiante/futuro?explore=universities')
            ->assertOk()
            ->assertSee('Elige una universidad para ver sus carreras')
            ->assertSee('El sistema no elige la institución por ti.')
            ->assertSee('Selecciona una universidad');

        $this->get('/estudiante/futuro?explore=universities&university=Universidad+Privada+Boliviana')
            ->assertOk()
            ->assertSee('1 opción(es)')
            ->assertSee('value="Universidad Privada Boliviana" selected', false);

        $this->get('/estudiante/futuro?explore=all')
            ->assertOk()
            ->assertSee('4 opción(es)')
            ->assertSee('Todas las carreras')
            ->assertSee('Primero elige una carrera');

        $this->get('/estudiante/futuro?explore=all&career=ingenieria-de-sistemas-computacionales')
            ->assertOk()
            ->assertSee('Ingeniería de Sistemas Computacionales')
            ->assertSee('2 opción(es)')
            ->assertSee('Universidad Privada Boliviana')
            ->assertSee('Universidad Católica Boliviana San Pablo');

        $this->get('/estudiante/futuro?explore=all&career=ingenieria-de-sistemas-computacionales&university=Universidad+Privada+Boliviana')
            ->assertOk()
            ->assertSee('1 opción(es)')
            ->assertSee('value="Universidad Privada Boliviana" selected', false);

        $this->get('/estudiante/futuro?explore=all&career=ingenieria-de-sistemas-computacionales&university=Universidad+Extranjera')
            ->assertOk()
            ->assertSee('2 opción(es)')
            ->assertDontSee('value="Universidad Extranjera"', false);

        $this->get('/estudiante/futuro?explore=ranking')->assertSessionHasErrors('explore');
        $this->get('/estudiante/asistente')->assertOk()->assertSee('Pregunta, practica y comprende')->assertSee('¡Hola!');
        $this->get('/aula-virtual/mi-orientacion?section=tutor')->assertRedirect('/estudiante/asistente');
    }

    public function test_service_outage_preserves_saved_answers_and_analysis(): void
    {
        $user = OrientationFixture::create();
        $activity = OrientacionActividad::create(['cod_est' => 'EST_TEST', 'estado' => 'finalizado', 'avance' => 100, 'riasec_public' => ['instrument_version' => 'test', 'responses' => array_map(fn ($id) => ['item_id' => $id, 'value' => 3], range(1, 30))], 'riasec_score' => ['saved' => true], 'analysis_snapshot' => ['saved' => true]]);
        config()->set('services.aporte_ingenieril.enabled', true);
        config()->set('services.aporte_ingenieril.url', 'http://127.0.0.1:8001');
        Http::fake(['*' => Http::failedConnection()]);
        $result = app(StudentOrientationService::class)->analyze($user);
        $this->assertFalse($result->available);
        $this->assertSame(['saved' => true], $activity->fresh()->analysis_snapshot);
        $this->assertSame(3, $activity->fresh()->riasec_public['responses'][0]['value']);

        $this->actingAs($user)
            ->post('/estudiante/intereses/analizar')
            ->assertRedirect()
            ->assertSessionHas('orientation_notice', function (array $notice): bool {
                return $notice['title'] === 'No pudimos actualizar tu análisis en este momento'
                    && str_contains($notice['message'], 'No es un error de tus respuestas')
                    && str_contains($notice['preserved'], 'siguen guardados')
                    && str_contains($notice['next_step'], 'volver a intentarlo');
            });

        $this->get('/estudiante/intereses')
            ->assertOk()
            ->assertSee('No pudimos actualizar tu análisis en este momento')
            ->assertSee('No es un error de tus respuestas')
            ->assertSee('Tu cuestionario RIASEC y tu último análisis siguen guardados')
            ->assertDontSee('Revisa los campos señalados');

        $this->assertSame(['saved' => true], $activity->fresh()->analysis_snapshot);
        $this->assertSame(3, $activity->fresh()->riasec_public['responses'][0]['value']);
    }

    public function test_student_scope_excludes_another_students_snapshot(): void
    {
        $owner = OrientationFixture::create();
        OrientacionActividad::create(['cod_est' => 'EST_TEST', 'estado' => 'finalizado', 'avance' => 100, 'riasec_public' => ['private' => 'owner-only']]);
        $other = User::factory()->create();
        $other->assignRole($owner->roles->first());
        $record = (array) DB::table('estudiante')->where('cod_est', 'EST_TEST')->first();
        $record['cod_est'] = 'EST_OTHER';
        $record['cod_per'] = $other->cod_per;
        $record['rud_est'] = 'OTHER_TEST';
        DB::table('estudiante')->insert($record);
        $context = app(StudentOrientationService::class)->context($other);
        $this->assertNull($context['activity']);
        $this->assertArrayNotHasKey('riasec_public', $context['payload']);
        $this->assertArrayNotHasKey('academic', $context['payload']);
    }

    public function test_snapshot_migration_is_reversible_and_preserves_legacy_activity(): void
    {
        OrientationFixture::create();
        $activity = OrientacionActividad::create(['cod_est' => 'EST_TEST', 'estado' => 'en_proceso', 'avance' => 25]);
        $migration = require database_path('migrations/2026_10_01_000001_extend_orientation_peter3_snapshots.php');
        $migration->down();
        $this->assertFalse(Schema::hasColumn('orientacion_actividades', 'riasec_public'));
        $this->assertSame(25, $activity->fresh()->avance);
        $migration->up();
        $this->assertTrue(Schema::hasColumn('orientacion_actividades', 'riasec_public'));
        $this->assertNull($activity->fresh()->riasec_public);
    }

    private function careerProfile(string $careerId, string $careerName, string $university): array
    {
        return [
            'career_id' => $careerId,
            'career_name' => $careerName,
            'university' => $university,
            'academic_program' => [
                'degree' => 'Licenciatura',
                'duration' => '9 semestres',
                'professional_profile' => 'Diseña y desarrolla soluciones tecnológicas.',
                'knowledge_areas' => ['Desarrollo de software', 'Bases de datos'],
                'documented_subjects' => ['Programación I', 'Álgebra Lineal'],
                'curriculum_status' => 'PARTIAL',
                'curriculum_scope' => 'DOCUMENTED_INITIAL_SUBJECTS',
                'curriculum_note' => 'Existe una malla oficial en el corpus; la malla completa se verifica en la fuente oficial.',
                'sources' => [[
                    'source_id' => $careerId.'-MALLA',
                    'title' => 'Malla curricular oficial',
                    'source_type' => 'OFFICIAL_CURRICULUM_PDF',
                    'reference' => 'https://universidad.example.test/malla.pdf',
                ]],
            ],
            'vocational_interest_relation' => ['status' => 'PARTIAL', 'sources' => []],
            'technical_relation' => ['status' => 'UNAVAILABLE'],
            'preparation' => [
                'interpretation' => 'Existe evidencia parcial para revisar esta opción.',
                'relations_with_observed_academic_evidence' => 1,
                'related_relations' => 2,
                'reinforcement_areas' => [],
                'evidence_items' => [],
            ],
            'evidence_quality' => [
                'academic_record_count' => 4,
                'distinct_subject_count' => 3,
                'ordered_period_count' => 1,
            ],
            'areas_without_evidence' => [],
            'sources' => [],
            'limitations' => [],
        ];
    }
}
