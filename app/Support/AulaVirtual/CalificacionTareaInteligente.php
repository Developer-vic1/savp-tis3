<?php

namespace App\Support\AulaVirtual;

use App\Support\Core\SoporteInteligenteBase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CalificacionTareaInteligente extends SoporteInteligenteBase
{
    /**
     * Analiza la calificación y retroalimentación asignada por un docente a una entrega.
     */
    public function analizarCalificacion(string $codEnt, float $puntaje, ?string $retroalimentacion = null): array
    {
        $bloqueos = [];
        $advertencias = [];
        $sugerencias = [];
        $hallazgos = [];
        $datosCalculados = [];
        $impacto = [];
        $fuentes = ['Reglamento de Evaluación del Desarrollo Curricular RM 0190/2024'];

        if (! Schema::hasTable('entrega_tarea') || ! Schema::hasTable('tarea')) {
            return $this->construirResultado();
        }

        $entrega = DB::table('entrega_tarea')
            ->join('tarea', 'entrega_tarea.cod_tar', '=', 'tarea.cod_tar')
            ->where('entrega_tarea.cod_ent', $codEnt)
            ->select('entrega_tarea.*', 'tarea.pun_max_tar', 'tarea.tit_tar')
            ->first();

        if (! $entrega) {
            $msg = "La entrega '{$codEnt}' no existe.";
            $bloqueos[] = $msg;
            $this->registrarHallazgo($hallazgos, 'AV_ENTREGA_NO_EXISTE', self::TIPO_INTEGRIDAD, self::COMP_BLOQUEO, $msg, self::RIESGO_CRITICO);

            return $this->construirResultado(false, false, self::ESTADO_BLOQUEADO, self::RIESGO_CRITICO, $bloqueos, [], [], $hallazgos);
        }

        $puntajeMax = (float) $entrega->pun_max_tar;
        $datosCalculados['puntaje_maximo'] = $puntajeMax;
        $datosCalculados['puntaje_asignado'] = $puntaje;

        // 1. Validar Rango de Puntaje
        if ($puntaje < 0) {
            $msg = 'La calificación no puede ser un valor negativo.';
            $bloqueos[] = $msg;
            $this->registrarHallazgo($hallazgos, 'AV_CALIF_NEGATIVA', self::TIPO_NORMATIVA, self::COMP_BLOQUEO, $msg, self::RIESGO_CRITICO);
        } elseif ($puntaje > $puntajeMax) {
            $msg = "La calificación ({$puntaje}) excede el puntaje máximo asignado a la tarea ({$puntajeMax}).";
            $bloqueos[] = $msg;
            $this->registrarHallazgo($hallazgos, 'AV_CALIF_EXCEDE_MAXIMO', self::TIPO_NORMATIVA, self::COMP_BLOQUEO, $msg, self::RIESGO_CRITICO, ['maximo' => $puntajeMax]);
        }

        // 2. Advertencia si la entrega fue tardía
        if ($entrega->est_ent === 'ENTREGADO_TARDE') {
            $adv = "La entrega fue recibida fuera de plazo. Verifique si aplica penalización institucional según su rúbrica.";
            $advertencias[] = $adv;
            $this->registrarHallazgo($hallazgos, 'AV_CALIF_ENTREGA_TARDIA', self::TIPO_INSTITUCIONAL, self::COMP_ADVERTENCIA, $adv, self::RIESGO_BAJO);
        }

        // 3. Sugerencia de Retroalimentación pedagógica
        $porcentaje = $puntajeMax > 0 ? ($puntaje / $puntajeMax) * 100 : 0;
        $datosCalculados['porcentaje_alcanzado'] = round($porcentaje, 1);

        if ($porcentaje < 51.0 && blank($retroalimentacion)) {
            $sug = 'La nota es inferior al 51%. Se recomienda añadir retroalimentación formativa para orientar la mejora del estudiante.';
            $sugerencias[] = $sug;
            $this->registrarHallazgo($hallazgos, 'AV_SUG_RETROALIMENTACION_FORMATIVA', self::TIPO_PEDAGOGICA, self::COMP_SUGERENCIA, $sug, self::RIESGO_BAJO);
        }

        $resumen = [
            'puntaje' => $puntaje,
            'maximo' => $puntajeMax,
            'porcentaje' => round($porcentaje, 1),
            'tiene_retroalimentacion' => filled($retroalimentacion),
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
}
