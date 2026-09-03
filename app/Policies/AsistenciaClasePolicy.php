<?php

namespace App\Policies;

use App\Models\AulaVirtual\AsistenciaClase;
use App\Models\AulaVirtual\ClaseVirtual;
use App\Models\User;
use App\Services\AulaVirtual\CursoVirtualService;

class AsistenciaClasePolicy
{
    protected CursoVirtualService $cursoService;

    public function __construct(CursoVirtualService $cursoService)
    {
        $this->cursoService = $cursoService;
    }

    public function view(User $user, AsistenciaClase $asistencia): bool
    {
        if (! $user->can('Acceso_Aula_Virtual')) {
            return false;
        }

        return (bool) ($this->cursoService->cursoParaEstudiante($user, $asistencia->cod_cla)
            ?? $this->cursoService->cursoParaDocente($user, $asistencia->cod_cla));
    }

    public function create(User $user, ClaseVirtual $curso): bool
    {
        return $user->can('Aula_Virtual_Docente')
            && (bool) $this->cursoService->cursoParaDocente($user, $curso->cod_cla);
    }

    public function update(User $user, AsistenciaClase $asistencia): bool
    {
        return $user->can('Aula_Virtual_Docente')
            && (bool) $this->cursoService->cursoParaDocente($user, $asistencia->cod_cla);
    }

    public function close(User $user, AsistenciaClase $asistencia): bool
    {
        return $this->update($user, $asistencia);
    }

    public function cancel(User $user, AsistenciaClase $asistencia): bool
    {
        return $this->update($user, $asistencia);
    }
}
