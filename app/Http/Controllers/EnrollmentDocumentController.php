<?php

namespace App\Http\Controllers;

use App\Models\Oficial\Academico\DocumentoInscripcionEstudiante;
use App\Services\BitacoraService;
use App\Services\RoleDashboardResolver;
use App\Support\PrivateFilePath;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class EnrollmentDocumentController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(in_array(app(RoleDashboardResolver::class)->roleFor($request->user()), ['Administrador', 'Secretaria'], true) && $request->user()->can('Inscripciones'), 403);
        $filters = $request->validate(['search' => 'nullable|string|max:100', 'estado' => 'nullable|in:PENDIENTE,PRESENTADO,VALIDADO,OBSERVADO,VENCIDO,NO_APLICA,ANULADO']);
        $rows = DocumentoInscripcionEstudiante::with('inscripcion.estudiante.persona', 'inscripcion.curso')
            ->when($filters['estado'] ?? null, fn ($q, $state) => $q->where('est_die', $state))
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where(fn ($names) => $names->where('nom_die', 'like', '%'.$search.'%')
                ->orWhereHas('inscripcion.estudiante.persona', fn ($p) => $p->where('nom_per', 'like', '%'.$search.'%')->orWhere('ape_pat_per', 'like', '%'.$search.'%'))))
            ->orderByDesc('created_at')->paginate(20)->withQueryString();

        return view('workspaces.documentacion', compact('rows', 'filters'));
    }

    public function download(DocumentoInscripcionEstudiante $document)
    {
        Gate::authorize('view', $document);
        abort_unless(PrivateFilePath::valid($document->rut_die, 'inscripciones-privadas') && Storage::disk('local')->exists($document->rut_die), 404);
        BitacoraService::registrar(accion: 'DESCARGAR_DOCUMENTO_INSCRIPCION', tabla: 'documento_inscripcion_estudiante', registro: $document->cod_die, modulo: 'Inscripciones');

        return Storage::disk('local')->download($document->rut_die, basename($document->rut_die), ['X-Content-Type-Options' => 'nosniff', 'Content-Type' => 'application/pdf']);
    }
}
