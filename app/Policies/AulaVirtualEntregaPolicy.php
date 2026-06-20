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

        if ($estudiante && $entrega->cod_est === $estudiante->cod_est) {
            return true;
        }

        $entrega->loadMissing('tarea');

        return (bool) $service->cursoParaDocente($user, $entrega->tarea->cod_cla);
    }

    public function grade(User $user, EntregaTarea $entrega): bool
    {
        $entrega->loadMissing('tarea');

        return $user->can('Aula_Virtual_Docente')
            && (bool) app(CursoVirtualService::class)->cursoParaDocente($user, $entrega->tarea->cod_cla);
    }
}
