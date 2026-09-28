<?php

namespace App\Services\AulaVirtual;

use App\Models\AulaVirtual\AsistenciaClase;
use App\Models\AulaVirtual\AsistenciaEstudiante;
use App\Models\AulaVirtual\ClaseVirtual;
use App\Models\AulaVirtual\EstadoAsistencia;
use App\Models\Docente;
use App\Models\User;
use App\Services\BitacoraService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AsistenciaService
{
    public function guardar(array $datos, Docente $docente, User $usuario): AsistenciaClase
    {
        abort_unless($usuario->est_usu === 'ACTIVO' && $usuario->can('Aula_Virtual_Docente')
            && app(CursoVirtualService::class)->docenteDeUsuario($usuario)?->cod_doc === $docente->cod_doc, 403);
        $clase = ClaseVirtual::query()
            ->with('estudiantes')
            ->where('cod_cla', $datos['cod_cla'])
            ->where('est_cla', 'ACTIVA')
            ->whereHas('planAsignatura', fn ($query) => $query->where('cod_doc', $docente->cod_doc))
            ->firstOrFail();

        $allowedStudents = $clase->estudiantes
            ->where('est_cla_est', 'ACTIVO')
            ->pluck('cod_est')
            ->all();
        $foreignStudents = array_diff(array_keys($datos['asistencias'] ?? []), $allowedStudents);

        if ($foreignStudents !== []) {
            throw ValidationException::withMessages([
                'asistencias' => 'La solicitud contiene estudiantes que no pertenecen al curso.',
            ]);
        }

        return DB::transaction(function () use ($datos, $docente, $usuario) {
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

            abort_unless($asistencia->est_asi_cla === 'ABIERTA', 422, 'La sesión de asistencia está cerrada.');

            foreach (($datos['asistencias'] ?? []) as $codEst => $registro) {
                $estado = EstadoAsistencia::query()
                    ->where('cod_est_asi', $registro['cod_est_asi'] ?? null)
                    ->where('est_est_asi', 'ACTIVO')
                    ->first();

                if (! $estado) {
                    throw ValidationException::withMessages([
                        'asistencias' => 'Uno de los estados de asistencia no es válido.',
                    ]);
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

            BitacoraService::registrar(
                accion: 'REGISTRAR_ASISTENCIA_CURSO',
                tabla: 'asistencia_clase',
                registro: $asistencia->cod_asi_cla,
                modulo: 'Aula Virtual',
                nombreRegistro: $datos['cod_cla'],
                descripcion: 'Se registró asistencia validando la pertenencia de cada estudiante al curso.',
                valoresNuevos: ['estudiantes' => count($datos['asistencias'] ?? [])]
            );

            return $asistencia;
        });
    }
}
