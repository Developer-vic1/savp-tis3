<?php

namespace Tests\Feature;

use App\Models\Oficial\AporteAcademicoVocacional\OrientacionActividad;
use App\Models\Oficial\AulaVirtual\ClaseVirtual;
use App\Models\Oficial\Sistema\User;
use App\Services\AporteIngenieril\StudentOrientationService;
use App\Services\AulaVirtual\CursoVirtualService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\OrientationFixture;
use Tests\TestCase;

class BorradorInteresesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('Estas pruebas requieren PDO SQLite en memoria.');
        }
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        $rutas = array_values(array_filter(glob(database_path('migrations/*.php')), fn ($ruta) => basename($ruta) < '2026_10_04'));
        $this->artisan('migrate', ['--force' => true, '--path' => $rutas, '--realpath' => true])->assertExitCode(0);
        Schema::table('calificacion', fn (Blueprint $tabla) => $tabla->string('cod_ins')->nullable());
        Schema::table('clase_virtual', fn (Blueprint $tabla) => $tabla->string('cod_pes')->nullable());
        Schema::table('orientacion_actividades', function (Blueprint $tabla) {
            $tabla->string('cod_ior')->nullable();
            $tabla->integer('int_oac')->nullable();
        });
        Schema::create('grupo_academico', function (Blueprint $tabla) {
            $tabla->string('cod_gac')->primary();
            $tabla->string('cod_gea');
        });
        foreach (['plan_asignatura', 'plan_especialidad'] as $plan) {
            Schema::table($plan, fn (Blueprint $tabla) => $tabla->string('cod_gac')->nullable());
        }
        $this->partialMock(CursoVirtualService::class, function (MockInterface $mock) {
            $mock->shouldReceive('studentQuery')->andReturnUsing(fn () => ClaseVirtual::query());
        });
        Http::preventStrayRequests();
        config(['services.aporte_ingenieril.enabled' => true, 'services.aporte_ingenieril.url' => 'http://127.0.0.1:8001',
            'services.aporte_ingenieril.key' => 'test-only-internal-api-key-32-characters']);
    }

    private function prepararEstudiante(): User
    {
        $usuario = OrientationFixture::create();
        DB::table('grupo_academico')->insert(['cod_gac' => 'GAC_TEST', 'cod_gea' => 'GEA_TEST']);
        DB::table('plan_asignatura')->where('cod_pas', 'PAS_TEST')->update(['cod_gac' => 'GAC_TEST']);
        DB::table('calificacion')->where('cod_cal', 'CAL_TEST')->update(['cod_ins' => 'INS_TEST', 'est_cal' => 'VIGENTE']);
        $this->actingAs($usuario);

        return $usuario;
    }

    private function resultado(): array
    {
        return ['schema_version' => '2.0', 'instrument_version' => '2.0-es-2025', 'trace_id' => 'prueba-score',
            'scores' => array_fill_keys(str_split('RIASEC'), 10), 'top_codes' => str_split('RIASEC'), 'holland_code' => 'RIA', 'limitations' => []];
    }

    private function servicioDisponible(): void
    {
        Http::fake([
            '*/api/v2/riasec/instrument' => Http::response([
                'schema_version' => '2.0', 'instrument_version' => '2.0-es-2025', 'title' => 'Instrumento de prueba aislado',
                'items' => array_map(fn ($id) => ['item_id' => $id, 'text' => 'Actividad '.$id], range(1, 30)),
                'response_scale' => array_map(fn ($valor) => ['value' => $valor, 'label' => 'Opción '.$valor], range(1, 5)),
                'source_url' => 'https://onetinterestprofiler.org/es/', 'source_attribution' => 'Prueba aislada', 'limitations' => [],
            ]),
            '*/api/v2/riasec/score' => Http::response($this->resultado(), 200, ['X-Trace-Id' => 'prueba-score']),
        ]);
    }

    public function test_avance_parcial_persiste_y_se_retoma_sin_generar_perfil_ni_analisis(): void
    {
        $usuario = $this->prepararEstudiante();
        $this->servicioDisponible();
        $this->postJson(route('estudiante.intereses.borrador'), ['instrument_version' => '2.0-es-2025', 'responses' => [5 => 4, 1 => '3']])
            ->assertOk()->assertJsonPath('draftSaved', true)->assertJsonPath('answered', 2)
            ->assertJsonPath('responses.1', 3)->assertJsonPath('responses.5', 4)->assertJsonStructure(['savedAt']);
        $actividad = OrientacionActividad::firstOrFail();
        $this->assertSame('en_proceso', $actividad->estado);
        $this->assertSame(6, $actividad->avance);
        $this->assertNull($actividad->riasec_score);
        $this->assertNull($actividad->analysis_snapshot);
        $this->assertNull($actividad->finalizado_at);
        $this->assertSame([['item_id' => 1, 'value' => 3], ['item_id' => 5, 'value' => 4]], $actividad->riasec_public['responses']);

        $contexto = app(StudentOrientationService::class)->context($usuario);
        $this->assertSame($actividad->id, $contexto['draft']->id);
        $this->assertNull($contexto['activity']);
        $this->assertArrayNotHasKey('riasec_public', $contexto['payload']);
        $this->assertFalse($contexto['precheck']['ready']);
        $this->postJson('/estudiante/intereses/analizar')->assertUnprocessable();
        Http::assertSentCount(1);
    }

    public function test_recargar_cuestionario_recupera_respuestas_del_borrador_del_servidor(): void
    {
        $this->prepararEstudiante();
        $this->servicioDisponible();
        $this->postJson(route('estudiante.intereses.borrador'), ['instrument_version' => '2.0-es-2025', 'responses' => [1 => 3, 5 => 4]])->assertOk();

        $pagina = $this->get('/estudiante/intereses');

        $pagina->assertOk()->assertSee('Continuar mi borrador')->assertSee('2 de 30 respondidas');
        $this->assertMatchesRegularExpression('/<input[^>]*id="interes-1-3"[^>]*\schecked(?:\s|>)/', $pagina->getContent());
        $this->assertMatchesRegularExpression('/<input[^>]*id="interes-5-4"[^>]*\schecked(?:\s|>)/', $pagina->getContent());
        $this->assertSame(2, preg_match_all('/<input[^>]*type="radio"[^>]*\schecked(?:\s|>)/', $pagina->getContent()));
        Http::assertSentCount(2);
    }

    public function test_error_al_completar_preserva_borrador_y_no_crea_perfil_final(): void
    {
        $usuario = $this->prepararEstudiante();
        $this->servicioDisponible();
        $this->postJson(route('estudiante.intereses.borrador'), ['instrument_version' => '2.0-es-2025', 'responses' => [1 => 3]])->assertOk();
        $antes = OrientacionActividad::firstOrFail()->getAttributes();
        config(['services.aporte_ingenieril.enabled' => false]);

        $this->postJson('/estudiante/intereses', ['instrument_version' => '2.0-es-2025', 'responses' => array_fill(1, 30, 3)])->assertStatus(503);

        $this->assertSame($antes, OrientacionActividad::firstOrFail()->getAttributes());
        $contexto = app(StudentOrientationService::class)->context($usuario);
        $this->assertNull($contexto['activity']);
        $this->assertNotNull($contexto['draft']);
        $this->assertSame(1, OrientacionActividad::count());
        Http::assertSentCount(1);
    }

    public function test_guardados_sucesivos_actualizan_un_solo_borrador_y_no_ocultan_el_estudio_anterior(): void
    {
        $usuario = $this->prepararEstudiante();
        $previo = OrientacionActividad::create(['cod_est' => 'EST_TEST', 'cod_gea' => 'GEA_TEST', 'estado' => 'finalizado',
            'riasec_public' => ['instrument_version' => '2.0-es-2025', 'responses' => array_map(fn ($id) => ['item_id' => $id, 'value' => 3], range(1, 30))],
            'riasec_score' => $this->resultado(), 'analysis_snapshot' => ['trace_id' => 'estudio-previo']]);
        $this->servicioDisponible();
        $this->postJson(route('estudiante.intereses.borrador'), ['instrument_version' => '2.0-es-2025', 'responses' => [1 => 4]])->assertOk();
        $id = OrientacionActividad::latest('id')->firstOrFail()->id;
        $this->postJson(route('estudiante.intereses.borrador'), ['instrument_version' => '2.0-es-2025', 'responses' => [1 => 5, 2 => 4]])->assertOk();

        $this->assertSame(2, OrientacionActividad::count());
        $contexto = app(StudentOrientationService::class)->context($usuario);
        $this->assertSame($id, $contexto['draft']->id);
        $this->assertSame($previo->id, $contexto['activity']->id);
        $this->assertTrue($contexto['precheck']['ready']);
        $this->assertSame(['trace_id' => 'estudio-previo'], $previo->refresh()->analysis_snapshot);
    }

    #[DataProvider('entradasInvalidas')]
    public function test_ids_y_valores_invalidos_se_rechazan_antes_de_red(mixed $respuestas): void
    {
        $this->prepararEstudiante();
        $this->postJson(route('estudiante.intereses.borrador'), ['instrument_version' => '2.0-es-2025', 'responses' => $respuestas])->assertUnprocessable();
        Http::assertNothingSent();
        $this->assertSame(0, OrientacionActividad::count());
    }

    public static function entradasInvalidas(): array
    {
        return ['sin respuestas' => [[]], 'escalar' => ['invalido'], 'id cero' => [[0 => 3]], 'id ajeno' => [[31 => 3]],
            'id duplicado textual' => [[1 => 3, '01' => 4]], 'valor interno cero' => [[1 => 0]], 'booleano' => [[1 => true]], 'anidado' => [[1 => ['value' => 3]]]];
    }

    public function test_version_distinta_no_sobrescribe_borrador_y_indisponibilidad_conserva_avance(): void
    {
        $this->prepararEstudiante();
        $this->servicioDisponible();
        $this->postJson(route('estudiante.intereses.borrador'), ['instrument_version' => '2.0-es-2025', 'responses' => [1 => 3]])->assertOk();
        $previo = OrientacionActividad::firstOrFail()->getAttributes();
        $this->postJson(route('estudiante.intereses.borrador'), ['instrument_version' => 'version-ajena', 'responses' => [1 => 4]])->assertUnprocessable();
        $this->assertSame($previo, OrientacionActividad::firstOrFail()->getAttributes());
        config(['services.aporte_ingenieril.enabled' => false]);
        $this->postJson(route('estudiante.intereses.borrador'), ['instrument_version' => '2.0-es-2025', 'responses' => [1 => 5]])->assertStatus(503)
            ->assertJsonPath('notice.title', 'No pudimos guardar tu avance en este momento');
        $this->assertSame($previo, OrientacionActividad::firstOrFail()->getAttributes());
        Http::assertSentCount(2);
    }

    public function test_otro_estudiante_no_puede_leer_ni_actualizar_borrador_ajeno(): void
    {
        $propietario = $this->prepararEstudiante();
        $this->servicioDisponible();
        $this->postJson(route('estudiante.intereses.borrador'), ['instrument_version' => '2.0-es-2025', 'responses' => [1 => 3]])->assertOk();
        $propio = OrientacionActividad::firstOrFail();
        $otro = User::factory()->create();
        $otro->assignRole($propietario->roles->first());
        $fila = (array) DB::table('estudiante')->where('cod_est', 'EST_TEST')->first();
        $fila['cod_est'] = 'EST_OTRO';
        $fila['cod_per'] = $otro->cod_per;
        $fila['rud_est'] = 'TEST_OTRO';
        DB::table('estudiante')->insert($fila);
        $inscripcion = (array) DB::table('inscripcion_estudiante')->where('cod_ins', 'INS_TEST')->first();
        $inscripcion['cod_ins'] = 'INS_OTRO';
        $inscripcion['cod_est'] = 'EST_OTRO';
        DB::table('inscripcion_estudiante')->insert($inscripcion);
        $this->actingAs($otro);

        $this->assertNull(app(StudentOrientationService::class)->context($otro)['draft']);
        $this->postJson(route('estudiante.intereses.borrador'), ['student_id' => 'EST_TEST', 'instrument_version' => '2.0-es-2025', 'responses' => [1 => 4]])->assertUnprocessable();
        $this->postJson(route('estudiante.intereses.borrador'), ['instrument_version' => '2.0-es-2025', 'responses' => [1 => 4]])->assertOk();
        $this->assertSame('EST_OTRO', app(StudentOrientationService::class)->context($otro)['draft']->cod_est);
        $this->assertSame(3, $propio->refresh()->riasec_public['responses'][0]['value']);
        $this->assertSame(2, OrientacionActividad::count());
    }

    public function test_cuestionario_completo_finaliza_la_misma_fila_y_conserva_perfiles_previos_incluso_mismo_hash(): void
    {
        $usuario = $this->prepararEstudiante();
        $publico = ['instrument_version' => '2.0-es-2025', 'responses' => array_map(fn ($id) => ['item_id' => $id, 'value' => 3], range(1, 30))];
        $previo = OrientacionActividad::create(['cod_est' => 'EST_TEST', 'cod_gea' => 'GEA_TEST', 'estado' => 'finalizado', 'riasec_public' => $publico,
            'riasec_score' => $this->resultado(), 'analysis_snapshot' => ['trace_id' => 'previo'], 'riasec_input_hash' => hash('sha256', json_encode($publico, JSON_THROW_ON_ERROR))]);
        $this->servicioDisponible();
        $this->postJson(route('estudiante.intereses.borrador'), ['instrument_version' => '2.0-es-2025', 'responses' => [1 => 4]])->assertOk();
        $id = OrientacionActividad::latest('id')->firstOrFail()->id;
        $this->postJson('/estudiante/intereses', ['instrument_version' => '2.0-es-2025', 'responses' => array_fill(1, 30, 3)])->assertOk();

        $contexto = app(StudentOrientationService::class)->context($usuario);
        $this->assertNull($contexto['draft']);
        $this->assertSame($id, $contexto['activity']->id);
        $this->assertSame('finalizado', $contexto['activity']->estado);
        $this->assertSame(100, $contexto['activity']->avance);
        $this->assertNotNull($contexto['activity']->finalizado_at);
        $this->assertSame($publico, $contexto['activity']->riasec_public);
        $this->assertSame(2, OrientacionActividad::count());
        $this->assertSame(['trace_id' => 'previo'], $previo->refresh()->analysis_snapshot);
    }

    public function test_borrador_de_otra_gestion_no_se_retoma_ni_se_finaliza_desde_la_actual(): void
    {
        $usuario = $this->prepararEstudiante();
        DB::table('gestion_academica')->insert(['cod_gea' => 'GEA_ANTERIOR', 'ani_gea' => 2025]);
        $anterior = OrientacionActividad::create(['cod_est' => 'EST_TEST', 'cod_gea' => 'GEA_ANTERIOR', 'estado' => 'en_proceso',
            'riasec_public' => ['instrument_version' => '2.0-es-2025', 'responses' => [['item_id' => 1, 'value' => 4]]]]);
        $this->assertNull(app(StudentOrientationService::class)->context($usuario)['draft']);
        $this->servicioDisponible();
        $this->postJson('/estudiante/intereses', ['instrument_version' => '2.0-es-2025', 'responses' => array_fill(1, 30, 3)])->assertOk();
        $this->assertSame('en_proceso', $anterior->refresh()->estado);
        $this->assertSame(2, OrientacionActividad::count());
    }

    public function test_permiso_revocado_bloquea_guardado_de_borrador_antes_de_red(): void
    {
        $usuario = $this->prepararEstudiante();
        $usuario->roles->first()->revokePermissionTo('Orientacion_Academica_Profesional');
        $this->postJson(route('estudiante.intereses.borrador'), ['instrument_version' => '2.0-es-2025', 'responses' => [1 => 3]])->assertForbidden();
        Http::assertNothingSent();
        $this->assertSame(0, OrientacionActividad::count());
    }

    private function perfilFinal(): OrientacionActividad
    {
        $publico = ['instrument_version' => '2.0-es-2025', 'responses' => array_map(fn ($id) => ['item_id' => $id, 'value' => 3], range(1, 30))];

        return OrientacionActividad::create(['cod_est' => 'EST_TEST', 'cod_gea' => 'GEA_TEST', 'estado' => 'finalizado', 'avance' => 100,
            'iniciado_at' => '2026-10-01 10:00:00', 'finalizado_at' => '2026-10-01 10:05:00', 'riasec_public' => $publico,
            'riasec_score' => $this->resultado(), 'riasec_input_hash' => hash('sha256', json_encode($publico, JSON_THROW_ON_ERROR))]);
    }

    private function estudio(string $hash = 'a'): array
    {
        return ['schema_version' => '2.0', 'trace_id' => 'prueba-estudio', 'student_ref' => hash_hmac('sha256', 'EST_TEST', config('app.key')),
            'analysis_status' => 'PARTIAL',
            'student_snapshot' => array_fill_keys(array_map(fn ($componente) => $componente.'_evidence', ['academic', 'attendance', 'learning_activity', 'historical', 'technical', 'declared_interest']), ['status' => 'PARTIAL']),
            'career_evidence_profiles' => [], 'informational_external_careers' => [], 'sources_used' => [], 'limitations' => [], 'warnings' => [],
            'traceability' => ['input_hash' => str_repeat($hash, 64), 'trace_id' => 'prueba-estudio']];
    }

    public function test_estudio_se_inserta_sin_modificar_el_cuestionario_finalizado_y_se_lee_en_contexto(): void
    {
        $usuario = $this->prepararEstudiante();
        $perfil = $this->perfilFinal();
        $antes = $perfil->refresh()->getAttributes();
        // Reproduce la prohibición institucional en la base auxiliar, sin tocar PostgreSQL.
        DB::unprepared("CREATE TRIGGER prueba_historico BEFORE UPDATE ON orientacion_actividades WHEN OLD.estado = 'finalizado' BEGIN SELECT RAISE(ABORT, 'No sobrescribir prueba finalizada'); END");
        Http::fake(['*/api/v2/analysis' => Http::response($this->estudio(), 200, ['X-Trace-Id' => 'prueba-estudio'])]);

        $this->postJson('/estudiante/intereses/analizar')->assertOk()->assertJsonPath('redirect', route('estudiante.analisis').'#mi-analisis');

        $this->assertSame($antes, $perfil->refresh()->getAttributes());
        $contexto = app(StudentOrientationService::class)->context($usuario);
        $this->assertNotSame($perfil->id, $contexto['activity']->id);
        $this->assertSame($perfil->riasec_public, $contexto['activity']->riasec_public);
        $this->assertSame($perfil->riasec_score, $contexto['activity']->riasec_score);
        $this->assertSame($perfil->riasec_input_hash, $contexto['activity']->riasec_input_hash);
        $this->assertSame($perfil->finalizado_at->toDateTimeString(), $contexto['activity']->finalizado_at->toDateTimeString());
        $this->assertSame($this->estudio(), $contexto['activity']->analysis_snapshot);
        $this->assertNotNull($contexto['activity']->analysis_completed_at);
        $this->assertNull($contexto['draft']);
        $this->assertSame(2, OrientacionActividad::count());
        Http::assertSentCount(1);
    }

    public function test_estudios_iguales_se_deduplican_y_estudios_con_contexto_nuevo_conservan_historial(): void
    {
        $this->prepararEstudiante();
        $perfil = $this->perfilFinal();
        $segundo = false;
        Http::fake(['*/api/v2/analysis' => function () use (&$segundo) {
            return Http::response($this->estudio($segundo ? 'b' : 'a'), 200, ['X-Trace-Id' => 'prueba-estudio']);
        }]);

        $this->postJson('/estudiante/intereses/analizar')->assertOk();
        $primero = OrientacionActividad::latest('id')->firstOrFail();
        $this->postJson('/estudiante/intereses/analizar')->assertOk();
        $this->assertSame(2, OrientacionActividad::count());
        $this->assertSame($primero->id, OrientacionActividad::latest('id')->firstOrFail()->id);
        $segundo = true;
        $this->postJson('/estudiante/intereses/analizar')->assertOk();

        $this->assertSame(3, OrientacionActividad::count());
        $this->assertNull($perfil->refresh()->analysis_snapshot);
        $this->assertSame(str_repeat('a', 64), data_get($primero->refresh()->analysis_snapshot, 'traceability.input_hash'));
        $this->assertSame(str_repeat('b', 64), data_get(OrientacionActividad::latest('id')->firstOrFail()->analysis_snapshot, 'traceability.input_hash'));
    }

    public function test_estudio_no_se_asocia_a_respuestas_nuevas_guardadas_mientras_python_responde(): void
    {
        $usuario = $this->prepararEstudiante();
        $perfil = $this->perfilFinal();
        Http::fake(['*/api/v2/analysis' => function () use ($perfil) {
            $publico = $perfil->riasec_public;
            $publico['responses'][0]['value'] = 4;
            OrientacionActividad::create(['cod_est' => 'EST_TEST', 'cod_gea' => 'GEA_TEST', 'estado' => 'finalizado',
                'riasec_public' => $publico, 'riasec_score' => $this->resultado(), 'riasec_input_hash' => hash('sha256', json_encode($publico, JSON_THROW_ON_ERROR))]);

            return Http::response($this->estudio(), 200, ['X-Trace-Id' => 'prueba-estudio']);
        }]);

        $this->postJson('/estudiante/intereses/analizar')->assertUnprocessable()->assertJsonPath('notice.title', 'Tu perfil cambió durante el análisis');

        $contexto = app(StudentOrientationService::class)->context($usuario);
        $this->assertSame(4, $contexto['activity']->riasec_public['responses'][0]['value']);
        $this->assertNull($contexto['activity']->analysis_snapshot);
        $this->assertNull($perfil->refresh()->analysis_snapshot);
        $this->assertSame(2, OrientacionActividad::count());
    }

    public function test_fallo_de_persistencia_se_distingue_del_calculo_y_preserva_la_fila_original(): void
    {
        $this->prepararEstudiante();
        $perfil = $this->perfilFinal();
        $antes = $perfil->refresh()->getAttributes();
        DB::unprepared("CREATE TRIGGER prueba_fallo_guardado BEFORE INSERT ON orientacion_actividades WHEN NEW.analysis_snapshot IS NOT NULL BEGIN SELECT RAISE(ABORT, 'Detalle SQL privado'); END");
        Http::fake(['*/api/v2/analysis' => Http::response($this->estudio(), 200, ['X-Trace-Id' => 'prueba-estudio'])]);

        $respuesta = $this->postJson('/estudiante/intereses/analizar');

        $respuesta->assertStatus(503)->assertJsonPath('notice.title', 'No pudimos guardar tu estudio')
            ->assertJsonPath('notice.message', 'El servicio calculó el estudio, pero no pudimos confirmar su guardado. Tu cuestionario y los estudios anteriores siguen conservados.')
            ->assertDontSee('Detalle SQL privado')->assertDontSee('SQLSTATE');
        $this->assertSame($antes, $perfil->refresh()->getAttributes());
        $this->assertSame(1, OrientacionActividad::count());
        Http::assertSentCount(1);
    }

    public function test_estudio_con_instrumento_institucional_asigna_nuevo_intento_sin_reutilizar_unico(): void
    {
        $this->prepararEstudiante();
        $perfil = $this->perfilFinal();
        $perfil->fill(['cod_ior' => 'IOR_PRUEBA', 'int_oac' => 3])->save();
        Schema::table('orientacion_actividades', fn (Blueprint $tabla) => $tabla->unique(['cod_est', 'cod_ior', 'int_oac']));
        Http::fake(['*/api/v2/analysis' => Http::response($this->estudio(), 200, ['X-Trace-Id' => 'prueba-estudio'])]);

        $this->postJson('/estudiante/intereses/analizar')->assertOk();

        $ultima = OrientacionActividad::latest('id')->firstOrFail();
        $this->assertSame('IOR_PRUEBA', $ultima->cod_ior);
        $this->assertSame(4, $ultima->int_oac);
        $this->assertSame(3, $perfil->refresh()->int_oac);
        $this->assertSame(2, OrientacionActividad::count());
    }

    public function test_entrada_identica_con_motor_actualizado_conserva_una_version_nueva_del_estudio(): void
    {
        $usuario = $this->prepararEstudiante();
        $this->perfilFinal();
        $versionMotor = 'motor-1';
        $trace = 'trace-original';
        Http::fake(['*/api/v2/analysis' => function () use (&$versionMotor, &$trace) {
            $estudio = $this->estudio();
            $estudio['engine_version'] = $versionMotor;
            $estudio['traceability']['engine_version'] = $versionMotor;
            $estudio['traceability']['trace_id'] = $trace;
            $estudio['trace_id'] = $trace;

            return Http::response($estudio, 200, ['X-Trace-Id' => $trace]);
        }]);
        $servicio = app(StudentOrientationService::class);
        $this->assertTrue($servicio->analyze($usuario)->available);
        $trace = 'trace-reintento';
        $reutilizado = $servicio->analyze($usuario);
        $this->assertTrue($reutilizado->available);
        $this->assertSame('trace-original', $reutilizado->traceId);
        $this->assertSame('trace-original', $reutilizado->data['trace_id']);
        $this->assertSame(2, OrientacionActividad::count());
        $versionMotor = 'motor-2';

        $this->assertTrue($servicio->analyze($usuario)->available);

        $this->assertSame(3, OrientacionActividad::count());
        $this->assertSame('motor-2', data_get(OrientacionActividad::latest('id')->firstOrFail()->analysis_snapshot, 'engine_version'));
    }

    public function test_orden_de_claves_json_anidadas_no_duplica_un_estudio_equivalente(): void
    {
        $usuario = $this->prepararEstudiante();
        $this->perfilFinal();
        $reordenar = false;
        Http::fake(['*/api/v2/analysis' => function () use (&$reordenar) {
            $estudio = $this->estudio();
            $estudio['student_snapshot']['academic_evidence'] = ['status' => 'PARTIAL', 'record_count' => 1];
            if ($reordenar) {
                $estudio['trace_id'] = 'trace-equivalente';
                $estudio['traceability']['trace_id'] = 'trace-equivalente';
                $estudio['student_snapshot']['academic_evidence'] = ['record_count' => 1, 'status' => 'PARTIAL'];
                $estudio['student_snapshot'] = array_reverse($estudio['student_snapshot'], true);
                $estudio['traceability'] = array_reverse($estudio['traceability'], true);
                $estudio = array_reverse($estudio, true);
            }

            return Http::response($estudio, 200, ['X-Trace-Id' => $estudio['trace_id']]);
        }]);
        $servicio = app(StudentOrientationService::class);
        $this->assertTrue($servicio->analyze($usuario)->available);
        $original = OrientacionActividad::latest('id')->firstOrFail();
        $reordenar = true;

        $reutilizado = $servicio->analyze($usuario);

        $this->assertTrue($reutilizado->available);
        $this->assertSame($original->analysis_snapshot, $reutilizado->data);
        $this->assertSame('prueba-estudio', $reutilizado->traceId);
        $this->assertSame($original->id, OrientacionActividad::latest('id')->firstOrFail()->id);
        $this->assertSame(2, OrientacionActividad::count());
    }
}
