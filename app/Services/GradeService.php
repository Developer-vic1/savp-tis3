<?php

namespace App\Services;

use App\Models\Oficial\Academico\Calificacion;
use App\Models\Oficial\Academico\ConfiguracionCalendarioGestion;
use App\Models\Oficial\Academico\InscripcionEstudiante;
use App\Models\Oficial\Academico\PeriodoEvaluacion;
use App\Models\Oficial\Academico\PlanAsignatura;
use App\Models\Oficial\Academico\PlanEspecialidad;
use App\Models\Oficial\Sistema\User;
use App\Services\AulaVirtual\CursoVirtualService;
use App\Support\Evaluacion\CalificacionInteligente;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class GradeService
{
    public function __construct(private readonly CursoVirtualService $courses) {}

    public function available(): bool
    {
        return Schema::hasColumn('calificacion', 'cod_pas');
    }

    public function gradesForTeacherCourse(User $user, string $courseId): array
    {
        $context = $this->teacherContext($user, $courseId);
        ['course' => $course,'ready' => $ready] = $context;
        $grades = $ready ? Calificacion::query()->with('estudiante.persona', 'asignatura', 'periodoEvaluacion')
            ->where($course->cod_pas ? 'cod_pas' : 'cod_pes', $course->cod_pas ?? $course->cod_pes)->orderBy('cod_ins')->paginate(30) : collect();

        return $context + ['grades' => $grades];
    }

    public function teacherContext(User $user, string $courseId): array
    {
        abort_unless(app(RoleDashboardResolver::class)->roleFor($user) === 'Docente', 403);
        abort_unless($user->canAny(['calificaciones.ver.curso', 'Calificaciones']), 403);
        $course = $this->courses->cursoParaDocente($user, $courseId);
        abort_unless($course, 403);
        $ready = $this->available();
        $students = $this->courses->estudiantesVigentes($course)->with('estudiante.persona');

        return [
            'course' => $course, 'ready' => $ready,
            'students' => $students->get()->pluck('estudiante')->filter()->unique('cod_est'),
            'periods' => PeriodoEvaluacion::where('est_pev', 'ACTIVO')->orderBy('ord_pev')->get(),
        ];
    }

    public function teacherGrade(User $user, string $courseId, string $gradeId): Calificacion
    {
        abort_unless(app(RoleDashboardResolver::class)->roleFor($user) === 'Docente'
            && $user->can('calificaciones.gestionar.curso')
            && $user->canAny(['calificaciones.ver.curso', 'Calificaciones']), 403);
        abort_unless(strlen($gradeId) <= 20, 422);
        $course = $this->courses->cursoParaDocente($user, $courseId);
        abort_unless($course, 403);
        abort_unless($this->available(), 409, 'El historial por gestión está pendiente de aplicación autorizada.');
        $grade = Calificacion::where($course->cod_pas ? 'cod_pas' : 'cod_pes', $course->cod_pas ?? $course->cod_pes)->whereKey($gradeId)->firstOrFail();
        abort_unless(in_array($grade->est_cal, ['VIGENTE', 'RECTIFICADA'], true), 403, 'Solo se permite revisar notas activas; la rectificación institucional requiere otro permiso.');

        return $grade;
    }

    public function previewTeacherGrade(User $user, string $courseId, array $draft, ?string $gradeId = null): array
    {
        abort_unless(app(RoleDashboardResolver::class)->roleFor($user) === 'Docente'
            && $user->can('calificaciones.gestionar.curso')
            && $user->canAny(['calificaciones.ver.curso', 'Calificaciones']), 403);
        $course = $this->courses->cursoParaDocente($user, $courseId);
        abort_unless($course, 403);
        abort_unless($this->available(), 409, 'El historial por gestión está pendiente de aplicación autorizada.');
        $data = validator($draft, [
            'cod_est' => ['nullable', 'string', 'max:20'], 'cod_pev' => ['nullable', 'string', 'max:20'],
            'not_cal' => ['nullable', 'numeric'], 'obs_cal' => ['nullable', 'string', 'max:255'], 'fea_cal' => ['nullable', 'date_format:Y-m-d'],
        ])->validate();
        $grade = $gradeId ? $this->teacherGrade($user, $courseId, $gradeId) : null;
        if ($grade) {
            abort_unless(($data['cod_est'] ?? null) === $grade->cod_est && ($data['cod_pev'] ?? null) === $grade->cod_pev, 422, 'La revisión conserva estudiante y periodo de la nota original.');
        }
        if (filled($data['cod_est'] ?? null)) {
            $plan = $course->planAsignatura ?? $course->planEspecialidad;
            $enrollment = InscripcionEstudiante::where('cod_est', $data['cod_est'])->where('est_ins', 'ACTIVA')->where('cod_gea', $plan->cod_gea);
            abort_unless($enrollment->exists(), 403, 'El estudiante no pertenece al contexto vigente del curso.');
        }
        if (filled($data['cod_pev'] ?? null)) {
            abort_unless(PeriodoEvaluacion::whereKey($data['cod_pev'])->where('est_pev', 'ACTIVO')->exists(), 422, 'Selecciona un periodo activo.');
        }

        return app(CalificacionInteligente::class)->analizar($data + ['cod_asi' => $course->planAsignatura?->cod_asi, 'cod_pas' => $course->cod_pas, 'cod_pes' => $course->cod_pes, 'est_cal' => 'VIGENTE'], $grade?->cod_cal);
    }

    public function update(User $user, Calificacion $grade, float $value, ?string $observation, ?string $reason = null): void
    {
        $this->save($user, $grade->cod_pas ?? $grade->cod_pes, $grade->cod_est, $grade->cod_pev, $value, $observation, $grade, $reason, fechaAcademica: $grade->fea_cal?->format('Y-m-d'));
    }

    public function save(User $actor, string $planId, string $studentId, string $periodId, float $value, ?string $observation, ?Calificacion $grade = null, ?string $reason = null, ?string $state = null, ?string $fechaAcademica = null): Calificacion
    {
        abort_unless($actor->est_usu === 'ACTIVO', 403);
        $institutional = app(RoleDashboardResolver::class)->roleFor($actor);
        $admin = $institutional === 'Administrador' && $actor->can('calificaciones.gestionar.global');
        $teacher = $institutional === 'Docente' && $actor->can('calificaciones.gestionar.curso');
        abort_unless($admin || $teacher, 403, 'No tienes autorización para registrar calificaciones.');
        abort_unless($this->available(), 409, 'El historial por gestión está pendiente de aplicación autorizada.');
        $fechaAcademica ??= $grade?->fea_cal?->format('Y-m-d');
        validator(['nota' => $value, 'observacion' => $observation, 'motivo' => $reason, 'fea_cal' => $fechaAcademica], [
            'nota' => ['required', 'numeric', 'between:0,100'], 'observacion' => ['nullable', 'string', 'max:255'], 'motivo' => ['nullable', 'string', 'max:1000'],
            'fea_cal' => ['required', 'date_format:Y-m-d'],
        ])->validate();

        $state ??= $grade?->est_cal ?? 'VIGENTE';
        abort_unless(in_array($state, ['VIGENTE', 'RECTIFICADA', 'ANULADA'], true), 422);
        if ($state !== ($grade?->est_cal ?? 'VIGENTE')) {
            abort_unless($admin && $actor->can('calificaciones.rectificar') && mb_strlen(trim($reason ?? '')) >= 10, 403, 'El cambio de estado requiere autorización de rectificación y motivo.');
        }
        abort_unless($admin || in_array($grade?->est_cal ?? 'VIGENTE', ['VIGENTE', 'RECTIFICADA'], true), 403, 'No puedes modificar una nota anulada o inactiva.');

        return DB::transaction(function () use ($actor, $admin, $planId, $studentId, $periodId, $value, $observation, $grade, $reason, $state, $fechaAcademica) {
            $plan = PlanAsignatura::query()->with('gestionAcademica', 'grupoAcademico')->lockForUpdate()->find($planId)
                ?? PlanEspecialidad::query()->with('gestionAcademica', 'grupoAcademico')->lockForUpdate()->findOrFail($planId);
            $tecnico = $plan->getTable() === 'plan_especialidad';
            $campoPlan = $tecnico ? 'cod_pes' : 'cod_pas';
            $period = PeriodoEvaluacion::query()->lockForUpdate()->findOrFail($periodId);
            if ($grade) {
                $grade = Calificacion::query()->lockForUpdate()->findOrFail($grade->cod_cal);
                abort_unless($planId === $grade->$campoPlan && $grade->cod_est === $studentId && $grade->cod_pev === $periodId, 422);
                abort_unless($admin || in_array($grade->est_cal, ['VIGENTE', 'RECTIFICADA'], true), 403);
                if ($state !== $grade->est_cal) {
                    abort_unless($admin && $actor->can('calificaciones.rectificar') && mb_strlen(trim($reason ?? '')) >= 10, 403);
                }
            }
            if (! $admin) {
                abort_unless($this->courses->docenteDeUsuario($actor)?->cod_doc === $plan->cod_doc, 403);
            }
            $enrollment = InscripcionEstudiante::where('cod_est', $studentId);
            $enrollment->where('cod_gea', $plan->cod_gea)
                ->whereHas('inscripcionVigenciaRegistros', fn ($vigencia) => $vigencia
                    ->where('cod_gac', $plan->cod_gac)->where('cod_esp_tec', $tecnico ? $plan->cod_esp : null)
                    ->where('fii_ivg', '<=', $fechaAcademica)
                    ->where(fn ($fin) => $fin->whereNull('ffi_ivg')->orWhere('ffi_ivg', '>=', $fechaAcademica)));
            $rectification = $grade && $admin && $actor->can('calificaciones.rectificar') && mb_strlen(trim($reason ?? '')) >= 10;
            if (! $rectification) {
                $enrollment->where('est_ins', 'ACTIVA');
            }
            $inscripcion = $enrollment->lockForUpdate()->first();
            abort_unless($inscripcion, 422, 'No existe un trayecto compatible con el plan y la fecha académica.');
            $sufijo = $tecnico ? 'pes' : 'pas';
            abort_unless($fechaAcademica >= $plan->{'fii_'.$sufijo}->format('Y-m-d') && (! $plan->{'ffi_'.$sufijo} || $fechaAcademica <= $plan->{'ffi_'.$sufijo}->format('Y-m-d')), 422, 'La fecha académica está fuera de la vigencia del plan.');
            $calendario = ConfiguracionCalendarioGestion::where('cod_gea', $plan->cod_gea)->where('cod_pev', $periodId)
                ->where('fii_tri_ccg', '<=', $fechaAcademica)->where('ffi_tri_ccg', '>=', $fechaAcademica)->first();
            abort_unless($calendario, 422, 'La fecha académica no corresponde al periodo de esta gestión.');
            $open = $plan->{'est_'.$sufijo} === 'ACTIVO' && in_array($plan->gestionAcademica?->est_gea, ['ACTIVO', 'ACTIVA'], true) && $period->est_pev === 'ACTIVO' && $calendario->cie_ccg === null;
            if (! $open && (! $grade || ! $admin || ! $actor->can('calificaciones.rectificar') || mb_strlen(trim($reason ?? '')) < 10)) {
                throw ValidationException::withMessages(['motivo' => 'El periodo o gestión está cerrado. La rectificación requiere autorización administrativa y un motivo de al menos 10 caracteres.']);
            }
            if (! $grade && Calificacion::where($campoPlan, $planId)->where('cod_ins', $inscripcion->cod_ins)->where('cod_pev', $periodId)->where('est_cal', '!=', 'ANULADA')->exists()) {
                throw ValidationException::withMessages(['cod_pev' => 'Ya existe una nota oficial para este estudiante, asignación y periodo.']);
            }
            // El análisis local complementa autorización, contexto y restricciones de persistencia.
            $preventive = app(CalificacionInteligente::class)->analizar([
                'cod_est' => $studentId, 'cod_ins' => $inscripcion->cod_ins, 'cod_asi' => $tecnico ? null : $plan->cod_asi, $campoPlan => $planId,
                'cod_pev' => $periodId, 'not_cal' => $value, 'obs_cal' => $observation, 'est_cal' => $state, 'fea_cal' => $fechaAcademica,
            ], $grade?->cod_cal);
            if (! $preventive['puede_guardar']) {
                throw ValidationException::withMessages(['not_cal' => $preventive['bloqueos']]);
            }
            $before = $grade?->only(['not_cal', 'obs_cal', 'cod_pas', 'cod_pev', 'est_cal']);
            $grade ??= new Calificacion;
            $grade->fill(['cod_ins' => $inscripcion->cod_ins, 'cod_pas' => $tecnico ? null : $planId, 'cod_pes' => $tecnico ? $planId : null, 'cod_pev' => $periodId, 'fea_cal' => $fechaAcademica,
                'not_cal' => $value, 'obs_cal' => $observation, 'est_cal' => $state])->save();
            BitacoraService::registrar(accion: $rectification || ! $open ? 'RECTIFICAR_NOTA_OFICIAL' : 'GUARDAR_NOTA_OFICIAL', tabla: 'calificacion', registro: $grade->cod_cal,
                modulo: 'Calificaciones', descripcion: 'Nota oficial contextualizada por asignación y periodo.', valoresAnteriores: $before,
                valoresNuevos: ['not_cal' => $value, 'cod_pas' => $planId, 'cod_pev' => $periodId, 'motivo' => $reason, 'est_cal' => $state]);

            return $grade;
        });
    }
}
