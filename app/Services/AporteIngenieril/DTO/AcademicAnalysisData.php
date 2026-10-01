<?php

namespace App\Services\AporteIngenieril\DTO;

final readonly class AcademicAnalysisData
{
    public function __construct(
        public string $studentId,
        public ?string $period,
        public array $subjects,
        public array $grades,
        public array $attendance,
        public array $interests = [],
        public ?string $specialty = null,
    ) {}

    public function toPayload(): array
    {
        return array_filter([
            'schema_version' => '1.0',
            'student_id' => $this->studentId,
            'academic_period' => $this->period,
            'academic' => $this->grades ? ['records' => $this->grades] : null,
            'attendance' => $this->attendance ?: null,
            'declared_interests' => $this->interests ?: null,
            'technical' => $this->specialty ? ['specialty' => $this->specialty] : null,
        ], fn ($value) => $value !== null);
    }
}
