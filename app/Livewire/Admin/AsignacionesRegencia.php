<?php

namespace App\Livewire\Admin;

use App\Models\Curso;
use App\Models\GestionAcademica;
use App\Models\Regente;
use App\Models\RegenteAsignacion;
use App\Services\RegencyAccessService;
use Livewire\Component;
use Livewire\WithPagination;

class AsignacionesRegencia extends Component
{
    use WithPagination;

    public string $gestion = '';
    public array $form = ['cod_reg' => '', 'cod_gea' => '', 'cod_cur' => '', 'activa' => true];

    public function updatedGestion(): void { $this->resetPage(); }

    public function mount(): void
    {
        $this->gestion = GestionAcademica::whereIn('est_gea', ['ACTIVO', 'ACTIVA'])->orderByDesc('ani_gea')->value('cod_gea') ?? '';
        $this->form['cod_gea'] = $this->gestion;
    }

    public function retirar(int $id, RegencyAccessService $service): void
    {
        abort_unless(auth()->user()?->est_usu === 'ACTIVO' && auth()->user()->hasRole('Administrador') && auth()->user()->can('regencia.asignaciones.gestionar'), 403);
        $assignment = RegenteAsignacion::findOrFail($id);
        $service->assign(auth()->user(), $assignment->only(['cod_reg', 'cod_gea', 'cod_cur']) + ['activa' => false]);
        session()->flash('status', 'Asignación retirada. Se conserva el registro y la bitácora.');
    }

    public function guardar(RegencyAccessService $service): void
    {
        $service->assign(auth()->user(), $this->form);
        session()->flash('status', 'Asignación guardada y auditada.');
    }

    public function render(RegencyAccessService $service)
    {
        $ready = $service->available();
        return view('livewire.admin.asignaciones-regencia', [
            'ready' => $ready,
            'assignments' => $ready ? RegenteAsignacion::with('regente.personalInstitucional.persona', 'gestion', 'curso')
                ->when($this->gestion !== '', fn ($q) => $q->where('cod_gea', $this->gestion))->orderByDesc('cod_gea')->paginate(20) : collect(),
            'regents' => Regente::with('personalInstitucional.persona')->where('est_reg', 'ACTIVO')->whereHas('personalInstitucional', fn ($q) => $q->where('est_pin', 'ACTIVO'))->get(),
            'years' => GestionAcademica::orderByDesc('ani_gea')->get(),
            'courses' => Curso::where('est_cur', 'ACTIVO')->orderBy('nom_cur')->get(),
        ]);
    }
}
