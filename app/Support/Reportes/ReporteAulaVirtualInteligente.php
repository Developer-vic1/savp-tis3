<?php

namespace App\Support\Reportes;

use App\Support\Core\SoporteInteligenteBase;
use Illuminate\Support\Collection;

class ReporteAulaVirtualInteligente extends SoporteInteligenteBase
{
    /**
     * Genera un diagnóstico inteligente sobre el rendimiento agregado del aula virtual.
     */
    public function diagnosticoLms(array $metricas): array
    {
        $advertencias = [];
        $sugerencias = [];
        $hallazgos = [];
        $datosCalculados = [];
        $fuentes = ['Métricas Agregadas y Analítica de Aprendizaje SAVP LMS'];

        $totalTareas = (int) ($metricas['total_tareas'] ?? 0);
        $totalEntregas = (int) ($metricas['total_entregas'] ?? 0);
        $entregasTardias = (int) ($metricas['entregas_tardias'] ?? 0);
        $tasaAsistencia = (float) ($metricas['tasa_asistencia'] ?? 100.0);

        $tasaPuntualidad = $totalEntregas > 0 ? round((($totalEntregas - $entregasTardias) / $totalEntregas) * 100, 1) : 100.0;
        $datosCalculados['tasa_puntualidad'] = $tasaPuntualidad;
        $datosCalculados['tasa_asistencia'] = $tasaAsistencia;

        if ($totalEntregas > 0 && $tasaPuntualidad < 70.0) {
            $adv = "La tasa de entregas puntuales es del {$tasaPuntualidad}%. Más del 30% de las tareas se están entregando fuera de plazo.";
            $advertencias[] = $adv;
            $this->registrarHallazgo($hallazgos, 'REP_LMS_PUNTUALIDAD_BAJA', self::TIPO_ESTADISTICA, self::COMP_ADVERTENCIA, $adv, self::RIESGO_MEDIO, ['tasa' => $tasaPuntualidad]);
        }

        if ($tasaAsistencia < 80.0) {
            $adv = "La asistencia global promedio en clases virtuales es del {$tasaAsistencia}%, por debajo del umbral óptimo (85%).";
            $advertencias[] = $adv;
            $this->registrarHallazgo($hallazgos, 'REP_LMS_ASISTENCIA_BAJA', self::TIPO_ESTADISTICA, self::COMP_ADVERTENCIA, $adv, self::RIESGO_MEDIO, ['asistencia' => $tasaAsistencia]);
        }

        return $this->construirResultado(
            puedeContinuar: true,
            puedeGuardar: true,
            estado: count($advertencias) > 0 ? self::ESTADO_OBSERVADO : self::ESTADO_OK,
            nivelRiesgo: count($advertencias) > 0 ? self::RIESGO_MEDIO : self::RIESGO_BAJO,
            advertencias: $advertencias,
            sugerencias: $sugerencias,
            hallazgos: $hallazgos,
            datosCalculados: $datosCalculados,
            fuentesRegla: $fuentes
        );
    }
}
