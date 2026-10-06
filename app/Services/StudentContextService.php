<?php

namespace App\Services;

use App\Models\Oficial\Academico\AsistenciaEstudiante;
use App\Models\Oficial\AulaVirtual\EntregaTarea;
use App\Models\Oficial\Academico\Estudiante;
use App\Models\Oficial\Sistema\User;
use App\Services\AulaVirtual\CursoVirtualService;
use Illuminate\Support\Facades\Gate;

class StudentContextService
{
    public function __construct(private readonly CursoVirtualService $courses) {}

    public function details(User $user, string $studentId, ?string $classId = null): array
    {
        $actor = app(RoleDashboardResolver::class)->roleFor($user);
        abort_unless($actor, 403);
        $class = null;
        $query = Estudiante::with('persona')->whereKey($studentId);
        if ($actor === 'Docente') {
            abort_unless($classId && $user->can('estudiantes.ver.curso'), 403);
            $class = $this->courses->cursoParaDocente($user, $classId);
            abort_unless($class && $this->courses->estudiantesVigentes($class)->where('cod_est', $studentId)->exists(), 403);
        } elseif ($actor === 'Estudiante') {
            abort_unless($this->courses->estudianteDeUsuario($user)?->cod_est === $studentId, 403);
            if ($classId) {
                $class = $this->courses->cursoParaEstudiante($user, $classId);
                abort_unless($class, 403);
            }
        } elseif ($actor === 'Regente') {
            abort_unless($user->can('estudiantes.ver.institucional'), 403);
            $query->whereHas('inscripciones', fn ($q) => app(RegencyAccessService::class)->constrain($q, $user, 'inscripcion_estudiante')->where('est_ins', 'ACTIVA'));
        } else {
            abort_unless($user->canAny(['estudiantes.ver.institucional', 'estudiantes.ver.global', 'Estudiantes']), 403);
        }
        $student = $query->firstOrFail();
        Gate::forUser($user)->authorize('view', $student);
        $enrollments = $student->inscripciones()->with('curso', 'paralelo', 'gestionAcademica');
        if ($class) {
            $plan = $class->planAsignatura ?? $class->planEspecialidad;
            $enrollments->where('cod_gea', $plan->cod_gea)
                ->whereHas('inscripcionVigenciaRegistros', fn ($vigencia) => $vigencia
                    ->where('cod_gac', $plan->cod_gac)
                    ->where('cod_esp_tec', $class->cod_pes ? $plan->cod_esp : null));
        } elseif ($actor === 'Regente') {
            app(RegencyAccessService::class)->constrain($enrollments->getQuery(), $user, 'inscripcion_estudiante');
        }
        $deliveries = $class && $user->can('Entregas_Aula') ? EntregaTarea::with('tarea', 'calificacion')->where('cod_est', $studentId)
            ->whereHas('tarea', fn ($q) => $q->where('cod_cla', $class->cod_cla))->latest('fec_ent')->limit(10)->get() : collect();
        $attendance = $class && $user->can('Asistencia_Aula') ? AsistenciaEstudiante::with('asistenciaClase', 'estadoAsistencia')->where('cod_est', $studentId)
            ->where('est_asi_est', '!=', 'ANULADO')->whereHas('asistenciaClase', fn ($q) => $q->where('cod_cla', $class->cod_cla))->latest('fec_reg_asi_est')->limit(10)->get() : collect();

        return ['student' => $student, 'enrollments' => $enrollments->orderByDesc('fei_ins')->limit(10)->get(), 'deliveries' => $deliveries, 'attendance' => $attendance];
    }
}
