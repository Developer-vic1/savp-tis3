<?php

namespace App\Support\Notificaciones;

use App\Support\Core\SoporteInteligenteBase;
use Illuminate\Support\Collection;

class NotificacionInteligente extends SoporteInteligenteBase
{
    /**
     * Agrupa y prioriza una lista de notificaciones para evitar fatiga de alertas.
     */
    public function procesarYAgrupar(array $notificaciones): array
    {
        $coleccion = collect($notificaciones);
        $urgentes = [];
        $agrupadas = [];
        $hallazgos = [];

        foreach ($coleccion as $notif) {
            $tipo = $notif['tipo'] ?? 'GENERAL';
            $nivel = $notif['nivel'] ?? 'INFO';

            if ($nivel === 'CRITICO' || $nivel === 'ALTO') {
                $urgentes[] = $notif;
            } else {
                $agrupadas[$tipo][] = $notif;
            }
        }

        $resumenAgrupado = [];
        foreach ($agrupadas as $tipo => $items) {
            if (count($items) > 3) {
                $resumenAgrupado[] = [
                    'tipo' => $tipo,
                    'es_agrupada' => true,
                    'cantidad' => count($items),
                    'titulo' => "Tienes " . count($items) . " novedades sobre {$tipo}",
                    'items' => $items,
                ];
            } else {
                foreach ($items as $item) {
                    $resumenAgrupado[] = $item;
                }
            }
        }

        $datosCalculados = [
            'total_recibidas' => $coleccion->count(),
            'total_urgentes' => count($urgentes),
            'total_procesadas' => count($urgentes) + count($resumenAgrupado),
        ];

        return $this->construirResultado(
            puedeContinuar: true,
            puedeGuardar: true,
            estado: self::ESTADO_OK,
            nivelRiesgo: self::RIESGO_BAJO,
            hallazgos: $hallazgos,
            datosCalculados: $datosCalculados,
            resumen: [
                'urgentes' => $urgentes,
                'generales' => $resumenAgrupado,
            ]
        );
    }
}
