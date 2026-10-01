<?php

namespace App\Services;

use App\Models\Calificacion;
use App\Models\InscripcionEstudiante;
use App\Models\PeriodoEvaluacion;
use App\Models\PlanAsignatura;
use App\Models\User;
use App\Services\AulaVirtual\CursoVirtualService;
use App\Support\Evaluacion\CalificacionInteligente;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
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
            ->where('cod_pas', $course->cod_pas)->orderBy('cod_est')->paginate(30) : collect();

        return $context + ['grades' => $grades];
    }

    public function teacherContext(User $user, string $courseId): array
    {
        abort_unless(app(RoleDashboardResolver::class)->roleFor($user) === 'Docente', 403);
        abort_unless($user->canAny(['calificaciones.ver.curso', 'Calificaciones']), 403);
        $course = $this->courses->cursoParaDocente($user, $courseId);
        abort_unless($course, 403);
        $ready = $this->available();
        $students = InscripcionEstudiante::query()->with('estudiante.persona');
        foreach (['cod_gea', 'cod_cur', 'cod_par', 'cod_tur'] as $field) {
            $students->where($field, $course->planAsignatura->$field);
        }

        return [
            'course' => $course, 'ready' => $ready,
            'students' => $students->where('est_ins', 'ACTIVA')->get()->pluck('estudiante')->filter()->unique('cod_est'),
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
        $grade = Calificacion::where('cod_pas', $course->cod_pas)->whereKey($gradeId)->firstOrFail();
        abort_unless($grade->est_cal === 'ACTIVO', 403, 'Solo se permite revisar notas activas; la rectificación institucional requiere otro permiso.');

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
            'not_cal' => ['nullable', 'numeric'], 'obs_cal' => ['nullable', 'string', 'max:255'],
        ])->validate();
        $grade = $gradeId ? $this->teacherGrade($user, $courseId, $gradeId) : null;
        if ($grade) {
            abort_unless(($data['cod_est'] ?? null) === $grade->cod_est && ($data['cod_pev'] ?? null) === $grade->cod_pev, 422, 'La revisión conserva estudiante y periodo de la nota original.');
        }
        if (filled($data['cod_est'] ?? null)) {
            $enrollment = InscripcionEstudiante::where('cod_est', $data['cod_est'])->where('est_ins', 'ACTIVA');
            foreach (['cod_gea', 'cod_cur', 'cod_par', 'cod_tur'] as $field) {
                $enrollment->where($field, $course->planAsignatura->$field);
            }
            abort_unless($enrollment->exists(), 403, 'El estudiante no pertenece al contexto vigente del curso.');
        }
        if (filled($data['cod_pev'] ?? null)) {
            abort_unless(PeriodoEvaluacion::whereKey($data['cod_pev'])->where('est_pev', 'ACTIVO')->exists(), 422, 'Selecciona un periodo activo.');
        }

        return app(CalificacionInteligente::class)->analizar($data + ['cod_asi' => $course->planAsignatura->cod_asi, 'cod_pas' => $course->cod_pas, 'est_cal' => 'ACTIVO'], $grade?->cod_cal);
    }

    public function update(User $user, Calificacion $grade, float $value, ?string $observation, ?string $reason = null): void
    {
        abort_unless($grade->cod_pas, 409, 'Esta nota histórica no tiene asignación académica reconciliada. No puede modificarse automáticamente.');
        $this->save($user, $grade->cod_pas, $grade->cod_est, $grade->cod_pev, $value, $observation, $grade, $reason);
    }

    public function save(User $actor, string $planId, string $studentId, string $periodId, float $value, ?string $observation, ?Calificacion $grade = null, ?string $reason = null, ?string $state = null): Calificacion
    {
        abort_unless($actor->est_usu === 'ACTIVO', 403);
        $institutional = app(RoleDashboardResolver::class)->roleFor($actor);
        $admin = $institutional === 'Administrador' && $actor->can('calificaciones.gestionar.global');
        $teacher = $institutional === 'Docente' && $actor->can('calificaciones.gestionar.curso');
        abort_unless($admin || $teacher, 403, 'No tienes autorización para registrar calificaciones.');
        abort_unless($this->available(), 409, 'El historial por gestión está pendiente de aplicación autorizada.');
        validator(['nota' => $value, 'observacion' => $observation, 'motivo' => $reason], [
            'nota' => ['required', 'numeric', 'between:0,100'], 'observacion' => ['nullable', 'string', 'max:255'], 'motivo' => ['nullable', 'string', 'max:1000'],
        ])->validate();

        $state ??= $grade?->est_cal ?? 'ACTIVO';
        abort_unless(in_array($state, ['ACTIVO', 'INACTIVO', 'ANULADO'], true), 422);
        if ($state !== ($grade?->est_cal ?? 'ACTIVO')) {
            abort_unless($admin && $actor->can('calificaciones.rectificar') && mb_strlen(trim($reason ?? '')) >= 10, 403, 'El cambio de estado requiere autorización de rectificación y motivo.');
        }
        abort_unless($admin || ($grade?->est_cal ?? 'ACTIVO') === 'ACTIVO', 403, 'No puedes modificar una nota anulada o inactiva.');

        return DB::transaction(function () use ($actor, $admin, $planId, $studentId, $periodId, $value, $observation, $grade, $reason, $state) {
            $plan = PlanAsignatura::query()->with('gestionAcademica')->lockForUpdate()->findOrFail($planId);
            $period = PeriodoEvaluacion::query()->lockForUpdate()->findOrFail($periodId);
            if ($grade) {
                $grade = Calificacion::query()->lockForUpdate()->findOrFail($grade->cod_cal);
                abort_unless($grade->cod_pas === $planId && $grade->cod_est === $studentId && $grade->cod_pev === $periodId, 422);
                abort_unless($admin || $grade->est_cal === 'ACTIVO', 403);
                if ($state !== $grade->est_cal) {
                    abort_unless($admin && $actor->can('calificaciones.rectificar') && mb_strlen(trim($reason ?? '')) >= 10, 403);
                }
            }
            if (! $admin) {
                abort_unless($this->courses->docenteDeUsuario($actor)?->cod_doc === $plan->cod_doc, 403);
            }
            $enrollment = InscripcionEstudiante::where('cod_est', $studentId);
            foreach (['cod_gea', 'cod_cur', 'cod_par', 'cod_tur'] as $field) {
                $enrollment->where($field, $plan->$field);
            }
            $rectification = $grade && $admin && $actor->can('calificaciones.rectificar') && mb_strlen(trim($reason ?? '')) >= 10;
            if (! $rectification) {
                $enrollment->where('est_ins', 'ACTIVA');
            }
            abort_unless($enrollment->exists(), 422, 'No existe una inscripción válida en esta asignación y gestión; una inscripción histórica requiere rectificación autorizada.');
            $open = $plan->est_pas === 'ACTIVO' && in_array($plan->gestionAcademica?->est_gea, ['ACTIVO', 'ACTIVA'], true) && $period->est_pev === 'ACTIVO';
            if (! $open && (! $grade || ! $admin || ! $actor->can('calificaciones.rectificar') || mb_strlen(trim($reason ?? '')) < 10)) {
                throw ValidationException::withMessages(['motivo' => 'El periodo o gestión está cerrado. La rectificación requiere autorización administrativa y un motivo de al menos 10 caracteres.']);
            }
            if (! $grade && Calificacion::where('cod_pas', $planId)->where('cod_est', $studentId)->where('cod_pev', $periodId)->exists()) {
                throw ValidationException::withMessages(['cod_pev' => 'Ya existe una nota oficial para este estudiante, asignación y periodo.']);
            }
            // El análisis local complementa autorización, contexto y restricciones de persistencia.
            $preventive = app(CalificacionInteligente::class)->analizar([
                'cod_est' => $studentId, 'cod_asi' => $plan->cod_asi, 'cod_pas' => $planId,
                'cod_pev' => $periodId, 'not_cal' => $value, 'obs_cal' => $observation, 'est_cal' => $state,
            ], $grade?->cod_cal);
            if (! $preventive['puede_guardar']) {
                throw ValidationException::withMessages(['not_cal' => $preventive['bloqueos']]);
            }
            $before = $grade?->only(['not_cal', 'obs_cal', 'cod_pas', 'cod_pev', 'est_cal']);
            $grade ??= new Calificacion(['cod_cal' => 'CAL_'.Str::upper(Str::random(16))]);
            $grade->fill(['cod_est' => $studentId, 'cod_pas' => $planId, 'cod_asi' => $plan->cod_asi, 'cod_pev' => $periodId,
                'not_cal' => $value, 'obs_cal' => $observation, 'est_cal' => $state])->save();
            BitacoraService::registrar(accion: $rectification || ! $open ? 'RECTIFICAR_NOTA_OFICIAL' : 'GUARDAR_NOTA_OFICIAL', tabla: 'calificacion', registro: $grade->cod_cal,
                modulo: 'Calificaciones', descripcion: 'Nota oficial contextualizada por asignación y periodo.', valoresAnteriores: $before,
                valoresNuevos: ['not_cal' => $value, 'cod_pas' => $planId, 'cod_pev' => $periodId, 'motivo' => $reason, 'est_cal' => $state]);

            return $grade;
        });
    }
}
