<?php

namespace App\Livewire\Admin;

use App\Models\Docente;
use App\Support\Comunidad\DocenteInteligente;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;

class GestionDocente extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'tailwind';

    public string $search = '';
    public string $estado = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedEstado(): void
    {
        $this->resetPage();
    }

    public function limpiarFiltros(): void
    {
        $this->search = '';
        $this->estado = '';
        $this->resetPage();
    }

    public function render()
    {
        $docentesBase = Docente::with(['personalInstitucional.persona', 'planAsignaturas']);
        $soporte = app(DocenteInteligente::class);
        $todos = (clone $docentesBase)->get();
        $incompletos = $todos->filter(fn (Docente $docente) => ! $soporte->analizarEspecialidad($docente->esp_doc)['puede_guardar'])->count();

        $docentes = $docentesBase
            ->when($this->search !== '', function (Builder $query) {
                $search = trim($this->search);
                $query->where(function (Builder $sub) use ($search) {
                    $sub->where('esp_doc', 'ILIKE', "%{$search}%")
                        ->orWhereHas('personalInstitucional.persona', fn ($q) => $q->where(function ($persona) use ($search) {
                            $persona->where('nom_per', 'ILIKE', "%{$search}%")
                                ->orWhere('ape_pat_per', 'ILIKE', "%{$search}%")
                                ->orWhere('ape_mat_per', 'ILIKE', "%{$search}%")
                                ->orWhere('ci_per', 'ILIKE', "%{$search}%");
                        }));
                });
            })
            ->when($this->estado !== '', fn (Builder $query) => $query->where('est_doc', $this->estado))
            ->orderBy('cod_doc')
            ->paginate(10);

        return view('livewire.admin.gestion-docente', [
            'docentes' => $docentes,
            'soporteDocente' => $soporte,
            'metricasDocentes' => [
                'total' => $todos->count(),
                'activos' => $todos->where('est_doc', 'ACTIVO')->count(),
                'carga' => $todos->sum(fn (Docente $docente) => $docente->planAsignaturas->count()),
                'incompletos' => $incompletos,
            ],
        ]);
    }
}
