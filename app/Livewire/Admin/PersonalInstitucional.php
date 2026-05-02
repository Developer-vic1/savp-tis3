<?php

namespace App\Livewire\Admin;

use App\Models\Asignatura;
use App\Models\Bitacora;
use App\Models\Curso;
use App\Models\Docente;
use App\Models\EspecialidadTecnica;
use App\Models\GestionAcademica;
use App\Models\Paralelo;
use App\Models\PlanAsignatura;
use App\Models\PlanEspecialidad;
use App\Models\Turno;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class PersonalInstitucional extends Component
{
    use WithPagination;

    /*
    |--------------------------------------------------------------------------
    | FILTROS PRINCIPALES
    |--------------------------------------------------------------------------
    */
    public string $search = '';
    public string $estado = '';
    public string $carga = '';
    public string $tipoCargaFiltro = '';
    public int $perPage = 10;

    /*
    |--------------------------------------------------------------------------
    | CONTROL DE MODALES
    |--------------------------------------------------------------------------
    */
    public bool $modalVer = false;
    public bool $modalAsignar = false;
    public bool $modalEditar = false;

    public ?Docente $docenteDetalle = null;

    /*
    |--------------------------------------------------------------------------
    | REGLAS INSTITUCIONALES
    |--------------------------------------------------------------------------
    | maxHorasDocente:
    | Límite total combinado entre materias de la mañana y especialidades de la tarde.
    |
    | maxModificaciones:
    | Control de modificaciones permitidas para datos sensibles del docente.
    |--------------------------------------------------------------------------
    */
    public int $maxHorasDocente = 24;
    public int $maxModificaciones = 3;

    /*
    |--------------------------------------------------------------------------
    | TURNOS Y GESTIÓN AUTOMÁTICOS
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
    | FORMULARIO DE ASIGNACIÓN
    |--------------------------------------------------------------------------
    | tipo_carga:
    | - MATERIA: usa plan_asignatura
    | - ESPECIALIDAD: usa plan_especialidad
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
    ];

    /*
    |--------------------------------------------------------------------------
    | FORMULARIO DE EDICIÓN
    |--------------------------------------------------------------------------
    | esp_doc representa el perfil profesional del docente.
    | No representa necesariamente una especialidad técnica asignada.
    |--------------------------------------------------------------------------
    */
    public array $formEditar = [
        'cod_doc' => '',
        'esp_doc' => '',
        'est_doc' => 'ACTIVO',
    ];

    /*
    |--------------------------------------------------------------------------
    | CICLO DE VIDA
    |--------------------------------------------------------------------------
    */
    public function mount(): void
    {
        $this->cargarConfiguracionAcademicaAutomatica();
    }

    /*
    |--------------------------------------------------------------------------
    | VALIDACIONES
    |--------------------------------------------------------------------------
    */
    protected function rules(): array
    {
        return [
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
                'max:' . $this->maxHorasDocente,
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
        ]);

        $this->resetPage();
    }

    /*
    |--------------------------------------------------------------------------
    | CONFIGURACIÓN AUTOMÁTICA: TURNO Y GESTIÓN
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
            ? 'Gestión ' . $gestionActual->ani_gea
            : 'Gestión activa no definida';
    }

    private function obtenerTurnoPorNombre(array $nombres): ?Turno
    {
        return Turno::query()
            ->where('est_tur', 'ACTIVO')
            ->where(function ($query) use ($nombres) {
                foreach ($nombres as $nombre) {
                    $query->orWhere('nom_tur', 'ILIKE', '%' . $nombre . '%');
                }
            })
            ->orderBy('cod_tur')
            ->first();
    }

    private function obtenerGestionAcademicaPorDefecto(): ?GestionAcademica
    {
        /*
        |--------------------------------------------------------------------------
        | Regla institucional
        |--------------------------------------------------------------------------
        | Normalmente se usa la gestión activa.
        | Para planificación futura, desde octubre/noviembre se podría habilitar
        | selección de la siguiente gestión en la vista, pero por ahora el sistema
        | asigna automáticamente la gestión activa para evitar errores humanos.
        |--------------------------------------------------------------------------
        */
        return GestionAcademica::query()
            ->where('est_gea', 'ACTIVO')
            ->orderByDesc('ani_gea')
            ->first();
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

        /*
        |--------------------------------------------------------------------------
        | Limpiar selección opuesta
        |--------------------------------------------------------------------------
        | Si se elige materia, se limpia cod_esp.
        | Si se elige especialidad, se limpia cod_asi.
        |--------------------------------------------------------------------------
        */
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
    | UTILIDADES DE MODALES
    |--------------------------------------------------------------------------
    */
    private function cerrarTodosLosModales(): void
    {
        $this->modalVer = false;
        $this->modalAsignar = false;
        $this->modalEditar = false;
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
    | MODAL VER
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
    | MODAL ASIGNAR CARGA
    |--------------------------------------------------------------------------
    */
    public function abrirModalAsignar(string $codDoc): void
    {
        $this->resetValidation();
        $this->cerrarTodosLosModales();

        $docente = $this->cargarDocenteDetalle($codDoc);

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

        $this->formAsignacion = [
            'tipo_carga' => 'MATERIA',
            'cod_doc' => $docente->cod_doc,
            'cod_asi' => '',
            'cod_esp' => '',
            'cod_cur' => '',
            'cod_par' => '',
            'cod_tur' => '',
            'cod_gea' => '',
            'hor_car' => '',
            'est_car' => 'ACTIVO',
        ];

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
        $this->resetValidation();
    }

    public function guardarAsignacion(): void
    {
        $this->validate();

        $tipoCarga = $this->formAsignacion['tipo_carga'];

        DB::transaction(function () use ($tipoCarga) {
            $docente = Docente::lockForUpdate()->findOrFail($this->formAsignacion['cod_doc']);

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
                    mensaje: 'La asignación supera la carga máxima permitida de ' . $this->maxHorasDocente . ' horas. Actualmente tiene ' . $horasActuales . ' horas.'
                );

                return;
            }

            if ($tipoCarga === 'MATERIA') {
                $this->guardarAsignacionMateria();
                return;
            }

            if ($tipoCarga === 'ESPECIALIDAD') {
                $this->guardarAsignacionEspecialidad();
                return;
            }

            $this->dispatch('error-general', mensaje: 'Tipo de carga no reconocido.');
        });
    }

    private function guardarAsignacionMateria(): void
    {
        $existe = PlanAsignatura::where('cod_doc', $this->formAsignacion['cod_doc'])
            ->where('cod_asi', $this->formAsignacion['cod_asi'])
            ->where('cod_cur', $this->formAsignacion['cod_cur'])
            ->where('cod_par', $this->formAsignacion['cod_par'])
            ->where('cod_tur', $this->formAsignacion['cod_tur'])
            ->where('cod_gea', $this->formAsignacion['cod_gea'])
            ->exists();

        if ($existe) {
            $this->addError('formAsignacion.cod_asi', 'Esta materia ya fue asignada al docente en el mismo curso, paralelo, turno y gestión.');
            return;
        }

        $plan = PlanAsignatura::create([
            'cod_doc' => $this->formAsignacion['cod_doc'],
            'cod_asi' => $this->formAsignacion['cod_asi'],
            'cod_cur' => $this->formAsignacion['cod_cur'],
            'cod_par' => $this->formAsignacion['cod_par'],
            'cod_tur' => $this->formAsignacion['cod_tur'],
            'cod_gea' => $this->formAsignacion['cod_gea'],
            'hor_pas' => (int) $this->formAsignacion['hor_car'],
            'est_pas' => $this->formAsignacion['est_car'],
        ]);

        $this->registrarBitacora('ASIGNAR_MATERIA_MANANA', 'plan_asignatura', $plan->cod_pas);

        $this->cerrarModalAsignar();

        $this->dispatch('asignacion-creada');
    }

    private function guardarAsignacionEspecialidad(): void
    {
        $existe = PlanEspecialidad::where('cod_doc', $this->formAsignacion['cod_doc'])
            ->where('cod_esp', $this->formAsignacion['cod_esp'])
            ->where('cod_cur', $this->formAsignacion['cod_cur'])
            ->where('cod_par', $this->formAsignacion['cod_par'])
            ->where('cod_tur', $this->formAsignacion['cod_tur'])
            ->where('cod_gea', $this->formAsignacion['cod_gea'])
            ->exists();

        if ($existe) {
            $this->addError('formAsignacion.cod_esp', 'Esta especialidad ya fue asignada al docente en el mismo curso, paralelo, turno y gestión.');
            return;
        }

        $plan = PlanEspecialidad::create([
            'cod_doc' => $this->formAsignacion['cod_doc'],
            'cod_esp' => $this->formAsignacion['cod_esp'],
            'cod_cur' => $this->formAsignacion['cod_cur'],
            'cod_par' => $this->formAsignacion['cod_par'],
            'cod_tur' => $this->formAsignacion['cod_tur'],
            'cod_gea' => $this->formAsignacion['cod_gea'],
            'hor_pes' => (int) $this->formAsignacion['hor_car'],
            'est_pes' => $this->formAsignacion['est_car'],
        ]);

        $this->registrarBitacora('ASIGNAR_ESPECIALIDAD_TARDE', 'plan_especialidad', $plan->cod_pes);

        $this->cerrarModalAsignar();

        $this->dispatch('asignacion-creada');
    }

    /*
    |--------------------------------------------------------------------------
    | MODAL EDITAR DOCENTE
    |--------------------------------------------------------------------------
    */
    public function abrirModalEditar(string $codDoc): void
    {
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
        $this->modalEditar = true;
    }

    public function cerrarModalEditar(): void
    {
        $this->modalEditar = false;
        $this->docenteDetalle = null;
        $this->resetValidation();
    }

    public function actualizarDocente(): void
    {
        $this->validate([
            'formEditar.cod_doc' => [
                'required',
                'exists:docente,cod_doc',
            ],
            'formEditar.esp_doc' => [
                'required',
                'string',
                'min:3',
                'max:150',
            ],
            'formEditar.est_doc' => [
                'required',
                Rule::in(['ACTIVO', 'INACTIVO']),
            ],
        ]);

        DB::transaction(function () {
            $docente = Docente::with('personalInstitucional')
                ->lockForUpdate()
                ->findOrFail($this->formEditar['cod_doc']);

            if ((int) $docente->num_mod_doc >= $this->maxModificaciones) {
                $this->dispatch('error-general', mensaje: 'Este docente ya no puede ser modificado porque alcanzó el límite permitido.');
                return;
            }

            $docente->update([
                'esp_doc' => $this->formEditar['esp_doc'],
                'est_doc' => $this->formEditar['est_doc'],
                'num_mod_doc' => ((int) $docente->num_mod_doc) + 1,
            ]);

            if ($docente->personalInstitucional) {
                $docente->personalInstitucional->update([
                    'est_pin' => $this->formEditar['est_doc'],
                ]);
            }

            $this->registrarBitacora('EDITAR_DOCENTE', 'docente', $docente->cod_doc);

            $this->cerrarModalEditar();

            $this->dispatch('docente-actualizado');
        });
    }

    /*
    |--------------------------------------------------------------------------
    | CAMBIAR ESTADO DOCENTE
    |--------------------------------------------------------------------------
    */
    public function cambiarEstado(string $codDoc, string $estado): void
    {
        if (! in_array($estado, ['ACTIVO', 'INACTIVO'], true)) {
            $this->dispatch('error-general', mensaje: 'Estado no permitido.');
            return;
        }

        DB::transaction(function () use ($codDoc, $estado) {
            $docente = Docente::with('personalInstitucional')
                ->lockForUpdate()
                ->findOrFail($codDoc);

            if ((int) $docente->num_mod_doc >= $this->maxModificaciones) {
                $this->dispatch('error-general', mensaje: 'No se puede cambiar el estado porque el docente alcanzó el límite de modificaciones.');
                return;
            }

            $docente->update([
                'est_doc' => $estado,
                'num_mod_doc' => ((int) $docente->num_mod_doc) + 1,
            ]);

            if ($docente->personalInstitucional) {
                $docente->personalInstitucional->update([
                    'est_pin' => $estado,
                ]);
            }

            $this->registrarBitacora(
                $estado === 'ACTIVO' ? 'REACTIVAR_DOCENTE' : 'DESACTIVAR_DOCENTE',
                'docente',
                $docente->cod_doc
            );

            $this->dispatch($estado === 'ACTIVO' ? 'docente-reactivado' : 'docente-desactivado');
        });
    }

    /*
    |--------------------------------------------------------------------------
    | CÁLCULO DE CARGA ACADÉMICA
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
            ->where('est_pas', 'ACTIVO')
            ->sum('hor_pas');
    }

    private function obtenerHorasEspecialidadesDocente(string $codDoc): int
    {
        return (int) PlanEspecialidad::where('cod_doc', $codDoc)
            ->where('est_pes', 'ACTIVO')
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
            ->where('est_pas', 'ACTIVO')
            ->count();

        $especialidades = PlanEspecialidad::where('cod_doc', $codDoc)
            ->where('est_pes', 'ACTIVO')
            ->count();

        return $materias + $especialidades;
    }

    private function docentesPorRangoHoras(int $min, int $max): array
    {
        $horasMaterias = PlanAsignatura::selectRaw('cod_doc, SUM(hor_pas) as total_horas')
            ->where('est_pas', 'ACTIVO')
            ->groupBy('cod_doc')
            ->get();

        $horasEspecialidades = PlanEspecialidad::selectRaw('cod_doc, SUM(hor_pes) as total_horas')
            ->where('est_pes', 'ACTIVO')
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

    private function docentesSinCargaActiva(): array
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
            'SIN_ASIGNACION' => $query->whereNotIn('cod_doc', $this->docentesSinCargaActiva()),
            'NORMAL' => $query->whereIn('cod_doc', $this->docentesPorRangoHoras(1, 10)),
            'MEDIA' => $query->whereIn('cod_doc', $this->docentesPorRangoHoras(11, 18)),
            'CRITICA' => $query->whereIn('cod_doc', $this->docentesPorRangoHoras(19, 999)),
            default => $query,
        };
    }

    private function aplicarFiltroTipoCarga($query)
    {
        return match ($this->tipoCargaFiltro) {
            'MATERIA' => $query->whereHas('planAsignaturas', function ($sub) {
                $sub->where('est_pas', 'ACTIVO');
            }),
            'ESPECIALIDAD' => $query->whereHas('planEspecialidades', function ($sub) {
                $sub->where('est_pes', 'ACTIVO');
            }),
            'AMBAS' => $query
                ->whereHas('planAsignaturas', function ($sub) {
                    $sub->where('est_pas', 'ACTIVO');
                })
                ->whereHas('planEspecialidades', function ($sub) {
                    $sub->where('est_pes', 'ACTIVO');
                }),
            default => $query,
        };
    }

    /*
    |--------------------------------------------------------------------------
    | BITÁCORA MANUAL
    |--------------------------------------------------------------------------
    */
    private function registrarBitacora(string $accion, string $tabla, string $registro): void
    {
        Bitacora::create([
            'acc_bit' => $accion,
            'tab_bit' => $tabla,
            'reg_bit' => $registro,
            'cod_usu' => Auth::id(),
            'fec_bit' => now(),
            'est_bit' => 'ACTIVO',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | RENDER
    |--------------------------------------------------------------------------
    */
    public function render()
    {
        $docentes = Docente::query()
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
                    $query->where('est_pas', 'ACTIVO');
                },
                'planEspecialidades as total_especialidades' => function ($query) {
                    $query->where('est_pes', 'ACTIVO');
                },
            ])
            ->withSum([
                'planAsignaturas as total_horas_materias' => function ($query) {
                    $query->where('est_pas', 'ACTIVO');
                },
            ], 'hor_pas')
            ->withSum([
                'planEspecialidades as total_horas_especialidades' => function ($query) {
                    $query->where('est_pes', 'ACTIVO');
                },
            ], 'hor_pes')
            ->when($this->search !== '', function ($query) {
                $query->where(function ($q) {
                    $q->where('esp_doc', 'ILIKE', "%{$this->search}%")
                        ->orWhereHas('personalInstitucional.persona', function ($sub) {
                            $sub->where('nom_per', 'ILIKE', "%{$this->search}%")
                                ->orWhere('ape_pat_per', 'ILIKE', "%{$this->search}%")
                                ->orWhere('ape_mat_per', 'ILIKE', "%{$this->search}%")
                                ->orWhere('ci_per', 'ILIKE', "%{$this->search}%")
                                ->orWhere('ema_per', 'ILIKE', "%{$this->search}%");
                        })
                        ->orWhereHas('planAsignaturas.asignatura', function ($sub) {
                            $sub->where('nom_asi', 'ILIKE', "%{$this->search}%")
                                ->orWhere('sig_asi', 'ILIKE', "%{$this->search}%");
                        })
                        ->orWhereHas('planEspecialidades.especialidad', function ($sub) {
                            $sub->where('nom_esp', 'ILIKE', "%{$this->search}%")
                                ->orWhere('des_esp', 'ILIKE', "%{$this->search}%");
                        });
                });
            })
            ->when($this->estado !== '', fn ($query) => $query->where('est_doc', $this->estado))
            ->when($this->carga !== '', fn ($query) => $this->aplicarFiltroCarga($query))
            ->when($this->tipoCargaFiltro !== '', fn ($query) => $this->aplicarFiltroTipoCarga($query))
            ->orderByDesc('cod_doc')
            ->paginate($this->perPage);

        /*
        |--------------------------------------------------------------------------
        | TOTALES GENERALES
        |--------------------------------------------------------------------------
        */
        $totalDocentes = Docente::count();

        $docentesActivos = Docente::where('est_doc', 'ACTIVO')->count();

        $docentesInactivos = Docente::where('est_doc', 'INACTIVO')->count();

        $totalMateriasAsignadas = PlanAsignatura::where('est_pas', 'ACTIVO')->count();

        $totalEspecialidadesAsignadas = PlanEspecialidad::where('est_pes', 'ACTIVO')->count();

        $totalAsignaciones = $totalMateriasAsignadas + $totalEspecialidadesAsignadas;

        $totalHorasMaterias = (int) PlanAsignatura::where('est_pas', 'ACTIVO')->sum('hor_pas');

        $totalHorasEspecialidades = (int) PlanEspecialidad::where('est_pes', 'ACTIVO')->sum('hor_pes');

        $totalHoras = $totalHorasMaterias + $totalHorasEspecialidades;

        $docentesSinAsignacion = Docente::whereNotIn('cod_doc', $this->docentesSinCargaActiva())->count();

        $docentesSobrecargados = count($this->docentesPorRangoHoras(19, 999));

        /*
        |--------------------------------------------------------------------------
        | CATÁLOGOS PARA LA VISTA
        |--------------------------------------------------------------------------
        */
        return view('livewire.admin.personal-institucional', [
            'docentes' => $docentes,

            'totalDocentes' => $totalDocentes,
            'docentesActivos' => $docentesActivos,
            'docentesInactivos' => $docentesInactivos,

            'totalAsignaciones' => $totalAsignaciones,
            'totalMateriasAsignadas' => $totalMateriasAsignadas,
            'totalEspecialidadesAsignadas' => $totalEspecialidadesAsignadas,

            'totalHoras' => $totalHoras,
            'totalHorasMaterias' => $totalHorasMaterias,
            'totalHorasEspecialidades' => $totalHorasEspecialidades,

            'docentesSinAsignacion' => $docentesSinAsignacion,
            'docentesSobrecargados' => $docentesSobrecargados,

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

            'gestiones' => GestionAcademica::where('est_gea', 'ACTIVO')
                ->orderByDesc('ani_gea')
                ->get(),
        ]);
    }
}