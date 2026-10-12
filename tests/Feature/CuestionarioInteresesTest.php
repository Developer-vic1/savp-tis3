<?php

namespace Tests\Feature;

use App\Models\Oficial\AporteAcademicoVocacional\OrientacionActividad;
use App\Models\Oficial\AulaVirtual\ClaseVirtual;
use App\Models\Oficial\Sistema\User;
use App\Services\AporteIngenieril\AporteIngenierilClient;
use App\Services\AporteIngenieril\DTO\AporteResponse;
use App\Services\AporteIngenieril\StudentOrientationService;
use App\Services\AulaVirtual\CursoVirtualService;
use App\Support\Orientacion\ExperienciaIntereses;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Mockery\MockInterface;
use Tests\OrientationFixture;
use Tests\TestCase;

class CuestionarioInteresesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('Estas pruebas requieren PDO SQLite en memoria.');
        }
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        // La instalación auxiliar del repositorio reproduce el esquema previo en SQLite.
        // Las correcciones institucionales PostgreSQL no forman parte de esta fixture.
        $rutas = array_values(array_filter(glob(database_path('migrations/*.php')), fn ($ruta) => basename($ruta) < '2026_10_04'));
        $this->artisan('migrate', ['--force' => true, '--path' => $rutas, '--realpath' => true])->assertExitCode(0);
        // La relación vigente usa inscripción; la fixture auxiliar todavía conserva cod_est.
        Schema::table('calificacion', fn (Blueprint $tabla) => $tabla->string('cod_ins')->nullable());
        Schema::table('clase_virtual', fn (Blueprint $tabla) => $tabla->string('cod_pes')->nullable());
        Schema::create('grupo_academico', function (Blueprint $tabla) {
            $tabla->string('cod_gac')->primary();
            $tabla->string('cod_gea');
        });
        foreach (['plan_asignatura', 'plan_especialidad'] as $plan) {
            Schema::table($plan, fn (Blueprint $tabla) => $tabla->string('cod_gac')->nullable());
        }
        Http::preventStrayRequests();
        config(['services.aporte_ingenieril.enabled' => true, 'services.aporte_ingenieril.url' => 'http://127.0.0.1:8001',
            'services.aporte_ingenieril.key' => 'test-only-internal-api-key-32-characters']);
    }

    private function respuestas(): array
    {
        return ['instrument_version' => '2.0-es-2025', 'responses' => array_map(fn ($id) => ['item_id' => $id, 'value' => 3], range(1, 30))];
    }

    private function resultado(): array
    {
        return ['schema_version' => '2.0', 'instrument_version' => '2.0-es-2025', 'trace_id' => 'prueba-score',
            'scores' => array_fill_keys(str_split('RIASEC'), 10), 'top_codes' => str_split('RIASEC'), 'holland_code' => 'RIA', 'limitations' => []];
    }

    private function aislarConsultaDeCursos(): void
    {
        $this->partialMock(CursoVirtualService::class, function (MockInterface $mock) {
            $mock->shouldReceive('studentQuery')->andReturnUsing(fn () => ClaseVirtual::query());
        });
    }

    private function habilitarEvidenciaAcademica(): void
    {
        DB::table('grupo_academico')->insert(['cod_gac' => 'GAC_TEST', 'cod_gea' => 'GEA_TEST']);
        DB::table('plan_asignatura')->where('cod_pas', 'PAS_TEST')->update(['cod_gac' => 'GAC_TEST']);
        DB::table('calificacion')->where('cod_cal', 'CAL_TEST')->update(['cod_ins' => 'INS_TEST', 'est_cal' => 'VIGENTE']);
    }

    public function test_json_rechaza_identificador_fuera_del_instrumento_antes_de_red(): void
    {
        $this->actingAs(OrientationFixture::create());
        $respuestas = array_fill(1, 30, 3);
        unset($respuestas[30]);
        $respuestas[31] = 3;

        $this->postJson('/estudiante/intereses', ['instrument_version' => '2.0-es-2025', 'responses' => $respuestas])
            ->assertUnprocessable()->assertJsonValidationErrors('responses');
        Http::assertNothingSent();
        $this->assertSame(0, OrientacionActividad::count());
    }

    public function test_json_rechaza_identidad_ajena_antes_de_red(): void
    {
        $this->actingAs(OrientationFixture::create());
        $this->postJson('/estudiante/intereses', [
            'instrument_version' => '2.0-es-2025', 'responses' => array_fill(1, 30, 3), 'student_id' => 'EST_OTRO',
        ])->assertUnprocessable()->assertJsonValidationErrors('student_id');
        Http::assertNothingSent();
    }

    public function test_permiso_revocado_impide_guardar_respuestas_y_generar_analisis(): void
    {
        $usuario = OrientationFixture::create();
        $usuario->roles->first()->revokePermissionTo('Orientacion_Academica_Profesional');
        $this->actingAs($usuario);

        $this->postJson('/estudiante/intereses', ['instrument_version' => '2.0-es-2025', 'responses' => array_fill(1, 30, 3)])->assertForbidden();
        $this->postJson('/estudiante/intereses/analizar')->assertForbidden();
        $this->get('/estudiante/analisis')->assertForbidden();
        Http::assertNothingSent();
    }

    public function test_cuestionario_historico_no_habilita_analisis_ni_se_sobrescribe(): void
    {
        $usuario = OrientationFixture::create();
        $this->actingAs($usuario);
        // Las reglas de membresía temporal pertenecen a pruebas de Aula Virtual.
        $this->aislarConsultaDeCursos();
        // Evidencia académica existente en la fixture; el bloqueo debe proceder de RIASEC.
        $this->habilitarEvidenciaAcademica();
        $actividad = OrientacionActividad::create([
            'cod_est' => 'EST_TEST', 'cod_gea' => 'GEA_TEST', 'estado' => 'finalizado', 'avance' => 100,
            'riasec_public' => ['instrument_id' => 'onet-mini-ip-2.0-es', 'version' => '2.0-es-2025', 'responses' => array_map(fn ($id) => ['item_id' => $id, 'value' => 2], range(1, 30))],
            'riasec_score' => ['raw_scores' => array_fill_keys(str_split('RIASEC'), 10), 'holland_code' => 'RIA'],
        ]);
        $anterior = $actividad->riasec_score;

        $contexto = app(StudentOrientationService::class)->context($usuario);
        $this->assertArrayNotHasKey('riasec_public', $contexto['payload']);
        $this->assertArrayHasKey('academic', $contexto['payload']);
        $this->assertFalse($contexto['precheck']['ready']);
        $this->postJson('/estudiante/intereses/analizar')->assertUnprocessable()->assertJsonPath('notice.title', 'Tu análisis necesita información adicional');
        Http::assertNothingSent();
        $this->assertSame($anterior, $actividad->refresh()->riasec_score);
        $this->assertNull($actividad->analysis_snapshot);
    }

    public function test_guardado_json_persiste_contrato_vigente_y_dirige_al_estudio_separado(): void
    {
        $this->actingAs(OrientationFixture::create());
        $this->aislarConsultaDeCursos();
        Http::fake(['*/api/v2/riasec/score' => Http::response($this->resultado(), 200, ['X-Trace-Id' => 'prueba-score'])]);

        $this->postJson('/estudiante/intereses', ['instrument_version' => '2.0-es-2025', 'responses' => array_fill(1, 30, 3)])
            ->assertOk()->assertJsonPath('redirect', route('estudiante.analisis').'#mi-perfil');

        $actividad = OrientacionActividad::firstOrFail();
        $this->assertTrue(ExperienciaIntereses::perfilVigente($actividad->riasec_public, $actividad->riasec_score));
        $this->assertSame($this->respuestas(), $actividad->riasec_public);
        $this->assertSame('EST_TEST', $actividad->cod_est);
        $this->assertNull($actividad->analysis_snapshot);
        Http::assertSentCount(1);
    }

    public function test_fallo_del_servicio_al_guardar_json_preserva_perfil_y_analisis_anteriores(): void
    {
        $this->actingAs(OrientationFixture::create());
        $this->aislarConsultaDeCursos();
        $actividad = OrientacionActividad::create(['cod_est' => 'EST_TEST', 'cod_gea' => 'GEA_TEST',
            'riasec_public' => $this->respuestas(), 'riasec_score' => $this->resultado(), 'analysis_snapshot' => ['trace_id' => 'estudio-anterior']]);
        $anterior = $actividad->refresh()->getAttributes();
        Http::fake(['*/api/v2/riasec/score' => Http::response([], 503)]);

        $this->postJson('/estudiante/intereses', ['instrument_version' => '2.0-es-2025', 'responses' => array_fill(1, 30, 4)])
            ->assertStatus(503)->assertJsonPath('notice.title', 'No pudimos guardar un resultado nuevo en este momento')
            ->assertJsonPath('notice.preserved', 'Tu resultado anterior sigue guardado y mantuvimos tus respuestas en el formulario.');

        $this->assertSame(1, OrientacionActividad::count());
        $this->assertSame($anterior, $actividad->refresh()->getAttributes());
        Http::assertSentCount(1);
    }

    public function test_guardado_html_fallido_conserva_las_respuestas_en_sesion(): void
    {
        $this->actingAs(OrientationFixture::create());
        $this->aislarConsultaDeCursos();
        Http::fake(['*/api/v2/riasec/score' => Http::response([], 503)]);
        $entrada = ['instrument_version' => '2.0-es-2025', 'responses' => array_fill(1, 30, 4)];

        $this->from('/estudiante/intereses')->post('/estudiante/intereses', $entrada)
            ->assertRedirect('/estudiante/intereses')->assertSessionHasInput('responses', $entrada['responses'])
            ->assertSessionHas('orientation_notice');
        $this->assertSame(0, OrientacionActividad::count());
    }

    public function test_generacion_json_exitosa_dirige_a_la_ventana_del_estudio(): void
    {
        $usuario = OrientationFixture::create();
        $this->actingAs($usuario);
        $this->mock(StudentOrientationService::class, function (MockInterface $mock) use ($usuario) {
            $mock->shouldReceive('analyze')->once()->withArgs(fn ($actor) => $actor->is($usuario))->andReturn(new AporteResponse(true));
        });

        $this->postJson('/estudiante/intereses/analizar')->assertOk()
            ->assertJsonPath('redirect', route('estudiante.analisis').'#mi-analisis')
            ->assertSessionHas('status', 'Tu análisis está listo. Revisa los motivos y elige tu siguiente paso.');
        Http::assertNothingSent();
    }

    public function test_enlace_anterior_de_analisis_redirige_al_estudio_y_conserva_autorizacion(): void
    {
        $usuario = OrientationFixture::create();
        $this->actingAs($usuario);
        $this->aislarConsultaDeCursos();

        $this->get('/aula-virtual/mi-orientacion?section=analisis')->assertRedirect(route('estudiante.analisis'));

        $usuario->roles->first()->revokePermissionTo('Orientacion_Academica_Profesional');
        $this->get('/aula-virtual/mi-orientacion?section=analisis')->assertForbidden();
        Http::assertNothingSent();
    }

    public function test_cuestionario_y_estudio_se_renderizan_en_ventanas_independientes(): void
    {
        $usuario = OrientationFixture::create();
        $this->actingAs($usuario);
        $contexto = ['student' => null, 'activity' => null, 'payload' => [], 'precheck' => ['ready' => false, 'requirements' => []], 'storageReady' => true, 'gestiones' => []];
        $this->mock(StudentOrientationService::class, function (MockInterface $mock) use ($contexto) {
            $mock->shouldReceive('context')->twice()->andReturn($contexto);
        });
        $this->mock(AporteIngenierilClient::class, function (MockInterface $mock) {
            $mock->shouldReceive('riasecInstrument')->once()->andReturn(new AporteResponse(true, [
                'instrument_version' => '2.0-es-2025', 'items' => array_map(fn ($id) => ['item_id' => $id, 'text' => 'Actividad oficial aislada '.$id], range(1, 30)),
                'response_scale' => array_map(fn ($valor) => ['value' => $valor, 'label' => 'Opción '.$valor], range(1, 5)),
            ]));
        });

        $this->get('/estudiante/intereses')->assertOk()->assertViewIs('estudiante.orientacion.intereses')
            ->assertSee('name="responses[1]"', false)->assertSee('Actividad oficial aislada 30')
            ->assertDontSee('id="mi-analisis"', false)->assertDontSee('id="mi-trayectoria"', false);
        $this->get('/estudiante/analisis')->assertOk()->assertViewIs('estudiante.orientacion.analisis')
            ->assertSee('Tus notas por gestión y materia')->assertSee('Todavía no tienes un perfil completo')
            ->assertDontSee('type="radio"', false)->assertDontSee('Actividad oficial aislada 1')->assertDontSee('name="responses[', false);
        Http::assertNothingSent();
    }

    public function test_cuestionario_tolera_entrada_anterior_escalar_sin_marcar_respuestas(): void
    {
        $this->prepararVistaCuestionario();

        $respuesta = $this->withSession(['_old_input' => ['responses' => 'entrada-invalida']])->get('/estudiante/intereses');

        $respuesta->assertOk()->assertSee('0 de 30 respondidas')->assertSee('Actividad oficial aislada 30');
        $this->assertDoesNotMatchRegularExpression('/<input[^>]*type="radio"[^>]*\schecked(?:\s|>)/', $respuesta->getContent());
        Http::assertNothingSent();
    }

    public function test_cuestionario_descarta_valores_anidados_y_preserva_solo_opciones_validas(): void
    {
        $this->prepararVistaCuestionario();
        $respuestas = [1 => ['value' => 3], 2 => 4, 3 => '5', 4 => true, 5 => 0, 6 => 6];

        $respuesta = $this->withSession(['_old_input' => ['responses' => $respuestas]])->get('/estudiante/intereses');

        $respuesta->assertOk()->assertSee('2 de 30 respondidas');
        $this->assertMatchesRegularExpression('/<input[^>]*id="interes-2-4"[^>]*\schecked(?:\s|>)/', $respuesta->getContent());
        $this->assertMatchesRegularExpression('/<input[^>]*id="interes-3-5"[^>]*\schecked(?:\s|>)/', $respuesta->getContent());
        $this->assertSame(2, preg_match_all('/<input[^>]*type="radio"[^>]*\schecked(?:\s|>)/', $respuesta->getContent()));
        Http::assertNothingSent();
    }

    private function prepararVistaCuestionario(): void
    {
        $this->actingAs(OrientationFixture::create());
        $contexto = ['student' => null, 'activity' => null, 'payload' => [], 'precheck' => ['ready' => false, 'requirements' => []], 'storageReady' => true, 'gestiones' => []];
        $this->mock(StudentOrientationService::class, function (MockInterface $mock) use ($contexto) {
            $mock->shouldReceive('context')->once()->andReturn($contexto);
        });
        $this->mock(AporteIngenierilClient::class, function (MockInterface $mock) {
            $mock->shouldReceive('riasecInstrument')->once()->andReturn(new AporteResponse(true, [
                'instrument_version' => '2.0-es-2025', 'items' => array_map(fn ($id) => ['item_id' => $id, 'text' => 'Actividad oficial aislada '.$id], range(1, 30)),
                'response_scale' => array_map(fn ($valor) => ['value' => $valor, 'label' => 'Opción '.$valor], range(1, 5)),
            ]));
        });
    }

    public function test_mismo_hash_con_resultado_invalido_permite_recuperar_un_perfil_vigente(): void
    {
        $this->actingAs(OrientationFixture::create());
        $this->aislarConsultaDeCursos();
        $publico = $this->respuestas();
        $actividad = OrientacionActividad::create(['cod_est' => 'EST_TEST', 'cod_gea' => 'GEA_TEST', 'riasec_public' => $publico,
            'riasec_score' => ['raw_scores' => [], 'holland_code' => 'RCS'], 'riasec_input_hash' => hash('sha256', json_encode($publico, JSON_THROW_ON_ERROR))]);
        Http::fake(['*/api/v2/riasec/score' => Http::response($this->resultado(), 200, ['X-Trace-Id' => 'prueba-score'])]);

        $this->postJson('/estudiante/intereses', ['instrument_version' => '2.0-es-2025', 'responses' => array_fill(1, 30, 3)])->assertOk();

        $ultimo = OrientacionActividad::latest('id')->firstOrFail();
        $this->assertTrue(ExperienciaIntereses::perfilVigente($ultimo->riasec_public, $ultimo->riasec_score));
        $this->assertSame(['raw_scores' => [], 'holland_code' => 'RCS'], $actividad->refresh()->riasec_score);
    }

    public function test_mismo_hash_con_resultado_vigente_no_duplica_el_cuestionario(): void
    {
        $this->actingAs(OrientationFixture::create());
        $this->aislarConsultaDeCursos();
        $publico = $this->respuestas();
        $actividad = OrientacionActividad::create(['cod_est' => 'EST_TEST', 'cod_gea' => 'GEA_TEST', 'riasec_public' => $publico,
            'riasec_score' => $this->resultado(), 'riasec_input_hash' => hash('sha256', json_encode($publico, JSON_THROW_ON_ERROR))]);
        Http::fake(['*/api/v2/riasec/score' => Http::response($this->resultado(), 200, ['X-Trace-Id' => 'prueba-score'])]);

        $this->postJson('/estudiante/intereses', ['instrument_version' => '2.0-es-2025', 'responses' => array_fill(1, 30, 3)])->assertOk();

        $this->assertSame(1, OrientacionActividad::count());
        $this->assertSame($actividad->id, OrientacionActividad::latest('id')->firstOrFail()->id);
        Http::assertSentCount(1);
    }

    public function test_ultimo_perfil_de_otro_estudiante_no_se_lee_ni_impide_guardar_el_propio(): void
    {
        $usuario = OrientationFixture::create();
        $otro = User::factory()->create();
        $fila = (array) DB::table('estudiante')->where('cod_est', 'EST_TEST')->first();
        $fila['cod_est'] = 'EST_OTRO';
        $fila['cod_per'] = $otro->cod_per;
        $fila['rud_est'] = 'TEST_OTRO';
        DB::table('estudiante')->insert($fila);
        $publico = $this->respuestas();
        $ajena = OrientacionActividad::create(['cod_est' => 'EST_OTRO', 'riasec_public' => $publico,
            'riasec_score' => $this->resultado(), 'riasec_input_hash' => hash('sha256', json_encode($publico, JSON_THROW_ON_ERROR))]);
        $this->actingAs($usuario);
        $this->aislarConsultaDeCursos();
        $this->assertNull(app(StudentOrientationService::class)->context($usuario)['activity']);
        Http::fake(['*/api/v2/riasec/score' => Http::response($this->resultado(), 200, ['X-Trace-Id' => 'prueba-score'])]);

        $this->postJson('/estudiante/intereses', ['instrument_version' => '2.0-es-2025', 'responses' => array_fill(1, 30, 3)])->assertOk();

        $contexto = app(StudentOrientationService::class)->context($usuario);
        $this->assertSame('EST_TEST', $contexto['activity']->cod_est);
        $this->assertNotSame($ajena->id, $contexto['activity']->id);
        $this->assertSame('EST_OTRO', $ajena->refresh()->cod_est);
        $this->assertSame(2, OrientacionActividad::count());
    }

    public function test_estudio_json_no_disponible_preserva_cuestionario_y_snapshot(): void
    {
        $usuario = OrientationFixture::create();
        $this->actingAs($usuario);
        $this->aislarConsultaDeCursos();
        $this->habilitarEvidenciaAcademica();
        $actividad = OrientacionActividad::create(['cod_est' => 'EST_TEST', 'cod_gea' => 'GEA_TEST',
            'riasec_public' => $this->respuestas(), 'riasec_score' => $this->resultado(), 'analysis_snapshot' => ['trace_id' => 'snapshot-preservado']]);
        $anterior = $actividad->refresh()->getAttributes();
        $this->assertTrue(app(StudentOrientationService::class)->context($usuario)['precheck']['ready']);
        Http::fake(['*/api/v2/analysis' => Http::response([], 503)]);

        $this->postJson('/estudiante/intereses/analizar')->assertStatus(503)
            ->assertJsonPath('notice.title', 'No pudimos actualizar tu análisis en este momento')
            ->assertJsonPath('notice.preserved', 'Tu cuestionario RIASEC y tu último análisis siguen guardados.');

        $this->assertSame($anterior, $actividad->refresh()->getAttributes());
        Http::assertSentCount(1);
    }
}
