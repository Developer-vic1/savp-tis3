<?php

namespace App\Http\Controllers;

use App\Services\DomainReadinessService;
use App\Services\RoleDashboardResolver;
use Illuminate\Http\Request;

class DomainBoundaryController extends Controller
{
    public function show(Request $request, string $domain, DomainReadinessService $readiness)
    {
        $actor = app(RoleDashboardResolver::class)->roleFor($request->user());
        abort_unless($actor, 403);
        $allowed = match ($actor) {
            'Administrador' => ['kardex', 'lms-configuracion', 'configuracion'], 'Director' => ['kardex', 'prevencion', 'seguimientos'],
            'Secretaria' => ['kardex'], 'Regente' => ['kardex', 'seguimientos', 'alertas'], 'Docente' => ['kardex', 'seguimientos'], 'Estudiante' => ['seguimientos'], default => []
        };
        abort_unless(in_array($domain, $allowed, true), 403);
        $permission = match ($actor) {
            'Administrador' => 'Gestion_Academica','Director','Regente' => 'estudiantes.ver.institucional','Secretaria' => 'Estudiantes','Docente' => 'estudiantes.ver.curso','Estudiante' => 'Perfil_Academico'
        };
        abort_unless($request->user()->can($permission), 403);

        return view('workspaces.domain-boundary', $readiness->describe($domain) + ['root' => app(RoleDashboardResolver::class)->routeFor($request->user()), 'actor' => $actor]);
    }
}
