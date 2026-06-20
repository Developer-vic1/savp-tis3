<?php

namespace App\Policies;

use App\Models\AulaVirtual\AsistenciaClase;
use App\Models\User;
use App\Services\AulaVirtual\CursoVirtualService;

class AulaVirtualAsistenciaPolicy
{
    public function view(User $user, AsistenciaClase $asistencia): bool
    {
        $service = app(CursoVirtualService::class);

        return (bool) ($service->cursoParaEstudiante($user, $asistencia->cod_cla)
            ?? $service->cursoParaDocente($user, $asistencia->cod_cla));
    }

    public function update(User $user, AsistenciaClase $asistencia): bool
    {
        return $user->can('Aula_Virtual_Docente')
            && (bool) app(CursoVirtualService::class)->cursoParaDocente($user, $asistencia->cod_cla);
    }
}
