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
            'student_id' => $this->studentId,
            'periodo' => $this->period,
            'materias' => $this->subjects,
            'notas' => $this->grades,
            'asistencia' => $this->attendance,
            'intereses' => $this->interests,
            'especialidad' => $this->specialty,
        ], fn ($value) => $value !== null);
    }
}
