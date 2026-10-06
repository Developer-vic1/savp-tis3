<?php
namespace App\Livewire\Admin;

use App\Models\Oficial\Academico\{Calificacion, Curso, GestionAcademica, PeriodoEvaluacion};
use App\Models\Oficial\AporteAcademicoVocacional\OrientacionActividad;
use App\Support\Evaluacion\PanelResultadosAcademicos;
use App\Services\AporteIngenieril\AporteIngenierilClient;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class Calificaciones extends Component
{
    use WithPagination;
    public string $search = '';
    public string $gestionFiltro = '';
    public string $gradoFiltro = '';
    public string $periodoFiltro = '';
    public string $seguimiento = '';
    public string $vista = 'comparativas';
    public int $perPage = 10;
    #[Locked] public ?string $seleccionado = null;
    #[Locked] public string $conexionEstudio = 'sin_comprobar';
    #[Locked] public ?string $conexionComprobada = null;

    public function boot(): void
    {
        $actor = auth()->user();
        abort_unless($actor?->est_usu === 'ACTIVO' && $actor->hasRole('Administrador') && $actor->can('Calificaciones'), 403);
    }
    public function mount(): void
    {
        $this->gestionFiltro = (string) GestionAcademica::whereIn('est_gea', ['ACTIVA', 'ACTIVO'])->orderByDesc('ani_gea')->value('cod_gea');
        if ($this->gestionFiltro === '') $this->gestionFiltro = (string) GestionAcademica::orderByDesc('ani_gea')->value('cod_gea');
    }
    public function updated($property): void
    {
        if (in_array($property, ['search', 'gestionFiltro', 'gradoFiltro', 'periodoFiltro', 'seguimiento', 'perPage'], true)) {
            $this->seleccionado = null;
            $this->resetPage();
        }
        if (!in_array($this->perPage, [10, 20, 50], true)) $this->perPage = 10;
        if (!in_array($this->vista, ['comparativas', 'proyecto', 'estudiantes'], true)) $this->vista = 'comparativas';
    }
    public function verEstudiante(string $inscripcion): void
    {
        abort_unless(app(PanelResultadosAcademicos::class)->consulta($this->filtros())->where('i.cod_ins', $inscripcion)->exists(), 404);
        $this->seleccionado = $inscripcion;
    }
    public function cerrarDetalle(): void { $this->seleccionado = null; }
    public function comprobarConexion(): void
    {
        abort_unless(auth()->user()->can('orientacion.ver.institucional'), 403);
        $respuesta = app(AporteIngenierilClient::class)->health();
        $this->conexionEstudio = $respuesta->available ? 'disponible' : 'no_disponible';
        $this->conexionComprobada = now()->format('d/m/Y H:i');
    }
    public function verGrado(string $grado): void
    {
        abort_unless(Curso::whereKey($grado)->exists(), 404);
        $this->gradoFiltro = $grado;
        $this->vista = 'estudiantes';
        $this->seleccionado = null;
        $this->resetPage();
    }
    public function limpiarFiltros(): void
    {
        $this->reset('search', 'gradoFiltro', 'periodoFiltro', 'seguimiento', 'seleccionado');
        $this->resetPage();
    }
    // Consulta administrativa: las llamadas directas tampoco pueden alterar notas.
    public function abrirCrear(): void { abort(403, 'Este panel es de consulta institucional.'); }
    public function abrirEditar(string $id): void { abort(403); }
    public function guardar(): void { abort(403); }
    public function cambiarEstado(string $id): void { abort(403); }
    public function descargarReporte()
    {
        $consulta = app(PanelResultadosAcademicos::class)->consulta($this->filtros())->orderBy('i.cod_ins');
        return response()->streamDownload(function () use ($consulta) {
            $salida = fopen('php://output', 'w');
            fwrite($salida, "\xEF\xBB\xBF");
            fputcsv($salida, ['Gestión','Grado','Paralelo','Estudiante','Notas registradas','Promedio observado','Notas de 50 o menos','RIASEC finalizado','Estudio guardado','Retenido oficial','Promovido / egresado oficial','Estado inscripción','Notas marcadas sintéticas'], ';', '"', '');
            foreach ($consulta->cursor() as $e) {
                $nombre = trim($e->nom_per.' '.$e->ape_pat_per.' '.$e->ape_mat_per);
                $verOrientacion = auth()->user()->can('orientacion.ver.institucional');
                $fila = [$e->ani_gea,$e->nom_cur,$e->nom_par,$nombre,$e->notas,$e->promedio,$e->bajas,$verOrientacion ? $e->riasec : 'Acceso restringido',$verOrientacion ? $e->analisis : 'Acceso restringido',$e->retenido,$e->promovido,$e->est_ins,$e->sinteticas];
                $fila = array_map(fn ($v) => preg_match('/^[=+@\-\t\r\n]/', (string)$v) ? "'".$v : $v, $fila);
                fputcsv($salida, $fila, ';', '"', '');
            }
            fclose($salida);
        }, 'seguimiento-academico.csv', ['Content-Type'=>'text/csv; charset=UTF-8']);
    }
    private function filtros(): array
    {
        $verOrientacion = auth()->user()->can('orientacion.ver.institucional');
        return ['gestion' => $this->gestionFiltro, 'grado' => $this->gradoFiltro, 'periodo' => $this->periodoFiltro, 'search' => mb_substr($this->search, 0, 120), 'seguimiento' => !$verOrientacion && $this->seguimiento === 'sin_analisis' ? '' : $this->seguimiento, 'orientacion' => $verOrientacion];
    }
    public function render()
    {
        $panel = app(PanelResultadosAcademicos::class);
        $consulta = $panel->consulta($this->filtros());
        $detalle = null;
        if ($this->seleccionado) {
            $registro = (clone $consulta)->where('i.cod_ins', $this->seleccionado)->first();
            if ($registro) {
                $notas = Calificacion::with(['asignatura', 'planEspecialidad.especialidad', 'periodoEvaluacion', 'inscripcionEstudiante.gestionAcademica'])
                    ->whereHas('inscripcionEstudiante', fn ($q) => $q->where('cod_est', $registro->cod_est))
                    ->whereIn('est_cal', ['VIGENTE', 'RECTIFICADA'])->orderBy('fea_cal')->get();
                $actividades = auth()->user()->can('orientacion.ver.institucional')
                    ? OrientacionActividad::with(['gestionAcademica', 'resultado.carreras', 'respuestas.pregunta', 'respuestas.instrumentoPregunta.orientacionPregunta'])->where('cod_est', $registro->cod_est)->orderByDesc('id')->get() : collect();
                $detalle = compact('registro', 'notas', 'actividades');
            }
        }
        $grados = $panel->grados($consulta);
        $resumen = $panel->resumen($consulta);
        $trayectoria = $panel->periodos($this->filtros());
        $gestiones = $panel->gestiones($this->filtros());
        $graficos = [
            'promedios' => ['labels' => $grados->pluck('nom_cur')->all(), 'tipo' => 'radar', 'unidad' => 'puntos / 100', 'maximo' => 100,
                'series' => [['label' => 'Promedio individual del grado', 'token' => 'primary', 'data' => $grados->map(fn ($g) => $g->promedio === null ? null : round((float) $g->promedio, 2))->all()]]],
            'seguimiento' => ['labels' => ['Con notas, todas > 50', 'Alguna nota ≤ 50', 'Sin notas'], 'tipo' => 'doughnut', 'unidad' => 'estudiantes',
                'series' => [['label' => 'Estudiantes', 'tokens' => ['primary', 'danger', 'muted'], 'data' => [(int)$resumen->evaluados - (int)$resumen->riesgo, (int)$resumen->riesgo, (int)$resumen->total - (int)$resumen->evaluados]]]],
            'periodos' => ['labels' => $trayectoria->pluck('nom_pev')->all(), 'tipo' => 'line', 'unidad' => 'puntos / 100', 'maximo' => 100,
                'series' => [['label' => 'Promedio de notas del período', 'token' => 'info', 'data' => $trayectoria->map(fn ($p) => $p->promedio === null ? null : round((float) $p->promedio, 2))->all()]]],
            'gestiones' => ['labels' => $gestiones->pluck('ani_gea')->all(), 'tipo' => 'line', 'unidad' => 'estudiantes',
                'series' => [
                    ['label' => 'Promoción / egreso oficial', 'token' => 'primary', 'data' => $gestiones->map(fn ($g) => $g->promovidos + $g->retenidos > 0 ? (int)$g->promovidos : null)->all()],
                    ['label' => 'Retención oficial', 'token' => 'danger', 'data' => $gestiones->map(fn ($g) => $g->promovidos + $g->retenidos > 0 ? (int)$g->retenidos : null)->all()],
                    ['label' => 'Retiros documentados', 'token' => 'warning', 'data' => $gestiones->map(fn ($g) => (int)$g->retirados)->all()],
                ]],
        ];
        return view('livewire.admin.calificaciones', [
            'estudiantes' => (clone $consulta)->orderBy('p.ape_pat_per')->orderBy('p.ape_mat_per')->orderBy('p.nom_per')->paginate($this->perPage),
            'resumen' => $resumen, 'grados' => $grados,
            'destacados' => $panel->destacados($consulta, auth()->user()->can('orientacion.ver.institucional')),
            'trayectoria' => $trayectoria, 'gestiones' => $gestiones, 'graficos' => $graficos,
            'perfiles' => $panel->perfiles($this->filtros()),
            'proyecto' => $this->vista === 'proyecto' ? $panel->estudios($this->filtros()) : null,
            'years' => GestionAcademica::orderByDesc('ani_gea')->get(), 'cursos' => Curso::orderBy('ord_cur')->get(),
            'periodos' => PeriodoEvaluacion::orderBy('ord_pev')->get(), 'detalle' => $detalle,
            'puedeVerOrientacion' => auth()->user()->can('orientacion.ver.institucional'),
        ]);
    }
}
