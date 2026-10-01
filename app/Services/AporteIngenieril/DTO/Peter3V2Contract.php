<?php

namespace App\Services\AporteIngenieril\DTO;

use Illuminate\Support\Facades\Validator;

final class Peter3V2Contract
{
    public static function validRiasec(array $data): bool
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

    public static function validRequest(string $operation, array $data): bool
    {
        if ($operation === 'riasec_score') {
            return self::validRiasec($data);
        }
        if ($operation !== 'analysis_v2' || ($data['schema_version'] ?? null) !== '2.0') {
            return false;
        }
        $allowed = ['schema_version', 'student_id', 'academic_period', 'academic', 'attendance', 'riasec_public', 'technical', 'declared_interests', 'history', 'learning_activity'];
        if (array_diff(array_keys($data), $allowed) || ! self::validRiasec($data['riasec_public'] ?? [])) {
            return false;
        }
        $base = array_intersect_key($data, array_flip(['student_id', 'academic_period', 'academic', 'attendance', 'technical', 'declared_interests']));
        return Peter3Contract::validRequest('analysis', ['schema_version' => '1.0'] + $base)
            && is_array($data['history'] ?? []) && is_array($data['learning_activity'] ?? []);
    }

    public static function validResponse(string $operation, mixed $data): bool
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
                'student_snapshot' => 'required|array', 'career_evidence_profiles' => 'present|array',
                'traceability' => 'required|array', 'traceability.input_hash' => 'required|regex:/^[a-f0-9]{64}$/',
                'sources_used' => 'present|array', 'limitations' => 'present|array', 'warnings' => 'present|array',
                'career_evidence_profiles.*.career_name' => 'required|string',
                'career_evidence_profiles.*.university' => 'required|string',
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
        return ! Validator::make($data, $rules)->fails();
    }
}
