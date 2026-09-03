<?php

namespace App\Services\AulaVirtual;

use App\Models\AulaVirtual\CalificacionTarea;
use App\Models\AulaVirtual\EntregaArchivo;
use App\Models\AulaVirtual\EntregaTarea;
use App\Models\AulaVirtual\Tarea;
use App\Models\Docente;
use App\Models\Estudiante;
use App\Models\User;
use App\Services\BitacoraService;
use App\Support\AulaVirtual\CalificacionTareaInteligente;
use App\Support\AulaVirtual\EntregaTareaInteligente;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class EntregaService
{
    /**
     * Registra o actualiza la entrega de un estudiante.
     */
    public function guardarEntrega(Tarea $tarea, Estudiante $estudiante, array $datos, ?UploadedFile $archivo = null, ?User $usuario = null): EntregaTarea
    {
        $tarea->loadMissing('claseVirtual');

        $entrega = EntregaTarea::with('archivos')->firstOrNew([
            'cod_tar' => $tarea->cod_tar,
            'cod_est' => $estudiante->cod_est,
        ]);

        if ($entrega->exists && in_array($entrega->est_ent, ['CALIFICADO', 'ANULADO'], true)) {
            throw ValidationException::withMessages([
                'entrega' => 'La tarea ya fue calificada o anulada y no admite modificaciones.',
            ]);
        }

        $accion = $datos['accion'] ?? 'enviar';
        $esEnvioDefinitivo = $accion === 'enviar';

        $archivosInfo = [];
        if ($archivo) {
            $archivosInfo[] = ['nombre' => $archivo->getClientOriginalName(), 'tamano' => $archivo->getSize()];
        } elseif ($entrega->exists && $entrega->archivos->where('est_arc', 'ACTIVO')->isNotEmpty()) {
            $archivosInfo[] = ['nombre' => 'archivo_existente'];
        }

        // Ejecutar soporte inteligente
        $soporte = app(EntregaTareaInteligente::class);
        $analisis = $soporte->analizarEnvio(
            codTar: $tarea->cod_tar,
            codEst: $estudiante->cod_est,
            datos: [
                'tex_ent' => $datos['tex_ent'] ?? '',
                'archivos' => $archivosInfo,
                'fec_ent' => now(),
            ]
        );

        if ($esEnvioDefinitivo && ! ($analisis['puede_guardar'] ?? false)) {
            $primerBloqueo = $analisis['bloqueos'][0] ?? 'La entrega no cumple con los requisitos mínimos (no puede enviarse vacía).';
            throw ValidationException::withMessages(['entrega' => $primerBloqueo]);
        }

        $esTardia = $analisis['datos_calculados']['es_tardia'] ?? false;
        $disco = config('savp.aula_virtual.entregas.disco_almacenamiento', 'local');
        $rutaArchivoGuardado = null;

        return DB::transaction(function () use ($tarea, $estudiante, $entrega, $datos, $archivo, $esEnvioDefinitivo, $esTardia, $disco, &$rutaArchivoGuardado, $usuario) {
            try {
                if ($archivo) {
                    $rutaArchivoGuardado = $archivo->store('aula-virtual/entregas', $disco);
                }

                if (! $entrega->exists) {
                    $ultimo = EntregaTarea::where('cod_ent', 'like', 'ENT_%')
                        ->orderByDesc('cod_ent')
                        ->value('cod_ent');
                    $num = $ultimo ? ((int) str_replace('ENT_', '', $ultimo)) + 1 : 1;
                    $entrega->cod_ent = 'ENT_' . str_pad($num, 5, '0', STR_PAD_LEFT);
                }

                $estadoFinal = $esEnvioDefinitivo
                    ? ($esTardia ? 'ENTREGADO_TARDE' : 'ENTREGADO')
                    : ($entrega->est_ent === 'DEVUELTO' ? 'DEVUELTO' : 'PENDIENTE');

                $entrega->fill([
                    'tex_ent' => isset($datos['tex_ent']) ? trim($datos['tex_ent']) : null,
                    'est_ent' => $estadoFinal,
                    'fec_ent' => $entrega->fec_ent ?: ($esEnvioDefinitivo ? now() : null),
                ])->save();

                if ($archivo && $rutaArchivoGuardado) {
                    $ultimoArc = EntregaArchivo::where('cod_ent_arc', 'like', 'ENTA_%')
                        ->orderByDesc('cod_ent_arc')
                        ->value('cod_ent_arc');
                    $numArc = $ultimoArc ? ((int) str_replace('ENTA_', '', $ultimoArc)) + 1 : 1;
                    $codEntArc = 'ENTA_' . str_pad($numArc, 4, '0', STR_PAD_LEFT);

                    EntregaArchivo::create([
                        'cod_ent_arc' => $codEntArc,
                        'cod_ent' => $entrega->cod_ent,
                        'nom_arc' => $archivo->getClientOriginalName(),
                        'rut_arc' => $rutaArchivoGuardado,
                        'mime_arc' => $archivo->getMimeType(),
                        'tam_arc' => $archivo->getSize(),
                        'est_arc' => 'ACTIVO',
                    ]);
                }

                if (class_exists(BitacoraService::class)) {
                    app(BitacoraService::class)->registrar(
                        accion: 'GUARDAR_ENTREGA',
                        tabla: 'entrega_tarea',
                        registro: $entrega->cod_ent,
                        descripcion: "Estudiante {$estudiante->cod_est} registró entrega en tarea {$tarea->cod_tar} ({$estadoFinal}).",
                        nivel: 'SUCCESS'
                    );
                }

                return $entrega;
            } catch (\Throwable $e) {
                // Eliminar archivo físico huérfano si falló la base de datos
                if ($rutaArchivoGuardado && Storage::disk($disco)->exists($rutaArchivoGuardado)) {
                    Storage::disk($disco)->delete($rutaArchivoGuardado);
                }
                throw $e;
            }
        });
    }

    /**
     * Califica una entrega docente.
     */
    public function calificar(EntregaTarea $entrega, Docente $docente, float $puntaje, ?string $retroalimentacion, ?User $usuario = null): CalificacionTarea
    {
        $entrega->loadMissing(['tarea.claseVirtual.planAsignatura', 'estudiante']);
        $tarea = $entrega->tarea;

        if (! $tarea || ($tarea->claseVirtual && $tarea->claseVirtual->planAsignatura && $tarea->claseVirtual->planAsignatura->cod_doc !== $docente->cod_doc)) {
            abort(403, 'No estás autorizado para calificar entregas de este curso.');
        }

        $max = (float) $tarea->pun_max_tar;
        if ($puntaje < 0 || $puntaje > $max) {
            throw ValidationException::withMessages([
                'puntaje' => "El puntaje debe encontrarse entre 0 y {$max} puntos.",
            ]);
        }

        // Ejecutar soporte inteligente
        $soporte = app(CalificacionTareaInteligente::class);
        $analisis = $soporte->analizarCalificacion(
            codEnt: $entrega->cod_ent,
            puntaje: $puntaje,
            retroalimentacion: $retroalimentacion
        );

        if (! ($analisis['puede_guardar'] ?? false)) {
            $primerBloqueo = $analisis['bloqueos'][0] ?? 'La calificación no cumple con los criterios reglamentarios.';
            throw ValidationException::withMessages(['calificacion' => $primerBloqueo]);
        }

        return DB::transaction(function () use ($entrega, $tarea, $docente, $puntaje, $retroalimentacion, $max, $usuario) {
            $calificacion = CalificacionTarea::where('cod_ent', $entrega->cod_ent)->first();
            $esRectificacion = (bool) $calificacion;

            $calificacion = CalificacionTarea::updateOrCreate(
                ['cod_ent' => $entrega->cod_ent],
                [
                    'cod_tar' => $entrega->cod_tar,
                    'cod_est' => $entrega->cod_est,
                    'cod_doc' => $docente->cod_doc,
                    'pun_obt' => $puntaje,
                    'pun_max' => $max,
                    'com_cal' => $retroalimentacion ? trim($retroalimentacion) : null,
                    'fec_cal' => now(),
                    'est_cal' => $esRectificacion ? 'RECTIFICADO' : 'REGISTRADO',
                ]
            );

            $entrega->update(['est_ent' => 'CALIFICADO']);

            if (class_exists(BitacoraService::class)) {
                app(BitacoraService::class)->registrar(
                    accion: $esRectificacion ? 'RECTIFICAR_CALIFICACION' : 'CALIFICAR_ENTREGA',
                    tabla: 'calificacion_tarea',
                    registro: $calificacion->cod_cal_tar,
                    descripcion: "Se calificó entrega {$entrega->cod_ent} con nota {$puntaje}/{$max}.",
                    nivel: 'SUCCESS'
                );
            }

            return $calificacion;
        });
    }

    /**
     * Devuelve una entrega para corrección o reentrega.
     */
    public function devolver(EntregaTarea $entrega, Docente $docente, ?string $observacion = null): EntregaTarea
    {
        $entrega->loadMissing('tarea.claseVirtual.planAsignatura');
        $tarea = $entrega->tarea;

        if (! $tarea || ($tarea->claseVirtual && $tarea->claseVirtual->planAsignatura && $tarea->claseVirtual->planAsignatura->cod_doc !== $docente->cod_doc)) {
            abort(403, 'No estás autorizado para devolver entregas de este curso.');
        }

        $entrega->update([
            'est_ent' => 'DEVUELTO',
            'obs_ent' => $observacion ? trim($observacion) : 'Entrega devuelta por el docente para corrección.',
        ]);

        return $entrega;
    }

    /**
     * Descarga segura de archivo de entrega.
     */
    public function descargarArchivo(EntregaArchivo $archivo, User $usuario)
    {
        $disco = config('savp.aula_virtual.entregas.disco_almacenamiento', 'local');
        abort_if(! $archivo->rut_arc || ! Storage::disk($disco)->exists($archivo->rut_arc), 404, 'Archivo no encontrado en el almacenamiento.');

        return Storage::disk($disco)->download($archivo->rut_arc, $archivo->nom_arc);
    }
}
