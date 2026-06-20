<?php

namespace App\Http\Controllers\AulaVirtual;

use App\Http\Controllers\Controller;
use App\Services\AulaVirtual\CursoVirtualService;
use App\Services\AulaVirtual\ReporteAulaVirtualService;
use Illuminate\Http\Request;

class ReporteAulaVirtualController extends Controller
{
    public function __construct(
        private readonly CursoVirtualService $cursos,
        private readonly ReporteAulaVirtualService $reportes,
    ) {}

    public function index(Request $request)
    {
        $cursos = $this->cursos->cursosDocente($request->user());

        return view('aula-virtual.reportes.index', [
            'cursos' => $cursos,
            'consolidados' => $cursos->map(fn ($curso) => $this->reportes->consolidadoCurso($curso)),
        ]);
    }
}
