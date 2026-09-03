<?php

namespace App\Support\AulaVirtual;

use App\Models\AulaVirtual\AsistenciaClase;
use App\Models\AulaVirtual\AsistenciaEstudiante;
use App\Models\AulaVirtual\ClaseVirtual;
use App\Models\Estudiante;
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
    public function analizarSesion(string $codCla, array $estudiantesMarcados, string $fecha, bool $modoCierre = false): array
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

        // 2. Obtener lista oficial de estudiantes pertenecientes a la clase
        $estudiantesOficiales = collect();
        if (Schema::hasTable('clase_estudiante')) {
            $estudiantesOficiales = DB::table('clase_estudiante')
                ->where('cod_cla', $codCla)
                ->where('est_cla_est', 'ACTIVO')
                ->pluck('cod_est');
        }

        $totalOficiales = $estudiantesOficiales->count();
        $datosCalculados['total_estudiantes_oficiales'] = $totalOficiales;

        // 3. Comprobar Pertenencia Estricta (Backend Defensivo)
        $noPertenecen = [];
        foreach (array_keys($estudiantesMarcados) as $codEst) {
            if ($totalOficiales > 0 && ! $estudiantesOficiales->contains($codEst)) {
                $noPertenecen[] = $codEst;
            }
        }

        if (count($noPertenecen) > 0) {
            $msg = 'Se detectaron estudiantes en el registro que no pertenecen a esta clase (' . implode(', ', array_slice($noPertenecen, 0, 3)) . ').';
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
            $msg = 'No se puede consolidar la asistencia porque hay ' . count($faltantesPorMarcar) . ' estudiante(s) sin marcar.';
            $bloqueos[] = $msg;
            $this->registrarHallazgo($hallazgos, 'AV_ASISTENCIA_INCOMPLETA', self::TIPO_INTEGRIDAD, self::COMP_BLOQUEO, $msg, self::RIESGO_ALTO, ['sin_marcar' => count($faltantesPorMarcar)]);
        } elseif (count($faltantesPorMarcar) > 0) {
            $adv = 'Existen ' . count($faltantesPorMarcar) . ' estudiantes pendientes de registrar en esta sesión.';
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
                $adv = count($estudiantesConFaltaReiterada) . ' estudiante(s) acumulan 3 o más inasistencias continuas. Se sugiere alerta temprana para coordinación o secretaría.';
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
        $afectados = [];

        foreach ($codigosEstudiantes as $codEst) {
            $ultimasAsistencias = DB::table('asistencia_estudiante')
                ->join('asistencia_clase', 'asistencia_estudiante.cod_asi_cla', '=', 'asistencia_clase.cod_asi_cla')
                ->join('estado_asistencia', 'asistencia_estudiante.cod_est_asi', '=', 'estado_asistencia.cod_est_asi')
                ->where('asistencia_clase.cod_cla', $codCla)
                ->where('asistencia_estudiante.cod_est', $codEst)
                ->orderByDesc('asistencia_clase.fec_asi_cla')
                ->limit(3)
                ->pluck('estado_asistencia.nom_est_asi');

            if ($ultimasAsistencias->count() >= 3) {
                $todasFaltas = $ultimasAsistencias->every(fn ($nom) => str_contains(strtoupper((string) $nom), 'FALT') || str_contains(strtoupper((string) $nom), 'AUS'));
                if ($todasFaltas) {
                    $afectados[] = $codEst;
                }
            }
        }

        return $afectados;
    }
}
