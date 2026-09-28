<?php

namespace App\Livewire\Admin;

use App\Models\Asignatura;
use App\Models\Calificacion;
use App\Models\Estudiante;
use App\Models\PeriodoEvaluacion;
use App\Services\BitacoraService;
use App\Support\Evaluacion\CalificacionInteligente;
use Illuminate\Database\Eloquent\Builder;
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
    public string $gestionFiltro = '';
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
    public function updatedGestionFiltro(): void { $this->resetPage(); }

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
        abort_unless($calificacion->cod_pas, 409, 'Nota histórica pendiente de reconciliación de su asignación.');
        $this->form = $calificacion->only(['cod_est', 'cod_asi', 'cod_pas', 'cod_pev', 'not_cal', 'obs_cal', 'est_cal']);
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
        if (! empty($this->form['cod_pas'])) {
            $this->form['cod_asi'] = \App\Models\PlanAsignatura::find($this->form['cod_pas'])?->cod_asi;
        }
        $this->analisis = app(CalificacionInteligente::class)->analizar($this->form, $this->seleccionado);
    }

    public function aplicarObservacion(): void
    {
        $this->analizar();
        $this->form['obs_cal'] = $this->analisis['datos']['obs_cal'] ?? '';
    }

    public function guardar(): void
    {
        abort_unless(app(\App\Services\GradeService::class)->available(), 409, 'El historial por gestión requiere aplicación autorizada del esquema.');
        $this->analizar();
        if (! ($this->analisis['puede_guardar'] ?? false)) {
            $this->dispatch('swal:warning', title: 'Calificación bloqueada', text: implode(' ', $this->analisis['bloqueos'] ?? []));
            return;
        }

        $this->form = $this->analisis['datos'];
        $this->validate([
            'form.cod_est' => ['required', 'exists:estudiante,cod_est'],
            'form.cod_asi' => ['required', 'exists:asignatura,cod_asi'],
            'form.cod_pas' => ['required', 'exists:plan_asignatura,cod_pas'],
            'form.cod_pev' => ['required', 'exists:periodo_evaluacion,cod_pev'],
            'form.not_cal' => ['required', 'numeric', 'min:0', 'max:100'],
            'form.obs_cal' => ['nullable', 'string', 'max:255'],
            'form.est_cal' => ['required', Rule::in(['ACTIVO', 'INACTIVO', 'ANULADO'])],
        ]);

        app(\App\Services\GradeService::class)->save(auth()->user(), $this->form['cod_pas'], $this->form['cod_est'], $this->form['cod_pev'],
            (float) $this->form['not_cal'], $this->form['obs_cal'] ?? null,
            $this->editando ? Calificacion::findOrFail($this->seleccionado) : null, $this->form['motivo'] ?? null, $this->form['est_cal']);

        $this->modalFormulario = false;
        $this->dispatch('swal:success', title: 'Calificación guardada', text: 'La calificación fue registrada correctamente.');
    }

    public function cambiarEstado(string $codigo): void
    {
        $this->abrirEditar($codigo);
        $this->form['est_cal'] = $this->form['est_cal'] === 'ACTIVO' ? 'ANULADO' : 'ACTIVO';
        $this->addError('form.motivo', 'Para cambiar el estado indica el motivo de rectificación y guarda la nota.');
    }

    public function limpiarFiltros(): void
    {
        $this->search = $this->periodoFiltro = $this->asignaturaFiltro = $this->estado = '';
        $this->gestionFiltro = '';
        $this->resetPage();
    }

    public function render()
    {
        $query = Calificacion::query()
            ->with(['estudiante.persona', 'estudiante.especialidad', 'asignatura', 'periodoEvaluacion', 'planAsignatura.gestionAcademica', 'planAsignatura.curso', 'planAsignatura.paralelo'])
            ->when($this->gestionFiltro !== '', fn ($q) => $q->whereHas('planAsignatura', fn ($p) => $p->where('cod_gea', $this->gestionFiltro)))
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
        $metricQuery = (clone $query)->where('est_cal', 'ACTIVO');
        $lowest = (clone $metricQuery)->select('cod_asi')->selectRaw('AVG(not_cal) as promedio')
            ->groupBy('cod_asi')->orderBy('promedio')->first();
        $menor = $lowest ? ['nombre' => $lowest->asignatura?->nom_asi, 'promedio' => round($lowest->promedio, 2)] : null;

        return view('livewire.admin.calificaciones', [
            'years' => \App\Models\GestionAcademica::orderByDesc('ani_gea')->get(),
            'plans' => \App\Models\PlanAsignatura::with('asignatura', 'gestionAcademica', 'curso', 'paralelo')->orderByDesc('cod_gea')->get(),
            'ready' => app(\App\Services\GradeService::class)->available(),
            'calificaciones' => $query->orderByDesc('created_at')->paginate(10),
            'estudiantes' => Estudiante::with('persona')->where('est_est', 'ACTIVO')->get()->sortBy(fn ($e) => $e->persona?->ape_pat_per),
            'asignaturas' => Asignatura::where('est_asi', 'ACTIVO')->orderBy('nom_asi')->get(),
            'periodos' => PeriodoEvaluacion::orderBy('ord_pev')->get(),
            'soporte' => $soporte,
            'metricas' => [
                'promedio' => ($avg = (clone $metricQuery)->avg('not_cal')) === null ? null : round((float) $avg, 2),
                'riesgo' => (clone $metricQuery)->where('not_cal', '<=', 50)->count(),
                'destacadas' => (clone $metricQuery)->where('not_cal', '>=', 90)->count(),
                'menor' => $menor,
            ],
        ]);
    }

    private function limpiarFormulario(): void
    {
        $this->form = ['cod_est' => '', 'cod_asi' => '', 'cod_pas' => '', 'cod_pev' => '', 'not_cal' => '', 'obs_cal' => '', 'motivo' => '', 'est_cal' => 'ACTIVO'];
        $this->analisis = [];
    }
}
