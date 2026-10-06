<?php

namespace App\Services\AporteIngenieril;

use App\Contracts\SpecializedAcademicClient;
use App\Services\AporteIngenieril\DTO\AporteResponse;
use Illuminate\Support\Str;

class TutorService
{
    public function __construct(private readonly SpecializedAcademicClient $client) {}

    public function ask(
        string $studentId,
        string $question,
        array $academicContext = [],
        array $studentContext = [],
        array $conversationHistory = [],
    ): AporteResponse {
        $payload = [
            'schema_version' => '1.0',
            'question' => trim($question),
        ];
        $academicContext = $this->sanitizeContext($academicContext, [
            'academic_period', 'areas_to_reinforce', 'course', 'preparation_route', 'strengths',
        ]);
        $studentContext = $this->sanitizeContext($studentContext, [
            'affinity_label', 'areas_to_reinforce', 'preparation_label', 'riasec_code', 'strengths', 'technical_specialty',
        ]);
        $conversationHistory = collect($conversationHistory)
            ->filter(fn ($turn) => is_array($turn)
                && in_array($turn['role'] ?? null, ['user', 'assistant'], true)
                && is_string($turn['content'] ?? null)
                && trim($turn['content']) !== '')
            ->take(-8)
            ->map(fn ($turn) => [
                'role' => $turn['role'],
                'content' => Str::limit(trim($turn['content']), 2000, ''),
            ])->values()->all();

        if ($academicContext) {
            $payload['academic_context'] = $academicContext;
        }
        if ($studentContext) {
            $payload['student_context'] = $studentContext;
        }
        if ($conversationHistory) {
            $payload['conversation_history'] = $conversationHistory;
        }

        return $this->client->tutor($payload);
    }

    private function sanitizeContext(array $context, array $allowed): array
    {
        return collect($context)->only($allowed)->map(function ($value) {
            if (is_string($value)) {
                return Str::limit(trim($value), 300, '');
            }
            if (is_array($value)) {
                return collect($value)->filter(fn ($item) => is_string($item) && trim($item) !== '')
                    ->take(12)->map(fn ($item) => Str::limit(trim($item), 200, ''))->values()->all();
            }

            return null;
        })->filter(fn ($value) => $value !== null && $value !== '' && $value !== [])->all();
    }
}
