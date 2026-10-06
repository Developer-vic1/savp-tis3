<?php

namespace App\Services\AporteIngenieril;

use App\Models\Oficial\AporteAcademicoVocacional\OrientacionActividad;
use App\Models\Oficial\Academico\AsistenciaEstudiante;
use App\Models\Oficial\AulaVirtual\EntregaTarea;
use App\Models\Oficial\AulaVirtual\Tarea;
use App\Models\Oficial\Academico\Calificacion;
use App\Models\Oficial\Academico\Estudiante;
use App\Models\Oficial\Academico\InscripcionEstudiante;
use App\Models\Oficial\AulaVirtual\ClaseVirtual;
use App\Models\Oficial\Sistema\User;
use App\Services\AporteIngenieril\DTO\AporteResponse;
use App\Services\AulaVirtual\CursoVirtualService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class StudentOrientationService
{
    public function __construct(private readonly CursoVirtualService $courses, private readonly AporteIngenierilClient $client, private readonly OrientationReadiness $readiness) {}

    public function student(User $user): Estudiante
    {
        abort_unless($user->can('Orientacion_Academica_Profesional'), 403);
        $student = $this->courses->estudianteDeUsuario($user);
        abort_unless($student, 403, 'Tu cuenta todavía no tiene un perfil estudiantil activo vinculado.');
        return $student;
    }

    public function context(User $user): array
    {
        $student = $this->student($user);
        $enrollment = $student->inscripciones()->with('especialidadTecnica')->where('est_ins', 'ACTIVA')->latest('fei_ins')->first();

        return $this->buildContext($student, $enrollment, $this->courses->studentQuery($user));
    }

    /** Vista previa institucional: reutiliza la preparación del aporte sin suplantar al alumno. */
    public function institutionalContext(User $actor, string $inscripcion): array
    {
        app(\App\Services\InstitutionalQueryService::class)->authorizeQuery($actor, 'lms', 'admin');
        abort_unless($actor->can('orientacion.ver.institucional') && $actor->can('calificaciones.ver.global') && $actor->can('estudiantes.ver.global'), 403);
        $enrollment = InscripcionEstudiante::with(['estudiante', 'especialidadTecnica', 'gestionAcademica'])->where('est_ins', '!=', 'ANULADA')->findOrFail($inscripcion);
        $clases = ClaseVirtual::query()->whereHas('estudiantes', fn ($q) => $q->where('cod_est', $enrollment->cod_est)->where('est_cla_est', '!=', 'ANULADO'));

        return $this->buildContext($enrollment->estudiante, $enrollment, $clases, true);
    }

    private function buildContext(Estudiante $student, ?InscripcionEstudiante $enrollment, \Illuminate\Database\Eloquent\Builder $clases, bool $institucional = false): array
    {
        $storageReady = Schema::hasColumn('orientacion_actividades', 'riasec_public');
        $activity = $storageReady ? OrientacionActividad::where('cod_est', $student->cod_est)
            ->when($institucional, fn ($q) => $q->where('cod_gea', $enrollment?->cod_gea))
            ->when(! $institucional, fn ($q) => $q->whereNotNull('riasec_public'))->latest('id')->first() : null;
        $grades = Calificacion::with('asignatura', 'periodoEvaluacion', 'planAsignatura')
            ->deEstudiante($student->cod_est)->whereIn('est_cal', ['VIGENTE', 'RECTIFICADA'])->whereNotNull('cod_pas')
            ->whereHas('planAsignatura')->orderBy('cod_cal')->get();
        $record = fn ($grade) => ['subject' => $grade->asignatura->nom_asi, 'score' => (float) $grade->not_cal, 'scale_min' => 0, 'scale_max' => 100,
            'period' => $grade->periodoEvaluacion?->nom_pev, 'period_order' => $grade->periodoEvaluacion?->ord_pev];
        $grades = $grades->filter(fn ($grade) => $grade->asignatura && $grade->not_cal !== null && $grade->not_cal >= 0 && $grade->not_cal <= 100);
        $current = $grades->filter(fn ($grade) => $institucional ? $grade->cod_ins === $enrollment?->cod_ins : $grade->planAsignatura->cod_gea === $enrollment?->cod_gea);
        $payload = ['schema_version' => '2.0', 'student_id' => $student->cod_est, 'history' => [], 'declared_interests' => []];
        if ($current->isNotEmpty()) {
            $payload['academic'] = ['records' => $current->map($record)->values()->all()];
        }
        if ($enrollment) {
            $payload['academic_period'] = $enrollment->cod_gea;
        }
        $historial = $grades->reject(fn ($grade) => $grade->planAsignatura->cod_gea === $enrollment?->cod_gea);
        if ($institucional) {
            $year = $enrollment?->gestionAcademica?->ani_gea;
            $grades->load('inscripcionEstudiante.gestionAcademica');
            $historial = $historial->filter(fn ($grade) => $grade->inscripcionEstudiante?->gestionAcademica?->ani_gea < $year);
        }
        foreach ($historial->groupBy('planAsignatura.cod_gea') as $period => $rows) {
            $payload['history'][] = ['period' => (string) $period, 'records' => $rows->map($record)->values()->all()];
        }
        if ($activity?->riasec_public && $activity->riasec_score && (! $institucional || ($activity->finalizado_at
            && \App\Services\AporteIngenieril\DTO\ContratoAporteIngenierilV2::riasecValido($activity->riasec_public)))) {
            $payload['riasec_public'] = $activity->riasec_public;
        }
        $technicalStatus = $enrollment?->est_esp_tec_ins ?? 'NO_INFORMADO';
        $specialty = $enrollment?->especialidadTecnica?->nom_esp;
        if ($technicalStatus !== 'NO_APLICA' && $specialty) {
            $payload['technical'] = ['specialty' => $specialty];
        }
        if ($enrollment) {
            $attendance = AsistenciaEstudiante::with('estadoAsistencia')->where('cod_est', $student->cod_est)
                ->whereIn('est_asi_est', ['REGISTRADO', 'RECTIFICADO'])
                ->whereHas('asistenciaClase.claseVirtual.planAsignatura', fn ($query) => $query->deGestion($enrollment->cod_gea))->get();
            // Solo conteos binarios respaldados por catálogo; no convertir porcentajes parciales en clases.
            $known = $attendance->filter(fn ($row) => $row->estadoAsistencia?->afecta_asistencia && in_array((float) $row->estadoAsistencia->valor_porcentual, [0.0, 100.0], true));
            if ($known->isNotEmpty() && $known->count() === $attendance->count()) {
                $payload['attendance'] = ['total_classes' => $known->count(), 'attended_classes' => $known->filter(fn ($row) => (float) $row->estadoAsistencia->valor_porcentual === 100.0)->count()];
            }
            $courses = $clases->where(fn ($planes) => $planes
                ->whereHas('planAsignatura', fn ($query) => $query->deGestion($enrollment->cod_gea))
                ->orWhereHas('planEspecialidad', fn ($query) => $query->deGestion($enrollment->cod_gea)))->pluck('cod_cla');
            $tasks = Tarea::whereIn('cod_cla', $courses)->whereIn('est_tar', ['PUBLICADA', 'CERRADA'])->pluck('cod_tar');
            if ($tasks->isNotEmpty()) {
                $delivered = EntregaTarea::where('cod_est', $student->cod_est)->whereIn('cod_tar', $tasks)->whereIn('est_ent', ['ENTREGADO', 'ENTREGADO_TARDE', 'CALIFICADO'])->distinct()->count('cod_tar');
                $payload['learning_activity'] = ['assigned' => $tasks->count(), 'delivered' => $delivered];
            }
        }
        $precheck = $this->readiness->evaluate(true, $payload, $technicalStatus);
        if (! $storageReady) {
            $precheck['ready'] = false;
        }
        return compact('student', 'activity', 'payload', 'precheck', 'storageReady');
    }

    public function score(User $user, array $public): AporteResponse
    {
        $context = $this->context($user);
        if (! $context['storageReady']) {
            return new AporteResponse(false, [], 'La persistencia de orientación está pendiente de habilitación.');
        }
        $response = $this->client->riasecScore($public);
        if (! $response->available) {
            return $response;
        }
        $hash = hash('sha256', json_encode($public, JSON_THROW_ON_ERROR));
        DB::transaction(function () use ($context, $public, $response, $hash) {
            // Serializa envíos del mismo estudiante; no sobrescribe instrumentos locales ni snapshots anteriores.
            Estudiante::whereKey($context['student']->cod_est)->lockForUpdate()->firstOrFail();
            $existing = OrientacionActividad::where('cod_est', $context['student']->cod_est)->whereNotNull('riasec_public')->latest('id')->first();
            if ($existing?->riasec_input_hash !== $hash) {
                OrientacionActividad::create(['cod_est' => $context['student']->cod_est, 'cod_gea' => $context['payload']['academic_period'] ?? null,
                    'estado' => 'finalizado', 'avance' => 100, 'iniciado_at' => now(), 'finalizado_at' => now(),
                    'riasec_public' => $public, 'riasec_score' => $response->data, 'riasec_input_hash' => $hash]);
            }
        });
        return $response;
    }

    public function analyze(User $user): AporteResponse
    {
        $context = $this->context($user);
        if (! $context['precheck']['ready']) {
            return new AporteResponse(false, [], 'Tu perfil todavía no está listo para generar un análisis completo.', 422);
        }
        $response = $this->client->analysisV2($context['payload']);
        if ($response->available) {
            $context['activity']->forceFill(['analysis_snapshot' => $response->data, 'analysis_completed_at' => now()])->save();
        }
        return $response;
    }
}
