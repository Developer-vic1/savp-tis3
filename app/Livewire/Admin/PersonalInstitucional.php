<?php

namespace App\Livewire\Admin;

use App\Models\Oficial\Academico\Asignatura;
use App\Models\Oficial\Academico\Curso;
use App\Models\Oficial\Academico\Docente;
use App\Models\Oficial\Academico\EspecialidadTecnica;
use App\Models\Oficial\Academico\GestionAcademica;
use App\Models\Oficial\Academico\Paralelo;
use App\Models\Oficial\Academico\Persona;
use App\Models\Oficial\Academico\PlanAsignatura;
use App\Models\Oficial\Academico\PlanEspecialidad;
use App\Models\Oficial\Academico\Turno;
use App\Services\BitacoraService;
use App\Services\PlanAcademicoService;
use App\Support\Comunidad\DocenteInteligente;
use App\Support\Comunidad\HorarioPersonal;
use App\Support\Comunidad\IndicadoresPersonal;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class PersonalInstitucional extends Component
{
    use WithPagination, \App\Livewire\Admin\Concerns\SeleccionaEspecialidadDocente;

    protected $paginationTheme = 'tailwind';

    /*
    |--------------------------------------------------------------------------
    | Filtros principales
    |--------------------------------------------------------------------------
    */
    public string $search = '';

    public string $estado = '';

    public string $carga = '';

    public string $tipoCargaFiltro = '';

    public int $perPage = 10;

    public string $cargoFiltro = '';

    public string $vinculacionFiltro = '';

    public string $materiaFiltro = '';

    public string $especialidadFiltro = '';

    public string $cursoFiltro = '';

    public string $contactoFiltro = '';

    public string $orden = 'APELLIDO_AZ';

    public function updated(string $propiedad): void
    {
        if (in_array($propiedad, ['materiaFiltro', 'especialidadFiltro', 'cursoFiltro', 'contactoFiltro', 'orden'], true)) {
            $this->resetPage();
        }
    }

    public bool $modalPersonal = false;

    public string $seccionFicha = 'institucion';

    public array $horarioPersonal = [];

    public ?\App\Models\Oficial\Academico\PersonalInstitucional $personalDetalle = null;

    public function updatedCargoFiltro(): void
    {
        $this->resetPage();
    }

    public function updatedVinculacionFiltro(): void
    {
        $this->resetPage();
    }

    public function abrirFichaPersonal(string $identificador, string $seccion = 'institucion'): void
    {
        $this->cerrarTodosLosModales();
        $this->resetValidation();
        $relaciones = ['persona.usuario.roles', 'docente.planAsignaturas.asignatura', 'docente.planAsignaturas.curso', 'docente.planAsignaturas.paralelo', 'docente.planEspecialidades.especialidad', 'docente.planEspecialidades.curso', 'docente.planEspecialidades.paralelo'];
        if (Schema::hasTable('vinculo_personal')) {
            $relaciones[] = 'vinculoPersonalRegistros.cargoInstitucional';
        }
        if (Schema::hasTable('documento_personal')) {
            $relaciones[] = 'documentoPersonalRegistros.tipoDocumentoPersonal';
        }
        $this->personalDetalle = \App\Models\Oficial\Academico\PersonalInstitucional::with($relaciones)->findOrFail($identificador);
        $this->seccionFicha = in_array($seccion, ['institucion', 'historial', 'carga'], true) && ($seccion !== 'carga' || $this->personalDetalle->docente) ? $seccion : 'institucion';
        $this->horarioPersonal = app(HorarioPersonal::class)->consultar($this->personalDetalle, $this->codGestionActual);
        $this->modalPersonal = true;
    }

    public function cerrarFichaPersonal(): void
    {
        $this->modalPersonal = false;
        $this->personalDetalle = null;
        $this->horarioPersonal = [];
    }

    /*
    |--------------------------------------------------------------------------
    | Control de modales
    |--------------------------------------------------------------------------
    */
    public bool $modalVer = false;

    public bool $modalAsignar = false;

    public bool $modalEditar = false;

    public ?Docente $docenteDetalle = null;

    /*
    |--------------------------------------------------------------------------
    | Reglas institucionales
    |--------------------------------------------------------------------------
    */
    public int $maxHorasDocente = 24;

    public int $maxModificaciones = 3;

    /*
    |--------------------------------------------------------------------------
    | Turnos y gestión automáticos
    |--------------------------------------------------------------------------
    | Materia curricular   => turno mañana
    | Especialidad técnica => turno tarde
    |--------------------------------------------------------------------------
    */
    public ?string $codTurnoManana = null;

    public ?string $codTurnoTarde = null;

    public ?string $codGestionActual = null;

    public string $nombreTurnoManana = 'Mañana';

    public string $nombreTurnoTarde = 'Tarde';

    public string $nombreGestionActual = 'Gestión activa no definida';

    /*
    |--------------------------------------------------------------------------
    | Formulario de asignación
    |--------------------------------------------------------------------------
    */
    public array $formAsignacion = [
        'tipo_carga' => 'MATERIA',
        'cod_doc' => '',
        'cod_asi' => '',
        'cod_esp' => '',
        'cod_cur' => '',
        'cod_par' => '',
        'cod_tur' => '',
        'cod_gea' => '',
        'hor_car' => '',
        'est_car' => 'ACTIVO',
        'fii_plan' => '',
        'ffi_plan' => '',
    ];

    /*
    |--------------------------------------------------------------------------
    | Formulario de edición
    |--------------------------------------------------------------------------
    */
    public array $formEditar = [
        'cod_doc' => '',
        'esp_doc' => '',
        'est_doc' => 'ACTIVO',
    ];

    /*
    |--------------------------------------------------------------------------
    | Ciclo de vida
    |--------------------------------------------------------------------------
    */
    public function mount(): void
    {
        $this->cargarConfiguracionAcademicaAutomatica();
    }

    /*
    |--------------------------------------------------------------------------
    | Validaciones
    |--------------------------------------------------------------------------
    */
    protected function rules(): array
    {
        return [
            'formAsignacion.fii_plan' => ['required', 'date_format:Y-m-d'],
            'formAsignacion.ffi_plan' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:formAsignacion.fii_plan'],
            'formAsignacion.tipo_carga' => [
                'required',
                Rule::in(['MATERIA', 'ESPECIALIDAD']),
            ],

            'formAsignacion.cod_doc' => [
                'required',
                'exists:docente,cod_doc',
            ],

            'formAsignacion.cod_asi' => [
                Rule::requiredIf(fn () => $this->formAsignacion['tipo_carga'] === 'MATERIA'),
                'nullable',
                'exists:asignatura,cod_asi',
            ],

            'formAsignacion.cod_esp' => [
                Rule::requiredIf(fn () => $this->formAsignacion['tipo_carga'] === 'ESPECIALIDAD'),
                'nullable',
                'exists:especialidad_tecnica,cod_esp',
            ],

            'formAsignacion.cod_cur' => [
                'required',
                'exists:curso,cod_cur',
            ],

            'formAsignacion.cod_par' => [
                'required',
                'exists:paralelo,cod_par',
            ],

            'formAsignacion.cod_tur' => [
                'required',
                'exists:turno,cod_tur',
            ],

            'formAsignacion.cod_gea' => [
                'required',
                'exists:gestion_academica,cod_gea',
            ],

            'formAsignacion.hor_car' => [
                'required',
                'integer',
                'min:1',
                'max:'.$this->maxHorasDocente,
            ],

            'formAsignacion.est_car' => [
                'required',
                Rule::in(['ACTIVO', 'INACTIVO']),
            ],
        ];
    }

    protected array $messages = [
        'formAsignacion.tipo_carga.required' => 'Selecciona el tipo de carga académica.',
        'formAsignacion.tipo_carga.in' => 'El tipo de carga seleccionado no es válido.',

        'formAsignacion.cod_doc.required' => 'No se pudo identificar al docente.',
        'formAsignacion.cod_doc.exists' => 'El docente seleccionado no existe.',

        'formAsignacion.cod_asi.required' => 'Selecciona una materia curricular.',
        'formAsignacion.cod_asi.exists' => 'La materia seleccionada no existe.',

        'formAsignacion.cod_esp.required' => 'Selecciona una especialidad técnica.',
        'formAsignacion.cod_esp.exists' => 'La especialidad técnica seleccionada no existe.',

        'formAsignacion.cod_cur.required' => 'Selecciona un curso.',
        'formAsignacion.cod_cur.exists' => 'El curso seleccionado no existe.',

        'formAsignacion.cod_par.required' => 'Selecciona un paralelo.',
        'formAsignacion.cod_par.exists' => 'El paralelo seleccionado no existe.',

        'formAsignacion.cod_tur.required' => 'No se pudo definir el turno automáticamente.',
        'formAsignacion.cod_tur.exists' => 'El turno definido no existe.',

        'formAsignacion.cod_gea.required' => 'No se pudo definir la gestión académica automáticamente.',
        'formAsignacion.cod_gea.exists' => 'La gestión académica definida no existe.',

        'formAsignacion.hor_car.required' => 'Ingresa las horas asignadas.',
        'formAsignacion.hor_car.integer' => 'Las horas deben ser un número entero.',
        'formAsignacion.hor_car.min' => 'Las horas deben ser mayores a cero.',
        'formAsignacion.hor_car.max' => 'No puedes asignar más horas que el límite total permitido.',

        'formAsignacion.est_car.required' => 'Selecciona el estado de la carga.',
        'formAsignacion.est_car.in' => 'El estado seleccionado no es válido.',

        'formEditar.esp_doc.required' => 'La especialidad profesional del docente es obligatoria.',
        'formEditar.esp_doc.min' => 'La especialidad debe tener al menos 3 caracteres.',
        'formEditar.esp_doc.max' => 'La especialidad no debe superar los 150 caracteres.',
        'formEditar.est_doc.required' => 'Selecciona el estado del docente.',
        'formEditar.est_doc.in' => 'El estado seleccionado no es válido.',
    ];

    /*
    |--------------------------------------------------------------------------
    | Reactividad de filtros
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

    public function updatingCarga(): void
    {
        $this->resetPage();
    }

    public function updatingTipoCargaFiltro(): void
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
            'carga',
            'tipoCargaFiltro',
            'cargoFiltro',
            'vinculacionFiltro',
            'materiaFiltro',
            'especialidadFiltro',
            'cursoFiltro',
            'contactoFiltro',
        ]);

        $this->resetPage();
    }

    /*
    |--------------------------------------------------------------------------
    | Configuración automática: turno y gestión
    |--------------------------------------------------------------------------
    */
    private function cargarConfiguracionAcademicaAutomatica(): void
    {
        $turnoManana = $this->obtenerTurnoPorNombre(['mañana', 'manana']);
        $turnoTarde = $this->obtenerTurnoPorNombre(['tarde']);
        $gestionActual = $this->obtenerGestionAcademicaPorDefecto();

        $this->codTurnoManana = $turnoManana?->cod_tur;
        $this->codTurnoTarde = $turnoTarde?->cod_tur;
        $this->codGestionActual = $gestionActual?->cod_gea;

        $this->nombreTurnoManana = $turnoManana?->nom_tur ?? 'Mañana no configurada';
        $this->nombreTurnoTarde = $turnoTarde?->nom_tur ?? 'Tarde no configurada';

        $this->nombreGestionActual = $gestionActual?->ani_gea
            ? 'Gestión '.$gestionActual->ani_gea
            : 'Gestión activa no definida';
    }

    private function obtenerTurnoPorNombre(array $nombres): ?Turno
    {
        return Turno::query()
            ->where('est_tur', 'ACTIVO')
            ->where(function ($query) use ($nombres) {
                foreach ($nombres as $nombre) {
                    $query->orWhere('nom_tur', 'ILIKE', '%'.$nombre.'%');
                }
            })
            ->orderBy('cod_tur')
            ->first();
    }

    private function obtenerGestionAcademicaPorDefecto(): ?GestionAcademica
    {
        $gestiones = GestionAcademica::query()
            ->whereIn('est_gea', ['ACTIVO', 'ACTIVA'])
            ->limit(2)->get();

        return $gestiones->count() === 1 ? $gestiones->first() : null;
    }

    private function prepararTurnoYGestionSegunTipoCarga(string $tipoCarga): void
    {
        $this->cargarConfiguracionAcademicaAutomatica();

        if ($tipoCarga === 'MATERIA') {
            $this->formAsignacion['cod_tur'] = $this->codTurnoManana ?? '';
        }

        if ($tipoCarga === 'ESPECIALIDAD') {
            $this->formAsignacion['cod_tur'] = $this->codTurnoTarde ?? '';
        }

        $this->formAsignacion['cod_gea'] = $this->codGestionActual ?? '';
    }

    public function updatedFormAsignacionTipoCarga(string $tipoCarga): void
    {
        if (! in_array($tipoCarga, ['MATERIA', 'ESPECIALIDAD'], true)) {
            $this->formAsignacion['tipo_carga'] = 'MATERIA';
            $tipoCarga = 'MATERIA';
        }

        if ($tipoCarga === 'MATERIA') {
            $this->formAsignacion['cod_esp'] = '';
        }

        if ($tipoCarga === 'ESPECIALIDAD') {
            $this->formAsignacion['cod_asi'] = '';
        }

        $this->prepararTurnoYGestionSegunTipoCarga($tipoCarga);
        $this->resetValidation();
    }

    /*
    |--------------------------------------------------------------------------
    | Utilidades de modales
    |--------------------------------------------------------------------------
    */
    private function cerrarTodosLosModales(): void
    {
        $this->modalPersonal = false;
        $this->personalDetalle = null;
        $this->horarioPersonal = [];
        $this->modalVer = false;
        $this->modalAsignar = false;
        $this->modalEditar = false;
    }

    private function resetFormAsignacion(): void
    {
        $this->formAsignacion = [
            'tipo_carga' => 'MATERIA',
            'cod_doc' => '',
            'cod_asi' => '',
            'cod_esp' => '',
            'cod_cur' => '',
            'cod_par' => '',
            'cod_tur' => '',
            'cod_gea' => '',
            'hor_car' => '',
            'est_car' => 'ACTIVO',
            'fii_plan' => '',
            'ffi_plan' => '',
        ];
    }

    private function resetFormEditar(): void
    {
        $this->formEditar = [
            'cod_doc' => '',
            'esp_doc' => '',
            'est_doc' => 'ACTIVO',
        ];
    }

    private function cargarDocenteDetalle(string $codDoc): Docente
    {
        return Docente::with([
            'personalInstitucional.persona.usuario',

            'planAsignaturas.asignatura',
            'planAsignaturas.curso',
            'planAsignaturas.paralelo',
            'planAsignaturas.turno',
            'planAsignaturas.gestionAcademica',

            'planEspecialidades.especialidad',
            'planEspecialidades.curso',
            'planEspecialidades.paralelo',
            'planEspecialidades.turno',
            'planEspecialidades.gestionAcademica',
        ])->findOrFail($codDoc);
    }

    /*
    |--------------------------------------------------------------------------
    | Modal ver
    |--------------------------------------------------------------------------
    */
    public function abrirModalVer(string $codDoc): void
    {
        $this->resetValidation();
        $this->cerrarTodosLosModales();

        $this->docenteDetalle = $this->cargarDocenteDetalle($codDoc);
        $this->modalVer = true;
    }

    public function cerrarModalVer(): void
    {
        $this->modalVer = false;
        $this->docenteDetalle = null;
        $this->resetValidation();
    }

    /*
    |--------------------------------------------------------------------------
    | Modal asignar carga
    |--------------------------------------------------------------------------
    */
    public function abrirModalAsignar(string $codDoc): void
    {
        $this->resetValidation();
        $this->cerrarTodosLosModales();

        $docente = $this->cargarDocenteDetalle($codDoc);

        if ($docente->personalInstitucional?->persona?->usuario?->est_usu !== 'ACTIVO') {
            $this->docenteDetalle = null;
            $this->dispatch('error-general', mensaje: 'La cuenta vinculada no tiene acceso activo. Se conservan las asignaciones y la trayectoria del personal.');

            return;
        }

        if ($docente->est_doc !== 'ACTIVO') {
            $this->docenteDetalle = null;
            $this->dispatch('error-general', mensaje: 'No puedes asignar carga académica a un docente inactivo.');

            return;
        }

        $horasActuales = $this->obtenerHorasTotalesDocente($docente->cod_doc);

        if ($horasActuales >= $this->maxHorasDocente) {
            $this->docenteDetalle = null;
            $this->dispatch('error-general', mensaje: 'El docente ya alcanzó la carga máxima permitida.');

            return;
        }

        $this->resetFormAsignacion();

        $this->formAsignacion['cod_doc'] = $docente->cod_doc;

        $this->prepararTurnoYGestionSegunTipoCarga('MATERIA');

        if (! $this->codTurnoManana) {
            $this->docenteDetalle = null;
            $this->dispatch('error-general', mensaje: 'No existe un turno activo de mañana. Configura el catálogo de turnos.');

            return;
        }

        if (! $this->codTurnoTarde) {
            $this->docenteDetalle = null;
            $this->dispatch('error-general', mensaje: 'No existe un turno activo de tarde. Configura el catálogo de turnos.');

            return;
        }

        if (! $this->codGestionActual) {
            $this->docenteDetalle = null;
            $this->dispatch('error-general', mensaje: 'No existe una gestión académica activa. Configura la gestión actual.');

            return;
        }

        $this->docenteDetalle = $docente;
        $this->modalAsignar = true;
    }

    public function cerrarModalAsignar(): void
    {
        $this->modalAsignar = false;
        $this->docenteDetalle = null;
        $this->resetFormAsignacion();
        $this->resetValidation();
    }

    public function guardarAsignacion(): void
    {
        $this->validate();

        $tipoCarga = $this->formAsignacion['tipo_carga'];

        DB::transaction(function () use ($tipoCarga) {
            $docente = Docente::with('personalInstitucional.persona.usuario')
                ->lockForUpdate()
                ->findOrFail($this->formAsignacion['cod_doc']);

            if ($docente->personalInstitucional?->persona?->usuario?->est_usu !== 'ACTIVO') {
                $this->dispatch('error-general', mensaje: 'La cuenta vinculada no tiene acceso activo. No se registró la carga; sus asignaciones anteriores se conservan.');

                return;
            }

            if ($docente->est_doc !== 'ACTIVO') {
                $this->dispatch('error-general', mensaje: 'El docente está inactivo. No se puede registrar la carga académica.');

                return;
            }

            $this->prepararTurnoYGestionSegunTipoCarga($tipoCarga);

            if (! $this->formAsignacion['cod_tur'] || ! $this->formAsignacion['cod_gea']) {
                $this->dispatch('error-general', mensaje: 'No se pudo definir turno o gestión académica automáticamente.');

                return;
            }

            $horasActuales = $this->obtenerHorasTotalesDocente($docente->cod_doc);
            $nuevasHoras = (int) $this->formAsignacion['hor_car'];

            if (($horasActuales + $nuevasHoras) > $this->maxHorasDocente) {
                $this->dispatch(
                    'error-general',
                    mensaje: 'La asignación supera la carga máxima permitida de '.$this->maxHorasDocente.' horas. Actualmente tiene '.$horasActuales.' horas.'
                );

                return;
            }

            if ($tipoCarga === 'MATERIA') {
                $this->guardarAsignacionMateria($docente);

                return;
            }

            if ($tipoCarga === 'ESPECIALIDAD') {
                $this->guardarAsignacionEspecialidad($docente);

                return;
            }

            $this->dispatch('error-general', mensaje: 'Tipo de carga no reconocido.');
        });
    }

    private function guardarAsignacionMateria(Docente $docente): void
    {
        $existe = PlanAsignatura::where('cod_doc', $this->formAsignacion['cod_doc'])
            ->where('cod_asi', $this->formAsignacion['cod_asi'])
            ->where('fii_pas', $this->formAsignacion['fii_plan'])
            ->whereHas('grupoAcademico', fn ($grupo) => $grupo->where(collect($this->formAsignacion)->only(['cod_cur', 'cod_par', 'cod_tur', 'cod_gea'])->all()))
            ->exists();

        if ($existe) {
            $this->addError('formAsignacion.cod_asi', 'Esta materia ya fue asignada al docente en el mismo curso, paralelo, turno y gestión.');
            $this->dispatch('error-general', mensaje: 'La materia seleccionada ya fue asignada a este docente en el mismo curso, paralelo, turno y gestión.');

            return;
        }

        $plan = app(PlanAcademicoService::class)->guardar([
            'cod_doc' => $this->formAsignacion['cod_doc'],
            'cod_asi' => $this->formAsignacion['cod_asi'],
            'cod_cur' => $this->formAsignacion['cod_cur'],
            'cod_par' => $this->formAsignacion['cod_par'],
            'cod_tur' => $this->formAsignacion['cod_tur'],
            'cod_gea' => $this->formAsignacion['cod_gea'],
            'hor_pas' => (int) $this->formAsignacion['hor_car'],
            'est_pas' => $this->formAsignacion['est_car'],
            'fii_plan' => $this->formAsignacion['fii_plan'],
            'ffi_plan' => $this->formAsignacion['ffi_plan'],
        ]);

        $plan->load(['asignatura', 'curso', 'paralelo', 'turno', 'gestionAcademica']);

        $this->registrarBitacora(
            accion: 'ASIGNAR_MATERIA_MANANA',
            tabla: 'plan_asignatura',
            registro: $plan->cod_pas,
            nombreRegistro: $this->nombreDocente($docente),
            descripcion: 'Se asignó una materia curricular al docente en el turno de la mañana.',
            nivel: 'SUCCESS',
            resultado: 'EXITOSO',
            valoresNuevos: [
                'docente' => $docente->cod_doc,
                'nombre_docente' => $this->nombreDocente($docente),
                'materia' => $plan->asignatura->nom_asi ?? $plan->cod_asi,
                'curso' => $plan->curso->nom_cur ?? $plan->cod_cur,
                'paralelo' => $plan->paralelo->nom_par ?? $plan->cod_par,
                'turno' => $plan->turno->nom_tur ?? $plan->cod_tur,
                'gestion' => $plan->gestionAcademica->ani_gea ?? $plan->cod_gea,
                'horas' => $plan->hor_pas,
                'estado' => $plan->est_pas,
            ]
        );

        $this->cerrarModalAsignar();

        $this->dispatch('asignacion-creada');
        $this->dispatch('success-general', mensaje: 'Materia asignada correctamente al docente.');
    }

    private function guardarAsignacionEspecialidad(Docente $docente): void
    {
        $existe = PlanEspecialidad::where('cod_doc', $this->formAsignacion['cod_doc'])
            ->where('cod_esp', $this->formAsignacion['cod_esp'])
            ->where('fii_pes', $this->formAsignacion['fii_plan'])
            ->whereHas('grupoAcademico', fn ($grupo) => $grupo->where(collect($this->formAsignacion)->only(['cod_cur', 'cod_par', 'cod_tur', 'cod_gea'])->all()))
            ->exists();

        if ($existe) {
            $this->addError('formAsignacion.cod_esp', 'Esta especialidad ya fue asignada al docente en el mismo curso, paralelo, turno y gestión.');
            $this->dispatch('error-general', mensaje: 'La especialidad seleccionada ya fue asignada a este docente en el mismo curso, paralelo, turno y gestión.');

            return;
        }

        $plan = app(PlanAcademicoService::class)->guardar([
            'cod_doc' => $this->formAsignacion['cod_doc'],
            'cod_esp' => $this->formAsignacion['cod_esp'],
            'cod_cur' => $this->formAsignacion['cod_cur'],
            'cod_par' => $this->formAsignacion['cod_par'],
            'cod_tur' => $this->formAsignacion['cod_tur'],
            'cod_gea' => $this->formAsignacion['cod_gea'],
            'hor_pes' => (int) $this->formAsignacion['hor_car'],
            'est_pes' => $this->formAsignacion['est_car'],
            'fii_plan' => $this->formAsignacion['fii_plan'],
            'ffi_plan' => $this->formAsignacion['ffi_plan'],
        ], tecnico: true);

        $plan->load(['especialidad', 'curso', 'paralelo', 'turno', 'gestionAcademica']);

        $this->registrarBitacora(
            accion: 'ASIGNAR_ESPECIALIDAD_TARDE',
            tabla: 'plan_especialidad',
            registro: $plan->cod_pes,
            nombreRegistro: $this->nombreDocente($docente),
            descripcion: 'Se asignó una especialidad técnica al docente en el turno de la tarde.',
            nivel: 'SUCCESS',
            resultado: 'EXITOSO',
            valoresNuevos: [
                'docente' => $docente->cod_doc,
                'nombre_docente' => $this->nombreDocente($docente),
                'especialidad' => $plan->especialidad->nom_esp ?? $plan->cod_esp,
                'curso' => $plan->curso->nom_cur ?? $plan->cod_cur,
                'paralelo' => $plan->paralelo->nom_par ?? $plan->cod_par,
                'turno' => $plan->turno->nom_tur ?? $plan->cod_tur,
                'gestion' => $plan->gestionAcademica->ani_gea ?? $plan->cod_gea,
                'horas' => $plan->hor_pes,
                'estado' => $plan->est_pes,
            ]
        );

        $this->cerrarModalAsignar();

        $this->dispatch('asignacion-creada');
        $this->dispatch('success-general', mensaje: 'Especialidad técnica asignada correctamente al docente.');
    }

    /*
    |--------------------------------------------------------------------------
    | Modal editar docente
    |--------------------------------------------------------------------------
    */
    public function abrirModalEditar(string $codDoc): void
    {
        \Illuminate\Support\Facades\Gate::authorize('Personal_Institucional');
        $this->resetValidation();
        $this->cerrarTodosLosModales();

        $docente = $this->cargarDocenteDetalle($codDoc);

        if ((int) $docente->num_mod_doc >= $this->maxModificaciones) {
            $this->docenteDetalle = null;
            $this->dispatch('error-general', mensaje: 'Este docente alcanzó el límite de modificaciones permitidas.');

            return;
        }

        $this->formEditar = [
            'cod_doc' => $docente->cod_doc,
            'esp_doc' => $docente->esp_doc ?? '',
            'est_doc' => $docente->est_doc,
        ];

        $this->docenteDetalle = $docente;
        $this->prepararSeleccionEspecialidad($docente);
        $this->modalEditar = true;
    }

    public function cerrarModalEditar(): void
    {
        $this->modalEditar = false;
        $this->docenteDetalle = null;
        $this->resetFormEditar();
        $this->resetValidation();
    }

    public function actualizarDocente(): void
    {
        $this->guardarPerfilEspecialidad();
    }

    /*
    |--------------------------------------------------------------------------
    | Cambiar estado docente
    |--------------------------------------------------------------------------
    */
    public function cambiarEstado(string $codDoc, string $estado): void
    {
        $this->dispatch('error-general', mensaje: 'El estado se gestiona desde la cuenta de usuario vinculada. Las asignaciones y la trayectoria del personal se conservan.');
    }

    /*
    |--------------------------------------------------------------------------
    | Cálculo de carga académica
    |--------------------------------------------------------------------------
    */
    public function nivelCarga(int $horas): string
    {
        return match (true) {
            $horas === 0 => 'SIN_ASIGNACION',
            $horas <= 10 => 'NORMAL',
            $horas <= 18 => 'MEDIA',
            default => 'CRITICA',
        };
    }

    private function obtenerHorasMateriasDocente(string $codDoc): int
    {
        return (int) PlanAsignatura::where('cod_doc', $codDoc)
            ->where('est_pas', 'ACTIVO')->deGestion($this->codGestionActual ?? '')
            ->sum('hor_pas');
    }

    private function obtenerHorasEspecialidadesDocente(string $codDoc): int
    {
        return (int) PlanEspecialidad::where('cod_doc', $codDoc)
            ->where('est_pes', 'ACTIVO')->deGestion($this->codGestionActual ?? '')
            ->sum('hor_pes');
    }

    private function obtenerHorasTotalesDocente(string $codDoc): int
    {
        return $this->obtenerHorasMateriasDocente($codDoc)
            + $this->obtenerHorasEspecialidadesDocente($codDoc);
    }

    private function obtenerTotalAsignacionesDocente(string $codDoc): int
    {
        $materias = PlanAsignatura::where('cod_doc', $codDoc)
            ->where('est_pas', 'ACTIVO')->deGestion($this->codGestionActual ?? '')
            ->count();

        $especialidades = PlanEspecialidad::where('cod_doc', $codDoc)
            ->where('est_pes', 'ACTIVO')->deGestion($this->codGestionActual ?? '')
            ->count();

        return $materias + $especialidades;
    }

    private function docentesPorRangoHoras(int $min, int $max): array
    {
        $horasMaterias = PlanAsignatura::selectRaw('cod_doc, SUM(hor_pas) as total_horas')
            ->where('est_pas', 'ACTIVO')->deGestion($this->codGestionActual ?? '')
            ->groupBy('cod_doc')
            ->get();

        $horasEspecialidades = PlanEspecialidad::selectRaw('cod_doc, SUM(hor_pes) as total_horas')
            ->where('est_pes', 'ACTIVO')->deGestion($this->codGestionActual ?? '')
            ->groupBy('cod_doc')
            ->get();

        return $horasMaterias
            ->concat($horasEspecialidades)
            ->groupBy('cod_doc')
            ->map(fn ($items) => (int) $items->sum('total_horas'))
            ->filter(fn ($total) => $total >= $min && $total <= $max)
            ->keys()
            ->values()
            ->toArray();
    }

    private function docentesConCargaActiva(): array
    {
        $conMateria = PlanAsignatura::where('est_pas', 'ACTIVO')
            ->pluck('cod_doc')
            ->toArray();

        $conEspecialidad = PlanEspecialidad::where('est_pes', 'ACTIVO')
            ->pluck('cod_doc')
            ->toArray();

        return array_values(array_unique(array_merge($conMateria, $conEspecialidad)));
    }

    private function aplicarFiltroCarga($query)
    {
        return match ($this->carga) {
            'SIN_ASIGNACION' => $query->whereNotIn('cod_doc', $this->docentesConCargaActiva()),
            'NORMAL' => $query->whereIn('cod_doc', $this->docentesPorRangoHoras(1, 10)),
            'MEDIA' => $query->whereIn('cod_doc', $this->docentesPorRangoHoras(11, 18)),
            'COMPLETA' => $query->whereIn('cod_doc', $this->docentesPorRangoHoras(19, $this->maxHorasDocente)),
            'EXCESO' => $query->whereIn('cod_doc', $this->docentesPorRangoHoras($this->maxHorasDocente + 1, PHP_INT_MAX)),
            default => $query,
        };
    }

    private function aplicarFiltroTipoCarga($query)
    {
        return match ($this->tipoCargaFiltro) {
            'MATERIA' => $query->whereHas('planAsignaturas', function ($sub) {
                $sub->where('est_pas', 'ACTIVO')->deGestion($this->codGestionActual ?? '');
            }),
            'ESPECIALIDAD' => $query->whereHas('planEspecialidades', function ($sub) {
                $sub->where('est_pes', 'ACTIVO')->deGestion($this->codGestionActual ?? '');
            }),
            'AMBAS' => $query
                ->whereHas('planAsignaturas', function ($sub) {
                    $sub->where('est_pas', 'ACTIVO')->deGestion($this->codGestionActual ?? '');
                })
                ->whereHas('planEspecialidades', function ($sub) {
                    $sub->where('est_pes', 'ACTIVO')->deGestion($this->codGestionActual ?? '');
                }),
            default => $query,
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Bitácora
    |--------------------------------------------------------------------------
    */
    private function registrarBitacora(
        string $accion,
        string $tabla,
        ?string $registro = null,
        ?string $modulo = 'Gestión de Personal Institucional',
        ?string $nombreRegistro = null,
        ?string $descripcion = null,
        string $nivel = 'INFO',
        string $resultado = 'EXITOSO',
        ?array $valoresAnteriores = null,
        ?array $valoresNuevos = null,
        ?string $error = null
    ): void {
        BitacoraService::registrar(
            accion: $accion,
            tabla: $tabla,
            registro: $registro,
            modulo: $modulo,
            nombreRegistro: $nombreRegistro,
            descripcion: $descripcion,
            nivel: $nivel,
            resultado: $resultado,
            valoresAnteriores: $valoresAnteriores,
            valoresNuevos: $valoresNuevos,
            error: $error
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */
    public function puedeGuardarAsignacion(): bool
    {
        return filled($this->formAsignacion['tipo_carga'] ?? null)
            && filled($this->formAsignacion['cod_doc'] ?? null)
            && filled($this->formAsignacion['cod_cur'] ?? null)
            && filled($this->formAsignacion['cod_par'] ?? null)
            && filled($this->formAsignacion['cod_tur'] ?? null)
            && filled($this->formAsignacion['cod_gea'] ?? null)
            && filled($this->formAsignacion['hor_car'] ?? null)
            && filter_var($this->formAsignacion['hor_car'], FILTER_VALIDATE_INT) !== false
            && (int) $this->formAsignacion['hor_car'] >= 1
            && (int) $this->formAsignacion['hor_car'] <= max(0, $this->maxHorasDocente - (int) $this->docenteDetalle?->planAsignaturas->where('est_pas', 'ACTIVO')->deGestion($this->codGestionActual ?? '')->sum('hor_pas') - (int) $this->docenteDetalle?->planEspecialidades->where('est_pes', 'ACTIVO')->deGestion($this->codGestionActual ?? '')->sum('hor_pes'))
            && filled($this->formAsignacion['est_car'] ?? null)
            && (
                ($this->formAsignacion['tipo_carga'] === 'MATERIA' && filled($this->formAsignacion['cod_asi'] ?? null))
                || ($this->formAsignacion['tipo_carga'] === 'ESPECIALIDAD' && filled($this->formAsignacion['cod_esp'] ?? null))
            );
    }

    public function puedeActualizarDocente(): bool
    {
        return filled($this->formEditar['cod_doc'] ?? null)
            && filled($this->formEditar['esp_doc'] ?? null)
            && mb_strlen((string) $this->formEditar['esp_doc']) >= 3
            && mb_strlen((string) $this->formEditar['esp_doc']) <= 150
            && filled($this->formEditar['est_doc'] ?? null);
    }

    private function nombreDocente(?Docente $docente): string
    {
        if (! $docente) {
            return 'Docente no identificado';
        }

        $persona = $docente->personalInstitucional?->persona;

        if (! $persona) {
            return $docente->cod_doc;
        }

        $nombre = trim(collect([
            $persona->nom_per,
            $persona->ape_pat_per,
            $persona->ape_mat_per,
        ])->filter()->implode(' '));

        return $nombre !== '' ? $nombre : $docente->cod_doc;
    }

    /*
    |--------------------------------------------------------------------------
    | Render
    |--------------------------------------------------------------------------
    */
    public function render()
    {
        $consultaDocentes = Docente::query()
            ->with([
                'personalInstitucional.persona.usuario',

                'planAsignaturas.asignatura',
                'planAsignaturas.curso',
                'planAsignaturas.paralelo',
                'planAsignaturas.turno',
                'planAsignaturas.gestionAcademica',

                'planEspecialidades.especialidad',
                'planEspecialidades.curso',
                'planEspecialidades.paralelo',
                'planEspecialidades.turno',
                'planEspecialidades.gestionAcademica',
            ])
            ->withCount([
                'planAsignaturas as total_materias' => function ($query) {
                    $query->where('est_pas', 'ACTIVO')->deGestion($this->codGestionActual ?? '');
                },
                'planEspecialidades as total_especialidades' => function ($query) {
                    $query->where('est_pes', 'ACTIVO')->deGestion($this->codGestionActual ?? '');
                },
            ])
            ->withSum([
                'planAsignaturas as total_horas_materias' => function ($query) {
                    $query->where('est_pas', 'ACTIVO')->deGestion($this->codGestionActual ?? '');
                },
            ], 'hor_pas')
            ->withSum([
                'planEspecialidades as total_horas_especialidades' => function ($query) {
                    $query->where('est_pes', 'ACTIVO')->deGestion($this->codGestionActual ?? '');
                },
            ], 'hor_pes')
            ->when($this->carga !== '', fn ($query) => $this->aplicarFiltroCarga($query))
            ->when($this->tipoCargaFiltro !== '', fn ($query) => $this->aplicarFiltroTipoCarga($query))
            ->when($this->materiaFiltro !== '', fn ($q) => $q->whereHas('planAsignaturas', fn ($p) => $p->where('est_pas', 'ACTIVO')->deGestion($this->codGestionActual ?? '')->where('cod_asi', $this->materiaFiltro)->when($this->cursoFiltro !== '', fn ($c) => $c->deCurso($this->cursoFiltro))))
            ->when($this->especialidadFiltro !== '', fn ($q) => $q->whereHas('planEspecialidades', fn ($p) => $p->where('est_pes', 'ACTIVO')->deGestion($this->codGestionActual ?? '')->where('cod_esp', $this->especialidadFiltro)->when($this->cursoFiltro !== '', fn ($c) => $c->deCurso($this->cursoFiltro))))
            ->when($this->cursoFiltro !== '' && $this->materiaFiltro === '' && $this->especialidadFiltro === '', fn ($q) => $q->where(fn ($d) => $d->whereHas('planAsignaturas', fn ($p) => $p->where('est_pas', 'ACTIVO')->deGestion($this->codGestionActual ?? '')->deCurso($this->cursoFiltro))->orWhereHas('planEspecialidades', fn ($p) => $p->where('est_pes', 'ACTIVO')->deGestion($this->codGestionActual ?? '')->deCurso($this->cursoFiltro))))
            ->orderByDesc('cod_doc');
        $tieneCargoHistorico = Schema::hasColumn('personal_institucional', 'car_pin');
        $consultaPersonal = \App\Models\Oficial\Academico\PersonalInstitucional::query()->with(['persona.usuario.roles', 'docente' => fn ($d) => $d
            ->withSum(['planAsignaturas as horas_materias_vista' => fn ($p) => $p->where('est_pas', 'ACTIVO')->deGestion($this->codGestionActual ?? '')], 'hor_pas')
            ->withSum(['planEspecialidades as horas_tecnicas_vista' => fn ($p) => $p->where('est_pes', 'ACTIVO')->deGestion($this->codGestionActual ?? '')], 'hor_pes')
            ->withCount(['planAsignaturas as materias_vista' => fn ($p) => $p->where('est_pas', 'ACTIVO')->deGestion($this->codGestionActual ?? ''), 'planEspecialidades as tecnicas_vista' => fn ($p) => $p->where('est_pes', 'ACTIVO')->deGestion($this->codGestionActual ?? '')])])
            ->when($this->estado !== '', fn ($q) => $q->whereHas('persona.usuario', fn ($u) => $u->where('est_usu', $this->estado)))
            ->when($this->cargoFiltro !== '' && $tieneCargoHistorico, fn ($q) => $q->where('car_pin', $this->cargoFiltro))
            ->when($this->vinculacionFiltro === 'CON_CUENTA', fn ($q) => $q->whereHas('persona.usuario'))
            ->when($this->vinculacionFiltro === 'SIN_CUENTA', fn ($q) => $q->whereDoesntHave('persona.usuario'))
            ->when($this->contactoFiltro === 'SIN_CORREO', fn ($q) => $q->whereHas('persona', fn ($p) => $p->where(fn ($c) => $c->whereNull('ema_per')->orWhereRaw("TRIM(ema_per) = ''"))))
            ->when($this->contactoFiltro === 'SIN_TELEFONO', fn ($q) => $q->whereHas('persona', fn ($p) => $p->where(fn ($c) => $c->whereNull('tel_per')->orWhereRaw("TRIM(tel_per) = ''"))))
            ->when(trim($this->search) !== '', function ($q) use ($tieneCargoHistorico) {
                $termino = '%'.trim($this->search).'%';
                $q->where(function ($busqueda) use ($termino, $tieneCargoHistorico) {
                    $busqueda->whereHas('persona', fn ($p) => $p->where('nom_per', 'ILIKE', $termino)->orWhere('ape_pat_per', 'ILIKE', $termino)->orWhere('ape_mat_per', 'ILIKE', $termino)->orWhere('ci_per', 'ILIKE', $termino)->orWhere('ema_per', 'ILIKE', $termino));
                    if ($tieneCargoHistorico) {
                        $busqueda->orWhere('car_pin', 'ILIKE', $termino);
                    }
                    $busqueda->orWhereHas('docente', fn ($d) => $d->where('esp_doc', 'ILIKE', $termino)->orWhereHas('planAsignaturas.asignatura', fn ($a) => $a->where('nom_asi', 'ILIKE', $termino))->orWhereHas('planEspecialidades.especialidad', fn ($e) => $e->where('nom_esp', 'ILIKE', $termino)));
                });
            });
        if ($this->carga !== '' || $this->tipoCargaFiltro !== '' || $this->materiaFiltro !== '' || $this->especialidadFiltro !== '' || $this->cursoFiltro !== '') {
            $consultaPersonal->whereIn('cod_pin', (clone $consultaDocentes)->reorder()->select('cod_pin')->pluck('cod_pin'));
        }
        $consultaDocentes->whereIn('cod_pin', (clone $consultaPersonal)->withoutEagerLoads()->select('cod_pin'));
        $cantidad = in_array($this->perPage, [10, 20, 50], true) ? $this->perPage : 10;
        $consultaOrdenada = clone $consultaPersonal;
        if (in_array($this->orden, ['REGISTRO_RECIENTE', 'REGISTRO_ANTIGUO'], true)) {
            $consultaOrdenada->orderBy('created_at', $this->orden === 'REGISTRO_ANTIGUO' ? 'asc' : 'desc');
        } else {
            $porNombre = in_array($this->orden, ['NOMBRE_AZ', 'NOMBRE_ZA'], true);
            $direccion = in_array($this->orden, ['APELLIDO_ZA', 'NOMBRE_ZA'], true) ? 'desc' : 'asc';
            $columnas = $porNombre ? ['nom_per', 'ape_pat_per', 'ape_mat_per'] : ['ape_pat_per', 'ape_mat_per', 'nom_per'];
            foreach ($columnas as $columna) {
                $consultaOrdenada->orderBy(Persona::query()->selectRaw('UPPER('.$columna.')')->whereColumn('persona.cod_per', 'personal_institucional.cod_per')->limit(1), $direccion);
            }
        }
        $personal = $consultaOrdenada->orderBy('cod_pin')->paginate($cantidad);
        $cargosPersonal = $tieneCargoHistorico ? \App\Models\Oficial\Academico\PersonalInstitucional::query()->whereNotNull('car_pin')->select('car_pin')->selectRaw('COUNT(*) as cantidad')->groupBy('car_pin')->orderByDesc('cantidad')->get() : collect();
        $indicadoresPersonal = app(IndicadoresPersonal::class)->calcular($consultaDocentes, $this->maxHorasDocente);
        $this->dispatch('indicadores-personal', datos: $indicadoresPersonal);

        $etiquetasFiltro = ['cargoFiltro' => 'Cargo', 'tipoCargaFiltro' => 'Tipo de asignación', 'carga' => 'Carga horaria', 'materiaFiltro' => 'Materia', 'especialidadFiltro' => 'Especialidad', 'cursoFiltro' => 'Curso', 'contactoFiltro' => 'Contacto pendiente'];
        $filtrosActivos = [];
        foreach ($etiquetasFiltro as $propiedad => $etiqueta) {
            if ($this->{$propiedad} !== '') {
                $filtrosActivos[] = ['propiedad' => $propiedad, 'etiqueta' => $etiqueta];
            }
        }

        return view('livewire.admin.personal-institucional', [
            'personal' => $personal,
            'cargosPersonal' => $cargosPersonal,
            'resumenPersonal' => ['total' => \App\Models\Oficial\Academico\PersonalInstitucional::count(), 'activos' => \App\Models\Oficial\Academico\PersonalInstitucional::whereHas('persona.usuario', fn ($u) => $u->where('est_usu', 'ACTIVO'))->count(), 'con_cuenta' => \App\Models\Oficial\Academico\PersonalInstitucional::whereHas('persona.usuario')->count(), 'sin_cuenta' => \App\Models\Oficial\Academico\PersonalInstitucional::whereDoesntHave('persona.usuario')->count()],
            'indicadoresPersonal' => $indicadoresPersonal,
            'filtrosActivos' => $filtrosActivos,
            'cantidadFiltrosAdicionales' => count(array_filter($filtrosActivos, fn ($filtro) => $filtro['propiedad'] !== 'cargoFiltro')),
            'analisisEspecialidad' => app(DocenteInteligente::class)->analizarEspecialidad($this->formEditar['esp_doc']),

            'maxHorasDocente' => $this->maxHorasDocente,
            'maxModificaciones' => $this->maxModificaciones,

            'codTurnoManana' => $this->codTurnoManana,
            'codTurnoTarde' => $this->codTurnoTarde,
            'codGestionActual' => $this->codGestionActual,

            'nombreTurnoManana' => $this->nombreTurnoManana,
            'nombreTurnoTarde' => $this->nombreTurnoTarde,
            'nombreGestionActual' => $this->nombreGestionActual,

            'asignaturas' => Asignatura::where('est_asi', 'ACTIVO')
                ->orderBy('nom_asi')
                ->get(),

            'especialidadesTecnicas' => EspecialidadTecnica::where('est_esp', 'ACTIVO')
                ->orderBy('nom_esp')
                ->get(),

            'cursos' => Curso::where('est_cur', 'ACTIVO')
                ->orderBy('nom_cur')
                ->get(),

            'paralelos' => Paralelo::where('est_par', 'ACTIVO')
                ->orderBy('nom_par')
                ->get(),

            'turnos' => Turno::where('est_tur', 'ACTIVO')
                ->orderBy('nom_tur')
                ->get(),

            'gestiones' => GestionAcademica::whereIn('est_gea', ['ACTIVO', 'ACTIVA'])
                ->orderByDesc('ani_gea')
                ->get(),
        ]);
    }
}
