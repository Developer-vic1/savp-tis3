<?php

namespace App\Policies;

use App\Models\AulaVirtual\Tarea;
use App\Models\User;
use App\Services\AulaVirtual\CursoVirtualService;

class AulaVirtualTareaPolicy
{
    public function submit(User $user, Tarea $tarea): bool
    {
        return $user->can('Aula_Virtual_Estudiante')
            && (bool) app(CursoVirtualService::class)->cursoParaEstudiante($user, $tarea->cod_cla);
    }

    public function review(User $user, Tarea $tarea): bool
    {
        return $user->can('Aula_Virtual_Docente')
            && (bool) app(CursoVirtualService::class)->cursoParaDocente($user, $tarea->cod_cla);
    }
}
