<?php

namespace App\Livewire\Admin;

use App\Models\Bitacora;
use App\Models\Paralelo;
use App\Services\BitacoraService;
use App\Support\Academico\ParaleloInteligente;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;
use Throwable;

class GestionParalelo extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'tailwind';

    /*
    |--------------------------------------------------------------------------
    | Tabla y filtros
    |--------------------------------------------------------------------------
    */

    public string $search = '';
    public string $estado = '';
    public string $usoAcademico = '';
    public string $impacto = '';
    public int $perPage = 10;

    public string $sortField = 'nom_par';
    public string $sortDirection = 'asc';

    /*
    |--------------------------------------------------------------------------
    | Modales
    |--------------------------------------------------------------------------
    */

    public bool $modalCrear = false;
    public bool $modalEditar = false;
    public bool $modalDetalle = false;
    public bool $modalCatalogo = false;
    public bool $modalHistoricos = false;

    /*
    |--------------------------------------------------------------------------
    | Formularios
    |--------------------------------------------------------------------------
    */

    public array $form = [
        'nom_par' => '',
        'est_par' => 'ACTIVO',
    ];

    public array $formEditar = [
        'cod_par' => '',
        'nom_par' => '',
        'est_par' => 'ACTIVO',
    ];

    /*
    |--------------------------------------------------------------------------
    | Análisis inteligente
    |--------------------------------------------------------------------------
    */

    public array $analisisCrear = [];
    public array $analisisEditar = [];
    public bool $puedeGuardarCrear = false;
    public ?string $bloqueoCrearMensaje = null;

    /*
    |--------------------------------------------------------------------------
    | Detalle
    |--------------------------------------------------------------------------
    */

    public ?string $paraleloSeleccionado = null;
    public array $detalleParalelo = [];

    /*
    |--------------------------------------------------------------------------
    | Catálogos
    |--------------------------------------------------------------------------
    */

    public array $estadosDisponibles = [
        'ACTIVO' => 'Activo',
        'INACTIVO' => 'Inactivo',
    ];

    public array $opcionesUsoAcademico = [
        '' => 'Todos',
        'CON_ESTUDIANTES' => 'Con estudiantes',
        'SIN_ESTUDIANTES' => 'Sin estudiantes',
        'CON_PLANIFICACION' => 'Con planificación',
        'SIN_USO' => 'Sin uso académico',
        'HISTORICO' => 'Históricos recuperables',
    ];

    public array $opcionesImpacto = [
        '' => 'Todos',
        'ALTO' => 'Uso alto',
        'MEDIO' => 'Uso medio',
        'BAJO' => 'Uso bajo',
        'SIN_USO' => 'Sin uso',
        'HISTORICO' => 'Histórico',
    ];

    /*
    |--------------------------------------------------------------------------
    | Ciclo de vida
    |--------------------------------------------------------------------------
    */

    public function mount(): void
    {
        $this->reiniciarAnalisisCrear();
        $this->reiniciarAnalisisEditar();
    }

    public function render()
    {
        $paralelos = $this->obtenerParalelosPaginados();

        return view('livewire.admin.gestion-paralelo', [
            'paralelos' => $paralelos,
            'resumen' => $this->obtenerResumen(),
            'distribucion' => $this->obtenerDistribucionEstudiantil(),
            'recomendaciones' => $this->obtenerRecomendacionesSistema(),
            'catalogoSugerido' => ParaleloInteligente::catalogoSugerido(),
            'historicos' => $this->obtenerHistoricosRecuperables(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Filtros
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

    public function updatingUsoAcademico(): void
    {
        $this->resetPage();
    }

    public function updatingImpacto(): void
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
            'usoAcademico',
            'impacto',
        ]);

        $this->perPage = 10;
        $this->sortField = 'nom_par';
        $this->sortDirection = 'asc';

        $this->resetPage();
    }

    public function ordenarPor(string $campo): void
    {
        $permitidos = [
            'nom_par',
            'est_par',
        ];

        if (! in_array($campo, $permitidos, true)) {
            return;
        }

        if ($this->sortField === $campo) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $campo;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    /*
    |--------------------------------------------------------------------------
    | Consultas principales
    |--------------------------------------------------------------------------
    */

    private function obtenerParalelosPaginados(): LengthAwarePaginator
    {
        $paginados = $this->paralelosQuery()->paginate($this->perPage);

        $paginados->getCollection()->transform(function (Paralelo $paralelo) {
            return $this->enriquecerParalelo($paralelo);
        });

        if ($this->impacto !== '') {
            $filtrados = $paginados->getCollection()
                ->filter(fn(Paralelo $paralelo) => ($paralelo->impacto_academico['nivel'] ?? '') === $this->impacto)
                ->values();

            $paginados->setCollection($filtrados);
        }

        return $paginados;
    }

    private function paralelosQuery(): Builder
    {
        $query = Paralelo::query();

        if (trim($this->search) !== '') {
            $busqueda = trim($this->search);

            $query->where(function (Builder $subQuery) use ($busqueda) {
                $subQuery->where('nom_par', 'like', '%' . $busqueda . '%');
            });
        }

        if ($this->estado !== '') {
            $query->where('est_par', $this->estado);
        }

        $this->aplicarFiltroUsoAcademico($query);

        return $query->orderBy($this->sortField, $this->sortDirection);
    }

    private function aplicarFiltroUsoAcademico(Builder $query): void
    {
        if ($this->usoAcademico === '') {
            return;
        }

        if ($this->usoAcademico === 'CON_ESTUDIANTES') {
            $this->whereExisteEnTablas($query, $this->tablasInscripcionDisponibles());
            return;
        }

        if ($this->usoAcademico === 'SIN_ESTUDIANTES') {
            $this->whereNoExisteEnTablas($query, $this->tablasInscripcionDisponibles());
            return;
        }

        if ($this->usoAcademico === 'CON_PLANIFICACION') {
            $this->whereExisteEnTablas($query, ['plan_asignatura', 'plan_especialidad']);
            return;
        }

        if ($this->usoAcademico === 'SIN_USO') {
            $this->whereNoExisteEnTablas($query, array_merge(
                $this->tablasInscripcionDisponibles(),
                ['plan_asignatura', 'plan_especialidad', 'horario_detalle']
            ));
            return;
        }

        if ($this->usoAcademico === 'HISTORICO') {
            $query->where('est_par', 'INACTIVO');
        }
    }

    private function whereExisteEnTablas(Builder $query, array $tablas): void
    {
        $query->where(function (Builder $subQuery) use ($tablas) {
            foreach ($tablas as $tabla) {
                if ($this->tablaTieneColumna($tabla, 'cod_par')) {
                    $subQuery->orWhereExists(function ($exists) use ($tabla) {
                        $exists
                            ->selectRaw('1')
                            ->from($tabla)
                            ->whereColumn($tabla . '.cod_par', 'paralelo.cod_par');
                    });
                }
            }
        });
    }

    private function whereNoExisteEnTablas(Builder $query, array $tablas): void
    {
        foreach ($tablas as $tabla) {
            if ($this->tablaTieneColumna($tabla, 'cod_par')) {
                $query->whereNotExists(function ($subQuery) use ($tabla) {
                    $subQuery
                        ->selectRaw('1')
                        ->from($tabla)
                        ->whereColumn($tabla . '.cod_par', 'paralelo.cod_par');
                });
            }
        }
    }

    private function enriquecerParalelo(Paralelo $paralelo): Paralelo
    {
        $uso = $this->obtenerUsoAcademico($paralelo->cod_par);
        $impacto = $this->calcularImpacto($paralelo, $uso);
        $bitacora = $this->obtenerUltimaBitacora($paralelo->cod_par);
        $analisis = ParaleloInteligente::interpretar($paralelo->nom_par, []);

        $paralelo->uso_academico = $uso;
        $paralelo->impacto_academico = $impacto;
        $paralelo->ultima_bitacora = $bitacora;
        $paralelo->analisis_inteligente = $analisis;
        $paralelo->disponibilidad = $this->obtenerDisponibilidad($paralelo, $uso);

        return $paralelo;
    }

    /*
    |--------------------------------------------------------------------------
    | Uso académico
    |--------------------------------------------------------------------------
    */

    public function obtenerUsoAcademico(string $codPar): array
    {
        $estudiantes = $this->contarEstudiantes($codPar);
        $inscripciones = $this->contarInscripciones($codPar);
        $planesAsignatura = $this->contarTabla('plan_asignatura', 'cod_par', $codPar);
        $planesEspecialidad = $this->contarTabla('plan_especialidad', 'cod_par', $codPar);
        $horarios = $this->contarHorarios($codPar);
        $cursos = $this->contarCursosVinculados($codPar);

        $total = $inscripciones + $planesAsignatura + $planesEspecialidad + $horarios;

        return [
            'estudiantes' => $estudiantes,
            'inscripciones' => $inscripciones,
            'planes_asignatura' => $planesAsignatura,
            'planes_especialidad' => $planesEspecialidad,
            'horarios' => $horarios,
            'cursos' => $cursos,
            'total' => $total,
            'tiene_estudiantes' => $estudiantes > 0,
            'tiene_planificacion' => ($planesAsignatura + $planesEspecialidad + $horarios) > 0,
            'tiene_uso' => $total > 0 || $estudiantes > 0,
            'texto' => $this->textoUsoAcademico($estudiantes, $planesAsignatura, $planesEspecialidad, $horarios),
        ];
    }

    private function contarEstudiantes(string $codPar): int
    {
        $total = 0;

        foreach ($this->tablasInscripcionDisponibles() as $tabla) {
            if (! $this->tablaTieneColumna($tabla, 'cod_par')) {
                continue;
            }

            $columnaEstudiante = $this->primeraColumnaDisponible($tabla, [
                'cod_est',
                'cod_estu',
                'cod_estudiante',
                'cod_per',
            ]);

            if ($columnaEstudiante !== null) {
                $total += DB::table($tabla)
                    ->where('cod_par', $codPar)
                    ->whereNotNull($columnaEstudiante)
                    ->distinct()
                    ->count($columnaEstudiante);
            } else {
                $total += DB::table($tabla)
                    ->where('cod_par', $codPar)
                    ->count();
            }
        }

        return $total;
    }

    private function contarInscripciones(string $codPar): int
    {
        $total = 0;

        foreach ($this->tablasInscripcionDisponibles() as $tabla) {
            if ($this->tablaTieneColumna($tabla, 'cod_par')) {
                $total += DB::table($tabla)
                    ->where('cod_par', $codPar)
                    ->count();
            }
        }

        return $total;
    }

    private function contarCursosVinculados(string $codPar): int
    {
        $codigos = collect();

        foreach (array_merge($this->tablasInscripcionDisponibles(), ['plan_asignatura', 'plan_especialidad']) as $tabla) {
            if ($this->tablaTieneColumna($tabla, 'cod_par') && $this->tablaTieneColumna($tabla, 'cod_cur')) {
                $codigos = $codigos->merge(
                    DB::table($tabla)
                        ->where('cod_par', $codPar)
                        ->whereNotNull('cod_cur')
                        ->pluck('cod_cur')
                );
            }
        }

        return $codigos->unique()->count();
    }

    private function contarHorarios(string $codPar): int
    {
        if (! Schema::hasTable('horario_detalle')) {
            return 0;
        }

        $total = 0;

        if (Schema::hasColumn('horario_detalle', 'cod_par')) {
            $total += DB::table('horario_detalle')
                ->where('cod_par', $codPar)
                ->count();
        }

        if (Schema::hasColumn('horario_detalle', 'cod_pas') && Schema::hasTable('plan_asignatura')) {
            $planes = DB::table('plan_asignatura')
                ->where('cod_par', $codPar)
                ->pluck('cod_pas')
                ->filter()
                ->values();

            if ($planes->isNotEmpty()) {
                $total += DB::table('horario_detalle')
                    ->whereIn('cod_pas', $planes)
                    ->count();
            }
        }

        if (Schema::hasColumn('horario_detalle', 'cod_pes') && Schema::hasTable('plan_especialidad')) {
            $planes = DB::table('plan_especialidad')
                ->where('cod_par', $codPar)
                ->pluck('cod_pes')
                ->filter()
                ->values();

            if ($planes->isNotEmpty()) {
                $total += DB::table('horario_detalle')
                    ->whereIn('cod_pes', $planes)
                    ->count();
            }
        }

        return $total;
    }

    private function contarTabla(string $tabla, string $columna, string $valor): int
    {
        if (! $this->tablaTieneColumna($tabla, $columna)) {
            return 0;
        }

        return DB::table($tabla)
            ->where($columna, $valor)
            ->count();
    }

    private function textoUsoAcademico(int $estudiantes, int $planesAsignatura, int $planesEspecialidad, int $horarios): string
    {
        $partes = [];

        if ($estudiantes > 0) {
            $partes[] = $estudiantes . ' estudiante' . ($estudiantes === 1 ? '' : 's');
        }

        if ($planesAsignatura > 0) {
            $partes[] = $planesAsignatura . ' plan' . ($planesAsignatura === 1 ? '' : 'es') . ' de asignatura';
        }

        if ($planesEspecialidad > 0) {
            $partes[] = $planesEspecialidad . ' plan' . ($planesEspecialidad === 1 ? '' : 'es') . ' de especialidad';
        }

        if ($horarios > 0) {
            $partes[] = $horarios . ' horario' . ($horarios === 1 ? '' : 's');
        }

        return count($partes) > 0 ? implode(' / ', $partes) : 'Sin uso académico';
    }

    private function calcularImpacto(Paralelo $paralelo, array $uso): array
    {
        if ($paralelo->est_par === 'INACTIVO') {
            return [
                'nivel' => 'HISTORICO',
                'texto' => 'Histórico',
                'descripcion' => 'Paralelo inactivo con historial recuperable.',
            ];
        }

        $peso = ((int) $uso['estudiantes'] * 2)
            + ((int) $uso['planes_asignatura'] * 3)
            + ((int) $uso['planes_especialidad'] * 3)
            + ((int) $uso['horarios'] * 2);

        if ($peso === 0) {
            return [
                'nivel' => 'SIN_USO',
                'texto' => 'Sin uso',
                'descripcion' => 'No tiene estudiantes ni planificación vinculada.',
            ];
        }

        if ($peso >= 60) {
            return [
                'nivel' => 'ALTO',
                'texto' => 'Uso alto',
                'descripcion' => 'Tiene alta participación estudiantil o planificación activa.',
            ];
        }

        if ($peso >= 15) {
            return [
                'nivel' => 'MEDIO',
                'texto' => 'Uso medio',
                'descripcion' => 'Tiene uso académico relevante.',
            ];
        }

        return [
            'nivel' => 'BAJO',
            'texto' => 'Uso bajo',
            'descripcion' => 'Tiene uso académico reducido.',
        ];
    }

    private function obtenerDisponibilidad(Paralelo $paralelo, array $uso): array
    {
        if ($paralelo->est_par === 'INACTIVO') {
            return [
                'estado' => 'HISTORICO',
                'texto' => 'Histórico recuperable',
                'descripcion' => 'No aparece en selectores operativos, pero puede reactivarse.',
            ];
        }

        if (($uso['estudiantes'] ?? 0) === 0 && ! ($uso['tiene_planificacion'] ?? false)) {
            return [
                'estado' => 'SIN_USO',
                'texto' => 'Sin uso actual',
                'descripcion' => 'Puede revisarse para desactivación.',
            ];
        }

        return [
            'estado' => 'DISPONIBLE',
            'texto' => 'Disponible',
            'descripcion' => 'Disponible para planificación académica.',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Resumen y distribución
    |--------------------------------------------------------------------------
    */

    private function obtenerResumen(): array
    {
        $total = Paralelo::count();
        $activos = Paralelo::where('est_par', 'ACTIVO')->count();
        $inactivos = Paralelo::where('est_par', 'INACTIVO')->count();

        $estudiantes = 0;
        $conEstudiantes = 0;
        $sinUso = 0;
        $planesVinculados = 0;

        Paralelo::query()->get()->each(function (Paralelo $paralelo) use (&$estudiantes, &$conEstudiantes, &$sinUso, &$planesVinculados) {
            $uso = $this->obtenerUsoAcademico($paralelo->cod_par);

            $estudiantes += (int) $uso['estudiantes'];
            $planesVinculados += (int) $uso['planes_asignatura'] + (int) $uso['planes_especialidad'];

            if ((int) $uso['estudiantes'] > 0) {
                $conEstudiantes++;
            }

            if (! ($uso['tiene_uso'] ?? false) && $paralelo->est_par === 'ACTIVO') {
                $sinUso++;
            }
        });

        return [
            'total' => $total,
            'activos' => $activos,
            'historicos' => $inactivos,
            'estudiantes' => $estudiantes,
            'con_estudiantes' => $conEstudiantes,
            'sin_uso' => $sinUso,
            'planes_vinculados' => $planesVinculados,
        ];
    }

    private function obtenerDistribucionEstudiantil(): array
    {
        return Paralelo::query()
            ->where('est_par', 'ACTIVO')
            ->orderBy('nom_par')
            ->get()
            ->map(function (Paralelo $paralelo) {
                $uso = $this->obtenerUsoAcademico($paralelo->cod_par);

                return [
                    'nombre' => 'Paralelo ' . $paralelo->nom_par,
                    'valor' => (int) $uso['estudiantes'],
                    'texto' => (int) $uso['estudiantes'] . ' estudiante' . ((int) $uso['estudiantes'] === 1 ? '' : 's'),
                ];
            })
            ->values()
            ->toArray();
    }

    private function obtenerRecomendacionesSistema(): array
    {
        $recomendaciones = [];

        Paralelo::query()->orderBy('nom_par')->get()->each(function (Paralelo $paralelo) use (&$recomendaciones) {
            $uso = $this->obtenerUsoAcademico($paralelo->cod_par);

            if ($paralelo->est_par === 'ACTIVO' && ! ($uso['tiene_uso'] ?? false)) {
                $recomendaciones[] = [
                    'tipo' => 'warning',
                    'titulo' => 'Paralelo sin uso actual',
                    'mensaje' => 'El Paralelo ' . $paralelo->nom_par . ' no tiene estudiantes ni planificación vinculada. Puede revisarse para desactivación.',
                ];
            }

            if ($paralelo->est_par === 'INACTIVO') {
                $recomendaciones[] = [
                    'tipo' => 'info',
                    'titulo' => 'Histórico recuperable',
                    'mensaje' => 'El Paralelo ' . $paralelo->nom_par . ' está inactivo y puede reactivarse si la institución vuelve a necesitarlo.',
                ];
            }

            if (($uso['estudiantes'] ?? 0) > 0 && $paralelo->est_par === 'INACTIVO') {
                $recomendaciones[] = [
                    'tipo' => 'danger',
                    'titulo' => 'Inactivo con estudiantes',
                    'mensaje' => 'El Paralelo ' . $paralelo->nom_par . ' figura como inactivo, pero mantiene estudiantes vinculados. Revisa su historial académico.',
                ];
            }
        });

        if (count($recomendaciones) === 0) {
            $recomendaciones[] = [
                'tipo' => 'success',
                'titulo' => 'Organización estable',
                'mensaje' => 'No se detectaron paralelos duplicados, sin uso o inconsistentes.',
            ];
        }

        return array_slice($recomendaciones, 0, 5);
    }

    /*
    |--------------------------------------------------------------------------
    | Crear
    |--------------------------------------------------------------------------
    */

    public function abrirModalCrear(): void
    {
        $this->resetValidation();
        $this->limpiarFormularioCrear();
        $this->modalCrear = true;
    }

    public function cerrarModalCrear(): void
    {
        $this->modalCrear = false;
        $this->limpiarFormularioCrear();
        $this->resetValidation();
    }

    private function limpiarFormularioCrear(): void
    {
        $this->form = [
            'nom_par' => '',
            'est_par' => 'ACTIVO',
        ];

        $this->puedeGuardarCrear = false;
        $this->bloqueoCrearMensaje = null;

        $this->reiniciarAnalisisCrear();
    }

    public function updatedFormNomPar(): void
    {
        $this->interpretarParaleloCrear();
    }

    public function interpretarParaleloCrear(): void
    {
        $this->analisisCrear = ParaleloInteligente::interpretar(
            $this->form['nom_par'] ?? '',
            $this->obtenerParalelosExistentesParaAnalisis()
        );

        $this->aplicarReglaSecuencialCrear();

        $this->puedeGuardarCrear = (bool) ($this->analisisCrear['puede_crear'] ?? false);
        $this->bloqueoCrearMensaje = $this->puedeGuardarCrear
            ? null
            : ($this->analisisCrear['mensaje'] ?? 'No se puede registrar este paralelo.');

        if ($this->puedeGuardarCrear && ! empty($this->analisisCrear['nombre_sugerido'])) {
            $this->form['nom_par'] = $this->analisisCrear['nombre_sugerido'];
        }
    }

    private function aplicarReglaSecuencialCrear(): void
    {
        $estado = $this->analisisCrear['estado_inteligente'] ?? null;

        if (in_array($estado, [
            ParaleloInteligente::ESTADO_DUPLICADO_ACTIVO,
            ParaleloInteligente::ESTADO_DUPLICADO_INACTIVO,
            ParaleloInteligente::ESTADO_BLOQUEADO,
        ], true)) {
            return;
        }

        $nombreSugerido = trim((string) ($this->analisisCrear['nombre_sugerido'] ?? ''));

        if ($nombreSugerido === '') {
            return;
        }

        if (! $this->esParaleloLetra($nombreSugerido)) {
            return;
        }

        $letraSugerida = mb_strtoupper($nombreSugerido);
        $siguientePermitida = $this->obtenerSiguienteLetraPermitida();

        if ($siguientePermitida === null) {
            return;
        }

        if ($letraSugerida !== $siguientePermitida) {
            $this->bloquearAnalisisCrearPorSecuencia(
                'El registro de paralelos debe seguir un orden institucional. Actualmente corresponde crear el Paralelo '
                    . $siguientePermitida
                    . ', no el Paralelo '
                    . $letraSugerida
                    . '.'
            );
        }
    }

    private function bloquearAnalisisCrearPorSecuencia(string $mensaje): void
    {
        $this->analisisCrear['valido'] = false;
        $this->analisisCrear['puede_crear'] = false;
        $this->analisisCrear['puede_reactivar'] = false;
        $this->analisisCrear['estado_inteligente'] = ParaleloInteligente::ESTADO_BLOQUEADO;
        $this->analisisCrear['mensaje'] = $mensaje;
        $this->analisisCrear['confianza'] = 100;
        $this->analisisCrear['requiere_soporte'] = false;

        $advertencias = $this->analisisCrear['advertencias'] ?? [];

        $advertencias[] = 'No se pueden saltar paralelos. Debe registrarse la siguiente letra disponible.';
        $advertencias[] = 'Ejemplo correcto: si existen A, B, C y D, el siguiente paralelo permitido es E.';

        $this->analisisCrear['advertencias'] = array_values(array_unique($advertencias));
    }

    private function obtenerSiguienteLetraPermitida(): ?string
    {
        $letrasExistentes = Paralelo::query()
            ->pluck('nom_par')
            ->map(fn($nombre) => mb_strtoupper(trim((string) $nombre)))
            ->filter(fn($nombre) => $this->esParaleloLetra($nombre))
            ->unique()
            ->sort()
            ->values()
            ->toArray();

        $abecedario = range('A', 'Z');

        foreach ($abecedario as $letra) {
            if (! in_array($letra, $letrasExistentes, true)) {
                return $letra;
            }
        }

        return null;
    }

    private function esParaleloLetra(string $nombre): bool
    {
        return preg_match('/^[A-Z]$/u', mb_strtoupper(trim($nombre))) === 1;
    }

    public function usarSugerenciaCrear(): void
    {
        if (! ($this->analisisCrear['nombre_sugerido'] ?? null)) {
            $this->dispatch('advertencia-general', mensaje: 'No existe una sugerencia válida para aplicar.');
            return;
        }

        $this->form['nom_par'] = $this->analisisCrear['nombre_sugerido'];
        $this->interpretarParaleloCrear();
    }

    public function guardarParalelo(): void
    {
        $this->normalizarFormularioCrear();
        $this->interpretarParaleloCrear();

        if (! $this->puedeGuardarCrear) {
            $this->addError('form.nom_par', $this->bloqueoCrearMensaje ?? 'No se puede registrar este paralelo.');

            $this->dispatch(
                'advertencia-general',
                mensaje: $this->bloqueoCrearMensaje ?? 'No se puede registrar este paralelo.'
            );

            return;
        }

        if (($this->analisisCrear['estado_inteligente'] ?? '') === ParaleloInteligente::ESTADO_DUPLICADO_INACTIVO) {
            $this->addError('form.nom_par', $this->analisisCrear['mensaje'] ?? 'Ya existe un paralelo inactivo con ese nombre.');

            $this->dispatch(
                'duplicado-inactivo',
                mensaje: $this->analisisCrear['mensaje'] ?? 'Existe un paralelo inactivo con ese nombre. Se recomienda reactivarlo.'
            );

            return;
        }

        if (! ($this->analisisCrear['puede_crear'] ?? false)) {
            $this->addError('form.nom_par', $this->analisisCrear['mensaje'] ?? 'No se puede registrar este paralelo.');

            $this->registrarBitacoraSeguro(
                accion: 'INTENTO_CREAR_PARALELO_BLOQUEADO',
                tabla: 'paralelo',
                registro: $this->analisisCrear['codigo_existente'] ?? null,
                nombreRegistro: $this->form['nom_par'] ?? null,
                descripcion: $this->analisisCrear['mensaje'] ?? 'Intento bloqueado de creación de paralelo.',
                nivel: 'WARNING',
                resultado: 'BLOQUEADO',
                valoresNuevos: [
                    'formulario' => $this->form,
                    'analisis' => $this->analisisCrear,
                ]
            );

            $this->dispatch(
                'advertencia-general',
                mensaje: $this->analisisCrear['mensaje'] ?? 'No se puede registrar este paralelo.'
            );

            return;
        }

        $this->validate($this->rulesCrear(), [], $this->validationAttributesCrear());

        try {
            DB::transaction(function () {
                $paralelo = Paralelo::create([
                    'nom_par' => $this->analisisCrear['nombre_sugerido'] ?: $this->form['nom_par'],
                    'est_par' => $this->form['est_par'],
                ]);

                $this->registrarBitacoraSeguro(
                    accion: 'CREAR_PARALELO',
                    tabla: 'paralelo',
                    registro: $paralelo->cod_par,
                    nombreRegistro: 'Paralelo ' . $paralelo->nom_par,
                    descripcion: 'Se registró el Paralelo ' . $paralelo->nom_par . ' con validación inteligente.',
                    nivel: ($this->analisisCrear['estado_inteligente'] ?? '') === ParaleloInteligente::ESTADO_REQUIERE_REVISION ? 'WARNING' : 'SUCCESS',
                    resultado: 'EXITOSO',
                    valoresNuevos: [
                        'paralelo' => $paralelo->toArray(),
                        'analisis' => $this->analisisCrear,
                    ]
                );
            });

            $this->cerrarModalCrear();
            $this->resetPage();

            $this->dispatch('paralelo-creado', mensaje: 'Paralelo registrado correctamente.');
        } catch (Throwable $e) {
            report($e);

            $this->registrarBitacoraSeguro(
                accion: 'ERROR_CREAR_PARALELO',
                tabla: 'paralelo',
                nombreRegistro: $this->form['nom_par'] ?? null,
                descripcion: 'No se pudo registrar el paralelo.',
                nivel: 'ERROR',
                resultado: 'FALLIDO',
                valoresNuevos: $this->form,
                error: $e->getMessage()
            );

            $this->dispatch('error-general', mensaje: 'No se pudo registrar el paralelo. Revisa los datos e intenta nuevamente.');
        }
    }

    public function reactivarExistenteDesdeAnalisisCrear(): void
    {
        $codigo = $this->analisisCrear['codigo_existente'] ?? null;

        if (! $codigo) {
            $this->dispatch('advertencia-general', mensaje: 'No se encontró un paralelo inactivo para reactivar.');
            return;
        }

        $this->reactivarParalelo($codigo);
        $this->cerrarModalCrear();
    }

    /*
    |--------------------------------------------------------------------------
    | Editar
    |--------------------------------------------------------------------------
    */

    public function abrirModalEditar(string $codPar): void
    {
        $this->resetValidation();

        $paralelo = Paralelo::where('cod_par', $codPar)->firstOrFail();

        $this->paraleloSeleccionado = $paralelo->cod_par;

        $this->formEditar = [
            'cod_par' => $paralelo->cod_par,
            'nom_par' => $paralelo->nom_par,
            'est_par' => $paralelo->est_par,
        ];

        $this->interpretarParaleloEditar();

        $this->modalEditar = true;
    }

    public function cerrarModalEditar(): void
    {
        $this->modalEditar = false;
        $this->paraleloSeleccionado = null;

        $this->formEditar = [
            'cod_par' => '',
            'nom_par' => '',
            'est_par' => 'ACTIVO',
        ];

        $this->reiniciarAnalisisEditar();
        $this->resetValidation();
    }

    public function updatedFormEditarNomPar(): void
    {
        $this->interpretarParaleloEditar();
    }

    public function interpretarParaleloEditar(): void
    {
        $existentes = collect($this->obtenerParalelosExistentesParaAnalisis())
            ->reject(fn(array $item) => ($item['cod_par'] ?? null) === ($this->formEditar['cod_par'] ?? null))
            ->values()
            ->toArray();

        $this->analisisEditar = ParaleloInteligente::interpretar(
            $this->formEditar['nom_par'] ?? '',
            $existentes
        );
    }

    public function usarSugerenciaEditar(): void
    {
        if (! ($this->analisisEditar['nombre_sugerido'] ?? null)) {
            $this->dispatch('advertencia-general', mensaje: 'No existe una sugerencia válida para aplicar.');
            return;
        }

        $this->formEditar['nom_par'] = $this->analisisEditar['nombre_sugerido'];
        $this->interpretarParaleloEditar();
    }

    public function guardarEdicionParalelo(): void
    {
        $this->normalizarFormularioEditar();
        $this->interpretarParaleloEditar();

        $paralelo = Paralelo::where('cod_par', $this->formEditar['cod_par'])->firstOrFail();
        $valoresAnteriores = $paralelo->toArray();
        $uso = $this->obtenerUsoAcademico($paralelo->cod_par);

        if (! ($this->analisisEditar['puede_crear'] ?? false)) {
            $this->addError('formEditar.nom_par', $this->analisisEditar['mensaje'] ?? 'No se puede actualizar este paralelo.');

            $this->dispatch(
                'advertencia-general',
                mensaje: $this->analisisEditar['mensaje'] ?? 'No se puede actualizar este paralelo.'
            );

            return;
        }

        if (($uso['tiene_uso'] ?? false) && ! ParaleloInteligente::esCambioMenor($paralelo->nom_par, $this->analisisEditar['nombre_sugerido'] ?? $this->formEditar['nom_par'])) {
            $this->addError('formEditar.nom_par', 'Este paralelo tiene historial académico. Solo se permiten correcciones menores.');

            $this->dispatch(
                'advertencia-general',
                mensaje: 'Cambio bloqueado: el paralelo tiene estudiantes, planes u horarios vinculados. Solo se permiten correcciones menores.'
            );

            return;
        }

        $this->validate($this->rulesEditar(), [], $this->validationAttributesEditar());

        try {
            DB::transaction(function () use ($paralelo, $valoresAnteriores, $uso) {
                $paralelo->update([
                    'nom_par' => $this->analisisEditar['nombre_sugerido'] ?: $this->formEditar['nom_par'],
                    'est_par' => $this->formEditar['est_par'],
                ]);

                $this->registrarBitacoraSeguro(
                    accion: 'EDITAR_PARALELO',
                    tabla: 'paralelo',
                    registro: $paralelo->cod_par,
                    nombreRegistro: 'Paralelo ' . $paralelo->nom_par,
                    descripcion: 'Se actualizó el Paralelo ' . $paralelo->nom_par . '.',
                    nivel: ($uso['tiene_uso'] ?? false) ? 'WARNING' : 'SUCCESS',
                    resultado: 'EXITOSO',
                    valoresAnteriores: [
                        'paralelo' => $valoresAnteriores,
                        'uso_academico' => $uso,
                    ],
                    valoresNuevos: [
                        'paralelo' => $paralelo->fresh()?->toArray(),
                        'analisis' => $this->analisisEditar,
                    ]
                );
            });

            $this->cerrarModalEditar();

            $this->dispatch('paralelo-actualizado', mensaje: 'Paralelo actualizado correctamente.');
        } catch (Throwable $e) {
            report($e);

            $this->registrarBitacoraSeguro(
                accion: 'ERROR_EDITAR_PARALELO',
                tabla: 'paralelo',
                registro: $this->formEditar['cod_par'] ?? null,
                nombreRegistro: $this->formEditar['nom_par'] ?? null,
                descripcion: 'No se pudo editar el paralelo.',
                nivel: 'ERROR',
                resultado: 'FALLIDO',
                valoresNuevos: $this->formEditar,
                error: $e->getMessage()
            );

            $this->dispatch('error-general', mensaje: 'No se pudo actualizar el paralelo.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Detalle
    |--------------------------------------------------------------------------
    */

    public function abrirModalDetalle(string $codPar): void
    {
        $paralelo = Paralelo::where('cod_par', $codPar)->firstOrFail();
        $uso = $this->obtenerUsoAcademico($paralelo->cod_par);
        $impacto = $this->calcularImpacto($paralelo, $uso);
        $bitacora = $this->obtenerUltimaBitacora($paralelo->cod_par);
        $distribucionCursos = $this->obtenerDistribucionCursosPorParalelo($paralelo->cod_par);

        $this->paraleloSeleccionado = $paralelo->cod_par;

        $this->detalleParalelo = [
            'nombre' => $paralelo->nom_par,
            'estado' => $paralelo->est_par,
            'disponibilidad' => $this->obtenerDisponibilidad($paralelo, $uso),
            'uso' => $uso,
            'impacto' => $impacto,
            'ultima_bitacora' => $bitacora,
            'distribucion_cursos' => $distribucionCursos,
            'recomendacion' => $this->obtenerRecomendacionInstitucional($paralelo, $uso, $impacto),
        ];

        $this->modalDetalle = true;
    }

    public function cerrarModalDetalle(): void
    {
        $this->modalDetalle = false;
        $this->detalleParalelo = [];
        $this->paraleloSeleccionado = null;
    }

    private function obtenerRecomendacionInstitucional(Paralelo $paralelo, array $uso, array $impacto): string
    {
        if ($paralelo->est_par === 'INACTIVO') {
            return 'Este paralelo está inactivo. No aparece en selectores operativos, pero conserva su historial académico y puede reactivarse si la institución vuelve a necesitarlo.';
        }

        if (($uso['estudiantes'] ?? 0) > 0) {
            return 'Este paralelo tiene estudiantes asignados. No se recomienda desactivarlo mientras mantenga estudiantes o planificación activa.';
        }

        if (($impacto['nivel'] ?? '') === 'SIN_USO') {
            return 'Este paralelo no tiene uso académico actual. Puede revisarse para desactivación lógica si la institución ya no lo necesita.';
        }

        return 'Este paralelo se encuentra disponible para la organización académica institucional.';
    }

    /*
    |--------------------------------------------------------------------------
    | Catálogo e históricos
    |--------------------------------------------------------------------------
    */

    public function abrirModalCatalogo(): void
    {
        $this->modalCatalogo = true;
    }

    public function cerrarModalCatalogo(): void
    {
        $this->modalCatalogo = false;
    }

    public function usarDesdeCatalogo(string $nombre): void
    {
        $this->form['nom_par'] = $nombre;
        $this->form['est_par'] = 'ACTIVO';
        $this->interpretarParaleloCrear();

        $this->modalCatalogo = false;
        $this->modalCrear = true;
    }

    public function abrirModalHistoricos(): void
    {
        $this->modalHistoricos = true;
    }

    public function cerrarModalHistoricos(): void
    {
        $this->modalHistoricos = false;
    }

    private function obtenerHistoricosRecuperables(): array
    {
        return Paralelo::query()
            ->where('est_par', 'INACTIVO')
            ->orderBy('nom_par')
            ->get()
            ->map(function (Paralelo $paralelo) {
                $uso = $this->obtenerUsoAcademico($paralelo->cod_par);
                $bitacora = $this->obtenerUltimaBitacora($paralelo->cod_par, ['DESACTIVAR_PARALELO', 'ELIMINAR_PARALELO_LOGICO']);

                return [
                    'codigo' => $paralelo->cod_par,
                    'nombre' => $paralelo->nom_par,
                    'uso' => $uso,
                    'ultima_bitacora' => $bitacora,
                ];
            })
            ->values()
            ->toArray();
    }

    /*
    |--------------------------------------------------------------------------
    | Desactivar / Reactivar
    |--------------------------------------------------------------------------
    */

    public function solicitarDesactivar(string $codPar): void
    {
        $paralelo = Paralelo::where('cod_par', $codPar)->firstOrFail();
        $uso = $this->obtenerUsoAcademico($paralelo->cod_par);

        $titulo = '¿Desactivar paralelo?';

        if (($uso['estudiantes'] ?? 0) > 0) {
            $mensaje = 'El Paralelo ' . $paralelo->nom_par . ' tiene estudiantes asignados. No se recomienda desactivarlo hasta reasignar o cerrar su uso académico.';
            $riesgo = 'ALTO';
        } elseif (($uso['tiene_planificacion'] ?? false)) {
            $mensaje = 'El Paralelo ' . $paralelo->nom_par . ' tiene planificación vinculada. Se desactivará de forma lógica y conservará su historial.';
            $riesgo = 'MEDIO';
        } else {
            $mensaje = 'Este paralelo no será eliminado físicamente. Se ocultará de selectores operativos y conservará su historial académico.';
            $riesgo = 'BAJO';
        }

        $this->dispatch(
            'confirmar-desactivar-paralelo',
            codigo: $paralelo->cod_par,
            titulo: $titulo,
            mensaje: $mensaje,
            riesgo: $riesgo
        );
    }

    public function desactivarParalelo(string $codPar): void
    {
        $paralelo = Paralelo::where('cod_par', $codPar)->firstOrFail();

        if ($paralelo->est_par === 'INACTIVO') {
            $this->dispatch('advertencia-general', mensaje: 'El paralelo ya se encuentra inactivo.');
            return;
        }

        $valoresAnteriores = $paralelo->toArray();
        $uso = $this->obtenerUsoAcademico($paralelo->cod_par);

        try {
            DB::transaction(function () use ($paralelo, $valoresAnteriores, $uso) {
                $paralelo->update([
                    'est_par' => 'INACTIVO',
                ]);

                $this->registrarBitacoraSeguro(
                    accion: 'DESACTIVAR_PARALELO',
                    tabla: 'paralelo',
                    registro: $paralelo->cod_par,
                    nombreRegistro: 'Paralelo ' . $paralelo->nom_par,
                    descripcion: 'Se desactivó lógicamente el Paralelo ' . $paralelo->nom_par . '. No fue eliminado físicamente.',
                    nivel: ($uso['estudiantes'] ?? 0) > 0 ? 'WARNING' : 'INFO',
                    resultado: 'EXITOSO',
                    valoresAnteriores: [
                        'paralelo' => $valoresAnteriores,
                        'uso_academico' => $uso,
                    ],
                    valoresNuevos: [
                        'paralelo' => $paralelo->fresh()?->toArray(),
                    ]
                );
            });

            $this->dispatch('paralelo-desactivado', mensaje: 'Paralelo desactivado correctamente. Su historial académico fue conservado.');
        } catch (Throwable $e) {
            report($e);

            $this->dispatch('error-general', mensaje: 'No se pudo desactivar el paralelo.');
        }
    }

    public function solicitarReactivar(string $codPar): void
    {
        $paralelo = Paralelo::where('cod_par', $codPar)->firstOrFail();

        $this->dispatch(
            'confirmar-reactivar-paralelo',
            codigo: $paralelo->cod_par,
            titulo: '¿Reactivar paralelo?',
            mensaje: 'El Paralelo ' . $paralelo->nom_par . ' volverá a estar disponible para planificación, inscripciones y horarios.'
        );
    }

    public function reactivarParalelo(string $codPar): void
    {
        $paralelo = Paralelo::where('cod_par', $codPar)->firstOrFail();

        if ($paralelo->est_par === 'ACTIVO') {
            $this->dispatch('advertencia-general', mensaje: 'El paralelo ya se encuentra activo.');
            return;
        }

        $valoresAnteriores = $paralelo->toArray();

        try {
            DB::transaction(function () use ($paralelo, $valoresAnteriores) {
                $paralelo->update([
                    'est_par' => 'ACTIVO',
                ]);

                $this->registrarBitacoraSeguro(
                    accion: 'REACTIVAR_PARALELO',
                    tabla: 'paralelo',
                    registro: $paralelo->cod_par,
                    nombreRegistro: 'Paralelo ' . $paralelo->nom_par,
                    descripcion: 'Se reactivó el Paralelo ' . $paralelo->nom_par . ' para uso académico institucional.',
                    nivel: 'SUCCESS',
                    resultado: 'EXITOSO',
                    valoresAnteriores: [
                        'paralelo' => $valoresAnteriores,
                    ],
                    valoresNuevos: [
                        'paralelo' => $paralelo->fresh()?->toArray(),
                    ]
                );
            });

            $this->dispatch('paralelo-reactivado', mensaje: 'Paralelo reactivado correctamente.');
        } catch (Throwable $e) {
            report($e);

            $this->dispatch('error-general', mensaje: 'No se pudo reactivar el paralelo.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Bitácora
    |--------------------------------------------------------------------------
    */

    private function obtenerUltimaBitacora(string $codPar, array $acciones = []): ?array
    {
        if (! Schema::hasTable('bitacora')) {
            return null;
        }

        $query = Bitacora::query()
            ->where('tab_bit', 'paralelo')
            ->where('reg_bit', $codPar);

        if (! empty($acciones)) {
            $query->whereIn('acc_bit', $acciones);
        }

        $bitacora = $query->orderByDesc('fec_bit')->first();

        if (! $bitacora) {
            return null;
        }

        return [
            'accion' => $bitacora->acc_bit,
            'descripcion' => $bitacora->des_bit,
            'fecha' => optional($bitacora->fec_bit)->format('d/m/Y H:i'),
            'usuario' => $bitacora->cod_usu,
            'rol' => $bitacora->rol_bit,
            'nivel' => $bitacora->niv_bit,
            'resultado' => $bitacora->res_bit,
        ];
    }

    private function registrarBitacoraSeguro(
        string $accion,
        string $tabla,
        ?string $registro = null,
        ?string $nombreRegistro = null,
        ?string $descripcion = null,
        string $nivel = 'INFO',
        string $resultado = 'EXITOSO',
        ?array $valoresAnteriores = null,
        ?array $valoresNuevos = null,
        ?string $error = null
    ): void {
        try {
            if (! class_exists(BitacoraService::class)) {
                return;
            }

            BitacoraService::registrar(
                accion: $accion,
                tabla: $tabla,
                registro: $registro,
                modulo: 'Gestión de Paralelos',
                nombreRegistro: $nombreRegistro,
                descripcion: $descripcion,
                nivel: $nivel,
                resultado: $resultado,
                valoresAnteriores: $valoresAnteriores,
                valoresNuevos: $valoresNuevos,
                error: $error
            );
        } catch (Throwable $e) {
            report($e);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Validaciones
    |--------------------------------------------------------------------------
    */

    private function rulesCrear(): array
    {
        return [
            'form.nom_par' => [
                'required',
                'string',
                'min:1',
                'max:30',
            ],
            'form.est_par' => [
                'required',
                Rule::in(array_keys($this->estadosDisponibles)),
            ],
        ];
    }

    private function rulesEditar(): array
    {
        return [
            'formEditar.cod_par' => [
                'required',
                'string',
                'exists:paralelo,cod_par',
            ],
            'formEditar.nom_par' => [
                'required',
                'string',
                'min:1',
                'max:30',
            ],
            'formEditar.est_par' => [
                'required',
                Rule::in(array_keys($this->estadosDisponibles)),
            ],
        ];
    }

    private function validationAttributesCrear(): array
    {
        return [
            'form.nom_par' => 'nombre del paralelo',
            'form.est_par' => 'estado inicial',
        ];
    }

    private function validationAttributesEditar(): array
    {
        return [
            'formEditar.cod_par' => 'paralelo seleccionado',
            'formEditar.nom_par' => 'nombre del paralelo',
            'formEditar.est_par' => 'estado',
        ];
    }

    protected function messages(): array
    {
        return [
            'required' => 'El campo :attribute es obligatorio.',
            'string' => 'El campo :attribute debe ser texto.',
            'min' => 'El campo :attribute no cumple la longitud mínima.',
            'max' => 'El campo :attribute supera la longitud máxima permitida.',
            'exists' => 'El paralelo seleccionado no existe.',
            'in' => 'El valor seleccionado para :attribute no es válido.',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Normalización
    |--------------------------------------------------------------------------
    */

    private function normalizarFormularioCrear(): void
    {
        $this->form['nom_par'] = ParaleloInteligente::formatearNombre($this->form['nom_par'] ?? '');
        $this->form['est_par'] = $this->form['est_par'] ?: 'ACTIVO';
    }

    private function normalizarFormularioEditar(): void
    {
        $this->formEditar['nom_par'] = ParaleloInteligente::formatearNombre($this->formEditar['nom_par'] ?? '');
        $this->formEditar['est_par'] = $this->formEditar['est_par'] ?: 'ACTIVO';
    }

    private function reiniciarAnalisisCrear(): void
    {
        $this->analisisCrear = ParaleloInteligente::interpretar('');
        $this->puedeGuardarCrear = false;
        $this->bloqueoCrearMensaje = null;
    }

    private function reiniciarAnalisisEditar(): void
    {
        $this->analisisEditar = ParaleloInteligente::interpretar('');
    }

    private function obtenerParalelosExistentesParaAnalisis(): array
    {
        return Paralelo::query()
            ->select('cod_par', 'nom_par', 'est_par')
            ->get()
            ->map(function (Paralelo $paralelo) {
                return [
                    'cod_par' => $paralelo->cod_par,
                    'nom_par' => $paralelo->nom_par,
                    'est_par' => $paralelo->est_par,
                    'bitacora' => $this->obtenerUltimaBitacora($paralelo->cod_par, ['DESACTIVAR_PARALELO', 'ELIMINAR_PARALELO_LOGICO']),
                ];
            })
            ->toArray();
    }

    /*
    |--------------------------------------------------------------------------
    | Distribución por cursos
    |--------------------------------------------------------------------------
    */

    private function obtenerDistribucionCursosPorParalelo(string $codPar): array
    {
        $datos = collect();

        foreach ($this->tablasInscripcionDisponibles() as $tabla) {
            if (! $this->tablaTieneColumna($tabla, 'cod_par') || ! $this->tablaTieneColumna($tabla, 'cod_cur')) {
                continue;
            }

            $columnaEstudiante = $this->primeraColumnaDisponible($tabla, [
                'cod_est',
                'cod_estu',
                'cod_estudiante',
                'cod_per',
            ]);

            $registros = DB::table($tabla)
                ->where('cod_par', $codPar)
                ->select('cod_cur')
                ->selectRaw($columnaEstudiante ? 'COUNT(DISTINCT ' . $columnaEstudiante . ') as total' : 'COUNT(*) as total')
                ->groupBy('cod_cur')
                ->get();

            $datos = $datos->merge($registros);
        }

        if ($datos->isEmpty()) {
            return [];
        }

        return $datos
            ->groupBy('cod_cur')
            ->map(function (Collection $items, string $curso) {
                return [
                    'curso' => $this->nombreCurso($curso),
                    'estudiantes' => (int) $items->sum('total'),
                ];
            })
            ->sortByDesc('estudiantes')
            ->values()
            ->toArray();
    }

    private function nombreCurso(string $codCur): string
    {
        if (! Schema::hasTable('curso')) {
            return 'Curso vinculado';
        }

        $columnasNombre = [
            'nom_cur',
            'nombre',
            'des_cur',
        ];

        foreach ($columnasNombre as $columna) {
            if (Schema::hasColumn('curso', $columna)) {
                $nombre = DB::table('curso')
                    ->where('cod_cur', $codCur)
                    ->value($columna);

                return $nombre ?: 'Curso vinculado';
            }
        }

        return 'Curso vinculado';
    }

    /*
    |--------------------------------------------------------------------------
    | Utilidades DB
    |--------------------------------------------------------------------------
    */

    private function tablasInscripcionDisponibles(): array
    {
        return collect([
            'inscripcion_estudiante',
            'inscripciones_estudiante',
            'inscripcion_estudiantes',
            'inscripciones_estudiantes',
            'inscripcion',
            'inscripciones',
            'estudiante_inscripcion',
            'estudiantes_inscripciones',
        ])
            ->filter(fn(string $tabla) => Schema::hasTable($tabla))
            ->values()
            ->toArray();
    }

    private function tablaTieneColumna(string $tabla, string $columna): bool
    {
        return Schema::hasTable($tabla) && Schema::hasColumn($tabla, $columna);
    }

    private function primeraColumnaDisponible(string $tabla, array $columnas): ?string
    {
        foreach ($columnas as $columna) {
            if (Schema::hasColumn($tabla, $columna)) {
                return $columna;
            }
        }

        return null;
    }
}
