<?php

namespace App\Livewire\Admin;

use App\Models\DocumentoInscripcionEstudiante;
use App\Models\InscripcionEstudiante;
use App\Support\Academico\InscripcionAcademica;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Throwable;

class GestionInscripciones extends Component
{
    use WithPagination;
    use WithFileUploads;

    protected string $paginationTheme = 'tailwind';

    public string $vista = 'proceso';
    public string $subvistaProceso = 'pendientes';

    public string $busquedaEstudiante = '';
    public string $busquedaPendientes = '';
    public string $busquedaTabla = '';

    public int $porPagina = 10;
    public int $pasoInscripcion = 1;

    public string $modoFormulario = 'crear';
    public ?string $codInscripcionEditando = null;
    public ?string $codInscripcionAccion = null;

    public array $catalogos = [];
    public array $resumen = [];
    public array $panelGestion = [];
    public array $panelCupo = [];
    public array $panelDocumental = [];
    public array $panelRevision = [];
    public array $reportesDisponibles = [];

    public array $estudiantesSinInscripcion = [];
    public array $resultadosEstudiantes = [];
    public ?array $estudianteSeleccionado = null;
    public array $historialEstudiante = [];
    public array $situacionEstudiante = [];
    public array $cursoSugerido = [];

    public array $analisis = [];
    public array $confirmacionFinal = [];
    public array $pasos = [];
    public array $documentos = [];
    public array $previsualizacionChecklist = [];

    // Sugerencias aplicadas (UX): ocultan botones contextuales al aplicarse.
    public bool $cursoSugeridoAplicado = false;
    public bool $turnoSugeridoAplicado = false;
    public bool $tipoSugeridoAplicado = false;

    // Paso 3: carga PDF por documento (Livewire no maneja bien archivos dentro del array $documentos).
    public array $archivosDocumentos = [];

    // Paso 3: sugerencia compacta de documentos faltantes para el tipo actual.
    public bool $sugerenciaDocumentalVisible = false;
    public array $documentosFaltantesSugeridos = [];

    public bool $modalInscripcion = false;
    public bool $modalDetalle = false;
    public bool $modalDocumentos = false;
    public bool $modalAnular = false;
    public bool $modalRetirar = false;
    public bool $modalChecklist = false;

    // Modal de acciones agrupadas por fila.
    public bool $modalAcciones = false;
    public ?array $accionesInscripcion = null;

    public ?array $detalleInscripcion = null;
    public array $documentosModal = [];

    public string $motivoAccion = '';
    public ?string $fechaRetiro = null;

    public bool $mostrarValidaciones = false;
    public bool $permitirSobrecupo = false;
    public bool $turnoMananaAplicado = false;
    public bool $mostrarPanelEspecialidad = false;
    public bool $condicionesAceptadas = false;

    // BTH: ocultar botón "Dejar pendiente" tras aplicarlo.
    public bool $especialidadPendienteAplicada = false;

    public array $pasosWizard = [
        1 => 'Estudiante',
        2 => 'Asignación académica',
        3 => 'Documentos',
        4 => 'Revisión y confirmación',
    ];

    public array $formInscripcion = [
        'cod_est' => '',
        'cod_gea' => '',
        'cod_cur' => '',
        'cod_par' => '',
        'cod_tur' => '',
        'fei_ins' => '',
        'tip_ins' => 'REGULAR',
        'con_ins' => 'NORMAL',
        'est_ins' => 'PENDIENTE',
        'obs_ins' => '',
        'mot_obs_ins' => '',
        'pro_ins' => '',
        // Auxiliar UI: tipo de procedencia (no se guarda como columna si no existe).
        'tip_pro_ins' => 'SIN_REGISTRO',
        'doc_com_ins' => false,
        'sob_aut_ins' => false,

        // Especialidad Técnica BTH: opcional desde 4to hacia arriba.
        'cod_esp_tec' => '',
        'est_esp_tec_ins' => 'NO_APLICA',
        'obs_esp_tec_ins' => '',
    ];

    public array $filtros = [
        'cod_gea' => '',
        'cod_cur' => '',
        'cod_par' => '',
        'cod_tur' => '',
        'tip_ins' => '',
        'est_ins' => '',
        'con_ins' => '',
        'documentos' => '',
        'prioridad' => '',
        'especialidad' => '',
    ];

    protected array $messages = [
        'formInscripcion.cod_est.required' => 'Selecciona un estudiante antes de continuar.',
        'formInscripcion.cod_gea.required' => 'Selecciona la gestión académica.',
        'formInscripcion.cod_cur.required' => 'Selecciona el curso.',
        'formInscripcion.cod_par.required' => 'Selecciona el paralelo.',
        'formInscripcion.cod_tur.required' => 'Selecciona el turno.',
        'formInscripcion.fei_ins.required' => 'La fecha de inscripción es obligatoria.',
        'formInscripcion.fei_ins.date' => 'La fecha de inscripción no es válida.',
        'formInscripcion.tip_ins.required' => 'Selecciona el tipo de inscripción.',
        'formInscripcion.con_ins.required' => 'Selecciona la condición de inscripción.',
        'formInscripcion.est_ins.required' => 'Selecciona el estado de inscripción.',
        'formInscripcion.obs_ins.max' => 'La observación institucional no debe superar los 1000 caracteres.',
        'formInscripcion.mot_obs_ins.max' => 'La justificación no debe superar los 1000 caracteres.',
        'formInscripcion.pro_ins.max' => 'La procedencia no debe superar los 180 caracteres.',
        'formInscripcion.obs_esp_tec_ins.max' => 'La observación de especialidad no debe superar los 500 caracteres.',
        'motivoAccion.required' => 'Registra el motivo para conservar trazabilidad.',
        'motivoAccion.min' => 'El motivo debe tener al menos 5 caracteres.',
        'fechaRetiro.required' => 'Registra la fecha de retiro.',
        'fechaRetiro.date' => 'La fecha de retiro no es válida.',
    ];

    public function mount(): void
    {
        $this->cargarContextoInicial();
        $this->prepararFormularioInicial();
        $this->aplicarTurnoMananaPorDefecto();
        $this->actualizarEstadoEspecialidad();
        $this->cargarEstudiantesSinInscripcion();
        $this->recalcularTodo();
    }

    public function render(): View
    {
        return view('livewire.admin.gestion-inscripciones', [
            'inscripciones' => $this->consultaInscripciones()->paginate($this->porPagina),
        ]);
    }

    /* -------------------------------------------------------------------------
     | Navegación
     * ------------------------------------------------------------------------- */

    public function cambiarVista(string $vista): void
    {
        if (! in_array($vista, ['proceso', 'tabla', 'curso', 'paralelo', 'observados', 'documentos', 'historial'], true)) {
            return;
        }

        $this->vista = $vista;
        $this->resetPage();
        $this->recalcularTodo();
    }

    public function cambiarSubvistaProceso(string $subvista): void
    {
        if (! in_array($subvista, ['pendientes', 'busqueda', 'asistente', 'revision'], true)) {
            return;
        }

        $this->subvistaProceso = $subvista;
    }

    /* -------------------------------------------------------------------------
     | Reactividad
     * ------------------------------------------------------------------------- */

    public function updatedBusquedaEstudiante(): void
    {
        $this->buscarEstudiantes();
    }

    public function updatedBusquedaPendientes(): void
    {
        $this->cargarEstudiantesSinInscripcion();
    }

    public function updatedBusquedaTabla(): void
    {
        $this->resetPage();
    }

    public function updatedPorPagina(): void
    {
        $this->porPagina = in_array((int) $this->porPagina, [10, 25, 50, 100], true)
            ? (int) $this->porPagina
            : 10;

        $this->resetPage();
    }

    public function updatedFiltros(): void
    {
        $this->resetPage();
    }

    public function updatedFormInscripcionCodGea(): void
    {
        $this->cargarEstudiantesSinInscripcion();
        $this->recalcularTodo();
    }

    public function updatedFormInscripcionCodCur(): void
    {
        $this->actualizarEstadoEspecialidad();

        // Si el usuario cambia manualmente, resetea bandera si deja de coincidir con la sugerencia.
        $codSugerido = $this->cursoSugerido['cod_cur']
            ?? ($this->analisis['curso_sugerido_disponible']['cod_cur'] ?? null);
        if ($codSugerido && ($this->formInscripcion['cod_cur'] ?? null) !== $codSugerido) {
            $this->cursoSugeridoAplicado = false;
        }

        $this->recalcularTodo();
    }

    public function updatedFormInscripcionCodPar(): void
    {
        $this->recalcularTodo();
    }

    public function updatedFormInscripcionCodTur(): void
    {
        $turnoManana = $this->catalogos['turno_manana'] ?? null;
        $this->turnoMananaAplicado = $turnoManana && ($this->formInscripcion['cod_tur'] ?? null) === ($turnoManana['cod_tur'] ?? null);

        if ($turnoManana && ($this->formInscripcion['cod_tur'] ?? null) !== ($turnoManana['cod_tur'] ?? null)) {
            $this->turnoSugeridoAplicado = false;
        }

        $this->recalcularTodo();
    }

    public function updatedFormInscripcionFeiIns(): void
    {
        $this->recalcularTodo();
    }

    public function updatedFormInscripcionTipIns(): void
    {
        $this->formInscripcion['tip_ins'] = $this->normalizarMayuscula($this->formInscripcion['tip_ins'] ?? 'REGULAR');
        // Mantiene previsualización para lógica interna, pero la UI del paso 3 usa una sugerencia compacta.
        $this->previsualizarChecklist();
        $this->recalcularTodo();

        if ($this->pasoInscripcion === 3) {
            $this->asegurarListaDocumental();
        } else {
            $this->evaluarSugerenciaDocumental();
        }

        if (! empty($this->situacionEstudiante['tipo_sugerido'])
            && ($this->formInscripcion['tip_ins'] ?? null) !== ($this->situacionEstudiante['tipo_sugerido'] ?? null)
        ) {
            $this->tipoSugeridoAplicado = false;
        }
    }

    public function updatedFormInscripcionConIns(): void
    {
        $this->formInscripcion['con_ins'] = $this->normalizarMayuscula($this->formInscripcion['con_ins'] ?? 'NORMAL');
        $this->recalcularTodo();
    }

    public function updatedFormInscripcionEstIns(): void
    {
        $this->formInscripcion['est_ins'] = $this->normalizarMayuscula($this->formInscripcion['est_ins'] ?? 'PENDIENTE');
        $this->recalcularTodo();
    }

    public function updatedFormInscripcionSobAutIns(): void
    {
        $this->permitirSobrecupo = (bool) $this->formInscripcion['sob_aut_ins'];
        $this->recalcularTodo();
    }

    public function updatedFormInscripcionCodEspTec(): void
    {
        $this->actualizarEstadoEspecialidad();

        if (! empty($this->formInscripcion['cod_esp_tec'])) {
            $this->formInscripcion['est_esp_tec_ins'] = 'ASIGNADA';
            $this->especialidadPendienteAplicada = false;
        } else if ($this->mostrarPanelEspecialidad) {
            $this->formInscripcion['est_esp_tec_ins'] = 'PENDIENTE';
        }

        $this->recalcularTodo();
    }

    public function updatedFormInscripcionEstEspTecIns(): void
    {
        $estado = $this->normalizarMayuscula($this->formInscripcion['est_esp_tec_ins'] ?? 'PENDIENTE');

        if (! in_array($estado, InscripcionAcademica::ESTADOS_ESPECIALIDAD, true)) {
            $estado = 'PENDIENTE';
        }

        $this->formInscripcion['est_esp_tec_ins'] = $estado;
        $this->recalcularTodo();
    }

    public function updatedDocumentos(): void
    {
        // Normaliza combinaciones incoherentes (archivo vs estado) en edición directa.
        foreach (array_keys($this->documentos) as $i) {
            $this->normalizarDocumento($i);
        }
        $this->recalcularTodo();
    }

    public function updatedArchivosDocumentos($archivo, $key): void
    {
        $indice = (int) $key;

        if (! isset($this->documentos[$indice])) {
            return;
        }

        $this->validate([
            "archivosDocumentos.{$indice}" => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
        ]);

        if (! $archivo) {
            return;
        }

        // Subir PDF y actualizar metadata.
        $codEst = $this->formInscripcion['cod_est'] ?? 'SIN_EST';
        $path = $archivo->store("inscripciones/{$codEst}", 'public');

        $this->documentos[$indice]['rut_die'] = $path;
        $this->documentos[$indice]['for_die'] = 'PDF';
        $this->documentos[$indice]['tam_die'] = method_exists($archivo, 'getSize') ? $archivo->getSize() : null;
        $this->documentos[$indice]['has_die'] = method_exists($archivo, 'hashName') ? $archivo->hashName() : null;

        // Regla: subir PDF implica minimo PRESENTADO.
        $this->documentos[$indice]['est_die'] = 'PRESENTADO';
        $this->documentos[$indice]['fec_pre_die'] = $this->documentos[$indice]['fec_pre_die'] ?? now()->toDateString();
        $this->documentos[$indice]['fec_lim_die'] = null;

        $this->normalizarDocumento($indice);
        $this->recalcularTodo();
    }

    private function normalizarDocumento(int $indice): void
    {
        if (! isset($this->documentos[$indice])) {
            return;
        }

        $estado = $this->normalizarMayuscula($this->documentos[$indice]['est_die'] ?? 'PENDIENTE');
        if (($this->documentos[$indice]['est_die'] ?? null) !== $estado) {
            $this->documentos[$indice]['est_die'] = $estado;
        }

        $tieneArchivo = ! empty($this->documentos[$indice]['rut_die'])
            || ! empty($this->documentos[$indice]['for_die'])
            || ! empty($this->documentos[$indice]['tam_die'])
            || ! empty($this->documentos[$indice]['has_die']);

        // Si hay archivo, no puede quedar PENDIENTE.
        if ($estado === 'PENDIENTE' && $tieneArchivo) {
            $estado = 'PRESENTADO';
            $this->documentos[$indice]['est_die'] = $estado;
        }

        if ($estado === 'PENDIENTE') {
            $tieneMetadata = ! empty($this->documentos[$indice]['rut_die'])
                || ! empty($this->documentos[$indice]['for_die'])
                || ! empty($this->documentos[$indice]['tam_die'])
                || ! empty($this->documentos[$indice]['has_die'])
                || ! empty($this->documentos[$indice]['fec_pre_die']);

            if ($tieneMetadata) {
                $this->documentos[$indice]['rut_die'] = null;
                $this->documentos[$indice]['for_die'] = null;
                $this->documentos[$indice]['tam_die'] = null;
                $this->documentos[$indice]['has_die'] = null;
                $this->documentos[$indice]['fec_pre_die'] = null;
            }
        }

        if ($estado === 'NO_APLICA') {
            $tieneCampos = ! empty($this->documentos[$indice]['rut_die'])
                || ! empty($this->documentos[$indice]['for_die'])
                || ! empty($this->documentos[$indice]['tam_die'])
                || ! empty($this->documentos[$indice]['has_die'])
                || ! empty($this->documentos[$indice]['fec_pre_die'])
                || ! empty($this->documentos[$indice]['fec_lim_die'])
                || ! empty($this->documentos[$indice]['obs_die']);

            if ($tieneCampos) {
                $this->documentos[$indice]['rut_die'] = null;
                $this->documentos[$indice]['for_die'] = null;
                $this->documentos[$indice]['tam_die'] = null;
                $this->documentos[$indice]['has_die'] = null;
                $this->documentos[$indice]['fec_pre_die'] = null;
                $this->documentos[$indice]['fec_lim_die'] = null;
                $this->documentos[$indice]['obs_die'] = null;
            }
        }

        if (in_array($estado, ['PRESENTADO', 'VALIDADO'], true) && empty($this->documentos[$indice]['fec_pre_die'])) {
            $this->documentos[$indice]['fec_pre_die'] = now()->toDateString();
        }
    }

    /* -------------------------------------------------------------------------
     | Proceso principal
     * ------------------------------------------------------------------------- */

    public function abrirModalInscripcion(): void
    {
        $this->cerrarTodosLosModales();
        $this->resetValidation();

        $this->cerrarTodosLosModales();
        $this->resetearProceso();
        $this->modalInscripcion = true;
        $this->subvistaProceso = 'asistente';
    }

    public function cerrarModalInscripcion(): void
    {
        $this->modalInscripcion = false;
        $this->resetValidation();

        // Limpia archivos temporales para evitar rehidratar uploads al reabrir.
        $this->archivosDocumentos = [];
    }

    public function cerrarTodosLosModales(): void
    {
        $this->modalInscripcion = false;
        $this->modalDetalle = false;
        $this->modalDocumentos = false;
        $this->modalAnular = false;
        $this->modalRetirar = false;
        $this->modalChecklist = false;
        $this->modalAcciones = false;
        $this->accionesInscripcion = null;
    }

    public function abrirModalAcciones(string $codIns): void
    {
        $detalle = $this->soporte()->obtenerInscripcion($codIns);
        if (! $detalle) {
            $this->notificar('error', 'No se encontró la inscripción seleccionada.');
            return;
        }

        $this->cerrarTodosLosModales();
        $this->codInscripcionAccion = $codIns;
        $this->accionesInscripcion = $detalle;
        $this->modalAcciones = true;
    }

    public function cerrarModalAcciones(): void
    {
        $this->modalAcciones = false;
        $this->accionesInscripcion = null;
        $this->codInscripcionAccion = null;
    }

    public function resetearProceso(): void
    {
        $this->modoFormulario = 'crear';
        $this->codInscripcionEditando = null;
        $this->codInscripcionAccion = null;
        $this->pasoInscripcion = 1;
        $this->mostrarValidaciones = false;
        $this->permitirSobrecupo = false;
        $this->turnoMananaAplicado = false;
        $this->mostrarPanelEspecialidad = false;
        $this->condicionesAceptadas = false;
        $this->especialidadPendienteAplicada = false;

        $this->modalAcciones = false;
        $this->accionesInscripcion = null;

        $this->busquedaEstudiante = '';
        $this->resultadosEstudiantes = [];
        $this->estudianteSeleccionado = null;
        $this->historialEstudiante = [];
        $this->situacionEstudiante = [];
        $this->cursoSugerido = [];
        $this->documentos = [];
        $this->previsualizacionChecklist = [];
        $this->archivosDocumentos = [];

        $this->prepararFormularioInicial();
        $this->aplicarTurnoMananaPorDefecto();
        $this->actualizarEstadoEspecialidad();
        $this->recalcularTodo();
    }

    public function limpiarEstudianteSeleccionado(): void
    {
        $this->estudianteSeleccionado = null;
        $this->historialEstudiante = [];
        $this->situacionEstudiante = [];
        $this->cursoSugerido = [];
        $this->cursoSugeridoAplicado = false;
        $this->turnoSugeridoAplicado = false;
        $this->tipoSugeridoAplicado = false;
        $this->especialidadPendienteAplicada = false;
        $this->formInscripcion['cod_est'] = '';

        $this->recalcularTodo();
    }

    /* -------------------------------------------------------------------------
     | Estudiantes
     * ------------------------------------------------------------------------- */

    public function cargarEstudiantesSinInscripcion(): void
    {
        $codGea = $this->formInscripcion['cod_gea']
            ?: ($this->catalogos['gestion_trabajo']['cod_gea'] ?? null);

        $this->estudiantesSinInscripcion = $this->soporte()
            ->estudiantesSinInscripcion($codGea, $this->busquedaPendientes, 20);
    }

    public function buscarEstudiantes(): void
    {
        $termino = trim($this->busquedaEstudiante);

        if (mb_strlen($termino) < 2) {
            $this->resultadosEstudiantes = [];
            return;
        }

        $this->resultadosEstudiantes = $this->soporte()->buscarEstudiantes($termino, 10);
    }

    public function seleccionarPendiente(string $codEst): void
    {
        $this->abrirModalInscripcion();
        $this->seleccionarEstudiante($codEst);
    }

    public function seleccionarEstudiante(string $codEst): void
    {
        $estudiante = $this->soporte()->obtenerEstudiante($codEst);

        if (! $estudiante) {
            $this->notificar('error', 'No se pudo seleccionar el estudiante.');
            return;
        }

        $this->estudianteSeleccionado = $estudiante;
        $this->historialEstudiante = $this->soporte()->historialEstudiante($codEst);
        $this->situacionEstudiante = $this->soporte()->clasificarSituacionEstudiante($codEst, $this->formInscripcion['cod_gea']);
        $this->cursoSugerido = $this->soporte()->cursoSugeridoDisponible($estudiante['edad'] ?? null) ?? [];

        $this->formInscripcion['cod_est'] = $codEst;

        if (! empty($this->situacionEstudiante['tipo_sugerido'])) {
            $this->formInscripcion['tip_ins'] = $this->situacionEstudiante['tipo_sugerido'];
            $this->tipoSugeridoAplicado = true;
        }

        $this->aplicarTurnoMananaPorDefecto();
        $this->actualizarEstadoEspecialidad();
        $this->recalcularTodo();

        if ($this->modalInscripcion && $this->pasoInscripcion < 2) {
            $this->pasoInscripcion = 2;
        }

        $this->notificar('success', 'Estudiante seleccionado. Continúa con la asignación académica.');
    }

    /* -------------------------------------------------------------------------
     | Tipo, turno mañana y especialidad técnica BTH
     * ------------------------------------------------------------------------- */

    public function seleccionarTipoRapido(string $tipo): void
    {
        $tipo = $this->normalizarMayuscula($tipo);

        if (! in_array($tipo, InscripcionAcademica::TIPOS_INSCRIPCION, true)) {
            return;
        }

        $this->formInscripcion['tip_ins'] = $tipo;
        $this->tipoSugeridoAplicado = ! empty($this->situacionEstudiante['tipo_sugerido'])
            && $tipo === ($this->situacionEstudiante['tipo_sugerido'] ?? null);
        $this->previsualizarChecklist();
        $this->recalcularTodo();
    }

    public function aplicarTurnoMananaPorDefecto(): void
    {
        $turnoManana = $this->catalogos['turno_manana'] ?? $this->soporte()->turnoManana();

        if (! $turnoManana || empty($turnoManana['cod_tur'])) {
            $this->turnoMananaAplicado = false;
            return;
        }

        if (empty($this->formInscripcion['cod_tur'])) {
            $this->formInscripcion['cod_tur'] = $turnoManana['cod_tur'];
        }

        $this->turnoMananaAplicado = ($this->formInscripcion['cod_tur'] ?? null) === $turnoManana['cod_tur'];
    }

    public function forzarTurnoManana(): void
    {
        $turnoManana = $this->catalogos['turno_manana'] ?? $this->soporte()->turnoManana();

        if (! $turnoManana || empty($turnoManana['cod_tur'])) {
            $this->notificar('warning', 'No se detectó un turno Mañana activo. Revisa Gestión de Turnos.');
            return;
        }

        $this->formInscripcion['cod_tur'] = $turnoManana['cod_tur'];
        $this->turnoMananaAplicado = true;
        $this->turnoSugeridoAplicado = true;

        $this->recalcularTodo();
        $this->notificar('success', 'Turno Mañana aplicado como jornada principal.');
    }

    public function aplicarCursoSugerido(): void
    {
        $codCurso = $this->cursoSugerido['cod_cur']
            ?? ($this->analisis['curso_sugerido_disponible']['cod_cur'] ?? null);

        $nombreCurso = $this->cursoSugerido['nombre']
            ?? ($this->analisis['curso_sugerido_disponible']['nombre'] ?? null);

        if (empty($codCurso)) {
            $this->notificar('warning', 'No existe un curso sugerido disponible para aplicar.');
            return;
        }

        $this->formInscripcion['cod_cur'] = $codCurso;
        $this->cursoSugeridoAplicado = true;

        $this->actualizarEstadoEspecialidad();
        $this->recalcularTodo();

        $this->registrarBitacora('APLICAR_CURSO_SUGERIDO', 'inscripcion_estudiante', $this->formInscripcion['cod_est'] ?: 'SIN_REGISTRO', [
            'estudiante' => $this->estudianteSeleccionado['nombre_completo'] ?? null,
            'curso' => $nombreCurso,
        ]);

        $this->notificar('success', 'Se aplicó el curso sugerido. Revisa paralelo, turno y especialidad si corresponde.');
    }

    public function ignorarCursoSugerido(): void
    {
        $this->cursoSugerido = [];
        $this->cursoSugeridoAplicado = false;
        $this->notificar('info', 'La sugerencia fue omitida. Puedes seleccionar el curso manualmente.');
    }

    public function actualizarEstadoEspecialidad(): void
    {
        $revision = $this->soporte()->cursoRequiereEspecialidad($this->formInscripcion['cod_cur'] ?? null);
        $this->mostrarPanelEspecialidad = (bool) ($revision['requiere'] ?? false);

        if (! $this->mostrarPanelEspecialidad) {
            $this->formInscripcion['cod_esp_tec'] = '';
            $this->formInscripcion['est_esp_tec_ins'] = 'NO_APLICA';
            $this->formInscripcion['obs_esp_tec_ins'] = '';
            return;
        }

        if (! empty($this->formInscripcion['cod_esp_tec'])) {
            $this->formInscripcion['est_esp_tec_ins'] = 'ASIGNADA';
            return;
        }

        $this->formInscripcion['est_esp_tec_ins'] = 'PENDIENTE';
    }

    public function dejarEspecialidadPendiente(): void
    {
        if (! $this->mostrarPanelEspecialidad) {
            return;
        }

        $this->formInscripcion['cod_esp_tec'] = '';
        $this->formInscripcion['est_esp_tec_ins'] = 'PENDIENTE';
        $this->especialidadPendienteAplicada = true;

        $this->recalcularTodo();
        $this->notificar('info', 'La especialidad técnica quedó pendiente de elección.');
    }

    public function aplicarEstadoSugerido(): void
    {
        if (! empty($this->analisis['estado_sugerido'])) {
            $this->formInscripcion['est_ins'] = $this->analisis['estado_sugerido'];
        }

        if (! empty($this->analisis['condicion_sugerida'])) {
            $this->formInscripcion['con_ins'] = $this->analisis['condicion_sugerida'];
        }

        $this->recalcularTodo();
        $this->notificar('success', 'Se aplicó el estado sugerido por la revisión del sistema.');
    }

    public function alternarSobrecupo(): void
    {
        $this->formInscripcion['sob_aut_ins'] = ! (bool) ($this->formInscripcion['sob_aut_ins'] ?? false);
        $this->permitirSobrecupo = (bool) $this->formInscripcion['sob_aut_ins'];
        $this->recalcularTodo();

        $this->notificar(
            $this->permitirSobrecupo ? 'warning' : 'info',
            $this->permitirSobrecupo
                ? 'Sobrecupo marcado como autorizado. La inscripción quedará con seguimiento.'
                : 'Sobrecupo desactivado.'
        );
    }

    /* -------------------------------------------------------------------------
     | Wizard
     * ------------------------------------------------------------------------- */

    public function irAPaso(int $paso): void
    {
        if ($paso < 1 || $paso > 4) {
            return;
        }

        if ($paso > 1 && ! $this->estudianteSeleccionado) {
            $this->notificar('warning', 'Seleccione un estudiante antes de continuar.');
            return;
        }

        $this->pasoInscripcion = $paso;
        if ($paso === 3) {
            $this->asegurarListaDocumental();
        }
        $this->recalcularTodo();
    }

    public function siguientePaso(): void
    {
        if (! $this->validarPaso($this->pasoInscripcion)) {
            return;
        }

        if ($this->pasoInscripcion < 4) {
            $this->pasoInscripcion++;
            if ($this->pasoInscripcion === 3) {
                $this->asegurarListaDocumental();
            }
            $this->recalcularTodo();
        }
    }

    public function pasoAnterior(): void
    {
        if ($this->pasoInscripcion > 1) {
            $this->pasoInscripcion--;
            $this->evaluarSugerenciaDocumental();
            $this->recalcularTodo();
        }
    }

    public function asegurarListaDocumental(): void
    {
        // Al entrar al paso 3, si no hay documentos, genera automáticamente la lista requerida.
        if (empty($this->documentos)) {
            $this->generarDocumentosRequeridos();
            $this->ocultarSugerenciaDocumental();
            return;
        }

        $this->evaluarSugerenciaDocumental();
    }

    public function generarDocumentosRequeridos(): void
    {
        $this->documentos = $this->soporte()->documentosBase($this->formInscripcion['tip_ins'] ?? 'REGULAR');

        $this->registrarBitacora('GENERAR_LISTA_DOCUMENTAL', 'documento_inscripcion_estudiante', $this->codInscripcionEditando ?: 'SIN_REGISTRO', [
            'estudiante' => $this->estudianteSeleccionado['nombre_completo'] ?? null,
            'tipo' => $this->formInscripcion['tip_ins'] ?? null,
        ]);

        $this->recalcularTodo();
        $this->notificar('success', 'Se generó automáticamente la lista documental requerida.');
    }

    private function evaluarSugerenciaDocumental(): void
    {
        $preview = $this->soporte()->previsualizarChecklistPorTipo($this->formInscripcion['tip_ins'] ?? 'REGULAR', $this->documentos);
        $faltantes = $preview['faltantes'] ?? [];

        if (count($faltantes) > 0) {
            $this->documentosFaltantesSugeridos = $faltantes;
            $this->sugerenciaDocumentalVisible = true;
            return;
        }

        $this->ocultarSugerenciaDocumental();
    }

    public function mostrarSugerenciaDocumental(): void
    {
        $this->evaluarSugerenciaDocumental();
    }

    public function ocultarSugerenciaDocumental(): void
    {
        $this->sugerenciaDocumentalVisible = false;
        $this->documentosFaltantesSugeridos = [];
    }

    public function documentoPendiente(int $indice): void
    {
        $this->marcarDocumento($indice, 'PENDIENTE');
    }

    public function documentoPresentado(int $indice): void
    {
        $this->marcarDocumento($indice, 'PRESENTADO');
    }

    public function documentoObservado(int $indice): void
    {
        $this->marcarDocumento($indice, 'OBSERVADO');
    }

    public function documentoValidado(int $indice): void
    {
        $this->marcarDocumento($indice, 'VALIDADO');
    }

    public function documentoNoAplica(int $indice): void
    {
        $this->marcarDocumento($indice, 'NO_APLICA');
    }

    private function validarPaso(int $paso): bool
    {
        if ($paso === 1 && ! $this->estudianteSeleccionado) {
            $this->notificar('warning', 'Seleccione un estudiante antes de continuar.');
            return false;
        }

        $this->actualizarEstadoEspecialidad();
        $this->recalcularTodo();

        $resultado = $this->soporte()->puedeAvanzarPaso(
            $paso,
            $this->formInscripcion,
            $this->documentos,
            $this->estudianteSeleccionado,
            $this->analisis
        );

        if (! ($resultado['puede'] ?? false)) {
            $this->mostrarValidaciones = true;
            $this->notificar('warning', $resultado['mensaje'] ?? 'Completa el paso actual antes de continuar.');
            return false;
        }

        if ($paso === 2) {
            $this->validarAsignacionBasica();
        }

        if ($paso === 3) {
            // Paso 3: reglas mínimas para confirmar consistencia documental.
            $doc = $this->analisis['documentos'] ?? [];
            if (! empty($doc['bloqueos'] ?? [])) {
                $this->mostrarValidaciones = true;
                $this->notificar('warning', 'Existen bloqueos documentales que deben corregirse.');
                return false;
            }
        }

        return true;
    }

    /* -------------------------------------------------------------------------
     | Checklist documental seguro
     * ------------------------------------------------------------------------- */

    public function previsualizarChecklist(): void
    {
        $this->previsualizacionChecklist = $this->soporte()
            ->previsualizarChecklistPorTipo($this->formInscripcion['tip_ins'] ?? 'REGULAR', $this->documentos);
    }

    public function solicitarChecklistBase(): void
    {
        $this->previsualizarChecklist();

        if (empty($this->documentos)) {
            $this->aplicarChecklistReemplazar();
            return;
        }

        $this->cerrarTodosLosModales();
        $this->modalChecklist = true;
    }

    public function cerrarChecklist(): void
    {
        $this->modalChecklist = false;
    }

    public function aplicarChecklistAgregarFaltantes(): void
    {
        $recomendados = $this->previsualizacionChecklist['recomendados']
            ?? $this->soporte()->documentosBase($this->formInscripcion['tip_ins'] ?? 'REGULAR');

        $this->documentos = $this->soporte()->fusionarDocumentosSinBorrar($this->documentos, $recomendados);

        $this->registrarBitacora('AGREGAR_DOCUMENTOS_RECOMENDADOS', 'documento_inscripcion_estudiante', $this->codInscripcionEditando ?: 'SIN_REGISTRO', [
            'estudiante' => $this->estudianteSeleccionado['nombre_completo'] ?? null,
        ]);

        $this->modalChecklist = false;
        $this->ocultarSugerenciaDocumental();
        $this->recalcularTodo();
        $this->notificar('success', 'Se agregaron documentos recomendados sin borrar lo registrado.');
    }

    public function aplicarChecklistReemplazar(): void
    {
        $this->documentos = $this->soporte()->documentosBase($this->formInscripcion['tip_ins'] ?? 'REGULAR');

        $this->registrarBitacora('GENERAR_CHECKLIST_DOCUMENTAL', 'documento_inscripcion_estudiante', $this->codInscripcionEditando ?: 'SIN_REGISTRO', [
            'estudiante' => $this->estudianteSeleccionado['nombre_completo'] ?? null,
        ]);

        $this->modalChecklist = false;
        $this->ocultarSugerenciaDocumental();
        $this->recalcularTodo();
        $this->notificar('success', 'Checklist documental generado correctamente.');
    }

    public function agregarDocumentoManual(): void
    {
        $this->documentos[] = [
            'cod_die' => null,
            'nom_die' => 'Documento adicional',
            'tip_die' => 'GENERAL',
            'est_die' => 'PENDIENTE',
            'obs_die' => null,
            'fec_pre_die' => null,
            'fec_lim_die' => null,
            'rut_die' => null,
            'for_die' => null,
            'tam_die' => null,
            'has_die' => null,
            'obligatorio' => false,
        ];

        $this->recalcularTodo();
    }

    public function quitarDocumento(int $indice): void
    {
        if (! isset($this->documentos[$indice])) {
            return;
        }

        unset($this->documentos[$indice]);
        $this->documentos = array_values($this->documentos);
        $this->recalcularTodo();
    }

    public function marcarDocumento(int $indice, string $estado): void
    {
        if (! isset($this->documentos[$indice])) {
            return;
        }

        $estado = $this->normalizarMayuscula($estado);

        if (! in_array($estado, InscripcionAcademica::ESTADOS_DOCUMENTO, true)) {
            return;
        }

        $this->documentos[$indice]['est_die'] = $estado;

        if ($estado === 'PRESENTADO' && empty($this->documentos[$indice]['fec_pre_die'])) {
            $this->documentos[$indice]['fec_pre_die'] = now()->toDateString();
        }

        if ($estado === 'PENDIENTE') {
            // Regla: PENDIENTE no debe tener archivo.
            $this->documentos[$indice]['rut_die'] = null;
            $this->documentos[$indice]['for_die'] = null;
            $this->documentos[$indice]['tam_die'] = null;
            $this->documentos[$indice]['has_die'] = null;
            $this->documentos[$indice]['fec_pre_die'] = null;
        }

        if ($estado === 'OBSERVADO') {
            // OBSERVADO exige motivo en UI; mantenemos los campos para que se complete.
            if (empty($this->documentos[$indice]['fec_lim_die'])) {
                $this->documentos[$indice]['fec_lim_die'] = null;
            }
        }

        if ($estado === 'VALIDADO') {
            // VALIDADO exige archivo (validación/bloqueo se aplica en el análisis documental).
            if (empty($this->documentos[$indice]['fec_pre_die'])) {
                $this->documentos[$indice]['fec_pre_die'] = now()->toDateString();
            }
        }

        if ($estado === 'NO_APLICA') {
            $this->documentos[$indice]['rut_die'] = null;
            $this->documentos[$indice]['for_die'] = null;
            $this->documentos[$indice]['tam_die'] = null;
            $this->documentos[$indice]['has_die'] = null;
            $this->documentos[$indice]['fec_pre_die'] = null;
            $this->documentos[$indice]['fec_lim_die'] = null;
            $this->documentos[$indice]['obs_die'] = null;
        }

        $this->recalcularTodo();
    }

    /* -------------------------------------------------------------------------
     | Guardado
     * ------------------------------------------------------------------------- */

    public function guardarPendiente(): void
    {
        $this->guardarInscripcion(forzarPendiente: true);
    }

    public function confirmarInscripcion(): void
    {
        if (! $this->condicionesAceptadas) {
            $this->notificar('warning', 'Debe aceptar los términos y condiciones de inscripción para confirmar.');
            return;
        }
        $this->guardarInscripcion();
    }

    public function confirmarEImprimir(): void
    {
        if (! $this->condicionesAceptadas) {
            $this->notificar('warning', 'Debe aceptar los términos y condiciones de inscripción para confirmar.');
            return;
        }
        $this->guardarInscripcion(imprimir: true);
    }

    private function guardarInscripcion(bool $forzarPendiente = false, bool $imprimir = false): void
    {
        $this->resetValidation();

        $this->aplicarTurnoMananaPorDefecto();
        $this->actualizarEstadoEspecialidad();

        $this->validarFormularioBase();
        $this->recalcularTodo(modoLive: false);

        if (! ($this->analisis['puede_continuar'] ?? false) && ! $forzarPendiente) {
            $this->pasoInscripcion = 4;
            $this->notificar('warning', 'La inscripción tiene bloqueos. Corrige los datos antes de confirmar.');
            return;
        }

        $formParaGuardar = $this->formInscripcion;
        $formParaGuardar['pro_ins'] = $this->procedenciaParaGuardar(
            $formParaGuardar['tip_pro_ins'] ?? null,
            $formParaGuardar['pro_ins'] ?? null
        );
        unset($formParaGuardar['tip_pro_ins']);

        $datos = $this->soporte()->normalizarDatosInscripcion($formParaGuardar, $this->analisis);

        if ($forzarPendiente) {
            $datos['est_ins'] = 'PENDIENTE';
            $datos['con_ins'] = $datos['con_ins'] === 'NORMAL' ? 'OBSERVADA' : $datos['con_ins'];
        }

        // Columnas confirmadas en BD (fec_con_ins SÍ existe; confirmado_por NO existe).
        if (! $forzarPendiente && ($datos['est_ins'] ?? null) === 'INSCRITO') {
            $datos['fec_con_ins'] = now();
        }

        // Capa de seguridad definitiva: solo guardar campos del $fillable del modelo.
        // Esto garantiza que ninguna columna fantasma llegue al INSERT, independientemente
        // del comportamiento del Schema::hasColumn con el driver de PgSQL en Livewire.
        $fillablePermitidos = (new \App\Models\InscripcionEstudiante())->getFillable();
        $datos = array_intersect_key($datos, array_flip($fillablePermitidos));

        try {
            DB::transaction(function () use ($datos, $forzarPendiente) {
                if ($this->modoFormulario === 'editar' && $this->codInscripcionEditando) {
                    DB::table('inscripcion_estudiante')
                        ->where('cod_ins', $this->codInscripcionEditando)
                        ->update(array_merge($datos, ['updated_at' => now()]));

                    $codIns = $this->codInscripcionEditando;
                    $accion = 'ACTUALIZAR_INSCRIPCION';
                } else {
                    $inscripcion = new InscripcionEstudiante();

                    foreach ($datos as $campo => $valor) {
                        $inscripcion->{$campo} = $valor;
                    }

                    $inscripcion->save();

                    $codIns = $inscripcion->cod_ins;
                    $this->codInscripcionEditando = $codIns;

                    $accion = match (true) {
                        $forzarPendiente => 'GUARDAR_INSCRIPCION_PENDIENTE',
                        ($datos['est_ins'] ?? null) === 'OBSERVADO' => 'CREAR_INSCRIPCION_OBSERVADA',
                        ($datos['est_ins'] ?? null) === 'CONDICIONAL' => 'CREAR_INSCRIPCION_CONDICIONAL',
                        ($datos['est_ins'] ?? null) === 'PROVISIONAL' => 'CREAR_INSCRIPCION_PROVISIONAL',
                        default => 'CREAR_INSCRIPCION',
                    };
                }

                $this->guardarDocumentosInscripcion($codIns);

                $this->registrarBitacora($accion, 'inscripcion_estudiante', $codIns, $this->datosBitacora($datos));
            });

            $this->cargarContextoInicial();
            $this->cargarEstudiantesSinInscripcion();
            $this->resetPage();

            $mensaje = $this->modoFormulario === 'editar'
                ? 'Inscripción actualizada correctamente.'
                : 'Inscripción registrada correctamente.';

            $this->notificar('success', $mensaje);

            if ($imprimir && $this->codInscripcionEditando) {
                $this->generarConstancia($this->codInscripcionEditando);
            }

            $this->cerrarModalInscripcion();
        } catch (Throwable $e) {
            report($e);
            logger()->error('Error al guardar inscripción', [
                'mensaje' => $e->getMessage(),
                'archivo' => $e->getFile(),
                'linea' => $e->getLine(),
            ]);
            $this->notificar('error', 'No se pudo guardar la inscripción. Revisa los datos e intenta nuevamente.');
        }
    }

    private function procedenciaParaGuardar(?string $tipo, ?string $detalle): ?string
    {
        $tipo = $this->normalizarMayuscula($tipo ?? '');
        $detalle = trim((string) ($detalle ?? ''));

        if ($tipo === '' || $tipo === 'SIN_REGISTRO') {
            return $detalle !== '' ? $detalle : null;
        }

        // Persistimos en pro_ins una representación legible sin requerir nueva columna.
        if ($detalle !== '' && in_array($tipo, ['OTRA_UNIDAD', 'TRASLADO_DEPARTAMENTAL', 'TRASLADO_INTERDEPARTAMENTAL', 'EXTERIOR', 'OTRO'], true)) {
            return $tipo . ' - ' . $detalle;
        }

        return $tipo;
    }

    /* -------------------------------------------------------------------------
     | Edición y detalle
     * ------------------------------------------------------------------------- */

    public function abrirEditar(string $codIns): void
    {
        $evaluacion = $this->soporte()->evaluarEdicion($codIns);

        if (! ($evaluacion['puede_continuar'] ?? false)) {
            $this->notificar('warning', $evaluacion['mensaje'] ?? 'No se puede editar esta inscripción.');
            return;
        }

        if (! empty($evaluacion['advertencias'])) {
            $this->notificar('warning', $evaluacion['mensaje']);
        }

        $this->cargarInscripcionEnFormulario($codIns);
    }

    public function cargarInscripcionEnFormulario(string $codIns): void
    {
        $registro = DB::table('inscripcion_estudiante')
            ->where('cod_ins', $codIns)
            ->first();

        if (! $registro) {
            $this->notificar('error', 'No se encontró la inscripción seleccionada.');
            return;
        }

        $this->resetValidation();

        $this->modoFormulario = 'editar';
        $this->codInscripcionEditando = $codIns;
        $this->modalInscripcion = true;
        $this->pasoInscripcion = 2;
        $this->condicionesAceptadas = false;

        $this->formInscripcion = [
            'cod_est' => $registro->cod_est ?? '',
            'cod_gea' => $registro->cod_gea ?? '',
            'cod_cur' => $registro->cod_cur ?? '',
            'cod_par' => $registro->cod_par ?? '',
            'cod_tur' => $registro->cod_tur ?? '',
            'fei_ins' => $registro->fei_ins ?? now()->toDateString(),
            'tip_ins' => $registro->tip_ins ?? 'REGULAR',
            'con_ins' => $registro->con_ins ?? 'NORMAL',
            'est_ins' => $registro->est_ins ?? 'PENDIENTE',
            'obs_ins' => $registro->obs_ins ?? '',
            'mot_obs_ins' => $registro->mot_obs_ins ?? '',
            'pro_ins' => $registro->pro_ins ?? '',
            'doc_com_ins' => (bool) ($registro->doc_com_ins ?? false),
            'sob_aut_ins' => (bool) ($registro->sob_aut_ins ?? false),

            'cod_esp_tec' => $registro->cod_esp_tec ?? '',
            'est_esp_tec_ins' => $registro->est_esp_tec_ins ?? 'NO_APLICA',
            'obs_esp_tec_ins' => $registro->obs_esp_tec_ins ?? '',
        ];

        $this->permitirSobrecupo = (bool) ($registro->sob_aut_ins ?? false);

        $this->estudianteSeleccionado = $this->soporte()->obtenerEstudiante($this->formInscripcion['cod_est']);
        $this->historialEstudiante = $this->soporte()->historialEstudiante($this->formInscripcion['cod_est']);
        $this->situacionEstudiante = $this->soporte()->clasificarSituacionEstudiante($this->formInscripcion['cod_est'], $this->formInscripcion['cod_gea']);
        $this->cursoSugerido = $this->estudianteSeleccionado
            ? ($this->soporte()->cursoSugeridoDisponible($this->estudianteSeleccionado['edad'] ?? null) ?? [])
            : [];

        $this->documentos = $this->obtenerDocumentosDeInscripcion($codIns);

        $this->actualizarEstadoEspecialidad();
        $this->recalcularTodo();
    }

    public function abrirDetalle(string $codIns): void
    {
        $this->detalleInscripcion = $this->soporte()->obtenerInscripcion($codIns);

        if (! $this->detalleInscripcion) {
            $this->notificar('error', 'No se encontró la inscripción seleccionada.');
            return;
        }

        $this->cerrarTodosLosModales();
        $this->modalDetalle = true;
    }

    public function cerrarDetalle(): void
    {
        $this->modalDetalle = false;
        $this->detalleInscripcion = null;
    }

    /* -------------------------------------------------------------------------
     | Documentos modal
     * ------------------------------------------------------------------------- */

    public function abrirDocumentos(string $codIns): void
    {
        $this->codInscripcionAccion = $codIns;
        $this->documentosModal = $this->obtenerDocumentosDeInscripcion($codIns);
        $this->cerrarTodosLosModales();
        $this->modalDocumentos = true;
    }

    public function cerrarDocumentos(): void
    {
        $this->modalDocumentos = false;
        $this->codInscripcionAccion = null;
        $this->documentosModal = [];
    }

    public function generarChecklistEnModal(): void
    {
        if (! $this->codInscripcionAccion) {
            return;
        }

        $inscripcion = DB::table('inscripcion_estudiante')
            ->where('cod_ins', $this->codInscripcionAccion)
            ->first();

        if (! $inscripcion) {
            $this->notificar('error', 'No se encontró la inscripción.');
            return;
        }

        $actuales = $this->documentosModal;
        $recomendados = $this->soporte()->documentosBase($inscripcion->tip_ins ?? 'REGULAR');

        $this->documentosModal = $this->soporte()->fusionarDocumentosSinBorrar($actuales, $recomendados);

        $this->notificar('success', 'Se agregaron documentos recomendados sin borrar lo existente.');
    }

    public function agregarDocumentoEnModal(): void
    {
        $this->documentosModal[] = [
            'cod_die' => null,
            'nom_die' => 'Documento adicional',
            'tip_die' => 'GENERAL',
            'est_die' => 'PENDIENTE',
            'obs_die' => null,
            'fec_pre_die' => null,
            'fec_lim_die' => null,
            'rut_die' => null,
            'for_die' => null,
            'tam_die' => null,
            'has_die' => null,
            'obligatorio' => false,
        ];
    }

    public function quitarDocumentoEnModal(int $indice): void
    {
        if (! isset($this->documentosModal[$indice])) {
            return;
        }

        unset($this->documentosModal[$indice]);
        $this->documentosModal = array_values($this->documentosModal);
    }

    public function guardarDocumentosModal(): void
    {
        if (! $this->codInscripcionAccion) {
            $this->notificar('error', 'No existe inscripción seleccionada.');
            return;
        }

        try {
            DB::transaction(function () {
                $existentes = DB::table('documento_inscripcion_estudiante')
                    ->where('cod_ins', $this->codInscripcionAccion)
                    ->get()
                    ->keyBy('cod_die');

                $procesados = [];

                $normalizados = $this->soporte()->normalizarDocumentosParaGuardar($this->documentosModal);

                foreach ($normalizados as $documento) {
                    $codDie = $documento['cod_die'] ?? null;

                    if ($codDie && $existentes->has($codDie)) {
                        DB::table('documento_inscripcion_estudiante')
                            ->where('cod_die', $codDie)
                            ->update(array_merge(
                                collect($documento)->except(['cod_die', 'cod_ins'])->all(),
                                ['updated_at' => now()]
                            ));
                        $procesados[] = $codDie;
                        continue;
                    }

                    DocumentoInscripcionEstudiante::create(array_merge(
                        collect($documento)->except(['cod_die'])->all(),
                        ['cod_ins' => $this->codInscripcionAccion]
                    ));
                }

                // Anular los omitidos (no eliminar físicamente).
                $noProcesados = $existentes->keys()->diff($procesados);
                foreach ($noProcesados as $codDie) {
                    DB::table('documento_inscripcion_estudiante')
                        ->where('cod_die', $codDie)
                        ->update([
                            'est_die' => 'ANULADO',
                            'updated_at' => now(),
                        ]);
                }

                $revision = $this->soporte()->analizarDocumentos($normalizados);

                DB::table('inscripcion_estudiante')
                    ->where('cod_ins', $this->codInscripcionAccion)
                    ->update([
                        'doc_com_ins' => (bool) ($revision['completos'] ?? false),
                        'updated_at' => now(),
                    ]);

                $detalle = $this->soporte()->obtenerInscripcion($this->codInscripcionAccion);

                $this->registrarBitacora('ACTUALIZAR_DOCUMENTOS_INSCRIPCION', 'documento_inscripcion_estudiante', $this->codInscripcionAccion, [
                    'estudiante' => $detalle['estudiante'] ?? null,
                ]);
            });

            $this->cargarContextoInicial();
            $this->cerrarDocumentos();

            $this->notificar('success', 'Documentos actualizados correctamente.');
        } catch (Throwable $e) {
            report($e);
            $this->notificar('error', 'No se pudieron guardar los documentos.');
        }
    }

    /* -------------------------------------------------------------------------
     | Anulación, retiro y reactivación
     * ------------------------------------------------------------------------- */

    public function confirmarAnular(string $codIns): void
    {
        $this->cerrarTodosLosModales();
        $this->codInscripcionAccion = $codIns;
        $this->motivoAccion = '';

        $evaluacion = $this->soporte()->evaluarAnulacion($codIns);
        $this->detalleInscripcion = $evaluacion['inscripcion'] ?? null;
        $this->analisis = $evaluacion;

        $this->modalAnular = true;
    }

    public function cerrarAnular(): void
    {
        $this->modalAnular = false;
        $this->codInscripcionAccion = null;
        $this->motivoAccion = '';
        $this->detalleInscripcion = null;
        $this->recalcularTodo();
    }

    public function anularInscripcion(): void
    {
        $this->validate([
            'motivoAccion' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        $evaluacion = $this->soporte()->evaluarAnulacion($this->codInscripcionAccion, $this->motivoAccion);

        if (! ($evaluacion['puede_continuar'] ?? false)) {
            $this->analisis = $evaluacion;
            $this->notificar('warning', 'La anulación presenta bloqueos.');
            return;
        }

        try {
            DB::transaction(function () use ($evaluacion) {
                DB::table('inscripcion_estudiante')
                    ->where('cod_ins', $this->codInscripcionAccion)
                    ->update($this->soporte()->prepararDatosAnulacion($this->motivoAccion));

                $this->registrarBitacora('ANULAR_INSCRIPCION', 'inscripcion_estudiante', $this->codInscripcionAccion, [
                    'estudiante' => $evaluacion['inscripcion']['estudiante'] ?? null,
                    'motivo' => $this->motivoAccion,
                ]);
            });

            $this->cargarContextoInicial();
            $this->cargarEstudiantesSinInscripcion();
            $this->cerrarAnular();
            $this->resetPage();

            $this->notificar('success', 'Inscripción anulada correctamente. Se conservó la trazabilidad.');
        } catch (Throwable $e) {
            report($e);
            $this->notificar('error', 'No se pudo anular la inscripción.');
        }
    }

    public function confirmarRetiro(string $codIns): void
    {
        $this->cerrarTodosLosModales();
        $this->codInscripcionAccion = $codIns;
        $this->motivoAccion = '';
        $this->fechaRetiro = now()->toDateString();

        $evaluacion = $this->soporte()->evaluarRetiro($codIns);
        $this->detalleInscripcion = $evaluacion['inscripcion'] ?? null;
        $this->analisis = $evaluacion;

        $this->modalRetirar = true;
    }

    public function cerrarRetiro(): void
    {
        $this->modalRetirar = false;
        $this->codInscripcionAccion = null;
        $this->motivoAccion = '';
        $this->fechaRetiro = null;
        $this->detalleInscripcion = null;
        $this->recalcularTodo();
    }

    public function registrarRetiro(): void
    {
        $this->validate([
            'motivoAccion' => ['required', 'string', 'min:5', 'max:500'],
            'fechaRetiro' => ['required', 'date'],
        ]);

        $evaluacion = $this->soporte()->evaluarRetiro($this->codInscripcionAccion, $this->motivoAccion);

        if (! ($evaluacion['puede_continuar'] ?? false)) {
            $this->analisis = $evaluacion;
            $this->notificar('warning', 'El retiro presenta bloqueos.');
            return;
        }

        try {
            DB::transaction(function () use ($evaluacion) {
                $datos = $this->soporte()->prepararDatosRetiro($this->motivoAccion);
                $datos['fec_anu_ins'] = $this->fechaRetiro;

                DB::table('inscripcion_estudiante')
                    ->where('cod_ins', $this->codInscripcionAccion)
                    ->update($datos);

                $this->registrarBitacora('REGISTRAR_RETIRO_INSCRIPCION', 'inscripcion_estudiante', $this->codInscripcionAccion, [
                    'estudiante' => $evaluacion['inscripcion']['estudiante'] ?? null,
                    'motivo' => $this->motivoAccion,
                ]);
            });

            $this->cargarContextoInicial();
            $this->cargarEstudiantesSinInscripcion();
            $this->cerrarRetiro();
            $this->resetPage();

            $this->notificar('success', 'Retiro registrado correctamente. Se conservó el historial.');
        } catch (Throwable $e) {
            report($e);
            $this->notificar('error', 'No se pudo registrar el retiro.');
        }
    }

    public function reactivarInscripcion(string $codIns): void
    {
        $registro = DB::table('inscripcion_estudiante')->where('cod_ins', $codIns)->first();

        if (! $registro) {
            $this->notificar('error', 'No se encontró la inscripción.');
            return;
        }

        $duplicidad = $this->soporte()->verificarInscripcionExistente(
            $registro->cod_est ?? null,
            $registro->cod_gea ?? null,
            $codIns
        );

        if ($duplicidad['existe'] ?? false) {
            $this->notificar('warning', 'No se puede reactivar porque el estudiante ya tiene una inscripción activa en esa gestión.');
            return;
        }

        try {
            DB::table('inscripcion_estudiante')
                ->where('cod_ins', $codIns)
                ->update([
                    'est_ins' => 'PENDIENTE',
                    'con_ins' => 'OBSERVADA',
                    'fec_anu_ins' => null,
                    'anulado_por' => null,
                    'mot_anu_ins' => null,
                    'updated_at' => now(),
                ]);

            $detalle = $this->soporte()->obtenerInscripcion($codIns);

            $this->registrarBitacora('REACTIVAR_INSCRIPCION', 'inscripcion_estudiante', $codIns, [
                'estudiante' => $detalle['estudiante'] ?? null,
            ]);

            $this->cargarContextoInicial();
            $this->cargarEstudiantesSinInscripcion();
            $this->resetPage();

            $this->notificar('success', 'Inscripción reactivada como pendiente para revisión.');
        } catch (Throwable $e) {
            report($e);
            $this->notificar('error', 'No se pudo reactivar la inscripción.');
        }
    }

    /* -------------------------------------------------------------------------
     | Revisión
     * ------------------------------------------------------------------------- */

    public function recalcularTodo(bool $modoLive = true): void
    {
        // Mantener coherencia documental antes de solicitar análisis inteligente.
        $this->documentos = $this->normalizarCoherenciaDocumental($this->documentos);

        $this->analisis = $this->soporte()->analizarInscripcion(
            form: $this->formInscripcion,
            documentos: $this->documentos,
            ignorarCodIns: $this->codInscripcionEditando,
            modoLive: $modoLive
        );

        $estadoSugerido = $this->analisis['estado_sugerido'] ?? 'PENDIENTE';

        $this->confirmacionFinal = [
            'puede_confirmar' => (bool) ($this->analisis['puede_continuar'] ?? false),
            'accion_recomendada' => $estadoSugerido === 'INSCRITO' ? 'CONFIRMAR' : 'GUARDAR_CON_SEGUIMIENTO',
            'texto_boton' => match ($estadoSugerido) {
                'INSCRITO' => 'Confirmar inscripción',
                'OBSERVADO' => 'Guardar como observada',
                'CONDICIONAL' => 'Guardar como condicional',
                'PROVISIONAL' => 'Guardar como provisional',
                default => 'Guardar pendiente',
            },
            'estado_final' => $estadoSugerido,
            'condicion_final' => $this->analisis['condicion_sugerida'] ?? 'NORMAL',
        ];

        $this->pasos = $this->analisis['pasos'] ?? [];
        $this->panelGestion = $this->soporte()->panelGestion();
        $this->panelCupo = $this->soporte()->panelCupo($this->formInscripcion);
        $this->panelDocumental = $this->soporte()->panelDocumental($this->documentos);
        $this->panelRevision = $this->soporte()->panelRevision($this->analisis);
        $this->reportesDisponibles = $this->soporte()->reportesDisponibles($this->formInscripcion['cod_gea'] ?: null);

        $this->formInscripcion['doc_com_ins'] = (bool) ($this->analisis['documentos']['completos'] ?? false);

        if ($this->modoFormulario === 'crear') {
            if (! empty($this->analisis['estado_sugerido'])) {
                $this->formInscripcion['est_ins'] = $this->analisis['estado_sugerido'];
            }

            if (! empty($this->analisis['condicion_sugerida'])) {
                $this->formInscripcion['con_ins'] = $this->analisis['condicion_sugerida'];
            }

            if (! empty($this->analisis['especialidad_tecnica']['estado_sugerido'])) {
                $this->formInscripcion['est_esp_tec_ins'] = $this->analisis['especialidad_tecnica']['estado_sugerido'];
            }
        }
    }

    private function normalizarCoherenciaDocumental(array $documentos): array
    {
        return collect($documentos)
            ->map(function (array $doc) {
                $estado = $this->normalizarMayuscula($doc['est_die'] ?? 'PENDIENTE');

                $tieneArchivo = ! empty($doc['rut_die']) || ! empty($doc['for_die']) || ! empty($doc['tam_die']) || ! empty($doc['has_die']);

                if ($estado === 'PENDIENTE') {
                    // PENDIENTE no admite archivo.
                    $doc['rut_die'] = null;
                    $doc['for_die'] = null;
                    $doc['tam_die'] = null;
                    $doc['has_die'] = null;
                    $doc['fec_pre_die'] = null;
                }

                if ($estado === 'NO_APLICA') {
                    $doc['rut_die'] = null;
                    $doc['for_die'] = null;
                    $doc['tam_die'] = null;
                    $doc['has_die'] = null;
                    $doc['fec_pre_die'] = null;
                    $doc['fec_lim_die'] = null;
                }

                if ($tieneArchivo && $estado === 'PENDIENTE') {
                    // Si llega por datos previos incoherentes, auto-corrige a PRESENTADO.
                    $doc['est_die'] = 'PRESENTADO';
                    $doc['fec_pre_die'] = $doc['fec_pre_die'] ?: now()->toDateString();
                }

                if (($doc['est_die'] ?? '') === 'PRESENTADO' && empty($doc['fec_pre_die'])) {
                    $doc['fec_pre_die'] = now()->toDateString();
                }

                return $doc;
            })
            ->values()
            ->all();
    }

    /* -------------------------------------------------------------------------
     | Filtros
     * ------------------------------------------------------------------------- */

    public function limpiarFiltros(): void
    {
        $this->busquedaTabla = '';

        $this->filtros = [
            'cod_gea' => $this->catalogos['gestion_trabajo']['cod_gea'] ?? '',
            'cod_cur' => '',
            'cod_par' => '',
            'cod_tur' => '',
            'tip_ins' => '',
            'est_ins' => '',
            'con_ins' => '',
            'documentos' => '',
            'prioridad' => '',
            'especialidad' => '',
        ];

        $this->resetPage();
    }

    /* -------------------------------------------------------------------------
     | Reportes
     * ------------------------------------------------------------------------- */

    public function exportarGeneral(): void
    {
        $this->dispatch('inscripcion:exportar-general', filtros: $this->filtros);
        $this->notificar('info', 'El reporte general se conectará en la fase de PDF.');
    }

    public function exportarPorCurso(): void
    {
        $this->dispatch('inscripcion:exportar-curso', filtros: $this->filtros);
        $this->notificar('info', 'El reporte por curso se conectará en la fase de PDF.');
    }

    public function exportarPorParalelo(): void
    {
        $this->dispatch('inscripcion:exportar-paralelo', filtros: $this->filtros);
        $this->notificar('info', 'El reporte por paralelo se conectará en la fase de PDF.');
    }

    public function exportarObservados(): void
    {
        $this->dispatch('inscripcion:exportar-observados', filtros: $this->filtros);
        $this->notificar('info', 'El reporte de observados se conectará en la fase de PDF.');
    }

    public function exportarDocumentosPendientes(): void
    {
        $this->dispatch('inscripcion:exportar-documentos-pendientes', filtros: $this->filtros);
        $this->notificar('info', 'El reporte documental se conectará en la fase de PDF.');
    }

    public function exportarEspecialidadesPendientes(): void
    {
        $this->dispatch('inscripcion:exportar-especialidades-pendientes', filtros: $this->filtros);
        $this->notificar('info', 'El reporte de especialidades pendientes se conectará en la fase de PDF.');
    }

    public function generarNomina(): void
    {
        $this->dispatch('inscripcion:generar-nomina', filtros: $this->filtros);
        $this->notificar('info', 'La nómina se conectará en la fase de PDF.');
    }

    public function generarConstancia(?string $codIns = null): void
    {
        $codIns = $codIns ?: $this->codInscripcionEditando ?: $this->codInscripcionAccion;

        if (! $codIns) {
            $this->notificar('warning', 'Selecciona una inscripción para generar la constancia.');
            return;
        }

        $constancia = $this->soporte()->prepararConstancia($codIns);

        if (! ($constancia['disponible'] ?? false)) {
            $this->notificar('warning', $constancia['mensaje'] ?? 'No se pudo preparar la constancia.');
            return;
        }

        $this->dispatch('inscripcion:constancia', codIns: $codIns);
        $this->notificar('info', 'La constancia estará disponible cuando se conecte el reporte PDF.');
    }

    /* -------------------------------------------------------------------------
     | Badges para la vista
     * ------------------------------------------------------------------------- */

    public function badgeEstado(string $estado): string
    {
        return match ($this->normalizarMayuscula($estado)) {
            'INSCRITO', 'ACTIVO' => 'ui-badge-success',
            'OBSERVADO', 'PENDIENTE' => 'ui-badge-warning',
            'CONDICIONAL' => 'ui-badge-warning',
            'PROVISIONAL' => 'ui-badge-violet',
            'ANULADO' => 'ui-badge-danger',
            'RETIRADO', 'INACTIVO' => 'ui-badge-muted',
            default => 'ui-badge-muted',
        };
    }

    public function badgeDocumento(string $estado): string
    {
        return match ($this->normalizarMayuscula($estado)) {
            'PRESENTADO' => 'ui-badge-success',
            'PENDIENTE' => 'ui-badge-warning',
            'OBSERVADO' => 'ui-badge-danger',
            'NO_APLICA' => 'ui-badge-muted',
            default => 'ui-badge-muted',
        };
    }

    public function badgeRevision(string $estado): string
    {
        return match ($this->normalizarMayuscula($estado)) {
            'VALIDO', 'CORRECTO', 'COMPLETO' => 'ui-badge-success',
            'OBSERVADO', 'PENDIENTE' => 'ui-badge-warning',
            'BLOQUEADO' => 'ui-badge-danger',
            default => 'ui-badge-muted',
        };
    }

    public function badgeCupo(string $estado): string
    {
        return match ($this->normalizarMayuscula($estado)) {
            'DISPONIBLE' => 'ui-badge-success',
            'CASI_LLENO' => 'ui-badge-warning',
            'LLENO' => 'ui-badge-danger',
            default => 'ui-badge-muted',
        };
    }

    public function badgePaso(string $estado): string
    {
        return match ($this->normalizarMayuscula($estado)) {
            'COMPLETO', 'VALIDO' => 'ui-badge-success',
            'OBSERVADO', 'PENDIENTE' => 'ui-badge-warning',
            'BLOQUEADO' => 'ui-badge-danger',
            default => 'ui-badge-muted',
        };
    }

    public function badgePrioridad(string $prioridad): string
    {
        return match ($this->normalizarMayuscula($prioridad)) {
            'ALTA' => 'ui-badge-danger',
            'MEDIA' => 'ui-badge-warning',
            'BAJA' => 'ui-badge-success',
            default => 'ui-badge-muted',
        };
    }

    public function badgeEspecialidad(string $estado): string
    {
        return match ($this->normalizarMayuscula($estado)) {
            'ASIGNADA' => 'ui-badge-success',
            'PENDIENTE' => 'ui-badge-warning',
            'OBSERVADA' => 'ui-badge-danger',
            'NO_APLICA' => 'ui-badge-muted',
            default => 'ui-badge-muted',
        };
    }

    /* -------------------------------------------------------------------------
     | Consultas internas
     * ------------------------------------------------------------------------- */

    private function consultaInscripciones(): Builder
    {
        $select = [
            'inscripcion_estudiante.cod_ins',
            'inscripcion_estudiante.cod_est',
            'inscripcion_estudiante.fei_ins',
            'inscripcion_estudiante.tip_ins',
            'inscripcion_estudiante.con_ins',
            'inscripcion_estudiante.est_ins',
            'inscripcion_estudiante.doc_com_ins',
            'inscripcion_estudiante.sob_aut_ins',
            'inscripcion_estudiante.obs_ins',
            'estudiante.rud_est',
            'persona.nom_per',
            'persona.ape_pat_per',
            'persona.ape_mat_per',
            'persona.ci_per',
            'gestion_academica.ani_gea',
            'curso.nom_cur',
            'paralelo.nom_par',
            'turno.nom_tur',
        ];

        if (Schema::hasColumn('persona', 'com_per')) {
            $select[] = 'persona.com_per';
        } else {
            $select[] = DB::raw('NULL as com_per');
        }

        if (Schema::hasColumn('persona', 'exp_per')) {
            $select[] = 'persona.exp_per';
        } else {
            $select[] = DB::raw('NULL as exp_per');
        }

        if (Schema::hasColumn('inscripcion_estudiante', 'cod_esp_tec')) {
            $select[] = 'inscripcion_estudiante.cod_esp_tec';
        } else {
            $select[] = DB::raw('NULL as cod_esp_tec');
        }

        if (Schema::hasColumn('inscripcion_estudiante', 'est_esp_tec_ins')) {
            $select[] = 'inscripcion_estudiante.est_esp_tec_ins';
        } else {
            $select[] = DB::raw("'NO_APLICA' as est_esp_tec_ins");
        }

        if (Schema::hasColumn('inscripcion_estudiante', 'obs_esp_tec_ins')) {
            $select[] = 'inscripcion_estudiante.obs_esp_tec_ins';
        } else {
            $select[] = DB::raw('NULL as obs_esp_tec_ins');
        }

        $query = DB::table('inscripcion_estudiante')
            ->leftJoin('estudiante', 'estudiante.cod_est', '=', 'inscripcion_estudiante.cod_est')
            ->leftJoin('persona', 'persona.cod_per', '=', 'estudiante.cod_per')
            ->leftJoin('gestion_academica', 'gestion_academica.cod_gea', '=', 'inscripcion_estudiante.cod_gea')
            ->leftJoin('curso', 'curso.cod_cur', '=', 'inscripcion_estudiante.cod_cur')
            ->leftJoin('paralelo', 'paralelo.cod_par', '=', 'inscripcion_estudiante.cod_par')
            ->leftJoin('turno', 'turno.cod_tur', '=', 'inscripcion_estudiante.cod_tur')
            ->select($select)
            ->orderByDesc('gestion_academica.ani_gea')
            ->orderBy('persona.ape_pat_per')
            ->orderBy('persona.ape_mat_per');

        if (Schema::hasTable('documento_inscripcion_estudiante')) {
            $query->selectSub(function ($sub) {
                $sub->from('documento_inscripcion_estudiante')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('documento_inscripcion_estudiante.cod_ins', 'inscripcion_estudiante.cod_ins')
                    ->where('documento_inscripcion_estudiante.est_die', 'PENDIENTE');
            }, 'documentos_pendientes');

            $query->selectSub(function ($sub) {
                $sub->from('documento_inscripcion_estudiante')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('documento_inscripcion_estudiante.cod_ins', 'inscripcion_estudiante.cod_ins')
                    ->where('documento_inscripcion_estudiante.est_die', 'OBSERVADO');
            }, 'documentos_observados');
        } else {
            $query->selectRaw('0 as documentos_pendientes, 0 as documentos_observados');
        }

        if ($this->busquedaTabla !== '') {
            $buscar = '%' . mb_strtolower(trim($this->busquedaTabla)) . '%';

            $query->where(function ($q) use ($buscar) {
                $q->whereRaw('LOWER(persona.nom_per) LIKE ?', [$buscar])
                    ->orWhereRaw('LOWER(persona.ape_pat_per) LIKE ?', [$buscar])
                    ->orWhereRaw('LOWER(COALESCE(persona.ape_mat_per, \'\')) LIKE ?', [$buscar])
                    ->orWhereRaw('LOWER(COALESCE(persona.ci_per, \'\')) LIKE ?', [$buscar])
                    ->orWhereRaw('LOWER(COALESCE(estudiante.rud_est, \'\')) LIKE ?', [$buscar]);
            });
        }

        foreach (['cod_gea', 'cod_cur', 'cod_par', 'cod_tur', 'tip_ins', 'est_ins', 'con_ins'] as $filtro) {
            if (! empty($this->filtros[$filtro])) {
                $query->where("inscripcion_estudiante.{$filtro}", $this->filtros[$filtro]);
            }
        }

        if (($this->filtros['documentos'] ?? '') === 'PENDIENTES' && Schema::hasTable('documento_inscripcion_estudiante')) {
            $query->whereExists(function ($sub) {
                $sub->from('documento_inscripcion_estudiante')
                    ->whereColumn('documento_inscripcion_estudiante.cod_ins', 'inscripcion_estudiante.cod_ins')
                    ->where('documento_inscripcion_estudiante.est_die', 'PENDIENTE');
            });
        }

        if (($this->filtros['documentos'] ?? '') === 'OBSERVADOS' && Schema::hasTable('documento_inscripcion_estudiante')) {
            $query->whereExists(function ($sub) {
                $sub->from('documento_inscripcion_estudiante')
                    ->whereColumn('documento_inscripcion_estudiante.cod_ins', 'inscripcion_estudiante.cod_ins')
                    ->where('documento_inscripcion_estudiante.est_die', 'OBSERVADO');
            });
        }

        if (($this->filtros['documentos'] ?? '') === 'COMPLETOS') {
            $query->where('inscripcion_estudiante.doc_com_ins', true);
        }

        if (($this->filtros['especialidad'] ?? '') !== '' && Schema::hasColumn('inscripcion_estudiante', 'est_esp_tec_ins')) {
            $query->where('inscripcion_estudiante.est_esp_tec_ins', $this->filtros['especialidad']);
        }

        if ($this->vista === 'observados') {
            $query->whereIn('inscripcion_estudiante.est_ins', ['OBSERVADO', 'CONDICIONAL', 'PROVISIONAL']);
        }

        if ($this->vista === 'documentos' && Schema::hasTable('documento_inscripcion_estudiante')) {
            $query->whereExists(function ($sub) {
                $sub->from('documento_inscripcion_estudiante')
                    ->whereColumn('documento_inscripcion_estudiante.cod_ins', 'inscripcion_estudiante.cod_ins')
                    ->whereIn('documento_inscripcion_estudiante.est_die', ['PENDIENTE', 'OBSERVADO']);
            });
        }

        if ($this->vista === 'historial') {
            $query->whereIn('inscripcion_estudiante.est_ins', ['ANULADO', 'RETIRADO']);
        }

        return $query;
    }

    private function obtenerDocumentosDeInscripcion(string $codIns): array
    {
        if (! Schema::hasTable('documento_inscripcion_estudiante')) {
            return [];
        }

        return DB::table('documento_inscripcion_estudiante')
            ->where('cod_ins', $codIns)
            ->orderBy('created_at')
            ->get()
            ->map(fn($documento) => [
                'cod_die' => $documento->cod_die ?? null,
                'nom_die' => $documento->nom_die ?? 'Documento',
                'tip_die' => $documento->tip_die ?? 'GENERAL',
                'est_die' => $documento->est_die ?? 'PENDIENTE',
                'obl_die' => (bool) ($documento->obl_die ?? false),
                'fec_lim_die' => $documento->fec_lim_die ?? null,
                'obs_die' => $documento->obs_die ?? null,
                'fec_pre_die' => $documento->fec_pre_die ?? null,
                'rut_die' => $documento->rut_die ?? null,
                'for_die' => $documento->for_die ?? null,
                'tam_die' => $documento->tam_die ?? null,
                'has_die' => $documento->has_die ?? null,
                'obligatorio' => (bool) ($documento->obl_die ?? false),
            ])
            ->values()
            ->all();
    }

    private function guardarDocumentosInscripcion(string $codIns): void
    {
        if (! Schema::hasTable('documento_inscripcion_estudiante')) {
            return;
        }

        $existentes = DB::table('documento_inscripcion_estudiante')
            ->where('cod_ins', $codIns)
            ->get()
            ->keyBy('cod_die');

        $procesados = [];

        foreach ($this->soporte()->normalizarDocumentosParaGuardar($this->documentos) as $documento) {
            $codDie = $documento['cod_die'] ?? null;

            if ($codDie && $existentes->has($codDie)) {
                DB::table('documento_inscripcion_estudiante')
                    ->where('cod_die', $codDie)
                    ->update(array_merge(
                        collect($documento)
                            ->except(['cod_die', 'cod_ins'])
                            ->all(),
                        ['updated_at' => now()]
                    ));

                $procesados[] = $codDie;
                continue;
            }

            DocumentoInscripcionEstudiante::create(array_merge(
                collect($documento)->except(['cod_die'])->all(),
                ['cod_ins' => $codIns]
            ));
        }

        // No eliminamos documentos omitidos: los anulamos para conservar historial.
        $noProcesados = $existentes->keys()->diff($procesados);
        foreach ($noProcesados as $codDie) {
            DB::table('documento_inscripcion_estudiante')
                ->where('cod_die', $codDie)
                ->update([
                    'est_die' => 'ANULADO',
                    'updated_at' => now(),
                ]);
        }
    }

    /* -------------------------------------------------------------------------
     | Validaciones
     * ------------------------------------------------------------------------- */

    private function validarFormularioBase(): void
    {
        $this->validate([
            'formInscripcion.cod_est' => ['required', 'string', 'max:20'],
            'formInscripcion.cod_gea' => ['required', 'string', 'max:20'],
            'formInscripcion.cod_cur' => ['required', 'string', 'max:20'],
            'formInscripcion.cod_par' => ['required', 'string', 'max:20'],
            'formInscripcion.cod_tur' => ['required', 'string', 'max:20'],
            'formInscripcion.fei_ins' => ['required', 'date'],
            'formInscripcion.tip_ins' => ['required', 'string', 'max:30'],
            'formInscripcion.con_ins' => ['required', 'string', 'max:30'],
            'formInscripcion.est_ins' => ['required', 'string', 'max:30'],
            'formInscripcion.obs_ins' => ['nullable', 'string', 'max:1000'],
            'formInscripcion.mot_obs_ins' => ['nullable', 'string', 'max:1000'],
            'formInscripcion.pro_ins' => ['nullable', 'string', 'max:180'],
            'formInscripcion.doc_com_ins' => ['boolean'],
            'formInscripcion.sob_aut_ins' => ['boolean'],
            'formInscripcion.cod_esp_tec' => ['nullable', 'string', 'max:30'],
            'formInscripcion.est_esp_tec_ins' => ['nullable', 'string', 'max:30'],
            'formInscripcion.obs_esp_tec_ins' => ['nullable', 'string', 'max:500'],
        ]);

        if (! $this->soporte()->estadoFinalManualPermitido($this->formInscripcion['est_ins'])) {
            throw ValidationException::withMessages([
                'formInscripcion.est_ins' => 'El estado de inscripción seleccionado no es válido.',
            ]);
        }

        if (! $this->soporte()->condicionPermitida($this->formInscripcion['con_ins'])) {
            throw ValidationException::withMessages([
                'formInscripcion.con_ins' => 'La condición de inscripción seleccionada no es válida.',
            ]);
        }

        $estadoEspecialidad = $this->normalizarMayuscula($this->formInscripcion['est_esp_tec_ins'] ?? 'NO_APLICA');

        if (! in_array($estadoEspecialidad, InscripcionAcademica::ESTADOS_ESPECIALIDAD, true)) {
            throw ValidationException::withMessages([
                'formInscripcion.est_esp_tec_ins' => 'El estado de especialidad técnica no es válido.',
            ]);
        }
    }

    private function validarTipoBasico(): void
    {
        $this->validate([
            'formInscripcion.tip_ins' => ['required', 'string', 'max:30'],
        ]);
    }

    private function validarAsignacionBasica(): void
    {
        $this->validate([
            'formInscripcion.cod_gea' => ['required', 'string', 'max:20'],
            'formInscripcion.cod_cur' => ['required', 'string', 'max:20'],
            'formInscripcion.cod_par' => ['required', 'string', 'max:20'],
            'formInscripcion.cod_tur' => ['required', 'string', 'max:20'],
            'formInscripcion.fei_ins' => ['required', 'date'],
            'formInscripcion.tip_ins' => ['required', 'string', 'max:30'],
            'formInscripcion.tip_pro_ins' => ['nullable', 'string', 'max:40'],
            'formInscripcion.pro_ins' => ['nullable', 'string', 'max:180'],
        ]);

        // Regla: traslado requiere procedencia distinta a SIN_REGISTRO.
        if (($this->formInscripcion['tip_ins'] ?? null) === 'TRASLADO') {
            $tipo = $this->normalizarMayuscula($this->formInscripcion['tip_pro_ins'] ?? 'SIN_REGISTRO');
            if ($tipo === 'SIN_REGISTRO' || $tipo === '') {
                throw ValidationException::withMessages([
                    'formInscripcion.tip_pro_ins' => 'Seleccione procedencia para inscripción por traslado.',
                ]);
            }

            $requiereDetalle = in_array($tipo, ['OTRA_UNIDAD', 'TRASLADO_DEPARTAMENTAL', 'TRASLADO_INTERDEPARTAMENTAL', 'EXTERIOR', 'OTRO'], true);
            if ($requiereDetalle && empty(trim((string) ($this->formInscripcion['pro_ins'] ?? '')))) {
                throw ValidationException::withMessages([
                    'formInscripcion.pro_ins' => 'Registre la unidad educativa de procedencia.',
                ]);
            }
        }
    }

    /* -------------------------------------------------------------------------
     | Contexto, bitácora y utilidades
     * ------------------------------------------------------------------------- */

    private function cargarContextoInicial(): void
    {
        $this->catalogos = $this->soporte()->catalogos();

        $gestion = $this->catalogos['gestion_trabajo'] ?? null;
        $this->resumen = $this->soporte()->resumenGeneral($gestion['cod_gea'] ?? null);
        $this->panelGestion = $this->soporte()->panelGestion();
        $this->reportesDisponibles = $this->soporte()->reportesDisponibles($gestion['cod_gea'] ?? null);

        if (empty($this->filtros['cod_gea']) && ! empty($gestion['cod_gea'])) {
            $this->filtros['cod_gea'] = $gestion['cod_gea'];
        }
    }

    private function prepararFormularioInicial(): void
    {
        $gestion = $this->catalogos['gestion_trabajo'] ?? $this->soporte()->gestionTrabajo();
        $turnoManana = $this->catalogos['turno_manana'] ?? $this->soporte()->turnoManana();

        $this->formInscripcion = [
            'cod_est' => '',
            'cod_gea' => $gestion['cod_gea'] ?? '',
            'cod_cur' => '',
            'cod_par' => '',
            'cod_tur' => $turnoManana['cod_tur'] ?? '',
            'fei_ins' => now()->toDateString(),
            'tip_ins' => 'REGULAR',
            'con_ins' => 'NORMAL',
            'est_ins' => 'PENDIENTE',
            'obs_ins' => '',
            'mot_obs_ins' => '',
            'pro_ins' => '',
            'doc_com_ins' => false,
            'sob_aut_ins' => false,
            'cod_esp_tec' => '',
            'est_esp_tec_ins' => 'NO_APLICA',
            'obs_esp_tec_ins' => '',
        ];

        $this->turnoMananaAplicado = ! empty($turnoManana['cod_tur']);
    }

    private function registrarBitacora(string $accion, string $tabla, string $registro, array $datos = []): void
    {
        $descripcion = $this->soporte()->descripcionBitacora($accion, $datos);
        $this->registrarBitacoraSeguro($accion, $tabla, $registro, $descripcion);
    }

    private function registrarBitacoraSeguro(
        string $accion,
        string $tabla,
        string $registro,
        string $descripcion,
        string $nombreVisible = '',
        string $nivel = 'SUCCESS'
    ): void {
        try {
            if (class_exists(\Spatie\Activitylog\Facades\Activity::class)) {
                activity()
                    ->performedOn(Schema::hasTable($tabla) ? DB::table($tabla)->where('cod_' . substr($tabla, 0, 3), $registro)->first() : null)
                    ->causedBy(auth()->user())
                    ->withProperties([
                        'modulo' => 'Gestión de Inscripciones',
                        'tabla' => $tabla,
                        'registro' => $registro,
                        'descripcion' => $descripcion,
                    ])
                    ->log($accion . ' - ' . $descripcion);
            }
        } catch (Throwable) {
            //
        }

        if (! Schema::hasTable('bitacora')) {
            return;
        }

        try {
            $columnas = Schema::getColumnListing('bitacora');
            $request = request();
            $usuario = auth()->user();
            $payload = [];

            if (in_array('cod_bit', $columnas, true)) {
                $ultimo = DB::table('bitacora')
                    ->where('cod_bit', 'like', 'BIT_%')
                    ->orderByDesc('cod_bit')
                    ->value('cod_bit');

                $numero = $ultimo
                    ? ((int) str_replace('BIT_', '', $ultimo)) + 1
                    : 1;
                $payload['cod_bit'] = 'BIT_' . str_pad((string) $numero, 4, '0', STR_PAD_LEFT);
            }
            if (in_array('acc_bit', $columnas, true)) {
                $payload['acc_bit'] = mb_substr($descripcion, 0, 500);
            }
            if (in_array('tab_bit', $columnas, true)) {
                $payload['tab_bit'] = $tabla;
            }
            if (in_array('reg_bit', $columnas, true)) {
                $payload['reg_bit'] = $registro;
            }
            if (in_array('cod_usu', $columnas, true)) {
                $payload['cod_usu'] = $usuario?->cod_usu ?? auth()->id();
            }
            if (in_array('rol_bit', $columnas, true)) {
                $payload['rol_bit'] = $usuario?->getRoleNames()?->first() ?? 'Sin rol';
            }
            if (in_array('fec_bit', $columnas, true)) {
                $payload['fec_bit'] = now();
            }
            if (in_array('mod_bit', $columnas, true)) {
                $payload['mod_bit'] = 'Gestión de Inscripciones';
            }
            if (in_array('nom_reg_bit', $columnas, true)) {
                $payload['nom_reg_bit'] = $nombreVisible ?: $registro;
            }
            if (in_array('des_bit', $columnas, true)) {
                $payload['des_bit'] = $descripcion;
            }
            if (in_array('niv_bit', $columnas, true)) {
                $payload['niv_bit'] = $nivel;
            }
            if (in_array('res_bit', $columnas, true)) {
                $payload['res_bit'] = 'EXITOSO';
            }
            if (in_array('ip_bit', $columnas, true)) {
                $payload['ip_bit'] = $request?->ip() ?? '127.0.0.1';
            }
            if (in_array('age_bit', $columnas, true)) {
                $payload['age_bit'] = $request?->userAgent() ?? 'SAVP-TIS3';
            }
            if (in_array('rut_bit', $columnas, true)) {
                $payload['rut_bit'] = $request?->path() ?? 'livewire';
            }
            if (in_array('met_bit', $columnas, true)) {
                $payload['met_bit'] = $request?->method() ?? 'POST';
            }

            DB::table('bitacora')->insert($payload);
        } catch (Throwable $e) {
            report($e);
        }
    }

    private function datosBitacora(array $datos = []): array
    {
        return [
            'estudiante' => $this->estudianteSeleccionado['nombre_completo'] ?? null,
            'gestion' => $this->nombreCatalogo('gestiones', 'cod_gea', 'anio', $datos['cod_gea'] ?? $this->formInscripcion['cod_gea'] ?? null),
            'curso' => $this->nombreCatalogo('cursos', 'cod_cur', 'nombre', $datos['cod_cur'] ?? $this->formInscripcion['cod_cur'] ?? null),
            'paralelo' => $this->nombreCatalogo('paralelos', 'cod_par', 'nombre', $datos['cod_par'] ?? $this->formInscripcion['cod_par'] ?? null),
            'turno' => $this->nombreCatalogo('turnos', 'cod_tur', 'nombre', $datos['cod_tur'] ?? $this->formInscripcion['cod_tur'] ?? null),
            'estado' => $datos['est_ins'] ?? $this->formInscripcion['est_ins'] ?? null,
            'condicion' => $datos['con_ins'] ?? $this->formInscripcion['con_ins'] ?? null,
        ];
    }

    private function nombreCatalogo(string $catalogo, string $clave, string $campo, ?string $valor): ?string
    {
        if (! $valor) {
            return null;
        }

        foreach (($this->catalogos[$catalogo] ?? []) as $item) {
            if (($item[$clave] ?? null) === $valor) {
                return (string) ($item[$campo] ?? null);
            }
        }

        return null;
    }

    private function notificar(string $tipo, string $mensaje): void
    {
        $this->dispatch('swal', [
            'tipo' => $tipo,
            'mensaje' => $mensaje,
        ]);
    }

    private function soporte(): InscripcionAcademica
    {
        return app(InscripcionAcademica::class);
    }

    private function normalizarMayuscula(?string $valor): string
    {
        return mb_strtoupper(trim((string) $valor));
    }
}
