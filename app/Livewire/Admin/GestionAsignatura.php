<?php

namespace App\Livewire\Admin;

use App\Models\Asignatura;
use App\Services\BitacoraService;
use App\Support\Academico\AsignaturaInteligente;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;
use Throwable;

class GestionAsignatura extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'tailwind';

    /*
    |--------------------------------------------------------------------------
    | FILTROS Y ESTADO DE TABLA
    |--------------------------------------------------------------------------
    */

    public string $search = '';
    public string $estado = '';
    public string $usoAcademico = '';
    public int $perPage = 10;
    public string $sortField = 'nom_asi';
    public string $sortDirection = 'asc';

    /*
    |--------------------------------------------------------------------------
    | MODALES
    |--------------------------------------------------------------------------
    */

    public bool $modalCrear = false;
    public bool $modalEditar = false;
    public bool $modalDetalle = false;
    public bool $modalCatalogo = false;

    /*
    |--------------------------------------------------------------------------
    | FORMULARIOS
    |--------------------------------------------------------------------------
    */

    public array $form = [
        'nom_asi' => '',
        'sig_asi' => '',
        'hor_asi' => 2,
        'est_asi' => 'ACTIVO',
    ];

    public array $formEditar = [
        'cod_asi' => '',
        'nom_asi' => '',
        'sig_asi' => '',
        'hor_asi' => 2,
        'est_asi' => 'ACTIVO',
    ];

    /*
    |--------------------------------------------------------------------------
    | ANÁLISIS INTELIGENTE
    |--------------------------------------------------------------------------
    */

    public array $analisisCrear = [];
    public array $analisisEditar = [];

    /*
    |--------------------------------------------------------------------------
    | DETALLE
    |--------------------------------------------------------------------------
    */

    public ?string $asignaturaSeleccionada = null;
    public array $detalleAsignatura = [];

    /*
    |--------------------------------------------------------------------------
    | CONFIGURACIÓN
    |--------------------------------------------------------------------------
    */

    public array $estadosDisponibles = [
        'ACTIVO' => 'Activo',
        'INACTIVO' => 'Inactivo',
    ];

    public array $opcionesUsoAcademico = [
        '' => 'Todos',
        'CON_USO' => 'Con uso académico',
        'SIN_USO' => 'Sin uso académico',
        'CON_PLAN' => 'Con plan de asignatura',
        'CON_CALIFICACIONES' => 'Con calificaciones',
    ];

    protected $listeners = [
        'confirmar-desactivar-asignatura' => 'desactivarAsignatura',
        'confirmar-reactivar-asignatura' => 'reactivarAsignatura',
    ];

    /*
    |--------------------------------------------------------------------------
    | CICLO DE VIDA
    |--------------------------------------------------------------------------
    */

    public function mount(): void
    {
        $this->reiniciarAnalisisCrear();
        $this->reiniciarAnalisisEditar();
    }

    public function render()
    {
        $asignaturas = $this->obtenerAsignaturasPaginadas();

        return view('livewire.admin.gestion-asignatura', [
            'asignaturas' => $asignaturas,
            'resumen' => $this->obtenerResumen(),
            'catalogoInteligente' => AsignaturaInteligente::catalogoSugerencias(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | ACTUALIZACIÓN DE FILTROS
    |--------------------------------------------------------------------------
    */

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingEstado(): void
    {
        $this->resetPage();
    }

    public function updatingUsoAcademico(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function limpiarFiltros(): void
    {
        $this->reset([
            'search',
            'estado',
            'usoAcademico',
        ]);

        $this->perPage = 10;
        $this->sortField = 'nom_asi';
        $this->sortDirection = 'asc';

        $this->resetPage();
    }

    public function ordenarPor(string $campo): void
    {
        $camposPermitidos = [
            'cod_asi',
            'nom_asi',
            'sig_asi',
            'hor_asi',
            'est_asi',
        ];

        if (! in_array($campo, $camposPermitidos, true)) {
            return;
        }

        if ($this->sortField === $campo) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $campo;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    /*
    |--------------------------------------------------------------------------
    | CONSULTAS
    |--------------------------------------------------------------------------
    */

    private function obtenerAsignaturasPaginadas(): LengthAwarePaginator
    {
        $query = $this->asignaturasQuery();

        $asignaturas = $query->paginate($this->perPage);

        $asignaturas->getCollection()->transform(function (Asignatura $asignatura) {
            $uso = $this->obtenerUsoAcademico($asignatura->cod_asi);

            $asignatura->uso_academico = $uso;
            $asignatura->analisis_inteligente = AsignaturaInteligente::interpretar($asignatura->nom_asi);

            return $asignatura;
        });

        return $asignaturas;
    }

    private function asignaturasQuery(): Builder
    {
        $query = Asignatura::query();

        if (trim($this->search) !== '') {
            $busqueda = trim($this->search);

            $query->where(function (Builder $subQuery) use ($busqueda) {
                $subQuery
                    ->where('cod_asi', 'like', '%' . $busqueda . '%')
                    ->orWhere('nom_asi', 'like', '%' . $busqueda . '%')
                    ->orWhere('sig_asi', 'like', '%' . $busqueda . '%');
            });
        }

        if ($this->estado !== '') {
            $query->where('est_asi', $this->estado);
        }

        $query = $this->aplicarFiltroUsoAcademico($query);

        return $query->orderBy($this->sortField, $this->sortDirection);
    }

    private function aplicarFiltroUsoAcademico(Builder $query): Builder
    {
        if ($this->usoAcademico === '') {
            return $query;
        }

        if ($this->usoAcademico === 'CON_PLAN' && Schema::hasTable('plan_asignatura')) {
            return $query->whereExists(function ($subQuery) {
                $subQuery
                    ->selectRaw('1')
                    ->from('plan_asignatura')
                    ->whereColumn('plan_asignatura.cod_asi', 'asignatura.cod_asi');
            });
        }

        if ($this->usoAcademico === 'CON_CALIFICACIONES' && Schema::hasTable('calificacion')) {
            return $query->whereExists(function ($subQuery) {
                $subQuery
                    ->selectRaw('1')
                    ->from('calificacion')
                    ->whereColumn('calificacion.cod_asi', 'asignatura.cod_asi');
            });
        }

        if ($this->usoAcademico === 'CON_USO') {
            return $query->where(function (Builder $subQuery) {
                if (Schema::hasTable('plan_asignatura')) {
                    $subQuery->orWhereExists(function ($exists) {
                        $exists
                            ->selectRaw('1')
                            ->from('plan_asignatura')
                            ->whereColumn('plan_asignatura.cod_asi', 'asignatura.cod_asi');
                    });
                }

                if (Schema::hasTable('calificacion')) {
                    $subQuery->orWhereExists(function ($exists) {
                        $exists
                            ->selectRaw('1')
                            ->from('calificacion')
                            ->whereColumn('calificacion.cod_asi', 'asignatura.cod_asi');
                    });
                }
            });
        }

        if ($this->usoAcademico === 'SIN_USO') {
            if (Schema::hasTable('plan_asignatura')) {
                $query->whereNotExists(function ($subQuery) {
                    $subQuery
                        ->selectRaw('1')
                        ->from('plan_asignatura')
                        ->whereColumn('plan_asignatura.cod_asi', 'asignatura.cod_asi');
                });
            }

            if (Schema::hasTable('calificacion')) {
                $query->whereNotExists(function ($subQuery) {
                    $subQuery
                        ->selectRaw('1')
                        ->from('calificacion')
                        ->whereColumn('calificacion.cod_asi', 'asignatura.cod_asi');
                });
            }
        }

        return $query;
    }

    /*
    |--------------------------------------------------------------------------
    | RESUMEN
    |--------------------------------------------------------------------------
    */

    private function obtenerResumen(): array
    {
        $total = Asignatura::count();
        $activas = Asignatura::where('est_asi', 'ACTIVO')->count();
        $inactivas = Asignatura::where('est_asi', 'INACTIVO')->count();
        $horas = (int) Asignatura::sum('hor_asi');

        $conPlan = $this->contarAsignaturasConRelacion('plan_asignatura', 'cod_asi');
        $conCalificaciones = $this->contarAsignaturasConRelacion('calificacion', 'cod_asi');

        $codigosConUso = collect();

        if (Schema::hasTable('plan_asignatura')) {
            $codigosConUso = $codigosConUso->merge(
                DB::table('plan_asignatura')
                    ->whereNotNull('cod_asi')
                    ->pluck('cod_asi')
            );
        }

        if (Schema::hasTable('calificacion')) {
            $codigosConUso = $codigosConUso->merge(
                DB::table('calificacion')
                    ->whereNotNull('cod_asi')
                    ->pluck('cod_asi')
            );
        }

        $conUso = $codigosConUso->unique()->count();
        $sinUso = max($total - $conUso, 0);

        return [
            'total' => $total,
            'activas' => $activas,
            'inactivas' => $inactivas,
            'con_plan' => $conPlan,
            'con_calificaciones' => $conCalificaciones,
            'con_uso' => $conUso,
            'sin_uso' => $sinUso,
            'horas' => $horas,
        ];
    }

    private function contarAsignaturasConRelacion(string $tabla, string $columnaAsignatura): int
    {
        if (! Schema::hasTable($tabla) || ! Schema::hasColumn($tabla, $columnaAsignatura)) {
            return 0;
        }

        return DB::table($tabla)
            ->whereNotNull($columnaAsignatura)
            ->distinct()
            ->count($columnaAsignatura);
    }

    /*
    |--------------------------------------------------------------------------
    | MODAL CREAR
    |--------------------------------------------------------------------------
    */

    public function abrirModalCrear(): void
    {
        $this->resetValidation();
        $this->limpiarFormularioCrear();
        $this->modalCrear = true;
    }

    public function cerrarModalCrear(): void
    {
        $this->modalCrear = false;
        $this->limpiarFormularioCrear();
        $this->resetValidation();
    }

    private function limpiarFormularioCrear(): void
    {
        $this->form = [
            'nom_asi' => '',
            'sig_asi' => '',
            'hor_asi' => 2,
            'est_asi' => 'ACTIVO',
        ];

        $this->reiniciarAnalisisCrear();
    }

    public function updatedFormNomAsi(): void
    {
        $this->interpretarAsignaturaCrear();
    }

    public function interpretarAsignaturaCrear(): void
    {
        $existentes = $this->obtenerAsignaturasExistentes();

        $this->analisisCrear = AsignaturaInteligente::interpretar(
            $this->form['nom_asi'] ?? '',
            $existentes
        );

        if (($this->analisisCrear['valido'] ?? false) && ! ($this->analisisCrear['duplicado'] ?? false)) {
            $this->form['sig_asi'] = $this->analisisCrear['sigla'] ?: $this->form['sig_asi'];
            $this->form['hor_asi'] = $this->analisisCrear['horas'] ?: $this->form['hor_asi'];
        }
    }

    public function usarSugerenciaCrear(): void
    {
        if (! ($this->analisisCrear['valido'] ?? false)) {
            $this->dispatch('advertencia-general', mensaje: $this->analisisCrear['mensaje'] ?? 'No existe una sugerencia válida para aplicar.');
            return;
        }

        $this->form['nom_asi'] = $this->analisisCrear['nombre'] ?? $this->form['nom_asi'];
        $this->form['sig_asi'] = $this->analisisCrear['sigla'] ?? $this->form['sig_asi'];
        $this->form['hor_asi'] = $this->analisisCrear['horas'] ?? $this->form['hor_asi'];

        $this->interpretarAsignaturaCrear();
    }

    public function guardarAsignatura(): void
    {
        $this->normalizarFormularioCrear();
        $this->interpretarAsignaturaCrear();

        if (! ($this->analisisCrear['valido'] ?? false)) {
            $this->registrarBitacoraSeguro(
                accion: 'INTENTO_CREAR_ASIGNATURA_BLOQUEADO',
                tabla: 'asignatura',
                registro: null,
                nombreRegistro: $this->form['nom_asi'] ?? null,
                descripcion: $this->analisisCrear['mensaje'] ?? 'Intento bloqueado de creación de asignatura.',
                nivel: 'WARNING',
                resultado: 'BLOQUEADO',
                valoresNuevos: $this->form
            );

            $this->addError('form.nom_asi', $this->analisisCrear['mensaje'] ?? AsignaturaInteligente::mensajeSoporte());

            $this->dispatch(
                'advertencia-general',
                mensaje: $this->analisisCrear['mensaje'] ?? AsignaturaInteligente::mensajeSoporte()
            );

            return;
        }

        if (($this->analisisCrear['duplicado'] ?? false) === true) {
            $this->addError('form.nom_asi', 'Ya existe una asignatura equivalente o con la misma sigla.');

            $this->dispatch(
                'advertencia-general',
                mensaje: 'No se puede registrar la asignatura porque el sistema detectó un posible duplicado fuerte.'
            );

            return;
        }

        $this->validate($this->rulesCrear(), [], $this->validationAttributesCrear());

        try {
            DB::transaction(function () {
                $asignatura = Asignatura::create([
                    'nom_asi' => $this->form['nom_asi'],
                    'sig_asi' => mb_strtoupper($this->form['sig_asi']),
                    'hor_asi' => (int) $this->form['hor_asi'],
                    'est_asi' => $this->form['est_asi'],
                ]);

                $this->registrarBitacoraSeguro(
                    accion: 'CREAR_ASIGNATURA',
                    tabla: 'asignatura',
                    registro: $asignatura->cod_asi,
                    nombreRegistro: $asignatura->nom_asi,
                    descripcion: 'Se registró la asignatura ' . $asignatura->nom_asi . ' con validación inteligente.',
                    nivel: ($this->analisisCrear['requiere_revision'] ?? false) ? 'WARNING' : 'SUCCESS',
                    resultado: 'EXITOSO',
                    valoresNuevos: [
                        'asignatura' => $asignatura->toArray(),
                        'analisis_inteligente' => $this->analisisCrear,
                    ]
                );
            });

            $this->cerrarModalCrear();
            $this->resetPage();

            $this->dispatch('asignatura-creada', mensaje: 'Asignatura registrada correctamente.');
        } catch (Throwable $e) {
            report($e);

            $this->registrarBitacoraSeguro(
                accion: 'ERROR_CREAR_ASIGNATURA',
                tabla: 'asignatura',
                registro: null,
                nombreRegistro: $this->form['nom_asi'] ?? null,
                descripcion: 'No se pudo registrar la asignatura.',
                nivel: 'ERROR',
                resultado: 'FALLIDO',
                valoresNuevos: $this->form,
                error: $e->getMessage()
            );

            $this->dispatch('error-general', mensaje: 'No se pudo registrar la asignatura. Revisa los datos e intenta nuevamente.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | MODAL EDITAR
    |--------------------------------------------------------------------------
    */

    public function abrirModalEditar(string $codAsi): void
    {
        $this->resetValidation();

        $asignatura = Asignatura::where('cod_asi', $codAsi)->firstOrFail();

        $this->asignaturaSeleccionada = $asignatura->cod_asi;

        $this->formEditar = [
            'cod_asi' => $asignatura->cod_asi,
            'nom_asi' => $asignatura->nom_asi,
            'sig_asi' => $asignatura->sig_asi,
            'hor_asi' => (int) $asignatura->hor_asi,
            'est_asi' => $asignatura->est_asi,
        ];

        $this->interpretarAsignaturaEditar();

        $this->modalEditar = true;
    }

    public function cerrarModalEditar(): void
    {
        $this->modalEditar = false;
        $this->asignaturaSeleccionada = null;

        $this->formEditar = [
            'cod_asi' => '',
            'nom_asi' => '',
            'sig_asi' => '',
            'hor_asi' => 2,
            'est_asi' => 'ACTIVO',
        ];

        $this->reiniciarAnalisisEditar();
        $this->resetValidation();
    }

    public function updatedFormEditarNomAsi(): void
    {
        $this->interpretarAsignaturaEditar();
    }

    public function interpretarAsignaturaEditar(): void
    {
        $existentes = collect($this->obtenerAsignaturasExistentes())
            ->reject(fn(array $item) => ($item['cod_asi'] ?? null) === ($this->formEditar['cod_asi'] ?? null))
            ->values()
            ->toArray();

        $this->analisisEditar = AsignaturaInteligente::interpretar(
            $this->formEditar['nom_asi'] ?? '',
            $existentes
        );
    }

    public function usarSugerenciaEditar(): void
    {
        if (! ($this->analisisEditar['valido'] ?? false)) {
            $this->dispatch('advertencia-general', mensaje: $this->analisisEditar['mensaje'] ?? 'No existe una sugerencia válida para aplicar.');
            return;
        }

        $this->formEditar['nom_asi'] = $this->analisisEditar['nombre'] ?? $this->formEditar['nom_asi'];
        $this->formEditar['sig_asi'] = $this->analisisEditar['sigla'] ?? $this->formEditar['sig_asi'];
        $this->formEditar['hor_asi'] = $this->analisisEditar['horas'] ?? $this->formEditar['hor_asi'];

        $this->interpretarAsignaturaEditar();
    }

    public function guardarEdicionAsignatura(): void
    {
        $this->normalizarFormularioEditar();
        $this->interpretarAsignaturaEditar();

        $asignatura = Asignatura::where('cod_asi', $this->formEditar['cod_asi'])->firstOrFail();
        $valoresAnteriores = $asignatura->toArray();
        $uso = $this->obtenerUsoAcademico($asignatura->cod_asi);

        if (! ($this->analisisEditar['valido'] ?? false)) {
            $this->addError('formEditar.nom_asi', $this->analisisEditar['mensaje'] ?? AsignaturaInteligente::mensajeSoporte());

            $this->dispatch(
                'advertencia-general',
                mensaje: $this->analisisEditar['mensaje'] ?? AsignaturaInteligente::mensajeSoporte()
            );

            return;
        }

        if (($this->analisisEditar['duplicado'] ?? false) === true) {
            $this->addError('formEditar.nom_asi', 'Existe otra asignatura equivalente o con la misma sigla.');
            return;
        }

        if (
            $uso['calificaciones'] > 0
            && ! AsignaturaInteligente::esCorreccionMenor($asignatura->nom_asi, $this->formEditar['nom_asi'])
        ) {
            $this->addError(
                'formEditar.nom_asi',
                'Esta asignatura ya tiene calificaciones históricas. Solo se permiten correcciones menores de nombre.'
            );

            $this->dispatch(
                'advertencia-general',
                mensaje: 'Cambio bloqueado: la asignatura tiene historial de calificaciones y no puede cambiar su identidad académica.'
            );

            return;
        }

        $this->validate($this->rulesEditar(), [], $this->validationAttributesEditar());

        try {
            DB::transaction(function () use ($asignatura, $valoresAnteriores, $uso) {
                $asignatura->update([
                    'nom_asi' => $this->formEditar['nom_asi'],
                    'sig_asi' => mb_strtoupper($this->formEditar['sig_asi']),
                    'hor_asi' => (int) $this->formEditar['hor_asi'],
                    'est_asi' => $this->formEditar['est_asi'],
                ]);

                $nivel = ($uso['total'] > 0 || ($this->analisisEditar['requiere_revision'] ?? false))
                    ? 'WARNING'
                    : 'SUCCESS';

                $this->registrarBitacoraSeguro(
                    accion: 'EDITAR_ASIGNATURA',
                    tabla: 'asignatura',
                    registro: $asignatura->cod_asi,
                    nombreRegistro: $asignatura->nom_asi,
                    descripcion: 'Se actualizó la asignatura ' . $asignatura->nom_asi . '.',
                    nivel: $nivel,
                    resultado: 'EXITOSO',
                    valoresAnteriores: [
                        'asignatura' => $valoresAnteriores,
                        'uso_academico' => $uso,
                    ],
                    valoresNuevos: [
                        'asignatura' => $asignatura->fresh()?->toArray(),
                        'analisis_inteligente' => $this->analisisEditar,
                    ]
                );
            });

            $this->cerrarModalEditar();

            $this->dispatch('asignatura-actualizada', mensaje: 'Asignatura actualizada correctamente.');
        } catch (Throwable $e) {
            report($e);

            $this->registrarBitacoraSeguro(
                accion: 'ERROR_EDITAR_ASIGNATURA',
                tabla: 'asignatura',
                registro: $this->formEditar['cod_asi'] ?? null,
                nombreRegistro: $this->formEditar['nom_asi'] ?? null,
                descripcion: 'No se pudo editar la asignatura.',
                nivel: 'ERROR',
                resultado: 'FALLIDO',
                valoresNuevos: $this->formEditar,
                error: $e->getMessage()
            );

            $this->dispatch('error-general', mensaje: 'No se pudo actualizar la asignatura. Intenta nuevamente.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | MODAL DETALLE
    |--------------------------------------------------------------------------
    */

    public function abrirModalDetalle(string $codAsi): void
    {
        $asignatura = Asignatura::where('cod_asi', $codAsi)->firstOrFail();

        $uso = $this->obtenerUsoAcademico($asignatura->cod_asi);
        $analisis = AsignaturaInteligente::interpretar($asignatura->nom_asi);

        $this->asignaturaSeleccionada = $asignatura->cod_asi;

        $this->detalleAsignatura = [
            'codigo' => $asignatura->cod_asi,
            'nombre' => $asignatura->nom_asi,
            'sigla' => $asignatura->sig_asi,
            'horas' => (int) $asignatura->hor_asi,
            'estado' => $asignatura->est_asi,
            'uso' => $uso,
            'analisis' => $analisis,
            'recomendacion' => $this->recomendacionInstitucional($asignatura, $uso, $analisis),
        ];

        $this->modalDetalle = true;
    }

    public function cerrarModalDetalle(): void
    {
        $this->modalDetalle = false;
        $this->detalleAsignatura = [];
        $this->asignaturaSeleccionada = null;
    }

    /*
    |--------------------------------------------------------------------------
    | CATÁLOGO
    |--------------------------------------------------------------------------
    */

    public function abrirModalCatalogo(): void
    {
        $this->modalCatalogo = true;
    }

    public function cerrarModalCatalogo(): void
    {
        $this->modalCatalogo = false;
    }

    public function usarDesdeCatalogo(string $sigla): void
    {
        $analisis = AsignaturaInteligente::desdeSigla($sigla);

        if (! ($analisis['valido'] ?? false)) {
            $this->dispatch('advertencia-general', mensaje: $analisis['mensaje'] ?? 'No se pudo usar esta asignatura del catálogo.');
            return;
        }

        $this->form['nom_asi'] = $analisis['nombre'];
        $this->form['sig_asi'] = $analisis['sigla'];
        $this->form['hor_asi'] = $analisis['horas'];
        $this->form['est_asi'] = 'ACTIVO';

        $this->analisisCrear = AsignaturaInteligente::interpretar(
            $this->form['nom_asi'],
            $this->obtenerAsignaturasExistentes()
        );

        $this->modalCatalogo = false;
        $this->modalCrear = true;
    }

    /*
    |--------------------------------------------------------------------------
    | DESACTIVAR / REACTIVAR
    |--------------------------------------------------------------------------
    */

    public function solicitarDesactivar(string $codAsi): void
    {
        $asignatura = Asignatura::where('cod_asi', $codAsi)->firstOrFail();
        $uso = $this->obtenerUsoAcademico($codAsi);

        $mensaje = $uso['total'] > 0
            ? 'Esta asignatura tiene uso académico. Se desactivará para nuevas planificaciones, pero conservará su historial.'
            : 'La asignatura será desactivada y no estará disponible para nuevas planificaciones.';

        $this->dispatch(
            'confirmar-desactivar',
            codigo: $asignatura->cod_asi,
            titulo: '¿Desactivar asignatura?',
            mensaje: $mensaje
        );
    }

    public function desactivarAsignatura(string $codAsi): void
    {
        $asignatura = Asignatura::where('cod_asi', $codAsi)->firstOrFail();

        if ($asignatura->est_asi === 'INACTIVO') {
            $this->dispatch('advertencia-general', mensaje: 'La asignatura ya se encuentra inactiva.');
            return;
        }

        $valoresAnteriores = $asignatura->toArray();
        $uso = $this->obtenerUsoAcademico($asignatura->cod_asi);

        try {
            DB::transaction(function () use ($asignatura, $valoresAnteriores, $uso) {
                $asignatura->update([
                    'est_asi' => 'INACTIVO',
                ]);

                $this->registrarBitacoraSeguro(
                    accion: 'DESACTIVAR_ASIGNATURA',
                    tabla: 'asignatura',
                    registro: $asignatura->cod_asi,
                    nombreRegistro: $asignatura->nom_asi,
                    descripcion: 'Se desactivó la asignatura ' . $asignatura->nom_asi . '.',
                    nivel: $uso['total'] > 0 ? 'WARNING' : 'INFO',
                    resultado: 'EXITOSO',
                    valoresAnteriores: [
                        'asignatura' => $valoresAnteriores,
                        'uso_academico' => $uso,
                    ],
                    valoresNuevos: [
                        'asignatura' => $asignatura->fresh()?->toArray(),
                    ]
                );
            });

            $this->dispatch('asignatura-desactivada', mensaje: 'Asignatura desactivada correctamente.');
        } catch (Throwable $e) {
            report($e);

            $this->dispatch('error-general', mensaje: 'No se pudo desactivar la asignatura.');
        }
    }

    public function solicitarReactivar(string $codAsi): void
    {
        $asignatura = Asignatura::where('cod_asi', $codAsi)->firstOrFail();

        $this->dispatch(
            'confirmar-reactivar',
            codigo: $asignatura->cod_asi,
            titulo: '¿Reactivar asignatura?',
            mensaje: 'La asignatura volverá a estar disponible para nuevas planificaciones académicas.'
        );
    }

    public function reactivarAsignatura(string $codAsi): void
    {
        $asignatura = Asignatura::where('cod_asi', $codAsi)->firstOrFail();

        if ($asignatura->est_asi === 'ACTIVO') {
            $this->dispatch('advertencia-general', mensaje: 'La asignatura ya se encuentra activa.');
            return;
        }

        $valoresAnteriores = $asignatura->toArray();

        try {
            DB::transaction(function () use ($asignatura, $valoresAnteriores) {
                $asignatura->update([
                    'est_asi' => 'ACTIVO',
                ]);

                $this->registrarBitacoraSeguro(
                    accion: 'REACTIVAR_ASIGNATURA',
                    tabla: 'asignatura',
                    registro: $asignatura->cod_asi,
                    nombreRegistro: $asignatura->nom_asi,
                    descripcion: 'Se reactivó la asignatura ' . $asignatura->nom_asi . '.',
                    nivel: 'SUCCESS',
                    resultado: 'EXITOSO',
                    valoresAnteriores: [
                        'asignatura' => $valoresAnteriores,
                    ],
                    valoresNuevos: [
                        'asignatura' => $asignatura->fresh()?->toArray(),
                    ]
                );
            });

            $this->dispatch('asignatura-reactivada', mensaje: 'Asignatura reactivada correctamente.');
        } catch (Throwable $e) {
            report($e);

            $this->dispatch('error-general', mensaje: 'No se pudo reactivar la asignatura.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | USO ACADÉMICO
    |--------------------------------------------------------------------------
    */

    public function obtenerUsoAcademico(string $codAsi): array
    {
        $planes = $this->contarUsoTabla('plan_asignatura', 'cod_asi', $codAsi);
        $calificaciones = $this->contarUsoTabla('calificacion', 'cod_asi', $codAsi);

        $horarios = 0;

        if (Schema::hasTable('horario_detalle')) {
            if (Schema::hasColumn('horario_detalle', 'cod_asi')) {
                $horarios += $this->contarUsoTabla('horario_detalle', 'cod_asi', $codAsi);
            }

            if (Schema::hasColumn('horario_detalle', 'cod_pas') && Schema::hasTable('plan_asignatura')) {
                $planesAsignatura = DB::table('plan_asignatura')
                    ->where('cod_asi', $codAsi)
                    ->pluck('cod_pas')
                    ->filter()
                    ->values();

                if ($planesAsignatura->isNotEmpty()) {
                    $horarios += DB::table('horario_detalle')
                        ->whereIn('cod_pas', $planesAsignatura)
                        ->count();
                }
            }
        }

        $total = $planes + $calificaciones + $horarios;

        return [
            'planes' => $planes,
            'calificaciones' => $calificaciones,
            'horarios' => $horarios,
            'total' => $total,
            'tiene_uso' => $total > 0,
            'texto' => $this->textoUsoAcademico($planes, $calificaciones, $horarios),
        ];
    }

    private function contarUsoTabla(string $tabla, string $columna, string $codAsi): int
    {
        if (! Schema::hasTable($tabla) || ! Schema::hasColumn($tabla, $columna)) {
            return 0;
        }

        return DB::table($tabla)
            ->where($columna, $codAsi)
            ->count();
    }

    private function textoUsoAcademico(int $planes, int $calificaciones, int $horarios): string
    {
        $partes = [];

        if ($planes > 0) {
            $partes[] = $planes . ' plan' . ($planes === 1 ? '' : 'es');
        }

        if ($calificaciones > 0) {
            $partes[] = $calificaciones . ' calificación' . ($calificaciones === 1 ? '' : 'es');
        }

        if ($horarios > 0) {
            $partes[] = $horarios . ' horario' . ($horarios === 1 ? '' : 's');
        }

        return count($partes) > 0 ? implode(' / ', $partes) : 'Sin uso académico';
    }

    private function recomendacionInstitucional(Asignatura $asignatura, array $uso, array $analisis): string
    {
        if (($analisis['estado_inteligente'] ?? '') === AsignaturaInteligente::ESTADO_BLOQUEADA) {
            return 'La asignatura requiere revisión porque no pudo validarse claramente como materia académica.';
        }

        if ($uso['calificaciones'] > 0) {
            return 'Asignatura con historial de calificaciones. No se recomienda cambiar su identidad académica; solo realizar correcciones menores.';
        }

        if ($uso['total'] > 0) {
            return 'Asignatura vinculada a planificación académica. Puede actualizarse con cuidado, conservando trazabilidad institucional.';
        }

        if ($asignatura->est_asi === 'INACTIVO') {
            return 'Asignatura inactiva y sin uso académico relevante. Puede reactivarse si vuelve a ser necesaria.';
        }

        return 'Asignatura activa y disponible para planes de asignatura, horarios, evaluaciones y calificaciones.';
    }

    /*
    |--------------------------------------------------------------------------
    | VALIDACIONES
    |--------------------------------------------------------------------------
    */

    private function rulesCrear(): array
    {
        return [
            'form.nom_asi' => [
                'required',
                'string',
                'min:3',
                'max:150',
                Rule::unique('asignatura', 'nom_asi'),
            ],
            'form.sig_asi' => [
                'required',
                'string',
                'min:2',
                'max:15',
                Rule::unique('asignatura', 'sig_asi'),
            ],
            'form.hor_asi' => [
                'required',
                'integer',
                'min:1',
                'max:80',
            ],
            'form.est_asi' => [
                'required',
                Rule::in(array_keys($this->estadosDisponibles)),
            ],
        ];
    }

    private function rulesEditar(): array
    {
        $codAsi = $this->formEditar['cod_asi'] ?? null;

        return [
            'formEditar.cod_asi' => [
                'required',
                'string',
                'exists:asignatura,cod_asi',
            ],
            'formEditar.nom_asi' => [
                'required',
                'string',
                'min:3',
                'max:150',
                Rule::unique('asignatura', 'nom_asi')->ignore($codAsi, 'cod_asi'),
            ],
            'formEditar.sig_asi' => [
                'required',
                'string',
                'min:2',
                'max:15',
                Rule::unique('asignatura', 'sig_asi')->ignore($codAsi, 'cod_asi'),
            ],
            'formEditar.hor_asi' => [
                'required',
                'integer',
                'min:1',
                'max:80',
            ],
            'formEditar.est_asi' => [
                'required',
                Rule::in(array_keys($this->estadosDisponibles)),
            ],
        ];
    }

    private function validationAttributesCrear(): array
    {
        return [
            'form.nom_asi' => 'nombre de la asignatura',
            'form.sig_asi' => 'sigla de la asignatura',
            'form.hor_asi' => 'horas académicas',
            'form.est_asi' => 'estado',
        ];
    }

    private function validationAttributesEditar(): array
    {
        return [
            'formEditar.cod_asi' => 'código de la asignatura',
            'formEditar.nom_asi' => 'nombre de la asignatura',
            'formEditar.sig_asi' => 'sigla de la asignatura',
            'formEditar.hor_asi' => 'horas académicas',
            'formEditar.est_asi' => 'estado',
        ];
    }

    protected function messages(): array
    {
        return [
            'required' => 'El campo :attribute es obligatorio.',
            'string' => 'El campo :attribute debe ser texto.',
            'integer' => 'El campo :attribute debe ser un número entero.',
            'min' => 'El campo :attribute no cumple el valor mínimo permitido.',
            'max' => 'El campo :attribute supera el máximo permitido.',
            'unique' => 'Ya existe un registro con este :attribute.',
            'exists' => 'El registro seleccionado no existe.',
            'in' => 'El valor seleccionado para :attribute no es válido.',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | NORMALIZACIÓN DE FORMULARIOS
    |--------------------------------------------------------------------------
    */

    private function normalizarFormularioCrear(): void
    {
        $this->form['nom_asi'] = $this->limpiarNombre($this->form['nom_asi'] ?? '');
        $this->form['sig_asi'] = $this->limpiarSigla($this->form['sig_asi'] ?? '');
        $this->form['hor_asi'] = (int) ($this->form['hor_asi'] ?? 2);
        $this->form['est_asi'] = $this->form['est_asi'] ?: 'ACTIVO';
    }

    private function normalizarFormularioEditar(): void
    {
        $this->formEditar['nom_asi'] = $this->limpiarNombre($this->formEditar['nom_asi'] ?? '');
        $this->formEditar['sig_asi'] = $this->limpiarSigla($this->formEditar['sig_asi'] ?? '');
        $this->formEditar['hor_asi'] = (int) ($this->formEditar['hor_asi'] ?? 2);
        $this->formEditar['est_asi'] = $this->formEditar['est_asi'] ?: 'ACTIVO';
    }

    private function limpiarNombre(string $nombre): string
    {
        $nombre = trim($nombre);
        $nombre = preg_replace('/\s+/', ' ', $nombre) ?? '';

        return $nombre;
    }

    private function limpiarSigla(string $sigla): string
    {
        $sigla = trim($sigla);
        $sigla = preg_replace('/[^a-zA-Z0-9]/', '', $sigla) ?? '';

        return mb_strtoupper($sigla);
    }

    /*
    |--------------------------------------------------------------------------
    | DATOS AUXILIARES
    |--------------------------------------------------------------------------
    */

    private function obtenerAsignaturasExistentes(): array
    {
        return Asignatura::query()
            ->select('cod_asi', 'nom_asi', 'sig_asi')
            ->get()
            ->map(fn(Asignatura $asignatura) => [
                'cod_asi' => $asignatura->cod_asi,
                'nom_asi' => $asignatura->nom_asi,
                'sig_asi' => $asignatura->sig_asi,
            ])
            ->toArray();
    }

    private function reiniciarAnalisisCrear(): void
    {
        $this->analisisCrear = AsignaturaInteligente::interpretar('');
    }

    private function reiniciarAnalisisEditar(): void
    {
        $this->analisisEditar = AsignaturaInteligente::interpretar('');
    }

    /*
    |--------------------------------------------------------------------------
    | BITÁCORA SEGURA
    |--------------------------------------------------------------------------
    */

    private function registrarBitacoraSeguro(
        string $accion,
        string $tabla,
        ?string $registro = null,
        ?string $nombreRegistro = null,
        ?string $descripcion = null,
        string $nivel = 'INFO',
        string $resultado = 'EXITOSO',
        ?array $valoresAnteriores = null,
        ?array $valoresNuevos = null,
        ?string $error = null
    ): void {
        try {
            if (! class_exists(BitacoraService::class)) {
                return;
            }

            BitacoraService::registrar(
                accion: $accion,
                tabla: $tabla,
                registro: $registro,
                modulo: 'Gestión de Asignaturas',
                nombreRegistro: $nombreRegistro,
                descripcion: $descripcion,
                nivel: $nivel,
                resultado: $resultado,
                valoresAnteriores: $valoresAnteriores,
                valoresNuevos: $valoresNuevos,
                error: $error
            );
        } catch (Throwable $e) {
            report($e);
        }
    }
}
