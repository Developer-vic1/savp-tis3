<?php

namespace App\Livewire\Admin;

use App\Models\Asignatura;
use App\Models\Calificacion;
use App\Models\Estudiante;
use App\Models\PeriodoEvaluacion;
use App\Services\BitacoraService;
use App\Support\Evaluacion\CalificacionInteligente;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class Calificaciones extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'tailwind';

    public string $search = '';
    public string $periodoFiltro = '';
    public string $asignaturaFiltro = '';
    public string $estado = '';
    public bool $modalFormulario = false;
    public bool $editando = false;
    public ?string $seleccionado = null;
    public array $form = [];
    public array $analisis = [];

    public function mount(): void { $this->limpiarFormulario(); }
    public function updatedForm(mixed $value = null, ?string $key = null): void { $this->analizar(); }
    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedPeriodoFiltro(): void { $this->resetPage(); }
    public function updatedAsignaturaFiltro(): void { $this->resetPage(); }
    public function updatedEstado(): void { $this->resetPage(); }

    public function abrirCrear(): void
    {
        $this->editando = false;
        $this->seleccionado = null;
        $this->limpiarFormulario();
        $this->modalFormulario = true;
    }

    public function abrirEditar(string $codigo): void
    {
        $calificacion = Calificacion::findOrFail($codigo);
        $this->form = $calificacion->only(['cod_est', 'cod_asi', 'cod_pev', 'not_cal', 'obs_cal', 'est_cal']);
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
        $this->analisis = app(CalificacionInteligente::class)->analizar($this->form, $this->seleccionado);
    }

    public function aplicarObservacion(): void
    {
        $this->analizar();
        $this->form['obs_cal'] = $this->analisis['datos']['obs_cal'] ?? '';
    }

    public function guardar(): void
    {
        Gate::authorize('Calificaciones');
        $this->analizar();
        if (! ($this->analisis['puede_guardar'] ?? false)) {
            $this->dispatch('swal:warning', title: 'Calificación bloqueada', text: implode(' ', $this->analisis['bloqueos'] ?? []));
            return;
        }

        $this->form = $this->analisis['datos'];
        $this->validate([
            'form.cod_est' => ['required', 'exists:estudiante,cod_est'],
            'form.cod_asi' => ['required', 'exists:asignatura,cod_asi'],
            'form.cod_pev' => ['required', 'exists:periodo_evaluacion,cod_pev'],
            'form.not_cal' => ['required', 'numeric', 'min:0', 'max:100'],
            'form.obs_cal' => ['nullable', 'string', 'max:255'],
            'form.est_cal' => ['required', Rule::in(['ACTIVO', 'INACTIVO', 'ANULADO'])],
        ]);

        $anterior = null;
        if ($this->editando && $this->seleccionado) {
            $calificacion = Calificacion::findOrFail($this->seleccionado);
            $anterior = $calificacion->toArray();
            $calificacion->update($this->form);
        } else {
            $calificacion = Calificacion::create($this->form);
        }

        BitacoraService::registrar(
            accion: $this->editando ? 'ACTUALIZAR_CALIFICACION' : 'CREAR_CALIFICACION',
            tabla: 'calificacion',
            registro: $calificacion->cod_cal,
            modulo: 'Calificaciones',
            nombreRegistro: $calificacion->cod_cal,
            descripcion: 'Se guardó una calificación sobre 100 con validación académica.',
            valoresAnteriores: $anterior,
            valoresNuevos: $calificacion->fresh()->toArray(),
        );

        $this->modalFormulario = false;
        $this->dispatch('swal:success', title: 'Calificación guardada', text: 'La calificación fue registrada correctamente.');
    }

    public function cambiarEstado(string $codigo): void
    {
        Gate::authorize('Calificaciones');
        $calificacion = Calificacion::findOrFail($codigo);
        $calificacion->update(['est_cal' => $calificacion->est_cal === 'ACTIVO' ? 'ANULADO' : 'ACTIVO']);
        $this->dispatch('swal:success', title: 'Estado actualizado', text: 'El estado de la calificación fue actualizado.');
    }

    public function limpiarFiltros(): void
    {
        $this->search = $this->periodoFiltro = $this->asignaturaFiltro = $this->estado = '';
        $this->resetPage();
    }

    public function render()
    {
        $query = Calificacion::query()
            ->with(['estudiante.persona', 'estudiante.especialidad', 'asignatura', 'periodoEvaluacion'])
            ->when($this->search !== '', function (Builder $query) {
                $search = trim($this->search);
                $query->where(function (Builder $sub) use ($search) {
                    $sub->whereHas('estudiante.persona', fn ($q) => $q->where('nom_per', 'ILIKE', "%{$search}%")->orWhere('ape_pat_per', 'ILIKE', "%{$search}%"))
                        ->orWhereHas('asignatura', fn ($q) => $q->where('nom_asi', 'ILIKE', "%{$search}%"));
                });
            })
            ->when($this->periodoFiltro !== '', fn ($q) => $q->where('cod_pev', $this->periodoFiltro))
            ->when($this->asignaturaFiltro !== '', fn ($q) => $q->where('cod_asi', $this->asignaturaFiltro))
            ->when($this->estado !== '', fn ($q) => $q->where('est_cal', $this->estado));

        $soporte = app(CalificacionInteligente::class);
        $menor = Calificacion::with('asignatura')->where('est_cal', 'ACTIVO')->get()->groupBy('cod_asi')
            ->map(fn ($items) => ['nombre' => $items->first()->asignatura?->nom_asi, 'promedio' => round($items->avg('not_cal'), 2)])
            ->sortBy('promedio')->first();

        return view('livewire.admin.calificaciones', [
            'calificaciones' => $query->orderByDesc('created_at')->paginate(10),
            'estudiantes' => Estudiante::with('persona')->where('est_est', 'ACTIVO')->get()->sortBy(fn ($e) => $e->persona?->ape_pat_per),
            'asignaturas' => Asignatura::where('est_asi', 'ACTIVO')->orderBy('nom_asi')->get(),
            'periodos' => PeriodoEvaluacion::where('est_pev', 'ACTIVO')->orderBy('ord_pev')->get(),
            'soporte' => $soporte,
            'metricas' => [
                'promedio' => round((float) Calificacion::where('est_cal', 'ACTIVO')->avg('not_cal'), 2),
                'riesgo' => Calificacion::where('est_cal', 'ACTIVO')->where('not_cal', '<=', 50)->count(),
                'destacadas' => Calificacion::where('est_cal', 'ACTIVO')->where('not_cal', '>=', 90)->count(),
                'menor' => $menor,
            ],
        ]);
    }

    private function limpiarFormulario(): void
    {
        $this->form = ['cod_est' => '', 'cod_asi' => '', 'cod_pev' => '', 'not_cal' => '', 'obs_cal' => '', 'est_cal' => 'ACTIVO'];
        $this->analisis = [];
    }
}
