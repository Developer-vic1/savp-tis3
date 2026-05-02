<?php

namespace App\Livewire\Admin;

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

    public array $revisionCierre = [
        'gestion' => 'Sin gestión seleccionada',
        'inscripciones_pendientes' => 0,
        'planes_asignatura_incompletos' => 0,
        'planes_especialidad_incompletos' => 0,
        'puede_cerrar' => false,
    ];

    public array $form = [
        'anio' => '',
        'nombre' => '',
        'fecha_inicio' => '',
        'fecha_fin' => '',
        'modalidad' => 'Técnico Humanístico',
        'estado' => 'ACTIVO',
        'descripcion' => '',
        'copiar_estructura' => false,
        'crear_periodos' => false,
    ];

    public function mount(): void
    {
        $anio = $this->siguienteAnioDisponible();

        $this->form = [
            'anio' => (string) $anio,
            'nombre' => 'Gestión Académica ' . $anio,
            'fecha_inicio' => $anio . '-02-03',
            'fecha_fin' => $anio . '-11-30',
            'modalidad' => 'Técnico Humanístico',
            'estado' => $this->existeGestionActiva() ? 'PLANIFICADO' : 'ACTIVO',
            'descripcion' => '',
            'copiar_estructura' => false,
            'crear_periodos' => false,
        ];
    }

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

    public function abrirNuevaGestion(): void
    {
        $anio = $this->siguienteAnioDisponible();

        $this->resetValidation();

        $this->form = [
            'anio' => (string) $anio,
            'nombre' => 'Gestión Académica ' . $anio,
            'fecha_inicio' => $anio . '-02-03',
            'fecha_fin' => $anio . '-11-30',
            'modalidad' => 'Técnico Humanístico',
            'estado' => $this->existeGestionActiva() ? 'PLANIFICADO' : 'ACTIVO',
            'descripcion' => '',
            'copiar_estructura' => false,
            'crear_periodos' => false,
        ];

        $this->showCreateModal = true;
    }

    public function cerrarModal(): void
    {
        $this->showCreateModal = false;
        $this->resetValidation();
    }

    public function crearGestionAcademica(): void
    {
        $this->validate([
            'form.anio' => ['required', 'integer', 'min:2020', 'max:2100'],
            'form.fecha_inicio' => ['nullable', 'date'],
            'form.fecha_fin' => ['nullable', 'date', 'after_or_equal:form.fecha_inicio'],
            'form.estado' => ['required', 'in:ACTIVO,INACTIVO,CERRADO,ARCHIVADO,PLANIFICADO'],
        ], [
            'form.anio.required' => 'El año de gestión es obligatorio.',
            'form.anio.integer' => 'El año de gestión debe ser numérico.',
            'form.anio.min' => 'El año de gestión no puede ser menor a 2020.',
            'form.anio.max' => 'El año de gestión no puede ser mayor a 2100.',
            'form.fecha_inicio.date' => 'La fecha de inicio no es válida.',
            'form.fecha_fin.date' => 'La fecha de finalización no es válida.',
            'form.fecha_fin.after_or_equal' => 'La fecha de finalización debe ser igual o posterior a la fecha de inicio.',
            'form.estado.required' => 'El estado de gestión es obligatorio.',
            'form.estado.in' => 'El estado seleccionado no es válido.',
        ]);

        if (! Schema::hasTable('gestion_academica')) {
            $this->alerta(
                'warning',
                'Tabla no encontrada',
                'No existe la tabla gestion_academica.'
            );

            return;
        }

        if ($this->existeAnioGestion((int) $this->form['anio'])) {
            $this->alerta(
                'warning',
                'Gestión duplicada',
                'Ya existe una gestión académica registrada para el año ' . $this->form['anio'] . '.'
            );

            return;
        }

        if ($this->form['estado'] === 'ACTIVO' && $this->existeGestionActiva()) {
            $this->alerta(
                'warning',
                'Ya existe una gestión activa',
                'Solo puede existir una gestión académica ACTIVA. Cierra o cambia el estado de la gestión actual antes de activar una nueva.'
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
                $this->crearPeriodosBaseSiNoExisten();
            }

            $this->registrarBitacoraSeguro(
                accion: 'CREAR_GESTION_ACADEMICA',
                tabla: 'gestion_academica',
                registro: $codGea,
                descripcion: 'Se registró la gestión académica ' . $this->form['anio'] . '.'
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
        } catch (\Throwable $e) {
            DB::rollBack();

            report($e);

            $this->alerta(
                'error',
                'No se pudo registrar',
                'Ocurrió un error al crear la gestión académica. Revisa storage/logs/laravel.log para ver el detalle exacto.'
            );
        }
    }

    public function abrirDetalle(string $id): void
    {
        $gestion = $this->gestionesNormalizadas()->firstWhere('id', $id);

        if (! $gestion) {
            $this->alerta(
                'warning',
                'Gestión no encontrada',
                'La gestión académica seleccionada no existe.'
            );

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

    public function activarGestion(string $id): void
    {
        if (! Schema::hasTable('gestion_academica')) {
            $this->alerta(
                'warning',
                'Tabla no encontrada',
                'No existe la tabla gestion_academica.'
            );

            return;
        }

        $gestion = DB::table('gestion_academica')
            ->where('cod_gea', $id)
            ->first();

        if (! $gestion) {
            $this->alerta(
                'warning',
                'Gestión no encontrada',
                'La gestión académica seleccionada no existe.'
            );

            return;
        }

        if (($gestion->est_gea ?? null) === 'ACTIVO') {
            $this->alerta(
                'info',
                'Gestión ya activa',
                'La gestión seleccionada ya se encuentra ACTIVA.'
            );

            return;
        }

        if ($this->existeGestionActiva()) {
            $this->alerta(
                'warning',
                'Acción bloqueada',
                'Ya existe una gestión académica ACTIVA. Primero cierra o cambia el estado de la gestión activa actual.'
            );

            return;
        }

        DB::table('gestion_academica')
            ->where('cod_gea', $id)
            ->update([
                'est_gea' => 'ACTIVO',
                'updated_at' => now(),
            ]);

        $this->registrarBitacoraSeguro(
            accion: 'ACTIVAR_GESTION_ACADEMICA',
            tabla: 'gestion_academica',
            registro: $id,
            descripcion: 'Se activó la gestión académica ' . $gestion->ani_gea . '.'
        );

        $this->alerta(
            'success',
            'Gestión activada',
            'La gestión académica fue marcada como ACTIVA.'
        );
    }

    public function prepararCierre(string $id): void
    {
        if ($id === '') {
            $this->alerta(
                'warning',
                'Sin gestión seleccionada',
                'No existe una gestión académica seleccionada para revisar el cierre.'
            );

            return;
        }

        $gestion = $this->gestionesNormalizadas()
            ->firstWhere('id', $id);

        if (! $gestion) {
            $this->alerta(
                'warning',
                'Gestión no encontrada',
                'La gestión académica seleccionada no existe o ya no está disponible.'
            );

            return;
        }

        if ($gestion['estado'] !== 'ACTIVO') {
            $this->alerta(
                'warning',
                'Cierre no permitido',
                'Solo se puede cerrar una gestión académica con estado ACTIVO.'
            );

            return;
        }

        $inscripcionesPendientes = $this->contarPendientesInscripcionPorGestion($id);
        $planesAsignaturaIncompletos = $this->contarPlanesAsignaturaIncompletosPorGestion($id);
        $planesEspecialidadIncompletos = $this->contarPlanesEspecialidadIncompletosPorGestion($id);

        $puedeCerrar = $inscripcionesPendientes === 0
            && $planesAsignaturaIncompletos === 0
            && $planesEspecialidadIncompletos === 0;

        $this->revisionCierre = [
            'gestion' => $gestion['nombre'],
            'inscripciones_pendientes' => $inscripcionesPendientes,
            'planes_asignatura_incompletos' => $planesAsignaturaIncompletos,
            'planes_especialidad_incompletos' => $planesEspecialidadIncompletos,
            'puede_cerrar' => $puedeCerrar,
        ];

        $this->gestionParaCerrarId = $id;
        $this->showCloseModal = true;
    }

    public function confirmarCierreGestion(): void
    {
        if (! $this->gestionParaCerrarId) {
            $this->alerta(
                'warning',
                'Sin gestión seleccionada',
                'No existe una gestión académica seleccionada para cerrar.'
            );

            return;
        }

        if (! Schema::hasTable('gestion_academica')) {
            $this->alerta(
                'warning',
                'Tabla no encontrada',
                'No existe la tabla gestion_academica.'
            );

            return;
        }

        $gestion = DB::table('gestion_academica')
            ->where('cod_gea', $this->gestionParaCerrarId)
            ->first();

        if (! $gestion) {
            $this->alerta(
                'warning',
                'Gestión no encontrada',
                'La gestión académica seleccionada no existe.'
            );

            return;
        }

        if (($gestion->est_gea ?? null) !== 'ACTIVO') {
            $this->alerta(
                'warning',
                'Cierre no permitido',
                'Solo se puede cerrar una gestión académica con estado ACTIVO.'
            );

            return;
        }

        $inscripcionesPendientes = $this->contarPendientesInscripcionPorGestion($this->gestionParaCerrarId);
        $planesAsignaturaIncompletos = $this->contarPlanesAsignaturaIncompletosPorGestion($this->gestionParaCerrarId);
        $planesEspecialidadIncompletos = $this->contarPlanesEspecialidadIncompletosPorGestion($this->gestionParaCerrarId);

        if (
            $inscripcionesPendientes > 0 ||
            $planesAsignaturaIncompletos > 0 ||
            $planesEspecialidadIncompletos > 0
        ) {
            $this->revisionCierre = [
                'gestion' => 'Gestión Académica ' . $gestion->ani_gea,
                'inscripciones_pendientes' => $inscripcionesPendientes,
                'planes_asignatura_incompletos' => $planesAsignaturaIncompletos,
                'planes_especialidad_incompletos' => $planesEspecialidadIncompletos,
                'puede_cerrar' => false,
            ];

            $this->alerta(
                'warning',
                'Cierre bloqueado',
                'No se puede cerrar la gestión porque existen procesos académicos pendientes.'
            );

            return;
        }

        DB::beginTransaction();

        try {
            DB::table('gestion_academica')
                ->where('cod_gea', $this->gestionParaCerrarId)
                ->update([
                    'est_gea' => 'CERRADO',
                    'updated_at' => now(),
                ]);

            $this->registrarBitacoraSeguro(
                accion: 'CERRAR_GESTION_ACADEMICA',
                tabla: 'gestion_academica',
                registro: $this->gestionParaCerrarId,
                descripcion: 'Se cerró la gestión académica ' . $gestion->ani_gea . '.'
            );

            DB::commit();

            $this->showCloseModal = false;
            $this->showDetailDrawer = false;
            $this->gestionParaCerrarId = null;
            $this->selectedGestionId = null;

            $this->revisionCierre = [
                'gestion' => 'Sin gestión seleccionada',
                'inscripciones_pendientes' => 0,
                'planes_asignatura_incompletos' => 0,
                'planes_especialidad_incompletos' => 0,
                'puede_cerrar' => false,
            ];

            $this->alerta(
                'success',
                'Gestión académica cerrada',
                'La gestión académica fue cerrada correctamente y queda disponible como historial institucional.'
            );
        } catch (\Throwable $e) {
            DB::rollBack();

            report($e);

            $this->alerta(
                'error',
                'No se pudo cerrar',
                'Ocurrió un error al cerrar la gestión académica. Revisa el log del sistema.'
            );
        }
    }

    public function exportarGestion(string $id): void
    {
        if ($id === '') {
            $this->alerta(
                'warning',
                'Sin gestión seleccionada',
                'No existe una gestión académica seleccionada para exportar.'
            );

            return;
        }

        $gestion = $this->gestionesNormalizadas()->firstWhere('id', $id);

        if (! $gestion) {
            $this->alerta(
                'warning',
                'Gestión no encontrada',
                'La gestión académica seleccionada no existe.'
            );

            return;
        }

        $this->alerta(
            'info',
            'Exportación pendiente',
            'La exportación de ' . $gestion['nombre'] . ' queda lista para conectar posteriormente con PDF o Excel.'
        );
    }

    public function limpiarFiltros(): void
    {
        $this->busqueda = '';
        $this->filtroEstado = '';
        $this->filtroAnio = '';

        $this->resetPage();
    }

    public function getGestionesProperty(): LengthAwarePaginator
    {
        $coleccion = $this->gestionesNormalizadas();

        if ($this->busqueda !== '') {
            $buscar = mb_strtolower(trim($this->busqueda));

            $coleccion = $coleccion->filter(function (array $gestion) use ($buscar) {
                return str_contains((string) $gestion['anio'], $buscar)
                    || str_contains(mb_strtolower($gestion['nombre']), $buscar)
                    || str_contains(mb_strtolower($gestion['estado']), $buscar)
                    || str_contains(mb_strtolower($gestion['codigo']), $buscar);
            });
        }

        if ($this->filtroEstado !== '') {
            $coleccion = $coleccion->where('estado', $this->filtroEstado);
        }

        if ($this->filtroAnio !== '') {
            $coleccion = $coleccion->where('anio', (int) $this->filtroAnio);
        }

        return $this->paginarColeccion($coleccion->values(), 6);
    }

    public function getGestionActivaProperty(): ?array
    {
        return $this->gestionesNormalizadas()
            ->firstWhere('estado', 'ACTIVO');
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
                'descripcion' => 'Total histórico real',
                'color' => 'sky',
            ],
            [
                'titulo' => 'Gestión activa',
                'valor' => $gestiones->where('estado', 'ACTIVO')->count(),
                'descripcion' => 'Solo una permitida',
                'color' => 'emerald',
            ],
            [
                'titulo' => 'Periodos configurados',
                'valor' => $this->contarTabla('periodo_evaluacion'),
                'descripcion' => 'Catálogo general',
                'color' => 'violet',
            ],
            [
                'titulo' => 'Estudiantes inscritos',
                'valor' => $activa ? $this->contarInscripcionesPorGestion($activa['id']) : 0,
                'descripcion' => 'En gestión activa',
                'color' => 'sky',
            ],
            [
                'titulo' => 'Cursos habilitados',
                'valor' => $this->contarActivos('curso', 'est_cur'),
                'descripcion' => 'Cursos activos',
                'color' => 'emerald',
            ],
            [
                'titulo' => 'Paralelos habilitados',
                'valor' => $this->contarActivos('paralelo', 'est_par'),
                'descripcion' => 'Paralelos activos',
                'color' => 'slate',
            ],
            [
                'titulo' => 'Especialidades técnicas',
                'valor' => $this->contarActivos('especialidad_tecnica', 'est_esp'),
                'descripcion' => 'Especialidades activas',
                'color' => 'violet',
            ],
            [
                'titulo' => 'Docentes activos',
                'valor' => $this->contarActivos('docente', 'est_doc'),
                'descripcion' => 'Registros docentes',
                'color' => 'amber',
            ],
        ];
    }

    public function getPeriodosProperty(): array
    {
        if (! Schema::hasTable('periodo_evaluacion')) {
            return [];
        }

        return DB::table('periodo_evaluacion')
            ->orderByRaw('ord_pev IS NULL')
            ->orderBy('ord_pev')
            ->orderBy('nom_pev')
            ->get()
            ->map(function ($periodo) {
                return [
                    'id' => $periodo->cod_pev,
                    'nombre' => $periodo->nom_pev,
                    'orden' => $periodo->ord_pev,
                    'estado' => strtoupper($periodo->est_pev ?? 'SIN_ESTADO'),
                    'fecha_inicio' => null,
                    'fecha_fin' => null,
                    'progreso' => 0,
                ];
            })
            ->values()
            ->all();
    }

    public function getEstructuraProperty(): array
    {
        return [
            [
                'titulo' => 'Cursos activos',
                'valor' => $this->contarActivos('curso', 'est_cur'),
                'detalle' => 'Tabla curso',
                'color' => 'emerald',
            ],
            [
                'titulo' => 'Paralelos activos',
                'valor' => $this->contarActivos('paralelo', 'est_par'),
                'detalle' => 'Tabla paralelo',
                'color' => 'sky',
            ],
            [
                'titulo' => 'Turnos activos',
                'valor' => $this->contarActivos('turno', 'est_tur'),
                'detalle' => 'Tabla turno',
                'color' => 'violet',
            ],
            [
                'titulo' => 'Especialidades activas',
                'valor' => $this->contarActivos('especialidad_tecnica', 'est_esp'),
                'detalle' => 'Tabla especialidad_tecnica',
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
                'detalle' => 'Asociados a gestión activa',
                'color' => 'emerald',
            ],
        ];
    }

    public function getPendientesCierreProperty(): array
    {
        $activa = $this->gestionActiva;

        if (! $activa) {
            return [
                [
                    'titulo' => 'Inscripciones pendientes',
                    'valor' => 0,
                    'color' => 'amber',
                ],
                [
                    'titulo' => 'Planes de asignatura incompletos',
                    'valor' => 0,
                    'color' => 'amber',
                ],
                [
                    'titulo' => 'Planes de especialidad incompletos',
                    'valor' => 0,
                    'color' => 'amber',
                ],
                [
                    'titulo' => 'Reportes pendientes',
                    'valor' => 0,
                    'color' => 'rose',
                ],
            ];
        }

        return [
            [
                'titulo' => 'Inscripciones pendientes',
                'valor' => $this->contarPendientesInscripcionPorGestion($activa['id']),
                'color' => 'amber',
            ],
            [
                'titulo' => 'Planes de asignatura incompletos',
                'valor' => $this->contarPlanesAsignaturaIncompletosPorGestion($activa['id']),
                'color' => 'amber',
            ],
            [
                'titulo' => 'Planes de especialidad incompletos',
                'valor' => $this->contarPlanesEspecialidadIncompletosPorGestion($activa['id']),
                'color' => 'amber',
            ],
            [
                'titulo' => 'Reportes pendientes',
                'valor' => $this->contarReportesPendientes(),
                'color' => 'rose',
            ],
        ];
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

    public function badgeEstadoClass(string $estado): string
    {
        return match (strtoupper($estado)) {
            'ACTIVO' => 'border-emerald-300 bg-emerald-50 text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-950 dark:text-emerald-300',
            'INACTIVO' => 'border-rose-300 bg-rose-50 text-rose-800 dark:border-rose-500/30 dark:bg-rose-950 dark:text-rose-300',
            'PLANIFICADO' => 'border-sky-300 bg-sky-50 text-sky-800 dark:border-sky-500/30 dark:bg-sky-950 dark:text-sky-300',
            'CERRADO' => 'border-violet-300 bg-violet-50 text-violet-800 dark:border-violet-500/30 dark:bg-violet-950 dark:text-violet-300',
            'ARCHIVADO' => 'border-slate-300 bg-slate-50 text-slate-800 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-300',
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
        ]);
    }

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
        return [
            'id' => $row->cod_gea,
            'codigo' => $row->cod_gea,
            'anio' => (int) $row->ani_gea,
            'nombre' => 'Gestión Académica ' . $row->ani_gea,
            'fecha_inicio' => $row->fii_gea,
            'fecha_fin' => $row->ffi_gea,
            'estado' => strtoupper($row->est_gea ?? 'SIN_ESTADO'),
            'modalidad' => 'No registrado en tabla',
            'descripcion' => 'Sin descripción registrada',
            'responsable' => 'Sin responsable registrado',
            'fecha_registro' => $row->created_at ?? null,
            'progreso' => $this->progresoFechas($row->fii_gea, $row->ffi_gea),
            'dias_transcurridos' => $this->diasTranscurridos($row->fii_gea),
            'dias_restantes' => $this->diasRestantes($row->ffi_gea),
            'estudiantes' => $this->contarInscripcionesPorGestion($row->cod_gea),
            'cursos' => $this->contarActivos('curso', 'est_cur'),
            'paralelos' => $this->contarActivos('paralelo', 'est_par'),
            'especialidades' => $this->contarActivos('especialidad_tecnica', 'est_esp'),
            'periodos' => $this->contarTabla('periodo_evaluacion'),
            'planes_asignatura' => $this->contarPorGestion('plan_asignatura', $row->cod_gea),
            'planes_especialidad' => $this->contarPorGestion('plan_especialidad', $row->cod_gea),
            'ultima_actualizacion' => $this->fechaRelativa($row->updated_at ?? $row->created_at ?? null),
        ];
    }

    private function existeGestionActiva(): bool
    {
        return Schema::hasTable('gestion_academica')
            && DB::table('gestion_academica')
            ->where('est_gea', 'ACTIVO')
            ->exists();
    }

    private function existeAnioGestion(int $anio): bool
    {
        return Schema::hasTable('gestion_academica')
            && DB::table('gestion_academica')
            ->where('ani_gea', $anio)
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

    private function crearPeriodosBaseSiNoExisten(): void
    {
        if (! Schema::hasTable('periodo_evaluacion')) {
            return;
        }

        if (DB::table('periodo_evaluacion')->exists()) {
            return;
        }

        $periodos = [
            ['Primer trimestre', 1],
            ['Segundo trimestre', 2],
            ['Tercer trimestre', 3],
        ];

        foreach ($periodos as [$nombre, $orden]) {
            DB::table('periodo_evaluacion')->insert([
                'cod_pev' => $this->generarCodigoPeriodo(),
                'nom_pev' => $nombre,
                'ord_pev' => $orden,
                'est_pev' => 'ACTIVO',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

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

    private function contarInscripcionesPorGestion(string $codGea): int
    {
        if (! Schema::hasTable('inscripcion_estudiante')) {
            return 0;
        }

        return DB::table('inscripcion_estudiante')
            ->where('cod_gea', $codGea)
            ->count();
    }

    private function contarPendientesInscripcionPorGestion(string $codGea): int
    {
        if (! Schema::hasTable('inscripcion_estudiante')) {
            return 0;
        }

        return DB::table('inscripcion_estudiante')
            ->where('cod_gea', $codGea)
            ->whereIn('est_ins', [
                'PENDIENTE',
                'OBSERVADO',
                'INACTIVO',
            ])
            ->count();
    }

    private function contarPlanesAsignaturaIncompletosPorGestion(string $codGea): int
    {
        if (! Schema::hasTable('plan_asignatura')) {
            return 0;
        }

        return DB::table('plan_asignatura')
            ->where('cod_gea', $codGea)
            ->where(function ($query) {
                $query->where('est_pas', '!=', 'ACTIVO')
                    ->orWhereNull('hor_pas')
                    ->orWhere('hor_pas', '<=', 0);
            })
            ->count();
    }

    private function contarPlanesEspecialidadIncompletosPorGestion(string $codGea): int
    {
        if (! Schema::hasTable('plan_especialidad')) {
            return 0;
        }

        return DB::table('plan_especialidad')
            ->where('cod_gea', $codGea)
            ->where(function ($query) {
                $query->where('est_pes', '!=', 'ACTIVO')
                    ->orWhere('hor_pes', '<=', 0);
            })
            ->count();
    }

    private function contarReportesPendientes(): int
    {
        if (! Schema::hasTable('reporte')) {
            return 0;
        }

        if (! Schema::hasColumn('reporte', 'est_rep')) {
            return 0;
        }

        return DB::table('reporte')
            ->whereIn('est_rep', [
                'PENDIENTE',
                'OBSERVADO',
                'NO_GENERADO',
            ])
            ->count();
    }

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
            'CERRAR_GESTION_ACADEMICA' => 'Se cerró una gestión académica.',
            'ACTUALIZAR_GESTION_ACADEMICA' => 'Se actualizó la información de una gestión académica.',
            'CREAR_PERIODO' => 'Se configuró un periodo académico.',
            'INSCRIBIR_ESTUDIANTE' => 'Se registró una inscripción académica.',
            'SIN_ACCION' => 'Sin acción registrada.',
            default => ucfirst(mb_strtolower(str_replace('_', ' ', $accion))),
        };
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
