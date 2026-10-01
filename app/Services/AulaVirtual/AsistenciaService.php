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
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AsistenciaService
{
    public function guardar(array $datos, Docente $docente, User $usuario): AsistenciaClase
    {
        abort_unless($usuario->est_usu === 'ACTIVO' && $usuario->can('Aula_Virtual_Docente') && $usuario->can('Asistencia_Aula')
            && app(CursoVirtualService::class)->docenteDeUsuario($usuario)?->cod_doc === $docente->cod_doc, 403);
        $datos = validator($datos, [
            'cod_cla' => ['required', 'string'], 'fec_asi_cla' => ['required', 'date'],
            'tit_asi_cla' => ['nullable', 'string', 'max:150'], 'obs_asi_cla' => ['nullable', 'string', 'max:2000'],
            'asistencias' => ['required', 'array', 'min:1'], 'asistencias.*.cod_est_asi' => ['required', 'string'],
            'asistencias.*.min_retraso' => ['nullable', 'integer', 'min:0', 'max:300'],
            'asistencias.*.obs_asi_est' => ['nullable', 'string', 'max:1000'],
        ])->validate();
        $clase = ClaseVirtual::query()
            ->with('estudiantes')
            ->where('cod_cla', $datos['cod_cla'])
            ->where('est_cla', 'ACTIVA')
            ->whereHas('planAsignatura', fn ($query) => $query->where('cod_doc', $docente->cod_doc))
            ->firstOrFail();

        $allowedStudents = app(CursoVirtualService::class)->estudiantesVigentes($clase)
            ->pluck('cod_est')
            ->all();
        $foreignStudents = array_diff(array_keys($datos['asistencias'] ?? []), $allowedStudents);

        if ($foreignStudents !== []) {
            throw ValidationException::withMessages([
                'asistencias' => 'La solicitud contiene estudiantes que no pertenecen al curso.',
            ]);
        }

        return DB::transaction(function () use ($datos, $docente, $usuario) {
            // Serializa la sesión antes de firstOrCreate: también protege el primer registro concurrente.
            $class = ClaseVirtual::lockForUpdate()->findOrFail($datos['cod_cla']);
            abort_unless(app(CursoVirtualService::class)->cursoParaDocente($usuario, $class->cod_cla) && $class->est_cla === 'ACTIVA', 403);
            $allowed = app(CursoVirtualService::class)->estudiantesVigentes($class)->lockForUpdate()->pluck('cod_est')->all();
            if (array_diff(array_keys($datos['asistencias']), $allowed)) {
                throw ValidationException::withMessages(['asistencias' => 'La pertenencia al curso cambió. Revisa la lista e inténtalo de nuevo.']);
            }
            $asistencia = AsistenciaClase::firstOrCreate(
                [
                    'cod_cla' => $datos['cod_cla'],
                    'cod_doc' => $docente->cod_doc,
                    'fec_asi_cla' => $datos['fec_asi_cla'],
                    'cod_hbl' => $datos['cod_hbl'] ?? null,
                ],
                [
                    'cod_asi_cla' => 'ASIC_'.Str::upper(Str::random(15)),
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

                $record = AsistenciaEstudiante::firstOrNew(['cod_asi_cla' => $asistencia->cod_asi_cla, 'cod_est' => $codEst]);
                $changed = $record->exists && ($record->cod_est_asi !== $estado->cod_est_asi
                    || (int) $record->min_retraso !== (int) ($registro['min_retraso'] ?? 0));
                if ($changed && blank($registro['obs_asi_est'] ?? null)) {
                    throw ValidationException::withMessages(['asistencias' => 'La rectificación de asistencia requiere un motivo en la observación.']);
                }
                if (! $record->exists) {
                    $record->cod_asi_est = 'ASIE_'.Str::upper(Str::random(15));
                }
                $record->fill([
                    'cod_est_asi' => $estado->cod_est_asi,
                    'cod_usu_reg' => $usuario->cod_usu,
                    'min_retraso' => max(0, (int) ($registro['min_retraso'] ?? 0)),
                    'obs_asi_est' => $registro['obs_asi_est'] ?? null,
                    'fec_reg_asi_est' => now(),
                    'est_asi_est' => $changed ? 'RECTIFICADO' : ($record->est_asi_est ?? 'REGISTRADO'),
                ])->save();
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
