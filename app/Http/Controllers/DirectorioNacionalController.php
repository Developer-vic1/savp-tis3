<?php

namespace App\Http\Controllers;

use App\Services\AporteIngenieril\AporteIngenierilClient;
use App\Services\AporteIngenieril\StudentOrientationService;
use App\Services\RoleDashboardResolver;
use App\Support\Conocimiento\DirectorioNacional;
use Illuminate\Http\Request;

class DirectorioNacionalController extends Controller
{
    public function index(Request $request, AporteIngenierilClient $cliente, StudentOrientationService $orientacion)
    {
        $estudiante = $request->routeIs('estudiante.*');
        if ($estudiante) {
            $orientacion->student($request->user());
        } else {
            abort_unless(in_array(app(RoleDashboardResolver::class)->roleFor($request->user()), ['Administrador', 'Director'], true)
                && $request->user()->can('conocimiento.ver'), 403);
        }
        $filtros = array_map(fn ($valor) => trim($valor ?? ''), $request->validate([
            'buscar' => ['nullable', 'string', 'max:180'],
            'departamento' => ['nullable', 'string', 'max:120'],
            'regimen' => ['nullable', 'string', 'max:100'],
            'duracion' => ['nullable', 'string', 'max:300'],
            'economia' => ['nullable', 'in:verificados,pendientes'],
            'orden' => ['nullable', 'in:nombre,departamento'],
        ]));
        $resultado = $cliente->nationalDirectory();
        $directorio = $resultado->available ? $resultado->data : [];
        $presentacion = DirectorioNacional::presentar($directorio, $filtros);
        $todasInstituciones = DirectorioNacional::presentar($directorio, [])['instituciones'];

        return response()->view('conocimiento.directorio-nacional', [
            'estudiante' => $estudiante,
            'resultado' => $resultado,
            'directorio' => $directorio,
            'consultaUrl' => route($estudiante ? 'estudiante.directorio-nacional' : 'conocimiento.directorio-nacional'),
            'volverUrl' => route($estudiante ? 'estudiante.futuro' : 'conocimiento.fuentes.index'),
            'todasInstituciones' => $todasInstituciones,
        ] + $presentacion)->header('Cache-Control', 'no-store');
    }
}
