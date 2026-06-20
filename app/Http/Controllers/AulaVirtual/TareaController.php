<?php

namespace App\Http\Controllers\AulaVirtual;

use App\Http\Controllers\Controller;
use App\Models\AulaVirtual\Tarea;
use App\Services\AulaVirtual\CursoVirtualService;
use App\Services\AulaVirtual\TareaService;
use Illuminate\Http\Request;

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
        $datos['perm_ent_tardia'] = $request->boolean('perm_ent_tardia');
        $this->tareas->crear($datos, $docente);

        return back()->with('status', 'Tarea guardada.');
    }

    public function entregar(Request $request, Tarea $tarea)
    {
        abort_if(! $this->cursos->cursoParaEstudiante($request->user(), $tarea->cod_cla), 403);

        return view('aula-virtual.tareas.entregar', [
            'tarea' => $tarea->load('claseVirtual.planAsignatura.asignatura', 'materiales'),
            'estudiante' => $this->cursos->estudianteDeUsuario($request->user()),
        ]);
    }

    public function revisar(Request $request, Tarea $tarea)
    {
        abort_if(! $this->cursos->cursoParaDocente($request->user(), $tarea->cod_cla), 403);

        return view('aula-virtual.tareas.revisar', [
            'tarea' => $tarea->load('entregas.estudiante.persona', 'entregas.archivos', 'entregas.calificacion'),
        ]);
    }
}
