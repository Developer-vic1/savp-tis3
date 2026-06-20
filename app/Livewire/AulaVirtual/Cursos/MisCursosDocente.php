<?php

namespace App\Livewire\AulaVirtual\Cursos;

use App\Services\AulaVirtual\CursoVirtualService;
use Livewire\Component;

class MisCursosDocente extends Component
{
    public function render(CursoVirtualService $cursos)
    {
        return view('livewire.aula-virtual.cursos.mis-cursos-docente', [
            'cursos' => $cursos->cursosDocente(auth()->user()),
            'servicio' => $cursos,
        ]);
    }
}
