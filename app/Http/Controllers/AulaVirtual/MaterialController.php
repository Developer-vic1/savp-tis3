<?php

namespace App\Http\Controllers\AulaVirtual;

use App\Http\Controllers\Controller;
use App\Models\AulaVirtual\MaterialClase;
use App\Services\AulaVirtual\CursoVirtualService;
use App\Services\AulaVirtual\MaterialService;
use Illuminate\Http\Request;

class MaterialController extends Controller
{
    public function __construct(
        private readonly CursoVirtualService $cursos,
        private readonly MaterialService $materiales,
    ) {}

    public function index(Request $request, string $curso)
    {
        $clase = $this->cursoAutorizado($request, $curso);

        return view('aula-virtual.materiales.index', [
            'curso' => $clase,
            'materiales' => $clase->materiales()->latest()->get(),
        ]);
    }

    public function store(Request $request, string $curso)
    {
        $clase = $this->cursos->cursoParaDocente($request->user(), $curso);
        abort_if(! $clase, 403);

        $datos = $request->validate([
            'nom_mat' => ['required', 'string', 'max:180'],
            'tip_mat' => ['required', 'in:ARCHIVO,ENLACE,PDF,VIDEO,IMAGEN,DOCUMENTO,OTRO'],
            'url_mat' => ['nullable', 'url', 'max:500'],
            'archivo' => ['nullable', 'file', 'max:10240', 'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,jpg,jpeg,png,mp4,txt'],
            'est_mat' => ['nullable', 'in:ACTIVO,OCULTO'],
        ]);

        $datos['cod_cla'] = $clase->cod_cla;
        $datos['est_mat'] = $datos['est_mat'] ?? 'ACTIVO';

        $this->materiales->crear($datos, $request->user(), $request->file('archivo'));

        return back()->with('status', 'Material guardado.');
    }

    public function descargar(Request $request, MaterialClase $material)
    {
        abort_if(! $this->puedeVerCurso($request, $material->cod_cla), 403);

        return $this->materiales->descargar($material);
    }

    public function publicar(Request $request, MaterialClase $material)
    {
        abort_if(! $this->cursos->cursoParaDocente($request->user(), $material->cod_cla), 403);
        $material->forceFill(['est_mat' => 'ACTIVO'])->save();

        return back()->with('status', 'Material publicado.');
    }

    public function ocultar(Request $request, MaterialClase $material)
    {
        abort_if(! $this->cursos->cursoParaDocente($request->user(), $material->cod_cla), 403);
        $material->forceFill(['est_mat' => 'OCULTO'])->save();

        return back()->with('status', 'Material oculto.');
    }

    private function cursoAutorizado(Request $request, string $curso)
    {
        return $this->cursos->cursoParaEstudiante($request->user(), $curso)
            ?? $this->cursos->cursoParaDocente($request->user(), $curso)
            ?? abort(403);
    }

    private function puedeVerCurso(Request $request, string $curso): bool
    {
        return (bool) ($this->cursos->cursoParaEstudiante($request->user(), $curso)
            ?? $this->cursos->cursoParaDocente($request->user(), $curso));
    }
}
