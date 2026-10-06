<?php

namespace App\Http\Controllers;

use App\Services\InstitutionalQueryService;
use Illuminate\Http\Request;

class InstitutionalQueryController extends Controller
{
    public const AREAS = InstitutionalQueryService::AREAS;

    public function index(Request $request, string $area, InstitutionalQueryService $service)
    {
        $workspace = explode('.', $request->route()->getName())[0];
        $service->authorizeQuery($request->user(), $area, $workspace);

        if ($workspace === 'admin' && $area === 'lms') {
            abort_unless($request->user()->can('calificaciones.ver.global') && $request->user()->can('estudiantes.ver.global'), 403);

            return view('admin.rendimiento-estudiantes');
        }

        return view('workspaces.consulta', ['area' => $area, 'workspace' => $workspace, 'title' => self::AREAS[$area][0]]);
    }
}
