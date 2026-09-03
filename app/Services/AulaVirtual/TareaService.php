<?php

namespace App\Services\AulaVirtual;

use App\Models\AulaVirtual\ClaseVirtual;
use App\Models\AulaVirtual\Tarea;
use App\Models\Docente;
use App\Models\User;
use App\Services\BitacoraService;
use App\Support\AulaVirtual\TareaInteligente;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TareaService
{
    /**
     * Crea una nueva tarea o actividad académica.
     */
    public function crear(array $datos, Docente $docente, User $usuario): Tarea
    {
        $codCla = $datos['cod_cla'] ?? '';
        $clase = ClaseVirtual::with('planAsignatura')->find($codCla);

        if (! $clase || ($clase->planAsignatura && $clase->planAsignatura->cod_doc !== $docente->cod_doc)) {
            abort(403, 'No estás autorizado para crear tareas en este curso.');
        }

        // Ejecutar soporte inteligente
        $soporte = app(TareaInteligente::class);
        $analisis = $soporte->analizar([
            'cod_cla' => $codCla,
            'tit_tar' => $datos['tit_tar'] ?? '',
            'des_tar' => $datos['des_tar'] ?? '',
            'tip_tar' => $datos['tip_tar'] ?? 'TAREA',
            'fec_pub_tar' => $datos['fec_pub_tar'] ?? now(),
            'fec_lim_tar' => $datos['fec_lim_tar'] ?? null,
            'pun_max_tar' => $datos['pun_max_tar'] ?? 100,
        ]);

        if (! ($analisis['puede_guardar'] ?? false)) {
            $primerBloqueo = $analisis['bloqueos'][0] ?? 'La tarea no cumple con los criterios pedagógicos e institucionales.';
            throw ValidationException::withMessages(['tarea' => $primerBloqueo]);
        }

        return DB::transaction(function () use ($datos, $codCla, $docente) {
            $ultimo = Tarea::where('cod_tar', 'like', 'TAR_%')
                ->orderByDesc('cod_tar')
                ->value('cod_tar');
            $num = $ultimo ? ((int) str_replace('TAR_', '', $ultimo)) + 1 : 1;
            $codTar = 'TAR_' . str_pad($num, 5, '0', STR_PAD_LEFT);

            $tarea = Tarea::create([
                'cod_tar' => $codTar,
                'cod_cla' => $codCla,
                'cod_doc' => $docente->cod_doc,
                'tit_tar' => trim($datos['tit_tar']),
                'des_tar' => isset($datos['des_tar']) ? trim($datos['des_tar']) : null,
                'tip_tar' => $datos['tip_tar'] ?? 'TAREA',
                'fec_pub_tar' => $datos['fec_pub_tar'] ?? now(),
                'fec_lim_tar' => $datos['fec_lim_tar'] ?? null,
                'pun_max_tar' => (float) ($datos['pun_max_tar'] ?? 100),
                'perm_ent_tardia' => (bool) ($datos['perm_ent_tardia'] ?? false),
                'est_tar' => in_array($datos['est_tar'] ?? '', ['BORRADOR', 'PUBLICADA'], true) ? $datos['est_tar'] : 'PUBLICADA',
            ]);

            if (class_exists(BitacoraService::class)) {
                app(BitacoraService::class)->registrar(
                    accion: 'CREAR_TAREA',
                    tabla: 'tarea',
                    registro: $tarea->cod_tar,
                    descripcion: "Se creó la actividad '{$tarea->tit_tar}' para el curso {$codCla}.",
                    nivel: 'SUCCESS'
                );
            }

            return $tarea;
        });
    }

    /**
     * Actualiza una tarea existente.
     */
    public function actualizar(Tarea $tarea, array $datos, Docente $docente, User $usuario): Tarea
    {
        $tarea->loadMissing('claseVirtual.planAsignatura');
        if ($tarea->claseVirtual && $tarea->claseVirtual->planAsignatura && $tarea->claseVirtual->planAsignatura->cod_doc !== $docente->cod_doc) {
            abort(403, 'No estás autorizado para modificar esta tarea.');
        }

        $soporte = app(TareaInteligente::class);
        $analisis = $soporte->analizar([
            'cod_cla' => $tarea->cod_cla,
            'tit_tar' => $datos['tit_tar'] ?? $tarea->tit_tar,
            'des_tar' => $datos['des_tar'] ?? $tarea->des_tar,
            'tip_tar' => $datos['tip_tar'] ?? $tarea->tip_tar,
            'fec_pub_tar' => $datos['fec_pub_tar'] ?? $tarea->fec_pub_tar,
            'fec_lim_tar' => $datos['fec_lim_tar'] ?? $tarea->fec_lim_tar,
            'pun_max_tar' => $datos['pun_max_tar'] ?? $tarea->pun_max_tar,
        ], $tarea->cod_tar);

        if (! ($analisis['puede_guardar'] ?? false)) {
            $primerBloqueo = $analisis['bloqueos'][0] ?? 'La modificación no cumple con los criterios requeridos.';
            throw ValidationException::withMessages(['tarea' => $primerBloqueo]);
        }

        return DB::transaction(function () use ($tarea, $datos) {
            $tarea->update([
                'tit_tar' => isset($datos['tit_tar']) ? trim($datos['tit_tar']) : $tarea->tit_tar,
                'des_tar' => isset($datos['des_tar']) ? trim($datos['des_tar']) : $tarea->des_tar,
                'tip_tar' => $datos['tip_tar'] ?? $tarea->tip_tar,
                'fec_pub_tar' => $datos['fec_pub_tar'] ?? $tarea->fec_pub_tar,
                'fec_lim_tar' => $datos['fec_lim_tar'] ?? $tarea->fec_lim_tar,
                'pun_max_tar' => isset($datos['pun_max_tar']) ? (float) $datos['pun_max_tar'] : $tarea->pun_max_tar,
                'perm_ent_tardia' => isset($datos['perm_ent_tardia']) ? (bool) $datos['perm_ent_tardia'] : $tarea->perm_ent_tardia,
                'est_tar' => $datos['est_tar'] ?? $tarea->est_tar,
            ]);

            if (class_exists(BitacoraService::class)) {
                app(BitacoraService::class)->registrar(
                    accion: 'ACTUALIZAR_TAREA',
                    tabla: 'tarea',
                    registro: $tarea->cod_tar,
                    descripcion: "Se actualizó la actividad '{$tarea->tit_tar}'.",
                    nivel: 'SUCCESS'
                );
            }

            return $tarea;
        });
    }

    public function publicar(Tarea $tarea): void
    {
        $tarea->update(['est_tar' => 'PUBLICADA']);
    }

    public function cerrar(Tarea $tarea): void
    {
        $tarea->update(['est_tar' => 'CERRADA']);
    }

    public function anular(Tarea $tarea): void
    {
        $tarea->update(['est_tar' => 'ANULADA']);
    }
}
