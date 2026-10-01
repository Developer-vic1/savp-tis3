<?php

namespace Tests\Unit;

use App\Models\EspecialidadTecnica;
use App\Models\InstitucionProcedencia;
use App\Models\TipoVinculacionEstudiante;
use App\Support\Academico\AsignaturaInteligente;
use App\Support\Academico\CursoInteligente;
use App\Support\Academico\EspecialidadTecnicaInteligente;
use App\Support\Academico\GestionAcademicaInteligente;
use App\Support\Academico\InscripcionAcademica;
use App\Support\Academico\ParaleloInteligente;
use App\Support\Academico\PeriodoEvaluacionInteligente;
use App\Support\Academico\PlanAsignaturaInteligente;
use App\Support\Academico\TurnoInteligente;
use App\Support\Comunidad\DocenteInteligente;
use App\Support\Comunidad\InstitucionProcedenciaInteligente;
use App\Support\Comunidad\TipoVinculacionEstudianteInteligente;
use App\Support\Evaluacion\CalificacionInteligente;
use App\Support\Personas\PersonaInteligente;
use App\Support\Reportes\ReporteAcademicoInteligente;
use App\Support\Reportes\ReporteAdministrativoInteligente;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use Tests\TestCase;

/** Reglas reales con colecciones y dobles de consulta; sin conexión ni DDL. */
class SupportPreventiveTest extends TestCase
{
    public function test_docente_preserves_original_completeness_and_normalization(): void
    {
        $support = new DocenteInteligente;
        $valid = $support->analizarEspecialidad('  ciencias   naturales ');
        $invalid = $support->analizarEspecialidad(' X ');
        $this->assertSame('Ciencias Naturales', $valid['especialidad']);
        $this->assertSame(100, $valid['completitud']);
        $this->assertTrue($valid['puede_guardar']);
        $this->assertSame(30, $invalid['completitud']);
        $this->assertFalse($invalid['puede_guardar']);
        $this->assertContains('La especialidad profesional es incompleta.', $invalid['bloqueos']);
    }

    public function test_catalog_base_preserves_similarity_and_edit_exclusion(): void
    {
        $support = new InstitucionProcedenciaInteligente;
        $records = collect([new InstitucionProcedencia(['cod_ipe' => 'IPE1', 'nom_ipe' => 'Colegio San José'])]);
        $duplicate = $support->analizarDuplicidad('colegio san jose', $records);
        $this->assertTrue($duplicate['exacto']);
        $this->assertSame('IPE1', $duplicate['registro']['codigo']);
        $own = $support->analizarDuplicidad('colegio san jose', $records, 'IPE1');
        $this->assertFalse((bool) $own['exacto']);
        $this->assertNull($own['registro']);
        $this->assertSame(50, $support->completitud(['nombre' => 'Colegio'], ['nombre', 'ciudad']));
    }

    public function test_persona_normalizes_identity_contact_and_address(): void
    {
        $support = new PersonaInteligente;
        $data = $support->normalizarDatos(['nom_per' => '  ana   MARÍA ', 'ci_per' => '12.345.678', 'ema_per' => ' ANA@EXAMPLE.COM ', 'zona_per' => 'centro', 'cal_per' => 'bolivar']);
        $this->assertSame('Ana María', $data['nom_per']);
        $this->assertSame('12345678', $data['ci_per']);
        $this->assertSame('ana@example.com', $data['ema_per']);
        $this->assertStringContainsString('Bolivar', $data['dir_per']);
        $this->assertGreaterThan(0, $support->calcularCompletitud($data)['porcentaje']);
        $this->assertLessThan(100, $support->calcularCompletitud($data)['porcentaje']);
    }

    public function test_persona_contact_suggestion_is_non_blocking_and_correction_removes_it(): void
    {
        $support = new PersonaInteligente;
        $missing = $support->analizarContacto($support->normalizarDatos([]));
        $this->assertSame([], $missing['bloqueos']);
        $this->assertContains('Registra al menos un medio de contacto institucional.', $missing['sugerencias']);
        $fixed = $support->analizarContacto($support->normalizarDatos(['ema_per' => 'ana@example.com']));
        $this->assertSame([], $fixed['advertencias']);
        $this->assertSame([], $fixed['sugerencias']);
    }

    public function test_persona_future_date_blocks_but_unusual_student_age_only_warns(): void
    {
        $this->travelTo(now()->setDate(2026, 9, 30));
        $support = new PersonaInteligente;
        $future = $support->analizarEdad('2027-01-01');
        $this->assertContains('La fecha de nacimiento no puede ser futura.', $future['bloqueos']);
        $adultStudent = $support->analizarEdad('1990-01-01', 'ESTUDIANTE');
        $this->assertSame([], $adultStudent['bloqueos']);
        $this->assertNotEmpty($adultStudent['advertencias']);
        $this->assertNotEmpty($adultStudent['sugerencias']);
    }

    public function test_persona_active_duplicate_blocks_and_inactive_duplicate_recommends_recovery(): void
    {
        $support = new PersonaInteligente;
        $active = $support->analizarDuplicidad([], null, ['duplicado_ci' => true]);
        $this->assertNotEmpty($active['bloqueos']);
        $inactive = ['duplicado_ci' => true, 'persona_inactiva_recuperable' => true];
        $recovery = $support->analizarDuplicidad([], null, $inactive);
        $this->assertSame([], $recovery['bloqueos']);
        $this->assertNotEmpty($recovery['advertencias']);
        $this->assertNotEmpty($recovery['sugerencias']);
        $this->assertSame('REACTIVAR_PERSONA', $support->sugerirAccion([], $inactive)['accion']);
        $this->assertSame('USAR_EXISTENTE', $support->sugerirAccion([], ['duplicado_ci' => true])['accion']);
    }

    public function test_persona_edit_excludes_self_in_each_query_and_bounds_results(): void
    {
        Schema::shouldReceive('hasTable')->with('persona')->andReturnTrue();
        Schema::shouldReceive('hasColumn')->andReturnTrue();
        $query = Mockery::mock(Builder::class);
        DB::shouldReceive('table')->with('persona')->times(3)->andReturn($query);
        $query->shouldReceive('when')->andReturnUsing(function ($condition, $callback) use ($query) {
            if ($condition) {
                $callback($query);
            }

return $query;
        });
        $query->shouldReceive('where')->with('cod_per', '!=', 'PER_SELF')->times(3)->andReturnSelf();
        $query->shouldReceive('where')->with('ci_per', '12345678')->once()->andReturnSelf();
        $query->shouldReceive('whereRaw')->times(3)->andReturnSelf();
        $query->shouldReceive('limit')->with(5)->twice()->andReturnSelf();
        $query->shouldReceive('limit')->with(8)->once()->andReturnSelf();
        $query->shouldReceive('get')->times(3)->andReturn(collect());
        $result = (new PersonaInteligente)->buscarCoincidencias(['nom_per' => 'Ana', 'ape_pat_per' => 'Perez', 'ci_per' => '12345678', 'ema_per' => 'ana@example.com'], 'PER_SELF');
        $this->assertFalse($result['duplicado_ci']);
        $this->assertFalse($result['duplicado_correo']);
    }

    public function test_course_interpretation_blocks_ambiguity_and_keeps_technical_context(): void
    {
        $this->assertFalse(CursoInteligente::interpretar('1ro y 2do secundaria')['valido']);
        $this->assertFalse(CursoInteligente::desdeOrden(7)['valido']);
        $technical = CursoInteligente::interpretar('cuarto secundaria');
        $this->assertTrue($technical['valido']);
        $this->assertSame(4, $technical['orden']);
        $this->assertTrue($technical['requiere_plan_especialidad']);
        $this->assertNotEmpty($technical['relaciones_esperadas']);
    }

    public function test_asignatura_detects_existing_subject_and_rejects_empty_input(): void
    {
        $result = AsignaturaInteligente::interpretar('Matemática', [['cod_asi' => 'ASI1', 'nom_asi' => 'Matemática', 'sig_asi' => 'MAT']]);
        $this->assertTrue($result['duplicado']);
        $this->assertNotEmpty($result['coincidencias']);
        $this->assertFalse(AsignaturaInteligente::interpretar('')['valido']);
    }

    public function test_paralelo_distinguishes_active_duplicate_and_recoverable_inactive(): void
    {
        $active = ParaleloInteligente::interpretar('A', [['cod_par' => 'PAR1', 'nom_par' => 'A', 'est_par' => 'ACTIVO']]);
        $inactive = ParaleloInteligente::interpretar('A', [['cod_par' => 'PAR1', 'nom_par' => 'A', 'est_par' => 'INACTIVO']]);
        $this->assertSame('DUPLICADO_ACTIVO', $active['estado_inteligente']);
        $this->assertFalse($active['puede_crear']);
        $this->assertSame('DUPLICADO_INACTIVO', $inactive['estado_inteligente']);
        $this->assertFalse($inactive['puede_crear']);
    }

    public function test_gestion_invalid_range_blocks_while_suggestions_remain_distinct(): void
    {
        $support = new GestionAcademicaInteligente;
        $reversed = $support->analizarRangoGestion(2026, '2026-12-01', '2026-02-01');
        $this->assertContains('La fecha de cierre debe ser posterior a la fecha de inicio.', $reversed['bloqueos']);
        $suggested = $support->sugerirPeriodosEvaluacion(2026);
        $this->assertCount(3, $suggested);
        $this->assertNotEmpty($support->sugerirFechasGestion(2026));
    }

    public function test_turno_template_outside_actual_year_range_blocks_with_suggestion(): void
    {
        $result = (new TurnoInteligente)->validarPlantillaContraGestion(['fec_ini_pho' => '2026-01-01', 'fec_fin_pho' => '2026-12-30', 'tip_pho' => 'REGULAR'], (object) ['fii_gea' => '2026-02-01', 'ffi_gea' => '2026-12-01']);
        $this->assertNotEmpty($result['bloqueos']);
        $this->assertNotEmpty($result['sugerencias']);
    }

    public function test_inscripcion_document_deadline_respects_actual_gestion_and_inscription(): void
    {
        $this->travelTo(now()->setDate(2026, 9, 30));
        $support = Mockery::mock(InscripcionAcademica::class)->makePartial();
        $support->shouldReceive('gestionTrabajo')->andReturn(['inicio' => '2026-02-01', 'fin' => '2026-12-01']);
        $invalid = $support->validarFechaLimiteDocumento('2026-12-15', '2026-09-30');
        $valid = $support->validarFechaLimiteDocumento('2026-10-15', '2026-09-30');
        $this->assertFalse($invalid['valida']);
        $this->assertNotEmpty($invalid['bloqueos']);
        $this->assertTrue($valid['valida']);
    }

    public function test_plan_missing_context_blocks_without_querying_database(): void
    {
        DB::shouldReceive('select')->never();
        $result = (new PlanAsignaturaInteligente)->analizar(['hor_pas' => 41]);
        $this->assertFalse($result['puede_guardar']);
        $this->assertCount(6, $result['faltantes']);
        $this->assertCount(2, $result['bloqueos']);
    }

    public static function catalogRules(): array
    {
        return [
            [EspecialidadTecnicaInteligente::class, EspecialidadTecnica::class, ['nom_esp' => 'Sistemas'], 'des_esp'],
            [InstitucionProcedenciaInteligente::class, InstitucionProcedencia::class, ['nom_ipe' => 'Colegio Central', 'ciu_ipe' => 'La Paz'], 'tip_ipe'],
            [TipoVinculacionEstudianteInteligente::class, TipoVinculacionEstudiante::class, ['nom_tve' => 'Regular'], 'des_tve'],
        ];
    }

    #[DataProvider('catalogRules')]
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_catalogs_keep_non_blocking_generated_guidance(string $supportClass, string $modelClass, array $data, string $field): void
    {
        // Sustituye únicamente la consulta del catálogo; ejecuta las reglas reales.
        $model = Mockery::mock('alias:'.$modelClass);
        $model->shouldReceive('all')->once()->andReturn(collect());
        $result = (new $supportClass)->analizar($data);
        $this->assertTrue($result['puede_guardar']);
        $this->assertNotEmpty($result['datos'][$field]);
        $this->assertSame([], $result['bloqueos']);
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_period_order_collision_preserves_catalog_suggestions(): void
    {
        $model = Mockery::mock('alias:App\Models\PeriodoEvaluacion');
        $model->shouldReceive('all')->andReturn(collect());
        $query = Mockery::mock();
        $model->shouldReceive('query')->andReturn($query);
        $query->shouldReceive('when', 'where')->andReturnSelf();
        $query->shouldReceive('exists')->andReturnTrue();
        $result = (new PeriodoEvaluacionInteligente)->analizar(['nom_pev' => 'Primer Trimestre', 'ord_pev' => 1]);
        $this->assertFalse($result['puede_guardar']);
        $this->assertContains('El orden seleccionado ya está asignado a otro periodo.', $result['bloqueos']);
        $this->assertCount(3, $result['sugerencias']);
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_calificacion_range_duplicate_and_observation_use_local_rules(): void
    {
        $model = Mockery::mock('alias:App\Models\Calificacion');
        $query = Mockery::mock();
        $model->shouldReceive('query')->andReturn($query);
        $query->shouldReceive('when', 'where')->andReturnSelf();
        $query->shouldReceive('exists')->andReturnTrue();
        Schema::shouldReceive('hasColumn')->with('calificacion', 'cod_pas')->andReturnTrue();
        $result = (new CalificacionInteligente)->analizar(['cod_est' => 'EST1', 'cod_asi' => 'ASI1', 'cod_pas' => 'PAS1', 'cod_pev' => 'PEV1', 'not_cal' => 101]);
        $this->assertFalse($result['puede_guardar']);
        $this->assertCount(2, $result['bloqueos']);
        $this->assertTrue($result['duplicado']);
        $this->assertNotEmpty($result['datos']['obs_cal']);
    }

    public function test_report_support_keeps_quality_alerts_and_local_orientation(): void
    {
        $quality = (new ReporteAdministrativoInteligente)->diagnostico(['personas' => 10, 'inscripciones' => 0, 'docentes' => 0, 'cursos' => 0]);
        $this->assertSame('Requiere atención', $quality['estado']);
        $this->assertSame(25, $quality['completitud']);
        $this->assertContains('inscripciones', $quality['advertencias']);
        $academic = new ReporteAcademicoInteligente;
        $this->assertSame('En riesgo', $academic->clasificar(50));
        $this->assertContains('Informática', $academic->orientacionPorEspecialidad('Sistemas')[1]);
    }
}
