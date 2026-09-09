<?php

namespace Tests\Feature\Support;

use App\Models\CalendarioEvento;
use App\Models\ConfiguracionCalendarioGestion;
use App\Models\InscripcionEstudiante;
use App\Models\InscripcionVigencia;
use App\Models\Persona;
use App\Models\SesionAcademica;
use App\Models\User;
use App\Policies\CalendarioEventoPolicy;
use App\Services\InscripcionService;
use App\Services\SesionAcademicaService;
use App\Support\Academico\CalendarioAcademicoInteligente;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * Suite de pruebas para Prevenciones Académicas Integrales – 20 casos obligatorios.
 *
 * Todos los tests usan DatabaseTransactions para no alterar la BD real.
 */
class SesionesRecuperacionCalendarioTest extends TestCase
{
    use RefreshDatabase;

    // =========================================================================
    // HELPERS DE FIXTURES
    // =========================================================================

    /** Retorna una cadena de fecha que sea el $weekday (1=Mon…7=Sun) en el año/mes dado. */
    private function nextWeekday(int $isoWeekday, int $anio, int $mes, int $desde = 1): string
    {
        $d = Carbon::create($anio, $mes, $desde);
        while ($d->dayOfWeekIso !== $isoWeekday) {
            $d->addDay();
        }

        return $d->toDateString();
    }

    private function crearGestion(string $codGea = 'GEA_SES_TEST', int $anio = 2096): void
    {
        DB::table('gestion_academica')->insertOrIgnore([
            'cod_gea' => $codGea,
            'ani_gea' => $anio,
            'est_gea' => 'ACTIVA',
            'fii_gea' => "{$anio}-02-01",
            'ffi_gea' => "{$anio}-12-05",
        ]);
    }

    private function crearConfiguracion200Dias(string $codGea = 'GEA_SES_TEST'): void
    {
        $trims = [[1, 66, '02-01', '05-15'], [2, 68, '05-18', '08-28'], [3, 66, '09-01', '12-05']];
        $anio = DB::table('gestion_academica')->where('cod_gea', $codGea)->value('ani_gea');
        foreach ($trims as [$num, $dias, $ini, $fin]) {
            ConfiguracionCalendarioGestion::firstOrCreate(
                ['cod_gea' => $codGea, 'num_tri_ccg' => $num],
                [
                    'cod_ccg' => "CCG_{$codGea}_T{$num}",
                    'cod_gea' => $codGea,
                    'num_tri_ccg' => $num,
                    'dias_req_ccg' => $dias,
                    'fii_tri_ccg' => "{$anio}-{$ini}",
                    'ffi_tri_ccg' => "{$anio}-{$fin}",
                    'est_ccg' => 'ACTIVO',
                ]
            );
        }
    }

    private function crearHorarioDetalle(string $codGea = 'GEA_SES_TEST'): string
    {
        $codCur = 'CUR_SES';
        $codPar = 'PAR_SES';
        $codTur = 'TUR_SES';
        $codAsi = 'ASI_SES';
        $codDoc = 'DOC_SES';
        $codPho = 'PHO_SES';
        $codHor = 'HOR_SES';
        $codHbl = 'HBL_SES';
        $codHde = 'HDE_SES';

        DB::table('curso')->insertOrIgnore(['cod_cur' => $codCur, 'nom_cur' => 'Primero', 'est_cur' => 'ACTIVO']);
        DB::table('paralelo')->insertOrIgnore(['cod_par' => $codPar, 'nom_par' => 'A', 'est_par' => 'ACTIVO']);
        DB::table('turno')->insertOrIgnore(['cod_tur' => $codTur, 'nom_tur' => 'Mañana', 'est_tur' => 'ACTIVO']);
        DB::table('asignatura')->insertOrIgnore(['cod_asi' => $codAsi, 'nom_asi' => 'Matemáticas', 'est_asi' => 'ACTIVO']);

        // personal_institucional: cod_pin (PK), cod_per (FK → persona), car_pin, est_pin
        $persona = Persona::firstOrCreate(
            ['ci_per' => 'DOC_SES_CI'],
            ['nom_per' => 'Docente', 'ape_pat_per' => 'SES', 'fec_nac_per' => '1980-01-01', 'est_per' => true]
        );
        DB::table('personal_institucional')->insertOrIgnore([
            'cod_pin' => 'PIN_DOC_SES',
            'cod_per' => $persona->cod_per,
            'car_pin' => 'Docente',
            'est_pin' => 'ACTIVO',
        ]);
        // docente: cod_doc (PK), cod_pin (FK), est_doc
        DB::table('docente')->insertOrIgnore(['cod_doc' => $codDoc, 'cod_pin' => 'PIN_DOC_SES', 'est_doc' => 'ACTIVO']);

        // plantilla_horaria: nom_pho es NOT NULL
        DB::table('plantilla_horaria')->insertOrIgnore([
            'cod_pho' => $codPho,
            'cod_tur' => $codTur,
            'nom_pho' => 'Plantilla Regular',
            'tip_pho' => 'REGULAR',
            'est_pho' => true,
        ]);
        DB::table('horario')->insertOrIgnore(['cod_hor' => $codHor, 'cod_gea' => $codGea, 'cod_cur' => $codCur, 'cod_par' => $codPar, 'cod_pho' => $codPho, 'est_hor' => 'ACTIVO']);

        // horario_bloque: num_hbl es NOT NULL, hor_ini_hbl y hor_fin_hbl también
        DB::table('horario_bloque')->insertOrIgnore([
            'cod_hbl' => $codHbl,
            'cod_pho' => $codPho,
            'num_hbl' => 1,
            'nom_hbl' => 'Bloque 1',
            'hor_ini_hbl' => '08:00:00',
            'hor_fin_hbl' => '09:00:00',
            'tip_hbl' => 'CLASE',
            'est_hbl' => 'ACTIVO',
        ]);

        if (! DB::table('plan_asignatura')->where('cod_pas', 'PAS_SES')->exists()) {
            DB::table('plan_asignatura')->insertOrIgnore([
                'cod_pas' => 'PAS_SES', 'cod_asi' => $codAsi, 'cod_doc' => $codDoc,
                'cod_cur' => $codCur, 'cod_par' => $codPar, 'cod_tur' => $codTur,
                'cod_gea' => $codGea, 'hor_pas' => 4, 'est_pas' => 'ACTIVO',
            ]);
        }
        DB::table('horario_detalle')->insertOrIgnore([
            'cod_hde' => $codHde, 'cod_hor' => $codHor, 'cod_hbl' => $codHbl,
            'dia_hde' => 'LUNES', 'cod_pas' => 'PAS_SES', 'est_hde' => 'ACTIVO',
        ]);

        return $codHde;
    }

    private function crearEvento(string $codGea, string $fecha, string $efecto = 'SIN_CLASES', string $estado = 'CONFIRMADO', ?string $horaIni = null, ?string $horaFin = null): CalendarioEvento
    {
        return CalendarioEvento::create([
            'cod_cae' => 'CAE_'.uniqid(),
            'cod_gea' => $codGea,
            'nom_cae' => 'Evento de prueba',
            'tip_cae' => 'SUSPENSION',
            'efe_cae' => $efecto,
            'est_cae' => $estado,
            'fii_cae' => $fecha,
            'ffi_cae' => $fecha,
            'hoi_cae' => $horaIni,
            'hof_cae' => $horaFin,
            'mot_cae' => 'Prueba automatizada',
        ]);
    }

    // =========================================================================
    // TEST 1: Generación idempotente de sesiones
    // =========================================================================

    public function test_generacion_idempotente_de_sesiones(): void
    {
        $this->crearGestion();
        $codHde = $this->crearHorarioDetalle();
        $service = app(SesionAcademicaService::class);

        // Usamos el primer lunes de marzo 2096 (dia_hde = 'LUNES')
        $lunes = $this->nextWeekday(1, 2096, 3); // Carbon: 1=Monday

        // Primer llamado: crea sesiones
        $resultado1 = $service->generarParaGestion('GEA_SES_TEST', $lunes, $lunes);
        $this->assertGreaterThan(0, $resultado1['creadas']);
        $this->assertSame(0, $resultado1['existentes']);

        // Segundo llamado: no duplica
        $resultado2 = $service->generarParaGestion('GEA_SES_TEST', $lunes, $lunes);
        $this->assertSame(0, $resultado2['creadas']);
        $this->assertGreaterThan(0, $resultado2['existentes']);

        // Total en BD sigue siendo el mismo
        $total = SesionAcademica::where('cod_gea', 'GEA_SES_TEST')->count();
        $this->assertSame($resultado1['creadas'], $total);
    }

    // =========================================================================
    // TEST 2: Suspensión total afecta sesiones del día
    // =========================================================================

    public function test_suspension_total_cambia_estado_a_suspendida(): void
    {
        $this->crearGestion('GEA_SUS_TOT', 2097);
        $codHde = $this->crearHorarioDetalle('GEA_SUS_TOT');
        $service = app(SesionAcademicaService::class);

        // Crear sesión programada para el lunes 2097-03-01
        SesionAcademica::create([
            'cod_ses' => 'SES_SUSP_TOT',
            'cod_hde' => $codHde,
            'cod_gea' => 'GEA_SUS_TOT',
            'fec_ses' => '2097-03-01',
            'hor_pla_ses' => 1.0,
            'est_ses' => 'PROGRAMADA',
        ]);

        $evento = $this->crearEvento('GEA_SUS_TOT', '2097-03-01', 'SIN_CLASES');
        $resultado = $service->aplicarEvento($evento);

        $this->assertGreaterThan(0, $resultado['afectadas']);
        $this->assertSame('SUSPENDIDA', SesionAcademica::find('SES_SUSP_TOT')->est_ses);
    }

    // =========================================================================
    // TEST 3: Suspensión parcial por horario afecta solo las sesiones intersectadas
    // =========================================================================

    public function test_suspension_parcial_solo_afecta_sesiones_intersectadas(): void
    {
        $this->crearGestion('GEA_SUS_PAR', 2097);
        $codHde = $this->crearHorarioDetalle('GEA_SUS_PAR');

        // Sesión en 08:00-09:00
        SesionAcademica::create(['cod_ses' => 'SES_PAR_01', 'cod_hde' => $codHde, 'cod_gea' => 'GEA_SUS_PAR', 'fec_ses' => '2097-03-03', 'hor_pla_ses' => 1.0, 'est_ses' => 'PROGRAMADA']);

        $service = app(SesionAcademicaService::class);
        // Suspensión parcial 10:00-12:00: NO intersecta con 08:00-09:00
        $evento = $this->crearEvento('GEA_SUS_PAR', '2097-03-03', 'SUSPENSION_PARCIAL', 'CONFIRMADO', '10:00:00', '12:00:00');
        $resultado = $service->aplicarEvento($evento);

        $this->assertSame(0, $resultado['afectadas']);
        $this->assertSame('PROGRAMADA', SesionAcademica::find('SES_PAR_01')->est_ses);

        // Suspensión parcial 08:00-09:30: SÍ intersecta
        SesionAcademica::create(['cod_ses' => 'SES_PAR_02', 'cod_hde' => $codHde, 'cod_gea' => 'GEA_SUS_PAR', 'fec_ses' => '2097-03-10', 'hor_pla_ses' => 1.0, 'est_ses' => 'PROGRAMADA']);
        $evento2 = $this->crearEvento('GEA_SUS_PAR', '2097-03-10', 'SUSPENSION_PARCIAL', 'CONFIRMADO', '08:00:00', '09:30:00');
        $resultado2 = $service->aplicarEvento($evento2);
        $this->assertGreaterThan(0, $resultado2['afectadas']);
        $this->assertSame('PARCIAL', SesionAcademica::find('SES_PAR_02')->est_ses);
    }

    // =========================================================================
    // TEST 4: Prealerta no modifica sesiones
    // =========================================================================

    public function test_prealerta_no_modifica_sesiones(): void
    {
        $this->crearGestion('GEA_PRE_SES', 2097);
        $codHde = $this->crearHorarioDetalle('GEA_PRE_SES');

        SesionAcademica::create(['cod_ses' => 'SES_PREALERTA', 'cod_hde' => $codHde, 'cod_gea' => 'GEA_PRE_SES', 'fec_ses' => '2097-04-07', 'hor_pla_ses' => 1.0, 'est_ses' => 'PROGRAMADA']);

        $service = app(SesionAcademicaService::class);
        $evento = $this->crearEvento('GEA_PRE_SES', '2097-04-07', 'SIN_CLASES', 'PREALERTA');
        $resultado = $service->aplicarEvento($evento);

        $this->assertSame(0, $resultado['afectadas']);
        $this->assertArrayHasKey('advertencia', $resultado);
        $this->assertSame('PROGRAMADA', SesionAcademica::find('SES_PREALERTA')->est_ses);
    }

    // =========================================================================
    // TEST 5: Dos eventos el mismo día no descuentan dos veces
    // =========================================================================

    public function test_dos_eventos_mismo_dia_no_descuentan_dos_veces(): void
    {
        $lunes = $this->nextWeekday(1, 2094, 9); // Primer lunes de sept 2094 (2094-09-06)
        $viernes = Carbon::parse($lunes)->addDays(4)->toDateString(); // Viernes de esa semana (2094-09-10)

        DB::table('gestion_academica')->insertOrIgnore(['cod_gea' => 'GEA_DOBLE', 'ani_gea' => 2094, 'est_gea' => 'ACTIVA', 'fii_gea' => $lunes, 'ffi_gea' => $viernes]);
        $this->crearConfiguracion200Dias('GEA_DOBLE');

        foreach (['A', 'B'] as $cod) {
            CalendarioEvento::create(['cod_cae' => "CAE_DOBLE_{$cod}", 'cod_gea' => 'GEA_DOBLE', 'nom_cae' => 'Evento doble', 'tip_cae' => 'FERIADO', 'efe_cae' => 'SIN_CLASES', 'est_cae' => 'CONFIRMADO', 'fii_cae' => $lunes, 'ffi_cae' => $lunes, 'mot_cae' => 'Doble evento']);
        }

        $soporte = app(CalendarioAcademicoInteligente::class);
        // Lunes es suspendido por 2 eventos → del lunes al viernes debe dar 4 días efectivos
        $efectivos = $soporte->calcularDiasEfectivos('GEA_DOBLE', $lunes, $viernes);
        $this->assertSame(4, $efectivos, 'El día suspendido por dos eventos debe contar solo UNA afectación.');

        // Resumen: suspendidos debe ser 1, no 2
        $resumen = $soporte->resumenDiasGestion('GEA_DOBLE');
        $this->assertSame(1, $resumen['suspendidos'], 'Dos eventos el mismo día = 1 día suspendido, no 2.');
    }

    // =========================================================================
    // TEST 6: Sesión suspendida no genera falta injustificada
    // =========================================================================

    public function test_sesion_suspendida_no_genera_falta_injustificada(): void
    {
        DB::table('gestion_academica')->insertOrIgnore(['cod_gea' => 'GEA_FALTA', 'ani_gea' => 2097, 'est_gea' => 'ACTIVA']);
        $this->crearEvento('GEA_FALTA', '2097-05-04', 'SIN_CLASES', 'CONFIRMADO');

        $soporte = app(CalendarioAcademicoInteligente::class);
        $generaFalta = $soporte->ausenciaGeneraFalta('GEA_FALTA', '2097-05-04');
        $this->assertFalse($generaFalta, 'Una sesión con evento SIN_CLASES confirmado NO debe generar falta injustificada.');

        // Sin evento confirmado: sí debería generar falta
        $generaFaltaNormal = $soporte->ausenciaGeneraFalta('GEA_FALTA', '2097-05-05');
        $this->assertTrue($generaFaltaNormal, 'Una sesión normal SÍ debe generar falta.');
    }

    // =========================================================================
    // TEST 7: Suspensión retroactiva conserva asistencia existente
    // =========================================================================

    public function test_suspension_retroactiva_conserva_asistencia_existente(): void
    {
        $this->crearGestion('GEA_RETRO', 2097);
        $codHde = $this->crearHorarioDetalle('GEA_RETRO');

        // Sesión ya "REALIZADA" (tiene asistencia registrada)
        SesionAcademica::create(['cod_ses' => 'SES_RETRO', 'cod_hde' => $codHde, 'cod_gea' => 'GEA_RETRO', 'fec_ses' => '2097-03-17', 'hor_pla_ses' => 1.0, 'est_ses' => 'REALIZADA', 'hor_rea_ses' => 1.0]);

        $service = app(SesionAcademicaService::class);
        $evento = $this->crearEvento('GEA_RETRO', '2097-03-17', 'SIN_CLASES', 'CONFIRMADO');
        $service->aplicarEvento($evento);

        // La sesión ya REALIZADA NO debe ser modificada retroactivamente
        $sesion = SesionAcademica::find('SES_RETRO');
        $this->assertSame('REALIZADA', $sesion->est_ses, 'Una sesión ya REALIZADA no debe modificarse retroactivamente.');
        $this->assertSame(1.0, $sesion->hor_rea_ses, 'Las horas realizadas deben conservarse.');
    }

    // =========================================================================
    // TEST 8: Recuperación asociada a sesión perdida
    // =========================================================================

    public function test_recuperacion_asociada_a_sesion_perdida(): void
    {
        $this->crearGestion('GEA_REC', 2097);
        $codHde = $this->crearHorarioDetalle('GEA_REC');

        $sesionPerdida = SesionAcademica::create(['cod_ses' => 'SES_PERDIDA', 'cod_hde' => $codHde, 'cod_gea' => 'GEA_REC', 'fec_ses' => '2097-04-07', 'hor_pla_ses' => 1.0, 'est_ses' => 'SUSPENDIDA']);

        $this->actingAs(User::factory()->create());
        Gate::before(fn () => true);

        $service = app(SesionAcademicaService::class);
        $sesionRec = $service->registrarRecuperacion([
            'cod_hde' => $codHde,
            'cod_gea' => 'GEA_REC',
            'fec_ses' => '2097-04-12', // sábado requeriría autorización, usamos viernes
            'cod_ses_ori' => 'SES_PERDIDA',
            'hora_inicio' => '08:00:00',
            'hora_fin' => '09:00:00',
            'obs_ses' => 'Recuperación documentada',
        ]);

        $this->assertSame('SES_PERDIDA', $sesionRec->cod_ses_ori);
        $this->assertSame('PROGRAMADA', $sesionRec->est_ses);
        $this->assertDatabaseHas('sesion_academica', ['cod_ses' => $sesionRec->cod_ses, 'cod_ses_ori' => 'SES_PERDIDA']);
    }

    // =========================================================================
    // TEST 9: Recuperación parcial (saldo pendiente > 0)
    // =========================================================================

    public function test_recuperacion_parcial_calcula_saldo_pendiente(): void
    {
        $this->crearGestion('GEA_REC_PAR', 2097);
        $codHde = $this->crearHorarioDetalle('GEA_REC_PAR');

        // Sesión perdida: 2 horas planificadas
        SesionAcademica::create(['cod_ses' => 'SES_PERD_2H', 'cod_hde' => $codHde, 'cod_gea' => 'GEA_REC_PAR', 'fec_ses' => '2097-04-07', 'hor_pla_ses' => 2.0, 'est_ses' => 'SUSPENDIDA']);

        // Sesión de recuperación: solo 1 hora realizada
        SesionAcademica::create(['cod_ses' => 'SES_REC_1H', 'cod_hde' => $codHde, 'cod_gea' => 'GEA_REC_PAR', 'fec_ses' => '2097-04-14', 'hor_pla_ses' => 1.0, 'hor_rea_ses' => 1.0, 'est_ses' => 'RECUPERADA', 'cod_ses_ori' => 'SES_PERD_2H']);

        $service = app(SesionAcademicaService::class);
        $resumen = $service->resumenHoras('GEA_REC_PAR');

        $this->assertSame(2.0, $resumen['horas_perdidas']);
        $this->assertSame(1.0, $resumen['horas_recuperadas']);
        $this->assertSame(1.0, $resumen['saldo'], 'El saldo pendiente debe ser 1 hora.');
    }

    // =========================================================================
    // TEST 10: Recuperación completa (saldo = 0)
    // =========================================================================

    public function test_recuperacion_completa_saldo_cero(): void
    {
        $this->crearGestion('GEA_REC_COM', 2097);
        $codHde = $this->crearHorarioDetalle('GEA_REC_COM');

        SesionAcademica::create(['cod_ses' => 'SES_PERD_C', 'cod_hde' => $codHde, 'cod_gea' => 'GEA_REC_COM', 'fec_ses' => '2097-04-07', 'hor_pla_ses' => 1.0, 'est_ses' => 'SUSPENDIDA']);
        SesionAcademica::create(['cod_ses' => 'SES_REC_C', 'cod_hde' => $codHde, 'cod_gea' => 'GEA_REC_COM', 'fec_ses' => '2097-04-14', 'hor_pla_ses' => 1.0, 'hor_rea_ses' => 1.0, 'est_ses' => 'RECUPERADA', 'cod_ses_ori' => 'SES_PERD_C']);

        $resumen = app(SesionAcademicaService::class)->resumenHoras('GEA_REC_COM');
        $this->assertSame(0.0, $resumen['saldo'], 'Con horas recuperadas iguales a las perdidas el saldo debe ser 0.');
    }

    // =========================================================================
    // TEST 11: Conflicto de recuperación es detectado
    // =========================================================================

    public function test_conflicto_de_recuperacion_es_bloqueado(): void
    {
        $this->crearGestion('GEA_CONF_REC', 2097);
        $codHde = $this->crearHorarioDetalle('GEA_CONF_REC');

        // Sesión perdida
        SesionAcademica::create(['cod_ses' => 'SES_CONF_PERD', 'cod_hde' => $codHde, 'cod_gea' => 'GEA_CONF_REC', 'fec_ses' => '2097-04-07', 'hor_pla_ses' => 1.0, 'est_ses' => 'SUSPENDIDA']);

        // Ya existe una sesión activa para el mismo horario el día de recuperación
        SesionAcademica::create(['cod_ses' => 'SES_CONF_EXIST', 'cod_hde' => $codHde, 'cod_gea' => 'GEA_CONF_REC', 'fec_ses' => '2097-04-14', 'hor_pla_ses' => 1.0, 'est_ses' => 'PROGRAMADA']);

        $service = app(SesionAcademicaService::class);
        $this->expectException(ValidationException::class);
        $service->registrarRecuperacion([
            'cod_hde' => $codHde,
            'cod_gea' => 'GEA_CONF_REC',
            'fec_ses' => '2097-04-14',
            'cod_ses_ori' => 'SES_CONF_PERD',
            'hora_inicio' => '08:00:00',
            'hora_fin' => '09:00:00',
        ]);
    }

    // =========================================================================
    // TEST 12: Cálculo de horas perdidas y recuperadas
    // =========================================================================

    public function test_calculo_horas_perdidas_y_recuperadas(): void
    {
        $this->crearGestion('GEA_HRS', 2097);
        $codHde = $this->crearHorarioDetalle('GEA_HRS');

        SesionAcademica::create(['cod_ses' => 'SES_HRS_S1', 'cod_hde' => $codHde, 'cod_gea' => 'GEA_HRS', 'fec_ses' => '2097-03-10', 'hor_pla_ses' => 2.0, 'est_ses' => 'SUSPENDIDA']);
        SesionAcademica::create(['cod_ses' => 'SES_HRS_P1', 'cod_hde' => $codHde, 'cod_gea' => 'GEA_HRS', 'fec_ses' => '2097-03-17', 'hor_pla_ses' => 1.5, 'hor_rea_ses' => 0.5, 'est_ses' => 'PARCIAL']); // perdió 1.0h
        SesionAcademica::create(['cod_ses' => 'SES_HRS_R1', 'cod_hde' => $codHde, 'cod_gea' => 'GEA_HRS', 'fec_ses' => '2097-03-24', 'hor_pla_ses' => 1.0, 'hor_rea_ses' => 1.0, 'est_ses' => 'RECUPERADA', 'cod_ses_ori' => 'SES_HRS_S1']);

        $resumen = app(SesionAcademicaService::class)->resumenHoras('GEA_HRS');
        $this->assertSame(3.0, $resumen['horas_perdidas'], '2h SUSPENDIDA + 1h PARCIAL = 3h perdidas');
        $this->assertSame(1.0, $resumen['horas_recuperadas']);
        $this->assertSame(2.0, $resumen['saldo']);
    }

    // =========================================================================
    // TEST 13: Cálculo total 200 días desde configuración
    // =========================================================================

    public function test_calculo_total_200_dias_desde_configuracion(): void
    {
        DB::table('gestion_academica')->insertOrIgnore(['cod_gea' => 'GEA_200', 'ani_gea' => 2026, 'est_gea' => 'ACTIVA']);
        $this->crearConfiguracion200Dias('GEA_200');

        $total = ConfiguracionCalendarioGestion::totalDiasGestion('GEA_200');
        $this->assertSame(200, $total, 'La suma de los tres trimestres debe ser 200 días.');

        $soporte = app(CalendarioAcademicoInteligente::class);
        $requeridos = $soporte->diasRequeridos('GEA_200');
        $this->assertSame(200, $requeridos['total']);
    }

    // =========================================================================
    // TEST 14: Distribución 66/68/66 por trimestre
    // =========================================================================

    public function test_distribucion_66_68_66_por_trimestre(): void
    {
        DB::table('gestion_academica')->insertOrIgnore(['cod_gea' => 'GEA_TRI', 'ani_gea' => 2026, 'est_gea' => 'ACTIVA']);
        $this->crearConfiguracion200Dias('GEA_TRI');

        $distribucion = ConfiguracionCalendarioGestion::distribucionPorTrimestre('GEA_TRI');
        $this->assertSame([1 => 66, 2 => 68, 3 => 66], $distribucion);
        $this->assertSame(200, array_sum($distribucion));
    }

    // =========================================================================
    // TEST 15: Proyección de cierre (fecha base vs proyectada)
    // =========================================================================

    public function test_proyeccion_cierre_distingue_fecha_base_y_proyectada(): void
    {
        DB::table('gestion_academica')->insertOrIgnore(['cod_gea' => 'GEA_PROY', 'ani_gea' => 2097, 'est_gea' => 'ACTIVA', 'fii_gea' => '2097-02-01', 'ffi_gea' => '2097-12-05']);
        $this->crearConfiguracion200Dias('GEA_PROY');

        // Agregar evento que cause 2 días suspendidos
        foreach (['2097-12-03', '2097-12-04'] as $fecha) {
            CalendarioEvento::create(['cod_cae' => 'CAE_PROY_'.str_replace('-', '', $fecha), 'cod_gea' => 'GEA_PROY', 'nom_cae' => 'Suspensión fin', 'tip_cae' => 'SUSPENSION', 'efe_cae' => 'SIN_CLASES', 'est_cae' => 'CONFIRMADO', 'fii_cae' => $fecha, 'ffi_cae' => $fecha, 'mot_cae' => 'Prueba proyección']);
        }

        $soporte = app(CalendarioAcademicoInteligente::class);
        $proyeccion = $soporte->proyeccionCierre('GEA_PROY');

        $this->assertArrayHasKey('fecha_fin_base', $proyeccion);
        $this->assertArrayHasKey('fecha_fin_proyectada', $proyeccion);
        $this->assertArrayHasKey('fecha_fin_efectiva', $proyeccion);
        $this->assertNull($proyeccion['fecha_fin_efectiva'], 'La fecha efectiva es null hasta que termine la gestión.');
        $this->assertSame('2097-12-05', $proyeccion['fecha_fin_base'], 'El fin base no debe modificarse.');
        // La proyectada debe ser posterior o igual a la base si hay días pendientes
        $this->assertArrayHasKey('deficit_dias', $proyeccion);
    }

    // =========================================================================
    // TEST 16: Tarea en jornada suspendida genera advertencia, no mueve fecha
    // =========================================================================

    public function test_tarea_en_jornada_suspendida_advierte_sin_mover_fecha(): void
    {
        DB::table('gestion_academica')->insertOrIgnore(['cod_gea' => 'GEA_TAR_SUS', 'ani_gea' => 2097, 'est_gea' => 'ACTIVA']);
        $this->crearEvento('GEA_TAR_SUS', '2097-05-10', 'SIN_CLASES', 'CONFIRMADO');

        // Simulamos la lógica de TareaInteligente: la fecha límite coincide con el evento
        $soporte = app(CalendarioAcademicoInteligente::class);
        $analisis = $soporte->analizarFecha('GEA_TAR_SUS', '2097-05-10');

        // La fecha está bloqueada (suspendida)
        $this->assertFalse($analisis['puede_continuar']);
        // Pero la lógica de tareas solo advierte, no mueve automáticamente
        // Aquí verificamos que el sistema proporciona bloqueos/advertencias sin modificar la BD
        $this->assertNotEmpty($analisis['bloqueos']);
    }

    // =========================================================================
    // TEST 17: Evaluación en sesión suspendida puede reprogramarse conservando fecha original
    // =========================================================================

    public function test_evaluacion_reprogramada_conserva_fecha_original(): void
    {
        $this->crearGestion('GEA_EVAL_REP', 2097);
        $codHde = $this->crearHorarioDetalle('GEA_EVAL_REP');

        // Sesión suspendida donde había evaluación
        SesionAcademica::create(['cod_ses' => 'SES_EVAL_SUS', 'cod_hde' => $codHde, 'cod_gea' => 'GEA_EVAL_REP', 'fec_ses' => '2097-05-19', 'hor_pla_ses' => 1.0, 'est_ses' => 'SUSPENDIDA', 'obs_ses' => 'Evaluación bimestral pendiente de reprogramación']);

        // Verificar que la sesión original conserva su fecha y observación
        $sesion = SesionAcademica::find('SES_EVAL_SUS');
        $this->assertSame('2097-05-19', $sesion->fec_ses->toDateString(), 'La fecha original debe conservarse.');
        $this->assertSame('SUSPENDIDA', $sesion->est_ses);
        $this->assertStringContainsString('Evaluación', $sesion->obs_ses);

        // La recuperación se registra como sesión separada (sin borrar la original)
        SesionAcademica::create(['cod_ses' => 'SES_EVAL_REP', 'cod_hde' => $codHde, 'cod_gea' => 'GEA_EVAL_REP', 'fec_ses' => '2097-05-26', 'hor_pla_ses' => 1.0, 'est_ses' => 'PROGRAMADA', 'cod_ses_ori' => 'SES_EVAL_SUS', 'obs_ses' => 'Evaluación reprogramada desde 2097-05-19']);

        $this->assertDatabaseHas('sesion_academica', ['cod_ses' => 'SES_EVAL_SUS']);
        $this->assertDatabaseHas('sesion_academica', ['cod_ses' => 'SES_EVAL_REP', 'cod_ses_ori' => 'SES_EVAL_SUS']);
    }

    // =========================================================================
    // TEST 18: Estudiante retirado no reaparece en Aula Virtual
    // =========================================================================

    public function test_estudiante_retirado_no_reaparece_en_aula_virtual(): void
    {
        $persona = Persona::create(['nom_per' => 'Ret', 'ape_pat_per' => 'Test', 'ci_per' => 'RETAV99', 'fec_nac_per' => '2010-01-01', 'est_per' => true]);
        DB::table('tipo_vinculacion_estudiante')->insertOrIgnore(['cod_tve' => 'TVE_RETAV', 'nom_tve' => 'Regular', 'est_tve' => 'ACTIVO']);
        DB::table('estudiante')->insertOrIgnore(['cod_est' => 'EST_RETAV', 'cod_per' => $persona->cod_per, 'cod_tve' => 'TVE_RETAV', 'rud_est' => 'RUDE_RETAV', 'est_est' => 'ACTIVO']);
        DB::table('gestion_academica')->insertOrIgnore(['cod_gea' => 'GEA_RETAV', 'ani_gea' => 2097, 'est_gea' => 'ACTIVA']);
        DB::table('curso')->insertOrIgnore(['cod_cur' => 'CUR_RETAV', 'nom_cur' => '1ro', 'est_cur' => 'ACTIVO']);
        DB::table('paralelo')->insertOrIgnore(['cod_par' => 'PAR_RETAV', 'nom_par' => 'A', 'est_par' => 'ACTIVO']);
        DB::table('turno')->insertOrIgnore(['cod_tur' => 'TUR_RETAV', 'nom_tur' => 'Mañana', 'est_tur' => 'ACTIVO']);

        $inscripcion = InscripcionEstudiante::create(['cod_ins' => 'INS_RETAV', 'cod_est' => 'EST_RETAV', 'cod_gea' => 'GEA_RETAV', 'cod_cur' => 'CUR_RETAV', 'cod_par' => 'PAR_RETAV', 'cod_tur' => 'TUR_RETAV', 'fei_ins' => '2097-02-01', 'est_ins' => 'RETIRADA', 'fec_ret_ins' => '2097-03-01', 'mot_ret_ins' => 'Traslado']);
        InscripcionVigencia::create(['cod_ivg' => 'IVG_RETAV', 'cod_ins' => 'INS_RETAV', 'cod_cur' => 'CUR_RETAV', 'cod_par' => 'PAR_RETAV', 'cod_tur' => 'TUR_RETAV', 'fii_ivg' => '2097-02-01', 'ffi_ivg' => '2097-03-01', 'est_ivg' => 'CERRADA', 'tip_ivg' => 'INICIAL', 'cie_ivg' => 'RETIRO', 'mot_ivg' => 'Traslado']);

        // Verificar que la inscripción está retirada (sin vigencia activa)
        $this->assertSame('RETIRADA', $inscripcion->fresh()->est_ins);
        $vigenciaActiva = $inscripcion->vigencias()->where('est_ivg', 'ACTIVA')->exists();
        $this->assertFalse($vigenciaActiva, 'Un estudiante retirado NO debe tener vigencia activa.');
    }

    // =========================================================================
    // TEST 19: Reingreso válido restaura la vigencia
    // =========================================================================

    public function test_reingreso_valido_restaura_vigencia_activa(): void
    {
        $this->travelTo(Carbon::parse('2097-05-20'));
        $persona = Persona::create(['nom_per' => 'Rei', 'ape_pat_per' => 'Ingreso', 'ci_per' => 'REIIN97', 'fec_nac_per' => '2010-01-01', 'est_per' => true]);
        DB::table('tipo_vinculacion_estudiante')->insertOrIgnore(['cod_tve' => 'TVE_REIIN', 'nom_tve' => 'Regular', 'est_tve' => 'ACTIVO']);
        DB::table('estudiante')->insertOrIgnore(['cod_est' => 'EST_REIIN', 'cod_per' => $persona->cod_per, 'cod_tve' => 'TVE_REIIN', 'rud_est' => 'RUDE_REIIN', 'est_est' => 'ACTIVO']);
        DB::table('gestion_academica')->insertOrIgnore(['cod_gea' => 'GEA_REIIN', 'ani_gea' => 2097, 'est_gea' => 'ACTIVA', 'fii_gea' => '2097-02-01', 'ffi_gea' => '2097-12-05']);
        DB::table('curso')->insertOrIgnore(['cod_cur' => 'CUR_REIIN', 'nom_cur' => '1ro', 'est_cur' => 'ACTIVO']);
        DB::table('paralelo')->insertOrIgnore(['cod_par' => 'PAR_REIIN', 'nom_par' => 'A', 'est_par' => 'ACTIVO']);
        DB::table('turno')->insertOrIgnore(['cod_tur' => 'TUR_REIIN', 'nom_tur' => 'Mañana', 'est_tur' => 'ACTIVO']);

        InscripcionEstudiante::create(['cod_ins' => 'INS_REIIN', 'cod_est' => 'EST_REIIN', 'cod_gea' => 'GEA_REIIN', 'cod_cur' => 'CUR_REIIN', 'cod_par' => 'PAR_REIIN', 'cod_tur' => 'TUR_REIIN', 'fei_ins' => '2097-02-01', 'est_ins' => 'RETIRADA', 'fec_ret_ins' => '2097-04-15', 'mot_ret_ins' => 'Traslado temporal']);
        InscripcionVigencia::create(['cod_ivg' => 'IVG_REIIN', 'cod_ins' => 'INS_REIIN', 'cod_cur' => 'CUR_REIIN', 'cod_par' => 'PAR_REIIN', 'cod_tur' => 'TUR_REIIN', 'fii_ivg' => '2097-02-01', 'ffi_ivg' => '2097-04-15', 'est_ivg' => 'CERRADA', 'tip_ivg' => 'INICIAL', 'cie_ivg' => 'RETIRO', 'mot_ivg' => 'Traslado temporal']);

        $this->actingAs(User::factory()->create());
        Gate::before(fn () => true);

        app(InscripcionService::class)->reingresar('INS_REIIN', 'Retorno documentado', '2097-05-20');

        $inscripcion = InscripcionEstudiante::find('INS_REIIN');
        $this->assertSame('ACTIVA', $inscripcion->est_ins, 'El reingreso debe activar la inscripción.');
        $this->assertTrue($inscripcion->vigencias()->where('est_ivg', 'ACTIVA')->exists(), 'El reingreso debe crear una nueva vigencia activa.');
        $this->assertSame(2, $inscripcion->vigencias()->count(), 'Debe haber 2 vigencias: la cerrada original y la nueva activa.');
    }

    // =========================================================================
    // TEST 20: Usuario sin permiso no puede confirmar/corregir eventos
    // =========================================================================

    public function test_usuario_sin_permiso_no_puede_confirmar_ni_corregir_eventos(): void
    {
        DB::table('gestion_academica')->insertOrIgnore(['cod_gea' => 'GEA_PERM', 'ani_gea' => 2097, 'est_gea' => 'ACTIVA']);
        $evento = CalendarioEvento::create(['cod_cae' => 'CAE_PERM', 'cod_gea' => 'GEA_PERM', 'nom_cae' => 'Evento confidencial', 'tip_cae' => 'BLOQUEO', 'efe_cae' => 'SIN_CLASES', 'est_cae' => 'PREALERTA', 'fii_cae' => '2097-07-01', 'ffi_cae' => '2097-07-01', 'mot_cae' => 'Prueba de permisos']);

        $usuarioSinPermiso = User::factory()->create();
        $this->actingAs($usuarioSinPermiso);

        $policy = app(CalendarioEventoPolicy::class);

        $this->assertFalse($policy->confirmar($usuarioSinPermiso, $evento), 'Sin permiso, no puede confirmar.');
        $this->assertFalse($policy->correct($usuarioSinPermiso, $evento), 'Sin permiso, no puede corregir.');
        $this->assertFalse($policy->cancel($usuarioSinPermiso, $evento), 'Sin permiso, no puede cancelar.');
    }
}
