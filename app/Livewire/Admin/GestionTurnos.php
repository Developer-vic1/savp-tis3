<?php

namespace App\Livewire\Admin;

use App\Support\Academico\TurnoInteligente;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;
use Throwable;

class GestionTurnos extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'tailwind';

    public string $vista = 'jornada';

    public string $search = '';
    public string $estado = '';
    public string $tipoPlantilla = '';
    public string $aplicacion = '';
    public string $usoAcademico = '';
    public int $perPage = 10;

    public ?string $turnoSeleccionado = null;
    public ?string $plantillaSeleccionada = null;
    public ?string $bloqueSeleccionado = null;

    public bool $modalTurno = false;
    public bool $modalPlantilla = false;
    public bool $modalBloque = false;
    public bool $modalDetalleTurno = false;
    public bool $modalAuditoria = false;
    public bool $modalAplicarPlantilla = false;
    public bool $modalDesactivar = false;
    public bool $modalVistaPrevia = false;

    public string $modoTurno = 'crear';
    public string $modoPlantilla = 'crear';
    public string $modoBloque = 'crear';

    public array $formTurno = [
        'cod_tur' => null,
        'nom_tur' => '',
        'hor_ini_tur' => '',
        'hor_fin_tur' => '',
        'est_tur' => 'ACTIVO',
    ];

    public array $formPlantilla = [
        'cod_pho' => null,
        'cod_tur' => '',
        'nom_pho' => '',
        'tip_pho' => 'REGULAR',
        'des_pho' => '',
        'fec_ini_pho' => '',
        'fec_fin_pho' => '',
        'dur_blo_pho' => 45,
        'ord_pho' => 1,
        'act_pho' => false,
        'est_pho' => true,
    ];

    public array $formBloque = [
        'cod_hbl' => null,
        'cod_tur' => '',
        'cod_pho' => '',
        'num_hbl' => '',
        'hor_ini_hbl' => '',
        'hor_fin_hbl' => '',
        'nom_hbl' => '',
        'tip_hbl' => 'CLASE',
        'obs_hbl' => '',
        'est_hbl' => 'ACTIVO',
    ];

    public array $analisisTurno = [];
    public array $analisisPlantilla = [];
    public array $analisisBloque = [];
    public array $auditoriaEstructura = [];
    public array $vistaPreviaAplicacion = [];

    public ?array $detalleTurno = null;
    public ?array $plantillaParaAplicar = null;
    public ?array $registroParaDesactivar = null;

    public bool $normalizarAlGuardar = true;

    protected TurnoInteligente $soporte;

    public function boot(TurnoInteligente $soporte): void
    {
        $this->soporte = $soporte;
    }

    public function mount(): void
    {
        $this->analisisTurno = $this->analisisInicial('Completa los datos principales del turno.');
        $this->analisisPlantilla = $this->analisisInicial('Completa los datos principales de la plantilla horaria.');
        $this->analisisBloque = $this->analisisInicial('Completa los datos principales del bloque horario.');

        $this->auditarEstructuraSilenciosa();
        $this->seleccionarPlantillaInicial();
        $this->actualizarVistaPrevia();
    }

    public function render(): View
    {
        return view('livewire.admin.gestion-turnos', [
            'gestionTrabajo' => $this->gestionTrabajo(),
            'turnos' => $this->turnosPaginados(),
            'turnosCatalogo' => $this->turnosCatalogo(),
            'plantillasCatalogo' => $this->plantillasCatalogo(),
            'plantillasAgrupadas' => $this->plantillasAgrupadas(),
            'bloquesPlantillaSeleccionada' => $this->bloquesPlantillaSeleccionada(),
            'resumen' => $this->resumenGeneral(),
            'auditoria' => $this->auditoriaEstructura,
            'vistaPrevia' => $this->vistaPreviaAplicacion,
            'recomendaciones' => $this->recomendacionesSistema(),
            'tiposPlantilla' => ['REGULAR', 'INVIERNO'],
            'tiposPlantillaExtra' => ['AJUSTE', 'EMERGENCIA'],
            'tiposBloque' => ['CLASE', 'RECREO', 'DESCANSO', 'FORMACION', 'SALIDA', 'OTRO'],
            'estadosRegistro' => ['ACTIVO', 'INACTIVO'],
        ]);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingEstado(): void
    {
        $this->resetPage();
    }

    public function updatingTipoPlantilla(): void
    {
        $this->resetPage();
    }

    public function updatingAplicacion(): void
    {
        $this->resetPage();
    }

    public function updatingUsoAcademico(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function cambiarVista(string $vista): void
    {
        if (! in_array($vista, ['jornada', 'tabla', 'plantillas', 'agenda', 'bloques', 'compacta'], true)) {
            return;
        }

        $this->vista = $vista;
        $this->actualizarVistaPrevia();
    }

    public function limpiarFiltros(): void
    {
        $this->reset([
            'search',
            'estado',
            'tipoPlantilla',
            'aplicacion',
            'usoAcademico',
        ]);

        $this->perPage = 10;
        $this->resetPage();
    }

    // ============================================================
    // AUDITORÍA Y CORRECCIÓN AUTOMÁTICA
    // ============================================================

    public function auditarEstructuraHoraria(): void
    {
        $this->auditoriaEstructura = $this->soporte->auditarEstructuraHoraria();
        $this->actualizarVistaPrevia();
        $this->modalAuditoria = true;

        $this->registrarBitacora(
            accion: 'AUDITAR',
            tabla: 'horario_bloque',
            registro: 'ESTRUCTURA_HORARIA',
            descripcion: 'Auditoría de estructura horaria desde Gestión de Turnos.'
        );
    }

    public function auditarEstructuraSilenciosa(): void
    {
        try {
            $this->auditoriaEstructura = $this->soporte->auditarEstructuraHoraria();
        } catch (Throwable $e) {
            report($e);
            $this->auditoriaEstructura = $this->analisisInicial('No se pudo ejecutar la auditoría inicial.');
        }
    }

    public function cerrarModalAuditoria(): void
    {
        $this->modalAuditoria = false;
    }

    public function corregirEstructuraHoraria(): void
    {
        try {
            $resultado = $this->soporte->corregirEstructuraHoraria();

            $this->auditoriaEstructura = $this->soporte->auditarEstructuraHoraria();
            $this->actualizarVistaPrevia();
            $this->seleccionarPlantillaInicial(true);

            $this->registrarBitacora(
                accion: 'CORREGIR',
                tabla: 'horario_bloque',
                registro: 'ESTRUCTURA_HORARIA',
                descripcion: 'Corrección automática de bloques sin plantilla horaria.'
            );

            if ($resultado['puede_continuar'] ?? false) {
                $this->dispatch('success-general', mensaje: $resultado['mensaje'] ?? 'La estructura horaria fue corregida correctamente.');
            } else {
                $this->dispatch('error-general', mensaje: $resultado['mensaje'] ?? 'La estructura horaria requiere revisión.');
            }
        } catch (Throwable $e) {
            report($e);
            $this->dispatch('error-general', mensaje: 'No se pudo corregir la estructura horaria. Revisa los registros del sistema.');
        }
    }

    public function crearPlantillaRegularParaTurno(string $codTur): void
    {
        $turno = $this->buscarTurno($codTur);

        if (! $turno) {
            $this->dispatch('error-general', mensaje: 'No se encontró el turno seleccionado.');
            return;
        }

        try {
            DB::transaction(function () use ($codTur, $turno) {
                $resultado = $this->soporte->asegurarPlantillaRegularPorTurno($codTur);

                $this->registrarBitacora(
                    accion: $resultado['creada'] ? 'CREAR' : 'VALIDAR',
                    tabla: 'plantilla_horaria',
                    registro: $resultado['cod_pho'],
                    descripcion: 'Aseguramiento de plantilla regular para el turno ' . ($turno['nombre'] ?? 'seleccionado') . '.'
                );

                $this->plantillaSeleccionada = $resultado['cod_pho'];
            });

            $this->auditarEstructuraSilenciosa();
            $this->actualizarVistaPrevia();

            $this->dispatch('success-general', mensaje: 'La plantilla regular quedó preparada para el turno seleccionado.');
        } catch (Throwable $e) {
            report($e);
            $this->dispatch('error-general', mensaje: 'No se pudo preparar la plantilla regular.');
        }
    }

    public function asociarBloquesSinPlantillaDelTurno(string $codTur): void
    {
        $turno = $this->buscarTurno($codTur);

        if (! $turno) {
            $this->dispatch('error-general', mensaje: 'No se encontró el turno seleccionado.');
            return;
        }

        try {
            DB::transaction(function () use ($codTur, $turno) {
                $resultado = $this->soporte->asegurarPlantillaRegularPorTurno($codTur);
                $cantidad = $this->soporte->asociarBloquesSinPlantilla($codTur, $resultado['cod_pho']);

                $this->registrarBitacora(
                    accion: 'ASOCIAR',
                    tabla: 'horario_bloque',
                    registro: $resultado['cod_pho'],
                    descripcion: "Asociación de {$cantidad} bloque(s) sin plantilla al turno " . ($turno['nombre'] ?? 'seleccionado') . '.'
                );

                $this->plantillaSeleccionada = $resultado['cod_pho'];
            });

            $this->auditarEstructuraSilenciosa();
            $this->actualizarVistaPrevia();

            $this->dispatch('success-general', mensaje: 'Los bloques existentes fueron asociados a la plantilla regular.');
        } catch (Throwable $e) {
            report($e);
            $this->dispatch('error-general', mensaje: 'No se pudo asociar los bloques existentes.');
        }
    }

    // ============================================================
    // TURNO
    // ============================================================

    public function abrirModalCrearTurno(): void
    {
        $this->modoTurno = 'crear';
        $this->modalTurno = true;
        $this->resetValidation();
        $this->limpiarFormularioTurno();
        $this->analizarTurnoTiempoReal();
    }

    public function abrirModalEditarTurno(string $codTur): void
    {
        $turno = $this->buscarTurno($codTur);

        if (! $turno) {
            $this->dispatch('error-general', mensaje: 'No se encontró el turno seleccionado.');
            return;
        }

        $this->modoTurno = 'editar';
        $this->modalTurno = true;
        $this->resetValidation();

        $this->formTurno = [
            'cod_tur' => $codTur,
            'nom_tur' => $turno['nombre'],
            'hor_ini_tur' => $turno['hora_inicio'],
            'hor_fin_tur' => $turno['hora_fin'],
            'est_tur' => $turno['estado'],
        ];

        $this->analizarTurnoTiempoReal();
    }

    public function cerrarModalTurno(): void
    {
        $this->modalTurno = false;
        $this->modoTurno = 'crear';
        $this->limpiarFormularioTurno();
        $this->resetValidation();
    }

    public function analizarTurnoTiempoReal(): void
    {
        $this->analisisTurno = $this->soporte->analizarTurno(
            $this->formTurno,
            $this->modoTurno === 'editar' ? ($this->formTurno['cod_tur'] ?? null) : null,
            true
        );
    }

    public function guardarTurno(): void
    {
        $this->analisisTurno = $this->soporte->analizarTurno(
            $this->formTurno,
            $this->modoTurno === 'editar' ? ($this->formTurno['cod_tur'] ?? null) : null,
            false
        );

        if (! ($this->analisisTurno['puede_continuar'] ?? false)) {
            $this->dispatch('error-general', mensaje: $this->analisisTurno['mensaje'] ?? 'Corrige los datos del turno.');
            return;
        }

        $datos = $this->soporte->normalizarDatosTurno($this->formTurno, $this->normalizarAlGuardar);

        $this->validate($this->reglasTurno(), [], $this->atributosTurno());

        try {
            DB::transaction(function () use ($datos) {
                if ($this->modoTurno === 'crear') {
                    $codTur = $this->generarCodigo('turno', 'cod_tur', 'TUR');

                    DB::table('turno')->insert($this->filtrarColumnas('turno', [
                        'cod_tur' => $codTur,
                        'nom_tur' => $datos['nom_tur'],
                        'hor_ini_tur' => $datos['hor_ini_tur'],
                        'hor_fin_tur' => $datos['hor_fin_tur'],
                        'est_tur' => $datos['est_tur'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]));

                    $this->registrarBitacora(
                        accion: 'CREAR',
                        tabla: 'turno',
                        registro: $codTur,
                        descripcion: 'Registro de turno académico.'
                    );

                    $this->turnoSeleccionado = $codTur;
                } else {
                    $codTur = (string) $this->formTurno['cod_tur'];

                    DB::table('turno')
                        ->where('cod_tur', $codTur)
                        ->update($this->filtrarColumnas('turno', [
                            'nom_tur' => $datos['nom_tur'],
                            'hor_ini_tur' => $datos['hor_ini_tur'],
                            'hor_fin_tur' => $datos['hor_fin_tur'],
                            'est_tur' => $datos['est_tur'],
                            'updated_at' => now(),
                        ]));

                    $this->registrarBitacora(
                        accion: 'ACTUALIZAR',
                        tabla: 'turno',
                        registro: $codTur,
                        descripcion: 'Actualización de turno académico.'
                    );

                    $this->turnoSeleccionado = $codTur;
                }
            });

            $this->cerrarModalTurno();
            $this->auditarEstructuraSilenciosa();
            $this->actualizarVistaPrevia();

            $this->dispatch('success-general', mensaje: 'El turno fue guardado correctamente.');
        } catch (Throwable $e) {
            report($e);
            $this->dispatch('error-general', mensaje: 'No se pudo guardar el turno.');
        }
    }

    // ============================================================
    // PLANTILLA HORARIA
    // ============================================================

    public function abrirModalCrearPlantilla(?string $codTur = null, string $tipo = 'REGULAR'): void
    {
        $this->modoPlantilla = 'crear';
        $this->modalPlantilla = true;
        $this->resetValidation();
        $this->limpiarFormularioPlantilla();

        $tipo = in_array($tipo, ['REGULAR', 'INVIERNO'], true) ? $tipo : 'REGULAR';

        $this->formPlantilla['tip_pho'] = $tipo;
        $this->formPlantilla['dur_blo_pho'] = $tipo === 'INVIERNO' ? 30 : 45;

        if ($codTur) {
            $turno = $this->buscarTurno($codTur);

            if ($turno) {
                $this->formPlantilla['cod_tur'] = $codTur;
                $this->formPlantilla['nom_pho'] = 'Plantilla ' . ucfirst(strtolower($tipo)) . ' - ' . $turno['nombre'];
            }
        }

        if ($tipo === 'INVIERNO') {
            $sugerencia = $this->soporte->sugerirRangoInvierno();

            if ($sugerencia['disponible'] ?? false) {
                $this->formPlantilla['fec_ini_pho'] = $sugerencia['fecha_inicio'] ?? '';
                $this->formPlantilla['fec_fin_pho'] = $sugerencia['fecha_fin'] ?? '';
            }
        }

        $this->analizarPlantillaTiempoReal();
    }

    public function abrirModalEditarPlantilla(string $codPho): void
    {
        $plantilla = $this->buscarPlantilla($codPho);

        if (! $plantilla) {
            $this->dispatch('error-general', mensaje: 'No se encontró la plantilla seleccionada.');
            return;
        }

        $this->modoPlantilla = 'editar';
        $this->modalPlantilla = true;
        $this->resetValidation();

        $this->formPlantilla = [
            'cod_pho' => $codPho,
            'cod_tur' => $plantilla['cod_tur'],
            'nom_pho' => $plantilla['nombre'],
            'tip_pho' => $plantilla['tipo'],
            'des_pho' => $plantilla['descripcion'],
            'fec_ini_pho' => $plantilla['fecha_inicio'],
            'fec_fin_pho' => $plantilla['fecha_fin'],
            'dur_blo_pho' => $plantilla['duracion_base'],
            'ord_pho' => $plantilla['orden'],
            'act_pho' => $plantilla['aplicada'],
            'est_pho' => $plantilla['activa'],
        ];

        $this->analizarPlantillaTiempoReal();
    }

    public function cerrarModalPlantilla(): void
    {
        $this->modalPlantilla = false;
        $this->modoPlantilla = 'crear';
        $this->limpiarFormularioPlantilla();
        $this->resetValidation();
    }

    public function analizarPlantillaTiempoReal(): void
    {
        $this->analisisPlantilla = $this->soporte->analizarPlantilla(
            $this->formPlantilla,
            $this->modoPlantilla === 'editar' ? ($this->formPlantilla['cod_pho'] ?? null) : null,
            true
        );

        $this->actualizarVistaPreviaConFormulario();
    }

    public function aplicarSugerenciaInvierno(): void
    {
        $sugerencia = $this->soporte->sugerirRangoInvierno();

        if (! ($sugerencia['disponible'] ?? false)) {
            $this->dispatch('error-general', mensaje: $sugerencia['mensaje'] ?? 'No se pudo calcular una sugerencia de invierno.');
            return;
        }

        $this->formPlantilla['tip_pho'] = 'INVIERNO';
        $this->formPlantilla['fec_ini_pho'] = $sugerencia['fecha_inicio'] ?? '';
        $this->formPlantilla['fec_fin_pho'] = $sugerencia['fecha_fin'] ?? '';
        $this->formPlantilla['dur_blo_pho'] = 30;

        if ($this->formPlantilla['cod_tur']) {
            $turno = $this->buscarTurno($this->formPlantilla['cod_tur']);

            if ($turno && trim((string) $this->formPlantilla['nom_pho']) === '') {
                $this->formPlantilla['nom_pho'] = 'Plantilla Invierno - ' . $turno['nombre'];
            }
        }

        $this->analizarPlantillaTiempoReal();

        $this->dispatch('success-general', mensaje: 'Se aplicó la sugerencia de rango para horario de invierno.');
    }

    public function guardarPlantilla(): void
    {
        $this->analisisPlantilla = $this->soporte->analizarPlantilla(
            $this->formPlantilla,
            $this->modoPlantilla === 'editar' ? ($this->formPlantilla['cod_pho'] ?? null) : null,
            false
        );

        if (! ($this->analisisPlantilla['puede_continuar'] ?? false)) {
            $this->dispatch('error-general', mensaje: $this->analisisPlantilla['mensaje'] ?? 'Corrige los datos de la plantilla.');
            return;
        }

        $datos = $this->soporte->normalizarDatosPlantilla($this->formPlantilla, $this->normalizarAlGuardar);

        $this->validate($this->reglasPlantilla(), [], $this->atributosPlantilla());

        try {
            DB::transaction(function () use ($datos) {
                if ((bool) ($datos['act_pho'] ?? false)) {
                    DB::table('plantilla_horaria')
                        ->where('cod_tur', $datos['cod_tur'])
                        ->where('tip_pho', $datos['tip_pho'])
                        ->when(
                            $this->modoPlantilla === 'editar' && ! empty($this->formPlantilla['cod_pho']),
                            fn(Builder $query) => $query->where('cod_pho', '!=', $this->formPlantilla['cod_pho'])
                        )
                        ->update($this->filtrarColumnas('plantilla_horaria', [
                            'act_pho' => false,
                            'updated_at' => now(),
                        ]));
                }

                if ($this->modoPlantilla === 'crear') {
                    $codPho = $this->generarCodigo('plantilla_horaria', 'cod_pho', 'PHO');

                    DB::table('plantilla_horaria')->insert($this->filtrarColumnas('plantilla_horaria', [
                        'cod_pho' => $codPho,
                        'cod_tur' => $datos['cod_tur'],
                        'nom_pho' => $datos['nom_pho'],
                        'tip_pho' => $datos['tip_pho'],
                        'des_pho' => $datos['des_pho'],
                        'fec_ini_pho' => $datos['fec_ini_pho'],
                        'fec_fin_pho' => $datos['fec_fin_pho'],
                        'dur_blo_pho' => $datos['dur_blo_pho'],
                        'ord_pho' => $datos['ord_pho'],
                        'act_pho' => (bool) $datos['act_pho'],
                        'est_pho' => (bool) $datos['est_pho'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]));

                    $this->registrarBitacora(
                        accion: 'CREAR',
                        tabla: 'plantilla_horaria',
                        registro: $codPho,
                        descripcion: 'Registro de plantilla horaria ' . $datos['tip_pho'] . '.'
                    );

                    $this->plantillaSeleccionada = $codPho;
                } else {
                    $codPho = (string) $this->formPlantilla['cod_pho'];

                    DB::table('plantilla_horaria')
                        ->where('cod_pho', $codPho)
                        ->update($this->filtrarColumnas('plantilla_horaria', [
                            'cod_tur' => $datos['cod_tur'],
                            'nom_pho' => $datos['nom_pho'],
                            'tip_pho' => $datos['tip_pho'],
                            'des_pho' => $datos['des_pho'],
                            'fec_ini_pho' => $datos['fec_ini_pho'],
                            'fec_fin_pho' => $datos['fec_fin_pho'],
                            'dur_blo_pho' => $datos['dur_blo_pho'],
                            'ord_pho' => $datos['ord_pho'],
                            'act_pho' => (bool) $datos['act_pho'],
                            'est_pho' => (bool) $datos['est_pho'],
                            'updated_at' => now(),
                        ]));

                    DB::table('horario_bloque')
                        ->where('cod_pho', $codPho)
                        ->update($this->filtrarColumnas('horario_bloque', [
                            'cod_tur' => $datos['cod_tur'],
                            'updated_at' => now(),
                        ]));

                    $this->registrarBitacora(
                        accion: 'ACTUALIZAR',
                        tabla: 'plantilla_horaria',
                        registro: $codPho,
                        descripcion: 'Actualización de plantilla horaria ' . $datos['tip_pho'] . '.'
                    );

                    $this->plantillaSeleccionada = $codPho;
                }
            });

            $this->cerrarModalPlantilla();
            $this->auditarEstructuraSilenciosa();
            $this->actualizarVistaPrevia();

            $this->dispatch('success-general', mensaje: 'La plantilla horaria fue guardada correctamente.');
        } catch (Throwable $e) {
            report($e);
            $this->dispatch('error-general', mensaje: 'No se pudo guardar la plantilla horaria.');
        }
    }

    public function confirmarAplicarPlantilla(string $codPho): void
    {
        $plantilla = $this->buscarPlantilla($codPho);

        if (! $plantilla) {
            $this->dispatch('error-general', mensaje: 'No se encontró la plantilla seleccionada.');
            return;
        }

        $this->plantillaParaAplicar = $plantilla;
        $this->modalAplicarPlantilla = true;
    }

    public function cerrarModalAplicarPlantilla(): void
    {
        $this->modalAplicarPlantilla = false;
        $this->plantillaParaAplicar = null;
    }

    public function aplicarPlantilla(): void
    {
        if (! $this->plantillaParaAplicar) {
            $this->dispatch('error-general', mensaje: 'No hay plantilla seleccionada.');
            return;
        }

        try {
            DB::transaction(function () {
                $codPho = $this->plantillaParaAplicar['cod_pho'];
                $codTur = $this->plantillaParaAplicar['cod_tur'];
                $tipo = $this->plantillaParaAplicar['tipo'];

                DB::table('plantilla_horaria')
                    ->where('cod_tur', $codTur)
                    ->where('tip_pho', $tipo)
                    ->update($this->filtrarColumnas('plantilla_horaria', [
                        'act_pho' => false,
                        'updated_at' => now(),
                    ]));

                DB::table('plantilla_horaria')
                    ->where('cod_pho', $codPho)
                    ->update($this->filtrarColumnas('plantilla_horaria', [
                        'act_pho' => true,
                        'est_pho' => true,
                        'updated_at' => now(),
                    ]));

                $this->registrarBitacora(
                    accion: 'APLICAR',
                    tabla: 'plantilla_horaria',
                    registro: $codPho,
                    descripcion: 'Aplicación de plantilla horaria ' . $tipo . '.'
                );

                $this->plantillaSeleccionada = $codPho;
            });

            $this->cerrarModalAplicarPlantilla();
            $this->auditarEstructuraSilenciosa();
            $this->actualizarVistaPrevia();

            $this->dispatch('success-general', mensaje: 'La plantilla fue aplicada correctamente.');
        } catch (Throwable $e) {
            report($e);
            $this->dispatch('error-general', mensaje: 'No se pudo aplicar la plantilla seleccionada.');
        }
    }

    public function duplicarComoInvierno(string $codPho): void
    {
        $plantilla = $this->buscarPlantilla($codPho);

        if (! $plantilla) {
            $this->dispatch('error-general', mensaje: 'No se encontró la plantilla base.');
            return;
        }

        $sugerencia = $this->soporte->sugerirRangoInvierno();

        try {
            DB::transaction(function () use ($plantilla, $sugerencia) {
                $nuevoCodPho = $this->generarCodigo('plantilla_horaria', 'cod_pho', 'PHO');
                $turno = $this->buscarTurno($plantilla['cod_tur']);

                DB::table('plantilla_horaria')->insert($this->filtrarColumnas('plantilla_horaria', [
                    'cod_pho' => $nuevoCodPho,
                    'cod_tur' => $plantilla['cod_tur'],
                    'nom_pho' => 'Plantilla Invierno - ' . ($turno['nombre'] ?? 'Turno'),
                    'tip_pho' => 'INVIERNO',
                    'des_pho' => 'Plantilla generada desde la plantilla regular para aplicar horario de invierno.',
                    'fec_ini_pho' => $sugerencia['fecha_inicio'] ?? null,
                    'fec_fin_pho' => $sugerencia['fecha_fin'] ?? null,
                    'dur_blo_pho' => 30,
                    'ord_pho' => ((int) ($plantilla['orden'] ?? 1)) + 1,
                    'act_pho' => false,
                    'est_pho' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));

                $bloques = DB::table('horario_bloque')
                    ->where('cod_pho', $codPho)
                    ->orderBy('num_hbl')
                    ->get();

                foreach ($bloques as $bloque) {
                    $inicio = $this->formatearHora($bloque->hor_ini_hbl ?? null);
                    $fin = $this->calcularHoraFin($inicio, $this->duracionInviernoParaBloque($bloque));

                    DB::table('horario_bloque')->insert($this->filtrarColumnas('horario_bloque', [
                        'cod_hbl' => $this->generarCodigo('horario_bloque', 'cod_hbl', 'HBL'),
                        'cod_tur' => $plantilla['cod_tur'],
                        'cod_pho' => $nuevoCodPho,
                        'num_hbl' => $bloque->num_hbl ?? null,
                        'hor_ini_hbl' => $inicio,
                        'hor_fin_hbl' => $fin,
                        'nom_hbl' => $bloque->nom_hbl ?? null,
                        'tip_hbl' => $bloque->tip_hbl ?? 'CLASE',
                        'obs_hbl' => 'Bloque generado para horario de invierno.',
                        'est_hbl' => 'ACTIVO',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]));
                }

                $this->registrarBitacora(
                    accion: 'DUPLICAR',
                    tabla: 'plantilla_horaria',
                    registro: $nuevoCodPho,
                    descripcion: 'Duplicación de plantilla regular como plantilla de invierno.'
                );

                $this->plantillaSeleccionada = $nuevoCodPho;
            });

            $this->auditarEstructuraSilenciosa();
            $this->actualizarVistaPrevia();

            $this->dispatch('success-general', mensaje: 'Se generó una plantilla de invierno con bloques base.');
        } catch (Throwable $e) {
            report($e);
            $this->dispatch('error-general', mensaje: 'No se pudo duplicar la plantilla como invierno.');
        }
    }

    // ============================================================
    // BLOQUES HORARIOS
    // ============================================================

    public function seleccionarPlantilla(string $codPho): void
    {
        if (! $this->buscarPlantilla($codPho)) {
            $this->dispatch('error-general', mensaje: 'No se encontró la plantilla seleccionada.');
            return;
        }

        $this->plantillaSeleccionada = $codPho;
    }

    public function abrirModalCrearBloque(?string $codPho = null): void
    {
        $this->modoBloque = 'crear';
        $this->modalBloque = true;
        $this->resetValidation();
        $this->limpiarFormularioBloque();

        $codPho = $codPho ?: $this->plantillaSeleccionada;

        if ($codPho) {
            $plantilla = $this->buscarPlantilla($codPho);

            if ($plantilla) {
                $this->formBloque['cod_pho'] = $codPho;
                $this->formBloque['cod_tur'] = $plantilla['cod_tur'];
                $this->formBloque['num_hbl'] = $this->siguienteNumeroBloque($codPho);
                $this->formBloque['nom_hbl'] = $this->nombreSugeridoBloque((int) $this->formBloque['num_hbl']);
                $this->formBloque['tip_hbl'] = 'CLASE';
            }
        }

        $this->analizarBloqueTiempoReal();
    }

    public function abrirModalEditarBloque(string $codHbl): void
    {
        $bloque = $this->buscarBloque($codHbl);

        if (! $bloque) {
            $this->dispatch('error-general', mensaje: 'No se encontró el bloque seleccionado.');
            return;
        }

        $this->modoBloque = 'editar';
        $this->modalBloque = true;
        $this->resetValidation();

        $this->formBloque = [
            'cod_hbl' => $codHbl,
            'cod_tur' => $bloque['cod_tur'],
            'cod_pho' => $bloque['cod_pho'],
            'num_hbl' => $bloque['numero'],
            'hor_ini_hbl' => $bloque['hora_inicio'],
            'hor_fin_hbl' => $bloque['hora_fin'],
            'nom_hbl' => $bloque['nombre'],
            'tip_hbl' => $bloque['tipo'],
            'obs_hbl' => $bloque['observacion'],
            'est_hbl' => $bloque['estado'],
        ];

        $this->analizarBloqueTiempoReal();
    }

    public function cerrarModalBloque(): void
    {
        $this->modalBloque = false;
        $this->modoBloque = 'crear';
        $this->limpiarFormularioBloque();
        $this->resetValidation();
    }

    public function sincronizarTurnoDesdePlantilla(): void
    {
        $plantilla = $this->buscarPlantilla($this->formBloque['cod_pho'] ?? null);

        if ($plantilla) {
            $this->formBloque['cod_tur'] = $plantilla['cod_tur'];
        }

        $this->analizarBloqueTiempoReal();
    }

    public function analizarBloqueTiempoReal(): void
    {
        $this->analisisBloque = $this->soporte->analizarBloque(
            $this->formBloque,
            $this->modoBloque === 'editar' ? ($this->formBloque['cod_hbl'] ?? null) : null,
            true
        );
    }

    public function guardarBloque(): void
    {
        $this->sincronizarTurnoDesdePlantilla();

        $this->analisisBloque = $this->soporte->analizarBloque(
            $this->formBloque,
            $this->modoBloque === 'editar' ? ($this->formBloque['cod_hbl'] ?? null) : null,
            false
        );

        if (! ($this->analisisBloque['puede_continuar'] ?? false)) {
            $this->dispatch('error-general', mensaje: $this->analisisBloque['mensaje'] ?? 'Corrige los datos del bloque.');
            return;
        }

        $datos = $this->soporte->normalizarDatosBloque($this->formBloque, $this->normalizarAlGuardar);

        $this->validate($this->reglasBloque(), [], $this->atributosBloque());

        try {
            DB::transaction(function () use ($datos) {
                if ($this->modoBloque === 'crear') {
                    $codHbl = $this->generarCodigo('horario_bloque', 'cod_hbl', 'HBL');

                    DB::table('horario_bloque')->insert($this->filtrarColumnas('horario_bloque', [
                        'cod_hbl' => $codHbl,
                        'cod_tur' => $datos['cod_tur'],
                        'cod_pho' => $datos['cod_pho'],
                        'num_hbl' => $datos['num_hbl'],
                        'hor_ini_hbl' => $datos['hor_ini_hbl'],
                        'hor_fin_hbl' => $datos['hor_fin_hbl'],
                        'nom_hbl' => $datos['nom_hbl'],
                        'tip_hbl' => $datos['tip_hbl'],
                        'obs_hbl' => $datos['obs_hbl'],
                        'est_hbl' => $datos['est_hbl'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]));

                    $this->registrarBitacora(
                        accion: 'CREAR',
                        tabla: 'horario_bloque',
                        registro: $codHbl,
                        descripcion: 'Registro de bloque horario asociado a plantilla.'
                    );

                    $this->bloqueSeleccionado = $codHbl;
                } else {
                    $codHbl = (string) $this->formBloque['cod_hbl'];

                    DB::table('horario_bloque')
                        ->where('cod_hbl', $codHbl)
                        ->update($this->filtrarColumnas('horario_bloque', [
                            'cod_tur' => $datos['cod_tur'],
                            'cod_pho' => $datos['cod_pho'],
                            'num_hbl' => $datos['num_hbl'],
                            'hor_ini_hbl' => $datos['hor_ini_hbl'],
                            'hor_fin_hbl' => $datos['hor_fin_hbl'],
                            'nom_hbl' => $datos['nom_hbl'],
                            'tip_hbl' => $datos['tip_hbl'],
                            'obs_hbl' => $datos['obs_hbl'],
                            'est_hbl' => $datos['est_hbl'],
                            'updated_at' => now(),
                        ]));

                    $this->registrarBitacora(
                        accion: 'ACTUALIZAR',
                        tabla: 'horario_bloque',
                        registro: $codHbl,
                        descripcion: 'Actualización de bloque horario asociado a plantilla.'
                    );

                    $this->bloqueSeleccionado = $codHbl;
                }

                $this->plantillaSeleccionada = $datos['cod_pho'];
            });

            $this->cerrarModalBloque();
            $this->auditarEstructuraSilenciosa();
            $this->actualizarVistaPrevia();

            $this->dispatch('success-general', mensaje: 'El bloque horario fue guardado correctamente.');
        } catch (Throwable $e) {
            report($e);
            $this->dispatch('error-general', mensaje: 'No se pudo guardar el bloque horario.');
        }
    }

    public function validarBloquesDePlantilla(?string $codPho = null): void
    {
        $codPho = $codPho ?: $this->plantillaSeleccionada;

        if (! $codPho) {
            $this->dispatch('error-general', mensaje: 'Selecciona una plantilla para validar sus bloques.');
            return;
        }

        $resultado = $this->soporte->validarBloquesDePlantilla($codPho);

        if ($resultado['puede_continuar'] ?? false) {
            $this->dispatch('success-general', mensaje: $resultado['mensaje'] ?? 'La plantilla puede utilizarse.');
        } else {
            $this->dispatch('error-general', mensaje: $resultado['mensaje'] ?? 'La plantilla tiene observaciones.');
        }

        $this->registrarBitacora(
            accion: 'VALIDAR',
            tabla: 'horario_bloque',
            registro: $codPho,
            descripcion: 'Validación de bloques de una plantilla horaria.'
        );
    }

    public function reordenarBloque(string $direccion, string $codHbl): void
    {
        $bloque = $this->buscarBloque($codHbl);

        if (! $bloque || ! $bloque['cod_pho']) {
            return;
        }

        $actual = (int) $bloque['numero'];
        $nuevoNumero = $direccion === 'arriba' ? $actual - 1 : $actual + 1;

        if ($nuevoNumero <= 0) {
            return;
        }

        $otro = DB::table('horario_bloque')
            ->where('cod_pho', $bloque['cod_pho'])
            ->where('num_hbl', $nuevoNumero)
            ->first();

        if (! $otro) {
            return;
        }

        try {
            DB::transaction(function () use ($bloque, $otro, $actual, $nuevoNumero) {
                DB::table('horario_bloque')
                    ->where('cod_hbl', $bloque['cod_hbl'])
                    ->update($this->filtrarColumnas('horario_bloque', [
                        'num_hbl' => -999,
                        'updated_at' => now(),
                    ]));

                DB::table('horario_bloque')
                    ->where('cod_hbl', $otro->cod_hbl)
                    ->update($this->filtrarColumnas('horario_bloque', [
                        'num_hbl' => $actual,
                        'updated_at' => now(),
                    ]));

                DB::table('horario_bloque')
                    ->where('cod_hbl', $bloque['cod_hbl'])
                    ->update($this->filtrarColumnas('horario_bloque', [
                        'num_hbl' => $nuevoNumero,
                        'updated_at' => now(),
                    ]));

                $this->registrarBitacora(
                    accion: 'REORDENAR',
                    tabla: 'horario_bloque',
                    registro: $bloque['cod_hbl'],
                    descripcion: 'Reordenamiento de bloque horario dentro de plantilla.'
                );
            });

            $this->dispatch('success-general', mensaje: 'El orden del bloque fue actualizado.');
        } catch (Throwable $e) {
            report($e);
            $this->dispatch('error-general', mensaje: 'No se pudo reordenar el bloque.');
        }
    }

    // ============================================================
    // DETALLE, VISTA PREVIA Y DESACTIVACIÓN
    // ============================================================

    public function abrirDetalleTurno(string $codTur): void
    {
        $turno = $this->buscarTurno($codTur);

        if (! $turno) {
            $this->dispatch('error-general', mensaje: 'No se encontró el turno seleccionado.');
            return;
        }

        $this->turnoSeleccionado = $codTur;
        $this->detalleTurno = $this->detalleTurno($codTur);
        $this->modalDetalleTurno = true;
    }

    public function cerrarDetalleTurno(): void
    {
        $this->modalDetalleTurno = false;
        $this->detalleTurno = null;
    }

    public function abrirVistaPreviaAplicacion(): void
    {
        $this->actualizarVistaPrevia();
        $this->modalVistaPrevia = true;
    }

    public function cerrarVistaPreviaAplicacion(): void
    {
        $this->modalVistaPrevia = false;
    }

    public function actualizarVistaPrevia(): void
    {
        try {
            $this->vistaPreviaAplicacion = $this->soporte->generarVistaPreviaAplicacion();
        } catch (Throwable $e) {
            report($e);
            $this->vistaPreviaAplicacion = [
                'disponible' => false,
                'mensaje' => 'No se pudo generar la vista previa de aplicación horaria.',
                'segmentos' => [],
            ];
        }
    }

    public function actualizarVistaPreviaConFormulario(): void
    {
        try {
            $this->vistaPreviaAplicacion = $this->soporte->generarVistaPreviaAplicacionConFormulario($this->formPlantilla);
        } catch (Throwable $e) {
            report($e);
            $this->actualizarVistaPrevia();
        }
    }

    public function confirmarDesactivar(string $tipo, string $codigo): void
    {
        $tipo = mb_strtolower($tipo);

        $analisis = match ($tipo) {
            'turno' => $this->soporte->puedeDesactivarTurno($codigo),
            'plantilla' => $this->soporte->puedeDesactivarPlantilla($codigo),
            'bloque' => $this->soporte->puedeDesactivarBloque($codigo),
            default => $this->analisisInicial('No se pudo analizar el registro seleccionado.'),
        };

        $this->registroParaDesactivar = [
            'tipo' => $tipo,
            'codigo' => $codigo,
            'analisis' => $analisis,
        ];

        $this->modalDesactivar = true;
    }

    public function cerrarModalDesactivar(): void
    {
        $this->modalDesactivar = false;
        $this->registroParaDesactivar = null;
    }

    public function desactivarRegistro(): void
    {
        if (! $this->registroParaDesactivar) {
            return;
        }

        $tipo = $this->registroParaDesactivar['tipo'];
        $codigo = $this->registroParaDesactivar['codigo'];

        try {
            DB::transaction(function () use ($tipo, $codigo) {
                match ($tipo) {
                    'turno' => DB::table('turno')
                        ->where('cod_tur', $codigo)
                        ->update($this->filtrarColumnas('turno', [
                            'est_tur' => 'INACTIVO',
                            'updated_at' => now(),
                        ])),
                    'plantilla' => DB::table('plantilla_horaria')
                        ->where('cod_pho', $codigo)
                        ->update($this->filtrarColumnas('plantilla_horaria', [
                            'est_pho' => false,
                            'act_pho' => false,
                            'updated_at' => now(),
                        ])),
                    'bloque' => DB::table('horario_bloque')
                        ->where('cod_hbl', $codigo)
                        ->update($this->filtrarColumnas('horario_bloque', [
                            'est_hbl' => 'INACTIVO',
                            'updated_at' => now(),
                        ])),
                    default => null,
                };

                $this->registrarBitacora(
                    accion: 'DESACTIVAR',
                    tabla: $this->tablaPorTipo($tipo),
                    registro: $codigo,
                    descripcion: 'Desactivación lógica desde Gestión de Turnos.'
                );
            });

            $this->cerrarModalDesactivar();
            $this->auditarEstructuraSilenciosa();
            $this->actualizarVistaPrevia();

            $this->dispatch('success-general', mensaje: 'El registro fue desactivado correctamente.');
        } catch (Throwable $e) {
            report($e);
            $this->dispatch('error-general', mensaje: 'No se pudo desactivar el registro.');
        }
    }

    public function reactivarRegistro(string $tipo, string $codigo): void
    {
        $tipo = mb_strtolower($tipo);

        try {
            DB::transaction(function () use ($tipo, $codigo) {
                match ($tipo) {
                    'turno' => DB::table('turno')
                        ->where('cod_tur', $codigo)
                        ->update($this->filtrarColumnas('turno', [
                            'est_tur' => 'ACTIVO',
                            'updated_at' => now(),
                        ])),
                    'plantilla' => DB::table('plantilla_horaria')
                        ->where('cod_pho', $codigo)
                        ->update($this->filtrarColumnas('plantilla_horaria', [
                            'est_pho' => true,
                            'updated_at' => now(),
                        ])),
                    'bloque' => DB::table('horario_bloque')
                        ->where('cod_hbl', $codigo)
                        ->update($this->filtrarColumnas('horario_bloque', [
                            'est_hbl' => 'ACTIVO',
                            'updated_at' => now(),
                        ])),
                    default => null,
                };

                $this->registrarBitacora(
                    accion: 'REACTIVAR',
                    tabla: $this->tablaPorTipo($tipo),
                    registro: $codigo,
                    descripcion: 'Reactivación lógica desde Gestión de Turnos.'
                );
            });

            $this->auditarEstructuraSilenciosa();
            $this->actualizarVistaPrevia();

            $this->dispatch('success-general', mensaje: 'El registro fue reactivado correctamente.');
        } catch (Throwable $e) {
            report($e);
            $this->dispatch('error-general', mensaje: 'No se pudo reactivar el registro.');
        }
    }

    // ============================================================
    // CONSULTAS PARA LA VISTA
    // ============================================================

    private function turnosPaginados(): LengthAwarePaginator
    {
        $query = DB::table('turno');

        if ($this->search !== '') {
            $buscar = '%' . mb_strtolower(trim($this->search)) . '%';

            $query->where(function (Builder $subquery) use ($buscar) {
                $subquery->whereRaw('LOWER(nom_tur) LIKE ?', [$buscar]);

                if ($this->tablaExiste('plantilla_horaria')) {
                    $subquery->orWhereExists(function (Builder $exists) use ($buscar) {
                        $exists->from('plantilla_horaria')
                            ->whereColumn('plantilla_horaria.cod_tur', 'turno.cod_tur')
                            ->whereRaw('LOWER(plantilla_horaria.nom_pho) LIKE ?', [$buscar]);
                    });
                }
            });
        }

        if ($this->estado !== '') {
            $query->where('est_tur', $this->estado);
        }

        if ($this->tipoPlantilla !== '' && $this->tablaExiste('plantilla_horaria')) {
            $query->whereExists(function (Builder $exists) {
                $exists->from('plantilla_horaria')
                    ->whereColumn('plantilla_horaria.cod_tur', 'turno.cod_tur')
                    ->where('plantilla_horaria.tip_pho', $this->tipoPlantilla);
            });
        }

        if ($this->aplicacion !== '' && $this->tablaExiste('plantilla_horaria')) {
            $query->whereExists(function (Builder $exists) {
                $exists->from('plantilla_horaria')
                    ->whereColumn('plantilla_horaria.cod_tur', 'turno.cod_tur')
                    ->where('plantilla_horaria.act_pho', $this->aplicacion === 'APLICADA');
            });
        }

        if ($this->usoAcademico !== '') {
            if ($this->usoAcademico === 'CON_HORARIO') {
                $query->whereExists(function (Builder $exists) {
                    $exists->from('horario')
                        ->whereColumn('horario.cod_tur', 'turno.cod_tur');
                });
            }

            if ($this->usoAcademico === 'SIN_HORARIO') {
                $query->whereNotExists(function (Builder $exists) {
                    $exists->from('horario')
                        ->whereColumn('horario.cod_tur', 'turno.cod_tur');
                });
            }
        }

        return $query
            ->orderByRaw("CASE WHEN est_tur = 'ACTIVO' THEN 0 ELSE 1 END")
            ->orderBy('hor_ini_tur')
            ->orderBy('nom_tur')
            ->paginate($this->perPage)
            ->through(fn($turno) => $this->mapearTurno($turno));
    }

    private function turnosCatalogo(): Collection
    {
        if (! $this->tablaExiste('turno')) {
            return collect();
        }

        return DB::table('turno')
            ->orderByRaw("CASE WHEN est_tur = 'ACTIVO' THEN 0 ELSE 1 END")
            ->orderBy('hor_ini_tur')
            ->orderBy('nom_tur')
            ->get()
            ->map(fn($turno) => $this->mapearTurno($turno));
    }

    private function plantillasCatalogo(): Collection
    {
        if (! $this->tablaExiste('plantilla_horaria')) {
            return collect();
        }

        return DB::table('plantilla_horaria')
            ->orderByRaw("CASE WHEN tip_pho = 'REGULAR' THEN 0 WHEN tip_pho = 'INVIERNO' THEN 1 ELSE 2 END")
            ->orderByDesc('act_pho')
            ->orderBy('cod_tur')
            ->orderBy('ord_pho')
            ->orderBy('nom_pho')
            ->get()
            ->map(fn($plantilla) => $this->mapearPlantilla($plantilla));
    }

    private function plantillasAgrupadas(): Collection
    {
        return $this->plantillasCatalogo()
            ->groupBy('cod_tur');
    }

    private function bloquesPlantillaSeleccionada(): Collection
    {
        if (! $this->plantillaSeleccionada || ! $this->tablaExiste('horario_bloque')) {
            return collect();
        }

        return DB::table('horario_bloque')
            ->where('cod_pho', $this->plantillaSeleccionada)
            ->orderBy('num_hbl')
            ->orderBy('hor_ini_hbl')
            ->get()
            ->map(fn($bloque) => $this->mapearBloque($bloque));
    }

    private function resumenGeneral(): array
    {
        return [
            'turnos_activos' => $this->tablaExiste('turno')
                ? DB::table('turno')->where('est_tur', 'ACTIVO')->count()
                : 0,
            'plantillas_regulares' => $this->tablaExiste('plantilla_horaria')
                ? DB::table('plantilla_horaria')->where('tip_pho', 'REGULAR')->count()
                : 0,
            'plantillas_invierno' => $this->tablaExiste('plantilla_horaria')
                ? DB::table('plantilla_horaria')->where('tip_pho', 'INVIERNO')->count()
                : 0,
            'bloques_horarios' => $this->tablaExiste('horario_bloque')
                ? DB::table('horario_bloque')->count()
                : 0,
            'bloques_sin_plantilla' => $this->auditoriaEstructura['resumen']['bloques_sin_plantilla'] ?? 0,
            'estructura_corregible' => (bool) ($this->auditoriaEstructura['resumen']['puede_corregir'] ?? false),
            'gestion' => $this->gestionTrabajo(),
        ];
    }

    private function recomendacionesSistema(): array
    {
        $recomendaciones = [];

        if (($this->auditoriaEstructura['resumen']['puede_corregir'] ?? false) === true) {
            $recomendaciones[] = [
                'tipo' => 'warning',
                'titulo' => 'Estructura horaria corregible',
                'mensaje' => 'Existen bloques sin plantilla o plantillas regulares faltantes. Puedes corregirlo sin eliminar datos.',
            ];
        }

        $gestion = $this->gestionTrabajo();

        if (! $gestion) {
            $recomendaciones[] = [
                'tipo' => 'warning',
                'titulo' => 'Gestión académica no detectada',
                'mensaje' => 'Activa o planifica una gestión para validar fechas de plantillas.',
            ];
        }

        if ($this->tablaExiste('plantilla_horaria')) {
            $invierno = DB::table('plantilla_horaria')
                ->where('tip_pho', 'INVIERNO')
                ->count();

            if ($invierno === 0) {
                $recomendaciones[] = [
                    'tipo' => 'info',
                    'titulo' => 'Horario de invierno pendiente',
                    'mensaje' => 'Puedes duplicar una plantilla regular como invierno y ajustar sus fechas.',
                ];
            }
        }

        if (empty($recomendaciones)) {
            $recomendaciones[] = [
                'tipo' => 'success',
                'titulo' => 'Configuración estable',
                'mensaje' => 'La estructura de turnos, plantillas y bloques no presenta errores críticos.',
            ];
        }

        return array_slice($recomendaciones, 0, 6);
    }

    private function gestionTrabajo(): ?array
    {
        if (! $this->tablaExiste('gestion_academica')) {
            return null;
        }

        $gestion = DB::table('gestion_academica')
            ->whereIn('est_gea', ['ACTIVA', 'ACTIVO'])
            ->orderByDesc('ani_gea')
            ->first();

        if (! $gestion) {
            $gestion = DB::table('gestion_academica')
                ->whereIn('est_gea', ['PLANIFICADA', 'PLANIFICADO'])
                ->orderByDesc('ani_gea')
                ->first();
        }

        if (! $gestion) {
            return null;
        }

        return [
            'anio' => $gestion->ani_gea ?? null,
            'inicio' => $gestion->fii_gea ?? null,
            'fin' => $gestion->ffi_gea ?? null,
            'estado' => $gestion->est_gea ?? null,
            'rango' => $this->formatearFecha($gestion->fii_gea ?? null) . ' - ' . $this->formatearFecha($gestion->ffi_gea ?? null),
        ];
    }

    // ============================================================
    // MAPEO DE REGISTROS
    // ============================================================

    private function mapearTurno(object $turno): array
    {
        $codTur = (string) $turno->cod_tur;

        $plantillas = $this->plantillasPorTurno($codTur);
        $regular = $plantillas->firstWhere('tipo', 'REGULAR');
        $invierno = $plantillas->firstWhere('tipo', 'INVIERNO');

        $analisis = $this->soporte->analizarTurno([
            'cod_tur' => $codTur,
            'nom_tur' => $turno->nom_tur ?? '',
            'hor_ini_tur' => $this->formatearHora($turno->hor_ini_tur ?? null),
            'hor_fin_tur' => $this->formatearHora($turno->hor_fin_tur ?? null),
            'est_tur' => $turno->est_tur ?? 'ACTIVO',
        ], $codTur, false);

        return [
            'cod_tur' => $codTur,
            'nombre' => $turno->nom_tur ?? 'Turno',
            'hora_inicio' => $this->formatearHora($turno->hor_ini_tur ?? null),
            'hora_fin' => $this->formatearHora($turno->hor_fin_tur ?? null),
            'rango' => $this->rangoTexto($turno->hor_ini_tur ?? null, $turno->hor_fin_tur ?? null),
            'estado' => $turno->est_tur ?? 'ACTIVO',
            'esta_activo' => ($turno->est_tur ?? 'ACTIVO') === 'ACTIVO',
            'plantillas' => $plantillas,
            'plantilla_regular' => $regular,
            'plantilla_invierno' => $invierno,
            'plantillas_total' => $plantillas->count(),
            'bloques_total' => $this->contarBloquesTurno($codTur),
            'bloques_sin_plantilla' => $this->contarBloquesSinPlantillaTurno($codTur),
            'horarios_total' => $this->contarHorariosTurno($codTur),
            'revision' => $analisis['estado'] ?? 'VALIDO',
            'mensaje_revision' => $analisis['mensaje'] ?? 'Turno validado.',
            'advertencias' => $analisis['advertencias'] ?? [],
            'sugerencias' => $analisis['sugerencias'] ?? [],
        ];
    }

    private function mapearPlantilla(object $plantilla): array
    {
        $codPho = (string) $plantilla->cod_pho;
        $turno = $this->buscarTurno($plantilla->cod_tur ?? null);

        return [
            'cod_pho' => $codPho,
            'cod_tur' => $plantilla->cod_tur ?? null,
            'turno' => $turno,
            'nombre' => $plantilla->nom_pho ?? 'Plantilla horaria',
            'tipo' => $plantilla->tip_pho ?? 'REGULAR',
            'descripcion' => $plantilla->des_pho ?? null,
            'fecha_inicio' => $plantilla->fec_ini_pho ?? null,
            'fecha_fin' => $plantilla->fec_fin_pho ?? null,
            'vigencia' => $this->vigenciaTexto($plantilla->fec_ini_pho ?? null, $plantilla->fec_fin_pho ?? null),
            'duracion_base' => $plantilla->dur_blo_pho ?? null,
            'orden' => $plantilla->ord_pho ?? 1,
            'aplicada' => (bool) ($plantilla->act_pho ?? false),
            'activa' => (bool) ($plantilla->est_pho ?? true),
            'bloques_total' => $this->contarBloquesPlantilla($codPho),
            'detalles_total' => $this->contarDetallesPlantilla($codPho),
            'uso_academico' => $this->contarDetallesPlantilla($codPho) > 0
                ? $this->contarDetallesPlantilla($codPho) . ' registros en horario'
                : 'Sin uso académico',
        ];
    }

    private function mapearBloque(object $bloque): array
    {
        return [
            'cod_hbl' => $bloque->cod_hbl ?? null,
            'cod_tur' => $bloque->cod_tur ?? null,
            'cod_pho' => $bloque->cod_pho ?? null,
            'numero' => $bloque->num_hbl ?? null,
            'nombre' => $bloque->nom_hbl ?? 'Bloque',
            'tipo' => $bloque->tip_hbl ?? 'CLASE',
            'observacion' => $bloque->obs_hbl ?? null,
            'hora_inicio' => $this->formatearHora($bloque->hor_ini_hbl ?? null),
            'hora_fin' => $this->formatearHora($bloque->hor_fin_hbl ?? null),
            'rango' => $this->rangoTexto($bloque->hor_ini_hbl ?? null, $bloque->hor_fin_hbl ?? null),
            'duracion' => $this->duracionMinutos($bloque->hor_ini_hbl ?? null, $bloque->hor_fin_hbl ?? null),
            'estado' => $bloque->est_hbl ?? 'ACTIVO',
            'esta_activo' => ($bloque->est_hbl ?? 'ACTIVO') === 'ACTIVO',
            'tiene_uso' => $this->bloqueTieneUso($bloque->cod_hbl ?? null),
        ];
    }

    private function buscarTurno(?string $codTur): ?array
    {
        if (! $codTur || ! $this->tablaExiste('turno')) {
            return null;
        }

        $turno = DB::table('turno')
            ->where('cod_tur', $codTur)
            ->first();

        if (! $turno) {
            return null;
        }

        return [
            'cod_tur' => $turno->cod_tur,
            'nombre' => $turno->nom_tur ?? 'Turno',
            'hora_inicio' => $this->formatearHora($turno->hor_ini_tur ?? null),
            'hora_fin' => $this->formatearHora($turno->hor_fin_tur ?? null),
            'rango' => $this->rangoTexto($turno->hor_ini_tur ?? null, $turno->hor_fin_tur ?? null),
            'estado' => $turno->est_tur ?? 'ACTIVO',
        ];
    }

    private function buscarPlantilla(?string $codPho): ?array
    {
        if (! $codPho || ! $this->tablaExiste('plantilla_horaria')) {
            return null;
        }

        $plantilla = DB::table('plantilla_horaria')
            ->where('cod_pho', $codPho)
            ->first();

        return $plantilla ? $this->mapearPlantilla($plantilla) : null;
    }

    private function buscarBloque(?string $codHbl): ?array
    {
        if (! $codHbl || ! $this->tablaExiste('horario_bloque')) {
            return null;
        }

        $bloque = DB::table('horario_bloque')
            ->where('cod_hbl', $codHbl)
            ->first();

        return $bloque ? $this->mapearBloque($bloque) : null;
    }

    private function detalleTurno(string $codTur): array
    {
        return [
            'turno' => $this->buscarTurno($codTur),
            'plantillas' => $this->plantillasPorTurno($codTur)->values()->all(),
            'bloques' => $this->bloquesPorTurno($codTur)->values()->all(),
            'uso' => [
                'plantillas' => $this->contarPlantillasTurno($codTur),
                'bloques' => $this->contarBloquesTurno($codTur),
                'bloques_sin_plantilla' => $this->contarBloquesSinPlantillaTurno($codTur),
                'horarios' => $this->contarHorariosTurno($codTur),
            ],
        ];
    }

    // ============================================================
    // COLECCIONES AUXILIARES
    // ============================================================

    private function plantillasPorTurno(string $codTur): Collection
    {
        if (! $this->tablaExiste('plantilla_horaria')) {
            return collect();
        }

        return DB::table('plantilla_horaria')
            ->where('cod_tur', $codTur)
            ->orderByRaw("CASE WHEN tip_pho = 'REGULAR' THEN 0 WHEN tip_pho = 'INVIERNO' THEN 1 ELSE 2 END")
            ->orderByDesc('act_pho')
            ->orderBy('ord_pho')
            ->get()
            ->map(fn($plantilla) => $this->mapearPlantilla($plantilla));
    }

    private function bloquesPorTurno(string $codTur): Collection
    {
        if (! $this->tablaExiste('horario_bloque')) {
            return collect();
        }

        return DB::table('horario_bloque')
            ->where('cod_tur', $codTur)
            ->orderBy('num_hbl')
            ->orderBy('hor_ini_hbl')
            ->get()
            ->map(fn($bloque) => $this->mapearBloque($bloque));
    }

    // ============================================================
    // REGLAS DE VALIDACIÓN
    // ============================================================

    private function reglasTurno(): array
    {
        return [
            'formTurno.nom_tur' => ['required', 'string', 'min:3', 'max:100'],
            'formTurno.hor_ini_tur' => ['required', 'date_format:H:i'],
            'formTurno.hor_fin_tur' => ['required', 'date_format:H:i'],
            'formTurno.est_tur' => ['required', Rule::in(['ACTIVO', 'INACTIVO'])],
        ];
    }

    private function reglasPlantilla(): array
    {
        $reglas = [
            'formPlantilla.cod_tur' => ['required', 'string', Rule::exists('turno', 'cod_tur')],
            'formPlantilla.nom_pho' => ['required', 'string', 'min:3', 'max:120'],
            'formPlantilla.tip_pho' => ['required', Rule::in(['REGULAR', 'INVIERNO', 'AJUSTE', 'EMERGENCIA'])],
            'formPlantilla.des_pho' => ['nullable', 'string', 'max:800'],
            'formPlantilla.fec_ini_pho' => ['nullable', 'date'],
            'formPlantilla.fec_fin_pho' => ['nullable', 'date', 'after_or_equal:formPlantilla.fec_ini_pho'],
            'formPlantilla.dur_blo_pho' => ['required', 'integer', 'min:5', 'max:120'],
            'formPlantilla.ord_pho' => ['required', 'integer', 'min:1', 'max:99'],
            'formPlantilla.act_pho' => ['boolean'],
            'formPlantilla.est_pho' => ['boolean'],
        ];

        if (($this->formPlantilla['tip_pho'] ?? null) === 'INVIERNO') {
            $reglas['formPlantilla.fec_ini_pho'] = ['required', 'date'];
            $reglas['formPlantilla.fec_fin_pho'] = ['required', 'date', 'after_or_equal:formPlantilla.fec_ini_pho'];
        }

        return $reglas;
    }

    private function reglasBloque(): array
    {
        return [
            'formBloque.cod_tur' => ['required', 'string', Rule::exists('turno', 'cod_tur')],
            'formBloque.cod_pho' => ['required', 'string', Rule::exists('plantilla_horaria', 'cod_pho')],
            'formBloque.num_hbl' => ['required', 'integer', 'min:1', 'max:30'],
            'formBloque.hor_ini_hbl' => ['required', 'date_format:H:i'],
            'formBloque.hor_fin_hbl' => ['required', 'date_format:H:i'],
            'formBloque.nom_hbl' => ['nullable', 'string', 'max:100'],
            'formBloque.tip_hbl' => ['required', Rule::in(['CLASE', 'RECREO', 'DESCANSO', 'FORMACION', 'SALIDA', 'OTRO'])],
            'formBloque.obs_hbl' => ['nullable', 'string', 'max:500'],
            'formBloque.est_hbl' => ['required', Rule::in(['ACTIVO', 'INACTIVO'])],
        ];
    }

    private function atributosTurno(): array
    {
        return [
            'formTurno.nom_tur' => 'nombre del turno',
            'formTurno.hor_ini_tur' => 'hora de inicio',
            'formTurno.hor_fin_tur' => 'hora de finalización',
            'formTurno.est_tur' => 'estado',
        ];
    }

    private function atributosPlantilla(): array
    {
        return [
            'formPlantilla.cod_tur' => 'turno',
            'formPlantilla.nom_pho' => 'nombre de la plantilla',
            'formPlantilla.tip_pho' => 'tipo de plantilla',
            'formPlantilla.des_pho' => 'descripción',
            'formPlantilla.fec_ini_pho' => 'fecha de inicio',
            'formPlantilla.fec_fin_pho' => 'fecha de fin',
            'formPlantilla.dur_blo_pho' => 'duración base',
            'formPlantilla.ord_pho' => 'orden',
            'formPlantilla.act_pho' => 'aplicación actual',
            'formPlantilla.est_pho' => 'estado',
        ];
    }

    private function atributosBloque(): array
    {
        return [
            'formBloque.cod_tur' => 'turno',
            'formBloque.cod_pho' => 'plantilla horaria',
            'formBloque.num_hbl' => 'número de bloque',
            'formBloque.hor_ini_hbl' => 'hora de inicio',
            'formBloque.hor_fin_hbl' => 'hora de finalización',
            'formBloque.nom_hbl' => 'nombre del bloque',
            'formBloque.tip_hbl' => 'tipo de bloque',
            'formBloque.obs_hbl' => 'observación',
            'formBloque.est_hbl' => 'estado',
        ];
    }

    // ============================================================
    // CONTADORES
    // ============================================================

    private function contarPlantillasTurno(string $codTur): int
    {
        return $this->tablaExiste('plantilla_horaria')
            ? DB::table('plantilla_horaria')->where('cod_tur', $codTur)->count()
            : 0;
    }

    private function contarBloquesTurno(string $codTur): int
    {
        return $this->tablaExiste('horario_bloque')
            ? DB::table('horario_bloque')->where('cod_tur', $codTur)->count()
            : 0;
    }

    private function contarBloquesSinPlantillaTurno(string $codTur): int
    {
        if (! $this->tablaExiste('horario_bloque') || ! Schema::hasColumn('horario_bloque', 'cod_pho')) {
            return 0;
        }

        return DB::table('horario_bloque')
            ->where('cod_tur', $codTur)
            ->where(function ($query) {
                $query->whereNull('cod_pho')
                    ->orWhere('cod_pho', '');
            })
            ->count();
    }

    private function contarBloquesPlantilla(string $codPho): int
    {
        return $this->tablaExiste('horario_bloque')
            ? DB::table('horario_bloque')->where('cod_pho', $codPho)->count()
            : 0;
    }

    private function contarHorariosTurno(string $codTur): int
    {
        return $this->tablaExiste('horario') && Schema::hasColumn('horario', 'cod_tur')
            ? DB::table('horario')->where('cod_tur', $codTur)->count()
            : 0;
    }

    private function contarDetallesPlantilla(string $codPho): int
    {
        if (! $this->tablaExiste('horario_bloque') || ! $this->tablaExiste('horario_detalle')) {
            return 0;
        }

        $bloques = DB::table('horario_bloque')
            ->where('cod_pho', $codPho)
            ->pluck('cod_hbl');

        if ($bloques->isEmpty()) {
            return 0;
        }

        return DB::table('horario_detalle')
            ->whereIn('cod_hbl', $bloques)
            ->count();
    }

    private function bloqueTieneUso(?string $codHbl): bool
    {
        if (! $codHbl || ! $this->tablaExiste('horario_detalle')) {
            return false;
        }

        return DB::table('horario_detalle')
            ->where('cod_hbl', $codHbl)
            ->exists();
    }

    // ============================================================
    // LIMPIEZA DE FORMULARIOS
    // ============================================================

    private function limpiarFormularioTurno(): void
    {
        $this->formTurno = [
            'cod_tur' => null,
            'nom_tur' => '',
            'hor_ini_tur' => '',
            'hor_fin_tur' => '',
            'est_tur' => 'ACTIVO',
        ];
    }

    private function limpiarFormularioPlantilla(): void
    {
        $this->formPlantilla = [
            'cod_pho' => null,
            'cod_tur' => '',
            'nom_pho' => '',
            'tip_pho' => 'REGULAR',
            'des_pho' => '',
            'fec_ini_pho' => '',
            'fec_fin_pho' => '',
            'dur_blo_pho' => 45,
            'ord_pho' => 1,
            'act_pho' => false,
            'est_pho' => true,
        ];
    }

    private function limpiarFormularioBloque(): void
    {
        $this->formBloque = [
            'cod_hbl' => null,
            'cod_tur' => '',
            'cod_pho' => '',
            'num_hbl' => '',
            'hor_ini_hbl' => '',
            'hor_fin_hbl' => '',
            'nom_hbl' => '',
            'tip_hbl' => 'CLASE',
            'obs_hbl' => '',
            'est_hbl' => 'ACTIVO',
        ];
    }

    // ============================================================
    // UTILIDADES
    // ============================================================

    private function seleccionarPlantillaInicial(bool $forzar = false): void
    {
        if (! $forzar && $this->plantillaSeleccionada) {
            return;
        }

        if (! $this->tablaExiste('plantilla_horaria')) {
            return;
        }

        $this->plantillaSeleccionada = DB::table('plantilla_horaria')
            ->orderByDesc('act_pho')
            ->orderByRaw("CASE WHEN tip_pho = 'REGULAR' THEN 0 WHEN tip_pho = 'INVIERNO' THEN 1 ELSE 2 END")
            ->orderBy('ord_pho')
            ->value('cod_pho');
    }

    private function siguienteNumeroBloque(string $codPho): int
    {
        if (! $this->tablaExiste('horario_bloque')) {
            return 1;
        }

        return ((int) DB::table('horario_bloque')
            ->where('cod_pho', $codPho)
            ->max('num_hbl')) + 1;
    }

    private function nombreSugeridoBloque(int $numero): string
    {
        return match ($numero) {
            1 => '1er bloque',
            2 => '2do bloque',
            3 => '3er bloque',
            4 => '4to bloque',
            5 => '5to bloque',
            6 => '6to bloque',
            7 => '7mo bloque',
            8 => '8vo bloque',
            default => "{$numero}° bloque",
        };
    }

    private function duracionInviernoParaBloque(object $bloque): int
    {
        $tipo = $bloque->tip_hbl ?? 'CLASE';

        return match ($tipo) {
            'RECREO' => 10,
            'DESCANSO' => 10,
            'SALIDA' => 5,
            default => 30,
        };
    }

    private function calcularHoraFin(?string $inicio, int $duracion): ?string
    {
        if (! $inicio) {
            return null;
        }

        try {
            return Carbon::createFromFormat('H:i', $inicio)
                ->addMinutes($duracion)
                ->format('H:i');
        } catch (Throwable) {
            return null;
        }
    }

    private function generarCodigo(string $tabla, string $columna, string $prefijo): string
    {
        $ultimo = DB::table($tabla)
            ->where($columna, 'like', "{$prefijo}_%")
            ->orderByDesc($columna)
            ->value($columna);

        $numero = $ultimo
            ? (int) str_replace("{$prefijo}_", '', $ultimo)
            : 0;

        do {
            $numero++;
            $codigo = $prefijo . '_' . str_pad((string) $numero, 4, '0', STR_PAD_LEFT);
        } while (DB::table($tabla)->where($columna, $codigo)->exists());

        return $codigo;
    }

    private function filtrarColumnas(string $tabla, array $datos): array
    {
        if (! $this->tablaExiste($tabla)) {
            return [];
        }

        return collect($datos)
            ->filter(fn($value, $key) => Schema::hasColumn($tabla, $key))
            ->all();
    }

    private function tablaExiste(string $tabla): bool
    {
        try {
            return Schema::hasTable($tabla);
        } catch (Throwable) {
            return false;
        }
    }

    private function tablaPorTipo(string $tipo): string
    {
        return match ($tipo) {
            'turno' => 'turno',
            'plantilla' => 'plantilla_horaria',
            'bloque' => 'horario_bloque',
            default => 'registro',
        };
    }

    private function formatearHora(mixed $hora): ?string
    {
        if (! $hora) {
            return null;
        }

        try {
            return Carbon::parse($hora)->format('H:i');
        } catch (Throwable) {
            $hora = trim((string) $hora);

            if (preg_match('/^([01]?\d|2[0-3]):([0-5]\d)(:[0-5]\d)?$/', $hora, $m)) {
                return str_pad((string) $m[1], 2, '0', STR_PAD_LEFT) . ':' . $m[2];
            }

            return null;
        }
    }

    private function formatearFecha(mixed $fecha): string
    {
        if (! $fecha) {
            return 'Sin fecha';
        }

        try {
            return Carbon::parse($fecha)->format('d/m/Y');
        } catch (Throwable) {
            return 'Sin fecha';
        }
    }

    private function rangoTexto(mixed $inicio, mixed $fin): string
    {
        $inicio = $this->formatearHora($inicio);
        $fin = $this->formatearHora($fin);

        if (! $inicio || ! $fin) {
            return 'Sin rango definido';
        }

        return "{$inicio} - {$fin}";
    }

    private function vigenciaTexto(?string $inicio, ?string $fin): string
    {
        if (! $inicio && ! $fin) {
            return 'Base anual';
        }

        return $this->formatearFecha($inicio) . ' - ' . $this->formatearFecha($fin);
    }

    private function duracionMinutos(mixed $inicio, mixed $fin): ?int
    {
        $inicio = $this->formatearHora($inicio);
        $fin = $this->formatearHora($fin);

        if (! $inicio || ! $fin) {
            return null;
        }

        [$h1, $m1] = explode(':', $inicio);
        [$h2, $m2] = explode(':', $fin);

        return (((int) $h2 * 60) + (int) $m2) - (((int) $h1 * 60) + (int) $m1);
    }

    private function analisisInicial(string $mensaje): array
    {
        return [
            'puede_continuar' => false,
            'estado' => 'INCOMPLETO',
            'estado_inteligente' => 'INCOMPLETO',
            'nivel_riesgo' => 'BAJO',
            'mensaje' => $mensaje,
            'bloqueos' => [],
            'advertencias' => [],
            'sugerencias' => [],
            'resumen' => [],
        ];
    }

    private function registrarBitacora(string $accion, string $tabla, string $registro, string $descripcion): void
    {
        try {
            if (function_exists('activity')) {
                activity()
                    ->causedBy(auth()->user())
                    ->withProperties([
                        'modulo' => 'Gestión de Turnos',
                        'tabla' => $tabla,
                        'registro' => $registro,
                        'descripcion' => $descripcion,
                    ])
                    ->log($accion . ' - ' . $descripcion);
            }
        } catch (Throwable) {
            //
        }

        if (! $this->tablaExiste('bitacora')) {
            return;
        }

        try {
            $payload = [
                'cod_bit' => $this->generarCodigo('bitacora', 'cod_bit', 'BIT'),
                'acc_bit' => $accion,
                'tab_bit' => $tabla,
                'reg_bit' => $registro,
                'cod_usu' => auth()->user()->cod_usu ?? auth()->id(),
                'fec_bit' => now(),
                'est_bit' => 'ACTIVO',
            ];

            DB::table('bitacora')->insert($this->filtrarColumnas('bitacora', $payload));
        } catch (Throwable) {
            //
        }
    }

    // ============================================================
    // REPORTES
    // ============================================================

    public function exportarTurnosPdf(): void
    {
        $this->registrarBitacora(
            accion: 'EXPORTAR',
            tabla: 'turno',
            registro: 'REPORTE_TURNOS',
            descripcion: 'Solicitud de reporte PDF de turnos.'
        );

        $this->dispatch('error-general', mensaje: 'El reporte PDF de turnos será conectado en la siguiente etapa.');
    }

    public function exportarPlantillasPdf(): void
    {
        $this->registrarBitacora(
            accion: 'EXPORTAR',
            tabla: 'plantilla_horaria',
            registro: 'REPORTE_PLANTILLAS',
            descripcion: 'Solicitud de reporte PDF de plantillas horarias.'
        );

        $this->dispatch('error-general', mensaje: 'El reporte PDF de plantillas será conectado en la siguiente etapa.');
    }

    public function exportarBloquesPdf(): void
    {
        $this->registrarBitacora(
            accion: 'EXPORTAR',
            tabla: 'horario_bloque',
            registro: 'REPORTE_BLOQUES',
            descripcion: 'Solicitud de reporte PDF de bloques horarios.'
        );

        $this->dispatch('error-general', mensaje: 'El reporte PDF de bloques será conectado en la siguiente etapa.');
    }
}
