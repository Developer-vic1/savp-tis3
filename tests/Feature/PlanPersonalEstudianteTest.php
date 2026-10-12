<?php

namespace Tests\Feature;

use App\Models\Oficial\Academico\Estudiante;
use App\Models\Oficial\Academico\Persona;
use App\Models\Oficial\Academico\SeguimientoAcademico;
use App\Models\Oficial\AporteAcademicoVocacional\OrientacionActividad;
use App\Models\Oficial\Sistema\Role;
use App\Models\Oficial\Sistema\User;
use App\Services\AporteIngenieril\AporteIngenierilClient;
use App\Services\AporteIngenieril\DTO\AporteResponse;
use App\Services\AporteIngenieril\OrientationReadiness;
use App\Services\AporteIngenieril\PlanPersonalEstudiante;
use App\Services\AporteIngenieril\StudentOrientationService;
use App\Services\AulaVirtual\CursoVirtualService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\ViewErrorBag;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

class PlanPersonalEstudianteTest extends TestCase
{
    private PlanPersonalEstudiante $servicio;

    private User $usuario;

    private OrientacionActividad $actividad;

    protected function setUp(): void
    {
        parent::setUp();
        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('Requiere SQLite en memoria.');
        }
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        Schema::create('estudiante', function (Blueprint $t) {
            $t->string('cod_est')->primary();
            $t->string('cod_per');
            $t->timestamps();
        });
        Schema::create('inscripcion_estudiante', function (Blueprint $t) {
            foreach (['cod_ins', 'cod_est', 'cod_gea', 'est_ins', 'fei_ins'] as $campo) {
                $t->string($campo);
            } $t->primary('cod_ins');
        });
        Schema::create('seguimiento_academico', function (Blueprint $t) {
            $t->string('cod_seg')->primary();
            foreach (['cod_ins', 'tip_seg', 'ori_seg', 'vis_seg', 'cod_usu_res', 'est_seg', 'fec_ape_seg'] as $campo) {
                $t->string($campo);
            } $t->text('mot_seg');
            $t->text('pro_acc_seg');
            $t->timestamps();
        });
        DB::table('estudiante')->insert(['cod_est' => 'EST1', 'cod_per' => 'PER1']);
        DB::table('inscripcion_estudiante')->insert(['cod_ins' => 'INS1', 'cod_est' => 'EST1', 'cod_gea' => 'GEA1', 'est_ins' => 'ACTIVA', 'fei_ins' => '2026-02-01']);
        $this->usuario = Mockery::mock(User::class)->makePartial();
        $this->usuario->forceFill(['cod_usu' => 'USU1', 'cod_per' => 'PER1']);
        $this->usuario->shouldReceive('can')->andReturnTrue();
        $cursos = Mockery::mock(CursoVirtualService::class);
        $cursos->shouldReceive('estudianteDeUsuario')->andReturnUsing(fn ($user) => Estudiante::where('cod_per', $user->cod_per)->first());
        $cursos->shouldReceive('dashboardEstudiante')->andReturn(['metricas' => ['actividades_pendientes' => 0, 'asignaturas' => 0, 'tareas_entregadas' => 0], 'pendientes' => collect(), 'materias' => collect()]);
        $orientacion = Mockery::mock(StudentOrientationService::class);
        $orientacion->shouldReceive('context')->andReturnUsing(fn () => ['activity' => $this->actividad, 'student' => Estudiante::find('EST1'),
            'payload' => ['academic_period' => 'GEA1', 'history' => []], 'precheck' => ['ready' => true, 'requirements' => []], 'storageReady' => true, 'draft' => null, 'gestiones' => []]);
        $this->servicio = new PlanPersonalEstudiante($cursos, $orientacion);
        $this->app->instance(CursoVirtualService::class, $cursos);
        $this->app->instance(StudentOrientationService::class, $orientacion);
        $this->app->instance(PlanPersonalEstudiante::class, $this->servicio);
        $this->actividad = new OrientacionActividad;
        $this->actividad->forceFill(['id' => 1, 'cod_est' => 'EST1', 'cod_gea' => 'GEA1', 'finalizado_at' => '2026-10-01 10:00:00',
            'riasec_public' => ['instrument_version' => '2.0-es-2025', 'responses' => array_map(fn ($id) => ['item_id' => $id, 'value' => 3], range(1, 30))],
            'riasec_score' => ['schema_version' => '2.0', 'instrument_version' => '2.0-es-2025', 'trace_id' => 'score-inicial', 'scores' => array_fill_keys(str_split('RIASEC'), 10), 'top_codes' => str_split('RIASEC'), 'holland_code' => 'RIA', 'limitations' => []],
            'riasec_input_hash' => str_repeat('a', 64), 'analysis_snapshot' => $this->estudio()]);
    }

    private function estudio(): array
    {
        $carrera = fn ($id) => ['career_id' => $id, 'career_name' => 'Carrera '.$id, 'university' => 'Universidad de prueba',
            'academic_program' => ['degree' => null, 'duration' => null, 'professional_profile' => null, 'knowledge_areas' => [], 'documented_subjects' => ['Matemática inicial'],
                'curriculum_status' => 'PARTIAL', 'curriculum_scope' => 'DOCUMENTED_INITIAL_SUBJECTS', 'curriculum_note' => 'Materias iniciales documentadas, no malla completa.', 'sources' => [['source_id' => 'fuente-prueba']]],
            'vocational_interest_relation' => ['status' => 'UNAVAILABLE'], 'technical_relation' => ['status' => 'UNAVAILABLE'],
            'preparation' => ['interpretation' => 'Revisa la evidencia disponible.', 'reinforcement_areas' => [['competency' => 'Lectura', 'rationale' => 'Hay evidencia parcial en comprensión.']]],
            'evidence_quality' => ['academic_record_count' => 1, 'distinct_subject_count' => 1, 'ordered_period_count' => 1], 'areas_without_evidence' => [], 'sources' => [], 'limitations' => []];

        return ['schema_version' => '2.0', 'trace_id' => 'estudio1', 'student_ref' => 'ref1', 'analysis_status' => 'PARTIAL',
            'student_snapshot' => array_fill_keys(array_map(fn ($s) => $s.'_evidence', ['academic', 'attendance', 'learning_activity', 'historical', 'technical', 'declared_interest']), ['status' => 'PARTIAL']),
            'career_evidence_profiles' => [$carrera('A'), $carrera('B')], 'informational_external_careers' => [], 'traceability' => ['input_hash' => str_repeat('b', 64)], 'sources_used' => [], 'limitations' => [], 'warnings' => []];
    }

    private function elegir(string $id = 'A'): array
    {
        return $this->servicio->elegir($this->usuario, ['career_id' => $id, 'meta' => 'Explorar las materias y decidir con información.', 'accion_personal' => 'Leer la malla curricular y anotar mis preguntas.', 'compromiso' => true]);
    }

    public function test_eleccion_personal_sin_indice_alto_guarda_materias_refuerzos_y_origen(): void
    {
        $plan = $this->elegir();
        $this->assertSame('A', $plan['career_id']);
        $this->assertCount(4, $plan['acciones']);
        $this->assertSame('Leer la malla curricular y anotar mis preguntas.', $plan['acciones'][0]['titulo']);
        $this->assertSame('Explorar Matemática inicial', $plan['acciones'][1]['titulo']);
        $this->assertSame('Practicar Lectura', $plan['acciones'][2]['titulo']);
        $this->assertSame('score-inicial', $plan['origen']['score_trace_id']);
        $this->assertSame(0, $this->servicio->snapshot($this->usuario)['progresoPlan']);
        $this->assertSame('PLAN_PERSONAL', SeguimientoAcademico::firstOrFail()->tip_seg);
    }

    public function test_progreso_crea_revision_sin_sobrescribir_historia(): void
    {
        $plan = $this->elegir();
        $antes = SeguimientoAcademico::firstOrFail()->pro_acc_seg;
        $this->servicio->progreso($this->usuario, ['plan_id' => $plan['id'], 'accion_id' => $plan['acciones'][0]['id'], 'completada' => true]);
        $this->assertSame(25, $this->servicio->snapshot($this->usuario)['progresoPlan']);
        $this->assertSame($antes, SeguimientoAcademico::orderBy('cod_seg')->firstOrFail()->pro_acc_seg);
        $this->assertSame(2, SeguimientoAcademico::count());
    }

    public function test_cambio_requiere_nuevo_cuestionario_y_conserva_acciones_anteriores(): void
    {
        $plan = $this->elegir();
        $this->servicio->progreso($this->usuario, ['plan_id' => $plan['id'], 'accion_id' => $plan['acciones'][0]['id'], 'completada' => true]);
        $this->servicio->solicitarCambio($this->usuario, ['career_id' => 'B', 'motivo' => 'Quiero explorar otra área por una experiencia reciente.', 'confirmar' => true]);
        $this->assertTrue($this->servicio->requiereCuestionarioNuevo($this->usuario, 'EST1', 'GEA1', 'score-inicial'));
        try {
            $this->elegir('B');
            $this->fail('Debió exigir cuestionario nuevo.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('career_id', $e->errors());
        }
        $score = $this->actividad->riasec_score;
        $score['trace_id'] = 'score-nuevo';
        $this->actividad->riasec_score = $score;
        $this->actividad->finalizado_at = now()->addSecond();
        $nuevo = $this->elegir('B');
        $snapshot = $this->servicio->snapshot($this->usuario);
        $this->assertNotSame($plan['id'], $nuevo['id']);
        $this->assertSame(0, $snapshot['progresoPlan']);
        $this->assertCount(1, $snapshot['historialPlanes']);
        $this->assertTrue($snapshot['historialPlanes'][0]['acciones'][0]['completada']);
        $this->assertSame('B', $snapshot['planPersonal']['career_id']);
    }

    public function test_carrera_inexistente_y_falta_de_compromiso_no_persisten(): void
    {
        foreach ([['career_id' => 'INVENTADA', 'meta' => 'Una meta válida para el plan', 'compromiso' => true], ['career_id' => 'A', 'meta' => 'Una meta válida para el plan', 'compromiso' => false]] as $datos) {
            try {
                $this->servicio->elegir($this->usuario, $datos);
                $this->fail('Debió validar.');
            } catch (ValidationException $e) {
                $this->assertNotEmpty($e->errors());
            }
        }
        $this->assertSame(0, SeguimientoAcademico::count());
    }

    public function test_otro_estudiante_no_lee_ni_actualiza_el_plan_ajeno(): void
    {
        $plan = $this->elegir();
        DB::table('estudiante')->insert(['cod_est' => 'EST2', 'cod_per' => 'PER2']);
        DB::table('inscripcion_estudiante')->insert(['cod_ins' => 'INS2', 'cod_est' => 'EST2', 'cod_gea' => 'GEA1', 'est_ins' => 'ACTIVA', 'fei_ins' => '2026-02-01']);
        $otro = Mockery::mock(User::class)->makePartial();
        $otro->forceFill(['cod_usu' => 'USU2', 'cod_per' => 'PER2']);
        $otro->shouldReceive('can')->andReturnTrue();
        $this->assertNull($this->servicio->snapshot($otro)['planPersonal']);
        try {
            $this->servicio->progreso($otro, ['plan_id' => $plan['id'], 'accion_id' => $plan['acciones'][0]['id'], 'completada' => true]);
            $this->fail('Debió bloquear el plan ajeno.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('plan_id', $e->errors());
        }
        $this->assertSame(1, SeguimientoAcademico::count());
    }

    public function test_cambio_sin_motivo_o_confirmacion_no_altera_el_enfoque_actual(): void
    {
        $plan = $this->elegir();
        foreach ([['career_id' => 'B', 'motivo' => '', 'confirmar' => true], ['career_id' => 'B', 'motivo' => 'Quiero revisar un enfoque distinto.', 'confirmar' => false]] as $datos) {
            try {
                $this->servicio->solicitarCambio($this->usuario, $datos);
                $this->fail('El cambio necesita motivo y confirmación.');
            } catch (ValidationException $error) {
                $this->assertNotEmpty($error->errors());
            }
        }
        $this->assertSame($plan['id'], $this->servicio->snapshot($this->usuario)['planPersonal']['id']);
        $this->assertSame(1, SeguimientoAcademico::count());
    }

    public function test_estudio_de_otra_gestion_no_habilita_eleccion_en_la_gestion_actual(): void
    {
        $this->actividad->cod_gea = 'OTRA_GESTION';
        $this->assertSame([], $this->servicio->snapshot($this->usuario)['carrerasPlan']);
        $this->expectException(ValidationException::class);
        $this->elegir();
    }

    public function test_cuestionario_nuevo_sigue_pendiente_si_trace_de_resultado_no_cambio(): void
    {
        $this->elegir();
        $this->servicio->solicitarCambio($this->usuario, ['career_id' => 'B', 'motivo' => 'Voy a revisar nuevas actividades y posibilidades.', 'confirmar' => true]);
        $this->assertFalse($this->servicio->requiereCuestionarioNuevo($this->usuario, 'EST1', 'OTRA_GESTION', 'score-inicial'));
        $this->assertFalse($this->servicio->requiereCuestionarioNuevo($this->usuario, 'EST1', 'GEA1', 'score-nuevo'));
        $this->assertTrue($this->servicio->requiereCuestionarioNuevo($this->usuario, 'EST1', 'GEA1', 'score-inicial'));
        $this->assertSame(0, $this->servicio->snapshot($this->usuario)['progresoPlan']);
    }

    public function test_vistas_plan_y_preparacion_renderizan_selector_y_enfoque_sin_seleccion_automatica(): void
    {
        $snapshot = $this->servicio->snapshot($this->usuario);
        $plantilla = file_get_contents(resource_path('views/estudiante/orientacion/plan.blade.php'));
        $plantilla = preg_replace('/@extends\([^\n]+\)|@section\((?:\x27title\x27|\x27page-title\x27)[^\n]+\)|@section\(\x27content\x27\)|@endsection/', '', $plantilla);
        $html = Blade::render($plantilla, $snapshot + ['academic' => ['metricas' => ['actividades_pendientes' => 0]], 'errors' => new ViewErrorBag]);
        $this->assertStringContainsString('plan-elegir-carrera', $html);
        $this->assertStringContainsString('El primer paso que haré esta semana', $html);
        $this->assertStringNotContainsString('<select', $html);
        $this->assertStringContainsString('name="career_id" value=""', $html);
        $this->elegir();
        $snapshot = $this->servicio->snapshot($this->usuario);
        $plantilla = file_get_contents(resource_path('views/estudiante/orientacion/preparacion.blade.php'));
        $plantilla = preg_replace('/@extends\([^\n]+\)|@section\((?:\x27title\x27|\x27page-title\x27)[^\n]+\)|@section\(\x27content\x27\)|@endsection/', '', $plantilla);
        $html = Blade::render($plantilla, $snapshot + ['academic' => ['metricas' => ['actividades_pendientes' => 0, 'asignaturas' => 0, 'tareas_entregadas' => 0], 'pendientes' => collect(), 'materias' => collect()], 'errors' => new ViewErrorBag]);
        $this->assertStringContainsString('Preparación para Carrera A', $html);
        $this->assertStringContainsString('Leer la malla curricular y anotar mis preguntas.', $html);
    }

    public function test_get_plan_y_preparacion_preservan_actor_y_permiso_existente(): void
    {
        $this->usuario->forceFill(['est_usu' => 'ACTIVO', 'email' => 'plan@example.test', 'email_verified_at' => now()]);
        $this->usuario->shouldReceive('hasRole')->andReturnUsing(fn ($rol) => $rol === 'Estudiante');
        $this->usuario->shouldReceive('checkPermissionTo')->andReturnTrue();
        $this->usuario->setRelation('persona', new Persona(['nom_per' => 'Estudiante de prueba']));
        $this->usuario->setRelation('roles', new Collection([new Role(['name' => 'Estudiante'])]));
        $this->actingAs($this->usuario);

        $this->get('/estudiante/plan')->assertOk()->assertSee('plan-elegir-carrera')->assertDontSee('<select', false);
        $this->elegir();
        $this->get('/estudiante/preparacion')->assertOk()->assertSee('Preparación para Carrera A');

        $sinPermiso = Mockery::mock(User::class)->makePartial();
        $sinPermiso->forceFill(['cod_usu' => 'USU1', 'cod_per' => 'PER1', 'est_usu' => 'ACTIVO', 'email' => 'plan@example.test', 'email_verified_at' => now()]);
        $sinPermiso->shouldReceive('hasRole')->andReturnUsing(fn ($rol) => $rol === 'Estudiante');
        $sinPermiso->shouldReceive('can')->andReturnFalse();
        $sinPermiso->shouldReceive('checkPermissionTo')->andReturnFalse();
        $this->actingAs($sinPermiso);
        $this->get('/estudiante/plan')->assertForbidden();
        $this->get('/estudiante/preparacion')->assertForbidden();
        $this->post('/estudiante/plan', [])->assertForbidden();
    }

    public function test_cambio_pendiente_muestra_cuestionario_vacio_y_preserva_perfil_anterior(): void
    {
        $this->elegir();
        $this->servicio->solicitarCambio($this->usuario, ['career_id' => 'B', 'motivo' => 'Quiero explorar nuevas actividades para otra carrera.', 'confirmar' => true]);
        $antes = $this->actividad->riasec_public;
        $this->usuario->forceFill(['est_usu' => 'ACTIVO', 'email' => 'plan@example.test', 'email_verified_at' => now()]);
        $this->usuario->shouldReceive('hasRole')->andReturnUsing(fn ($rol) => $rol === 'Estudiante');
        $this->usuario->shouldReceive('checkPermissionTo')->andReturnTrue();
        $this->usuario->setRelation('persona', new Persona(['nom_per' => 'Estudiante de prueba']));
        $this->usuario->setRelation('roles', new Collection([new Role(['name' => 'Estudiante'])]));
        $this->actingAs($this->usuario);
        $cliente = Mockery::mock(AporteIngenierilClient::class);
        $cliente->shouldReceive('riasecInstrument')->once()->andReturn(new AporteResponse(true, [
            'instrument_version' => '2.0-es-2025', 'items' => array_map(fn ($id) => ['item_id' => $id, 'text' => 'Actividad de prueba '.$id], range(1, 30)),
            'response_scale' => array_map(fn ($value) => ['value' => $value, 'label' => 'Opción '.$value], range(1, 5)),
        ]));
        $this->app->instance(AporteIngenierilClient::class, $cliente);

        $respuesta = $this->get('/estudiante/intereses');

        $respuesta->assertOk()->assertSee('Tu nuevo enfoque empieza por tus respuestas actuales')->assertSee('0 de 30 respondidas');
        $this->assertSame(0, preg_match_all('/<input[^>]*type="radio"[^>]*\schecked(?:\s|>)/', $respuesta->getContent()));
        $this->assertSame($antes, $this->actividad->riasec_public);
        $this->assertSame('A', $this->servicio->snapshot($this->usuario)['planPersonal']['career_id']);
    }

    public function test_doble_eleccion_no_reinicia_el_plan_ya_confirmado(): void
    {
        $plan = $this->elegir();
        try {
            $this->elegir();
            $this->fail('La elección confirmada no debe reiniciarse.');
        } catch (ValidationException $error) {
            $this->assertArrayHasKey('career_id', $error->errors());
        }
        $this->assertSame(1, SeguimientoAcademico::count());
        $this->assertSame($plan['id'], $this->servicio->snapshot($this->usuario)['planPersonal']['id']);
    }

    public function test_cambio_pendiente_persiste_nuevo_cuestionario_aunque_las_respuestas_sean_iguales(): void
    {
        $this->elegir();
        $this->servicio->solicitarCambio($this->usuario, ['career_id' => 'B', 'motivo' => 'Voy a explorar otro programa con nuevas actividades.', 'confirmar' => true]);
        Schema::create('orientacion_actividades', function (Blueprint $tabla) {
            $tabla->id();
            foreach (['cod_est', 'cod_gea', 'estado', 'riasec_input_hash'] as $campo) {
                $tabla->string($campo)->nullable();
            }
            $tabla->integer('avance')->default(0);
            foreach (['iniciado_at', 'finalizado_at'] as $campo) {
                $tabla->dateTime($campo)->nullable();
            }
            foreach (['riasec_public', 'riasec_score', 'analysis_snapshot'] as $campo) {
                $tabla->json($campo)->nullable();
            }
            $tabla->timestamps();
        });
        $publico = $this->actividad->riasec_public;
        $perfil = OrientacionActividad::create(['cod_est' => 'EST1', 'cod_gea' => 'GEA1', 'estado' => 'finalizado', 'avance' => 100,
            'riasec_public' => $publico, 'riasec_score' => $this->actividad->riasec_score, 'riasec_input_hash' => hash('sha256', json_encode($publico, JSON_THROW_ON_ERROR)),
            'analysis_snapshot' => $this->actividad->analysis_snapshot]);
        $score = $this->actividad->riasec_score;
        $score['trace_id'] = 'score-respuesta-nueva';
        config(['services.aporte_ingenieril.enabled' => true, 'services.aporte_ingenieril.url' => 'http://127.0.0.1:8001',
            'services.aporte_ingenieril.key' => 'test-only-internal-api-key-32-characters']);
        Http::fake(['*/api/v2/riasec/score' => Http::response($score, 200, ['X-Trace-Id' => 'score-respuesta-nueva'])]);
        $orientacion = Mockery::mock(StudentOrientationService::class, [app(CursoVirtualService::class), app(AporteIngenierilClient::class), new OrientationReadiness])->makePartial();
        $orientacion->shouldReceive('context')->andReturn(['student' => Estudiante::find('EST1'), 'storageReady' => true, 'payload' => ['academic_period' => 'GEA1'], 'activity' => $perfil]);

        $this->assertTrue($orientacion->score($this->usuario, $publico)->available);

        $this->assertSame(2, OrientacionActividad::count());
        $this->assertSame($publico, OrientacionActividad::latest('id')->firstOrFail()->riasec_public);
        $this->assertSame('score-respuesta-nueva', data_get(OrientacionActividad::latest('id')->firstOrFail()->riasec_score, 'trace_id'));
        $this->assertSame('score-inicial', data_get($perfil->refresh()->riasec_score, 'trace_id'));
        $this->assertNotNull($perfil->analysis_snapshot);
        Http::assertSentCount(1);
    }

    public function test_cancelar_cambio_conserva_acciones_progreso_y_motivo_en_historial(): void
    {
        $plan = $this->elegir();
        $this->servicio->progreso($this->usuario, ['plan_id' => $plan['id'], 'accion_id' => $plan['acciones'][0]['id'], 'completada' => true]);
        $this->servicio->solicitarCambio($this->usuario, ['career_id' => 'B', 'motivo' => 'Quiero revisar otra carrera antes de decidir.', 'confirmar' => true]);
        $solicitud = SeguimientoAcademico::orderByDesc('cod_seg')->firstOrFail();
        $this->assertTrue($this->servicio->requiereCuestionarioNuevo($this->usuario, 'EST1', 'GEA1', 'score-inicial'));

        $conservado = $this->servicio->cancelarCambio($this->usuario, ['plan_id' => $plan['id']]);

        $this->assertSame($plan['id'], $conservado['id']);
        $this->assertSame('A', $conservado['career_id']);
        $this->assertNull($conservado['cambio_pendiente']);
        $this->assertSame('Solicitud de cambio cancelada por estudiante', $conservado['motivo']);
        $this->assertSame(25, $this->servicio->snapshot($this->usuario)['progresoPlan']);
        $this->assertFalse($this->servicio->requiereCuestionarioNuevo($this->usuario, 'EST1', 'GEA1', 'score-inicial'));
        $this->assertStringContainsString('Quiero revisar otra carrera antes de decidir.', $solicitud->refresh()->pro_acc_seg);
        $this->assertSame(4, SeguimientoAcademico::count());
    }

    public function test_cancelacion_rechaza_identidad_ajena_y_plan_inexistente(): void
    {
        $plan = $this->elegir();
        $this->servicio->solicitarCambio($this->usuario, ['career_id' => 'B', 'motivo' => 'Quiero revisar otra carrera antes de decidir.', 'confirmar' => true]);
        foreach ([['plan_id' => $plan['id'], 'student_id' => 'EST2'], ['plan_id' => (string) Str::uuid()]] as $datos) {
            try {
                $this->servicio->cancelarCambio($this->usuario, $datos);
                $this->fail('No debe cancelar una solicitud ajena.');
            } catch (ValidationException $error) {
                $this->assertNotEmpty($error->errors());
            }
        }
        $this->assertNotNull($this->servicio->snapshot($this->usuario)['planPersonal']['cambio_pendiente']);
        $this->assertSame(2, SeguimientoAcademico::count());
    }

    public function test_permiso_de_plan_no_habilita_lectura_del_catalogo_de_orientacion(): void
    {
        $this->elegir();
        $usuario = Mockery::mock(User::class)->makePartial();
        $usuario->forceFill(['cod_usu' => 'USU1', 'cod_per' => 'PER1']);
        $usuario->shouldReceive('can')->andReturnUsing(fn ($permiso) => $permiso === 'Perfil_Academico');

        $snapshot = $this->servicio->snapshot($usuario);

        $this->assertNotNull($snapshot['planPersonal']);
        $this->assertSame([], $snapshot['carrerasPlan']);
        $this->assertSame(0, $snapshot['progresoPlan']);
    }

    public function test_meta_o_accion_formadas_por_espacios_no_crean_un_plan(): void
    {
        $base = ['career_id' => 'A', 'meta' => 'Revisar las materias y formular preguntas.', 'accion_personal' => 'Leer la malla curricular esta semana.', 'compromiso' => true];
        foreach (['meta', 'accion_personal'] as $campo) {
            $datos = $base;
            $datos[$campo] = str_repeat(' ', 20);
            try {
                $this->servicio->elegir($this->usuario, $datos);
                $this->fail('Un campo vacío después de normalizar no es válido.');
            } catch (ValidationException $error) {
                $this->assertArrayHasKey($campo, $error->errors());
            }
        }
        $this->assertSame(0, SeguimientoAcademico::count());
    }

    public function test_motivo_de_cambio_formado_por_espacios_no_crea_otra_revision(): void
    {
        $plan = $this->elegir();
        try {
            $this->servicio->solicitarCambio($this->usuario, ['career_id' => 'B', 'motivo' => str_repeat(' ', 20), 'confirmar' => true]);
            $this->fail('El motivo vacío después de normalizar debe rechazarse.');
        } catch (ValidationException $error) {
            $this->assertArrayHasKey('motivo', $error->errors());
        }
        $this->assertSame(1, SeguimientoAcademico::count());
        $actual = $this->servicio->snapshot($this->usuario)['planPersonal'];
        $this->assertSame($plan['id'], $actual['id']);
        $this->assertNull($actual['cambio_pendiente']);
    }

    public function test_fallo_al_guardar_revision_conserva_plan_y_avance_sin_exponer_sql(): void
    {
        $plan = $this->elegir();
        $this->servicio->progreso($this->usuario, ['plan_id' => $plan['id'], 'accion_id' => $plan['acciones'][0]['id'], 'completada' => true]);
        $antes = $this->servicio->snapshot($this->usuario)['planPersonal'];
        DB::unprepared("CREATE TRIGGER plan_guardado_falla BEFORE INSERT ON seguimiento_academico BEGIN SELECT RAISE(ABORT, 'Detalle SQL privado'); END");

        try {
            $this->servicio->progreso($this->usuario, ['plan_id' => $plan['id'], 'accion_id' => $plan['acciones'][1]['id'], 'completada' => true]);
            $this->fail('El fallo de escritura debe informarse.');
        } catch (ValidationException $error) {
            $this->assertArrayHasKey('plan', $error->errors());
            $mensaje = $error->errors()['plan'][0];
            $this->assertStringContainsString('Conservamos el plan confirmado anterior', $mensaje);
            $this->assertStringNotContainsString('SQL', $mensaje);
        }
        $snapshot = $this->servicio->snapshot($this->usuario);
        $this->assertSame($antes, $snapshot['planPersonal']);
        $this->assertSame(25, $snapshot['progresoPlan']);
        $this->assertSame(2, SeguimientoAcademico::count());
    }

    public function test_fallo_al_crear_plan_redirige_con_campos_y_error_recuperable(): void
    {
        $this->usuario->forceFill(['est_usu' => 'ACTIVO', 'email' => 'plan@example.test', 'email_verified_at' => now()]);
        $this->usuario->shouldReceive('hasRole')->andReturnUsing(fn ($rol) => $rol === 'Estudiante');
        $this->usuario->shouldReceive('checkPermissionTo')->andReturnTrue();
        $this->actingAs($this->usuario);
        DB::unprepared("CREATE TRIGGER plan_inicial_falla BEFORE INSERT ON seguimiento_academico BEGIN SELECT RAISE(ABORT, 'Detalle SQL privado'); END");
        $entrada = ['career_id' => 'A', 'meta' => 'Explorar las materias y decidir con información.', 'accion_personal' => 'Leer la malla curricular y anotar preguntas.', 'compromiso' => '1'];

        $this->from('/estudiante/plan')->post('/estudiante/plan', $entrada)->assertRedirect('/estudiante/plan')
            ->assertSessionHasErrors('plan')->assertSessionHasInput('meta', $entrada['meta'])
            ->assertSessionHasInput('accion_personal', $entrada['accion_personal'])->assertSessionHasInput('career_id', 'A');

        $this->assertSame(0, SeguimientoAcademico::count());
        $this->assertNull($this->servicio->snapshot($this->usuario)['planPersonal']);
    }
}
