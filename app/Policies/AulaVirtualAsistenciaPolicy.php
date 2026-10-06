<?php

namespace App\Policies;

use App\Models\Oficial\Academico\AsistenciaClase;
use App\Models\Oficial\Sistema\User;
use App\Services\AulaVirtual\CursoVirtualService;

class AulaVirtualAsistenciaPolicy
{
    public function view(User $user, AsistenciaClase $asistencia): bool
    {
        $service = app(CursoVirtualService::class);

        return $user->can('Acceso_Aula_Virtual') && $user->can('Asistencia_Aula') && (bool) ($service->cursoParaEstudiante($user, $asistencia->cod_cla)
            ?? $service->cursoParaDocente($user, $asistencia->cod_cla));
    }

    public function update(User $user, AsistenciaClase $asistencia): bool
    {
        return $user->can('Aula_Virtual_Docente') && $user->can('Asistencia_Aula')
            && app(CursoVirtualService::class)->cursoParaDocente($user, $asistencia->cod_cla)?->est_cla === 'ACTIVA';
    }
}
