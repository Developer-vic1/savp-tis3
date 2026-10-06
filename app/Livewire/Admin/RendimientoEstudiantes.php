<?php

namespace App\Livewire\Admin;

use App\Models\Oficial\Academico\{Curso, GestionAcademica, InscripcionEstudiante, Paralelo, PeriodoEvaluacion, Turno};
use App\Models\Oficial\AporteAcademicoVocacional\OrientacionActividad;
use App\Services\AporteIngenieril\AporteIngenierilClient;
use App\Services\AporteIngenieril\StudentOrientationService;
use App\Services\InstitutionalQueryService;
use App\Support\Evaluacion\{PanelResultadosAcademicos, RendimientoEstudiantil};
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\{Locked, Url};
use Livewire\Component;
use Livewire\WithPagination;

class RendimientoEstudiantes extends Component
{
    use WithPagination;

    #[Url] public string $search = '';
    #[Url] public string $gestion = '';
    #[Url(as: 'curso')] public string $curso = '';
    #[Url(as: 'estudiante')] public string $estudiante = '';
    #[Url] public string $periodo = '';
    #[Url] public string $seguimiento = '';
    #[Url] public string $nivel = '';
    #[Url] public string $paralelo = '';
    #[Url] public string $turno = '';
    public int $perPage = 10;
    public string $vista = 'grupo';
    #[Locked] public ?string $seleccionado = null;
    #[Locked] public string $conexion = 'sin_comprobar';
    #[Locked] public ?string $comprobada = null;
    #[Locked] public array $preparacion = [];
    #[Locked] public ?array $vistaPrevia = null;
    #[Locked] public ?string $mensajeAnalisis = null;
    #[Locked] public string $carreraSeleccionada = '';
    #[Locked] public string $tipoRelacion = 'academica';

    public function boot(): void
    {
        $usuario = auth()->user();
        abort_unless($usuario?->est_usu === 'ACTIVO', 403);
        app(InstitutionalQueryService::class)->authorizeQuery($usuario, 'lms', 'admin');
        abort_unless($usuario->can('calificaciones.ver.global') && $usuario->can('estudiantes.ver.global'), 403);
    }

    public function mount(): void
    {
        if ($this->gestion === '') {
            $this->gestion = (string) GestionAcademica::whereIn('est_gea', ['ACTIVA', 'ACTIVO'])
                ->orderByDesc('ani_gea')->value('cod_gea');
        }
    }

    protected function rules(): array
    {
        return ['search' => 'nullable|string|max:100', 'gestion' => 'nullable|string|max:30',
            'curso' => 'nullable|string|max:30', 'estudiante' => 'nullable|string|max:30',
            'periodo' => 'nullable|string|max:30', 'seguimiento' => 'nullable|in:riesgo,sin_notas,sin_analisis,retirados',
            'nivel' => 'nullable|string|max:50', 'paralelo' => 'nullable|string|max:30', 'turno' => 'nullable|string|max:30',
            'vista' => 'required|in:estudiantes,grupo',
            'perPage' => 'required|integer|in:10,20,50'];
    }

    public function updated(string $propiedad): void
    {
        $this->seleccionado = null;
        $this->reset('preparacion', 'vistaPrevia', 'mensajeAnalisis');
        $this->resetPage();
        $this->resetValidation();
        if ($propiedad === 'gestion') {
            $this->reset('curso', 'periodo', 'nivel', 'paralelo', 'turno', 'estudiante');
        } elseif ($propiedad === 'nivel') {
            $this->reset('curso', 'estudiante');
        }
        $this->validateOnly($propiedad);
    }

    public function limpiarFiltros(): void
    {
        $this->reset('search', 'curso', 'estudiante', 'periodo', 'seguimiento', 'nivel', 'paralelo', 'turno', 'seleccionado');
        $this->resetValidation();
        $this->resetPage();
        $this->reset('preparacion', 'vistaPrevia', 'mensajeAnalisis');
    }

    private function filtros(): array
    {
        $validador = Validator::make($this->only(['search', 'gestion', 'curso', 'estudiante', 'periodo', 'seguimiento', 'nivel', 'paralelo', 'turno', 'perPage', 'vista']), $this->rules());
        if ($validador->fails()) {
            $this->setErrorBag($validador->errors());
        }
        $orientacion = auth()->user()->can('orientacion.ver.institucional');

        return ['gestion' => $validador->fails() ? '' : $this->gestion, 'grado' => $this->curso,
            'search' => trim(mb_substr($this->search, 0, 100)), 'estudiante' => $this->estudiante,
            'periodo' => $this->periodo, 'seguimiento' => ! $orientacion && $this->seguimiento === 'sin_analisis' ? '' : $this->seguimiento,
            'nivel' => $this->nivel, 'paralelo' => $this->paralelo, 'turno' => $this->turno,
            'orientacion' => $orientacion];
    }

    public function verEstudiante(string $inscripcion): void
    {
        abort_unless(strlen($inscripcion) <= 30 && app(RendimientoEstudiantil::class)->consulta($this->filtros())->where('i.cod_ins', $inscripcion)->exists(), 404);
        $this->seleccionado = $inscripcion;
        $this->reset('preparacion', 'vistaPrevia', 'mensajeAnalisis');
    }

    public function cerrarDetalle(): void
    {
        $this->seleccionado = null;
        $this->reset('preparacion', 'vistaPrevia', 'mensajeAnalisis');
    }

    public function verRelacionCarrera(string $carrera, string $tipo): void
    {
        abort_unless(auth()->user()->can('orientacion.ver.institucional'), 403);
        $catalogo = app(\App\Support\Evaluacion\CatalogoCarrerasRendimiento::class)->leer();
        abort_unless(isset($catalogo['carreras'][$carrera]) && $catalogo['carreras'][$carrera]['elegible']
            && in_array($tipo, ['academica', 'tecnica', 'declarados'], true), 404);
        $this->carreraSeleccionada = $carrera;
        $this->tipoRelacion = $tipo;
        $this->vista = 'grupo';
        $this->cerrarDetalle();
    }

    public function revisarPreparacion(): void
    {
        abort_unless(auth()->user()->can('orientacion.ver.institucional'), 403);
        abort_unless($this->seleccionado && app(RendimientoEstudiantil::class)->consulta($this->filtros())->where('i.cod_ins', $this->seleccionado)->exists(), 404);
        $contexto = app(StudentOrientationService::class)->institutionalContext(auth()->user(), $this->seleccionado);
        $this->preparacion = $contexto['precheck'];
        $this->reset('vistaPrevia', 'mensajeAnalisis');
    }

    public function analizarEvidencia(): void
    {
        $this->revisarPreparacion();
        if (! ($this->preparacion['ready'] ?? false)) {
            $this->mensajeAnalisis = 'Completa los requisitos obligatorios indicados. Las notas y las respuestas existentes se conservan.';

            return;
        }
        $contexto = app(StudentOrientationService::class)->institutionalContext(auth()->user(), $this->seleccionado);
        $respuesta = app(AporteIngenierilClient::class)->analysisV2($contexto['payload']);
        $this->conexion = $respuesta->available ? 'disponible' : 'no_disponible';
        $this->comprobada = now()->format('d/m/Y H:i');
        if (! $respuesta->available) {
            $this->mensajeAnalisis = $respuesta->message;

            return;
        }
        $this->vistaPrevia = $respuesta->data;
        $this->mensajeAnalisis = 'Vista previa calculada con la evidencia actual. No sustituye el estudio guardado.';
    }

    public function comprobarConexion(): void
    {
        abort_unless(auth()->user()->can('orientacion.ver.institucional'), 403);
        $respuesta = app(AporteIngenierilClient::class)->health();
        $this->conexion = $respuesta->available ? 'disponible' : 'no_disponible';
        $this->comprobada = now()->format('d/m/Y H:i');
    }

    public function render()
    {
        $filtros = $this->filtros();
        $servicio = app(RendimientoEstudiantil::class);
        $panel = app(PanelResultadosAcademicos::class);
        $consulta = $servicio->consulta($filtros);
        $registro = $this->seleccionado ? (clone $consulta)->where('i.cod_ins', $this->seleccionado)->first() : null;
        $detalle = $registro ? $servicio->detalle($registro, $filtros['orientacion'], $this->periodo) : null;
        $estudiantes = (clone $consulta)->orderBy('p.ape_pat_per')->orderBy('p.ape_mat_per')->orderBy('p.nom_per')->orderBy('i.cod_ins')
            ->paginate(in_array($this->perPage, [10, 20, 50], true) ? $this->perPage : 10);
        $estudios = collect();
        if ($filtros['orientacion'] && $estudiantes->isNotEmpty()) {
            $actividades = OrientacionActividad::where('cod_gea', $filtros['gestion'])->whereIn('cod_est', $estudiantes->pluck('cod_est'))
                ->whereRaw('id = (SELECT MAX(actual.id) FROM orientacion_actividades AS actual WHERE actual.cod_est = orientacion_actividades.cod_est AND actual.cod_gea = orientacion_actividades.cod_gea)')->get();
            $estudios = $actividades->mapWithKeys(fn ($actividad) => [$actividad->cod_est => $servicio->estudio($actividad) + ['actividad' => $actividad]]);
        }

        return view('livewire.admin.rendimiento-estudiantes', [
            'estudiantes' => $estudiantes, 'estudios' => $estudios,
            'resumen' => $panel->resumen($consulta), 'grados' => $panel->grados($consulta),
            'years' => GestionAcademica::orderByDesc('ani_gea')->get(),
            'cursos' => Curso::whereHas('inscripciones', fn ($q) => $q->where('cod_gea', $filtros['gestion']))
                ->when($this->nivel !== '', fn ($q) => $q->where('niv_cur', $this->nivel))->orderBy('ord_cur')->get(),
            'niveles' => Curso::whereHas('inscripciones', fn ($q) => $q->where('cod_gea', $filtros['gestion']))->whereNotNull('niv_cur')->distinct()->orderBy('niv_cur')->pluck('niv_cur'),
            'paralelos' => Paralelo::whereHas('inscripciones', fn ($q) => $q->where('cod_gea', $filtros['gestion']))->orderBy('nom_par')->get(),
            'turnos' => Turno::whereIn('cod_tur', InscripcionEstudiante::where('cod_gea', $filtros['gestion'])->select('cod_tur'))->orderBy('nom_tur')->get(),
            'periodos' => PeriodoEvaluacion::whereHas('calificaciones', fn ($q) => $q->whereHas('inscripcionEstudiante', fn ($i) => $i->where('cod_gea', $filtros['gestion'])))->orderBy('ord_pev')->get(),
            'detalle' => $detalle, 'puedeVerOrientacion' => $filtros['orientacion'],
            'grupo' => $this->vista === 'grupo' ? $servicio->grupo($consulta, $filtros['orientacion'], $this->carreraSeleccionada, $this->tipoRelacion) : null,
        ]);
    }
}
