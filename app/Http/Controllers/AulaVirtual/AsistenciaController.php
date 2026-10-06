<?php

namespace App\Http\Controllers\AulaVirtual;

use App\Http\Controllers\Controller;
use App\Models\Oficial\Academico\AsistenciaEstudiante;
use App\Models\Oficial\Academico\EstadoAsistencia;
use App\Models\Oficial\Academico\HorarioDetalle;
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
        $fecha = $request->validate(['fecha' => ['nullable', 'date_format:Y-m-d']])['fecha'] ?? now()->toDateString();
        $clase->setRelation('estudiantes', $this->cursos->estudiantesVigentes($clase, $fecha)->with('estudiante.persona')->get());
        $bloques = HorarioDetalle::with('horarioBloque')->where($clase->cod_pas ? 'cod_pas' : 'cod_pes', $clase->cod_pas ?? $clase->cod_pes)
            ->where('est_hde', 'ACTIVO')->get()->pluck('horarioBloque')->filter()->unique('cod_hbl')->sortBy('num_hbl')->values();

        return view('aula-virtual.asistencia.registrar', [
            'curso' => $clase,
            'fechaAsistencia' => $fecha,
            'bloques' => $bloques,
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
            'cod_hbl' => ['nullable', 'exists:horario_bloque,cod_hbl'],
            'tit_asi_cla' => ['nullable', 'string', 'max:150'],
            'obs_asi_cla' => ['nullable', 'string'],
            'asistencias' => ['array'],
            'asistencias.*.cod_est_asi' => ['required', 'string'],
            'asistencias.*.min_retraso' => ['nullable', 'integer', 'min:0', 'max:300'],
            'asistencias.*.obs_asi_est' => ['nullable', 'string', 'max:1000'],
            'asistencias.*.cod_nes' => ['nullable', 'exists:novedad_estudiante,cod_nes'],
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
                ? $estudiante->hasMany(AsistenciaEstudiante::class, 'cod_est', 'cod_est')->with('asistenciaClase.claseVirtual.planAsignatura.asignatura', 'estadoAsistencia')->latest('fec_reg_asi_est')->get()
                : collect(),
        ]);
    }
}
