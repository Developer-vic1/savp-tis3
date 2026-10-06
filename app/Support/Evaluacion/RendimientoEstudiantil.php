<?php

namespace App\Support\Evaluacion;

use App\Models\Oficial\Academico\Calificacion;
use App\Models\Oficial\AporteAcademicoVocacional\OrientacionActividad;
use App\Services\AporteIngenieril\DTO\ContratoAporteIngenierilV2;
use Illuminate\Database\Query\Builder;

/** Lectura de hechos y estudios conservados; no genera ni modifica resultados. */
class RendimientoEstudiantil
{
    public function __construct(private readonly PanelResultadosAcademicos $panel) {}

    public function consulta(array $filtros): Builder
    {
        $consulta = $this->panel->consulta(array_replace($filtros, ['search' => '']));

        // Una gestión es obligatoria: nunca sumar trayectorias anuales como alumnos diferentes.
        $consulta->when(empty($filtros['gestion']), fn ($q) => $q->whereRaw('1 = 0'));
        $consulta->when($filtros['estudiante'] ?? '', fn ($q, $codigo) => $q->where('i.cod_est', $codigo));
        $consulta->when($filtros['nivel'] ?? '', fn ($q, $valor) => $q->where('c.niv_cur', $valor));
        $consulta->when($filtros['paralelo'] ?? '', fn ($q, $valor) => $q->where('i.cod_par', $valor));
        $consulta->when($filtros['turno'] ?? '', fn ($q, $valor) => $q->where('i.cod_tur', $valor));
        $consulta->when($filtros['search'] ?? '', function ($q, $texto) {
            $q->where(fn ($w) => $w->whereRaw("LOWER(CONCAT(p.nom_per, ' ', p.ape_pat_per, ' ', COALESCE(p.ape_mat_per, ''))) LIKE ?", ['%'.mb_strtolower($texto).'%'])
                ->orWhere('i.cod_est', 'like', '%'.$texto.'%'));
        });

        return $consulta;
    }

    public function estudio(?OrientacionActividad $actividad): array
    {
        if (! $actividad || ! $actividad->analysis_completed_at || ! $actividad->analysis_snapshot) {
            return ['estado' => 'pendiente', 'datos' => null];
        }

        $datos = $actividad->analysis_snapshot;
        $referencia = hash_hmac('sha256', (string) $actividad->cod_est, (string) config('app.key'));
        if (! ContratoAporteIngenierilV2::respuestaValida('analysis_v2', $datos)
            || ! hash_equals($referencia, (string) ($datos['student_ref'] ?? ''))
            || ($datos['trace_id'] ?? null) !== data_get($datos, 'traceability.trace_id')) {
            return ['estado' => 'requiere_revision', 'datos' => null];
        }

        return ['estado' => 'contrato_valido', 'datos' => $datos];
    }

    public function detalle(object $registro, bool $orientacion, string $periodo = ''): array
    {
        $notas = Calificacion::with(['asignatura', 'planEspecialidad.especialidad', 'periodoEvaluacion'])
            ->where('cod_ins', $registro->cod_ins)->whereIn('est_cal', ['VIGENTE', 'RECTIFICADA'])
            ->when($periodo !== '', fn ($q) => $q->where('cod_pev', $periodo))
            ->orderBy('fea_cal')->orderBy('cod_cal')->get();
        $actividad = $orientacion ? OrientacionActividad::with(['resultado.carreras', 'respuestas.pregunta', 'respuestas.instrumentoPregunta.orientacionPregunta'])->where('cod_est', $registro->cod_est)
            ->where('cod_gea', $registro->cod_gea)->orderByDesc('id')->first() : null;
        $estudio = $this->estudio($actividad);
        $notasPosteriores = $actividad?->analysis_completed_at && $notas->contains(
            fn ($nota) => $nota->fea_cal?->greaterThan($actividad->analysis_completed_at)
                || $nota->updated_at?->greaterThan($actividad->analysis_completed_at)
        );

        return compact('registro', 'notas', 'actividad', 'estudio', 'notasPosteriores');
    }

    public function grupo(Builder $consulta, bool $orientacion, string $carreraSeleccionada = '', string $tipoRelacion = 'academica'): array
    {
        $catalogo = $orientacion ? app(CatalogoCarrerasRendimiento::class)->leer() : ['version' => null, 'carreras' => []];
        $resultado = ['validos' => 0, 'revision' => 0, 'estados' => ['COMPLETE' => 0, 'PARTIAL' => 0, 'INSUFFICIENT' => 0], 'evidencias' => [], 'carreras' => $catalogo['carreras'], 'ultima' => null,
            'version_catalogo' => $catalogo['version'], 'vinculados' => [], 'carrera_seleccionada' => $catalogo['carreras'][$carreraSeleccionada] ?? null];
        if (! $orientacion) {
            return $resultado;
        }
        $cohorte = (clone $consulta)->select('i.cod_est', 'i.cod_gea', 'i.cod_ins', 'p.nom_per', 'p.ape_pat_per', 'p.ape_mat_per')->distinct();
        $actividades = OrientacionActividad::query()->joinSub($cohorte, 'cohorte', fn ($j) => $j
            ->on('cohorte.cod_est', '=', 'orientacion_actividades.cod_est')->on('cohorte.cod_gea', '=', 'orientacion_actividades.cod_gea'))
            ->whereRaw('id = (SELECT MAX(actual.id) FROM orientacion_actividades AS actual WHERE actual.cod_est = orientacion_actividades.cod_est AND actual.cod_gea = orientacion_actividades.cod_gea)')
            ->select('orientacion_actividades.*', 'cohorte.cod_ins', 'cohorte.nom_per', 'cohorte.ape_pat_per', 'cohorte.ape_mat_per')->cursor();
        foreach ($actividades as $actividad) {
            $estudio = $this->estudio($actividad);
            if ($estudio['estado'] === 'requiere_revision') {
                $resultado['revision']++;
            }
            if (! $estudio['datos']) {
                continue;
            }
            $datos = $estudio['datos'];
            $resultado['validos']++;
            $resultado['estados'][$datos['analysis_status']]++;
            $fecha = $actividad->analysis_completed_at->format('Y-m-d H:i:s');
            $resultado['ultima'] = max($resultado['ultima'] ?? '', $fecha);
            foreach (['academic', 'attendance', 'learning_activity', 'historical', 'technical', 'declared_interest'] as $tipo) {
                $estado = data_get($datos, 'student_snapshot.'.$tipo.'_evidence.status');
                $resultado['evidencias'][$tipo] ??= array_fill_keys(['AVAILABLE', 'PARTIAL', 'INSUFFICIENT', 'UNAVAILABLE'], 0);
                $resultado['evidencias'][$tipo][$estado]++;
            }
            $vistas = [];
            foreach ($datos['career_evidence_profiles'] as $carrera) {
                $clave = $carrera['career_id'] ?? '';
                if (isset($vistas[$clave]) || ! isset($resultado['carreras'][$clave]) || ! $resultado['carreras'][$clave]['elegible']) {
                    continue;
                }
                $vistas[$clave] = true;
                $resultado['carreras'][$clave]['estudios']++;
                $relaciones = $this->relacionesCarrera($carrera);
                foreach ($relaciones as $tipo => $presente) {
                    $resultado['carreras'][$clave][$tipo] += (int) $presente;
                }
                if ($clave === $carreraSeleccionada && ($relaciones[$tipoRelacion] ?? false)) {
                    $resultado['vinculados'][] = ['inscripcion' => $actividad->cod_ins, 'codigo' => $actividad->cod_est,
                        'nombre' => trim($actividad->nom_per.' '.$actividad->ape_pat_per.' '.$actividad->ape_mat_per),
                        'fecha' => $actividad->analysis_completed_at->format('d/m/Y'), 'relaciones' => $relaciones];
                }
            }
        }
        $resultado['carreras'] = array_values($resultado['carreras']);

        return $resultado;
    }

    public function relacionesCarrera(array $carrera): array
    {
        // Presencia de evidencia, sin fabricar un score compuesto de compatibilidad.
        return ['academica' => (int) data_get($carrera, 'preparation.relations_with_observed_academic_evidence', 0) > 0
                && ! empty(data_get($carrera, 'preparation.academic_evidence', [])),
            'tecnica' => in_array(data_get($carrera, 'technical_relation.status'), ['AVAILABLE', 'PARTIAL'], true)
                && ! empty(data_get($carrera, 'technical_evidence', [])),
            'declarados' => in_array(data_get($carrera, 'declared_interest_relation.status'), ['AVAILABLE', 'PARTIAL'], true)
                && ! empty(data_get($carrera, 'declared_interest_evidence', []))];
    }
}
