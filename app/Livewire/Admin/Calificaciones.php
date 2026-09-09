<?php

namespace App\Livewire\Admin;

use App\Models\Asignatura;
use App\Models\Calificacion;
use App\Models\Estudiante;
use App\Models\PeriodoEvaluacion;
use App\Models\PlanAsignatura;
use App\Services\CalificacionService;
use App\Support\Evaluacion\CalificacionInteligente;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
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

    #[Locked]
    public ?string $versionCalificacion = null;

    public string $motivoRectificacion = '';

    public array $form = [];

    public array $analisis = [];

    public bool $modalImportacion = false;

    public string $textoImportacion = '';

    #[Locked]
    public array $filasImportacion = [];

    public array $previewImportacion = [];

    #[Locked]
    public bool $importacionValidada = false;

    public function updatedTextoImportacion(): void
    {
        $this->importacionValidada = false;
        $this->previewImportacion = [];
    }

    public function abrirImportacion(): void
    {
        Gate::authorize('Calificaciones');
        $this->textoImportacion = '';
        $this->filasImportacion = $this->previewImportacion = [];
        $this->importacionValidada = false;
        $this->resetValidation();
        $this->modalImportacion = true;
    }

    public function previsualizarImportacion(): void
    {
        Gate::authorize('Calificaciones');
        $this->validate(['textoImportacion' => 'required|string|max:100000']);
        $this->importacionValidada = false;
        $this->previewImportacion = [];
        $this->filasImportacion = [];
        foreach (preg_split('/\R/', trim($this->textoImportacion)) as $linea) {
            $valores = str_getcsv($linea, ',', '"', '');
            if (count($valores) !== 4) {
                $this->addError('importacion', 'Cada fila requiere estudiante, plan, periodo y nota.');

                return;
            }
            $this->filasImportacion[] = array_combine(['cod_est', 'cod_pas', 'cod_pev', 'not_cal'], array_map('trim', $valores));
        }
        $this->previewImportacion = app(CalificacionService::class)->previsualizarImportacion($this->filasImportacion);
        $this->importacionValidada = $this->previewImportacion['puede_continuar'];
    }

    public function confirmarImportacion(): void
    {
        abort_unless($this->importacionValidada, 422, 'Debe previsualizar y validar todas las filas antes de confirmar.');
        $resumen = app(CalificacionService::class)->importar($this->filasImportacion);
        $this->modalImportacion = false;
        $this->filasImportacion = [];
        $this->importacionValidada = false;
        $this->dispatch('swal:success', title: 'Importación completada', text: $resumen['registradas'].' calificaciones registradas.');
    }

    public function mount(): void
    {
        $this->limpiarFormulario();
    }

    public function updatedForm(mixed $value = null, ?string $key = null): void
    {
        $this->analizar();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedPeriodoFiltro(): void
    {
        $this->resetPage();
    }

    public function updatedAsignaturaFiltro(): void
    {
        $this->resetPage();
    }

    public function updatedEstado(): void
    {
        $this->resetPage();
    }

    public function abrirCrear(): void
    {
        $this->editando = false;
        $this->seleccionado = null;
        $this->versionCalificacion = null;
        $this->motivoRectificacion = '';
        $this->limpiarFormulario();
        $this->modalFormulario = true;
    }

    public function abrirEditar(string $codigo): void
    {
        $calificacion = Calificacion::findOrFail($codigo);
        $this->form = $calificacion->only(['cod_est', 'cod_pas', 'cod_asi', 'cod_pev', 'not_cal', 'obs_cal', 'est_cal']);
        $this->seleccionado = $codigo;
        $this->versionCalificacion = $calificacion->getRawOriginal('updated_at');
        $this->motivoRectificacion = '';
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
            $this->form['cod_asi'] = PlanAsignatura::whereKey($this->form['cod_pas'])->value('cod_asi');
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

        app(CalificacionService::class)->registrar($this->form, $this->editando ? $this->seleccionado : null, $this->versionCalificacion, $this->motivoRectificacion);

        $this->modalFormulario = false;
        $this->dispatch('swal:success', title: 'Calificación guardada', text: 'La calificación fue registrada correctamente.');
    }

    public function cambiarEstado(string $codigo, string $destino, ?string $version = null): void
    {
        Gate::authorize('Calificaciones');
        $calificacion = Calificacion::findOrFail($codigo);
        app(CalificacionService::class)->registrar(
            array_replace($calificacion->toArray(), ['est_cal' => $destino]),
            $codigo,
            $version,
        );
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
            'periodos' => PeriodoEvaluacion::whereIn('est_pev', ['ACTIVO', 'EN_CIERRE', 'REABIERTO', 'CERRADO'])->orderBy('ord_pev')->get(),
            'planes' => PlanAsignatura::with('asignatura')->where('est_pas', 'ACTIVO')->get(),
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
