<?php

namespace App\Support\AulaVirtual;

use App\Support\Core\SoporteInteligenteBase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CursoVirtualInteligente extends SoporteInteligenteBase
{
    /**
     * Analiza el estado, matrícula e integridad de una clase virtual.
     */
    public function analizarClase(string $codCla): array
    {
        $bloqueos = [];
        $advertencias = [];
        $sugerencias = [];
        $hallazgos = [];
        $datosCalculados = [];
        $impacto = [];
        $fuentes = ['Estructura Académica y Aula Virtual SAVP'];

        if (! Schema::hasTable('clase_virtual')) {
            return $this->construirResultado();
        }

        $clase = DB::table('clase_virtual')->where('cod_cla', $codCla)->first();
        if (! $clase) {
            $msg = "La clase virtual '{$codCla}' no existe.";
            $bloqueos[] = $msg;
            $this->registrarHallazgo($hallazgos, 'AV_CLASE_NO_EXISTE', self::TIPO_INTEGRIDAD, self::COMP_BLOQUEO, $msg, self::RIESGO_CRITICO);

            return $this->construirResultado(false, false, self::ESTADO_BLOQUEADO, self::RIESGO_CRITICO, $bloqueos, [], [], $hallazgos);
        }

        // 1. Verificación del Plan de Asignatura
        if (Schema::hasTable('plan_asignatura')) {
            $plan = DB::table('plan_asignatura')->where('cod_pas', $clase->cod_pas)->first();
            if (! $plan) {
                $msg = 'La clase virtual está vinculada a un plan de asignatura inexistente.';
                $bloqueos[] = $msg;
                $this->registrarHallazgo($hallazgos, 'AV_CLASE_PLAN_INEXISTENTE', self::TIPO_INTEGRIDAD, self::COMP_BLOQUEO, $msg, self::RIESGO_CRITICO);
            } else {
                $datosCalculados['curso'] = $plan->cod_cur;
                $datosCalculados['paralelo'] = $plan->cod_par;
                $datosCalculados['docente'] = $plan->cod_doc;
                $datosCalculados['gestion'] = $plan->cod_gea;
            }
        }

        // 2. Conteo de Estudiantes Matriculados
        if (Schema::hasTable('clase_estudiante')) {
            $matriculados = DB::table('clase_estudiante')
                ->where('cod_cla', $codCla)
                ->where('est_cla_est', 'ACTIVO')
                ->count();

            $datosCalculados['total_matriculados'] = $matriculados;

            if ($matriculados === 0) {
                $adv = 'La clase virtual no tiene ningún estudiante matriculado actualmente.';
                $advertencias[] = $adv;
                $this->registrarHallazgo($hallazgos, 'AV_CLASE_SIN_ESTUDIANTES', self::TIPO_PEDAGOGICA, self::COMP_ADVERTENCIA, $adv, self::RIESGO_MEDIO);
            }
        }

        // 3. Tareas y Materiales activos
        if (Schema::hasTable('tarea')) {
            $totalTareas = DB::table('tarea')
                ->where('cod_cla', $codCla)
                ->where('est_tar', 'PUBLICADA')
                ->count();
            $datosCalculados['tareas_publicadas'] = $totalTareas;
        }

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
            fuentesRegla: $fuentes
        );
    }
}
