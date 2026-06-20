<?php

namespace App\Livewire\AulaVirtual;

use App\Services\AulaVirtual\CursoVirtualService;
use Livewire\Component;

class DashboardEstudiante extends Component
{
    public function render(CursoVirtualService $cursos)
    {
        return view('livewire.aula-virtual.dashboard-estudiante', $cursos->dashboardEstudiante(auth()->user()));
    }
}
