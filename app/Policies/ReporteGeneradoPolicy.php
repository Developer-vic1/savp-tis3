<?php

namespace App\Policies;

use App\Models\ReporteGenerado;
use App\Models\User;

class ReporteGeneradoPolicy
{
    public function view(User $user, ReporteGenerado $report): bool
    {
        // El archivo histórico no tiene gestión/grado verificables: no se expone a Regencia.
        return $user->est_usu === 'ACTIVO' && $user->hasAnyRole(['Administrador', 'Director'])
            && $user->canAny(['reportes.ver.institucional', 'Reportes_Academicos', 'Reportes_Administrativos']);
    }

    public function delete(User $user, ReporteGenerado $report): bool
    {
        return $user->est_usu === 'ACTIVO' && $user->hasRole('Administrador') && $user->can('Reportes_Administrativos');
    }
}
