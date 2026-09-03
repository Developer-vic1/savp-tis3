<?php

namespace App\Policies;

use App\Models\AulaVirtual\ClaseVirtual;
use App\Models\AulaVirtual\Tarea;
use App\Models\User;
use App\Services\AulaVirtual\CursoVirtualService;

class TareaPolicy
{
    protected CursoVirtualService $cursoService;

    public function __construct(CursoVirtualService $cursoService)
    {
        $this->cursoService = $cursoService;
    }

    public function view(User $user, Tarea $tarea): bool
    {
        if (! $user->can('Acceso_Aula_Virtual')) {
            return false;
        }

        if ($user->can('Aula_Virtual_Docente') && $this->cursoService->tareaParaDocente($user, $tarea->cod_tar)) {
            return true;
        }

        if ($user->can('Aula_Virtual_Estudiante') && $this->cursoService->tareaParaEstudiante($user, $tarea->cod_tar)) {
            return true;
        }

        return false;
    }

    public function create(User $user, ClaseVirtual $curso): bool
    {
        return $user->can('Aula_Virtual_Docente')
            && (bool) $this->cursoService->cursoParaDocente($user, $curso->cod_cla);
    }

    public function update(User $user, Tarea $tarea): bool
    {
        return $user->can('Aula_Virtual_Docente')
            && (bool) $this->cursoService->tareaParaDocente($user, $tarea->cod_tar);
    }

    public function publish(User $user, Tarea $tarea): bool
    {
        return $this->update($user, $tarea);
    }

    public function close(User $user, Tarea $tarea): bool
    {
        return $this->update($user, $tarea);
    }

    public function cancel(User $user, Tarea $tarea): bool
    {
        return $this->update($user, $tarea);
    }

    public function deliver(User $user, Tarea $tarea): bool
    {
        return $user->can('Aula_Virtual_Estudiante')
            && (bool) $this->cursoService->tareaParaEstudiante($user, $tarea->cod_tar);
    }

    public function review(User $user, Tarea $tarea): bool
    {
        return $this->update($user, $tarea);
    }
}
