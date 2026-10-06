<?php

namespace App\Livewire\Admin;

use App\Models\Oficial\Academico\Asignatura;
use App\Services\BitacoraService;
use App\Support\Academico\AsignaturaInteligente;
use App\Support\Academico\ConsultaAsignaturasInstitucionales;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
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
    use \App\Livewire\Admin\Concerns\VerificaDocumentoCurricular;

    protected string $paginationTheme = 'tailwind';

    /*
    |--------------------------------------------------------------------------
    | FILTROS Y ESTADO DE TABLA
    |--------------------------------------------------------------------------
    */

    public string $search = '';
    public string $estado = '';
    public string $usoAcademico = '';
    public string $campoEducativo = '';
    public string $gestionFiltro = '';
    public string $motivoEdicion = '';
    public string $motivoCambio = '';
    public bool $confirmarCambio = false;
    public bool $modalCambio = false;
    #[Locked] public ?string $cambioCodigo = null;
    #[Locked] public string $tipoCambio = '';
    public int $perPage = 10;
    public string $filtroHoras = '';
    public string $filtroDocentes = '';
    public string $filtroClases = '';
    public function updatedFiltroHoras(): void { $this->resetPage(); }
    public function updatedFiltroDocentes(): void { $this->resetPage(); }
    public function updatedFiltroClases(): void { $this->resetPage(); }
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

    #[Locked] public array $analisisCrear = [];
    #[Locked] public array $analisisEditar = [];

    /*
    |--------------------------------------------------------------------------
    | DETALLE
    |--------------------------------------------------------------------------
    */

    #[Locked] public ?string $asignaturaSeleccionada = null;
    #[Locked] public array $detalleAsignatura = [];

    /*
    |--------------------------------------------------------------------------
    | CONFIGURACIÓN
    |--------------------------------------------------------------------------
    */

    public array $estadosDisponibles = [
        'ACTIVO' => 'Vigente',
        'INACTIVO' => 'Retirada del catálogo',
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
        $this->autorizar();
        $this->gestionFiltro = $this->gestiones->firstWhere('est_gea','ACTIVO')->cod_gea ?? $this->gestiones->first()?->cod_gea ?? '';
        $this->reiniciarAnalisisCrear();
        $this->reiniciarAnalisisEditar();
    }

    public function render()
    {
        $this->autorizar();
        return view('livewire.admin.gestion-asignatura', [
            'asignaturas'=>$this->obtenerAsignaturasPaginadas(),
            'resumen'=>$this->obtenerResumen(), 'materias'=>$this->materias,
            'gestiones'=>$this->gestiones, 'gestion'=>$this->gestiones->firstWhere('cod_gea',$this->gestionFiltro),
            'campos'=>ConsultaAsignaturasInstitucionales::campos(),
            'catalogoInteligente'=>$this->catalogoPendiente,
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
            'campoEducativo',
            'filtroHoras','filtroDocentes','filtroClases',
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
        $materias=$this->materias->filter(function($m){
            if ($this->search !== '' && !str_contains(AsignaturaInteligente::normalizar($m->nom_asi.' '.$m->sig_asi),AsignaturaInteligente::normalizar($this->search))) return false;
            if ($this->estado && $m->est_asi !== $this->estado) return false;
            if ($this->campoEducativo && $m->campo_educativo['clave'] !== $this->campoEducativo) return false;
            if($this->filtroHoras==='hasta2' && $m->hor_asi>2 || $this->filtroHoras==='3a4' && ($m->hor_asi<3 || $m->hor_asi>4) || $this->filtroHoras==='5mas' && $m->hor_asi<5) return false;
            if($this->filtroDocentes==='ninguno' && $m->docentes_actuales>0 || $this->filtroDocentes==='uno' && $m->docentes_actuales!==1 || $this->filtroDocentes==='varios' && $m->docentes_actuales<2) return false;
            if($this->filtroClases==='con' && $m->bloques_actuales===0 || $this->filtroClases==='sin' && $m->bloques_actuales>0) return false;
            return match($this->usoAcademico){
                'CON_USO','CON_PLAN'=>$m->planes_actuales>0,
                'SIN_USO'=>$m->planes_actuales===0,
                'CON_CALIFICACIONES'=>$m->uso_academico['calificaciones']>0,
                default=>true,
            };
        });
        $campo=in_array($this->sortField,['nom_asi','sig_asi','hor_asi','est_asi'],true)?$this->sortField:'nom_asi';
        $materias=$this->sortDirection==='desc'?$materias->sortByDesc($campo):$materias->sortBy($campo);
        $cantidad=in_array($this->perPage,[10,20,50],true)?$this->perPage:10;
        $pagina=max(1,min((int)$this->getPage(),max(1,(int)ceil($materias->count()/$cantidad))));
        return new \Illuminate\Pagination\LengthAwarePaginator($materias->values()->forPage($pagina,$cantidad),$materias->count(),$cantidad,$pagina);
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
                    ->from('calificacion')->join('plan_asignatura as plan_notas_asignatura', 'plan_notas_asignatura.cod_pas', '=', 'calificacion.cod_pas')
                    ->whereColumn('plan_notas_asignatura.cod_asi', 'asignatura.cod_asi');
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
                            ->from('calificacion')->join('plan_asignatura as plan_notas_asignatura', 'plan_notas_asignatura.cod_pas', '=', 'calificacion.cod_pas')
                            ->whereColumn('plan_notas_asignatura.cod_asi', 'asignatura.cod_asi');
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
                        ->from('calificacion')->join('plan_asignatura as plan_notas_asignatura', 'plan_notas_asignatura.cod_pas', '=', 'calificacion.cod_pas')
                        ->whereColumn('plan_notas_asignatura.cod_asi', 'asignatura.cod_asi');
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
        $materias=$this->materias;
        return ['total'=>$materias->count(),'activas'=>$materias->where('est_asi','ACTIVO')->count(),
            'inactivas'=>$materias->where('est_asi','INACTIVO')->count(), 'horas'=>$materias->sum('hor_asi'),
            'con_plan'=>$materias->where('planes_actuales','>',0)->count(),
            'con_calificaciones'=>$materias->filter(fn($m)=>$m->uso_academico['calificaciones']>0)->count(),
            'con_uso'=>$materias->where('planes_actuales','>',0)->count(),
            'sin_uso'=>$materias->where('planes_actuales',0)->count()];
    }

    private function contarAsignaturasConRelacion(string $tabla, string $columnaAsignatura): int
    {
        if ($tabla === 'calificacion' && $columnaAsignatura === 'cod_asi') {
            return DB::table('calificacion')->join('plan_asignatura as asignaturas_con_nota', 'asignaturas_con_nota.cod_pas', '=', 'calificacion.cod_pas')
                ->distinct()->count('asignaturas_con_nota.cod_asi');
        }
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
        $this->autorizar();
        $this->resetValidation();
        $this->limpiarFormularioCrear();
        $this->prepararDocumentoCurricular('crear');
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
        $this->autorizar();
        $existentes = $this->obtenerAsignaturasExistentes();

        $this->analisisCrear = AsignaturaInteligente::interpretar(
            $this->form['nom_asi'] ?? '',
            $existentes
        );

        // La sigla y carga autorizadas se conservan tal como fueron leídas del documento.
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
        $this->autorizar();
        $revision=$this->comprobarDocumentoCurricular($this->form);
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

        if(!AsignaturaInteligente::siglaCompatible($this->form['nom_asi'],$this->form['sig_asi'])){
            $this->addError('form.sig_asi','La sigla debe corresponder al nombre. Ejemplo sugerido: '.AsignaturaInteligente::generarSigla($this->form['nom_asi']));
            return;
        }
        app(\App\Services\IncorporacionCurricularService::class)->programar('asignatura',$this->form,$revision,$this->documentoCurricular);
        $this->cerrarModalCrear();
        $this->dispatch('asignatura-creada',mensaje:'Incorporación programada para la siguiente gestión. Se publicó el aviso institucional.');
        return;

    }

    /*
    |--------------------------------------------------------------------------
    | MODAL EDITAR
    |--------------------------------------------------------------------------
    */

    public function abrirModalEditar(string $codAsi): void
    {
        $this->autorizar();
        $this->resetValidation();
        $this->motivoEdicion = '';
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

        $this->prepararDocumentoCurricular('editar');
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
        $this->autorizar();
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
        $this->autorizar();
        $revision=$this->comprobarDocumentoCurricular($this->formEditar);
        $this->motivoEdicion=$this->motivoCurricular;
        if(!AsignaturaInteligente::siglaCompatible($this->formEditar['nom_asi'],$this->formEditar['sig_asi'])){
            $this->addError('formEditar.sig_asi','La sigla no corresponde al nombre. Sugerencia: '.AsignaturaInteligente::generarSigla($this->formEditar['nom_asi']));
            return;
        }
        $this->normalizarFormularioEditar();
        $this->interpretarAsignaturaEditar();

        abort_unless($this->asignaturaSeleccionada && $this->asignaturaSeleccionada === ($this->formEditar['cod_asi'] ?? null), 422);
        $this->validate(['motivoEdicion'=>['required','string','min:15','max:1500']]);
        $asignatura = Asignatura::findOrFail($this->asignaturaSeleccionada);
        $this->formEditar['est_asi'] = $asignatura->est_asi;
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

        $revision = $this->conservarDocumentoCurricular($revision);
        try {
            DB::transaction(function () use ($asignatura, $valoresAnteriores, $uso, $revision) {
                if(!Schema::hasTable('bitacora')) throw ValidationException::withMessages(['documentoCurricular'=>'La bitácora no está disponible. No se guardó el cambio.']);
                $asignatura = Asignatura::lockForUpdate()->findOrFail($this->asignaturaSeleccionada);
                $usoActual = $this->usoHistoricoActual($asignatura->cod_asi);
                if ($usoActual['calificaciones'] > 0 && !AsignaturaInteligente::esCorreccionMenor($asignatura->nom_asi,$this->formEditar['nom_asi'])) throw ValidationException::withMessages(['formEditar.nom_asi'=>'Conserva la identidad de esta materia: ya tiene notas registradas.']);
                $asignatura->update([
                    'nom_asi' => $this->formEditar['nom_asi'],
                    'sig_asi' => mb_strtoupper($this->formEditar['sig_asi']),
                    'hor_asi' => (int) $this->formEditar['hor_asi'],
                    'est_asi' => $asignatura->est_asi,
                ]);

                $nivel = ($uso['total'] > 0 || ($this->analisisEditar['requiere_revision'] ?? false))
                    ? 'WARNING'
                    : 'SUCCESS';

                $this->registrarBitacoraSeguro(
                    accion: 'EDITAR_ASIGNATURA',
                    tabla: 'asignatura',
                    registro: $asignatura->cod_asi,
                    nombreRegistro: $asignatura->nom_asi,
                    descripcion: trim($this->motivoEdicion),
                    nivel: $nivel,
                    resultado: 'EXITOSO',
                    valoresAnteriores: [
                        'asignatura' => $valoresAnteriores,
                        'uso_academico' => $uso,
                    ],
                    valoresNuevos: [
                        'asignatura' => $asignatura->fresh()?->toArray(),
                        'analisis_inteligente' => $this->analisisEditar,
                        'motivo' => trim($this->motivoEdicion),
                        'documento' => $revision,
                    ]
                );
            });

            $this->cerrarModalEditar();

            unset($this->materias,$this->catalogoPendiente);
            $this->dispatch('asignatura-actualizada', mensaje: 'Asignatura actualizada correctamente.');
        } catch (ValidationException $e) {
            \Illuminate\Support\Facades\Storage::disk('local')->delete($revision['ruta_pdf']);
            throw $e;
        } catch (Throwable $e) {
            \Illuminate\Support\Facades\Storage::disk('local')->delete($revision['ruta_pdf']);
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
        $this->autorizar();
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
            'campo' => ConsultaAsignaturasInstitucionales::campo($analisis['area'] ?? ''),
            'docentes' => app(ConsultaAsignaturasInstitucionales::class)->docentes($codAsi,$this->gestionFiltro),
            'historial' => app(ConsultaAsignaturasInstitucionales::class)->historial($codAsi),
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
        $this->autorizar();
        $this->modalCatalogo = true;
    }

    public function cerrarModalCatalogo(): void
    {
        $this->modalCatalogo = false;
    }

    public function usarDesdeCatalogo(string $sigla): void
    {
        $this->autorizar();
        abort_unless(collect($this->catalogoPendiente)->contains('sigla',$sigla),422);
        $this->limpiarFormularioCrear();
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
        $this->prepararCambioCatalogo($codAsi,'retirar');
    }

    public function desactivarAsignatura(string $codAsi): void
    {
        $this->guardarCambioCatalogo($codAsi,'retirar');
    }

    public function solicitarReactivar(string $codAsi): void
    {
        $this->prepararCambioCatalogo($codAsi,'recuperar');
    }

    public function reactivarAsignatura(string $codAsi): void
    {
        $this->guardarCambioCatalogo($codAsi,'recuperar');
    }

    /*
    |--------------------------------------------------------------------------
    | USO ACADÉMICO
    |--------------------------------------------------------------------------
    */

    public function obtenerUsoAcademico(string $codAsi): array
    {
        $this->autorizar();
        $materia=$this->materias->firstWhere('cod_asi',$codAsi);
        abort_unless($materia,404);
        return $materia->uso_academico;
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
            'motivoEdicion.required' => 'Explica el motivo institucional de esta edición.',
            'motivoEdicion.min' => 'Describe el motivo de la edición en al menos 15 caracteres.',
            'motivoCambio.required' => 'Explica el motivo institucional de este cambio.',
            'motivoCambio.min' => 'Describe el motivo del cambio en al menos 15 caracteres.',
            'confirmarCambio.accepted' => 'Confirma que revisaste el efecto y el respaldo del cambio.',
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
        $this->form['est_asi'] = 'ACTIVO';
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

    private function autorizar(): void
    {
        abort_unless(auth()->user()?->hasRole('Administrador'),403);
        Gate::authorize('Asignaturas');
    }

    public function getGestionesProperty(): Collection
    {
        return DB::table('gestion_academica')->orderByDesc('ani_gea')->get();
    }

    public function getMateriasProperty(): Collection
    {
        abort_unless($this->gestiones->contains('cod_gea',$this->gestionFiltro),422);
        return app(ConsultaAsignaturasInstitucionales::class)->catalogo($this->gestionFiltro);
    }

    public function getCatalogoPendienteProperty(): array
    {
        $nombres=$this->materias->flatMap(fn($m)=>[AsignaturaInteligente::normalizar($m->nom_asi),AsignaturaInteligente::normalizar($m->analisis_inteligente['nombre']??'')]);
        $siglas=$this->materias->pluck('sig_asi')->map(fn($s)=>mb_strtoupper($s));
        return collect(AsignaturaInteligente::catalogoSugerencias())->reject(fn($s)=>$nombres->contains(AsignaturaInteligente::normalizar($s['nombre'])) || $siglas->contains(mb_strtoupper($s['sigla'])))->values()->all();
    }

    public function updatedCampoEducativo(): void { $this->resetPage(); }

    public function updatedGestionFiltro(): void
    {
        $this->resetPage();
        $this->modalDetalle=false;
        unset($this->materias);
    }

    private function usoHistoricoActual(string $codigo): array
    {
        return ['calificaciones'=>DB::table('calificacion as n')->join('plan_asignatura as p','p.cod_pas','=','n.cod_pas')->where('p.cod_asi',$codigo)->where('n.est_cal','<>','ANULADA')->count()];
    }

    private function prepararCambioCatalogo(string $codigo,string $tipo): void
    {
        $this->autorizar();
        $this->resetValidation();
        $this->abrirModalDetalle($codigo);
        $this->modalDetalle=false;
        $this->motivoCambio='';
        $this->confirmarCambio=false;
        $this->cambioCodigo=$codigo;
        $this->tipoCambio=$tipo;
        $this->modalCambio=true;
    }

    public function guardarCambioCatalogo(string $codigo,string $tipo): void
    {
        $this->autorizar();
        abort_unless($this->modalCambio && $this->cambioCodigo===$codigo && $this->tipoCambio===$tipo && in_array($tipo,['retirar','recuperar'],true),422);
        $this->motivoCambio=trim($this->motivoCambio);
        $this->validate(['motivoCambio'=>['required','string','min:15','max:1500'],'confirmarCambio'=>['accepted']]);
        try {
            DB::transaction(function() use($codigo,$tipo){
                $materia=Asignatura::lockForUpdate()->findOrFail($codigo);
                $estado=$tipo==='retirar'?'INACTIVO':'ACTIVO';
                if ($materia->est_asi===$estado) throw ValidationException::withMessages(['motivoCambio'=>'La disponibilidad de esta materia ya cambió. Cierra esta revisión y vuelve a consultar.']);
                $anterior=$materia->toArray();
                $materia->update(['est_asi'=>$estado]);
                $this->registrarBitacoraSeguro(accion:$tipo==='retirar'?'DESACTIVAR_ASIGNATURA':'REACTIVAR_ASIGNATURA',tabla:'asignatura',registro:$codigo,nombreRegistro:$materia->nom_asi,descripcion:$this->motivoCambio,
                    valoresAnteriores:['asignatura'=>$anterior],valoresNuevos:['asignatura'=>$materia->toArray(),'motivo'=>$this->motivoCambio,'gestion_consultada'=>$this->gestionFiltro]);
            });
            unset($this->materias,$this->catalogoPendiente);
            $this->modalCambio=false;
            $this->modalDetalle=false;
            $this->dispatch($tipo==='retirar'?'asignatura-desactivada':'asignatura-reactivada',mensaje:$tipo==='retirar'?'Materia retirada del catálogo. Su historial se conserva.':'Materia disponible nuevamente para planificación.');
        } catch(ValidationException $e) { throw $e; }
        catch(Throwable $e) { report($e); $this->addError('motivoCambio','No pudimos registrar el cambio y su motivo. Conservamos la materia; vuelve a intentarlo.'); }
    }

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
            if (! Schema::hasTable('bitacora')) {
                throw new \RuntimeException('No está disponible el registro de cambios institucionales.');
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
            if ($resultado === 'EXITOSO') throw $e;
        }
    }
}
