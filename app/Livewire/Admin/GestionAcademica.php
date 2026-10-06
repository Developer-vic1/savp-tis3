<?php

namespace App\Livewire\Admin;

use App\Support\Academico\GestionAcademicaInteligente;
use App\Support\Academico\PanelGestionAcademica;
use App\Support\Academico\CalendarioAcademicoInteligente;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use App\Services\Academico\EstudioCalendarioMinisterial;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Support\Academico\EsquemaAcademico as Schema;
use Livewire\Component;
use Livewire\WithPagination;

class GestionAcademica extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'tailwind';

    public string $vista = 'general';

    public string $filtroSeguimiento = '';
    public string $filtroCursoComparativa = '';
    public string $busquedaSeguimiento = '';
    public string $tipoDescarga = 'RESUMEN';
    #[Locked]
    public array $avisosSesion = [];
    private ?Collection $cacheGestiones = null;

    public string $busqueda = '';

    public string $filtroEstado = '';

    public string $filtroAnio = '';

    public bool $showCreateModal = false;

    public bool $showDetailDrawer = false;

    public bool $showCloseModal = false;

    public ?string $selectedGestionId = null;

    public ?string $gestionParaCerrarId = null;

    public array $analisisCreacion = [];

    public array $fechasSugeridas = [];

    public array $periodosSugeridos = [];

    #[Locked]
    public array $fuenteFechasAplicadas = [];

    public array $revisionCierre = [
        'gestion' => 'Sin gestión seleccionada',
        'puede_cerrar' => false,
        'estado_inteligente' => 'SIN_DATOS',
        'nivel_riesgo' => 'BAJO',
        'mensaje' => 'No existe una gestión seleccionada para revisar.',
        'bloqueos' => [],
        'advertencias' => [],
        'sugerencias' => [],
        'resumen' => [],
        'pendientes_cierre' => [],

        'inscripciones_pendientes' => 0,
        'planes_asignatura_incompletos' => 0,
        'planes_especialidad_incompletos' => 0,
    ];

    public array $form = [
        'anio' => '',
        'nombre' => '',
        'fecha_inicio' => '',
        'fecha_fin' => '',
        'modalidad' => 'Técnico Humanístico',
        'estado' => GestionAcademicaInteligente::ESTADO_ACTIVA,
        'descripcion' => '',
        'copiar_estructura' => false,
        'crear_periodos' => false,
    ];

    // ============================================================
    // CICLO DE VIDA
    // ============================================================

    public function mount(): void
    {
        Gate::authorize('Gestion_Academica');
        $this->prepararFormularioInicial();
        $gestionConsulta = request()->query('gestion');
        $this->selectedGestionId = is_string($gestionConsulta) && strlen($gestionConsulta) <= 50 && DB::table('gestion_academica')->where('cod_gea', $gestionConsulta)->exists() ? $gestionConsulta : ($this->gestionActiva['id'] ?? null);
    }

    public function render()
    {
        Gate::authorize('Gestion_Academica');
        $panel = app(PanelGestionAcademica::class)->consultar($this->gestionSeleccionada);
        if ($this->getErrorBag()->isNotEmpty()) {
            $panel['alertas'][] = ['nivel' => 'warning', 'titulo' => 'Hay campos que necesitan corrección',
                'mensaje' => implode(' ', $this->getErrorBag()->all()), 'vista' => $this->vista];
        }

        return view('livewire.admin.gestion-academica', [
            'panelAcademico' => $panel,
            'opcionesGestion' => $this->gestionesNormalizadas()->map(fn ($g) => ['valor' => $g['id'], 'etiqueta' => $g['nombre']])->all(),
            'reglaPeriodos' => app(PanelGestionAcademica::class)->planificacion($this->gestionSeleccionada, $this->periodos),
            'gestiones' => $this->gestiones,
            'gestionActiva' => $this->gestionActiva,
            'gestionSeleccionada' => $this->gestionSeleccionada,
            'resumen' => $this->resumen,
            'periodos' => $this->periodos,
            'estructura' => $this->vista === 'estructura' ? $this->estructura : [],
            'pendientesCierre' => in_array($this->vista, ['inscripciones', 'cierre'], true) ? $this->pendientesCierre : [],
            'actividadReciente' => [],
            'aniosDisponibles' => $this->aniosDisponibles,
            'tablaDisponible' => Schema::hasTable('gestion_academica'),
            'estadosGestion' => GestionAcademicaInteligente::estadosParaSelect(),
            'fechasSugeridas' => $this->fechasSugeridas,
            'periodosSugeridos' => $this->periodosSugeridos,
            'analisisCreacion' => $this->analisisCreacion,
            'fechasRegistradas' => $this->showCreateModal ? DB::table('gestion_academica')->where('ani_gea', (int) $this->form['anio'])->first(['fii_gea', 'ffi_gea']) : null,
        ]);
    }

    // ============================================================
    // REACTIVIDAD
    // ============================================================

    public function updatedBusqueda(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroEstado(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroAnio(): void
    {
        $this->resetPage();
    }

    public function updatedFormAnio(): void
    {
        $this->fuenteFechasAplicadas = [];
        $this->actualizarRecomendacionesFormulario();
    }

    public function updatedFormFechaInicio(): void
    {
        $this->actualizarRecomendacionesFormulario();
    }

    public function updatedFormFechaFin(): void
    {
        $this->actualizarRecomendacionesFormulario();
    }

    public function updatedFormEstado(): void
    {
        $this->form['estado'] = GestionAcademicaInteligente::normalizarEstado($this->form['estado'] ?? null);
        $this->actualizarRecomendacionesFormulario();
    }

    // ============================================================
    // NAVEGACIÓN INTERNA
    // ============================================================

    public function cambiarVista(string $vista)
    {
        if ($vista === 'calendario') {
            Gate::authorize('Gestion_Academica');
            return $this->redirectRoute('admin.calendario', ['gestion' => $this->gestionSeleccionada['id'] ?? null], navigate: true);
        }
        $permitidas = [
            'general',
            'anios',
            'periodos',
            'estructura',
            'inscripciones',
            'reportes',
            'respaldos',
            'cierre', 'calendario', 'seguimiento', 'alertas', 'comparativas', 'documentacion',
        ];

        if (! in_array($vista, $permitidas, true)) {
            return;
        }

        $this->vista = $vista === 'respaldos' ? 'reportes' : $vista;
    }

    public function limpiarFiltros(): void
    {
        $this->busqueda = '';
        $this->filtroEstado = '';
        $this->filtroAnio = '';

        $this->resetPage();
    }

    // ============================================================
    // CREACIÓN
    // ============================================================

    public function abrirNuevaGestion(): void
    {
        $this->resetValidation();
        $this->prepararFormularioInicial();
        $this->showCreateModal = true;
    }

    public function cerrarModal(): void
    {
        $this->showCreateModal = false;
        $this->resetValidation();
    }

    public function aplicarFechasInstitucionales(): void
    {
        Gate::authorize('Gestion_Academica');
        $anio = (int) ($this->form['anio'] ?: now()->year);
        $fechas = DB::table('gestion_academica')->where('ani_gea', $anio)->first(['fii_gea', 'ffi_gea']);
        if (! $fechas?->fii_gea || ! $fechas?->ffi_gea) {
            $this->alerta('info', 'Sin fechas registradas', 'No hay un rango registrado en la BD para este año. Usa la propuesta curricular o consulta las fuentes oficiales.');
            return;
        }
        $this->form['fecha_inicio'] = $fechas->fii_gea;
        $this->form['fecha_fin'] = $fechas->ffi_gea;
        $this->fuenteFechasAplicadas = ['fuente' => 'Fechas registradas en la BD para '.$anio, 'inicio' => $fechas->fii_gea, 'cierre' => $fechas->ffi_gea];

        $this->actualizarRecomendacionesFormulario();
    }

    public function aplicarFechasCurriculares(): void
    {
        Gate::authorize('Gestion_Academica');
        $anio = (int) ($this->form['anio'] ?: now()->year);
        $fechas = $this->soporte()->sugerirFechasGestion($anio);

        $this->form['fecha_inicio'] = $fechas['inicio_curricular'] ?? "{$anio}-02-02";
        $this->form['fecha_fin'] = $fechas['cierre_curricular'] ?? "{$anio}-12-02";
        $this->fuenteFechasAplicadas = [];

        $this->actualizarRecomendacionesFormulario();
    }

    #[On('calendario-ministerial-aplicar')]
    public function aplicarEstudioMinisterial(int $anio): void
    {
        Gate::authorize('Gestion_Academica');
        $this->validate(['form.anio' => ['required', 'integer', 'min:2020', 'max:2100']]);
        if (! $this->showCreateModal || $anio !== (int) $this->form['anio']) {
            $this->alerta('info', 'Revisa el año seleccionado', 'Las fechas del estudio pertenecen a otra gestión. Conservamos tu formulario.');
            return;
        }
        $estudio = app(EstudioCalendarioMinisterial::class)->leer($anio);
        $datos = $estudio['resultado'] ?? [];
        if (($estudio['estado'] ?? '') !== 'RESULTADO' || ! preg_match('/^'.$anio.'-\d{2}-\d{2}$/', $datos['inicio'] ?? '') || ! preg_match('/^'.$anio.'-\d{2}-\d{2}$/', $datos['cierre'] ?? '') || $datos['inicio'] >= $datos['cierre']) {
            $this->alerta('info', 'El estudio todavía no está listo', 'Espera fechas confirmables de este año. Conservamos los datos que estabas preparando.');
            return;
        }
        $this->form['fecha_inicio'] = $datos['inicio'];
        $this->form['fecha_fin'] = $datos['cierre'];
        $this->fuenteFechasAplicadas = ['fuente' => $datos['documento'], 'inicio' => $datos['inicio'], 'cierre' => $datos['cierre'], 'url' => $datos['url']];
        $this->actualizarRecomendacionesFormulario();
    }

    public function crearGestionAcademica(): void
    {
        Gate::authorize('Gestion_Academica');
        $this->normalizarFormulario();

        $this->validate([
            'form.anio' => ['required', 'integer', 'min:2020', 'max:2100'],
            'form.nombre' => ['required', 'string', 'min:5', 'max:150'],
            'form.modalidad' => ['required', 'string', 'min:3', 'max:150'],
            'form.descripcion' => ['nullable', 'string', 'max:500'],
            'form.fecha_inicio' => ['required', 'date'],
            'form.fecha_fin' => ['required', 'date', 'after:form.fecha_inicio'],
            'form.estado' => ['required', 'in:'.implode(',', GestionAcademicaInteligente::estados())],
        ], [
            'form.anio.required' => 'El año de gestión es obligatorio.',
            'form.nombre.min' => 'Escribe un nombre de gestión con al menos 5 caracteres.',
            'form.modalidad.min' => 'Indica la modalidad con al menos 3 caracteres.',
            'form.descripcion.max' => 'La descripción admite hasta 500 caracteres.',
            'form.anio.integer' => 'El año de gestión debe ser numérico.',
            'form.anio.min' => 'El año de gestión no puede ser menor a 2020.',
            'form.anio.max' => 'El año de gestión no puede ser mayor a 2100.',
            'form.fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
            'form.fecha_inicio.date' => 'La fecha de inicio no es válida.',
            'form.fecha_fin.required' => 'La fecha de cierre es obligatoria.',
            'form.fecha_fin.date' => 'La fecha de cierre no es válida.',
            'form.fecha_fin.after' => 'La fecha de cierre debe ser posterior a la fecha de inicio.',
            'form.estado.required' => 'El estado de gestión es obligatorio.',
            'form.estado.in' => 'El estado seleccionado no es válido.',
        ]);

        if (! Schema::hasTable('gestion_academica')) {
            $this->alerta('warning', 'Tabla no encontrada', 'No existe la tabla gestion_academica.');

            return;
        }

        $analisis = $this->soporte()->analizarCreacion($this->form);
        $this->analisisCreacion = $analisis;

        if (! ($analisis['puede_continuar'] ?? false)) {
            $this->alerta(
                'warning',
                'Gestión bloqueada',
                $analisis['mensaje'] ?? 'La gestión académica presenta inconsistencias.'
            );

            return;
        }

        DB::beginTransaction();

        try {
            $codGea = $this->generarCodigoGestion();

            DB::table('gestion_academica')->insert([
                'cod_gea' => $codGea,
                'ani_gea' => (int) $this->form['anio'],
                'fii_gea' => $this->form['fecha_inicio'] ?: null,
                'ffi_gea' => $this->form['fecha_fin'] ?: null,
                'est_gea' => $this->form['estado'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ((bool) $this->form['crear_periodos']) {
                $this->crearPeriodosBaseSiNoExisten((int) $this->form['anio']);
            }

            $this->registrarBitacoraSeguro(
                accion: 'CREAR_GESTION_ACADEMICA',
                tabla: 'gestion_academica',
                registro: $codGea,
                descripcion: 'Se registró la gestión académica '.$this->form['anio'].' con estado '.$this->form['estado'].'.'
                    .($this->form['descripcion'] ? ' Motivo y referencia institucional: '.$this->form['descripcion'] : '')
            );

            app(\App\Services\NotificationService::class)->publicar([
                'clave_evento' => 'gestion-creada:'.$codGea, 'origen' => 'ADMINISTRATIVO', 'tipo' => 'INFORMACION',
                'titulo' => 'La nueva gestión quedó registrada',
                'mensaje' => 'Gestión '.$this->form['anio'].': revisa su calendario, períodos y estructura antes de continuar con la planificación.',
                'cod_gea' => $codGea, 'cod_usu_emisor' => auth()->id(),
                'permiso' => 'Gestion_Academica', 'ruta' => 'admin.gestion-academica',
            ], [auth()->user()]);

            DB::commit();

            $this->showCreateModal = false;
            $this->resetValidation();
            $this->resetPage();

            $this->alerta(
                'success',
                'Gestión académica registrada',
                'La gestión académica '.$this->form['anio'].' fue creada correctamente.'
            );

            $this->prepararFormularioInicial();
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            $this->alerta(
                'error',
                'No se pudo registrar',
                'Tus campos se conservan. Vuelve a intentarlo; si el problema continúa, contacta con soporte.'
            );
        }
    }

    // ============================================================
    // DETALLE
    // ============================================================

    public function abrirDetalle(string $id): void
    {
        $gestion = $this->gestionesNormalizadas()->firstWhere('id', $id);

        if (! $gestion) {
            $this->alerta('warning', 'Gestión no encontrada', 'La gestión académica seleccionada no existe.');

            return;
        }

        $this->selectedGestionId = $id;
        $this->showDetailDrawer = true;
        $this->cacheGestiones = null;
    }

    public function cerrarDetalle(): void
    {
        $this->showDetailDrawer = false;
    }

    // ============================================================
    // ACTIVACIÓN Y CIERRE
    // ============================================================

    public function activarGestion(string $id): void
    {
        if (! Schema::hasTable('gestion_academica')) {
            $this->alerta('warning', 'Tabla no encontrada', 'No existe la tabla gestion_academica.');

            return;
        }

        $analisis = $this->soporte()->analizarActivacion($id);

        if (! ($analisis['puede_continuar'] ?? false)) {
            $this->alerta(
                'warning',
                'Activación bloqueada',
                $analisis['mensaje'] ?? 'La gestión académica no puede activarse.'
            );

            return;
        }

        $gestion = DB::table('gestion_academica')
            ->where('cod_gea', $id)
            ->first();

        if (! $gestion) {
            $this->alerta('warning', 'Gestión no encontrada', 'La gestión académica seleccionada no existe.');

            return;
        }

        DB::beginTransaction();

        try {
            DB::table('gestion_academica')
                ->where('cod_gea', $id)
                ->update([
                    'est_gea' => GestionAcademicaInteligente::ESTADO_ACTIVA,
                    'updated_at' => now(),
                ]);

            $this->registrarBitacoraSeguro(
                accion: 'ACTIVAR_GESTION_ACADEMICA',
                tabla: 'gestion_academica',
                registro: $id,
                descripcion: 'Se activó la gestión académica '.$gestion->ani_gea.'.'
            );

            DB::commit();

            $this->alerta('success', 'Gestión activada', 'La gestión académica fue marcada como ACTIVA.');
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            $this->alerta('error', 'No se pudo activar', 'Ocurrió un error al activar la gestión académica.');
        }
    }

    public function prepararCierre(string $id): void
    {
        if ($id === '') {
            $this->alerta('warning', 'Sin gestión seleccionada', 'No existe una gestión académica seleccionada para revisar el cierre.');

            return;
        }

        $gestion = $this->gestionesNormalizadas()->firstWhere('id', $id);

        if (! $gestion) {
            $this->alerta('warning', 'Gestión no encontrada', 'La gestión académica seleccionada no existe o ya no está disponible.');

            return;
        }

        $analisis = $this->soporte()->analizarInicioCierre($id);
        $pendientes = $analisis['resumen']['pendientes_cierre'] ?? [];

        $this->revisionCierre = [
            'gestion' => $gestion['nombre'],
            'puede_cerrar' => ($analisis['estado_inteligente'] ?? '') === 'LISTO_PARA_CIERRE',
            'estado_inteligente' => $analisis['estado_inteligente'] ?? 'SIN_DATOS',
            'nivel_riesgo' => $analisis['nivel_riesgo'] ?? 'BAJO',
            'mensaje' => $analisis['mensaje'] ?? 'Revisión generada.',
            'bloqueos' => $analisis['bloqueos'] ?? [],
            'advertencias' => $analisis['advertencias'] ?? [],
            'sugerencias' => $analisis['sugerencias'] ?? [],
            'resumen' => $analisis['resumen'] ?? [],
            'pendientes_cierre' => $pendientes,

            'inscripciones_pendientes' => (int) ($pendientes['Inscripciones pendientes u observadas'] ?? 0),
            'planes_asignatura_incompletos' => (int) ($pendientes['Planes de asignatura incompletos'] ?? 0),
            'planes_especialidad_incompletos' => (int) ($pendientes['Planes de especialidad incompletos'] ?? 0),
        ];

        $this->gestionParaCerrarId = $id;
        $this->showCloseModal = true;
    }

    public function iniciarCierreGestion(): void
    {
        if (! $this->gestionParaCerrarId) {
            $this->alerta('warning', 'Sin gestión seleccionada', 'No existe una gestión académica seleccionada.');

            return;
        }

        if (! Schema::hasTable('gestion_academica')) {
            $this->alerta('warning', 'Tabla no encontrada', 'No existe la tabla gestion_academica.');

            return;
        }

        $analisis = $this->soporte()->analizarInicioCierre($this->gestionParaCerrarId);

        if (! ($analisis['puede_continuar'] ?? false)) {
            $this->alerta(
                'warning',
                'Acción bloqueada',
                $analisis['mensaje'] ?? 'La gestión no puede iniciar cierre.'
            );

            return;
        }

        $gestion = DB::table('gestion_academica')
            ->where('cod_gea', $this->gestionParaCerrarId)
            ->first();

        if (! $gestion) {
            $this->alerta('warning', 'Gestión no encontrada', 'La gestión académica seleccionada no existe.');

            return;
        }

        DB::beginTransaction();

        try {
            DB::table('gestion_academica')
                ->where('cod_gea', $this->gestionParaCerrarId)
                ->update([
                    'est_gea' => GestionAcademicaInteligente::ESTADO_EN_CIERRE,
                    'updated_at' => now(),
                ]);

            $this->registrarBitacoraSeguro(
                accion: 'INICIAR_CIERRE_GESTION_ACADEMICA',
                tabla: 'gestion_academica',
                registro: $this->gestionParaCerrarId,
                descripcion: 'La gestión académica '.$gestion->ani_gea.' pasó a EN_CIERRE.'
            );

            DB::commit();

            $this->prepararCierre($this->gestionParaCerrarId);

            $this->alerta(
                'success',
                'Gestión en cierre',
                'La gestión fue marcada como EN_CIERRE para revisión institucional.'
            );
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            $this->alerta('error', 'No se pudo iniciar cierre', 'Ocurrió un error al iniciar el cierre de gestión.');
        }
    }

    public function confirmarCierreGestion(): void
    {
        if (! $this->gestionParaCerrarId) {
            $this->alerta('warning', 'Sin gestión seleccionada', 'No existe una gestión académica seleccionada para cerrar.');

            return;
        }

        if (! Schema::hasTable('gestion_academica')) {
            $this->alerta('warning', 'Tabla no encontrada', 'No existe la tabla gestion_academica.');

            return;
        }

        $analisis = $this->soporte()->analizarCierreDefinitivo($this->gestionParaCerrarId);

        if (! ($analisis['puede_continuar'] ?? false)) {
            $this->revisionCierre = array_merge($this->revisionCierre, [
                'puede_cerrar' => false,
                'estado_inteligente' => $analisis['estado_inteligente'] ?? 'BLOQUEADO',
                'nivel_riesgo' => $analisis['nivel_riesgo'] ?? 'ALTO',
                'mensaje' => $analisis['mensaje'] ?? 'La gestión no puede cerrarse.',
                'bloqueos' => $analisis['bloqueos'] ?? [],
                'advertencias' => $analisis['advertencias'] ?? [],
                'sugerencias' => $analisis['sugerencias'] ?? [],
                'resumen' => $analisis['resumen'] ?? [],
                'pendientes_cierre' => $analisis['resumen']['pendientes_cierre'] ?? [],
            ]);

            $this->alerta(
                'warning',
                'Cierre bloqueado',
                $analisis['mensaje'] ?? 'No se puede cerrar la gestión porque existen procesos académicos pendientes.'
            );

            return;
        }

        $gestion = DB::table('gestion_academica')
            ->where('cod_gea', $this->gestionParaCerrarId)
            ->first();

        if (! $gestion) {
            $this->alerta('warning', 'Gestión no encontrada', 'La gestión académica seleccionada no existe.');

            return;
        }

        DB::beginTransaction();

        try {
            DB::table('gestion_academica')
                ->where('cod_gea', $this->gestionParaCerrarId)
                ->update([
                    'est_gea' => GestionAcademicaInteligente::ESTADO_CERRADA,
                    'updated_at' => now(),
                ]);

            $this->registrarBitacoraSeguro(
                accion: 'CERRAR_GESTION_ACADEMICA',
                tabla: 'gestion_academica',
                registro: $this->gestionParaCerrarId,
                descripcion: 'Se cerró definitivamente la gestión académica '.$gestion->ani_gea.'.'
            );

            DB::commit();

            $this->showCloseModal = false;
            $this->showDetailDrawer = false;
            $this->gestionParaCerrarId = null;
            $this->selectedGestionId = null;
            $this->limpiarRevisionCierre();

            $this->alerta(
                'success',
                'Gestión académica cerrada',
                'La gestión académica fue cerrada correctamente y queda como expediente histórico institucional.'
            );
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            $this->alerta('error', 'No se pudo cerrar', 'Ocurrió un error al cerrar la gestión académica.');
        }
    }

    public function cerrarModalCierre(): void
    {
        $this->showCloseModal = false;
        $this->gestionParaCerrarId = null;
        $this->limpiarRevisionCierre();
    }

    // ============================================================
    // EXPORTACIÓN
    // ============================================================

    public function updatedSelectedGestionId(): void
    {
        Gate::authorize('Gestion_Academica');
        $this->filtroCursoComparativa = '';
        if ($this->selectedGestionId && ! DB::table('gestion_academica')->where('cod_gea', $this->selectedGestionId)->exists()) {
            $this->selectedGestionId = null;
        }
        $this->resetValidation();
        $this->resetPage();
    }

    public function descargarExpediente()
    {
        return $this->exportarGestion($this->gestionSeleccionada['id'] ?? '', $this->tipoDescarga);
    }

    public function exportarGestion(string $id, string $tipo = 'COMPLETA')
    {
        Gate::authorize('Gestion_Academica');
        $gestion = $this->gestionesNormalizadas()->firstWhere('id', $id);
        if (! $gestion) {
            $this->alerta('warning', 'Elige una gestión', 'Selecciona el expediente que quieres descargar.');
            return null;
        }
        $tipo = strtoupper($tipo);
        if (! in_array($tipo, array_merge(GestionAcademicaInteligente::TIPOS_EXPORTACION, ['RESUMEN', 'CALENDARIO', 'SEGUIMIENTO']), true)) {
            abort(422);
        }
        $datos = app(PanelGestionAcademica::class)->consultar($gestion);
        $filas = [['Gestión', $gestion['anio'], $gestion['estado']], ['Fechas registradas', $gestion['fecha_inicio'], $gestion['fecha_fin']]];
        if (in_array($tipo, ['CALENDARIO', 'COMPLETA', 'RESUMEN'], true)) {
            foreach ($datos['eventos'] as $e) {
                $filas[] = ['Calendario: '.$e['nombre'], $e['inicio'].' / '.$e['fin'], $e['estado'].' · '.$e['efecto']];
            }
        }
        if (in_array($tipo, ['SEGUIMIENTO', 'COMPLETA'], true)) {
            foreach ($datos['movimientos'] as $m) {
                $filas[] = [$m['tipo'].': '.$m['nombre'], $m['fecha'], $m['motivo'].' '.$m['observacion']];
            }
        }
        if (in_array($tipo, ['INSCRIPCIONES', 'COMPLETA', 'RESUMEN'], true)) {
            foreach ($datos['cursos'] as $c) {
                $filas[] = ['Inscripciones por curso', $c['nombre'], $c['cantidad']];
            }
            foreach ($datos['inscripciones'] as $estado) {
                $filas[] = ['Estado de inscripción', $estado['nombre'], $estado['cantidad']];
            }
        }
        if (! in_array($tipo, ['CALENDARIO', 'SEGUIMIENTO', 'INSCRIPCIONES'], true)) {
            foreach (['planes_asignatura', 'planes_especialidad', 'horarios', 'calificaciones', 'asistencias', 'clases_virtuales'] as $campo) {
                $filas[] = ['Resumen registrado', ucfirst(str_replace('_', ' ', $campo)), $gestion[$campo] ?? 'No disponible'];
            }
        }
        foreach ($datos['alertas'] as $alerta) {
            $filas[] = ['Revisión pendiente', $alerta['titulo'], $alerta['mensaje']];
        }
        return response()->streamDownload(function () use ($filas) {
            $archivo = fopen('php://output', 'w');
            fwrite($archivo, "\xEF\xBB\xBF");
            fputcsv($archivo, ['Sección', 'Dato o fecha', 'Detalle o cantidad'], ';', '"', '');
            foreach ($filas as $fila) {
                // Impide interpretar texto institucional como fórmulas al abrirlo en una hoja de cálculo.
                fputcsv($archivo, array_map(fn ($v) => preg_match('/^[=+@\-\t\r]/', (string) $v) ? "'".(string) $v : (string) $v, $fila), ';', '"', '');
            }
            fclose($archivo);
        }, 'SAVP-gestion-'.$gestion['anio'].'-'.strtolower($tipo).'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    // ============================================================
    // RESPALDOS ACADÉMICOS
    // ============================================================

    public function generarRespaldo(?string $id = null): void
    {
        $codGea = $id ?: ($this->selectedGestionId ?: ($this->gestionActiva['id'] ?? null));

        if (! $codGea) {
            $this->alerta('warning', 'Sin gestión', 'No se seleccionó una gestión académica para generar respaldo.');

            return;
        }

        $analisis = $this->soporte()->analizarGeneracionRespaldo($codGea);

        if (! ($analisis['puede_continuar'] ?? false)) {
            $this->alerta('warning', 'Respaldo bloqueado', $analisis['mensaje'] ?? 'No se puede generar el respaldo.');

            return;
        }

        $gestion = DB::table('gestion_academica')->where('cod_gea', $codGea)->first();

        if (! $gestion) {
            $this->alerta('warning', 'Gestión no encontrada', 'No se encontró la gestión académica.');

            return;
        }

        $tipoRespaldo = $analisis['resumen']['tipo_respaldo_sugerido'] ?? 'PRELIMINAR';

        DB::beginTransaction();

        try {
            $codRga = $this->generarCodigoRespaldo();

            DB::table('respaldo_gestion_academica')->insert([
                'cod_rga' => $codRga,
                'cod_gea' => $codGea,
                'tip_rga' => $tipoRespaldo,
                'for_rga' => 'JSON',
                'fec_rga' => now(),
                'est_rga' => 'GENERADO',
                'obs_rga' => 'Respaldo académico generado automáticamente por el sistema SAVP-TIS3.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->registrarBitacoraSeguro(
                accion: 'GENERAR_RESPALDO_ACADEMICO',
                tabla: 'respaldo_gestion_academica',
                registro: $codRga,
                nombreVisible: 'Respaldo '.strtolower($tipoRespaldo).' - Gestión '.$gestion->ani_gea,
                descripcion: 'Se generó un respaldo académico de '.strtolower($tipoRespaldo).' para la gestión '.$gestion->ani_gea.' antes del cierre institucional.',
                nivel: 'SUCCESS'
            );

            DB::commit();

            $this->alerta('success', 'Respaldo generado', 'Se generó el respaldo académico de '.strtolower($tipoRespaldo).' para la gestión '.$gestion->ani_gea.'.');
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            $this->alerta('error', 'Error al generar respaldo', 'Ocurrió un error al generar el respaldo académico.');
        }
    }

    public function validarRespaldo(string $codRga): void
    {
        if (! Schema::hasTable('respaldo_gestion_academica')) {
            $this->alerta('warning', 'Tabla no encontrada', 'No existe la tabla de respaldos.');

            return;
        }

        $respaldo = DB::table('respaldo_gestion_academica')->where('cod_rga', $codRga)->first();

        if (! $respaldo) {
            $this->alerta('warning', 'Respaldo no encontrado', 'El respaldo seleccionado no existe.');

            return;
        }

        if ($respaldo->est_rga !== 'GENERADO') {
            $this->alerta('warning', 'Estado incorrecto', 'Solo se pueden validar respaldos en estado GENERADO.');

            return;
        }

        DB::beginTransaction();

        try {
            $valoresAnteriores = ['est_rga' => $respaldo->est_rga];

            DB::table('respaldo_gestion_academica')
                ->where('cod_rga', $codRga)
                ->update([
                    'est_rga' => 'VALIDADO',
                    'updated_at' => now(),
                ]);

            $gestion = DB::table('gestion_academica')->where('cod_gea', $respaldo->cod_gea)->first();

            $this->registrarBitacoraSeguro(
                accion: 'VALIDAR_RESPALDO_ACADEMICO',
                tabla: 'respaldo_gestion_academica',
                registro: $codRga,
                nombreVisible: 'Respaldo '.strtolower($respaldo->tip_rga).' - Gestión '.($gestion->ani_gea ?? ''),
                descripcion: 'Se validó el respaldo académico '.strtolower($respaldo->tip_rga).' de la gestión '.($gestion->ani_gea ?? '').'. El respaldo queda habilitado para permitir el cierre definitivo.',
                nivel: 'SUCCESS',
                valoresAnteriores: $valoresAnteriores,
                valoresNuevos: ['est_rga' => 'VALIDADO']
            );

            DB::commit();

            $this->alerta('success', 'Respaldo validado', 'El respaldo académico fue validado correctamente.');
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            $this->alerta('error', 'Error al validar', 'Ocurrió un error al validar el respaldo.');
        }
    }

    public function archivarRespaldo(string $codRga): void
    {
        if (! Schema::hasTable('respaldo_gestion_academica')) {
            $this->alerta('warning', 'Tabla no encontrada', 'No existe la tabla de respaldos.');

            return;
        }

        $respaldo = DB::table('respaldo_gestion_academica')->where('cod_rga', $codRga)->first();

        if (! $respaldo) {
            $this->alerta('warning', 'Respaldo no encontrado', 'El respaldo seleccionado no existe.');

            return;
        }

        if (! in_array($respaldo->est_rga, ['GENERADO', 'VALIDADO'], true)) {
            $this->alerta('warning', 'Estado incorrecto', 'Solo se pueden archivar respaldos en estado GENERADO o VALIDADO.');

            return;
        }

        DB::beginTransaction();

        try {
            $valoresAnteriores = ['est_rga' => $respaldo->est_rga];

            DB::table('respaldo_gestion_academica')
                ->where('cod_rga', $codRga)
                ->update([
                    'est_rga' => 'ARCHIVADO',
                    'updated_at' => now(),
                ]);

            $gestion = DB::table('gestion_academica')->where('cod_gea', $respaldo->cod_gea)->first();

            $this->registrarBitacoraSeguro(
                accion: 'ARCHIVAR_RESPALDO_ACADEMICO',
                tabla: 'respaldo_gestion_academica',
                registro: $codRga,
                nombreVisible: 'Respaldo '.strtolower($respaldo->tip_rga).' - Gestión '.($gestion->ani_gea ?? ''),
                descripcion: 'Se archivó el respaldo académico '.strtolower($respaldo->tip_rga).' de la gestión '.($gestion->ani_gea ?? '').' como expediente histórico institucional.',
                nivel: 'INFO',
                valoresAnteriores: $valoresAnteriores,
                valoresNuevos: ['est_rga' => 'ARCHIVADO']
            );

            DB::commit();

            $this->alerta('success', 'Respaldo archivado', 'El respaldo académico fue archivado correctamente.');
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            $this->alerta('error', 'Error al archivar', 'Ocurrió un error al archivar el respaldo.');
        }
    }

    public function observarRespaldo(string $codRga, string $observacion = ''): void
    {
        if (! Schema::hasTable('respaldo_gestion_academica')) {
            return;
        }

        $respaldo = DB::table('respaldo_gestion_academica')->where('cod_rga', $codRga)->first();

        if (! $respaldo || $respaldo->est_rga === 'ANULADO') {
            $this->alerta('warning', 'Acción no permitida', 'No se puede observar este respaldo.');

            return;
        }

        DB::beginTransaction();

        try {
            $valoresAnteriores = ['est_rga' => $respaldo->est_rga, 'obs_rga' => $respaldo->obs_rga];

            DB::table('respaldo_gestion_academica')
                ->where('cod_rga', $codRga)
                ->update([
                    'est_rga' => 'OBSERVADO',
                    'obs_rga' => $observacion ?: 'Observado por administración académica.',
                    'updated_at' => now(),
                ]);

            $gestion = DB::table('gestion_academica')->where('cod_gea', $respaldo->cod_gea)->first();

            $this->registrarBitacoraSeguro(
                accion: 'OBSERVAR_RESPALDO_ACADEMICO',
                tabla: 'respaldo_gestion_academica',
                registro: $codRga,
                nombreVisible: 'Respaldo '.strtolower($respaldo->tip_rga).' - Gestión '.($gestion->ani_gea ?? ''),
                descripcion: 'Se marcó como observado el respaldo académico de la gestión '.($gestion->ani_gea ?? '').'. Motivo: '.($observacion ?: 'Sin observación detallada.'),
                nivel: 'WARNING',
                valoresAnteriores: $valoresAnteriores,
                valoresNuevos: ['est_rga' => 'OBSERVADO', 'obs_rga' => $observacion]
            );

            DB::commit();

            $this->alerta('info', 'Respaldo observado', 'El respaldo fue marcado como observado.');
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            $this->alerta('error', 'Error', 'Ocurrió un error al observar el respaldo.');
        }
    }

    // ============================================================
    // PROPIEDADES COMPUTADAS
    // ============================================================

    public function getGestionesProperty(): LengthAwarePaginator
    {
        $coleccion = $this->gestionesNormalizadas();

        if ($this->busqueda !== '') {
            $buscar = mb_strtolower(trim($this->busqueda));

            $coleccion = $coleccion->filter(function (array $gestion) use ($buscar) {
                return str_contains((string) $gestion['anio'], $buscar)
                    || str_contains(mb_strtolower($gestion['nombre']), $buscar)
                    || str_contains(mb_strtolower($gestion['estado']), $buscar)
                    || str_contains(mb_strtolower($gestion['estado_original']), $buscar)
                    || str_contains(mb_strtolower($gestion['codigo']), $buscar);
            });
        }

        if ($this->filtroEstado !== '') {
            $estado = GestionAcademicaInteligente::normalizarEstado($this->filtroEstado);
            $coleccion = $coleccion->where('estado', $estado);
        }

        if ($this->filtroAnio !== '') {
            $coleccion = $coleccion->where('anio', (int) $this->filtroAnio);
        }

        return $this->paginarColeccion($coleccion->values(), 6);
    }

    public function getGestionActivaProperty(): ?array
    {
        return $this->gestionesNormalizadas()
            ->firstWhere('estado', GestionAcademicaInteligente::ESTADO_ACTIVA);
    }

    public function getGestionSeleccionadaProperty(): ?array
    {
        if (! $this->selectedGestionId) {
            return $this->gestionActiva;
        }

        return $this->gestionesNormalizadas()
            ->firstWhere('id', $this->selectedGestionId);
    }

    public function getAniosDisponiblesProperty(): array
    {
        return $this->gestionesNormalizadas()
            ->pluck('anio')
            ->filter()
            ->unique()
            ->sortDesc()
            ->values()
            ->all();
    }

    public function getResumenProperty(): array
    {
        $gestion = $this->gestionSeleccionada;
        return [
            ['titulo' => 'Estudiantes inscritos', 'valor' => $gestion['estudiantes'] ?? 0, 'descripcion' => 'En el expediente de consulta', 'color' => 'sky'],
            ['titulo' => 'Planes de asignatura', 'valor' => $gestion['planes_asignatura'] ?? 0, 'descripcion' => 'Planificación de esta gestión', 'color' => 'emerald'],
            ['titulo' => 'Trimestres registrados', 'valor' => count($this->periodos), 'descripcion' => 'Periodos y fechas de esta gestión', 'color' => 'violet'],
            ['titulo' => 'Eventos del calendario', 'valor' => $gestion ? (app(PanelGestionAcademica::class)->porGestion('calendario_evento', $gestion['id'])?->count() ?? 0) : 0, 'descripcion' => 'Feriados, actividades y contingencias', 'color' => 'amber'],
        ];
    }

    public function getPeriodosProperty(): array
    {
        $gestion = $this->gestionSeleccionada;

        return $gestion ? app(PanelGestionAcademica::class)->periodos($gestion['id']) : [];
    }

    public function getEstructuraProperty(): array
    {
        return [
            [
                'titulo' => 'Cursos activos',
                'valor' => $this->contarActivos('curso', 'est_cur'),
                'detalle' => 'Base por nivel académico',
                'color' => 'emerald',
            ],
            [
                'titulo' => 'Paralelos activos',
                'valor' => $this->contarActivos('paralelo', 'est_par'),
                'detalle' => 'Grupos académicos operativos',
                'color' => 'sky',
            ],
            [
                'titulo' => 'Turnos activos',
                'valor' => $this->contarActivos('turno', 'est_tur'),
                'detalle' => 'Mañana, tarde o noche',
                'color' => 'violet',
            ],
            [
                'titulo' => 'Asignaturas activas',
                'valor' => $this->contarActivos('asignatura', 'est_asi'),
                'detalle' => 'Catálogo académico',
                'color' => 'emerald',
            ],
            [
                'titulo' => 'Especialidades técnicas',
                'valor' => $this->contarActivos('especialidad_tecnica', 'est_esp'),
                'detalle' => 'Bachillerato Técnico Humanístico',
                'color' => 'violet',
            ],
            [
                'titulo' => 'Planes de asignatura',
                'valor' => $this->contarPorGestionActiva('plan_asignatura'),
                'detalle' => 'Asociados a la gestión de consulta',
                'color' => 'sky',
            ],
            [
                'titulo' => 'Planes de especialidad',
                'valor' => $this->contarPorGestionActiva('plan_especialidad'),
                'detalle' => 'Formación técnica vinculada',
                'color' => 'emerald',
            ],
            [
                'titulo' => 'Horarios generados',
                'valor' => $this->contarPorGestionActiva('horario'),
                'detalle' => 'Organización semanal',
                'color' => 'amber',
            ],
        ];
    }

    public function getPendientesCierreProperty(): array
    {
        $activa = $this->gestionSeleccionada;

        if (! $activa) {
            return [
                ['titulo' => 'Inscripciones pendientes u observadas', 'valor' => 0, 'color' => 'amber'],
                ['titulo' => 'Planes de asignatura incompletos', 'valor' => 0, 'color' => 'amber'],
                ['titulo' => 'Planes de especialidad incompletos', 'valor' => 0, 'color' => 'amber'],
                ['titulo' => 'Horarios en borrador o inconsistentes', 'valor' => 0, 'color' => 'rose'],
                ['titulo' => 'Tareas o asistencias abiertas', 'valor' => 0, 'color' => 'rose'],
            ];
        }

        $pendientes = $this->soporte()->pendientesCierre($activa['id']);

        return collect($pendientes)
            ->map(function (int $valor, string $titulo) {
                return [
                    'titulo' => $titulo,
                    'valor' => $valor,
                    'color' => $valor > 0 ? 'amber' : 'emerald',
                ];
            })
            ->values()
            ->all();
    }

    public function getRespaldosGestionProperty(): array
    {
        $gestion = $this->gestionSeleccionada ?? $this->gestionActiva;

        if (! $gestion || ! Schema::hasTable('respaldo_gestion_academica')) {
            return [];
        }

        return DB::table('respaldo_gestion_academica')
            ->where('cod_gea', $gestion['id'])
            ->orderByDesc('fec_rga')
            ->get()
            ->map(function ($row) {
                return [
                    'id' => $row->cod_rga,
                    'tipo' => $row->tip_rga,
                    'tipo_label' => match ($row->tip_rga) {
                        'PRELIMINAR' => 'Preliminar',
                        'CIERRE' => 'Cierre',
                        'AUDITORIA' => 'Auditoría',
                        'RECUPERACION' => 'Recuperación',
                        default => $row->tip_rga,
                    },
                    'formato' => $row->for_rga,
                    'estado' => $row->est_rga,
                    'estado_label' => match ($row->est_rga) {
                        'GENERADO' => 'Generado',
                        'VALIDADO' => 'Validado',
                        'OBSERVADO' => 'Observado',
                        'ARCHIVADO' => 'Archivado',
                        'ANULADO' => 'Anulado',
                        default => $row->est_rga,
                    },
                    'fecha' => $row->fec_rga ? Carbon::parse($row->fec_rga)->format('d/m/Y H:i') : 'Sin fecha',
                    'observacion' => $row->obs_rga,
                    'tamanio' => $row->tam_rga ?? null,
                ];
            })
            ->values()
            ->all();
    }

    public function getActividadRecienteProperty(): array
    {
        if (! Schema::hasTable('bitacora')) {
            return [];
        }

        $columnas = Schema::getColumnListing('bitacora');

        $fechaCol = in_array('fec_bit', $columnas, true) ? 'fec_bit' : null;
        $accionCol = in_array('acc_bit', $columnas, true) ? 'acc_bit' : null;
        $moduloCol = in_array('mod_bit', $columnas, true)
            ? 'mod_bit'
            : (in_array('tab_bit', $columnas, true) ? 'tab_bit' : null);
        $resultadoCol = in_array('res_bit', $columnas, true) ? 'res_bit' : null;
        $usuarioCol = in_array('cod_usu', $columnas, true) ? 'cod_usu' : null;

        return DB::table('bitacora')
            ->when($fechaCol, fn ($query) => $query->orderByDesc($fechaCol))
            ->whereIn('tab_bit', ['gestion_academica', 'periodo_evaluacion', 'calendario_evento', 'respaldo_gestion_academica'])
            ->limit(5)
            ->get()
            ->map(function ($row) use ($fechaCol, $accionCol, $moduloCol, $resultadoCol, $usuarioCol) {
                return [
                    'fecha' => $this->fechaCorta($fechaCol ? ($row->{$fechaCol} ?? null) : null),
                    'responsable' => mb_strtoupper($row->rol_bit ?? 'Administración académica'),
                    'evento' => $this->humanizarAccion($accionCol ? ($row->{$accionCol} ?? 'SIN_ACCION') : 'SIN_ACCION'),
                    'modulo' => $moduloCol ? ($row->{$moduloCol} ?? 'Sin módulo') : 'Sin módulo',
                    'resultado' => $resultadoCol ? ($row->{$resultadoCol} ?? 'Sin resultado') : 'Sin resultado',
                ];
            })
            ->values()
            ->all();
    }

    // ============================================================
    // CLASES VISUALES
    // ============================================================

    public function badgeEstadoClass(string $estado): string
    {
        return match (GestionAcademicaInteligente::normalizarEstado($estado)) {
            GestionAcademicaInteligente::ESTADO_ACTIVA => 'ga-borde-primary ga-suave-primary ga-texto-primary',
            GestionAcademicaInteligente::ESTADO_PLANIFICADA => 'ga-borde-info ga-suave-info ga-texto-info',
            GestionAcademicaInteligente::ESTADO_EN_CIERRE => 'ga-borde-warning ga-suave-warning ga-texto-warning',
            GestionAcademicaInteligente::ESTADO_CERRADA => 'ga-borde-violet ga-suave-violet ga-texto-violet',
            GestionAcademicaInteligente::ESTADO_ANULADA => 'ga-borde-danger ga-suave-danger ga-texto-danger',
            default => 'ga-borde ga-fondo-suave ga-text',
        };
    }

    public function colorClass(string $color, string $tipo = 'soft'): string
    {
        $rol = ['emerald' => 'primary', 'sky' => 'info', 'violet' => 'violet', 'amber' => 'warning', 'rose' => 'danger'][$color] ?? 'primary';
        return 'ga-borde-'.$rol.' ga-suave-'.$rol.' ga-texto-'.$rol;
    }

    // ============================================================
    // NORMALIZACIÓN Y CONSULTAS
    // ============================================================

    private function gestionesNormalizadas(): Collection
    {
        if ($this->cacheGestiones !== null) {
            return $this->cacheGestiones;
        }
        if (! Schema::hasTable('gestion_academica')) {
            return collect();
        }

        return $this->cacheGestiones = DB::table('gestion_academica')
            ->orderByDesc('ani_gea')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($row) => $this->normalizarGestion($row))
            ->values();
    }

    private function normalizarGestion(object $row): array
    {
        $estadoOriginal = strtoupper($row->est_gea ?? 'SIN_ESTADO');
        $estadoNormalizado = GestionAcademicaInteligente::normalizarEstado($estadoOriginal);
        $resumenSoporte = $this->soporte()->resumenGestion($row->cod_gea, $this->showDetailDrawer && $this->selectedGestionId === $row->cod_gea);

        return [
            'id' => $row->cod_gea,
            'codigo' => $row->cod_gea,
            'anio' => (int) $row->ani_gea,
            'nombre' => 'Gestión Académica '.$row->ani_gea,
            'fecha_inicio' => $row->fii_gea,
            'fecha_fin' => $row->ffi_gea,
            'estado' => $estadoNormalizado,
            'estado_original' => $estadoOriginal,
            'modalidad' => 'Técnico Humanístico',
            'descripcion' => 'Expediente institucional anual para inscripción, planificación, desarrollo curricular, evaluación, cierre y respaldo académico.',
            'responsable' => 'Administración académica',
            'fecha_registro' => $row->created_at ?? null,
            'progreso' => $this->progresoFechas($row->fii_gea, $row->ffi_gea),
            'dias_transcurridos' => $this->diasTranscurridos($row->fii_gea),
            'dias_restantes' => $this->diasRestantes($row->ffi_gea),

            'estudiantes' => (int) ($resumenSoporte['inscripciones'] ?? 0),
            'cursos' => (int) ($resumenSoporte['cursos_activos'] ?? 0),
            'paralelos' => (int) ($resumenSoporte['paralelos_activos'] ?? 0),
            'turnos' => (int) ($resumenSoporte['turnos_activos'] ?? 0),
            'asignaturas' => (int) ($resumenSoporte['asignaturas_activas'] ?? 0),
            'especialidades' => (int) ($resumenSoporte['especialidades_activas'] ?? $this->contarActivos('especialidad_tecnica', 'est_esp')),
            'periodos' => (int) ($resumenSoporte['periodos_catalogo'] ?? 0),
            'planes_asignatura' => (int) ($resumenSoporte['planes_asignatura'] ?? 0),
            'planes_especialidad' => (int) ($resumenSoporte['planes_especialidad'] ?? 0),
            'horarios' => (int) ($resumenSoporte['horarios'] ?? 0),
            'calificaciones' => (int) ($resumenSoporte['calificaciones'] ?? 0),
            'clases_virtuales' => (int) ($resumenSoporte['clases_virtuales'] ?? 0),
            'tareas' => (int) ($resumenSoporte['tareas'] ?? 0),
            'asistencias' => (int) ($resumenSoporte['asistencias'] ?? 0),

            'fechas_sugeridas' => $resumenSoporte['fechas_sugeridas'] ?? [],
            'periodos_sugeridos' => $resumenSoporte['periodos_sugeridos'] ?? [],

            'ultima_actualizacion' => $this->fechaRelativa($row->updated_at ?? $row->created_at ?? null),
        ];
    }

    private function prepararFormularioInicial(): void
    {
        $this->fuenteFechasAplicadas = [];
        $anio = $this->siguienteAnioDisponible();
        $fechas = $this->soporte()->sugerirFechasGestion($anio);

        $this->form = [
            'anio' => (string) $anio,
            'nombre' => 'Gestión Académica '.$anio,
            'fecha_inicio' => $fechas['inicio_curricular'] ?? $this->soporte()->primerLunesFebrero($anio),
            'fecha_fin' => $fechas['cierre_curricular'] ?? "{$anio}-12-02",
            'modalidad' => 'Técnico Humanístico',
            'estado' => $this->existeGestionActiva()
                ? GestionAcademicaInteligente::ESTADO_PLANIFICADA
                : GestionAcademicaInteligente::ESTADO_ACTIVA,
            'descripcion' => '',
            'copiar_estructura' => false,
            'crear_periodos' => false,
        ];

        $this->actualizarRecomendacionesFormulario();
    }

    private function actualizarRecomendacionesFormulario(): void
    {
        $this->normalizarFormulario();
        if ($this->fuenteFechasAplicadas && (($this->fuenteFechasAplicadas['inicio'] ?? '') !== $this->form['fecha_inicio'] || ($this->fuenteFechasAplicadas['cierre'] ?? '') !== $this->form['fecha_fin'])) {
            $this->fuenteFechasAplicadas = [];
        }

        $anio = (int) ($this->form['anio'] ?: now()->year);

        $this->fechasSugeridas = $this->soporte()->sugerirFechasGestion($anio);
        $this->periodosSugeridos = $this->soporte()->sugerirPeriodosEvaluacion($anio);
        $this->analisisCreacion = $this->soporte()->analizarCreacion($this->form);
    }

    private function normalizarFormulario(): void
    {
        $anio = (int) ($this->form['anio'] ?: now()->year);

        $this->form['anio'] = (string) $anio;
        $this->form['nombre'] = trim((string) ($this->form['nombre'] ?: 'Gestión Académica '.$anio));
        $this->form['modalidad'] = trim((string) ($this->form['modalidad'] ?: 'Técnico Humanístico'));
        $this->form['estado'] = GestionAcademicaInteligente::normalizarEstado($this->form['estado'] ?? null);
        $this->form['descripcion'] = trim((string) ($this->form['descripcion'] ?? ''));
        $this->form['copiar_estructura'] = (bool) ($this->form['copiar_estructura'] ?? false);
        $this->form['crear_periodos'] = (bool) ($this->form['crear_periodos'] ?? false);
    }

    private function existeGestionActiva(): bool
    {
        if (! Schema::hasTable('gestion_academica')) {
            return false;
        }

        return DB::table('gestion_academica')
            ->whereIn('est_gea', GestionAcademicaInteligente::estadosActivosCompatibles())
            ->exists();
    }

    private function siguienteAnioDisponible(): int
    {
        if (! Schema::hasTable('gestion_academica')) {
            return now()->year;
        }

        $ultimo = DB::table('gestion_academica')->max('ani_gea');

        return $ultimo ? ((int) $ultimo + 1) : now()->year;
    }

    private function crearPeriodosBaseSiNoExisten(int $anio): void
    {
        if (! Schema::hasTable('periodo_evaluacion')) {
            return;
        }

        if (DB::table('periodo_evaluacion')->exists()) {
            return;
        }

        foreach ($this->soporte()->sugerirPeriodosEvaluacion($anio) as $periodo) {
            DB::table('periodo_evaluacion')->insert([
                'cod_pev' => $this->generarCodigoPeriodo(),
                'nom_pev' => $periodo['nombre'],
                'ord_pev' => $periodo['orden'],
                'est_pev' => 'ACTIVO',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    // ============================================================
    // CONTADORES
    // ============================================================

    private function contarTabla(string $table): int
    {
        if (! Schema::hasTable($table)) {
            return 0;
        }

        return DB::table($table)->count();
    }

    private function contarActivos(string $table, string $estadoColumn): int
    {
        if (! Schema::hasTable($table)) {
            return 0;
        }

        if (! Schema::hasColumn($table, $estadoColumn)) {
            return DB::table($table)->count();
        }

        return DB::table($table)
            ->where($estadoColumn, 'ACTIVO')
            ->count();
    }

    private function contarPorGestion(string $table, string $codGea): int
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'cod_gea')) {
            return 0;
        }

        return DB::table($table)
            ->where('cod_gea', $codGea)
            ->count();
    }

    private function contarPorGestionActiva(string $table): int
    {
        $activa = $this->gestionSeleccionada;

        if (! $activa) {
            return 0;
        }

        return $this->contarPorGestion($table, $activa['id']);
    }

    private function totalPendientesCierre(string $codGea): int
    {
        return array_sum(array_map(
            fn ($valor) => (int) $valor,
            $this->soporte()->pendientesCierre($codGea)
        ));
    }

    // ============================================================
    // CÓDIGOS
    // ============================================================

    private function generarCodigoGestion(): string
    {
        return \App\Support\Modelos\FormatoCodigoInstitucional::siguiente(DB::connection(), 'gestion_academica');
    }

    private function generarCodigoPeriodo(): string
    {
        return \App\Support\Modelos\FormatoCodigoInstitucional::siguiente(DB::connection(), 'periodo_evaluacion');
    }

    private function generarCodigoBitacora(): string
    {
        return \App\Support\Modelos\FormatoCodigoInstitucional::siguiente(DB::connection(), 'bitacora');
    }

    // ============================================================
    // FECHAS Y TEXTO
    // ============================================================

    private function progresoFechas(?string $inicio, ?string $fin): int
    {
        if (! $inicio || ! $fin) {
            return 0;
        }

        try {
            $inicioCarbon = Carbon::parse($inicio)->startOfDay();
            $finCarbon = Carbon::parse($fin)->startOfDay();
            $hoy = now()->startOfDay();

            if ($hoy->lessThanOrEqualTo($inicioCarbon)) {
                return 0;
            }

            if ($hoy->greaterThanOrEqualTo($finCarbon)) {
                return 100;
            }

            $total = max(1, $inicioCarbon->diffInDays($finCarbon));
            $actual = $inicioCarbon->diffInDays($hoy);

            return min(100, max(0, (int) round(($actual / $total) * 100)));
        } catch (\Throwable) {
            return 0;
        }
    }

    private function diasTranscurridos(?string $inicio): int
    {
        if (! $inicio) {
            return 0;
        }

        try {
            return max(0, Carbon::parse($inicio)->startOfDay()->diffInDays(now()->startOfDay(), false));
        } catch (\Throwable) {
            return 0;
        }
    }

    private function diasRestantes(?string $fin): int
    {
        if (! $fin) {
            return 0;
        }

        try {
            return max(0, now()->startOfDay()->diffInDays(Carbon::parse($fin)->startOfDay(), false));
        } catch (\Throwable) {
            return 0;
        }
    }

    private function fechaCorta(mixed $fecha): string
    {
        if (! $fecha) {
            return 'Sin registro';
        }

        try {
            return Carbon::parse($fecha)->format('d/m/Y H:i');
        } catch (\Throwable) {
            return (string) $fecha;
        }
    }

    private function fechaRelativa(mixed $fecha): string
    {
        if (! $fecha) {
            return 'Sin registro';
        }

        try {
            return Carbon::parse($fecha)->diffForHumans();
        } catch (\Throwable) {
            return (string) $fecha;
        }
    }

    private function humanizarAccion(string $accion): string
    {
        return match (strtoupper($accion)) {
            'CREAR_GESTION_ACADEMICA' => 'Se registró una nueva gestión académica.',
            'ACTIVAR_GESTION_ACADEMICA' => 'Se activó una gestión académica.',
            'INICIAR_CIERRE_GESTION_ACADEMICA' => 'Se inició la revisión de cierre de una gestión académica.',
            'CERRAR_GESTION_ACADEMICA' => 'Se cerró una gestión académica.',
            'PREPARAR_EXPORTACION_GESTION' => 'Se preparó una exportación de gestión académica.',
            'ACTUALIZAR_GESTION_ACADEMICA' => 'Se actualizó la información de una gestión académica.',
            'CREAR_PERIODO' => 'Se configuró un periodo académico.',
            'INSCRIBIR_ESTUDIANTE' => 'Se registró una inscripción académica.',
            'GENERAR_RESPALDO_ACADEMICO' => 'Se generó un respaldo académico institucional.',
            'VALIDAR_RESPALDO_ACADEMICO' => 'Se validó un respaldo académico para cierre.',
            'ARCHIVAR_RESPALDO_ACADEMICO' => 'Se archivó un respaldo académico como expediente histórico.',
            'OBSERVAR_RESPALDO_ACADEMICO' => 'Se marcó un respaldo académico como observado.',
            'SIN_ACCION' => 'Sin acción registrada.',
            default => ucfirst(mb_strtolower(str_replace('_', ' ', $accion))),
        };
    }

    // ============================================================
    // UTILIDADES
    // ============================================================

    private function soporte(): GestionAcademicaInteligente
    {
        return app(GestionAcademicaInteligente::class);
    }

    private function paginarColeccion(Collection $items, int $perPage = 6): LengthAwarePaginator
    {
        $page = Paginator::resolveCurrentPage() ?: 1;
        $items = $items->values();

        return new LengthAwarePaginator(
            $items->forPage($page, $perPage)->values(),
            $items->count(),
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );
    }

    private function limpiarRevisionCierre(): void
    {
        $this->revisionCierre = [
            'gestion' => 'Sin gestión seleccionada',
            'puede_cerrar' => false,
            'estado_inteligente' => 'SIN_DATOS',
            'nivel_riesgo' => 'BAJO',
            'mensaje' => 'No existe una gestión seleccionada para revisar.',
            'bloqueos' => [],
            'advertencias' => [],
            'sugerencias' => [],
            'resumen' => [],
            'pendientes_cierre' => [],

            'inscripciones_pendientes' => 0,
            'planes_asignatura_incompletos' => 0,
            'planes_especialidad_incompletos' => 0,
        ];
    }

    private function registrarBitacoraSeguro(
        string $accion,
        string $tabla,
        string $registro,
        string $descripcion,
        string $nombreVisible = '',
        string $nivel = 'SUCCESS',
        ?array $valoresAnteriores = null,
        ?array $valoresNuevos = null
    ): void {
        \App\Services\BitacoraService::registrar(
            accion: $accion, tabla: $tabla, registro: $registro, modulo: 'Gestión Académica',
            nombreRegistro: $nombreVisible, descripcion: $descripcion, nivel: $nivel,
            valoresAnteriores: $valoresAnteriores, valoresNuevos: $valoresNuevos
        );
    }

    private function generarCodigoRespaldo(): string
    {
        return \App\Support\Modelos\FormatoCodigoInstitucional::siguiente(DB::connection(), 'respaldo_gestion_academica');
    }

    private function alerta(string $icon, string $title, string $text): void
    {
        $this->cacheGestiones = null;
        if (in_array($icon, ['warning', 'error'], true)) {
            $this->avisosSesion = array_slice(array_merge($this->avisosSesion, [['nivel' => $icon, 'titulo' => $title, 'mensaje' => $text, 'vista' => $this->vista]]), -12);
        }
        $this->dispatch(
            'gestion-academica-alerta',
            icon: $icon,
            title: $title,
            text: $text
        );
    }
}
