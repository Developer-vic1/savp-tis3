<?php

namespace App\Livewire\Admin;

use App\Models\Asignatura;
use App\Models\Curso;
use App\Models\Docente;
use App\Models\GestionAcademica;
use App\Models\Paralelo;
use App\Models\PlanAsignatura;
use App\Models\Turno;
use App\Services\BitacoraService;
use App\Support\Academico\PlanAsignaturaInteligente;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class PlanesAsignatura extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'tailwind';

    public string $search = '';
    public string $estado = '';
    public string $asignaturaFiltro = '';
    public bool $modalFormulario = false;
    public bool $editando = false;
    public ?string $seleccionado = null;
    public array $form = [];
    public array $analisis = [];

    public function mount(): void
    {
        $this->limpiarFormulario();
    }

    public function updatedForm(mixed $value = null, ?string $key = null): void
    {
        $this->analizar();
    }

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedEstado(): void { $this->resetPage(); }
    public function updatedAsignaturaFiltro(): void { $this->resetPage(); }

    public function abrirCrear(): void
    {
        $this->editando = false;
        $this->seleccionado = null;
        $this->limpiarFormulario();
        $this->modalFormulario = true;
    }

    public function abrirEditar(string $codigo): void
    {
        $plan = PlanAsignatura::findOrFail($codigo);
        $this->form = $plan->only(['cod_asi', 'cod_doc', 'cod_cur', 'cod_par', 'cod_tur', 'cod_gea', 'hor_pas', 'est_pas']);
        $this->seleccionado = $codigo;
        $this->editando = true;
        $this->analizar();
        $this->modalFormulario = true;
    }

    public function cerrarFormulario(): void
    {
        $this->modalFormulario = false;
        $this->resetValidation();
    }

    public function analizar(): void
    {
        $this->analisis = app(PlanAsignaturaInteligente::class)->analizar($this->form, $this->seleccionado);
    }

    public function guardar(): void
    {
        $this->analizar();
        if (! ($this->analisis['puede_guardar'] ?? false)) {
            $this->dispatch('swal:warning', title: 'Plan bloqueado', text: implode(' ', $this->analisis['bloqueos'] ?? []));
            return;
        }

        $this->form = $this->analisis['datos'];
        $this->validate([
            'form.cod_asi' => ['required', 'exists:asignatura,cod_asi'],
            'form.cod_doc' => ['required', 'exists:docente,cod_doc'],
            'form.cod_cur' => ['required', 'exists:curso,cod_cur'],
            'form.cod_par' => ['required', 'exists:paralelo,cod_par'],
            'form.cod_tur' => ['required', 'exists:turno,cod_tur'],
            'form.cod_gea' => ['required', 'exists:gestion_academica,cod_gea'],
            'form.hor_pas' => ['required', 'integer', 'min:1', 'max:40'],
            'form.est_pas' => ['required', Rule::in(['ACTIVO', 'INACTIVO'])],
        ]);

        $anterior = null;
        if ($this->editando && $this->seleccionado) {
            $plan = PlanAsignatura::findOrFail($this->seleccionado);
            $anterior = $plan->toArray();
            $plan->update($this->form);
        } else {
            $plan = PlanAsignatura::create($this->form);
        }

        BitacoraService::registrar(
            accion: $this->editando ? 'ACTUALIZAR_PLAN_ASIGNATURA' : 'CREAR_PLAN_ASIGNATURA',
            tabla: 'plan_asignatura',
            registro: $plan->cod_pas,
            modulo: 'Planes de Asignatura',
            nombreRegistro: $plan->cod_pas,
            descripcion: 'Se guardó una asignación académica con validación de duplicidad.',
            valoresAnteriores: $anterior,
            valoresNuevos: $plan->fresh()->toArray(),
        );

        $this->modalFormulario = false;
        $this->dispatch('swal:success', title: 'Plan guardado', text: 'La asignación académica fue registrada correctamente.');
    }

    public function cambiarEstado(string $codigo): void
    {
        $plan = PlanAsignatura::findOrFail($codigo);
        $plan->update(['est_pas' => $plan->est_pas === 'ACTIVO' ? 'INACTIVO' : 'ACTIVO']);
        $this->dispatch('swal:success', title: 'Estado actualizado', text: 'El estado del plan fue actualizado.');
    }

    public function limpiarFiltros(): void
    {
        $this->search = '';
        $this->estado = '';
        $this->asignaturaFiltro = '';
        $this->resetPage();
    }

    public function render()
    {
        $planes = PlanAsignatura::query()
            ->with(['asignatura', 'docente.personalInstitucional.persona', 'curso', 'paralelo', 'turno', 'gestionAcademica'])
            ->when($this->search !== '', function (Builder $query) {
                $search = trim($this->search);
                $query->where(function (Builder $sub) use ($search) {
                    $sub->where('cod_pas', 'ILIKE', "%{$search}%")
                        ->orWhereHas('asignatura', fn ($q) => $q->where('nom_asi', 'ILIKE', "%{$search}%"))
                        ->orWhereHas('docente.personalInstitucional.persona', fn ($q) => $q->where('nom_per', 'ILIKE', "%{$search}%")->orWhere('ape_pat_per', 'ILIKE', "%{$search}%"));
                });
            })
            ->when($this->estado !== '', fn ($q) => $q->where('est_pas', $this->estado))
            ->when($this->asignaturaFiltro !== '', fn ($q) => $q->where('cod_asi', $this->asignaturaFiltro))
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('livewire.admin.planes-asignatura', [
            'planes' => $planes,
            'asignaturas' => Asignatura::where('est_asi', 'ACTIVO')->orderBy('nom_asi')->get(),
            'docentes' => Docente::with('personalInstitucional.persona')->where('est_doc', 'ACTIVO')->get(),
            'cursos' => Curso::where('est_cur', 'ACTIVO')->orderBy('nom_cur')->get(),
            'paralelos' => Paralelo::where('est_par', 'ACTIVO')->orderBy('nom_par')->get(),
            'turnos' => Turno::where('est_tur', 'ACTIVO')->orderBy('nom_tur')->get(),
            'gestiones' => GestionAcademica::orderByDesc('ani_gea')->get(),
            'metricas' => [
                'total' => PlanAsignatura::count(),
                'activos' => PlanAsignatura::where('est_pas', 'ACTIVO')->count(),
                'horas' => PlanAsignatura::where('est_pas', 'ACTIVO')->sum('hor_pas'),
                'docentes' => PlanAsignatura::distinct()->count('cod_doc'),
            ],
        ]);
    }

    private function limpiarFormulario(): void
    {
        $this->form = ['cod_asi' => '', 'cod_doc' => '', 'cod_cur' => '', 'cod_par' => '', 'cod_tur' => '', 'cod_gea' => '', 'hor_pas' => 4, 'est_pas' => 'ACTIVO'];
        $this->analisis = [];
    }
}
