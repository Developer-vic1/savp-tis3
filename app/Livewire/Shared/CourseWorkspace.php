<?php

namespace App\Livewire\Shared;

use App\Models\Oficial\Academico\AsistenciaEstudiante;
use App\Models\Oficial\AulaVirtual\EntregaTarea;
use App\Models\Oficial\Academico\Calificacion;
use App\Services\AulaVirtual\CursoVirtualService;
use App\Services\AulaVirtual\UnitContentService;
use App\Services\GradeService;
use App\Services\RoleDashboardResolver;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

abstract class CourseWorkspace extends Component
{
    use WithPagination;

    protected const ACTOR = '';

    #[Locked]
    public string $curso = '';

    #[Url(as: 'tab', keep: true)]
    public string $tab = 'resumen';

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $state = '';

    #[Locked]
    public ?string $editing = null;

    public function updatedTab(): void
    {
        $this->resetPage();
        $this->editing = null;
        $this->search = '';
        $this->state = '';
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedState(): void
    {
        $this->resetPage();
    }

    public function editar(string $id): void
    {
        $user = auth()->user();
        abort_unless($user && static::ACTOR === 'Docente' && app(RoleDashboardResolver::class)->roleFor($user) === 'Docente', 403);
        $class = app(CursoVirtualService::class)->cursoParaDocente($user, $this->curso);
        abort_unless($class && $class->est_cla === 'ACTIVA', 403);
        abort_unless(in_array($this->tab, ['materiales', 'tareas', 'actividades'], true)
            && $user->can($this->tab === 'materiales' ? 'Materiales_Aula' : 'Tareas_Aula'), 403);
        $record = ($this->tab === 'materiales' ? $class->materiales() : $class->tareas())->findOrFail($id);
        Gate::authorize($this->tab === 'materiales' ? 'update' : 'review', $record);
        abort_if($this->tab !== 'materiales' && $record->est_tar === 'CERRADA', 422);
        $this->editing = $id;
    }

    public function cancelarEdicion(): void
    {
        $this->editing = null;
    }

    #[On('course-updated')]
    public function refreshCourse(): void {}

    public function render()
    {
        $user = auth()->user();
        abort_unless($user && app(RoleDashboardResolver::class)->roleFor($user) === static::ACTOR
            && $user->can('Acceso_Aula_Virtual'), 403);
        $teacher = static::ACTOR === 'Docente';
        $service = app(CursoVirtualService::class);
        $class = $teacher ? $service->cursoParaDocente($user, $this->curso) : $service->cursoParaEstudiante($user, $this->curso);
        abort_unless($class, 403, 'No tienes acceso a este curso.');
        Gate::authorize($teacher ? 'manage' : 'view', $class);
        $student = $teacher ? null : $service->estudianteDeUsuario($user);
        $tabs = [
            'resumen' => ['Resumen', null],
            'contenido' => ['Contenido', 'Materiales_Aula'],
            'materiales' => ['Materiales', 'Materiales_Aula'],
            'actividades' => ['Actividades', 'Actividades_Aula'],
            'tareas' => ['Tareas', 'Tareas_Aula'],
            'entregas' => [$teacher ? 'Entregas' : 'Mis entregas', 'Entregas_Aula'],
            'asistencia' => ['Asistencia', 'Asistencia_Aula'],
            'calificaciones' => ['Calificaciones LMS', 'Calificaciones_Aula'],
            'notas-oficiales' => ['Notas oficiales', $teacher ? 'calificaciones.ver.curso' : 'calificaciones.ver.propias'],
            'calendario' => ['Calendario', 'Calendario_Aula'],
        ];
        if ($teacher) {
            $tabs['estudiantes'] = ['Estudiantes', 'estudiantes.ver.curso'];
            $tabs['kardex'] = ['Kardex', 'estudiantes.ver.curso'];
        }
        $tabs = array_filter($tabs, fn ($tab) => $tab[1] === null || $user->can($tab[1]));
        abort_unless(isset($tabs[$this->tab]), 403);
        $this->validate(['search' => 'string|max:100', 'state' => 'string|max:30']);
        $stateField = match ($this->tab) {
            'materiales' => 'est_mat','contenido' => 'est_pub','tareas','actividades','calendario' => 'est_tar','entregas','calificaciones' => 'est_ent',default => null
        };
        $states = match ($this->tab) {
            'materiales' => $teacher ? ['ACTIVO', 'OCULTO'] : ['ACTIVO'], 'contenido' => $teacher ? ['BORRADOR', 'PUBLICADO', 'OCULTO'] : ['PUBLICADO'],
            'tareas','actividades','calendario' => $teacher ? ['BORRADOR', 'PUBLICADA', 'CERRADA', 'ANULADA'] : ['PUBLICADA', 'CERRADA'], 'entregas','calificaciones' => ['PENDIENTE', 'ENTREGADO', 'ENTREGADO_TARDE', 'DEVUELTO', 'CALIFICADO', 'ANULADO'],default => []
        };
        if ($this->state !== '' && ! in_array($this->state, $states, true)) {
            throw ValidationException::withMessages(['state' => 'Selecciona un estado permitido para esta sección.']);
        }
        $columns = [];
        $query = null;
        switch ($this->tab) {
            case 'contenido':
                $query = $class->publicaciones()->when(! $teacher, fn ($q) => $q->where('est_pub', 'PUBLICADO'));
                $columns = ['tit_pub' => 'Título', 'con_pub' => 'Contenido', 'fec_pub' => 'Fecha'];
                break;
            case 'materiales':
                $query = $class->materiales()->when(! $teacher, fn ($q) => $q->where('est_mat', 'ACTIVO'));
                $columns = ['nom_mat' => 'Material', 'tip_mat' => 'Tipo', 'est_mat' => 'Estado'];
                break;
            case 'tareas':
            case 'actividades':
            case 'calendario':
                $query = $class->tareas()->when(! $teacher, fn ($q) => $q->whereIn('est_tar', ['PUBLICADA', 'CERRADA']))
                    ->when($this->tab === 'calendario', fn ($q) => $q->whereNotNull('fec_lim_tar'))
                    ->when($this->tab === 'actividades', fn ($q) => $q->where('tip_tar', '!=', 'TAREA'));
                $columns = ['tit_tar' => 'Tarea', 'fec_lim_tar' => 'Fecha límite', 'est_tar' => 'Estado', 'pun_max_tar' => 'Puntaje máximo'];
                break;
            case 'entregas':
            case 'calificaciones':
                $query = EntregaTarea::with('tarea', 'estudiante.persona', 'calificacion')
                    ->whereHas('tarea', fn ($q) => $q->where('cod_cla', $class->cod_cla))
                    ->when(! $teacher, fn ($q) => $q->where('cod_est', $student->cod_est))
                    ->when($this->tab === 'calificaciones', fn ($q) => $q->whereHas('calificacion', fn ($g) => $g->whereIn('est_cal', ['REGISTRADO', 'RECTIFICADO'])));
                $columns = ['tarea.tit_tar' => 'Tarea', 'est_ent' => 'Estado', 'fec_ent' => 'Fecha', 'calificacion.pun_obt' => 'Nota LMS', 'calificacion.com_cal' => 'Retroalimentación'];
                if ($teacher) {
                    $columns = ['estudiante.persona.nom_per' => 'Estudiante'] + $columns;
                }
                break;
            case 'asistencia':
                $query = AsistenciaEstudiante::with('estadoAsistencia', 'estudiante.persona', 'asistenciaClase')
                    ->whereHas('asistenciaClase', fn ($q) => $q->where('cod_cla', $class->cod_cla))
                    ->where('est_asi_est', '!=', 'ANULADO')
                    ->when(! $teacher, fn ($q) => $q->where('cod_est', $student->cod_est));
                $columns = ['asistenciaClase.fec_asi_cla' => 'Sesión', 'estadoAsistencia.nom_est_asi' => 'Asistencia', 'min_retraso' => 'Minutos de retraso'];
                if ($teacher) {
                    $columns = ['estudiante.persona.nom_per' => 'Estudiante'] + $columns;
                }
                break;
            case 'notas-oficiales':
                if (app(GradeService::class)->available()) {
                    $query = Calificacion::with('periodoEvaluacion', 'estudiante.persona')->where($class->cod_pas ? 'cod_pas' : 'cod_pes', $class->cod_pas ?? $class->cod_pes)
                        ->whereIn('est_cal', ['VIGENTE', 'RECTIFICADA'])->when(! $teacher, fn ($q) => $q->deEstudiante($student->cod_est));
                    $columns = ['periodoEvaluacion.nom_pev' => 'Periodo', 'not_cal' => 'Nota oficial'];
                    if ($teacher) {
                        $columns = ['estudiante.persona.nom_per' => 'Estudiante'] + $columns;
                    }
                }
                break;
            case 'estudiantes':
                $query = $service->estudiantesVigentes($class)->with('estudiante.persona');
                $columns = ['cod_est' => 'Código', 'estudiante.persona.nom_per' => 'Nombre', 'estudiante.persona.ape_pat_per' => 'Apellido'];
                break;
        }
        if ($query) {
            if ($this->state !== '' && $stateField) {
                $query->where($stateField, $this->state);
            }
            if ($this->search !== '') {
                $text = '%'.trim($this->search).'%';
                match ($this->tab) {
                    'materiales' => $query->where('nom_mat', 'like', $text), 'contenido' => $query->where('tit_pub', 'like', $text),
                    'tareas','actividades','calendario' => $query->where('tit_tar', 'like', $text), 'entregas','calificaciones' => $query->whereHas('tarea', fn ($q) => $q->where('tit_tar', 'like', $text)),
                    'estudiantes','asistencia','notas-oficiales' => $query->whereHas('estudiante.persona', fn ($q) => $q->where(fn ($name) => $name->where('nom_per', 'like', $text)->orWhere('ape_pat_per', 'like', $text))), default => null
                };
            }
        }
        $rows = $query ? ($this->tab === 'calendario' ? $query->orderBy('fec_lim_tar') : $query)->orderByDesc($query->getModel()->getQualifiedKeyName())->paginate(15) : null;
        $summary = $this->tab === 'resumen' ? $service->cursoResumen($class, $student) : [];
        $units = $this->tab === 'contenido' && app(UnitContentService::class)->available()
            ? app(UnitContentService::class)->forCourse($user, $class->cod_cla) : null;

        return view('livewire.shared.course-workspace', compact('class', 'teacher', 'student', 'tabs', 'rows', 'columns', 'summary', 'states', 'units'));
    }
}
