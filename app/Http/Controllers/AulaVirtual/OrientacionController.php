<?php

namespace App\Http\Controllers\AulaVirtual;

use App\Http\Controllers\Controller;
use App\Models\Oficial\AporteAcademicoVocacional\OrientacionActividad;
use App\Services\AulaVirtual\CursoVirtualService;
use App\Services\AulaVirtual\OrientacionService;
use Illuminate\Http\Request;

class OrientacionController extends Controller
{
    public function __construct(
        private readonly OrientacionService $orientacion,
        private readonly CursoVirtualService $cursos,
    ) {}

    public function estudiante(Request $request)
    {
        return view('aula-virtual.orientacion.estudiante', [
            'resumen' => $this->orientacion->resumen($request->user()),
            'dimensiones' => $this->orientacion->dimensiones(),
        ]);
    }

    public function explorador(Request $request)
    {
        return view('aula-virtual.orientacion.explorador', [
            'dimensiones' => $this->orientacion->dimensiones(),
        ]);
    }

    public function resultados(Request $request)
    {
        return view('aula-virtual.orientacion.resultados', [
            'resumen' => $this->orientacion->resumen($request->user()),
        ]);
    }

    public function seguimientoDocente(Request $request)
    {
        $filters = $request->validate(['search' => ['nullable', 'string', 'max:100'],
            'estado' => ['nullable', 'in:pendiente,en_proceso,finalizado,revisado,requiere_seguimiento']]);
        $scope = $this->cursos->vinculosVigentes($request->user())->selectRaw('1')
            ->whereColumn('clase_estudiante.cod_est', 'orientacion_actividades.cod_est')
            ->whereHas('claseVirtual', fn ($clase) => $clase->where(fn ($planes) => $planes
                ->whereHas('planAsignatura.grupoAcademico', fn ($q) => $q->whereColumn('grupo_academico.cod_gea', 'orientacion_actividades.cod_gea'))
                ->orWhereHas('planEspecialidad.grupoAcademico', fn ($q) => $q->whereColumn('grupo_academico.cod_gea', 'orientacion_actividades.cod_gea'))));
        $rows = OrientacionActividad::with('estudiante.persona', 'gestionAcademica', 'resultado')
            ->whereExists($scope->toBase())
            ->when(filled($filters['estado'] ?? null), fn ($q) => $q->where('estado', $filters['estado']))
            ->when(filled($filters['search'] ?? null), fn ($q) => $q->whereHas('estudiante.persona', fn ($p) => $p->where(fn ($names) => $names->where('nom_per', 'like', '%'.$filters['search'].'%')->orWhere('ape_pat_per', 'like', '%'.$filters['search'].'%'))))
            ->latest()->paginate(20)->withQueryString();

        return view('aula-virtual.orientacion.seguimiento-docente', [
            'rows' => $rows,
            'filters' => $filters,
            'dimensiones' => $this->orientacion->dimensiones(),
        ]);
    }
}
