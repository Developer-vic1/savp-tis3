<?php

namespace App\Livewire\Admin;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class GestionCurso extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    /*
    |--------------------------------------------------------------------------
    | Filtros principales
    |--------------------------------------------------------------------------
    */
    public string $search = '';
    public string $estado = '';
    public string $nivel = '';
    public string $gestionFiltro = '';
    public string $planificacion = '';
    public int $perPage = 10;

    /*
    |--------------------------------------------------------------------------
    | Control visual
    |--------------------------------------------------------------------------
    */
    public bool $modalCrear = false;
    public bool $modalEditar = false;
    public bool $modalDetalle = false;
    public bool $modalPlanificar = false;

    public ?array $cursoDetalle = null;
    public ?string $cursoSeleccionado = null;

    /*
    |--------------------------------------------------------------------------
    | Formularios
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

    public array $formPlan = [
        'cod_gea' => '',
        'materias' => [],
        'cod_doc' => '',
        'hor_pas' => '',
        'est_pas' => 'ACTIVO',
    ];

    /*
    |--------------------------------------------------------------------------
    | Mensajes
    |--------------------------------------------------------------------------
    */
    protected array $messages = [
        'form.nom_cur.required' => 'El nombre del curso es obligatorio.',
        'form.nom_cur.max' => 'El nombre del curso no debe superar los 120 caracteres.',
        'form.nom_cur.unique' => 'Ya existe un curso registrado con ese nombre.',
        'form.ord_cur.required' => 'El orden académico es obligatorio.',
        'form.ord_cur.integer' => 'El orden académico debe ser numérico.',
        'form.ord_cur.min' => 'El orden académico debe ser mayor o igual a 1.',
        'form.ord_cur.max' => 'El orden académico no debe ser mayor a 20.',
        'form.niv_cur.required' => 'Debes seleccionar el nivel académico.',
        'form.niv_cur.max' => 'El nivel académico no debe superar los 100 caracteres.',
        'form.des_cur.max' => 'La descripción no debe superar los 255 caracteres.',
        'form.est_cur.required' => 'Debes seleccionar el estado del curso.',
        'form.est_cur.in' => 'El estado seleccionado no es válido.',

        'formEditar.cod_cur.required' => 'No se pudo identificar el curso.',
        'formEditar.cod_cur.exists' => 'El curso seleccionado no existe.',
        'formEditar.nom_cur.required' => 'El nombre del curso es obligatorio.',
        'formEditar.nom_cur.max' => 'El nombre del curso no debe superar los 120 caracteres.',
        'formEditar.nom_cur.unique' => 'Ya existe otro curso registrado con ese nombre.',
        'formEditar.ord_cur.required' => 'El orden académico es obligatorio.',
        'formEditar.ord_cur.integer' => 'El orden académico debe ser numérico.',
        'formEditar.ord_cur.min' => 'El orden académico debe ser mayor o igual a 1.',
        'formEditar.ord_cur.max' => 'El orden académico no debe ser mayor a 20.',
        'formEditar.niv_cur.required' => 'Debes seleccionar el nivel académico.',
        'formEditar.niv_cur.max' => 'El nivel académico no debe superar los 100 caracteres.',
        'formEditar.des_cur.max' => 'La descripción no debe superar los 255 caracteres.',
        'formEditar.est_cur.required' => 'Debes seleccionar el estado del curso.',
        'formEditar.est_cur.in' => 'El estado seleccionado no es válido.',

        'formPlan.cod_gea.required' => 'Debes seleccionar una gestión académica.',
        'formPlan.cod_gea.exists' => 'La gestión académica seleccionada no existe.',
        'formPlan.materias.required' => 'Debes seleccionar al menos una materia.',
        'formPlan.materias.array' => 'La selección de materias no es válida.',
        'formPlan.materias.min' => 'Debes seleccionar al menos una materia.',
        'formPlan.materias.*.required' => 'Una de las materias seleccionadas no es válida.',
        'formPlan.materias.*.exists' => 'Una de las materias seleccionadas no existe.',
        'formPlan.cod_doc.required' => 'Debes seleccionar un docente.',
        'formPlan.cod_doc.exists' => 'El docente seleccionado no existe.',
        'formPlan.hor_pas.required' => 'La carga horaria es obligatoria.',
        'formPlan.hor_pas.integer' => 'La carga horaria debe ser numérica.',
        'formPlan.hor_pas.min' => 'La carga horaria debe ser mayor a 0.',
        'formPlan.hor_pas.max' => 'La carga horaria no debe superar 80 horas.',
        'formPlan.est_pas.required' => 'Debes seleccionar el estado del plan.',
        'formPlan.est_pas.in' => 'El estado del plan no es válido.',
    ];

    /*
    |--------------------------------------------------------------------------
    | Reactividad
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

    public function updatingPlanificacion(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    /*
    |--------------------------------------------------------------------------
    | Validaciones
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

    private function rulesPlan(): array
    {
        $rules = [
            'formPlan.cod_gea' => ['required'],
            'formPlan.cod_asi' => ['required'],
            'formPlan.cod_doc' => ['required'],
            'formPlan.hor_pas' => ['required', 'integer', 'min:1', 'max:80'],
            'formPlan.est_pas' => ['required', Rule::in(['ACTIVO', 'INACTIVO', 'PLANIFICADO'])],
        ];

        if ($this->tablaExiste('gestion_academica') && $this->columnaExiste('gestion_academica', 'cod_gea')) {
            $rules['formPlan.cod_gea'][] = 'exists:gestion_academica,cod_gea';
        }

        if ($this->tablaExiste($this->tablaAsignatura()) && $this->columnaExiste($this->tablaAsignatura(), $this->columnaCodigoAsignatura())) {
            $rules['formPlan.cod_asi'][] = 'exists:' . $this->tablaAsignatura() . ',' . $this->columnaCodigoAsignatura();
        }

        if ($this->tablaExiste('docente') && $this->columnaExiste('docente', 'cod_doc')) {
            $rules['formPlan.cod_doc'][] = 'exists:docente,cod_doc';
        }

        return $rules;
    }

    /*
    |--------------------------------------------------------------------------
    | Modales
    |--------------------------------------------------------------------------
    */
    public function abrirModalCrear(): void
    {
        $this->resetValidation();
        $this->resetFormulario();
        $this->modalCrear = true;
    }

    public function cerrarModalCrear(): void
    {
        $this->modalCrear = false;
        $this->resetValidation();
        $this->resetFormulario();
    }

    public function abrirModalEditar(string $codCur): void
    {
        if (! $this->tablaExiste('curso')) {
            $this->dispatch('error-general', mensaje: 'La tabla de cursos no existe.');
            return;
        }

        $curso = DB::table('curso')->where('cod_cur', $codCur)->first();

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
        $this->cursoDetalle = $curso;
        $this->modalDetalle = true;
    }

    public function cerrarModalDetalle(): void
    {
        $this->modalDetalle = false;
        $this->cursoDetalle = null;
        $this->cursoSeleccionado = null;
    }

    public function abrirModalPlanificar(string $codCur): void
    {
        $curso = $this->obtenerCursoDetalle($codCur);

        if (! $curso) {
            $this->dispatch('error-general', mensaje: 'No se encontró el curso seleccionado.');
            return;
        }

        if (! $this->tablaExiste('plan_asignatura')) {
            $this->dispatch('error-general', mensaje: 'La tabla plan_asignatura no existe. No se puede planificar materias todavía.');
            return;
        }

        $this->cursoSeleccionado = $codCur;
        $this->cursoDetalle = $curso;

        $this->resetValidation();
        $this->resetFormularioPlan();

        $gestionActiva = $this->gestionActiva();

        if ($gestionActiva && isset($gestionActiva->cod_gea)) {
            $this->formPlan['cod_gea'] = $gestionActiva->cod_gea;
        }

        $this->modalPlanificar = true;
    }

    public function cerrarModalPlanificar(): void
    {
        $this->modalPlanificar = false;
        $this->cursoDetalle = null;
        $this->cursoSeleccionado = null;
        $this->resetValidation();
        $this->resetFormularioPlan();
    }

    /*
    |--------------------------------------------------------------------------
    | Reset formularios
    |--------------------------------------------------------------------------
    */
    private function resetFormulario(): void
    {
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

    private function resetFormularioPlan(): void
    {
        $this->formPlan = [
            'cod_gea' => '',
            'materias' => [],
            'cod_doc' => '',
            'hor_pas' => '',
            'est_pas' => 'ACTIVO',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | CRUD Curso
    |--------------------------------------------------------------------------
    */
    public function guardarCurso(): void
    {
        if (! $this->tablaExiste('curso')) {
            $this->dispatch('error-general', mensaje: 'La tabla de cursos no existe.');
            return;
        }

        $this->validate($this->rulesCrear(), $this->messages);

        DB::transaction(function () {
            $codCur = $this->generarCodigo('curso', 'cod_cur', 'CUR');

            $data = $this->payloadCurso(
                codCur: $codCur,
                nombre: $this->form['nom_cur'],
                orden: $this->form['ord_cur'],
                nivel: $this->form['niv_cur'],
                descripcion: $this->form['des_cur'],
                estado: $this->form['est_cur']
            );

            DB::table('curso')->insert($data);

            $cursoNuevo = DB::table('curso')->where('cod_cur', $codCur)->first();

            $this->registrarBitacoraSeguro(
                accion: 'CREAR_CURSO',
                tabla: 'curso',
                registro: $codCur,
                nombreRegistro: $cursoNuevo->nom_cur ?? $this->form['nom_cur'],
                descripcion: 'Se registró un nuevo curso académico base para la estructura institucional.',
                nivel: 'SUCCESS',
                resultado: 'EXITOSO',
                valoresNuevos: $cursoNuevo ? (array) $cursoNuevo : $data
            );

            $this->cerrarModalCrear();

            $this->dispatch('curso-creado');
            $this->dispatch('success-general', mensaje: 'Curso registrado correctamente.');
            $this->dispatch('actualizar-graficos-cursos', data: $this->datosGraficos);
        });
    }

    public function actualizarCurso(): void
    {
        if (! $this->tablaExiste('curso')) {
            $this->dispatch('error-general', mensaje: 'La tabla de cursos no existe.');
            return;
        }

        $this->validate($this->rulesEditar(), $this->messages);

        DB::transaction(function () {
            $curso = DB::table('curso')
                ->where('cod_cur', $this->formEditar['cod_cur'])
                ->first();

            if (! $curso) {
                $this->dispatch('error-general', mensaje: 'No se encontró el curso seleccionado.');
                return;
            }

            $valoresAnteriores = (array) $curso;

            $data = $this->payloadCurso(
                codCur: $this->formEditar['cod_cur'],
                nombre: $this->formEditar['nom_cur'],
                orden: $this->formEditar['ord_cur'],
                nivel: $this->formEditar['niv_cur'],
                descripcion: $this->formEditar['des_cur'],
                estado: $this->formEditar['est_cur'],
                incluirCodigo: false
            );

            DB::table('curso')
                ->where('cod_cur', $this->formEditar['cod_cur'])
                ->update($data);

            $cursoActualizado = DB::table('curso')
                ->where('cod_cur', $this->formEditar['cod_cur'])
                ->first();

            $this->registrarBitacoraSeguro(
                accion: 'ACTUALIZAR_CURSO',
                tabla: 'curso',
                registro: $this->formEditar['cod_cur'],
                nombreRegistro: $cursoActualizado->nom_cur ?? $this->formEditar['nom_cur'],
                descripcion: 'Se actualizó la información institucional de un curso académico.',
                nivel: 'INFO',
                resultado: 'EXITOSO',
                valoresAnteriores: $valoresAnteriores,
                valoresNuevos: $cursoActualizado ? (array) $cursoActualizado : $data
            );

            $this->cerrarModalEditar();

            $this->dispatch('curso-actualizado');
            $this->dispatch('success-general', mensaje: 'Curso actualizado correctamente.');
            $this->dispatch('actualizar-graficos-cursos', data: $this->datosGraficos);
        });
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
        if (! $this->tablaExiste('curso')) {
            $this->dispatch('error-general', mensaje: 'La tabla de cursos no existe.');
            return;
        }

        DB::transaction(function () use ($codCur, $estadoNuevo) {
            $curso = DB::table('curso')->where('cod_cur', $codCur)->first();

            if (! $curso) {
                $this->dispatch('error-general', mensaje: 'No se encontró el curso seleccionado.');
                return;
            }

            $estadoActual = $this->normalizarEstadoParaFormulario($curso->est_cur ?? 'ACTIVO');

            if ($estadoActual === $estadoNuevo) {
                $mensaje = $estadoNuevo === 'ACTIVO'
                    ? 'El curso ya se encuentra activo.'
                    : 'El curso ya se encuentra inactivo.';

                $this->dispatch('error-general', mensaje: $mensaje);
                return;
            }

            $valoresAnteriores = (array) $curso;
            $payload = [];

            if ($this->columnaExiste('curso', 'est_cur')) {
                $payload['est_cur'] = $this->valorEstadoParaBase('curso', 'est_cur', $estadoNuevo);
            }

            if ($this->columnaExiste('curso', 'updated_at')) {
                $payload['updated_at'] = now();
            }

            DB::table('curso')->where('cod_cur', $codCur)->update($payload);

            $cursoActualizado = DB::table('curso')->where('cod_cur', $codCur)->first();

            $accion = $estadoNuevo === 'ACTIVO' ? 'REACTIVAR_CURSO' : 'DESACTIVAR_CURSO';
            $descripcion = $estadoNuevo === 'ACTIVO'
                ? 'Se reactivó un curso académico base en el sistema.'
                : 'Se desactivó un curso académico base sin eliminar información histórica.';

            $this->registrarBitacoraSeguro(
                accion: $accion,
                tabla: 'curso',
                registro: $codCur,
                nombreRegistro: $cursoActualizado->nom_cur ?? 'Curso',
                descripcion: $descripcion,
                nivel: $estadoNuevo === 'ACTIVO' ? 'SUCCESS' : 'WARNING',
                resultado: 'EXITOSO',
                valoresAnteriores: $valoresAnteriores,
                valoresNuevos: $cursoActualizado ? (array) $cursoActualizado : $payload
            );

            $this->dispatch($estadoNuevo === 'ACTIVO' ? 'curso-reactivado' : 'curso-desactivado');
            $this->dispatch(
                'success-general',
                mensaje: $estadoNuevo === 'ACTIVO'
                    ? 'Curso reactivado correctamente.'
                    : 'Curso desactivado correctamente.'
            );

            $this->dispatch('actualizar-graficos-cursos', data: $this->datosGraficos);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Planificación de materias
    |--------------------------------------------------------------------------
    */
    public function guardarMateriaPlanificada(): void
    {
        if (! $this->cursoSeleccionado) {
            $this->dispatch('error-general', mensaje: 'Debes seleccionar un curso.');
            return;
        }

        if (! $this->tablaExiste('plan_asignatura')) {
            $this->dispatch('error-general', mensaje: 'La tabla plan_asignatura no existe.');
            return;
        }

        $this->validate($this->rulesPlan(), $this->messages);

        $materiasSeleccionadas = collect($this->formPlan['materias'])
            ->filter()
            ->unique()
            ->values();

        if ($materiasSeleccionadas->isEmpty()) {
            $this->addError('formPlan.materias', 'Debes seleccionar al menos una materia.');
            return;
        }

        $materiasDuplicadas = $materiasSeleccionadas->filter(function ($codAsignatura) {
            return $this->materiaYaPlanificada(
                codCurso: $this->cursoSeleccionado,
                codGestion: $this->formPlan['cod_gea'],
                codAsignatura: $codAsignatura
            );
        });

        if ($materiasDuplicadas->isNotEmpty()) {
            $this->addError('formPlan.materias', 'Una o más materias seleccionadas ya están asignadas a este curso en la gestión elegida.');
            return;
        }

        DB::transaction(function () use ($materiasSeleccionadas) {
            $registrosInsertados = [];

            foreach ($materiasSeleccionadas as $codAsignatura) {
                $codPas = $this->generarCodigo('plan_asignatura', 'cod_pas', 'PAS');

                $data = [];

                if ($this->columnaExiste('plan_asignatura', 'cod_pas')) {
                    $data['cod_pas'] = $codPas;
                }

                if ($this->columnaExiste('plan_asignatura', 'cod_cur')) {
                    $data['cod_cur'] = $this->cursoSeleccionado;
                }

                if ($this->columnaExiste('plan_asignatura', 'cod_gea')) {
                    $data['cod_gea'] = $this->formPlan['cod_gea'];
                }

                if ($this->columnaExiste('plan_asignatura', 'cod_asi')) {
                    $data['cod_asi'] = $codAsignatura;
                }

                if ($this->columnaExiste('plan_asignatura', 'cod_doc')) {
                    $data['cod_doc'] = $this->formPlan['cod_doc'];
                }

                if ($this->columnaExiste('plan_asignatura', 'hor_pas')) {
                    $data['hor_pas'] = (int) $this->formPlan['hor_pas'];
                }

                if ($this->columnaExiste('plan_asignatura', 'est_pas')) {
                    $data['est_pas'] = $this->formPlan['est_pas'];
                }

                if ($this->columnaExiste('plan_asignatura', 'created_at')) {
                    $data['created_at'] = now();
                }

                if ($this->columnaExiste('plan_asignatura', 'updated_at')) {
                    $data['updated_at'] = now();
                }

                DB::table('plan_asignatura')->insert($data);

                $registrosInsertados[] = $data;
            }

            $this->registrarBitacoraSeguro(
                accion: 'ASIGNAR_MATERIAS_CURSO',
                tabla: 'plan_asignatura',
                registro: $this->cursoSeleccionado,
                nombreRegistro: $this->nombreCurso($this->cursoSeleccionado),
                descripcion: 'Se asignaron varias materias al curso mediante el plan de asignatura de la gestión académica.',
                nivel: 'SUCCESS',
                resultado: 'EXITOSO',
                valoresNuevos: [
                    'curso' => $this->cursoSeleccionado,
                    'gestion' => $this->formPlan['cod_gea'],
                    'materias_asignadas' => $materiasSeleccionadas->values()->toArray(),
                    'docente' => $this->formPlan['cod_doc'],
                    'carga_horaria' => $this->formPlan['hor_pas'],
                    'estado' => $this->formPlan['est_pas'],
                    'registros' => $registrosInsertados,
                ]
            );

            $this->resetFormularioPlan();

            $gestionActiva = $this->gestionActiva();

            if ($gestionActiva && isset($gestionActiva->cod_gea)) {
                $this->formPlan['cod_gea'] = $gestionActiva->cod_gea;
            }

            if ($this->cursoSeleccionado) {
                $this->cursoDetalle = $this->obtenerCursoDetalle($this->cursoSeleccionado);
            }

            $this->dispatch('materia-planificada');
            $this->dispatch('success-general', mensaje: 'Materias asignadas correctamente al curso.');
            $this->dispatch('actualizar-graficos-cursos', data: $this->datosGraficos);
        });
    }

    public function quitarMateriaPlanificada(string $codPas): void
    {
        if (! $this->tablaExiste('plan_asignatura')) {
            $this->dispatch('error-general', mensaje: 'La tabla plan_asignatura no existe.');
            return;
        }

        DB::transaction(function () use ($codPas) {
            $plan = DB::table('plan_asignatura')
                ->when($this->columnaExiste('plan_asignatura', 'cod_pas'), fn($q) => $q->where('cod_pas', $codPas))
                ->first();

            if (! $plan) {
                $this->dispatch('error-general', mensaje: 'No se encontró la materia planificada.');
                return;
            }

            $valoresAnteriores = (array) $plan;

            DB::table('plan_asignatura')
                ->when($this->columnaExiste('plan_asignatura', 'cod_pas'), fn($q) => $q->where('cod_pas', $codPas))
                ->delete();

            $this->registrarBitacoraSeguro(
                accion: 'QUITAR_MATERIA_CURSO',
                tabla: 'plan_asignatura',
                registro: $codPas,
                nombreRegistro: $this->nombreCurso($this->cursoSeleccionado),
                descripcion: 'Se quitó una materia del plan de asignatura del curso seleccionado.',
                nivel: 'WARNING',
                resultado: 'EXITOSO',
                valoresAnteriores: $valoresAnteriores
            );

            if ($this->cursoSeleccionado) {
                $this->cursoDetalle = $this->obtenerCursoDetalle($this->cursoSeleccionado);
            }

            $this->dispatch('materia-quitada');
            $this->dispatch('success-general', mensaje: 'Materia retirada del curso.');
            $this->dispatch('actualizar-graficos-cursos', data: $this->datosGraficos);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Filtros
    |--------------------------------------------------------------------------
    */
    public function limpiarFiltros(): void
    {
        $this->reset([
            'search',
            'estado',
            'nivel',
            'gestionFiltro',
            'planificacion',
        ]);

        $this->resetPage();
    }

    /*
    |--------------------------------------------------------------------------
    | Query principal
    |--------------------------------------------------------------------------
    */
    private function cursosQuery()
    {
        if (! $this->tablaExiste('curso')) {
            return collect();
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

        if ($this->planificacion !== '') {
            $codGestion = $this->gestionFiltro ?: optional($this->gestionActiva())->cod_gea;

            if ($this->planificacion === 'con_materias') {
                $query->whereExists(function ($sub) use ($codGestion) {
                    $sub->select(DB::raw(1))
                        ->from('plan_asignatura')
                        ->whereColumn('plan_asignatura.cod_cur', 'curso.cod_cur');

                    if ($codGestion && $this->columnaExiste('plan_asignatura', 'cod_gea')) {
                        $sub->where('plan_asignatura.cod_gea', $codGestion);
                    }
                });
            }

            if ($this->planificacion === 'sin_materias') {
                $query->whereNotExists(function ($sub) use ($codGestion) {
                    $sub->select(DB::raw(1))
                        ->from('plan_asignatura')
                        ->whereColumn('plan_asignatura.cod_cur', 'curso.cod_cur');

                    if ($codGestion && $this->columnaExiste('plan_asignatura', 'cod_gea')) {
                        $sub->where('plan_asignatura.cod_gea', $codGestion);
                    }
                });
            }
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

    /*
    |--------------------------------------------------------------------------
    | Propiedades computadas
    |--------------------------------------------------------------------------
    */
    public function getCursosProperty()
    {
        if (! $this->tablaExiste('curso')) {
            return collect();
        }

        return $this->cursosQuery()
            ->paginate($this->perPage)
            ->through(fn($curso) => $this->mapearCurso($curso));
    }

    public function getTotalCursosProperty(): int
    {
        return $this->tablaExiste('curso') ? DB::table('curso')->count() : 0;
    }

    public function getTotalActivosProperty(): int
    {
        if (! $this->tablaExiste('curso') || ! $this->columnaExiste('curso', 'est_cur')) {
            return 0;
        }

        return $this->conteoPorEstado('curso', 'est_cur', 'ACTIVO');
    }

    public function getTotalInactivosProperty(): int
    {
        if (! $this->tablaExiste('curso') || ! $this->columnaExiste('curso', 'est_cur')) {
            return 0;
        }

        return $this->conteoPorEstado('curso', 'est_cur', 'INACTIVO');
    }

    public function getTotalConMateriasProperty(): int
    {
        if (! $this->tablaExiste('curso') || ! $this->tablaExiste('plan_asignatura')) {
            return 0;
        }

        $codGestion = $this->gestionFiltro ?: optional($this->gestionActiva())->cod_gea;

        return DB::table('curso')
            ->whereExists(function ($query) use ($codGestion) {
                $query->select(DB::raw(1))
                    ->from('plan_asignatura')
                    ->whereColumn('plan_asignatura.cod_cur', 'curso.cod_cur');

                if ($codGestion && $this->columnaExiste('plan_asignatura', 'cod_gea')) {
                    $query->where('plan_asignatura.cod_gea', $codGestion);
                }
            })
            ->count();
    }

    public function getTotalSinMateriasProperty(): int
    {
        if (! $this->tablaExiste('curso')) {
            return 0;
        }

        return max($this->totalCursos - $this->totalConMaterias, 0);
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

    public function getGestionesProperty()
    {
        if (! $this->tablaExiste('gestion_academica')) {
            return collect();
        }

        return DB::table('gestion_academica')
            ->select(['cod_gea', 'ani_gea', 'est_gea'])
            ->orderByDesc('ani_gea')
            ->get();
    }

    public function getAsignaturasProperty()
    {
        $tabla = $this->tablaAsignatura();

        if (! $this->tablaExiste($tabla)) {
            return collect();
        }

        $codigo = $this->columnaCodigoAsignatura();
        $nombre = $this->columnaNombreAsignatura();

        if (! $this->columnaExiste($tabla, $codigo) || ! $this->columnaExiste($tabla, $nombre)) {
            return collect();
        }

        return DB::table($tabla)
            ->select([$codigo . ' as codigo', $nombre . ' as nombre'])
            ->orderBy($nombre)
            ->get();
    }

    public function getDocentesProperty()
    {
        if (! $this->tablaExiste('docente') || ! $this->columnaExiste('docente', 'cod_doc')) {
            return collect();
        }

        $query = DB::table('docente')->select('docente.cod_doc');

        if ($this->tablaExiste('personal_institucional') && $this->columnaExiste('docente', 'cod_pin')) {
            $query->leftJoin('personal_institucional', 'personal_institucional.cod_pin', '=', 'docente.cod_pin');

            if ($this->tablaExiste('persona') && $this->columnaExiste('personal_institucional', 'cod_per')) {
                $query->leftJoin('persona', 'persona.cod_per', '=', 'personal_institucional.cod_per');

                $query->addSelect(DB::raw("
                    TRIM(
                        COALESCE(persona.nom_per, '') || ' ' ||
                        COALESCE(persona.ape_pat_per, '') || ' ' ||
                        COALESCE(persona.ape_mat_per, '')
                    ) as nombre
                "));
            } else {
                $query->addSelect(DB::raw("'Docente institucional' as nombre"));
            }
        } else {
            $query->addSelect(DB::raw("'Docente' as nombre"));
        }

        return $query->orderBy('nombre')->get();
    }

    public function getNivelesDisponiblesProperty(): array
    {
        return [
            'Técnica Tecnológica General',
            'Especialización Técnica',
            'Secundaria Comunitaria Productiva',
        ];
    }

    public function getDatosGraficoPlanificacionProperty(): array
    {
        return [
            'labels' => ['Con materias', 'Sin materias'],
            'data' => [
                $this->totalConMaterias,
                $this->totalSinMaterias,
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
            'planificacion' => $this->datosGraficoPlanificacion,
            'inscritos' => $this->datosGraficoInscritos,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers de mapeo
    |--------------------------------------------------------------------------
    */
    private function mapearCurso(object $curso): array
    {
        $codCur = $curso->cod_cur ?? null;
        $nombre = $curso->nom_cur ?? 'Curso sin nombre';

        return [
            'cod_cur' => $codCur,
            'nom_cur' => $nombre,
            'ord_cur' => $curso->ord_cur ?? $this->inferirOrdenCurso($nombre),
            'niv_cur' => $curso->niv_cur ?? $this->inferirNivelCurso($nombre),
            'des_cur' => $curso->des_cur ?? 'Sin descripción registrada.',
            'est_cur' => $this->normalizarEstadoParaFormulario($curso->est_cur ?? 'ACTIVO'),
            'estado_label' => $this->estadoCursoLabel($curso->est_cur ?? 'ACTIVO'),
            'materias_count' => $this->contarMateriasCurso($codCur),
            'inscritos_count' => $this->contarInscritosCurso($codCur),
            'tiene_planificacion' => $this->contarMateriasCurso($codCur) > 0,
            'materias' => $this->materiasCurso($codCur),
            'created_at' => $curso->created_at ?? null,
            'updated_at' => $curso->updated_at ?? null,
        ];
    }

    private function obtenerCursoDetalle(string $codCur): ?array
    {
        if (! $this->tablaExiste('curso')) {
            return null;
        }

        $curso = DB::table('curso')->where('cod_cur', $codCur)->first();

        if (! $curso) {
            return null;
        }

        return $this->mapearCurso($curso);
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
    | Conteos
    |--------------------------------------------------------------------------
    */
    private function contarMateriasCurso(?string $codCur): int
    {
        if (! $codCur || ! $this->tablaExiste('plan_asignatura') || ! $this->columnaExiste('plan_asignatura', 'cod_cur')) {
            return 0;
        }

        $query = DB::table('plan_asignatura')->where('cod_cur', $codCur);

        $codGestion = $this->gestionFiltro ?: optional($this->gestionActiva())->cod_gea;

        if ($codGestion && $this->columnaExiste('plan_asignatura', 'cod_gea')) {
            $query->where('cod_gea', $codGestion);
        }

        return $query->count();
    }

    private function contarInscritosCurso(?string $codCur): int
    {
        if (! $codCur || ! $this->tablaExiste('inscripcion_estudiante') || ! $this->columnaExiste('inscripcion_estudiante', 'cod_cur')) {
            return 0;
        }

        $query = DB::table('inscripcion_estudiante')->where('cod_cur', $codCur);

        $codGestion = $this->gestionFiltro ?: optional($this->gestionActiva())->cod_gea;

        if ($codGestion && $this->columnaExiste('inscripcion_estudiante', 'cod_gea')) {
            $query->where('cod_gea', $codGestion);
        }

        return $query->count();
    }

    private function materiasCurso(?string $codCur)
    {
        if (! $codCur || ! $this->tablaExiste('plan_asignatura')) {
            return collect();
        }

        $tablaAsignatura = $this->tablaAsignatura();
        $codAsignatura = $this->columnaCodigoAsignatura();
        $nomAsignatura = $this->columnaNombreAsignatura();

        $query = DB::table('plan_asignatura')
            ->where('plan_asignatura.cod_cur', $codCur);

        if (
            $this->tablaExiste($tablaAsignatura)
            && $this->columnaExiste('plan_asignatura', 'cod_asi')
            && $this->columnaExiste($tablaAsignatura, $codAsignatura)
        ) {
            $query->leftJoin($tablaAsignatura, "{$tablaAsignatura}.{$codAsignatura}", '=', 'plan_asignatura.cod_asi');
        }

        if ($this->tablaExiste('docente') && $this->columnaExiste('plan_asignatura', 'cod_doc')) {
            $query->leftJoin('docente', 'docente.cod_doc', '=', 'plan_asignatura.cod_doc');

            if ($this->tablaExiste('personal_institucional') && $this->columnaExiste('docente', 'cod_pin')) {
                $query->leftJoin('personal_institucional', 'personal_institucional.cod_pin', '=', 'docente.cod_pin');

                if ($this->tablaExiste('persona') && $this->columnaExiste('personal_institucional', 'cod_per')) {
                    $query->leftJoin('persona', 'persona.cod_per', '=', 'personal_institucional.cod_per');
                }
            }
        }

        $select = [
            'plan_asignatura.cod_pas',
            'plan_asignatura.cod_cur',
            'plan_asignatura.cod_gea',
            'plan_asignatura.cod_asi',
            'plan_asignatura.cod_doc',
            'plan_asignatura.hor_pas',
            'plan_asignatura.est_pas',
        ];

        if ($this->tablaExiste($tablaAsignatura) && $this->columnaExiste($tablaAsignatura, $nomAsignatura)) {
            $select[] = DB::raw("{$tablaAsignatura}.{$nomAsignatura} as materia");
        } else {
            $select[] = DB::raw("'Materia registrada' as materia");
        }

        if ($this->tablaExiste('persona')) {
            $select[] = DB::raw("
                TRIM(
                    COALESCE(persona.nom_per, '') || ' ' ||
                    COALESCE(persona.ape_pat_per, '') || ' ' ||
                    COALESCE(persona.ape_mat_per, '')
                ) as docente
            ");
        } else {
            $select[] = DB::raw("'Docente asignado' as docente");
        }

        $codGestion = $this->gestionFiltro ?: optional($this->gestionActiva())->cod_gea;

        if ($codGestion && $this->columnaExiste('plan_asignatura', 'cod_gea')) {
            $query->where('plan_asignatura.cod_gea', $codGestion);
        }

        return $query
            ->select($select)
            ->when($this->columnaExiste('plan_asignatura', 'created_at'), fn($q) => $q->orderByDesc('plan_asignatura.created_at'))
            ->get();
    }

    private function materiaYaPlanificada(string $codCurso, string $codGestion, string $codAsignatura): bool
    {
        if (! $this->tablaExiste('plan_asignatura')) {
            return false;
        }

        $query = DB::table('plan_asignatura');

        if ($this->columnaExiste('plan_asignatura', 'cod_cur')) {
            $query->where('cod_cur', $codCurso);
        }

        if ($this->columnaExiste('plan_asignatura', 'cod_gea')) {
            $query->where('cod_gea', $codGestion);
        }

        if ($this->columnaExiste('plan_asignatura', 'cod_asi')) {
            $query->where('cod_asi', $codAsignatura);
        }

        return $query->exists();
    }

    /*
    |--------------------------------------------------------------------------
    | Gestión académica
    |--------------------------------------------------------------------------
    */
    private function gestionActiva(): ?object
    {
        if (! $this->tablaExiste('gestion_academica')) {
            return null;
        }

        $query = DB::table('gestion_academica');

        if ($this->columnaExiste('gestion_academica', 'est_gea')) {
            $query->where('est_gea', 'ACTIVO');
        }

        return $query
            ->when($this->columnaExiste('gestion_academica', 'ani_gea'), fn($q) => $q->orderByDesc('ani_gea'))
            ->first();
    }

    /*
    |--------------------------------------------------------------------------
    | Bitácora segura
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
                $data['val_ant_bit'] = $valoresAnteriores ? json_encode($valoresAnteriores, JSON_UNESCAPED_UNICODE) : null;
            }

            if ($this->columnaExiste('bitacora', 'val_nue_bit')) {
                $data['val_nue_bit'] = $valoresNuevos ? json_encode($valoresNuevos, JSON_UNESCAPED_UNICODE) : null;
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
    | Helpers de base de datos
    |--------------------------------------------------------------------------
    */
    private function tablaExiste(string $tabla): bool
    {
        try {
            return Schema::hasTable($tabla);
        } catch (\Throwable) {
            return false;
        }
    }

    private function columnaExiste(string $tabla, string $columna): bool
    {
        try {
            return Schema::hasTable($tabla) && Schema::hasColumn($tabla, $columna);
        } catch (\Throwable) {
            return false;
        }
    }

    private function tipoColumna(string $tabla, string $columna): ?string
    {
        try {
            if (! $this->columnaExiste($tabla, $columna)) {
                return null;
            }

            return Schema::getColumnType($tabla, $columna);
        } catch (\Throwable) {
            return null;
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

        if ($orden >= 1 && $orden <= 3) {
            return 'Técnica Tecnológica General';
        }

        if ($orden >= 4 && $orden <= 6) {
            return 'Especialización Técnica';
        }

        return 'Secundaria Comunitaria Productiva';
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

    /*
    |--------------------------------------------------------------------------
    | Detección flexible de asignaturas
    |--------------------------------------------------------------------------
    */
    private function tablaAsignatura(): string
    {
        if ($this->tablaExiste('asignatura')) {
            return 'asignatura';
        }

        if ($this->tablaExiste('materia')) {
            return 'materia';
        }

        return 'asignatura';
    }

    private function columnaCodigoAsignatura(): string
    {
        $tabla = $this->tablaAsignatura();

        if ($this->columnaExiste($tabla, 'cod_asi')) {
            return 'cod_asi';
        }

        if ($this->columnaExiste($tabla, 'cod_mat')) {
            return 'cod_mat';
        }

        return 'cod_asi';
    }

    private function columnaNombreAsignatura(): string
    {
        $tabla = $this->tablaAsignatura();

        if ($this->columnaExiste($tabla, 'nom_asi')) {
            return 'nom_asi';
        }

        if ($this->columnaExiste($tabla, 'nom_mat')) {
            return 'nom_mat';
        }

        if ($this->columnaExiste($tabla, 'nombre')) {
            return 'nombre';
        }

        return 'nom_asi';
    }

    /*
    |--------------------------------------------------------------------------
    | Render
    |--------------------------------------------------------------------------
    */
    public function render()
    {
        return view('livewire.admin.gestion-curso', [
            'cursos' => $this->cursos,
            'gestiones' => $this->gestiones,
            'asignaturas' => $this->asignaturas,
            'docentes' => $this->docentes,
            'nivelesDisponibles' => $this->nivelesDisponibles,

            'totalCursos' => $this->totalCursos,
            'totalActivos' => $this->totalActivos,
            'totalInactivos' => $this->totalInactivos,
            'totalConMaterias' => $this->totalConMaterias,
            'totalSinMaterias' => $this->totalSinMaterias,
            'totalInscritos' => $this->totalInscritos,

            'gestionActiva' => $this->gestionActiva(),
            'datosGraficos' => $this->datosGraficos,
        ]);
    }
}
