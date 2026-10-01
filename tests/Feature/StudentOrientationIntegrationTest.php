<?php

namespace Tests\Feature;

use App\Models\AulaVirtual\OrientacionActividad;
use App\Services\AporteIngenieril\AporteIngenierilClient;
use Illuminate\Support\Facades\Http;
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
        if (! getenv('PETER3_REAL_E2E')) {
            $this->markTestSkipped('Opt-in real loopback service: PETER3_REAL_E2E=1.');
        }
        config()->set('services.peter3.enabled', true);
        config()->set('services.peter3.url', 'http://127.0.0.1:8001');
        config()->set('services.peter3.key', trim(file_get_contents(storage_path('logs/peter3-session.key'))));
        config()->set('services.peter3.timeout', 30);
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
        $this->get('/aula-virtual/mi-orientacion?section=analisis')->assertOk()->assertSee('Mi análisis')->assertSee('Limitaciones del análisis');
        $warmup = app(AporteIngenierilClient::class)->warmUp();
        $this->assertTrue($warmup->available, $warmup->message);
        $this->post('/aula-virtual/mi-orientacion/consulta', ['mode' => 'fuentes', 'question' => 'materias iniciales de Ingeniería Civil'])->assertOk()->assertSee('Trazabilidad');
        $this->post('/aula-virtual/mi-orientacion/consulta', ['mode' => 'tutor', 'question' => 'materias iniciales de Ingeniería Civil'])->assertOk()->assertSee('Trazabilidad');
        $this->post('/aula-virtual/mi-orientacion/analisis', ['student_id' => 'EST_OTHER'])->assertSessionHasErrors('student_id');
        $this->assertSame('EST_TEST', $activity->cod_est);
        file_put_contents(storage_path('logs/peter3-real-e2e.json'), json_encode(['fixture_classification' => 'ISOLATED_TEST_DATA', 'status' => 'PASS', 'login' => true, 'persistence' => true, 'riasec_trace_id' => $activity->riasec_score['trace_id'], 'analysis_trace_id' => $activity->analysis_snapshot['trace_id'], 'corpus_version' => $warmup->data['corpus_version'] ?? null, 'corpus_sha256' => hash_file('sha256', base_path('ai-service/data/processed/corpus.jsonl'))], JSON_PRETTY_PRINT));
    }

    public function test_missing_riasec_blocks_analysis_before_network_and_revoked_permission_blocks_ui(): void
    {
        $user = OrientationFixture::create();
        $this->actingAs($user);
        Http::fake();
        $this->post('/aula-virtual/mi-orientacion/analisis')->assertSessionHasErrors('orientation');
        Http::assertNothingSent();
        $user->roles->first()->revokePermissionTo('Orientacion_Academica_Profesional');
        $this->get('/aula-virtual/mi-orientacion')->assertForbidden();
    }

    public function test_service_outage_preserves_saved_answers_and_analysis(): void
    {
        $user = OrientationFixture::create();
        $activity = OrientacionActividad::create(['cod_est' => 'EST_TEST', 'estado' => 'finalizado', 'avance' => 100, 'riasec_public' => ['instrument_version' => 'test', 'responses' => array_map(fn ($id) => ['item_id' => $id, 'value' => 3], range(1, 30))], 'riasec_score' => ['saved' => true], 'analysis_snapshot' => ['saved' => true]]);
        config()->set('services.peter3.enabled', true);
        config()->set('services.peter3.url', 'http://127.0.0.1:8001');
        Http::fake(['*' => Http::failedConnection()]);
        $result = app(\App\Services\AporteIngenieril\StudentOrientationService::class)->analyze($user);
        $this->assertFalse($result->available);
        $this->assertSame(['saved' => true], $activity->fresh()->analysis_snapshot);
        $this->assertSame(3, $activity->fresh()->riasec_public['responses'][0]['value']);
    }

    public function test_student_scope_excludes_another_students_snapshot(): void
    {
        $owner = OrientationFixture::create();
        OrientacionActividad::create(['cod_est' => 'EST_TEST', 'estado' => 'finalizado', 'avance' => 100, 'riasec_public' => ['private' => 'owner-only']]);
        $other = \App\Models\User::factory()->create();
        $other->assignRole($owner->roles->first());
        $record = (array) \Illuminate\Support\Facades\DB::table('estudiante')->where('cod_est', 'EST_TEST')->first();
        $record['cod_est'] = 'EST_OTHER';
        $record['cod_per'] = $other->cod_per;
        $record['rud_est'] = 'OTHER_TEST';
        \Illuminate\Support\Facades\DB::table('estudiante')->insert($record);
        $context = app(\App\Services\AporteIngenieril\StudentOrientationService::class)->context($other);
        $this->assertNull($context['activity']);
        $this->assertArrayNotHasKey('riasec_public', $context['payload']);
        $this->assertArrayNotHasKey('academic', $context['payload']);
    }

    public function test_snapshot_migration_is_reversible_and_preserves_legacy_activity(): void
    {
        OrientationFixture::create();
        $activity = OrientacionActividad::create(['cod_est'=>'EST_TEST','estado'=>'en_proceso','avance'=>25]);
        $migration = require database_path('migrations/2026_10_01_000001_extend_orientation_peter3_snapshots.php');
        $migration->down();
        $this->assertFalse(\Illuminate\Support\Facades\Schema::hasColumn('orientacion_actividades','riasec_public'));
        $this->assertSame(25, $activity->fresh()->avance);
        $migration->up();
        $this->assertTrue(\Illuminate\Support\Facades\Schema::hasColumn('orientacion_actividades','riasec_public'));
        $this->assertNull($activity->fresh()->riasec_public);
    }
}
