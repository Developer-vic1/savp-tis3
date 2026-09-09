<?php

namespace App\Support\AulaVirtual;

use App\Models\InscripcionEstudiante;
use App\Support\Academico\CalendarioAcademicoInteligente;
use App\Support\Academico\SeguimientoAcademicoInteligente;
use App\Support\Core\SoporteInteligenteBase;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AsistenciaInteligente extends SoporteInteligenteBase
{
    public const UMBRAL_AUSENCIAS_CONSECUTIVAS = 3;

    public const UMBRAL_INASISTENCIA_GRUPAL_ANOMALA = 0.50; // 50% o más de inasistencia súbita

    /**
     * Analiza una sesión de asistencia antes de ser confirmada o cerrada.
     */
    public function analizarSesion(string $codCla, array $estudiantesMarcados, string $fecha, bool $modoCierre = false, ?string $codHbl = null): array
    {
        $bloqueos = [];
        $advertencias = [];
        $sugerencias = [];
        $hallazgos = [];
        $datosCalculados = [];
        $impacto = [];
        $fuentes = ['Reglamento de Asistencia y Permanencia Escolar MinEdu', 'Protocolo de Retención Escolar'];

        // 1. Verificación de Clase Virtual
        if (! Schema::hasTable('clase_virtual')) {
            return $this->construirResultado();
        }

        $clase = DB::table('clase_virtual')->where('cod_cla', $codCla)->first();
        if (! $clase) {
            $msg = "La clase virtual '{$codCla}' no existe.";
            $bloqueos[] = $msg;
            $this->registrarHallazgo($hallazgos, 'AV_CLASE_INEXISTENTE', self::TIPO_INTEGRIDAD, self::COMP_BLOQUEO, $msg, self::RIESGO_CRITICO);

            return $this->construirResultado(false, false, self::ESTADO_BLOQUEADO, self::RIESGO_CRITICO, $bloqueos, [], [], $hallazgos);
        }

        $plan = ! empty($clase->cod_pas)
            ? DB::table('plan_asignatura')->where('cod_pas', $clase->cod_pas)->first()
            : (! empty($clase->cod_pes) ? DB::table('plan_especialidad')->where('cod_pes', $clase->cod_pes)->first() : null);
        if ($plan && Schema::hasTable('calendario_evento')) {
            $ambito = (array) $plan;
            if ($codHbl) {
                $dia = [1 => 'LUNES', 2 => 'MARTES', 3 => 'MIERCOLES', 4 => 'JUEVES', 5 => 'VIERNES', 6 => 'SABADO', 7 => 'DOMINGO'][Carbon::parse($fecha)->isoWeekday()];
                $detalle = DB::table('horario_detalle as d')->join('horario_bloque as b', 'b.cod_hbl', '=', 'd.cod_hbl')
                    ->join('horario as h', 'h.cod_hor', '=', 'd.cod_hor')
                    ->join('plantilla_horaria as p', 'p.cod_pho', '=', 'h.cod_pho')
                    ->where('d.est_hde', 'ACTIVO')->where('h.est_hor', 'ACTIVO')->where('p.est_pho', true)
                    ->where(fn ($q) => $q->whereNull('p.fec_ini_pho')->orWhereDate('p.fec_ini_pho', '<=', $fecha))
                    ->where(fn ($q) => $q->whereNull('p.fec_fin_pho')->orWhereDate('p.fec_fin_pho', '>=', $fecha))
                    ->where('d.dia_hde', $dia)
                    ->where('d.cod_hbl', $codHbl)->where(! empty($clase->cod_pas) ? 'd.cod_pas' : 'd.cod_pes', ! empty($clase->cod_pas) ? $clase->cod_pas : $clase->cod_pes)->first(['d.cod_hde', 'b.hor_ini_hbl', 'b.hor_fin_hbl']);
                if (! $detalle) {
                    $bloqueos[] = 'El bloque seleccionado no corresponde al plan de la clase.';
                } else {
                    $ambito += ['cod_hde' => $detalle->cod_hde, 'hora_inicio' => $detalle->hor_ini_hbl, 'hora_fin' => $detalle->hor_fin_hbl];
                }
            }
            $calendario = app(CalendarioAcademicoInteligente::class)->puedeRegistrarAsistencia($plan->cod_gea, $fecha, $ambito);
            $bloqueos = array_merge($bloqueos, $calendario['bloqueos']);
            $advertencias = array_merge($advertencias, $calendario['advertencias']);
            foreach (array_keys($estudiantesMarcados) as $codEst) {
                $inscripcion = InscripcionEstudiante::where('cod_est', $codEst)->where('cod_gea', $plan->cod_gea)->first();
                if (! $inscripcion || ! $inscripcion->vigencias()->enFecha($fecha)->where('cod_cur', $plan->cod_cur)->where('cod_par', $plan->cod_par)->where('cod_tur', $plan->cod_tur)->exists()) {
                    $bloqueos[] = 'El estudiante '.$codEst.' no tiene vigencia para la fecha y el grupo de asistencia.';
                }
                $novedades = app(SeguimientoAcademicoInteligente::class)->novedadesVigentes($codEst, $plan->cod_gea, $fecha);
                if ($novedades->isNotEmpty()) {
                    $advertencias[] = 'El estudiante '.$codEst.' tiene una novedad vigente; revise justificación y adaptación horaria.';
                }
            }
        }

        // 2. Obtener lista oficial de estudiantes pertenecientes a la clase
        $estudiantesOficiales = collect();
        if (Schema::hasTable('clase_estudiante')) {
            $estudiantesOficiales = DB::table('clase_estudiante')
                ->where('cod_cla', $codCla)
                ->when(! $plan, fn ($q) => $q->where('est_cla_est', 'ACTIVO'))
                ->pluck('cod_est');
            if ($plan && Schema::hasTable('inscripcion_vigencia')) {
                $vigentes = DB::table('inscripcion_vigencia as v')->join('inscripcion_estudiante as i', 'i.cod_ins', '=', 'v.cod_ins')
                    ->where('i.cod_gea', $plan->cod_gea)->where('v.cod_cur', $plan->cod_cur)->where('v.cod_par', $plan->cod_par)->where('v.cod_tur', $plan->cod_tur)
                    ->where('v.est_ivg', '<>', 'ANULADA')->whereDate('v.fii_ivg', '<=', $fecha)->where(fn ($q) => $q->whereNull('v.ffi_ivg')->orWhereDate('v.ffi_ivg', '>=', $fecha))->pluck('i.cod_est');
                $estudiantesOficiales = $estudiantesOficiales->intersect($vigentes)->values();
            }
        }

        $totalOficiales = $estudiantesOficiales->count();
        $datosCalculados['total_estudiantes_oficiales'] = $totalOficiales;
        $datosCalculados['estudiantes_oficiales'] = $estudiantesOficiales->all();

        // 3. Comprobar Pertenencia Estricta (Backend Defensivo)
        $noPertenecen = [];
        foreach (array_keys($estudiantesMarcados) as $codEst) {
            if (! $estudiantesOficiales->contains($codEst)) {
                $noPertenecen[] = $codEst;
            }
        }

        if (count($noPertenecen) > 0) {
            $msg = 'Se detectaron estudiantes en el registro que no pertenecen a esta clase ('.implode(', ', array_slice($noPertenecen, 0, 3)).').';
            $bloqueos[] = $msg;
            $this->registrarHallazgo($hallazgos, 'AV_ESTUDIANTE_NO_PERTENECE', self::TIPO_INTEGRIDAD, self::COMP_BLOQUEO, $msg, self::RIESGO_CRITICO, ['no_pertenecen' => $noPertenecen]);
        }

        // 4. Comprobar Estudiantes Faltantes por Marcar
        $marcadosValidos = 0;
        $presentes = 0;
        $ausentes = 0;
        $justificados = 0;
        $atrasos = 0;
        $faltantesPorMarcar = [];

        foreach ($estudiantesOficiales as $codEst) {
            $estadoEst = trim((string) ($estudiantesMarcados[$codEst] ?? ''));
            if ($estadoEst === '') {
                $faltantesPorMarcar[] = $codEst;
            } else {
                $marcadosValidos++;
                $estUpper = strtoupper($estadoEst);
                if (str_contains($estUpper, 'PRES')) {
                    $presentes++;
                } elseif (str_contains($estUpper, 'FALT') || str_contains($estUpper, 'AUS')) {
                    $ausentes++;
                } elseif (str_contains($estUpper, 'JUST')) {
                    $justificados++;
                } elseif (str_contains($estUpper, 'ATRA') || str_contains($estUpper, 'RET')) {
                    $atrasos++;
                }
            }
        }

        $datosCalculados['presentes'] = $presentes;
        $datosCalculados['ausentes'] = $ausentes;
        $datosCalculados['justificados'] = $justificados;
        $datosCalculados['atrasos'] = $atrasos;
        $datosCalculados['sin_marcar'] = count($faltantesPorMarcar);

        if ($modoCierre && count($faltantesPorMarcar) > 0) {
            $msg = 'No se puede consolidar la asistencia porque hay '.count($faltantesPorMarcar).' estudiante(s) sin marcar.';
            $bloqueos[] = $msg;
            $this->registrarHallazgo($hallazgos, 'AV_ASISTENCIA_INCOMPLETA', self::TIPO_INTEGRIDAD, self::COMP_BLOQUEO, $msg, self::RIESGO_ALTO, ['sin_marcar' => count($faltantesPorMarcar)]);
        } elseif (count($faltantesPorMarcar) > 0) {
            $adv = 'Existen '.count($faltantesPorMarcar).' estudiantes pendientes de registrar en esta sesión.';
            $advertencias[] = $adv;
            $this->registrarHallazgo($hallazgos, 'AV_ASISTENCIA_PARCIAL', self::TIPO_PEDAGOGICA, self::COMP_ADVERTENCIA, $adv, self::RIESGO_BAJO);
        }

        // 5. Análisis Estadístico: Inasistencia Anómala y Ausencias Consecutivas
        if ($totalOficiales > 0 && ($ausentes / $totalOficiales) >= self::UMBRAL_INASISTENCIA_GRUPAL_ANOMALA && $totalOficiales >= 5) {
            $porcentajeAusentes = round(($ausentes / $totalOficiales) * 100);
            $adv = "Se observa un nivel inusual de inasistencia grupal ({$porcentajeAusentes}% de faltas en la sesión). Verifique si hubo algún evento institucional o suspensión.";
            $advertencias[] = $adv;
            $this->registrarHallazgo($hallazgos, 'AV_ASISTENCIA_ANOMALA_GRUPAL', self::TIPO_ESTADISTICA, self::COMP_ADVERTENCIA, $adv, self::RIESGO_MEDIO, ['porcentaje' => $porcentajeAusentes]);
        }

        // 6. Análisis individual de ausencias reiteradas
        if (Schema::hasTable('asistencia_estudiante') && Schema::hasTable('asistencia_clase')) {
            $estudiantesConFaltaReiterada = $this->detectarFaltasReiteradas($codCla, array_keys($estudiantesMarcados));
            if (count($estudiantesConFaltaReiterada) > 0) {
                $adv = count($estudiantesConFaltaReiterada).' estudiante(s) acumulan 3 o más inasistencias continuas. Se sugiere alerta temprana para coordinación o secretaría.';
                $advertencias[] = $adv;
                $this->registrarHallazgo($hallazgos, 'AV_ASISTENCIA_ANOMALA_INDIVIDUAL', self::TIPO_ESTADISTICA, self::COMP_ADVERTENCIA, $adv, self::RIESGO_MEDIO, [
                    'estudiantes_afectados' => $estudiantesConFaltaReiterada,
                ]);
            }
        }

        $resumen = [
            'total_alumnos' => $totalOficiales,
            'presentes' => $presentes,
            'ausentes' => $ausentes,
            'justificados' => $justificados,
            'atrasos' => $atrasos,
            'pendientes' => count($faltantesPorMarcar),
        ];

        return $this->construirResultado(
            puedeContinuar: count($bloqueos) === 0,
            puedeGuardar: count($bloqueos) === 0,
            estado: count($bloqueos) > 0 ? self::ESTADO_BLOQUEADO : (count($advertencias) > 0 ? self::ESTADO_OBSERVADO : self::ESTADO_OK),
            nivelRiesgo: count($bloqueos) > 0 ? self::RIESGO_ALTO : (count($advertencias) > 0 ? self::RIESGO_MEDIO : self::RIESGO_BAJO),
            bloqueos: $bloqueos,
            advertencias: $advertencias,
            sugerencias: $sugerencias,
            hallazgos: $hallazgos,
            datosCalculados: $datosCalculados,
            impacto: $impacto,
            resumen: $resumen,
            fuentesRegla: $fuentes
        );
    }

    /**
     * Detecta estudiantes con 3 o más ausencias continuas registradas.
     */
    private function detectarFaltasReiteradas(string $codCla, array $codigosEstudiantes): array
    {
        $plan = DB::table('clase_virtual as c')->join('plan_asignatura as p', 'p.cod_pas', '=', 'c.cod_pas')->where('c.cod_cla', $codCla)->select('p.cod_gea')->first();
        if (! $plan) {
            return [];
        }
        $soporte = app(SeguimientoAcademicoInteligente::class);
        $afectados = [];
        foreach ($codigosEstudiantes as $codEst) {
            $analisis = $soporte->analizar($codEst, $plan->cod_gea);
            if (in_array('AUSENCIAS_CONSECUTIVAS', $analisis['advertencias'], true)) {
                $afectados[] = $codEst;
            }
        }

        return $afectados;
    }
}
