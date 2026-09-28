<?php

namespace App\Policies;

use App\Models\AulaVirtual\EntregaTarea;
use App\Models\User;
use App\Services\AulaVirtual\CursoVirtualService;

class AulaVirtualEntregaPolicy
{
    public function view(User $user, EntregaTarea $entrega): bool
    {
        $service = app(CursoVirtualService::class);
        $estudiante = $service->estudianteDeUsuario($user);

        if ($user->can('Aula_Virtual_Estudiante') && $estudiante && $entrega->cod_est === $estudiante->cod_est) {
            return true;
        }

        $entrega->loadMissing('tarea');

        return $user->can('Aula_Virtual_Docente') && $entrega->tarea && (bool) $service->cursoParaDocente($user, $entrega->tarea->cod_cla);
    }

    public function grade(User $user, EntregaTarea $entrega): bool
    {
        $entrega->loadMissing('tarea');

        return $user->can('Aula_Virtual_Docente')
            && $entrega->tarea && (bool) app(CursoVirtualService::class)->cursoParaDocente($user, $entrega->tarea->cod_cla);
    }
}
