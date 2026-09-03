<?php

namespace App\Policies;

use App\Models\AulaVirtual\EntregaTarea;
use App\Models\AulaVirtual\Tarea;
use App\Models\User;
use App\Services\AulaVirtual\CursoVirtualService;

class EntregaTareaPolicy
{
    protected CursoVirtualService $cursoService;

    public function __construct(CursoVirtualService $cursoService)
    {
        $this->cursoService = $cursoService;
    }

    public function view(User $user, EntregaTarea $entrega): bool
    {
        $estudiante = $this->cursoService->estudianteDeUsuario($user);

        // El estudiante dueño de la entrega puede verla
        if ($estudiante && $entrega->cod_est === $estudiante->cod_est) {
            return true;
        }

        // El docente del curso puede verla
        $entrega->loadMissing('tarea');
        if ($entrega->tarea && $this->cursoService->tareaParaDocente($user, $entrega->tarea->cod_tar)) {
            return true;
        }

        return false;
    }

    public function submit(User $user, Tarea $tarea): bool
    {
        return $user->can('Aula_Virtual_Estudiante')
            && (bool) $this->cursoService->tareaParaEstudiante($user, $tarea->cod_tar);
    }

    public function updateOwn(User $user, EntregaTarea $entrega): bool
    {
        $estudiante = $this->cursoService->estudianteDeUsuario($user);

        if (! $estudiante || $entrega->cod_est !== $estudiante->cod_est) {
            return false;
        }

        // No se puede editar una entrega calificada o anulada
        if (in_array($entrega->est_ent, ['CALIFICADO', 'ANULADO'], true)) {
            return false;
        }

        return true;
    }

    public function grade(User $user, EntregaTarea $entrega): bool
    {
        $entrega->loadMissing('tarea');

        return $user->can('Aula_Virtual_Docente')
            && $entrega->tarea
            && (bool) $this->cursoService->tareaParaDocente($user, $entrega->tarea->cod_tar);
    }

    public function return(User $user, EntregaTarea $entrega): bool
    {
        return $this->grade($user, $entrega);
    }

    public function downloadFiles(User $user, EntregaTarea $entrega): bool
    {
        return $this->view($user, $entrega);
    }
}
