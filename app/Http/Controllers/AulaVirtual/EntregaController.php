<?php

namespace App\Http\Controllers\AulaVirtual;

use App\Http\Controllers\Controller;
use App\Models\AulaVirtual\EntregaArchivo;
use App\Models\AulaVirtual\EntregaTarea;
use App\Models\AulaVirtual\Tarea;
use App\Services\AulaVirtual\CursoVirtualService;
use App\Services\AulaVirtual\EntregaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EntregaController extends Controller
{
    public function __construct(
        private readonly CursoVirtualService $cursos,
        private readonly EntregaService $entregas,
    ) {}

    public function store(Request $request, Tarea $tarea)
    {
        abort_if(! $this->cursos->cursoParaEstudiante($request->user(), $tarea->cod_cla), 403);
        abort_if(! $tarea->puedeRecibirEntregas(), 422, 'La tarea no recibe entregas en este momento.');

        $datos = $request->validate([
            'tex_ent' => ['nullable', 'string'],
            'accion' => ['required', 'in:guardar,enviar'],
            'archivo' => ['nullable', 'file', 'max:10240', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,txt,zip'],
        ]);

        $estudiante = $this->cursos->estudianteDeUsuario($request->user());
        abort_if(! $estudiante, 403);

        $this->entregas->guardarEntrega($tarea, $estudiante, $datos, $request->file('archivo'));

        return redirect()->route('aula-virtual.estudiante.curso', $tarea->cod_cla)->with('status', 'Entrega guardada.');
    }

    public function calificar(Request $request, EntregaTarea $entrega)
    {
        $entrega->load('tarea');
        $docente = $this->cursos->docenteDeUsuario($request->user());
        abort_if(! $docente || ! $this->cursos->cursoParaDocente($request->user(), $entrega->tarea->cod_cla), 403);

        $datos = $request->validate([
            'pun_obt' => ['required', 'numeric', 'min:0'],
            'com_cal' => ['nullable', 'string'],
        ]);

        $this->entregas->calificar($entrega, $docente, (float) $datos['pun_obt'], $datos['com_cal'] ?? null);

        return back()->with('status', 'Calificación guardada.');
    }

    public function devolver(Request $request, EntregaTarea $entrega)
    {
        $entrega->load('tarea');
        abort_if(! $this->cursos->cursoParaDocente($request->user(), $entrega->tarea->cod_cla), 403);

        $datos = $request->validate(['obs_ent' => ['required', 'string', 'max:2000']]);
        $entrega->devolver($datos['obs_ent']);

        return back()->with('status', 'Observación enviada.');
    }

    public function descargar(Request $request, EntregaArchivo $archivo)
    {
        $archivo->load('entrega.tarea');
        $tarea = $archivo->entrega->tarea;
        $estudiante = $this->cursos->estudianteDeUsuario($request->user());
        $esPropia = $estudiante && $archivo->entrega->cod_est === $estudiante->cod_est;
        $esDocente = (bool) $this->cursos->cursoParaDocente($request->user(), $tarea->cod_cla);

        abort_if(! $esPropia && ! $esDocente, 403);
        abort_if(! Storage::exists($archivo->rut_arc), 404);

        return Storage::download($archivo->rut_arc, $archivo->nom_arc);
    }
}
