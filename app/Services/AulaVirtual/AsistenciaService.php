<?php

namespace App\Services\AulaVirtual;

use App\Models\AulaVirtual\AsistenciaClase;
use App\Models\AulaVirtual\AsistenciaEstudiante;
use App\Models\AulaVirtual\ClaseVirtual;
use App\Models\AulaVirtual\EstadoAsistencia;
use App\Models\Docente;
use App\Models\User;
use App\Services\BitacoraService;
use App\Support\AulaVirtual\AsistenciaInteligente;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class AsistenciaService
{
    /**
     * Guarda y consolida la asistencia de una clase virtual.
     */
    public function guardar(array $datos, Docente $docente, User $usuario): AsistenciaClase
    {
        $codCla = $datos['cod_cla'] ?? '';
        $clase = ClaseVirtual::with('planAsignatura')->find($codCla);

        if (! $clase || ($clase->planAsignatura && $clase->planAsignatura->cod_doc !== $docente->cod_doc)) {
            abort(403, 'No estás autorizado para registrar asistencia en este curso.');
        }
        Gate::forUser($usuario)->authorize('create', [AsistenciaClase::class, $clase]);

        $fecha = Carbon::parse($datos['fec_asi_cla'] ?? now()->toDateString())->format('Y-m-d');
        if (Carbon::parse($fecha)->isFuture()) {
            throw ValidationException::withMessages([
                'fecha' => 'La fecha de asistencia no puede ser posterior al día de hoy.',
            ]);
        }

        $asistenciasInput = $datos['asistencias'] ?? [];

        // Ejecutar soporte inteligente
        $soporte = app(AsistenciaInteligente::class);
        $mapeoSimple = [];
        foreach ($asistenciasInput as $codEst => $reg) {
            $mapeoSimple[$codEst] = is_array($reg) ? ($reg['cod_est_asi'] ?? '') : (string) $reg;
        }

        $analisis = $soporte->analizarSesion(
            codCla: $codCla,
            estudiantesMarcados: $mapeoSimple,
            fecha: $fecha,
            modoCierre: true,
            codHbl: $datos['cod_hbl'] ?? null,
        );

        if (! ($analisis['puede_guardar'] ?? false)) {
            $primerBloqueo = $analisis['bloqueos'][0] ?? 'La sesión de asistencia presenta inconsistencias que impiden su consolidación.';
            throw ValidationException::withMessages(['asistencia' => $primerBloqueo]);
        }

        // Verificar estados con observación obligatoria
        $estadosRequeridos = EstadoAsistencia::where('requiere_observacion', true)->pluck('cod_est_asi')->toArray();

        return DB::transaction(function () use ($datos, $codCla, $docente, $usuario, $fecha, $asistenciasInput, $estadosRequeridos, $mapeoSimple) {
            ClaseVirtual::whereKey($codCla)->lockForUpdate()->firstOrFail();
            $revalidacion = app(AsistenciaInteligente::class)->analizarSesion($codCla, $mapeoSimple, $fecha, true, $datos['cod_hbl'] ?? null);
            if (! $revalidacion['puede_guardar']) {
                throw ValidationException::withMessages(['asistencia' => implode(' ', $revalidacion['bloqueos'])]);
            }
            $estudiantesValidos = $revalidacion['datos_calculados']['estudiantes_oficiales'];
            $existente = AsistenciaClase::where('cod_cla', $codCla)->where('cod_hbl', $datos['cod_hbl'] ?? null)->whereDate('fec_asi_cla', $fecha)->lockForUpdate()->first();
            if ($existente && $existente->est_asi_cla === 'ANULADA') {
                throw ValidationException::withMessages(['asistencia' => 'La sesión anulada conserva su historia y no puede sobrescribirse.']);
            }
            if ($existente && $existente->est_asi_cla === 'CERRADA' && trim($datos['motivo_rectificacion'] ?? '') === '') {
                throw ValidationException::withMessages(['asistencia' => 'La asistencia está cerrada. La rectificación requiere autorización y motivo.']);
            }
            $antes = $existente ? AsistenciaEstudiante::where('cod_asi_cla', $existente->cod_asi_cla)->get()->toArray() : null;
            $asistencia = $existente ?? AsistenciaClase::firstOrCreate(
                [
                    'cod_cla' => $codCla,
                    'cod_doc' => $docente->cod_doc,
                    'fec_asi_cla' => $fecha,
                    'cod_hbl' => $datos['cod_hbl'] ?? null,
                ],
                [
                    'cod_usu_reg' => $usuario->cod_usu,
                    'tip_asi_cla' => $datos['tip_asi_cla'] ?? 'CLASE',
                    'tit_asi_cla' => $datos['tit_asi_cla'] ?? ('Sesión del '.Carbon::parse($fecha)->format('d/m/Y')),
                    'obs_asi_cla' => $datos['obs_asi_cla'] ?? null,
                    'ori_asi_cla' => 'MANUAL',
                    'est_asi_cla' => 'CERRADA',
                ]
            );

            // Actualizar si ya existía como borrador
            if ($asistencia->est_asi_cla === 'BORRADOR' || $asistencia->est_asi_cla === 'ABIERTA') {
                $asistencia->update([
                    'est_asi_cla' => 'CERRADA',
                    'obs_asi_cla' => $datos['obs_asi_cla'] ?? $asistencia->obs_asi_cla,
                ]);
            }

            foreach ($estudiantesValidos as $codEst) {
                $registro = $asistenciasInput[$codEst] ?? null;
                $codEstAsi = is_array($registro) ? ($registro['cod_est_asi'] ?? null) : $registro;
                $obsEst = is_array($registro) ? ($registro['obs_asi_est'] ?? null) : ($datos['observaciones'][$codEst] ?? null);
                $minRetraso = is_array($registro) ? max(0, (int) ($registro['min_retraso'] ?? 0)) : 0;

                if (! $codEstAsi) {
                    continue;
                }

                if (in_array($codEstAsi, $estadosRequeridos, true) && empty(trim((string) $obsEst))) {
                    throw ValidationException::withMessages([
                        "asistencias.{$codEst}.obs_asi_est" => 'El estado seleccionado para este estudiante requiere una justificación u observación obligatoria.',
                    ]);
                }

                AsistenciaEstudiante::updateOrCreate(
                    [
                        'cod_asi_cla' => $asistencia->cod_asi_cla,
                        'cod_est' => $codEst,
                    ],
                    [
                        'cod_est_asi' => $codEstAsi,
                        'cod_usu_reg' => $usuario->cod_usu,
                        'min_retraso' => $minRetraso,
                        'obs_asi_est' => $obsEst ? trim($obsEst) : null,
                        'fec_reg_asi_est' => now(),
                        'est_asi_est' => $antes !== null ? 'RECTIFICADO' : 'REGISTRADO',
                    ]
                );
            }

            if (class_exists(BitacoraService::class)) {
                app(BitacoraService::class)->registrar(
                    accion: $antes !== null ? 'ASISTENCIA_RECTIFICADA' : 'CONSOLIDAR_ASISTENCIA',
                    tabla: 'asistencia_clase',
                    registro: $asistencia->cod_asi_cla,
                    descripcion: "Se consolidó la asistencia para la clase {$codCla} en fecha {$fecha}. ".trim($datos['motivo_rectificacion'] ?? ''),
                    nivel: 'SUCCESS',
                    valoresAnteriores: $antes,
                    valoresNuevos: AsistenciaEstudiante::where('cod_asi_cla', $asistencia->cod_asi_cla)->get()->toArray(),
                );
            }

            return $asistencia;
        });
    }
}
