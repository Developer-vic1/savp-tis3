<?php

namespace App\Policies;

use App\Models\AulaVirtual\ClaseVirtual;
use App\Models\User;
use App\Services\AulaVirtual\CursoVirtualService;

class AulaVirtualCursoPolicy
{
    public function view(User $user, ClaseVirtual $curso): bool
    {
        $service = app(CursoVirtualService::class);

        return $user->can('Acceso_Aula_Virtual')
            && (bool) ($service->cursoParaEstudiante($user, $curso->cod_cla) ?? $service->cursoParaDocente($user, $curso->cod_cla));
    }

    public function manage(User $user, ClaseVirtual $curso): bool
    {
        return $user->can('Aula_Virtual_Docente')
            && (bool) app(CursoVirtualService::class)->cursoParaDocente($user, $curso->cod_cla);
    }
}
