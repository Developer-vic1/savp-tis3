<?php

namespace App\Services\AporteIngenieril\DTO;

use Illuminate\Support\Facades\Validator;

/** Contrato 1.0 leído del aporte; no transforma Likert local en RIASEC 0..4. */
final class ContratoAporteIngenieril
{
    public static function solicitudValida(string $operation, array $data): bool
    {
        $rules = match ($operation) {
            'tutor' => [
                'schema_version' => 'required|in:1.0', 'question' => 'required|string|min:2|max:2000',
                'academic_context' => 'sometimes|array:academic_period,areas_to_reinforce,course,preparation_route,strengths',
                'student_context' => 'sometimes|array:affinity_label,areas_to_reinforce,preparation_label,riasec_code,strengths,technical_specialty',
                'conversation_history' => 'sometimes|array|max:8',
                'conversation_history.*' => 'array:role,content',
                'conversation_history.*.role' => 'required|in:user,assistant',
                'conversation_history.*.content' => 'required|string|min:1|max:2000',
            ],
            'knowledge' => ['schema_version' => 'required|in:1.0', 'query' => 'required|string|min:2|max:1000', 'top_k' => 'required|integer|min:1|max:20', 'official_only' => 'required|boolean'],
            'analysis' => ['schema_version' => 'required|in:1.0', 'student_id' => 'required|string|max:100|regex:/^[A-Za-z0-9._:-]+$/', 'academic_period' => 'nullable|string|max:80',
                'academic' => 'nullable|array:records', 'academic.records' => 'required_with:academic|array|min:1|max:500',
                'academic.records.*' => 'array:subject,score,scale_min,scale_max,period,period_order,area',
                'academic.records.*.subject' => 'required|string|max:120', 'academic.records.*.score' => 'required|numeric|between:-10000,10000',
                'academic.records.*.scale_min' => 'required|numeric|between:-10000,10000', 'academic.records.*.scale_max' => 'required|numeric|between:-10000,10000',
                'academic.records.*.period' => 'nullable|string|max:80', 'academic.records.*.period_order' => 'nullable|integer|between:0,100', 'academic.records.*.area' => 'nullable|string|max:120',
                'attendance' => 'nullable|array:attended_classes,total_classes', 'attendance.attended_classes' => 'required_with:attendance|integer|between:0,10000',
                'attendance.total_classes' => 'required_with:attendance|integer|between:1,10000', 'declared_interests' => 'nullable|array|max:50',
                'declared_interests.*' => 'string|max:160', 'technical' => 'nullable|array:specialty', 'technical.specialty' => 'nullable|string|max:160'],
            default => [],
        };
        $allowed = array_filter(array_keys($rules), fn ($key) => ! str_contains($key, '.'));
        if (! $rules || array_diff(array_keys($data), $allowed) || Validator::make($data, $rules)->fails()) {
            return false;
        }
        foreach ($data['academic']['records'] ?? [] as $record) {
            if ($record['scale_max'] <= $record['scale_min'] || $record['score'] < $record['scale_min'] || $record['score'] > $record['scale_max']) {
                return false;
            }
        }

        return ! isset($data['attendance']) || $data['attendance']['attended_classes'] <= $data['attendance']['total_classes'];
    }

    public static function respuestaValida(string $operation, mixed $data): bool
    {
        if (! is_array($data) || ! $data || array_is_list($data)) {
            return false;
        }
        if ($operation === 'health') {
            return ($data['status'] ?? null) === 'ok' && ($data['schema_version'] ?? null) === '1.0';
        }
        $rules = ['schema_version' => 'required|in:1.0', 'trace_id' => 'required|string|max:100', 'warnings' => 'required|array', 'warnings.*' => 'string|max:2000'];
        $rules += match ($operation) {
            'tutor' => ['answer' => 'required|string|max:20000', 'answer_mode' => 'required|in:STRUCTURED', 'suggested_topics' => 'required|array',
                'suggested_topics.*' => 'string|max:500', 'sources' => 'required|array|max:20', 'insufficient_evidence' => 'required|boolean'],
            'knowledge' => ['results' => 'required|array|max:20', 'insufficient_evidence' => 'required|boolean', 'corpus_version' => 'required|string|max:100', 'embedding_model' => 'required|string|max:200', 'retrieval_version' => 'required|string|max:100'],
            'analysis' => ['status' => 'required|in:COMPLETE,PARTIAL,INSUFFICIENT', 'student_ref' => 'required|string|max:100', 'input_hash' => 'required|string|max:100',
                'generated_at' => 'required|date', 'coverage' => 'required|array:ratio,components', 'coverage.ratio' => 'required|numeric|between:0,1', 'coverage.components' => 'required|array',
                'career_ranking' => 'required|array', 'ranking_status' => 'required|in:RANKED,INSUFFICIENT_EVIDENCE'],
            default => ['invalid_operation' => 'required'],
        };
        // Una lista vacía es válida para fuentes o recomendaciones: no prueba existencia de evidencia.
        foreach (['warnings', 'sources', 'suggested_topics', 'results', 'career_ranking', 'coverage.components'] as $key) {
            if (isset($rules[$key])) {
                $rules[$key] = str_replace('required|array', 'present|array', $rules[$key]);
            }
        }
        if (Validator::make($data, $rules)->fails()) {
            return false;
        }
        foreach ($data['sources'] ?? $data['results'] ?? [] as $source) {
            if (! is_array($source) || Validator::make($source, [
                'source_id' => 'required|string|max:200', 'title' => 'required|string|max:500', 'institution' => 'required|string|max:300',
                'reference' => 'required|string|max:2000', 'official' => 'required|boolean', 'summary' => 'present|string|max:10000',
            ])->fails()) {
                return false;
            }
        }

        return ! isset($data['insufficient_evidence']) || is_bool($data['insufficient_evidence']);
    }
}
