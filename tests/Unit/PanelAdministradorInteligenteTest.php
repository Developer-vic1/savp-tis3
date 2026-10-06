<?php

namespace Tests\Unit;

use App\Models\Oficial\Academico\Bitacora;
use App\Models\Oficial\Academico\GestionAcademica;
use App\Models\Oficial\Academico\PeriodoEvaluacion;
use App\Models\Oficial\Academico\Persona;
use App\Models\Oficial\Sistema\User;
use App\Support\Academico\PeriodoEvaluacionInteligente;
use App\Support\Bitacora\BitacoraInteligente;
use Carbon\CarbonImmutable;
use Tests\TestCase;

class PanelAdministradorInteligenteTest extends TestCase
{
    private function gestion(): GestionAcademica
    {
        return new GestionAcademica(['ani_gea' => 2026, 'fii_gea' => '2026-02-02', 'ffi_gea' => '2026-12-04']);
    }

    private function catalogo(): array
    {
        return array_map(fn ($orden, $nombre) => new PeriodoEvaluacion(['ord_pev' => $orden, 'nom_pev' => $nombre, 'est_pev' => 'ACTIVO']),
            [1, 2, 3], ['Primer Trimestre', 'Segundo Trimestre', 'Tercer Trimestre']);
    }

    public function test_el_periodo_es_unico_y_sigue_siendo_referencial(): void
    {
        $support = new PeriodoEvaluacionInteligente;
        foreach (['2026-05-08' => 'Primer Trimestre', '2026-05-11' => 'Segundo Trimestre', '2026-08-31' => 'Segundo Trimestre',
            '2026-09-01' => 'Tercer Trimestre', '2026-10-03' => 'Tercer Trimestre', '2026-12-02' => 'Tercer Trimestre'] as $fecha => $nombre) {
            $resultado = $support->orientarPeriodoActual($this->gestion(), $this->catalogo(), CarbonImmutable::parse($fecha, 'America/La_Paz'));
            $this->assertSame($nombre, $resultado['nombre']);
            $this->assertSame('REFERENCIAL', $resultado['estado']);
            $this->assertStringContainsString('Pendiente de confirmación', $resultado['explicacion']);
        }
    }

    public function test_no_inventa_periodo_con_gestion_ausente_catalogo_ambiguo_o_diferente(): void
    {
        $support = new PeriodoEvaluacionInteligente;
        $fecha = CarbonImmutable::parse('2026-10-03', 'America/La_Paz');
        $this->assertSame('SIN_FECHAS', $support->orientarPeriodoActual(null, $this->catalogo(), $fecha)['estado']);
        $this->assertSame('SIN_FECHAS', $support->orientarPeriodoActual($this->gestion(), [], $fecha)['estado']);
        $this->assertSame('SIN_FECHAS', $support->orientarPeriodoActual($this->gestion(), [...$this->catalogo(), $this->catalogo()[2]], $fecha)['estado']);
        $this->assertSame('SIN_FECHAS', $support->orientarPeriodoActual($this->gestion(), $this->catalogo(), $fecha->addYear())['estado']);
        $semestral = [new PeriodoEvaluacion(['nom_pev' => 'Primer Semestre', 'ord_pev' => 3, 'est_pev' => 'ACTIVO'])];
        $this->assertSame('SIN_FECHAS', $support->orientarPeriodoActual($this->gestion(), $semestral, $fecha)['estado']);
    }

    public function test_fuera_de_rango_no_se_muestra_un_trimestre_activo(): void
    {
        $resultado = (new PeriodoEvaluacionInteligente)->orientarPeriodoActual($this->gestion(), $this->catalogo(), CarbonImmutable::parse('2026-12-03'));
        $this->assertSame('FUERA_DE_RANGO', $resultado['estado']);
    }

    public function test_bitacora_humaniza_sin_cambiar_el_registro_y_no_expone_valores_privados(): void
    {
        $persona = new Persona(['nom_per' => 'María Elena', 'ape_pat_per' => 'Pérez']);
        $usuario = (new User(['email' => 'privado@example.test']))->setRelation('persona', $persona);
        $evento = (new Bitacora(['acc_bit' => 'login_google_exitoso', 'tab_bit' => 'users', 'res_bit' => 'EXITOSO',
            'fec_bit' => '2026-10-03 12:00:00', 'val_nue_bit' => ['password' => 'privado'], 'ip_bit' => '10.1.2.3']))->setRelation('usuario', $usuario);
        $original = $evento->getAttributes();
        $resultado = (new BitacoraInteligente)->presentar($evento);
        $this->assertSame('Acceso con Google', $resultado['titulo']);
        $this->assertStringContainsString('María Elena Pérez', $resultado['detalle']);
        $this->assertSame('Completado', $resultado['resultado']);
        $this->assertStringNotContainsString('privado', json_encode($resultado));
        $this->assertStringNotContainsString('10.1.2.3', json_encode($resultado));
        $this->assertSame($original, $evento->getAttributes());
    }

    public function test_evento_desconocido_no_inventa_exito_autor_ni_modulo(): void
    {
        $evento = (new Bitacora(['acc_bit' => 'EVENTO_NUEVO_27', 'tab_bit' => 'tabla_interna']))->setRelation('usuario', null);
        $resultado = (new BitacoraInteligente)->presentar($evento);
        $this->assertSame('Actividad institucional registrada', $resultado['titulo']);
        $this->assertSame('Autor no informado · Área institucional', $resultado['detalle']);
        $this->assertSame('Resultado no informado', $resultado['resultado']);
        $this->assertSame('Sin fecha registrada', $resultado['fecha']);
    }

    public function test_un_fallo_o_bloqueo_no_se_humaniza_como_operacion_completada(): void
    {
        foreach (['FALLIDO' => 'No se completó', 'BLOQUEADO' => 'Bloqueado'] as $estado => $etiqueta) {
            $evento = (new Bitacora(['acc_bit' => 'LOGIN_FALLIDO', 'res_bit' => $estado, 'des_bit' => 'SQLSTATE: token privado']))->setRelation('usuario', null);
            $resultado = (new BitacoraInteligente)->presentar($evento);
            $this->assertSame('Intento de acceso', $resultado['titulo']);
            $this->assertSame($etiqueta, $resultado['resultado']);
            $this->assertStringNotContainsString('SQLSTATE', $resultado['detalle']);
        }
    }

    public function test_la_bitacora_completa_y_el_dashboard_comparten_vocabulario(): void
    {
        $componente = new \App\Livewire\Admin\Bitacora;
        $this->assertSame('Acceso con Google', $componente->accionInstitucional('login_google_exitoso'));
        $this->assertSame('Calendario institucional', $componente->tablaInstitucional('calendario_evento'));
    }
}
