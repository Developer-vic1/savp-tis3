<?php

namespace App\Services\AulaVirtual;

use App\Models\AulaVirtual\CalificacionTarea;
use App\Models\AulaVirtual\ClaseVirtual;
use App\Models\AulaVirtual\EntregaArchivo;
use App\Models\AulaVirtual\EntregaTarea;
use App\Models\AulaVirtual\Tarea;
use App\Models\Docente;
use App\Models\Estudiante;
use App\Services\BitacoraService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EntregaService
{
    public function guardarEntrega(Tarea $tarea, Estudiante $estudiante, array $datos, ?UploadedFile $archivo = null): EntregaTarea
    {
        $user = auth()->user();
        abort_unless($user && app(CursoVirtualService::class)->estudianteDeUsuario($user)?->cod_est === $estudiante->cod_est, 403);
        Gate::authorize('submit', $tarea);
        $datos = validator($datos, [
            'tex_ent' => ['nullable', 'string', 'max:20000'], 'accion' => ['required', 'in:guardar,enviar'],
        ])->validate();
        validator(['archivo' => $archivo], ['archivo' => ['nullable', 'file', 'max:10240', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,txt,zip']])->validate();
        $path = null;
        try {
            return DB::transaction(function () use ($tarea, $estudiante, $datos, $archivo, &$path) {
                $class = ClaseVirtual::lockForUpdate()->findOrFail($tarea->cod_cla);
                abort_unless($class->est_cla === 'ACTIVA', 422);
                $task = Tarea::lockForUpdate()->findOrFail($tarea->cod_tar);
                Gate::authorize('submit', $task);
                abort_unless($task->puedeRecibirEntregas(), 422, 'La tarea no recibe entregas.');
                $entrega = EntregaTarea::where('cod_tar', $task->cod_tar)->where('cod_est', $estudiante->cod_est)->lockForUpdate()->first()
                    ?? new EntregaTarea(['cod_ent' => 'ENT_'.Str::upper(Str::random(16)), 'cod_tar' => $task->cod_tar, 'cod_est' => $estudiante->cod_est]);
                abort_if($entrega->exists && in_array($entrega->est_ent, ['ENTREGADO', 'ENTREGADO_TARDE', 'CALIFICADO', 'ANULADO'], true), 422, 'La tarea ya fue enviada y no puede modificarse.');
                if ($datos['accion'] === 'enviar') {
                    abort_if(blank($datos['tex_ent'] ?? null) && ! $archivo && ! ($entrega->exists && $entrega->archivos()->where('est_arc', 'ACTIVO')->exists()), 422, 'Escribe una respuesta o adjunta un archivo.');
                }
                $before = $entrega->exists ? $entrega->only(['est_ent', 'fec_ent']) : [];
                $entrega->fill([
                    'tex_ent' => $datos['tex_ent'] ?? null,
                    'est_ent' => $datos['accion'] === 'enviar' ? ($task->vencida() ? 'ENTREGADO_TARDE' : 'ENTREGADO') : 'PENDIENTE',
                    'fec_ent' => $datos['accion'] === 'enviar' ? now() : $entrega->fec_ent,
                ])->save();
                if ($archivo) {
                    $path = $archivo->store('aula-virtual/entregas', 'local');
                    abort_unless($path, 503, 'No fue posible guardar el archivo.');
                    EntregaArchivo::create([
                        'cod_ent_arc' => 'ENTA_'.Str::upper(Str::random(15)), 'cod_ent' => $entrega->cod_ent,
                        'nom_arc' => Str::limit($archivo->getClientOriginalName(), 180, ''), 'rut_arc' => $path,
                        'mime_arc' => $archivo->getMimeType(), 'tam_arc' => $archivo->getSize(), 'est_arc' => 'ACTIVO',
                    ]);
                }
                BitacoraService::registrar(accion: 'GUARDAR_ENTREGA', tabla: 'entrega_tarea', registro: $entrega->cod_ent,
                    modulo: 'Aula Virtual', valoresAnteriores: $before, valoresNuevos: $entrega->only(['est_ent', 'fec_ent']));

                return $entrega;
            });
        } catch (\Throwable $error) {
            if ($path) {
                Storage::disk('local')->delete($path);
            }
            throw $error;
        }
    }

    public function calificar(EntregaTarea $entrega, Docente $docente, float $puntaje, ?string $retroalimentacion): CalificacionTarea
    {
        Gate::authorize('grade', $entrega);
        abort_unless(app(CursoVirtualService::class)->docenteDeUsuario(auth()->user())?->cod_doc === $docente->cod_doc, 403);

        return DB::transaction(function () use ($entrega, $docente, $puntaje, $retroalimentacion) {
            $class = ClaseVirtual::lockForUpdate()->findOrFail($entrega->tarea->cod_cla);
            abort_unless($class->est_cla === 'ACTIVA', 422);
            $locked = EntregaTarea::with('tarea')->lockForUpdate()->findOrFail($entrega->cod_ent);
            Gate::authorize('grade', $locked);
            abort_unless(in_array($locked->est_ent, ['ENTREGADO', 'ENTREGADO_TARDE', 'CALIFICADO'], true), 422, 'Esta entrega no está disponible para calificación.');
            abort_unless(is_finite($puntaje) && $puntaje >= 0 && $puntaje <= (float) $locked->tarea->pun_max_tar, 422, 'El puntaje debe estar entre cero y el máximo de la tarea.');
            $grade = CalificacionTarea::where('cod_ent', $locked->cod_ent)->first();
            abort_if($grade && blank($retroalimentacion), 422, 'La rectificación requiere un motivo en la retroalimentación.');
            $before = $grade?->only(['pun_obt', 'pun_max', 'est_cal']);
            $grade ??= new CalificacionTarea(['cod_cal_tar' => 'CALT_'.Str::upper(Str::random(15)), 'cod_ent' => $locked->cod_ent]);
            $grade->fill([
                'cod_tar' => $locked->cod_tar, 'cod_est' => $locked->cod_est, 'cod_doc' => $docente->cod_doc,
                'pun_obt' => $puntaje, 'pun_max' => $locked->tarea->pun_max_tar, 'com_cal' => $retroalimentacion,
                'fec_cal' => now(), 'est_cal' => $grade->exists ? 'RECTIFICADO' : 'REGISTRADO',
            ])->save();
            $locked->marcarCalificada();
            BitacoraService::registrar(accion: $before ? 'RECTIFICAR_NOTA_LMS' : 'CALIFICAR_ENTREGA', tabla: 'calificacion_tarea',
                registro: $grade->cod_cal_tar, modulo: 'Aula Virtual', valoresAnteriores: $before, valoresNuevos: $grade->only(['pun_obt', 'pun_max', 'est_cal']));

            return $grade;
        });
    }

    public function devolver(EntregaTarea $entrega, string $reason): void
    {
        Gate::authorize('returnForCorrection', $entrega);
        abort_if(blank($reason), 422, 'La devolución requiere un motivo.');
        DB::transaction(function () use ($entrega, $reason) {
            $class = ClaseVirtual::lockForUpdate()->findOrFail($entrega->tarea->cod_cla);
            abort_unless($class->est_cla === 'ACTIVA', 422);
            $locked = EntregaTarea::lockForUpdate()->findOrFail($entrega->cod_ent);
            Gate::authorize('returnForCorrection', $locked);
            abort_unless(in_array($locked->est_ent, ['ENTREGADO', 'ENTREGADO_TARDE'], true), 422, 'Solo puedes devolver entregas pendientes de calificación.');
            $before = $locked->only('est_ent');
            $locked->devolver($reason);
            BitacoraService::registrar(accion: 'DEVOLVER_ENTREGA', tabla: 'entrega_tarea', registro: $locked->cod_ent,
                modulo: 'Aula Virtual', valoresAnteriores: $before, valoresNuevos: ['est_ent' => 'DEVUELTO']);
        });
    }
}
