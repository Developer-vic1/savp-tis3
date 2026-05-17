<?php

namespace App\Livewire\Admin;

use App\Support\Academico\GestionAcademicaInteligente;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;
use Livewire\WithPagination;

class GestionAcademica extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'tailwind';

    public string $vista = 'general';
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
        $this->prepararFormularioInicial();
    }

    public function render()
    {
        return view('livewire.admin.gestion-academica', [
            'gestiones' => $this->gestiones,
            'gestionActiva' => $this->gestionActiva,
            'gestionSeleccionada' => $this->gestionSeleccionada,
            'resumen' => $this->resumen,
            'periodos' => $this->periodos,
            'estructura' => $this->estructura,
            'pendientesCierre' => $this->pendientesCierre,
            'actividadReciente' => $this->actividadReciente,
            'aniosDisponibles' => $this->aniosDisponibles,
            'tablaDisponible' => Schema::hasTable('gestion_academica'),
            'estadosGestion' => GestionAcademicaInteligente::estadosParaSelect(),
            'fechasSugeridas' => $this->fechasSugeridas,
            'periodosSugeridos' => $this->periodosSugeridos,
            'analisisCreacion' => $this->analisisCreacion,
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

    public function cambiarVista(string $vista): void
    {
        $permitidas = [
            'general',
            'anios',
            'periodos',
            'estructura',
            'inscripciones',
            'reportes',
            'cierre',
        ];

        if (! in_array($vista, $permitidas, true)) {
            return;
        }

        $this->vista = $vista;
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
        $anio = (int) ($this->form['anio'] ?: now()->year);
        $fechas = $this->soporte()->sugerirFechasGestion($anio);

        $this->form['fecha_inicio'] = $fechas['inicio_institucional'] ?? "{$anio}-01-19";
        $this->form['fecha_fin'] = $fechas['cierre_institucional'] ?? "{$anio}-12-11";

        $this->actualizarRecomendacionesFormulario();
    }

    public function aplicarFechasCurriculares(): void
    {
        $anio = (int) ($this->form['anio'] ?: now()->year);
        $fechas = $this->soporte()->sugerirFechasGestion($anio);

        $this->form['fecha_inicio'] = $fechas['inicio_curricular'] ?? "{$anio}-02-02";
        $this->form['fecha_fin'] = $fechas['cierre_curricular'] ?? "{$anio}-12-02";

        $this->actualizarRecomendacionesFormulario();
    }

    public function crearGestionAcademica(): void
    {
        $this->normalizarFormulario();

        $this->validate([
            'form.anio' => ['required', 'integer', 'min:2020', 'max:2100'],
            'form.fecha_inicio' => ['required', 'date'],
            'form.fecha_fin' => ['required', 'date', 'after:form.fecha_inicio'],
            'form.estado' => ['required', 'in:' . implode(',', GestionAcademicaInteligente::estados())],
        ], [
            'form.anio.required' => 'El año de gestión es obligatorio.',
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
                descripcion: 'Se registró la gestión académica ' . $this->form['anio'] . ' con estado ' . $this->form['estado'] . '.'
            );

            DB::commit();

            $this->showCreateModal = false;
            $this->resetValidation();
            $this->resetPage();

            $this->alerta(
                'success',
                'Gestión académica registrada',
                'La gestión académica ' . $this->form['anio'] . ' fue creada correctamente.'
            );

            $this->prepararFormularioInicial();
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            $this->alerta(
                'error',
                'No se pudo registrar',
                'Ocurrió un error al crear la gestión académica. Revisa storage/logs/laravel.log.'
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
    }

    public function cerrarDetalle(): void
    {
        $this->showDetailDrawer = false;
        $this->selectedGestionId = null;
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
                descripcion: 'Se activó la gestión académica ' . $gestion->ani_gea . '.'
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
                descripcion: 'La gestión académica ' . $gestion->ani_gea . ' pasó a EN_CIERRE.'
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
                descripcion: 'Se cerró definitivamente la gestión académica ' . $gestion->ani_gea . '.'
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

    public function exportarGestion(string $id, string $tipo = 'COMPLETA'): void
    {
        if ($id === '') {
            $this->alerta('warning', 'Sin gestión seleccionada', 'No existe una gestión académica seleccionada para exportar.');
            return;
        }

        $analisis = $this->soporte()->analizarExportacion($id, $tipo);

        if (! ($analisis['puede_continuar'] ?? false)) {
            $this->alerta(
                'warning',
                'Exportación bloqueada',
                $analisis['mensaje'] ?? 'La gestión no puede prepararse para exportación.'
            );

            return;
        }

        $gestion = $this->gestionesNormalizadas()->firstWhere('id', $id);

        if (! $gestion) {
            $this->alerta('warning', 'Gestión no encontrada', 'La gestión académica seleccionada no existe.');
            return;
        }

        $this->registrarBitacoraSeguro(
            accion: 'PREPARAR_EXPORTACION_GESTION',
            tabla: 'gestion_academica',
            registro: $id,
            descripcion: 'Se preparó la exportación ' . strtoupper($tipo) . ' de ' . $gestion['nombre'] . '.'
        );

        $this->alerta(
            'info',
            'Respaldo preparado',
            'La ' . strtolower($tipo) . ' de ' . $gestion['nombre'] . ' queda validada para conectarse posteriormente con PDF, Excel o ZIP.'
        );
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
        $gestiones = $this->gestionesNormalizadas();
        $activa = $this->gestionActiva;

        return [
            [
                'titulo' => 'Gestiones registradas',
                'valor' => $gestiones->count(),
                'descripcion' => 'Expedientes académicos anuales',
                'color' => 'sky',
            ],
            [
                'titulo' => 'Gestión activa',
                'valor' => $gestiones->where('estado', GestionAcademicaInteligente::ESTADO_ACTIVA)->count(),
                'descripcion' => 'Solo una gestión operativa',
                'color' => 'emerald',
            ],
            [
                'titulo' => 'En cierre',
                'valor' => $gestiones->where('estado', GestionAcademicaInteligente::ESTADO_EN_CIERRE)->count(),
                'descripcion' => 'Revisión institucional',
                'color' => 'amber',
            ],
            [
                'titulo' => 'Gestiones cerradas',
                'valor' => $gestiones->where('estado', GestionAcademicaInteligente::ESTADO_CERRADA)->count(),
                'descripcion' => 'Historial recuperable',
                'color' => 'violet',
            ],
            [
                'titulo' => 'Periodos configurados',
                'valor' => $this->contarTabla('periodo_evaluacion'),
                'descripcion' => 'Catálogo evaluativo',
                'color' => 'violet',
            ],
            [
                'titulo' => 'Estudiantes inscritos',
                'valor' => $activa ? ($activa['estudiantes'] ?? 0) : 0,
                'descripcion' => 'En gestión activa',
                'color' => 'sky',
            ],
            [
                'titulo' => 'Planes de asignatura',
                'valor' => $activa ? ($activa['planes_asignatura'] ?? 0) : 0,
                'descripcion' => 'Planificación vigente',
                'color' => 'emerald',
            ],
            [
                'titulo' => 'Pendientes de cierre',
                'valor' => $activa ? $this->totalPendientesCierre($activa['id']) : 0,
                'descripcion' => 'Alertas institucionales',
                'color' => 'amber',
            ],
        ];
    }

    public function getPeriodosProperty(): array
    {
        $anio = $this->gestionActiva['anio'] ?? (int) ($this->form['anio'] ?: now()->year);
        $sugeridos = $this->soporte()->sugerirPeriodosEvaluacion((int) $anio);

        if (! Schema::hasTable('periodo_evaluacion')) {
            return collect($sugeridos)
                ->map(fn(array $periodo) => array_merge($periodo, [
                    'id' => null,
                    'estado' => 'SUGERIDO',
                    'progreso' => $this->progresoFechas($periodo['fecha_inicio'], $periodo['fecha_fin']),
                ]))
                ->values()
                ->all();
        }

        $catalogo = DB::table('periodo_evaluacion')
            ->orderByRaw('ord_pev IS NULL')
            ->orderBy('ord_pev')
            ->orderBy('nom_pev')
            ->get()
            ->map(function ($periodo) use ($sugeridos) {
                $orden = (int) ($periodo->ord_pev ?? 0);
                $sugerido = collect($sugeridos)->firstWhere('orden', $orden) ?? [];

                return [
                    'id' => $periodo->cod_pev,
                    'nombre' => $periodo->nom_pev,
                    'orden' => $orden,
                    'estado' => strtoupper($periodo->est_pev ?? 'SIN_ESTADO'),
                    'fecha_inicio' => $sugerido['fecha_inicio'] ?? null,
                    'fecha_fin' => $sugerido['fecha_fin'] ?? null,
                    'dias_habiles_referencia' => $sugerido['dias_habiles_referencia'] ?? 0,
                    'incluye_descanso_pedagogico' => $sugerido['incluye_descanso_pedagogico'] ?? false,
                    'descanso_pedagogico_dias_habiles' => $sugerido['descanso_pedagogico_dias_habiles'] ?? 0,
                    'progreso' => $this->progresoFechas($sugerido['fecha_inicio'] ?? null, $sugerido['fecha_fin'] ?? null),
                ];
            })
            ->values()
            ->all();

        return count($catalogo) > 0
            ? $catalogo
            : collect($sugeridos)
            ->map(fn(array $periodo) => array_merge($periodo, [
                'id' => null,
                'estado' => 'SUGERIDO',
                'progreso' => $this->progresoFechas($periodo['fecha_inicio'], $periodo['fecha_fin']),
            ]))
            ->values()
            ->all();
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
                'detalle' => 'Asociados a gestión activa',
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
        $activa = $this->gestionActiva;

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
            ->when($fechaCol, fn($query) => $query->orderByDesc($fechaCol))
            ->limit(5)
            ->get()
            ->map(function ($row) use ($fechaCol, $accionCol, $moduloCol, $resultadoCol, $usuarioCol) {
                return [
                    'fecha' => $this->fechaCorta($fechaCol ? ($row->{$fechaCol} ?? null) : null),
                    'responsable' => $usuarioCol ? ($row->{$usuarioCol} ?? 'Sin usuario') : 'Sin usuario',
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
            GestionAcademicaInteligente::ESTADO_ACTIVA => 'border-emerald-300 bg-emerald-50 text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-950 dark:text-emerald-300',
            GestionAcademicaInteligente::ESTADO_PLANIFICADA => 'border-sky-300 bg-sky-50 text-sky-800 dark:border-sky-500/30 dark:bg-sky-950 dark:text-sky-300',
            GestionAcademicaInteligente::ESTADO_EN_CIERRE => 'border-amber-300 bg-amber-50 text-amber-800 dark:border-amber-500/30 dark:bg-amber-950 dark:text-amber-300',
            GestionAcademicaInteligente::ESTADO_CERRADA => 'border-violet-300 bg-violet-50 text-violet-800 dark:border-violet-500/30 dark:bg-violet-950 dark:text-violet-300',
            GestionAcademicaInteligente::ESTADO_ANULADA => 'border-rose-300 bg-rose-50 text-rose-800 dark:border-rose-500/30 dark:bg-rose-950 dark:text-rose-300',
            default => 'border-slate-300 bg-slate-50 text-slate-800 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-300',
        };
    }

    public function colorClass(string $color, string $tipo = 'soft'): string
    {
        return match ($color . '-' . $tipo) {
            'emerald-soft' => 'border-emerald-300 bg-emerald-50 text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-950 dark:text-emerald-300',
            'sky-soft' => 'border-sky-300 bg-sky-50 text-sky-800 dark:border-sky-500/30 dark:bg-sky-950 dark:text-sky-300',
            'violet-soft' => 'border-violet-300 bg-violet-50 text-violet-800 dark:border-violet-500/30 dark:bg-violet-950 dark:text-violet-300',
            'amber-soft' => 'border-amber-300 bg-amber-50 text-amber-800 dark:border-amber-500/30 dark:bg-amber-950 dark:text-amber-300',
            'rose-soft' => 'border-rose-300 bg-rose-50 text-rose-800 dark:border-rose-500/30 dark:bg-rose-950 dark:text-rose-300',
            default => 'border-slate-300 bg-slate-50 text-slate-800 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-300',
        };
    }

    // ============================================================
    // NORMALIZACIÓN Y CONSULTAS
    // ============================================================

    private function gestionesNormalizadas(): Collection
    {
        if (! Schema::hasTable('gestion_academica')) {
            return collect();
        }

        return DB::table('gestion_academica')
            ->orderByDesc('ani_gea')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($row) => $this->normalizarGestion($row))
            ->values();
    }

    private function normalizarGestion(object $row): array
    {
        $estadoOriginal = strtoupper($row->est_gea ?? 'SIN_ESTADO');
        $estadoNormalizado = GestionAcademicaInteligente::normalizarEstado($estadoOriginal);
        $resumenSoporte = $this->soporte()->resumenGestion($row->cod_gea);

        return [
            'id' => $row->cod_gea,
            'codigo' => $row->cod_gea,
            'anio' => (int) $row->ani_gea,
            'nombre' => 'Gestión Académica ' . $row->ani_gea,
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
        $anio = $this->siguienteAnioDisponible();
        $fechas = $this->soporte()->sugerirFechasGestion($anio);

        $this->form = [
            'anio' => (string) $anio,
            'nombre' => 'Gestión Académica ' . $anio,
            'fecha_inicio' => $fechas['inicio_institucional'] ?? "{$anio}-01-19",
            'fecha_fin' => $fechas['cierre_institucional'] ?? "{$anio}-12-11",
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

        $anio = (int) ($this->form['anio'] ?: now()->year);

        $this->fechasSugeridas = $this->soporte()->sugerirFechasGestion($anio);
        $this->periodosSugeridos = $this->soporte()->sugerirPeriodosEvaluacion($anio);
        $this->analisisCreacion = $this->soporte()->analizarCreacion($this->form);
    }

    private function normalizarFormulario(): void
    {
        $anio = (int) ($this->form['anio'] ?: now()->year);

        $this->form['anio'] = (string) $anio;
        $this->form['nombre'] = trim((string) ($this->form['nombre'] ?: 'Gestión Académica ' . $anio));
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
        $activa = $this->gestionActiva;

        if (! $activa) {
            return 0;
        }

        return $this->contarPorGestion($table, $activa['id']);
    }

    private function totalPendientesCierre(string $codGea): int
    {
        return array_sum(array_map(
            fn($valor) => (int) $valor,
            $this->soporte()->pendientesCierre($codGea)
        ));
    }

    // ============================================================
    // CÓDIGOS
    // ============================================================

    private function generarCodigoGestion(): string
    {
        $ultimo = DB::table('gestion_academica')
            ->where('cod_gea', 'like', 'GEA_%')
            ->orderByDesc('cod_gea')
            ->value('cod_gea');

        $numero = 1;

        if ($ultimo && preg_match('/GEA_(\d+)/', $ultimo, $matches)) {
            $numero = ((int) $matches[1]) + 1;
        }

        return 'GEA_' . str_pad((string) $numero, 4, '0', STR_PAD_LEFT);
    }

    private function generarCodigoPeriodo(): string
    {
        $ultimo = DB::table('periodo_evaluacion')
            ->where('cod_pev', 'like', 'PEV_%')
            ->orderByDesc('cod_pev')
            ->value('cod_pev');

        $numero = 1;

        if ($ultimo && preg_match('/PEV_(\d+)/', $ultimo, $matches)) {
            $numero = ((int) $matches[1]) + 1;
        }

        return 'PEV_' . str_pad((string) $numero, 4, '0', STR_PAD_LEFT);
    }

    private function generarCodigoBitacora(): string
    {
        if (! Schema::hasTable('bitacora') || ! Schema::hasColumn('bitacora', 'cod_bit')) {
            return '';
        }

        $ultimo = DB::table('bitacora')
            ->where('cod_bit', 'like', 'BIT_%')
            ->orderByDesc('cod_bit')
            ->value('cod_bit');

        $numero = 1;

        if ($ultimo && preg_match('/BIT_(\d+)/', $ultimo, $matches)) {
            $numero = ((int) $matches[1]) + 1;
        }

        return 'BIT_' . str_pad((string) $numero, 4, '0', STR_PAD_LEFT);
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
        string $descripcion
    ): void {
        if (! Schema::hasTable('bitacora')) {
            return;
        }

        $columnas = Schema::getColumnListing('bitacora');
        $payload = [];

        if (in_array('cod_bit', $columnas, true)) {
            $payload['cod_bit'] = $this->generarCodigoBitacora();
        }

        if (in_array('acc_bit', $columnas, true)) {
            $payload['acc_bit'] = $accion;
        }

        if (in_array('tab_bit', $columnas, true)) {
            $payload['tab_bit'] = $tabla;
        }

        if (in_array('reg_bit', $columnas, true)) {
            $payload['reg_bit'] = $registro;
        }

        if (in_array('cod_usu', $columnas, true)) {
            $payload['cod_usu'] = Auth::id();
        }

        if (in_array('fec_bit', $columnas, true)) {
            $payload['fec_bit'] = now();
        }

        if (in_array('est_bit', $columnas, true)) {
            $payload['est_bit'] = 'ACTIVO';
        }

        if (in_array('mod_bit', $columnas, true)) {
            $payload['mod_bit'] = 'Gestión Académica';
        }

        if (in_array('nom_reg_bit', $columnas, true)) {
            $payload['nom_reg_bit'] = $registro;
        }

        if (in_array('des_bit', $columnas, true)) {
            $payload['des_bit'] = $descripcion;
        }

        if (in_array('niv_bit', $columnas, true)) {
            $payload['niv_bit'] = 'SUCCESS';
        }

        if (in_array('res_bit', $columnas, true)) {
            $payload['res_bit'] = 'EXITOSO';
        }

        if (in_array('created_at', $columnas, true)) {
            $payload['created_at'] = now();
        }

        if (in_array('updated_at', $columnas, true)) {
            $payload['updated_at'] = now();
        }

        if (! empty($payload)) {
            DB::table('bitacora')->insert($payload);
        }
    }

    private function alerta(string $icon, string $title, string $text): void
    {
        $this->dispatch(
            'gestion-academica-alerta',
            icon: $icon,
            title: $title,
            text: $text
        );
    }
}
