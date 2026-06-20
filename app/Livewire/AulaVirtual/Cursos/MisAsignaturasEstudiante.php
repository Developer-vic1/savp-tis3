<?php

namespace App\Livewire\AulaVirtual\Cursos;

use App\Services\AulaVirtual\CursoVirtualService;
use Livewire\Component;

class MisAsignaturasEstudiante extends Component
{
    public function render(CursoVirtualService $cursos)
    {
        return view('livewire.aula-virtual.cursos.mis-asignaturas-estudiante', [
            'cursos' => $cursos->cursosEstudiante(auth()->user()),
            'servicio' => $cursos,
        ]);
    }
}
