<?php

namespace App\Services\AulaVirtual;

use App\Models\AulaVirtual\CalificacionTarea;
use App\Models\AulaVirtual\EntregaArchivo;
use App\Models\AulaVirtual\EntregaTarea;
use App\Models\AulaVirtual\Tarea;
use App\Models\Docente;
use App\Models\Estudiante;
use Illuminate\Http\UploadedFile;

class EntregaService
{
    public function guardarEntrega(Tarea $tarea, Estudiante $estudiante, array $datos, ?UploadedFile $archivo = null): EntregaTarea
    {
        $entrega = EntregaTarea::firstOrNew([
            'cod_tar' => $tarea->cod_tar,
            'cod_est' => $estudiante->cod_est,
        ]);

        abort_if($entrega->exists && $entrega->estaCalificada(), 422, 'La entrega ya fue revisada.');

        $tardia = $tarea->vencida();
        $entrega->fill([
            'tex_ent' => $datos['tex_ent'] ?? null,
            'est_ent' => ($datos['accion'] ?? 'guardar') === 'enviar'
                ? ($tardia ? 'ENTREGADO_TARDE' : 'ENTREGADO')
                : 'PENDIENTE',
            'fec_ent' => ($datos['accion'] ?? 'guardar') === 'enviar' ? now() : $entrega->fec_ent,
        ])->save();

        if ($archivo) {
            $ruta = $archivo->store('aula-virtual/entregas');

            EntregaArchivo::create([
                'cod_ent' => $entrega->cod_ent,
                'nom_arc' => $archivo->getClientOriginalName(),
                'rut_arc' => $ruta,
                'mime_arc' => $archivo->getMimeType(),
                'tam_arc' => $archivo->getSize(),
                'est_arc' => 'ACTIVO',
            ]);
        }

        return $entrega;
    }

    public function calificar(EntregaTarea $entrega, Docente $docente, float $puntaje, ?string $retroalimentacion): CalificacionTarea
    {
        $tarea = $entrega->tarea;
        abort_if(! $tarea || $puntaje > (float) $tarea->pun_max_tar, 422, 'La nota no puede superar el puntaje maximo.');

        $calificacion = CalificacionTarea::updateOrCreate(
            ['cod_ent' => $entrega->cod_ent],
            [
                'cod_tar' => $entrega->cod_tar,
                'cod_est' => $entrega->cod_est,
                'cod_doc' => $docente->cod_doc,
                'pun_obt' => $puntaje,
                'pun_max' => $tarea->pun_max_tar,
                'com_cal' => $retroalimentacion,
                'fec_cal' => now(),
                'est_cal' => 'REGISTRADO',
            ]
        );

        $entrega->marcarCalificada();

        return $calificacion;
    }
}
