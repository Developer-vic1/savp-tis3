<?php

namespace App\Services\AporteIngenieril;

use App\Models\Oficial\Academico\Estudiante;
use App\Models\Oficial\Academico\SeguimientoAcademico;
use App\Models\Oficial\Sistema\User;
use App\Services\AporteIngenieril\DTO\ContratoAporteIngenierilV2;
use App\Services\AulaVirtual\CursoVirtualService;
use App\Support\Orientacion\ExperienciaIntereses;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/** Seguimiento personal versionado: ninguna elección constituye una recomendación automática. */
final class PlanPersonalEstudiante
{
    public function __construct(private CursoVirtualService $cursos, private StudentOrientationService $orientacion) {}

    private function contexto(User $usuario): array
    {
        abort_unless($usuario->can('Perfil_Academico'), 403);
        $estudiante = $this->cursos->estudianteDeUsuario($usuario);
        abort_unless($estudiante, 403);
        $inscripcion = $estudiante->inscripciones()->where('est_ins', 'ACTIVA')->latest('fei_ins')->first();
        abort_unless($inscripcion, 409, 'Tu plan necesita una inscripción activa.');
        $versiones = SeguimientoAcademico::where('cod_ins', $inscripcion->cod_ins)->where('cod_usu_res', $usuario->cod_usu)
            ->where('tip_seg', 'PLAN_PERSONAL')->where('ori_seg', 'ESTUDIANTE')->orderByDesc('created_at')->orderByDesc('cod_seg')->get()
            ->map(fn ($fila) => json_decode($fila->pro_acc_seg ?? '', true))->filter(fn ($plan) => $this->planValido($plan))->values();
        $contextoOrientacion = $usuario->can('Orientacion_Academica_Profesional') ? $this->orientacion->context($usuario) : null;
        $actividad = $contextoOrientacion['activity'] ?? null;
        $estudio = $actividad?->analysis_snapshot;
        $catalogo = $actividad?->cod_est === $estudiante->cod_est && $actividad?->cod_gea === $inscripcion->cod_gea
            && ExperienciaIntereses::perfilVigente($actividad?->riasec_public, $actividad?->riasec_score)
            && is_array($estudio) && ContratoAporteIngenierilV2::respuestaValida('analysis_v2', $estudio)
            ? collect($estudio['career_evidence_profiles'])->filter(fn ($carrera) => is_string($carrera['career_id'] ?? null))->keyBy('career_id')->all() : [];

        return compact('estudiante', 'inscripcion', 'versiones', 'actividad', 'catalogo');
    }

    public function snapshot(User $usuario): array
    {
        $contexto = $this->contexto($usuario);
        $actual = $contexto['versiones']->first();
        $historial = $contexto['versiones']->unique('id')->filter(fn ($plan) => $plan['id'] !== ($actual['id'] ?? null))->values()->all();

        return ['planPersonal' => $actual, 'historialPlanes' => $historial, 'carrerasPlan' => $contexto['catalogo'],
            'progresoPlan' => $actual && count($actual['acciones']) ? (int) round(100 * count(array_filter($actual['acciones'], fn ($accion) => $accion['completada'])) / count($actual['acciones'])) : 0,
            'gestionPlan' => $contexto['inscripcion']->cod_gea];
    }

    public function elegir(User $usuario, array $datos): array
    {
        foreach (['meta', 'accion_personal'] as $campo) {
            if (is_string($datos[$campo] ?? null)) {
                $datos[$campo] = trim($datos[$campo]);
            }
        }
        validator($datos, ['career_id' => 'required|string|max:160', 'meta' => 'required|string|min:10|max:1000',
            'accion_personal' => 'required|string|min:10|max:500', 'compromiso' => 'accepted', 'student_id' => 'prohibited'])->validate();

        return $this->mutar($usuario, function ($contexto) use ($datos) {
            $anterior = $contexto['versiones']->first();
            $carrera = $contexto['catalogo'][$datos['career_id']] ?? null;
            if (! $carrera) {
                throw ValidationException::withMessages(['career_id' => 'Elige una carrera documentada en tu estudio actual.']);
            }
            if ($anterior) {
                $cambio = $anterior['cambio_pendiente'] ?? null;
                if (! $cambio || $cambio['career_id'] !== $datos['career_id']
                    || $contexto['actividad']->riasec_score['trace_id'] === $cambio['score_trace_id']
                    || $contexto['actividad']->finalizado_at?->toIso8601String() < $cambio['solicitado_at']) {
                    throw ValidationException::withMessages(['career_id' => 'Para cambiar el enfoque, explica el motivo, responde de nuevo el cuestionario y genera un estudio actualizado.']);
                }
            }
            $acciones = [['id' => (string) Str::uuid(), 'titulo' => trim($datos['accion_personal']), 'tipo' => 'COMPROMISO_PERSONAL', 'completada' => false]];
            foreach (data_get($carrera, 'academic_program.documented_subjects', []) as $materia) {
                $acciones[] = ['id' => (string) Str::uuid(), 'titulo' => 'Explorar '.$materia, 'tipo' => 'MATERIA_DOCUMENTADA', 'completada' => false];
            }
            foreach (data_get($carrera, 'preparation.reinforcement_areas', []) as $refuerzo) {
                $acciones[] = ['id' => (string) Str::uuid(), 'titulo' => 'Practicar '.$refuerzo['competency'], 'motivo' => $refuerzo['rationale'], 'tipo' => 'REFUERZO', 'completada' => false];
            }
            $acciones[] = ['id' => (string) Str::uuid(), 'titulo' => 'Revisar las fuentes y los requisitos de la carrera antes de decidir', 'tipo' => 'REVISION_PERSONAL', 'completada' => false];
            $actividad = $contexto['actividad'];

            return ['schema_version' => 'plan-personal-1.0', 'id' => (string) Str::uuid(), 'revision' => 1, 'meta' => trim($datos['meta']),
                'career_id' => $carrera['career_id'], 'carrera' => $carrera['career_name'], 'universidad' => $carrera['university'],
                'acciones' => $acciones, 'fuentes' => data_get($carrera, 'academic_program.sources', []),
                'origen' => ['actividad_id' => $actividad->id, 'gestion' => $contexto['inscripcion']->cod_gea,
                    'riasec_hash' => $actividad->riasec_input_hash, 'instrument_version' => $actividad->riasec_public['instrument_version'],
                    'score_trace_id' => $actividad->riasec_score['trace_id'], 'study_trace_id' => data_get($actividad->analysis_snapshot, 'trace_id'),
                    'engine_version' => data_get($actividad->analysis_snapshot, 'engine_version')],
                'cambio_pendiente' => null, 'sustituye_plan_id' => $anterior['id'] ?? null, 'motivo' => $anterior['cambio_pendiente']['motivo'] ?? 'Elección personal inicial',
                'creado_at' => now()->toIso8601String()];
        });
    }

    public function solicitarCambio(User $usuario, array $datos): array
    {
        if (is_string($datos['motivo'] ?? null)) {
            $datos['motivo'] = trim($datos['motivo']);
        }
        validator($datos, ['career_id' => 'required|string|max:160', 'motivo' => 'required|string|min:15|max:1000', 'confirmar' => 'accepted', 'student_id' => 'prohibited'])->validate();

        return $this->mutar($usuario, function ($contexto) use ($datos) {
            $plan = $contexto['versiones']->first();
            if (! $plan || ! isset($contexto['catalogo'][$datos['career_id']]) || $datos['career_id'] === $plan['career_id']) {
                throw ValidationException::withMessages(['career_id' => 'Elige otra carrera del estudio para solicitar el cambio.']);
            }
            $plan['revision']++;
            $plan['cambio_pendiente'] = ['career_id' => $datos['career_id'], 'motivo' => trim($datos['motivo']),
                'solicitado_at' => now()->toIso8601String(), 'score_trace_id' => $contexto['actividad']->riasec_score['trace_id']];
            $plan['motivo'] = 'Solicitud de cambio: '.$datos['motivo'];

            return $plan;
        });
    }

    public function progreso(User $usuario, array $datos): array
    {
        validator($datos, ['plan_id' => 'required|uuid', 'accion_id' => 'required|uuid', 'completada' => 'required|boolean', 'student_id' => 'prohibited'])->validate();

        return $this->mutar($usuario, function ($contexto) use ($datos) {
            $plan = $contexto['versiones']->first();
            if (! $plan || $plan['id'] !== $datos['plan_id']) {
                throw ValidationException::withMessages(['plan_id' => 'Actualiza la página para trabajar en tu plan actual.']);
            }
            $encontrada = false;
            foreach ($plan['acciones'] as &$accion) {
                if ($accion['id'] === $datos['accion_id']) {
                    $accion['completada'] = (bool) $datos['completada'];
                    $encontrada = true;
                }
            }
            if (! $encontrada) {
                throw ValidationException::withMessages(['accion_id' => 'Esta acción no pertenece a tu plan actual.']);
            }
            $plan['revision']++;
            $plan['motivo'] = 'Actualización personal de una acción';

            return $plan;
        });
    }

    public function cancelarCambio(User $usuario, array $datos): array
    {
        validator($datos, ['plan_id' => 'required|uuid', 'student_id' => 'prohibited'])->validate();

        return $this->mutar($usuario, function ($contexto) use ($datos) {
            $plan = $contexto['versiones']->first();
            if (! $plan || $plan['id'] !== $datos['plan_id'] || ! ($plan['cambio_pendiente'] ?? null)) {
                throw ValidationException::withMessages(['plan_id' => 'Revisa tu plan actual antes de cancelar una solicitud de cambio.']);
            }
            $plan['revision']++;
            $plan['cambio_pendiente'] = null;
            $plan['motivo'] = 'Solicitud de cambio cancelada por estudiante';

            return $plan;
        });
    }

    private function mutar(User $usuario, callable $operacion): array
    {
        $previo = $this->contexto($usuario);

        try {
            return DB::transaction(function () use ($usuario, $previo, $operacion) {
                Estudiante::whereKey($previo['estudiante']->cod_est)->lockForUpdate()->firstOrFail();
                $contexto = $this->contexto($usuario);
                $plan = $operacion($contexto);
                SeguimientoAcademico::create(['cod_ins' => $contexto['inscripcion']->cod_ins, 'tip_seg' => 'PLAN_PERSONAL', 'ori_seg' => 'ESTUDIANTE',
                    'vis_seg' => 'PERSONAL', 'cod_usu_res' => $usuario->cod_usu, 'est_seg' => 'EN_SEGUIMIENTO', 'fec_ape_seg' => now(),
                    'mot_seg' => $plan['motivo'], 'pro_acc_seg' => json_encode($plan, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE)]);

                return $plan;
            });
        } catch (QueryException $error) {
            Log::warning('No se pudo guardar una revisión del plan personal.', ['failure_stage' => 'storage', 'sqlstate' => $error->errorInfo[0] ?? null]);
            throw ValidationException::withMessages(['plan' => 'No pudimos guardar el cambio. Conservamos el plan confirmado anterior. Tus campos permanecen en el formulario; vuelve a intentarlo.']);
        }
    }

    public function requiereCuestionarioNuevo(User $usuario, string $estudiante, ?string $periodo, ?string $scoreTrace): bool
    {
        if (! $usuario->can('Perfil_Academico') || ! Schema::hasColumn('seguimiento_academico', 'cod_ins')) {
            return false;
        }
        $plan = SeguimientoAcademico::where('cod_usu_res', $usuario->cod_usu)->where('tip_seg', 'PLAN_PERSONAL')->where('ori_seg', 'ESTUDIANTE')
            ->whereHas('inscripcionEstudiante', fn ($q) => $q->where('cod_est', $estudiante)->where('cod_gea', $periodo)->where('est_ins', 'ACTIVA'))
            ->orderByDesc('created_at')->orderByDesc('cod_seg')->get()->map(fn ($fila) => json_decode($fila->pro_acc_seg ?? '', true))
            ->first(fn ($plan) => $this->planValido($plan));

        return ($plan['schema_version'] ?? null) === 'plan-personal-1.0'
            && is_array($plan['cambio_pendiente'] ?? null) && ($plan['cambio_pendiente']['score_trace_id'] ?? null) === $scoreTrace;
    }

    private function planValido(mixed $plan): bool
    {
        return is_array($plan) && ($plan['schema_version'] ?? null) === 'plan-personal-1.0' && Validator::make($plan, [
            'id' => 'required|uuid', 'revision' => 'required|integer|min:1', 'career_id' => 'required|string',
            'carrera' => 'required|string', 'universidad' => 'required|string', 'meta' => 'required|string',
            'acciones' => 'required|array|min:1', 'acciones.*.id' => 'required|uuid', 'acciones.*.titulo' => 'required|string',
            'acciones.*.completada' => 'required|boolean', 'cambio_pendiente' => 'present|nullable|array',
            'cambio_pendiente.career_id' => 'required_with:cambio_pendiente|string',
            'cambio_pendiente.motivo' => 'required_with:cambio_pendiente|string',
            'cambio_pendiente.solicitado_at' => 'required_with:cambio_pendiente|date',
            'cambio_pendiente.score_trace_id' => 'required_with:cambio_pendiente|string',
        ])->passes();
    }
}
