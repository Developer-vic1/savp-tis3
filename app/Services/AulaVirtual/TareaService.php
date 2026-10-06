<?php

namespace App\Services\AulaVirtual;

use App\Models\Oficial\Academico\Docente;
use App\Models\Oficial\AulaVirtual\ClaseVirtual;
use App\Models\Oficial\AulaVirtual\Tarea;
use App\Models\Oficial\AulaVirtual\TareaMaterial;
use App\Services\BitacoraService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TareaService
{
    public function crear(array $datos, Docente $docente, ?UploadedFile $archivo = null): Tarea
    {
        $user = auth()->user();
        abort_unless($user && app(CursoVirtualService::class)->docenteDeUsuario($user)?->cod_doc === $docente->cod_doc, 403);
        $class = app(CursoVirtualService::class)->cursoParaDocente($user, $datos['cod_cla'] ?? '');
        abort_unless($class && $class->est_cla === 'ACTIVA' && $user->can('Tareas_Aula'), 403);
        Gate::authorize('manage', $class);
        $datos = validator($datos, [
            'cod_cla' => ['required', 'string'], 'tit_tar' => ['required', 'string', 'max:180'],
            'des_tar' => ['nullable', 'string', 'max:10000'],
            'tip_tar' => ['required', 'in:TAREA,PRACTICA,PROYECTO,INVESTIGACION,LABORATORIO,EVALUACION'],
            'fec_lim_tar' => ['nullable', 'date', 'after_or_equal:today'],
            'pun_max_tar' => ['required', 'numeric', 'min:1', 'max:1000'],
            'perm_ent_tardia' => ['nullable', 'boolean'], 'est_tar' => ['required', 'in:BORRADOR,PUBLICADA'],
            'int_tar' => ['nullable', 'integer', 'min:1'],
            'ape_tar' => ['nullable', 'date'], 'cor_tar' => ['nullable', 'date', 'after:ape_tar'],
        ])->validate();
        $datos['cod_doc'] = $docente->cod_doc;
        $datos['est_tar'] = $datos['est_tar'] ?? 'BORRADOR';

        validator(['archivo' => $archivo], ['archivo' => ['nullable', 'file', 'max:10240', 'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,jpg,jpeg,png,txt']])->validate();
        $path = null;
        try {
            return DB::transaction(function () use ($datos, $archivo, &$path) {
                $class = ClaseVirtual::lockForUpdate()->findOrFail($datos['cod_cla']);
                Gate::authorize('manage', $class);
                abort_unless($class->est_cla === 'ACTIVA', 422);
                $task = Tarea::create($datos);
                if ($archivo) {
                    $path = $archivo->store('aula-virtual/tareas', 'local');
                    abort_unless($path, 503, 'No fue posible guardar el archivo.');
                    TareaMaterial::create([
                        'cod_tar' => $task->cod_tar, 'nom_tar_mat' => Str::limit($archivo->getClientOriginalName(), 180, ''),
                        'tip_tar_mat' => 'ARCHIVO', 'rut_tar_mat' => $path,
                        'mime_tar_mat' => $archivo->getMimeType(), 'tam_tar_mat' => $archivo->getSize(), 'est_tar_mat' => 'ACTIVO',
                    ]);
                }
                BitacoraService::registrar(accion: 'CREAR_TAREA', tabla: 'tarea', registro: $task->cod_tar,
                    modulo: 'Aula Virtual', valoresNuevos: $task->only(['cod_cla', 'cod_doc', 'est_tar', 'pun_max_tar']));

                $this->notificarPublicacion($task, $class);

                return $task;
            });
        } catch (\Throwable $error) {
            if ($path) {
                Storage::disk('local')->delete($path);
            }
            throw $error;
        }
    }

    public function publicar(Tarea $tarea): void
    {
        $this->cambiarEstado($tarea, 'PUBLICADA');
    }

    public function cerrar(Tarea $tarea): void
    {
        $this->cambiarEstado($tarea, 'CERRADA');
    }

    private function cambiarEstado(Tarea $task, string $state): void
    {
        $this->actualizar($task, $task->only(['tit_tar', 'des_tar', 'tip_tar', 'fec_lim_tar', 'pun_max_tar', 'perm_ent_tardia'])
            + ['est_tar' => $state, 'motivo' => 'Cambio explícito del estado de la tarea.']);
    }

    public function actualizar(Tarea $task, array $data): void
    {
        Gate::authorize('review', $task);
        abort_unless(auth()->user()->can('Tareas_Aula'), 403);
        $data = validator($data, [
            'tit_tar' => ['required', 'string', 'max:180'], 'des_tar' => ['nullable', 'string', 'max:10000'],
            'tip_tar' => ['required', 'in:TAREA,PRACTICA,PROYECTO,INVESTIGACION,LABORATORIO,EVALUACION'],
            'fec_lim_tar' => ['nullable', 'date'], 'pun_max_tar' => ['required', 'numeric', 'min:1', 'max:1000'],
            'perm_ent_tardia' => ['required', 'boolean'], 'est_tar' => ['required', 'in:BORRADOR,PUBLICADA,CERRADA'],
            'motivo' => ['nullable', 'string', 'max:2000'],
            'int_tar' => ['nullable', 'integer', 'min:1'],
            'ape_tar' => ['nullable', 'date'], 'cor_tar' => ['nullable', 'date', 'after:ape_tar'],
        ])->validate();
        DB::transaction(function () use ($task, $data) {
            $class = ClaseVirtual::lockForUpdate()->findOrFail($task->cod_cla);
            abort_unless($class->est_cla === 'ACTIVA', 422);
            $locked = Tarea::with('claseVirtual')->lockForUpdate()->findOrFail($task->cod_tar);
            Gate::authorize('review', $locked);
            abort_unless($locked->claseVirtual->est_cla === 'ACTIVA' && $locked->est_tar !== 'CERRADA', 422, 'La tarea está cerrada para cambios.');
            $errors = [];
            if ($locked->est_tar === 'PUBLICADA' && $data['est_tar'] === 'BORRADOR') {
                $errors['est_tar'] = 'Una tarea publicada no puede volver a borrador.';
            }
            if ($locked->est_tar === 'BORRADOR' && $data['est_tar'] === 'CERRADA') {
                $errors['est_tar'] = 'Publica la tarea antes de cerrarla.';
            }
            if ($locked->est_tar !== 'BORRADOR' && trim($data['motivo'] ?? '') === '') {
                $errors['motivo'] = 'Describe el motivo del cambio sobre una tarea publicada.';
            }
            if ((float) $locked->pun_max_tar !== (float) $data['pun_max_tar'] && $locked->entregas()->exists()) {
                $errors['pun_max_tar'] = 'No puedes cambiar el puntaje máximo cuando ya hay entregas.';
            }
            if (array_key_exists('int_tar', $data) && (int) ($data['int_tar'] ?? 1) < (int) $locked->entregas()->max('int_ent')) {
                $errors['int_tar'] = 'No puedes reducir el máximo por debajo de los intentos históricos registrados.';
            }
            if ($errors) {
                throw ValidationException::withMessages($errors);
            }
            $before = $locked->only(['est_tar', 'pun_max_tar', 'fec_lim_tar']);
            $reason = $data['motivo'] ?? null;
            unset($data['motivo']);
            $locked->update($data);
            BitacoraService::registrar(accion: 'EDITAR_TAREA', tabla: 'tarea', registro: $locked->cod_tar,
                modulo: 'Aula Virtual', valoresAnteriores: $before,
                valoresNuevos: $locked->only(['est_tar', 'pun_max_tar', 'fec_lim_tar']) + ['motivo' => $reason]);
            if ($before['est_tar'] === 'BORRADOR') {
                $this->notificarPublicacion($locked, $class);
            }
        });
    }

    private function notificarPublicacion(Tarea $tarea, ClaseVirtual $clase): void
    {
        $avisos = app(\App\Services\NotificationService::class);
        if ($tarea->est_tar !== 'PUBLICADA' || ! $clase->vis_cla || ! $avisos->available()) return;
        $plan = $clase->planAsignatura ?? $clase->planEspecialidad;
        if (! $plan || $plan->gestionAcademica?->est_gea !== 'ACTIVA') return;
        $personas = app(CursoVirtualService::class)->estudiantesVigentes($clase)
            ->with('estudiante')->get()->pluck('estudiante.cod_per')->filter()->unique();
        $usuarios = \App\Models\Oficial\Sistema\User::whereIn('cod_per', $personas)->pluck('cod_usu');
        $avisos->publicar([
            'clave_evento' => 'tarea-publicada:'.$tarea->getKey(), 'origen' => 'AULA_VIRTUAL', 'tipo' => 'ACCION',
            'titulo' => 'Tienes una nueva actividad',
            'mensaje' => \Illuminate\Support\Str::limit($clase->nom_cla.': '.$tarea->tit_tar, 450).'. Revisa las indicaciones y la fecha de entrega en tus materias.',
            'permiso' => 'Aula_Virtual_Estudiante', 'ruta' => 'estudiante.materias',
            'cod_gea' => $plan->cod_gea, 'cod_usu_emisor' => auth()->id(),
        ], $usuarios);
    }
}
