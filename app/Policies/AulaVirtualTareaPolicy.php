<?php

namespace App\Policies;

use App\Models\Oficial\AulaVirtual\Tarea;
use App\Models\Oficial\Sistema\User;
use App\Services\AulaVirtual\CursoVirtualService;

class AulaVirtualTareaPolicy
{
    public function submit(User $user, Tarea $tarea): bool
    {
        return $tarea->est_tar === 'PUBLICADA' && $user->can('Aula_Virtual_Estudiante') && $user->can('Entregas_Aula')
            && (bool) app(CursoVirtualService::class)->cursoParaEstudiante($user, $tarea->cod_cla);
    }

    public function review(User $user, Tarea $tarea): bool
    {
        return $user->can('Aula_Virtual_Docente')
            && (bool) app(CursoVirtualService::class)->cursoParaDocente($user, $tarea->cod_cla);
    }
}
