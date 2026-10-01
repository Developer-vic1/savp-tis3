<?php

namespace App\Http\Controllers;

use App\Services\RoleDashboardResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request, RoleDashboardResolver $resolver): RedirectResponse
    {
        $route = $resolver->routeFor($request->user());

        abort_if(! $route, 403, 'Tu cuenta requiere un único actor institucional activo. Solicita revisión a Administración.');

        return redirect()->route($route);
    }
}
