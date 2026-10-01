<?php

namespace App\Policies;

use App\Models\AulaVirtual\EntregaTarea;
use App\Models\User;
use App\Services\AulaVirtual\CursoVirtualService;

class AulaVirtualEntregaPolicy
{
    public function view(User $user, EntregaTarea $entrega): bool
    {
        if (! $user->can('Acceso_Aula_Virtual') || ! $user->can('Entregas_Aula')) {
            return false;
        }
        $service = app(CursoVirtualService::class);
        $estudiante = $service->estudianteDeUsuario($user);
        $entrega->loadMissing('tarea');

        if ($user->can('Aula_Virtual_Estudiante') && $estudiante && $entrega->cod_est === $estudiante->cod_est) {
            return $entrega->tarea && (bool) $service->cursoParaEstudiante($user, $entrega->tarea->cod_cla);
        }

        return $user->can('Aula_Virtual_Docente') && $entrega->tarea && (bool) $service->cursoParaDocente($user, $entrega->tarea->cod_cla);
    }

    public function grade(User $user, EntregaTarea $entrega): bool
    {
        return $user->can('Calificaciones_Aula') && $this->returnForCorrection($user, $entrega);
    }

    public function returnForCorrection(User $user, EntregaTarea $entrega): bool
    {
        if (! $user->can('Acceso_Aula_Virtual') || ! $user->can('Aula_Virtual_Docente') || ! $user->can('Entregas_Aula')) {
            return false;
        }
        $entrega->loadMissing('tarea');

        $class = $entrega->tarea ? app(CursoVirtualService::class)->cursoParaDocente($user, $entrega->tarea->cod_cla) : null;

        return $class?->est_cla === 'ACTIVA';
    }
}
