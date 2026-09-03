<?php

namespace App\Policies;

use App\Models\AulaVirtual\ClaseVirtual;
use App\Models\AulaVirtual\MaterialClase;
use App\Models\User;
use App\Services\AulaVirtual\CursoVirtualService;

class MaterialClasePolicy
{
    protected CursoVirtualService $cursoService;

    public function __construct(CursoVirtualService $cursoService)
    {
        $this->cursoService = $cursoService;
    }

    public function view(User $user, MaterialClase $material): bool
    {
        if (! $user->can('Acceso_Aula_Virtual')) {
            return false;
        }

        if ($user->can('Aula_Virtual_Docente') && $this->cursoService->materialParaDocente($user, $material->cod_mat)) {
            return true;
        }

        if ($user->can('Aula_Virtual_Estudiante') && $this->cursoService->materialParaEstudiante($user, $material->cod_mat)) {
            return true;
        }

        return false;
    }

    public function create(User $user, ClaseVirtual $curso): bool
    {
        return $user->can('Aula_Virtual_Docente')
            && (bool) $this->cursoService->cursoParaDocente($user, $curso->cod_cla);
    }

    public function update(User $user, MaterialClase $material): bool
    {
        return $user->can('Aula_Virtual_Docente')
            && (bool) $this->cursoService->materialParaDocente($user, $material->cod_mat);
    }

    public function publish(User $user, MaterialClase $material): bool
    {
        return $this->update($user, $material);
    }

    public function hide(User $user, MaterialClase $material): bool
    {
        return $this->update($user, $material);
    }

    public function cancel(User $user, MaterialClase $material): bool
    {
        return $this->update($user, $material);
    }

    public function download(User $user, MaterialClase $material): bool
    {
        return $this->view($user, $material);
    }
}
