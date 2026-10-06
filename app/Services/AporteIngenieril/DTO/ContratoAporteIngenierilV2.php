<?php

namespace App\Services\AporteIngenieril\DTO;

use Illuminate\Support\Facades\Validator;

final class ContratoAporteIngenierilV2
{
    public static function riasecValido(array $data): bool
    {
        return ! array_diff(array_keys($data), ['instrument_version', 'responses'])
            && ! Validator::make($data, [
                'instrument_version' => 'required|string|max:80',
                'responses' => 'required|array|size:30',
                'responses.*' => 'required|array:item_id,value',
                'responses.*.item_id' => 'required|integer|distinct|min:1|max:30',
                'responses.*.value' => 'required|integer|min:1|max:5',
            ])->fails()
            && collect($data['responses'])->every(fn ($item) => is_int($item['item_id']) && is_int($item['value']));
    }

    public static function solicitudValida(string $operation, array $data): bool
    {
        if ($operation === 'riasec_score') {
            return self::riasecValido($data);
        }
        if ($operation !== 'analysis_v2' || ($data['schema_version'] ?? null) !== '2.0') {
            return false;
        }
        $allowed = ['schema_version', 'student_id', 'academic_period', 'academic', 'attendance', 'riasec_public', 'technical', 'declared_interests', 'history', 'learning_activity'];
        if (array_diff(array_keys($data), $allowed) || ! self::riasecValido($data['riasec_public'] ?? [])) {
            return false;
        }
        $base = array_intersect_key($data, array_flip(['student_id', 'academic_period', 'academic', 'attendance', 'technical', 'declared_interests']));

        return ContratoAporteIngenieril::solicitudValida('analysis', ['schema_version' => '1.0'] + $base)
            && is_array($data['history'] ?? []) && is_array($data['learning_activity'] ?? []);
    }

    public static function respuestaValida(string $operation, mixed $data): bool
    {
        if (! is_array($data) || ($data['schema_version'] ?? null) !== '2.0') {
            return false;
        }
        $rules = match ($operation) {
            'riasec_instrument' => [
                'instrument_version' => 'required|string|max:80', 'title' => 'required|string',
                'items' => 'required|array|size:30', 'items.*.item_id' => 'required|integer|distinct|min:1|max:30',
                'items.*.text' => 'required|string|max:2000', 'response_scale' => 'required|array|size:5',
                'response_scale.*.value' => 'required|integer|distinct|min:1|max:5', 'response_scale.*.label' => 'required|string',
                'source_url' => 'required|url', 'source_attribution' => 'required|string',
                'limitations' => 'present|array',
            ],
            'riasec_score' => [
                'trace_id' => 'required|string|max:100', 'instrument_version' => 'required|string|max:80',
                'scores' => 'required|array:R,I,A,S,E,C', 'top_codes' => 'required|array',
                'top_codes.*' => 'in:R,I,A,S,E,C', 'holland_code' => 'required|regex:/^[RIASEC]{3}$/',
                'limitations' => 'present|array',
            ],
            'analysis_v2' => [
                'trace_id' => 'required|string|max:100', 'student_ref' => 'required|string|max:100',
                'analysis_status' => 'required|in:COMPLETE,PARTIAL,INSUFFICIENT',
                'student_snapshot' => 'required|array', 'career_evidence_profiles' => 'present|array', 'informational_external_careers' => 'present|array',
                'traceability' => 'required|array', 'traceability.input_hash' => 'required|regex:/^[a-f0-9]{64}$/',
                'sources_used' => 'present|array', 'limitations' => 'present|array', 'warnings' => 'present|array',
                'career_evidence_profiles.*.career_name' => 'required|string',
                'career_evidence_profiles.*.university' => 'required|string',
                'career_evidence_profiles.*.academic_program' => 'required|array',
                'career_evidence_profiles.*.academic_program.degree' => 'nullable|string',
                'career_evidence_profiles.*.academic_program.duration' => 'nullable|string',
                'career_evidence_profiles.*.academic_program.professional_profile' => 'nullable|string',
                'career_evidence_profiles.*.academic_program.knowledge_areas' => 'present|array',
                'career_evidence_profiles.*.academic_program.knowledge_areas.*' => 'string',
                'career_evidence_profiles.*.academic_program.documented_subjects' => 'present|array',
                'career_evidence_profiles.*.academic_program.documented_subjects.*' => 'string',
                'career_evidence_profiles.*.academic_program.curriculum_status' => 'required|in:AVAILABLE,PARTIAL,INSUFFICIENT,UNAVAILABLE',
                'career_evidence_profiles.*.academic_program.curriculum_scope' => 'required|in:DOCUMENTED_INITIAL_SUBJECTS,OFFICIAL_CURRICULUM_SOURCE_ONLY,UNAVAILABLE',
                'career_evidence_profiles.*.academic_program.curriculum_note' => 'required|string',
                'career_evidence_profiles.*.academic_program.sources' => 'present|array',
                'career_evidence_profiles.*.academic_program.sources.*.source_id' => 'required|string',
                'career_evidence_profiles.*.vocational_interest_relation.status' => 'required|in:AVAILABLE,PARTIAL,INSUFFICIENT,UNAVAILABLE',
                'career_evidence_profiles.*.technical_relation.status' => 'required|in:AVAILABLE,PARTIAL,INSUFFICIENT,UNAVAILABLE',
                'career_evidence_profiles.*.preparation.interpretation' => 'required|string',
                'career_evidence_profiles.*.preparation.reinforcement_areas' => 'present|array',
                'career_evidence_profiles.*.preparation.reinforcement_areas.*.competency' => 'required|string',
                'career_evidence_profiles.*.preparation.reinforcement_areas.*.rationale' => 'required|string',
                'career_evidence_profiles.*.evidence_quality.academic_record_count' => 'required|integer|min:0',
                'career_evidence_profiles.*.evidence_quality.distinct_subject_count' => 'required|integer|min:0',
                'career_evidence_profiles.*.evidence_quality.ordered_period_count' => 'required|integer|min:0',
                'career_evidence_profiles.*.areas_without_evidence' => 'present|array',
                'career_evidence_profiles.*.sources' => 'present|array',
                'career_evidence_profiles.*.sources.*.source_id' => 'required|string',
                'career_evidence_profiles.*.limitations' => 'present|array',
                'career_evidence_profiles.*.limitations.*.message' => 'required|string',
                'informational_external_careers.*.career_id' => 'required|string',
                'informational_external_careers.*.university' => 'required|string',
                'informational_external_careers.*.evidence_layer' => 'required|in:FUENTE_OFICIAL_EXTERNA,WEB_NO_VERIFICADA',
                'informational_external_careers.*.recommendation_eligible' => 'present|boolean',
                'informational_external_careers.*.source_ids' => 'required|array|min:1',
                'informational_external_careers.*.sources' => 'required|array|min:1',
                'informational_external_careers.*.sources.*.source_id' => 'required|string',
                'limitations.*.message' => 'required|string',
            ],
            default => ['unsupported' => 'required'],
        };
        if ($operation === 'riasec_score') {
            foreach (str_split('RIASEC') as $code) {
                $rules['scores.'.$code] = 'required|integer|between:0,20';
            }
        }
        if ($operation === 'analysis_v2') {
            foreach (['academic', 'attendance', 'learning_activity', 'historical', 'technical', 'declared_interest'] as $component) {
                $rules['student_snapshot.'.$component.'_evidence.status'] = 'required|in:AVAILABLE,PARTIAL,INSUFFICIENT,UNAVAILABLE';
            }
        }

        if (Validator::make($data, $rules)->fails()) {
            return false;
        }

        return $operation !== 'analysis_v2'
            || collect($data['informational_external_careers'])->every(
                fn ($career) => is_array($career)
                    && array_key_exists('recommendation_eligible', $career)
                    && $career['recommendation_eligible'] === false
            );
    }
}
