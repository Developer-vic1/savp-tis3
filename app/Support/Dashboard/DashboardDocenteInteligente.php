<?php

namespace App\Support\Dashboard;

use App\Support\Core\SoporteInteligenteBase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardDocenteInteligente extends SoporteInteligenteBase
{
    /**
     * Analiza y prioriza tareas pendientes para la vista del docente.
     */
    public function compilarResumenDocente(string $codDoc): array
    {
        $advertencias = [];
        $sugerencias = [];
        $hallazgos = [];
        $metricas = [];
        $fuentes = ['Gestión Pedagógica y Aula Virtual SAVP'];

        // 1. Entregas pendientes de calificación
        if (Schema::hasTable('entrega_tarea') && Schema::hasTable('tarea')) {
            $entregasPorRevisar = DB::table('entrega_tarea')
                ->join('tarea', 'entrega_tarea.cod_tar', '=', 'tarea.cod_tar')
                ->where('tarea.cod_doc', $codDoc)
                ->whereIn('entrega_tarea.est_ent', ['ENTREGADO', 'ENTREGADO_TARDE'])
                ->count();

            $metricas['entregas_pendientes_calificar'] = $entregasPorRevisar;
            if ($entregasPorRevisar > 0) {
                $adv = "Tienes {$entregasPorRevisar} entrega(s) de tareas pendientes de revisión y calificación.";
                $advertencias[] = $adv;
                $this->registrarHallazgo($hallazgos, 'DASH_DOC_ENTREGAS_PENDIENTES', self::TIPO_PEDAGOGICA, self::COMP_ADVERTENCIA, $adv, self::RIESGO_BAJO, ['cantidad' => $entregasPorRevisar]);
            }
        }

        // 2. Sesiones de asistencia en borrador
        if (Schema::hasTable('asistencia_clase')) {
            $asistenciasBorrador = DB::table('asistencia_clase')
                ->where('cod_doc', $codDoc)
                ->where('est_asi_cla', 'BORRADOR')
                ->count();

            $metricas['asistencias_en_borrador'] = $asistenciasBorrador;
            if ($asistenciasBorrador > 0) {
                $sug = "Tienes {$asistenciasBorrador} sesión(es) de asistencia guardada(s) como borrador pendientes de consolidar.";
                $sugerencias[] = $sug;
                $this->registrarHallazgo($hallazgos, 'DASH_DOC_ASISTENCIA_BORRADOR', self::TIPO_PEDAGOGICA, self::COMP_SUGERENCIA, $sug, self::RIESGO_BAJO);
            }
        }

        return $this->construirResultado(
            puedeContinuar: true,
            puedeGuardar: true,
            estado: count($advertencias) > 0 ? self::ESTADO_OBSERVADO : self::ESTADO_OK,
            nivelRiesgo: self::RIESGO_BAJO,
            advertencias: $advertencias,
            sugerencias: $sugerencias,
            hallazgos: $hallazgos,
            datosCalculados: $metricas,
            fuentesRegla: $fuentes
        );
    }
}
