<?php

namespace App\Http\Controllers;

use App\Services\BitacoraService;
use App\Services\RegencyReportService;
use App\Services\Reportes\GeneradorMpdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RegencyReportController extends Controller
{
    public function index(Request $request, RegencyReportService $reports)
    {
        $filters = $request->validate(['search' => 'nullable|string|max:100']);
        $rows = $reports->query($request->user())->when($filters['search'] ?? null, fn ($q, $text) => $q->where(fn ($where) => $where->where('cod_pas', 'like', '%'.$text.'%')
            ->orWhereHas('asignatura', fn ($a) => $a->where('nom_asi', 'like', '%'.$text.'%'))))
            ->orderBy('cod_gea')->orderBy('cod_cur')->orderBy('cod_pas')->paginate(15)->withQueryString();

        return view('workspaces.reportes-regencia', compact('rows', 'filters'));
    }

    public function pdf(Request $request, string $plan, RegencyReportService $reports, GeneradorMpdfService $pdf)
    {
        $row = $reports->find($request->user(), $plan);
        $filename = 'regencia-'.Str::uuid().'.pdf';
        $relative = 'reportes/regencia/'.$filename;
        try {
            Storage::disk('local')->makeDirectory('reportes/regencia');
            Storage::disk('local')->makeDirectory('reportes/temp');
            $relative = $pdf->generar('pdf.reportes.regencia', ['row' => $row, 'fecha' => now()], $filename, 'regencia');
            BitacoraService::registrar(accion: 'EXPORTAR_REPORTE_REGENCIA', tabla: 'plan_asignatura', registro: $plan, modulo: 'Regencia');

            return response()->download(Storage::disk('local')->path($relative), 'Reporte-regencia.pdf',
                ['Content-Type' => 'application/pdf', 'X-Content-Type-Options' => 'nosniff'])->deleteFileAfterSend(true);
        } catch (\Throwable $error) {
            Storage::disk('local')->delete($relative);
            report($error);

            return back()->with('error', 'No fue posible generar el reporte del grado asignado.');
        }
    }
}
