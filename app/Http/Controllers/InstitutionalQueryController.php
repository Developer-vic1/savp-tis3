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

        return view('workspaces.consulta', ['area' => $area, 'workspace' => $workspace, 'title' => self::AREAS[$area][0]]);
    }
}
