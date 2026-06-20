<?php

namespace App\Services\AulaVirtual;

use App\Models\AulaVirtual\AsistenciaClase;
use App\Models\AulaVirtual\AsistenciaEstudiante;
use App\Models\AulaVirtual\EstadoAsistencia;
use App\Models\Docente;
use App\Models\User;

class AsistenciaService
{
    public function guardar(array $datos, Docente $docente, User $usuario): AsistenciaClase
    {
        $asistencia = AsistenciaClase::firstOrCreate(
            [
                'cod_cla' => $datos['cod_cla'],
                'cod_doc' => $docente->cod_doc,
                'fec_asi_cla' => $datos['fec_asi_cla'],
                'cod_hbl' => $datos['cod_hbl'] ?? null,
            ],
            [
                'cod_usu_reg' => $usuario->cod_usu,
                'tip_asi_cla' => $datos['tip_asi_cla'] ?? 'CLASE',
                'tit_asi_cla' => $datos['tit_asi_cla'] ?? 'Registro de asistencia',
                'obs_asi_cla' => $datos['obs_asi_cla'] ?? null,
                'est_asi_cla' => 'ABIERTA',
            ]
        );

        foreach (($datos['asistencias'] ?? []) as $codEst => $registro) {
            $estado = EstadoAsistencia::where('cod_est_asi', $registro['cod_est_asi'] ?? null)->first();

            if (! $estado) {
                continue;
            }

            AsistenciaEstudiante::updateOrCreate(
                ['cod_asi_cla' => $asistencia->cod_asi_cla, 'cod_est' => $codEst],
                [
                    'cod_est_asi' => $estado->cod_est_asi,
                    'cod_usu_reg' => $usuario->cod_usu,
                    'min_retraso' => max(0, (int) ($registro['min_retraso'] ?? 0)),
                    'obs_asi_est' => $registro['obs_asi_est'] ?? null,
                    'fec_reg_asi_est' => now(),
                    'est_asi_est' => 'REGISTRADO',
                ]
            );
        }

        return $asistencia;
    }
}
