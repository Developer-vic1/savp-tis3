<?php

namespace App\Support\Seguridad;

use App\Support\Core\SoporteInteligenteBase;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BitacoraInteligente extends SoporteInteligenteBase
{
    /**
     * Analiza eventos de bitácora para detectar patrones atípicos sin emitir juicios arbitrarios.
     */
    public function analizarEventosRecientes(int $horasAtras = 24): array
    {
        $advertencias = [];
        $sugerencias = [];
        $hallazgos = [];
        $datosCalculados = [];
        $fuentes = ['Auditoría y Trazabilidad del Sistema SAVP'];

        if (! Schema::hasTable('bitacora')) {
            return $this->construirResultado();
        }

        $desde = Carbon::now()->subHours($horasAtras);

        // 1. Detección de múltiples eliminaciones en poco tiempo
        $eliminaciones = DB::table('bitacora')
            ->where('created_at', '>=', $desde)
            ->where(function ($q) {
                $q->where('accion', 'like', '%ELIMINAR%')
                    ->orWhere('accion', 'like', '%DELETE%')
                    ->orWhere('accion', 'like', '%ANULAR%');
            })
            ->count();

        $datosCalculados['eliminaciones_recientes'] = $eliminaciones;

        if ($eliminaciones >= 15) {
            $adv = "Se registraron {$eliminaciones} operaciones de eliminación o anulación en las últimas {$horasAtras} horas. Se sugiere verificar el registro de auditoría.";
            $advertencias[] = $adv;
            $this->registrarHallazgo($hallazgos, 'SEC_BITACORA_ELIMINACIONES_MASIVAS', self::TIPO_ESTADISTICA, self::COMP_ADVERTENCIA, $adv, self::RIESGO_MEDIO, ['cantidad' => $eliminaciones]);
        }

        // 2. Detección de múltiples errores registrados
        $errores = DB::table('bitacora')
            ->where('created_at', '>=', $desde)
            ->where('nivel', 'ERROR')
            ->count();

        $datosCalculados['errores_recientes'] = $errores;

        if ($errores >= 10) {
            $adv = "Se registraron {$errores} eventos clasificados como ERROR en las últimas {$horasAtras} horas.";
            $advertencias[] = $adv;
            $this->registrarHallazgo($hallazgos, 'SEC_BITACORA_ERRORES_FRECUENTES', self::TIPO_ESTADISTICA, self::COMP_ADVERTENCIA, $adv, self::RIESGO_MEDIO, ['cantidad' => $errores]);
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
