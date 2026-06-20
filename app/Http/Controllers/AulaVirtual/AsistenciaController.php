<?php

namespace App\Http\Controllers\AulaVirtual;

use App\Http\Controllers\Controller;
use App\Models\AulaVirtual\EstadoAsistencia;
use App\Services\AulaVirtual\AsistenciaService;
use App\Services\AulaVirtual\CursoVirtualService;
use Illuminate\Http\Request;

class AsistenciaController extends Controller
{
    public function __construct(
        private readonly CursoVirtualService $cursos,
        private readonly AsistenciaService $asistencias,
    ) {}

    public function registrar(Request $request, string $curso)
    {
        $clase = $this->cursos->cursoParaDocente($request->user(), $curso);
        abort_if(! $clase, 403);

        return view('aula-virtual.asistencia.registrar', [
            'curso' => $clase,
            'estados' => EstadoAsistencia::where('est_est_asi', 'ACTIVO')->orderBy('nom_est_asi')->get(),
        ]);
    }

    public function guardar(Request $request, string $curso)
    {
        $clase = $this->cursos->cursoParaDocente($request->user(), $curso);
        $docente = $this->cursos->docenteDeUsuario($request->user());
        abort_if(! $clase || ! $docente, 403);

        $datos = $request->validate([
            'fec_asi_cla' => ['required', 'date'],
            'tit_asi_cla' => ['nullable', 'string', 'max:150'],
            'obs_asi_cla' => ['nullable', 'string'],
            'asistencias' => ['array'],
            'asistencias.*.cod_est_asi' => ['required', 'string'],
            'asistencias.*.min_retraso' => ['nullable', 'integer', 'min:0', 'max:300'],
            'asistencias.*.obs_asi_est' => ['nullable', 'string', 'max:1000'],
        ]);

        $datos['cod_cla'] = $clase->cod_cla;
        $this->asistencias->guardar($datos, $docente, $request->user());

        return back()->with('status', 'Asistencia guardada.');
    }

    public function miAsistencia(Request $request)
    {
        $estudiante = $this->cursos->estudianteDeUsuario($request->user());

        return view('aula-virtual.asistencia.mi-asistencia', [
            'estudiante' => $estudiante,
            'resumen' => $this->cursos->resumenAsistenciaEstudiante($estudiante),
            'registros' => $estudiante
                ? $estudiante->hasMany(\App\Models\AulaVirtual\AsistenciaEstudiante::class, 'cod_est', 'cod_est')->with('asistenciaClase.claseVirtual.planAsignatura.asignatura', 'estadoAsistencia')->latest('fec_reg_asi_est')->get()
                : collect(),
        ]);
    }
}
