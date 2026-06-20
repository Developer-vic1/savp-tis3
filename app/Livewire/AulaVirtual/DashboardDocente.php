<?php

namespace App\Livewire\AulaVirtual;

use App\Services\AulaVirtual\CursoVirtualService;
use Livewire\Component;

class DashboardDocente extends Component
{
    public function render(CursoVirtualService $cursos)
    {
        return view('livewire.aula-virtual.dashboard-docente', $cursos->dashboardDocente(auth()->user()));
    }
}
