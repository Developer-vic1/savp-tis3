<?php

namespace App\Policies;

use App\Models\AulaVirtual\ClaseVirtual;
use App\Models\User;
use App\Services\AulaVirtual\CursoVirtualService;

class ClaseVirtualPolicy
{
    protected CursoVirtualService $cursoService;

    public function __construct(CursoVirtualService $cursoService)
    {
        $this->cursoService = $cursoService;
    }

    public function view(User $user, ClaseVirtual $curso): bool
    {
        if (! $user->can('Acceso_Aula_Virtual')) {
            return false;
        }

        if ($user->can('Aula_Virtual_Docente') && $this->cursoService->cursoParaDocente($user, $curso->cod_cla)) {
            return true;
        }

        if ($user->can('Aula_Virtual_Estudiante') && $this->cursoService->cursoParaEstudiante($user, $curso->cod_cla)) {
            return true;
        }

        return false;
    }

    public function manage(User $user, ClaseVirtual $curso): bool
    {
        return $user->can('Aula_Virtual_Docente')
            && (bool) $this->cursoService->cursoParaDocente($user, $curso->cod_cla);
    }

    public function registrarAsistencia(User $user, ClaseVirtual $curso): bool
    {
        return $this->manage($user, $curso);
    }

    public function crearTarea(User $user, ClaseVirtual $curso): bool
    {
        return $this->manage($user, $curso);
    }

    public function crearMaterial(User $user, ClaseVirtual $curso): bool
    {
        return $this->manage($user, $curso);
    }

    public function verReportes(User $user, ClaseVirtual $curso): bool
    {
        return $this->manage($user, $curso);
    }
}
