<?php

namespace App\Http\Controllers\AulaVirtual;

use App\Http\Controllers\Controller;
use App\Services\AulaVirtual\CursoVirtualService;
use App\Services\AulaVirtual\OrientacionService;
use Illuminate\Http\Request;

class OrientacionController extends Controller
{
    public function __construct(
        private readonly OrientacionService $orientacion,
        private readonly CursoVirtualService $cursos,
    ) {}

    public function estudiante(Request $request)
    {
        return view('aula-virtual.orientacion.estudiante', [
            'resumen' => $this->orientacion->resumen($request->user()),
            'dimensiones' => $this->orientacion->dimensiones(),
        ]);
    }

    public function explorador(Request $request)
    {
        return view('aula-virtual.orientacion.explorador', [
            'dimensiones' => $this->orientacion->dimensiones(),
        ]);
    }

    public function resultados(Request $request)
    {
        return view('aula-virtual.orientacion.resultados', [
            'resumen' => $this->orientacion->resumen($request->user()),
        ]);
    }

    public function seguimientoDocente(Request $request)
    {
        return view('aula-virtual.orientacion.seguimiento-docente', [
            'cursos' => $this->cursos->cursosDocente($request->user()),
        ]);
    }
}
