<?php

namespace App\Livewire\Admin;

use App\Models\Oficial\Academico\PersonalInstitucional;
use App\Support\Comunidad\ConsultaDocentes;
use App\Support\Comunidad\HorarioPersonal;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class GestionDocente extends Component
{
    use WithPagination, \App\Livewire\Admin\Concerns\SeleccionaEspecialidadDocente;

    public string $search = '';
    public string $gestion = '';
    public string $materia = '';
    public string $curso = '';
    public string $carga = '';
    public string $acceso = '';
    public string $perfil = '';
    public string $orden = 'nombre';
    public string $vistaActiva = 'tarjetas';
    public int $perPage = 10;
    #[Locked]
    public ?string $detalle = null;
    public string $seccion = 'perfil';

    public bool $modalEditar=false;
    #[Locked] public ?\App\Models\Oficial\Academico\Docente $docenteDetalle=null;
    public int $maxModificaciones=3;
    public function abrirModalEditar(string $codigo): void
    {
        Gate::authorize('Docentes');Gate::authorize('Personal_Institucional');
        abort_unless(app(ConsultaDocentes::class)->consultar($this->gestion,$codigo)->isNotEmpty(),404);
        $this->detalle=null;$this->resetValidation();
        $this->docenteDetalle=\App\Models\Oficial\Academico\Docente::with('personalInstitucional.persona')->findOrFail($codigo);
        $this->prepararSeleccionEspecialidad($this->docenteDetalle);$this->modalEditar=true;
    }
    public function cerrarModalEditar(): void {$this->modalEditar=false;$this->docenteDetalle=null;$this->perfilDocenteCodigo='';$this->resetValidation();}
    public function actualizarDocente(): void {$this->guardarPerfilEspecialidad();}

    public function mount(): void
    {
        $gestiones = app(ConsultaDocentes::class)->gestiones();
        $this->gestion = (string) ($gestiones->firstWhere('est_gea', 'ACTIVO') ?? $gestiones->first())?->getKey();
    }

    public function updated(string $propiedad): void
    {
        if ($propiedad === 'search') $this->validateOnly('search', ['search'=>'string|max:100'], ['search.max'=>'Escribe hasta 100 caracteres para buscar.']);
        $permitidos = ['acceso'=>['','ACTIVO','INACTIVO','SIN_CUENTA'],'carga'=>['','CON_CARGA','SIN_CARGA','TECNICA','MATERIA'],'perfil'=>['','COMPLETO','PENDIENTE'],'orden'=>['nombre','horas_desc','horas_asc','perfil']];
        if (isset($permitidos[$propiedad]) && ! in_array($this->$propiedad, $permitidos[$propiedad], true)) {
            $this->$propiedad = $propiedad === 'orden' ? 'nombre' : '';
            $this->addError($propiedad, 'Elige una opción disponible para continuar.');
        } else { $this->resetValidation($propiedad); }
        if (in_array($propiedad, ['search', 'gestion', 'materia', 'curso', 'carga', 'acceso', 'perfil', 'orden', 'perPage'], true)) $this->resetPage();
        if ($propiedad === 'gestion') {
            $this->materia = $this->curso = '';
            $this->detalle = null;
        }
        if (! in_array($this->perPage, [10, 20, 50], true)) $this->perPage = 10;
        if (! in_array($this->vistaActiva, ['tarjetas', 'lista', 'tabla', 'cargas'], true)) $this->vistaActiva = 'tarjetas';
    }

    public function limpiarFiltros(): void
    {
        $this->search = $this->materia = $this->curso = $this->carga = $this->acceso = $this->perfil = '';
        $this->orden = 'nombre';
        $this->resetValidation();
        $this->resetPage();
    }

    public function abrirFicha(string $clave, string $seccion = 'perfil'): void
    {
        Gate::authorize('Docentes');
        abort_unless(app(ConsultaDocentes::class)->consultar($this->gestion, $clave)->isNotEmpty(), 404);
        $this->detalle = $clave;
        $this->seccion = in_array($seccion, ['perfil', 'horario', 'asignaciones', 'trayectoria'], true) ? $seccion : 'perfil';
    }

    public function cerrarFicha(): void { $this->detalle = null; }

    public function render()
    {
        Gate::authorize('Docentes');
        $consulta = app(ConsultaDocentes::class);
        $gestiones = $consulta->gestiones();
        if (! $gestiones->contains('cod_gea', $this->gestion)) {
            $this->gestion = (string) ($gestiones->firstWhere('est_gea', 'ACTIVO') ?? $gestiones->first())?->getKey();
        }
        $registros = $consulta->consultar($this->gestion);
        $filas = $registros->map(fn ($docente) => $consulta->ficha($docente));
        $materias = $filas->flatMap(fn ($fila) => $fila['materias'])->unique()->sort()->values();
        $cursos = $filas->flatMap(fn ($fila) => $fila['cursos'])->unique()->sort()->values();
        $busqueda = mb_strtolower(trim(mb_substr($this->search, 0, 100)));
        $seleccion = $filas->filter(fn ($fila) =>
            ($busqueda === '' || str_contains(mb_strtolower(implode(' ', [$fila['nombre'], $fila['ci'], $fila['perfil'], implode(' ', $fila['materias'])])), $busqueda))
            && ($this->materia === '' || in_array($this->materia, $fila['materias'], true))
            && ($this->curso === '' || in_array($this->curso, $fila['cursos'], true))
            && ($this->acceso === '' || $this->acceso === $fila['acceso'])
            && ($this->perfil === '' || ($this->perfil === 'COMPLETO') === $fila['completo'])
            && ($this->carga === '' || match ($this->carga) {
                'SIN_CARGA' => $fila['horas'] == 0,
                'CON_CARGA' => $fila['horas'] > 0,
                'TECNICA' => $fila['horas_tecnicas'] > 0,
                'MATERIA' => $fila['horas_materias'] > 0,
                default => false,
            })
        );
        $seleccion = match ($this->orden) {
            'horas_desc' => $seleccion->sortByDesc('horas'), 'horas_asc' => $seleccion->sortBy('horas'),
            'perfil' => $seleccion->sortBy('perfil'), default => $seleccion->sortBy('nombre'),
        };
        $grupos = $seleccion->flatMap(fn ($fila) => collect($fila['activos'])->map(fn ($plan) => $plan + ['docente' => $fila['clave']]))
            ->groupBy('nombre')->map(fn ($planes, $nombre) => ['nombre' => $nombre, 'horas' => round($planes->sum('horas'), 2), 'docentes' => $planes->pluck('docente')->unique()->count(), 'grupos' => $planes->pluck('grupo')->unique()->count()])->sortByDesc('horas')->values();
        $mapaCarga = $seleccion->groupBy(fn($fila)=>$fila['horas'].'|'.count($fila['cursos']))->map(fn($grupo)=>['horas'=>$grupo->first()['horas'],'grupos'=>count($grupo->first()['cursos']),'docentes'=>$grupo->map(fn($fila)=>['clave'=>$fila['clave'],'nombre'=>$fila['nombre']])->values()->all()])->values();
        $cantidad = in_array($this->perPage, [10, 20, 50], true) ? $this->perPage : 10;
        $pagina = min(max(1, $this->getPage()), max(1, (int) ceil($seleccion->count() / $cantidad)));
        $docentes = new LengthAwarePaginator($seleccion->values()->forPage($pagina, $cantidad), $seleccion->count(), $cantidad, $pagina);
        $ficha = null;
        $horario = [];
        $docenteFicha = $this->detalle ? $registros->firstWhere('cod_doc', $this->detalle) : null;
        if ($docenteFicha) {
            foreach (['formacionDocenteRegistros'=>'formacion_docente','experienciaDocenteRegistros'=>'experiencia_docente','capacitacionDocenteRegistros'=>'capacitacion_docente'] as $relacion=>$tabla) {
                if (Schema::hasTable($tabla)) $docenteFicha->load($relacion);
            }
            $ficha = $consulta->ficha($docenteFicha);
            $personal = PersonalInstitucional::find($docenteFicha->cod_pin);
            if ($personal && $this->seccion === 'horario') {
                $personal->setRelation('docente', $docenteFicha);
                $horario = app(HorarioPersonal::class)->consultar($personal, $this->gestion);
            }
        }
        return view('livewire.admin.gestion-docente', compact('docentes', 'gestiones', 'materias', 'cursos', 'grupos', 'ficha', 'horario', 'docenteFicha','mapaCarga') + [
            'nombreGestion' => $gestiones->firstWhere('cod_gea', $this->gestion)?->ani_gea ?: 'Sin gestión',
            'resumen' => ['total' => $filas->count(), 'con_carga' => $filas->where('horas', '>', 0)->count(), 'horas' => round($filas->sum('horas'), 2), 'grupos' => $filas->flatMap(fn ($f) => $f['cursos'])->unique()->count()],
            'indicadores' => ['total' => $seleccion->count(), 'materias' => round($seleccion->sum('horas_materias'), 2), 'tecnicas' => round($seleccion->sum('horas_tecnicas'), 2), 'sin_carga' => $seleccion->where('horas', 0)->count()],
        ]);
    }
}
