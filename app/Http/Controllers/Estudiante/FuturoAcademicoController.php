<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use App\Services\AporteIngenieril\StudentOrientationService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FuturoAcademicoController extends Controller
{
    public function index(Request $request, StudentOrientationService $orientacion): View
    {
        $request->validate([
            'explore' => 'nullable|in:analysis,universities,all,compare',
            'career' => 'nullable|string|max:160',
            'university' => 'nullable|string|max:180',
            'compare' => 'nullable|array|max:3',
            'compare.*' => 'nullable|string|max:180|distinct',
        ]);

        return view('estudiante.orientacion.futuro', $orientacion->context($request->user()));
    }
}
