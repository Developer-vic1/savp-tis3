<?php

namespace App\Http\Controllers;

use App\Models\ReporteGenerado;
use App\Services\BitacoraService;
use App\Services\HistoricalReportAccessService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class HistoricalReportController extends Controller
{
    public function secretary(Request $request, HistoricalReportAccessService $access)
    {
        $filters = $request->validate(['search' => ['nullable', 'string', 'max:100']]);
        $rows = $access->query($request->user())->when(filled($filters['search'] ?? null), fn ($q) => $q->where('codigo', 'like', '%'.$filters['search'].'%'))->latest()->paginate(20)->withQueryString();

        return view('workspaces.reportes', compact('rows', 'filters'));
    }

    public function download(Request $request, ReporteGenerado $report)
    {
        Gate::authorize('view', $report);
        $disk = Storage::disk('local');
        abort_unless($disk->exists($report->ruta_archivo), 404, 'El archivo histórico no está disponible.');
        abort_unless($report->hash_archivo && hash_equals($report->hash_archivo, hash_file('sha256', $disk->path($report->ruta_archivo))), 409, 'El archivo requiere una revisión de integridad.');
        BitacoraService::registrar(accion: 'DESCARGAR_REPORTE', tabla: 'reportes_generados', registro: (string) $report->getKey(), modulo: 'Reportes');

        return $disk->download($report->ruta_archivo, 'Reporte-'.$report->getKey().'.pdf', ['Content-Type' => 'application/pdf', 'X-Content-Type-Options' => 'nosniff']);
    }
}
