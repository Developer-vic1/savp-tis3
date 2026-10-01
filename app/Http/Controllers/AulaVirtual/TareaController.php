<?php

namespace App\Http\Controllers\AulaVirtual;

use App\Http\Controllers\Controller;
use App\Models\AulaVirtual\EntregaTarea;
use App\Models\AulaVirtual\Tarea;
use App\Models\AulaVirtual\TareaMaterial;
use App\Services\AulaVirtual\CursoVirtualService;
use App\Services\AulaVirtual\TareaService;
use App\Support\PrivateFilePath;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TareaController extends Controller
{
    public function __construct(
        private readonly CursoVirtualService $cursos,
        private readonly TareaService $tareas,
    ) {}

    public function store(Request $request, string $curso)
    {
        $clase = $this->cursos->cursoParaDocente($request->user(), $curso);
        $docente = $this->cursos->docenteDeUsuario($request->user());
        abort_if(! $clase || ! $docente, 403);

        $datos = $request->validate([
            'tit_tar' => ['required', 'string', 'max:180'],
            'des_tar' => ['nullable', 'string'],
            'tip_tar' => ['required', 'in:TAREA,PRACTICA,PROYECTO,INVESTIGACION,LABORATORIO,EVALUACION'],
            'fec_lim_tar' => ['nullable', 'date', 'after_or_equal:today'],
            'pun_max_tar' => ['required', 'numeric', 'min:1', 'max:1000'],
            'perm_ent_tardia' => ['nullable', 'boolean'],
            'est_tar' => ['nullable', 'in:BORRADOR,PUBLICADA'],
        ]);

        $datos['cod_cla'] = $clase->cod_cla;
        $datos['est_tar'] = $datos['est_tar'] ?? 'BORRADOR';
        $datos['perm_ent_tardia'] = $request->boolean('perm_ent_tardia');
        $this->tareas->crear($datos, $docente, $request->file('archivo'));

        return back()->with('status', 'Tarea guardada.');
    }

    public function entregar(Request $request, Tarea $tarea)
    {
        abort_unless(in_array($tarea->est_tar, ['PUBLICADA', 'CERRADA'], true), 404);
        abort_if(! $this->cursos->cursoParaEstudiante($request->user(), $tarea->cod_cla), 403);

        $estudiante = $this->cursos->estudianteDeUsuario($request->user());
        $entrega = EntregaTarea::query()
            ->with(['archivos' => fn ($query) => $query->where('est_arc', 'ACTIVO'), 'calificacion'])
            ->where('cod_tar', $tarea->cod_tar)
            ->where('cod_est', $estudiante->cod_est)
            ->first();

        return view('aula-virtual.tareas.entregar', [
            'tarea' => $tarea->load(['claseVirtual.planAsignatura.asignatura', 'materiales' => fn ($query) => $query->where('est_tar_mat', 'ACTIVO')]),
            'estudiante' => $estudiante,
            'entrega' => $entrega,
        ]);
    }

    public function revisar(Request $request, Tarea $tarea)
    {
        abort_if(! $this->cursos->cursoParaDocente($request->user(), $tarea->cod_cla), 403);
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'in:PENDIENTE,ENTREGADO,ENTREGADO_TARDE,CALIFICADO,DEVUELTO,ANULADO'],
        ]);
        $entregas = $tarea->entregas()->with([
            'estudiante.persona', 'archivos' => fn ($q) => $q->where('est_arc', 'ACTIVO'), 'calificacion',
        ])->when(filled($filters['search'] ?? null), fn ($q) => $q->whereHas('estudiante.persona', fn ($person) => $person->where(fn ($names) => $names->where('nom_per', 'like', '%'.$filters['search'].'%')
            ->orWhere('ape_pat_per', 'like', '%'.$filters['search'].'%')->orWhere('ape_mat_per', 'like', '%'.$filters['search'].'%'))))
            ->when(filled($filters['state'] ?? null), fn ($q) => $q->where('est_ent', $filters['state']))
            ->orderByDesc('fec_ent')->orderBy('cod_ent')->paginate(20)->withQueryString();

        return view('aula-virtual.tareas.revisar', [
            'tarea' => $tarea,
            'entregas' => $entregas,
            'filters' => $filters,
        ]);
    }

    public function descargarMaterial(Request $request, TareaMaterial $archivo)
    {
        $task = $archivo->tarea;
        $teacher = $this->cursos->cursoParaDocente($request->user(), $task->cod_cla);
        $student = $this->cursos->cursoParaEstudiante($request->user(), $task->cod_cla);
        abort_unless($teacher || ($student && in_array($task->est_tar, ['PUBLICADA', 'CERRADA'], true)), 403);
        abort_unless($archivo->est_tar_mat === 'ACTIVO' && PrivateFilePath::valid($archivo->rut_tar_mat, 'aula-virtual/tareas'), 404);
        $disk = Storage::disk('local');
        abort_unless($disk->exists($archivo->rut_tar_mat), 404);

        return $disk->download($archivo->rut_tar_mat, basename($archivo->rut_tar_mat), ['X-Content-Type-Options' => 'nosniff']);
    }
}
