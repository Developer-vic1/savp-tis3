<?php

namespace App\Http\Controllers\AulaVirtual;

use App\Http\Controllers\Controller;
use App\Services\AulaVirtual\CursoVirtualService;

class AulaVirtualController extends Controller
{
    public function __construct(private readonly CursoVirtualService $cursos) {}

    public function index()
    {
        $user = auth()->user();

        if (! $user->can('Acceso_Aula_Virtual')) {
            abort(403, 'No tienes acceso habilitado al Aula Virtual.');
        }

        if ($user->hasRole('Docente') || $user->can('Aula_Virtual_Docente')) {
            return view('aula-virtual.dashboard.docente', $this->cursos->dashboardDocente($user));
        }

        if ($user->hasRole('Estudiante') || $user->can('Aula_Virtual_Estudiante')) {
            return view('aula-virtual.dashboard.estudiante', $this->cursos->dashboardEstudiante($user));
        }

        return view('aula-virtual.dashboard.index');
    }
}
