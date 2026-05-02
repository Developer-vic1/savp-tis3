<?php

namespace App\Livewire\Admin;

use App\Models\Bitacora as BitacoraModel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class Bitacora extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'tailwind';

    /*
    |--------------------------------------------------------------------------
    | Filtros principales
    |--------------------------------------------------------------------------
    */
    public string $search = '';
    public string $fechaDesde = '';
    public string $fechaHasta = '';
    public string $filtroUsuario = '';
    public string $filtroRol = '';
    public string $filtroModulo = '';
    public string $filtroTabla = '';
    public string $filtroNivel = '';
    public string $filtroResultado = '';
    public string $filtroAccion = '';
    public string $filtroMetodo = '';
    public int $perPage = 10;

    /*
    |--------------------------------------------------------------------------
    | Tipo de vista
    |--------------------------------------------------------------------------
    */
    public string $vistaActiva = 'tabla';

    /*
    |--------------------------------------------------------------------------
    | Drawer de detalle
    |--------------------------------------------------------------------------
    */
    public bool $drawerDetalle = false;
    public ?BitacoraModel $eventoDetalle = null;

    /*
    |--------------------------------------------------------------------------
    | Catálogos visuales
    |--------------------------------------------------------------------------
    */
    public array $vistasDisponibles = [
        'tabla' => 'Tabla general',
        'modulos' => 'Por módulos',
        'usuarios' => 'Por usuarios',
        'acciones' => 'Por acciones',
        'seguridad' => 'Seguridad',
        'timeline' => 'Timeline',
    ];

    public array $nivelesDisponibles = [
        'INFO',
        'SUCCESS',
        'WARNING',
        'ERROR',
        'SECURITY',
        'CRITICAL',
    ];

    public array $resultadosDisponibles = [
        'EXITOSO',
        'FALLIDO',
        'BLOQUEADO',
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

    public function updatingFechaDesde(): void
    {
        $this->resetPage();
    }

    public function updatingFechaHasta(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroUsuario(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroRol(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroModulo(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroTabla(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroNivel(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroResultado(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroAccion(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroMetodo(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    /*
    |--------------------------------------------------------------------------
    | Acciones generales
    |--------------------------------------------------------------------------
    */
    public function cambiarVista(string $vista): void
    {
        if (! array_key_exists($vista, $this->vistasDisponibles)) {
            return;
        }

        $this->vistaActiva = $vista;
        $this->resetPage();

        $this->dispatch('actualizar-graficos-bitacora', data: $this->datosGraficos());
    }

    public function limpiarFiltros(): void
    {
        $this->reset([
            'search',
            'fechaDesde',
            'fechaHasta',
            'filtroUsuario',
            'filtroRol',
            'filtroModulo',
            'filtroTabla',
            'filtroNivel',
            'filtroResultado',
            'filtroAccion',
            'filtroMetodo',
        ]);

        $this->resetPage();

        $this->dispatch('actualizar-graficos-bitacora', data: $this->datosGraficos());
    }

    public function actualizarVista(): void
    {
        $this->resetPage();

        $this->dispatch('actualizar-graficos-bitacora', data: $this->datosGraficos());

        $this->dispatch('success-general', mensaje: 'Bitácora actualizada correctamente.');
    }

    /*
    |--------------------------------------------------------------------------
    | Query base
    |--------------------------------------------------------------------------
    */
    private function queryBase(bool $conRelaciones = true): Builder
    {
        $query = BitacoraModel::query();

        if ($conRelaciones) {
            $query->with([
                'usuario.persona',
                'usuario.roles',
            ]);
        }

        return $this->aplicarFiltros($query);
    }

    private function aplicarFiltros(Builder $query): Builder
    {
        return $query
            ->when(trim($this->search) !== '', function (Builder $query) {
                $search = trim($this->search);

                $query->where(function (Builder $q) use ($search) {
                    $q->where('cod_bit', 'ILIKE', "%{$search}%")
                        ->orWhere('acc_bit', 'ILIKE', "%{$search}%")
                        ->orWhere('mod_bit', 'ILIKE', "%{$search}%")
                        ->orWhere('tab_bit', 'ILIKE', "%{$search}%")
                        ->orWhere('reg_bit', 'ILIKE', "%{$search}%")
                        ->orWhere('nom_reg_bit', 'ILIKE', "%{$search}%")
                        ->orWhere('des_bit', 'ILIKE', "%{$search}%")
                        ->orWhere('rol_bit', 'ILIKE', "%{$search}%")
                        ->orWhere('niv_bit', 'ILIKE', "%{$search}%")
                        ->orWhere('res_bit', 'ILIKE', "%{$search}%")
                        ->orWhere('ip_bit', 'ILIKE', "%{$search}%")
                        ->orWhere('rut_bit', 'ILIKE', "%{$search}%")
                        ->orWhere('met_bit', 'ILIKE', "%{$search}%")
                        ->orWhereHas('usuario', function (Builder $sub) use ($search) {
                            $sub->where('email', 'ILIKE', "%{$search}%")
                                ->orWhere('cod_usu', 'ILIKE', "%{$search}%")
                                ->orWhereHas('persona', function (Builder $persona) use ($search) {
                                    $persona->where('nom_per', 'ILIKE', "%{$search}%")
                                        ->orWhere('ape_pat_per', 'ILIKE', "%{$search}%")
                                        ->orWhere('ape_mat_per', 'ILIKE', "%{$search}%")
                                        ->orWhere('ci_per', 'ILIKE', "%{$search}%")
                                        ->orWhereRaw(
                                            "CONCAT(nom_per, ' ', ape_pat_per, ' ', COALESCE(ape_mat_per, '')) ILIKE ?",
                                            ["%{$search}%"]
                                        );
                                });
                        });
                });
            })
            ->when($this->fechaDesde !== '', function (Builder $query) {
                $query->whereDate('fec_bit', '>=', $this->fechaDesde);
            })
            ->when($this->fechaHasta !== '', function (Builder $query) {
                $query->whereDate('fec_bit', '<=', $this->fechaHasta);
            })
            ->when($this->filtroUsuario !== '', function (Builder $query) {
                if ($this->filtroUsuario === 'SISTEMA') {
                    $query->whereNull('cod_usu');
                } else {
                    $query->where('cod_usu', $this->filtroUsuario);
                }
            })
            ->when($this->filtroRol !== '', function (Builder $query) {
                $query->where('rol_bit', $this->filtroRol);
            })
            ->when($this->filtroModulo !== '', function (Builder $query) {
                $query->where('mod_bit', $this->filtroModulo);
            })
            ->when($this->filtroTabla !== '', function (Builder $query) {
                $query->where('tab_bit', $this->filtroTabla);
            })
            ->when($this->filtroNivel !== '', function (Builder $query) {
                $query->where('niv_bit', $this->filtroNivel);
            })
            ->when($this->filtroResultado !== '', function (Builder $query) {
                $query->where('res_bit', $this->filtroResultado);
            })
            ->when($this->filtroAccion !== '', function (Builder $query) {
                $query->where('acc_bit', $this->filtroAccion);
            })
            ->when($this->filtroMetodo !== '', function (Builder $query) {
                $query->where('met_bit', $this->filtroMetodo);
            })
            ->when($this->vistaActiva === 'seguridad', function (Builder $query) {
                $this->aplicarFiltroSeguridad($query);
            });
    }

    private function aplicarFiltroSeguridad(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->whereIn('niv_bit', ['WARNING', 'ERROR', 'SECURITY', 'CRITICAL'])
                ->orWhereIn('res_bit', ['FALLIDO', 'BLOQUEADO'])
                ->orWhere('acc_bit', 'ILIKE', '%DESACTIVAR%')
                ->orWhere('acc_bit', 'ILIKE', '%REACTIVAR%')
                ->orWhere('acc_bit', 'ILIKE', '%PASSWORD%')
                ->orWhere('acc_bit', 'ILIKE', '%ROL%')
                ->orWhere('acc_bit', 'ILIKE', '%ERROR%')
                ->orWhere('acc_bit', 'ILIKE', '%SINCRONIZAR%')
                ->orWhere('acc_bit', 'ILIKE', '%CAMBIAR%')
                ->orWhere('acc_bit', 'ILIKE', '%LOTE%');
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Acciones desde cards
    |--------------------------------------------------------------------------
    */
    public function filtrarModulo(?string $modulo): void
    {
        $this->filtroModulo = $modulo ?? '';
        $this->vistaActiva = 'tabla';
        $this->resetPage();

        $this->dispatch('actualizar-graficos-bitacora', data: $this->datosGraficos());
    }

    public function filtrarUsuario(?string $codUsu): void
    {
        $this->filtroUsuario = $codUsu ?: 'SISTEMA';
        $this->vistaActiva = 'tabla';
        $this->resetPage();

        $this->dispatch('actualizar-graficos-bitacora', data: $this->datosGraficos());
    }

    public function filtrarAccion(string $accion): void
    {
        $this->filtroAccion = $accion;
        $this->vistaActiva = 'tabla';
        $this->resetPage();

        $this->dispatch('actualizar-graficos-bitacora', data: $this->datosGraficos());
    }

    public function filtrarNivel(string $nivel): void
    {
        $this->filtroNivel = $nivel;
        $this->vistaActiva = 'tabla';
        $this->resetPage();

        $this->dispatch('actualizar-graficos-bitacora', data: $this->datosGraficos());
    }

    public function filtrarResultado(string $resultado): void
    {
        $this->filtroResultado = $resultado;
        $this->vistaActiva = 'tabla';
        $this->resetPage();

        $this->dispatch('actualizar-graficos-bitacora', data: $this->datosGraficos());
    }

    /*
    |--------------------------------------------------------------------------
    | Drawer de detalle
    |--------------------------------------------------------------------------
    */
    public function abrirDetalle(string $codBit): void
    {
        $this->eventoDetalle = BitacoraModel::query()
            ->with([
                'usuario.persona',
                'usuario.roles',
            ])
            ->where('cod_bit', $codBit)
            ->first();

        if (! $this->eventoDetalle) {
            $this->dispatch('error-general', mensaje: 'No se encontró el evento seleccionado.');
            return;
        }

        $this->drawerDetalle = true;
    }

    public function cerrarDetalle(): void
    {
        $this->drawerDetalle = false;
        $this->eventoDetalle = null;
    }

    /*
    |--------------------------------------------------------------------------
    | Cards resumen
    |--------------------------------------------------------------------------
    */
    public function totalEventos(): int
    {
        return BitacoraModel::count();
    }

    public function eventosHoy(): int
    {
        return BitacoraModel::whereDate('fec_bit', today())->count();
    }

    public function eventosExitosos(): int
    {
        return BitacoraModel::where('res_bit', 'EXITOSO')->count();
    }

    public function eventosAdvertencia(): int
    {
        return BitacoraModel::where('niv_bit', 'WARNING')->count();
    }

    public function eventosFallidos(): int
    {
        return BitacoraModel::query()
            ->where(function (Builder $query) {
                $query->whereIn('niv_bit', ['ERROR', 'CRITICAL'])
                    ->orWhereIn('res_bit', ['FALLIDO', 'BLOQUEADO']);
            })
            ->count();
    }

    public function usuariosConActividad(): int
    {
        return BitacoraModel::query()
            ->whereNotNull('cod_usu')
            ->distinct('cod_usu')
            ->count('cod_usu');
    }

    public function porcentajeExito(): float
    {
        $total = max($this->totalEventos(), 1);

        return round(($this->eventosExitosos() / $total) * 100, 1);
    }

    public function eventosSensibles(): int
    {
        return BitacoraModel::query()
            ->where(function (Builder $query) {
                $this->aplicarFiltroSeguridad($query);
            })
            ->count();
    }

    public function promedioDiarioEventos(): int
    {
        $desde = now()->subDays(30)->startOfDay();

        $total = BitacoraModel::where('fec_bit', '>=', $desde)->count();

        return (int) ceil($total / 30);
    }

    public function ultimoEvento(): ?BitacoraModel
    {
        return BitacoraModel::query()
            ->with(['usuario.persona', 'usuario.roles'])
            ->orderByDesc('fec_bit')
            ->first();
    }

    /*
    |--------------------------------------------------------------------------
    | Agrupaciones
    |--------------------------------------------------------------------------
    */
    public function gruposModulo(): Collection
    {
        return $this->queryBase(false)
            ->selectRaw("
                COALESCE(mod_bit, 'Sistema') as modulo,
                COUNT(*) as total,
                SUM(CASE WHEN res_bit = 'EXITOSO' THEN 1 ELSE 0 END) as exitosos,
                SUM(CASE WHEN niv_bit = 'WARNING' THEN 1 ELSE 0 END) as advertencias,
                SUM(CASE WHEN niv_bit IN ('ERROR', 'CRITICAL') OR res_bit IN ('FALLIDO', 'BLOQUEADO') THEN 1 ELSE 0 END) as errores,
                MAX(fec_bit) as ultima_actividad
            ")
            ->groupByRaw("COALESCE(mod_bit, 'Sistema')")
            ->orderByDesc('total')
            ->get();
    }

    public function gruposUsuario(): Collection
    {
        $grupos = $this->queryBase(false)
            ->selectRaw("
                COALESCE(cod_usu, 'SISTEMA') as usuario_key,
                COALESCE(rol_bit, 'Automático') as rol_evento,
                COUNT(*) as total,
                SUM(CASE WHEN res_bit = 'EXITOSO' THEN 1 ELSE 0 END) as exitosos,
                SUM(CASE WHEN niv_bit = 'WARNING' THEN 1 ELSE 0 END) as advertencias,
                SUM(CASE WHEN niv_bit IN ('ERROR', 'CRITICAL') OR res_bit IN ('FALLIDO', 'BLOQUEADO') THEN 1 ELSE 0 END) as errores,
                MAX(fec_bit) as ultima_actividad
            ")
            ->groupByRaw("COALESCE(cod_usu, 'SISTEMA'), COALESCE(rol_bit, 'Automático')")
            ->orderByDesc('total')
            ->get();

        $usuarios = User::query()
            ->with(['persona', 'roles'])
            ->whereIn(
                'cod_usu',
                $grupos->pluck('usuario_key')
                    ->filter(fn($id) => $id !== 'SISTEMA')
                    ->values()
                    ->toArray()
            )
            ->get()
            ->keyBy('cod_usu');

        return $grupos->map(function ($grupo) use ($usuarios) {
            $usuario = $usuarios->get($grupo->usuario_key);

            $grupo->usuario_modelo = $usuario;
            $grupo->nombre_visible = $usuario
                ? $this->nombreUsuarioDesdeModelo($usuario)
                : 'Sistema';

            $grupo->correo_visible = $usuario?->email ?? 'Sin correo asociado';
            $grupo->rol_visible = $usuario?->roles?->first()?->name ?? $grupo->rol_evento ?? 'Automático';

            return $grupo;
        });
    }

    public function gruposAccion(): Collection
    {
        $acciones = $this->queryBase(false)
            ->selectRaw("
                acc_bit,
                COUNT(*) as total,
                MAX(fec_bit) as ultima_actividad
            ")
            ->whereNotNull('acc_bit')
            ->groupBy('acc_bit')
            ->orderByDesc('total')
            ->get();

        $resultadosPorAccion = BitacoraModel::query()
            ->selectRaw("acc_bit, res_bit, COUNT(*) as total")
            ->whereNotNull('acc_bit')
            ->whereNotNull('res_bit')
            ->groupBy('acc_bit', 'res_bit')
            ->get()
            ->groupBy('acc_bit');

        return $acciones->map(function ($grupo) use ($resultadosPorAccion) {
            $resultados = $resultadosPorAccion->get($grupo->acc_bit, collect());

            $resultadoPredominante = $resultados
                ->sortByDesc('total')
                ->first()?->res_bit;

            $grupo->titulo_humano = $this->accionInstitucional($grupo->acc_bit);
            $grupo->resultado_predominante = $resultadoPredominante ?? 'EXITOSO';

            $grupo->modulos = BitacoraModel::query()
                ->where('acc_bit', $grupo->acc_bit)
                ->whereNotNull('mod_bit')
                ->distinct()
                ->pluck('mod_bit')
                ->take(4)
                ->values()
                ->toArray();

            return $grupo;
        });
    }

    public function eventosTimeline(): Collection
    {
        return $this->queryBase()
            ->orderByDesc('fec_bit')
            ->limit(60)
            ->get()
            ->groupBy(function (BitacoraModel $evento) {
                if (! $evento->fec_bit) {
                    return 'Sin fecha';
                }

                if ($evento->fec_bit->isToday()) {
                    return 'Hoy';
                }

                if ($evento->fec_bit->isYesterday()) {
                    return 'Ayer';
                }

                if ($evento->fec_bit->greaterThanOrEqualTo(now()->startOfWeek())) {
                    return 'Esta semana';
                }

                return $evento->fec_bit->translatedFormat('d F Y');
            });
    }

    /*
    |--------------------------------------------------------------------------
    | Gráficos
    |--------------------------------------------------------------------------
    */
    public function datosGraficos(): array
    {
        return [
            'eventosPorModulo' => $this->chartEventosPorModulo(),
            'distribucionNivel' => $this->chartDistribucionNivel(),
            'actividadReciente' => $this->chartActividadReciente(),
            'usuariosMasActivos' => $this->chartUsuariosMasActivos(),
            'miniIndicadores' => $this->miniIndicadores(),
        ];
    }

    private function chartEventosPorModulo(): array
    {
        $datos = $this->queryBase(false)
            ->selectRaw("COALESCE(mod_bit, 'Sistema') as modulo, COUNT(*) as total")
            ->groupByRaw("COALESCE(mod_bit, 'Sistema')")
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        return [
            'labels' => $datos->pluck('modulo')->toArray(),
            'data' => $datos->pluck('total')->map(fn($valor) => (int) $valor)->toArray(),
        ];
    }

    private function chartDistribucionNivel(): array
    {
        $datos = $this->queryBase(false)
            ->selectRaw("COALESCE(niv_bit, 'INFO') as nivel, COUNT(*) as total")
            ->groupByRaw("COALESCE(niv_bit, 'INFO')")
            ->get()
            ->keyBy('nivel');

        $orden = collect(['INFO', 'SUCCESS', 'WARNING', 'ERROR', 'SECURITY', 'CRITICAL']);

        return [
            'labels' => $orden
                ->filter(fn($nivel) => isset($datos[$nivel]))
                ->values()
                ->toArray(),

            'data' => $orden
                ->filter(fn($nivel) => isset($datos[$nivel]))
                ->map(fn($nivel) => (int) $datos[$nivel]->total)
                ->values()
                ->toArray(),
        ];
    }

    private function chartActividadReciente(): array
    {
        $inicio = now()->subDays(6)->startOfDay();
        $fin = now()->endOfDay();

        $datos = $this->queryBase(false)
            ->selectRaw("DATE(fec_bit) as fecha, COUNT(*) as total")
            ->whereBetween('fec_bit', [$inicio, $fin])
            ->groupByRaw("DATE(fec_bit)")
            ->orderBy('fecha')
            ->get()
            ->keyBy(fn($item) => Carbon::parse($item->fecha)->format('Y-m-d'));

        $labels = [];
        $data = [];

        for ($i = 6; $i >= 0; $i--) {
            $fecha = now()->subDays($i);
            $key = $fecha->format('Y-m-d');

            $labels[] = $fecha->translatedFormat('d M');
            $data[] = (int) ($datos->get($key)->total ?? 0);
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    private function chartUsuariosMasActivos(): array
    {
        $datos = $this->queryBase(false)
            ->selectRaw("COALESCE(cod_usu, 'SISTEMA') as usuario_key, COUNT(*) as total")
            ->groupByRaw("COALESCE(cod_usu, 'SISTEMA')")
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $usuarios = User::query()
            ->with('persona')
            ->whereIn(
                'cod_usu',
                $datos->pluck('usuario_key')
                    ->filter(fn($id) => $id !== 'SISTEMA')
                    ->values()
                    ->toArray()
            )
            ->get()
            ->keyBy('cod_usu');

        return [
            'labels' => $datos->map(function ($item) use ($usuarios) {
                if ($item->usuario_key === 'SISTEMA') {
                    return 'Sistema';
                }

                $usuario = $usuarios->get($item->usuario_key);

                return $usuario
                    ? $this->nombreUsuarioDesdeModelo($usuario)
                    : $item->usuario_key;
            })->toArray(),

            'data' => $datos->pluck('total')->map(fn($valor) => (int) $valor)->toArray(),
        ];
    }

    private function miniIndicadores(): array
    {
        $moduloMasActivo = $this->gruposModulo()->first();
        $usuarioMasActivo = $this->gruposUsuario()->first();
        $ultimoEvento = $this->ultimoEvento();

        return [
            'moduloMasActivo' => [
                'titulo' => 'Módulo con más actividad',
                'valor' => $moduloMasActivo?->modulo ?? 'Sin registros',
            ],
            'usuarioMasActivo' => [
                'titulo' => 'Usuario con más acciones',
                'valor' => $usuarioMasActivo?->nombre_visible ?? 'Sin registros',
            ],
            'ultimoEvento' => [
                'titulo' => 'Último evento registrado',
                'valor' => $ultimoEvento?->fec_bit?->diffForHumans() ?? 'Sin actividad',
            ],
            'porcentajeExito' => [
                'titulo' => 'Porcentaje de éxito',
                'valor' => $this->porcentajeExito() . '%',
            ],
            'eventosSensibles' => [
                'titulo' => 'Eventos sensibles',
                'valor' => $this->eventosSensibles(),
            ],
            'promedioDiario' => [
                'titulo' => 'Promedio diario',
                'valor' => $this->promedioDiarioEventos() . ' eventos',
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Opciones para filtros
    |--------------------------------------------------------------------------
    */
    private function opcionesUsuarios(): Collection
    {
        $usuariosConBitacora = BitacoraModel::query()
            ->whereNotNull('cod_usu')
            ->distinct()
            ->pluck('cod_usu')
            ->toArray();

        return User::query()
            ->with(['persona', 'roles'])
            ->whereIn('cod_usu', $usuariosConBitacora)
            ->orderBy('email')
            ->get();
    }

    private function opcionesRoles(): Collection
    {
        return BitacoraModel::query()
            ->whereNotNull('rol_bit')
            ->distinct()
            ->orderBy('rol_bit')
            ->pluck('rol_bit');
    }

    private function opcionesModulos(): Collection
    {
        return BitacoraModel::query()
            ->whereNotNull('mod_bit')
            ->distinct()
            ->orderBy('mod_bit')
            ->pluck('mod_bit');
    }

    private function opcionesTablas(): Collection
    {
        return BitacoraModel::query()
            ->whereNotNull('tab_bit')
            ->distinct()
            ->orderBy('tab_bit')
            ->pluck('tab_bit');
    }

    private function opcionesAcciones(): Collection
    {
        return BitacoraModel::query()
            ->whereNotNull('acc_bit')
            ->distinct()
            ->orderBy('acc_bit')
            ->pluck('acc_bit');
    }

    private function opcionesMetodos(): Collection
    {
        return BitacoraModel::query()
            ->whereNotNull('met_bit')
            ->distinct()
            ->orderBy('met_bit')
            ->pluck('met_bit');
    }

    /*
    |--------------------------------------------------------------------------
    | Humanización institucional
    |--------------------------------------------------------------------------
    */
    public function tituloEvento(?BitacoraModel $evento): string
    {
        if (! $evento) {
            return 'Actividad institucional registrada';
        }

        return $this->accionInstitucional($evento->acc_bit);
    }

    public function accionInstitucional(?string $accion): string
    {
        return match ($accion) {
            /*
            |--------------------------------------------------------------------------
            | Usuarios
            |--------------------------------------------------------------------------
            */
            'CREAR_USUARIO' => 'Registro de cuenta institucional',
            'ACTUALIZAR_USUARIO' => 'Actualización de cuenta institucional',
            'DESACTIVAR_USUARIO' => 'Desactivación de cuenta institucional',
            'REACTIVAR_USUARIO' => 'Reactivación de cuenta institucional',
            'ACTIVAR_USUARIOS_LOTE' => 'Reactivación masiva de cuentas',
            'DESACTIVAR_USUARIOS_LOTE' => 'Desactivación masiva de cuentas',
            'SINCRONIZAR_DATOS_USUARIOS' => 'Sincronización de usuarios ejecutada',
            'SINCRONIZAR_PERFILES_USUARIO' => 'Sincronización de perfiles institucionales',
            'REVISAR_SINCRONIZACION_USUARIOS' => 'Revisión de sincronización institucional',
            'ERROR_SINCRONIZAR_DATOS_USUARIOS' => 'Error durante sincronización institucional',
            'ERROR_CREAR_USUARIO' => 'Error al registrar cuenta institucional',
            'ERROR_ACTUALIZAR_USUARIO' => 'Error al actualizar cuenta institucional',

            /*
            |--------------------------------------------------------------------------
            | Personas
            |--------------------------------------------------------------------------
            */
            'CREAR_PERSONA' => 'Registro de persona',
            'ACTUALIZAR_PERSONA' => 'Actualización de datos personales',
            'DESACTIVAR_PERSONA' => 'Desactivación de persona',
            'REACTIVAR_PERSONA' => 'Reactivación de persona',
            'ELIMINAR_FOTO_PERSONA' => 'Eliminación de fotografía personal',

            /*
            |--------------------------------------------------------------------------
            | Estudiantes
            |--------------------------------------------------------------------------
            */
            'REGISTRAR_ESTUDIANTE' => 'Registro académico de estudiante',
            'EDITAR_ESTUDIANTE' => 'Actualización académica de estudiante',
            'DESACTIVAR_ESTUDIANTE' => 'Desactivación de estudiante',
            'REACTIVAR_ESTUDIANTE' => 'Reactivación de estudiante',
            'MARCAR_ESTUDIANTE_RETIRADO' => 'Cambio de estado a retirado',
            'MARCAR_ESTUDIANTE_OBSERVADO' => 'Cambio de estado a observado',
            'MARCAR_ESTUDIANTE_EGRESADO' => 'Cambio de estado a egresado',
            'MARCAR_ESTUDIANTE_TRASLADADO' => 'Cambio de estado a trasladado',

            /*
            |--------------------------------------------------------------------------
            | Inscripciones
            |--------------------------------------------------------------------------
            */
            'INSCRIBIR_ESTUDIANTE' => 'Inscripción académica registrada',
            'ACTUALIZAR_INSCRIPCION_ESTUDIANTE' => 'Actualización de inscripción académica',

            /*
            |--------------------------------------------------------------------------
            | Personal institucional
            |--------------------------------------------------------------------------
            */
            'ASIGNAR_MATERIA_MANANA' => 'Asignación de materia curricular',
            'ASIGNAR_ESPECIALIDAD_TARDE' => 'Asignación de especialidad técnica',
            'EDITAR_DOCENTE' => 'Actualización de información docente',
            'DESACTIVAR_DOCENTE' => 'Desactivación de docente',
            'REACTIVAR_DOCENTE' => 'Reactivación de docente',

            default => $this->formatearAccion($accion),
        };
    }

    public function tablaInstitucional(?string $tabla): string
    {
        return match ($tabla) {
            'users' => 'Usuarios del sistema',
            'persona' => 'Personas registradas',
            'estudiante' => 'Estudiantes',
            'inscripcion_estudiante' => 'Inscripciones académicas',
            'docente' => 'Docentes',
            'personal_institucional' => 'Personal institucional',
            'plan_asignatura' => 'Planificación de materias',
            'plan_especialidad' => 'Planificación de especialidades',
            'bitacora' => 'Bitácora institucional',
            'roles' => 'Roles del sistema',
            'permissions' => 'Permisos del sistema',
            'model_has_roles' => 'Asignación de roles',
            'model_has_permissions' => 'Asignación de permisos',
            default => $tabla ?: 'No identificado',
        };
    }

    public function registroInstitucional(?BitacoraModel $evento): string
    {
        if (! $evento) {
            return 'No identificado';
        }

        if (! empty($evento->nom_reg_bit)) {
            return $evento->nom_reg_bit;
        }

        return match ($evento->reg_bit) {
            'SINCRONIZACION' => 'Proceso de sincronización',
            'SINCRONIZACION_USUARIOS' => 'Proceso de sincronización de usuarios',
            'SINCRONIZACION_PERFILES' => 'Proceso de sincronización de perfiles',
            'LOTE' => 'Acción masiva',
            default => $evento->reg_bit ?: 'Registro no identificado',
        };
    }

    public function descripcionEvento(?BitacoraModel $evento): string
    {
        if (! $evento) {
            return 'No se encontró descripción para este evento.';
        }

        if (! empty($evento->des_bit)) {
            return $evento->des_bit;
        }

        $usuario = $this->nombreUsuario($evento);
        $accion = mb_strtolower($this->accionInstitucional($evento->acc_bit));
        $modulo = $evento->mod_bit ?: $this->moduloDesdeTabla($evento->tab_bit);
        $registro = $this->registroInstitucional($evento);

        return "{$usuario} ejecutó {$accion} en {$modulo}, afectando {$registro}.";
    }

    public function resumenInstitucionalEvento(?BitacoraModel $evento): string
    {
        if (! $evento) {
            return 'Evento no disponible.';
        }

        $usuario = $this->nombreUsuario($evento);
        $accion = mb_strtolower($this->accionInstitucional($evento->acc_bit));
        $registro = $this->registroInstitucional($evento);
        $resultado = mb_strtolower($evento->res_bit ?? 'registrado');

        return "{$usuario} realizó {$accion} sobre {$registro}. Resultado: {$resultado}.";
    }

    public function traducirAccion(?string $accion): string
    {
        return $this->accionInstitucional($accion);
    }

    public function formatearAccion(?string $accion): string
    {
        if (! $accion) {
            return 'Actividad institucional registrada';
        }

        return Str::of($accion)
            ->replace('_', ' ')
            ->lower()
            ->ucfirst()
            ->toString();
    }

    /*
    |--------------------------------------------------------------------------
    | Usuarios
    |--------------------------------------------------------------------------
    */
    public function nombreUsuario(?BitacoraModel $evento): string
    {
        if (! $evento || ! $evento->usuario) {
            return 'Sistema';
        }

        return $this->nombreUsuarioDesdeModelo($evento->usuario);
    }

    public function nombreUsuarioDesdeModelo(?User $usuario): string
    {
        if (! $usuario) {
            return 'Sistema';
        }

        $persona = $usuario->persona;

        if ($persona) {
            $nombre = trim(collect([
                $persona->nom_per,
                $persona->ape_pat_per,
                $persona->ape_mat_per,
            ])->filter()->implode(' '));

            if ($nombre !== '') {
                return $nombre;
            }
        }

        return $usuario->email ?? $usuario->cod_usu ?? 'Usuario no identificado';
    }

    public function correoUsuario(?BitacoraModel $evento): string
    {
        return $evento?->usuario?->email ?? 'Sin correo asociado';
    }

    public function rolUsuario(?BitacoraModel $evento): string
    {
        if (! $evento) {
            return 'Sin rol';
        }

        return $evento->rol_bit
            ?: $evento->usuario?->roles?->first()?->name
            ?: 'Automático';
    }

    public function inicialesUsuario(?BitacoraModel $evento): string
    {
        $nombre = $this->nombreUsuario($evento);

        if ($nombre === 'Sistema') {
            return 'S';
        }

        $partes = collect(explode(' ', $nombre))
            ->filter()
            ->values();

        $primera = mb_substr($partes->get(0, 'U'), 0, 1);
        $segunda = mb_substr($partes->get(1, 'S'), 0, 1);

        return mb_strtoupper($primera . $segunda);
    }

    /*
    |--------------------------------------------------------------------------
    | Módulos y tablas
    |--------------------------------------------------------------------------
    */
    public function moduloVisible(?BitacoraModel $evento): string
    {
        if (! $evento) {
            return 'Sistema';
        }

        return $evento->mod_bit ?: $this->moduloDesdeTabla($evento->tab_bit);
    }

    public function moduloDesdeTabla(?string $tabla): string
    {
        return match ($tabla) {
            'users',
            'roles',
            'permissions',
            'model_has_roles',
            'model_has_permissions' => 'Gestión de Usuarios',

            'persona' => 'Gestión de Personas',

            'estudiante' => 'Gestión de Estudiantes',

            'inscripcion_estudiante' => 'Inscripciones Académicas',

            'docente',
            'personal_institucional',
            'plan_asignatura',
            'plan_especialidad' => 'Gestión de Personal Institucional',

            'bitacora' => 'Bitácora Institucional',

            default => 'Sistema',
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Badges
    |--------------------------------------------------------------------------
    */
    public function badgeNivel(?string $nivel): string
    {
        return match ($nivel) {
            'SUCCESS' => 'ui-badge-success',
            'WARNING' => 'ui-badge-warning',
            'ERROR' => 'ui-badge-danger',
            'SECURITY' => 'ui-badge-violet',
            'CRITICAL' => 'ui-badge-danger',
            'INFO' => 'ui-badge-info',
            default => 'ui-badge-muted',
        };
    }

    public function badgeResultado(?string $resultado): string
    {
        return match ($resultado) {
            'EXITOSO' => 'ui-badge-success',
            'FALLIDO' => 'ui-badge-danger',
            'BLOQUEADO' => 'ui-badge-warning',
            default => 'ui-badge-muted',
        };
    }

    public function badgeModulo(?string $modulo): string
    {
        $modulo = mb_strtolower((string) $modulo);

        return match (true) {
            str_contains($modulo, 'usuario') => 'ui-badge-violet',
            str_contains($modulo, 'persona') => 'ui-badge-info',
            str_contains($modulo, 'estudiante') => 'ui-badge-success',
            str_contains($modulo, 'inscripcion') => 'ui-badge-success',
            str_contains($modulo, 'personal') => 'ui-badge-warning',
            str_contains($modulo, 'docente') => 'ui-badge-warning',
            str_contains($modulo, 'seguridad') => 'ui-badge-danger',
            default => 'ui-badge-muted',
        };
    }

    public function colorPuntoNivel(?string $nivel): string
    {
        return match ($nivel) {
            'SUCCESS' => 'bg-emerald-500',
            'WARNING' => 'bg-amber-500',
            'ERROR' => 'bg-rose-500',
            'SECURITY' => 'bg-violet-500',
            'CRITICAL' => 'bg-red-600',
            'INFO' => 'bg-sky-500',
            default => 'bg-slate-400',
        };
    }

    public function etiquetaResultado(?string $resultado): string
    {
        return match ($resultado) {
            'EXITOSO' => 'Operación exitosa',
            'FALLIDO' => 'Operación fallida',
            'BLOQUEADO' => 'Operación bloqueada',
            default => $resultado ?: 'Sin resultado',
        };
    }

    public function etiquetaNivel(?string $nivel): string
    {
        return match ($nivel) {
            'INFO' => 'Informativo',
            'SUCCESS' => 'Correcto',
            'WARNING' => 'Advertencia',
            'ERROR' => 'Error',
            'SECURITY' => 'Seguridad',
            'CRITICAL' => 'Crítico',
            default => $nivel ?: 'Sin nivel',
        };
    }

    /*
    |--------------------------------------------------------------------------
    | JSON / valores antes y después
    |--------------------------------------------------------------------------
    */
    public function cambiosRegistrados(?BitacoraModel $evento = null): array
    {
        $evento = $evento ?? $this->eventoDetalle;

        if (! $evento) {
            return [];
        }

        $anteriores = $this->normalizarJson($evento->val_ant_bit);
        $nuevos = $this->normalizarJson($evento->val_nue_bit);

        /*
        |--------------------------------------------------------------------------
        | Si no existen valores anteriores, no forzamos comparación.
        | En ese caso la vista debe mostrar "Datos registrados".
        |--------------------------------------------------------------------------
        */
        if (empty($anteriores) || empty($nuevos)) {
            return [];
        }

        $campos = collect(array_keys($anteriores))
            ->merge(array_keys($nuevos))
            ->unique()
            ->reject(fn($campo) => $this->campoSensible((string) $campo))
            ->values();

        return $campos->map(function ($campo) use ($anteriores, $nuevos) {
            $antes = $anteriores[$campo] ?? null;
            $despues = $nuevos[$campo] ?? null;

            $antesFormateado = $this->formatearValorJson($antes);
            $despuesFormateado = $this->formatearValorJson($despues);

            return [
                'campo' => $this->campoInstitucional((string) $campo),
                'campo_original' => $campo,
                'antes' => $antesFormateado,
                'despues' => $despuesFormateado,
                'cambio' => $antesFormateado !== $despuesFormateado,
            ];
        })->toArray();
    }

    public function datosRegistrados(?BitacoraModel $evento = null): array
    {
        $evento = $evento ?? $this->eventoDetalle;

        if (! $evento) {
            return [];
        }

        $nuevos = $this->normalizarJson($evento->val_nue_bit);

        return collect($nuevos)
            ->reject(fn($valor, $campo) => $this->campoSensible((string) $campo))
            ->map(fn($valor, $campo) => [
                'campo' => $this->campoInstitucional((string) $campo),
                'campo_original' => $campo,
                'valor' => $this->formatearValorJson($valor),
            ])
            ->values()
            ->toArray();
    }

    private function normalizarJson(mixed $valor): array
    {
        if (is_array($valor)) {
            return $valor;
        }

        if (is_object($valor)) {
            return json_decode(json_encode($valor), true) ?: [];
        }

        if (is_string($valor) && trim($valor) !== '') {
            return json_decode($valor, true) ?: [];
        }

        return [];
    }

    private function campoSensible(string $campo): bool
    {
        $campo = mb_strtolower($campo);

        return str_contains($campo, 'password')
            || str_contains($campo, 'token')
            || str_contains($campo, 'secret')
            || str_contains($campo, 'remember')
            || str_contains($campo, 'two_factor')
            || str_contains($campo, 'recovery')
            || str_contains($campo, 'current_team_id');
    }

    public function campoInstitucional(string $campo): string
    {
        return match ($campo) {
            'cod_usu' => 'Código de usuario',
            'cod_per' => 'Código de persona',
            'cod_est' => 'Código de estudiante',
            'cod_doc' => 'Código de docente',
            'cod_pin' => 'Código de personal institucional',
            'email' => 'Correo electrónico',
            'rol' => 'Rol asignado',
            'role' => 'Rol asignado',
            'est_usu' => 'Estado de usuario',
            'est_per' => 'Estado de persona',
            'est_est' => 'Estado de estudiante',
            'est_doc' => 'Estado de docente',
            'nombre' => 'Nombre completo',
            'nombre_docente' => 'Nombre del docente',
            'rud_est' => 'RUD/RUDE',
            'tipo_vinculacion' => 'Tipo de vinculación',
            'institucion_procedencia' => 'Institución de procedencia',
            'especialidad' => 'Especialidad técnica',
            'curso' => 'Curso',
            'curso_actual' => 'Curso actual',
            'paralelo' => 'Paralelo',
            'paralelo_actual' => 'Paralelo actual',
            'gestion' => 'Gestión académica',
            'gestion_actual' => 'Gestión actual',
            'estado' => 'Estado',
            'horas' => 'Horas asignadas',
            'total_sincronizados' => 'Perfiles sincronizados',
            'detalle' => 'Detalle de sincronización',
            'password_cambiada' => 'Contraseña modificada',
            'accion_lote' => 'Acción masiva',
            'afectados' => 'Registros afectados',
            'omitidos' => 'Registros omitidos',
            'total_afectados' => 'Total afectados',
            'total_omitidos' => 'Total omitidos',
            default => Str::of($campo)
                ->replace('_', ' ')
                ->lower()
                ->ucfirst()
                ->toString(),
        };
    }

    private function formatearValorJson(mixed $valor): string
    {
        if (is_null($valor)) {
            return 'Sin dato';
        }

        if (is_bool($valor)) {
            return $valor ? 'Sí' : 'No';
        }

        if (is_array($valor)) {
            if (empty($valor)) {
                return 'Vacío';
            }

            return json_encode($valor, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        if (is_object($valor)) {
            return json_encode($valor, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        $valor = trim((string) $valor);

        return $valor !== '' ? $valor : 'Sin dato';
    }

    /*
    |--------------------------------------------------------------------------
    | Fechas
    |--------------------------------------------------------------------------
    */
    public function fechaCompleta(?BitacoraModel $evento): string
    {
        if (! $evento?->fec_bit) {
            return 'Sin fecha';
        }

        return $evento->fec_bit->format('d/m/Y H:i');
    }

    public function fechaRelativa(?BitacoraModel $evento): string
    {
        if (! $evento?->fec_bit) {
            return 'Sin referencia temporal';
        }

        return $evento->fec_bit->diffForHumans();
    }

    /*
    |--------------------------------------------------------------------------
    | Render
    |--------------------------------------------------------------------------
    */
    public function render()
    {
        $eventos = $this->queryBase()
            ->orderByDesc('fec_bit')
            ->paginate($this->perPage);

        return view('livewire.admin.bitacora', [
            /*
            |--------------------------------------------------------------------------
            | Eventos
            |--------------------------------------------------------------------------
            */
            'eventos' => $eventos,

            /*
            |--------------------------------------------------------------------------
            | Cards resumen
            |--------------------------------------------------------------------------
            */
            'totalEventos' => $this->totalEventos(),
            'eventosHoy' => $this->eventosHoy(),
            'eventosExitosos' => $this->eventosExitosos(),
            'eventosAdvertencia' => $this->eventosAdvertencia(),
            'eventosFallidos' => $this->eventosFallidos(),
            'usuariosConActividad' => $this->usuariosConActividad(),

            /*
            |--------------------------------------------------------------------------
            | Gráficos
            |--------------------------------------------------------------------------
            */
            'datosGraficos' => $this->datosGraficos(),

            /*
            |--------------------------------------------------------------------------
            | Agrupaciones
            |--------------------------------------------------------------------------
            */
            'gruposModulo' => $this->gruposModulo(),
            'gruposUsuario' => $this->gruposUsuario(),
            'gruposAccion' => $this->gruposAccion(),
            'eventosTimeline' => $this->eventosTimeline(),

            /*
            |--------------------------------------------------------------------------
            | Opciones filtros
            |--------------------------------------------------------------------------
            */
            'usuarios' => $this->opcionesUsuarios(),
            'roles' => $this->opcionesRoles(),
            'modulos' => $this->opcionesModulos(),
            'tablas' => $this->opcionesTablas(),
            'acciones' => $this->opcionesAcciones(),
            'metodos' => $this->opcionesMetodos(),

            /*
            |--------------------------------------------------------------------------
            | Indicadores
            |--------------------------------------------------------------------------
            */
            'miniIndicadores' => $this->miniIndicadores(),
            'porcentajeExito' => $this->porcentajeExito(),
            'eventosSensibles' => $this->eventosSensibles(),
            'promedioDiarioEventos' => $this->promedioDiarioEventos(),
            'ultimoEvento' => $this->ultimoEvento(),
        ]);
    }
}
