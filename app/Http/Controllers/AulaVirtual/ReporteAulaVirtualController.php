<?php

namespace App\Http\Controllers\AulaVirtual;

use App\Http\Controllers\Controller;
use App\Services\AulaVirtual\CursoVirtualService;
use App\Services\AulaVirtual\ReporteAulaVirtualService;
use App\Services\BitacoraService;
use App\Services\Reportes\GeneradorMpdfService;
use App\Services\RoleDashboardResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ReporteAulaVirtualController extends Controller
{
    public function __construct(
        private readonly CursoVirtualService $cursos,
        private readonly ReporteAulaVirtualService $reportes,
    ) {}

    public function index(Request $request)
    {
        $filters = $request->validate(['search' => ['nullable', 'string', 'max:100'], 'gestion' => ['nullable', 'digits:4']]);
        $cursos = $this->cursos->paginarCursos($request->user(), true, $filters['search'] ?? '', $filters['gestion'] ?? '');

        return view('aula-virtual.reportes.index', [
            'cursos' => $cursos,
            'consolidados' => $cursos->getCollection()->map(fn ($curso) => $this->reportes->consolidadoCurso($curso)),
            'filters' => $filters,
        ]);
    }

    public function pdf(Request $request, string $curso, GeneradorMpdfService $pdf)
    {
        abort_unless(app(RoleDashboardResolver::class)->roleFor($request->user()) === 'Docente'
            && $request->user()->can('Reportes_Aula'), 403);
        $class = $this->cursos->cursoParaDocente($request->user(), $curso);
        abort_unless($class, 403);
        $data = $this->reportes->consolidadoCurso($class);
        $filename = 'consolidado-'.Str::uuid().'.pdf';
        $relative = 'reportes/aula/'.$filename;
        try {
            Storage::disk('local')->makeDirectory('reportes/aula');
            Storage::disk('local')->makeDirectory('reportes/temp');
            $relative = $pdf->generar('pdf.reportes.curso-aula', ['reporte' => $data, 'fecha' => now()], $filename, 'aula');
            BitacoraService::registrar(accion: 'EXPORTAR_CONSOLIDADO_CURSO', tabla: 'clase_virtual', registro: $curso, modulo: 'Aula Virtual');

            return response()->download(Storage::disk('local')->path($relative), 'Consolidado-curso.pdf',
                ['Content-Type' => 'application/pdf', 'X-Content-Type-Options' => 'nosniff'])->deleteFileAfterSend(true);
        } catch (\Throwable $error) {
            Storage::disk('local')->delete($relative);
            report($error);

            return back()->with('error', 'No fue posible generar el consolidado del curso.');
        }
    }
}
