<?php

namespace Tests\Feature\Support;

use App\Livewire\Admin\CalendarioAcademico;
use App\Models\CalendarioEvento;
use App\Models\InscripcionEstudiante;
use App\Models\InscripcionVigencia;
use App\Models\Persona;
use App\Models\User;
use App\Policies\CalendarioEventoPolicy;
use App\Services\CalendarioAcademicoService;
use App\Services\InscripcionService;
use App\Support\Academico\CalendarioAcademicoInteligente;
use App\Support\Academico\InscripcionAcademica;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PrevencionesIntegralesTest extends TestCase
{
    use DatabaseTransactions;

    public function test_permiso_consulta_calendario_no_autoriza_creacion(): void
    {
        $usuario = User::factory()->create();
        Permission::findOrCreate('Gestion_Academica', 'web');
        $usuario->givePermissionTo('Gestion_Academica');
        $policy = app(CalendarioEventoPolicy::class);
        $this->assertTrue($policy->viewAny($usuario));
        $this->assertFalse($policy->create($usuario));
        $this->assertFalse($policy->confirmar($usuario, new CalendarioEvento));
        $this->actingAs($usuario);
        Livewire::test(CalendarioAcademico::class)->assertOk()->call('abrir')->assertForbidden();
    }

    public function test_secretaria_solo_modifica_prealertas_futuras_y_no_elimina_historia(): void
    {
        $usuario = User::factory()->create();
        Permission::findOrCreate('Gestion_Academica', 'web');
        Role::findOrCreate('Secretaria', 'web');
        $usuario->givePermissionTo('Gestion_Academica');
        $usuario->assignRole('Secretaria');
        $policy = app(CalendarioEventoPolicy::class);
        $evento = new CalendarioEvento(['est_cae' => 'PREALERTA', 'fii_cae' => today()->addDay()->toDateString()]);
        $this->assertTrue($policy->update($usuario, $evento));
        $evento->fii_cae = today()->subDay();
        $this->assertFalse($policy->update($usuario, $evento));
        $evento->est_cae = 'CONFIRMADO';
        $this->assertFalse($policy->update($usuario, $evento));
        foreach (['confirm', 'cancel', 'correct', 'delete'] as $accion) {
            $this->assertFalse($policy->{$accion}($usuario, $evento));
        }
    }

    public function test_disposicion_superior_se_versiona_sin_sobrescribir_fechas(): void
    {
        $this->actingAs(User::factory()->create());
        Gate::before(fn () => true);
        DB::table('gestion_academica')->insert(['cod_gea' => 'GEA_NORMA', 'ani_gea' => 2095, 'est_gea' => 'ACTIVO']);
        $service = app(CalendarioAcademicoService::class);
        $datos = ['cod_gea' => 'GEA_NORMA', 'nom_cae' => 'Descanso oficial', 'tip_cae' => 'DESCANSO_PEDAGOGICO', 'efe_cae' => 'SIN_CLASES', 'est_cae' => 'CONFIRMADO', 'fii_cae' => '2095-07-01', 'ffi_cae' => '2095-07-14', 'mot_cae' => 'Disposición documentada', 'niv_cae' => 'NACIONAL'];
        $original = $service->registrarEvento($datos);
        try {
            $service->registrarEvento(array_replace($datos, ['niv_cae' => 'INSTITUCIONAL', 'cod_cae_ant' => $original->cod_cae]));
            $this->fail('Una autoridad inferior no puede superar una disposición nacional.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('cod_cae_ant', $e->errors());
        }
        $nueva = $service->registrarEvento(array_replace($datos, ['ffi_cae' => '2095-07-21', 'cod_cae_ant' => $original->cod_cae]));
        $this->assertSame('SUPERADO', $original->fresh()->est_cae);
        $this->assertSame('2095-07-14', $original->fresh()->ffi_cae->toDateString());
        $this->assertSame($original->cod_cae, $nueva->cod_cae_ant);
        $this->assertSame('CONFIRMADO', $nueva->cer_cae);
    }

    public function test_eventos_superpuestos_no_descuentan_dos_veces(): void
    {
        DB::table('gestion_academica')->insert(['cod_gea' => 'GEA_DIAS', 'ani_gea' => 2094, 'est_gea' => 'ACTIVO']);
        foreach (['A', 'B'] as $codigo) {
            CalendarioEvento::create(['cod_cae' => 'CAE_DIAS_'.$codigo, 'cod_gea' => 'GEA_DIAS', 'nom_cae' => 'Suspensión', 'tip_cae' => 'FERIADO', 'efe_cae' => 'SIN_CLASES', 'est_cae' => 'CONFIRMADO', 'fii_cae' => '2026-09-07', 'ffi_cae' => '2026-09-07', 'mot_cae' => 'Prueba de cómputo']);
        }
        $support = app(CalendarioAcademicoInteligente::class);
        $this->assertSame(4, $support->calcularDiasEfectivos('GEA_DIAS', '2026-09-07', '2026-09-11'));
        $this->assertSame('2026-09-14', $support->proyectarFechaFin('GEA_DIAS', '2026-09-07', 5));
    }

    private function inscripcion(): InscripcionEstudiante
    {
        $persona = Persona::create(['nom_per' => 'Prueba', 'ape_pat_per' => 'Prevenciones', 'ci_per' => 'PREV12345', 'fec_nac_per' => '2010-01-01', 'est_per' => true]);
        DB::table('tipo_vinculacion_estudiante')->insert(['cod_tve' => 'TVE_PREV', 'nom_tve' => 'Regular', 'est_tve' => 'ACTIVO']);
        DB::table('estudiante')->insert(['cod_est' => 'EST_PREV', 'cod_per' => $persona->cod_per, 'cod_tve' => 'TVE_PREV', 'rud_est' => 'RUDE_PREV', 'est_est' => 'ACTIVO']);
        DB::table('gestion_academica')->insert(['cod_gea' => 'GEA_PREV', 'ani_gea' => 2026, 'est_gea' => 'ACTIVA', 'fii_gea' => '2026-01-01', 'ffi_gea' => '2026-12-31']);
        DB::table('curso')->insert(['cod_cur' => 'CUR_PREV', 'nom_cur' => 'Primero', 'est_cur' => 'ACTIVO']);
        DB::table('paralelo')->insert(['cod_par' => 'PAR_PREV', 'nom_par' => 'A', 'est_par' => 'ACTIVO']);
        DB::table('turno')->insert(['cod_tur' => 'TUR_PREV', 'nom_tur' => 'Mañana', 'est_tur' => 'ACTIVO']);
        $inscripcion = InscripcionEstudiante::create(['cod_ins' => 'INS_PREV', 'cod_est' => 'EST_PREV', 'cod_gea' => 'GEA_PREV', 'cod_cur' => 'CUR_PREV', 'cod_par' => 'PAR_PREV', 'cod_tur' => 'TUR_PREV', 'fei_ins' => '2026-02-01', 'est_ins' => 'ACTIVA']);
        InscripcionVigencia::create($inscripcion->only(['cod_ins', 'cod_cur', 'cod_par', 'cod_tur']) + ['cod_ivg' => 'IVG_PREV', 'fii_ivg' => '2026-02-01', 'est_ivg' => 'ACTIVA', 'tip_ivg' => 'INICIAL']);

        return $inscripcion;
    }

    public function test_retiro_y_reingreso_conservan_historial(): void
    {
        $this->travelTo(Carbon::parse('2026-09-09'));
        $registro = $this->inscripcion();
        $this->actingAs(User::factory()->create());
        Gate::before(fn () => true);
        $servicio = app(InscripcionService::class);
        $servicio->retirar($registro->cod_ins, 'Traslado temporal', '2026-09-08');
        $this->assertDatabaseHas('inscripcion_vigencia', ['cod_ivg' => 'IVG_PREV', 'est_ivg' => 'CERRADA', 'ffi_ivg' => '2026-09-08']);
        $servicio->reingresar($registro->cod_ins, 'Retorno documentado', '2026-09-09');
        $this->assertSame(2, $registro->vigencias()->count());
        $this->assertSame('ACTIVA', $registro->fresh()->est_ins);
        $this->assertSame('2026-09-08', $registro->fresh()->fec_ret_ins->toDateString());
    }

    public function test_un_usuario_sin_permiso_no_puede_retirar(): void
    {
        $this->travelTo(Carbon::parse('2026-09-09'));
        $registro = $this->inscripcion();
        $this->actingAs(User::factory()->create());
        $this->expectException(AuthorizationException::class);
        app(InscripcionService::class)->retirar($registro->cod_ins, 'Solicitud', '2026-09-09');
    }

    public function test_bd_rechaza_vigencias_solapadas(): void
    {
        $registro = $this->inscripcion();
        $this->expectException(QueryException::class);
        DB::transaction(fn () => InscripcionVigencia::create($registro->only(['cod_ins', 'cod_cur', 'cod_par', 'cod_tur']) + ['cod_ivg' => 'IVG_SOLAPE', 'fii_ivg' => '2026-03-01', 'ffi_ivg' => '2026-03-02', 'est_ivg' => 'CERRADA', 'tip_ivg' => 'CAMBIO', 'cie_ivg' => 'CAMBIO', 'mot_ivg' => 'Error de prueba']));
    }

    public function test_retiro_utiliza_sus_campos_y_conserva_separacion_de_anulacion(): void
    {
        $datos = app(InscripcionAcademica::class)->prepararDatosRetiro('  Traslado familiar  ');
        $this->assertSame('RETIRADA', $datos['est_ins']);
        $this->assertSame('Traslado familiar', $datos['mot_ret_ins']);
        $this->assertNotNull($datos['fec_ret_ins']);
        $this->assertNull($datos['fec_anu_ins']);
        $this->assertNull($datos['mot_anu_ins']);
        $this->assertArrayNotHasKey('anulado_por', $datos);
    }

    public function test_transiciones_impiden_reactivacion_directa_y_doble_retiro(): void
    {
        $soporte = app(InscripcionAcademica::class);
        foreach ([['RETIRADA', 'RETIRADA'], ['ANULADA', 'RETIRADA'], ['RETIRADA', 'ANULADA'], ['RETIRADA', 'ACTIVA'], ['ANULADA', 'ACTIVA']] as [$origen,$destino]) {
            $this->assertFalse($soporte->transicionPermitida($origen, $destino));
        }
        $this->assertTrue($soporte->transicionPermitida('ACTIVA', 'RETIRADA'));
        $this->assertSame(['CAMBIO'], $soporte->opcionesCambio('CONFIRMADA', true));
    }

    public function test_motivo_de_espacios_se_rechaza_antes_de_operar(): void
    {
        $this->expectException(ValidationException::class);
        app(InscripcionService::class)->retirar('INS_NO_EXISTE', '   ', today()->toDateString());
    }

    public function test_prealerta_confirmacion_y_cancelacion_tienen_efectos_distintos(): void
    {
        DB::table('gestion_academica')->insert(['cod_gea' => 'GEA_PREV_TEST', 'ani_gea' => 2095, 'est_gea' => 'ACTIVO']);
        $evento = CalendarioEvento::create(['cod_cae' => 'CAE_PREV_TEST', 'cod_gea' => 'GEA_PREV_TEST', 'nom_cae' => 'Contingencia', 'tip_cae' => 'BLOQUEO', 'efe_cae' => 'SIN_CLASES', 'est_cae' => 'PREALERTA', 'fii_cae' => '2095-09-05', 'ffi_cae' => '2095-09-05', 'mot_cae' => 'Decisión institucional']);
        $soporte = app(CalendarioAcademicoInteligente::class);
        $this->assertTrue($soporte->analizarFecha('GEA_PREV_TEST', '2095-09-05')['puede_continuar']);
        $evento->update(['est_cae' => 'CONFIRMADO']);
        $this->assertFalse($soporte->analizarFecha('GEA_PREV_TEST', '2095-09-05')['puede_continuar']);
        $evento->update(['est_cae' => 'CANCELADO']);
        $this->assertTrue($soporte->analizarFecha('GEA_PREV_TEST', '2095-09-05')['puede_continuar']);
        $this->assertSame(1, CalendarioEvento::whereKey('CAE_PREV_TEST')->count());
    }

    public function test_suspension_parcial_respeta_horas(): void
    {
        DB::table('gestion_academica')->insert(['cod_gea' => 'GEA_PREV_TEST', 'ani_gea' => 2095, 'est_gea' => 'ACTIVO']);
        CalendarioEvento::create(['cod_cae' => 'CAE_PREV_TEST', 'cod_gea' => 'GEA_PREV_TEST', 'nom_cae' => 'Suspensión parcial', 'tip_cae' => 'SUSPENSION', 'efe_cae' => 'SUSPENSION_PARCIAL', 'est_cae' => 'CONFIRMADO', 'fii_cae' => '2095-09-05', 'ffi_cae' => '2095-09-05', 'hoi_cae' => '10:00:00', 'hof_cae' => '12:00:00', 'mot_cae' => 'Decisión institucional']);
        $soporte = app(CalendarioAcademicoInteligente::class);
        $this->assertTrue($soporte->analizarFecha('GEA_PREV_TEST', '2095-09-05', ['hora_inicio' => '08:00:00', 'hora_fin' => '09:00:00'])['puede_continuar']);
        $this->assertFalse($soporte->analizarFecha('GEA_PREV_TEST', '2095-09-05', ['hora_inicio' => '11:00:00', 'hora_fin' => '12:30:00'])['puede_continuar']);
    }
}
