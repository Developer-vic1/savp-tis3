<?php

namespace App\Services\AporteIngenieril;

use App\Models\Oficial\Academico\AsistenciaEstudiante;
use App\Models\Oficial\Academico\Calificacion;
use App\Models\Oficial\Academico\Estudiante;
use App\Models\Oficial\Academico\GestionAcademica;
use App\Models\Oficial\Academico\InscripcionEstudiante;
use App\Models\Oficial\AporteAcademicoVocacional\OrientacionActividad;
use App\Models\Oficial\AulaVirtual\ClaseVirtual;
use App\Models\Oficial\AulaVirtual\EntregaTarea;
use App\Models\Oficial\AulaVirtual\Tarea;
use App\Models\Oficial\Sistema\User;
use App\Services\AporteIngenieril\DTO\AporteResponse;
use App\Services\AulaVirtual\CursoVirtualService;
use App\Services\InstitutionalQueryService;
use App\Support\Orientacion\ExperienciaIntereses;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

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
        app(InstitutionalQueryService::class)->authorizeQuery($actor, 'lms', 'admin');
        abort_unless($actor->can('orientacion.ver.institucional') && $actor->can('calificaciones.ver.global') && $actor->can('estudiantes.ver.global'), 403);
        $enrollment = InscripcionEstudiante::with(['estudiante', 'especialidadTecnica', 'gestionAcademica'])->where('est_ins', '!=', 'ANULADA')->findOrFail($inscripcion);
        $clases = ClaseVirtual::query()->whereHas('estudiantes', fn ($q) => $q->where('cod_est', $enrollment->cod_est)->where('est_cla_est', '!=', 'ANULADO'));

        return $this->buildContext($enrollment->estudiante, $enrollment, $clases, true);
    }

    private function buildContext(Estudiante $student, ?InscripcionEstudiante $enrollment, Builder $clases, bool $institucional = false): array
    {
        $storageReady = Schema::hasColumn('orientacion_actividades', 'riasec_public');
        $activity = $storageReady ? OrientacionActividad::where('cod_est', $student->cod_est)
            ->where(fn ($q) => $q->where('estado', '!=', 'en_proceso')->orWhereNotNull('riasec_score'))
            ->when($institucional, fn ($q) => $q->where('cod_gea', $enrollment?->cod_gea))
            ->when(! $institucional, fn ($q) => $q->whereNotNull('riasec_public'))->latest('id')->first() : null;
        $draft = $storageReady && ! $institucional ? $this->consultaBorrador($student->cod_est, $enrollment?->cod_gea)->first() : null;
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
        if (ExperienciaIntereses::perfilVigente($activity?->riasec_public, $activity?->riasec_score)
            && (! $institucional || $activity->finalizado_at)) {
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

        $periodos = array_merge(array_column($payload['history'], 'period'), isset($payload['academic_period']) ? [$payload['academic_period']] : []);
        $gestiones = GestionAcademica::whereIn('cod_gea', $periodos)->pluck('ani_gea', 'cod_gea')->map(fn ($anio) => (int) $anio)->all();

        return compact('student', 'activity', 'draft', 'payload', 'precheck', 'storageReady', 'gestiones');
    }

    /** Guarda respuestas parciales, sin puntuar ni habilitar un análisis. */
    public function guardarBorrador(User $user, array $public): AporteResponse
    {
        $student = $this->student($user);
        if (! Schema::hasColumn('orientacion_actividades', 'riasec_public')) {
            return new AporteResponse(false, [], 'Todavía no podemos guardar tu avance. La administración necesita habilitar esta función.');
        }
        $validator = Validator::make($public, [
            'instrument_version' => 'required|string|max:80',
            'responses' => 'required|array|min:1|max:30',
            'responses.*' => 'required|array:item_id,value',
            'responses.*.item_id' => 'required|integer|distinct|between:1,30',
            'responses.*.value' => 'required|integer|between:1,5',
        ]);
        if ($validator->fails() || array_diff(array_keys($public), ['instrument_version', 'responses'])
            || ! collect($public['responses'])->every(fn ($respuesta) => is_int($respuesta['item_id']) && is_int($respuesta['value']))) {
            return new AporteResponse(false, [], 'Revisa las respuestas del cuestionario antes de guardar tu avance.', 422);
        }
        $instrument = $this->client->riasecInstrument();
        if (! $instrument->available) {
            return new AporteResponse(false, [], 'No podemos comprobar la versión del cuestionario en este momento. Tu borrador anterior sigue guardado.', 503);
        }
        if ($public['instrument_version'] !== ($instrument->data['instrument_version'] ?? null)) {
            return new AporteResponse(false, [], 'La versión del cuestionario cambió. Recarga esta página antes de guardar un avance nuevo.', 422);
        }
        $period = $student->inscripciones()->where('est_ins', 'ACTIVA')->latest('fei_ins')->value('cod_gea');
        usort($public['responses'], fn ($a, $b) => $a['item_id'] <=> $b['item_id']);
        $draft = DB::transaction(function () use ($student, $period, $public) {
            Estudiante::whereKey($student->cod_est)->lockForUpdate()->firstOrFail();
            $draft = $this->consultaBorrador($student->cod_est, $period, $public['instrument_version'])->first()
                ?? new OrientacionActividad(['cod_est' => $student->cod_est, 'cod_gea' => $period, 'estado' => 'en_proceso', 'iniciado_at' => now()]);
            $draft->fill(['riasec_public' => $public, 'avance' => (int) floor(count($public['responses']) / 30 * 100)]);
            $draft->save();

            return $draft;
        });

        return new AporteResponse(true, [
            'draftSaved' => true,
            'answered' => count($public['responses']),
            'savedAt' => $draft->updated_at->toIso8601String(),
            'responses' => (object) collect($public['responses'])->mapWithKeys(fn ($respuesta) => [$respuesta['item_id'] => $respuesta['value']])->all(),
            'instrument_version' => $public['instrument_version'],
        ], 'Tu avance está guardado. Puedes retomarlo otro día.', 200);
    }

    private function consultaBorrador(string $estudiante, ?string $periodo, ?string $version = null): Builder
    {
        return OrientacionActividad::where('cod_est', $estudiante)->where('cod_gea', $periodo)
            ->where('estado', 'en_proceso')->whereNull('riasec_score')->whereNull('finalizado_at')
            ->whereNotNull('riasec_public')->when($version, fn ($q) => $q->where('riasec_public->instrument_version', $version))->latest('id');
    }

    public function score(User $user, array $public): AporteResponse
    {
        $context = $this->context($user);
        if (! $context['storageReady']) {
            return new AporteResponse(false, [], 'Todavía no podemos guardar tu cuestionario. La administración necesita habilitar esta función.');
        }
        $response = $this->client->riasecScore($public);
        if (! $response->available) {
            return $response;
        }
        $hash = hash('sha256', json_encode($public, JSON_THROW_ON_ERROR));
        DB::transaction(function () use ($user, $context, $public, $response, $hash) {
            // Serializa envíos del mismo estudiante; no sobrescribe instrumentos locales ni snapshots anteriores.
            Estudiante::whereKey($context['student']->cod_est)->lockForUpdate()->firstOrFail();
            $periodo = $context['payload']['academic_period'] ?? null;
            $draft = $this->consultaBorrador($context['student']->cod_est, $periodo, $public['instrument_version'])->first();
            $existing = OrientacionActividad::where('cod_est', $context['student']->cod_est)->whereNotNull('riasec_public')
                ->where(fn ($q) => $q->where('estado', '!=', 'en_proceso')->orWhereNotNull('riasec_score'))->latest('id')->first();
            $nuevoPorCambio = app(PlanPersonalEstudiante::class)->requiereCuestionarioNuevo($user, $context['student']->cod_est, $periodo, data_get($existing?->riasec_score, 'trace_id'));
            if ($draft || $nuevoPorCambio || $existing?->riasec_input_hash !== $hash || ! ExperienciaIntereses::perfilVigente($existing?->riasec_public, $existing?->riasec_score)) {
                $final = $draft ?? new OrientacionActividad(['cod_est' => $context['student']->cod_est, 'cod_gea' => $periodo, 'iniciado_at' => now()]);
                $final->fill(['estado' => 'finalizado', 'avance' => 100, 'finalizado_at' => now(),
                    'riasec_public' => $public, 'riasec_score' => $response->data, 'riasec_input_hash' => $hash])->save();
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
        if (! $response->available) {
            return $response;
        }

        // Las pruebas finalizadas son historia inmutable: cada estudio conserva una versión nueva.
        try {
            return DB::transaction(function () use ($context, $response) {
                Estudiante::whereKey($context['student']->cod_est)->lockForUpdate()->firstOrFail();
                $origen = $context['activity'];
                $actual = OrientacionActividad::where('cod_est', $context['student']->cod_est)->whereNotNull('riasec_public')
                    ->where(fn ($q) => $q->where('estado', '!=', 'en_proceso')->orWhereNotNull('riasec_score'))->latest('id')->first();
                $periodoActual = $context['student']->inscripciones()->where('est_ins', 'ACTIVA')->latest('fei_ins')->value('cod_gea');
                if (! $actual || $actual->cod_gea !== $origen->cod_gea
                    || $actual->riasec_public !== $origen->riasec_public || $actual->riasec_score !== $origen->riasec_score
                    || $actual->riasec_input_hash !== $origen->riasec_input_hash
                    || $periodoActual !== ($context['payload']['academic_period'] ?? null)) {
                    return new AporteResponse(false, ['failure_stage' => 'profile_changed'], 'Tu cuestionario o gestión cambió mientras preparábamos el estudio. Revisa tu perfil actual y vuelve a generar el análisis.', 422);
                }
                if (is_array($actual->analysis_snapshot)
                    && $this->contenidoEstudio($actual->analysis_snapshot) === $this->contenidoEstudio($response->data)) {
                    return new AporteResponse(true, $actual->analysis_snapshot, $response->message, 200,
                        data_get($actual->analysis_snapshot, 'trace_id'), $response->latencyMs, $response->serverLatencyMs);
                }
                $nuevo = $origen->only(['cod_est', 'cod_gea', 'riasec_public', 'riasec_score', 'riasec_input_hash', 'iniciado_at', 'finalizado_at', 'cod_ior']);
                if ($origen->cod_ior !== null) {
                    $nuevo['int_oac'] = (int) OrientacionActividad::where('cod_est', $origen->cod_est)->where('cod_ior', $origen->cod_ior)->max('int_oac') + 1;
                }
                OrientacionActividad::create($nuevo + ['estado' => 'finalizado', 'avance' => 100,
                    'analysis_snapshot' => $response->data, 'analysis_completed_at' => now()]);

                return $response;
            });
        } catch (QueryException $exception) {
            Log::warning('No se pudo guardar la nueva versión del estudio de orientación.', ['failure_stage' => 'storage', 'trace_id' => $response->traceId, 'sqlstate' => $exception->errorInfo[0] ?? null]);

            return new AporteResponse(false, ['failure_stage' => 'storage'], 'El servicio calculó el estudio, pero no pudimos confirmar su guardado. Tu cuestionario y los estudios anteriores siguen conservados.', 503, $response->traceId);
        }
    }

    private function contenidoEstudio(array $estudio): array
    {
        // Las versiones del motor, criterios, catálogo, fuentes y resultados sí distinguen estudios.
        unset($estudio['trace_id'], $estudio['traceability']['trace_id'], $estudio['traceability']['generated_at']);

        return $this->ordenarContenidoEstudio($estudio);
    }

    private function ordenarContenidoEstudio(array $contenido): array
    {
        foreach ($contenido as $clave => $valor) {
            if (is_array($valor)) {
                $contenido[$clave] = $this->ordenarContenidoEstudio($valor);
            }
        }
        if (! array_is_list($contenido)) {
            ksort($contenido);
        }

        return $contenido;
    }
}
