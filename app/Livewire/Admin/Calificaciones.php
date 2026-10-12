<?php
namespace App\Livewire\Admin;

use App\Models\Oficial\Academico\{Calificacion, Curso, GestionAcademica, PeriodoEvaluacion};
use App\Models\Oficial\AporteAcademicoVocacional\OrientacionActividad;
use App\Support\Evaluacion\{PanelResultadosAcademicos, CuadroHonorAcademico};
use App\Services\AporteIngenieril\AporteIngenierilClient;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class Calificaciones extends Component
{
    use WithPagination;
    public string $search = '';
    public string $gestionFiltro = '';
    public string $gradoFiltro = '';
    public string $periodoFiltro = '';
    public string $seguimiento = '';
    public string $vista = 'honor';
    public string $orden = 'promedio';
    public int $perPage = 10;
    #[Locked] public ?string $datosLeidos = null;
    #[Locked] public ?string $seleccionado = null;
    #[Locked] public bool $detalleHonor = false;
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
            $this->detalleHonor = false;
            $this->resetPage();
        }
        if (!in_array($this->perPage, [10, 20, 50], true)) $this->perPage = 10;
        if (!in_array($this->vista, ['honor', 'comparativas', 'proyecto', 'estudiantes'], true)) $this->vista = 'honor';
        if (!in_array($this->orden, ['promedio', 'nombre'], true)) $this->orden = 'promedio';
        if ($property === 'orden') $this->resetPage();
        if ($property === 'gestionFiltro') {
            $this->limpiarFiltros();
            $this->vista = 'honor';
        }
    }
    public function verEstudiante(string $inscripcion): void
    {
        abort_unless(app(PanelResultadosAcademicos::class)->consulta($this->filtros())->where('i.cod_ins', $inscripcion)->exists(), 404);
        $this->seleccionado = $inscripcion;
        $this->detalleHonor = false;
    }
    public function cerrarDetalle(): void { $this->seleccionado = null; $this->detalleHonor = false; }
    public function verHonor(string $inscripcion): void
    {
        abort_unless(strlen($inscripcion) <= 30 && app(CuadroHonorAcademico::class)->consulta($this->gestionFiltro)->where('i.cod_ins', $inscripcion)->exists(), 404);
        $this->seleccionado = $inscripcion;
        $this->detalleHonor = true;
    }
    public function cambiarGestionHonor(string $gestion): void
    {
        abort_unless(strlen($gestion) <= 30 && GestionAcademica::whereKey($gestion)->exists(), 404);
        $this->gestionFiltro = $gestion;
        $this->limpiarFiltros();
        $this->vista = 'honor';
    }
    public function cambiarVista(string $vista): void
    {
        abort_unless(in_array($vista, ['honor', 'comparativas', 'proyecto', 'estudiantes'], true), 422);
        $this->cerrarDetalle();
        $this->vista = $vista;
    }
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
        $this->reset('search', 'gradoFiltro', 'periodoFiltro', 'seguimiento', 'seleccionado', 'detalleHonor');
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
    public function descargarHonor()
    {
        $honor = app(CuadroHonorAcademico::class)->resumen($this->gestionFiltro, false);
        return response()->streamDownload(function () use ($honor) {
            $salida = fopen('php://output', 'w');
            fwrite($salida, "\xEF\xBB\xBF");
            fputcsv($salida, ['Propuesta académica · requiere revisión', 'Gestión', 'Grado', 'Paralelo', 'Puesto', 'Estudiante', 'Promedio observado / 100', 'Notas', 'Empate', 'Estado de evidencia'], ';', '"', '');
            $grupos = $honor['grados']->map(fn ($g) => ['tipo' => 'Reconocimiento por grado', 'estudiantes' => $g['estudiantes']]);
            $grupos->push(['tipo' => 'Candidatura de promoción', 'estudiantes' => $honor['promocion']['estudiantes']]);
            foreach ($grupos as $grupo) foreach ($grupo['estudiantes'] as $e) {
                $fila = [$grupo['tipo'], $e->ani_gea, $e->nom_cur, $e->nom_par, $e->puesto,
                    trim($e->nom_per.' '.$e->ape_pat_per.' '.$e->ape_mat_per), number_format((float)$e->promedio, 2, '.', ''), $e->notas, $e->empatado ? 'Sí' : 'No', $e->estado_honor];
                $fila = array_map(fn ($v) => preg_match('/^[=+@\-\t\r\n]/', (string)$v) ? "'".$v : $v, $fila);
                fputcsv($salida, $fila, ';', '"', '');
            }
            fclose($salida);
        }, 'cuadro-honor-propuestas.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
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
            $detalleConsulta = $this->detalleHonor ? app(CuadroHonorAcademico::class)->consulta($this->gestionFiltro) : clone $consulta;
            $registro = $detalleConsulta->where('i.cod_ins', $this->seleccionado)->first();
            if ($registro) {
                $notas = Calificacion::with(['asignatura', 'planEspecialidad.especialidad', 'periodoEvaluacion', 'inscripcionEstudiante.gestionAcademica'])
                    ->whereHas('inscripcionEstudiante', fn ($q) => $q->where('cod_est', $registro->cod_est))
                    ->whereIn('est_cal', ['VIGENTE', 'RECTIFICADA'])->orderBy('fea_cal')->get();
                $actividades = auth()->user()->can('orientacion.ver.institucional')
                    ? OrientacionActividad::with(['gestionAcademica', 'resultado.carreras', 'respuestas.pregunta', 'respuestas.instrumentoPregunta.orientacionPregunta'])->where('cod_est', $registro->cod_est)->orderByDesc('id')->get() : collect();
                $detalle = compact('registro', 'notas', 'actividades');
            }
        }
        return view('livewire.admin.calificaciones', $this->datosLectura() + ['detalle' => $detalle]);
    }
    private function claveLectura(string $vista): string
    {
        $conexion = config('database.default');
        return 'calificaciones:lectura:v2:'.hash('sha256', json_encode([
            $conexion, config('database.connections.'.$conexion.'.database'),
            auth()->id(), auth()->user()->can('orientacion.ver.institucional'), $vista,
        ]));
    }
    private function datosLectura(): array
    {
        $cache = Cache::store(config('calificaciones.cache_store', 'file'));
        $clave = $this->claveLectura($this->vista);
        $firma = hash('sha256', json_encode([$this->filtros(), $this->orden, $this->perPage, $this->getPage()]));
        $lectura = $cache->get($clave);
        if (!is_array($lectura) || ($lectura['firma'] ?? '') !== $firma) {
            // El FileStore vigente restringe la deserialización de clases: guardar sólo JSON y escalares.
            $lectura = ['firma' => $firma, 'datos' => json_encode($this->construirDatosPanel(), JSON_THROW_ON_ERROR), 'fecha' => now()->format('H:i:s')];
            // Cuatro entradas máximas por actor/ámbito; la búsqueda reemplaza la lectura anterior.
            $cache->put($clave, $lectura, 20);
        }
        $this->datosLeidos = $lectura['fecha'];
        return $this->restaurarLectura($lectura['datos']);
    }
    private function restaurarLectura(string $json): array
    {
        $datos = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        $registros = fn ($filas) => collect($filas)->map(fn ($fila) => (object)$fila);
        $datos['resumen'] = (object)$datos['resumen'];
        foreach (['grados','trayectoria','gestiones','perfiles','years','cursos','periodos'] as $campo) $datos[$campo] = $registros($datos[$campo]);
        $p = $datos['estudiantes'];
        $datos['estudiantes'] = new LengthAwarePaginator($registros($p['data']), $p['total'], $p['per_page'], $p['current_page'], ['path'=>$p['path'],'pageName'=>'page']);
        foreach ($datos['destacados'] as &$destacado) {
            $destacado['registro'] = (object)$destacado['registro'];
            if ($destacado['fecha']) $destacado['fecha'] = \Carbon\Carbon::parse($destacado['fecha']);
        }
        unset($destacado);
        if ($datos['honor']) {
            foreach (['grados','historial'] as $campo) {
                $datos['honor'][$campo] = collect($datos['honor'][$campo])->map(function ($grupo) use ($registros) {
                    $grupo['estudiantes'] = $registros($grupo['estudiantes']);
                    return $grupo;
                });
            }
            $datos['honor']['promocion']['estudiantes'] = $registros($datos['honor']['promocion']['estudiantes']);
        }
        return $datos;
    }
    public function actualizarDatos(): void
    {
        $cache = Cache::store(config('calificaciones.cache_store', 'file'));
        foreach (['honor', 'comparativas', 'proyecto', 'estudiantes'] as $vista) $cache->forget($this->claveLectura($vista));
    }
    private function construirDatosPanel(): array
    {
        $panel = app(PanelResultadosAcademicos::class);
        $consulta = $panel->consulta($this->filtros());
        $grados = $this->vista === 'honor' ? collect() : $panel->grados($consulta);
        $resumen = $panel->resumen($consulta);
        $trayectoria = $this->vista === 'comparativas' ? $panel->periodos($this->filtros()) : collect();
        $gestiones = $this->vista === 'comparativas' ? $panel->gestiones($this->filtros()) : collect();
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
        $listado = (clone $consulta)->when($this->orden !== 'nombre', fn ($q) => $q->orderByRaw('CASE WHEN n.promedio IS NULL THEN 1 ELSE 0 END')->orderByDesc('n.promedio'))
            ->orderBy('p.ape_pat_per')->orderBy('p.ape_mat_per')->orderBy('p.nom_per')->orderBy('i.cod_ins');
        $cantidad = in_array($this->perPage, [10,20,50], true) ? $this->perPage : 10;
        $pagina = max(1, (int)$this->getPage());
        // El resumen ya contó la misma consulta; no repetir el COUNT de paginate().
        $estudiantes = new LengthAwarePaginator($listado->forPage($pagina, $cantidad)->get(), (int)$resumen->total, $cantidad, $pagina,
            ['path' => LengthAwarePaginator::resolveCurrentPath(), 'pageName' => 'page']);
        return [
            'estudiantes' => $estudiantes,
            'resumen' => $resumen, 'grados' => $grados,
            'destacados' => $this->vista === 'honor' ? [] : $panel->destacados($consulta, auth()->user()->can('orientacion.ver.institucional')),
            'honor' => $this->vista === 'honor' ? app(CuadroHonorAcademico::class)->resumen($this->gestionFiltro, auth()->user()->can('orientacion.ver.institucional')) : null,
            'trayectoria' => $trayectoria, 'gestiones' => $gestiones, 'graficos' => $graficos,
            'perfiles' => $this->vista === 'honor' ? collect() : $panel->perfiles($this->filtros()),
            'proyecto' => $this->vista === 'proyecto' ? $panel->estudios($this->filtros()) : null,
            'years' => GestionAcademica::orderByDesc('ani_gea')->get(), 'cursos' => Curso::orderBy('ord_cur')->get(),
            'periodos' => PeriodoEvaluacion::orderBy('ord_pev')->get(),
            'puedeVerOrientacion' => auth()->user()->can('orientacion.ver.institucional'),
        ];
    }

}
