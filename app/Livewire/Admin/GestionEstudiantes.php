<?php

namespace App\Livewire\Admin;

use App\Models\Curso;
use App\Models\EspecialidadTecnica;
use App\Models\Estudiante;
use App\Models\GestionAcademica;
use App\Models\InscripcionEstudiante;
use App\Models\InstitucionProcedencia;
use App\Models\Paralelo;
use App\Models\Persona;
use App\Models\TipoVinculacionEstudiante;
use App\Services\BitacoraService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class GestionEstudiantes extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'tailwind';

    /*
    |--------------------------------------------------------------------------
    | Filtros principales
    |--------------------------------------------------------------------------
    */
    public string $search = '';
    public string $filtroCurso = '';
    public string $filtroParalelo = '';
    public string $filtroEspecialidad = '';
    public string $filtroEstado = '';
    public string $filtroInscripcion = '';
    public string $filtroVinculacion = '';
    public string $filtroProcedencia = '';
    public int $perPage = 10;

    /*
    |--------------------------------------------------------------------------
    | Formatos de vista
    |--------------------------------------------------------------------------
    */
    public string $vistaActiva = 'todos';
    public string $cursoCarpetaSeleccionado = '';
    public string $especialidadCarpetaSeleccionada = '';
    public string $procedenciaCarpetaSeleccionada = '';

    /*
    |--------------------------------------------------------------------------
    | Modales / paneles
    |--------------------------------------------------------------------------
    */
    public bool $modalRegistrar = false;
    public bool $modalEditar = false;
    public bool $modalInscripcion = false;
    public bool $modalHistorial = false;
    public bool $panelDetalle = false;

    /*
    |--------------------------------------------------------------------------
    | Estado interno
    |--------------------------------------------------------------------------
    */
    public ?Estudiante $estudianteDetalle = null;
    public ?string $codEstudianteSeleccionado = null;
    public ?string $gestionActualId = null;
    public string $nombreGestionActual = 'Sin gestión activa';

    /*
    |--------------------------------------------------------------------------
    | Formularios
    |--------------------------------------------------------------------------
    */
    public array $formEstudiante = [
        'cod_est' => '',
        'cod_per' => '',
        'rud_est' => '',
        'cod_tve' => '',
        'cod_ipe' => '',
        'cod_esp' => '',
        'est_est' => 'ACTIVO',
    ];

    public array $formInscripcion = [
        'cod_est' => '',
        'cod_cur' => '',
        'cod_par' => '',
        'cod_gea' => '',
        'fec_ins' => '',
        'est_ins' => 'ACTIVO',
    ];

    /*
    |--------------------------------------------------------------------------
    | Ciclo de vida
    |--------------------------------------------------------------------------
    */
    public function mount(): void
    {
        $this->resolverGestionActual();

        $this->formInscripcion['cod_gea'] = $this->gestionActualId ?? '';
        $this->formInscripcion['fec_ins'] = now()->toDateString();
    }

    /*
    |--------------------------------------------------------------------------
    | Reactividad de filtros
    |--------------------------------------------------------------------------
    */
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroCurso(): void
    {
        $this->filtroParalelo = '';
        $this->resetPage();
    }

    public function updatingFiltroParalelo(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroEspecialidad(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroEstado(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroInscripcion(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroVinculacion(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroProcedencia(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function updatedFormInscripcionCodCur(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Aunque paralelo no depende físicamente de curso en tu BD,
        | reiniciarlo evita errores administrativos al cambiar de curso.
        |--------------------------------------------------------------------------
        */
        $this->formInscripcion['cod_par'] = '';
    }

    /*
    |--------------------------------------------------------------------------
    | Acciones generales
    |--------------------------------------------------------------------------
    */
    public function limpiarFiltros(): void
    {
        $this->reset([
            'search',
            'filtroCurso',
            'filtroParalelo',
            'filtroEspecialidad',
            'filtroEstado',
            'filtroInscripcion',
            'filtroVinculacion',
            'filtroProcedencia',
            'cursoCarpetaSeleccionado',
            'especialidadCarpetaSeleccionada',
            'procedenciaCarpetaSeleccionada',
        ]);

        $this->resetPage();
    }

    private function cerrarTodosLosModales(): void
    {
        $this->modalRegistrar = false;
        $this->modalEditar = false;
        $this->modalInscripcion = false;
        $this->modalHistorial = false;
        $this->panelDetalle = false;
    }

    private function limpiarSeleccion(): void
    {
        $this->estudianteDetalle = null;
        $this->codEstudianteSeleccionado = null;
    }

    private function resetFormEstudiante(): void
    {
        $this->formEstudiante = [
            'cod_est' => '',
            'cod_per' => '',
            'rud_est' => '',
            'cod_tve' => '',
            'cod_ipe' => '',
            'cod_esp' => '',
            'est_est' => 'ACTIVO',
        ];
    }

    private function resetFormInscripcion(): void
    {
        $this->formInscripcion = [
            'cod_est' => '',
            'cod_cur' => '',
            'cod_par' => '',
            'cod_gea' => $this->gestionActualId ?? '',
            'fec_ins' => now()->toDateString(),
            'est_ins' => 'ACTIVO',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Formatos de vista
    |--------------------------------------------------------------------------
    */
    public function cambiarVista(string $vista): void
    {
        if (! in_array($vista, ['todos', 'cursos', 'especialidades', 'procedencias', 'tarjetas'], true)) {
            return;
        }

        $this->vistaActiva = $vista;

        if ($vista !== 'cursos') {
            $this->cursoCarpetaSeleccionado = '';
            $this->filtroCurso = '';
            $this->filtroParalelo = '';
        }

        if ($vista !== 'especialidades') {
            $this->especialidadCarpetaSeleccionada = '';
            $this->filtroEspecialidad = '';
        }

        if ($vista !== 'procedencias') {
            $this->procedenciaCarpetaSeleccionada = '';
            $this->filtroProcedencia = '';
        }

        $this->resetPage();
    }

    public function seleccionarCursoCarpeta(string $codCurso): void
    {
        $cursoExiste = Curso::query()
            ->where('cod_cur', $codCurso)
            ->where('est_cur', 'ACTIVO')
            ->exists();

        if (! $cursoExiste) {
            $this->dispatch(
                'error-general',
                mensaje: 'El curso seleccionado no existe o no está activo.'
            );

            return;
        }

        $this->vistaActiva = 'cursos';
        $this->cursoCarpetaSeleccionado = $codCurso;
        $this->filtroCurso = $codCurso;
        $this->filtroParalelo = '';
        $this->resetPage();
    }

    public function limpiarCursoCarpeta(): void
    {
        $this->cursoCarpetaSeleccionado = '';
        $this->filtroCurso = '';
        $this->filtroParalelo = '';
        $this->resetPage();
    }

    public function seleccionarEspecialidadCarpeta(string $codEspecialidad): void
    {
        $especialidadExiste = EspecialidadTecnica::query()
            ->where('cod_esp', $codEspecialidad)
            ->where('est_esp', 'ACTIVO')
            ->exists();

        if (! $especialidadExiste) {
            $this->dispatch(
                'error-general',
                mensaje: 'La especialidad seleccionada no existe o no está activa.'
            );

            return;
        }

        $this->vistaActiva = 'especialidades';
        $this->especialidadCarpetaSeleccionada = $codEspecialidad;
        $this->filtroEspecialidad = $codEspecialidad;
        $this->resetPage();
    }

    public function limpiarEspecialidadCarpeta(): void
    {
        $this->especialidadCarpetaSeleccionada = '';
        $this->filtroEspecialidad = '';
        $this->resetPage();
    }

    public function seleccionarProcedenciaCarpeta(string $codProcedencia): void
    {
        $procedenciaExiste = InstitucionProcedencia::query()
            ->where('cod_ipe', $codProcedencia)
            ->exists();

        if (! $procedenciaExiste) {
            $this->dispatch(
                'error-general',
                mensaje: 'La institución de procedencia seleccionada no existe.'
            );

            return;
        }

        $this->vistaActiva = 'procedencias';
        $this->procedenciaCarpetaSeleccionada = $codProcedencia;
        $this->filtroProcedencia = $codProcedencia;
        $this->resetPage();
    }

    public function limpiarProcedenciaCarpeta(): void
    {
        $this->procedenciaCarpetaSeleccionada = '';
        $this->filtroProcedencia = '';
        $this->resetPage();
    }

    /*
    |--------------------------------------------------------------------------
    | Gestión académica activa
    |--------------------------------------------------------------------------
    */
    private function resolverGestionActual(): void
    {
        $query = GestionAcademica::query()
            ->where('est_gea', 'ACTIVO');

        if (Schema::hasColumn('gestion_academica', 'ani_gea')) {
            $query->orderByDesc('ani_gea');
        }

        if (Schema::hasColumn('gestion_academica', 'fii_gea')) {
            $query->orderByDesc('fii_gea');
        }

        $gestion = $query->first();

        if (! $gestion) {
            $this->gestionActualId = null;
            $this->nombreGestionActual = 'Sin gestión activa';
            return;
        }

        $this->gestionActualId = $gestion->cod_gea;

        $this->nombreGestionActual = ! empty($gestion->ani_gea)
            ? (string) $gestion->ani_gea
            : $gestion->cod_gea;
    }

    /*
    |--------------------------------------------------------------------------
    | Carga de estudiante
    |--------------------------------------------------------------------------
    */
    private function cargarEstudianteDetalle(string $codEstudiante): Estudiante
    {
        return Estudiante::query()
            ->with([
                'persona.usuario',
                'tipoVinculacion',
                'institucionProcedencia',
                'especialidad',
                'inscripciones.curso',
                'inscripciones.paralelo',
                'inscripciones.gestionAcademica',
                'calificaciones',
            ])
            ->findOrFail($codEstudiante);
    }

    public function inscripcionActual(?Estudiante $estudiante = null): mixed
    {
        $estudiante = $estudiante ?? $this->estudianteDetalle;

        if (! $estudiante || ! $this->gestionActualId) {
            return null;
        }

        $inscripciones = $estudiante->relationLoaded('inscripciones')
            ? $estudiante->inscripciones
            : $estudiante->inscripciones()->get();

        return $inscripciones
            ->where('cod_gea', $this->gestionActualId)
            ->sortByDesc(function ($item) {
                $campoFecha = $this->campoFechaInscripcion();

                return $campoFecha && ! empty($item->{$campoFecha})
                    ? (string) $item->{$campoFecha}
                    : (string) ($item->created_at ?? $item->getKey());
            })
            ->first();
    }

    public function obtenerEstadoInscripcion(?Estudiante $estudiante = null): string
    {
        $inscripcion = $this->inscripcionActual($estudiante);

        if (! $inscripcion) {
            return 'SIN_INSCRIPCION';
        }

        $campoEstado = $this->campoEstadoInscripcion();

        if (! $campoEstado) {
            return 'INSCRITO';
        }

        $estado = $inscripcion->{$campoEstado} ?? 'ACTIVO';

        return $estado === 'ACTIVO'
            ? 'INSCRITO'
            : 'PENDIENTE';
    }

    /*
    |--------------------------------------------------------------------------
    | Panel detalle
    |--------------------------------------------------------------------------
    */
    public function abrirPanelDetalle(string $codEstudiante): void
    {
        $this->resetValidation();
        $this->cerrarTodosLosModales();

        $this->estudianteDetalle = $this->cargarEstudianteDetalle($codEstudiante);
        $this->codEstudianteSeleccionado = $codEstudiante;
        $this->panelDetalle = true;
    }

    public function cerrarPanelDetalle(): void
    {
        $this->panelDetalle = false;
        $this->limpiarSeleccion();
        $this->resetValidation();
    }

    /*
    |--------------------------------------------------------------------------
    | Registrar estudiante
    |--------------------------------------------------------------------------
    */
    public function abrirModalRegistrar(): void
    {
        $this->resetValidation();
        $this->cerrarTodosLosModales();
        $this->limpiarSeleccion();
        $this->resetFormEstudiante();

        $this->modalRegistrar = true;
    }

    public function cerrarModalRegistrar(): void
    {
        $this->modalRegistrar = false;
        $this->resetFormEstudiante();
        $this->resetValidation();
    }

    public function guardarEstudiante(): void
    {
        if (! $this->puedeGuardarEstudiante()) {
            $this->dispatch(
                'error-general',
                mensaje: 'Completa persona, RUD/RUDE, vinculación, procedencia y estado antes de guardar.'
            );

            return;
        }

        $this->validate($this->rulesEstudiante(), $this->messagesEstudiante());

        DB::transaction(function () {
            $personaYaEsEstudiante = Estudiante::query()
                ->where('cod_per', $this->formEstudiante['cod_per'])
                ->exists();

            if ($personaYaEsEstudiante) {
                $this->addError(
                    'formEstudiante.cod_per',
                    'Esta persona ya se encuentra registrada como estudiante.'
                );

                $this->dispatch(
                    'error-general',
                    mensaje: 'Esta persona ya se encuentra registrada como estudiante.'
                );

                return;
            }

            $estudiante = Estudiante::create([
                'cod_per' => $this->formEstudiante['cod_per'],
                'rud_est' => trim($this->formEstudiante['rud_est']),
                'cod_tve' => $this->formEstudiante['cod_tve'],
                'cod_ipe' => $this->formEstudiante['cod_ipe'],
                'cod_esp' => $this->formEstudiante['cod_esp'] ?: null,
                'est_est' => $this->formEstudiante['est_est'],
            ]);

            $estudiante = $this->cargarEstudianteDetalle($estudiante->cod_est);

            $this->registrarBitacora(
                accion: 'REGISTRAR_ESTUDIANTE',
                tabla: 'estudiante',
                registro: $estudiante->cod_est,
                nombreRegistro: $this->nombreEstudiante($estudiante),
                descripcion: 'Se registró un nuevo estudiante en el sistema académico institucional.',
                nivel: 'SUCCESS',
                resultado: 'EXITOSO',
                valoresNuevos: $this->resumenEstudiante($estudiante)
            );

            $this->cerrarModalRegistrar();

            $this->dispatch(
                'success-general',
                mensaje: 'Estudiante registrado correctamente.'
            );
        });

        $this->resetPage();
    }

    /*
    |--------------------------------------------------------------------------
    | Editar estudiante
    |--------------------------------------------------------------------------
    */
    public function abrirModalEditar(string $codEstudiante): void
    {
        $this->resetValidation();
        $this->cerrarTodosLosModales();

        $estudiante = $this->cargarEstudianteDetalle($codEstudiante);

        $this->estudianteDetalle = $estudiante;
        $this->codEstudianteSeleccionado = $codEstudiante;

        $this->formEstudiante = [
            'cod_est' => $estudiante->cod_est,
            'cod_per' => $estudiante->cod_per,
            'rud_est' => $estudiante->rud_est ?? '',
            'cod_tve' => $estudiante->cod_tve ?? '',
            'cod_ipe' => $estudiante->cod_ipe ?? '',
            'cod_esp' => $estudiante->cod_esp ?? '',
            'est_est' => $estudiante->est_est ?? 'ACTIVO',
        ];

        $this->modalEditar = true;
    }

    public function cerrarModalEditar(): void
    {
        $this->modalEditar = false;
        $this->resetFormEstudiante();
        $this->limpiarSeleccion();
        $this->resetValidation();
    }

    public function actualizarEstudiante(): void
    {
        if (! $this->puedeGuardarEstudiante()) {
            $this->dispatch(
                'error-general',
                mensaje: 'Completa RUD/RUDE, vinculación, procedencia y estado antes de actualizar.'
            );

            return;
        }

        $this->validate($this->rulesEstudiante(true), $this->messagesEstudiante());

        DB::transaction(function () {
            $estudiante = Estudiante::query()
                ->with([
                    'persona',
                    'tipoVinculacion',
                    'institucionProcedencia',
                    'especialidad',
                    'inscripciones.curso',
                    'inscripciones.paralelo',
                    'inscripciones.gestionAcademica',
                ])
                ->lockForUpdate()
                ->findOrFail($this->formEstudiante['cod_est']);

            $valoresAnteriores = $this->resumenEstudiante($estudiante);

            $estudiante->update([
                'rud_est' => trim($this->formEstudiante['rud_est']),
                'cod_tve' => $this->formEstudiante['cod_tve'],
                'cod_ipe' => $this->formEstudiante['cod_ipe'],
                'cod_esp' => $this->formEstudiante['cod_esp'] ?: null,
                'est_est' => $this->formEstudiante['est_est'],
            ]);

            $estudianteActualizado = $this->cargarEstudianteDetalle($estudiante->cod_est);

            $this->registrarBitacora(
                accion: 'EDITAR_ESTUDIANTE',
                tabla: 'estudiante',
                registro: $estudianteActualizado->cod_est,
                nombreRegistro: $this->nombreEstudiante($estudianteActualizado),
                descripcion: 'Se actualizó la información académica-administrativa del estudiante.',
                nivel: 'WARNING',
                resultado: 'EXITOSO',
                valoresAnteriores: $valoresAnteriores,
                valoresNuevos: $this->resumenEstudiante($estudianteActualizado)
            );

            $this->cerrarModalEditar();

            $this->dispatch(
                'success-general',
                mensaje: 'Información del estudiante actualizada correctamente.'
            );
        });

        $this->resetPage();
    }

    /*
    |--------------------------------------------------------------------------
    | Inscripción académica
    |--------------------------------------------------------------------------
    */
    public function abrirModalInscripcion(string $codEstudiante): void
    {
        $this->resetValidation();
        $this->cerrarTodosLosModales();
        $this->resetFormInscripcion();

        $estudiante = $this->cargarEstudianteDetalle($codEstudiante);

        if (($estudiante->est_est ?? 'ACTIVO') !== 'ACTIVO') {
            $this->dispatch(
                'error-general',
                mensaje: 'No puedes inscribir a un estudiante que no se encuentra activo.'
            );

            return;
        }

        $inscripcionActual = $this->inscripcionActual($estudiante);

        $this->formInscripcion = [
            'cod_est' => $estudiante->cod_est,
            'cod_cur' => $inscripcionActual->cod_cur ?? '',
            'cod_par' => $inscripcionActual->cod_par ?? '',
            'cod_gea' => $inscripcionActual->cod_gea ?? ($this->gestionActualId ?? ''),
            'fec_ins' => $this->obtenerFechaInscripcion($inscripcionActual) ?? now()->toDateString(),
            'est_ins' => $this->obtenerEstadoModeloInscripcion($inscripcionActual) ?? 'ACTIVO',
        ];

        $this->estudianteDetalle = $estudiante;
        $this->codEstudianteSeleccionado = $codEstudiante;
        $this->modalInscripcion = true;
    }

    public function cerrarModalInscripcion(): void
    {
        $this->modalInscripcion = false;
        $this->resetFormInscripcion();
        $this->limpiarSeleccion();
        $this->resetValidation();
    }

    public function guardarInscripcion(): void
    {
        if (! $this->puedeGuardarInscripcion()) {
            $this->dispatch(
                'error-general',
                mensaje: 'Selecciona gestión, curso, paralelo y estado de inscripción para continuar.'
            );

            return;
        }

        $this->validate($this->rulesInscripcion(), $this->messagesInscripcion());

        DB::transaction(function () {
            $estudiante = $this->cargarEstudianteDetalle($this->formInscripcion['cod_est']);

            if (($estudiante->est_est ?? 'ACTIVO') !== 'ACTIVO') {
                $this->dispatch(
                    'error-general',
                    mensaje: 'El estudiante no está activo. No se puede registrar la inscripción.'
                );

                return;
            }

            $cursoExiste = Curso::query()
                ->where('cod_cur', $this->formInscripcion['cod_cur'])
                ->where('est_cur', 'ACTIVO')
                ->exists();

            if (! $cursoExiste) {
                $this->addError(
                    'formInscripcion.cod_cur',
                    'El curso seleccionado no existe o no está activo.'
                );

                $this->dispatch(
                    'error-general',
                    mensaje: 'El curso seleccionado no existe o no está activo.'
                );

                return;
            }

            $paraleloExiste = Paralelo::query()
                ->where('cod_par', $this->formInscripcion['cod_par'])
                ->where('est_par', 'ACTIVO')
                ->exists();

            if (! $paraleloExiste) {
                $this->addError(
                    'formInscripcion.cod_par',
                    'El paralelo seleccionado no existe o no está activo.'
                );

                $this->dispatch(
                    'error-general',
                    mensaje: 'El paralelo seleccionado no existe o no está activo.'
                );

                return;
            }

            $gestionExiste = GestionAcademica::query()
                ->where('cod_gea', $this->formInscripcion['cod_gea'])
                ->where('est_gea', 'ACTIVO')
                ->exists();

            if (! $gestionExiste) {
                $this->addError(
                    'formInscripcion.cod_gea',
                    'La gestión académica seleccionada no existe o no está activa.'
                );

                $this->dispatch(
                    'error-general',
                    mensaje: 'La gestión académica seleccionada no existe o no está activa.'
                );

                return;
            }

            $inscripcion = InscripcionEstudiante::query()
                ->where('cod_est', $this->formInscripcion['cod_est'])
                ->where('cod_gea', $this->formInscripcion['cod_gea'])
                ->first();

            $payload = $this->payloadInscripcion();

            if ($inscripcion) {
                $valoresAnteriores = $this->resumenInscripcion($inscripcion);

                $inscripcion->update($payload);

                $inscripcionActualizada = $inscripcion->fresh([
                    'estudiante.persona',
                    'curso',
                    'paralelo',
                    'gestionAcademica',
                ]);

                $this->registrarBitacora(
                    accion: 'ACTUALIZAR_INSCRIPCION_ESTUDIANTE',
                    tabla: 'inscripcion_estudiante',
                    registro: (string) $inscripcionActualizada->getKey(),
                    nombreRegistro: $this->nombreEstudiante($estudiante),
                    descripcion: 'Se actualizó la inscripción académica del estudiante en la gestión seleccionada.',
                    nivel: 'WARNING',
                    resultado: 'EXITOSO',
                    valoresAnteriores: $valoresAnteriores,
                    valoresNuevos: $this->resumenInscripcion($inscripcionActualizada)
                );

                $mensaje = 'Inscripción académica actualizada correctamente.';
            } else {
                $inscripcion = InscripcionEstudiante::create($payload);

                $inscripcion = $inscripcion->fresh([
                    'estudiante.persona',
                    'curso',
                    'paralelo',
                    'gestionAcademica',
                ]);

                $this->registrarBitacora(
                    accion: 'INSCRIBIR_ESTUDIANTE',
                    tabla: 'inscripcion_estudiante',
                    registro: (string) $inscripcion->getKey(),
                    nombreRegistro: $this->nombreEstudiante($estudiante),
                    descripcion: 'Se registró la inscripción académica del estudiante en la gestión seleccionada.',
                    nivel: 'SUCCESS',
                    resultado: 'EXITOSO',
                    valoresNuevos: $this->resumenInscripcion($inscripcion)
                );

                $mensaje = 'Estudiante inscrito correctamente en la gestión actual.';
            }

            $this->cerrarModalInscripcion();

            $this->dispatch(
                'success-general',
                mensaje: $mensaje
            );
        });

        $this->resetPage();
    }

    /*
    |--------------------------------------------------------------------------
    | Historial
    |--------------------------------------------------------------------------
    */
    public function abrirModalHistorial(string $codEstudiante): void
    {
        $this->resetValidation();
        $this->cerrarTodosLosModales();

        $this->estudianteDetalle = $this->cargarEstudianteDetalle($codEstudiante);
        $this->codEstudianteSeleccionado = $codEstudiante;
        $this->modalHistorial = true;
    }

    public function cerrarModalHistorial(): void
    {
        $this->modalHistorial = false;
        $this->limpiarSeleccion();
        $this->resetValidation();
    }

    /*
    |--------------------------------------------------------------------------
    | Estado del estudiante
    |--------------------------------------------------------------------------
    */
    public function cambiarEstado(string $codEstudiante, string $estado): void
    {
        $estadosPermitidos = $this->estadosEstudiantePermitidos();

        if (! in_array($estado, $estadosPermitidos, true)) {
            $this->dispatch(
                'error-general',
                mensaje: 'Estado no permitido para el estudiante.'
            );

            return;
        }

        DB::transaction(function () use ($codEstudiante, $estado) {
            $estudiante = Estudiante::query()
                ->with([
                    'persona',
                    'tipoVinculacion',
                    'institucionProcedencia',
                    'especialidad',
                    'inscripciones.curso',
                    'inscripciones.paralelo',
                    'inscripciones.gestionAcademica',
                ])
                ->lockForUpdate()
                ->findOrFail($codEstudiante);

            if (($estudiante->est_est ?? null) === $estado) {
                $this->dispatch(
                    'error-general',
                    mensaje: 'El estudiante ya tiene el estado seleccionado.'
                );

                return;
            }

            $valoresAnteriores = $this->resumenEstudiante($estudiante);

            $estudiante->update([
                'est_est' => $estado,
            ]);

            $estudianteActualizado = $this->cargarEstudianteDetalle($estudiante->cod_est);

            $accion = match ($estado) {
                'ACTIVO' => 'REACTIVAR_ESTUDIANTE',
                'INACTIVO' => 'DESACTIVAR_ESTUDIANTE',
                'RETIRADO' => 'MARCAR_ESTUDIANTE_RETIRADO',
                'OBSERVADO' => 'MARCAR_ESTUDIANTE_OBSERVADO',
                'EGRESADO' => 'MARCAR_ESTUDIANTE_EGRESADO',
                'TRASLADADO' => 'MARCAR_ESTUDIANTE_TRASLADADO',
                default => 'CAMBIAR_ESTADO_ESTUDIANTE',
            };

            $this->registrarBitacora(
                accion: $accion,
                tabla: 'estudiante',
                registro: $estudianteActualizado->cod_est,
                nombreRegistro: $this->nombreEstudiante($estudianteActualizado),
                descripcion: 'Se actualizó el estado institucional del estudiante.',
                nivel: $estado === 'ACTIVO' ? 'SUCCESS' : 'WARNING',
                resultado: 'EXITOSO',
                valoresAnteriores: $valoresAnteriores,
                valoresNuevos: $this->resumenEstudiante($estudianteActualizado)
            );

            $this->dispatch(
                'success-general',
                mensaje: 'Estado del estudiante actualizado correctamente.'
            );
        });

        $this->resetPage();
    }

    /*
    |--------------------------------------------------------------------------
    | Validaciones
    |--------------------------------------------------------------------------
    */
    private function rulesEstudiante(bool $editando = false): array
    {
        $codEstudiante = $this->formEstudiante['cod_est'] ?? null;

        return [
            'formEstudiante.cod_per' => [
                'required',
                'exists:persona,cod_per',
            ],
            'formEstudiante.rud_est' => [
                'required',
                'string',
                'min:5',
                'max:30',
                $editando
                    ? Rule::unique('estudiante', 'rud_est')->ignore($codEstudiante, 'cod_est')
                    : Rule::unique('estudiante', 'rud_est'),
            ],
            'formEstudiante.cod_tve' => [
                'required',
                'exists:tipo_vinculacion_estudiante,cod_tve',
            ],
            'formEstudiante.cod_ipe' => [
                'required',
                'exists:institucion_procedencia,cod_ipe',
            ],
            'formEstudiante.cod_esp' => [
                'nullable',
                'exists:especialidad_tecnica,cod_esp',
            ],
            'formEstudiante.est_est' => [
                'required',
                Rule::in($this->estadosEstudiantePermitidos()),
            ],
        ];
    }

    private function messagesEstudiante(): array
    {
        return [
            'formEstudiante.cod_per.required' => 'Selecciona una persona para asociarla como estudiante.',
            'formEstudiante.cod_per.exists' => 'La persona seleccionada no existe.',
            'formEstudiante.rud_est.required' => 'Ingresa el RUD/RUDE del estudiante.',
            'formEstudiante.rud_est.min' => 'El RUD/RUDE debe tener al menos 5 caracteres.',
            'formEstudiante.rud_est.max' => 'El RUD/RUDE no debe superar los 30 caracteres.',
            'formEstudiante.rud_est.unique' => 'Este RUD/RUDE ya está registrado.',
            'formEstudiante.cod_tve.required' => 'Selecciona el tipo de vinculación.',
            'formEstudiante.cod_tve.exists' => 'El tipo de vinculación seleccionado no existe.',
            'formEstudiante.cod_ipe.required' => 'Selecciona la institución de procedencia.',
            'formEstudiante.cod_ipe.exists' => 'La institución de procedencia seleccionada no existe.',
            'formEstudiante.cod_esp.exists' => 'La especialidad técnica seleccionada no existe.',
            'formEstudiante.est_est.required' => 'Selecciona el estado del estudiante.',
            'formEstudiante.est_est.in' => 'El estado seleccionado no es válido.',
        ];
    }

    private function rulesInscripcion(): array
    {
        return [
            'formInscripcion.cod_est' => [
                'required',
                'exists:estudiante,cod_est',
            ],
            'formInscripcion.cod_cur' => [
                'required',
                'exists:curso,cod_cur',
            ],
            'formInscripcion.cod_par' => [
                'required',
                'exists:paralelo,cod_par',
            ],
            'formInscripcion.cod_gea' => [
                'required',
                'exists:gestion_academica,cod_gea',
            ],
            'formInscripcion.fec_ins' => [
                'nullable',
                'date',
            ],
            'formInscripcion.est_ins' => [
                'required',
                Rule::in(['ACTIVO', 'INACTIVO', 'PENDIENTE']),
            ],
        ];
    }

    private function messagesInscripcion(): array
    {
        return [
            'formInscripcion.cod_est.required' => 'Selecciona el estudiante.',
            'formInscripcion.cod_cur.required' => 'Selecciona el curso.',
            'formInscripcion.cod_cur.exists' => 'El curso seleccionado no existe.',
            'formInscripcion.cod_par.required' => 'Selecciona el paralelo.',
            'formInscripcion.cod_par.exists' => 'El paralelo seleccionado no existe.',
            'formInscripcion.cod_gea.required' => 'Selecciona la gestión académica.',
            'formInscripcion.cod_gea.exists' => 'La gestión académica seleccionada no existe.',
            'formInscripcion.fec_ins.date' => 'La fecha de inscripción no es válida.',
            'formInscripcion.est_ins.required' => 'Selecciona el estado de inscripción.',
            'formInscripcion.est_ins.in' => 'El estado de inscripción seleccionado no es válido.',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Validaciones visuales
    |--------------------------------------------------------------------------
    */
    public function puedeGuardarEstudiante(): bool
    {
        $rud = trim((string) ($this->formEstudiante['rud_est'] ?? ''));

        return filled($this->formEstudiante['cod_per'] ?? null)
            && strlen($rud) >= 5
            && strlen($rud) <= 30
            && filled($this->formEstudiante['cod_tve'] ?? null)
            && filled($this->formEstudiante['cod_ipe'] ?? null)
            && filled($this->formEstudiante['est_est'] ?? null)
            && in_array($this->formEstudiante['est_est'], $this->estadosEstudiantePermitidos(), true);
    }

    public function puedeGuardarInscripcion(): bool
    {
        return filled($this->formInscripcion['cod_est'] ?? null)
            && filled($this->formInscripcion['cod_cur'] ?? null)
            && filled($this->formInscripcion['cod_par'] ?? null)
            && filled($this->formInscripcion['cod_gea'] ?? null)
            && filled($this->formInscripcion['est_ins'] ?? null)
            && in_array($this->formInscripcion['est_ins'], ['ACTIVO', 'INACTIVO', 'PENDIENTE'], true);
    }

    /*
    |--------------------------------------------------------------------------
    | Filtros de consulta
    |--------------------------------------------------------------------------
    */
    private function aplicarBusqueda(Builder $query): Builder
    {
        $search = trim($this->search);

        if ($search === '') {
            return $query;
        }

        return $query->where(function (Builder $q) use ($search) {
            $q->where('rud_est', 'ILIKE', "%{$search}%")
                ->orWhereHas('persona', function (Builder $sub) use ($search) {
                    $sub->where('nom_per', 'ILIKE', "%{$search}%")
                        ->orWhere('ape_pat_per', 'ILIKE', "%{$search}%")
                        ->orWhere('ape_mat_per', 'ILIKE', "%{$search}%")
                        ->orWhere('ci_per', 'ILIKE', "%{$search}%")
                        ->orWhere('ema_per', 'ILIKE', "%{$search}%")
                        ->orWhere('tel_per', 'ILIKE', "%{$search}%");
                })
                ->orWhereHas('especialidad', function (Builder $sub) use ($search) {
                    $sub->where('nom_esp', 'ILIKE', "%{$search}%");
                })
                ->orWhereHas('institucionProcedencia', function (Builder $sub) use ($search) {
                    $sub->where('nom_ipe', 'ILIKE', "%{$search}%");
                })
                ->orWhereHas('tipoVinculacion', function (Builder $sub) use ($search) {
                    $sub->where('nom_tve', 'ILIKE', "%{$search}%");
                })
                ->orWhereHas('inscripciones.curso', function (Builder $sub) use ($search) {
                    $sub->where('nom_cur', 'ILIKE', "%{$search}%");
                })
                ->orWhereHas('inscripciones.paralelo', function (Builder $sub) use ($search) {
                    $sub->where('nom_par', 'ILIKE', "%{$search}%");
                });
        });
    }

    private function aplicarFiltrosAcademicos(Builder $query): Builder
    {
        return $query
            ->when($this->filtroEstado !== '', function (Builder $q) {
                $q->where('est_est', $this->filtroEstado);
            })
            ->when($this->filtroEspecialidad !== '', function (Builder $q) {
                $q->where('cod_esp', $this->filtroEspecialidad);
            })
            ->when($this->filtroProcedencia !== '', function (Builder $q) {
                $q->where('cod_ipe', $this->filtroProcedencia);
            })
            ->when($this->filtroVinculacion !== '', function (Builder $q) {
                $q->where('cod_tve', $this->filtroVinculacion);
            })
            ->when($this->filtroCurso !== '', function (Builder $q) {
                $q->whereHas('inscripciones', function (Builder $sub) {
                    $sub->where('cod_cur', $this->filtroCurso);

                    if ($this->gestionActualId) {
                        $sub->where('cod_gea', $this->gestionActualId);
                    }
                });
            })
            ->when($this->filtroParalelo !== '', function (Builder $q) {
                $q->whereHas('inscripciones', function (Builder $sub) {
                    $sub->where('cod_par', $this->filtroParalelo);

                    if ($this->gestionActualId) {
                        $sub->where('cod_gea', $this->gestionActualId);
                    }
                });
            })
            ->when($this->filtroInscripcion !== '', function (Builder $q) {
                $this->aplicarFiltroInscripcion($q);
            });
    }

    private function aplicarFiltroInscripcion(Builder $query): Builder
    {
        if (! $this->gestionActualId) {
            return $query;
        }

        $campoEstado = $this->campoEstadoInscripcion();

        return match ($this->filtroInscripcion) {
            'INSCRITO' => $query->whereHas('inscripciones', function (Builder $sub) use ($campoEstado) {
                $sub->where('cod_gea', $this->gestionActualId);

                if ($campoEstado) {
                    $sub->where($campoEstado, 'ACTIVO');
                }
            }),

            'PENDIENTE' => $query->whereHas('inscripciones', function (Builder $sub) use ($campoEstado) {
                $sub->where('cod_gea', $this->gestionActualId);

                if ($campoEstado) {
                    $sub->whereIn($campoEstado, ['PENDIENTE', 'INACTIVO']);
                }
            }),

            'SIN_INSCRIPCION' => $query->whereDoesntHave('inscripciones', function (Builder $sub) {
                $sub->where('cod_gea', $this->gestionActualId);
            }),

            default => $query,
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers de columnas flexibles
    |--------------------------------------------------------------------------
    */
    private function campoEstadoInscripcion(): ?string
    {
        foreach (['est_ins', 'est_ine', 'est_ies', 'estado'] as $campo) {
            if (Schema::hasColumn('inscripcion_estudiante', $campo)) {
                return $campo;
            }
        }

        return null;
    }

    private function campoFechaInscripcion(): ?string
    {
        foreach (['fec_ins', 'fec_ine', 'fec_ies', 'fecha', 'fecha_inscripcion'] as $campo) {
            if (Schema::hasColumn('inscripcion_estudiante', $campo)) {
                return $campo;
            }
        }

        return null;
    }

    private function obtenerFechaInscripcion(mixed $inscripcion): ?string
    {
        if (! $inscripcion) {
            return null;
        }

        $campoFecha = $this->campoFechaInscripcion();

        if (! $campoFecha || empty($inscripcion->{$campoFecha})) {
            return null;
        }

        try {
            return Carbon::parse($inscripcion->{$campoFecha})->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    private function obtenerEstadoModeloInscripcion(mixed $inscripcion): ?string
    {
        if (! $inscripcion) {
            return null;
        }

        $campoEstado = $this->campoEstadoInscripcion();

        if (! $campoEstado) {
            return 'ACTIVO';
        }

        return $inscripcion->{$campoEstado} ?? 'ACTIVO';
    }

    private function payloadInscripcion(): array
    {
        $payload = [
            'cod_est' => $this->formInscripcion['cod_est'],
            'cod_cur' => $this->formInscripcion['cod_cur'],
            'cod_par' => $this->formInscripcion['cod_par'],
            'cod_gea' => $this->formInscripcion['cod_gea'],
        ];

        $campoFecha = $this->campoFechaInscripcion();

        if ($campoFecha) {
            $payload[$campoFecha] = $this->formInscripcion['fec_ins'] ?: now()->toDateString();
        }

        $campoEstado = $this->campoEstadoInscripcion();

        if ($campoEstado) {
            $payload[$campoEstado] = $this->formInscripcion['est_ins'];
        }

        return $payload;
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers visuales para Blade
    |--------------------------------------------------------------------------
    */
    public function nombreCompleto(?Persona $persona): string
    {
        if (! $persona) {
            return 'Sin persona asociada';
        }

        return trim(collect([
            $persona->nom_per ?? '',
            $persona->ape_pat_per ?? '',
            $persona->ape_mat_per ?? '',
        ])->filter()->implode(' '));
    }

    public function iniciales(?Persona $persona): string
    {
        if (! $persona) {
            return 'ES';
        }

        $nombres = trim($persona->nom_per ?? '');
        $paterno = trim($persona->ape_pat_per ?? '');

        $primera = $nombres !== '' ? mb_substr($nombres, 0, 1) : 'E';
        $segunda = $paterno !== '' ? mb_substr($paterno, 0, 1) : 'S';

        return mb_strtoupper($primera . $segunda);
    }

    public function ciCompleto(?Persona $persona): string
    {
        if (! $persona) {
            return 'Sin CI';
        }

        return trim(collect([
            $persona->ci_per ?? '',
            $persona->com_per ? '-' . $persona->com_per : '',
            $persona->exp_per ?? '',
        ])->filter()->implode(' '));
    }

    public function edad(?Persona $persona): ?int
    {
        if (! $persona || empty($persona->fec_nac_per)) {
            return null;
        }

        try {
            return Carbon::parse($persona->fec_nac_per)->age;
        } catch (\Throwable) {
            return null;
        }
    }

    public function estadoEstudianteLabel(string $estado): string
    {
        return match ($estado) {
            'ACTIVO' => 'Activo',
            'INACTIVO' => 'Inactivo',
            'RETIRADO' => 'Retirado',
            'OBSERVADO' => 'Observado',
            'EGRESADO' => 'Egresado',
            'TRASLADADO' => 'Trasladado',
            default => ucfirst(strtolower($estado)),
        };
    }

    public function estadoInscripcionLabel(string $estado): string
    {
        return match ($estado) {
            'INSCRITO' => 'Inscrito',
            'PENDIENTE' => 'Pendiente',
            'SIN_INSCRIPCION' => 'Sin inscripción',
            default => ucfirst(strtolower($estado)),
        };
    }

    public function badgeEstadoEstudiante(string $estado): string
    {
        return match ($estado) {
            'ACTIVO' => 'ui-badge-success',
            'INACTIVO' => 'ui-badge-danger',
            'RETIRADO' => 'ui-badge-danger',
            'OBSERVADO' => 'ui-badge-warning',
            'EGRESADO' => 'ui-badge-info',
            'TRASLADADO' => 'ui-badge-muted',
            default => 'ui-badge-muted',
        };
    }

    public function badgeInscripcion(string $estado): string
    {
        return match ($estado) {
            'INSCRITO' => 'ui-badge-success',
            'PENDIENTE' => 'ui-badge-warning',
            'SIN_INSCRIPCION' => 'ui-badge-danger',
            default => 'ui-badge-muted',
        };
    }

    public function badgeVinculacion(?string $nombre): string
    {
        $nombre = mb_strtoupper($nombre ?? '');

        return match (true) {
            str_contains($nombre, 'NUEVO') => 'ui-badge-info',
            str_contains($nombre, 'ANTIGUO') => 'ui-badge-success',
            str_contains($nombre, 'TRASLADO') => 'ui-badge-warning',
            str_contains($nombre, 'REINGRESO') => 'ui-badge-violet',
            default => 'ui-badge-muted',
        };
    }

    public function estadosEstudiantePermitidos(): array
    {
        return [
            'ACTIVO',
            'INACTIVO',
            'RETIRADO',
            'OBSERVADO',
            'EGRESADO',
            'TRASLADADO',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Datos para selects y carpetas
    |--------------------------------------------------------------------------
    */
    private function personasDisponibles(): Collection
    {
        $personasOcupadas = Estudiante::query()
            ->pluck('cod_per')
            ->filter()
            ->toArray();

        $query = Persona::query()
            ->where('est_per', true)
            ->orderBy('ape_pat_per')
            ->orderBy('nom_per');

        if (! empty($personasOcupadas)) {
            $query->whereNotIn('cod_per', $personasOcupadas);
        }

        return $query->get();
    }

    private function paralelosFiltrados(): Collection
    {
        return Paralelo::query()
            ->where('est_par', 'ACTIVO')
            ->orderBy('nom_par')
            ->get();
    }

    private function paralelosParaFormularioInscripcion(): Collection
    {
        return Paralelo::query()
            ->where('est_par', 'ACTIVO')
            ->orderBy('nom_par')
            ->get();
    }

    private function cursosCarpeta(?string $campoEstadoInscripcion = null): Collection
    {
        return Curso::query()
            ->where('est_cur', 'ACTIVO')
            ->orderBy('nom_cur')
            ->get()
            ->map(function ($curso) use ($campoEstadoInscripcion) {
                $base = InscripcionEstudiante::query()
                    ->where('cod_cur', $curso->cod_cur);

                if ($this->gestionActualId) {
                    $base->where('cod_gea', $this->gestionActualId);
                }

                $total = (clone $base)
                    ->distinct('cod_est')
                    ->count('cod_est');

                $inscritos = (clone $base)
                    ->when($campoEstadoInscripcion, function ($query) use ($campoEstadoInscripcion) {
                        $query->where($campoEstadoInscripcion, 'ACTIVO');
                    })
                    ->distinct('cod_est')
                    ->count('cod_est');

                $paralelosUsados = (clone $base)
                    ->whereNotNull('cod_par')
                    ->distinct('cod_par')
                    ->count('cod_par');

                $curso->total_estudiantes_curso = $total;
                $curso->total_inscritos_curso = $inscritos;
                $curso->total_paralelos_curso = $paralelosUsados;

                return $curso;
            });
    }

    private function especialidadesCarpeta(?string $campoEstadoInscripcion = null): Collection
    {
        return EspecialidadTecnica::query()
            ->where('est_esp', 'ACTIVO')
            ->orderBy('nom_esp')
            ->get()
            ->map(function ($especialidad) use ($campoEstadoInscripcion) {
                $base = Estudiante::query()
                    ->where('cod_esp', $especialidad->cod_esp);

                $total = (clone $base)->count();

                $activos = (clone $base)
                    ->where('est_est', 'ACTIVO')
                    ->count();

                $inscritos = (clone $base)
                    ->whereHas('inscripciones', function (Builder $query) use ($campoEstadoInscripcion) {
                        if ($this->gestionActualId) {
                            $query->where('cod_gea', $this->gestionActualId);
                        }

                        if ($campoEstadoInscripcion) {
                            $query->where($campoEstadoInscripcion, 'ACTIVO');
                        }
                    })
                    ->count();

                $especialidad->total_estudiantes_especialidad = $total;
                $especialidad->total_activos_especialidad = $activos;
                $especialidad->total_inscritos_especialidad = $inscritos;

                return $especialidad;
            });
    }

    private function procedenciasCarpeta(?string $campoEstadoInscripcion = null): Collection
    {
        return InstitucionProcedencia::query()
            ->orderBy('nom_ipe')
            ->get()
            ->map(function ($procedencia) use ($campoEstadoInscripcion) {
                $base = Estudiante::query()
                    ->where('cod_ipe', $procedencia->cod_ipe);

                $total = (clone $base)->count();

                $activos = (clone $base)
                    ->where('est_est', 'ACTIVO')
                    ->count();

                $inscritos = (clone $base)
                    ->whereHas('inscripciones', function (Builder $query) use ($campoEstadoInscripcion) {
                        if ($this->gestionActualId) {
                            $query->where('cod_gea', $this->gestionActualId);
                        }

                        if ($campoEstadoInscripcion) {
                            $query->where($campoEstadoInscripcion, 'ACTIVO');
                        }
                    })
                    ->count();

                $procedencia->total_estudiantes_procedencia = $total;
                $procedencia->total_activos_procedencia = $activos;
                $procedencia->total_inscritos_procedencia = $inscritos;

                return $procedencia;
            });
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
        ?string $modulo = 'Gestión de Estudiantes',
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
    | Resúmenes para auditoría
    |--------------------------------------------------------------------------
    */
    private function nombreEstudiante(?Estudiante $estudiante): string
    {
        if (! $estudiante) {
            return 'Estudiante no identificado';
        }

        return $this->nombreCompleto($estudiante->persona)
            ?: ($estudiante->rud_est ?? $estudiante->cod_est);
    }

    private function resumenEstudiante(Estudiante $estudiante): array
    {
        $inscripcion = $this->inscripcionActual($estudiante);

        return [
            'cod_est' => $estudiante->cod_est,
            'cod_per' => $estudiante->cod_per,
            'nombre' => $this->nombreEstudiante($estudiante),
            'rud_est' => $estudiante->rud_est,
            'estado' => $estudiante->est_est,
            'tipo_vinculacion' => $estudiante->tipoVinculacion->nom_tve ?? $estudiante->cod_tve,
            'institucion_procedencia' => $estudiante->institucionProcedencia->nom_ipe ?? $estudiante->cod_ipe,
            'especialidad' => $estudiante->especialidad->nom_esp ?? $estudiante->cod_esp,
            'curso_actual' => $inscripcion?->curso?->nom_cur,
            'paralelo_actual' => $inscripcion?->paralelo?->nom_par,
            'gestion_actual' => $inscripcion?->gestionAcademica?->ani_gea ?? $inscripcion?->cod_gea,
        ];
    }

    private function resumenInscripcion(InscripcionEstudiante $inscripcion): array
    {
        $campoFecha = $this->campoFechaInscripcion();
        $campoEstado = $this->campoEstadoInscripcion();

        return [
            'registro' => (string) $inscripcion->getKey(),
            'cod_est' => $inscripcion->cod_est,
            'estudiante' => $inscripcion->estudiante?->persona
                ? $this->nombreCompleto($inscripcion->estudiante->persona)
                : $inscripcion->cod_est,
            'curso' => $inscripcion->curso->nom_cur ?? $inscripcion->cod_cur,
            'paralelo' => $inscripcion->paralelo->nom_par ?? $inscripcion->cod_par,
            'gestion' => $inscripcion->gestionAcademica->ani_gea ?? $inscripcion->cod_gea,
            'fecha' => $campoFecha ? ($inscripcion->{$campoFecha} ?? null) : null,
            'estado' => $campoEstado ? ($inscripcion->{$campoEstado} ?? null) : null,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Render
    |--------------------------------------------------------------------------
    */
    public function render()
    {
        $campoEstadoInscripcion = $this->campoEstadoInscripcion();

        $estudiantesQuery = Estudiante::query()
            ->with([
                'persona.usuario',
                'tipoVinculacion',
                'institucionProcedencia',
                'especialidad',
                'inscripciones.curso',
                'inscripciones.paralelo',
                'inscripciones.gestionAcademica',
            ])
            ->withCount([
                'inscripciones as total_inscripciones',
                'calificaciones as total_calificaciones',
            ]);

        $estudiantesQuery = $this->aplicarBusqueda($estudiantesQuery);
        $estudiantesQuery = $this->aplicarFiltrosAcademicos($estudiantesQuery);

        $estudiantes = $estudiantesQuery
            ->orderByDesc('cod_est')
            ->paginate($this->perPage);

        $totalEstudiantes = Estudiante::count();
        $estudiantesActivos = Estudiante::where('est_est', 'ACTIVO')->count();

        $estudiantesInactivos = Estudiante::whereIn('est_est', [
            'INACTIVO',
            'RETIRADO',
        ])->count();

        $inscritosGestionActual = $this->gestionActualId
            ? InscripcionEstudiante::query()
            ->where('cod_gea', $this->gestionActualId)
            ->when($campoEstadoInscripcion, function ($query) use ($campoEstadoInscripcion) {
                $query->where($campoEstadoInscripcion, 'ACTIVO');
            })
            ->distinct('cod_est')
            ->count('cod_est')
            : 0;

        $sinInscripcionGestionActual = $this->gestionActualId
            ? Estudiante::query()
            ->whereDoesntHave('inscripciones', function (Builder $query) {
                $query->where('cod_gea', $this->gestionActualId);
            })
            ->count()
            : $totalEstudiantes;

        $totalEspecialidadesConEstudiantes = Estudiante::query()
            ->whereNotNull('cod_esp')
            ->distinct('cod_esp')
            ->count('cod_esp');

        $totalObservados = Estudiante::query()
            ->where('est_est', 'OBSERVADO')
            ->count();

        $cursosCarpeta = $this->cursosCarpeta($campoEstadoInscripcion);
        $especialidadesCarpeta = $this->especialidadesCarpeta($campoEstadoInscripcion);
        $procedenciasCarpeta = $this->procedenciasCarpeta($campoEstadoInscripcion);

        return view('livewire.admin.gestion-estudiantes', [
            'estudiantes' => $estudiantes,

            'totalEstudiantes' => $totalEstudiantes,
            'estudiantesActivos' => $estudiantesActivos,
            'estudiantesInactivos' => $estudiantesInactivos,
            'inscritosGestionActual' => $inscritosGestionActual,
            'sinInscripcionGestionActual' => $sinInscripcionGestionActual,
            'totalEspecialidadesConEstudiantes' => $totalEspecialidadesConEstudiantes,
            'totalObservados' => $totalObservados,

            'gestionActualId' => $this->gestionActualId,
            'nombreGestionActual' => $this->nombreGestionActual,

            'personasDisponibles' => $this->personasDisponibles(),

            'cursos' => Curso::where('est_cur', 'ACTIVO')
                ->orderBy('nom_cur')
                ->get(),

            'paralelos' => $this->paralelosFiltrados(),

            'paralelosFormulario' => $this->paralelosParaFormularioInscripcion(),

            'especialidades' => EspecialidadTecnica::where('est_esp', 'ACTIVO')
                ->orderBy('nom_esp')
                ->get(),

            'tiposVinculacion' => TipoVinculacionEstudiante::orderBy('nom_tve')
                ->get(),

            'institucionesProcedencia' => InstitucionProcedencia::orderBy('nom_ipe')
                ->get(),

            'gestiones' => GestionAcademica::where('est_gea', 'ACTIVO')
                ->orderByDesc('ani_gea')
                ->get(),

            'estadosEstudiante' => $this->estadosEstudiantePermitidos(),

            'cursosCarpeta' => $cursosCarpeta,
            'especialidadesCarpeta' => $especialidadesCarpeta,
            'procedenciasCarpeta' => $procedenciasCarpeta,
        ]);
    }
}
