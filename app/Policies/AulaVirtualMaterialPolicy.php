<?php

namespace App\Policies;

use App\Models\AulaVirtual\MaterialClase;
use App\Models\User;
use App\Services\AulaVirtual\CursoVirtualService;

class AulaVirtualMaterialPolicy
{
    public function view(User $user, MaterialClase $material): bool
    {
        $courses = app(CursoVirtualService::class);

        return $user->can('Acceso_Aula_Virtual') && (bool) (($material->est_mat === 'ACTIVO' ? $courses->cursoParaEstudiante($user, $material->cod_cla) : null)
            ?? $courses->cursoParaDocente($user, $material->cod_cla));
    }

    public function update(User $user, MaterialClase $material): bool
    {
        return $user->can('Aula_Virtual_Docente') && (bool) app(CursoVirtualService::class)->cursoParaDocente($user, $material->cod_cla);
    }
}
