<?php

namespace App\Support\AulaVirtual;

use App\Support\Core\SoporteInteligenteBase;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class EntregaTareaInteligente extends SoporteInteligenteBase
{
    /**
     * Analiza el envío o actualización de la entrega de una tarea.
     */
    public function analizarEnvio(string $codTar, string $codEst, array $datos): array
    {
        $bloqueos = [];
        $advertencias = [];
        $sugerencias = [];
        $hallazgos = [];
        $datosCalculados = [];
        $impacto = [];
        $fuentes = ['Reglamento de Aula Virtual SAVP'];

        $texto = trim((string) ($datos['tex_ent'] ?? ''));
        $archivos = $datos['archivos'] ?? [];
        $fechaEnvio = $datos['fec_ent'] ?? now();

        // 1. Verificación de Tarea
        if (! Schema::hasTable('tarea')) {
            return $this->construirResultado();
        }

        $tarea = DB::table('tarea')->where('cod_tar', $codTar)->first();
        if (! $tarea) {
            $msg = "La tarea '{$codTar}' no existe.";
            $bloqueos[] = $msg;
            $this->registrarHallazgo($hallazgos, 'AV_TAREA_NO_EXISTE', self::TIPO_INTEGRIDAD, self::COMP_BLOQUEO, $msg, self::RIESGO_CRITICO);

            return $this->construirResultado(false, false, self::ESTADO_BLOQUEADO, self::RIESGO_CRITICO, $bloqueos, [], [], $hallazgos);
        }

        // 2. Comprobar Pertenencia del Estudiante a la Clase de la Tarea
        if (Schema::hasTable('clase_estudiante')) {
            $pertenece = DB::table('clase_estudiante')
                ->where('cod_cla', $tarea->cod_cla)
                ->where('cod_est', $codEst)
                ->where('est_cla_est', 'ACTIVO')
                ->exists();

            if (! $pertenece) {
                $msg = 'El estudiante no está inscrito en la clase virtual correspondiente a esta tarea.';
                $bloqueos[] = $msg;
                $this->registrarHallazgo($hallazgos, 'AV_ESTUDIANTE_NO_MATRICULADO', self::TIPO_INTEGRIDAD, self::COMP_BLOQUEO, $msg, self::RIESGO_CRITICO);
            }
        }

        // 3. Comprobar Entrega Vacía Definitiva
        $tieneTexto = mb_strlen($texto) >= 2;
        $tieneArchivos = is_array($archivos) && count($archivos) > 0;

        if (! $tieneTexto && ! $tieneArchivos) {
            $msg = 'No puede enviar una entrega completamente vacía. Debe escribir una respuesta o adjuntar al menos un archivo.';
            $bloqueos[] = $msg;
            $this->registrarHallazgo($hallazgos, 'AV_ENTREGA_VACIA', self::TIPO_INTEGRIDAD, self::COMP_BLOQUEO, $msg, self::RIESGO_ALTO);
        }

        // 4. Verificación de Plazo y Entrega Tardía
        if ($tarea->fec_lim_tar) {
            $dtLim = Carbon::parse($tarea->fec_lim_tar);
            $dtEnvio = Carbon::parse($fechaEnvio);

            if ($dtEnvio->greaterThan($dtLim)) {
                if (! $tarea->perm_ent_tardia) {
                    $msg = 'El plazo de entrega ha vencido y el docente no habilitó entregas tardías para esta actividad.';
                    $bloqueos[] = $msg;
                    $this->registrarHallazgo($hallazgos, 'AV_ENTREGA_FUERA_DE_PLAZO_BLOQUEADA', self::TIPO_INSTITUCIONAL, self::COMP_BLOQUEO, $msg, self::RIESGO_ALTO);
                } else {
                    $minutosTarde = $dtLim->diffInMinutes($dtEnvio);
                    $adv = "La entrega se realiza fuera del plazo límite ({$minutosTarde} min de retraso). Quedará registrada como 'ENTREGADO TARDE'.";
                    $advertencias[] = $adv;
                    $datosCalculados['es_tardia'] = true;
                    $datosCalculados['minutos_retraso'] = $minutosTarde;
                    $this->registrarHallazgo($hallazgos, 'AV_ENTREGA_TARDIA', self::TIPO_INSTITUCIONAL, self::COMP_ADVERTENCIA, $adv, self::RIESGO_BAJO, ['minutos_retraso' => $minutosTarde]);
                }
            } else {
                $datosCalculados['es_tardia'] = false;
            }
        }

        $resumen = [
            'tiene_texto' => $tieneTexto,
            'total_archivos' => is_array($archivos) ? count($archivos) : 0,
            'es_tardia' => $datosCalculados['es_tardia'] ?? false,
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
