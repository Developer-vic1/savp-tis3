<?php

namespace App\Support\Dashboard;

use App\Support\Core\SoporteInteligenteBase;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardEstudianteInteligente extends SoporteInteligenteBase
{
    /**
     * Analiza el estado integral, tareas pendientes y alertas formativas para el estudiante.
     */
    public function compilarResumenEstudiante(string $codEst): array
    {
        $advertencias = [];
        $sugerencias = [];
        $hallazgos = [];
        $metricas = [];
        $fuentes = ['Portal del Estudiante SAVP'];

        // 1. Tareas pendientes con fecha límite próxima
        if (Schema::hasTable('clase_estudiante') && Schema::hasTable('tarea') && Schema::hasTable('entrega_tarea')) {
            $clasesEstudiante = DB::table('clase_estudiante')
                ->where('cod_est', $codEst)
                ->where('est_cla_est', 'ACTIVO')
                ->pluck('cod_cla');

            $tareasPendientes = DB::table('tarea')
                ->whereIn('cod_cla', $clasesEstudiante)
                ->where('est_tar', 'PUBLICADA')
                ->whereNotExists(function ($q) use ($codEst) {
                    $q->select(DB::raw(1))
                        ->from('entrega_tarea')
                        ->whereColumn('entrega_tarea.cod_tar', 'tarea.cod_tar')
                        ->where('entrega_tarea.cod_est', $codEst)
                        ->whereIn('entrega_tarea.est_ent', ['ENTREGADO', 'ENTREGADO_TARDE', 'CALIFICADO']);
                })
                ->where('fec_lim_tar', '>=', now())
                ->orderBy('fec_lim_tar')
                ->get();

            $metricas['tareas_pendientes'] = $tareasPendientes->count();

            $tareasProximas = $tareasPendientes->filter(function ($t) {
                return Carbon::parse($t->fec_lim_tar)->diffInHours(now()) <= 48;
            });

            if ($tareasProximas->count() > 0) {
                $adv = "Tienes {$tareasProximas->count()} tarea(s) que vencen en las próximas 48 horas.";
                $advertencias[] = $adv;
                $this->registrarHallazgo($hallazgos, 'DASH_EST_TAREAS_PROXIMAS_VENCER', self::TIPO_PEDAGOGICA, self::COMP_ADVERTENCIA, $adv, self::RIESGO_BAJO, ['tareas' => $tareasProximas->pluck('tit_tar')]);
            }
        }

        // 2. Estado del Test Vocacional RIASEC
        if (Schema::hasTable('orientacion_actividades')) {
            $actividad = DB::table('orientacion_actividades')
                ->where('cod_est', $codEst)
                ->latest('updated_at')
                ->first();

            if (! $actividad || $actividad->estado === 'pendiente') {
                $sug = 'Tienes disponible el Cuestionario de Exploración Vocacional (RIASEC) para descubrir tus afinidades técnicas.';
                $sugerencias[] = $sug;
                $this->registrarHallazgo($hallazgos, 'DASH_EST_ORIENTACION_PENDIENTE', self::TIPO_RECOMENDACION, self::COMP_SUGERENCIA, $sug, self::RIESGO_BAJO);
            }
        }

        return $this->construirResultado(
            puedeContinuar: true,
            puedeGuardar: true,
            estado: self::ESTADO_OK,
            nivelRiesgo: self::RIESGO_BAJO,
            advertencias: $advertencias,
            sugerencias: $sugerencias,
            hallazgos: $hallazgos,
            datosCalculados: $metricas,
            fuentesRegla: $fuentes
        );
    }
}
