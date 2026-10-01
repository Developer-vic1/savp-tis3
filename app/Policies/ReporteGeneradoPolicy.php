<?php

namespace App\Policies;

use App\Models\ReporteGenerado;
use App\Models\User;
use App\Services\HistoricalReportAccessService;
use App\Services\RoleDashboardResolver;

class ReporteGeneradoPolicy
{
    public function view(User $user, ReporteGenerado $report): bool
    {
        // El archivo histórico no tiene gestión/grado verificables: no se expone a Regencia.
        return app(HistoricalReportAccessService::class)->canRead($user, $report);
    }

    public function delete(User $user, ReporteGenerado $report): bool
    {
        return app(RoleDashboardResolver::class)->roleFor($user) === 'Administrador' && $user->can('Reportes_Administrativos');
    }
}
