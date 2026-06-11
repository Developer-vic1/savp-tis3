<?php

namespace App\Livewire\Admin;

use App\Models\EspecialidadTecnica;
use App\Support\Academico\EspecialidadTecnicaInteligente;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;
use Livewire\WithPagination;

class EspecialidadesTecnicas extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'tailwind';

    public string $search = '';
    public string $estado = '';
    public string $familiaFiltro = '';
    public string $campoFiltro = '';
    public string $estadoInteligenteFiltro = '';
    public int $perPage = 10;
    
    public bool $modalCrear = false;
    public bool $modalEditar = false;
    public bool $modalDetalle = false;

    public array $form = ['nom_esp' => '', 'des_esp' => '', 'est_esp' => 'ACTIVO'];
    public array $analisis = [];
    public array $detalle = [];
    public ?string $seleccionado = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'estado' => ['except' => ''],
        'familiaFiltro' => ['except' => ''],
        'campoFiltro' => ['except' => ''],
        'estadoInteligenteFiltro' => ['except' => ''],
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedEstado(): void
    {
        $this->resetPage();
    }

    public function updatedFamiliaFiltro(): void
    {
        $this->resetPage();
    }

    public function updatedCampoFiltro(): void
    {
        $this->resetPage();
    }

    public function updatedEstadoInteligenteFiltro(): void
    {
        $this->resetPage();
    }

    public function mount(): void
    {
        $this->limpiarFormulario();
    }

    public function limpiarFormulario(): void
    {
        $this->form = ['nom_esp' => '', 'des_esp' => '', 'est_esp' => 'ACTIVO'];
        $this->analisis = [];
        $this->seleccionado = null;
        $this->resetValidation();
    }

    public function updatedForm(): void
    {
        $this->analizarFormulario();
    }

    public function analizarFormulario(): void
    {
        $soporte = app(EspecialidadTecnicaInteligente::class);
        $this->analisis = $soporte->analizar($this->form, $this->seleccionado);
    }

    public function aplicarSugerencia(): void
    {
        $this->analizarFormulario();
        if (!empty($this->analisis['datos']['nom_esp'])) {
            $this->form['nom_esp'] = $this->analisis['datos']['nom_esp'];
            $this->form['des_esp'] = $this->analisis['datos']['des_esp'] ?? $this->form['des_esp'];
            $this->analizarFormulario();
        }
    }

    public function abrirModalCrear(): void
    {
        $this->limpiarFormulario();
        $this->modalCrear = true;
    }

    public function abrirModalEditar(string $codigo): void
    {
        $registro = EspecialidadTecnica::findOrFail($codigo);
        $this->seleccionado = $codigo;
        $this->form = [
            'nom_esp' => $registro->nom_esp,
            'des_esp' => $registro->des_esp,
            'est_esp' => $registro->est_esp,
        ];
        $this->analizarFormulario();
        $this->modalEditar = true;
    }

    public function abrirModalDetalle(string $codigo): void
    {
        $registro = EspecialidadTecnica::withCount('estudiantes')->findOrFail($codigo);
        $this->detalle = $registro->toArray();
        
        // Include BTH analysis in the details view
        $soporte = app(EspecialidadTecnicaInteligente::class);
        $analisisDetalle = $soporte->analizar([
            'nom_esp' => $registro->nom_esp,
            'des_esp' => $registro->des_esp,
            'est_esp' => $registro->est_esp,
        ], $registro->cod_esp);
        
        $this->detalle['analisis'] = $analisisDetalle;
        $this->modalDetalle = true;
    }

    public function cerrarModales(): void
    {
        $this->modalCrear = false;
        $this->modalEditar = false;
        $this->modalDetalle = false;
        $this->resetValidation();
    }

    public function guardar(): void
    {
        $this->analizarFormulario();

        if (!($this->analisis['puede_guardar'] ?? false)) {
            $this->dispatch('swal:warning', title: 'Registro bloqueado', text: implode(' ', $this->analisis['bloqueos'] ?? []));
            return;
        }

        $rules = [
            'form.nom_esp' => ['required', 'string', 'min:3', 'max:150'],
            'form.des_esp' => ['nullable', 'string', 'max:255'],
            'form.est_esp' => ['required', 'in:ACTIVO,INACTIVO'],
        ];
        $this->validate($rules);

        $soporte = app(EspecialidadTecnicaInteligente::class);
        $datosPersistibles = $soporte->mapearCamposPersistibles($this->form, $this->analisis);

        // Filter keys dynamically based on active DB columns
        $existingColumns = Schema::getColumnListing('especialidad_tecnica');
        $datosFiltrados = array_intersect_key($datosPersistibles, array_flip($existingColumns));

        if ($this->seleccionado) {
            $registro = EspecialidadTecnica::findOrFail($this->seleccionado);
            $registro->update($datosFiltrados);
        } else {
            EspecialidadTecnica::create($datosFiltrados);
        }

        $this->cerrarModales();
        $this->dispatch('swal:success', title: 'Especialidad guardada', text: 'La información curricular fue procesada y guardada correctamente.');
    }

    public function cambiarEstado(string $codigo): void
    {
        $registro = EspecialidadTecnica::findOrFail($codigo);
        $nuevo = $registro->est_esp === 'ACTIVO' ? 'INACTIVO' : 'ACTIVO';
        $registro->update(['est_esp' => $nuevo]);

        $this->dispatch('swal:success', title: 'Estado actualizado', text: "La especialidad técnica ahora está {$nuevo}.");
    }

    public function clasificarConMotor(string $codigo): void
    {
        $reg = EspecialidadTecnica::findOrFail($codigo);
        $soporte = app(EspecialidadTecnicaInteligente::class);
        $analisis = $soporte->analizar([
            'nom_esp' => $reg->nom_esp,
            'des_esp' => $reg->des_esp,
            'est_esp' => $reg->est_esp,
        ], $reg->cod_esp);

        $datosPersistibles = $soporte->mapearCamposPersistibles($reg->toArray(), $analisis);

        $existingColumns = Schema::getColumnListing('especialidad_tecnica');
        $datosFiltrados = array_intersect_key($datosPersistibles, array_flip($existingColumns));

        $reg->update($datosFiltrados);

        $this->dispatch('swal:success', title: 'Clasificación completada', text: 'Se ha ejecutado el análisis BTH y se actualizaron los parámetros del registro.');
    }

    public function limpiarFiltros(): void
    {
        $this->search = '';
        $this->estado = '';
        $this->familiaFiltro = '';
        $this->campoFiltro = '';
        $this->estadoInteligenteFiltro = '';
        $this->resetPage();
    }

    public function metricas(): array
    {
        $total = EspecialidadTecnica::count();
        $activos = EspecialidadTecnica::where('est_esp', 'ACTIVO')->count();
        $inactivos = EspecialidadTecnica::where('est_esp', 'INACTIVO')->count();
        
        $hasExtended = Schema::hasColumn('especialidad_tecnica', 'clas_bth_esp');
        $clasificados = $hasExtended ? EspecialidadTecnica::where('clas_bth_esp', true)->count() : 0;
        $pendientes = $hasExtended ? EspecialidadTecnica::where('clas_bth_esp', false)->count() : $total;
        $familias = EspecialidadTecnicaInteligente::contarFamiliasRegistradas();

        $relacionados = EspecialidadTecnica::query()->withCount('estudiantes')->get()->sum('estudiantes_count');

        return [
            'total' => $total,
            'activos' => $activos,
            'inactivos' => $inactivos,
            'clasificados' => $clasificados,
            'pendientes' => $pendientes,
            'familias' => $familias,
            'relacionados' => $relacionados,
        ];
    }

    public function familiasDisponibles(): array
    {
        return array_unique(array_column(EspecialidadTecnicaInteligente::catalogo(), 'familia_profesional'));
    }

    public function camposFormativosDisponibles(): array
    {
        return array_unique(array_column(EspecialidadTecnicaInteligente::catalogo(), 'campo_formativo'));
    }

    public function estadosInteligentesDisponibles(): array
    {
        return ['RECONOCIDA', 'REDACTABLE', 'REQUIERE_REVISION', 'BLOQUEADA', 'DUPLICADA', 'INCOMPLETA'];
    }

    public function render()
    {
        $hasExtended = Schema::hasColumn('especialidad_tecnica', 'fam_pro_esp');

        $query = EspecialidadTecnica::query()
            ->withCount('estudiantes')
            ->when($this->search !== '', function (Builder $q) {
                $q->where(function (Builder $sub) {
                    $sub->where('cod_esp', 'ILIKE', '%' . $this->search . '%')
                        ->orWhere('nom_esp', 'ILIKE', '%' . $this->search . '%')
                        ->orWhere('des_esp', 'ILIKE', '%' . $this->search . '%');
                });
            })
            ->when($this->estado !== '', fn ($q) => $q->where('est_esp', $this->estado));

        if ($hasExtended) {
            $query->when($this->familiaFiltro !== '', fn ($q) => $q->where('fam_pro_esp', $this->familiaFiltro))
                  ->when($this->campoFiltro !== '', fn ($q) => $q->where('cam_for_esp', $this->campoFiltro))
                  ->when($this->estadoInteligenteFiltro !== '', fn ($q) => $q->where('est_int_esp', $this->estadoInteligenteFiltro));
        }

        $query->orderBy('nom_esp');
        $registros = $query->paginate($this->perPage);

        // Fetch active specialties for the vocational map
        $todasEspecialidades = EspecialidadTecnica::withCount('estudiantes')->orderBy('nom_esp')->get();
        $especialidadesConAnalisis = [];
        $soporte = app(EspecialidadTecnicaInteligente::class);

        foreach ($todasEspecialidades as $reg) {
            $analisis = $soporte->analizar([
                'nom_esp' => $reg->nom_esp,
                'des_esp' => $reg->des_esp,
                'est_esp' => $reg->est_esp,
            ], $reg->cod_esp);

            $especialidadesConAnalisis[] = [
                'registro' => $reg,
                'analisis' => $analisis,
            ];
        }

        return view('livewire.admin.especialidades-tecnicas', [
            'registros' => $registros,
            'especialidadesConAnalisis' => $especialidadesConAnalisis,
            'metricas' => $this->metricas(),
            'familias' => $this->familiasDisponibles(),
            'camposFormativos' => $this->camposFormativosDisponibles(),
            'estadosInteligentes' => $this->estadosInteligentesDisponibles(),
            'hasExtended' => $hasExtended,
        ]);
    }
}
