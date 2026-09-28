<?php

namespace App\Services;

use App\Models\Calificacion;
use App\Models\InscripcionEstudiante;
use App\Models\PeriodoEvaluacion;
use App\Models\PlanAsignatura;
use App\Models\User;
use App\Services\AulaVirtual\CursoVirtualService;
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
        abort_unless($user->canAny(['calificaciones.ver.curso', 'Calificaciones']), 403);
        $course = $this->courses->cursoParaDocente($user, $courseId);
        abort_unless($course, 403);
        $ready = $this->available();
        $grades = $ready ? Calificacion::query()->with('estudiante.persona', 'asignatura', 'periodoEvaluacion')
            ->where('cod_pas', $course->cod_pas)->orderBy('cod_est')->paginate(30) : collect();
        $students = InscripcionEstudiante::query()->with('estudiante.persona');
        foreach (['cod_gea', 'cod_cur', 'cod_par', 'cod_tur'] as $field) {
            $students->where($field, $course->planAsignatura->$field);
        }

        return [
            'course' => $course, 'grades' => $grades, 'ready' => $ready,
            'students' => $students->where('est_ins', 'ACTIVA')->get()->pluck('estudiante')->filter()->unique('cod_est'),
            'periods' => PeriodoEvaluacion::where('est_pev', 'ACTIVO')->orderBy('ord_pev')->get(),
        ];
    }

    public function update(User $user, Calificacion $grade, float $value, ?string $observation, ?string $reason = null): void
    {
        abort_unless($grade->cod_pas, 409, 'Esta nota histórica no tiene asignación académica reconciliada. No puede modificarse automáticamente.');
        $this->save($user, $grade->cod_pas, $grade->cod_est, $grade->cod_pev, $value, $observation, $grade, $reason);
    }

    public function save(User $actor, string $planId, string $studentId, string $periodId, float $value, ?string $observation, ?Calificacion $grade = null, ?string $reason = null, ?string $state = null): Calificacion
    {
        abort_unless($actor->est_usu === 'ACTIVO', 403);
        $admin = $actor->hasRole('Administrador') && $actor->can('calificaciones.gestionar.global');
        $teacher = $actor->hasRole('Docente') && $actor->can('calificaciones.gestionar.curso');
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
            if (! $rectification) { $enrollment->where('est_ins', 'ACTIVA'); }
            abort_unless($enrollment->exists(), 422, 'No existe una inscripción válida en esta asignación y gestión; una inscripción histórica requiere rectificación autorizada.');
            $open = $plan->est_pas === 'ACTIVO' && in_array($plan->gestionAcademica?->est_gea, ['ACTIVO', 'ACTIVA'], true) && $period->est_pev === 'ACTIVO';
            if (! $open && (! $grade || ! $admin || ! $actor->can('calificaciones.rectificar') || mb_strlen(trim($reason ?? '')) < 10)) {
                throw ValidationException::withMessages(['motivo' => 'El periodo o gestión está cerrado. La rectificación requiere autorización administrativa y un motivo de al menos 10 caracteres.']);
            }
            if (! $grade && Calificacion::where('cod_pas', $planId)->where('cod_est', $studentId)->where('cod_pev', $periodId)->exists()) {
                throw ValidationException::withMessages(['cod_pev' => 'Ya existe una nota oficial para este estudiante, asignación y periodo.']);
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
