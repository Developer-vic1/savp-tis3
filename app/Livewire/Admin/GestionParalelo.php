<?php

namespace App\Livewire\Admin;

use App\Models\Oficial\Academico\Bitacora;
use App\Models\Oficial\Academico\Paralelo;
use App\Services\BitacoraService;
use App\Support\Academico\ParaleloInteligente;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Locked;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use App\Support\Academico\ConsultaParalelosInstitucionales;
use App\Support\Academico\RespaldoCursoInstitucional;
use App\Support\Academico\ExpedienteParaleloInstitucional;
use Livewire\WithPagination;
use Throwable;

class GestionParalelo extends Component
{
    use WithPagination, WithFileUploads;

    private ?array $instantanea = null;
    public string $motivoCambio = '';
    public string $causaCambio = '';
    public string $tipoDocumento = 'resolucion';
    public string $numeroDocumento = '';
    public string $fechaDocumento = '';
    public string $autoridadDocumento = '';
    public string $referenciaVerificacion = '';
    public string $gestionSolicitud = '';
    public bool $confirmarImpacto = false;
    public $documentoCambio;
    #[Locked] public array $revisionDocumento = [];
    #[Locked] public int $faseCrear = 1;
    #[Locked] public string $rechazoDocumentoRegistrado = '';
    #[Locked] public string $mensajeAutollenado = '';
    private array $expedienteGuardado = [];

    private function autorizar(): void
    {
        abort_unless(auth()->check(), 403);
        Gate::authorize('Paralelos');
    }

    private function datosVigentes(): array
    {
        return $this->instantanea ??= app(ConsultaParalelosInstitucionales::class)->consultar();
    }

    private function reiniciarExpediente(): void
    {
        $this->reset('motivoCambio', 'causaCambio', 'tipoDocumento', 'numeroDocumento', 'fechaDocumento', 'autoridadDocumento', 'referenciaVerificacion', 'documentoCambio', 'confirmarImpacto', 'revisionDocumento', 'faseCrear', 'rechazoDocumentoRegistrado', 'mensajeAutollenado');
        $this->gestionSolicitud = (string) ($this->datosVigentes()['gestion']?->ani_gea ?? '');
    }

    public function updated($campo): void
    {
        if (in_array($campo, ['filtroGrado','filtroTurno','filtroCapacidad','filtroDocumentacion'])) $this->resetPage();
        if ($campo === 'form.est_par') $this->confirmarImpacto = false;
        if (in_array($campo, ['documentoCambio', 'numeroDocumento', 'tipoDocumento', 'form.nom_par', 'formEditar.nom_par', 'gestionSolicitud', 'fechaDocumento'])) {
            $this->mensajeAutollenado = '';
            $this->revisionDocumento = [];
            $this->confirmarImpacto = false;
            if ($this->modalCrear && $this->faseCrear === 3) $this->faseCrear = 2;
        }
        $reglas = $this->reglasCamposExpediente();
        if (array_key_exists($campo, $reglas)) {
            $this->validateOnly($campo, $reglas, $this->mensajesCamposExpediente());
            if ($campo === 'motivoCambio') $this->validarJustificacion();
        }
    }

    private function reglasCamposExpediente(): array
    {
        return ['causaCambio'=>['required', Rule::in(['demanda','infraestructura','reorganizacion','rectificacion','otro'])],
            'motivoCambio'=>'required|string|min:30|max:2000',
            'numeroDocumento'=>['required','string','min:3','max:100','regex:/^[\p{L}\p{N}][\p{L}\p{N} .\/\-]*\d[\p{L}\p{N} .\/\-]*$/u'],
            'fechaDocumento'=>'required|date|before_or_equal:'.now('America/La_Paz')->toDateString(),
            'gestionSolicitud'=>'required|integer|min:'.now()->year.'|max:'.(now()->year+1),
            'autoridadDocumento'=>['required','string','min:8','max:200','regex:/[\p{L}]{3,}\s+[\p{L}]{3,}/u'],
            'referenciaVerificacion'=>'required|string|min:15|max:500',
            'tipoDocumento'=>['required', Rule::in(['resolucion','rectificacion'])], 'documentoCambio'=>'required|file|mimes:pdf|max:8192'];
    }

    private function mensajesCamposExpediente(): array
    {
        return ['causaCambio.required'=>'Selecciona el motivo que justifica el cambio.',
            'motivoCambio.required'=>'Explica la necesidad, los grados afectados y los ambientes disponibles.',
            'motivoCambio.min'=>'Escribe al menos 30 caracteres con una explicación concreta.',
            'numeroDocumento.required'=>'Copia el número completo de la resolución. Ejemplo de formato: 123/2026.',
            'numeroDocumento.regex'=>'Usa la referencia oficial con números. Ejemplo de formato: DDE-123/2026.',
            'fechaDocumento.required'=>'Indica la fecha de emisión que figura en el PDF.',
            'fechaDocumento.before_or_equal'=>'La fecha de emisión no puede ser posterior a hoy.',
            'autoridadDocumento.required'=>'Escribe la autoridad emisora que aparece en el documento.',
            'autoridadDocumento.regex'=>'Escribe el nombre completo de la autoridad, con palabras separadas.',
            'gestionSolicitud.required'=>'Indica el año autorizado en el documento.',
            'gestionSolicitud.min'=>'La gestión debe ser la vigente o la próxima.', 'gestionSolicitud.max'=>'La gestión debe ser la vigente o la próxima.',
            'documentoCambio.required'=>'Adjunta el PDF para leerlo o revisarlo.',
            'documentoCambio.mimes'=>'El respaldo debe ser un archivo PDF.', 'documentoCambio.max'=>'El PDF no puede superar 8 MB.',
            'referenciaVerificacion.required'=>'Registra cuándo, por qué canal oficial y con qué referencia comprobaste el respaldo.'];
    }

    public function updatedGestionSolicitud(): void
    {
        if ((int)$this->gestionSolicitud > now()->year && $this->modalCrear) $this->form['est_par'] = 'INACTIVO';
    }

    public function revisarDocumento(): void
    {
        $this->autorizar();
        $this->validate(['documentoCambio' => 'required|file|mimes:pdf|max:8192', 'numeroDocumento' => 'required|string|min:3|max:100', 'fechaDocumento' => 'required|date|before_or_equal:'.now('America/La_Paz')->toDateString(), 'gestionSolicitud' => 'required|integer', 'tipoDocumento' => ['required', Rule::in(['resolucion', 'rectificacion'])]]);
        $lectura = app(RespaldoCursoInstitucional::class)->leer($this->documentoCambio->getRealPath(), 'documentoCambio');
        $nombre = $this->modalEditar ? $this->formEditar['nom_par'] : $this->form['nom_par'];
        $this->revisionDocumento = ExpedienteParaleloInstitucional::revisar($lectura['texto'], $this->numeroDocumento, $nombre, $this->tipoDocumento, $this->fechaDocumento, $this->gestionSolicitud);
        $this->registrarRechazoDocumento($lectura, $nombre, ['numero'=>$this->numeroDocumento, 'fecha'=>$this->fechaDocumento, 'gestion'=>$this->gestionSolicitud, 'tipo'=>$this->tipoDocumento]);
    }

    public function completarDesdePdf(): void
    {
        $this->autorizar();
        abort_unless($this->modalCrear || $this->modalEditar, 422);
        $this->resetValidation();
        $this->mensajeAutollenado = '';
        $this->confirmarImpacto = false;
        $this->revisionDocumento = [];
        $this->validate(['documentoCambio'=>'required|file|mimes:pdf|max:8192'], $this->mensajesCamposExpediente(), ['documentoCambio'=>'respaldo PDF']);
        $lectura = app(RespaldoCursoInstitucional::class)->leer($this->documentoCambio->getRealPath(), 'documentoCambio');
        $datos = ExpedienteParaleloInstitucional::detectar($lectura['texto']);
        $nombre = $this->modalEditar ? $this->formEditar['nom_par'] : $this->form['nom_par'];
        $this->revisionDocumento = ExpedienteParaleloInstitucional::revisar($lectura['texto'], $datos['numero'], $nombre, $datos['tipo'], $datos['fecha'], $datos['gestion']);
        $this->revisionDocumento['reglas'] += [
            'Campos documentales únicos y completos'=>!in_array('', $datos, true),
            'Fecha de emisión no futura'=>$datos['fecha'] !== '' && $datos['fecha'] <= now('America/La_Paz')->toDateString(),
            'Gestión vigente o próxima'=>(int)$datos['gestion'] >= now()->year && (int)$datos['gestion'] <= now()->year + 1,
        ];
        $this->revisionDocumento['coherente'] = !in_array(false, $this->revisionDocumento['reglas'], true);
        $this->registrarRechazoDocumento($lectura, $nombre, $datos);
        if (!$this->revisionDocumento['coherente']) {
            $this->addError('documentoCambio', 'PDF rechazado para autollenado: revisa las observaciones. El intento se guardó en bitácora y tus valores no fueron sobrescritos.');
            return;
        }
        $this->numeroDocumento = $datos['numero'];
        $this->fechaDocumento = $datos['fecha'];
        $this->gestionSolicitud = $datos['gestion'];
        $this->autoridadDocumento = $datos['autoridad'];
        $this->tipoDocumento = $datos['tipo'];
        $this->updatedGestionSolicitud();
        if ($this->modalCrear) $this->faseCrear = 2;
        $this->mensajeAutollenado = 'PDF coherente: se actualizaron número, fecha, gestión, autoridad y tipo. Completa el motivo y comprueba el respaldo con la autoridad antes de guardar.';
    }

    private function registrarRechazoDocumento(array $lectura, string $nombre, array $datos): void
    {
        if (!$this->revisionDocumento['coherente']) {
            $huella = hash('sha256', json_encode([$lectura['sha256'], $nombre, $datos]));
            if ($this->rechazoDocumentoRegistrado !== $huella) {
                // Solo el rechazo de un PDF leído genera este intento; no el simple llenado del formulario.
                $ruta = $this->documentoCambio->store('expedientes/paralelos/rechazados', 'local');
                if (!$ruta) throw new \RuntimeException('No se pudo conservar el respaldo rechazado.');
                try {
                    $this->registrarBitacoraSeguro(accion: $this->modalEditar ? 'INTENTO_EDITAR_PARALELO_PDF_RECHAZADO' : 'INTENTO_CREAR_PARALELO_PDF_RECHAZADO', tabla: 'paralelo',
                        registro: $this->modalEditar ? $this->paraleloSeleccionado : null, nombreRegistro: 'Paralelo '.$nombre,
                        descripcion: 'El PDF leído no cumple las coincidencias documentales. No se creó ni modificó el paralelo.', nivel: 'WARNING', resultado: 'BLOQUEADO',
                        valoresNuevos: ['nombre_solicitado'=>$nombre, 'documento'=>$datos + ['archivo'=>$ruta, 'sha256'=>$lectura['sha256']], 'revision'=>$this->revisionDocumento]);
                    $this->rechazoDocumentoRegistrado = $huella;
                } catch (Throwable $e) {
                    Storage::disk('local')->delete($ruta);
                    throw $e;
                }
            }
        }
    }

    public function continuarCreacion(): void
    {
        $this->autorizar();
        $this->resetValidation();
        if ($this->faseCrear === 1) {
            $this->interpretarParaleloCrear();
            $this->validate($this->rulesCrear());
            if (!$this->puedeGuardarCrear) {
                $this->addError('form.nom_par', $this->bloqueoCrearMensaje ?? 'Corrige el nombre antes de continuar.');
                return;
            }
            $this->faseCrear = 2;
            return;
        }
        if ($this->faseCrear === 2) {
            $this->validate(['causaCambio'=>['required',Rule::in(['demanda','infraestructura','reorganizacion','rectificacion','otro'])], 'motivoCambio'=>'required|string|min:30|max:2000',
                'autoridadDocumento'=>'required|string|min:8|max:200', 'gestionSolicitud'=>'required|integer|min:'.now()->year.'|max:'.(now()->year+1)], $this->mensajesCamposExpediente());
            $this->validarJustificacion();
            $this->revisarDocumento();
            if (!$this->revisionDocumento['coherente']) {
                $this->addError('documentoCambio', 'PDF rechazado en la revisión de contenido. El intento quedó registrado; puedes corregir los datos o adjuntar el respaldo correcto.');
                return;
            }
            $this->faseCrear = 3;
        }
    }

    public function volverFaseCreacion(): void
    {
        $this->autorizar();
        $this->faseCrear = max(1, $this->faseCrear - 1);
        $this->confirmarImpacto = false;
        $this->resetValidation();
    }

    public function getExpedienteCompletoProperty(): bool
    {
        return strlen(trim($this->motivoCambio)) >= 30 && $this->causaCambio !== ''
            && $this->fechaDocumento !== '' && strlen(trim($this->autoridadDocumento)) >= 8
            && strlen(trim($this->referenciaVerificacion)) >= 15 && $this->confirmarImpacto
            && ($this->revisionDocumento['coherente'] ?? false);
    }

    private function validarJustificacion(): void
    {
        if (!ExpedienteParaleloInstitucional::justificacionComprensible($this->motivoCambio)) {
            throw \Illuminate\Validation\ValidationException::withMessages(['motivoCambio'=>'Explica la necesidad en una frase de al menos cinco palabras útiles. Una cadena de letras o palabras repetidas no justifica el cambio.']);
        }
    }

    private function validarExpediente(bool $crear = false): void
    {
        $this->autorizar();
        $this->validate($this->reglasCamposExpediente() + ['confirmarImpacto'=>'accepted'], $this->mensajesCamposExpediente(), ['motivoCambio'=>'justificación', 'causaCambio'=>'motivo', 'documentoCambio'=>'respaldo PDF', 'referenciaVerificacion'=>'verificación con la autoridad']);
        $this->validarJustificacion();
        if ($crear && $this->tipoDocumento !== 'resolucion') {
            throw \Illuminate\Validation\ValidationException::withMessages(['tipoDocumento' => 'La incorporación requiere resolución administrativa. Una solicitud enviada aún no es aprobación.']);
        }
        $estadoDestino = $crear ? $this->form['est_par'] : $this->formEditar['est_par'];
        if ((int)$this->gestionSolicitud > now()->year && $estadoDestino === 'ACTIVO') {
            throw \Illuminate\Validation\ValidationException::withMessages(['gestionSolicitud' => 'Una incorporación para la próxima gestión se registra inactiva. No se habilita anticipadamente ni de forma automática.']);
        }
        // Se vuelve a leer el archivo al guardar; no se confía en el análisis del navegador.
        $this->revisarDocumento();
        if (!($this->revisionDocumento['coherente'] ?? false)) {
            throw \Illuminate\Validation\ValidationException::withMessages(['documentoCambio' => 'El documento no cumple las coincidencias mínimas. Revisa las observaciones; los datos se conservan.']);
        }
        $this->expedienteGuardado = ['causa' => $this->causaCambio, 'motivo' => $this->motivoCambio, 'tipo' => $this->tipoDocumento,
            'numero' => $this->numeroDocumento, 'fecha' => $this->fechaDocumento, 'autoridad' => $this->autoridadDocumento,
            'verificacion' => $this->referenciaVerificacion, 'gestion_solicitada' => $this->gestionSolicitud,
            'alcance' => 'Catálogo institucional. No abre grupos, no traslada estudiantes ni aplica automáticamente a futuras gestiones.',
            'sha256' => hash_file('sha256', $this->documentoCambio->getRealPath()), 'revision' => $this->revisionDocumento];
    }


    protected string $paginationTheme = 'tailwind';

    /*
    |--------------------------------------------------------------------------
    | Tabla y filtros
    |--------------------------------------------------------------------------
    */

    public string $search = '';
    public string $estado = '';
    public string $usoAcademico = '';
    public string $impacto = '';
    public string $filtroGrado = '';
    public string $filtroTurno = '';
    public string $filtroCapacidad = '';
    public string $filtroDocumentacion = '';
    private ?Collection $expedientesCatalogo = null;
    public int $perPage = 10;

    public string $sortField = 'nom_par';
    public string $sortDirection = 'asc';

    /*
    |--------------------------------------------------------------------------
    | Modales
    |--------------------------------------------------------------------------
    */

    public bool $modalCrear = false;
    public bool $modalEditar = false;
    public bool $modalDetalle = false;
    public bool $modalCatalogo = false;
    public bool $modalHistoricos = false;

    /*
    |--------------------------------------------------------------------------
    | Formularios
    |--------------------------------------------------------------------------
    */

    public array $form = [
        'nom_par' => '',
        'est_par' => 'ACTIVO',
    ];

    public array $formEditar = [
        'cod_par' => '',
        'nom_par' => '',
        'est_par' => 'ACTIVO',
    ];

    /*
    |--------------------------------------------------------------------------
    | Análisis inteligente
    |--------------------------------------------------------------------------
    */

    #[Locked]
    public array $analisisCrear = [];
    #[Locked]
    public array $analisisEditar = [];
    public bool $puedeGuardarCrear = false;
    public ?string $bloqueoCrearMensaje = null;

    /*
    |--------------------------------------------------------------------------
    | Detalle
    |--------------------------------------------------------------------------
    */

    #[Locked]
    public ?string $paraleloSeleccionado = null;
    public array $detalleParalelo = [];

    /*
    |--------------------------------------------------------------------------
    | Catálogos
    |--------------------------------------------------------------------------
    */

    public array $estadosDisponibles = [
        'ACTIVO' => 'Activo',
        'INACTIVO' => 'Inactivo',
    ];

    public array $opcionesUsoAcademico = [
        '' => 'Todos',
        'CON_ESTUDIANTES' => 'Con estudiantes',
        'SIN_ESTUDIANTES' => 'Sin estudiantes',
        'CON_PLANIFICACION' => 'Con planificación',
        'SIN_USO' => 'Sin uso académico',
        'HISTORICO' => 'Históricos recuperables',
    ];

    public array $opcionesImpacto = [
        '' => 'Todos',
        'ALTO' => 'Con estudiantes',
        'MEDIO' => 'Planificado',
        'BAJO' => 'Grupos preparados',
        'SIN_USO' => 'Sin uso',
        'HISTORICO' => 'Histórico',
    ];

    /*
    |--------------------------------------------------------------------------
    | Ciclo de vida
    |--------------------------------------------------------------------------
    */

    public function mount(): void
    {
        $this->autorizar();
        $this->reiniciarAnalisisCrear();
        $this->reiniciarAnalisisEditar();
    }

    public function render()
    {
        $this->autorizar();
        $paralelos = $this->obtenerParalelosPaginados();

        return view('livewire.admin.gestion-paralelo', [
            'paralelos' => $paralelos,
            'resumen' => $this->obtenerResumen(),
            'distribucion' => $this->obtenerDistribucionEstudiantil(),
            'recomendaciones' => $this->obtenerRecomendacionesSistema(),
            'catalogoSugerido' => ParaleloInteligente::catalogoSugerido(),
            'historicos' => $this->modalHistoricos ? $this->obtenerHistoricosRecuperables() : collect(),
            'gestionVigente' => $this->datosVigentes()['gestion'],
            'mapaGrupos' => $this->datosVigentes()['mapa'],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Filtros
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

    public function updatingImpacto(): void
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
            'impacto',
            'filtroGrado', 'filtroTurno', 'filtroCapacidad', 'filtroDocumentacion',
        ]);

        $this->perPage = 10;
        $this->sortField = 'nom_par';
        $this->sortDirection = 'asc';

        $this->resetPage();
    }

    public function ordenarPor(string $campo): void
    {
        $permitidos = [
            'nom_par',
            'est_par',
        ];

        if (! in_array($campo, $permitidos, true)) {
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
    | Consultas principales
    |--------------------------------------------------------------------------
    */

    private function obtenerParalelosPaginados(): LengthAwarePaginator
    {
        $filas = Paralelo::query()->get()->map(fn ($p) => $this->enriquecerParalelo($p))->filter(function ($p) {
            $uso = $p->uso_academico;
            $gruposFiltrados = collect($uso['grupos'])->filter(fn ($g) => (!$this->filtroGrado || $g['cod_cur'] === $this->filtroGrado) && (!$this->filtroTurno || $g['cod_tur'] === $this->filtroTurno));
            $cumpleGrupos = (!$this->filtroGrado && !$this->filtroTurno && !$this->filtroCapacidad) || $gruposFiltrados->contains(fn ($g) => match ($this->filtroCapacidad) {
                'excedida' => $g['capacidad'] > 0 && $g['estudiantes'] > $g['capacidad'],
                'plazas' => $g['capacidad'] > $g['estudiantes'], 'vacio' => $g['estudiantes'] === 0, default => true,
            });
            $cumpleDocumento = true;
            if ($this->filtroDocumentacion) {
                $this->expedientesCatalogo ??= Bitacora::where('tab_bit', 'paralelo')->where('res_bit', 'EXITOSO')->orderByDesc('fec_bit')->get(['reg_bit','val_nue_bit'])->unique('reg_bit')->keyBy('reg_bit');
                $documentado = !empty($this->expedientesCatalogo->get($p->cod_par)?->val_nue_bit['expediente']);
                $cumpleDocumento = $this->filtroDocumentacion === 'con' ? $documentado : !$documentado;
            }
            return (trim($this->search) === '' || str_contains(mb_strtolower($p->nom_par), mb_strtolower(trim($this->search))))
                && $cumpleGrupos && $cumpleDocumento
                && (!$this->estado || $p->est_par === $this->estado)
                && (!$this->impacto || $p->impacto_academico['nivel'] === $this->impacto)
                && match ($this->usoAcademico) {
                    'CON_ESTUDIANTES' => $uso['tiene_estudiantes'], 'SIN_ESTUDIANTES' => !$uso['tiene_estudiantes'],
                    'CON_PLANIFICACION' => $uso['tiene_planificacion'], 'SIN_USO' => !$uso['tiene_uso'] && !count($uso['grupos']),
                    'HISTORICO' => $p->est_par === 'INACTIVO', default => true,
                };
        })->sortBy(in_array($this->sortField, ['nom_par', 'est_par']) ? $this->sortField : 'nom_par', SORT_NATURAL, $this->sortDirection === 'desc')->values();
        $pagina = max(1, $this->getPage());
        $limite = in_array($this->perPage, [10, 20, 50]) ? $this->perPage : 10;
        return new \Illuminate\Pagination\LengthAwarePaginator($filas->forPage($pagina, $limite)->values(), $filas->count(), $limite, $pagina);
    }

    private function paralelosQuery(): Builder
    {
        $query = Paralelo::query();

        if (trim($this->search) !== '') {
            $busqueda = trim($this->search);

            $query->where(function (Builder $subQuery) use ($busqueda) {
                $subQuery->where('nom_par', 'like', '%' . $busqueda . '%');
            });
        }

        if ($this->estado !== '') {
            $query->where('est_par', $this->estado);
        }

        $this->aplicarFiltroUsoAcademico($query);

        return $query->orderBy($this->sortField, $this->sortDirection);
    }

    private function aplicarFiltroUsoAcademico(Builder $query): void
    {
        if ($this->usoAcademico === '') {
            return;
        }

        if ($this->usoAcademico === 'CON_ESTUDIANTES') {
            $this->whereExisteEnTablas($query, $this->tablasInscripcionDisponibles());
            return;
        }

        if ($this->usoAcademico === 'SIN_ESTUDIANTES') {
            $this->whereNoExisteEnTablas($query, $this->tablasInscripcionDisponibles());
            return;
        }

        if ($this->usoAcademico === 'CON_PLANIFICACION') {
            $this->whereExisteEnTablas($query, ['plan_asignatura', 'plan_especialidad']);
            return;
        }

        if ($this->usoAcademico === 'SIN_USO') {
            $this->whereNoExisteEnTablas($query, array_merge(
                $this->tablasInscripcionDisponibles(),
                ['plan_asignatura', 'plan_especialidad', 'horario_detalle']
            ));
            return;
        }

        if ($this->usoAcademico === 'HISTORICO') {
            $query->where('est_par', 'INACTIVO');
        }
    }

    private function whereExisteEnTablas(Builder $query, array $tablas): void
    {
        $query->where(function (Builder $subQuery) use ($tablas) {
            foreach ($tablas as $tabla) {
                if ($this->tablaTieneColumna($tabla, 'cod_par')) {
                    $subQuery->orWhereExists(function ($exists) use ($tabla) {
                        $exists
                            ->selectRaw('1')
                            ->from($tabla)
                            ->whereColumn($tabla . '.cod_par', 'paralelo.cod_par');
                    });
                }
            }
        });
    }

    private function whereNoExisteEnTablas(Builder $query, array $tablas): void
    {
        foreach ($tablas as $tabla) {
            if ($this->tablaTieneColumna($tabla, 'cod_par')) {
                $query->whereNotExists(function ($subQuery) use ($tabla) {
                    $subQuery
                        ->selectRaw('1')
                        ->from($tabla)
                        ->whereColumn($tabla . '.cod_par', 'paralelo.cod_par');
                });
            }
        }
    }

    private function enriquecerParalelo(Paralelo $paralelo): Paralelo
    {
        $uso = $this->obtenerUsoAcademico($paralelo->cod_par);
        $impacto = $this->calcularImpacto($paralelo, $uso);
        $bitacora = null;
        $analisis = ParaleloInteligente::interpretar($paralelo->nom_par, []);

        $paralelo->uso_academico = $uso;
        $paralelo->impacto_academico = $impacto;
        $paralelo->ultima_bitacora = $bitacora;
        $paralelo->analisis_inteligente = $analisis;
        $paralelo->disponibilidad = $this->obtenerDisponibilidad($paralelo, $uso);

        return $paralelo;
    }

    /*
    |--------------------------------------------------------------------------
    | Uso académico
    |--------------------------------------------------------------------------
    */

    public function obtenerUsoAcademico(string $codPar): array
    {
        return $this->datosVigentes()['usos'][$codPar] ?? ['estudiantes'=>0, 'inscripciones'=>0, 'planes_asignatura'=>0, 'planes_especialidad'=>0, 'horarios'=>0, 'cursos'=>0, 'grupos'=>[], 'total'=>0, 'tiene_estudiantes'=>false, 'tiene_planificacion'=>false, 'tiene_uso'=>false, 'texto'=>'Sin uso en la gestión vigente'];
    }

    private function contarEstudiantes(string $codPar): int
    {
        $total = 0;

        foreach ($this->tablasInscripcionDisponibles() as $tabla) {
            if (! $this->tablaTieneColumna($tabla, 'cod_par')) {
                continue;
            }

            $columnaEstudiante = $this->primeraColumnaDisponible($tabla, [
                'cod_est',
                'cod_estu',
                'cod_estudiante',
                'cod_per',
            ]);

            if ($columnaEstudiante !== null) {
                $total += DB::table($tabla)
                    ->where('cod_par', $codPar)
                    ->whereNotNull($columnaEstudiante)
                    ->distinct()
                    ->count($columnaEstudiante);
            } else {
                $total += DB::table($tabla)
                    ->where('cod_par', $codPar)
                    ->count();
            }
        }

        return $total;
    }

    private function contarInscripciones(string $codPar): int
    {
        $total = 0;

        foreach ($this->tablasInscripcionDisponibles() as $tabla) {
            if ($this->tablaTieneColumna($tabla, 'cod_par')) {
                $total += DB::table($tabla)
                    ->where('cod_par', $codPar)
                    ->count();
            }
        }

        return $total;
    }

    private function contarCursosVinculados(string $codPar): int
    {
        $codigos = collect();

        foreach (array_merge($this->tablasInscripcionDisponibles(), ['plan_asignatura', 'plan_especialidad']) as $tabla) {
            if ($this->tablaTieneColumna($tabla, 'cod_par') && $this->tablaTieneColumna($tabla, 'cod_cur')) {
                $codigos = $codigos->merge(
                    DB::table($tabla)
                        ->where('cod_par', $codPar)
                        ->whereNotNull('cod_cur')
                        ->pluck('cod_cur')
                );
            }
        }

        return $codigos->unique()->count();
    }

    private function contarHorarios(string $codPar): int
    {
        if (! Schema::hasTable('horario_detalle')) {
            return 0;
        }

        $total = 0;

        if (Schema::hasColumn('horario_detalle', 'cod_par')) {
            $total += DB::table('horario_detalle')
                ->where('cod_par', $codPar)
                ->count();
        }

        if (Schema::hasColumn('horario_detalle', 'cod_pas') && Schema::hasTable('plan_asignatura')) {
            $planes = DB::table('plan_asignatura')->join('grupo_academico as contexto_paralelo', 'contexto_paralelo.cod_gac', '=', 'plan_asignatura.cod_gac')
                ->where('contexto_paralelo.cod_par', $codPar)
                ->pluck('cod_pas')
                ->filter()
                ->values();

            if ($planes->isNotEmpty()) {
                $total += DB::table('horario_detalle')
                    ->whereIn('cod_pas', $planes)
                    ->count();
            }
        }

        if (Schema::hasColumn('horario_detalle', 'cod_pes') && Schema::hasTable('plan_especialidad')) {
            $planes = DB::table('plan_especialidad')->join('grupo_academico as contexto_paralelo', 'contexto_paralelo.cod_gac', '=', 'plan_especialidad.cod_gac')
                ->where('contexto_paralelo.cod_par', $codPar)
                ->pluck('cod_pes')
                ->filter()
                ->values();

            if ($planes->isNotEmpty()) {
                $total += DB::table('horario_detalle')
                    ->whereIn('cod_pes', $planes)
                    ->count();
            }
        }

        return $total;
    }

    private function contarTabla(string $tabla, string $columna, string $valor): int
    {
        if (! $this->tablaTieneColumna($tabla, $columna)) {
            return 0;
        }

        return DB::table($tabla)
            ->where($columna, $valor)
            ->count();
    }

    private function textoUsoAcademico(int $estudiantes, int $planesAsignatura, int $planesEspecialidad, int $horarios): string
    {
        $partes = [];

        if ($estudiantes > 0) {
            $partes[] = $estudiantes . ' estudiante' . ($estudiantes === 1 ? '' : 's');
        }

        if ($planesAsignatura > 0) {
            $partes[] = $planesAsignatura . ' plan' . ($planesAsignatura === 1 ? '' : 'es') . ' de asignatura';
        }

        if ($planesEspecialidad > 0) {
            $partes[] = $planesEspecialidad . ' plan' . ($planesEspecialidad === 1 ? '' : 'es') . ' de especialidad';
        }

        if ($horarios > 0) {
            $partes[] = $horarios . ' horario' . ($horarios === 1 ? '' : 's');
        }

        return count($partes) > 0 ? implode(' / ', $partes) : 'Sin uso académico';
    }

    private function calcularImpacto(Paralelo $paralelo, array $uso): array
    {
        if ($paralelo->est_par === 'INACTIVO') {
            return [
                'nivel' => 'HISTORICO',
                'texto' => 'Histórico',
                'descripcion' => 'Paralelo inactivo con historial recuperable.',
            ];
        }

        if ($uso['tiene_estudiantes']) return ['nivel'=>'ALTO', 'texto'=>'Con estudiantes', 'descripcion'=>'Cambiar su disponibilidad afecta a estudiantes inscritos.'];
        if ($uso['tiene_planificacion']) return ['nivel'=>'MEDIO', 'texto'=>'Planificado', 'descripcion'=>'Conserva planes o horarios vigentes.'];
        if (count($uso['grupos'])) return ['nivel'=>'BAJO', 'texto'=>'Grupos preparados', 'descripcion'=>'Tiene grupos activos; revisa su planificación antes de cambiar la disponibilidad.'];
        return ['nivel'=>'SIN_USO', 'texto'=>'Sin uso', 'descripcion'=>'Sin grupos, estudiantes ni planificación vigente.'];
    }

    private function obtenerDisponibilidad(Paralelo $paralelo, array $uso): array
    {
        if ($paralelo->est_par === 'INACTIVO') {
            return [
                'estado' => 'HISTORICO',
                'texto' => 'Histórico recuperable',
                'descripcion' => 'No aparece en selectores operativos, pero puede reactivarse.',
            ];
        }

        if (($uso['estudiantes'] ?? 0) === 0 && ! ($uso['tiene_planificacion'] ?? false) && !count($uso['grupos'])) {
            return [
                'estado' => 'SIN_USO',
                'texto' => 'Sin uso actual',
                'descripcion' => 'Puede revisarse para desactivación.',
            ];
        }

        return [
            'estado' => 'DISPONIBLE',
            'texto' => 'Disponible',
            'descripcion' => 'Disponible para planificación académica.',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Resumen y distribución
    |--------------------------------------------------------------------------
    */

    private function obtenerResumen(): array
    {
        $total = Paralelo::count();
        $activos = Paralelo::where('est_par', 'ACTIVO')->count();
        $inactivos = Paralelo::where('est_par', 'INACTIVO')->count();

        $estudiantes = 0;
        $conEstudiantes = 0;
        $sinUso = 0;
        $planesVinculados = 0;

        Paralelo::query()->get()->each(function (Paralelo $paralelo) use (&$estudiantes, &$conEstudiantes, &$sinUso, &$planesVinculados) {
            $uso = $this->obtenerUsoAcademico($paralelo->cod_par);

            $estudiantes += (int) $uso['estudiantes'];
            $planesVinculados += (int) $uso['planes_asignatura'] + (int) $uso['planes_especialidad'];

            if ((int) $uso['estudiantes'] > 0) {
                $conEstudiantes++;
            }

            if (! ($uso['tiene_uso'] ?? false) && !count($uso['grupos']) && $paralelo->est_par === 'ACTIVO') {
                $sinUso++;
            }
        });

        return [
            'total' => $total,
            'activos' => $activos,
            'historicos' => $inactivos,
            'estudiantes' => $estudiantes,
            'con_estudiantes' => $conEstudiantes,
            'sin_uso' => $sinUso,
            'planes_vinculados' => $planesVinculados,
        ];
    }

    private function obtenerDistribucionEstudiantil(): array
    {
        return Paralelo::query()
            ->where('est_par', 'ACTIVO')
            ->orderBy('nom_par')
            ->get()
            ->map(function (Paralelo $paralelo) {
                $uso = $this->obtenerUsoAcademico($paralelo->cod_par);

                return [
                    'nombre' => 'Paralelo ' . $paralelo->nom_par,
                    'valor' => (int) $uso['estudiantes'],
                    'texto' => (int) $uso['estudiantes'] . ' estudiante' . ((int) $uso['estudiantes'] === 1 ? '' : 's'),
                ];
            })
            ->values()
            ->toArray();
    }

    private function obtenerRecomendacionesSistema(): array
    {
        $recomendaciones = [];

        Paralelo::query()->orderBy('nom_par')->get()->each(function (Paralelo $paralelo) use (&$recomendaciones) {
            $uso = $this->obtenerUsoAcademico($paralelo->cod_par);

            if ($paralelo->est_par === 'ACTIVO' && ! ($uso['tiene_uso'] ?? false) && !count($uso['grupos'])) {
                $recomendaciones[] = [
                    'tipo' => 'warning',
                    'titulo' => 'Paralelo sin uso actual',
                    'mensaje' => 'El Paralelo ' . $paralelo->nom_par . ' no tiene estudiantes ni planificación vinculada. Puede revisarse para desactivación.',
                ];
            }

            if ($paralelo->est_par === 'INACTIVO') {
                $recomendaciones[] = [
                    'tipo' => 'info',
                    'titulo' => 'Histórico recuperable',
                    'mensaje' => 'El Paralelo ' . $paralelo->nom_par . ' está inactivo y puede reactivarse si la institución vuelve a necesitarlo.',
                ];
            }

            if (($uso['estudiantes'] ?? 0) > 0 && $paralelo->est_par === 'INACTIVO') {
                $recomendaciones[] = [
                    'tipo' => 'danger',
                    'titulo' => 'Inactivo con estudiantes',
                    'mensaje' => 'El Paralelo ' . $paralelo->nom_par . ' figura como inactivo, pero mantiene estudiantes vinculados. Revisa su historial académico.',
                ];
            }
        });

        if (count($recomendaciones) === 0) {
            $recomendaciones[] = [
                'tipo' => 'success',
                'titulo' => 'Organización estable',
                'mensaje' => 'No hay alertas de uso del catálogo. Revisa cada grupo: el total por letra no demuestra equilibrio entre grados o turnos.',
            ];
        }

        $alertas = \App\Support\Academico\OrganizacionParalelosInteligente::revisar($this->datosVigentes()['mapa'], $this->datosVigentes()['gestion']?->ani_gea);
        if ($alertas) $recomendaciones = array_merge($alertas, array_filter($recomendaciones, fn ($r) => $r['tipo'] !== 'success'));
        return array_slice($recomendaciones, 0, 5);
    }

    /*
    |--------------------------------------------------------------------------
    | Crear
    |--------------------------------------------------------------------------
    */

    public function abrirModalCrear(): void
    {
        $this->autorizar();
        $this->reiniciarExpediente();
        $this->resetValidation();
        $this->limpiarFormularioCrear();
        $this->modalCrear = true;
    }

    public function cerrarModalCrear(): void
    {
        $this->instantanea = null;
        $this->modalCrear = false;
        $this->limpiarFormularioCrear();
        $this->resetValidation();
    }

    private function limpiarFormularioCrear(): void
    {
        $this->form = [
            'nom_par' => '',
            'est_par' => 'ACTIVO',
        ];

        $this->puedeGuardarCrear = false;
        $this->bloqueoCrearMensaje = null;

        $this->reiniciarAnalisisCrear();
    }

    public function updatedFormNomPar(): void
    {
        $this->interpretarParaleloCrear();
    }

    public function interpretarParaleloCrear(): void
    {
        $this->analisisCrear = ParaleloInteligente::interpretar(
            $this->form['nom_par'] ?? '',
            $this->obtenerParalelosExistentesParaAnalisis()
        );

        $this->aplicarReglaSecuencialCrear();

        $this->puedeGuardarCrear = (bool) ($this->analisisCrear['puede_crear'] ?? false);
        $this->bloqueoCrearMensaje = $this->puedeGuardarCrear
            ? null
            : ($this->analisisCrear['mensaje'] ?? 'No se puede registrar este paralelo.');

        if ($this->puedeGuardarCrear && ! empty($this->analisisCrear['nombre_sugerido'])) {
            $this->form['nom_par'] = $this->analisisCrear['nombre_sugerido'];
        }
    }

    private function aplicarReglaSecuencialCrear(): void
    {
        $estado = $this->analisisCrear['estado_inteligente'] ?? null;

        if (in_array($estado, [
            ParaleloInteligente::ESTADO_DUPLICADO_ACTIVO,
            ParaleloInteligente::ESTADO_DUPLICADO_INACTIVO,
            ParaleloInteligente::ESTADO_BLOQUEADO,
        ], true)) {
            return;
        }

        $nombreSugerido = trim((string) ($this->analisisCrear['nombre_sugerido'] ?? ''));

        if ($nombreSugerido === '') {
            return;
        }

        if (! $this->esParaleloLetra($nombreSugerido)) {
            return;
        }

        $letraSugerida = mb_strtoupper($nombreSugerido);
        $siguientePermitida = $this->obtenerSiguienteLetraPermitida();

        if ($siguientePermitida === null) {
            return;
        }

        if ($letraSugerida !== $siguientePermitida) {
            $this->bloquearAnalisisCrearPorSecuencia(
                'El registro de paralelos debe seguir un orden institucional. Actualmente corresponde crear el Paralelo '
                    . $siguientePermitida
                    . ', no el Paralelo '
                    . $letraSugerida
                    . '.'
            );
        }
    }

    private function bloquearAnalisisCrearPorSecuencia(string $mensaje): void
    {
        $this->analisisCrear['valido'] = false;
        $this->analisisCrear['puede_crear'] = false;
        $this->analisisCrear['puede_reactivar'] = false;
        $this->analisisCrear['estado_inteligente'] = ParaleloInteligente::ESTADO_BLOQUEADO;
        $this->analisisCrear['mensaje'] = $mensaje;
        $this->analisisCrear['confianza'] = 100;
        $this->analisisCrear['requiere_soporte'] = false;

        $advertencias = $this->analisisCrear['advertencias'] ?? [];

        $advertencias[] = 'No se pueden saltar paralelos. Debe registrarse la siguiente letra disponible.';
        $advertencias[] = 'Ejemplo correcto: si existen A, B, C y D, el siguiente paralelo permitido es E.';

        $this->analisisCrear['advertencias'] = array_values(array_unique($advertencias));
    }

    private function obtenerSiguienteLetraPermitida(): ?string
    {
        $letrasExistentes = Paralelo::query()
            ->pluck('nom_par')
            ->map(fn($nombre) => mb_strtoupper(trim((string) $nombre)))
            ->filter(fn($nombre) => $this->esParaleloLetra($nombre))
            ->unique()
            ->sort()
            ->values()
            ->toArray();

        $abecedario = range('A', 'Z');

        foreach ($abecedario as $letra) {
            if (! in_array($letra, $letrasExistentes, true)) {
                return $letra;
            }
        }

        return null;
    }

    private function esParaleloLetra(string $nombre): bool
    {
        return preg_match('/^[A-Z]$/u', mb_strtoupper(trim($nombre))) === 1;
    }

    public function usarSugerenciaCrear(): void
    {
        if (! ($this->analisisCrear['nombre_sugerido'] ?? null)) {
            $this->dispatch('advertencia-general', mensaje: 'No existe una sugerencia válida para aplicar.');
            return;
        }

        $this->form['nom_par'] = $this->analisisCrear['nombre_sugerido'];
        $this->interpretarParaleloCrear();
    }

    public function guardarParalelo(): void
    {
        $this->validarExpediente(true);
        abort_unless($this->faseCrear === 3, 422);
        $this->normalizarFormularioCrear();
        $this->interpretarParaleloCrear();

        if (! $this->puedeGuardarCrear) {
            $this->addError('form.nom_par', $this->bloqueoCrearMensaje ?? 'No se puede registrar este paralelo.');

            $this->dispatch(
                'advertencia-general',
                mensaje: $this->bloqueoCrearMensaje ?? 'No se puede registrar este paralelo.'
            );

            return;
        }

        if (($this->analisisCrear['estado_inteligente'] ?? '') === ParaleloInteligente::ESTADO_DUPLICADO_INACTIVO) {
            $this->addError('form.nom_par', $this->analisisCrear['mensaje'] ?? 'Ya existe un paralelo inactivo con ese nombre.');

            $this->dispatch(
                'duplicado-inactivo',
                mensaje: $this->analisisCrear['mensaje'] ?? 'Existe un paralelo inactivo con ese nombre. Se recomienda reactivarlo.'
            );

            return;
        }

        if (! ($this->analisisCrear['puede_crear'] ?? false)) {
            $this->addError('form.nom_par', $this->analisisCrear['mensaje'] ?? 'No se puede registrar este paralelo.');

            $this->registrarBitacoraSeguro(
                accion: 'INTENTO_CREAR_PARALELO_BLOQUEADO',
                tabla: 'paralelo',
                registro: $this->analisisCrear['codigo_existente'] ?? null,
                nombreRegistro: $this->form['nom_par'] ?? null,
                descripcion: $this->analisisCrear['mensaje'] ?? 'Intento bloqueado de creación de paralelo.',
                nivel: 'WARNING',
                resultado: 'BLOQUEADO',
                valoresNuevos: [
                    'formulario' => $this->form,
                    'analisis' => $this->analisisCrear,
                ]
            );

            $this->dispatch(
                'advertencia-general',
                mensaje: $this->analisisCrear['mensaje'] ?? 'No se puede registrar este paralelo.'
            );

            return;
        }

        $this->validate($this->rulesCrear(), [], $this->validationAttributesCrear());

        try {
            DB::transaction(function () {
                $gestion = $this->datosVigentes()['gestion'];
                if (!$gestion) throw new \RuntimeException('No hay gestión vigente para registrar el expediente.');
                DB::table('gestion_academica')->where('cod_gea', $gestion->cod_gea)->lockForUpdate()->first();
                if (Paralelo::whereRaw('LOWER(nom_par) = ?', [mb_strtolower($this->form['nom_par'])])->exists()) throw new \RuntimeException('El nombre ya está registrado.');
                $paralelo = Paralelo::create([
                    'nom_par' => $this->analisisCrear['nombre_sugerido'] ?: $this->form['nom_par'],
                    'est_par' => $this->form['est_par'],
                ]);

                $this->registrarBitacoraSeguro(
                    accion: 'CREAR_PARALELO',
                    tabla: 'paralelo',
                    registro: $paralelo->cod_par,
                    nombreRegistro: 'Paralelo ' . $paralelo->nom_par,
                    descripcion: 'Se registró el Paralelo ' . $paralelo->nom_par . ' con validación inteligente.',
                    nivel: ($this->analisisCrear['estado_inteligente'] ?? '') === ParaleloInteligente::ESTADO_REQUIERE_REVISION ? 'WARNING' : 'SUCCESS',
                    resultado: 'EXITOSO',
                    valoresNuevos: [
                        'paralelo' => $paralelo->toArray(),
                        'analisis' => $this->analisisCrear,
                    ]
                );
            });

            $this->cerrarModalCrear();
            $this->resetPage();

            $this->dispatch('paralelo-creado', mensaje: 'Paralelo registrado correctamente.');
        } catch (Throwable $e) {
            report($e);

            $this->registrarBitacoraSeguro(
                accion: 'ERROR_CREAR_PARALELO',
                tabla: 'paralelo',
                nombreRegistro: $this->form['nom_par'] ?? null,
                descripcion: 'No se pudo registrar el paralelo.',
                nivel: 'ERROR',
                resultado: 'FALLIDO',
                valoresNuevos: $this->form,
                error: $e->getMessage()
            );

            $this->dispatch('error-general', mensaje: 'No se pudo registrar el paralelo. Revisa los datos e intenta nuevamente.');
        }
    }

    public function reactivarExistenteDesdeAnalisisCrear(): void
    {
        $codigo = $this->analisisCrear['codigo_existente'] ?? null;

        if (! $codigo) {
            $this->dispatch('advertencia-general', mensaje: 'No se encontró un paralelo inactivo para reactivar.');
            return;
        }

        $this->cerrarModalCrear();
        $this->solicitarReactivar($codigo);
    }

    /*
    |--------------------------------------------------------------------------
    | Editar
    |--------------------------------------------------------------------------
    */

    public function abrirModalEditar(string $codPar): void
    {
        $this->autorizar();
        $this->reiniciarExpediente();
        $this->resetValidation();

        $paralelo = Paralelo::where('cod_par', $codPar)->firstOrFail();

        $this->paraleloSeleccionado = $paralelo->cod_par;

        $this->formEditar = [
            'cod_par' => $paralelo->cod_par,
            'nom_par' => $paralelo->nom_par,
            'est_par' => $paralelo->est_par,
        ];

        $this->interpretarParaleloEditar();

        $this->modalEditar = true;
    }

    public function cerrarModalEditar(): void
    {
        $this->instantanea = null;
        $this->modalEditar = false;
        $this->paraleloSeleccionado = null;

        $this->formEditar = [
            'cod_par' => '',
            'nom_par' => '',
            'est_par' => 'ACTIVO',
        ];

        $this->reiniciarAnalisisEditar();
        $this->resetValidation();
    }

    public function updatedFormEditarNomPar(): void
    {
        $this->interpretarParaleloEditar();
    }

    public function interpretarParaleloEditar(): void
    {
        $existentes = collect($this->obtenerParalelosExistentesParaAnalisis())
            ->reject(fn(array $item) => ($item['cod_par'] ?? null) === ($this->formEditar['cod_par'] ?? null))
            ->values()
            ->toArray();

        $this->analisisEditar = ParaleloInteligente::interpretar(
            $this->formEditar['nom_par'] ?? '',
            $existentes
        );
    }

    public function usarSugerenciaEditar(): void
    {
        if (! ($this->analisisEditar['nombre_sugerido'] ?? null)) {
            $this->dispatch('advertencia-general', mensaje: 'No existe una sugerencia válida para aplicar.');
            return;
        }

        $this->formEditar['nom_par'] = $this->analisisEditar['nombre_sugerido'];
        $this->interpretarParaleloEditar();
    }

    public function guardarEdicionParalelo(): void
    {
        $this->validarExpediente();
        abort_unless($this->paraleloSeleccionado === $this->formEditar['cod_par'], 422);
        $this->normalizarFormularioEditar();
        $this->interpretarParaleloEditar();

        $paralelo = Paralelo::where('cod_par', $this->formEditar['cod_par'])->firstOrFail();
        $valoresAnteriores = $paralelo->toArray();
        $uso = $this->obtenerUsoAcademico($paralelo->cod_par);
        if ($this->formEditar['est_par'] !== $paralelo->est_par && $this->tipoDocumento !== 'resolucion') {
            $this->addError('tipoDocumento', 'Cambiar la habilitación requiere resolución; un acta de corrección no basta.');
            return;
        }
        if ($this->formEditar['est_par'] === 'INACTIVO' && ($uso['tiene_uso'] || count($uso['grupos']) > 0)) {
            $this->addError('formEditar.est_par', 'No se puede desactivar un paralelo con grupos activos, estudiantes o planificación vigente.');
            return;
        }

        if (! ($this->analisisEditar['puede_crear'] ?? false)) {
            $this->addError('formEditar.nom_par', $this->analisisEditar['mensaje'] ?? 'No se puede actualizar este paralelo.');

            $this->dispatch(
                'advertencia-general',
                mensaje: $this->analisisEditar['mensaje'] ?? 'No se puede actualizar este paralelo.'
            );

            return;
        }

        if (DB::table('grupo_academico')->where('cod_par', $paralelo->cod_par)->exists() && ! ParaleloInteligente::esCambioMenor($paralelo->nom_par, $this->analisisEditar['nombre_sugerido'] ?? $this->formEditar['nom_par'])) {
            $this->addError('formEditar.nom_par', 'Este paralelo tiene historial académico. Solo se permiten correcciones menores.');

            $this->dispatch(
                'advertencia-general',
                mensaje: 'Cambio bloqueado: el paralelo tiene estudiantes, planes u horarios vinculados. Solo se permiten correcciones menores.'
            );

            return;
        }

        $this->validate($this->rulesEditar(), [], $this->validationAttributesEditar());

        try {
            DB::transaction(function () use ($paralelo, $valoresAnteriores, $uso) {
                $gestion = $this->datosVigentes()['gestion'];
                if (!$gestion) throw new \RuntimeException('No hay gestión vigente para registrar el expediente.');
                DB::table('gestion_academica')->where('cod_gea', $gestion->cod_gea)->lockForUpdate()->first();
                $actual = Paralelo::whereKey($paralelo->cod_par)->lockForUpdate()->firstOrFail();
                $this->instantanea = null;
                $usoActual = $this->obtenerUsoAcademico($paralelo->cod_par);
                if ($this->formEditar['est_par'] === 'INACTIVO' && ($usoActual['tiene_uso'] || count($usoActual['grupos']))) throw new \RuntimeException('El paralelo conserva grupos o uso vigente.');
                if (DB::table('grupo_academico')->where('cod_par', $actual->cod_par)->exists() && !ParaleloInteligente::esCambioMenor($actual->nom_par, $this->analisisEditar['nombre_sugerido'] ?: $this->formEditar['nom_par'])) throw new \RuntimeException('La identidad histórica del paralelo está protegida.');
                $paralelo->update([
                    'nom_par' => $this->analisisEditar['nombre_sugerido'] ?: $this->formEditar['nom_par'],
                    'est_par' => $this->formEditar['est_par'],
                ]);

                $this->registrarBitacoraSeguro(
                    accion: 'EDITAR_PARALELO',
                    tabla: 'paralelo',
                    registro: $paralelo->cod_par,
                    nombreRegistro: 'Paralelo ' . $paralelo->nom_par,
                    descripcion: 'Se actualizó el Paralelo ' . $paralelo->nom_par . '.',
                    nivel: ($uso['tiene_uso'] ?? false) ? 'WARNING' : 'SUCCESS',
                    resultado: 'EXITOSO',
                    valoresAnteriores: [
                        'paralelo' => $valoresAnteriores,
                        'uso_academico' => $uso,
                    ],
                    valoresNuevos: [
                        'paralelo' => $paralelo->fresh()?->toArray(),
                        'analisis' => $this->analisisEditar,
                    ]
                );
            });

            $this->cerrarModalEditar();

            $this->dispatch('paralelo-actualizado', mensaje: 'Paralelo actualizado correctamente.');
        } catch (Throwable $e) {
            report($e);

            $this->registrarBitacoraSeguro(
                accion: 'ERROR_EDITAR_PARALELO',
                tabla: 'paralelo',
                registro: $this->formEditar['cod_par'] ?? null,
                nombreRegistro: $this->formEditar['nom_par'] ?? null,
                descripcion: 'No se pudo editar el paralelo.',
                nivel: 'ERROR',
                resultado: 'FALLIDO',
                valoresNuevos: $this->formEditar,
                error: $e->getMessage()
            );

            $this->dispatch('error-general', mensaje: 'No se pudo actualizar el paralelo.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Detalle
    |--------------------------------------------------------------------------
    */

    public function abrirModalDetalle(string $codPar): void
    {
        $this->autorizar();
        $paralelo = Paralelo::where('cod_par', $codPar)->firstOrFail();
        $uso = $this->obtenerUsoAcademico($paralelo->cod_par);
        $impacto = $this->calcularImpacto($paralelo, $uso);
        $bitacora = $this->obtenerUltimaBitacora($paralelo->cod_par);
        $distribucionCursos = $this->obtenerDistribucionCursosPorParalelo($paralelo->cod_par);

        $this->paraleloSeleccionado = $paralelo->cod_par;

        $this->detalleParalelo = [
            'nombre' => $paralelo->nom_par,
            'estado' => $paralelo->est_par,
            'disponibilidad' => $this->obtenerDisponibilidad($paralelo, $uso),
            'uso' => $uso,
            'impacto' => $impacto,
            'ultima_bitacora' => $bitacora,
            'distribucion_cursos' => $distribucionCursos,
            'recomendacion' => $this->obtenerRecomendacionInstitucional($paralelo, $uso, $impacto),
        ];

        $this->modalDetalle = true;
    }

    public function cerrarModalDetalle(): void
    {
        $this->modalDetalle = false;
        $this->detalleParalelo = [];
        $this->paraleloSeleccionado = null;
    }

    private function obtenerRecomendacionInstitucional(Paralelo $paralelo, array $uso, array $impacto): string
    {
        if ($paralelo->est_par === 'INACTIVO') {
            return 'Este paralelo está inactivo. No aparece en selectores operativos, pero conserva su historial académico y puede reactivarse si la institución vuelve a necesitarlo.';
        }

        if (($uso['estudiantes'] ?? 0) > 0) {
            return 'Este paralelo tiene estudiantes asignados. No se recomienda desactivarlo mientras mantenga estudiantes o planificación activa.';
        }

        if (($impacto['nivel'] ?? '') === 'SIN_USO') {
            return 'Este paralelo no tiene uso académico actual. Puede revisarse para desactivación lógica si la institución ya no lo necesita.';
        }

        return 'Este paralelo se encuentra disponible para la organización académica institucional.';
    }

    /*
    |--------------------------------------------------------------------------
    | Catálogo e históricos
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

    public function usarDesdeCatalogo(string $nombre): void
    {
        $this->form['nom_par'] = $nombre;
        $this->form['est_par'] = 'ACTIVO';
        $this->interpretarParaleloCrear();

        $this->modalCatalogo = false;
        $this->modalCrear = true;
    }

    public function abrirModalHistoricos(): void
    {
        $this->autorizar();
        $this->modalHistoricos = true;
    }

    public function cerrarModalHistoricos(): void
    {
        $this->modalHistoricos = false;
    }

    private function obtenerHistoricosRecuperables(): array
    {
        return Paralelo::query()
            ->where('est_par', 'INACTIVO')
            ->orderBy('nom_par')
            ->get()
            ->map(function (Paralelo $paralelo) {
                $uso = $this->obtenerUsoAcademico($paralelo->cod_par);
                $bitacora = $this->obtenerUltimaBitacora($paralelo->cod_par, ['DESACTIVAR_PARALELO', 'ELIMINAR_PARALELO_LOGICO']);

                return [
                    'codigo' => $paralelo->cod_par,
                    'nombre' => $paralelo->nom_par,
                    'uso' => $uso,
                    'ultima_bitacora' => $bitacora,
                ];
            })
            ->values()
            ->toArray();
    }

    /*
    |--------------------------------------------------------------------------
    | Desactivar / Reactivar
    |--------------------------------------------------------------------------
    */

    public function solicitarDesactivar(string $codPar): void
    {
        $this->abrirModalEditar($codPar);
        $this->formEditar['est_par'] = 'INACTIVO';
    }

    public function desactivarParalelo(string $codPar): void
    {
        $this->autorizar();
        abort_unless($this->modalEditar && $this->paraleloSeleccionado === $codPar, 422);
        $this->formEditar['est_par'] = 'INACTIVO';
        $this->guardarEdicionParalelo();
    }

    public function solicitarReactivar(string $codPar): void
    {
        $this->abrirModalEditar($codPar);
        $this->formEditar['est_par'] = 'ACTIVO';
    }

    public function reactivarParalelo(string $codPar): void
    {
        $this->autorizar();
        abort_unless($this->modalEditar && $this->paraleloSeleccionado === $codPar, 422);
        $this->formEditar['est_par'] = 'ACTIVO';
        $this->guardarEdicionParalelo();
    }

    /*
    |--------------------------------------------------------------------------
    | Bitácora
    |--------------------------------------------------------------------------
    */

    private function obtenerUltimaBitacora(string $codPar, array $acciones = []): ?array
    {
        if (! Schema::hasTable('bitacora')) {
            return null;
        }

        $query = Bitacora::query()
            ->where('tab_bit', 'paralelo')
            ->where('reg_bit', $codPar);

        if (! empty($acciones)) {
            $query->whereIn('acc_bit', $acciones);
        }

        $bitacora = $query->orderByDesc('fec_bit')->first();

        if (! $bitacora) {
            return null;
        }

        return [
            'accion' => $bitacora->acc_bit,
            'descripcion' => $bitacora->des_bit,
            'fecha' => optional($bitacora->fec_bit)->format('d/m/Y H:i'),
            'usuario' => $bitacora->cod_usu,
            'rol' => $bitacora->rol_bit,
            'nivel' => $bitacora->niv_bit,
            'resultado' => $bitacora->res_bit,
            'expediente' => $bitacora->val_nue_bit['expediente'] ?? null,
        ];
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
            if (! class_exists(BitacoraService::class) || !Schema::hasTable('bitacora')) {
                throw new \RuntimeException('No se puede guardar sin bitácora institucional.');
            }

            if ($resultado === 'EXITOSO' && $this->expedienteGuardado) {
                $ruta = $this->documentoCambio->store('expedientes/paralelos', 'local');
                if (!$ruta) throw new \RuntimeException('No se pudo archivar el respaldo.');
                $valoresNuevos['expediente'] = $this->expedienteGuardado + ['archivo' => $ruta];
                $valoresNuevos['expediente']['comprobado_por'] = auth()->id();
                $descripcion .= ' Motivo: '.trim($this->motivoCambio);
            }
            BitacoraService::registrar(
                accion: $accion,
                tabla: $tabla,
                registro: $registro,
                modulo: 'Gestión de Paralelos',
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
            if (isset($ruta)) Storage::disk('local')->delete($ruta);
            if ($resultado === 'EXITOSO' || str_contains($accion, 'PDF_RECHAZADO')) throw $e;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Validaciones
    |--------------------------------------------------------------------------
    */

    private function rulesCrear(): array
    {
        return [
            'form.nom_par' => [
                'required',
                'string',
                'min:1',
                'max:30',
            ],
            'form.est_par' => [
                'required',
                Rule::in(['ACTIVO', 'INACTIVO']),
            ],
        ];
    }

    private function rulesEditar(): array
    {
        return [
            'formEditar.cod_par' => [
                'required',
                'string',
                'exists:paralelo,cod_par',
            ],
            'formEditar.nom_par' => [
                'required',
                'string',
                'min:1',
                'max:30',
            ],
            'formEditar.est_par' => [
                'required',
                Rule::in(['ACTIVO', 'INACTIVO']),
            ],
        ];
    }

    private function validationAttributesCrear(): array
    {
        return [
            'form.nom_par' => 'nombre del paralelo',
            'form.est_par' => 'estado inicial',
        ];
    }

    private function validationAttributesEditar(): array
    {
        return [
            'formEditar.cod_par' => 'paralelo seleccionado',
            'formEditar.nom_par' => 'nombre del paralelo',
            'formEditar.est_par' => 'estado',
        ];
    }

    protected function messages(): array
    {
        return [
            'required' => 'El campo :attribute es obligatorio.',
            'string' => 'El campo :attribute debe ser texto.',
            'min' => 'El campo :attribute no cumple la longitud mínima.',
            'max' => 'El campo :attribute supera la longitud máxima permitida.',
            'exists' => 'El paralelo seleccionado no existe.',
            'in' => 'El valor seleccionado para :attribute no es válido.',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Normalización
    |--------------------------------------------------------------------------
    */

    private function normalizarFormularioCrear(): void
    {
        $this->form['nom_par'] = ParaleloInteligente::formatearNombre($this->form['nom_par'] ?? '');
        $this->form['est_par'] = $this->form['est_par'] ?: 'ACTIVO';
    }

    private function normalizarFormularioEditar(): void
    {
        $this->formEditar['nom_par'] = ParaleloInteligente::formatearNombre($this->formEditar['nom_par'] ?? '');
        $this->formEditar['est_par'] = $this->formEditar['est_par'] ?: 'ACTIVO';
    }

    private function reiniciarAnalisisCrear(): void
    {
        $this->analisisCrear = ParaleloInteligente::interpretar('');
        $this->puedeGuardarCrear = false;
        $this->bloqueoCrearMensaje = null;
    }

    private function reiniciarAnalisisEditar(): void
    {
        $this->analisisEditar = ParaleloInteligente::interpretar('');
    }

    private function obtenerParalelosExistentesParaAnalisis(): array
    {
        return Paralelo::query()
            ->select('cod_par', 'nom_par', 'est_par')
            ->get()
            ->map(function (Paralelo $paralelo) {
                return [
                    'cod_par' => $paralelo->cod_par,
                    'nom_par' => $paralelo->nom_par,
                    'est_par' => $paralelo->est_par,
                    'bitacora' => null,
                ];
            })
            ->toArray();
    }

    /*
    |--------------------------------------------------------------------------
    | Distribución por cursos
    |--------------------------------------------------------------------------
    */

    private function obtenerDistribucionCursosPorParalelo(string $codPar): array
    {
        return collect($this->obtenerUsoAcademico($codPar)['grupos'])->map(fn ($g) => array_merge($g, ['curso' => $g['curso'].' · '.$g['turno']]))->all();
    }

    private function nombreCurso(string $codCur): string
    {
        if (! Schema::hasTable('curso')) {
            return 'Curso vinculado';
        }

        $columnasNombre = [
            'nom_cur',
            'nombre',
            'des_cur',
        ];

        foreach ($columnasNombre as $columna) {
            if (Schema::hasColumn('curso', $columna)) {
                $nombre = DB::table('curso')
                    ->where('cod_cur', $codCur)
                    ->value($columna);

                return $nombre ?: 'Curso vinculado';
            }
        }

        return 'Curso vinculado';
    }

    /*
    |--------------------------------------------------------------------------
    | Utilidades DB
    |--------------------------------------------------------------------------
    */

    private function tablasInscripcionDisponibles(): array
    {
        return collect([
            'inscripcion_estudiante',
            'inscripciones_estudiante',
            'inscripcion_estudiantes',
            'inscripciones_estudiantes',
            'inscripcion',
            'inscripciones',
            'estudiante_inscripcion',
            'estudiantes_inscripciones',
        ])
            ->filter(fn(string $tabla) => Schema::hasTable($tabla))
            ->values()
            ->toArray();
    }

    private function tablaTieneColumna(string $tabla, string $columna): bool
    {
        return Schema::hasTable($tabla) && Schema::hasColumn($tabla, $columna);
    }

    private function primeraColumnaDisponible(string $tabla, array $columnas): ?string
    {
        foreach ($columnas as $columna) {
            if (Schema::hasColumn($tabla, $columna)) {
                return $columna;
            }
        }

        return null;
    }
}
