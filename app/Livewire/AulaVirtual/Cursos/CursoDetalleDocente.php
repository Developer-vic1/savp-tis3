<?php

namespace App\Livewire\AulaVirtual\Cursos;

use Livewire\Component;

class CursoDetalleDocente extends Component
{
    public string $curso = '';

    public function render()
    {
        return view('livewire.aula-virtual.cursos.curso-detalle-docente');
    }
}
