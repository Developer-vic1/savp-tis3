<?php

namespace App\Livewire\Admin;

use App\Support\Academico\CursoInteligente;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

use App\Models\Oficial\Academico\Curso;
use App\Services\BitacoraService;
use App\Support\Academico\ConsultaCursosInstitucionales;
use App\Support\Academico\RespaldoCursoInstitucional;
use App\Support\Academico\PlanificacionClaseInteligente;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\WithFileUploads;

class GestionCurso extends Component
{
    use WithPagination, WithFileUploads;

    public array $filtroParalelos = [];
    public array $filtroTurnos = [];
    public string $ordenar = 'grado';
    public bool $modalFormulario = false;
    public string $seccionDetalle = 'ficha';
    #[Locked] public array $eventosHorario = [];
    #[Locked] public array $periodosHorario = [];
    public string $periodoHorario = '';
    #[Locked] public array $bloquesHorario = [];
    #[Locked] public array $opcionesClase = [];
    #[Locked] public array $analisisClase = [];
    #[Locked] public array $asignaciones = [];
    #[Locked] public ?string $cursoCambio = null;
    #[Locked] public string $operacion = 'crear';
    public string $entradaCurso = '';
    #[Locked] public array $interpretacion = [];
    public string $motivo = '';
    public string $causaInstitucional = '';
    public string $autoridadEmisora = '';
    public string $verificacionAutoridad = '';
    public string $numeroResolucion = '';
    public string $normaAdicional = '';
    public string $fechaResolucion = '';
    public $respaldoPdf = null;
    public bool $autenticidadConfirmada = false;
    public bool $confirmarCambio = false;
    public string $modalidadParalelos = 'uno';
    public array $paralelosNuevos = [];
    public string $turnoNuevo = '';
    #[Locked] public array $analisisDocumento = [];
    #[Locked] public int $faseCurso = 1;
    #[Locked] public string $rechazoDocumentoRegistrado = '';
    #[Locked] public string $pdfEscaneado = '';
    #[Locked] public array $datosEscaneados = [];

    protected $paginationTheme = 'tailwind';

    private array $tableExists = [];

    private array $columnsByTable = [];

    private array $columnTypes = [];

    /*
    |--------------------------------------------------------------------------
    | FILTROS PRINCIPALES
    |--------------------------------------------------------------------------
    */

    public string $search = '';
    public string $estado = '';
    public string $nivel = '';
    public string $gestionFiltro = '';
    public string $filtroPlanAsignatura = '';
    public string $filtroPlanEspecialidad = '';
    public string $filtroHorario = '';
    public int $perPage = 10;

    /*
    |--------------------------------------------------------------------------
    | MODALES
    |--------------------------------------------------------------------------
    */

    public bool $modalCrear = false;
    public bool $modalEditar = false;
    public bool $modalDetalle = false;
    public bool $modalPlanificar = false;
    public bool $modalClaseHorario = false;
    #[Locked] public bool $verOtrosDocentesClase = false;

    /*
    |--------------------------------------------------------------------------
    | CURSO SELECCIONADO
    |--------------------------------------------------------------------------
    */

    #[Locked] public ?string $cursoSeleccionado = null;
    #[Locked] public ?array $cursoDetalle = null;

    /*
    |--------------------------------------------------------------------------
    | VISOR DE HORARIO
    |--------------------------------------------------------------------------
    */

    public string $horarioGestion = '';
    public string $horarioParalelo = '';
    public string $horarioTurno = '';
    public string $horarioVista = 'MANANA';

    /*
    |--------------------------------------------------------------------------
    | CONTEXTO DE CELDA DE HORARIO
    |--------------------------------------------------------------------------
    | La vista trabaja con nombres visuales antiguos:
    | dia_hor, num_blo_hor, hor_ini_hor, hor_fin_hor.
    |
    | Internamente se guarda en:
    | horario_detalle.dia_hde
    | horario_detalle.cod_hbl
    |--------------------------------------------------------------------------
    */

    #[Locked] public array $claseContexto = [
        'dia_hor' => '',
        'num_blo_hor' => '',
        'cod_hbl' => '',
        'hor_ini_hor' => '',
        'hor_fin_hor' => '',
    ];

    /*
    |--------------------------------------------------------------------------
    | FORMULARIO CREAR CLASE EN HORARIO
    |--------------------------------------------------------------------------
    | El usuario selecciona:
    | - materia/asignatura
    | - especialidad técnica
    | - docente
    |
    | El sistema crea o reutiliza:
    | - plan_asignatura
    | - plan_especialidad
    |
    | Y guarda la clase real en:
    | - horario_detalle
    |--------------------------------------------------------------------------
    */

    public array $formClaseHorario = [
        'tipo_plan' => 'MATERIA',
        'cod_mat' => '',
        'cod_esp' => '',
        'cod_doc' => '',
        'carga_horaria' => 1,
        'aul_hor' => '',
        'obs_hor' => '',
        'est_hor' => 'ACTIVO',
    ];

    /*
    |--------------------------------------------------------------------------
    | FORMULARIOS CURSO
    |--------------------------------------------------------------------------
    */

    public array $form = [
        'nom_cur' => '',
        'ord_cur' => '',
        'niv_cur' => '',
        'des_cur' => '',
        'est_cur' => 'ACTIVO',
    ];

    public array $formEditar = [
        'cod_cur' => '',
        'nom_cur' => '',
        'ord_cur' => '',
        'niv_cur' => '',
        'des_cur' => '',
        'est_cur' => 'ACTIVO',
    ];

    /*
    |--------------------------------------------------------------------------
    | FORMULARIOS CURSO INTELIGENTE
    |--------------------------------------------------------------------------
    */

    public array $cursoInteligente = [
        'entrada' => '',
        'valido' => false,
        'mensaje' => 'Escribe o selecciona un curso institucional.',
        'confianza' => 0,
        'orden' => null,
        'nombre' => '',
        'nivel' => '',
        'descripcion' => '',
        'categoria' => '',
        'requiere_plan_especialidad' => false,
        'requiere_horario_tecnico' => false,
        'relaciones_esperadas' => [],
        'advertencias' => [],
        'sugerencias' => [],
        'duplicado' => false,
    ];

    /*
    |--------------------------------------------------------------------------
    | MENSAJES
    |--------------------------------------------------------------------------
    */

    protected array $messages = [
        'form.nom_cur.required' => 'El nombre del curso es obligatorio.',
        'form.nom_cur.string' => 'El nombre del curso debe ser texto.',
        'form.nom_cur.max' => 'El nombre del curso no debe superar los 120 caracteres.',
        'form.nom_cur.unique' => 'Ya existe un curso registrado con ese nombre.',
        'form.ord_cur.required' => 'El orden académico es obligatorio.',
        'form.ord_cur.integer' => 'El orden académico debe ser numérico.',
        'form.ord_cur.min' => 'El orden académico debe ser mayor o igual a 1.',
        'form.ord_cur.max' => 'El orden académico no debe ser mayor a 20.',
        'form.niv_cur.required' => 'Debes seleccionar el nivel académico.',
        'form.niv_cur.string' => 'El nivel académico debe ser texto.',
        'form.niv_cur.max' => 'El nivel académico no debe superar los 100 caracteres.',
        'form.des_cur.max' => 'La descripción no debe superar los 255 caracteres.',
        'form.est_cur.required' => 'Debes seleccionar el estado del curso.',
        'form.est_cur.in' => 'El estado seleccionado no es válido.',

        'formEditar.cod_cur.required' => 'No se pudo identificar el curso.',
        'formEditar.cod_cur.exists' => 'El curso seleccionado no existe.',
        'formEditar.nom_cur.required' => 'El nombre del curso es obligatorio.',
        'formEditar.nom_cur.string' => 'El nombre del curso debe ser texto.',
        'formEditar.nom_cur.max' => 'El nombre del curso no debe superar los 120 caracteres.',
        'formEditar.nom_cur.unique' => 'Ya existe otro curso registrado con ese nombre.',
        'formEditar.ord_cur.required' => 'El orden académico es obligatorio.',
        'formEditar.ord_cur.integer' => 'El orden académico debe ser numérico.',
        'formEditar.ord_cur.min' => 'El orden académico debe ser mayor o igual a 1.',
        'formEditar.ord_cur.max' => 'El orden académico no debe ser mayor a 20.',
        'formEditar.niv_cur.required' => 'Debes seleccionar el nivel académico.',
        'formEditar.niv_cur.string' => 'El nivel académico debe ser texto.',
        'formEditar.niv_cur.max' => 'El nivel académico no debe superar los 100 caracteres.',
        'formEditar.des_cur.max' => 'La descripción no debe superar los 255 caracteres.',
        'formEditar.est_cur.required' => 'Debes seleccionar el estado del curso.',
        'formEditar.est_cur.in' => 'El estado seleccionado no es válido.',

        'formClaseHorario.tipo_plan.required' => 'Debes seleccionar el tipo de clase.',
        'formClaseHorario.tipo_plan.in' => 'El tipo de clase seleccionado no es válido.',

        'formClaseHorario.cod_mat.required_if' => 'Debes seleccionar una materia.',
        'formClaseHorario.cod_mat.exists' => 'La materia seleccionada no existe.',

        'formClaseHorario.cod_esp.required_if' => 'Debes seleccionar una especialidad técnica.',
        'formClaseHorario.cod_esp.exists' => 'La especialidad técnica seleccionada no existe.',

        'formClaseHorario.cod_doc.required' => 'Debes seleccionar un docente.',
        'formClaseHorario.cod_doc.exists' => 'El docente seleccionado no existe.',

        'formClaseHorario.carga_horaria.required' => 'Debes ingresar la carga horaria.',
        'formClaseHorario.carga_horaria.integer' => 'La carga horaria debe ser numérica.',
        'formClaseHorario.carga_horaria.min' => 'La carga horaria debe ser mayor o igual a 1.',
        'formClaseHorario.carga_horaria.max' => 'La carga horaria no debe superar 80 horas.',

        'formClaseHorario.aul_hor.max' => 'El aula o laboratorio no debe superar los 100 caracteres.',
        'formClaseHorario.obs_hor.max' => 'La observación no debe superar los 255 caracteres.',
        'formClaseHorario.est_hor.required' => 'Debes seleccionar el estado de la clase.',
        'formClaseHorario.est_hor.in' => 'El estado seleccionado no es válido.',
        'form.ord_cur.unique' => 'Ya existe un curso registrado con ese orden académico.',
    ];

    /*
    |--------------------------------------------------------------------------
    | CICLO DE VIDA
    |--------------------------------------------------------------------------
    */

    public function mount(): void
    {
        $this->autorizar();
        $this->gestionFiltro = DB::table('gestion_academica')->where('est_gea', 'ACTIVO')->value('cod_gea') ?? '';
    }

    /*
    |--------------------------------------------------------------------------
    | REACTIVIDAD DE FILTROS
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

    public function updatingNivel(): void
    {
        $this->resetPage();
    }

    public function updatingGestionFiltro(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroPlanAsignatura(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroPlanEspecialidad(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroHorario(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function updatedCursoInteligenteEntrada(): void
    {
        $this->interpretarCursoInteligente();
    }

    public function interpretarCursoInteligente(): void
    {
        $resultado = CursoInteligente::interpretar($this->cursoInteligente['entrada'] ?? '');

        $this->aplicarResultadoCursoInteligente($resultado);
    }

    public function seleccionarCursoInstitucional(int $orden): void
    {
        $resultado = CursoInteligente::desdeOrden($orden);

        if ($resultado['valido']) {
            $resultado['entrada_original'] = $resultado['nombre'];
        }

        $this->aplicarResultadoCursoInteligente($resultado);
    }

    private function aplicarResultadoCursoInteligente(array $resultado): void
    {
        $duplicado = false;

        if ($resultado['valido'] && ! empty($resultado['orden'])) {
            $duplicado = $this->cursoInstitucionalYaExiste((int) $resultado['orden']);
        }

        $this->cursoInteligente = [
            'entrada' => $resultado['entrada_original'] ?? ($this->cursoInteligente['entrada'] ?? ''),
            'valido' => (bool) ($resultado['valido'] ?? false),
            'mensaje' => $resultado['mensaje'] ?? 'Resultado de interpretación no disponible.',
            'confianza' => (int) ($resultado['confianza'] ?? 0),
            'orden' => $resultado['orden'] ?? null,
            'nombre' => $resultado['nombre'] ?? '',
            'nivel' => $resultado['nivel'] ?? '',
            'descripcion' => $resultado['descripcion'] ?? '',
            'categoria' => $resultado['categoria'] ?? '',
            'requiere_plan_especialidad' => (bool) ($resultado['requiere_plan_especialidad'] ?? false),
            'requiere_horario_tecnico' => (bool) ($resultado['requiere_horario_tecnico'] ?? false),
            'relaciones_esperadas' => $resultado['relaciones_esperadas'] ?? [],
            'advertencias' => $resultado['advertencias'] ?? [],
            'sugerencias' => $resultado['sugerencias'] ?? [],
            'duplicado' => $duplicado,
        ];

        if (! ($resultado['valido'] ?? false)) {
            return;
        }

        $this->form['nom_cur'] = $resultado['nombre'];
        $this->form['ord_cur'] = (int) $resultado['orden'];
        $this->form['niv_cur'] = $resultado['nivel'];

        /*
    |--------------------------------------------------------------------------
    | Descripción sugerida
    |--------------------------------------------------------------------------
    | Si el usuario aún no escribió descripción, o si la descripción actual
    | coincide con una sugerencia anterior, se autocompleta.
    |--------------------------------------------------------------------------
    */

        if (
            empty($this->form['des_cur'])
            || $this->descripcionEsSugerida($this->form['des_cur'])
        ) {
            $this->form['des_cur'] = $resultado['descripcion'];
        }

        $this->resetValidation([
            'form.nom_cur',
            'form.ord_cur',
            'form.niv_cur',
            'form.des_cur',
            'cursoInteligente.entrada',
        ]);
    }

    private function prepararCursoInteligenteAntesDeGuardar(): bool
    {
        /*
    |--------------------------------------------------------------------------
    | Si no escribió entrada inteligente, intentamos interpretar el nombre actual.
    |--------------------------------------------------------------------------
    */

        if (empty($this->cursoInteligente['entrada']) && ! empty($this->form['nom_cur'])) {
            $this->cursoInteligente['entrada'] = $this->form['nom_cur'];
        }

        $resultado = CursoInteligente::interpretar($this->cursoInteligente['entrada'] ?? '');

        $this->aplicarResultadoCursoInteligente($resultado);

        if (! ($resultado['valido'] ?? false)) {
            $this->addError('cursoInteligente.entrada', $resultado['mensaje'] ?? 'No se pudo interpretar el curso.');

            $this->dispatch(
                'error-general',
                mensaje: $resultado['mensaje'] ?? 'No se pudo interpretar el curso.'
            );

            return false;
        }

        if (! empty($resultado['orden']) && $this->cursoInstitucionalYaExiste((int) $resultado['orden'])) {
            $this->addError(
                'cursoInteligente.entrada',
                'Ya existe un curso registrado con ese orden académico.'
            );

            $this->dispatch(
                'error-general',
                mensaje: 'Ya existe un curso registrado con ese orden académico. Revisa el catálogo de cursos.'
            );

            return false;
        }

        return true;
    }

    private function descripcionEsSugerida(?string $descripcion): bool
    {
        $descripcion = trim((string) $descripcion);

        if ($descripcion === '') {
            return true;
        }

        foreach (CursoInteligente::catalogo() as $curso) {
            if ($descripcion === ($curso['descripcion'] ?? '')) {
                return true;
            }
        }

        return false;
    }

    private function cursoInstitucionalYaExiste(int $orden, ?string $ignorarCodCur = null): bool
    {
        if (! $this->tablaExiste('curso')) {
            return false;
        }

        $query = DB::table('curso');

        $query->where(function ($q) use ($orden) {
            if ($this->columnaExiste('curso', 'ord_cur')) {
                $q->orWhere('ord_cur', $orden);
            }

            if ($this->columnaExiste('curso', 'nom_cur')) {
                $q->orWhere('nom_cur', CursoInteligente::cursoOficial($orden)['nombre']);
            }
        });

        if ($ignorarCodCur && $this->columnaExiste('curso', 'cod_cur')) {
            $query->where('cod_cur', '!=', $ignorarCodCur);
        }

        return $query->exists();
    }

    private function resetCursoInteligente(): void
    {
        $this->cursoInteligente = [
            'entrada' => '',
            'valido' => false,
            'mensaje' => 'Escribe o selecciona un curso institucional.',
            'confianza' => 0,
            'orden' => null,
            'nombre' => '',
            'nivel' => '',
            'descripcion' => '',
            'categoria' => '',
            'requiere_plan_especialidad' => false,
            'requiere_horario_tecnico' => false,
            'relaciones_esperadas' => [],
            'advertencias' => [],
            'sugerencias' => [],
            'duplicado' => false,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | REACTIVIDAD DEL CURSO INTELIGENTE
    |--------------------------------------------------------------------------
    */
    public function getCatalogoCursosInstitucionalesProperty(): array
    {
        return CursoInteligente::catalogo();
    }

    /*
    |--------------------------------------------------------------------------
    | REACTIVIDAD DEL VISOR DE HORARIOS
    |--------------------------------------------------------------------------
    */

    public function updatedHorarioGestion(): void
    {
        $this->refrescarDetalleCurso();
    }

    public function updatedHorarioParalelo(): void
    {
        $this->autorizar();
        $this->prepararPeriodosHorario();
        $this->cargarSeccion();
    }

    public function updatedPeriodoHorario(): void { $this->cargarSeccion(); }

    private function prepararPeriodosHorario(): void
    {
        $this->periodosHorario = $this->cursoSeleccionado && $this->gestionFiltro ? app(ConsultaCursosInstitucionales::class)->periodosHorario($this->cursoSeleccionado,$this->gestionFiltro,$this->horarioParalelo) : [];
        $this->periodoHorario = collect($this->periodosHorario)->firstWhere('aplicado',true)['valor'] ?? $this->periodosHorario[0]['valor'] ?? '';
    }

    public function updatedHorarioTurno(): void
    {
        $this->sincronizarVistaConTurno();
        $this->refrescarDetalleCurso();
    }

    public function updatedHorarioVista(): void
    {
        $this->horarioVista = strtoupper($this->horarioVista);

        if (in_array($this->horarioVista, ['MANANA', 'TARDE'], true)) {
            $codTurno = $this->codTurnoPorVista($this->horarioVista);

            if ($codTurno) {
                $this->horarioTurno = $codTurno;
            }
        }

        $this->refrescarDetalleCurso();
    }

    public function updatedFormClaseHorarioTipoPlan(): void
    {
        if (($this->formClaseHorario['tipo_plan'] ?? 'MATERIA') === 'MATERIA') {
            $this->formClaseHorario['cod_esp'] = '';
        }

        if (($this->formClaseHorario['tipo_plan'] ?? 'MATERIA') === 'ESPECIALIDAD') {
            $this->formClaseHorario['cod_mat'] = '';
        }

        $this->resetValidation([
            'formClaseHorario.cod_mat',
            'formClaseHorario.cod_esp',
        ]);
    }

    private function sincronizarVistaConTurno(): void
    {
        $nombreTurno = mb_strtolower($this->nombreTurno($this->horarioTurno));

        if (str_contains($nombreTurno, 'tarde') || str_contains($nombreTurno, 'vespertino')) {
            $this->horarioVista = 'TARDE';
            return;
        }

        if (str_contains($nombreTurno, 'mañana') || str_contains($nombreTurno, 'manana') || str_contains($nombreTurno, 'matutino')) {
            $this->horarioVista = 'MANANA';
        }
    }

    /*
    |--------------------------------------------------------------------------
    | VALIDACIONES
    |--------------------------------------------------------------------------
    */

    private function rulesCrear(): array
    {
        $rules = [
            'form.nom_cur' => ['required', 'string', 'max:120'],
            'form.ord_cur' => ['required', 'integer', 'min:1', 'max:20'],
            'form.niv_cur' => ['required', 'string', 'max:100'],
            'form.des_cur' => ['nullable', 'string', 'max:255'],
            'form.est_cur' => ['required', Rule::in(['ACTIVO', 'INACTIVO'])],
        ];

        if ($this->tablaExiste('curso') && $this->columnaExiste('curso', 'nom_cur')) {
            $rules['form.nom_cur'][] = Rule::unique('curso', 'nom_cur');
        }
        if ($this->tablaExiste('curso') && $this->columnaExiste('curso', 'ord_cur')) {
            $rules['form.ord_cur'][] = Rule::unique('curso', 'ord_cur');
        }

        return $rules;
    }

    private function rulesEditar(): array
    {
        $rules = [
            'formEditar.cod_cur' => ['required'],
            'formEditar.nom_cur' => ['required', 'string', 'max:120'],
            'formEditar.ord_cur' => ['required', 'integer', 'min:1', 'max:20'],
            'formEditar.niv_cur' => ['required', 'string', 'max:100'],
            'formEditar.des_cur' => ['nullable', 'string', 'max:255'],
            'formEditar.est_cur' => ['required', Rule::in(['ACTIVO', 'INACTIVO'])],
        ];

        if ($this->tablaExiste('curso') && $this->columnaExiste('curso', 'cod_cur')) {
            $rules['formEditar.cod_cur'][] = 'exists:curso,cod_cur';
        }

        if ($this->tablaExiste('curso') && $this->columnaExiste('curso', 'nom_cur')) {
            $rules['formEditar.nom_cur'][] = Rule::unique('curso', 'nom_cur')
                ->ignore($this->formEditar['cod_cur'], 'cod_cur');
        }

        return $rules;
    }

    private function rulesClaseHorario(): array
    {
        $tablaMateria = $this->tablaMateria();
        $codMateria = $this->columnaCodigoMateria();

        $tablaEspecialidad = $this->tablaEspecialidad();
        $codEspecialidad = $this->columnaCodigoEspecialidad();

        $rules = [
            'formClaseHorario.tipo_plan' => ['required', Rule::in(['MATERIA', 'ESPECIALIDAD'])],

            'formClaseHorario.cod_mat' => [
                'nullable',
                'required_if:formClaseHorario.tipo_plan,MATERIA',
            ],

            'formClaseHorario.cod_esp' => [
                'nullable',
                'required_if:formClaseHorario.tipo_plan,ESPECIALIDAD',
            ],

            'formClaseHorario.cod_doc' => ['required'],
            'formClaseHorario.carga_horaria' => ['required', 'integer', 'min:1', 'max:80'],
            'formClaseHorario.aul_hor' => ['nullable', 'string', 'max:100'],
            'formClaseHorario.obs_hor' => ['nullable', 'string', 'max:255'],
            'formClaseHorario.est_hor' => ['required', Rule::in(['ACTIVO', 'INACTIVO'])],
        ];

        if ($this->tablaExiste($tablaMateria) && $this->columnaExiste($tablaMateria, $codMateria)) {
            $rules['formClaseHorario.cod_mat'][] = "exists:{$tablaMateria},{$codMateria}";
        }

        if ($this->tablaExiste($tablaEspecialidad) && $this->columnaExiste($tablaEspecialidad, $codEspecialidad)) {
            $rules['formClaseHorario.cod_esp'][] = "exists:{$tablaEspecialidad},{$codEspecialidad}";
        }

        if ($this->tablaExiste('docente') && $this->columnaExiste('docente', 'cod_doc')) {
            $rules['formClaseHorario.cod_doc'][] = 'exists:docente,cod_doc';
        }

        return $rules;
    }

    /*
    |--------------------------------------------------------------------------
    | MODALES DE CURSO
    |--------------------------------------------------------------------------
    */

    public function abrirModalCrear(): void
    {
        $this->resetValidation();
        $this->resetFormularioCrear();

        /*
    |--------------------------------------------------------------------------
    | Carga inicial de sugerencias
    |--------------------------------------------------------------------------
    | El modal iniciará vacío, pero con catálogo disponible para la vista previa.
    |--------------------------------------------------------------------------
    */

        $this->cursoInteligente['sugerencias'] = array_values(array_map(
            fn(array $curso) => [
                'orden' => $curso['orden'],
                'nombre' => $curso['nombre'],
                'nivel' => $curso['nivel'],
            ],
            CursoInteligente::catalogo()
        ));

        $this->modalCrear = true;
    }

    public function cerrarModalCrear(): void
    {
        $this->modalCrear = false;
        $this->resetValidation();
        $this->resetFormularioCrear();
    }

    public function abrirModalEditar(string $codCur): void
    {
        if (! $this->tablaExiste('curso')) {
            $this->dispatch('error-general', mensaje: 'La tabla de cursos no existe.');
            return;
        }

        $curso = DB::table('curso')
            ->where('cod_cur', $codCur)
            ->first();

        if (! $curso) {
            $this->dispatch('error-general', mensaje: 'No se encontró el curso seleccionado.');
            return;
        }

        $this->resetValidation();

        $this->formEditar = [
            'cod_cur' => $curso->cod_cur ?? '',
            'nom_cur' => $curso->nom_cur ?? '',
            'ord_cur' => $curso->ord_cur ?? $this->inferirOrdenCurso($curso->nom_cur ?? ''),
            'niv_cur' => $curso->niv_cur ?? $this->inferirNivelCurso($curso->nom_cur ?? ''),
            'des_cur' => $curso->des_cur ?? '',
            'est_cur' => $this->normalizarEstadoParaFormulario($curso->est_cur ?? 'ACTIVO'),
        ];

        $this->modalEditar = true;
    }

    public function cerrarModalEditar(): void
    {
        $this->modalEditar = false;
        $this->resetValidation();
        $this->resetFormularioEditar();
    }

    public function abrirModalDetalle(string $codCur): void
    {
        $curso = $this->obtenerCursoDetalle($codCur);

        if (! $curso) {
            $this->dispatch('error-general', mensaje: 'No se encontró el curso seleccionado.');
            return;
        }

        $this->cursoSeleccionado = $codCur;

        $gestionActiva = $this->gestionActiva();

        $this->horarioGestion = $this->gestionFiltro ?: ($gestionActiva->cod_gea ?? '');
        $this->horarioParalelo = $this->primerParaleloDisponible();

        $turnoManana = $this->codTurnoPorVista('MANANA');
        $this->horarioTurno = $turnoManana ?: $this->primerTurnoDisponible();

        $this->horarioVista = 'MANANA';

        $this->cursoDetalle = $this->obtenerCursoDetalle($codCur);
        $this->modalDetalle = true;
    }

    public function cerrarModalDetalle(): void
    {
        $this->modalDetalle = false;
        $this->cursoDetalle = null;
        $this->cursoSeleccionado = null;

        $this->horarioGestion = '';
        $this->horarioParalelo = '';
        $this->horarioTurno = '';
        $this->horarioVista = 'MANANA';

        $this->cerrarModalClaseHorario();
    }

    private function refrescarDetalleCurso(): void
    {
        if (! $this->cursoSeleccionado) {
            return;
        }

        $this->cursoDetalle = $this->obtenerCursoDetalle($this->cursoSeleccionado);
        $this->dispatch('actualizar-graficos-cursos', data: $this->datosGraficos);
    }

    /*
    |--------------------------------------------------------------------------
    | MODAL CREAR CLASE DESDE CELDA VACÍA
    |--------------------------------------------------------------------------
    */

    public function abrirModalClaseHorario(string $dia, int $bloque, string $horaInicio, string $horaFin): void
    {
        $this->autorizar();
        $codBloque=DB::table('horario_bloque as b')->join('horario as h','h.cod_pho','=','b.cod_pho')->where('h.cod_hor',$this->periodoHorario)->where('b.num_hbl',$bloque)->value('b.cod_hbl');
        abort_unless($codBloque,422);
        $this->crearClaseDesdeBloque(strtoupper($dia),$codBloque);
    }

    public function cerrarModalClaseHorario(): void
    {
        $this->modalClaseHorario=false;
        $this->claseContexto=[];
        $this->analisisClase=[];
        $this->resetValidation();
    }

    public function guardarClaseHorario(): void
    {
        $this->autorizar();
        abort_unless(!$this->modalFormulario&&$this->modalDetalle&&$this->seccionDetalle==='horario'&&$this->modalClaseHorario&&!empty($this->claseContexto['cod_hor']),422);
        $this->validarAmbitoClase();
        try {
            app(PlanificacionClaseInteligente::class)->guardar($this->claseContexto['cod_hor'],$this->claseContexto['cod_hbl'],$this->claseContexto['dia_hor'],$this->formClaseHorario);
            $this->cerrarModalClaseHorario();
            unset($this->resumen);
            $this->cargarSeccion();
            $this->dispatch('clase-horario-creada');
            $this->dispatch('success-general',mensaje:'Clase organizada. Su materia, docente y bloque quedaron registrados; la historia académica se conserva.');
        } catch(ValidationException $e){throw $e;}
        catch(\Throwable $e){report($e);$this->addError('clase','No pudimos registrar la clase. Conservamos el horario; vuelve a revisar la disponibilidad.');}
    }

    public function quitarClaseHorario(string $codigo): void
    {
        $this->autorizar();
        $this->dispatch('advertencia-general',mensaje:'Las clases registradas conservan su historia. Su retiro requiere una revisión con motivo y no se realiza desde Agregar clase.');
    }

    /*
    |--------------------------------------------------------------------------
    | RESOLVERS PLAN ASIGNATURA / PLAN ESPECIALIDAD
    |--------------------------------------------------------------------------
    */

    private function resolverPlanAsignaturaDesdeMateria(): ?string
    {
        if (! $this->tablaExiste('plan_asignatura')) {
            return null;
        }

        $pk = 'cod_pas';
        $colMateriaPlan = $this->columnaMateriaEnPlanAsignatura();

        if (! $this->columnaExiste('plan_asignatura', $pk)) {
            return null;
        }

        if (! $colMateriaPlan || ! $this->columnaExiste('plan_asignatura', $colMateriaPlan)) {
            return null;
        }

        $query = DB::table('plan_asignatura')
            ->where('cod_cur', $this->cursoSeleccionado)
            ->where($colMateriaPlan, $this->formClaseHorario['cod_mat']);

        if ($this->columnaExiste('plan_asignatura', 'cod_gea')) {
            $query->where('cod_gea', $this->horarioGestion);
        }

        if ($this->columnaExiste('plan_asignatura', 'cod_par')) {
            $query->where('cod_par', $this->horarioParalelo);
        }

        if ($this->columnaExiste('plan_asignatura', 'cod_tur')) {
            $query->where('cod_tur', $this->horarioTurno);
        }

        if ($this->columnaExiste('plan_asignatura', 'cod_doc')) {
            $query->where('cod_doc', $this->formClaseHorario['cod_doc']);
        }

        $existente = $query->first();

        if ($existente && property_exists($existente, $pk) && ! empty($existente->{$pk})) {
            return $existente->{$pk};
        }

        $codPas = $this->generarCodigo('plan_asignatura', $pk, 'PAS');

        $data = [
            $pk => $codPas,
        ];

        if ($this->columnaExiste('plan_asignatura', 'cod_gea')) {
            $data['cod_gea'] = $this->horarioGestion;
        }

        if ($this->columnaExiste('plan_asignatura', 'cod_cur')) {
            $data['cod_cur'] = $this->cursoSeleccionado;
        }

        if ($this->columnaExiste('plan_asignatura', 'cod_par')) {
            $data['cod_par'] = $this->horarioParalelo;
        }

        if ($this->columnaExiste('plan_asignatura', 'cod_tur')) {
            $data['cod_tur'] = $this->horarioTurno;
        }

        if ($this->columnaExiste('plan_asignatura', $colMateriaPlan)) {
            $data[$colMateriaPlan] = $this->formClaseHorario['cod_mat'];
        }

        if ($this->columnaExiste('plan_asignatura', 'cod_doc')) {
            $data['cod_doc'] = $this->formClaseHorario['cod_doc'];
        }

        if ($this->columnaExiste('plan_asignatura', 'hor_pas')) {
            $data['hor_pas'] = (int) $this->formClaseHorario['carga_horaria'];
        }

        if ($this->columnaExiste('plan_asignatura', 'est_pas')) {
            $data['est_pas'] = 'ACTIVO';
        }

        if ($this->columnaExiste('plan_asignatura', 'created_at')) {
            $data['created_at'] = now();
        }

        if ($this->columnaExiste('plan_asignatura', 'updated_at')) {
            $data['updated_at'] = now();
        }

        DB::table('plan_asignatura')->insert($data);

        $this->registrarBitacoraSeguro(
            accion: 'CREAR_PLAN_ASIGNATURA_DESDE_HORARIO',
            tabla: 'plan_asignatura',
            registro: $codPas,
            nombreRegistro: $this->nombreCurso($this->cursoSeleccionado),
            descripcion: 'Se creó un plan de asignatura automáticamente desde el módulo de horarios.',
            nivel: 'INFO',
            resultado: 'EXITOSO',
            valoresNuevos: $data
        );

        return $codPas;
    }

    private function resolverPlanEspecialidadDesdeEspecialidad(): ?string
    {
        if (! $this->tablaExiste('plan_especialidad')) {
            return null;
        }

        $pk = $this->columnaCodigoPlanEspecialidad();
        $colEspecialidadPlan = $this->columnaEspecialidadEnPlanEspecialidad();

        if (! $this->columnaExiste('plan_especialidad', $pk)) {
            return null;
        }

        if (! $colEspecialidadPlan || ! $this->columnaExiste('plan_especialidad', $colEspecialidadPlan)) {
            return null;
        }

        $query = DB::table('plan_especialidad')
            ->where('cod_cur', $this->cursoSeleccionado)
            ->where($colEspecialidadPlan, $this->formClaseHorario['cod_esp']);

        if ($this->columnaExiste('plan_especialidad', 'cod_gea')) {
            $query->where('cod_gea', $this->horarioGestion);
        }

        if ($this->columnaExiste('plan_especialidad', 'cod_par')) {
            $query->where('cod_par', $this->horarioParalelo);
        }

        if ($this->columnaExiste('plan_especialidad', 'cod_tur')) {
            $query->where('cod_tur', $this->horarioTurno);
        }

        if ($this->columnaExiste('plan_especialidad', 'cod_doc')) {
            $query->where('cod_doc', $this->formClaseHorario['cod_doc']);
        }

        $existente = $query->first();

        if ($existente && property_exists($existente, $pk) && ! empty($existente->{$pk})) {
            return $existente->{$pk};
        }

        $prefijo = $pk === 'cod_ple' ? 'PLE' : 'PES';
        $codPes = $this->generarCodigo('plan_especialidad', $pk, $prefijo);

        $data = [
            $pk => $codPes,
        ];

        if ($this->columnaExiste('plan_especialidad', 'cod_gea')) {
            $data['cod_gea'] = $this->horarioGestion;
        }

        if ($this->columnaExiste('plan_especialidad', 'cod_cur')) {
            $data['cod_cur'] = $this->cursoSeleccionado;
        }

        if ($this->columnaExiste('plan_especialidad', 'cod_par')) {
            $data['cod_par'] = $this->horarioParalelo;
        }

        if ($this->columnaExiste('plan_especialidad', 'cod_tur')) {
            $data['cod_tur'] = $this->horarioTurno;
        }

        if ($this->columnaExiste('plan_especialidad', $colEspecialidadPlan)) {
            $data[$colEspecialidadPlan] = $this->formClaseHorario['cod_esp'];
        }

        if ($this->columnaExiste('plan_especialidad', 'cod_doc')) {
            $data['cod_doc'] = $this->formClaseHorario['cod_doc'];
        }

        if ($this->columnaExiste('plan_especialidad', 'hor_pes')) {
            $data['hor_pes'] = (int) $this->formClaseHorario['carga_horaria'];
        }

        if ($this->columnaExiste('plan_especialidad', 'hor_ple')) {
            $data['hor_ple'] = (int) $this->formClaseHorario['carga_horaria'];
        }

        if ($this->columnaExiste('plan_especialidad', 'est_pes')) {
            $data['est_pes'] = 'ACTIVO';
        }

        if ($this->columnaExiste('plan_especialidad', 'est_ple')) {
            $data['est_ple'] = 'ACTIVO';
        }

        if ($this->columnaExiste('plan_especialidad', 'created_at')) {
            $data['created_at'] = now();
        }

        if ($this->columnaExiste('plan_especialidad', 'updated_at')) {
            $data['updated_at'] = now();
        }

        DB::table('plan_especialidad')->insert($data);

        $this->registrarBitacoraSeguro(
            accion: 'CREAR_PLAN_ESPECIALIDAD_DESDE_HORARIO',
            tabla: 'plan_especialidad',
            registro: $codPes,
            nombreRegistro: $this->nombreCurso($this->cursoSeleccionado),
            descripcion: 'Se creó un plan de especialidad automáticamente desde el módulo de horarios.',
            nivel: 'INFO',
            resultado: 'EXITOSO',
            valoresNuevos: $data
        );

        return $codPes;
    }

    /*
    |--------------------------------------------------------------------------
    | CRUD CURSO
    |--------------------------------------------------------------------------
    */

    public function guardarCurso(): void
    {
        $this->autorizar();
        abort_unless($this->operacion === 'crear', 422);
        $this->guardarCambioCurso();
    }

    public function actualizarCurso(): void
    {
        $this->autorizar();
        abort_unless($this->operacion === 'editar', 422);
        $this->guardarCambioCurso();
    }

    public function desactivarCurso(string $codCur): void
    {
        $this->cambiarEstadoCurso($codCur, 'INACTIVO');
    }

    public function reactivarCurso(string $codCur): void
    {
        $this->cambiarEstadoCurso($codCur, 'ACTIVO');
    }

    private function cambiarEstadoCurso(string $codCur, string $estadoNuevo): void
    {
        $this->abrirFormularioCurso($estadoNuevo === 'ACTIVO' ? 'reactivar' : 'desactivar', $codCur);
    }

    /*
    |--------------------------------------------------------------------------
    | COMPATIBILIDAD CON MÉTODOS ANTIGUOS
    |--------------------------------------------------------------------------
    */

    public function abrirModalPlanificar(string $codCur): void
    {
        $this->abrirModalDetalle($codCur);

        $this->dispatch(
            'success-general',
            mensaje: 'La planificación ahora se consulta desde el detalle del curso y se organiza desde Horarios.'
        );
    }

    public function cerrarModalPlanificar(): void
    {
        $this->modalPlanificar = false;
    }

    public function irAHorarios(?string $codCur = null): void
    {
        $this->consultarCurso($codCur ?: $this->cursoSeleccionado, 'horario');
    }

    public function irAPlanAsignatura(?string $codCur = null): void
    {
        $this->dispatch('abrir-modulo-plan-asignatura', cod_cur: $codCur ?: $this->cursoSeleccionado);
    }

    public function irAPlanEspecialidad(?string $codCur = null): void
    {
        $this->dispatch('abrir-modulo-plan-especialidad', cod_cur: $codCur ?: $this->cursoSeleccionado);
    }

    /*
    |--------------------------------------------------------------------------
    | RESET FORMULARIOS
    |--------------------------------------------------------------------------
    */

    private function resetFormularioCrear(): void
    {
        $this->resetCursoInteligente();

        $this->form = [
            'nom_cur' => '',
            'ord_cur' => '',
            'niv_cur' => '',
            'des_cur' => '',
            'est_cur' => 'ACTIVO',
        ];
    }

    private function resetFormularioEditar(): void
    {
        $this->formEditar = [
            'cod_cur' => '',
            'nom_cur' => '',
            'ord_cur' => '',
            'niv_cur' => '',
            'des_cur' => '',
            'est_cur' => 'ACTIVO',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | FILTROS
    |--------------------------------------------------------------------------
    */

    public function limpiarFiltros(): void
    {
        $this->reset('search','estado','nivel','filtroHorario','filtroPlanAsignatura','filtroPlanEspecialidad','filtroParalelos','filtroTurnos','ordenar');
        $this->resetPage();
    }

    public function limpiarFiltrosHorario(): void
    {
        $gestionActiva = $this->gestionActiva();

        $this->horarioGestion = $gestionActiva->cod_gea ?? '';
        $this->horarioParalelo = $this->primerParaleloDisponible();

        $turnoManana = $this->codTurnoPorVista('MANANA');
        $this->horarioTurno = $turnoManana ?: $this->primerTurnoDisponible();

        $this->horarioVista = 'MANANA';

        $this->refrescarDetalleCurso();
    }

    /*
    |--------------------------------------------------------------------------
    | QUERY PRINCIPAL
    |--------------------------------------------------------------------------
    */

    private function cursosQuery()
    {
        if (! $this->tablaExiste('curso')) {
            return null;
        }

        $query = DB::table('curso');

        if ($this->search !== '') {
            $search = trim($this->search);

            $query->where(function ($q) use ($search) {
                if ($this->columnaExiste('curso', 'nom_cur')) {
                    $q->orWhere('nom_cur', 'ILIKE', "%{$search}%");
                }

                if ($this->columnaExiste('curso', 'niv_cur')) {
                    $q->orWhere('niv_cur', 'ILIKE', "%{$search}%");
                }

                if ($this->columnaExiste('curso', 'des_cur')) {
                    $q->orWhere('des_cur', 'ILIKE', "%{$search}%");
                }
            });
        }

        if ($this->estado !== '' && $this->columnaExiste('curso', 'est_cur')) {
            $this->aplicarFiltroEstado($query, 'curso', 'est_cur', $this->estado);
        }

        if ($this->nivel !== '' && $this->columnaExiste('curso', 'niv_cur')) {
            $query->where('niv_cur', $this->nivel);
        }

        $codGestion = $this->gestionFiltro ?: optional($this->gestionActiva())->cod_gea;

        if ($this->filtroPlanAsignatura !== '') {
            $this->aplicarFiltroExistenciaRelacion(
                query: $query,
                tablaRelacion: 'plan_asignatura',
                columnaRelacionCurso: 'cod_cur',
                codGestion: $codGestion,
                filtro: $this->filtroPlanAsignatura
            );
        }

        if ($this->filtroPlanEspecialidad !== '') {
            $this->aplicarFiltroExistenciaRelacion(
                query: $query,
                tablaRelacion: 'plan_especialidad',
                columnaRelacionCurso: 'cod_cur',
                codGestion: $codGestion,
                filtro: $this->filtroPlanEspecialidad
            );
        }

        if ($this->filtroHorario !== '') {
            $this->aplicarFiltroExistenciaRelacion(
                query: $query,
                tablaRelacion: 'horario',
                columnaRelacionCurso: 'cod_cur',
                codGestion: $codGestion,
                filtro: $this->filtroHorario
            );
        }

        if ($this->columnaExiste('curso', 'ord_cur')) {
            $query->orderBy('ord_cur');
        } elseif ($this->columnaExiste('curso', 'nom_cur')) {
            $query->orderBy('nom_cur');
        } elseif ($this->columnaExiste('curso', 'created_at')) {
            $query->orderByDesc('created_at');
        }

        return $query;
    }

    private function aplicarFiltroExistenciaRelacion(
        $query,
        string $tablaRelacion,
        string $columnaRelacionCurso,
        ?string $codGestion,
        string $filtro,
        string $columnaGestionRelacion = 'cod_gea'
    ): void {
        if (
            ! $this->tablaExiste($tablaRelacion)
            || ! $this->columnaExiste($tablaRelacion, $columnaRelacionCurso)
            || ! $this->columnaExiste('curso', 'cod_cur')
        ) {
            return;
        }

        if ($filtro === 'con') {
            $query->whereExists(function ($sub) use ($tablaRelacion, $columnaRelacionCurso, $codGestion, $columnaGestionRelacion) {
                $sub->select(DB::raw(1))
                    ->from($tablaRelacion)
                    ->whereColumn("{$tablaRelacion}.{$columnaRelacionCurso}", 'curso.cod_cur');

                if ($codGestion && $this->columnaExiste($tablaRelacion, $columnaGestionRelacion)) {
                    $sub->where("{$tablaRelacion}.{$columnaGestionRelacion}", $codGestion);
                }
            });
        }

        if ($filtro === 'sin') {
            $query->whereNotExists(function ($sub) use ($tablaRelacion, $columnaRelacionCurso, $codGestion, $columnaGestionRelacion) {
                $sub->select(DB::raw(1))
                    ->from($tablaRelacion)
                    ->whereColumn("{$tablaRelacion}.{$columnaRelacionCurso}", 'curso.cod_cur');

                if ($codGestion && $this->columnaExiste($tablaRelacion, $columnaGestionRelacion)) {
                    $sub->where("{$tablaRelacion}.{$columnaGestionRelacion}", $codGestion);
                }
            });
        }
    }

    /*
    |--------------------------------------------------------------------------
    | PROPIEDADES COMPUTADAS
    |--------------------------------------------------------------------------
    */

    public function getCursosProperty()
    {
        if (! $this->tablaExiste('curso')) {
            return collect();
        }

        $query = $this->cursosQuery();

        if (! $query) {
            return collect();
        }

        return $query
            ->paginate($this->perPage)
            ->through(fn($curso) => $this->mapearCurso($curso));
    }

    public function getGestionesProperty()
    {
        return DB::table('gestion_academica')->orderByDesc('ani_gea')->get();
    }

    public function getParalelosProperty(): Collection
    {
        if (! $this->tablaExiste('paralelo') || ! $this->columnaExiste('paralelo', 'cod_par')) {
            return collect();
        }

        $nombre = $this->columnaNombreParalelo();

        return DB::table('paralelo')
            ->select([
                'cod_par',
                DB::raw($this->columnaExiste('paralelo', $nombre) ? "{$nombre} as nombre" : "'Paralelo' as nombre"),
            ])
            ->orderBy('cod_par')
            ->get();
    }

    public function getTurnosProperty(): Collection
    {
        if (! $this->tablaExiste('turno') || ! $this->columnaExiste('turno', 'cod_tur')) {
            return collect();
        }

        $nombre = $this->columnaNombreTurno();

        return DB::table('turno')
            ->select([
                'cod_tur',
                DB::raw($this->columnaExiste('turno', $nombre) ? "{$nombre} as nombre" : "'Turno' as nombre"),
            ])
            ->orderBy('cod_tur')
            ->get();
    }

    public function getNivelesDisponiblesProperty(): array
    {
        return [
            'Técnica Tecnológica General',
            'Especialización Técnica',
        ];
    }

    public function getMateriasHorarioProperty(): Collection
    {
        $tabla = $this->tablaMateria();
        $codigo = $this->columnaCodigoMateria();
        $nombre = $this->columnaNombreMateria();

        if (! $this->tablaExiste($tabla) || ! $this->columnaExiste($tabla, $codigo)) {
            return collect();
        }

        $query = DB::table($tabla);

        if ($this->columnaExiste($tabla, 'est_mat')) {
            $query->whereIn("{$tabla}.est_mat", ['ACTIVO', 'ACTIVA', '1', 1, true]);
        }

        if ($this->columnaExiste($tabla, 'est_asi')) {
            $query->whereIn("{$tabla}.est_asi", ['ACTIVO', 'ACTIVA', '1', 1, true]);
        }

        $orderBy = $this->columnaExiste($tabla, $nombre) ? $nombre : $codigo;

        return $query
            ->select([
                "{$codigo} as codigo",
                DB::raw($this->columnaExiste($tabla, $nombre) ? "{$nombre} as nombre" : "'Materia registrada' as nombre"),
            ])
            ->orderBy($orderBy)
            ->get();
    }

    public function getEspecialidadesHorarioProperty(): Collection
    {
        $tabla = $this->tablaEspecialidad();
        $codigo = $this->columnaCodigoEspecialidad();
        $nombre = $this->columnaNombreEspecialidad();

        if (! $this->tablaExiste($tabla) || ! $this->columnaExiste($tabla, $codigo)) {
            return collect();
        }

        $query = DB::table($tabla);

        if ($this->columnaExiste($tabla, 'est_esp')) {
            $query->whereIn("{$tabla}.est_esp", ['ACTIVO', 'ACTIVA', '1', 1, true]);
        }

        if ($this->columnaExiste($tabla, 'est_ete')) {
            $query->whereIn("{$tabla}.est_ete", ['ACTIVO', 'ACTIVA', '1', 1, true]);
        }

        $orderBy = $this->columnaExiste($tabla, $nombre) ? $nombre : $codigo;

        return $query
            ->select([
                "{$codigo} as codigo",
                DB::raw($this->columnaExiste($tabla, $nombre) ? "{$nombre} as nombre" : "'Especialidad técnica' as nombre"),
            ])
            ->orderBy($orderBy)
            ->get();
    }

    public function getDocentesHorarioProperty(): Collection
    {
        if (! $this->tablaExiste('docente') || ! $this->columnaExiste('docente', 'cod_doc')) {
            return collect();
        }

        $query = DB::table('docente');

        $this->agregarJoinPersonaDesdeDocente($query);

        return $query
            ->select([
                'docente.cod_doc',
                $this->selectNombreDocente(),
            ])
            ->orderBy('docente.cod_doc')
            ->get()
            ->map(fn($docente) => (object) [
                'cod_doc' => $docente->cod_doc,
                'nombre' => trim($docente->docente ?? '') ?: 'Docente institucional',
            ]);
    }

    public function getPlanesAsignaturaHorarioProperty(): Collection
    {
        return $this->materiasHorario
            ->map(fn($materia) => (object) [
                'codigo' => $materia->codigo ?? '',
                'cod_pas' => null,
                'cod_mat' => $materia->codigo ?? '',
                'cod_asi' => $materia->codigo ?? '',
                'nombre' => $materia->nombre ?? 'Materia registrada',
                'docente' => 'Seleccione docente',
                'horas' => null,
                'estado' => 'ACTIVO',
            ]);
    }

    public function getPlanesEspecialidadHorarioProperty(): Collection
    {
        return $this->especialidadesHorario
            ->map(fn($especialidad) => (object) [
                'codigo' => $especialidad->codigo ?? '',
                'cod_pes' => null,
                'cod_esp' => $especialidad->codigo ?? '',
                'cod_ete' => $especialidad->codigo ?? '',
                'nombre' => $especialidad->nombre ?? 'Especialidad técnica',
                'docente' => 'Seleccione docente',
                'horas' => null,
                'estado' => 'ACTIVO',
            ]);
    }

    public function getTotalCursosProperty(): int
    {
        return $this->tablaExiste('curso') ? DB::table('curso')->count() : 0;
    }

    public function getTotalActivosProperty(): int
    {
        return $this->tablaExiste('curso') && $this->columnaExiste('curso', 'est_cur')
            ? $this->conteoPorEstado('curso', 'est_cur', 'ACTIVO')
            : 0;
    }

    public function getTotalInactivosProperty(): int
    {
        return $this->tablaExiste('curso') && $this->columnaExiste('curso', 'est_cur')
            ? $this->conteoPorEstado('curso', 'est_cur', 'INACTIVO')
            : 0;
    }

    public function getTotalConPlanAsignaturaProperty(): int
    {
        return $this->contarCursosConRelacion('plan_asignatura');
    }

    public function getTotalSinPlanAsignaturaProperty(): int
    {
        return max($this->totalCursos - $this->totalConPlanAsignatura, 0);
    }

    public function getTotalConPlanEspecialidadProperty(): int
    {
        return $this->contarCursosConRelacion('plan_especialidad');
    }

    public function getTotalSinPlanEspecialidadProperty(): int
    {
        return max($this->totalCursos - $this->totalConPlanEspecialidad, 0);
    }

    public function getTotalConHorariosProperty(): int
    {
        return $this->contarCursosConRelacion('horario');
    }

    public function getTotalSinHorariosProperty(): int
    {
        return max($this->totalCursos - $this->totalConHorarios, 0);
    }

    public function getTotalInscritosProperty(): int
    {
        if (! $this->tablaExiste('inscripcion_estudiante')) {
            return 0;
        }

        $query = DB::table('inscripcion_estudiante');

        $codGestion = $this->gestionFiltro ?: optional($this->gestionActiva())->cod_gea;

        if ($codGestion && $this->columnaExiste('inscripcion_estudiante', 'cod_gea')) {
            $query->where('cod_gea', $codGestion);
        }

        return $query->count();
    }

    public function getDatosGraficoHorariosProperty(): array
    {
        return [
            'labels' => ['Con horario', 'Pendiente'],
            'data' => [
                $this->totalConHorarios,
                $this->totalSinHorarios,
            ],
        ];
    }

    public function getDatosGraficoPlanAcademicoProperty(): array
    {
        return [
            'labels' => ['Plan asignatura', 'Plan especialidad', 'Horarios'],
            'data' => [
                $this->totalConPlanAsignatura,
                $this->totalConPlanEspecialidad,
                $this->totalConHorarios,
            ],
        ];
    }

    public function getDatosGraficoInscritosProperty(): array
    {
        if (! $this->tablaExiste('curso')) {
            return [
                'labels' => [],
                'data' => [],
            ];
        }

        $cursos = DB::table('curso')
            ->when($this->columnaExiste('curso', 'ord_cur'), fn($q) => $q->orderBy('ord_cur'))
            ->when(! $this->columnaExiste('curso', 'ord_cur') && $this->columnaExiste('curso', 'nom_cur'), fn($q) => $q->orderBy('nom_cur'))
            ->limit(10)
            ->get();

        return [
            'labels' => $cursos->map(fn($curso) => $curso->nom_cur ?? 'Curso')->toArray(),
            'data' => $cursos->map(fn($curso) => $this->contarInscritosCurso($curso->cod_cur ?? null))->toArray(),
        ];
    }

    public function getDatosGraficosProperty(): array
    {
        return [
            'horarios' => $this->datosGraficoHorarios,
            'plan_academico' => $this->datosGraficoPlanAcademico,
            'inscritos' => $this->datosGraficoInscritos,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | MAPEO DE CURSOS
    |--------------------------------------------------------------------------
    */

    private function mapearCurso(object $curso): array
    {
        $codCur = $curso->cod_cur ?? null;
        $nombre = $curso->nom_cur ?? 'Curso sin nombre';
        $codGestion = $this->gestionFiltro ?: optional($this->gestionActiva())->cod_gea;

        $planAsignaturaCount = $this->contarPlanAsignaturaCurso($codCur, $codGestion);
        $planEspecialidadCount = $this->contarPlanEspecialidadCurso($codCur, $codGestion);
        $horariosCount = $this->contarHorariosCurso($codCur, $codGestion);
        $inscritosCount = $this->contarInscritosCurso($codCur, $codGestion);

        return [
            'cod_cur' => $codCur,
            'nom_cur' => $nombre,
            'ord_cur' => $curso->ord_cur ?? $this->inferirOrdenCurso($nombre),
            'niv_cur' => $curso->niv_cur ?? $this->inferirNivelCurso($nombre),
            'des_cur' => $curso->des_cur ?? 'Sin descripción registrada.',
            'est_cur' => $this->normalizarEstadoParaFormulario($curso->est_cur ?? 'ACTIVO'),
            'estado_label' => $this->estadoCursoLabel($curso->est_cur ?? 'ACTIVO'),

            'plan_asignatura_count' => $planAsignaturaCount,
            'plan_especialidad_count' => $planEspecialidadCount,
            'horarios_count' => $horariosCount,
            'inscritos_count' => $inscritosCount,

            'tiene_plan_asignatura' => $planAsignaturaCount > 0,
            'tiene_plan_especialidad' => $planEspecialidadCount > 0,
            'tiene_horarios' => $horariosCount > 0,

            'porcentaje_horario' => $this->porcentajeHorarioCurso($codCur, $codGestion),
            'bloques_asignados' => $this->contarBloquesAsignadosCurso($codCur, $codGestion),
            'bloques_libres' => $this->contarBloquesLibresCurso($codCur, $codGestion),
            'materias_pendientes' => $this->contarMateriasPendientesHorario($codCur, $codGestion),
            'cruces_docentes' => $this->contarCrucesDocentesCurso($codCur, $codGestion),

            'materias' => $this->materiasCurso($codCur, $codGestion),
            'especialidades' => $this->especialidadesCurso($codCur, $codGestion),
            'horarios_relacionados' => $this->horariosRelacionadosCurso($codCur, $codGestion),

            'created_at' => $curso->created_at ?? null,
            'updated_at' => $curso->updated_at ?? null,
        ];
    }

    private function obtenerCursoDetalle(string $codCur): ?array
    {
        if (! $this->tablaExiste('curso')) {
            return null;
        }

        $curso = DB::table('curso')
            ->where('cod_cur', $codCur)
            ->first();

        if (! $curso) {
            return null;
        }

        $detalle = $this->mapearCurso($curso);
        $detalle['visor_horario'] = $this->visorHorarioCurso($codCur);

        return $detalle;
    }

    private function payloadCurso(
        string $codCur,
        string $nombre,
        int|string $orden,
        string $nivel,
        ?string $descripcion,
        string $estado,
        bool $incluirCodigo = true
    ): array {
        $data = [];

        if ($incluirCodigo && $this->columnaExiste('curso', 'cod_cur')) {
            $data['cod_cur'] = $codCur;
        }

        if ($this->columnaExiste('curso', 'nom_cur')) {
            $data['nom_cur'] = $this->limpiarTexto($nombre);
        }

        if ($this->columnaExiste('curso', 'ord_cur')) {
            $data['ord_cur'] = (int) $orden;
        }

        if ($this->columnaExiste('curso', 'niv_cur')) {
            $data['niv_cur'] = $nivel;
        }

        if ($this->columnaExiste('curso', 'des_cur')) {
            $data['des_cur'] = $this->limpiarTexto($descripcion);
        }

        if ($this->columnaExiste('curso', 'est_cur')) {
            $data['est_cur'] = $this->valorEstadoParaBase('curso', 'est_cur', $estado);
        }

        if ($incluirCodigo && $this->columnaExiste('curso', 'created_at')) {
            $data['created_at'] = now();
        }

        if ($this->columnaExiste('curso', 'updated_at')) {
            $data['updated_at'] = now();
        }

        return $data;
    }

    /*
    |--------------------------------------------------------------------------
    | CONTEOS
    |--------------------------------------------------------------------------
    */

    private function contarCursosConRelacion(string $tablaRelacion): int
    {
        if (
            ! $this->tablaExiste('curso')
            || ! $this->tablaExiste($tablaRelacion)
            || ! $this->columnaExiste($tablaRelacion, 'cod_cur')
        ) {
            return 0;
        }

        $codGestion = $this->gestionFiltro ?: optional($this->gestionActiva())->cod_gea;

        return DB::table('curso')
            ->whereExists(function ($query) use ($tablaRelacion, $codGestion) {
                $query->select(DB::raw(1))
                    ->from($tablaRelacion)
                    ->whereColumn("{$tablaRelacion}.cod_cur", 'curso.cod_cur');

                if ($codGestion && $this->columnaExiste($tablaRelacion, 'cod_gea')) {
                    $query->where("{$tablaRelacion}.cod_gea", $codGestion);
                }
            })
            ->count();
    }

    private function contarPlanAsignaturaCurso(?string $codCur, ?string $codGestion = null): int
    {
        if (! $codCur || ! $this->tablaExiste('plan_asignatura') || ! $this->columnaExiste('plan_asignatura', 'cod_cur')) {
            return 0;
        }

        return DB::table('plan_asignatura')
            ->where('cod_cur', $codCur)
            ->when($codGestion && $this->columnaExiste('plan_asignatura', 'cod_gea'), fn($q) => $q->where('cod_gea', $codGestion))
            ->count();
    }

    private function contarPlanEspecialidadCurso(?string $codCur, ?string $codGestion = null): int
    {
        if (! $codCur || ! $this->tablaExiste('plan_especialidad') || ! $this->columnaExiste('plan_especialidad', 'cod_cur')) {
            return 0;
        }

        return DB::table('plan_especialidad')
            ->where('cod_cur', $codCur)
            ->when($codGestion && $this->columnaExiste('plan_especialidad', 'cod_gea'), fn($q) => $q->where('cod_gea', $codGestion))
            ->count();
    }

    private function contarHorariosCurso(?string $codCur, ?string $codGestion = null): int
    {
        if (! $codCur || ! $this->tablaExiste('horario') || ! $this->columnaExiste('horario', 'cod_cur')) {
            return 0;
        }

        return DB::table('horario')
            ->where('cod_cur', $codCur)
            ->when($codGestion && $this->columnaExiste('horario', 'cod_gea'), fn($q) => $q->where('cod_gea', $codGestion))
            ->count();
    }

    private function contarInscritosCurso(?string $codCur, ?string $codGestion = null): int
    {
        if (! $codCur || ! $this->tablaExiste('inscripcion_estudiante') || ! $this->columnaExiste('inscripcion_estudiante', 'cod_cur')) {
            return 0;
        }

        return DB::table('inscripcion_estudiante')
            ->where('cod_cur', $codCur)
            ->when($codGestion && $this->columnaExiste('inscripcion_estudiante', 'cod_gea'), fn($q) => $q->where('cod_gea', $codGestion))
            ->count();
    }

    private function porcentajeHorarioCurso(?string $codCur, ?string $codGestion = null): int
    {
        $asignados = $this->contarBloquesAsignadosCurso($codCur, $codGestion);
        $total = $this->totalBloquesEsperadosCurso($codCur);

        if ($total <= 0) {
            return 0;
        }

        return min(100, (int) round(($asignados / $total) * 100));
    }

    private function contarBloquesAsignadosCurso(?string $codCur, ?string $codGestion = null): int
    {
        if (! $codCur || ! $this->tablaExiste('horario') || ! $this->tablaExiste('horario_detalle')) {
            return 0;
        }

        return DB::table('horario_detalle')
            ->join('horario', 'horario.cod_hor', '=', 'horario_detalle.cod_hor')
            ->where('horario.cod_cur', $codCur)
            ->when($codGestion && $this->columnaExiste('horario', 'cod_gea'), fn($q) => $q->where('horario.cod_gea', $codGestion))
            ->when($this->columnaExiste('horario_detalle', 'est_hde'), function ($q) {
                $this->aplicarFiltroEstado($q, 'horario_detalle', 'est_hde', 'ACTIVO');
            })
            ->count();
    }

    private function contarBloquesLibresCurso(?string $codCur, ?string $codGestion = null): int
    {
        return max($this->totalBloquesEsperadosCurso($codCur) - $this->contarBloquesAsignadosCurso($codCur, $codGestion), 0);
    }

    private function totalBloquesEsperadosCurso(?string $codCur): int
    {
        return count($this->diasInstitucionales()) * count($this->bloquesInstitucionales());
    }

    private function contarMateriasPendientesHorario(?string $codCur, ?string $codGestion = null): int
    {
        if (
            ! $codCur
            || ! $this->tablaExiste('plan_asignatura')
            || ! $this->tablaExiste('horario')
            || ! $this->tablaExiste('horario_detalle')
        ) {
            return 0;
        }

        if (! $this->columnaExiste('plan_asignatura', 'cod_pas') || ! $this->columnaExiste('horario_detalle', 'cod_pas')) {
            return 0;
        }

        $planes = DB::table('plan_asignatura')
            ->where('cod_cur', $codCur)
            ->when($codGestion && $this->columnaExiste('plan_asignatura', 'cod_gea'), fn($q) => $q->where('cod_gea', $codGestion))
            ->pluck('cod_pas');

        if ($planes->isEmpty()) {
            return 0;
        }

        $planesUbicados = DB::table('horario_detalle')
            ->join('horario', 'horario.cod_hor', '=', 'horario_detalle.cod_hor')
            ->where('horario.cod_cur', $codCur)
            ->whereIn('horario_detalle.cod_pas', $planes)
            ->when($codGestion && $this->columnaExiste('horario', 'cod_gea'), fn($q) => $q->where('horario.cod_gea', $codGestion))
            ->whereNotNull('horario_detalle.cod_pas')
            ->distinct()
            ->pluck('horario_detalle.cod_pas');

        return $planes->diff($planesUbicados)->count();
    }

    private function contarCrucesDocentesCurso(?string $codCur, ?string $codGestion = null): int
    {
        if (
            ! $codCur
            || ! $this->tablaExiste('horario')
            || ! $this->tablaExiste('horario_detalle')
            || ! $this->tablaExiste('horario_bloque')
        ) {
            return 0;
        }

        $registros = DB::table('horario_detalle')
            ->join('horario', 'horario.cod_hor', '=', 'horario_detalle.cod_hor')
            ->join('plantilla_horaria', 'plantilla_horaria.cod_pho', '=', 'horario.cod_pho')
            ->join('horario_bloque', 'horario_bloque.cod_hbl', '=', 'horario_detalle.cod_hbl')
            ->where('horario.cod_cur', $codCur)
            ->when($codGestion && $this->columnaExiste('horario', 'cod_gea'), fn($q) => $q->where('horario.cod_gea', $codGestion))
            ->select([
                'horario_detalle.*',
                'horario.cod_gea',
                'plantilla_horaria.cod_tur as cod_tur',
                'horario_bloque.num_hbl',
            ])
            ->get();

        $vistos = [];
        $cruces = 0;

        foreach ($registros as $registro) {
            $docente = $this->docenteIdDesdeHorario($registro);

            if (! $docente) {
                continue;
            }

            $clave = implode('|', [
                $registro->cod_gea ?? '',
                $registro->cod_tur ?? '',
                $registro->dia_hde ?? '',
                $registro->num_hbl ?? '',
                $docente,
            ]);

            if (isset($vistos[$clave])) {
                $cruces++;
                continue;
            }

            $vistos[$clave] = true;
        }

        return $cruces;
    }

    /*
    |--------------------------------------------------------------------------
    | DATOS RELACIONADOS
    |--------------------------------------------------------------------------
    */

    private function materiasCurso(?string $codCur, ?string $codGestion = null): Collection
    {
        if (! $codCur || ! $this->tablaExiste('plan_asignatura') || ! $this->columnaExiste('plan_asignatura', 'cod_cur')) {
            return collect();
        }

        $tablaMateria = $this->tablaMateria();
        $codMateria = $this->columnaCodigoMateria();
        $nomMateria = $this->columnaNombreMateria();
        $colMateriaPlan = $this->columnaMateriaEnPlanAsignatura();

        $query = DB::table('plan_asignatura')
            ->where('plan_asignatura.cod_cur', $codCur);

        if (
            $colMateriaPlan
            && $this->tablaExiste($tablaMateria)
            && $this->columnaExiste('plan_asignatura', $colMateriaPlan)
            && $this->columnaExiste($tablaMateria, $codMateria)
        ) {
            $query->leftJoin($tablaMateria, "{$tablaMateria}.{$codMateria}", '=', "plan_asignatura.{$colMateriaPlan}");
        }

        $this->agregarJoinDocente($query, 'plan_asignatura');

        $select = [];

        foreach (['cod_pas', 'cod_cur', 'cod_gea', 'cod_par', 'cod_tur', 'cod_doc', 'hor_pas', 'est_pas'] as $columna) {
            if ($this->columnaExiste('plan_asignatura', $columna)) {
                $select[] = "plan_asignatura.{$columna}";
            }
        }

        if ($colMateriaPlan && $this->columnaExiste('plan_asignatura', $colMateriaPlan)) {
            $select[] = "plan_asignatura.{$colMateriaPlan}";
        }

        if ($this->tablaExiste($tablaMateria) && $this->columnaExiste($tablaMateria, $nomMateria)) {
            $select[] = DB::raw("{$tablaMateria}.{$nomMateria} as nombre");
        } else {
            $select[] = DB::raw("'Materia registrada' as nombre");
        }

        $select[] = $this->selectNombreDocente();

        if ($codGestion && $this->columnaExiste('plan_asignatura', 'cod_gea')) {
            $query->where('plan_asignatura.cod_gea', $codGestion);
        }

        return $query
            ->select($select)
            ->limit(100)
            ->get();
    }

    private function especialidadesCurso(?string $codCur, ?string $codGestion = null): Collection
    {
        if (! $codCur || ! $this->tablaExiste('plan_especialidad') || ! $this->columnaExiste('plan_especialidad', 'cod_cur')) {
            return collect();
        }

        $tablaEspecialidad = $this->tablaEspecialidad();
        $codEspecialidad = $this->columnaCodigoEspecialidad();
        $nomEspecialidad = $this->columnaNombreEspecialidad();
        $colEspecialidadPlan = $this->columnaEspecialidadEnPlanEspecialidad();

        $query = DB::table('plan_especialidad')
            ->where('plan_especialidad.cod_cur', $codCur);

        if (
            $colEspecialidadPlan
            && $this->tablaExiste($tablaEspecialidad)
            && $this->columnaExiste('plan_especialidad', $colEspecialidadPlan)
            && $this->columnaExiste($tablaEspecialidad, $codEspecialidad)
        ) {
            $query->leftJoin($tablaEspecialidad, "{$tablaEspecialidad}.{$codEspecialidad}", '=', "plan_especialidad.{$colEspecialidadPlan}");
        }

        $this->agregarJoinDocente($query, 'plan_especialidad');

        $select = ['plan_especialidad.*'];

        if ($this->tablaExiste($tablaEspecialidad) && $this->columnaExiste($tablaEspecialidad, $nomEspecialidad)) {
            $select[] = DB::raw("{$tablaEspecialidad}.{$nomEspecialidad} as nombre");
        } else {
            $select[] = DB::raw("'Especialidad técnica' as nombre");
        }

        $select[] = $this->selectNombreDocente();

        if ($codGestion && $this->columnaExiste('plan_especialidad', 'cod_gea')) {
            $query->where('plan_especialidad.cod_gea', $codGestion);
        }

        return $query
            ->select($select)
            ->limit(100)
            ->get();
    }

    private function horariosRelacionadosCurso(?string $codCur, ?string $codGestion = null): Collection
    {
        if (! $codCur || ! $this->tablaExiste('horario') || ! $this->columnaExiste('horario', 'cod_cur')) {
            return collect();
        }

        $query = DB::table('horario')
            ->where('horario.cod_cur', $codCur);

        if ($codGestion && $this->columnaExiste('horario', 'cod_gea')) {
            $query->where('horario.cod_gea', $codGestion);
        }

        if ($this->tablaExiste('paralelo') && $this->columnaExiste('horario', 'cod_par')) {
            $query->leftJoin('paralelo', 'paralelo.cod_par', '=', 'horario.cod_par');
        }

        if ($this->tablaExiste('plantilla_horaria') && $this->columnaExiste('horario', 'cod_pho')) {
            $query->leftJoin('plantilla_horaria', 'plantilla_horaria.cod_pho', '=', 'horario.cod_pho');
            if ($this->tablaExiste('turno') && $this->columnaExiste('plantilla_horaria', 'cod_tur')) {
                $query->leftJoin('turno', 'turno.cod_tur', '=', 'plantilla_horaria.cod_tur');
            }
        }

        if ($this->tablaExiste('horario_detalle')) {
            $query->leftJoin('horario_detalle', 'horario_detalle.cod_hor', '=', 'horario.cod_hor');
        }

        $select = ['horario.cod_cur'];

        foreach (['cod_hor', 'cod_gea', 'cod_par'] as $columna) {
            if ($this->columnaExiste('horario', $columna)) {
                $select[] = "horario.{$columna}";
            }
        }

        if ($this->tablaExiste('plantilla_horaria') && $this->columnaExiste('plantilla_horaria', 'cod_tur')) {
            $select[] = 'plantilla_horaria.cod_tur as cod_tur';
        }

        if ($this->tablaExiste('horario_detalle')) {
            $select[] = DB::raw('COUNT(horario_detalle.cod_hde) as total_bloques');
        } else {
            $select[] = DB::raw('0 as total_bloques');
        }

        if ($this->tablaExiste('paralelo')) {
            $nombreParalelo = $this->columnaNombreParalelo();
            $select[] = DB::raw($this->columnaExiste('paralelo', $nombreParalelo) ? "MAX(paralelo.{$nombreParalelo}) as paralelo" : "'Paralelo' as paralelo");
        } else {
            $select[] = DB::raw("'Paralelo' as paralelo");
        }

        if ($this->tablaExiste('turno')) {
            $nombreTurno = $this->columnaNombreTurno();
            $select[] = DB::raw($this->columnaExiste('turno', $nombreTurno) ? "MAX(turno.{$nombreTurno}) as turno" : "'Turno' as turno");
        } else {
            $select[] = DB::raw("'Turno' as turno");
        }

        return $query
            ->select($select)
            ->groupBy(array_values(array_filter([
                $this->columnaExiste('horario', 'cod_hor') ? 'horario.cod_hor' : null,
                'horario.cod_cur',
                $this->columnaExiste('horario', 'cod_gea') ? 'horario.cod_gea' : null,
                $this->columnaExiste('horario', 'cod_par') ? 'horario.cod_par' : null,
                ($this->tablaExiste('plantilla_horaria') && $this->columnaExiste('plantilla_horaria', 'cod_tur')) ? 'plantilla_horaria.cod_tur' : null,
            ])))
            ->orderBy('paralelo')
            ->orderBy('turno')
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | VISOR DE HORARIOS
    |--------------------------------------------------------------------------
    */

    private function visorHorarioCurso(string $codCur): array
    {
        $dias = $this->diasInstitucionales();
        $horario = $this->obtenerHorarioCabecera($codCur);
        $bloques = $this->bloquesInstitucionales();

        $matriz = [];

        foreach ($bloques as $bloque) {
            $fila = [
                'cod_hbl' => $bloque['cod_hbl'] ?? null,
                'num_blo_hor' => $bloque['num_blo_hor'],
                'hor_ini_hor' => $bloque['hor_ini_hor'],
                'hor_fin_hor' => $bloque['hor_fin_hor'],
                'nom_hbl' => $bloque['nom_hbl'] ?? ('Bloque ' . $bloque['num_blo_hor']),
                'dias' => [],
            ];

            foreach ($dias as $dia) {
                $fila['dias'][$dia] = $this->celdaHorarioCurso(
                    codCur: $codCur,
                    dia: $dia,
                    numBloque: (int) $bloque['num_blo_hor']
                );
            }

            $matriz[] = $fila;
        }

        $asignados = collect($matriz)
            ->flatMap(fn($fila) => collect($fila['dias']))
            ->filter(fn($celda) => in_array($celda['estado_visual'], ['ASIGNADO', 'ESPECIALIDAD'], true))
            ->count();

        $total = count($dias) * count($bloques);
        $libres = max($total - $asignados, 0);

        return [
            'cod_hor' => $horario->cod_hor ?? null,
            'dias' => $dias,
            'bloques' => $bloques,
            'matriz' => $matriz,
            'indicadores' => [
                'porcentaje' => $total > 0 ? (int) round(($asignados / $total) * 100) : 0,
                'asignados' => $asignados,
                'total' => $total,
                'libres' => $libres,
                'cruces_docentes' => $this->contarCrucesDocentesCurso($codCur, $this->horarioGestion),
                'materias_pendientes' => $this->contarMateriasPendientesHorario($codCur, $this->horarioGestion),
            ],
            'contexto' => [
                'gestion' => $this->nombreGestion($this->horarioGestion),
                'paralelo' => $this->nombreParalelo($this->horarioParalelo),
                'turno' => $this->nombreTurno($this->horarioTurno),
                'vista' => $this->horarioVista,
            ],
        ];
    }

    private function celdaHorarioCurso(string $codCur, string $dia, int $numBloque): array
    {
        $registro = $this->buscarRegistroHorario($codCur, $dia, $numBloque);

        if (! $registro) {
            return $this->celdaLibreHorario();
        }

        $codPas = property_exists($registro, 'cod_pas') ? $registro->cod_pas : null;
        $codPes = property_exists($registro, 'cod_pes') ? $registro->cod_pes : null;

        if (! empty($codPes)) {
            $nombre = $this->nombrePlanEspecialidad($codPes);
            $docente = $this->docentePlanEspecialidad($codPes);

            return [
                'existe' => true,
                'cod_hde' => $registro->cod_hde ?? null,
                'cod_hor' => $registro->cod_hde ?? null,
                'cod_hor_cabecera' => $registro->cod_hor ?? null,
                'cod_hbl' => $registro->cod_hbl ?? null,
                'tipo' => 'ESPECIALIDAD',
                'codigo_visual' => $this->codigoVisualEspecialidad($nombre),
                'nombre' => $nombre,
                'docente' => $docente,
                'aula' => property_exists($registro, 'aul_hde') && $registro->aul_hde
                    ? $registro->aul_hde
                    : 'Aula no definida',
                'observacion' => property_exists($registro, 'obs_hde') ? ($registro->obs_hde ?? '') : '',
                'estado_visual' => 'ESPECIALIDAD',
                'badge' => $this->estadoHorarioLabel(
                    property_exists($registro, 'est_hde') ? ($registro->est_hde ?? 'ACTIVO') : 'ACTIVO'
                ),
                'puede_crear' => false,
            ];
        }

        if (! empty($codPas)) {
            $nombre = $this->nombrePlanAsignatura($codPas);
            $docente = $this->docentePlanAsignatura($codPas);

            return [
                'existe' => true,
                'cod_hde' => $registro->cod_hde ?? null,
                'cod_hor' => $registro->cod_hde ?? null,
                'cod_hor_cabecera' => $registro->cod_hor ?? null,
                'cod_hbl' => $registro->cod_hbl ?? null,
                'tipo' => 'MATERIA',
                'codigo_visual' => $this->codigoVisualMateria($nombre, $docente),
                'nombre' => $nombre,
                'docente' => $docente,
                'aula' => property_exists($registro, 'aul_hde') && $registro->aul_hde
                    ? $registro->aul_hde
                    : 'Aula no definida',
                'observacion' => property_exists($registro, 'obs_hde') ? ($registro->obs_hde ?? '') : '',
                'estado_visual' => 'ASIGNADO',
                'badge' => $this->estadoHorarioLabel(
                    property_exists($registro, 'est_hde') ? ($registro->est_hde ?? 'ACTIVO') : 'ACTIVO'
                ),
                'puede_crear' => false,
            ];
        }

        return $this->celdaLibreHorario();
    }

    private function buscarRegistroHorario(string $codCur, string $dia, int $numBloque): ?object
    {
        if (! $this->estructuraHorarioDisponible()) {
            return null;
        }

        return DB::table('horario_detalle')
            ->join('horario', 'horario.cod_hor', '=', 'horario_detalle.cod_hor')
            ->join('plantilla_horaria', 'plantilla_horaria.cod_pho', '=', 'horario.cod_pho')
            ->join('horario_bloque', 'horario_bloque.cod_hbl', '=', 'horario_detalle.cod_hbl')
            ->where('horario.cod_cur', $codCur)
            ->where('horario_bloque.num_hbl', $numBloque)
            ->where('horario_detalle.dia_hde', strtoupper($dia))
            ->when($this->horarioGestion, fn($q) => $q->where('horario.cod_gea', $this->horarioGestion))
            ->when($this->horarioParalelo, fn($q) => $q->where('horario.cod_par', $this->horarioParalelo))
            ->when(
                $this->horarioTurno,
                fn($q) => $q->where('plantilla_horaria.cod_tur', $this->horarioTurno)
            )
            ->when($this->columnaExiste('horario_detalle', 'est_hde'), function ($q) {
                $this->aplicarFiltroEstado($q, 'horario_detalle', 'est_hde', 'ACTIVO');
            })
            ->select([
                'horario_detalle.*',
                'horario.cod_gea',
                'horario.cod_cur',
                'horario.cod_par',
                'plantilla_horaria.cod_tur as cod_tur',
                'horario_bloque.num_hbl',
                'horario_bloque.hor_ini_hbl',
                'horario_bloque.hor_fin_hbl',
            ])
            ->first();
    }

    private function bloquesInstitucionales(): array
    {
        if ($this->tablaExiste('horario_bloque') && $this->tablaExiste('plantilla_horaria')) {
            $plantilla = DB::table('plantilla_horaria')
                ->where('cod_tur', $this->horarioTurno)
                ->where('est_pho', true)
                ->orderByDesc('act_pho')
                ->first();

            if ($plantilla) {
                $bloques = DB::table('horario_bloque')
                    ->where('cod_pho', $plantilla->cod_pho)
                    ->when($this->columnaExiste('horario_bloque', 'est_hbl'), function ($q) {
                        $this->aplicarFiltroEstado($q, 'horario_bloque', 'est_hbl', 'ACTIVO');
                    })
                    ->orderBy('num_hbl')
                    ->get();

                if ($bloques->isNotEmpty()) {
                    return $bloques
                        ->map(fn($bloque) => [
                            'cod_hbl' => $bloque->cod_hbl,
                            'num_blo_hor' => (int) $bloque->num_hbl,
                            'hor_ini_hor' => substr((string) $bloque->hor_ini_hbl, 0, 5),
                            'hor_fin_hor' => substr((string) $bloque->hor_fin_hbl, 0, 5),
                            'nom_hbl' => $bloque->nom_hbl ?? ('Bloque ' . $bloque->num_hbl),
                        ])
                        ->toArray();
                }
            }
        }

        return [];
    }

    private function diasInstitucionales(): array
    {
        return ['LUNES', 'MARTES', 'MIERCOLES', 'JUEVES', 'VIERNES'];
    }

    private function celdaLibreHorario(): array
    {
        return [
            'existe' => false,
            'cod_hde' => null,
            'cod_hor' => null,
            'cod_hor_cabecera' => null,
            'cod_hbl' => null,
            'tipo' => 'LIBRE',
            'codigo_visual' => 'Libre',
            'nombre' => 'Sin asignar',
            'docente' => 'Disponible',
            'aula' => 'Libre',
            'observacion' => '',
            'estado_visual' => 'LIBRE',
            'badge' => 'Libre',
            'puede_crear' => true,
        ];
    }

    private function bloqueHorarioPorNumero(int $numeroBloque): ?object
    {
        if (! $this->tablaExiste('horario_bloque') || ! $this->tablaExiste('plantilla_horaria')) {
            return null;
        }

        $plantilla = DB::table('plantilla_horaria')
            ->where('cod_tur', $this->horarioTurno)
            ->where('est_pho', true)
            ->orderByDesc('act_pho')
            ->first();

        if (!$plantilla) {
            return null;
        }

        return DB::table('horario_bloque')
            ->where('cod_pho', $plantilla->cod_pho)
            ->where('num_hbl', $numeroBloque)
            ->when($this->columnaExiste('horario_bloque', 'est_hbl'), function ($q) {
                $this->aplicarFiltroEstado($q, 'horario_bloque', 'est_hbl', 'ACTIVO');
            })
            ->first();
    }

    private function obtenerHorarioCabecera(?string $codCur = null): ?object
    {
        $codCur = $codCur ?: $this->cursoSeleccionado;

        if (! $codCur || ! $this->tablaExiste('horario')) {
            return null;
        }

        return DB::table('horario')
            ->join('plantilla_horaria', 'plantilla_horaria.cod_pho', '=', 'horario.cod_pho')
            ->where('horario.cod_gea', $this->horarioGestion)
            ->where('horario.cod_cur', $codCur)
            ->where('horario.cod_par', $this->horarioParalelo)
            ->where('plantilla_horaria.cod_tur', $this->horarioTurno)
            ->select('horario.*')
            ->first();
    }

    private function obtenerOCrearHorarioCabecera(): ?object
    {
        $existente = $this->obtenerHorarioCabecera($this->cursoSeleccionado);

        if ($existente) {
            return $existente;
        }

        if (! $this->tablaExiste('horario') || ! $this->tablaExiste('plantilla_horaria')) {
            return null;
        }

        // Buscar plantilla activa del turno
        $plantilla = DB::table('plantilla_horaria')
            ->where('cod_tur', $this->horarioTurno)
            ->where('est_pho', true)
            ->orderByDesc('act_pho')
            ->first();

        if (!$plantilla) {
            $this->dispatch('error-general', mensaje: 'No hay una plantilla horaria activa para este turno.');
            return null;
        }

        $codHor = $this->generarCodigo('horario', 'cod_hor', 'HOR');

        $data = [
            'cod_hor' => $codHor,
            'cod_gea' => $this->horarioGestion,
            'cod_cur' => $this->cursoSeleccionado,
            'cod_par' => $this->horarioParalelo,
            'cod_pho' => $plantilla->cod_pho,
        ];

        if ($this->columnaExiste('horario', 'nom_hor')) {
            $data['nom_hor'] = 'Horario '
                . $this->nombreCurso($this->cursoSeleccionado)
                . ' - '
                . $this->nombreParalelo($this->horarioParalelo)
                . ' - '
                . $this->nombreTurno($this->horarioTurno);
        }

        if ($this->columnaExiste('horario', 'obs_hor')) {
            $data['obs_hor'] = null;
        }

        if ($this->columnaExiste('horario', 'est_hor')) {
            $data['est_hor'] = 'ACTIVO';
        }

        if ($this->columnaExiste('horario', 'created_at')) {
            $data['created_at'] = now();
        }

        if ($this->columnaExiste('horario', 'updated_at')) {
            $data['updated_at'] = now();
        }

        DB::table('horario')->insert($data);

        $this->registrarBitacoraSeguro(
            accion: 'CREAR_CABECERA_HORARIO',
            tabla: 'horario',
            registro: $codHor,
            nombreRegistro: $this->nombreCurso($this->cursoSeleccionado),
            descripcion: 'Se creó la cabecera del horario para curso, gestión, paralelo y plantilla.',
            nivel: 'INFO',
            resultado: 'EXITOSO',
            valoresNuevos: $data
        );

        return DB::table('horario')
            ->where('cod_hor', $codHor)
            ->first();
    }

    private function estructuraHorarioDisponible(): bool
    {
        return $this->tablaExiste('horario')
            && $this->tablaExiste('horario_bloque')
            && $this->tablaExiste('horario_detalle')
            && $this->columnaExiste('horario', 'cod_hor')
            && $this->columnaExiste('horario_bloque', 'cod_hbl')
            && $this->columnaExiste('horario_detalle', 'cod_hde');
    }

    /*
    |--------------------------------------------------------------------------
    | CRUCE DOCENTE
    |--------------------------------------------------------------------------
    */

    private function existeCruceDocenteDirecto(string $codDoc, string $dia, int $bloque): bool
    {
        if (
            ! $codDoc
            || ! $this->tablaExiste('horario')
            || ! $this->tablaExiste('horario_detalle')
            || ! $this->tablaExiste('horario_bloque')
        ) {
            return false;
        }

        $registros = DB::table('horario_detalle')
            ->join('horario', 'horario.cod_hor', '=', 'horario_detalle.cod_hor')
            ->join('plantilla_horaria', 'plantilla_horaria.cod_pho', '=', 'horario.cod_pho')
            ->join('horario_bloque', 'horario_bloque.cod_hbl', '=', 'horario_detalle.cod_hbl')
            ->where('horario_detalle.dia_hde', strtoupper($dia))
            ->where('horario_bloque.num_hbl', $bloque)
            ->when($this->horarioGestion, fn($q) => $q->where('horario.cod_gea', $this->horarioGestion))
            ->when($this->horarioTurno, fn($q) => $q->where('plantilla_horaria.cod_tur', $this->horarioTurno))
            ->select('horario_detalle.*')
            ->get();

        foreach ($registros as $registro) {
            if ($this->docenteIdDesdeHorario($registro) === $codDoc) {
                return true;
            }
        }

        return false;
    }

    private function docenteIdDesdeHorario(object $registro): ?string
    {
        $codPas = property_exists($registro, 'cod_pas') ? $registro->cod_pas : null;
        $codPes = property_exists($registro, 'cod_pes') ? $registro->cod_pes : null;

        if (! empty($codPas)) {
            return $this->docenteIdPlanAsignatura($codPas);
        }

        if (! empty($codPes)) {
            return $this->docenteIdPlanEspecialidad($codPes);
        }

        return null;
    }

    private function docenteIdPlanAsignatura(?string $codPas): ?string
    {
        if (! $codPas || ! $this->tablaExiste('plan_asignatura') || ! $this->columnaExiste('plan_asignatura', 'cod_doc')) {
            return null;
        }

        return DB::table('plan_asignatura')
            ->where('cod_pas', $codPas)
            ->value('cod_doc');
    }

    private function docenteIdPlanEspecialidad(?string $codPes): ?string
    {
        if (! $codPes || ! $this->tablaExiste('plan_especialidad')) {
            return null;
        }

        $pk = $this->columnaCodigoPlanEspecialidad();

        if (! $this->columnaExiste('plan_especialidad', $pk) || ! $this->columnaExiste('plan_especialidad', 'cod_doc')) {
            return null;
        }

        return DB::table('plan_especialidad')
            ->where($pk, $codPes)
            ->value('cod_doc');
    }

    /*
    |--------------------------------------------------------------------------
    | NOMBRES DESDE PLANES
    |--------------------------------------------------------------------------
    */

    private function nombrePlanAsignatura(?string $codPas): string
    {
        if (! $codPas || ! $this->tablaExiste('plan_asignatura')) {
            return 'Materia planificada';
        }

        $tablaMateria = $this->tablaMateria();
        $codMateria = $this->columnaCodigoMateria();
        $nomMateria = $this->columnaNombreMateria();
        $colMateriaPlan = $this->columnaMateriaEnPlanAsignatura();

        $query = DB::table('plan_asignatura')
            ->where('plan_asignatura.cod_pas', $codPas);

        if (
            $colMateriaPlan
            && $this->tablaExiste($tablaMateria)
            && $this->columnaExiste('plan_asignatura', $colMateriaPlan)
            && $this->columnaExiste($tablaMateria, $codMateria)
        ) {
            $query->leftJoin($tablaMateria, "{$tablaMateria}.{$codMateria}", '=', "plan_asignatura.{$colMateriaPlan}");

            if ($this->columnaExiste($tablaMateria, $nomMateria)) {
                return $query->value("{$tablaMateria}.{$nomMateria}") ?? 'Materia planificada';
            }
        }

        return 'Materia planificada';
    }

    private function docentePlanAsignatura(?string $codPas): string
    {
        if (! $codPas || ! $this->tablaExiste('plan_asignatura')) {
            return 'Docente asignado';
        }

        $query = DB::table('plan_asignatura')
            ->where('plan_asignatura.cod_pas', $codPas);

        $this->agregarJoinDocente($query, 'plan_asignatura');

        return $query->select([$this->selectNombreDocente()])->value('docente') ?? 'Docente asignado';
    }

    private function nombrePlanEspecialidad(?string $codPes): string
    {
        if (! $codPes || ! $this->tablaExiste('plan_especialidad')) {
            return 'Especialidad técnica';
        }

        $pk = $this->columnaCodigoPlanEspecialidad();
        $tablaEspecialidad = $this->tablaEspecialidad();
        $codEspecialidad = $this->columnaCodigoEspecialidad();
        $nomEspecialidad = $this->columnaNombreEspecialidad();
        $colEspecialidadPlan = $this->columnaEspecialidadEnPlanEspecialidad();

        $query = DB::table('plan_especialidad')
            ->where("plan_especialidad.{$pk}", $codPes);

        if (
            $colEspecialidadPlan
            && $this->tablaExiste($tablaEspecialidad)
            && $this->columnaExiste('plan_especialidad', $colEspecialidadPlan)
            && $this->columnaExiste($tablaEspecialidad, $codEspecialidad)
        ) {
            $query->leftJoin($tablaEspecialidad, "{$tablaEspecialidad}.{$codEspecialidad}", '=', "plan_especialidad.{$colEspecialidadPlan}");

            if ($this->columnaExiste($tablaEspecialidad, $nomEspecialidad)) {
                return $query->value("{$tablaEspecialidad}.{$nomEspecialidad}") ?? 'Especialidad técnica';
            }
        }

        return 'Especialidad técnica';
    }

    private function docentePlanEspecialidad(?string $codPes): string
    {
        if (! $codPes || ! $this->tablaExiste('plan_especialidad')) {
            return 'Docente asignado';
        }

        $pk = $this->columnaCodigoPlanEspecialidad();

        $query = DB::table('plan_especialidad')
            ->where("plan_especialidad.{$pk}", $codPes);

        $this->agregarJoinDocente($query, 'plan_especialidad');

        return $query->select([$this->selectNombreDocente()])->value('docente') ?? 'Docente asignado';
    }

    /*
    |--------------------------------------------------------------------------
    | DETECCIÓN DE TABLAS Y COLUMNAS
    |--------------------------------------------------------------------------
    */

    private function tablaMateria(): string
    {
        if ($this->tablaExiste('materia')) {
            return 'materia';
        }

        if ($this->tablaExiste('asignatura')) {
            return 'asignatura';
        }

        return 'materia';
    }

    private function columnaCodigoMateria(): string
    {
        $tabla = $this->tablaMateria();

        if ($this->columnaExiste($tabla, 'cod_mat')) {
            return 'cod_mat';
        }

        if ($this->columnaExiste($tabla, 'cod_asi')) {
            return 'cod_asi';
        }

        return 'cod_mat';
    }

    private function columnaNombreMateria(): string
    {
        $tabla = $this->tablaMateria();

        if ($this->columnaExiste($tabla, 'nom_mat')) {
            return 'nom_mat';
        }

        if ($this->columnaExiste($tabla, 'nom_asi')) {
            return 'nom_asi';
        }

        if ($this->columnaExiste($tabla, 'nombre')) {
            return 'nombre';
        }

        return 'nom_mat';
    }

    private function tablaEspecialidad(): string
    {
        if ($this->tablaExiste('especialidad_tecnica')) {
            return 'especialidad_tecnica';
        }

        if ($this->tablaExiste('especialidad')) {
            return 'especialidad';
        }

        return 'especialidad_tecnica';
    }

    private function columnaCodigoEspecialidad(): string
    {
        $tabla = $this->tablaEspecialidad();

        if ($this->columnaExiste($tabla, 'cod_esp')) {
            return 'cod_esp';
        }

        if ($this->columnaExiste($tabla, 'cod_ete')) {
            return 'cod_ete';
        }

        return 'cod_esp';
    }

    private function columnaNombreEspecialidad(): string
    {
        $tabla = $this->tablaEspecialidad();

        if ($this->columnaExiste($tabla, 'nom_esp')) {
            return 'nom_esp';
        }

        if ($this->columnaExiste($tabla, 'nom_ete')) {
            return 'nom_ete';
        }

        if ($this->columnaExiste($tabla, 'nombre')) {
            return 'nombre';
        }

        return 'nom_esp';
    }

    private function columnaCodigoPlanEspecialidad(): string
    {
        if ($this->columnaExiste('plan_especialidad', 'cod_pes')) {
            return 'cod_pes';
        }

        if ($this->columnaExiste('plan_especialidad', 'cod_ple')) {
            return 'cod_ple';
        }

        return 'cod_pes';
    }

    private function columnaMateriaEnPlanAsignatura(): ?string
    {
        if ($this->columnaExiste('plan_asignatura', 'cod_mat')) {
            return 'cod_mat';
        }

        if ($this->columnaExiste('plan_asignatura', 'cod_asi')) {
            return 'cod_asi';
        }

        return null;
    }

    private function columnaEspecialidadEnPlanEspecialidad(): ?string
    {
        if ($this->columnaExiste('plan_especialidad', 'cod_esp')) {
            return 'cod_esp';
        }

        if ($this->columnaExiste('plan_especialidad', 'cod_ete')) {
            return 'cod_ete';
        }

        if ($this->columnaExiste('plan_especialidad', 'cod_mat')) {
            return 'cod_mat';
        }

        return null;
    }

    private function columnaNombreParalelo(): string
    {
        if ($this->columnaExiste('paralelo', 'nom_par')) {
            return 'nom_par';
        }

        if ($this->columnaExiste('paralelo', 'par_par')) {
            return 'par_par';
        }

        if ($this->columnaExiste('paralelo', 'nombre')) {
            return 'nombre';
        }

        return 'cod_par';
    }

    private function columnaNombreTurno(): string
    {
        if ($this->columnaExiste('turno', 'nom_tur')) {
            return 'nom_tur';
        }

        if ($this->columnaExiste('turno', 'tur_tur')) {
            return 'tur_tur';
        }

        if ($this->columnaExiste('turno', 'nombre')) {
            return 'nombre';
        }

        return 'cod_tur';
    }

    private function codTurnoPorVista(string $vista): ?string
    {
        if (! $this->tablaExiste('turno')) {
            return null;
        }

        $nombre = $this->columnaNombreTurno();

        if (! $this->columnaExiste('turno', $nombre)) {
            return null;
        }

        $palabras = strtoupper($vista) === 'TARDE'
            ? ['tarde', 'vespertino']
            : ['mañana', 'manana', 'matutino'];

        foreach ($palabras as $palabra) {
            $turno = DB::table('turno')
                ->whereRaw("LOWER({$nombre}) LIKE ?", ['%' . mb_strtolower($palabra) . '%'])
                ->first();

            if ($turno) {
                return $turno->cod_tur ?? null;
            }
        }

        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | GESTIÓN, CURSO, TURNO Y PARALELO
    |--------------------------------------------------------------------------
    */

    private function gestionActiva(): ?object
    {
        if (! $this->tablaExiste('gestion_academica')) {
            return null;
        }

        $query = DB::table('gestion_academica');

        if ($this->columnaExiste('gestion_academica', 'est_gea')) {
            $query->whereIn('est_gea', ['ACTIVO', 'ACTIVA', '1', 1, true]);
        }

        return $query
            ->when($this->columnaExiste('gestion_academica', 'ani_gea'), fn($q) => $q->orderByDesc('ani_gea'))
            ->first();
    }

    private function nombreGestion(?string $codGea): string
    {
        if (! $codGea || ! $this->tablaExiste('gestion_academica')) {
            return 'Gestión no definida';
        }

        $gestion = DB::table('gestion_academica')
            ->where('cod_gea', $codGea)
            ->first();

        if (! $gestion) {
            return 'Gestión no definida';
        }

        return 'Gestión ' . ($gestion->ani_gea ?? $gestion->cod_gea);
    }

    private function nombreCurso(?string $codCur): string
    {
        if (! $codCur || ! $this->tablaExiste('curso')) {
            return 'Curso no identificado';
        }

        return DB::table('curso')
            ->where('cod_cur', $codCur)
            ->value('nom_cur') ?? 'Curso no identificado';
    }

    private function nombreParalelo(?string $codPar): string
    {
        if (! $codPar || ! $this->tablaExiste('paralelo')) {
            return 'Paralelo no definido';
        }

        $columna = $this->columnaNombreParalelo();

        return DB::table('paralelo')
            ->where('cod_par', $codPar)
            ->value($columna) ?? $codPar;
    }

    private function nombreTurno(?string $codTur): string
    {
        if (! $codTur || ! $this->tablaExiste('turno')) {
            return 'Turno no definido';
        }

        $columna = $this->columnaNombreTurno();

        return DB::table('turno')
            ->where('cod_tur', $codTur)
            ->value($columna) ?? $codTur;
    }

    private function primerParaleloDisponible(): string
    {
        return $this->paralelos->first()->cod_par ?? '';
    }

    private function primerTurnoDisponible(): string
    {
        return $this->turnos->first()->cod_tur ?? '';
    }

    /*
    |--------------------------------------------------------------------------
    | DOCENTES
    |--------------------------------------------------------------------------
    */

    private function agregarJoinDocente($query, string $tablaPlan): void
    {
        if (! $this->tablaExiste('docente') || ! $this->columnaExiste($tablaPlan, 'cod_doc')) {
            return;
        }

        $query->leftJoin('docente', 'docente.cod_doc', '=', "{$tablaPlan}.cod_doc");
        $this->agregarJoinPersonaDesdeDocente($query);
    }

    private function agregarJoinPersonaDesdeDocente($query): void
    {
        if ($this->tablaExiste('personal_institucional') && $this->columnaExiste('docente', 'cod_pin')) {
            $query->leftJoin('personal_institucional', 'personal_institucional.cod_pin', '=', 'docente.cod_pin');

            if ($this->tablaExiste('persona') && $this->columnaExiste('personal_institucional', 'cod_per')) {
                $query->leftJoin('persona', 'persona.cod_per', '=', 'personal_institucional.cod_per');
            }
        }
    }

    private function selectNombreDocente()
    {
        if (
            $this->tablaExiste('persona')
            && $this->columnaExiste('persona', 'nom_per')
            && $this->columnaExiste('persona', 'ape_pat_per')
            && $this->columnaExiste('persona', 'ape_mat_per')
        ) {
            return DB::raw("
                TRIM(
                    COALESCE(persona.nom_per, '') || ' ' ||
                    COALESCE(persona.ape_pat_per, '') || ' ' ||
                    COALESCE(persona.ape_mat_per, '')
                ) as docente
            ");
        }

        return DB::raw("'Docente asignado' as docente");
    }

    /*
    |--------------------------------------------------------------------------
    | BITÁCORA
    |--------------------------------------------------------------------------
    */

    private function registrarBitacoraSeguro(
        string $accion,
        string $tabla,
        ?string $registro = null,
        string $modulo = 'Gestión de Cursos',
        ?string $nombreRegistro = null,
        ?string $descripcion = null,
        string $nivel = 'INFO',
        string $resultado = 'EXITOSO',
        ?array $valoresAnteriores = null,
        ?array $valoresNuevos = null,
        ?string $error = null
    ): void {
        try {
            if (! $this->tablaExiste('bitacora')) {
                return;
            }

            $data = [];

            if ($this->columnaExiste('bitacora', 'cod_bit')) {
                $data['cod_bit'] = $this->generarCodigo('bitacora', 'cod_bit', 'BIT');
            }

            if ($this->columnaExiste('bitacora', 'acc_bit')) {
                $data['acc_bit'] = $accion;
            }

            if ($this->columnaExiste('bitacora', 'tab_bit')) {
                $data['tab_bit'] = $tabla;
            }

            if ($this->columnaExiste('bitacora', 'reg_bit')) {
                $data['reg_bit'] = $registro;
            }

            if ($this->columnaExiste('bitacora', 'mod_bit')) {
                $data['mod_bit'] = $modulo;
            }

            if ($this->columnaExiste('bitacora', 'nom_reg_bit')) {
                $data['nom_reg_bit'] = $nombreRegistro;
            }

            if ($this->columnaExiste('bitacora', 'des_bit')) {
                $data['des_bit'] = $descripcion;
            }

            if ($this->columnaExiste('bitacora', 'niv_bit')) {
                $data['niv_bit'] = $nivel;
            }

            if ($this->columnaExiste('bitacora', 'res_bit')) {
                $data['res_bit'] = $resultado;
            }

            if ($this->columnaExiste('bitacora', 'cod_usu')) {
                $data['cod_usu'] = Auth::id();
            }

            if ($this->columnaExiste('bitacora', 'ip_bit')) {
                $data['ip_bit'] = request()->ip();
            }

            if ($this->columnaExiste('bitacora', 'age_bit')) {
                $data['age_bit'] = substr((string) request()->userAgent(), 0, 255);
            }

            if ($this->columnaExiste('bitacora', 'rut_bit')) {
                $data['rut_bit'] = substr((string) request()->path(), 0, 255);
            }

            if ($this->columnaExiste('bitacora', 'met_bit')) {
                $data['met_bit'] = request()->method();
            }

            if ($this->columnaExiste('bitacora', 'val_ant_bit')) {
                $data['val_ant_bit'] = $valoresAnteriores
                    ? json_encode($valoresAnteriores, JSON_UNESCAPED_UNICODE)
                    : null;
            }

            if ($this->columnaExiste('bitacora', 'val_nue_bit')) {
                $data['val_nue_bit'] = $valoresNuevos
                    ? json_encode($valoresNuevos, JSON_UNESCAPED_UNICODE)
                    : null;
            }

            if ($this->columnaExiste('bitacora', 'err_bit')) {
                $data['err_bit'] = $error;
            }

            if ($this->columnaExiste('bitacora', 'fec_bit')) {
                $data['fec_bit'] = now();
            }

            if ($this->columnaExiste('bitacora', 'est_bit')) {
                $data['est_bit'] = 'ACTIVO';
            }

            DB::table('bitacora')->insert($data);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS BASE DE DATOS
    |--------------------------------------------------------------------------
    */

    private function tablaExiste(string $tabla): bool
    {
        if (array_key_exists($tabla, $this->tableExists)) {
            return $this->tableExists[$tabla];
        }

        try {
            return $this->tableExists[$tabla] = Schema::hasTable($tabla);
        } catch (\Throwable) {
            return $this->tableExists[$tabla] = false;
        }
    }

    private function columnaExiste(string $tabla, string $columna): bool
    {
        if (! $this->tablaExiste($tabla)) {
            return false;
        }

        try {
            if (! array_key_exists($tabla, $this->columnsByTable)) {
                $this->columnsByTable[$tabla] = array_map('strtolower', Schema::getColumnListing($tabla));
            }

            return in_array(strtolower($columna), $this->columnsByTable[$tabla], true);
        } catch (\Throwable) {
            return false;
        }
    }

    private function tipoColumna(string $tabla, string $columna): ?string
    {
        $key = $tabla.'.'.$columna;
        if (array_key_exists($key, $this->columnTypes)) {
            return $this->columnTypes[$key];
        }

        try {
            if (! $this->columnaExiste($tabla, $columna)) {
                return $this->columnTypes[$key] = null;
            }

            return $this->columnTypes[$key] = Schema::getColumnType($tabla, $columna);
        } catch (\Throwable) {
            return $this->columnTypes[$key] = null;
        }
    }

    private function generarCodigo(string $tabla, string $columna, string $prefijo): string
    {
        if (! $this->tablaExiste($tabla) || ! $this->columnaExiste($tabla, $columna)) {
            return $prefijo . '_0001';
        }

        $ultimo = DB::table($tabla)
            ->where($columna, 'like', $prefijo . '_%')
            ->orderByDesc($columna)
            ->value($columna);

        if (! $ultimo) {
            return $prefijo . '_0001';
        }

        $numero = (int) str_replace($prefijo . '_', '', $ultimo);

        return $prefijo . '_' . str_pad((string) ($numero + 1), 4, '0', STR_PAD_LEFT);
    }

    private function aplicarFiltroEstado($query, string $tabla, string $columna, string $estado): void
    {
        $tipo = $this->tipoColumna($tabla, $columna);

        if ($tipo === 'boolean') {
            $query->where($columna, $estado === 'ACTIVO');
            return;
        }

        if ($estado === 'ACTIVO') {
            $query->whereIn($columna, ['ACTIVO', 'ACTIVA', '1', 1, true]);
            return;
        }

        $query->whereIn($columna, ['INACTIVO', 'INACTIVA', '0', 0, false]);
    }

    private function conteoPorEstado(string $tabla, string $columna, string $estado): int
    {
        if (! $this->tablaExiste($tabla) || ! $this->columnaExiste($tabla, $columna)) {
            return 0;
        }

        $query = DB::table($tabla);

        $this->aplicarFiltroEstado($query, $tabla, $columna, $estado);

        return $query->count();
    }

    private function valorEstadoParaBase(string $tabla, string $columna, string $estado): mixed
    {
        $tipo = $this->tipoColumna($tabla, $columna);

        if ($tipo === 'boolean') {
            return $estado === 'ACTIVO';
        }

        return $estado;
    }

    private function normalizarEstadoParaFormulario(mixed $estado): string
    {
        if (is_bool($estado)) {
            return $estado ? 'ACTIVO' : 'INACTIVO';
        }

        $estado = strtoupper(trim((string) $estado));

        return in_array($estado, ['ACTIVO', 'ACTIVA', '1'], true)
            ? 'ACTIVO'
            : 'INACTIVO';
    }

    private function estadoCursoLabel(mixed $estado): string
    {
        return $this->normalizarEstadoParaFormulario($estado) === 'ACTIVO'
            ? 'Activo'
            : 'Inactivo';
    }

    private function estadoHorarioLabel(mixed $estado): string
    {
        return $this->normalizarEstadoParaFormulario($estado) === 'ACTIVO'
            ? 'Activo'
            : 'Inactivo';
    }

    private function limpiarTexto(?string $valor): ?string
    {
        $valor = trim((string) $valor);
        $valor = preg_replace('/\s+/', ' ', $valor);

        return $valor === '' ? null : $valor;
    }

    private function inferirOrdenCurso(?string $nombre): int
    {
        $nombre = mb_strtolower((string) $nombre);

        return match (true) {
            str_contains($nombre, '1') || str_contains($nombre, 'primero') || str_contains($nombre, '1ro') => 1,
            str_contains($nombre, '2') || str_contains($nombre, 'segundo') || str_contains($nombre, '2do') => 2,
            str_contains($nombre, '3') || str_contains($nombre, 'tercero') || str_contains($nombre, '3ro') => 3,
            str_contains($nombre, '4') || str_contains($nombre, 'cuarto') || str_contains($nombre, '4to') => 4,
            str_contains($nombre, '5') || str_contains($nombre, 'quinto') || str_contains($nombre, '5to') => 5,
            str_contains($nombre, '6') || str_contains($nombre, 'sexto') || str_contains($nombre, '6to') => 6,
            default => 1,
        };
    }

    private function inferirNivelCurso(?string $nombre): string
    {
        $orden = $this->inferirOrdenCurso($nombre);

        if ($orden >= 4 && $orden <= 6) {
            return 'Especialización Técnica';
        }

        return 'Técnica Tecnológica General';
    }

    /*
    |--------------------------------------------------------------------------
    | CÓDIGOS VISUALES
    |--------------------------------------------------------------------------
    */

    private function codigoVisualMateria(string $materia, string $docente): string
    {
        return $this->abreviarTexto($materia) . '-' . $this->inicialesTexto($docente);
    }

    private function codigoVisualEspecialidad(string $especialidad): string
    {
        $especialidad = mb_strtolower($especialidad);

        if (str_contains($especialidad, 'sistema')) {
            return 'TTE-SIS';
        }

        if (str_contains($especialidad, 'electr')) {
            return 'TTE-ELC';
        }

        if (str_contains($especialidad, 'conta')) {
            return 'TTE-CON';
        }

        if (str_contains($especialidad, 'gastr')) {
            return 'TTE-GAS';
        }

        return 'TTE';
    }

    private function abreviarTexto(?string $texto): string
    {
        $texto = trim((string) $texto);

        if ($texto === '') {
            return 'MAT';
        }

        $palabras = preg_split('/\s+/', $texto);

        if (count($palabras) === 1) {
            return strtoupper(mb_substr($texto, 0, 3));
        }

        return strtoupper(
            collect($palabras)
                ->filter()
                ->take(3)
                ->map(fn($palabra) => mb_substr($palabra, 0, 1))
                ->implode('')
        );
    }

    private function inicialesTexto(?string $texto): string
    {
        $texto = trim((string) $texto);

        if ($texto === '') {
            return 'S.D.';
        }

        $partes = preg_split('/\s+/', $texto);

        $inicialNombre = mb_substr($partes[0] ?? '', 0, 1);
        $inicialApellido = mb_substr($partes[1] ?? '', 0, 1);

        return strtoupper($inicialNombre . '.' . $inicialApellido . '.');
    }

    /*
    |--------------------------------------------------------------------------
    | RENDER
    |--------------------------------------------------------------------------
    */

    public function render()
    {
        $this->autorizar();
        if($this->modalClaseHorario&&$this->claseContexto){
            $this->analisisClase=app(PlanificacionClaseInteligente::class)->analizar($this->claseContexto['cod_hor'],$this->claseContexto['cod_hbl'],$this->claseContexto['dia_hor'],$this->formClaseHorario);
        }
        $resumen = $this->resumen;
        $filtrados = $resumen->filter(function ($c) {
            if ($this->search !== '' && !str_contains(CursoInteligente::normalizar($c['nombre'].' '.$c['etapa']),CursoInteligente::normalizar($this->search))) return false;
            if ($this->estado && $c['estado'] !== $this->estado) return false;
            if ($this->nivel === 'general' && $c['orden'] > 3 || $this->nivel === 'tecnica' && $c['orden'] < 4) return false;
            if ($this->filtroHorario === 'con' && !$c['horarios'] || $this->filtroHorario === 'sin' && $c['horarios']) return false;
            if ($this->filtroPlanAsignatura === 'con' && !$c['planes'] || $this->filtroPlanAsignatura === 'sin' && $c['planes']) return false;
            if ($this->filtroPlanEspecialidad === 'con' && !$c['planes_tecnicos'] || $this->filtroPlanEspecialidad === 'sin' && $c['planes_tecnicos']) return false;
            return !$this->filtroParalelos && !$this->filtroTurnos || collect($c['contextos'])->contains(fn($g)=>(!$this->filtroParalelos || in_array($g['cod_par'],$this->filtroParalelos,true)) && (!$this->filtroTurnos || in_array($g['cod_tur'],$this->filtroTurnos,true)));
        });
        $filtrados = match ($this->ordenar) {'estudiantes_desc'=>$filtrados->sortByDesc('estudiantes'),'estudiantes_asc'=>$filtrados->sortBy('estudiantes'),'nombre'=>$filtrados->sortBy('nombre'),default=>$filtrados->sortBy('orden')};
        $porPagina = in_array($this->perPage,[10,20,50]) ? $this->perPage : 10;
        $pagina = max(1,(int)$this->getPage());
        $cursos = new \Illuminate\Pagination\LengthAwarePaginator($filtrados->values()->forPage($pagina,$porPagina),$filtrados->count(),$porPagina,$pagina);
        return view('livewire.admin.gestion-curso',[
            'cursos'=>$cursos,'resumen'=>$resumen,'gestion'=>$this->gestion,'gestiones'=>$this->gestiones,
            'trayectoria'=>app(ConsultaCursosInstitucionales::class)->trayectoria(),
            'paralelos'=>DB::table('paralelo')->orderBy('nom_par')->get(),'turnos'=>DB::table('turno')->orderBy('nom_tur')->get(),
            'turnosNuevos'=>$this->turnosDisponiblesParaCrear(),
            'control'=>$this->modalFormulario ? $this->control : null,
        ]);
    }
    private function autorizar(): void
    {
        \App\Support\AccesoGestionCursos::autorizar();
    }

    public function getGestionProperty(): ?object
    {
        return $this->gestiones->firstWhere('cod_gea', $this->gestionFiltro);
    }

    public function getResumenProperty()
    {
        return $this->gestion ? app(ConsultaCursosInstitucionales::class)->resumen($this->gestionFiltro) : collect();
    }

    public function updated($campo): void
    {
        if ($campo === 'formClaseHorario.cod_doc') $this->formClaseHorario['motivo_especialidad']='';
        if ($campo === 'formClaseHorario.modalidad_aula') {
            $this->formClaseHorario['aul_hor']=($this->formClaseHorario['modalidad_aula']==='curso') ? ($this->claseContexto['curso']??'').' · '.($this->claseContexto['paralelo']??'') : '';
        }
        if (in_array($campo, ['respaldoPdf','entradaCurso','gestionFiltro'])) {
            $this->pdfEscaneado = '';
            $this->datosEscaneados = [];
        }
        if ($campo === 'numeroResolucion') $this->validateOnly('numeroResolucion', ['numeroResolucion'=>['required','string','min:3','max:120','regex:/^[\p{L}\p{N}][\p{L}\p{N} .\/\-]*\d[\p{L}\p{N} .\/\-]*$/u']], ['numeroResolucion.regex'=>'Usa el número oficial, por ejemplo DDE-123/2026. No se aceptan letras sin referencia numérica.']);
        if (in_array($campo, ['search','gestionFiltro','estado','nivel','filtroHorario','filtroPlanAsignatura','filtroPlanEspecialidad','filtroParalelos','filtroTurnos','ordenar','perPage']) || str_starts_with($campo, 'filtroParalelos.') || str_starts_with($campo, 'filtroTurnos.')) {
            $this->resetPage();
            if ($campo === 'gestionFiltro') {
                $this->modalDetalle = false;
                $this->modalFormulario = false;
                $this->cursoDetalle = null;
                $this->eventosHorario = [];
            }
        }
        if (in_array($campo, ['entradaCurso','normaAdicional','numeroResolucion','fechaResolucion','respaldoPdf','causaInstitucional','autoridadEmisora','motivo','modalidadParalelos','turnoNuevo','paralelosNuevos']) || str_starts_with($campo, 'paralelosNuevos.')) {
            $this->analisisDocumento = [];
            $this->autenticidadConfirmada = false;
            $this->confirmarCambio = false;
            if($this->faseCurso===3)$this->faseCurso=2;
        }
    }

    public function consultarCurso(string $codigo, string $seccion = 'ficha'): void
    {
        $this->autorizar();
        abort_unless(in_array($seccion, ['ficha','horario','carga']), 422);
        $curso = $this->resumen->firstWhere('cod_cur', $codigo);
        abort_unless($curso, 404);
        $this->modalClaseHorario=false;
        $this->claseContexto=[];
        $this->cursoSeleccionado = $codigo;
        $this->cursoDetalle = $curso;
        $this->seccionDetalle = $seccion;
        $this->eventosHorario = [];
        $this->asignaciones = [];
        $this->horarioParalelo = $curso['paralelos'][0]['valor'] ?? '';
        if ($seccion === 'horario') $this->prepararPeriodosHorario();
        $this->cargarSeccion();
        $this->modalDetalle = true;
    }

    public function cambiarSeccion(string $seccion): void
    {
        $this->autorizar();
        abort_unless(in_array($seccion, ['ficha','horario','carga']), 422);
        $this->seccionDetalle = $seccion;
        if ($seccion === 'horario') $this->prepararPeriodosHorario();
        $this->cargarSeccion();
    }

    private function cargarSeccion(): void
    {
        $this->autorizar();
        if (!$this->cursoSeleccionado || !$this->gestion) return;
        $consulta = app(ConsultaCursosInstitucionales::class);
        if ($this->seccionDetalle === 'horario') {
            abort_unless(collect($this->cursoDetalle['paralelos'] ?? [])->pluck('valor')->contains($this->horarioParalelo) || $this->horarioParalelo === '', 422);
            abort_unless(collect($this->periodosHorario)->contains('valor',$this->periodoHorario) || !$this->periodosHorario,422);
            $this->bloquesHorario=$this->periodoHorario?DB::table('horario_bloque as b')->join('horario as h','h.cod_pho','=','b.cod_pho')->where('h.cod_hor',$this->periodoHorario)->where('b.est_hbl','ACTIVO')->where('b.tip_hbl','CLASE')->orderBy('b.num_hbl')->get(['b.cod_hbl','b.num_hbl','b.hor_ini_hbl','b.hor_fin_hbl'])->map(fn($b)=>['codigo'=>$b->cod_hbl,'numero'=>$b->num_hbl,'inicio'=>substr($b->hor_ini_hbl,0,5),'fin'=>substr($b->hor_fin_hbl,0,5)])->all():[];
            $this->eventosHorario = $this->periodoHorario ? $consulta->horario($this->cursoSeleccionado, $this->gestionFiltro, $this->horarioParalelo, $this->periodoHorario) : [];
        } elseif (in_array($this->seccionDetalle, ['ficha', 'carga'], true)) {
            $this->asignaciones = $consulta->materias($this->cursoSeleccionado, $this->gestionFiltro);
        }
    }

    public function getControlProperty(): array
    {
        $gestion = $this->gestion;
        $bloqueos = [];
        if (!$gestion || !in_array($gestion->est_gea, ['ACTIVO','PLANIFICADA'])) $bloqueos[] = 'Esta gestión es histórica. Conservamos sus cursos y horarios para consulta.';
        if ($gestion && now()->startOfDay()->gte(Carbon::parse($gestion->fii_gea)->startOfDay())) $bloqueos[] = 'Las clases de esta gestión ya comenzaron. Los cambios de grados se preparan antes del inicio del ciclo.';
        if ($gestion && DB::table('calificacion as n')->join('inscripcion_estudiante as i','i.cod_ins','=','n.cod_ins')->where('i.cod_gea',$gestion->cod_gea)->where('n.est_cal','<>','ANULADA')->exists()) $bloqueos[] = 'Ya existen notas en esta gestión. No se puede cambiar la estructura de grados.';
        return ['puede' => !$bloqueos, 'bloqueos'=>$bloqueos, 'fuente'=>RespaldoCursoInstitucional::FUENTE];
    }

    public function abrirFormularioCurso(string $operacion = 'crear', ?string $codigo = null): void
    {
        $this->autorizar();
        abort_unless(in_array($operacion, ['crear','editar','desactivar','reactivar']),422);
        $this->cerrarModalClaseHorario();
        $this->modalDetalle = false;
        $this->resetValidation();
        $this->reset('entradaCurso','interpretacion','motivo','causaInstitucional','autoridadEmisora','verificacionAutoridad','numeroResolucion','fechaResolucion','respaldoPdf','autenticidadConfirmada','confirmarCambio','analisisDocumento','paralelosNuevos','turnoNuevo','modalidadParalelos');
        $this->reset('faseCurso','rechazoDocumentoRegistrado','normaAdicional','pdfEscaneado','datosEscaneados');
        $this->operacion = $operacion;
        $this->cursoCambio = $codigo;
        if ($operacion !== 'crear') {
            $curso = Curso::findOrFail($codigo);
            $this->entradaCurso = $curso->nom_cur;
            $this->interpretacion = CursoInteligente::desdeOrden((int)$curso->ord_cur);
        }
        $this->modalFormulario = true;
    }

    public function interpretarCurso(): void
    {
        $this->autorizar();
        $this->interpretacion = CursoInteligente::interpretar($this->entradaCurso);
        $this->pdfEscaneado = '';
        $this->datosEscaneados = [];
        $this->analisisDocumento = [];
        $this->autenticidadConfirmada = false;
    }

    public function elegirGrado(int $grado): void
    {
        $this->autorizar();
        $this->interpretacion = CursoInteligente::desdeOrden($grado);
        $this->entradaCurso = $this->interpretacion['nombre'];
        $this->pdfEscaneado = '';
        $this->datosEscaneados = [];
        $this->analisisDocumento = [];
        $this->autenticidadConfirmada = false;
    }

    private function validarRespaldo(bool $comprobarAutoridad=true): void
    {
        if (!$this->pdfEscaneado || !$this->respaldoPdf || $this->pdfEscaneado !== hash_file('sha256', $this->respaldoPdf->getRealPath())) throw ValidationException::withMessages(['respaldoPdf'=>'Primero escanea un PDF correcto para habilitar los datos del respaldo.']);
        $this->validate([
            'motivo'=>['required','string','min:15','max:1500'],
            'causaInstitucional'=>['required',Rule::in(['demanda','infraestructura','reorganizacion','rectificacion','otro'])],
            'autoridadEmisora'=>['required','string','min:8','max:200'],
            'verificacionAutoridad'=>[$comprobarAutoridad?'required':'nullable','string','min:15','max:500'],
            'numeroResolucion'=>['required','string','min:3','max:120','regex:/^[\p{L}\p{N}][\p{L}\p{N} .\/\-]*\d[\p{L}\p{N} .\/\-]*$/u'],
            'fechaResolucion'=>['required','date','before_or_equal:'.now('America/La_Paz')->toDateString()],
            'respaldoPdf'=>['required','file','mimes:pdf','mimetypes:application/pdf','max:8192'],
        ], ['motivo.min'=>'Explica el cambio en al menos 15 caracteres. Este motivo quedará en la bitácora.',
            'respaldoPdf.required'=>'Adjunta la autorización en PDF.', 'respaldoPdf.max'=>'El PDF admite hasta 8 MB.',
            'fechaResolucion.before_or_equal'=>'La fecha de emisión no puede ser futura.',
            'numeroResolucion.regex'=>'Usa una referencia con número, por ejemplo DDE-123/2026, tal como figura en el PDF.',
            '*.required'=>'Completa este dato para revisar el respaldo.']);
        if (!\App\Support\Academico\ExpedienteParaleloInstitucional::justificacionComprensible($this->motivo)) throw ValidationException::withMessages(['motivo'=>'Explica la necesidad en al menos cinco palabras comprensibles. Ejemplo: Nueva infraestructura habilitada para ampliar la oferta educativa.']);
        if (!($this->interpretacion['valido'] ?? false)) throw ValidationException::withMessages(['entradaCurso'=>'Escribe un grado de secundaria reconocido. Los grados adicionales requieren autorización expresa.']);
        if($this->interpretacion['extraordinario']??false)$this->validate(['normaAdicional'=>'required|string|min:5|max:150'],['normaAdicional.required'=>'Identifica la norma que habilita la ampliación del grado. Debe aparecer en el respaldo.']);
    }

    public function escanearRespaldo(): void
    {
        $this->autorizar();
        abort_unless($this->modalFormulario && $this->faseCurso === 2, 422);
        $this->resetValidation();
        $this->pdfEscaneado = '';
        $this->datosEscaneados = [];
        $this->autenticidadConfirmada = false;
        $this->confirmarCambio = false;
        $this->analisisDocumento = [];
        $this->validate(['respaldoPdf'=>['required','file','mimes:pdf','max:8192']], ['respaldoPdf.required'=>'Adjunta la autorización en PDF antes de escanear.']);
        $this->interpretacion = CursoInteligente::interpretar($this->entradaCurso);
        abort_unless($this->interpretacion['valido'] ?? false, 422);
        $servicio = app(RespaldoCursoInstitucional::class);
        $lectura = $servicio->leer($this->respaldoPdf->getRealPath());
        $datos = \App\Support\Academico\ExpedienteParaleloInstitucional::detectar($lectura['texto']);
        preg_match_all('/\bnorma\s*(?:n(?:ro|umero|o)?[.°º\s]*)?([0-9]+\s*\/\s*20[0-9]{2})/iu', $lectura['texto'], $normas);
        $normas = array_values(array_unique($normas[1] ?? []));
        $norma = count($normas) === 1 ? $normas[0] : '';
        $revision = $servicio->analizar($lectura, $datos['numero'], $datos['fecha'] ?: '1900-01-01', (int)$this->interpretacion['orden'], $this->operacion, (int)$this->gestion->ani_gea, $norma);
        $revision['reglas'] += ['Número de resolución identificable y único'=>$datos['numero'] !== '',
            'Fecha de emisión identificable y única'=>$datos['fecha'] !== '',
            'Autoridad emisora identificable y única'=>$datos['autoridad'] !== '',
            'Resolución administrativa identificada'=>$datos['tipo'] === 'resolucion',
            'Fecha no futura'=>$datos['fecha'] !== '' && $datos['fecha'] <= now('America/La_Paz')->toDateString(),
            'Gestión detectada coincide'=>$datos['gestion'] === (string)$this->gestion->ani_gea];
        $revision['coherente'] = !in_array(false, $revision['reglas'], true);
        $this->analisisDocumento = $revision;
        if (!$revision['coherente']) {
            $this->datosEscaneados = $datos;
            $this->registrarRechazoDocumento();
            $this->addError('respaldoPdf','El documento no supera la revisión. Corrige las observaciones o adjunta otro PDF; el formulario sigue bloqueado y tus datos se conservan.');
            return;
        }
        $this->numeroResolucion = $datos['numero'];
        $this->fechaResolucion = $datos['fecha'];
        $this->autoridadEmisora = $datos['autoridad'];
        if ($this->interpretacion['extraordinario'] ?? false) $this->normaAdicional = $norma;
        $this->datosEscaneados = $datos;
        $this->pdfEscaneado = $lectura['sha256'];
        $this->analisisDocumento['contexto'] = $this->contextoDocumento();
    }

    public function analizarRespaldo(): void
    {
        $this->autorizar();
        $this->interpretacion = CursoInteligente::interpretar($this->entradaCurso);
        $this->validarRespaldo(false);

        $servicio = app(RespaldoCursoInstitucional::class);
        $lectura = $servicio->leer($this->respaldoPdf->getRealPath());
        $this->analisisDocumento = $servicio->analizar($lectura,$this->numeroResolucion,$this->fechaResolucion,(int)$this->interpretacion['orden'],$this->operacion,(int)$this->gestion->ani_gea,$this->normaAdicional,$this->alcanceDocumento());
        $this->analisisDocumento['contexto'] = $this->contextoDocumento();
        $this->autenticidadConfirmada = false;
        $this->confirmarCambio = false;
        if(!$this->analisisDocumento['coherente'])$this->registrarRechazoDocumento();
    }

    private function registrarRechazoDocumento(): void
    {
        $huella=hash('sha256',$this->analisisDocumento['sha256'].$this->contextoDocumento());
        if($this->rechazoDocumentoRegistrado===$huella)return;
        if(!Schema::hasTable('bitacora'))throw ValidationException::withMessages(['respaldoPdf'=>'No pudimos registrar la revisión en bitácora. El cambio sigue bloqueado. Solicita revisar el registro institucional.']);
        $ruta=$this->respaldoPdf->store('respaldos/cursos/rechazados','local');
        if(!$ruta)throw ValidationException::withMessages(['respaldoPdf'=>'No pudimos conservar el respaldo rechazado. El cambio permanece bloqueado.']);
        try{
            BitacoraService::registrar(accion:'INTENTO_'.mb_strtoupper($this->operacion).'_CURSO_PDF_RECHAZADO',tabla:'curso',registro:$this->cursoCambio,modulo:'Gestión de Cursos',nombreRegistro:$this->entradaCurso,
                descripcion:'El PDF leído no cumple las coincidencias del cambio solicitado. No se creó ni modificó el grado ni sus paralelos.',nivel:'WARNING',resultado:'BLOQUEADO',
                valoresNuevos:['solicitud'=>['grado'=>$this->entradaCurso,'gestion'=>$this->gestionFiltro,'operacion'=>$this->operacion,'motivo'=>$this->motivo,'paralelos'=>$this->paralelosNuevos,'turno'=>$this->turnoNuevo],
                    'documento'=>['ruta'=>$ruta,'nombre'=>$this->respaldoPdf->getClientOriginalName(),'numero'=>$this->numeroResolucion,'fecha'=>$this->fechaResolucion,'detectados'=>$this->datosEscaneados,'sha256'=>$this->analisisDocumento['sha256']], 'revision'=>$this->analisisDocumento['reglas']]);
            $this->rechazoDocumentoRegistrado=$huella;
        }catch(\Throwable $e){Storage::disk('local')->delete($ruta);report($e);throw ValidationException::withMessages(['respaldoPdf'=>'No pudimos conservar el intento en bitácora. No se guardó ningún cambio; vuelve a solicitar la revisión.']);}
    }

    public function continuarFaseCurso(): void
    {
        $this->autorizar();abort_unless($this->modalFormulario,422);$this->resetValidation();
        if($this->faseCurso===1){
            $this->interpretacion=CursoInteligente::interpretar($this->entradaCurso);
            if(!($this->interpretacion['valido']??false))throw ValidationException::withMessages(['entradaCurso'=>'Escribe un grado reconocido para continuar con su autorización.']);
            if(in_array($this->operacion,['crear','editar'])&&Curso::where('ord_cur',$this->interpretacion['orden'])->when($this->cursoCambio,fn($q)=>$q->where('cod_cur','<>',$this->cursoCambio))->exists())throw ValidationException::withMessages(['entradaCurso'=>'Este grado ya existe. Para otro grupo utiliza Registrar paralelo.']);

            $this->faseCurso=2;return;
        }
        if($this->faseCurso===2){
            $this->analizarRespaldo();
            if(!$this->analisisDocumento['coherente']){$this->addError('respaldoPdf','El PDF leído fue rechazado y el intento quedó en bitácora. Revisa las observaciones o adjunta el respaldo correcto.');return;}
            $this->faseCurso=3;
        }
    }

    public function volverFaseCurso(): void
    {
        $this->autorizar();$this->faseCurso=max(1,$this->faseCurso-1);$this->confirmarCambio=false;$this->autenticidadConfirmada=false;$this->resetValidation();
    }

    private function validarGruposNuevos(): void
    {
        $this->validate(['modalidadParalelos'=>['required',Rule::in(['uno','elegidos'])], 'paralelosNuevos'=>['required','array','min:1','max:3'], 'paralelosNuevos.*'=>['required','distinct',Rule::exists('paralelo','cod_par')->where('est_par','ACTIVO')], 'turnoNuevo'=>['required',Rule::exists('turno','cod_tur')->where('est_tur','ACTIVO')]],['paralelosNuevos.required'=>'Selecciona los paralelos autorizados.','turnoNuevo.required'=>'Elige el turno de los grupos nuevos.']);
        if($this->modalidadParalelos==='uno'&&count($this->paralelosNuevos)!==1)throw ValidationException::withMessages(['paralelosNuevos'=>'Elige un solo paralelo o cambia a varios paralelos.']);
        if(!$this->turnosDisponiblesParaCrear()->contains('cod_tur',$this->turnoNuevo))throw ValidationException::withMessages(['turnoNuevo'=>'Este turno aún no está incorporado para nuevos grupos. Primero debe habilitarse con su autorización institucional.']);
    }

    private function turnosDisponiblesParaCrear(): Collection
    {
        // La disponibilidad operativa se configura por institución; el catálogo histórico no habilita un turno nuevo.
        return DB::table('turno')->where('est_tur','ACTIVO')->get()->filter(fn($t)=>in_array(CursoInteligente::normalizar($t->nom_tur),config('academico.turnos_habilitados',['manana']),true))->values();
    }

    private function alcanceDocumento(): array
    {
        return [];
    }

    private function contextoDocumento(): string
    {
        return hash('sha256',json_encode([$this->gestionFiltro,$this->numeroResolucion,$this->fechaResolucion,$this->entradaCurso,$this->normaAdicional,$this->operacion,$this->causaInstitucional,$this->autoridadEmisora,$this->motivo,$this->modalidadParalelos,$this->paralelosNuevos,$this->turnoNuevo]));
    }

    public function guardarCambioCurso(): void
    {
        $this->autorizar();
        abort_unless($this->modalFormulario&&$this->faseCurso===3,422);
        $this->interpretacion = CursoInteligente::interpretar($this->entradaCurso);
        $this->validarRespaldo();
        $this->validate(['autenticidadConfirmada'=>['accepted'],'confirmarCambio'=>['accepted']],[
            'autenticidadConfirmada.accepted'=>'Verifica la autorización con la autoridad emisora antes de confirmar.',
            'confirmarCambio.accepted'=>'Confirma que deseas aplicar este cambio y registrar su motivo.',
        ]);
        if (!($this->analisisDocumento['coherente'] ?? false) || ($this->analisisDocumento['contexto'] ?? '') !== $this->contextoDocumento() || ($this->analisisDocumento['sha256'] ?? '') !== hash_file('sha256',$this->respaldoPdf->getRealPath())) throw ValidationException::withMessages(['respaldoPdf'=>'Revisa este respaldo con los datos actuales antes de guardar.']);
        $servicio = app(RespaldoCursoInstitucional::class);
        $revisionFinal = $servicio->analizar($servicio->leer($this->respaldoPdf->getRealPath()), $this->numeroResolucion, $this->fechaResolucion, (int)$this->interpretacion['orden'], $this->operacion, (int)$this->gestion->ani_gea,$this->normaAdicional,$this->alcanceDocumento());
        if (!$revisionFinal['coherente']) throw ValidationException::withMessages(['respaldoPdf'=>'La revisión final detectó omisiones en el respaldo. Los datos se conservan.']);
        $ruta = null;
        try {
            DB::transaction(function () use (&$ruta) {
                if(!Schema::hasTable('bitacora'))throw ValidationException::withMessages(['cambio'=>'La bitácora no está disponible. Conservamos los datos sin aplicar el cambio.']);
                // Serializa los cambios de estructura y verifica nuevamente la gestión en el servidor.
                DB::table('gestion_academica')->where('cod_gea',$this->gestionFiltro)->lockForUpdate()->first();
                unset($this->gestion,$this->gestiones,$this->control);
                if (!$this->control['puede']) throw ValidationException::withMessages(['cambio'=>implode(' ',$this->control['bloqueos'])]);
                $grado = (int)$this->interpretacion['orden'];
                $curso = $this->operacion === 'crear' ? new Curso : Curso::whereKey($this->cursoCambio)->lockForUpdate()->firstOrFail();
                $antes = $curso->exists ? $curso->getAttributes() : null;
                if ($this->operacion === 'crear' || $this->operacion === 'editar') {
                    if (Curso::where('ord_cur',$grado)->when($curso->exists,fn($q)=>$q->where('cod_cur','<>',$curso->cod_cur))->exists()) throw ValidationException::withMessages(['entradaCurso'=>'Este grado ya está registrado. Para otro grupo se crea un paralelo, sin duplicar el grado.']);
                    $curso->nom_cur = $this->interpretacion['nombre'];
                    $curso->ord_cur = $grado;
                    $curso->niv_cur = 'Secundaria';
                    if (!$curso->exists) $curso->est_cur = 'ACTIVO';
                } else {
                    if ($this->operacion === 'desactivar' && DB::table('inscripcion_estudiante')->where('cod_cur',$curso->cod_cur)->where('cod_gea',$this->gestionFiltro)->where('est_ins','ACTIVA')->exists()) throw ValidationException::withMessages(['cambio'=>'Este grado tiene estudiantes inscritos. Debes resolver sus trayectorias antes de desactivarlo.']);
                    $curso->est_cur = $this->operacion === 'reactivar' ? 'ACTIVO' : 'INACTIVO';
                }
                $ruta = $this->respaldoPdf->store('respaldos/cursos','local');
                if(!$ruta)throw new \RuntimeException('No se pudo conservar el respaldo institucional.');
                $curso->save();
                BitacoraService::registrar(accion:mb_strtoupper($this->operacion).'_CURSO',tabla:'curso',registro:$curso->cod_cur,modulo:'Gestión de Cursos',nombreRegistro:$curso->nom_cur,
                    descripcion:trim($this->motivo),nivel:'INFO',resultado:'EXITOSO',valoresAnteriores:$antes,
                    valoresNuevos:['curso'=>$curso->getAttributes(),'gestion'=>$this->gestionFiltro,'alcance'=>'Catálogo del grado; no crea grupos ni clases.','respaldo'=>['ruta'=>$ruta,'nombre'=>$this->respaldoPdf->getClientOriginalName(),'numero'=>$this->numeroResolucion,'fecha'=>$this->fechaResolucion,'norma_ampliacion'=>$this->normaAdicional,'causa'=>$this->causaInstitucional,'autoridad'=>$this->autoridadEmisora,'verificacion'=>$this->verificacionAutoridad,'sha256'=>$this->analisisDocumento['sha256'],'revision'=>$this->analisisDocumento['reglas'],'autenticidad_confirmada_por'=>auth()->id()]]);
            });
        } catch (\Throwable $e) {
            if ($ruta) Storage::disk('local')->delete($ruta);
            if ($e instanceof ValidationException) throw $e;
            report($e);
            $this->addError('cambio','No pudimos completar el cambio. Conservamos los datos y el historial; vuelve a intentarlo.');
            return;
        }
        $this->modalFormulario = false;
        $this->modalDetalle = false;
        $this->respaldoPdf = null;
        unset($this->resumen);
        $this->dispatch('success-general',mensaje:'El cambio y su respaldo quedaron registrados en la bitácora.');
    }
    public function crearClaseDesdeBloque(string $dia,string $bloque): void
    {
        $this->autorizar();
        abort_unless(!$this->modalFormulario&&$this->modalDetalle&&$this->seccionDetalle==='horario'&&$this->cursoSeleccionado,422);
        $servicio=app(PlanificacionClaseInteligente::class);
        $ctx=$servicio->contexto($this->periodoHorario,$bloque,$dia);
        if(!$ctx['horario']||!$ctx['bloque'])throw ValidationException::withMessages(['clase'=>implode(' ',$ctx['bloqueos'])]);
        abort_unless($ctx['horario']['cod_cur']===$this->cursoSeleccionado&&$ctx['horario']['cod_gea']===$this->gestionFiltro&&$ctx['horario']['cod_par']===$this->horarioParalelo,422);
        $this->resetValidation();
        $this->claseContexto=['cod_hor'=>$this->periodoHorario,'cod_hbl'=>$bloque,'dia_hor'=>$dia,'num_blo_hor'=>$ctx['bloque']['num_hbl'],'hor_ini_hor'=>substr($ctx['bloque']['hor_ini_hbl'],0,5),'hor_fin_hor'=>substr($ctx['bloque']['hor_fin_hbl'],0,5),'curso'=>$ctx['horario']['nom_cur'],'orden'=>(int)$ctx['horario']['ord_cur'],'paralelo'=>$ctx['horario']['nom_par'],'turno'=>$ctx['horario']['nom_tur'],'periodo'=>$ctx['horario']['fii_hor'].' — '.$ctx['horario']['ffi_hor']];
        $this->formClaseHorario=['tipo_plan'=>'MATERIA','cod_mat'=>'','cod_esp'=>'','cod_doc'=>'','carga_horaria'=>1,'aul_hor'=>$ctx['horario']['nom_cur'].' · '.$ctx['horario']['nom_par'],'modalidad_aula'=>'curso','motivo_especialidad'=>'','obs_hor'=>'','est_hor'=>'ACTIVO','confirmar_plan'=>false];
        $this->opcionesClase=$servicio->opciones($this->periodoHorario);
        $this->verOtrosDocentesClase=false;
        $this->modalClaseHorario=true;
    }

    private function validarAmbitoClase(): void
    {
        $h=DB::table('horario as h')->join('grupo_academico as g','g.cod_gac','=','h.cod_gac')->where('h.cod_hor',$this->claseContexto['cod_hor'])->first(['g.cod_cur','g.cod_gea','g.cod_par']);
        abort_unless($h&&$h->cod_cur===$this->cursoSeleccionado&&$h->cod_gea===$this->gestionFiltro&&$h->cod_par===$this->horarioParalelo&&$this->periodoHorario===$this->claseContexto['cod_hor'],422);
    }

    public function elegirMateriaClase(string $codigo,string $tipo='MATERIA'): void
    {
        $this->autorizar();abort_unless($this->modalClaseHorario&&in_array($tipo,['MATERIA','ESPECIALIDAD'],true),422);
        $opcion=collect($this->opcionesClase[$tipo==='MATERIA'?'materias':'especialidades']??[])->firstWhere('valor',$codigo);
        abort_unless($opcion,422);
        $this->formClaseHorario['tipo_plan']=$tipo;
        $this->formClaseHorario['cod_mat']=$tipo==='MATERIA'?$codigo:'';
        $this->formClaseHorario['cod_esp']=$tipo==='ESPECIALIDAD'?$codigo:'';
        $compatibles=app(PlanificacionClaseInteligente::class)->docentesDelArea($this->opcionesClase['docentes']??[], $opcion['etiqueta'], $tipo==='ESPECIALIDAD');
        $planificados=array_values(array_intersect($opcion['docentes'],array_column($compatibles,'valor')));
        $this->formClaseHorario['cod_doc']=count($planificados)===1?$planificados[0]:'';
        $this->verOtrosDocentesClase=false;
        $this->formClaseHorario['carga_horaria']=(int)($opcion['horas']??1);
        $this->formClaseHorario['confirmar_plan']=false;
        $this->formClaseHorario['motivo_especialidad']='';
    }

    public function alternarOtrosDocentesClase(): void
    {
        $this->autorizar();
        abort_unless($this->modalClaseHorario && !$this->modalFormulario,422);
        $this->verOtrosDocentesClase=!$this->verOtrosDocentesClase;
        if(!$this->verOtrosDocentesClase){
            $tipo=$this->formClaseHorario['tipo_plan'];
            $materia=collect($this->opcionesClase[$tipo==='MATERIA'?'materias':'especialidades']??[])->firstWhere('valor',$this->formClaseHorario[$tipo==='MATERIA'?'cod_mat':'cod_esp']??'');
            $compatibles=app(PlanificacionClaseInteligente::class)->docentesDelArea($this->opcionesClase['docentes']??[], $materia['etiqueta']??'', $tipo==='ESPECIALIDAD');
            if(!in_array($this->formClaseHorario['cod_doc'],array_column($compatibles,'valor'),true)){
                $this->formClaseHorario['cod_doc']='';
                $this->formClaseHorario['motivo_especialidad']='';
            }
        }
    }
}
